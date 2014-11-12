<?php
/*
 * ------------------------------------------------------------------------
 * JA Amazon S3 for joomla 2.5 & 3.1
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
*/ 

// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' );

jimport ( 'joomla.application.component.model' );

class jaAmazonS3ModelBucket extends JModelLegacy {
	
	var $_pagination = NULL;
	var $_total = 0;
	
	function getRow($cid = array(0)) {
		$table = &$this->getTable ( 'bucket', 'Table' );		
		// Load the current item if it has been defined
		$edit = JRequest::getVar ( 'edit', true );
		if (! $cid || @! $cid [0]) {
			$cid = JRequest::getVar ( 'cid', array (0 ), '', 'array' );
			JArrayHelper::toInteger ( $cid, array (0 ) );
		}

		if ($edit) {
			$table->load ( $cid [0] );
		}

		$item = $table;
		
		return $item;
	}
	
	function getRow2($cid) {
		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__jaamazons3_bucket WHERE id = ".$db->Quote($cid);
		$db->setQuery($query);
		$row = $db->loadObject();
		return $row;
	}
	
	function getRowByBucket($bucket) {
		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__jaamazons3_bucket WHERE bucket_name = '{$bucket}'";
		$db->setQuery($query);
		$row = $db->loadObject();
		return $row;
	}
	
	function getList($userState) {
		$db = JFactory::getDBO ();
		$bucket = array ();
		
		$limitstart = $userState['limitstart'];
		$limit = $userState['limit'];
		
		$acc_id = (isset($userState['acc_id'])) ? intval($userState['acc_id']) : 0;
		$cond = "AND t.acc_id = {$acc_id}";
		$order = ' t.bucket_name';
		
		if($order != '') {
			$order = "ORDER BY {$order}";
		}
		
		$sql = "
				SELECT t.*
				FROM #__jaamazons3_bucket AS t
				WHERE 1 {$cond}
				{$order}
				LIMIT {$limitstart}, {$limit}";
		$db->setQuery ( $sql );
		$bucket = $db->loadObjectList();
		return $bucket;
	}
	
	function getTotal($userState) {
		
		$acc_id = (isset($userState['acc_id'])) ? intval($userState['acc_id']) : 0;
		$cond = "AND t.acc_id = {$acc_id}";
		$order = '';
		
		$db = JFactory::getDBO ();
		$query = "
				SELECT COUNT(*)  
				FROM #__jaamazons3_bucket AS t
				WHERE 1 {$cond}";
		
		$db->setQuery ( $query );
		$this->_total = $db->loadResult ();
		return $this->_total;
	}
	
	function store($createBucket = false, $post = null) {
		
		$account = $this->getActiveAccount();
		if($account === false) {
			return JText::_("PLEASE_SELECT_ACCOUNT_FIRST");
		}
		
		$row =  $this->getRow ();
		
		if(!$post) {
			$post = $this->getState ( 'request' );
			if(!isset($post["acc_id"]) || empty($post["acc_id"])) {
				$post["acc_id"] = $account->id;
			}
		}
		
		$applySub = isset($post["apply_to_sub"]) ? true : false;
		
		/**
		 * create bucket, set permissions
		 */
		if($createBucket && isset($post["bucket_name"]) && !empty($post["bucket_name"])) {
			$acl = isset($post["bucket_acl"]) ? $post["bucket_acl"] : "";
			$region = isset($post["bucket_region"]) ? $post["bucket_region"] : AmazonS3::REGION_US_E1;
			
			switch ($acl) {
				case 'public': $acl = AmazonS3::ACL_PUBLIC; break;
				//case 'open': $acl = AmazonS3::ACL_OPEN; break;
				case 'private': 
				default: $acl = AmazonS3::ACL_PRIVATE; break;
			}
			
			if(isset($post["bucket_source_id"]) && !empty($post["bucket_source_id"])) {
				/**
				 * copy content from bucket source if selected clone feature
				 */
				$rowSource = $this->getRow2($post["bucket_source_id"]);
				
				$result = $this->copyBucket($account, $post["bucket_name"], $rowSource->bucket_name, $region, $acl);
				if($result !== true) {
					return JText::_($result);
				}
				
			} elseif ((!$row->id) || ($row->bucket_name != $post["bucket_name"]) || ($row->bucket_acl != $acl) || (empty($row->bucket_acl)) || $applySub) {
				//create buckets or update acl or update for bucket' objects
				$result = $this->createBucket($account, $post["bucket_name"], $region, $acl, $applySub);
				if($result !== true) {
					return JText::_($result);
				}
			}
		}
		
		if(!$row->id){			
			//create new bucket
			//check bucket if is already existed
			$rowCheck = $this->getRowByBucket($post["bucket_name"]);
			if($rowCheck && isset($rowCheck->bucket_name)) {
				return JText::_("BUCKET_IS_ALREADY_EXISTED");
			}
		}
		
		if (! $row->bind ( $post )) {
			return $row->getError();
		}
		
		if ( ($erros = $row->check ())) {
			//print_r($erros);
			return implode ( "<br/>", $erros );
		}
		
		if (! $row->store ()) {
			//echo 'error';
			return $row->getError();
		} else {
			//change region?
			
			//thanhnv: why don't have params to set region for specific bucket?
			/*$s3 = jaStorageHelper::getStorage($account);
			if($row->id) {
				$result = $s3->get_bucket_region($row->bucket_name);
				if($result->isOK()) {
					$oldRegion = (string) $result->body;
					if($region != $oldRegion) {
						//echo "{$region}|{$oldRegion}";
						$s3->set_region($region);
					}
				}
			}*/
		}

		return $row;
	}
	
	function createBucket($account, $bucket, $region = AmazonS3::REGION_US_E1, $acl = AmazonS3::ACL_PRIVATE, $applySub = false) {
		$s3 = jaStorageHelper::getStorage($account);
		
		try {
			if($s3->if_bucket_exists($bucket)) {
				$result = true;
			} else {
				$response = $s3->create_bucket($bucket, $region, $acl);
				$result = $response->isOK() ? true : $response->body->Message;
			}
			if($result === true && !empty($acl)) {
				//update bucket acl
				$s3->set_bucket_acl($bucket, $acl);
				//update acl for bucket' objects
				//if($applySub) {
					$modelRepo = JModelLegacy::getInstance ( 'repo', 'jaAmazonS3Model' );
					$modelRepo->updateAcl($account, $bucket, $acl, '');
				//}
			}
		} catch (S3_Exception $e) {
			$result = $e->getMessage();
		}
		return $result;
	}
	
	function copyBucket($account, $bucket, $srcBucket, $region = AmazonS3::REGION_US_E1, $acl = AmazonS3::ACL_PRIVATE) {
		$s3 = jaStorageHelper::getStorage($account);
		

		if(!$s3->if_bucket_exists($bucket)) {
			$response = false;
			
			$dest = $s3->create_bucket($bucket, $region, $acl);
			if ($dest->isOK())
			{
				$modelRepo = JModelLegacy::getInstance ( 'repo', 'jaAmazonS3Model' );
				$oScrBucket = $this->getBucketByName($srcBucket);
				if(!$oScrBucket) {
					return JText::_("INVALID_SOURCE_BUCKET");
				}
				$serverFiles = $modelRepo->getListServerFiles($oScrBucket, '');
				
				$handles = array();
	
				if(is_array($serverFiles) && count($serverFiles)) {
					foreach ($serverFiles as $item)
					{
						$handles[] = $s3->copy_object(
							array('bucket' => $srcBucket, 'filename'=> (string) $item->Key),
							array('bucket' => $bucket, 'filename'=> (string) $item->Key),
							array(
								'acl' => $acl,
								'returnCurlHandle' => true
							)
						);
					}
				}
	
				$request = new RequestCore();
				$response = $request->send_multi_request($handles);
			}
			
			if($response === false) {
				return JText::_("ERROR_IS_OCCUR_WHEN_COPIES_THE_CONTENTS_OF_A_BUCKET_INTO_A_NEW_BUCKET");
			}
		} else {
			//return JText::_("BUCKET_IS_ALREADY_EXISTED");
			return true;
		}
		return true;
	}
	
	function _getVars() {
		static $lists;
		if($lists) return $lists;
		
		$lists = array ();
		$lists ['order'] = JRequest::getString('order', 't.bucket_name desc' );
		$lists ['order_Dir'] = JRequest::getCmd( 'order_Dir', '');
		$lists ['limit'] = JRequest::getInt( 'limit', 20 );
		$lists ['limitstart'] = JRequest::getInt('limitstart', 0);
		// In case limit has been changed, adjust limitstart accordingly
		$limit = $lists['limit'];
		$lists ['limitstart'] = ( $limit != 0 ? (floor($lists['limitstart'] / $limit) * $limit) : 0 );
		
		return $lists;
	}
	
	function _getVars_admin() {
		$mainframe = JFactory::getApplication('administrator');
		$option='bucket';
		$lists = array ();
		$lists ['filter_order'] = $mainframe->getUserStateFromRequest ( $option . '.filter_order', 'filter_order', 't.id', 'string' );
		$lists ['filter_order_Dir'] = $mainframe->getUserStateFromRequest ( $option . '.filter_order_Dir', 'filter_order_Dir', 'desc', 'word' );
		$lists ['limit'] = $mainframe->getUserStateFromRequest ( $option . '.limit', 'limit', 20, 'int' );
		$lists ['limitstart'] = $mainframe->getUserStateFromRequest ( $option . '.limitstart', 'limitstart', 0, 'int' );
		// In case limit has been changed, adjust limitstart accordingly
		$limit = $lists['limit'];
		$lists ['limitstart'] = ( $limit != 0 ? (floor($lists['limitstart'] / $limit) * $limit) : 0 );
		
		$lists ['acc_id'] = $mainframe->getUserStateFromRequest ( $option . '.acc_id', 'acc_id', 0, 'int' );
		if(!$lists['acc_id']) {
			$db = JFactory::getDBO();
			$query = "SELECT id FROM #__jaamazons3_account";
			$db->setQuery($query);
			$obj = $db->loadObjectList();
			if (is_array($obj) && count($obj)==1) {
				$acc_id = $obj[0]->id;
				$lists['acc_id'] = $acc_id;
			}
		}
		return $lists;
	}	
	
	function delete($id) {
		//delete bucket
		$query = "DELETE FROM #__jaamazons3_bucket WHERE id={$id}";
		$this->_db->setQuery($query);
		$result = $this->_db->query();
		return $result;
	}
	
	function removeS3($id) {
		$row = $this->getRow2($id);
		if(!$row) {
			return false;
		}
		$account = $this->getActiveAccount();
		if($account === false) {
			return false;
		}
		$s3 = jaStorageHelper::getStorage($account);
		//delete the bucket and all of its contents.
		$result = $s3->delete_all_objects($row->bucket_name);
		$result = $s3->delete_bucket($row->bucket_name, true);
		
		if($result === false) {
			/**
			 * is maybe a wrong of cloudfusion when deleting a bucket that does not have any file
			 * Try to delte bucket again
			 */
			$result = $s3->delete_bucket($row->bucket_name, false);
		}
		
		if($result === false) {
			return false;
		} else {
			return $this->delete($id);
		}
	}
	
	function deleteByAccount($id) {
		
		// delete only bucket records on user' database
		// do not delete buckets on Amazon S3
		$query="DELETE FROM #__jaamazons3_bucket WHERE acc_id={$id}";
		$this->_db->setQuery($query);
		$result = $this->_db->query();
		return $result;
	}
	
	function getBoxBuckets($name, $value, $javascript = '', $acc_id = '')
	{
		$db = JFactory::getDBO();
		
		$where = (!empty($acc_id)) ? ' AND c.acc_id = ' . (intval($acc_id)) : '';
		$query = '
			SELECT 
				c.acc_id,
				s.acc_label,
				c.id AS bucket_id,
				c.bucket_name 
			FROM #__jaamazons3_account AS s
			INNER JOIN #__jaamazons3_bucket c ON c.acc_id = s.id
			WHERE 1 '.$where.'
			ORDER BY c.acc_id, c.bucket_name
			';
		$db->setQuery( $query );
		$cats = $db->loadObjectList();
		$HTMLCats=array();
		$HTMLCats[0] = new stdClass();
		$HTMLCats[0]->id = '';
		$HTMLCats[0]->title = JText::_("SELECT_BUCKET");
		$acc_id = 0;
		foreach ($cats as $cat) {
			if($acc_id != $cat->acc_id) {
				$acc_id = $cat->acc_id;
				
				$cat->id = $cat->acc_id;
				$cat->title = $cat->acc_label;
				$optgroup = JHtml::_('select.optgroup', $cat->title, 'id', 'title');
				array_push($HTMLCats, $optgroup);
			}
			$cat->id = $cat->bucket_id;
			$cat->title = $cat->bucket_name;
			array_push($HTMLCats, $cat);
		}
		$lists = JHtml::_('select.genericlist', $HTMLCats, $name, 'class="inputbox" size="1" style="width:220px;" '. $javascript, 'id', 'title', $value );
		
		return $lists;
	}
	
	function getListStatus() {
		$aData = array();
		$aData[] = JHtml::_( 'select.option', 'public', JText::_('PUBLIC') );
		$aData[] = JHtml::_( 'select.option', 'private', JText::_('PRIVATE') );
		//$aData[] = JHtml::_( 'select.option', 'open', JText::_('OPEN') );
		return $aData;
	}
	
	function getListProtocols() {
		$aData = array();
		$aData[] = JHtml::_( 'select.option', 'http', 'http' );
		$aData[] = JHtml::_( 'select.option', 'https', 'https' );
		return $aData;
	}
	
	function getListUrlFormats() {
		$aData = array();
		$aData[] = JHtml::_( 'select.option', 'subdomain', 'URL_FORMAT_SUBDOMAIN' );
		$aData[] = JHtml::_( 'select.option', 'folder', 'URL_FORMAT_FOLDER' );
		return $aData;
	}
	
	function getBoxRegion($name, $value = AmazonS3::REGION_US_E1, $javascript = '') {
		$aData = array();
		$aData[] = array('id' => AmazonS3::REGION_US_E1, 'title' => 'Northern Virginia');
		$aData[] = array('id' => AmazonS3::REGION_US_W1, 'title' => 'Northern California');
		$aData[] = array('id' => AmazonS3::REGION_APAC_SE1, 'title' => 'Singapore');
		$aData[] = array('id' => AmazonS3::REGION_EU_W1, 'title' => 'Ireland');
		$aData[] = array('id' => AmazonS3::REGION_APAC_NE1, 'title' => 'Japan');
		
		$lists = JHtml::_('select.genericlist', $aData, $name, 'class="inputbox" size="1" style="width:220px;" '. $javascript, 'id', 'title', $value );
		return $lists;
	}
	
	function getActiveAccount() {
		$lists = $this->_getVars_admin();
		if(!$lists['acc_id']) {
			return false;
		} else {
			$modeAccount = JModelLegacy::getInstance ( 'account', 'jaAmazonS3Model' );
			$account = $modeAccount->getRow2($lists['acc_id']);
			
			if(!isset($account->id) || !$account->id) {
				return false;
			} else {
				return $account;
			}
		}
	}
	
	/**
	 * get bucket by bucket id
	 *
	 * @param unknown_type $bucket_id
	 * @return unknown
	 */
	function getActiveBucket($bucket_id) {
		static $object;
		
		if(!is_object($object) || ($object->id != $bucket_id)) {
			$db = &JFactory::getDBO();
			$query = "
				SELECT 
					s.acc_label,
					s.acc_name,
					s.acc_accesskey,
					s.acc_secretkey,
					c.* 
				FROM #__jaamazons3_account AS s
				INNER JOIN #__jaamazons3_bucket c ON c.acc_id = s.id
				WHERE c.id = '{$bucket_id}'
				";
			$db->setQuery( $query );
			$object = $db->loadObject();
		}
		return $object;
	}
	
	function getBucketByName($bucket_name) {
		
		$db = &JFactory::getDBO();
		$query = "
			SELECT 
				s.acc_label,
				s.acc_name,
				s.acc_accesskey,
				s.acc_secretkey,
				c.* 
			FROM #__jaamazons3_account AS s
			INNER JOIN #__jaamazons3_bucket c ON c.acc_id = s.id
			WHERE c.bucket_name = ".$db->Quote($bucket_name)."
			";
		$db->setQuery( $query );
		$object = $db->loadObject();
		
		if(is_object($object)) {
			return $object;
		} else {
			return false;
		}
	}
	
	function import() {
		$account = $this->getActiveAccount();
		if($account === false) {
			return false;
		} else {
			$s3 = jaStorageHelper::getStorage($account);
			$lists = $s3->get_bucket_list();
			
			$result = true;
			foreach ($lists as $bucket) {
				// bucket name is unique with all user of s3
				$query="SELECT * FROM #__jaamazons3_bucket WHERE bucket_name='{$bucket}'";//AND acc_id = {$account->id}
				$this->_db->setQuery($query);
				$result = $this->_db->loadObject();
				if(!$result) {
					$post = array();
					$post['acc_id'] = $account->id;
					$post['bucket_name'] = $bucket;
					$result &= $this->store(false, $post);
				}
			}
			return $result;
		}
	}
	function getBoxMappedProfiles($name, $bucket_id, $javascript = '')
	{
		$db = &JFactory::getDBO();
		$value = '';
		
		$query = 'SELECT * FROM #__jaamazons3_profile WHERE bucket_id = '.$db->Quote($bucket_id).' ORDER BY profile_name';
		$db->setQuery( $query );
		$cats = $db->loadObjectList();
		$HTMLCats=array();
		$HTMLCats[0] = new stdClass();
		$HTMLCats[0]->id = '';
		$HTMLCats[0]->title = JText::_("SELECT_PROFILE");
		foreach ($cats as $cat) {
			$cat->id = $cat->id;
			$cat->title = $cat->profile_name;
			array_push($HTMLCats, $cat);
		}
		$lists = JHtml::_('select.genericlist', $HTMLCats, $name, 'class="inputbox" size="1" style="width:220px;" '. $javascript, 'id', 'title', $value );
		
		return $lists;
	}
}
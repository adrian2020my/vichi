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

class jaAmazonS3ModelProfile extends JModelLegacy {
	
	var $_pagination = NULL;
	var $_total = 0;
	
	function getRow($cid = array(0)) {
		$table = &$this->getTable ( 'profile', 'Table' );		
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
		$query = "SELECT * FROM #__jaamazons3_profile WHERE id = ".$db->Quote($cid);
		$db->setQuery($query);
		$row = $db->loadObject();
		return $row;
	}
	
	function getRowByProfile($profile) {
		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__jaamazons3_profile WHERE profile_name = '{$profile}'";
		$db->setQuery($query);
		$row = $db->loadObject();
		return $row;
	}
	
	function getList($userState) {
		$db = JFactory::getDBO ();
		$profile = array ();
		
		$limitstart = $userState['limitstart'];
		$limit = $userState['limit'];
		
		$order = ' t.profile_name';
		
		if($order != '') {
			$order = "ORDER BY t.is_default DESC, {$order}";
		}
		
		$sql = "
				SELECT t.*, b.bucket_name
				FROM #__jaamazons3_profile AS t
				LEFT JOIN #__jaamazons3_bucket b ON b.id = t.bucket_id
				WHERE 1
				{$order}
				LIMIT {$limitstart}, {$limit}";
		$db->setQuery ( $sql );
		$profile = $db->loadObjectList();
		return $profile;
	}
	
	function getTotal($userState) {
		
		$order = '';
		
		$db = JFactory::getDBO ();
		$query = "
				SELECT COUNT(*)  
				FROM #__jaamazons3_profile AS t
				WHERE 1";
		
		$db->setQuery ( $query );
		$this->_total = $db->loadResult ();
		return $this->_total;
	}
	
	function store($createProfile = false, $post = null) {
		
		$row = & $this->getRow ();
		
		if(!$post) {
			$post = $this->getState ( 'request' );
		}
		
		
		if(!$row->id){			
			//create new profile
			//check profile if is already existed
			$rowCheck = $this->getRowByProfile($post["profile_name"]);
			if($rowCheck && isset($rowCheck->profile_name)) {
				return JText::_("PROFILE_IS_ALREADY_EXISTED");
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
			//
		}

		return $row;
	}
		
	function _getVars() {
		static $lists;
		if($lists) return $lists;
		
		$lists = array ();
		$lists ['order'] = JRequest::getString('order', 't.profile_name desc' );
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
		$option='profile';
		$lists = array ();
		$lists ['filter_order'] = $mainframe->getUserStateFromRequest ( $option . '.filter_order', 'filter_order', 't.id', 'string' );
		$lists ['filter_order_Dir'] = $mainframe->getUserStateFromRequest ( $option . '.filter_order_Dir', 'filter_order_Dir', 'desc', 'word' );
		$lists ['limit'] = $mainframe->getUserStateFromRequest ( $option . '.limit', 'limit', 20, 'int' );
		$lists ['limitstart'] = $mainframe->getUserStateFromRequest ( $option . '.limitstart', 'limitstart', 0, 'int' );
		// In case limit has been changed, adjust limitstart accordingly
		$limit = $lists['limit'];
		$lists ['limitstart'] = ( $limit != 0 ? (floor($lists['limitstart'] / $limit) * $limit) : 0 );
		
		return $lists;
	}	
	
	function delete($id) {
		$id = (int) $id;
		
		$queryCheck = "SELECT * FROM #__jaamazons3_profile WHERE id={$id}";
		$this->_db->setQuery($queryCheck);
		$obj = $this->_db->loadObject();
		if(!is_object($obj)) {
			return false;
		} else {
			if($obj->is_default) {
				return false; //can not delete default profile
			}
		}
	
		//delete config of this profile' item
		$query = "DELETE FROM #__jaamazons3_disabled WHERE `profile_id` = '{$id}'";
		$this->_db->setQuery($query);
		$result = $this->_db->query();
		
		//delete profile
		$query = "DELETE FROM #__jaamazons3_profile WHERE id={$id}";
		$this->_db->setQuery($query);
		$result = $this->_db->query();
		return $result;
	}
	
	
	function deleteByBucket($id) {
		
		//delete config of profile' item
		$query = "
			DELETE FROM #__jaamazons3_disabled WHERE `profile_id` IN (
				SELECT `id` FROM #__jaamazons3_profile WHERE bucket_id={$id}
			)
			";
		$this->_db->setQuery($query);
		$result = $this->_db->query();
		
		// delete only profile records on user' database
		// do not delete profiles on Amazon S3
		$query="DELETE FROM #__jaamazons3_profile WHERE bucket_id={$id}";
		$this->_db->setQuery($query);
		$result = $this->_db->query();
		return $result;
	}
	
	function getBoxProfiles($name, $value, $javascript = '')
	{
		$db = &JFactory::getDBO();
		
		$query = 'SELECT * FROM #__jaamazons3_profile ORDER BY profile_name';
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
	
	/**
	 * get profile by profile id
	 *
	 * @param unknown_type $profile_id
	 * @return unknown
	 */
	function getActiveProfile($profile_id) {
		static $object;
		
		if(!is_object($object) || ($object->id != $profile_id)) {
			$db = &JFactory::getDBO();

			$query = "
				SELECT 
					a.acc_label,
					a.acc_name,
					a.acc_accesskey,
					a.acc_secretkey,
					b.acc_id, 
					b.bucket_name, 
					b.bucket_acl, 
					b.bucket_protocol, 
					b.bucket_url_format, 
					b.bucket_cloudfront_domain,
					b.last_sync,
					p.* 
				FROM #__jaamazons3_profile AS p
				INNER JOIN #__jaamazons3_bucket b ON b.id = p.bucket_id 
				INNER JOIN #__jaamazons3_account a ON b.acc_id = a.id 
				WHERE p.id = '{$profile_id}'
				";
			$db->setQuery( $query );
			$object = $db->loadObject();
		}
		return $object;
	}
	
	function getBoxFolders($name, $value, $javascript = '') {
		$params = JComponentHelper::getParams(JACOMPONENT);
		$depth = (int) $params->get('profile_path_depth', 2);
		if($depth) 
			$depth = $depth - 1;
		
		//$depth = JA_PROFILE_PATH_DEPTH;
		
		$folders = JFolder::folders(JPATH_ROOT, '.', $depth, true);
		
		$items = array();
		$items[] = (object) array('id' => '{jpath_root}', 'title' => JText::_("SITE_ROOT"));
		$separate = '- - ';
		foreach ($folders as $f) {
			$f = substr($f, strlen(JPATH_ROOT . '/'));
			
			$parts = explode('/', $f);
			$level = count($parts);
			$folder = $parts[$level-1];
			$title = str_pad('', $level * strlen($separate), $separate)."". $folder;
			
			$fid = JPath::clean('{jpath_root}/'.$f, '/');
			$items[] = (object) array('id' => $fid, 'title' => $title);
		}
		$lists = JHtml::_('select.genericlist', $items, $name, 'class="inputbox" size="1" style="width:220px;" '. $javascript, 'id', 'title', $value );
		return $lists;
	}
}
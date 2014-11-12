<?php
/**
 * @desc Modify from component Media Manager of Joomla
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

/**
 * Weblinks Component Weblink Model
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class jaAmazonS3ModelRepo extends JModelLegacy
{

	function getState($property = null,$default = null)
	{
		$folder = JRequest::getVar( 'folder', '', '', 'none' );
		//$folder = JPath::clean($folder);
		$this->setState('folder', $folder);

		$parent = str_replace("\\", "/", dirname($folder));
		$parent = ($parent == '.') ? null : $parent;
		$this->setState('parent', $parent);
		return parent::getState($property);
	}
	
	function getListServerFiles($bucket, $folder = '', $marker = '', $delimiter = '') {
		static $count = 0;
		$count++;
		$s3 = jaStorageHelper::getStorage($bucket);
		$aFiles = array();
		
		$maxKeys = 1000;
		if(!empty($folder)) {
			//upload for sub folder, not root folder
			$subFolderPath = jaStorageHelper::cleanPath($folder)."/";
		} else {
			$subFolderPath = $folder;
		}
		
		$opts = array("prefix"=>$subFolderPath,"marker"=>$marker, "max-keys" => $maxKeys);
		if(!empty($delimiter)) {
			$opts["delimiter"] = $delimiter;
		}
		$list = $s3->list_objects($bucket->bucket_name, $opts);
		
		if (isset($list))
		{
			// Loop through and find the filenames.
			foreach ($list->body->Contents as $file)
			{
				$fileName = (string) $file->Key; 
				/**
				 * [0] => CFSimpleXML Object
                        (
                            [Key] => images/smilies/biggrin.gif
                            [LastModified] => 2010-10-28T03:12:18.000Z
                            [ETag] => "829dd9c5e7f55fdbcb34309ec44b973e"
                            [Size] => 929
                            [Owner] => CFSimpleXML Object
                                (
                                    [ID] => 26466a41ee857b08763abe9568d29fb0078e62f5fb37a1cccb4399cf3e2e9638
                                    [DisplayName] => joomlart
                                )

                            [StorageClass] => STANDARD
                        )
				 */
				//$aFiles[] = $file; 
				$aFiles[] = (object) array(
										'Key' => (string) $file->Key, 
										'LastModified' => (string) $file->LastModified,
										'ETag' => (string) $file->ETag,  
										'Size' => (string) $file->Size);
			}
			
			if(((string) $list->body->IsTruncated === 'true') && !empty($fileName) && ($fileName != $marker)) {
				//echo $marker."|".$fileName . "\r\n";
				/*if($count >= 3) {
					die();
				}*/
				//if result is truncated
				//use last file as marker to get next page
				$aFiles2 = $this->getListServerFiles($bucket, $folder, $fileName, $delimiter);
				//merge pages
				if(count($aFiles2)) {
					foreach ($aFiles2 as $file) {
						$aFiles[] = $file;
					}
				}
			}
		}
		return $aFiles;
	}
	
	function updateAcl($account, $bucket, $acl = AmazonS3::ACL_PRIVATE, $folder = '') {
		$s3 = jaStorageHelper::getStorage($account);
		
		$modelBucket = JModelLegacy::getInstance ( 'bucket', 'jaAmazonS3Model' );
		$oBucket = $modelBucket->getBucketByName($bucket);
		if(!$oBucket) {
			return false;
		}
		$list = $this->getListServerFiles($oBucket, $folder);
		// As long as we have at least one match...
		if (count($list) > 0)
		{
			
			// Hold CURL handles
			$handles = array();

			// Go through all of the items and delete them.
			foreach ($list as $item)
			{
				$handles[] = $s3->set_object_acl($bucket, (string) $item->Key, $acl, array('returnCurlHandle' => true));
			}

			$request = new RequestCore();
			$request->send_multi_request($handles);
		}
		return true;
	}
	
	function getObjectAcl($list) {
		$acl = 'private';
		if(isset($list->Grant)) {
			foreach ($list->Grant as $user) {
				if(isset($user->Grantee) && isset($user->Grantee->URI)
					&& ($user->Grantee->URI == 'http://acs.amazonaws.com/groups/global/AllUsers')
				) {
					if(isset($user->Permission)) {
						switch ($user->Permission) {
							case 'FULL_CONTROL': $acl = 'open'; break;
							case 'WRITE': $acl = 'open'; break;
							case 'READ': $acl = 'public'; break;
						}
					} else {
						$acl = 'private';
					}
					break;
				}
			}
		}
		return $acl;
	}
	
	function deleteAdvance($bucket, $folder, $pcre = '\.php$') {
		$s3 = jaStorageHelper::getStorage($bucket);
		
		$folder = jaStorageHelper::cleanPath($folder);
		//delete is only apply for selected folder
		//escape Regex characters
		//$folderSearch = preg_replace("/([\:\-\/\.\?\(\)\[\]\{\}])/", "\\\\$1", $folder);
		if(!empty($pcre)) {
			//$pattern = "/{$folderSearch}.*?{$pcre}/";
			$pattern = "/{$pcre}/";
		} else {
			//delete all
			//if want to delete all when pcre is blank, let remove above code check empty $pcre
			//$pattern = "/{$folderSearch}.*/";
			$pattern = "/.*/i";
		}
		
		//$result = $s3->delete_all_objects($bucket->bucket_name, $pattern);
		
		/**
		 * Fixbug: delete_all_objects of CloudFusion
		 * Since it is unable to filter by prefix
		 */
		// Collect all matches
		$list = $s3->get_object_list($bucket->bucket_name, array('pcre' => $pattern, 'prefix' => $folder));
		
		// As long as we have at least one match...
		if (count($list) > 0)
		{
			// Hold CURL handles
			$handles = array();

			// Go through all of the items and delete them.
			foreach ($list as $item)
			{
				$handles[] = $s3->delete_object($bucket->bucket_name, $item, array('returnCurlHandle' => true));
			}

			$request = new RequestCore();
			$result = $request->send_multi_request($handles);
		} else {
			$result = false;
		}
		return $result;
	}

	function getFolderTree($base = null)
	{
		$bucket = $this->getActiveBucket();
		if($bucket === false) {
			return array();
		}
		
		$s3 = jaStorageHelper::getStorage($bucket);
		
		$result = $s3->list_objects($bucket->bucket_name, array("prefix" => "", "delimiter" => "/", "max-keys" => 5000));
		
		$mediaBase = '';
		$folders = array();
		if(isset($result->body->CommonPrefixes)) {
			foreach ($result->body->CommonPrefixes as $item) {
				$folder = $item->Prefix;
				// remove last slashes
				$folder = preg_replace("/\/*$/", "", $folder);
				if(!empty($folder) && $folder != '.' && $folder != '..') {
					$folder = "/{$folder}";
					if(!in_array($folder, $folders)) {
						$folders[] = $folder;
					}
				}
			}
		}
		sort($folders);

		$tree = array();
		foreach ($folders as $folder)
		{
			$folder		= JPath::clean($folder);
			$folder		= str_replace('\\', '/', $folder);
			$name		= substr($folder, strrpos($folder, '/') + 1);
			$relative	= str_replace($mediaBase, '', $folder);
			$absolute	= $folder;
			$path		= explode('/', $relative);
			$node		= (object) array('name' => $name, 'relative' => $relative, 'absolute' => $absolute);

			$tmp = &$tree;
			for ($i=0,$n=count($path); $i<$n; $i++)
			{
				if (!isset($tmp['children'])) {
					$tmp['children'] = array();
				}
				if ($i == $n-1) {
					// We need to place the node
					$tmp['children'][$relative] = array('data' =>$node, 'children' => array());
					break;
				}
				if (array_key_exists($key = implode('/', array_slice($path, 0, $i+1)), $tmp['children'])) {
					$tmp = &$tmp['children'][$key];
				}
			}
		}
		$tree['data'] = (object) array('name' => $bucket->bucket_name, 'relative' => '', 'absolute' => $base);
		return $tree;
	}
	
	function _getVars_admin() {
		$mainframe = JFactory::getApplication('administrator');
		$option='repo';
		$lists = array ();
		$lists ['bucket_id'] = $mainframe->getUserStateFromRequest ( $option . '.bucket_id', 'bucket_id', 0, 'int' );
		return $lists;
	}	
	
	function getActiveBucket($bucket_id = 0) {
		if(!$bucket_id) {
			$lists = $this->_getVars_admin();
			$bucket_id = $lists['bucket_id'];
		}
		
		if(!$bucket_id) {
			return false;
		} else {
			$modeBucket = JModelLegacy::getInstance ( 'bucket', 'jaAmazonS3Model' );
			$bucket = $modeBucket->getActiveBucket($bucket_id);
			
			if(!isset($bucket->id) || !$bucket->id) {
				return false;
			} else {
				return $bucket;
			}
		}
	}
}
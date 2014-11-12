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
class jaAmazonS3ModelLocalrepo extends JModelLegacy
{

	function getState($property = null,$default = null)
	{
		static $set;

		if (!$set) {
			$folder = JRequest::getVar( 'folder', '', '', 'none' );
			//$folder = JPath::clean($folder);
			$this->setState('folder', $folder);

			$parent = str_replace("\\", "/", dirname($folder));
			$parent = ($parent == '.') ? null : $parent;
			$this->setState('parent', $parent);
			$set = true;
		}
		return parent::getState($property);
	}
	
	function getProfileLocalPath($profile) {
		$basePath = JPath::clean($profile->site_path . '/');
		$basePath = str_replace('{jpath_root}', JPATH_ROOT, $basePath);
		$basePath = JPath::clean($basePath);
		return $basePath;
	}
	
	/**
	 * get list objects of profile depend on local
	 * so that can remove disabled item from scan list 
	 * and reduce process
	 *
	 * @param (object) $profile
	 * @param (string) $folder
	 * @param (string) $marker
	 */
	function getListServerFiles($profile, $folder = '') {
		
		$s3 = jaStorageHelper::getStorage($profile);
		$basePath = $this->getProfileLocalPath($profile);
		$uploadPath = JPath::clean($basePath.'/'.$folder . '/');
		$listFolders = JFolder::folders($uploadPath, '.', false, true);
		//
		$aExclude = $this->getListItemDisabled($profile);
		if(count($aExclude)) {
			foreach ($aExclude as $itemid => $item) {
				$aExclude[$itemid] = JPath::clean($basePath.'/'.$item);
			}
		}
		//die("400|".print_r($aExclude, true));
		
		if(count($aExclude)) {
			$aFiles = $this->_getListServerFiles($profile, $folder, '', '/');//files of current upload folder
			//get files of sub upload folders (except disabled folders)
			if(count($listFolders)) {
				foreach ($listFolders as $subFolder) {
					$subFolder = JPath::clean($subFolder);
					//die("400|".$subFolder);
					if(!in_array($subFolder, $aExclude)) {
						$sfolder = substr($subFolder, strlen($basePath));
						$sfolder = jaStorageHelper::cleanPath($sfolder);
						
						$aFilesSub = $this->_getListServerFiles($profile, $sfolder, '', '');
						if(count($aFilesSub)) {
							foreach ($aFilesSub as $file) {
								$aFiles[] = $file;
							}
						}
					} else {
						//die("400|{$subFolder}");
					}
				}
			}
			return $aFiles;
		} else {
			return $this->_getListServerFiles($profile, $folder, '', '');
		}
	}
	
	function _getListServerFiles($profile, $folder = '', $marker = '', $delimiter = '') {
		static $count = 0;
		$count++;
		$s3 = jaStorageHelper::getStorage($profile);
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
		$list = $s3->list_objects($profile->bucket_name, $opts);
		
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
				$aFiles2 = $this->_getListServerFiles($profile, $folder, $fileName, $delimiter);
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

	function getFolderTree($base = null)
	{
		$modelLocalrepo = JModelLegacy::getInstance ( 'localrepo', 'jaAmazonS3Model' );
		$profile = $modelLocalrepo->getActiveProfile();
		if($profile === false) {
			return array();
		}
		
		$profile_local_path = $modelLocalrepo->getProfileLocalPath($profile);
		$mediaBase = str_replace('\\', '/', $profile_local_path);
		
		if(empty($base)) {
			$base = $profile_local_path;
		}
		$base = preg_replace("#[/\\\\]+$#", '', $base);

		// Get the list of folders
		jimport('joomla.filesystem.folder');
		$folders = JFolder::folders($base, '.', 1, true);

		$tree = array();
		foreach ($folders as $folder)
		{
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
		$tree['data'] = (object) array('name' => $profile->bucket_name, 'relative' => '', 'absolute' => $base);
		return $tree;
	}
	
	function _getVars_admin() {
		$mainframe = JFactory::getApplication('administrator');
		$option='localrepo';
		$lists = array ();
		$lists ['profile_id'] = $mainframe->getUserStateFromRequest ( $option . '.profile_id', 'profile_id', 0, 'int' );
		return $lists;
	}		
	
	function getActiveProfile() {
		$lists = $this->_getVars_admin();
		if(!$lists['profile_id']) {
			return false;
		} else {
			$modeProfile = JModelLegacy::getInstance ( 'profile', 'jaAmazonS3Model' );
			$profile = $modeProfile->getActiveProfile($lists['profile_id']);
			
			if(!isset($profile->id) || !$profile->id) {
				return false;
			} else {
				return $profile;
			}
		}
	}
	
	function getListItemDisabled($profile = false) {
		static $list;
		
		if(!$profile) {
			$profile = $this->getActiveProfile();
		}
		
		if(!is_object($profile)) {
			return array();
		}
		
		if(!isset($list[$profile->id])) {
			$slist = array();
			
			if($profile !== false) {
				$db = JFactory::getDBO();
				$query = "
					SELECT `path` FROM #__jaamazons3_disabled
					WHERE `profile_id` = '{$profile->id}'";
				$db->setQuery($query);
				$listDisabled = $db->loadObjectList();
				
				if(count($listDisabled)) {
					foreach ($listDisabled as $item) {
						$slist[] = $item->path;
					}
				}
			}
			$list[$profile->id] = $slist;
		}
		return $list[$profile->id];
	}
	
	function checkItemStatus(&$item) {
		$listDisabled = $this->getListItemDisabled();
		
		$item->path_relative = JPath::clean("/{$item->path_relative}", '/');
		
		$s3Path = jaStorageHelper::cleanPath($item->path_relative);
		if(in_array($s3Path, $listDisabled)) {
			$item->status = 'disabled';
		} else {
			$item->status = 'enabled';
		}
		return $this->getItemStatus($item);
	}
	
	function getItemStatus($item) {
		
		$published = (isset($item->status) && ($item->status == 'disabled')) ? 0 : 1;
		$img 	= $published ? "tick.png" : "publish_x.png";
		$task 	= $published ? 'disable' : 'enable';
		$css 	= $published ? 'item-enabled' : 'item-disabled';
		$alt 	= $published ? JText::_('ENABLED' ) : JText::_('DISABLED' );
		$action = $published ? JText::_('DISABLE_ITEM' ) : JText::_('ENABLE_ITEM' );
		
		$s3Path = jaStorageHelper::cleanPath($item->path_relative);
		
		$id = preg_replace("/[\$\.]/", '_', $item->name);
		$href = '
		<span id="status-'.$id.'" class="'.$css.'">
		<a href="javascript:void(0);" onclick="jaUpdateStatus(\''. $id .'\', \''. $item->name .'\', \''. $s3Path .'\', \''. $task .'\');" title="'. $action .'">
		<img src="components/'.JACOMPONENT.'/assets/images/icons/'. $img .'" border="0" alt="'. $alt .'" /></a>
		</span>
		';

		return $href;
	}
	
	function setItemEnable($profile, $item) {
		$db = JFactory::getDBO();
		$s3Path = jaStorageHelper::cleanPath($item->path_relative);
		
		$query = "
			DELETE FROM #__jaamazons3_disabled
			WHERE `profile_id` = '{$profile->id}'
			AND `path` = '{$s3Path}'";
		$db->setQuery($query);
		$result = $db->query();
		if($result !== false) {
			return $this->getItemStatus($item);
		} else {
			return JText::_("FAIL_UPDATE");
		}
	}
	
	function setItemDisable($profile, $item) {
		$db = JFactory::getDBO();
		$s3Path = jaStorageHelper::cleanPath($item->path_relative);
		$query = "
			INSERT IGNORE INTO #__jaamazons3_disabled
			(`profile_id`, `path`) 
			VALUES 
			('{$profile->id}', '{$s3Path}')";
		$db->setQuery($query);
		$result = $db->query();
		if($result !== false) {
			return $this->getItemStatus($item);
		} else {
			return JText::_("FAIL_UPDATE");
		}
	}
	function getUploadFile($token, $suffix = '') {
		$path = JPATH_SITE.'/cache/'.JACOMPONENT.'/';
		if(!is_dir(JPATH_SITE.'/cache/'.JACOMPONENT.'/')) {
			JFolder::create($path, 0777);
		}
		
		$fileName = $token.$suffix;
		$file = $path.$fileName.".txt";
		return $file;
	}
}
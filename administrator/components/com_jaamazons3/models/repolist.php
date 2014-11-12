<?php
/**
 * @desc Modify from component Media Manager of Joomla
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Media Component List Model
 *
 * @package		Joomla
 * @subpackage	Media
 * @since 1.5
 */
class jaAmazonS3ModelRepolist extends JModelLegacy
{

	function getState($property = null)
	{
		$folder = JRequest::getVar( 'folder', '', '', 'none' );
		//$folder = JPath::clean($folder);
		$this->setState('folder', $folder);

		$parent = str_replace("\\", "/", dirname($folder));
		$parent = ($parent == '.') ? null : $parent;
		$this->setState('parent', $parent);
		return parent::getState($property);
	}

	function getImages()
	{
		$list = $this->getList();
		return $list['images'];
	}

	function getFolders()
	{
		$list = $this->getList();
		return $list['folders'];
	}

	function getDocuments()
	{
		$list = $this->getList();
		return $list['docs'];
	}

	/**
	 * Build imagelist
	 *
	 * @param string $listFolder The image directory to display
	 * @since 1.5
	 */
	function getList()
	{
		static $list;
		// Only process the list once per request
		if (is_array($list)) {
			return $list;
		}
		
		$modelRepo = JModelLegacy::getInstance ( 'repo', 'jaAmazonS3Model' );
		$bucket = $modelRepo->getActiveBucket();
		if($bucket === false) {
			return array('folders' => array(), 'docs' => array(), 'images' => array());
		}
		
		$s3 = jaStorageHelper::getStorage($bucket);


		// Get current path from request
		$current = $this->getState('folder');

		// If undefined, set to empty
		if ($current == 'undefined') {
			$current = '';
		}
		
		$current = jaStorageHelper::cleanPath($current);
		if(!empty($current)) {
			$current .= "/";
			$deepLevel = count(explode("/", $current)) - 1;
		} else {
			$deepLevel = 0;
		}
		
		//set delimiter is / to get only file on this folder, not on sub folders
		$result = $s3->list_objects($bucket->bucket_name, array("prefix"=>$current, "delimiter" => "/", "max-keys" => 5000));

		$images 	= array ();
		$folders 	= array ();
		$docs 		= array ();
		
		// Initialize variables
		$basePath = $current;
		$mediaBase = '';
		if(isset($result->body->Contents)) {
			$objects = $result->body->Contents;
	
			// Get the list of files and folders from the given folder
			$fileList 	= $objects;
			
	
			$iconUrl = "components/".JACOMPONENT."/assets/images/icons/";
			$iconPath = JPath::clean(JPATH_ADMINISTRATOR.'/'.$iconUrl.'/');
			// Iterate over the files if they exist
			if ($fileList !== false && count($fileList)) {
				foreach ($fileList as $file)
				{
					/*$acl = $s3->get_object_acl($bucket->bucket_name, $file->Key);
					if($acl) {
						$status = $modelRepo->getObjectAcl($acl->body->AccessControlList);
					} else {
						$status = 'na';
					}*/
					$status = 'na';
					
					$deepLevelSub = count(explode("/", (string) $file->Key));
					if (substr($file, 0, 1) != '.' && strtolower($file) !== 'index.html' && ($deepLevelSub == $deepLevel + 1) ) {
						$tmp = new JObject();
						$fileKey = (string) $file->Key;
						$tmp->name = basename($fileKey);
						$tmp->path = str_replace('\\', '/', JPath::clean($fileKey));
						$tmp->path_relative = str_replace($mediaBase, '', $tmp->path);
						$tmp->size = $file->Size;
						$tmp->lastModified = $file->LastModified;
						$tmp->status = $status;
	
						$ext = strtolower(JFile::getExt((string) $file->Key));
						$tmp->ext = $ext;
						switch ($ext)
						{
							// Image
							case 'jpg':
							case 'png':
							case 'gif':
							case 'xcf':
							case 'odg':
							case 'bmp':
							case 'jpeg':
								$iconfile_32 = $iconPath."mime-icon-32/".$ext.".png";
								if (file_exists($iconfile_32)) {
									$tmp->icon_32 = $iconUrl."mime-icon-32/".$ext.".png";
								} else {
									$tmp->icon_32 = $iconUrl."con_info.png";
								}
								$iconfile_16 = $iconPath."mime-icon-16/".$ext.".png";
								if (file_exists($iconfile_16)) {
									$tmp->icon_16 = $iconUrl."mime-icon-16/".$ext.".png";
								} else {
									$tmp->icon_16 = $iconUrl."con_info.png";
								}
								$images[] = $tmp;
								break;
							// Non-image document
							default:
								$iconfile_32 = $iconPath."mime-icon-32/".$ext.".png";
								if (file_exists($iconfile_32)) {
									$tmp->icon_32 = $iconUrl."mime-icon-32/".$ext.".png";
								} else {
									$tmp->icon_32 = $iconUrl."con_info.png";
								}
								$iconfile_16 = $iconPath."mime-icon-16/".$ext.".png";
								if (file_exists($iconfile_16)) {
									$tmp->icon_16 = $iconUrl."mime-icon-16/".$ext.".png";
								} else {
									$tmp->icon_16 = $iconUrl."con_info.png";
								}
								$docs[] = $tmp;
								break;
						}
					}
				}
			}
		}
	
		
		$folderList = array();
		if(isset($result->body->CommonPrefixes)) {
			foreach ($result->body->CommonPrefixes as $item) {
				$folder = $item->Prefix;
				$folder = JPath::clean("/{$folder}", '/');
				$folder = preg_replace("/\/+$/", '', $folder);
				
				if(!in_array($folder, $folderList)) {
					$folderList[] = $folder;
				}
			}
		}
		// Iterate over the folders if they exist
		if (count($folderList)) {
			foreach ($folderList as $folder) {
				$tmp = new JObject();
				$tmp->name = basename($folder);
				$tmp->path = str_replace('\\', '/', JPath::clean($folder));
				$tmp->path_relative = str_replace($mediaBase, '', $tmp->path);
				$tmp->files = 0;
				$tmp->folders = 0;

				$folders[] = $tmp;
			}
		}
		$list = array('folders' => $folders, 'docs' => $docs, 'images' => $images);
		//adding upload log information
		$db = JFactory::getDBO();
		foreach ($list as $group => $items) {
			if(count($items)) {
				foreach ($items as $id => $item) {
					$sql = "SELECT last_update, file_original_checksum FROM `#__jaamazons3_file` WHERE file_exists = 1 AND bucket_id = '{$bucket->id}' AND path = '{$item->path_relative}'";
					$db->setQuery($sql);
					$log = $db->loadObject();
					if(is_object($log)) {
						$item->last_update = $log->last_update;
						$item->compressed = !empty($log->file_original_checksum) ? 1 : 0;
					} else {
						$item->last_update = false;
						$item->compressed = 0;
					}
					$list[$group][$id] = $item;
				}
			}
		}

		return $list;
	}
}
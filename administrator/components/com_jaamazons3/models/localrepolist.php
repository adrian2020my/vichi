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
class jaAmazonS3ModelLocalrepolist extends JModelLegacy
{

	function getState($property = null)
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
		$params = JComponentHelper::getParams(JACOMPONENT);
		$uploadKey = $params->get('upload_secret_key', '');
		
		$modelLocalrepo = JModelLegacy::getInstance ( 'localrepo', 'jaAmazonS3Model' );
		$profile = $modelLocalrepo->getActiveProfile();
		if($profile === false) {
			return array('folders' => array(), 'docs' => array(), 'images' => array());
		}
		
		$profile_local_path = $modelLocalrepo->getProfileLocalPath($profile);
		$mediaBase = str_replace('\\', '/', $profile_local_path);

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
		
		$basePath = JPath::clean($profile_local_path.'/'.$current);
		
		$images 	= array ();
		$folders 	= array ();
		$docs 		= array ();
		
		
		//show only uploadable on list
		$exts = $profile->allowed_extension;
		$aExts = explode(",", $exts);
		if(count($aExts) >= 1) {
			$patterns = "\.(?:".implode("|", $aExts).")$";
		} else {
			$patterns = ".";
		}
					
		// Get the list of files and folders from the given folder
		$fileList 	= JFolder::files($basePath, $patterns);
		$folderList = JFolder::folders($basePath);

		$iconUrl = "components/".JACOMPONENT."/assets/images/icons/";
		$iconPath = JPath::clean(JPATH_ADMINISTRATOR.'/'.$iconUrl.'/');
		// Iterate over the files if they exist
		if ($fileList !== false) {
			foreach ($fileList as $file)
			{
				if (is_file($basePath.'/'.$file) && substr($file, 0, 1) != '.' && strtolower($file) !== 'index.html') {
					$tmp = new JObject();
					$tmp->name = $file;
					$tmp->path = str_replace('\\', '/', JPath::clean($basePath.'/'.$file));
					$tmp->path_relative = str_replace($mediaBase, '', $tmp->path);
					$tmp->size = filesize($tmp->path);

					$ext = strtolower(JFile::getExt($file));
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

		// Iterate over the folders if they exist
		if ($folderList !== false) {
			foreach ($folderList as $folder) {
				$tmp = new JObject();
				$tmp->name = basename($folder);
				$tmp->path = str_replace('\\', '/', JPath::clean($basePath.'/'.$folder));
				$tmp->path_relative = str_replace($mediaBase, '', $tmp->path);
				
				$key = "action=upload&uploadKey={$uploadKey}&profile={$profile->id}&folder={$tmp->path_relative}&run=1";
				$key = urlencode(jakey_encrypt($key, md5('1218787810')));
				$tmp->upload_url = JURI::root()."administrator/components/".JACOMPONENT."/cron.php?key=".$key;
					
				$count = RepoHelper::countFiles($tmp->path);
				$tmp->files = $count[0];
				$tmp->folders = $count[1];

				$folders[] = $tmp;
			}
		}

		$list = array('folders' => $folders, 'docs' => $docs, 'images' => $images);
		
		//adding upload log information
		$db = JFactory::getDBO();
		foreach ($list as $group => $items) {
			if(count($items)) {
				foreach ($items as $id => $item) {
					$sql = "SELECT last_update, file_original_checksum FROM `#__jaamazons3_file` WHERE file_exists = 1 AND bucket_id = '{$profile->bucket_id}' AND path = '{$item->path_relative}'";
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
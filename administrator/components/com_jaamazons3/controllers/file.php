<?php
/**
 * @desc Modify from component Media Manager of Joomla
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.path');

/**
 * Weblinks Weblink Controller
 *
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.5
 */
class jaAmazonS3ControllerFile extends jaAmazonS3Controller
{
	function update_acl_public() {
		$this->update_acl(AmazonS3::ACL_PUBLIC);
	}
	
	function update_acl_private() {
		$this->update_acl(AmazonS3::ACL_PRIVATE);
	}
	
	/*function update_acl_open() {
		$this->update_acl(AmazonS3::ACL_OPEN);
	}*/
	
	function update_acl($acl = AmazonS3::ACL_PRIVATE) {
		
		$mainframe = JFactory::getApplication('administrator');

		JRequest::checkToken( 'request' ) or jexit( 'Invalid Token' );

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		// Get some data from the request
		$tmpl	= JRequest::getCmd( 'tmpl' );
		$paths	= JRequest::getVar( 'rm', array(), '', 'array' );
		$folder = JRequest::getVar( 'folder', '');

		// Initialize variables
		$msg = array();
		$ret = true;
		/**
		 * get s3 object
		 */
		$modelRepo = JModelLegacy::getInstance ( 'repo', 'jaAmazonS3Model' );
		$bucket = $modelRepo->getActiveBucket();
		if($bucket === false) {
			return false;
		}
		$s3 = jaStorageHelper::getStorage($bucket);

		//
		if (count($paths)) {
			foreach ($paths as $path)
			{
				/*if ($path !== JFile::makeSafe($path)) {
					JError::raiseWarning(100, JText::_('UNABLE_TO_DELETE').htmlspecialchars($path, ENT_COMPAT, 'UTF-8').' '.JText::_('IT_SEEMS_A_SYSTEM_FILES'));
					continue;
				}*/
				$fullPath = jaStorageHelper::cleanPath($folder."/".$path, false);
				if(substr($fullPath, -1) == '/') {
					//is folder
					$retItem = $modelRepo->updateAcl($bucket, $bucket->bucket_name, $acl, $fullPath);
					$ret &= $retItem;
				} else {
					$response = $s3->set_object_acl($bucket->bucket_name, $fullPath, $acl);
					$retItem = $response->status == 200 ? true : false;
					$ret &= $retItem;
				}
				
			}
		}
		if($ret) {
			$msg = JText::_("SUCCESSFULLY_UPDATE_ACL_FOR_SELECTED_ITEMS");
			$msgType = 'message';
		} else {
			$msg = JText::_("UNSUCCESSFULLY_UPDATE_ACL_FOR_SELECTED_ITEMS");
			$msgType = 'error';
		}
		if ($tmpl == 'component') {
			// We are inside the iframe
			$mainframe->redirect('index.php?option='.JACOMPONENT.'&view=repolist&folder='.$folder.'&tmpl=component', $msg, $msgType);
		} else {
			$mainframe->redirect('index.php?option='.JACOMPONENT.'&view=repolist&folder='.$folder, $msg, $msgType);
		}
	}
	/**
	 * Deletes paths from the current path
	 *
	 * @param string $listFolder The image directory to delete a file from
	 * @since 1.5
	 */
	function delete()
	{
		$mainframe = JFactory::getApplication('administrator');

		JRequest::checkToken( 'request' ) or jexit( 'Invalid Token' );

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		// Get some data from the request
		$tmpl	= JRequest::getCmd( 'tmpl' );
		$paths	= JRequest::getVar( 'rm', array(), '', 'array' );
		$folder = JRequest::getVar( 'folder', '');

		// Initialize variables
		$msg = array();
		/**
		 * get s3 object
		 */
		$modelRepo = JModelLegacy::getInstance ( 'repo', 'jaAmazonS3Model' );
		$bucket = $modelRepo->getActiveBucket();
		if($bucket === false) {
			return false;
		}
		$s3 = jaStorageHelper::getStorage($bucket);

		//
		$errorMsg = array();
		if (count($paths)) {
			$db = JFactory::getDBO();
			
			foreach ($paths as $path)
			{
				/*if ($path !== JFile::makeSafe($path)) {
					JError::raiseWarning(100, JText::_('UNABLE_TO_DELETE').htmlspecialchars($path, ENT_COMPAT, 'UTF-8').' '.JText::_('IT_SEEMS_A_SYSTEM_FILES'));
					continue;
				}*/

				$fullPath = jaStorageHelper::cleanPath($folder."/".$path, false);
				
				if(substr($fullPath, -1) == '/') {
					//is folder
					$sqlClean = "UPDATE `#__jaamazons3_file` SET `file_exists` = 0 WHERE `bucket_id` = '{$bucket->id}' AND INSTR(`path`, '{$fullPath}') = 1;";
					$db->setQuery($sqlClean);
					$db->query();
					//
					$retItem = $modelRepo->deleteAdvance($bucket, $fullPath, '');
					if(!$retItem) {

					}
				} else {
					$sqlClean = "UPDATE `#__jaamazons3_file` SET `file_exists` = 0 WHERE `bucket_id` = '{$bucket->id}' AND path = '{$fullPath}';";
					$db->setQuery($sqlClean);
					$db->query();
					//
					$response = $s3->delete_object($bucket->bucket_name, $fullPath);
					$retItem = $response->status == 200 ? true : false;
					if(!$retItem) {
						$errorMsg[] = $response->body->Message;
					}
				}
				
			}
		}

		if(!count($errorMsg)) {
			$msg = JText::_("SUCCESSFULLY_DELETE_FILE");
			$type = 'message';
		} else {
			$type = 'error';
			$msg = implode('<br />', $errorMsg);
		}
		$deleteframe = JRequest::getCmd( 'deleteframe' );
		if($deleteframe){
			$mainframe->redirect('index.php?option='.JACOMPONENT.'&view=repo', $msg, $type);
		}
		if ($tmpl == 'component') {
			// We are inside the iframe
			$mainframe->redirect('index.php?option='.JACOMPONENT.'&view=repolist&folder='.$folder.'&tmpl=component', $msg, $type);
		} else {
			$mainframe->redirect('index.php?option='.JACOMPONENT.'&view=repolist&folder='.$folder, $msg, $type);
		}
	}
	
	function delete_folder()
	{
		$tmpl	= JRequest::getCmd( 'tmpl' );
		if ($tmpl == 'component') {
			// We are inside the iframe
			$mainframe->redirect('index.php?option='.JACOMPONENT.'&view=repolist&folder='.$folder.'&tmpl=component');
		} else {
			$mainframe->redirect('index.php?option='.JACOMPONENT.'&view=repolist&folder='.$folder);
		}
	}

	/**
	 * download
	 * - Use for download file
	 * @param
	 * @return None
	 */
	function download() {
		$mainframe = JFactory::getApplication('administrator');

		JRequest::checkToken( 'request' ) or jexit( 'Invalid Token' );

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		// Get some data from the request
		$tmpl	= JRequest::getCmd( 'tmpl' );
		$paths	= JRequest::getVar( 'rm', array(), '', 'array' );
		$folder = JRequest::getVar( 'folder', '');

		// Initialize variables
		$msg = array();
		$ret = true;
		/**
		 * get s3 object
		 */
		$modelRepo = JModelLegacy::getInstance ( 'repo', 'jaAmazonS3Model' );
		$bucket = $modelRepo->getActiveBucket();
		if($bucket === false) {
			return false;
		}
		$s3 = jaStorageHelper::getStorage($bucket);

		$fullPath = JPath::clean($folder."/".$paths[0], "/");
		//remove first slash
		$file = preg_replace("/^\//", '', $fullPath);
		
		$rs = $s3->get_object($bucket->bucket_name, $file);
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: ".$rs->header['content-type']);
		header("Content-Disposition: attachment; filename=".basename($file).";" );
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".$rs->header['content-length']);
		//print_r($rs);
		echo $rs->body;
		exit();
	}
	
	function multi_enable() {
		$this->multi_update_status('enable');
	}
	
	function multi_disable() {
		$this->multi_update_status('disable');
	}
	
	function multi_update_status($status) {
		$mainframe = JFactory::getApplication('administrator');

		JRequest::checkToken( 'request' ) or jexit( 'Invalid Token' );
		
		$status = ($status == "disable") ? 0 : 1;

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		// Get some data from the request
		$tmpl	= JRequest::getCmd( 'tmpl' );
		$paths	= JRequest::getVar( 'rm', array(), '', 'array' );
		$folder = JRequest::getVar( 'folder', '');

		// Initialize variables
		$msg = array();
		$ret = true;
		
		$model	=& $this->getModel('localrepo');
		$profile = $model->getActiveProfile();
		
		if ($profile !== false) {
			//
			if (count($paths)) {
				$db = JFactory::getDBO();
				foreach ($paths as $path)
				{
					$s3Path = jaStorageHelper::cleanPath($folder.'/'.$path);
					if(!$status) {
						$query = "INSERT IGNORE INTO #__jaamazons3_disabled (`profile_id`, `path`) VALUES ('{$profile->id}', '{$s3Path}')";
					} else {
						$query = "DELETE FROM #__jaamazons3_disabled WHERE `profile_id` = '{$profile->id}' AND `path` = '{$s3Path}'";
					}
					$db->setQuery($query);
					$db->query();
				}
			}
			if(!$status) {
				$msg = JText::_("SUCCESSFULLY_DISABLE_SELECTED_ITEMS");
			} else {
				$msg = JText::_("SUCCESSFULLY_ENABLE_SELECTED_ITEMS");
			}
		} else {
			$msg = JText::_("INVALID_PROFILE");
		}
		if ($tmpl == 'component') {
			// We are inside the iframe
			$mainframe->redirect('index.php?option='.JACOMPONENT.'&view=localrepolist&folder='.$folder.'&tmpl=component', $msg);
		} else {
			$mainframe->redirect('index.php?option='.JACOMPONENT.'&view=localrepolist&folder='.$folder, $msg);
		}
	}
	
	function pull_s3_files() {
		
		$mainframe = JFactory::getApplication('administrator');

		//JRequest::checkToken( 'request' ) or jexit( 'Invalid Token' );

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		// Get some data from the request
		$tmpl	= JRequest::getCmd( 'tmpl' );
		$paths	= JRequest::getVar( 'rm', array(), '', 'array' );
		$folder = JRequest::getVar( 'folder', '');
		$bucket_id = JRequest::getInt( 'bucket_id', 0);
		$profile_id = JRequest::getInt( 'profile_id', 0);
		$uploadToken = JRequest::getVar('jatoken');
		
		if(empty($uploadToken)) {
			jexit("401|".JText::_("INVALID_TOKEN"));
		}
		
		$modelLocalRepo = JModelLegacy::getInstance ( 'localrepo', 'jaAmazonS3Model' );
		
		$uploadStatusFile = $modelLocalRepo->getUploadFile($uploadToken);
		$uploadStopFile = $modelLocalRepo->getUploadFile($uploadToken, "_stop");
		$uploadFinishedFile = $modelLocalRepo->getUploadFile($uploadToken, "_finished");

		// Initialize variables
		$msg = array();
		$ret = true;
		/**
		 * get s3 object
		 */
		$modelRepo = JModelLegacy::getInstance ( 'repo', 'jaAmazonS3Model' );
		$bucket = $modelRepo->getActiveBucket($bucket_id);
		if($bucket === false) {
			jexit("401|".JText::_("BUCKET_IS_INVALID"));
		}
		$profile = $modelLocalRepo->getActiveProfile($profile_id);
		if(!$profile) {
			jexit("401|".JText::_("PLEASE_SELECT_A_PROFILE_FIRST"));
		}
		
		$s3 = jaStorageHelper::getStorage($bucket);

		
		//
		$aFiles = array();
		if (count($paths)) {
			foreach ($paths as $path)
			{
				/*if ($path !== JFile::makeSafe($path)) {
					JError::raiseWarning(100, JText::_('UNABLE_TO_DELETE').htmlspecialchars($path, ENT_COMPAT, 'UTF-8').' '.JText::_('IT_SEEMS_A_SYSTEM_FILES'));
					continue;
				}*/
				$fullPath = jaStorageHelper::cleanPath($folder."/".$path, false);
				if(substr($fullPath, -1) == '/') {
					//is folder
					//$retItem = $modelRepo->updateAcl($bucket, $bucket->bucket_name, $acl, $fullPath);
					//$ret &= $retItem;
					$aSubFiles = $modelRepo->getListServerFiles($bucket, $fullPath);
					if(is_array($aSubFiles)) {
						for ($i=0; $i<count($aSubFiles); $i++) {
							$item = $aSubFiles[$i];
							$aFiles[] = (string) $item->Key;
						}
					}
					//$aFiles = array_merge($aFiles, $aSubFiles);
				} else {
					//$response = $s3->set_object_acl($bucket->bucket_name, $fullPath, $acl);
					//$retItem = $response->status == 200 ? true : false;
					//$ret &= $retItem;
					$aFiles[] = $fullPath;
				}
				
			}
		}
		
		$totalFile = count($aFiles);
		if($totalFile) {
			$basePath = $modelLocalRepo->getProfileLocalPath($profile);
			
			for ($i=0; $i<$totalFile; $i++) {
				$localFile = JPath::clean($basePath.'/'.$aFiles[$i]);
				
				$fileContent = "{$i}|{$totalFile}|[{$aFiles[$i]}]";
				
				if(!JFile::exists($localFile)) {
					$file = preg_replace("/^\//", '', $aFiles[$i]);
					$rs = $s3->get_object($bucket->bucket_name, $file);
					
					if(is_object($rs) && $rs->isOK() && isset($rs->body)) {
						JFile::write($localFile, $rs->body);
					} else {
						$fileContent .= ' [ERROR]';
					}
				}
				
				//update status
				JFile::write($uploadStatusFile, $fileContent);
			}
		}
		
		//create a file to mark as finished
		$fileContent = "_FINISHED_";
		JFile::write($uploadFinishedFile, $fileContent);
		if($ret) {
			$msg = JText::_("SUCCESSFULLY_PULL_SELECTED_ITEMS_TO_LOCAL");
			$msgType = 'message';
			jexit("200|".$msg);
		} else {
			$msg = JText::_("UNSUCCESSFULLY_PULL_SELECTED_ITEMS_TO_LOCAL");
			$msgType = 'error';
			jexit("400|".$msg);
		}
		
	}
	
	public function upload()
	{
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		$params = JComponentHelper::getParams('com_media');
		// Get some data from the request
		$files			= JRequest::getVar('Filedata', '', 'files', 'array');
		$return			= JRequest::getVar('return-url', null, 'post', 'base64');
		$this->folder	= JRequest::getVar('folder', '', '', 'path');

		// Set the redirect
		if ($return)
		{
			$this->setRedirect(base64_decode($return) . '&folder=' . $this->folder);
		}

		// Authorize the user
		if (!$this->authoriseUser('create'))
		{
			return false;
		}
		if (
			$_SERVER['CONTENT_LENGTH']>($params->get('upload_maxsize', 0) * 1024 * 1024) ||
			$_SERVER['CONTENT_LENGTH']>(int)(ini_get('upload_max_filesize'))* 1024 * 1024 ||
			$_SERVER['CONTENT_LENGTH']>(int)(ini_get('post_max_size'))* 1024 * 1024 ||
			$_SERVER['CONTENT_LENGTH']>(int)(ini_get('memory_limit'))* 1024 * 1024
		)
		{
			JError::raiseWarning(100, JText::_('JA_ERROR_WARNFILETOOLARGE'));
			return false;
		}

		// Input is in the form of an associative array containing numerically indexed arrays
		// We want a numerically indexed array containing associative arrays
		// Cast each item as array in case the Filedata parameter was not sent as such
		$files = array_map( array($this, 'reformatFilesArray'),
			(array) $files['name'], (array) $files['type'], (array) $files['tmp_name'], (array) $files['error'], (array) $files['size']
		);

		// Perform basic checks on file info before attempting anything
		foreach ($files as &$file)
		{
			if ($file['error']==1)
			{
				JError::raiseWarning(100, JText::_('JA_ERROR_WARNFILETOOLARGE'));
				return false;
			}
			if ($file['size']>($params->get('upload_maxsize', 0) * 1024 * 1024))
			{
				JError::raiseNotice(100, JText::_('JA_ERROR_WARNFILETOOLARGE'));
				return false;
			}
			
			/*if (JFile::exists($file['filepath']))
			{
				// A file with this name already exists
				JError::raiseWarning(100, JText::_('JA_ERROR_FILE_EXISTS'));
				return false;
			}*/

			if (!isset($file['name']))
			{
				// No filename (after the name was cleaned by JFile::makeSafe)
				$this->setRedirect('index.php', JText::_('JA_INVALID_REQUEST'), 'error');
				return false;
			}
		}
		
		$modelRepo = JModelLegacy::getInstance ( 'repo', 'jaAmazonS3Model' );
		$bucket = $modelRepo->getActiveBucket();
		$s3 = jaStorageHelper::getStorage($bucket);
		$db = JFactory::getDbo();
		
		$httpHeaders = array(
					'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT',
					'Cache-Control' => 'no-cache, must-revalidate'
				);
		$uploadTimeLog = date('Y-m-d H:i:s');
		
		foreach ($files as &$file)
		{
			$checksum = $checksumOriginal = md5_file($file['tmp_name']);
			$content = file_get_contents($file['tmp_name']);
			$s3File = JPath::clean($this->folder.'/'.$file['name'], '/');
			$s3File = preg_replace('/^\/+/', '', $s3File);
			$opts = array(
				'filename' => $s3File,
				'body' => $content,
				'contentType' => ja_get_mime_content_type($file['name']),
				'acl' => AmazonS3::ACL_PUBLIC,
				'headers' => $httpHeaders
			);
			$upResult = $s3->create_object($bucket->bucket_name, $s3File, $opts);
			
			//update db
			$sql = "
						INSERT INTO `#__jaamazons3_file` 
						SET 
							bucket_id = '{$bucket->id}', 
							path = '{$s3File}', 
							last_update = '{$uploadTimeLog}', 
							file_checksum = '{$checksum}', 
							file_original_checksum = '{$checksumOriginal}', 
							`file_exists` = 1 
						ON DUPLICATE KEY UPDATE 
							last_update = '{$uploadTimeLog}', 
							file_checksum = '{$checksum}', 
							file_original_checksum = '{$checksumOriginal}', 
							`file_exists` = 1;" . "\r\n";
			$db->setQuery($sql);
			$db->query();
			
			//
			@unlink($file['tmp_name']);
		}

		return $upResult;
	}
	
	/**
	 * Used as a callback for array_map, turns the multi-file input array into a sensible array of files
	 * Also, removes illegal characters from the 'name' and sets a 'filepath' as the final destination of the file
	 *
	 * @param	string	- file name			($files['name'])
	 * @param	string	- file type			($files['type'])
	 * @param	string	- temporary name	($files['tmp_name'])
	 * @param	string	- error info		($files['error'])
	 * @param	string	- file size			($files['size'])
	 *
	 * @return	array
	 * @access	protected
	 */
	protected function reformatFilesArray($name, $type, $tmp_name, $error, $size)
	{
		$name = JFile::makeSafe($name);
		return array(
			'name'		=> $name,
			'type'		=> $type,
			'tmp_name'	=> $tmp_name,
			'error'		=> $error,
			'size'		=> $size
		);
	}
	
	/**
	 * Check that the user is authorized to perform this action
	 *
	 * @param   string   $action - the action to be peformed (create or delete)
	 *
	 * @return  boolean
	 * @access  protected
	 */
	protected function authoriseUser($action)
	{
		if (!JFactory::getUser()->authorise('core.' . strtolower($action), 'com_media'))
		{
			// User is not authorised
			JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_' . strtoupper($action) . '_NOT_PERMITTED'));
			return false;
		}

		return true;
	}
}

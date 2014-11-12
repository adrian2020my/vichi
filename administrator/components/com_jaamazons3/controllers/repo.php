<?php
/**
 * @version		$Id: controller.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla
 * @subpackage	Media
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.controller' );

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.path');

/**
 * Media Manager Component Controller
 *
 * @package		Joomla
 * @subpackage	Media
 * @version 1.5
 */
class jaAmazonS3ControllerRepo extends JControllerLegacy
{
	function __construct($default = array()) {

		parent::__construct ( $default );
		
		JRequest::setVar ( 'view', 'repo' );
		$this->registerTask ( 'update_list_s3_files', 'updateListS3Files' );
	}
	/**
	 * Display the view
	 */
	function display($cachable = false, $urlparams = false)
	{
		$mainframe = JFactory::getApplication('administrator');

		$vName = JRequest::getCmd('view', 'media');
		
		$this->_checkUserState();
		switch ($vName)
		{
			case 'images':
				$vLayout = JRequest::getCmd( 'layout', 'default' );
				$mName = 'manager';

				break;

			case 'imagesList':
				$mName = 'list';
				$vLayout = JRequest::getCmd( 'layout', 'default' );

				break;

			case 'repolist':
				$mName = 'repolist';
				//$vLayout = $mainframe->getUserStateFromRequest('media.list.layout', 'layout', 'details', 'word');
				$vLayout = "details";

				break;

			case 'media':
			default:
				$vName = 'repo';
				$vLayout = JRequest::getCmd( 'layout', 'default' );
				$mName = 'repo';
				break;
		}

		$document = JFactory::getDocument();
		$vType	= $document->getType();

		// Get/Create the view
		$view = $this->getView( $vName, $vType);

		// Get/Create the model
		if ($model = $this->getModel($mName)) {
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		// Set the layout
		$view->setLayout($vLayout);

		// Display the view
		$view->display();
	}

	function ftpValidate()
	{
		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
	}
	
	function _checkUserState() {
		$model	= $this->getModel('repo');
		$lists = $model->_getVars_admin ();
		if(!$lists['bucket_id']) {
			JError::raiseNotice(100, JText::_("PLEASE_SELECT_A_BUCKET_FIRST"));
			return false;
		}
		return true;
	}
	
	function delete_advance() {
		$mainframe = JFactory::getApplication('administrator');
		
		$model	=& $this->getModel('repo');
		$bucket = $model->getActiveBucket();
		
		$pcre = JRequest::getVar('pcre', '\.php$');
		$folder = JRequest::getVar('upload_folder', '');
		
		if($bucket !== false && !empty($pcre)) {
			$result = $model->deleteAdvance($bucket, $folder, $pcre);
		} else {
			$mainframe->redirect("index.php?option=".JACOMPONENT."&view=repo", JText::_("PLEASE_SELECT_A_BUCKET_FIRST"), 'error');
		}
		
		JRequest::setVar('folder', $folder);
		//die('test');
		if($result){
			JError::raiseNotice(200, JText::_("SUCCESSFULLY_DELETE_FILE"));
		}else{
		   JError::raiseNotice(200, JText::sprintf("NOT_FIND_SUCCESSFULLY_DELETE_FILE",$pcre));
		}
		$this->display();
	}
	
	function create_folder() {
		$mainframe = JFactory::getApplication('administrator');
		
		$model	=& $this->getModel('repo');
		$bucket = $model->getActiveBucket();
		
		$replace = JRequest::getInt('replace_file', 0);
		$folder = JRequest::getVar('upload_folder', '');
		$new_folder = JRequest::getVar('new_folder', '');
		
		$folder = jaStorageHelper::cleanPath($folder);
		$current = $folder;
		if(!empty($current)) {
			$current .= "/";
		}
		
		if(!empty($new_folder)) {
			if($bucket !== false) {
				$s3 = jaStorageHelper::getStorage($bucket);
				
				//check if folder is already exists
				$result = $s3->list_objects($bucket->bucket_name, array("prefix" => $current, "delimiter" => "/", "max-keys" => 5000));
		
				$folders = array();
				
				$existed = 0;
				$checkPath = $current . $new_folder;
				if(isset($result->body->CommonPrefixes)) {
					foreach ($result->body->CommonPrefixes as $item) {
						$subFolder = $item->Prefix;
						// remove last slashes
						$subFolder = preg_replace("/\/*$/", "", $subFolder);
						if($checkPath == $subFolder) {
							$existed = 1;
							break;
						}
					}
				}
				
				if($existed) {
					//JError::raiseNotice(200, JText::_("CAN_NOT_CREATE_A_FOLDER_WHEN_THAT_FOLDER_ALREADY_EXISTS"));
					$mainframe->redirect("index.php?option=".JACOMPONENT."&view=repo", JText::_("CAN_NOT_CREATE_A_FOLDER_WHEN_THAT_FOLDER_ALREADY_EXISTS"), 'error');
				} else {
					$filename = $folder .'/'. $new_folder . '/index.html';
					$filename = jaStorageHelper::cleanPath($filename);
					
					
					$secureFile = dirname(__FILE__).'/index.html';
					$content = file_get_contents($secureFile);
					$opts = array(
						'filename' => $filename,
						'body' => $content,
						'contentType' => ja_get_mime_content_type($secureFile),
						'acl' => AmazonS3::ACL_PUBLIC
					);
					$result = $s3->create_object($bucket->bucket_name, $filename, $opts);
					if($result === false) {
						//JError::raiseWarning(100, JText::_("ERROR_OCCURRED_UNABLE_TO_CREATE_FOLDER"));
						$mainframe->redirect("index.php?option=".JACOMPONENT."&view=repo", JText::_("ERROR_OCCURRED_UNABLE_TO_CREATE_FOLDER"), 'error');
					} else {
						//JError::raiseNotice(200, JText::_("SUCCESSFULLY_CREATE_FOLDER"));
						$mainframe->redirect("index.php?option=".JACOMPONENT."&view=repo", JText::_("SUCCESSFULLY_CREATE_FOLDER"));
					}
				}
			} else {
				//JError::raiseWarning(100, JText::_("PLEASE_SELECT_A_BUCKET_FIRST"));
				$mainframe->redirect("index.php?option=".JACOMPONENT."&view=repo", JText::_("PLEASE_SELECT_A_BUCKET_FIRST"), 'error');
			}
		}
		
		JRequest::setVar('folder', $folder);
		//die('test');
		$this->display();
	}
	
	function updateListS3Files() {
		$folder = JRequest::getVar('upload_folder', '');
		$folder = jaStorageHelper::cleanPath($folder);
		$bucket_id = JRequest::getInt('bid', 0);
		
		$model	=& $this->getModel('repo');
		$bucket = $model->getActiveBucket($bucket_id);
		
		
		//$folder = '';//get all files of bucket
		
		if($bucket !== false) {
			$db = JFactory::getDBO();
			$serverFiles = $model->getListServerFiles($bucket, $folder);
			/**
			 * CLEAN LOG:
			 * since some s3 files can be deleted by other s3 client (Eg: s3fox, ...) or by some reason.
			 * So, We must clear log database and get list s3 files again 
			 * to correct replacement of s3 plugin
			 */
			$sqlClean = "UPDATE `#__jaamazons3_file` SET `file_exists` = 0 WHERE bucket_id = '{$bucket->id}' AND file_checksum <> ''";
			if(!empty($folder)) {
				$updatePath = $folder . '/';
				$sqlClean .= "  AND INSTR(`path`, '{$updatePath}') = 1";
			}
			$db->setQuery($sqlClean);
			$db->query();
			
			//
			$sqlLog = array();
			if(is_array($serverFiles) && count($serverFiles)) {
				foreach ($serverFiles as $sfile) {
					$filename = $sfile->Key;
					$uploadTimeLog = date('Y-m-d H:i:s', strtotime($sfile->LastModified));
					$checksum = str_replace('"', '', $sfile->ETag);
					$sqlLog[] = "INSERT INTO `#__jaamazons3_file` SET bucket_id = '{$bucket->id}', path = '{$filename}', last_update = '{$uploadTimeLog}', file_checksum = '{$checksum}', `file_exists` = 1 ON DUPLICATE KEY UPDATE last_update = '{$uploadTimeLog}', file_checksum = '{$checksum}', `file_exists` = 1;" . "\r\n";
				}
			}
				
			if(count($sqlLog)) {
				foreach ($sqlLog as $sql) {
					$db->setQuery($sql);
					$db->query();
				}
			}
			//log update time
			$sqlSyncLog = "UPDATE `#__jaamazons3_bucket` SET `last_sync` = '".date('Y-m-d H:i:s')."' WHERE id = '{$bucket->id}'";
			$db->setQuery($sqlSyncLog);
			$db->query();
			//
			echo JText::_("SUCCESSFULLY_UPDATE_LIST_FILES_ON_S3_TO_DATABASE");
		} else {
			echo JText::_("INVALID_BUCKET");
		}
		exit();
	}
}

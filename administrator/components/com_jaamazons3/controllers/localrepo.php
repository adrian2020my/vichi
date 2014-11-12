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
class jaAmazonS3ControllerLocalrepo extends JControllerLegacy
{
	var $smushit_allow_extensions = array('jpg', 'jpeg', 'png');
	var $is_cronjob = 0;
	var $flag_cron_checking = "jaamazons3_upload_cron_running";
	var $upload_cron_profile = '';
	
	function __construct($default = array()) {

		parent::__construct ( $default );
		
		JRequest::setVar ( 'view', 'localrepo' );
		
		$this->registerTask('showprogressbar', 'showProgressBar');
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

			case 'localrepolist':
				$mName = 'localrepolist';
				//$vLayout = $mainframe->getUserStateFromRequest('media.list.layout', 'layout', 'details', 'word');
				$vLayout = "details";

				break;

			case 'media':
			default:
				$vName = 'localrepo';
				$vLayout = JRequest::getCmd( 'layout', 'default' );
				$mName = 'localrepo';
				break;
		}

		$document = &JFactory::getDocument();
		$vType		= $document->getType();

		// Get/Create the view
		$view = &$this->getView( $vName, $vType);

		// Get/Create the model
		if ($model = &$this->getModel($mName)) {
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
		$model	=& $this->getModel('localrepo');
		$lists = $model->_getVars_admin ();
		if(!$lists['profile_id']) {
			JError::raiseNotice(100, JText::_("PLEASE_SELECT_A_PROFILE_FIRST"));
			return false;
		}
		return true;
	}
	
	function enable() {
		$model	=& $this->getModel('localrepo');
		$name = JRequest::getVar('name');
		$path = JRequest::getVar('path');
		$profile = $model->getActiveProfile();
		if ($profile !== false) {
			$item = new stdClass();
			$item->name = $name;
			$item->path_relative = $path;
			$item->status = 'enabled';
			
			echo $model->setItemEnable($profile, $item);
			//update status of row
			$itemId = preg_replace("/[\$\.]/", '_', $item->name);
			echo '
			<script type="text/javascript">
			jQuery("#local-item-'.$itemId.'").removeClass("item-disabled");
			</script>
			';
		} else {
			echo JText::_("INVALID_PROFILE");
		}
		exit();
	}
	
	function disable() {
		$model	=& $this->getModel('localrepo');
		$name = JRequest::getVar('name');
		$path = JRequest::getVar('path');
		$profile = $model->getActiveProfile();
		if ($profile !== false) {
			$item = new stdClass();
			$item->name = $name;
			$item->path_relative = $path;
			$item->status = 'disabled';
			
			echo $model->setItemDisable($profile, $item);
			//update status of row
			$itemId = preg_replace("/[\$\.]/", '_', $item->name);
			echo '
			<script type="text/javascript">
			jQuery("#local-item-'.$itemId.'").addClass("item-disabled");
			</script>
			';
		} else {
			echo JText::_("INVALID_PROFILE");
		}
		exit();
		
	}
	
	function upload() {
		$model	=& $this->getModel('localrepo');
		$profile = $model->getActiveProfile();
		
		$replace = JRequest::getInt('replace_file', 0);
		$folder = JRequest::getVar('upload_folder', '');
		$folder = jaStorageHelper::cleanPath($folder);
		/**
		 * Base path
		 * Check it exists
		 * */
		$basePath = $model->getProfileLocalPath($profile);
		$uploadFilesPath = JPath::clean($basePath.'/'.$folder);
			
		$getfiles 	= JRequest::getVar('files',array());
		$uploadToken = JRequest::getVar('jatoken');
		$files = array();
		if(is_array($getfiles) && count($getfiles)>0){
			foreach ($getfiles as $f){
				if(file_exists($uploadFilesPath.'/'.$f)){
					array_push($files, $uploadFilesPath.'/'.$f);
				}
			}
		}
		//print_r($files);exit();
		//$getfiles = explode($files, $string)
		/**
		 * If replace file, replace only updated files
		 */
		if($replace == 1) $replace = 2;
		
		$this->_upload($uploadToken, $profile, $folder, $replace,$files);
	}
	
	/**
	 * _upload
	 *
	 * @param (string) $uploadToken
	 * @param (object) $profile
	 * @param (string) $folder - relative path with config path on profile
	 * @param (boolean) $replace
	 * 						+ 0: dont upload if file existed
	 * 						+ 1: replace file if existed
	 * 						+ 2: replace file if updated
	 *                   	+ 3: upload and delete local file                                      
	 * 
	 * @desc: code to test for any break point
	 * <code>
	 * echo "400|test value";
	 * exit();
	 * </code>
	 */
	function _upload($uploadToken, $profile, $folder='', $replace=2,$files = array()) {
		$mainframe = JFactory::getApplication('administrator');
		$db = JFactory::getDBO();
		$tmpPath = JPath::clean($mainframe->getCfg( 'tmp_path' ) . DS);
		
		$smushit = new jaSmushItHelper();
		
		if(empty($uploadToken)) {
			//JError::raiseWarning(100, JText::_("INVALID_TOKEN"));
			echo "401|".JText::_("INVALID_TOKEN");
			exit();
		}
		
		//check if is cron upload
		$this->is_cronjob = (strpos($uploadToken, 'jaamazons3_upload_cron') === 0) ? 1 : 0;
						
		$uploadStatusFile = $this->_getUploadFile($uploadToken);
		$uploadStopFile = $this->_getUploadFile($uploadToken, "_stop");
		$uploadFinishedFile = $this->_getUploadFile($uploadToken, "_finished");
		/*sleep(10);
		echo "401|".JText::_("TEST");
		exit();*/
			
		$model	=& $this->getModel('localrepo');
		
		if($profile !== false ) {
			//http header
			//cache control
			$cache_lifetime = (int) (isset($profile->cache_lifetime) ? $profile->cache_lifetime : 0);
			
			if(!$cache_lifetime) {
				//no-cache
				$httpHeaders = array(
					'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT',
					'Cache-Control' => 'no-cache, must-revalidate'
				);
			} else {
				$httpHeaders = array(
					//'Expires' => gmdate( 'D, d M Y H:i:s', time() + $cache_lifetime ) . ' GMT',
					'Cache-Control' => "max-age={$cache_lifetime}, must-revalidate"
				);
			}
			//
			$this->upload_cron_profile = $profile->id;
			$uploadCronChecking = $this->_getUploadFile($this->flag_cron_checking, $this->upload_cron_profile);
			if($this->is_cronjob) {
				if(JFile::exists($uploadCronChecking)) {
					//if this file is exists
					//other cron job will not be run until current cron job is completed
					$logTime = JFile::read($uploadCronChecking);
					$logTime = strtotime($logTime);
					$currTime = time();
					
					$interval = ((intval($profile->cron_day) * 24 * 60) + (intval($profile->cron_hour) * 60) + $profile->cron_minute) * 60;
					if($interval == 0) {
						// minimum is 10 minute
						$interval = 600;
					}
					
					if($logTime + $interval > $currTime) {
						//must check time because in some case
						//uploading was completed but $uploadCronChecking file was not deleted successfully
						//and the next "upload cron job" will never be run
						echo "400|".JText::_("An other cron job is running. Please try again later.");
						exit();
					}
				}
				$fileContent = date('Y-m-d H:i:s');
				JFile::write($uploadCronChecking, $fileContent);
			}
			
			//
			$s3 = jaStorageHelper::getStorage($profile);
			
			$basePath = $model->getProfileLocalPath($profile);
			$uploadPath = JPath::clean($basePath.'/'.$folder);
			
			//path is specific file?
			$uploadFile = JPath::clean($basePath.'/'.$folder);
			
			if(JFolder::exists($uploadPath) || JFile::exists($uploadFile)) {
				
				/**
				 * filter by allowed extensions
				 */
				$exts = $profile->allowed_extension;
				//die("400|".$exts);
				//$upload_disabled_item = (int) $params->get('upload_disabled_item', 0);
				$upload_disabled_item = 0;
				
				if($upload_disabled_item) {
					$aExclude = array();
				} else {
					$aExclude = $model->getListItemDisabled($profile);
					if(count($aExclude)) {
						foreach ($aExclude as $itemid => $item) {
							$aExclude[$itemid] = JPath::clean($basePath.'/'.$item);
						}
					}
					
					/*
					redure exclude list
					if user upload a sub folder inside disable folders
					we still allow to upload it, only sub disabled items of this folder will be remove
					Eg: folder "/root/images/" was disabled
					when user select to upload "/root/" folder, "images" folder will be removed from upload list
					but if user select to upload folder "/root/images/banners/", the "banners" folder still allow to able upload
					
					If you see it is not reasonable,
					pls comment below code
					*/
					if(count($aExclude)) {
						$aExcludeTmp = array();
						foreach ($aExclude as $disabledItem) {
							if(strpos($disabledItem, $uploadPath) === 0) {
								$aExcludeTmp[] = $disabledItem;
							}
						}
						$aExclude = $aExcludeTmp;
					}
					
				}
				
				$this->_checkUploadStopped($uploadStopFile, 0);
				/*echo "400|".print_r($aExclude, true);
				exit();*/
				
				$aExts = explode(",", $exts);
				if(count($aExts) >= 1) {
					$patterns = "\.(?:".implode("|", $aExts).")$";
						
					/**
					 * If exits
					 * Upload all file in folder selected
					 */
					if(empty($files)){
						$files = array();
						$folders = array();
						$isUploadFile = false;
						if(JFolder::exists($uploadPath)) {
							if(in_array($uploadPath, $aExclude)) {
								echo "400|".JText::_("THIS_FOLDER_WAS_DISABLED_FOR_UPLOAD_BUT_YOU_CAN_CHANGE_YOUR_CONFIGURATION_TO_ABLE_UPLOAD_DISABLED_ITEM");
								exit();
							}
							
							$files = JAFileHelpers::files($uploadPath, $patterns, true, true, $aExclude);
							//folders
							$folders = JAFileHelpers::folders($uploadPath, '.', true, true, $aExclude);
							$folders[] = $uploadPath;
							
							//echo "400|".print_r($folders, true); exit();
						} elseif(JFile::exists($uploadFile)) {
							$isUploadFile = true;
							if(in_array($uploadFile, $aExclude)) {
								echo "400|".JText::_("THIS_FILE_WAS_DISABLED_FOR_UPLOAD_BUT_YOU_CAN_CHANGE_YOUR_CONFIGURATION_TO_ABLE_UPLOAD_DISABLED_ITEM");
								exit();
							}
							if(preg_match("/{$patterns}/i", $uploadFile)) {
								$files[] = $uploadFile;
							}
						}
					} else {
						//UPLOAD SELECTED ITEMS
						$folders = array();
						$isUploadFile = false;
						if(JFolder::exists($uploadPath)) {
							if(in_array($uploadPath, $aExclude)) {
								echo "400|".JText::_("THIS_FOLDER_WAS_DISABLED_FOR_UPLOAD_BUT_YOU_CAN_CHANGE_YOUR_CONFIGURATION_TO_ABLE_UPLOAD_DISABLED_ITEM");
								exit();
							}
							
							//$files = JAFileHelpers::files($uploadPath, $patterns, true, true, $aExclude);
							//folders
							$folders = JAFileHelpers::folders($uploadPath, '.', true, true, $aExclude);
							$folders[] = $uploadPath;
							$files2 = array();
							if(count($files)) {
								foreach ($files as $file) {
									if(JFolder::exists($file)) {
										$files3 = JAFileHelpers::files($file, $patterns, true, true, $aExclude);
										$files2 = array_merge($files2, $files3);
									} else {
										$files2[] = $file;
									}
								}
							}
							$files = $files2;
							
							//echo "400|".print_r($files, true); exit();
						} elseif(JFile::exists($uploadFile)) {
							$isUploadFile = true;
							if(in_array($uploadFile, $aExclude)) {
								echo "400|".JText::_("THIS_FILE_WAS_DISABLED_FOR_UPLOAD_BUT_YOU_CAN_CHANGE_YOUR_CONFIGURATION_TO_ABLE_UPLOAD_DISABLED_ITEM");
								exit();
							}
							//if(preg_match("/{$patterns}/i", $uploadFile)) {
							//	$files[] = $uploadFile;
							//}
						}
					
					}
					/**
					 * If not replace or replace only update file
					 */
					if((!$replace || $replace == 0 || $replace == 2) && count($files)) {
						if($isUploadFile === true) {
							//upload only one file
							$s3File = substr($uploadFile, strlen($basePath));
							$s3File = JPath::clean($s3File, '/');
							if($s3->if_object_exists($profile->bucket_name, $s3File)) {
								$files = array();
							}
						} else {
							/**
							 * UPLOAD TUNING (thanhnv)
							 * if user choose upload files without replace existing files on server
							 * we dont check file exist for each file upload,
							 * we get list of all files on server, and compare with local to remove files are existed
							 * then we upload only non-exists files,
							 * so we can save more time and efforts to check files exists :)
							 */
							$serverFiles = $model->getListServerFiles($profile, $folder);
							
							//
							$this->_checkUploadStopped($uploadStopFile, 0);
							//
							$serverFiles2 = array();
							if(is_array($serverFiles) && count($serverFiles)) {
								foreach ($serverFiles as $sfile) {
									$localPath = JPath::clean($basePath . $sfile->Key);
									
									/*echo "400|".$sfile->ETag."|".md5_file($localPath);
									exit();*/
									if(!$replace) {
										//add to exception list
										$serverFiles2[] = $localPath;
									} elseif (is_file($localPath)) {
										$md5OrgFile = md5_file($localPath);
										if(($sfile->ETag == $md5OrgFile) || ($sfile->ETag == '"'.$md5OrgFile.'"')) {
											// file was not modified
											$serverFiles2[] = $localPath;
										} else {
											// check if file on storage server was compressed
											$fileExt = strtolower(JFile::getExt($localPath));
											
											if(in_array($fileExt, $this->smushit_allow_extensions)) {
												// check if file on storage server is smushed (by yahoo smushit)
												$sqlCheck = "
													SELECT file_checksum, file_original_checksum
													FROM `#__jaamazons3_file`
													WHERE bucket_id = '{$profile->bucket_id}'
													AND `file_exists` = 1
													AND `path` = " . $db->Quote($sfile->Key);
												
												$db->setQuery($sqlCheck);
												$rowCheck = $db->loadObject();
												if(is_object($rowCheck)) {
													/*echo "400|{$rowCheck->file_original_checksum}_{$md5OrgFile}";
													exit();*/
													if(($rowCheck->file_original_checksum == $md5OrgFile) || ($rowCheck->file_original_checksum == '"'.$md5OrgFile.'"')) {
														$serverFiles2[] = $localPath;
													}
												}
											}
										}
									}
								}
							}
							//test for list of existing files on server
							//echo "400|".count($serverFiles2); exit();
							/*echo "400|".print_r($serverFiles2, true);
							exit();*/
							
							$this->_checkUploadStopped($uploadStopFile, 0);
							// remove existed files on upload list
							$filesQueue = array();
							foreach ($files as $file) {
								if(!in_array($file, $serverFiles2)) {
									$filesQueue[] = $file;
								}
							}
							$files = $filesQueue;
							
						}
					}
					
					
					$this->_checkUploadStopped($uploadStopFile, 0);
					//test for list of upload files
					/*echo "400|".print_r($files, true);
					exit();*/
					
					$totalFile = count($files);
					
					$cnt = 0;
					$uploadTimeLog = date('Y-m-d H:i:s');
					$sqlLog = array();
					
					//log folder upload time
					if(count($folders)) {
						foreach ($folders as $fld) {
							$fld = substr($fld, strlen($basePath));
							$fld = jaStorageHelper::cleanPath($fld);
							$sqlLog[] = "INSERT INTO `#__jaamazons3_file` SET bucket_id = '{$profile->bucket_id}', path = '{$fld}', last_update = '{$uploadTimeLog}', file_checksum = '', `file_exists` = 1 ON DUPLICATE KEY UPDATE last_update = '{$uploadTimeLog}', file_checksum = '', `file_exists` = 1;" . "\r\n";
						}
					}
					//upload file and log time
					if($totalFile) {
						foreach ($files as $file) {
							//check to stop
							$this->_checkUploadStopped($uploadStopFile, $cnt);
							
							//update status
							$fileContent = "{$cnt}|{$totalFile}|[{$file}]";
							JFile::write($uploadStatusFile, $fileContent);
							
							/**
							 * filename - _string_ (Required) The filename for the object.
							 * 	body - _string_ (Required) The data to be stored in the object.
							 * 	contentType - _string_ (Required) The type of content that is being sent in the body.
							 * 	acl - _string_ (Optional) One of the following options: <AmazonS3::ACL_PRIVATE>, <AmazonS3::ACL_PUBLIC>, <AmazonS3::ACL_OPEN>, or <AmazonS3::ACL_AUTH_READ>. Defaults to <AmazonS3::ACL_PRIVATE>.
							 * 	headers - _array_ (Optional) Standard HTTP headers to send along in the request.
							 */
							$filename = substr($file, strlen($basePath));
							$filename = jaStorageHelper::cleanPath($filename);
							
							//smushit
							$smushed = 0;
							if($profile->use_smushit) {
								$fileExt = JFile::getExt($file);
								if(in_array($fileExt, $this->smushit_allow_extensions)) {
									$sResult = $smushit->smush($file);
									if(!$sResult) {
										/*echo "400|".$smushit->getError();
										exit();*/
									} else {
										$sResult = $smushit->getData();
										//create a temporary file to store compressed file
										$basename = JFile::stripExt(basename($file)) .'_'. md5($file).'.'.JFile::getExt($file);//[name]_[sand].[ext]
										$tmpFile = $tmpPath.$basename;
										/*echo "400|".$tmpFile;
										exit();*/
										$fileContent = file_get_contents($sResult->dest);
										$createdTmpFile = JFile::write($tmpFile, $fileContent);
										if($createdTmpFile) {
											
											if(JFile::exists($tmpFile)) {
												// successfully compressed file
												$smushed = 1;
												
												$checksum = md5_file($tmpFile);
												$checksumOriginal = md5_file($file);
												
												$sqlLog[] = "
													INSERT INTO `#__jaamazons3_file` 
													SET 
														bucket_id = '{$profile->bucket_id}', 
														path = '{$filename}', 
														last_update = '{$uploadTimeLog}', 
														file_checksum = '{$checksum}', 
														file_original_checksum = '{$checksumOriginal}', 
														`file_exists` = 1 
													ON DUPLICATE KEY UPDATE 
														last_update = '{$uploadTimeLog}', 
														file_checksum = '{$checksum}', 
														file_original_checksum = '{$checksumOriginal}', 
														`file_exists` = 1;" . "\r\n";
												
												$content = file_get_contents($tmpFile);
												$opts = array(
													'filename' => $filename,
													'body' => $content,
													'contentType' => ja_get_mime_content_type($file),
													'acl' => AmazonS3::ACL_PUBLIC,
													'headers' => $httpHeaders
												);
												$upResult = $s3->create_object($profile->bucket_name, $filename, $opts);
												
												if($upResult) {
													JFile::delete($tmpFile);
												}
											}
										}
									}
								}
							}
							//
							
							if(!$smushed) {
								$checksum = md5_file($file);
								$sqlLog[] = "
									INSERT INTO `#__jaamazons3_file` 
									SET 
										bucket_id = '{$profile->bucket_id}', 
										path = '{$filename}', 
										last_update = '{$uploadTimeLog}', 
										file_checksum = '{$checksum}', 
										file_original_checksum = '', 
										`file_exists` = 1 
									ON DUPLICATE KEY UPDATE 
										last_update = '{$uploadTimeLog}', 
										file_checksum = '{$checksum}', 
										file_original_checksum = '', 
										`file_exists` = 1;" . "\r\n";
								
								//multi part upload for large size file (> 10MB (10485760B))
								$fsize = filesize($file);
								if($fsize > 10485760) {
									$opts = array(
									    'fileUpload' => $file,
										'contentType' => ja_get_mime_content_type($file),
									
									    // Optional configuration
									    'partSize' => 10485760, // 10MB
									    'acl' => AmazonS3::ACL_PUBLIC
									);
									/**
									 * 'storage' => AmazonS3::STORAGE_REDUCED,
									
									    // Object metadata.
									    'meta' => array(
									        'param1' => 'value1',
									        'param2' => 'value2',
									     )
									 */
									$upResult = $s3->create_mpu_object($profile->bucket_name, $filename, $opts);
								} else {
									
									$content = file_get_contents($file);
									$opts = array(
										'filename' => $filename,
										'body' => $content,
										'contentType' => ja_get_mime_content_type($file),
										'acl' => AmazonS3::ACL_PUBLIC,
										'headers' => $httpHeaders
									);
									$upResult = $s3->create_object($profile->bucket_name, $filename, $opts);
								}
							}
							
							//Remove file
							if($replace == 3) {
								if(is_object($upResult) && $upResult->isOK()) {
									//delete local file after upload
									JFile::delete($file);
								}
							}
							//
							$cnt++;
						}
						
					}
					
					//store log to db
					if(count($sqlLog)) {
						//display step on progress bar
						$fileContent = "{$totalFile}|{$totalFile}|".JText::_("UPDATING_LIST_FILE_INTO_DATABASE");
						JFile::write($uploadStatusFile, $fileContent);
						//
						foreach ($sqlLog as $sql) {
							$db->setQuery($sql);
							$db->query();
						}
					}
					
					echo "200|{$totalFile}";
					//create a file to mark as finished
					$fileContent = "_FINISHED_";
					JFile::write($uploadFinishedFile, $fileContent);
				} else {
					//JError::raiseWarning(100, JText::_("DO_NOT_HAVE_ANY_EXTENSIONS_IS_ALLOWED_PLEASE_CHECK_YOUR_CONFIGURATION_AGAIN"));
					echo "400|".JText::_("DO_NOT_HAVE_ANY_EXTENSIONS_IS_ALLOWED_PLEASE_CHECK_YOUR_CONFIGURATION_AGAIN");
				}
				
			} else {
				//JError::raiseWarning(100, JText::_("SITE_PATH_IS_NOT_CORRECTED_PLEASE_CHECK_YOUR_PROFILE_CONFIGURATION_AGAIN"));
				echo "400|".JText::_("SITE_PATH_IS_NOT_CORRECTED_PLEASE_CHECK_YOUR_PROFILE_CONFIGURATION_AGAIN")."<br/>".$uploadPath;
			}
		} else {
			//JError::raiseWarning(100, JText::_("PLEASE_SELECT_A_PROFILE_FIRST"));
			echo "400|".JText::_("PLEASE_SELECT_A_PROFILE_FIRST");
		}
		
		if(isset($uploadCronChecking)) {
			JFile::delete($uploadCronChecking);
		}
		
		if(!$this->is_cronjob) {
			exit();
		}
		//JRequest::setVar('folder', $folder);
		//die('test');
		//$this->display();
	}
	
	/**
	 * get upload status and display as progress bar
	 *
	 */
	function uploadbar() {
		$uploadToken = JRequest::getVar('jatoken');
		
		if(empty($uploadToken)) {
			//401 Unauthorized
			echo "401|".JText::_("INVALID_TOKEN");
		} else {
			$uploadStatusFile = $this->_getUploadFile($uploadToken);
			if(is_file($uploadStatusFile)) {
				//200 OK
				$content = file_get_contents($uploadStatusFile);
				$content = "200|".$content;
				echo $content;
			} else {
				//404 Not Found
				echo "404|".JText::_("INITIALIZING_UPLOAD");
			}
		}
		exit();
	}
	
	/**
	 * handle stop upload request
	 *
	 */
	function stopupload() {
		
		$uploadToken = JRequest::getVar('jatoken');
		
		if(empty($uploadToken)) {
			//401 Unauthorized
			echo "401|".JText::_("INVALID_TOKEN");
		} else {
			$uploadStopFile = $this->_getUploadFile($uploadToken, "_stop");
			$fp = fopen($uploadStopFile, 'wb');
			$result = fwrite($fp, "_STOP_");
			fclose($fp);
			
			if($result === false) {
				$content = "500|".JText::_("FAIL_TO_STOP_UPLOAD");
			} else {
				//200 OK
				$content = "200|".JText::_("SUCCESSFULLY_STOP_UPLOADING");
			}
			echo $content;
		}
		exit();
	}
	
	function showProgressBar() {
		JRequest::setVar ( 'layout', 'progressbar' );
		parent::display();
	}
	
	function _getUploadFile($token, $suffix = '') {
		$path = JPATH_SITE.'/cache/'.JACOMPONENT.'/';
		if(!is_dir(JPATH_SITE.'/cache/'.JACOMPONENT.'/')) {
			JFolder::create($path, 0777);
		}
		
		$fileName = $token.$suffix;
		$file = $path.$fileName.".txt";
		return $file;
	}
	
	/**
	 * check if uploading is stopped
	 *
	 * @param (string) $uploadStopFile - path to file that used as stop flag
	 * @param (int) $cnt - number of upload file
	 */
	function _checkUploadStopped($uploadStopFile, $cnt) {
		if(JFile::exists($uploadStopFile)) {
			if($cnt == 0) {
				$cnt = -1;//stop when init process
			}
			
			$uploadCronChecking = $this->_getUploadFile($this->flag_cron_checking, $this->upload_cron_profile);
			JFile::delete($uploadCronChecking);
			//stop upload
			echo "200|{$cnt}";//display number of items was uploaded
			exit();
		}
	}
}

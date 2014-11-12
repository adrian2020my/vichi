<?php
// Implement ajax page
/**IMPLEMENT JOOMLA CORE**/
// Set flag that this is a parent file
define( '_JEXEC', 1 );
define('JPATH_BASE', dirname(dirname(dirname(dirname(__FILE__)))) );
define( 'DS', DIRECTORY_SEPARATOR );

require_once JPATH_BASE.'/includes/defines.php';
require_once JPATH_BASE.'/includes/framework.php';

// Mark afterLoad in the profiler.
JDEBUG ? $_PROFILER->mark('afterLoad') : null;

// Instantiate the application.
$mainframe = JFactory::getApplication('site');

// Initialise the application.
$mainframe->initialise();


/**BEGINING OF MY CODE**/

@set_time_limit(0);

if(!defined('JPATH_COMPONENT')) {
	define('JPATH_COMPONENT', dirname(__FILE__));
}
// Require constants
require_once(JPATH_COMPONENT.'/constants.php');
require_once(JPATH_COMPONENT.'/controllers/localrepo.php');

/**
 * 
 */

$params = JComponentHelper::getParams(JACOMPONENT);
$cron_mode = $params->get('cron_mode','off');
if($cron_mode == "off") {
	echo JText::_("Cron Job is disabled.");
	exit();
}			

$db = JFactory::getDBO();

$key = JRequest::getVar('key', '');
if(!empty($key)) {
	$userRequest = jakey_decrypt_params();
	$uploadKey = isset($userRequest['uploadKey']) ? $userRequest['uploadKey'] : '';
	//
	if(empty($uploadKey) || ($uploadKey != $params->get('upload_secret_key', ''))) {
		echo JText::_("Invalid Request.");
		exit();
	}
	
	$checkTime = isset($userRequest['checkTime']) ? $userRequest['checkTime'] : time();
	$checkDate = date('Y-m-d H:i:s', $checkTime);
	$uploadFolder = isset($userRequest['folder']) ? $userRequest['folder'] : '';
	$profile_id = isset($userRequest['profile']) ? intval($userRequest['profile']) : 0;
	$immediatelyRun = (isset($userRequest['run']) && $userRequest['run'] == 1) ? 1 : 0;
	
} else {
	$checkTime = time();
	$checkDate = date('Y-m-d H:i:s', $checkTime);
	$uploadFolder = '';
	$profile_id = 0;
	//deny immediately upload from front-end without key provided
	$immediatelyRun = 0;
}

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
			p.* 
		FROM #__jaamazons3_profile AS p
		INNER JOIN #__jaamazons3_bucket b ON b.id = p.bucket_id 
		INNER JOIN #__jaamazons3_account a ON b.acc_id = a.id 
		WHERE p.cron_enable = 1";
$db->setQuery($query);
$listProfiles = $db->loadObjectList();
//print_r($listProfiles);
if(count($listProfiles)) {
	$ctrlRepo = new jaAmazonS3ControllerLocalrepo();
	foreach ($listProfiles as $profile) {
		if($profile_id) {
			if($profile_id != $profile->id) {
				continue;
			}
		}
		$lastRunTime = (int) strtotime($profile->cron_last_run);
		$interval = ((intval($profile->cron_day) * 24 * 60 * 60) + (intval($profile->cron_hour) * 60 *60) + $profile->cron_minute) * 60;
		if($interval == 0) {
			// minimum is 10 minute
			$interval = 600;
		}
		$nextRunTime = $lastRunTime+$interval;
		/*echo "\r\n last run day:".$profile->cron_last_run;
		echo "\r\n last run:".$lastRunTime;
		echo "\r\n interval:".$interval;
		echo "\r\n next time:".$nextRunTime;
		echo "\r\n check date:".$checkDate;
		echo "\r\n check time:".$checkTime;*/
		
		if(($nextRunTime < $checkTime) || $immediatelyRun) {
			//update check time
			if($uploadFolder == '') {
				$query = "UPDATE #__jaamazons3_profile SET cron_last_run = '{$checkDate}' WHERE id = '{$profile->id}'";
				$db->setQuery($query);
				$db->query();
			}
			//do upload
			$uploadToken = "jaamazons3_upload_cron_".time();
			//$ctrlRepo->_upload($uploadToken, $profile, '', 0);
			$ctrlRepo->_upload($uploadToken, $profile, $uploadFolder, 2);//upload new file or updated file
			
			echo "<br />\r\n";
			if($uploadFolder == '') {
				echo JText::sprintf('Profile "%1$s" has been successfully uploaded.', $profile->profile_name);
			} else {
				echo JText::sprintf('The folder "%1$s" of profile "%2$s" has been successfully uploaded.', $uploadFolder, $profile->profile_name);
			}
		} else {
			echo JText::sprintf('Profile "%1$s" will be run on %2$s', $profile->profile_name, date("Y-m-d H:i:s", $nextRunTime));
		}
		echo "<br />\r\n";
	}
}else{
	echo JText::_("Cron Job is disabled.");
	exit();
}
?>
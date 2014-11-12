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
defined( '_JEXEC' ) or die( 'Restricted access' );
if(!defined('DS')) define( 'DS', DIRECTORY_SEPARATOR );

@set_time_limit(0);
@ini_set('memory_limit', '1024M');

JToolbarHelper::title(JText::_("JOOMLART_AMAZON_S3"));

// Require constants
require_once(JPATH_COMPONENT.'/constants.php');

// Load global assets
if(!defined('JA_GLOBAL_SKIN')) {
	define('JA_GLOBAL_SKIN', 1);
	
	JHtml::_('behavior.framework');
	JHtml::_('behavior.tooltip');
	JHtml::_('behavior.modal');
	
	$assets = JURI::root(true).'/administrator/components/'.JACOMPONENT.'/assets/';
	$doc = JFactory::getDocument();
	$doc->addStyleSheet($assets. 'css/style.css');
	
	if(version_compare(JVERSION, '3.0', 'lt')) {
		$doc->addScript($assets. 'js/jquery.js');
		$doc->addScript($assets. 'js/jquery.event.drag-1.4.min.js');
	} else {
		JHtml::_('jquery.framework');
	}
	$doc->addScript($assets. 'js/menu.js');
	$doc->addScript($assets. 'js/joomlart.js');
	//popup
	$doc->addStyleSheet($assets. 'japopup/ja.popup.css');
	$doc->addScript($assets. 'japopup/ja.popup.js');
	//alert
	$doc->addStyleSheet($assets. 'jquery.alerts/jquery.alerts.css');
	$doc->addScript($assets. 'jquery.alerts/jquery.alerts.js');
	
}

//check if plugin "JA Amazon S3" has been installed or not
$pluginFile = JPATH_PLUGINS."/system/plg_jaamazons3/plg_jaamazons3.php";
if(!is_file($pluginFile)) {
	JError::raiseNotice(100, JText::_("THE_PLUGIN_JA_AMAZON_S3_HAS_NOT_BEEN_INSTALLED_ON_YOUR_SITE_YET"));
}

//check account and buckets
$db = JFactory::getDBO();
jimport('joomla.filesystem.file');
/*VERSION 1.0.3*/
$path = JPath::clean(JPATH_COMPONENT.'/installer/sql/updated_103.s3');
if (!JFile::exists($path)) {
	$file = JPATH_COMPONENT.DS.'installer' . DS . 'sql'.DS.'upgrade_v1.0.3.sql';
	if(JFile::exists($file)) {
		$buffer = JFile::read($file);
		if($buffer) {
			$queries = $db->splitSql($buffer);
			foreach ($queries as $query) {
				$db->setQuery($query);
				$db->query();
			}
		}
	}
	$data = "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>";
	JFile::write($path, $data);
}
$warningMsg = "";
$sqlCheckAccount = "SELECT * FROM #__jaamazons3_account LIMIT 0,1";
$db->setQuery($sqlCheckAccount);
$oAcc = $db->loadObject();
$sqlCheckBucket = "SELECT * FROM #__jaamazons3_bucket LIMIT 0,1";
$db->setQuery($sqlCheckBucket);
$oBucket = $db->loadObject();

if(JRequest::getVar('tmpl') != 'component' || (JRequest::getVar('view') == 'profile' && JRequest::getVar('task') == 'edit')) {
	$session =& JFactory::getSession();
	$sessionQueue = $session->get('application.queue');
	if (!count($sessionQueue)) {
		if(JRequest::getVar('tmpl') != 'component') {
			if(!$oAcc || !$oBucket) {
				$warningMsg .= JText::_("ACTION_REQUIRED_BEFORE_SOME_FUNCTIONS_ARE_AVAILABLE");
				if(!$oAcc) {
					$warningMsg .= "<br />";
		$warningMsg .= JText::sprintf('CONFIG_AT_LEAST_1_AMAZON_S3_ACCOUNT', "index.php?option=".JACOMPONENT."&view=account");
				}
				if(!$oBucket) {
					$warningMsg .= "<br />";
		$warningMsg .= JText::sprintf('UPDATE_BUCKET_INFORMATION_FROM_AMAZON_S3_TO_COMPONENT_DATABASE', "index.php?option=".JACOMPONENT."&view=bucket");
				}
				JError::raiseNotice(100, $warningMsg);
			}
		} else {
			if(!$oAcc) {
				$warningMsg = JText::sprintf('CONFIG_AT_LEAST_1_ACCOUNT_BEFORE_CREATE_PROFILE', "index.php?option=".JACOMPONENT."&view=account");
				JError::raiseNotice(100, $warningMsg);
			} elseif(!$oBucket) {
				$warningMsg = JText::sprintf('CONFIG_AT_LEAST_1_BUCKET_BEFORE_CREATE_PROFILE', "index.php?option=".JACOMPONENT."&view=bucket");
				JError::raiseNotice(100, $warningMsg);
			}
		}
	}
}


// -----
// Require specific controller if requested
if($controller = JRequest::getWord( 'view', 'account' )) {
	$path = JPATH_COMPONENT.'/controllers/'.$controller.'.php';
	if(file_exists( $path )) {
		require_once $path;
	} else {
		$controller = '';
	}
}

// Create the controller
$className  = 'jaAmazonS3Controller'.$controller;

$controller = new $className();

// Perform the Request task
$controller->execute(JRequest::getVar('task'));

// Redirect if set by the controller
$controller->redirect();
?>
<?php include('assets/images/social.png'); ?>
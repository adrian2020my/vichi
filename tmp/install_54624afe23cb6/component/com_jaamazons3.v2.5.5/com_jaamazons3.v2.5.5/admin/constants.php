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

//JA Amazon S3 Component using Amazon S3 (Simple Storage Service) and CloudFusion Library
define('JACOMPONENT', 'com_jaamazons3');
define('JA_WORKING_DATA_FOLDER', JPATH_ROOT);
if(!defined('JASITE_URL')) define('JASITE_URL', JURI::root());

define('JA_PROFILE_PATH_DEPTH', 1);
// Require the base controller
require_once(JPATH_COMPONENT.'/controller.php');
//
require_once(JPATH_COMPONENT.'/helpers/mime.php');
require_once(JPATH_COMPONENT.'/helpers/jastorage.php');
require_once(JPATH_COMPONENT.'/helpers/jafile.php');
require_once(JPATH_COMPONENT.'/helpers/jaform.php');
require_once(JPATH_COMPONENT.'/helpers/jaloader.php');
require_once(JPATH_COMPONENT.'/helpers/repo.php');
require_once(JPATH_COMPONENT.'/helpers/jasmushit.php');
require_once(JPATH_COMPONENT.'/helpers/jaencrypt.php');
require_once(JPATH_COMPONENT.'/helpers/jamenu/jamenu.php');

// Require Tarzan Library (CloudFusion)
// Api Refrence: http://getcloudfusion.com/docs/2.6/
require_once(JPATH_COMPONENT.'/lib/cloudfusion.2.6/sdk.class.php');
/*$jaLoader = new jaLoader();
$jaLoader->importRecursive(JPATH_COMPONENT.'/lib/cloudfusion.2.6');*/

// Conflicted solve: 
// Remove CloudFustion autoloader (com_jaamazons3/lib/cloudfusion.2.6/sdk.class.php)
// And register Joomla autoloader (/joomla root/libraries/loader.php)
/*spl_autoload_unregister(array('CloudFusion', 'autoloader'));
spl_autoload_register('__autoload');*/
?>
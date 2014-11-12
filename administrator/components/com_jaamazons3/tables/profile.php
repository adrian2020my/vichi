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
defined ( '_JEXEC' ) or die ( 'Restricted access' );

class TableProfile extends JTable{
 	/** @var int */
 	var $id=0;
 	/** @var int */
 	var $bucket_id=0;
 	/** @var string */
 	var $profile_name='';
 	/** @var string */
 	var $allowed_extension='js,css,jpg,gif,png,bmp,doc,pdf';
 	/** @var string */
 	var $site_path='{jpath_root}';
 	/** @var string */
 	var $site_url='{juri_root}';
 	/** @var Use SmushIt.com to compress images */
 	var $use_smushit = 0;
 	/** @var int Browser Cache Lifetime */
 	var $cache_lifetime = 0;
 	/** @var int */
 	var $cron_enable=0;
 	/** @var int */
 	var $cron_minute=0;
 	/** @var int */
 	var $cron_hour=1;//default is every hour
 	/** @var int */
 	var $cron_day=0;
	var $cron_last_run = '';
	var $is_default = 0;
	var $profile_status = 0;
 	/** @var string */
 	//var $params='';
 	
 	function __construct(&$db){
 		parent::__construct( '#__jaamazons3_profile', 'id', $db );
	}
 	function bind( $array, $ignore='' ){
		if (key_exists( 'params', $array ) && is_array( $array['params'] )) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}
		return parent::bind($array, $ignore);
 	}
	function check(){		
		$error=array();
		/** check error data */
		if($this->profile_name=='')
			$error[]=JText::_("PLEASE_ENTER_PROFILE_NAME");		
		if(!isset($this->id))
			$error[]=JText::_("ID_MUST_NOT_BE_NULL");
		elseif(!is_numeric($this->id))
			$error[]=JText::_("ID_MUST_BE_NUMBER");
		elseif(!is_numeric($this->bucket_id))
			$error[]=JText::_("BUCKET_ID_MUST_BE_NUMBER");

		return $error;
	}
}
?>

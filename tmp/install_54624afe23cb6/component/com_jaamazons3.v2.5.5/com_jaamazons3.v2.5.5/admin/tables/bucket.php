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

class TableBucket extends JTable{
 	/** @var int */
 	var $id=0;
 	/** @var int */
 	var $acc_id=0;
 	/** @var string */
 	var $bucket_name='';
 	/** @var string */
 	var $bucket_cloudfront_domain='';
 	/** @var string */
 	var $bucket_acl='public';
 	/** @var string - http|https */
 	var $bucket_protocol='http';
 	/** @var string - folder|subdomain */
 	var $bucket_url_format='subdomain';
 	
 	/* if enable, images file will be compressed by smushit service (http://www.smushit.com/)*/
 	var $last_sync = '0000-00-00 00:00:00';
 	
 	function __construct(&$db){
 		parent::__construct( '#__jaamazons3_bucket', 'id', $db );
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
		if($this->bucket_name=='')
			$error[]=JText::_("PLEASE_ENTER_BUCKET_NAME");		
		if(!isset($this->id))
			$error[]=JText::_("ID_MUST_NOT_BE_NULL");
		elseif(!is_numeric($this->id))
			$error[]=JText::_("ID_MUST_BE_NUMBER");
		elseif(!is_numeric($this->acc_id))
			$error[]=JText::_("ACCOUNT_ID_MUST_BE_NUMBER");

		return $error;
	}
}
?>

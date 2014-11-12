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

class TableAccount extends JTable{
 	/** @var int */
 	var $id=0;
 	/** @var string */
 	var $acc_label='';
 	/** @var string */
 	var $acc_name='';
 	/** @var string */
 	var $acc_accesskey='';
 	/** @var string */
 	var $acc_secretkey='';
 	/** @var string */
 	//var $params='';
 	
 	function __construct(&$db){
 		parent::__construct( '#__jaamazons3_account', 'id', $db );
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
		if($this->acc_label=='')
			$error[]=JText::_("PLEASE_ENTER_ACCOUNT_NAME");		
		if($this->acc_accesskey=='')
			$error[]=JText::_("PLEASE_ENTER_ACCESS_KEY");		
		if($this->acc_secretkey=='')
			$error[]=JText::_("PLEASE_ENTER_SECRET_KEY");		
		if(!isset($this->id))
			$error[]=JText::_("ID_MUST_NOT_BE_NULL");
		elseif(!is_numeric($this->id))
			$error[]=JText::_("ID_MUST_BE_NUMBER");

		return $error;
	}
}
?>

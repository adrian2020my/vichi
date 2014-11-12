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

// Ensure this file is being included by a parent file
defined('_JEXEC') or die( 'Restricted access' );

/**
 * Radio List Element
 *
 * @since      Class available since Release 1.2.0
 */
class JElementJaparamhelper extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Japaramhelper';

	function fetchElement( $name, $value, &$node, $control_name ) {
		if (substr($name, 0, 1) == '@'  ) {
			$name = substr($name, 1);
			if (method_exists ($this, $name)) {
				return $this->$name ($name, $value, $node, $control_name);
			}
		} else {
			$subtype = ( isset( $node->_attributes['subtype'] ) ) ? trim($node->_attributes['subtype']) : '';
			if (method_exists ($this, $subtype)) {
				return $this->$subtype ($name, $value, $node, $control_name);
			}
		}
		return; 
	}
	
	function cronpath( $name, $value, &$node, $control_name ) {
		$html = JPATH_ADMINISTRATOR."/components/com_jaamazons3/cron.php";
		$html = "<span style=\"color:red;\">{$html}</span>";
		return $html;
	}
	
	function phppath( $name, $value, &$node, $control_name ) {
		$paramname = $control_name.'['.$name.']';
		$id = $control_name.$name;
		
		$attributes = $node->attributes();
		if (is_array($attributes)) {
			$attributes = JArrayHelper::toString($attributes);
		}
		
		if(empty($value)) {
			if ( substr(PHP_OS,0,3) == 'WIN' ) {
				//how to get PHP executable path on window?
				//http://www.php.net/manual/en/faq.installation.php#faq.installation.addtopath
				$value = "php";
			} else {
				$value = "php";
			}
		}
		
		$html = "\n\t<input type=\"text\" name=\"{$paramname}\" id=\"{$id}\" value=\"{$value}\" {$attributes} />";
		return $html;
	}
	
	function mysqlpath( $name, $value, &$node, $control_name ) {
		$paramname = $control_name.'['.$name.']';
		$id = $control_name.$name;
		
		$attributes = $node->attributes();
		if (is_array($attributes)) {
			$attributes = JArrayHelper::toString($attributes);
		}
		
		if(empty($value)) {
			$value = $this->_getMysqlVariables('mysqlpath', 'mysql');
		}
		
		$html = "\n\t<input type=\"text\" name=\"{$paramname}\" id=\"{$id}\" value=\"{$value}\" {$attributes} />";
		return $html;
	}
	
	function mysqldumppath( $name, $value, &$node, $control_name ) {
		$paramname = $control_name.'['.$name.']';
		$id = $control_name.$name;
		
		$attributes = $node->attributes();
		if (is_array($attributes)) {
			$attributes = JArrayHelper::toString($attributes);
		}
		
		if(empty($value)) {
			$value = $this->_getMysqlVariables('mysqldumppath', 'mysql');
		}
		
		$html = "\n\t<input type=\"text\" name=\"{$paramname}\" id=\"{$id}\" value=\"{$value}\" {$attributes} />";
		return $html;
	}
	
	function _getMysqlVariables($var, $default = '') {
		$aData = array();
		if ( substr(PHP_OS,0,3) == 'WIN') {
			$db =& JFactory::getDBO();
			$query = 'SHOW VARIABLES';
			$db->setQuery($query);
			$rs = $db->loadObjectList();
			$aMysqlVariables = array();
			foreach ($rs as $row) {
				$aMysqlVariables[$row->Variable_name] = $row->Value;
			}
			$aData['mysqlpath'] = (isset($aMysqlVariables['basedir'])) ? $aMysqlVariables['basedir'] . 'bin/mysql' : 'mysql';
			$aData['mysqldumppath'] = (isset($aMysqlVariables['basedir'])) ? $aMysqlVariables['basedir'] . 'bin/mysqldump' : 'mysqldump';
		} else {
			$aData['mysqlpath'] = 'mysql';
			$aData['mysqldumppath'] = 'mysqldump';
		}
		return (isset($aData[$var])) ? $aData[$var] : $default;
	}
} 
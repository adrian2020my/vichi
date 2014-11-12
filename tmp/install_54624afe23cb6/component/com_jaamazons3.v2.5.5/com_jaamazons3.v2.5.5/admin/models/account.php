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

jimport ( 'joomla.application.component.model' );

class jaAmazonS3ModelAccount extends JModelLegacy {
	
	var $_pagination = NULL;
	var $_total = 0;
	
	function getRow($cid = array(0)) 
	{
		$table = &$this->getTable ( 'account', 'Table' );		
		// Load the current item if it has been defined
		$edit = JRequest::getVar ( 'edit', true );
		if (! $cid || @! $cid [0]) {
			$cid = JRequest::getVar ( 'cid', array (0 ), '', 'array' );
			JArrayHelper::toInteger ( $cid, array (0 ) );
		}

		if ($edit) {
			$table->load ( $cid [0] );
		}

		$item = $table;
		
		return $item;
	}
	
	function getRow2($cid) 
	{
		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__jaamazons3_account WHERE id = ".$db->Quote($cid);
		$db->setQuery($query);
		$row = $db->loadObject();
		return $row;
	}
	
	function getList($userState) {
		$db = JFactory::getDBO ();
		$account = array ();
		
		$limitstart = $userState['limitstart'];
		$limit = $userState['limit'];
		$cond = "";
		$order = '';
		
		if($order != '') {
			$order = "ORDER BY {$order}";
		}
		
		$sql = "
				SELECT t.*
				FROM #__jaamazons3_account AS t
				WHERE 1 {$cond}
				{$order}
				LIMIT {$limitstart}, {$limit}";
		$db->setQuery ( $sql );
		$account = $db->loadObjectList();
		return $account;
	}
	
	function getTotal($userState) {
		$db = JFactory::getDBO ();
		
		$cond = "";
		$order = '';
		
		$query = "
				SELECT COUNT(*)  
				FROM #__jaamazons3_account AS t
				WHERE 1 {$cond}";
		
		$db->setQuery ( $query );
		$this->_total = $db->loadResult ();
		return $this->_total;
	}
	
	function checkInfoAvaiable($id, $label, $name, $accesskey) {
		$db = JFactory::getDBO();
		$query = "
			SELECT * FROM #__jaamazons3_account 
			WHERE (acc_label = ".$db->Quote($label)." OR acc_name = ".$db->Quote($name)." OR acc_accesskey = ".$db->Quote($accesskey).")
			";
		$id = (int) $id;
		if($id) {
			$query .= " AND id <> " . $id;
		}
		$db->setQuery($query);
		$row = $db->loadObject();
		return $row;
	}
	
	function store() 
	{
		$row = & $this->getRow ();
		$post = $this->getState ( 'request' );
		
		
		$rowCheck = $this->checkInfoAvaiable($row->id, $post["acc_label"], $post["acc_name"], $post["acc_accesskey"]);
		if($rowCheck && isset($rowCheck->acc_label)) {
			return JText::_("THIS_LABEL_ACCOUNT_ID_OR_ACCOUNT_ACCESSKEY_IS_USED_ON_ANOTHER_ACCOUNT");
		}
		
		//check amazon account infor
		
		$acc = (object) array(
							'acc_accesskey' => $post["acc_accesskey"],
							'acc_secretkey' => $post["acc_secretkey"]
						);
		$s3 = jaStorageHelper::getStorage($acc);
		$account = $s3->get_canonical_user_id();
		//Array ( [id] => xxx [display_name] => joomlart ) 
		if(empty($account["id"])) {
			return JText::_("THE_ACCOUNT_THAT_YOU_PROVIDED_IS_INCORRECT_PLEASE_CHECK_IT_AGAIN");
		}
		//
		
		if (! $row->bind ( $post )) {
			return $row->getError();
		}
		
		if ( ($erros = $row->check ())) {
			//print_r($erros);
			return implode ( "<br/>", $erros );
		}
		
		if (! $row->store ()) {
			//echo 'error';
			return $row->getError();
		}

		return $row;
	}
	
	function _getVars() {
		static $lists;
		if($lists) return $lists;
		
		$lists = array ();
		$lists ['order'] = JRequest::getString('order', 't.acc_label desc' );
		$lists ['order_Dir'] = JRequest::getCmd( 'order_Dir', '');
		$lists ['limit'] = JRequest::getInt( 'limit', 20 );
		$lists ['limitstart'] = JRequest::getInt('limitstart', 0);
		// In case limit has been changed, adjust limitstart accordingly
		$limit = $lists['limit'];
		$lists ['limitstart'] = ( $limit != 0 ? (floor($lists['limitstart'] / $limit) * $limit) : 0 );
		return $lists;
	}
	
	function _getVars_admin() {
		$mainframe = JFactory::getApplication('administrator');
		$option='account';
		$lists = array ();
		$lists ['filter_order'] = $mainframe->getUserStateFromRequest ( $option . '.filter_order', 'filter_order', 't.id', 'string' );
		$lists ['filter_order_Dir'] = $mainframe->getUserStateFromRequest ( $option . '.filter_order_Dir', 'filter_order_Dir', 'desc', 'word' );
		$lists ['limit'] = $mainframe->getUserStateFromRequest ( $option . '.limit', 'limit', 20, 'int' );
		$lists ['limitstart'] = $mainframe->getUserStateFromRequest ( $option . '.limitstart', 'limitstart', 0, 'int' );
		// In case limit has been changed, adjust limitstart accordingly
		$limit = $lists['limit'];
		$lists ['limitstart'] = ( $limit != 0 ? (floor($lists['limitstart'] / $limit) * $limit) : 0 );
		return $lists;
	}	
	
	function delete($id){
		$query="DELETE FROM #__jaamazons3_account WHERE id={$id}";
		$this->_db->setQuery($query);
		$result = $this->_db->query();
		return $result;
	}
	
	function getBoxAccounts($name, $value, $javascript = '')
	{
		$db = JFactory::getDBO ();
		
		$sql = "
				SELECT t.id AS value, t.acc_label AS text
				FROM #__jaamazons3_account AS t
				WHERE 1 
				ORDER BY t.acc_label";
		$db->setQuery ( $sql );
		
		$lists = array();
		$lists[] = JHtml::_('select.option',  '0', '- '. JText::_('SELECT_ACCOUNT' ) .' -' );
		$lists = array_merge( $lists, $db->loadObjectList() );
		$lists = JHtml::_('select.genericlist',   $lists, $name, 'class="inputbox" size="1" '. $javascript, 'value', 'text', $value );
		
		return $lists;
	}
}
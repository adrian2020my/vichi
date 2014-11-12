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

jimport ( 'joomla.application.component.view' );


class jaAmazonS3ViewAccount extends JViewLegacy {
	
	function display($tpl = null) {
		jaComponentMenuHeader();
		
		switch ($this->getLayout ()) {
			case 'form' :
				$this->edit ();
				break;
			case 'config' :
				$this->config ();
				break;
			case 'response':
				$this->response();
				break;
			default :
				$this->displayList ();
				break;
		}
		parent::display ( $tpl );

		jaComponentMenuFooter();
	}
	function displayList() {
		
		JHtml::_ ( 'behavior.calendar' );
		
		$model = JModelLegacy::getInstance ( 'account', 'jaAmazonS3Model' );

		$lists = $model->_getVars_admin ();

		$lists['create_date'] = JRequest::getVar('createdate',NULL);
				
		
		
		$total = $model->getTotal ($lists);
		jimport ( 'joomla.html.pagination' );
		$pageNav = new JPagination ( $total, $lists ['limitstart'], $lists ['limit'] );
		//update limit for query
		
		if ($lists ['limit'] > $total) {
			$lists ['limitstart'] = 0;
		}
		if ($lists ['limit'] == 0) {
			$lists ['limit'] = $total;
		}
		//$account = $model->getList ('', 't.acc_label ASC', $lists ['limitstart'], $lists ['limit'] );
		$account = $model->getList ($lists);

		$this->assign ( 'account', $account );
		
		$this->assign ( 'lists', $lists );
		
		$this->assign ( 'pageNav', $pageNav );
	}
	
	function edit($item = null) {
		
		JHtml::_ ( 'behavior.calendar' );
		
		$model = $this->getModel ( 'account' );

		if (! $item) {
			
			$item = $this->get ( 'row' );
			
			$postback = JRequest::getVar ( 'postback' );
			
			if (! $postback) {
				
				$post = JRequest::get ( 'request', JREQUEST_ALLOWHTML );
				
				$item->bind ( $post );
			}
		}
		
		$number = JRequest::getVar ( 'number', 0 );
		
		$this->assignRef ( 'listMode', $listMode );
		$this->assignRef ( 'item', $item );
		$this->assignRef ( 'number', $number );
	}
	
	function config($item = null) {
		$model = $this->getModel ( 'account' );

		if (! $item) {
			
			$item = $this->get ( 'row' );
			
			$postback = JRequest::getVar ( 'postback' );
			
			if (! $postback) {
				
				$post = JRequest::get ( 'request', JREQUEST_ALLOWHTML );
				
				$item->bind ( $post );
			}
		}
		
		$number = JRequest::getVar ( 'number', 0 );
		
		$this->assignRef ( 'item', $item );
		$this->assignRef ( 'number', $number );
	}
  	
	function response(){
		$model = JModelLegacy::getInstance ( 'account', 'jaAmazonS3Model' );
		$type = JRequest::getVar('type','admin_response');
		if (!isset($item)) {			
			$item = $model->getAdmin_response();
		}	
		
		$cid[0] = $item->item_id;
		$row = $model->getItem($cid)	;
		$item->item_title = $row?$row->title:'';
		$response = JFactory::getUser($item->user_id);
		$item->responsename = $response?$response->username:''; 
		$this->assign('item',$item);
		$this->assign('type',$type);
	}
}	
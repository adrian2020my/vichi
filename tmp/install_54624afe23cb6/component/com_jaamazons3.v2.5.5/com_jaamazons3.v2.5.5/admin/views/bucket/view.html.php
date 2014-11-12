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


class jaAmazonS3ViewBucket extends JViewLegacy {
	
	function display($tpl = null) {
		jaComponentMenuHeader();
		
		switch ($this->getLayout ()) {
			case 'form' :
				$this->edit ();
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
		
		$model = JModelLegacy::getInstance ( 'bucket', 'jaAmazonS3Model' );

		$lists = $model->_getVars_admin ();

		$lists['create_date'] = JRequest::getVar('createdate',NULL);
		$total = $model->getTotal ($lists);
				
		jimport ( 'joomla.html.pagination' );
		$pageNav = new JPagination ( $total, $lists ['limitstart'], $lists ['limit'] );
		//update limit for query
		if ($lists['limit'] == 0) {
			$lists['limit'] = $total;
		} else {
			$lists['limit'] = $lists['limit'];
		}
		
		//$bucket = $model->getList ('', 't.bucket_name ASC', $lists ['limitstart'], $lists ['limit'] );
		$bucket = $model->getList ($lists);
		
		$modeAccount = JModelLegacy::getInstance ( 'account', 'jaAmazonS3Model' );
		$lists ['boxAccounts'] = $modeAccount->getBoxAccounts('acc_id', $lists['acc_id']);

		$this->assign ( 'bucket', $bucket );
		
		$this->assign ( 'lists', $lists );
		
		$this->assign ( 'pageNav', $pageNav );
	}
	
	function edit($item = null) {
		
		JHtml::_ ( 'behavior.calendar' );
		
		$model = $this->getModel ( 'bucket' );

		if (! $item) {
			
			$item = $this->get ( 'row' );
			
			$postback = JRequest::getVar ( 'postback' );
			
			if (! $postback) {
				
				$post = JRequest::get ( 'request', JREQUEST_ALLOWHTML );
				
				$item->bind ( $post );
			}
		}
		$listStatus = JHtml::_ ( 'select.radiolist', $model->getListStatus(), 'bucket_acl', 'class="inputbox"', 'value', 'text', $item->bucket_acl );
		$listProtocols = JHtml::_ ( 'select.radiolist', $model->getListProtocols(), 'bucket_protocol', 'class="inputbox"', 'value', 'text', $item->bucket_protocol );
		$listUrlFormats = JHtml::_ ( 'select.radiolist', $model->getListUrlFormats(), 'bucket_url_format', 'class="inputbox"', 'value', 'text', $item->bucket_url_format, false, true );
		
		$account = $model->getActiveAccount();
		$acc_id = ($account !== false) ? $account->id : '';
		$listBuckets = $model->getBoxBuckets('bucket_source_id', '', '', $acc_id);
		
		$region = AmazonS3::REGION_US_E1;
		$boxRegionProperties = '';
		if($account) {
			$s3 = jaStorageHelper::getStorage($account);
			if($item->id) {
				$boxRegionProperties = 'disabled="disabled"';
				$result = $s3->get_bucket_region($item->bucket_name);
				if($result->isOK()) {
					$region = (string) $result->body;
					if(!empty($region)) {
						$region = 's3-'.$region.'.amazonaws.com';
					}
				}
			}
		}
		$listRegions = $model->getBoxRegion('bucket_region', $region, $boxRegionProperties);
		$number = JRequest::getVar ( 'number', 0 );
		
		$this->assignRef ( 'listStatus', $listStatus );
		$this->assignRef ( 'listProtocols', $listProtocols );
		$this->assignRef ( 'listUrlFormats', $listUrlFormats );
		$this->assignRef ( 'listBuckets', $listBuckets );
		$this->assignRef ( 'listRegions', $listRegions );
		$this->assignRef ( 'boxFolder', $boxFolder );
		$this->assignRef ( 'item', $item );
		$this->assignRef ( 'number', $number );
	}
  	
	function response(){
		$model = JModelLegacy::getInstance ( 'bucket', 'jaAmazonS3Model' );
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
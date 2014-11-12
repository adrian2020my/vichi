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


class jaAmazonS3ViewProfile extends JViewLegacy {
	
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
		
		$model = JModelLegacy::getInstance ( 'profile', 'jaAmazonS3Model' );

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
		
		//$profile = $model->getList ('', 't.profile_name ASC', $lists ['limitstart'], $lists ['limit'] );
		$profile = $model->getList ($lists);
		
		$modeAccount = JModelLegacy::getInstance ( 'account', 'jaAmazonS3Model' );

		$this->assign ( 'profile', $profile );
		
		$this->assign ( 'lists', $lists );
		
		$this->assign ( 'pageNav', $pageNav );
	}
	
	function edit($item = null) {
		
		JHtml::_ ( 'behavior.calendar' );
		
		$model = $this->getModel ( 'profile' );

		if (! $item) {
			
			$item = $this->get ( 'row' );
			
			$postback = JRequest::getVar ( 'postback' );
			
			if (! $postback) {
				
				$post = JRequest::get ( 'request', JREQUEST_ALLOWHTML );
				
				$item->bind ( $post );
			}
		}
		
		$number = JRequest::getVar ( 'number', 0 );
		$profile_status	= JHtml::_('select.booleanlist',  'profile_status', 'class="inputbox"', $item->profile_status );
		$cron_enable	= JHtml::_('select.booleanlist',  'cron_enable', 'class="inputbox"', $item->cron_enable );
		$useSmushit		= JHtml::_('select.booleanlist',  'use_smushit', 'class="inputbox"', $item->use_smushit );
		
		$boxFolder = $model->getBoxFolders('site_path', $item->site_path, 'onchange="selectProfilePathRoot(this.id);"');
		
		$modelBucket = JModelLegacy::getInstance ( 'bucket', 'jaAmazonS3Model' );
		$boxBuckets = $modelBucket->getBoxBuckets('bucket_id', $item->bucket_id);
		
		$this->assignRef ( 'useSmushit', $useSmushit );
		$this->assignRef ( 'profile_status', $profile_status );
		$this->assignRef ( 'cron_enable', $cron_enable );
		$this->assignRef ( 'boxFolder', $boxFolder );
		$this->assignRef ( 'boxBuckets', $boxBuckets );
		$this->assignRef ( 'item', $item );
		$this->assignRef ( 'number', $number );
	}
  	
	function response(){
		$model = JModelLegacy::getInstance ( 'profile', 'jaAmazonS3Model' );
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
		
	function showCronJobInterval($item) {
		$interval = "";
		if($item->cron_day > 0) {
			$interval .= "{$item->cron_day} ".(($item->cron_day == 1) ? JText::_("DAY") : JText::_("DAYS"))." - ";
		}
		if($item->cron_hour > 0) {
			$interval .= "{$item->cron_hour} ".(($item->cron_hour == 1) ? JText::_("HOUR") : JText::_("HOURS"))." - ";
		}
		if($item->cron_minute > 0) {
			$interval .= "{$item->cron_minute} ".(($item->cron_minute == 1) ? JText::_("MINUTE") : JText::_("MINUTES"));
		}
		if(!empty($interval)) {
			$interval = "<strong>".JText::_("RUN_ON")."</strong>".JText::_("EVERY").$interval." <br />";
		}
		$interval .= "<strong>".JText::_("LAST_RUN")."</strong>".$item->cron_last_run;
		return $interval;
	}
}	
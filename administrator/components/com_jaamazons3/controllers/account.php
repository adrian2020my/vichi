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

defined('JPATH_BASE') or die();

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.path');

class jaAmazonS3ControllerAccount extends jaAmazonS3Controller {

	function __construct($default = array()) {

		parent::__construct ( $default );
		
		
		$task = JRequest::getWord ( 'task', '' );
		switch ($task) {
			case 'add' :
			case 'save' :
			case 'apply' :
			case 'edit' :
				JToolBarHelper::apply ();
				JToolBarHelper::save ();
				JToolBarHelper::cancel ();
				break;
			default :
			    // Add a popup configuration button
				JToolBarHelper::addNew();
				JToolBarHelper::deleteList();
			    JToolBarHelper::preferences(JACOMPONENT, 250, 570);
				break;
		}
		// Register Extra tasks
		JRequest::setVar ( 'view', 'account' );
		$this->registerTask ( 'add', 'edit' );
		$this->registerTask ( 'apply', 'save' );
		$this->registerTask ( 'publish', 'setDefault' );
	}

	function display($cachable = false, $urlparams = false) {

		$user = JFactory::getUser ();
		$task =$this->getTask();
		switch ($task){
			case 'edit':
				JRequest::setVar ( 'layout', 'form' );
				break;
			case 'config':
				JRequest::setVar ( 'layout', 'config' );
				break;
		}
		if ($user->id == 0) {

			JError::raiseWarning ( 1001, JText::_("YOU_MUST_BE_SIGNED_IN" ) );

			$this->setRedirect ( JRoute::_ ( "index.php?option=com_user&view=login" ) );

			return;
		}

		parent::display();
	}

	function cancel() {
		$this->setRedirect ( 'index.php?option='.JACOMPONENT.'&view=account' );

		return TRUE;
	}
	function save(&$errors = '') {
		$task = $this->getTask ();

		$model	=& $this->getModel('account');
		$post	= JRequest::get('post');

		$post['acc_label'] 	= JRequest::getString( 'acc_label', '');
		$post['acc_name'] 	= JRequest::getString('acc_name', '');
		$post['acc_accesskey'] 	= JRequest::getString( 'acc_accesskey', '');
		$post['acc_secretkey'] 	= JRequest::getString('acc_secretkey', '');

		$model->setState( 'request', $post );
		$row = $model->store();
		if (!isset($row->id)){
			$errors[] = $row;
			return FALSE;

		}
		return $row;
	}
	
	function saveIFrame() {
		$errors = array ();
		$row = $this->save ( $errors );
		
		$redirectUrl = JRoute::_('index.php?option='.JACOMPONENT.'&view=account', false);

		if(isset($row->id)) {
			$this->redirectParent($redirectUrl, JText::_("SAVE_DATA_SUCCESSFULLY" ));
		} else {
			$this->redirectParent($redirectUrl, $errors, 'error');
		}
	}
	
	function saveConfig() {
		if(count($_POST)) {
			$post = JRequest::get ( 'request', JREQUEST_ALLOWHTML );
			$number = $post ['number'];
			$errors = array ();
			$row = $this->save ( $errors );
	
			$backUrl = JRequest::getVar('backUrl', '');
			if(!empty($backUrl)) {
				$backUrl = urldecode($backUrl);
				$this->setRedirect ( $backUrl);
			} else {
				$this->setRedirect ('index.php?option='.JACOMPONENT.'&view=account');
			}
		}
	}

	function saveorder() {
		$model = $this->getModel ( 'account' );
		$msg = '';
		if (! $model->saveOrder ()) {
			JError::raiseWarning ( 1001, JText::_('ERROR_OCCURRED_DATA_NOT_SAVED' ) );
		} else {
			$msg = JText::_('SAVE_DATA_SUCCESSFULLY' );
		}
		$this->setRedirect ( 'index.php?option='.JACOMPONENT.'&view=account', $msg );
	}

	function setDefault() {
		$model = $this->getModel ( 'account' );
		$createdate = JRequest::getInt('createdate',0);
		if (! $model->setDefault ( 1 )) {
			JError::raiseWarning ( 1001, JText::_('ERROR_OCCURRED_DATA_NOT_SAVED' ) );
		} else {
			$msg = JText::_('SAVE_DATA_SUCCESSFULLY' );
		}
		$link = 'index.php?option='.JACOMPONENT.'&view=account';
		if($createdate) $link.="&createdate=".$createdate;
		$this->setRedirect ( $link, $msg );
	}

	function remove() {
		$model = $this->getModel ( 'account' );
		$cids = JRequest::getVar ( 'cid', null, 'post', 'array' );
		$error = array ();
		foreach ( $cids as $cid ) {
			if (! $model->delete ( $cid )) {
				$error = $cid;
			} else {
				$modelBucket = JModelLegacy::getInstance ( 'bucket', 'jaAmazonS3Model' );
				$modelBucket->deleteByAccount($cid);
			}
		}
		if (count ( $error ) > 0) {
			$err = implode ( ",", $error );
			JError::raiseWarning ( 1001, JText::_('ERROR_OCCURRED_UNABLE_TO_DELETE_THE_ITEMS_WITH_ID' ).': ' . " [$err]" );
		} else
		$msg = JText::_("DELETE_DATA_SUCCESSFULLY" );
		$this->setRedirect ( 'index.php?option='.JACOMPONENT.'&view=account', $msg );
	}
}
?>
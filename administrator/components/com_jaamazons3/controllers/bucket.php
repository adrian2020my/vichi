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

class jaAmazonS3ControllerBucket extends jaAmazonS3Controller {

	function __construct($default = array()) {

		parent::__construct ( $default );
		
		$this->_checkUserState();
		
		$task = JRequest::getWord ( 'task', '' );
		
		$model	= $this->getModel('bucket');
		$account = $model->getActiveAccount();
		
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
			    if($account) {
					JToolBarHelper::addNew();
					JToolBarHelper::custom('import', 'update_list', 'update_list', JText::_("GET_LIST_BUCKETS_FROM_S3"), false);
					JToolBarHelper::deleteList(JText::_("WARNING_DO_YOU_REALLY_WANT_TO_DELETE_BUCKET_FROM_DATABASE_AND_FROM_S3_ALSO_AND_ALL_OF_ITS_CONTENTS"), 'removeS3', JText::_("DELETE_S3"));
					JToolBarHelper::deleteList(JText::_("DO_YOU_REALLY_WANT_TO_DELETE_BUCKET"));
			    }
			    JToolBarHelper::preferences(JACOMPONENT, 250, 570);
				break;
		}
		// Register Extra tasks
		JRequest::setVar ( 'view', 'bucket' );
		$this->registerTask ( 'add', 'edit' );
		$this->registerTask ( 'apply', 'save' );
		$this->registerTask ( 'import', 'import' );
		$this->registerTask ( 'publish', 'setDefault' );
	}
	
	function _checkUserState() {
		$model	= $this->getModel('bucket');
		$lists = $model->_getVars_admin ();
		if(!$lists['acc_id']) {
			JError::raiseNotice(100, JText::_("PLEASE_SELECT_AN_ACCOUNT_FIRST"));
			return false;
		}
		return true;
	}

	function display($cachable = false, $urlparams = false) {

		$user = JFactory::getUser ();
		$task =$this->getTask();
		switch ($task){
			case 'edit':
				JRequest::setVar ( 'layout', 'form' );
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
		$this->setRedirect ( 'index.php?option='.JACOMPONENT.'&view=bucket' );

		return TRUE;
	}
	function save(&$errors = '') {
		$task = $this->getTask ();

		$model	=& $this->getModel('bucket');
		$post	= JRequest::get('post');

		$post['bucket_name'] 	= JRequest::getString( 'bucket_name', '');
		$post['bucket_cloudfront_domain'] 	= JRequest::getString('bucket_cloudfront_domain', '');
		$post['bucket_acl'] 	= JRequest::getString('bucket_acl', '');

		$model->setState( 'request', $post );
		$row = $model->store(true);
		if (!isset($row->id)){
			$errors = $row;
			return FALSE;

		}
		return $row;
	}
	function saveIFrame() {
		$errors = array ();
		$row = $this->save ( $errors );;

		$redirectUrl = JRoute::_('index.php?option='.JACOMPONENT.'&view=bucket', false);
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
				$this->setRedirect ('index.php?option='.JACOMPONENT.'&view=bucket');
			}
		}
	}

	function saveorder() {
		$model = $this->getModel ( 'bucket' );
		$msg = '';
		if (! $model->saveOrder ()) {
			JError::raiseWarning ( 1001, JText::_('ERROR_OCCURRED_DATA_NOT_SAVED' ) );
		} else {
			$msg = JText::_('SAVE_DATA_SUCCESSFULLY' );
		}
		$this->setRedirect ( 'index.php?option='.JACOMPONENT.'&view=bucket', $msg );
	}

	function setDefault() {
		$model = $this->getModel ( 'bucket' );
		$createdate = JRequest::getInt('createdate',0);
		if (! $model->setDefault ( 1 )) {
			JError::raiseWarning ( 1001, JText::_('ERROR_OCCURRED_DATA_NOT_SAVED' ) );
		} else {
			$msg = JText::_('SAVE_DATA_SUCCESSFULLY' );
		}
		$link = 'index.php?option='.JACOMPONENT.'&view=bucket';
		if($createdate) $link.="&createdate=".$createdate;
		$this->setRedirect ( $link, $msg );
	}

	function remove() {
		$model = $this->getModel ( 'bucket' );
		$cids = JRequest::getVar ( 'cid', null, 'post', 'array' );
		$error = array ();
		foreach ( $cids as $cid ) {
			if (! $model->delete ( $cid )) {
				$error = $cid;
			} else {
			}
		}
		if (count ( $error ) > 0) {
			$err = implode ( ",", $error );
			JError::raiseWarning ( 1001, JText::_('ERROR_OCCURRED_UNABLE_TO_DELETE_THE_ITEMS_WITH_ID' ).': ' . " [$err]" );
		} else
		$msg = JText::_("DELETE_DATA_SUCCESSFULLY" );
		$this->setRedirect ( 'index.php?option='.JACOMPONENT.'&view=bucket', $msg );
	}

	function removeS3() {
		$model = $this->getModel ( 'bucket' );
		$cids = JRequest::getVar ( 'cid', null, 'post', 'array' );
		$error = array ();
		foreach ( $cids as $cid ) {
			if (! $model->removeS3 ( $cid )) {
				$error[] = $cid;
			} else {
			}
		}
		if (count ( $error ) > 0) {
			$err = implode ( ",", $error );
			JError::raiseWarning ( 1001, JText::_('ERROR_OCCURRED_UNABLE_TO_DELETE_THE_ITEMS_WITH_ID' ).': ' . " [$err]" );
		} else {
			$msg = JText::_("DELETE_DATA_SUCCESSFULLY" );
			$this->setRedirect ( 'index.php?option='.JACOMPONENT.'&view=bucket', $msg );
		}
	}
	
	function import() {
		$model = $this->getModel ( 'bucket' );
		$result = $model->import();
		$msg = ($result === false) 
				? JText::_("ERROR_OCCURRED_DURING_IMPORT_BUCKET_LIST_FROM_S3") 
				: JText::_("SUCCESSFULLY_IMPORT_BUCKET_LIST_FROM_S3");
		$this->setRedirect ( 'index.php?option='.JACOMPONENT.'&view=bucket', $msg );
	}
}
?>
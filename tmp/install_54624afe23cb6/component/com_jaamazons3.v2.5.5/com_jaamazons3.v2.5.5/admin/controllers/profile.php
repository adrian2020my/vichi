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

class jaAmazonS3ControllerProfile extends jaAmazonS3Controller {

	function __construct($default = array()) {

		parent::__construct ( $default );
		
		$this->_checkUserState();
		
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
				JToolBarHelper::deleteList(JText::_("DO_YOU_REALLY_WANT_TO_DELETE_PROFILE"));
			    JToolBarHelper::preferences(JACOMPONENT, 250, 570);
				break;
		}
		// Register Extra tasks
		JRequest::setVar ( 'view', 'profile' );
		$this->registerTask ( 'add', 'edit' );
		$this->registerTask ( 'apply', 'save' );
		$this->registerTask ( 'delete', 'remove' );
		$this->registerTask ( 'publish', 'setDefault' );
	}
	
	function _checkUserState() {
		$model	=& $this->getModel('profile');
		$lists = $model->_getVars_admin ();
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
		$this->setRedirect ( 'index.php?option='.JACOMPONENT.'&view=profile' );

		return TRUE;
	}
	function save(&$errors = '') {
		$task = $this->getTask ();

		$model	=& $this->getModel('profile');
		$post	= JRequest::get('post');

		$post['profile_name'] 	= JRequest::getString( 'profile_name', '');
		$post['site_path'] 	= JRequest::getString('site_path', '');
		$post['allowed_extension'] 	= JRequest::getString('allowed_extension', '');
		$post['site_url'] 	= JRequest::getString( 'site_url', '');
		$post['profile_acl'] 	= JRequest::getString('profile_acl', '');

		$model->setState( 'request', $post );
		$row = $model->store(true);
		if (!isset($row->id)){
			$errors = $row;
			return FALSE;

		}
		return $row;
	}
	function saveIFrame() {
		$db = JFactory::getDbo();
		$errors = array ();
		$row = $this->save ( $errors );

		$redirectUrl = JRoute::_('index.php?option='.JACOMPONENT.'&view=profile', false);
		if(isset($row->id)) {
			//check if the bucket is using for other profile
			$query = "SELECT * FROM #__jaamazons3_profile WHERE bucket_id = '{$row->bucket_id}' AND id <> '{$row->id}'";
			$db->setQuery($query);
			$rowCheck = $db->loadObject();

			if($rowCheck) {
				$this->redirectParent($redirectUrl, JText::_("SAVE_DATA_SUCCESSFULLY_BUT_BUCKET_IS_USING_FOR_OTHER_PROFILE" ));
			} else {
				$this->redirectParent($redirectUrl, JText::_("SAVE_DATA_SUCCESSFULLY" ));
			}
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
				$this->setRedirect ('index.php?option='.JACOMPONENT.'&view=profile');
			}
		}
	}

	function saveorder() {
		$model = $this->getModel ( 'profile' );
		$msg = '';
		if (! $model->saveOrder ()) {
			JError::raiseWarning ( 1001, JText::_('ERROR_OCCURRED_DATA_NOT_SAVED' ) );
		} else {
			$msg = JText::_('SAVE_DATA_SUCCESSFULLY' );
		}
		$this->setRedirect ( 'index.php?option='.JACOMPONENT.'&view=profile', $msg );
	}

	function setDefault() {
		$model = $this->getModel ( 'profile' );
		$createdate = JRequest::getInt('createdate',0);
		if (! $model->setDefault ( 1 )) {
			JError::raiseWarning ( 1001, JText::_('ERROR_OCCURRED_DATA_NOT_SAVED' ) );
		} else {
			$msg = JText::_('SAVE_DATA_SUCCESSFULLY' );
		}
		$link = 'index.php?option='.JACOMPONENT.'&view=profile';
		if($createdate) $link.="&createdate=".$createdate;
		$this->setRedirect ( $link, $msg );
	}

	function remove() {
		$model = $this->getModel ( 'profile' );
		$cids = JRequest::getVar ( 'cid', null );
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
		$this->setRedirect ( 'index.php?option='.JACOMPONENT.'&view=profile', $msg );
	}
	
	function import() {
		$model = $this->getModel ( 'profile' );
		$result = $model->import();
		$msg = ($result === false) 
				? JText::_("ERROR_OCCURRED_DURING_IMPORT_PROFILE_LIST_FROM_S3") 
				: JText::_("SUCCESSFULLY_IMPORT_PROFILE_LIST_FROM_S3");
		$this->setRedirect ( 'index.php?option='.JACOMPONENT.'&view=profile', $msg );
	}
}
?>
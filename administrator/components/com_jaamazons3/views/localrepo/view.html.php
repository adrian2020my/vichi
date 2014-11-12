<?php
/**
 * @desc Modify from component Media Manager of Joomla
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Media component
 *
 * @static
 * @package		Joomla
 * @subpackage	Media
 * @since 1.0
 */
class jaAmazonS3ViewLocalrepo extends JViewLegacy 
{
	function display($tpl = null)
	{
		$mainframe = JFactory::getApplication('administrator');
		jaComponentMenuHeader();
		
		$layout = $this->getLayout();
		switch ($layout) {
			case "progressbar":
				$this->displayProgressbar();
				break;
			default:
				$this->displayPanel();
				break;
		}

		parent::display($tpl);
		echo JHtml::_('behavior.keepalive');
		jaComponentMenuFooter();
	}
	
	function displayProgressbar() {
		
	}
	
	function displayPanel() {

		$config =& JComponentHelper::getParams(JACOMPONENT);

		//$style = $mainframe->getUserStateFromRequest('media.list.layout', 'layout', 'details', 'word');
		$style = "details";

		$document =& JFactory::getDocument();

		JHtml::_('behavior.framework', true);
		$assets = JURI::root().'administrator/components/'.JACOMPONENT.'/assets/';
		
		JHtml::_('script',  $assets. 'repo_manager/'.'localmanager.js', false, true);
		JHtml::_('stylesheet',  $assets. 'repo_manager/'.'repomanager.css', false, true);

		JHtml::_('behavior.modal', 'a.modal');
		$document->addScriptDeclaration("
		window.addEvent('domready', function() {
			document.preview = SqueezeBox;
		});");

		JHtml::_('script','system/mootree.js', true, true, false, false);
		JHtml::_('stylesheet','system/mootree.css', array(), true);

		if ($config->get('enable_flash', 0)) {
			JHtml::_('behavior.uploader', 'file-upload', array('onAllComplete' => 'function(){ MediaManager.refreshFrame(); }'));
		}
		/**
		 * get list profiles
		 */
		$modelLocalrepo = JModelLegacy::getInstance ( 'localrepo', 'jaAmazonS3Model' );
		$lists = $modelLocalrepo->_getVars_admin ();
		
		$modeProfile = JModelLegacy::getInstance ( 'profile', 'jaAmazonS3Model' );
		$lists ['boxProfiles'] = $modeProfile->getBoxProfiles('profile_id', $lists['profile_id'], "onchange=\"this.form.submit();\"");
		
		$profile = $modelLocalrepo->getActiveProfile();
		$needSync = 0;
		if($profile !== false){
			if($profile->last_sync == '0000-00-00 00:00:00') {
				$needSync = 1;
			}
			//get upload url
			$uploadKey = $config->get('upload_secret_key', '');
			$key = "action=upload&uploadKey={$uploadKey}&profile={$profile->id}&run=1";
			$key = urlencode(jakey_encrypt($key, md5('1218787810')));
			$cron_upload_url = JURI::root()."administrator/components/".JACOMPONENT."/cron.php?key=".$key;
		} else {
			$cron_upload_url = '#';
		}

		/*if(DS == '\\')
		{
			$base = str_replace(DS,"\\\\",$profile->bucket_name);
		} else {
			$base = $profile->bucket_name;
		}*/
		$base = "";

		$js = "
			var basepath = '".$base."';
			var viewstyle = '".$style."';
		" ;
		$document->addScriptDeclaration($js);

		/*
		 * Display form for FTP credentials?
		 * Don't set them here, as there are other functions called before this one if there is any file write operation
		 */
		jimport('joomla.client.helper');
		$ftp = !JClientHelper::hasCredentials('ftp');
		
		$this->assignRef('cron_upload_url', $cron_upload_url);
		$this->assignRef('needSync', $needSync);
		$this->assignRef('profile', $profile);
		$this->assignRef('lists', $lists);
		$this->assignRef('session', JFactory::getSession());
		$this->assignRef('config', $config);
		$this->assignRef('state', $this->get('state'));
		$this->assign('require_ftp', $ftp);
		$this->assign('folders_id', ' id="media-tree"');
		$this->assign('folders', $this->get('folderTree'));
		
		$user =& JFactory::getUser();
		$this->assignRef('user', $user);

		// Set the toolbar
		$this->_setToolBar();
		
	}

	function _setToolBar()
	{
		// Get the toolbar object instance
		$bar =& JToolBar::getInstance('toolbar');

		// Set the titlebar text
		JToolBarHelper::title( JText::_('JOOMLART_AMAZON_S3' ), 'generic');
		
	    
		// Add a upload button
		$title = JText::_('UPLOAD_TO_S3');
		JToolBarHelper::custom('upload', 'upload', 'upload', $title, false);
	    
		// Add a `Update S3 File list` button
		$title = JText::_('UPDATE_S3_FILE_LIST');
		JToolBarHelper::custom('update_list_s3_files', 'update_list', 'update_list', $title, false);
		
	    // Add a popup configuration button
	    JToolBarHelper::preferences(JACOMPONENT, 250, 570);

		// Add a popup configuration button
		//JToolBarHelper::help( 'screen.mediamanager' );
	}

	function getFolderLevel($folder)
	{
		$this->folders_id = null;
		$txt = null;
		if (isset($folder['children']) && count($folder['children'])) {
			$tmp = $this->folders;
			$this->folders = $folder;
			$txt = $this->loadTemplate('folders');
			$this->folders = $tmp;
		}
		return $txt;
	}
}

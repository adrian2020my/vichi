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
class jaAmazonS3ViewRepo extends JViewLegacy 
{
	function display($tpl = null)
	{
		$mainframe = JFactory::getApplication('administrator');
		jaComponentMenuHeader();

		$config = JComponentHelper::getParams(JACOMPONENT);

		//$style = $mainframe->getUserStateFromRequest('media.list.layout', 'layout', 'details', 'word');
		$style = "details";

		$document = JFactory::getDocument();

		JHtml::_('behavior.framework', true);
		$assets = JURI::root().'administrator/components/'.JACOMPONENT.'/assets/';
		
		JHtml::_('script',  $assets. 'repo_manager/'.'repomanager.js', false, true);
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
		 * get list buckets
		 */
		$model = JModelLegacy::getInstance ( 'repo', 'jaAmazonS3Model' );
		$lists = $model->_getVars_admin();
		
		$modelBucket = JModelLegacy::getInstance ( 'bucket', 'jaAmazonS3Model' );
		$lists ['boxBuckets'] = $modelBucket->getBoxBuckets('bucket_id', $lists['bucket_id'], "onchange=\"this.form.submit();\"");
		$lists ['boxMappedProfiles'] = $modelBucket->getBoxMappedProfiles('map_profile_id', $lists['bucket_id'], "");
		$bucket = $model->getActiveBucket();

		/*if(DS == '\\')
		{
			$base = str_replace(DS,"\\\\",$bucket->bucket_name);
		} else {
			$base = $bucket->bucket_name;
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

		$this->assignRef('lists', $lists);
		$this->assignRef('session', JFactory::getSession());
		$this->assignRef('config', $config);
		$this->assignRef('state', $this->get('state'));
		$this->assign('require_ftp', $ftp);
		$this->assign('folders_id', ' id="media-tree"');
		$this->assign('folders', $this->get('folderTree'));
		
		$user = JFactory::getUser();
		$this->assignRef('user', $user);

		// Set the toolbar
		$this->_setToolBar();

		parent::display($tpl);
		JHtml::_('behavior.keepalive');
		jaComponentMenuFooter();
	}

	function _setToolBar()
	{
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		// Set the titlebar text
		JToolBarHelper::title( JText::_('JOOMLART_AMAZON_S3' ), 'generic');
		
		
		$model = JModelLegacy::getInstance ( 'repo', 'jaAmazonS3Model' );
		
		$bucket = $model->getActiveBucket();
	    
		if($bucket) {
			
		    
			// Add a `Update S3 File list` button
			$title = JText::_('UPDATE_S3_FILE_LIST');
			JToolBarHelper::custom('pull_s3_files', 'restore', 'restore', JText::_("PULL_S3_FILE"), false);
			JToolBarHelper::custom('update_list_s3_files', 'update_list', 'update_list', $title, false);
			
			JToolBarHelper::divider();
			JToolBarHelper::custom('update_acl_public', 'publish', 'publish', JText::_("publish"), false);
			JToolBarHelper::custom('update_acl_private', 'unpublish', 'unpublish', JText::_("unpublish"), false); 
			// Add a upload button
			$title = JText::_('CREATE_DIRECTORY');
			JToolBarHelper::custom('create_folder', 'new', 'new', $title, false);
			JToolBarHelper::divider();
			$title = JText::_('DELETE');
			if(version_compare(JVERSION, '3.0', 'lt')) {
				$dhtml = "<a href=\"#\" onclick=\"multiDelete(); return false;\" class=\"toolbar\">
							<span class=\"icon-32-delete\" title=\"$title\" type=\"Custom\"></span>
							{$title}
							</a>";
			} else {
				$dhtml = "<button class=\"btn btn-small\" onclick=\"multiDelete(); return false;\" href=\"#\">
							<i class=\"icon-delete\"></i>
							{$title}
							</button>";
			}
			$bar->appendButton( 'Custom', $dhtml, 'delete' );
			JToolBarHelper::custom('delete_advance', 'delete', 'delete', JText::_("ADVANCED_DELETE"), false);
		}
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

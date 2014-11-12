<?php

/**
* Article Open Graph parameters plugin
* @Copyright (C) 2009-2011 Gavick.com
* @ All rights reserved
* @ Joomla! is Free Software
* @ Released under GNU/GPL License : http://www.gnu.org/copyleft/gpl.html
* @version $Revision: GK4 1.0 $
**/

defined( '_JEXEC' ) or die();

jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.event.plugin' );
jimport( 'joomla.html.parameter' );
jimport( 'joomla.application.module.helper' );

class plgSystemPlg_GK_OpenGraph extends JPlugin {
	var $_params;
	var $_pluginPath;
	
	function __construct( &$subject ) {
		parent::__construct( $subject );
		$this->_plugin = JPluginHelper::getPlugin( 'system', 'plg_gk_opengraph' );
		$this->_params = new JParameter( $this->_plugin->params );
		$this->_pluginPath = JPATH_PLUGINS.DS."system".DS."plg_gk_opengraph".DS;
	}
	//Add Gavick menu parameter
	function onContentPrepareForm($form, $data) {		
		if($form->getName()=='com_content.article') {
			JForm::addFormPath($this->_pluginPath);
			$form->loadFile('parameters', false);
		}
	}
	
	public function onAfterDispatch()
	{
		if (JFactory::getApplication()->isAdmin()) { return true; }
		// Set the variables
		$input = JFactory::getApplication()->input;
		// check option & view
		$option = $input->get('option', '', 'cmd');
		
		$view = $input->get('view');
		$doc = JFactory::getDocument();
		// Adjust the component buffer.
		if(($option === 'com_content' && $view === 'article') || ($option === 'com_k2' && $view === 'item')) {
			// leave article settings
		} else {
			$uri = JURI::getInstance();
			$doc->addCustomTag('<meta property="og:title" content="'.$this->_params->get('og:title').'" />');
			$doc->addCustomTag('<meta property="og:type" content="'.$this->_params->get('og:type').'" />');
			$doc->addCustomTag('<meta property="og:image" content="'.$uri->root().$this->_params->get('og:image').'" />');
			$doc->addCustomTag('<meta property="og:site_name" content="'.$this->_params->get('og:site_name').'" />');
			$doc->addCustomTag('<meta property="og:description" content="'.$this->_params->get('og:description').'" />');
		}
		
		return true;
		
	}
}
?>
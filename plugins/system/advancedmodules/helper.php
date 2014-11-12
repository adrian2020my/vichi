<?php
/**
 * Plugin Helper File
 *
 * @package         Advanced Module Manager
 * @version         4.17.0
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2014 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

require_once JPATH_PLUGINS . '/system/nnframework/helpers/functions.php';

NNFrameworkFunctions::loadLanguage('com_advancedmodules');

/*
 * ModuleHelper methods
 */

class plgSystemAdvancedModulesHelper
{
	public function loadModuleHelper()
	{
		$classes = get_declared_classes();
		if (in_array('JModuleHelper', $classes) || in_array('jmodulehelper', $classes))
		{
			return;
		}

		require_once JPATH_PLUGINS . '/system/advancedmodules/modulehelper.php';
	}

	public function registerEvents()
	{
		require_once JPATH_PLUGINS . '/system/advancedmodules/advancedmodulehelper.php';
		JFactory::getApplication()->registerEvent('onRenderModule', 'plgSystemAdvancedModulesRenderModule');
		JFactory::getApplication()->registerEvent('onCreateModuleQuery', 'plgSystemAdvancedModulesCreateModuleQuery');
		JFactory::getApplication()->registerEvent('onPrepareModuleList', 'plgSystemAdvancedModulesPrepareModuleList');
	}

	public function replaceLinks()
	{
		if (JFactory::getApplication()->input->get('option') == 'com_modules')
		{
			$this->replaceLinksInCoreModuleManager();

			return;
		}

		$body = JResponse::getBody();

		$body = preg_replace('#(\?option=com_)(modules[^a-z-_])#', '\1advanced\2', $body);
		$body = str_replace(array('?option=com_advancedmodules&force=1', '?option=com_advancedmodules&amp;force=1'), '?option=com_modules', $body);

		JResponse::setBody($body);
	}

	private function replaceLinksInCoreModuleManager()
	{
		$config = $this->getConfig();
		if (!$config->show_switch)
		{
			return;
		}

		$body = JResponse::getBody();

		$url = 'index.php?option=com_advancedmodules';
		if (JFactory::getApplication()->input->get('view') == 'module')
		{
			$url .= '&task=module.edit&id=' . (int) JFactory::getApplication()->input->get('id');
		}

		$link = '<a style="float:right;" href="' . $url . '">' . JText::_('AMM_SWITCH_TO_ADVANCED_MODULE_MANAGER') . '</a>';
		$body = preg_replace('#(<div class="m">\s*)((<\!--.*?-->\s*)*<form)#', '\1' . $link . '<div class="clr"></div>\2', $body);
		$body = preg_replace('#(</form>\s*)((<\!--.*?-->\s*)*<div class="clr"></div>)#', '\1' . $link . '\2', $body);

		JResponse::setBody($body);
	}

	private function getConfig()
	{
		static $instance;
		if (!is_object($instance))
		{
			require_once JPATH_PLUGINS . '/system/nnframework/helpers/parameters.php';
			$parameters = NNParameters::getInstance();
			$instance = $parameters->getComponentParams('advancedmodules');
		}

		return $instance;
	}
}

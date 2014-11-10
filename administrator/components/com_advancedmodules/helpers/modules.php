<?php
/**
 * @package         Advanced Module Manager
 * @version         4.17.0
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2014 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * @copyright      Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Modules component helper.
 *
 * @package        Joomla.Administrator
 * @subpackage     com_advancedmodules
 * @since          1.6
 */
abstract class ModulesHelper
{
	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return    JObject
	 */
	public static function getActions($module = 0)
	{
		$user = JFactory::getUser();
		$result = new JObject;

		if (empty($articleId))
		{
			$assetName = 'com_advancedmodules';
		}
		else
		{
			$assetName = 'com_advancedmodules.module.' . (int) $module;
		}

		$actions = JAccess::getActions('com_advancedmodules');

		foreach ($actions as $action)
		{
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
	}

	/**
	 * Get a list of filter options for the state of a module.
	 *
	 * @return    array    An array of JHtmlOption elements.
	 */
	public static function getStateOptions()
	{
		// Build the filter options.
		$options = array();
		$options[] = JHtml::_('select.option', '1', JText::_('JPUBLISHED'));
		$options[] = JHtml::_('select.option', '0', JText::_('JUNPUBLISHED'));
		$options[] = JHtml::_('select.option', '-2', JText::_('JTRASHED'));
		return $options;
	}

	/**
	 * Get a list of filter options for the application clients.
	 *
	 * @return    array    An array of JHtmlOption elements.
	 */
	public static function getClientOptions()
	{
		// Build the filter options.
		$options = array();
		$options[] = JHtml::_('select.option', '0', JText::_('JSITE'));
		$options[] = JHtml::_('select.option', '1', JText::_('JADMINISTRATOR'));
		return $options;
	}

	/**
	 * Get a list of modules positions
	 *
	 * @param   integer  $clientId  Client ID
	 *
	 * @return  array  A list of positions
	 */
	public static function getPositions($clientId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT(m.position)')
			->from('#__modules as m')
			->where('m.client_id = ' . (int) $clientId)
			->order('position');

		$db->setQuery($query);

		try
		{
			$positions = $db->loadColumn();
			$positions = is_array($positions) ? $positions : array();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
			return;
		}

		// Build the list
		$options = array();
		foreach ($positions as $position)
		{
			if (!$position)
			{
				$options[] = JHtml::_('select.option', 'none', ':: ' . JText::_('JNONE') . ' ::');
			}
			else
			{
				$options[] = JHtml::_('select.option', $position, $position);
			}
		}
		return $options;
	}

	/**
	 * Return a list of templates
	 *
	 * @param   integer  $clientId  Client ID
	 * @param   string   $state     State
	 * @param   string   $template  Template name
	 *
	 * @return  array  List of templates
	 */
	public static function getTemplates($clientId = 0, $state = '', $template = '')
	{
		$db = JFactory::getDbo();

		// Build the query.
		$query = $db->getQuery(true)
			->select('e.element, e.name, e.enabled')
			->from('#__extensions as e');
		if ($template != '')
		{
			$query->where('e.element = ' . $db->quote($template));
		}
		$query->where('e.type = ' . $db->quote('template'))
			->where('e.client_id = ' . (int) $clientId);
		if ($state != '')
		{
			$query->where('e.enabled = ' . $db->quote($state));
		}

		// Set the query and load the templates.
		$db->setQuery($query);
		$templates = $db->loadObjectList('element');
		return $templates;
	}

	/**
	 * Get a list of the unique modules installed in the client application.
	 *
	 * @param   int  $clientId  The client id.
	 *
	 * @return  array  Array of unique modules
	 */
	public static function getModules($clientId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('e.element AS value, e.name AS text')
			->from('#__extensions as e')
			->join('LEFT', '#__modules as m ON m.module=e.element AND m.client_id=e.client_id')
			->where('e.type = ' . $db->quote('module'))
			->where('e.client_id = ' . (int) $clientId)
			->where('m.module IS NOT NULL')
			->group('e.element, e.name');

		$db->setQuery($query);
		$modules = $db->loadObjectList();
		$lang = JFactory::getLanguage();

		foreach ($modules as $i => $module)
		{
			$extension = $module->value;
			$path = $clientId ? JPATH_ADMINISTRATOR : JPATH_SITE;
			$source = $path . "/modules/$extension";
			$lang->load("$extension.sys", $path, null, false, false)
				|| $lang->load("$extension.sys", $source, null, false, false)
				|| $lang->load("$extension.sys", $path, $lang->getDefault(), false, false)
				|| $lang->load("$extension.sys", $source, $lang->getDefault(), false, false);
			$modules[$i]->text = JText::_($module->text);
		}
		JArrayHelper::sortObjects($modules, 'text', 1, true, $lang->getLocale());
		return $modules;
	}

	/**
	 * Get a list of the assignment options for modules to menus.
	 *
	 * @param   int  $clientId  The client id.
	 *
	 * @return  array
	 */
	public static function getMenuItems($clientId)
	{
		$options = array();
		$options[] = JHtml::_('select.option', '0', JText::_('JALL'));
		$options[] = JHtml::_('select.option', '-', JText::_('JNONE'));

		if ($clientId == 0)
		{
			$options[] = JHtml::_('select.option', '-2', JText::_('COM_MODULES_ASSIGNED_VARIES_ONLY'));
			$options[] = JHtml::_('select.option', '-1', JText::_('COM_MODULES_ASSIGNED_VARIES_EXCEPT'));

			require_once JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php';
			$types = MenusHelper::getMenuLinks();

			if (!empty($types))
			{
				$options[] = JHtml::_('select.option', '--', '&nbsp;', 'value', 'text', true);
				$options[] = JHtml::_('select.option', '--', JText::_('JOPTION_SELECT_MENU_ITEM'), 'value', 'text', true);
				foreach ($types as $type)
				{
					$options[] = JHtml::_('select.option', '--', '&nbsp;', 'value', 'text', true);
					$options[] = JHtml::_('select.option', '--', $type->title, 'value', 'text', true);
					foreach ($type->links as $item)
					{
						$options[] = JHtml::_('select.option', $item->value, $item->text);
					}
				}
			}
		}

		return $options;
	}
}

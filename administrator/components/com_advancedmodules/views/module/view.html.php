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
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit a module.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_advancedmodules
 * @since       1.6
 */
class AdvancedModulesViewModule extends JViewLegacy
{
	protected $form;

	protected $item;

	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->item		= $this->get('Item');
		$this->getConfig();

		if (!isset($this->item->published) || $this->item->published == '')
		{
			$this->item->published = $this->config->default_state;
		}

		$this->form		= $this->get('Form');
		$this->state	= $this->get('State');
		$this->getAssignments();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		if (preg_match('#_gk[1-9]#', $this->item->module))
		{
			// Set message for Gavick modules
			JFactory::getApplication()->enqueueMessage(JText::sprintf(html_entity_decode(JText::_('AMM_MODULE_INCOMPATIBLE')), 'index.php?option=com_modules&task=module.edit&id=' . (int) $this->item->id), 'warning');
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Function that gets the config settings
	 *
	 * @return    Object
	 */
	protected function getConfig()
	{
		if (!isset($this->config))
		{
			require_once JPATH_PLUGINS . '/system/nnframework/helpers/parameters.php';
			$parameters = NNParameters::getInstance();
			$this->config = $parameters->getComponentParams('advancedmodules');
		}
		return $this->config;
	}

	/**
	 * Function that gets the config settings
	 *
	 * @return    Object
	 */
	protected function getAssignments()
	{
		if (!isset($this->assignments))
		{
			$xmlfile = JPATH_ADMINISTRATOR . '/components/com_advancedmodules/assignments.xml';
			$assignments = new JForm('assignments', array('control' => 'advancedparams'));
			$assignments->loadFile($xmlfile, 1, '//config');
			$assignments->bind($this->item->advancedparams);
			$this->assignments = $assignments;
		}
		return $this->assignments;
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since    1.6
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user = JFactory::getUser();
		$isNew = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo = ModulesHelper::getActions($this->item->id);

		$title = $this->item->module;
		if ($this->item->xml)
		{
			$title = JText::_((string) $this->item->xml->name) . ' [' . $title . ']';
		}
		JToolbarHelper::title(JText::sprintf('AMM_MODULE_EDIT', $title), 'module');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || $canDo->get('core.create')))
		{
			JToolbarHelper::apply('module.apply');
			JToolbarHelper::save('module.save');
		}
		if (!$checkedOut && $canDo->get('core.create'))
		{
			JToolbarHelper::save2new('module.save2new');
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			JToolbarHelper::save2copy('module.save2copy');
		}
		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('module.cancel');
		}
		else
		{
			JToolbarHelper::cancel('module.cancel', 'JTOOLBAR_CLOSE');
		}

		$tmpl = JFactory::getApplication()->input->get('tmpl');
		if ($tmpl != 'component')
		{
			if ($canDo->get('core.admin'))
			{
				JToolbarHelper::divider();
				JToolbarHelper::preferences('com_advancedmodules', 600, 900);
			}

			// Get the help information for the menu item.
			$lang = JFactory::getLanguage();

			$help = $this->get('Help');
			if ($lang->hasKey($help->url))
			{
				$debug = $lang->setDebug(false);
				$url = JText::_($help->url);
				$lang->setDebug($debug);
			}
			else
			{
				$url = null;
			}
			JToolbarHelper::divider();
			JToolbarHelper::help($help->key, false, $url);
		}
	}

	protected function render(&$form, $name = '')
	{
		$items = array();
		foreach ($form->getFieldset($name) as $field)
		{
			$items[] = (!$field->hidden ? $field->label : '') . $field->input;
		}
		if (empty ($items))
		{
			return '';
		}

		return '<li>' . implode('</li><li>', $items) . '</li>';
	}
}

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
 * Modules manager master display controller.
 *
 * @package        Joomla.Administrator
 * @subpackage     com_modules
 * @since          1.6
 */
class AdvancedModulesController extends JControllerLegacy
{
	/**
	 * @var        string    The default view
	 */
	protected $default_view = 'modules';

	/**
	 * Method to display a view.
	 *
	 * @param    boolean            If true, the view output will be cached
	 * @param    array              An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return    JController        This object to support chaining.
	 * @since    1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT . '/helpers/modules.php';

		$view = JFactory::getApplication()->input->get('view', 'modules');
		$layout = JFactory::getApplication()->input->get('layout', 'default');
		$id = JFactory::getApplication()->input->getInt('id');

		// Check for edit form.
		if ($view == 'module' && $layout == 'edit')
		{
			if (!$this->checkEditId('com_advancedmodules.edit.module', $id))
			{
				// Somehow the person just went to the form - we don't allow that.
				$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
				$this->setMessage($this->getError(), 'error');
				$this->setRedirect(JRoute::_('index.php?option=com_advancedmodules&view=modules', false));

				return false;
			}

			// Check general edit permission first.
			if (!JFactory::getUser()->authorise('core.edit', 'com_advancedmodules.module.' . $id))
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
				$this->setMessage($this->getError(), 'error');
				$this->setRedirect(JRoute::_('index.php?option=com_advancedmodules&view=modules', false));
				return false;
			}
		}

		parent::display();
	}
}

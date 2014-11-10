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

JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$clientid = (int) $this->state->get('filter.client_id');
$client = $clientid ? 'administrator' : 'site';
$user = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$canOrder = $user->authorise('core.edit.state', 'com_advancedmodules');
$saveOrder = $listOrder == 'ordering';

$canDo = ModulesHelper::getActions();
if ($canDo->get('core.admin'))
{
	$config = JComponentHelper::getParams('com_advancedmodules');
	if (!$config->get('config_saved'))
	{
		JFactory::getApplication()->enqueueMessage(JText::_('NN_SAVE_CONFIG'), 'notice');
		$script = "
			window.addEvent('domready', function(){
				SqueezeBox.open(document.getElement('#toolbar-popup-options a'), {
					handler: 'iframe',
					size: {x: 900, y: 600}
				});
			});
		";
		JFactory::getDocument()->addScriptDeclaration($script);
	}
}

$showcolors = ($client == 'site' && $this->config->show_color);
if ($showcolors)
{
	require_once JPATH_PLUGINS . '/system/nnframework/fields/colorpicker.php';
	$colors = explode(',', $this->config->main_colors);
	foreach ($colors as $i => $c)
	{
		$colors[$i] = strtoupper('#' . preg_replace('#[^a-z0-9]#i', '', $c));
	}
	$script = "
		mainColors = ['" . implode("', '", $colors) . "'];
		function setColor(id, color)
		{
			var f = document.getElementById('adminForm');
			f.setcolor.value = color;
			listItemTask(id, 'modules.setcolor');
		}
	";
	JFactory::getDocument()->addScriptDeclaration($script);
}

if ($this->config->open_in_modals)
{
	JHtml::_('behavior.modal', 'a.modal', array('closable' => 0, 'closeBtn' => 0));
	JFactory::getDocument()->addStyleDeclaration('#sbox-btn-close { display: none; }');
}

// Version check
require_once JPATH_PLUGINS . '/system/nnframework/helpers/versions.php';
if ($this->config->show_update_notification)
{
	echo NNVersions::getInstance()->getMessage('advancedmodules', '', '', 'component');
}
?>

<?php if ($this->config->show_configmsg) : ?>
	<?php echo html_entity_decode(JText::sprintf('AMM_CONFIG_MESSAGE', JText::_('JOPTIONS')), ENT_COMPAT, 'UTF-8'); ?>
	<div style="clear:both;"></div>
<?php endif; ?>


	<form action="<?php echo JRoute::_('index.php?option=com_advancedmodules'); ?>" method="post" name="adminForm" id="adminForm">
		<fieldset id="filter-bar">
			<div class="filter-search fltlft">
				<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
				<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_MODULES_MODULES_FILTER_SEARCH_DESC'); ?>" />
				<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
			</div>
			<div class="filter-select fltrt">
				<select name="filter_client_id" class="inputbox" onchange="this.form.submit()">
					<?php echo JHtml::_('select.options', ModulesHelper::getClientOptions(), 'value', 'text', $clientid); ?>
				</select>
				<select name="filter_state" class="inputbox" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
					<?php echo JHtml::_('select.options', ModulesHelper::getStateOptions(), 'value', 'text', $this->state->get('filter.state')); ?>
				</select>

				<select name="filter_position" class="inputbox" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('COM_MODULES_OPTION_SELECT_POSITION'); ?></option>
					<?php echo JHtml::_('select.options', ModulesHelper::getPositions($clientid), 'value', 'text', $this->state->get('filter.position')); ?>
				</select>

				<select name="filter_module" class="inputbox" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('COM_MODULES_OPTION_SELECT_MODULE'); ?></option>
					<?php echo JHtml::_('select.options', ModulesHelper::getModules($clientid), 'value', 'text', $this->state->get('filter.module')); ?>
				</select>

				<select name="filter_menuid" class="inputbox" onchange="this.form.submit()">
					<option value="">- <?php echo JText::_('AMM_MENU_ITEM_ASSIGNMENT'); ?> -</option>
					<?php echo JHtml::_('select.options', ModulesHelper::getMenuItems($clientid), 'value', 'text', $this->state->get('filter.menuid')); ?>
				</select>

				<select name="filter_access" class="inputbox" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS'); ?></option>
					<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access')); ?>
				</select>

				<select name="filter_language" class="inputbox" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE'); ?></option>
					<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language')); ?>
				</select>
			</div>
		</fieldset>
		<div class="clr"></div>

		<?php $cols = 10; ?>
		<table class="adminlist" id="modules-mgr">
			<thead>
				<tr>
					<th width="1%">
						<input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this)" />
					</th>
					<?php if ($showcolors) : ?>
						<?php $cols++; ?>
						<th width="1%" style="white-space: nowrap;">
							<?php echo JHtml::_('grid.sort', '<img src="' . JURI::root(true) . '/media/advancedmodules/images/color.png" alt="Color" />', 'color', $listDirn, $listOrder); ?>
						</th>
					<?php endif; ?>
					<th class="title">
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<?php if ($this->config->show_note == 3) : ?>
						<?php $cols++; ?>
						<th class="title">
							<?php echo JHtml::_('grid.sort', 'JFIELD_NOTE_LABEL', 'a.note', $listDirn, $listOrder); ?>
						</th>
					<?php endif; ?>
					<th width="5%">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
					</th>
					<th width="15%" class="left">
						<?php echo JHtml::_('grid.sort', 'COM_MODULES_HEADING_POSITION', 'position', $listDirn, $listOrder); ?>
					</th>
					<th width="10%">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'ordering', $listDirn, $listOrder); ?>
						<?php if ($canOrder && $saveOrder) : ?>
							<?php echo JHtml::_('grid.order', $this->items, 'filesave.png', 'modules.saveorder'); ?>
						<?php endif; ?>
					</th>
					<th width="10%" class="left">
						<?php echo JHtml::_('grid.sort', 'COM_MODULES_HEADING_MODULE', 'name', $listDirn, $listOrder); ?>
					</th>
					<th width="10%">
						<?php echo JHtml::_('grid.sort', 'NN_MENU_ITEMS', 'pages', $listDirn, $listOrder); ?>
					</th>
					<th width="10%">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
					</th>
					<th width="5%">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language_title', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="<?php echo $cols; ?>">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach ($this->items as $i => $item) :
					$ordering = ($listOrder == 'ordering');

					$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
					$canChange = $user->authorise('core.edit.state', 'com_advancedmodules.module.' . $item->id) && $canCheckin;
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<?php if ($showcolors) : ?>
							<?php
							$color = 'FFFFFF';
							if (isset($item->params->color) && $item->params->color)
							{
								$color = strtoupper(preg_replace('#[^a-z0-9]#si', '', $item->params->color));
							}
							?>
							<td class="center">
								<?php
								$colorpicker = new nnFieldColorPicker;
								echo $colorpicker->getInput('color', 'cb' . $i, $color, array('inlist' => 1, 'action' => 'setColor(\'cb' . $i . '\', color_picker_form_field.value)'));
								?>
							</td>
						<?php endif; ?>
						<td>
							<?php if ($item->checked_out) : ?>
								<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'modules.', $canCheckin); ?>
							<?php endif; ?>
							<?php
							$title = $this->escape($item->title);
							$tooltip = JText::_('AMM_EDIT_MODULE') . '::' . htmlspecialchars($title);
							if (!empty($item->note) && $this->config->show_note == 1)
							{
								$tooltip .= '<br /><em>' . htmlspecialchars(JText::sprintf('JGLOBAL_LIST_NOTE', $this->escape($item->note))) . '</em>';
							}
							$title = '<span class="hasTip" title="' . $tooltip . '">' . $title . '</span>';
							?>

							<?php if ($this->config->open_in_modals) : ?>
								<span class="hasTip"
									title="<?php echo $tooltip; ?><br /><br /><strong><?php echo $this->escape(JText::_('AMM_OPEN_IN_MODAL_WINDOW')); ?></strong>">
									<a href="<?php echo JRoute::_('index.php?option=com_advancedmodules&task=module.edit&id=' . (int) $item->id); ?>&tmpl=component"
										class="modal"
										rel="{handler: 'iframe', size: {x:window.getSize().x-100, y: window.getSize().y-100}, }">
										<img src="<?php echo JURI::root(true); ?>/media/advancedmodules/images/edit.png"
											alt="<?php echo $this->escape(JText::_('AMM_OPEN_IN_MODAL_WINDOW')); ?>" />
									</a>
								</span>
							<?php endif; ?>
							<a href="<?php echo JRoute::_('index.php?option=com_advancedmodules&task=module.edit&id=' . (int) $item->id); ?>">
								<?php echo $title; ?></a>

							<?php if (!empty($item->note) && $this->config->show_note == 2) : ?>
								<p class="smallsub">
									<?php echo JText::sprintf('JGLOBAL_LIST_NOTE', $this->escape($item->note)); ?></p>
							<?php endif; ?>
						</td>
						<?php if ($this->config->show_note == 3) : ?>
							<td class="left">
								<?php echo $this->escape($item->note); ?>
							</td>
						<?php endif; ?>
						<td class="center">
							<?php echo JHtml::_('modules.state', $item->published, $i, $canChange, 'cb'); ?>
						</td>
						<td class="left">
							<?php if ($item->position) : ?>
								<?php echo $item->position; ?>
							<?php else : ?>
								<?php echo ':: ' . JText::_('JNONE') . ' ::'; ?>
							<?php endif; ?>
						</td>
						<td class="order">
							<?php if ($canChange) : ?>
								<?php if ($saveOrder) : ?>
									<?php if ($listDirn == 'asc') : ?>
										<span><?php echo $this->pagination->orderUpIcon($i, (@$this->items[$i - 1]->position == $item->position), 'modules.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
										<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, (@$this->items[$i + 1]->position == $item->position), 'modules.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
									<?php elseif ($listDirn == 'desc') : ?>
										<span><?php echo $this->pagination->orderUpIcon($i, (@$this->items[$i - 1]->position == $item->position), 'modules.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
										<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, (@$this->items[$i + 1]->position == $item->position), 'modules.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
									<?php endif; ?>
								<?php endif; ?>
								<?php $disabled = $saveOrder ? '' : 'disabled="disabled"'; ?>
								<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" <?php echo $disabled ?>
									class="text-area-order" />
							<?php else : ?>
								<?php echo $item->ordering; ?>
							<?php endif; ?>
						</td>
						<td class="left">
							<?php echo $item->name; ?>
						</td>
						<td class="center">
							<?php echo $item->pages; ?>
						</td>

						<td class="center">
							<?php echo $this->escape($item->access_level); ?>
						</td>
						<td class="center">
							<?php if ($item->language == ''): ?>
								<?php echo JText::_('JDEFAULT'); ?>
							<?php elseif ($item->language == '*'): ?>
								<?php echo JText::alt('JALL', 'language'); ?>
							<?php
							else: ?>
								<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
							<?php endif; ?>
						</td>
						<td class="center">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php //Load the batch processing form if user is allowed ?>
		<?php if ($user->authorize('core.create', 'com_advancedmodules') || $user->authorize('core.edit', 'com_advancedmodules')) : ?>
			<?php echo $this->loadTemplate('batch'); ?>
		<?php endif; ?>

		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="setcolor" value="" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>

<?php if ($this->config->show_switch) : ?>
	<div style="text-align:right">
		<a href="<?php echo JRoute::_('index.php?option=com_modules&force=1'); ?>"><?php echo JText::_('AMM_SWITCH_TO_CORE'); ?></a>
	</div>
<?php endif; ?>
<?php
// PRO Check
require_once JPATH_PLUGINS . '/system/nnframework/helpers/licenses.php';
echo NNLicenses::getInstance()->getMessage('ADVANCED_MODULE_MANAGER', 0);

// Copyright
echo NNVersions::getInstance()->getCopyright('ADVANCED_MODULE_MANAGER', '', 10307, 'advancedmodules', 'component');

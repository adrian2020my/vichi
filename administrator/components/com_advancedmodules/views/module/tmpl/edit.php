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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.combobox');
$hasContent = empty($this->item->module) || $this->item->module == 'custom' || $this->item->module == 'mod_custom';

$script = "
Joomla.submitbutton = function(task)
{
	if (task == 'module.cancel' || document.formvalidator.isValid(document.id('module-form'))) {";
if ($hasContent)
{
	$script .= $this->form->getField('content')->save();
}
$script .= "		var f = document.getElementById('module-form');
		if (self != top) {
			if ( task == 'module.cancel' || task == 'module.save' ) {
				f.target = '_top';
			} else {
				f.action += '&tmpl=component';
			}
		}
		Joomla.submitform(task, f);
	} else {
		alert('" . $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')) . "');
	}
}
";
if (JFactory::getUser()->authorise('core.admin'))
{
	$script .= "
window.addEvent('domready', function() {
	document.getElements('button.nn_remove_assignment').each(function(el)
	{
		el.addEvent('click', function()
		{
			if(confirm('" . str_replace('<br />', '\n', addslashes(JText::_('AMM_DISABLE_ASSIGNMENT'))) . "')) {
				document.getElement('li#toolbar-popup-options a').click();
			}
		});
	});
});
";
}

if ($this->config->show_color)
{
	$colors = explode(',', $this->config->main_colors);
	foreach ($colors as $i => $c)
	{
		$colors[$i] = strtoupper('#' . preg_replace('#[^a-z0-9]#i', '', $c));
	}
	$script .= "
		mainColors = ['" . implode("', '", $colors) . "'];";
}

JFactory::getDocument()->addScriptDeclaration($script);
JHtml::script('nnframework/script.min.js', false, true);
JHtml::script('nnframework/toggler.min.js', false, true);

$tmpl = JFactory::getApplication()->input->get('tmpl');
if ($tmpl == 'component') : ?>
<?php
JFactory::getDocument()->addStyleDeclaration('html{ overflow-y: auto !important; }body{ overflow-y: auto !important; }');
$bar = JToolBar::getInstance('toolbar');
$bar = str_replace('href="#"', 'href="javascript://"', $bar->render());
?>
	<div id="toolbar-box">
		<div class="m">
			<?php echo $bar; ?>
			<?php echo JFactory::getApplication()->get('JComponentTitle'); ?>
		</div>
	</div>
<div id="element-box">
	<div class="m">
		<?php endif; ?>
		<form
			action="<?php echo JRoute::_('index.php?option=com_advancedmodules&layout=edit&id=' . (int) $this->item->id); ?>"
			method="post" name="adminForm" id="module-form" class="form-validate">

			<?php
			if ($this->config->layout != 'slides')
			{
				echo JHtml::_('tabs.start', 'advancedmodules-tabs', array('useCookie' => 1));
				echo JHtml::_('tabs.panel', JText::_('COM_MODULES_BASIC_FIELDSET_LABEL'), 'tab-basic');
			}
			?>
			<div class="width-50 fltlft">
				<fieldset class="adminform">
					<legend><?php echo JText::_('JDETAILS'); ?></legend>
					<ul class="adminformlist">
						<li><?php echo $this->form->getLabel('title'); ?>
							<?php echo $this->form->getInput('title'); ?></li>

						<li><?php echo $this->form->getLabel('showtitle'); ?>
							<?php echo $this->form->getInput('showtitle'); ?></li>

						<li><?php echo $this->form->getLabel('position'); ?>
							<?php echo $this->form->getInput('position'); ?></li>

						<?php if ((string) $this->item->xml->name != 'Login Form'): ?>
							<li><?php echo $this->form->getLabel('published'); ?>
								<?php echo $this->form->getInput('published'); ?></li>
						<?php endif; ?>

						<?php if ($this->item->client_id == 0 && $this->config->show_hideempty) : ?>
							<?php echo $this->render($this->assignments, 'hideempty'); ?>
						<?php endif; ?>

						<li><?php echo $this->form->getLabel('access'); ?>
							<?php echo $this->form->getInput('access'); ?></li>

						<li><?php echo $this->form->getLabel('ordering'); ?>
							<?php echo $this->form->getInput('ordering'); ?></li>

						<?php if ($this->item->client_id != 0): ?>
							<?php if ((string) $this->item->xml->name != 'Login Form'): ?>
								<li><?php echo $this->form->getLabel('publish_up'); ?>
									<?php echo $this->form->getInput('publish_up'); ?></li>

								<li><?php echo $this->form->getLabel('publish_down'); ?>
									<?php echo $this->form->getInput('publish_down'); ?></li>
							<?php endif; ?>

							<li><?php echo $this->form->getLabel('language'); ?>
								<?php echo $this->form->getInput('language'); ?></li>
						<?php endif; ?>

						<li><?php echo $this->form->getLabel('note'); ?>
							<?php echo $this->form->getInput('note'); ?></li>

						<?php if ($this->config->show_color) : ?>
							<?php echo $this->render($this->assignments, 'color'); ?>
						<?php endif; ?>

						<?php if ($this->item->id) : ?>
							<li><?php echo $this->form->getLabel('id'); ?>
								<?php echo $this->form->getInput('id'); ?></li>
						<?php endif; ?>

						<li><?php echo $this->form->getLabel('module'); ?>
							<?php echo $this->form->getInput('module'); ?>
							<input type="text" size="35" value="<?php if ($this->item->xml)
							{
								echo ($text = (string) $this->item->xml->name) ? JText::_($text) : $this->item->module;
							}
							else
							{
								echo JText::_('COM_MODULES_ERR_XML');
							}?>" class="readonly" readonly="readonly" /></li>

						<li><?php echo $this->form->getLabel('client_id'); ?>
							<input type="text" size="35"
								value="<?php echo $this->item->client_id == 0 ? JText::_('JSITE') : JText::_('JADMINISTRATOR'); ?>	"
								class="readonly" readonly="readonly" />
							<?php echo $this->form->getInput('client_id'); ?></li>
					</ul>
					<div class="clr"></div>
					<?php if ($this->item->xml) : ?>
						<?php if ($text = trim($this->item->xml->description)) : ?>
							<label>
								<?php echo JText::_('COM_MODULES_MODULE_DESCRIPTION'); ?>
							</label>
							<span class="readonly mod-desc"><?php echo JText::_($text); ?></span>
						<?php endif; ?>
					<?php else : ?>
						<p class="error"><?php echo JText::_('COM_MODULES_ERR_XML'); ?></p>
					<?php endif; ?>
					<div class="clr"></div>
				</fieldset>
			</div>

			<div class="width-50 fltrt">
				<?php if ($this->config->layout == 'slides') : ?>
					<?php
					echo JHtml::_('sliders.start', 'module-sliders');
					echo $this->loadTemplate('options');
					if ($this->item->client_id == 0)
					{
						echo $this->loadTemplate('assignment');
					}
					echo JHtml::_('sliders.panel', JText::_('JCONFIG_PERMISSIONS_LABEL'), 'permissions');
					?>
					<div class="width-100 fltlft">
						<fieldset class="adminform">
							<?php
							echo $this->form->getLabel('rules');
							echo $this->form->getInput('rules');
							?>
						</fieldset>
					</div>
					<?php echo JHtml::_('sliders.end'); ?>
				<?php else : ?>
					<?php echo $this->loadTemplate('basic'); ?>
				<?php endif; ?>
			</div>

			<?php if ($hasContent) : ?>
				<div class="width-100 fltlft">
					<fieldset class="adminform">
						<legend><?php echo JText::_('COM_MODULES_CUSTOM_OUTPUT'); ?></legend>
						<ul class="adminformlist">
							<div class="clr"></div>
							<li><?php echo $this->form->getLabel('content'); ?>
								<div class="clr"></div>
								<?php echo $this->form->getInput('content'); ?></li>
						</ul>
					</fieldset>
				</div>
			<?php endif; ?>

			<div class="clr"></div>

			<?php if ($this->config->layout != 'slides') : ?>
				<?php echo $this->loadTemplate('options'); ?>

				<?php if ($this->item->client_id == 0) : ?>
					<?php echo $this->loadTemplate('assignment'); ?>
				<?php endif; ?>

				<?php if (JFactory::getUser()->authorise('core.admin', 'com_advancedmodules')) : ?>
					<?php echo JHtml::_('tabs.panel', JText::_('JCONFIG_PERMISSIONS_LABEL'), 'tab-permissions'); ?>

					<div class="width-100 fltlft">
						<fieldset class="adminform">
							<?php
							echo $this->form->getLabel('rules');
							echo $this->form->getInput('rules');
							?>
						</fieldset>
					</div>
					<div class="clr"></div>
				<?php endif; ?>

				<?php echo JHtml::_('tabs.end'); ?>
			<?php endif; ?>

			<div>
				<input type="hidden" name="task" value="" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</form>

		<?php if ($this->config->show_switch) : ?>
		<div style="text-align:right"><a href="<?php echo JRoute::_('index.php?option=com_modules&force=1&task=module.edit&id=' . (int) $this->item->id); ?>"><?php echo JText::_('AMM_SWITCH_TO_CORE'); ?></a></div>
		<?php endif; ?>

		<?php if ($tmpl == 'component') : ?>
	</div>
</div>
<?php endif; ?>

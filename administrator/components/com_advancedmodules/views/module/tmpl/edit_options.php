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

$fieldSets = $this->form->getFieldsets('params');

foreach ($fieldSets as $name => $fieldSet) :
	$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_MODULES_' . $name . '_FIELDSET_LABEL';
	if ($this->config->layout == 'slides')
	{
		echo JHtml::_('sliders.panel', JText::_($label), $name . '-options');
	}
	else
	{
		if (in_array($name, array('basic', 'NSP_BASIC_SETTINGS')))
		{
			continue;
		}
		echo JHtml::_('tabs.panel', JText::_($label), 'tab-' . $name);
	}
	if (isset($fieldSet->description) && trim($fieldSet->description)) :
		echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
	endif;
	?>
	<div class="width-100 fltlft">
		<fieldset class="adminform">
			<ul class="adminformlist">
				<?php foreach ($this->form->getFieldset($name) as $field) : ?>
					<li>
						<?php if (!$field->hidden) : ?>
							<?php echo $field->label; ?>
						<?php endif; ?>
						<?php echo $field->input; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>
	</div>
	<div class="clr"></div>
<?php endforeach; ?>

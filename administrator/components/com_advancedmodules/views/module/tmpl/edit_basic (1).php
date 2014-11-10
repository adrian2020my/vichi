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

defined('_JEXEC') or die;

$fieldSets = $this->form->getFieldsets('params');

if (isset($fieldSets['basic'])) : ?>
	<?php
	$name = 'basic';
	$fieldSet = $fieldSets[$name];
	if (isset($fieldSet->description) && trim($fieldSet->description)) :
		echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
	endif;
	?>
	<fieldset class="adminform">
		<legend><?php echo JText::_('JOPTIONS'); ?></legend>
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
	<div class="clr"></div>
<?php endif; ?>

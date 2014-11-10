<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2014 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$this->fieldsClass->prefix = '';
$displayTitle = false;
ob_start();
foreach ($this->fields as $fieldName => $oneExtraField) {
	$value = '';
	if(empty($this->element->$fieldName) && !empty($this->element->main->$fieldName))$this->element->$fieldName = $this->element->main->$fieldName;
	if(isset($this->element->$fieldName))
		$value = trim($this->element->$fieldName);

	if (!empty ($value) || $value == 0) {
		$displayTitle = true;
	?>
	<dt>
		<span id="hikashop_product_custom_name_<?php echo $oneExtraField->field_id;?>" class="hikashop_product_custom_name">
			<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
		</span>
	</dt>
	<dd>
		<span id="hikashop_product_custom_value_<?php echo $oneExtraField->field_id;?>" class="hikashop_product_custom_value">
			<?php echo $this->fieldsClass->show($oneExtraField,$value); ?>
		</span>
	</dd>
	<?php
	}
}
$specifFields = ob_get_clean();
if($displayTitle){
?>

<div id="hikashop_product_custom_info_main" class="hikashop_product_custom_info_main">
	<h4><?php echo JText::_('SPECIFICATIONS');?></h4>
	<dl class="hikashop_product_custom_info">
		<?php echo $specifFields; ?>
	</dl>
</div>
<?php }

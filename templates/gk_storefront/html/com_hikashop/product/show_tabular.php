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


	hikashop_loadJslib('jquery');
	$js = '';
	$params = null;
	$this->params->set('vote_type','product');
	if(isset($this->element->main)){
		$product_id = $this->element->main->product_id;
	}else{
		$product_id = $this->element->product_id;
	}
	$this->params->set('vote_ref_id',$product_id);
	$this->params->set('productlayout','show_tabular');
	$layout_vote_mini = hikashop_getLayout('vote', 'mini', $this->params, $js);
	$layout_vote_listing = hikashop_getLayout('vote', 'listing', $this->params, $js);
	$layout_vote_form = hikashop_getLayout('vote', 'form', $this->params, $js);
	$config =& hikashop_config();
	$status_vote = $config->get('enable_status_vote');
	$hide_specs = 1;
	if($this->element->product_weight != 0 || $this->element->product_width != 0 || $this->element->product_height != 0 || $this->element->product_length != 0 || @$this->element->main->product_weight != 0 || @$this->element->main->product_width != 0 || @$this->element->main->product_height != 0 || @$this->element->main->product_length != 0)
		$hide_specs = 0;
	foreach ($this->fields as $fieldName => $oneExtraField) {
		$value = '';
		if(empty($this->element->$fieldName) && !empty($this->element->main->$fieldName))$this->element->$fieldName = $this->element->main->$fieldName;
		if(isset($this->element->$fieldName))
			$value = trim($this->element->$fieldName);
		if(!empty($value))
			$hide_specs = 0;
	}
?>

<div id="hikashop_product_left_part" class="hikashop_product_left_part">
	<?php
	if(!empty($this->element->extraData->leftBegin)) { echo implode("\r\n",$this->element->extraData->leftBegin); }

	$this->row = & $this->element;
	$this->setLayout('show_block_img');
	echo $this->loadTemplate();
	?>
	
	<?php if(!empty($this->element->extraData->leftEnd)) { echo implode("\r\n",$this->element->extraData->leftEnd); } ?>
</div>
<div id="hikashop_product_right_part" class="hikashop_product_right_part">
	<h1>
		<?php
			if (hikashop_getCID('product_id')!=$this->element->product_id && isset ($this->element->main->product_name))
				echo $this->element->main->product_name;
			else
				echo $this->element->product_name;
		?>
		<?php if ($this->config->get('show_code')) { ?>
		<small id="hikashop_product_code_main" class="hikashop_product_code_main">
			<?php
			echo $this->element->product_code;
			?>
		</small>
		<?php } ?>
	</h1>
	
	<div id="hikashop_product_vote_mini" class="hikashop_product_vote_mini">
		<?php
		if($this->params->get('show_vote_product') == '-1'){
			$this->params->set('show_vote_product',$config->get('show_vote_product'));
		}
		if($this->params->get('show_vote_product')){
			echo $layout_vote_mini;
		}
		?>
	</div>
	
	<?php if(!empty($this->element->product_description)) : ?>
	<div id="hikashop_product_description_main_mini" class="hikashop_product_description_main_mini">
		<?php
			$resume = substr(strip_tags(preg_replace('#<hr *id="system-readmore" */>.*#is','',$this->element->product_description)),0,150);
			if (!empty($this->element->product_description) && strlen($this->element->product_description)>150)
				$resume .= " &hellip; <a href='#hikashop_show_tabular_description'>".JText::_('READ_MORE')."</a>";
			echo JHTML::_('content.prepare',$resume);
		?>
	</div>
	<?php endif; ?>
	
	<?php
	$pluginsClass = hikashop_get('class.plugins');
	$plugin = $pluginsClass->getByName('content', 'hikashopsocial');
	if (@ $plugin->published || @ $plugin->enabled) {
		echo '{hikashop_social}';
	}
	?>
	
	<?php
	if(!empty($this->element->extraData->rightBegin))
		echo implode("\r\n",$this->element->extraData->rightBegin);
	?>
	<span id="hikashop_product_price_main" class="hikashop_product_price_main">
		<?php
		if ($this->params->get('show_price')) {
			$this->row = & $this->element;
			$this->setLayout('listing_price');
			echo $this->loadTemplate();
		}
		?>
	</span>
	<?php
	if($this->params->get('characteristic_display')!='list'){
		$this->setLayout('show_block_characteristic');
		echo $this->loadTemplate();
		?>
	
		<?php
	}
	$form = ',0';
	if (!$this->config->get('ajax_add_to_cart', 1)) {
		$form = ',\'hikashop_product_form\'';
	}
	if (hikashop_level(1) && !empty ($this->element->options)) {
	?>
		<div id="hikashop_product_options" class="hikashop_product_options">
			<?php
			$this->setLayout('option');
			echo $this->loadTemplate();
			?>
		</div>

		<?php
		$form = ',\'hikashop_product_form\'';
		if ($this->config->get('redirect_url_after_add_cart', 'stay_if_cart') == 'ask_user') {
		?>
			<input type="hidden" name="popup" value="1"/>
		<?php
		}
	}
	if (!$this->params->get('catalogue') && ($this->config->get('display_add_to_cart_for_free_products') || !empty ($this->element->prices))) {
		if (!empty ($this->itemFields)) {
			$form = ',\'hikashop_product_form\'';
			if ($this->config->get('redirect_url_after_add_cart', 'stay_if_cart') == 'ask_user') {
			?>
				<input type="hidden" name="popup" value="1"/>
			<?php
			}
			$this->setLayout('show_block_custom_item');
			echo $this->loadTemplate();
		}
	}
	$this->formName = $form;
	if($this->params->get('show_price')){ ?>
		<span id="hikashop_product_price_with_options_main" class="hikashop_product_price_with_options_main">
		</span>
	<?php } ?>

	<?php
	if(!empty($this->element->extraData->rightMiddle))
		echo implode("\r\n",$this->element->extraData->rightMiddle);
	?>

	<span id="hikashop_product_id_main" class="hikashop_product_id_main">
		<input type="hidden" name="product_id" value="<?php echo $this->element->product_id; ?>" />
	</span>
	
	<?php $contact = $this->config->get('product_contact',0); ?>
	<?php if (hikashop_level(1) && ($contact == 2 || ($contact == 1 && !empty ($this->element->product_contact)))) :?>
		<div id="hikashop_product_contact_main" class="hikashop_product_contact_main">
		<?php
			$empty = '';
			$params = new HikaParameter($empty);
			global $Itemid;
			$url_itemid='';
			if(!empty($Itemid)){
				$url_itemid='&Itemid='.$Itemid;
			}
			echo '<a href="'.hikashop_completeLink('product&task=contact&cid=' . $this->element->product_id.$url_itemid).'" onclick="' . 'window.location=\'' . hikashop_completeLink('product&task=contact&cid=' . $this->element->product_id.$url_itemid) . '\';return false;">' . JText :: _('CONTACT_US_FOR_INFO') . '</a>';
		?>
		</div>
	<?php endif; ?>
	
	<?php if(empty ($this->element->characteristics) || $this->params->get('characteristic_display')!='list'){ ?>
		<div id="hikashop_product_quantity_main" class="hikashop_product_quantity_main">
			<?php
			$this->row = & $this->element;
			$this->ajax = 'if(hikashopCheckChangeForm(\'item\',\'hikashop_product_form\')){ return hikashopModifyQuantity(\'' . $this->row->product_id . '\',field,1' . $form . ',\'cart\'); } else { return false; }';
			$this->setLayout('quantity');
			echo $this->loadTemplate();
			?>
		</div>
	<?php
	}
	$this->setLayout('show_block_product_files');
	echo $this->loadTemplate();
	?>
	<?php
	if(!empty($this->element->extraData->rightEnd))
		echo implode("\r\n",$this->element->extraData->rightEnd);
	?>
</div>

	<input type="hidden" name="cart_type" id="type" value="cart"/>
	<input type="hidden" name="add" value="1"/>
	<input type="hidden" name="ctrl" value="product"/>
	<input type="hidden" name="task" value="updatecart"/>
	<input type="hidden" name="return_url" value="<?php echo urlencode(base64_encode(urldecode($this->redirect_url)));?>"/>
</form>
<div id="hikashop_product_bottom_part" class="hikashop_product_bottom_part show_tabular">
	<div id="hikashop_tabs_div">
		<ul id="product-tabs" class="hikashop_tabs_ul">
			<li id="hikashop_show_tabular_description_li" onclick="displayTab('hikashop_show_tabular_description');"><?php echo JText::_('PRODUCT_DESCRIPTION');?></li>
			<?php if($hide_specs == 0){ ?>
			<li id="hikashop_show_tabular_specification_li" onclick="displayTab('hikashop_show_tabular_specification');"><?php echo JText::_('SPECIFICATIONS');?></li>
			<?php }
			if($status_vote == "comment" || $status_vote == "two" || $status_vote == "both" ){
			?>
			<li id="hikashop_show_tabular_comment_li" onclick="displayTab('hikashop_show_tabular_comment');"><?php echo JText::_('PRODUCT_COMMENT');?></li>
			<li id="hikashop_show_tabular_new_comment_li" onclick="displayTab('hikashop_show_tabular_new_comment');"><?php echo JText::_('PRODUCT_NEW_COMMENT');?></li>
			<?php } ?>
		</ul>
		<?php
		if(!empty($this->element->extraData->bottomBegin))
			echo implode("\r\n",$this->element->extraData->bottomBegin);
		?>
		<div class="hikashop_tabs_content" id="hikashop_show_tabular_description">
			<div id="hikashop_product_description_main" class="hikashop_product_description_main">
				<?php
				echo JHTML::_('content.prepare',preg_replace('#<hr *id="system-readmore" */>#i','',$this->element->product_description));
				?>
			</div>
			<span id="hikashop_product_url_main" class="hikashop_product_url_main">
				<?php
				if (!empty ($this->element->product_url)) {
					echo JText :: sprintf('MANUFACTURER_URL', '<a href="' . $this->element->product_url . '" target="_blank">' . $this->element->product_url . '</a>');
				}
				?>
			</span>
		</div>
		<?php if($hide_specs == 0){ ?>
		<div class="hikashop_tabs_content" id="hikashop_show_tabular_specification">
		<?php
			$this->setLayout('show_block_dimensions');
			echo $this->loadTemplate();
			if(!empty($this->fields)){
				$this->setLayout('show_block_custom_main');
				echo $this->loadTemplate();
			}
		?>
		</div>
		<?php }
if($status_vote == "comment" || $status_vote == "two" || $status_vote == "both" ){ ?>
<form action="<?php echo hikashop_currentURL() ?>" method="post" name="hikashop_comment_form" id="hikashop_comment_form">
		<?php
		if(!empty($this->element->extraData->bottomMiddle))
			echo implode("\r\n",$this->element->extraData->bottomMiddle);
		?>
			<div class="hikashop_tabs_content" id="hikashop_show_tabular_comment">
				<div id="hikashop_product_vote_listing" class="hikashop_product_vote_listing">
					<?php
						echo $layout_vote_listing;
					?>
				</div>
			</div>
			<div class="hikashop_tabs_content" id="hikashop_show_tabular_new_comment">
				<div id="hikashop_product_vote_form" class="hikashop_product_vote_form">
					<?php
						echo $layout_vote_form;
					?>
				</div>
			</div>
</form>
<?php } ?>
<input type="hidden" name="selected_tab" id="selected_tab" value="hikashop_show_tabular_description"/>
		<?php
		if(!empty($this->element->extraData->bottomEnd))
			echo implode("\r\n",$this->element->extraData->bottomEnd);
		?>
	</div>
</div>
<script type="text/javascript">
if(!hkjQuery) window.hkjQuery = window.jQuery;
(function($) {
	var selectedTab = $( "#selected_tab" ).val();
	displayTab(selectedTab,1);
})(hkjQuery);

function displayTab(id, load){
	var oldTab = hkjQuery( "#selected_tab" ).val();
	if(id != oldTab || load !== undefined){
		hkjQuery( "#"+oldTab ).css('display','none');
		hkjQuery( "#"+id ).css('display','inherit');
		hkjQuery( "#"+oldTab+"_li" ).removeClass('hikashop_tabs_li_selected');
		hkjQuery( "#"+id+"_li" ).addClass('hikashop_tabs_li_selected');
		hkjQuery( "#selected_tab" ).val(id);
	}
}
</script>

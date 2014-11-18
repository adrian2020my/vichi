<?php
/**
*
* Show the products in a category
*
* @package	VirtueMart
* @subpackage
* @author RolandD
* @author Max Milbers
* @todo add pagination
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default.php 5120 2011-12-18 18:29:26Z electrocity $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
JHTML::_( 'behavior.modal' );
/* javascript for list Slide
  Only here for the order list
  can be changed by the template maker */
$js = "jQuery(document).ready(function () {
	jQuery('.orderlistcontainer').hover(
		function() { jQuery(this).find('.orderlist').stop().show()},
		function() { jQuery(this).find('.orderlist').stop().hide()}
	)
});";

$document = JFactory::getDocument(); 
$document->addScriptDeclaration($js);
//var_dump($this->category);
?>

<?php
/* Show child categories */
if ( VmConfig::get('showCategory',1) and empty($this->keyword)) {
		if (!empty($this->category->haschildren)) {
		// Category and Columns Counter
		$iCol = 1;
		$iCategory = 1;
		// Calculating Categories Per Row
		$categories_per_row = VmConfig::get ( 'categories_per_row', 3 );
		$category_cellwidth = ' width'.floor ( 100 / $categories_per_row );
		$BrowseTotalProducts = count($this->products);
		// Separator
		$verticalseparator = " vertical-separator";
	?>
	<div class="category-view">
	
		<?php // Start the Output
		$modules = JModuleHelper::getModules('banner_catview');        
        foreach($modules as $mod)
        {
        	echo JModuleHelper::renderModule($mod);
        }
		if(!empty($this->category->children)) {
		foreach ( $this->category->children as $category ) { ?>
		<?php if ($iCol == 1 && $iCategory > $categories_per_row) : ?>
		<div class="horizontal-separator"></div>
		<?php endif; ?>
		
		<?php if ($iCol == 1) : ?>
		<div class="row">
		<?php endif; ?>
				<?php
			// Show the vertical seperator
			if ($iCategory == $categories_per_row or $iCategory % $categories_per_row == 0) {
				$show_vertical_separator = ' ';
			} else {
				$show_vertical_separator = $verticalseparator;
			}

			// Category Link
			$caturl = JRoute::_ ( 'index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $category->virtuemart_category_id );

				// Show Category ?>
				<div class="category floatleft<?php echo $category_cellwidth . $show_vertical_separator ?>">
					<div class="spacer">
						<a href="<?php echo $caturl ?>" title="<?php echo $category->category_name ?>"><?php echo $category->images[0]->displayMediaThumb("",false); ?></a>
						
						<h2 class="catSub"> <a href="<?php echo $caturl ?>" title="<?php echo $category->category_name ?>"> <?php echo $category->category_name ?> </a> </h2>
					</div>
				</div>
				<?php
			$iCategory ++;

		// Do we need to close the current row now?
		if ($iCol == $categories_per_row) { ?>
				<div class="clear"></div>
		</div>
		<?php
			$iCol = 1;
		} else {
			$iCol ++;
		}
	}
	}
	// Do we need a final closing row tag?
	if ($iCol != 1) { ?>
		<div class="clear"></div>
</div>
<?php } ?>
</div>
<?php }
}

?>

<div class="browse-view">

<?php
/**		 $modules = JModuleHelper::getModules('custom_position');        
        foreach($modules as $mod)
        {
        	echo JModuleHelper::renderModule($mod);
        } **/
    ?>
<?php
 		$modules = JModuleHelper::getModules('banner1');        
        foreach($modules as $mod)
        {
        	echo JModuleHelper::renderModule($mod);
        }

	// Show child categories
	if (!empty($this->products)) {
		if (!empty($this->keyword)) {
			?>
	<h1><?php echo $this->keyword; ?></h1>
	<?php
		}
		?>
	<?php // Category and Columns Counter
	$iBrowseCol = 1;
	$iBrowseProduct = 1;
	
	// Calculating Products Per Row
	$BrowseProducts_per_row = $this->perRow;
	$Browsecellwidth = ' width'.floor ( 100 / $BrowseProducts_per_row );
	
	// Separator
	$verticalseparator = " vertical-separator";
?>

		<?php if(!empty($this->category->category_name)) : ?>
		<h1><?php echo $this->category->category_name; ?></h1>
		<?php endif; ?>
		
		<?php if (empty($this->keyword) && !empty($this->category) && !empty($this->category->category_description)) : ?>
		<p class="category_description">
			<?php echo $this->category->category_description; ?>
		</p>
		<?php endif; ?>
		
		<form action="<?php echo JRoute::_ ('index.php?option=com_virtuemart&view=category&limitstart=0&virtuemart_category_id=' . $this->category->virtuemart_category_id, FALSE); ?>" method="get">
				<?php if (!empty($this->products)) : ?>
				<div class="orderby-displaynumber"><?php echo $this->orderByList['orderby']; ?>
						<div class="display-number"><?php echo $this->vmPagination->getResultsCounter();?> <?php echo $this->vmPagination->getLimitBox ($this->category->limit_list_step); ?></div>
						
				</div>
				<?php endif ?>
				
				<?php if (!empty($this->keyword)) {
				
				$category_id  = JRequest::getInt ('virtuemart_category_id', 0); ?>
				<!--BEGIN Search Box -->
				<!--<div class="virtuemart_search"> <?php echo $this->searchcustom ?> <br />
						<?php echo $this->searchcustomvalues ?>
						<input style="height:16px;vertical-align :middle;" name="keyword" class="inputbox" type="text" size="20" value="<?php echo $this->keyword ?>" />
						<input type="submit" value="<?php echo JText::_('COM_VIRTUEMART_SEARCH') ?>" class="button" onclick="this.form.keyword.focus();"/>
				</div>
				<input type="hidden" name="search" value="true" />
				<input type="hidden" name="view" value="category" />
				<input type="hidden" name="option" value="com_virtuemart"/>
				<input type="hidden" name="virtuemart_category_id" value="<?php echo $category_id; ?>"/>-->
				<!-- End Search Box -->
				<?php } ?>
		</form>
		<?php // Start the Output
foreach ( $this->products as $product ) {

	// Show the horizontal seperator
	if ($iBrowseCol == 1 && $iBrowseProduct > $BrowseProducts_per_row) { ?>
		<div class="horizontal-separator"></div>
		<?php }

	// this is an indicator wether a row needs to be opened or not
	if ($iBrowseCol == 1) { ?>
		<div class="row">
				<?php }

	// Show the vertical seperator
	if ($iBrowseProduct == $BrowseProducts_per_row or $iBrowseProduct % $BrowseProducts_per_row == 0) {
		$show_vertical_separator = ' ';
	} else {
		$show_vertical_separator = $verticalseparator;
	}
		// Show Products ?>
		<div class="product floatleft<?php echo $Browsecellwidth . $show_vertical_separator ?>">
			<div class="spacer">
				<div>
					<a title="<?php echo $product->product_name ?>" href="<?php echo $product->link; ?>">
						<?php
							echo $product->images[0]->displayMediaThumb('class="browseProductImage"', false);
						?>
					 </a>
				</div>
				
				<div>
					<h3 class="catProductTitle"><?php echo JHTML::link($product->link, $product->product_name); ?></h3>
					
					<div class="catProductPrice" id="productPrice<?php echo $product->virtuemart_product_id ?>">
					  <!--      <?php
                      if ($this->show_prices == '1') {
                         if( $product->prices['discountedPriceWithoutTax'] ):
                            echo '<span style="text-decoration: line-through;">' . $this->currency->createPriceDiv('basePriceWithTax', '', $product->prices) . '</span>'
                            . 'RM ' . round( (int)$product->prices['discountedPriceWithoutTax'], 2 );
                         else:
                            if ($product->prices['salesPrice']<=0 and VmConfig::get ('askprice', 1) and  !$product->images[0]->file_is_downloadable) {
                               echo JText::_ ('COM_VIRTUEMART_PRODUCT_ASKPRICE');
                            }
                            echo $this->currency->createPriceDiv('basePriceWithTax', '', $product->prices);
                            echo $this->currency->createPriceDiv('taxAmount','TPL_GK_LANG_VM_INC_TAX', $product->prices);
                         endif;
                      } ?> -->

						<?php
						if ($this->show_prices == '1') {
							if ($product->prices['salesPrice']<=0 and VmConfig::get ('askprice', 1) and  !$product->images[0]->file_is_downloadable) {
								echo JText::_ ('COM_VIRTUEMART_PRODUCT_ASKPRICE');
							} else if ($product->product_override_price > 0 && $product->product_discount_id != 0){
            	
            			echo 'Sales Price : '. '<span style="text-decoration: line-through;">' . 'RM ' . number_format($product->product_price, 2 , '.','')  . '</span>'
                        . '<span>' .'  RM ' . number_format($product->product_override_price, 2, '.' , '' ) . '</span>';
                      
                  } else {
							echo $this->currency->createPriceDiv ('salesPrice', 'COM_VIRTUEMART_PRODUCT_SALESPRICE', $product->prices);
						}
						} ?> 
					</div>
					
					<?php if ( VmConfig::get ('display_stock', 1)) : ?>
					<div class="stockLavel"> <span class="vmicon vm2-<?php echo $product->stock->stock_level ?>" title="<?php echo $product->stock->stock_tip ?>"></span> <span class="stock-level"><?php echo JText::_('COM_VIRTUEMART_STOCK_LEVEL_DISPLAY_TITLE_TIP') ?></span> </div>
					<?php endif; ?>
				</div>
				<div class="clear"> </div>
<div class="width100">  
<?php // Add To Cart Button
  if (!VmConfig::get('use_as_catalog', 0) and !empty($product->prices)) {?>
<div class="addtocart-area"><form method="post" class="product js-recalculate" action="index.php">
  <?php // Product custom_fields
  if (!empty($product->customfieldsCart)) {  ?>
<div class="product-fields">
  <?php foreach ($product->customfieldsCart as $field)
  { ?>
<div style="text-align: left;" class="product-field product-field-type-<?php echo $field->field_type ?>">
  <span class="product-fields-title"><b><?php echo  JText::_($field->custom_title) ?></b></span>
  <?php if ($field->custom_tip) echo JHTML::tooltip($field->custom_tip,  JText::_($field->custom_title), 'tooltip.png'); ?>
 
       <span class="product-field-display"><?php echo $field->display ?></span>
 
  <span class="product-field-desc"><?php echo $field->custom_field_desc ?></span>
  </div>
 
  <?php
    }
  ?>
  </div>
  <?php }
  /* Product custom Childs
   * to display a simple link use $field->virtuemart_product_id as link to child product_id
   * custom_value is relation value to child
  */
 
  if (!empty($product->customsChilds)) {  ?>
<div class="product-fields">
  <?php foreach ($product->customsChilds as $field) {  ?>
<div style="display: inline-block; float: right; padding: 3px;" class="product-field product-field-type-<?php echo $field->field->field_type ?>">
  <span class="product-fields-title"><b><?php echo JText::_($field->field->custom_title) ?></b></span>
  <span class="product-field-desc"><?php echo JText::_($field->field->custom_value) ?></span>
  <span class="product-field-display"><?php echo $field->display ?></span>
 
  </div>
 
  <?php } ?>
  </div>
  <?php } ?>

<div class="addtocart-bar">
 <!--
  <?php // Display the quantity box ?>
    <label for="quantity<?php echo $product->virtuemart_product_id;?>" class="quantity_box"><?php echo JText::_('COM_VIRTUEMART_CART_QUANTITY'); ?>: </label>
 
    <span class="quantity-box">
        <input type="text" class="quantity-input js-recalculate" name="quantity[]" value="<?php if (isset($product->min_order_level) && (int)$product->min_order_level > 0) {
            echo $product->min_order_level;
        } else {
            echo '1';
        } ?>"/>
        </span>
                <span class="quantity-controls js-recalculate">
        <input type="button" class="quantity-controls quantity-plus"/>
        <input type="button" class="quantity-controls quantity-minus"/>
        </span>-->
  <!-- Display the quantity box END -->
  <input type="hidden" class="quantity-input js-recalculate" name="quantity[]" value="<?php if (isset($product->min_order_level) && (int)$product->min_order_level > 0) {
            echo $product->min_order_level;
        } else {
            echo '1';
        } ?>" />
 
  <?php // Add the button
  $button_lbl = JText::_('COM_VIRTUEMART_CART_ADD_TO');
  $button_cls = 'addtocart-button'; //$button_cls = 'addtocart_button';
  $button_name = 'addtocart'; //$button_cls = 'addtocart_button';
 
 
  // Display the add to cart button
  $stockhandle = VmConfig::get('stockhandle','none');
  if(($stockhandle=='disableit' or $stockhandle=='disableadd') and ($product->product_in_stock - $product->product_ordered)<1){
  $button_lbl = JText::_('COM_VIRTUEMART_CART_NOTIFY');
  $button_cls = 'notify-button';
  $button_name = 'notifycustomer';
  }
  vmdebug('$stockhandle '.$stockhandle.' and stock '.$product->product_in_stock.' ordered '.$product->product_ordered);
  ?>
  <span class="addtocart-button">
  <?php if ($button_cls == "notify-button") { ?>
         <span class="outofstock"><?php echo JText::_('COM_VIRTUEMART_CART_PRODUCT_OUT_OF_STOCK'); ?></span>
 
           <?php } else {?>
           <input name="<?php echo $button_name ?>" class="<?php echo $button_cls ?>" value="<?php echo $button_lbl ?>" title="<?php echo $button_lbl ?>" type="submit" />
        <?php } ?>
  </span>
<div class="clear"> </div>
  </label></div>
 
  <?php // Display the add to cart button END ?>
  <input class="pname" value="<?php echo $product->product_name ?>" type="hidden" />
  <input name="option" value="com_virtuemart" type="hidden" />
  <input name="view" value="cart" type="hidden" />
 
  <input name="virtuemart_product_id[]" value="<?php echo $product->virtuemart_product_id ?>" type="hidden" />
  <?php /** @todo Handle the manufacturer view */ ?>
  <input name="virtuemart_manufacturer_id" value="<?php echo $product->virtuemart_manufacturer_id ?>" type="hidden" />
  <input name="virtuemart_category_id[]" value="<?php echo $product->virtuemart_category_id ?>" type="hidden" />
  </form>
        </div>
  <?php }  // Add To Cart Button END ?>
  
</div>
 

				<!--<a href="<?php echo $product->link; ?>" class="readon"><?php echo JText::_('COM_VIRTUEMART_PRODUCT_DETAILS'); ?></a> -->
			
			</div>
		</div>
	<?php
	$iBrowseProduct ++;

	// Do we need to close the current row now?
	if ($iBrowseCol == $BrowseProducts_per_row || (isset($BrowseTotalProducts) && $iBrowseProduct == $BrowseTotalProducts)) {?>
		</div>
		<?php
		$iBrowseCol = 1;
	} else {
		$iBrowseCol ++;
	}
}
// Do we need a final closing row tag?
if ($iBrowseCol != 1) { ?>
	<div class="clear"></div>
</div>
<?php
}
?>

<?php if($this->vmPagination->getPagesLinks() != '') : ?>
<div class="pagination"> 
	<?php echo str_replace('</ul>', '<li class="counter">'.$this->vmPagination->getPagesCounter().'</li></ul>', $this->vmPagination->getPagesLinks()); ?> 
</div>
<?php endif; ?>


</div>
<?php
} elseif (!empty($this->keyword)) {
	echo JText::_ ('COM_VIRTUEMART_NO_RESULT') . ($this->keyword ? ' : (' . $this->keyword . ')' : '');
}
?>

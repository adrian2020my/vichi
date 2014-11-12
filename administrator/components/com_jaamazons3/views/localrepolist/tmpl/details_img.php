<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
//
$this->cnt++;
$css = "row".($this->cnt%2);
$itemId = preg_replace("/[\$\.]/", '_', $this->_tmp_img->name);
?>

<tr class="<?php echo $css; ?>" id="local-item-<?php echo $itemId; ?>">
  <td><input type="checkbox" name="rm[]" id="chk-<?php echo $itemId; ?>" value="<?php echo $this->_tmp_img->name; ?>" /></td>
  <td class="description">
  <a> <img src="<?php echo $this->_tmp_img->icon_16; ?>" width="16" height="16" border="0" alt="<?php echo $this->_tmp_img->name; ?>" /> </a>
  <?php echo $this->escape( $this->_tmp_img->name); ?>
  <?php if($this->_tmp_img->compressed): ?>
  <sup style="color:red;" title="<?php echo JText::_('The remote file has been compressed by SmushIt!'); ?>"> <?php echo JText::_('Compressed!'); ?></sup>
  <?php endif; ?>
  <span class="ja-items-info"><?php echo RepoHelper::parseSize($this->_tmp_img->size); ?></span>
  </td>
  <td><?php echo $this->setStatus($this->_tmp_img); ?> </td>
  <td>
  	<?php
	if($this->_tmp_img->last_update === false) {
		echo '<span style="color:red;">'.JText::_('NEVER').'</span>';
	} else {
		echo jaStorageHelper::nicetime($this->_tmp_img->last_update);
	}
	?> 
  </td>
  <td>
  <span class="row_actions">
  <a href="#" onclick="javascript: window.parent.jaAmazonS3Upload('<?php echo $this->_tmp_img->name; ?>'); return false;" title="<?php echo JText::sprintf("CLICK_HERE_TO_UPLOAD_FILE_QUOTSQUOT", $this->_tmp_img->name); ?>"> <?php echo jaStorageHelper::showIcon("upload.png", JText::_('UPLOAD' ), JText::_('UPLOAD' )); ?> </a> 
  </span>
  </td>
</tr>

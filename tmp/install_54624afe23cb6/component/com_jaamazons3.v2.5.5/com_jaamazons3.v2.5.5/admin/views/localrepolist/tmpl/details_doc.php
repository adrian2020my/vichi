<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php 
$this->cnt++;
$css = "row".($this->cnt%2);
$itemId = preg_replace("/[\$\.]/", '_', $this->_tmp_doc->name);
?>

<tr class="<?php echo $css; ?>" id="local-item-<?php echo $itemId; ?>">
  <td><input type="checkbox" name="rm[]" id="chk-<?php echo $itemId; ?>" value="<?php echo $this->_tmp_doc->name; ?>" /></td>
  <td class="description">
  <img src="<?php echo $this->_tmp_doc->icon_16; ?>" width="16" height="16" border="0" alt="<?php echo $this->_tmp_doc->name; ?>" /> 
  <?php echo $this->_tmp_doc->name; ?>
  <span class="ja-items-info"><?php echo RepoHelper::parseSize($this->_tmp_doc->size); ?></span>
  </td>
  <td><?php echo $this->setStatus($this->_tmp_doc); ?> </td>
  <td>
  	<?php
	if($this->_tmp_doc->last_update === false) {
		echo '<span style="color:red;">'.JText::_('NEVER').'</span>';
	} else {
		echo jaStorageHelper::nicetime($this->_tmp_doc->last_update);
	}
	?> 
  </td>
  <td>
  <span class="row_actions">
  <a href="#" onclick="javascript: window.parent.jaAmazonS3Upload('<?php echo $this->_tmp_doc->name; ?>'); return false;" title="<?php echo JText::sprintf("CLICK_HERE_TO_UPLOAD_FILE_QUOTSQUOT", $this->_tmp_doc->name); ?>"> <?php echo jaStorageHelper::showIcon("upload.png", JText::_('UPLOAD' ), JText::_('UPLOAD' )); ?> </a> 
  </span>
  </td>
</tr>

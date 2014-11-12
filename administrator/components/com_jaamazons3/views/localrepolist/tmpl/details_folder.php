<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php 
$this->cnt++;
$css = "row".($this->cnt%2);

$itemId = preg_replace("/[\$\.]/", '_', $this->_tmp_folder->name);
?>

<tr class="<?php echo $css; ?>" id="local-item-<?php echo $itemId; ?>">
  <td><input type="checkbox" name="rm[]" id="chk-<?php echo $itemId; ?>" value="<?php echo $this->_tmp_folder->name; ?>/" /></td>
  <td class="description">
  <a href="index.php?option=<?php echo JACOMPONENT; ?>&amp;view=localrepolist&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>" target="folderframe"> <img src="components/<?php echo JACOMPONENT; ?>/assets/images/icons/folder_sm.png" width="16" height="16" border="0" alt="<?php echo $this->_tmp_folder->name; ?>" /></a>
  <a href="index.php?option=<?php echo JACOMPONENT; ?>&amp;view=localrepolist&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>" target="folderframe"><?php echo $this->_tmp_folder->name; ?></a> 
  </td>
  <td><?php echo $this->setStatus($this->_tmp_folder); ?> </td>
  <td>
  	<?php
	if($this->_tmp_folder->last_update === false) {
		echo '<span style="color:red;">'.JText::_('NEVER').'</span>';
	} else {
		echo jaStorageHelper::nicetime($this->_tmp_folder->last_update);
	}
	?> 
  </td>
  <td>
  <span class="row_actions">
  <a href="#" onclick="javascript: window.parent.jaAmazonS3Upload('<?php echo $this->_tmp_folder->name; ?>'); return false;" title="<?php echo JText::sprintf("CLICK_HERE_TO_UPLOAD_FOLDER_QUOTSQUOT", $this->_tmp_folder->name); ?>"> <?php echo jaStorageHelper::showIcon("upload.png", JText::_('UPLOAD' ), JText::_('UPLOAD' )); ?> </a> 
  &nbsp;|&nbsp;
  <a href="#" onclick="javascript: prompt('<?php echo JText::_("RUN_THIS_URL_TO_IMMEDIATELY_UPLOAD_THIS_FOLDER_FROM_FRONTEND", true); ?>', '<?php echo $this->_tmp_folder->upload_url; ?>'); return false;" title="<?php echo JText::_("CLICK_HERE_TO_GET_UPLOAD_URL"); ?>"> <?php echo jaStorageHelper::showIcon("info.png", JText::_('UPLOAD_URL' ), JText::_('UPLOAD_URL' )); ?> </a> 
  </span>
  </td>
</tr>

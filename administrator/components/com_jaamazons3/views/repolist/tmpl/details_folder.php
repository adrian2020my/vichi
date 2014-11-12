<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php 
$this->cnt++;
$css = "row".($this->cnt%2);
$itemId = preg_replace("/[\$\.]/", '_', $this->_tmp_folder->name);
?>

<tr class="<?php echo $css; ?>" id="local-item-<?php echo $itemId; ?>">
  <td><input type="checkbox" name="rm[]" id="chk-<?php echo $itemId; ?>" value="<?php echo $this->_tmp_folder->name; ?>/" /></td>
  <td class="description">
  <a href="index.php?option=<?php echo JACOMPONENT; ?>&amp;view=repolist&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>" target="folderframe"> <img src="components/<?php echo JACOMPONENT; ?>/assets/images/icons/folder_sm.png" width="16" height="16" border="0" alt="<?php echo $this->_tmp_folder->name; ?>" /></a>
  <a href="index.php?option=<?php echo JACOMPONENT; ?>&amp;view=repolist&amp;tmpl=component&amp;folder=<?php echo $this->_tmp_folder->path_relative; ?>" target="folderframe"><?php echo $this->_tmp_folder->name; ?></a> </td>
  <!--<td>&nbsp;</td>-->
  <td>&nbsp;</td>
  <td>
  <span class="row_actions">
  <?php $url = 'index.php?option=' . JACOMPONENT . '&amp;view=file&amp;task=delete&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1&amp;folder=' . $this->state->folder . '&amp;rm[]=' . $this->_tmp_folder->name.'/'; ?>
  <a onclick="deleteItem('<?php echo $url; ?>'); return false;" href="#" rel="<?php echo $this->_tmp_folder->name; ?>"> <?php echo jaStorageHelper::showIcon("edit_trash.gif", JText::_('DELETE' ), JText::_('DELETE' )); ?> </a> 
  </span>
  </td>
</tr>

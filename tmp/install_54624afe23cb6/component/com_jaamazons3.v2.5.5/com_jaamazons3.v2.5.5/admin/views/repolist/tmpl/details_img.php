<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
//
$this->cnt++;
$css = "row".($this->cnt%2);
$itemId = preg_replace("/[\$\.]/", '_', $this->_tmp_img->name);
?>

<tr class="<?php echo $css; ?>" id="local-item-<?php echo $itemId; ?>">
  <td><input type="checkbox" name="rm[]" id="chk-<?php echo $itemId; ?>" value="<?php echo $this->_tmp_img->name; ?>" />
  </td>
  <td class="description">
  <a> <img src="<?php echo $this->_tmp_img->icon_16; ?>" width="16" height="16" border="0" alt="<?php echo $this->_tmp_img->name; ?>" /> </a>
  <a href="<?php echo $this->s3Url . $this->_tmp_img->path_relative; ?>" class="hasTip" target="_blank" 
        title="<?php echo $this->_tmp_img->name; ?>::&lt;img src=&quot;<?php echo $this->s3Url . $this->_tmp_img->path_relative; ?>&quot;  style=&quot;max-width:300px;&quot;/&gt;" rel="preview"> <?php echo $this->escape( $this->_tmp_img->name); ?> </a> 
  <?php if($this->_tmp_img->compressed): ?>
  <sup style="color:red;"> <?php echo JText::_('Compressed!'); ?></sup>
  <?php endif; ?>
  <span class="ja-items-info"><?php echo RepoHelper::parseSize($this->_tmp_img->size); ?></span>    
  </td>
  <!--<td>
        <span class="ja-acl-<?php echo $this->_tmp_img->status; ?>"><?php echo $this->_tmp_img->status; ?></span>
    </td>-->
  <td><?php echo $this->_tmp_img->lastModified; ?> </td>
  <td>
  <span class="row_actions">
  <a href="index.php?option=<?php echo JACOMPONENT; ?>&amp;view=file&amp;task=download&amp;tmpl=component&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $this->_tmp_img->name; ?>" rel="<?php echo $this->_tmp_img->name; ?>"> <?php echo jaStorageHelper::showIcon("download.png", JText::_('DOWNLOAD' ), JText::_('DOWNLOAD' )); ?> </a>
  &nbsp;|&nbsp;
  <?php $url = 'index.php?option=' . JACOMPONENT . '&amp;view=file&amp;task=delete&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1&amp;folder=' . $this->state->folder . '&amp;rm[]=' . $this->_tmp_img->name; ?>
    <a onclick="deleteItem('<?php echo $url; ?>'); return false;" href="#" rel="<?php echo $this->_tmp_img->name; ?>"> <?php echo jaStorageHelper::showIcon("edit_trash.gif", JText::_('DELETE' ), JText::_('DELETE' )); ?> </a> 
  </span>
  </td>
</tr>

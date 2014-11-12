<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
//
$this->cnt++;
$css = "row".($this->cnt%2);
?>

<tr class="<?php echo $css; ?>">
  <td>&nbsp;</td>
  <td class="description">
  <a href="index.php?option=<?php echo JACOMPONENT; ?>&amp;view=localrepolist&amp;tmpl=component&amp;folder=<?php echo $this->state->parent; ?>" target="folderframe"> <img src="components/<?php echo JACOMPONENT; ?>/assets/images/icons/btnFolderUp.gif" width="16" height="16" border="0" alt=".." /></a>
  <a href="index.php?option=<?php echo JACOMPONENT; ?>&amp;view=localrepolist&amp;tmpl=component&amp;folder=<?php echo $this->state->parent; ?>" target="folderframe">..</a> 
  </td>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
</tr>

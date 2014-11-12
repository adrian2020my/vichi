<?php
/*
 * ------------------------------------------------------------------------
 * JA Amazon S3 for joomla 2.5 & 3.1
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
*/

// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' );

$bucket = $this->bucket; 
$lists = $this->lists;
$page = $this->pageNav; 

$backLink = 'index.php?option='.JACOMPONENT.'&view=bucket';

$view = 'bucket';
$viewLink = 'index.php?tmpl=component&option='.JACOMPONENT.'&view='.$view.'&viewmenu=0&task=%s&cid[]=%d&number=%d';
$linkNew = sprintf($viewLink, 'edit', 0, 0);
?>
<script type="text/javascript">
/*<![CDATA[*/
Joomla.submitbutton = function(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == 'add' || pressbutton == 'edit') {
		jaCreatePopup('<?php echo $linkNew; ?>', 650, 550, '<?php echo JText::_("CREATE_NEW_BUCKET", true)?>');
		//document.location.href = '<?php echo $linkNew; ?>';
	} else if (pressbutton == 'remove') {
		var selected = jQuery('input[name^=cid]:checked').val();
		if(jQuery('#chkDel' + selected).val() == 0) {
			alert('<?php echo JText::_('CAN_NOT_DELETE_DEFAULT_BUCKET', true); ?>');
			return false;
		} else {
			form.task.value = pressbutton;
			form.submit();
		}
	} else {
		form.task.value = pressbutton;
		form.submit();
	}
}
/*]]>*/
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">
  <?php echo JHtml::_( 'form.token' ); ?>
  <input type="hidden" name="option" value="<?php echo JACOMPONENT; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="view" value="<?php echo $view; ?>" />
  <input type="hidden" name="filter_order_Dir" value="<?php echo $lists['filter_order_Dir']; ?>" />
  <fieldset>
  <legend class="heading"><?php echo JText::_('BUCKET_MANAGER'); ?></legend>
  <div id="ja-toolbars">
	  <?php echo JText::_("ACCOUNTS");?>:
      <?php echo $lists['boxAccounts']; ?>
      <input type="button" onclick="this.form.submit();" value="<?php echo JText::_('GO'); ?>" />
  </div>
  <table class="adminlist table table-striped">
    <thead>
      <tr>
        <th width="2%" align="left"> <?php echo JText::_('NUM' ); ?> </th>
        <th width="2%">&nbsp;    </th>
        <th> <?php echo JText::_("BUCKET_NAME" ); ?> </th>
        <th><?php echo JText::_("LAST_UPDATE"); ?></th>
        <th><?php echo JText::_("STATUS"); ?></th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <td colspan="10"><?php echo $page->getListFooter(); ?> </td>
      </tr>
    </tfoot>
    <?php
	$count=count($bucket);
	if( $count>0 ) {
	for ($i=0;$i<$count; $i++) {
		$css = "row".($i%2);
		$item	= $bucket[$i];

		JFilterOutput::objectHtmlSafe($item);
		$title=JText::_('EDIT_BUCKET')." ID: ".$item->id;	
		$linkEdit = sprintf($viewLink, 'edit', $item->id, $i);
		?>
    <tr class="<?php echo $css; ?>">
      <td><?php echo $page->getRowOffset( $i ); ?> </td>
      <td>
        <input type="radio" id="cb<?php echo $item->id; ?>" name="cid[]" value="<?php echo $item->id; ?>" onclick="Joomla.isChecked(this.checked);" />
        <input type="hidden" id="chkDel<?php echo $item->id; ?>" name="chkDel<?php echo $item->id; ?>" value="1" />      </td>
      <td>
      <span id="bucket_name<?php echo $item->id?>" style="float: left;"> <?php echo $item->bucket_name;?> </span>
  		<span class="row_actions" style="float:right;">
          <a href="#" title="<?php echo JText::_('EDIT'); ?>" onclick="jaCreatePopup('<?php echo $linkEdit; ?>', 620, 520, '<?php echo JText::_("EDIT_BUCKET", true)?>'); return false;">
          <?php echo jaStorageHelper::showIcon("edit.png", JText::_('EDIT')); ?>          </a>
          &nbsp;|&nbsp;
          <a href="index.php?option=<?php echo JACOMPONENT; ?>&amp;view=repo&amp;bucket_id=<?php echo $item->id; ?>" title="<?php echo JText::_('S3_FILES_MANAGER'); ?>">
          <?php echo jaStorageHelper::showIcon("filemanager.png", JText::_('S3_FILES_BROWSER')); ?>          </a>   
          &nbsp;|&nbsp;
          <a href="#" onclick="jaAmazonS3UpdateListFiles(<?php echo $item->id;?>, 'bucket_name<?php echo $item->id?>'); return false;" title="<?php echo JText::_('UPDATE_S3_FILE_LIST'); ?>">
          <?php echo jaStorageHelper::showIcon("sync.png", JText::_('UPDATE_S3_FILE_LIST')); ?>          </a>        </span>      </td>
      <td align="center">
			<?php
            if($item->last_sync === '0000-00-00 00:00:00') {
                echo '<span style="color:red;">'.JText::_('NEVER').'</span>';
            } else {
                echo jaStorageHelper::nicetime($item->last_sync);
            }
            ?> 
      </td>
      <td align="center"> <span id="bucket_acl<?php echo $item->id?>" class="ja-acl-<?php echo $item->bucket_acl;?>"><?php echo $item->bucket_acl;?></span> </td>
    </tr>
    <?php }?>
    <?php }else{ ?>
    <tr>
      <td colspan="7"><?php echo JText::_("GET_BUCKET_LIST")?> </td>
    </tr>
    <?php } ?>
  </table>
  </fieldset>
</form>

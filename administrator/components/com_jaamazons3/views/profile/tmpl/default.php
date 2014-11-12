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

$profile = $this->profile; 
$lists = $this->lists;
$page = $this->pageNav; 

$backLink = 'index.php?option='.JACOMPONENT.'&view=profile';

$view = 'profile';
$viewLink = 'index.php?tmpl=component&option='.JACOMPONENT.'&view='.$view.'&viewmenu=0&task=%s&cid[]=%d&number=%d';
$linkNew = sprintf($viewLink, 'edit', 0, 0);
?>
<script type="text/javascript">
/*<![CDATA[*/
Joomla.submitbutton = function(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == 'add' || pressbutton == 'edit') {
		jaCreatePopup('<?php echo $linkNew; ?>', 600, 500, '<?php echo JText::_("CREATE_NEW_PROFILE", true)?>');
		//document.location.href = '<?php echo $linkNew; ?>';
	} else if (pressbutton == 'remove') {
		var selected = jQuery('input[name^=cid]:checked').val();
		if(jQuery('#chkDel' + selected).val() == 0) {
			alert('<?php echo JText::_('CAN_NOT_DELETE_DEFAULT_PROFILE', true); ?>');
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
  <legend class="heading"><?php echo JText::_('PROFILE_MANAGER'); ?></legend>
  <table class="adminlist table table-striped">
    <thead>
      <tr>
        <th width="2%" align="left"> <?php echo JText::_('NUM' ); ?> </th>
        <th width="2%">&nbsp;    </th>
        <th align="center"> <?php echo JText::_("PROFILE_NAME" ); ?> </th>
        <th align="center"><?php echo JText::_("BUCKET" ); ?></th>
        <th align="center"> <?php echo JText::_("SITE_PATH" ); ?> </th>
        <th align="center"> <?php echo JText::_("SITE_URL"); ?> </th>
        <th width="50"><?php echo JText::_("STATUS"); ?></th>
        <th width="50"><?php echo JText::_("CRON_JOB"); ?></th>
        <th width="200"><?php echo JText::_("ACTIONS"); ?></th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <td colspan="12"><?php echo $page->getListFooter(); ?> </td>
      </tr>
    </tfoot>
    <?php
	$count=count($profile);
	if( $count>0 ) {
	for ($i=0;$i<$count; $i++) {
		$css = "row".($i%2);
		$item	= $profile[$i];

		JFilterOutput::objectHtmlSafe($item);
		$title=JText::_('EDIT_PROFILE')." ID: ".$item->id;	
		$linkEdit = sprintf($viewLink, 'edit', $item->id, $i);
		?>
    <tr class="<?php echo $css; ?>">
      <td><?php echo $page->getRowOffset( $i ); ?> </td>
      <td>
        <input type="radio" id="cb<?php echo $item->id; ?>" name="cid[]" value="<?php echo $item->id; ?>" onclick="Joomla.isChecked(this.checked);" />
        <input type="hidden" id="chkDel<?php echo $item->id; ?>" name="chkDel<?php echo $item->id; ?>" value="1" />      </td>
      <td align="center"><span id="profile_name<?php echo $item->id?>"> <?php echo $item->profile_name;?> </span></td>
      <td align="center">
      <?php if(empty($item->bucket_name)): ?>
       <span style="color:red;"><?php echo jaStorageHelper::showIcon("warning.png", JText::_('MISSING'));?></span>
      <?php else: ?>
       <?php echo $item->bucket_name;?>
      <?php endif; ?>
      </td>
      <td align="center"><span id="site_path<?php echo $item->id?>"> <?php echo $item->site_path;?> </span></td>
      <td align="center"><span id="site_url<?php echo $item->id?>"> <?php echo nl2br($item->site_url);?> </span></td>
      <td align="center">
      <?php 
	  if($item->profile_status) {
	  	echo jaStorageHelper::showIcon("tick.png", JText::_('ENABLED' ), JText::_('ENABLED' ), '', 0);
	  } else {
	  	echo jaStorageHelper::showIcon("publish_x.png", JText::_('DISABLED' ), JText::_('DISABLED' ), '', 0);
	  }
	  ?>      </td>
      <td align="center">
      <?php 
	  if($item->cron_enable) {
	  	$title = $this->showCronJobInterval($item);
	  	echo jaStorageHelper::showIcon("tick.png", JText::_('ENABLED' ), JText::_('ENABLED' ), $title, 0);
	  } else {
	  	echo jaStorageHelper::showIcon("publish_x.png", JText::_('DISABLED' ), JText::_('DISABLED' ), '', 0);
	  }
	  ?>      </td>
      <td align="center">
      <span class="row_actions">
      <a href="#" title="<?php echo JText::_('EDIT'); ?>" onclick="jaCreatePopup('<?php echo $linkEdit; ?>', 600, 450, '<?php echo JText::_("EDIT_PROFILE", true)?>'); return false;">
	  <?php echo jaStorageHelper::showIcon("edit.png", JText::_('EDIT')); ?>      </a>
      &nbsp;|&nbsp;
      <a href="index.php?option=<?php echo JACOMPONENT; ?>&amp;view=localrepo&amp;profile_id=<?php echo $item->id; ?>" title="<?php echo JText::_('SYNC_MANAGER'); ?>">
      <?php echo jaStorageHelper::showIcon("sync.png", JText::_('SYNC_MANAGER')); ?>      </a>      </span>      </td>
    </tr>
    <?php }?>
    <?php }else{ ?>
    <tr>
      <td colspan="9"><?php echo JText::_("PLEASE_CREATE_A_PROFILE" )?> </td>
    </tr>
    <?php } ?>
  </table>
  </fieldset>
</form>

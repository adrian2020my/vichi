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

$account = $this->account; 
$lists = $this->lists; 
$page = $this->pageNav; 

$backLink = 'index.php?option='.JACOMPONENT.'&view=account';

$view = 'account';
$viewLink = 'index.php?tmpl=component&option='.JACOMPONENT.'&view='.$view.'&viewmenu=0&task=%s&cid[]=%d&number=%d';
$linkNew = sprintf($viewLink, 'edit', 0, 0);
?>
<script type="text/javascript">
/*<![CDATA[*/
Joomla.submitbutton = function(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == 'add' || pressbutton == 'edit') {
		jaCreatePopup('<?php echo $linkNew; ?>', 450, 350, '<?php echo JText::_("ACCOUNT_INFORMATIONS", true)?>');
	} else if (pressbutton == 'remove') {
		var selected = jQuery('input[name^=cid]:checked').val();
		if(jQuery('#chkDel' + selected).val() == 0) {
			alert('<?php echo JText::_('CAN_NOT_DELETE_DEFAULT_ACCOUNT', true); ?>');
			return false;
		} else {
			if(confirm('<?php echo JText::_('DO_YOU_REALLY_WANT_TO_DELETE_THIS_ACCOUNT_ALL_BUCKETSS_SETTINGS_OF_THIS_ACCOUNT_WILL_BE_REMOVED_ALSO', true); ?>')){
				form.task.value = pressbutton;
				form.submit();
			}
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
  <legend class="heading"><?php echo JText::_('ACCOUNT_MANAGER'); ?></legend>
  <table class="adminlist table table-striped">
    <thead>
      <tr>
        <th width="2%" align="left"> <?php echo JText::_('NUM' ); ?> </th>
        <th width="2%">&nbsp;    </th>
        <th> <?php echo JText::_("LABEL" ); ?> </th>
        <th> <?php echo JText::_("ACCOUNT_ID" ); ?> </th>
        <th> <?php echo JText::_("ACCESS_KEY"); ?> </th>
        <th width="160">&nbsp;  </th>
      </tr>
    </thead>
    <?php
	$count=count($account);
	if( $count>0 ) {
	for ($i=0;$i<$count; $i++) {
		$css = "row".($i%2);
		$item	= $account[$i];

		JFilterOutput::objectHtmlSafe($item);
		$title=JText::_('EDIT_ACCOUNT')." ID: ".$item->id;	
		$linkEdit = sprintf($viewLink, 'edit', $item->id, $i);
		?>
    <tr class="<?php echo $css; ?>">
      <td><?php echo $page->getRowOffset( $i ); ?> </td>
      <td>
        <input type="radio" id="cb<?php echo $item->id; ?>" name="cid[]" value="<?php echo $item->id; ?>" onclick="Joomla.isChecked(this.checked);" />
        <input type="hidden" id="chkDel<?php echo $item->id; ?>" name="chkDel<?php echo $item->id; ?>" value="1" />      </td>
      <td><span id="acc_label<?php echo $item->id?>"> <?php echo $item->acc_label;?> </span></td>
      <td><span id="acc_name<?php echo $item->id?>"> <?php echo $item->acc_name;?> </span></td>
      <td><span id="acc_accesskey<?php echo $item->id?>"> <?php echo $item->acc_accesskey;?> </span></td>
      <td align="center">
      <span class="row_actions">
      <a href="#" title="<?php echo JText::_('EDIT'); ?>" onclick="jaCreatePopup('<?php echo $linkEdit; ?>', 450, 350, '<?php echo JText::_("CONFIG_ACCOUNT", true)?>'); return false;"><?php echo jaStorageHelper::showIcon("edit.png", JText::_('EDIT')); ?></a>
      &nbsp;|&nbsp;
      <a href="index.php?option=<?php echo JACOMPONENT; ?>&amp;view=bucket&amp;acc_id=<?php echo $item->id; ?>" title="<?php echo JText::_('BUCKETS'); ?>"><?php echo jaStorageHelper::showIcon("buckets.png", JText::_('BUCKETS')); ?></a>
      </span>
      </td>
    </tr>
    <?php }?>
    <?php }else{ ?>
    <tr>
      <td colspan="7"><?php echo JText::_("HAVE_NO_RESULT")?> </td>
    </tr>
    <?php } ?>
    <tfoot>
      <tr>
        <td colspan="7"><?php echo $page->getListFooter(); ?> </td>
      </tr>
    </tfoot>
  </table>
  </fieldset>
</form>

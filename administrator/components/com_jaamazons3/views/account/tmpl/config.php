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

$item=$this->item;
?>
<div style="width:500px; margin:0 auto;">
<fieldset>
<form name="adminForm" id="adminForm" action="index.php" method="post">
  <input type="hidden" name="option" value="<?php echo JACOMPONENT; ?>" />
  <input type="hidden" name="view" value="account" />
  <input type="hidden" name="task" value="saveConfig" />
  <input type="hidden" name="tmpl" value="component" />
  <input type="hidden" name='id' id='id' value="<?php echo $item->id; ?>">
  <input type="hidden" name='cid[]' id='cid[]' value="<?php echo $item->id; ?>">
  <input type="hidden" name="number" value="<?php echo $this->number; ?>">
  <input type="hidden" id="backUrl" name="backUrl" value="<?php echo JRequest::getVar('backUrl', ''); ?>" />
    <fieldset>
        <legend> <?php echo JText::_('ACCOUNT_INFORMATION' ); ?> </legend>
        <table class="admintable" width="100%">
          <tr>
            <td width="30%" class="key" align="right" valign="top"><?php echo JText::_('TITLE' ); ?>: </td>
            <td width="70%">
      			<input type="text" id="acc_label" name="acc_label" size='50' value="<?php echo $item->acc_label; ?>" />            </td>
          </tr>
          <tr>
            <td class="key" align="right" valign="top"><?php echo JText::_('ACCOUNT_ID' ); ?>: </td>
            <td>
      			<input type="text" id="acc_name" name="acc_name" size='50' value="<?php echo $item->acc_name; ?>" />            </td>
          </tr>
    	</table>
  </fieldset>
  <fieldset>
        <legend> <?php echo JText::_('AUTHENTICATION' ); ?> </legend>
        <table class="admintable" width="100%">
          <tr>
            <td colspan="2"><?php echo JText::_('LEAVE_BLANK_IF_THIS_SERVICE_DO_NOT_REQUIRE_AUTHENTICATION'); ?></td>
          </tr>
          <tr>
            <td width="30%"  class="key" align="right" valign="top"><?php echo JText::_('ACCESS_KEY' ); ?>: </td>
            <td width="70%">
      			<input type="text" id="acc_accesskey" name="acc_accesskey" size='40' value="<?php echo $item->acc_accesskey; ?>" />            </td>
          </tr>
          <tr>
            <td class="key" align="right" valign="top"><?php echo JText::_('SECRET_KEY' ); ?>: </td>
            <td>
      			<input type="password" id="acc_secretkey" name="acc_secretkey" size='40' value="" />
                <?php if($item->id != 0): ?>
                <br /><small><?php echo JText::_('LEAVE_BLANK_IF_NO_REQUIRE_CHANGE' ); ?></small>
                <?php endif; ?>            </td>
          </tr>
    	</table>
  </fieldset>
  <button id="ja-save-button" name="ja-save-button"><?php echo JText::_("SAVE"); ?></button>
</form>
</fieldset>
</div>


<script type="text/javascript">
/*<![CDATA[*/
jQuery(document).ready(function(){
	jQuery("#ja-save-button").bind('click', function(e) {
		if(jQuery('#acc_label').val() == '') {
			alert('<?php echo JText::_('PLEASE_ENTER_TITLE', true); ?>');
			return false;
		}
		if(jQuery('#acc_name').val() == '') {
			alert('<?php echo JText::_('PLEASE_ENTER_ACCOUNT_ID', true); ?>');
			return false;
		}
		if(jQuery('#acc_accesskey').val() == '') {
			alert('<?php echo JText::_('PLEASE_ENTER_ACCESS_KEY', true); ?>');
			return false;
		}
		<?php if($item->id == 0): ?>
		if(jQuery('#acc_secretkey').val() == '') {
			alert('<?php echo JText::_('PLEASE_ENTER_SECRET_KEY', true); ?>');
			return false;
		}
		<?php endif; ?> 
		
		var form = document.adminForm;
		form.submit();
	});
});
/*]]>*/
</script>

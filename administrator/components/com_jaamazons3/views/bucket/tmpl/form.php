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
<form name="adminForm" id="adminForm" action="index.php" method="post">
  <input type="hidden" name="option" value="<?php echo JACOMPONENT; ?>" />
  <input type="hidden" name="view" value="bucket" />
  <input type="hidden" name="task" value="saveIFrame" />
  <input type="hidden" name="tmpl" value="component" />
  <input type="hidden" name='id' id='id' value="<?php echo $item->id; ?>">
  <input type="hidden" name='cid[]' id='cid[]' value="<?php echo $item->id; ?>">
  <input type="hidden" name="number" value="<?php echo $this->number; ?>">
        
    <table class="admintable" width="100%">
      <tr>
        <td width="150" class="key" align="right" valign="top" title="<?php echo JText::_('BUCKET_NAME' ); ?>::<?php echo htmlentities(JText::_('BUCKET_NAME_DESC')); ?>"><?php echo JText::_('BUCKET_NAME' ); ?>: </td>
        <td>
        <?php if ($item->id): ?>
            <input type="text" id="only_for_view" name="only_for_view" size='50' value="<?php echo $item->bucket_name; ?>" disabled="disabled" />
            <input type="hidden" id="bucket_name" name="bucket_name" size='50' value="<?php echo $item->bucket_name; ?>" />  
        <?php else: ?>   
            <input type="text" id="bucket_name" name="bucket_name" size='50' value="<?php echo $item->bucket_name; ?>" />         
        <?php endif; ?>
        </td>
      </tr>
      <tr>
        <td class="key" align="right" valign="top" title="<?php echo JText::_('BUCKET_STATUS' ); ?>::<?php echo JText::_('SET_STATUS_OF_BUCKET' ); ?>"><?php echo JText::_('BUCKET_STATUS' ); ?>: </td>
        <td>
            <?php echo $this->listStatus; ?>
            <input type="hidden" name="current_acl" id="current_acl" value="<?php echo $item->bucket_acl; ?>" />
            
        </td>
      </tr>
      <tr>
        <td class="key" align="right" valign="top" title="<?php echo JText::_('BUCKET_PROTOCOL' ); ?>::<?php echo JText::_('BUCKET_PROTOCOL_DESC' ); ?>"><?php echo JText::_('BUCKET_PROTOCOL' ); ?>: </td>
        <td>
            <?php echo $this->listProtocols; ?>
        </td>
      </tr>
      <tr>
        <td class="key" align="right" valign="top" title="<?php echo JText::_('BUCKET_URL_FORMAT' ); ?>::<?php echo JText::_('BUCKET_URL_FORMAT_DESC' ); ?>"><?php echo JText::_('BUCKET_URL_FORMAT' ); ?>: </td>
        <td>
            <?php echo $this->listUrlFormats; ?>
        </td>
      </tr>
      <tr>
        <td class="key" align="right" valign="top" title="<?php echo JText::_('CLOUD_FRONT_DOMAIN' ); ?>::<?php echo JText::_('CLOUD_FRONT_DISTRIBUTION_DOMAIN'); ?>">
        <?php echo JText::_('CLOUD_FRONT_DOMAIN' ); ?>: 
        </td>
        <td>
            <input type="text" id="bucket_cloudfront_domain" name="bucket_cloudfront_domain" size='50' value="<?php echo $item->bucket_cloudfront_domain; ?>"/>
        </td>
      </tr>
      <tr>
        <td class="key" align="right" valign="top" title="<?php echo JText::_('REGION' ); ?>::<?php echo JText::_('SELECT_A_REGION_WHERE_BUCKET_IS_PLACED' ); ?>">
        <?php echo JText::_('REGION' ); ?> 
        </td>
        <td>
            <?php echo $this->listRegions; ?>
        </td>
      </tr>
      <?php if (!$item->id): ?>
      <tr>
        <td class="key" align="right" valign="top" title="<?php echo JText::_('CLONE_FROM_EXISTING_BUCKETS' ); ?>::<?php echo JText::_('SELECT_AN_BUCKET_THAT_YOU_WANT_TO_COPY_ITS_CONTENT_TO_NEW_BUCKET' ); ?>">
        <?php echo JText::_('CLONE_FROM_EXISTING_BUCKETS' ); ?> 
        </td>
        <td>
            <?php echo $this->listBuckets; ?>
        </td>
      </tr>
      <?php endif; ?>
    </table>
</form>


<script type="text/javascript">
/*<![CDATA[*/
jQuery(document).ready(function(){
	//remove default save button
	var btnSubmit = jQuery('#ja-save-button', window.parent.document);
	btnSubmit.remove();
	//enable custom button
	jQuery("#ja-save2-button", window.parent.document).css({
		'display': 'block'
	}).html('<?php echo JText::_('SAVE_CONFIG', true); ?>').bind('click', function(e) {
		if(jQuery('#bucket_name').val() == '') {
			alert('<?php echo JText::_('PLEASE_ENTER_BUCKET_NAME', true); ?>');
			return false;
		}
		if(jQuery('#bucket_acl').val() == '') {
			alert('<?php echo JText::_('PLEASE_SELECT_BUCKET_STATUS', true); ?>');
			return false;
		}
		
		var form = document.adminForm;
		form.submit();
	});
	
	//check status is changed
	/*jQuery("input:radio[name=bucket_acl]").each(function(el){
		jQuery(el).click(function(el){
			var status = jQuery('input:radio[name=bucket_acl]:checked').val();
			if(status == jQuery('#current_acl').val()){
				jQuery('#status_wrapper').css('display', 'none');
			} else {
				jQuery('#status_wrapper').css('display', 'inline');
			}
		});
	});*/
});
window.addEvent('domready', function() {
	$$('td.key').each(function(el) {
		var title = el.get('title');
		if (title) {
			var parts = title.split('::', 2);
			el.store('tip:title', parts[0]);
			el.store('tip:text', parts[1]);
		}
	});
	var JTooltips = new Tips($$('td.key'), { maxTitleChars: 50, fixed: false});
});

/*]]>*/
</script>
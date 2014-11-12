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
  <input type="hidden" name="view" value="profile" />
  <input type="hidden" name="task" value="saveIFrame" />
  <input type="hidden" name="tmpl" value="component" />
  <input type="hidden" name='id' id='id' value="<?php echo $item->id; ?>">
  <input type="hidden" name='cid[]' id='cid[]' value="<?php echo $item->id; ?>">
  <input type="hidden" name="number" value="<?php echo $this->number; ?>">
        
    <table class="admintable" width="100%">
      <tr>
        <td class="key" align="right" valign="top" title="<?php echo JText::_('ENABLE' ); ?>::<?php echo JText::_('ENABLE_PROFILE_DESC' ); ?>"><?php echo JText::_('ENABLE' ); ?>: </td>
        <td>
            <?php echo $this->profile_status; ?>
        </td>
      </tr>
      <tr>
        <td class="key" align="right" valign="top" title="<?php echo JText::_('BUCKET' ); ?>::<?php echo JText::_('SELECT_BUCKET_DESC'); ?>"><?php echo JText::_('BUCKET' ); ?>: </td>
        <td>
            <?php echo $this->boxBuckets; ?>
        </td>
      </tr>
      <tr>
        <td width="150" class="key" align="right" valign="top" title="<?php echo JText::_('PROFILE_NAME' ); ?>::<?php echo JText::_('PROFILE_NAME_DESC'); ?>"><?php echo JText::_('PROFILE_NAME' ); ?>: </td>
        <td>
            <input type="text" id="profile_name" name="profile_name" size='50' value="<?php echo $item->profile_name; ?>" />
        </td>
      </tr>
      <tr>
        <td class="key" align="right" valign="top" title="<?php echo JText::_('FILE_TYPES' ); ?>::<?php echo JText::_('DEFINE_THE_FILE_TYPES_THAT_YOU_WANT_TO_UPLOAD_TO_S3__CLOUDFRONT_SERVER_SEPARATED_BY_COMMA'); ?>">
        <?php echo JText::_('FILE_TYPES' ); ?>: 
        </td>
        <td>
            <input type="text" id="allowed_extension" name="allowed_extension" size='50' value="<?php echo $item->allowed_extension; ?>"/>
        </td>
      </tr>
      <tr>
        <td class="key" align="right" valign="top" title="<?php echo JText::_('SITE_PATH' ); ?>::<?php echo JText::_('PATH_TO_LOCATION_ON_YOUR_SITE_WHERE_STATIC_FILES_ARE_PLACED'); ?>">
        <?php echo JText::_('SITE_PATH' ); ?>: 
        </td>
        <td>
		<?php echo $this->boxFolder; ?>
        <br />
		<span style="font-size: 11px; color:#999;">
        <?php echo JText::_('BY_DEFAULT_THE_FOLDER_DEPTH_IS_2_LEVEL_CHANGE_THIS_SETTING_IN_COMPONENT_PARAMETERS_WARNING_DEEPER_LEVEL_CAN_SLOW_DOWN_THIS_PAGE'); ?>
        </span>
        </td>
      </tr>
      <tr>
        <td class="key" align="right" valign="top" title="<?php echo JText::_('SITE_URL' ); ?>::<?php echo JText::_('ENTER_URLS_EACH_URL_IS_SEPARATED_ON_ONE_LINE' ); ?>">
        <?php echo JText::_('SITE_URL' ); ?>:
        </td>
        <td>
            <textarea id="site_url" name="site_url" cols="28" rows="3" wrap="off"><?php echo $item->site_url; ?></textarea>
            <br />
            <input id="ja-suggest-url" name="ja-suggest-url" size="50" title="<?php echo JText::_('SITE_URL_SUGGESTION' ); ?>" onclick="this.select();" />
        </td>
      </tr>
      <tr>
        <td class="key" align="right" valign="top" title="<?php echo JText::_('USE_SMUSHIT' ); ?>::<?php echo JText::_('USE_SMUSHIT_DESCRIPTION' ); ?>"><?php echo JText::_('USE_SMUSHIT' ); ?>: </td>
        <td>
            <?php echo $this->useSmushit; ?>
        </td>
      </tr>
      <tr>
        <td class="key" align="right" valign="top" title="<?php echo JText::_('BROWSER_CACHE_LEVEL' ); ?>::<?php echo JText::_('BROWSER_CACHE_LEVEL_DESC' ); ?>"><?php echo JText::_('CACHE_LIFETIME' ); ?>: </td>
        <td>
            <input type="text" id="cache_lifetime" name="cache_lifetime" size='50' value="<?php echo $item->cache_lifetime; ?>"/>
        </td>
      </tr>
      <tr>
        <td class="key" align="right" valign="top" title="<?php echo JText::_('CRON_JOB' ); ?>::<?php echo JText::_('CRON_JOB_DESC' ); ?>"><?php echo JText::_('ENABLE_UPLOAD_CRON_JOB' ); ?>: </td>
        <td>
            <?php echo $this->cron_enable; ?>
        </td>
      </tr>
      <tr>
        <td class="key" align="right" valign="top" title="<?php echo JText::_('CRON_JOB_DAY' ); ?>::<?php echo JText::_('CRON_JOB_DAY_DESC' ); ?>">
        <?php echo JText::_('DAY' ); ?>: 
        </td>
        <td>
            <input type="text" id="cron_day" name="cron_day" size='20' maxlength="11" value="<?php echo $item->cron_day; ?>"/>
        </td>
      </tr>
      <tr>
        <td class="key" align="right" valign="top" title="<?php echo JText::_('CRON_JOB_HOUR' ); ?>::<?php echo JText::_('CRON_JOB_HOUR_DESC' ); ?>">
        <?php echo JText::_('HOUR' ); ?>: 
        </td>
        <td>
            <input type="text" id="cron_hour" name="cron_hour" size='20' maxlength="11" value="<?php echo $item->cron_hour; ?>"/>
        </td>
      </tr>
      <tr>
        <td class="key" align="right" valign="top" title="<?php echo JText::_('CRON_JOB_MINUTE' ); ?>::<?php echo JText::_('CRON_JOB_MINUTE_DESC' ); ?>">
        <?php echo JText::_('MINUTE' ); ?>: 
        </td>
        <td>
            <input type="text" id="cron_minute" name="cron_minute" size='20' maxlength="11" value="<?php echo $item->cron_minute; ?>"/>
        </td>
      </tr>
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
		var intRegex = /^\d+$/;
		if(jQuery('#bucket_id').val() == '') {
			alert('<?php echo JText::_('PLEASE_SELECT_A_BUCKET', true); ?>');
			return false;
		}
		if(jQuery('#profile_name').val() == '') {
			alert('<?php echo JText::_('PLEASE_ENTER_PROFILE_NAME', true); ?>');
			return false;
		}
		if(jQuery('#allowed_extension').val() == '') {
			alert('<?php echo JText::_('PLEASE_ENTER_A_FILE_TYPE_THAT_YOU_WANT_TO_UPLOAD', true); ?>');
			return false;
		}
		if(jQuery('#site_path').val() == '') {
			alert('<?php echo JText::_('PLEASE_ENTER_SITE_PATH', true); ?>');
			return false;
		}
		if(jQuery('#site_url').val() == '') {
			alert('<?php echo JText::_('PLEASE_ENTER_SITE_URL', true); ?>');
			return false;
		}
		
		if(!intRegex.test(jQuery('#cache_lifetime').val())) {
			alert('<?php echo JText::_('VALID_CACHE_LIFETIME', true); ?>');
			return false;
		}
		
		var checked = jQuery('input[name=cron_enable]:checked', '#adminForm').val();
		checked = parseInt(checked);
		
		if(checked) {
			if(jQuery('#cron_day').val() != '') {
				if(!intRegex.test(jQuery('#cron_day').val())) {
					alert('<?php echo JText::_('VALID_CRON_DATE_NUMBER', true); ?>');
					return false;
				}
			}
			
			if(jQuery('#cron_hour').val() != '') {
				if(!intRegex.test(jQuery('#cron_hour').val())) {
					alert('<?php echo JText::_('VALID_CRON_HOUR_NUMBER', true); ?>');
					return false;
				}
			}
			
			if(jQuery('#cron_minute').val() != '') {
				if(!intRegex.test(jQuery('#cron_minute').val())) {
					alert('<?php echo JText::_('VALID_CRON_MINUTE_NUMBER', true); ?>');
					return false;
				}
			}
		}
		
		var form = document.adminForm;
		form.submit();
	});
	
	jQuery('input[name=cron_enable]', '#adminForm').click(function(){
		var checked = jQuery('input[name=cron_enable]:checked', '#adminForm').val();
		checked = parseInt(checked);
		displayCronjobOptions(checked);
	});
	
	displayCronjobOptions(<?php echo $item->cron_enable; ?>);
});

function displayCronjobOptions(checked) {
	if(checked) {
		jQuery('#cron_day').removeAttr('disabled');
		jQuery('#cron_hour').removeAttr('disabled');
		jQuery('#cron_minute').removeAttr('disabled');
	} else {
		jQuery('#cron_day').attr('disabled', 'disabled');
		jQuery('#cron_hour').attr('disabled', 'disabled');
		jQuery('#cron_minute').attr('disabled', 'disabled');
	}
}

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
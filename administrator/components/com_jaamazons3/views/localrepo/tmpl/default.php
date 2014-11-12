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

$lists = $this->lists; 
$bucket_id = (!$this->profile) ? 0 : $this->profile->bucket_id;
?>
<script type="text/javascript">
/*<![CDATA[*/
Joomla.submitbutton = function(pressbutton) {
	var form = document.adminForm2;
	if(pressbutton == '') {
		return false;
	}
	if (pressbutton == 'upload') {
		jaAmazonS3Upload();
	} else if(pressbutton == 'update_list_s3_files') {
		jaAmazonS3UpdateListFiles(<?php echo $bucket_id; ?>, 'toolbar-update_list');
	} else if (pressbutton == 'multi_enable') {
		jaUpdateStatusMulti('multi_enable', "<?php echo JText::_('DO_YOU_REALLY_WANT_TO_ENABLE_SELECTED_ITEMS', true); ?>");
	} else if (pressbutton == 'multi_disable') {
		jaUpdateStatusMulti('multi_disable', "<?php echo JText::_('DO_YOU_REALLY_WANT_TO_DISABLE_SELECTED_ITEMS', true); ?>");
	} else {
		form.task.value = pressbutton;
		form.submit();
	}
}

function jaSelectActions(obj) {
	pressbutton = obj.value;
	//reset select box
	Array.each(obj.options, function(option){
		if (option.value == '') option.selected = true;
	});
	//submit form
	Joomla.submitbutton(pressbutton);
}

jQuery(document).ready(function(){
	jQuery('#toolbar-update_list').click(function(){
		if(jQuery('#pull-s3-file-alert').size()) {
			jQuery('#pull-s3-file-alert').hide();
		}
	});
});
/*]]>*/
</script>

<fieldset>
<legend class="heading"><?php echo JText::_("VIEWING_LOCAL_FILES"); ?></legend>

<form action="index.php" method="post" id="adminForm2" name="adminForm2">
  <?php echo JHtml::_( 'form.token' ); ?>
  <input type="hidden" name="option" value="<?php echo JACOMPONENT; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="view" value="localrepo" />
  <input type="hidden" name="upload_folder" value="" />
  <input type="hidden" name="replace_file" value="" />
  <input type="hidden" name="pcre" value="" />
  <input type="hidden" name="new_folder" value="" />
  <div id="ja-toolbars">
    <?php echo JText::_("PROFILES");?>: <?php echo $lists['boxProfiles']; ?>
    <?php if($bucket_id): ?>
     [
     <a href="index.php?option=<?php echo JACOMPONENT; ?>&amp;view=repo&amp;bucket_id=<?php echo $bucket_id; ?>" title="<?php echo JText::_("VIEW_REMOTE_AMAZON_S3_FILES"); ?>"><?php echo JText::_("VIEW_REMOTE_FILES"); ?></a>
     &nbsp;|&nbsp;
     <a href="#" onclick="javascript: prompt('<?php echo JText::_("RUN_THIS_URL_TO_IMMEDIATELY_UPLOAD_THIS_PROFILE_FROM_FRONTEND", true); ?>', '<?php echo $this->cron_upload_url; ?>'); return false;" title="<?php echo JText::_("CLICK_HERE_TO_GET_UPLOAD_URL"); ?>"> <?php echo JText::_('UPLOAD_URL' ); ?> </a> 
     ]
    <?php endif; ?>
  </div>
</form>
<div id="ja-upload-wrapper" style="display:none;">
    <div id="ja-upload-actions">
      <a href="#" onclick="jaAmazonS3StopUpload(); return false;" title="<?php echo JText::_("CLICK_HERE_TO_STOP_UPLOADING"); ?>"><?php echo JText::_("CANCEL_UPLOADING"); ?></a>
      &nbsp;|&nbsp;
      <a href="#" onclick="jaAmazonS3PopupUpload(); return false;" title="<?php echo JText::_("CLICK_HERE_TO_OPEN_IN_NEW_POPUP_WINDOW"); ?>"><?php echo JText::_("TRANFER_TO_POPUP_WINDOW"); ?></a>
    </div>
    <div id="ja-upload-progress-bar">
    </div>
</div>
<?php if($this->needSync): ?>
<div class="ja-msg-box" id="pull-s3-file-alert">
    <?php echo JText::sprintf('FILE_INFORMATION_OF_BUCKET_1S_HAS_NOT_BEEN_UPDATED_TO_DATABASE', $this->profile->bucket_name); ?>
    <br />
    <?php echo JText::_('CLICK_THE_UPDATE_S3_FILE_LIST_BUTTON_ON_THE_TOP_RIGHT_TO_PULL_FILE_INFORMATION_IT_MAY_TAKE_SOME_MINUTES_IF_YOU_BUCKET_IS_LARGE'); ?>
</div>
<?php endif; ?>
<table width="100%" cellspacing="0">
  <tr valign="top">
    <td width="200px">
    <fieldset id="treeview">
      <div id="media-tree_tree"></div>
      <?php echo $this->loadTemplate('folders'); ?>
      </fieldset>
      </td>
    <td><form action="index.php?option=<?php echo JACOMPONENT; ?>&amp;view=file&amp;task=create" name="folderForm" id="folderForm" method="post">
        <fieldset id="folderview">
        <div class="path">
          <strong><?php echo JText::_("WITH_SELECTED"); ?> </strong>
          <select name="ja-box-actions" id="ja-box-actions" onchange="jaSelectActions(this);">
            <option value=""><?php echo JText::_("SELECT_ACTION"); ?></option>
            <optgroup label="<?php echo JText::_("SYNC"); ?>">
              <option value="multi_enable"><?php echo JText::_("ENABLE"); ?></option>
              <option value="multi_disable"><?php echo JText::_("DISABLE"); ?></option>
            </optgroup>
          </select>
          <strong><?php echo JText::_("LOCATION"); ?></strong> 
          <input class="inputbox" type="text" id="folderpath" readonly="readonly" />
          <input class="update-folder" type="hidden" name="folderbase" id="folderbase" value="<?php echo $this->state->folder; ?>" />
        </div>
        <div class="view">
          <iframe src="index.php?option=<?php echo JACOMPONENT; ?>&amp;view=localrepolist&amp;tmpl=component&amp;folder=<?php echo $this->state->folder;?>" id="folderframe" name="folderframe" width="100%" height="450px;" marginwidth="0" marginheight="0" scrolling="auto" frameborder="0"></iframe>
        </div>
        </fieldset>
        <?php echo JHtml::_( 'form.token' ); ?>
      </form>
      <form action="index.php?option=<?php echo JACOMPONENT; ?>&amp;view=localrepo" name="adminForm" id="mediamanager-form" method="post" enctype="multipart/form-data" >
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="cb1" id="cb1" value="0" />
        <input class="update-folder" type="hidden" name="folder" id="folder" value="<?php echo $this->state->folder; ?>" />
      </form></td>
  </tr>
</table>
</fieldset>
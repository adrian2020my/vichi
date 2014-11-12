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
?>
<script type="text/javascript">
/*<![CDATA[*/
Joomla.submitbutton = function(pressbutton) {
	var form = document.adminForm2;
	var bucket_id = form.bucket_id.value;
	
	if(pressbutton == '') {
		return false;
	}
	if (pressbutton == 'create_folder') {
		var msg = "<?php echo JText::_("PLEASE_ENTER_A_FOLDER_NAME", true); ?>";
		
		var new_folder = prompt(msg, 'NewFolder');
		if(new_folder!=null && new_folder != '') {
			var folderActive = jQuery('#folderpath').val();
			form.upload_folder.value = folderActive;
			form.new_folder.value = new_folder;
			form.task.value = pressbutton;
			form.submit();
		}
		
	} else if(pressbutton == 'update_list_s3_files') {
		jaAmazonS3UpdateListFiles(0, 'toolbar-update_list');
	} else if(pressbutton == 'multi_delete') {
		multiDelete();
	} else if (pressbutton == 'delete_advance') {
		var msgAlert = "<?php echo JText::_('WARNING_ARE_YOU_SURE_YOU_KNOW_ABOUT_REGULAR_EXPRESSION', true); ?>";
		var msg = "<?php echo JText::_('PLEASE_ENTER_PERLCOMPATIBLE_REGULAR_EXPRESSION_PCRE_TO_FILTER_THE_NAMES_AGAINST', true); ?>";
		
		if(!confirm(msgAlert)){
			return false;
		}
		
		var pcre = prompt(msg, '\\.php$');
		if(pcre!=null && pcre != '') {
				var folderActive = jQuery('#folderpath').val();
				form.upload_folder.value = folderActive;
				form.pcre.value = pcre;
				form.task.value = pressbutton;
				form.submit();
				return;
		}
	} else if (pressbutton == 'update_acl_public') {
		jaUpdateACL('update_acl_public', "<?php echo JText::_('DO_YOU_REALLY_WANT_TO_SET_SELECTED_ITEMS_TO_PUBLIC', true); ?>");
	} else if (pressbutton == 'update_acl_private') {
		jaUpdateACL('update_acl_private', "<?php echo JText::_('DO_YOU_REALLY_WANT_TO_SET_SELECTED_ITEMS_TO_PRIVATE', true); ?>");
	} else if (pressbutton == 'update_acl_open') {
		jaUpdateACL('update_acl_open', "<?php echo JText::_('DO_YOU_REALLY_WANT_TO_SET_SELECTED_ITEMS_TO_OPEN', true); ?>");
	} else if (pressbutton == 'pull_s3_files') {
		jaUpdateACLPull(bucket_id,'pull_s3_files', "<?php echo JText::_('DO_YOU_REALLY_WANT_TO_PULL_S3_FILES_TO_LOCAL', true); ?>");
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
	jQuery('#map_profile_id').click(function(){
		this.removeClass('input-focus');
	});
});
/*]]>*/
</script>

<fieldset>
<legend class="heading"><?php echo JText::_("VIEWING_REMOTE_AMAZON_S3_FILES"); ?></legend>
<form action="index.php" method="post" id="adminForm2" name="adminForm2">
  <?php echo JHtml::_( 'form.token' ); ?>
  <input type="hidden" name="option" value="<?php echo JACOMPONENT; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="boxchecked" value="0" />
  <input type="hidden" name="view" value="repo" />
  <input type="hidden" name="upload_folder" value="" />
  <input type="hidden" name="replace_file" value="" />
  <input type="hidden" name="pcre" value="" />
  <input type="hidden" name="new_folder" value="" />
  
  <div id="ja-toolbars">
	   <?php echo JText::_("BUCKETS");?>:
      <?php echo $lists['boxBuckets']; ?>
      <?php echo JText::_("MAPPED_PROFILES");?>:
	  <?php echo $lists['boxMappedProfiles']; ?>
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
<table width="100%" cellspacing="0">
  <tr valign="top">
    <td width="200px">
      <fieldset id="treeview">
      <div id="media-tree_tree"></div>
      <?php echo $this->loadTemplate('folders'); ?>
      </fieldset>
    </td>
    <td>
    <form action="index.php?option=<?php echo JACOMPONENT; ?>&amp;view=file&amp;task=create" name="folderForm" id="folderForm" method="post">
        <fieldset id="folderview">
        <div class="path">
          <strong><?php echo JText::_("WITH_SELECTED"); ?> </strong>
          <select name="ja-box-actions" id="ja-box-actions" onchange="jaSelectActions(this);">
            <option value=""><?php echo JText::_("SELECT_ACTION"); ?></option>
            <optgroup label="<?php echo JText::_("PERMISSIONS"); ?>">
              <option value="update_acl_public"><?php echo JText::_("SET_PUBLIC"); ?></option>
              <option value="update_acl_private"><?php echo JText::_("SET_PRIVATE"); ?></option>
            </optgroup>
            <optgroup label="<?php echo JText::_("OTHERS"); ?>">
              <option value="multi_delete"><?php echo JText::_("DELETE"); ?></option>
            </optgroup>
          </select>
          
          <strong><?php echo JText::_("LOCATION"); ?> </strong>
          <input class="inputbox" type="text" id="folderpath" readonly="readonly" />
          <input class="update-folder" type="hidden" name="folderbase" id="folderbase" value="<?php echo $this->state->folder; ?>" />
        </div>
        <div class="view">
          <iframe src="index.php?option=<?php echo JACOMPONENT; ?>&amp;view=repolist&amp;tmpl=component&amp;folder=<?php echo $this->state->folder;?>" id="folderframe" name="folderframe" width="100%" height="450px;" marginwidth="0" marginheight="0" scrolling="auto" frameborder="0"></iframe>
        </div>
        </fieldset>
        <?php echo JHTML::_( 'form.token' ); ?>
      </form>
      <form action="index.php?option=<?php echo JACOMPONENT; ?>&amp;view=repo" name="adminForm" id="mediamanager-form" method="post" enctype="multipart/form-data" >
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="cb1" id="cb1" value="0" />
        <input class="update-folder" type="hidden" name="folder" id="folder" value="<?php echo $this->state->folder; ?>" />
      </form>
    </td>
  </tr>
</table>
</fieldset>

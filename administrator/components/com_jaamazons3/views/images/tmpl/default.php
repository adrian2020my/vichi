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

// No direct access.
defined('_JEXEC') or die;
$user = JFactory::getUser();
?>
<script type='text/javascript'>
var image_base_path = '';
</script>
<form action="index.php?option=<?php echo JACOMPONENT ?>&amp;view=images&tmpl=component&amp;asset=<?php echo JRequest::getCmd('asset');?>&amp;author=<?php echo JRequest::getCmd('author');?>" id="imageForm" method="post" enctype="multipart/form-data">
	<div id="messages" style="display: none;">
		<span id="message"></span><?php echo JHtml::_('image', 'media/dots.gif', '...', array('width' =>22, 'height' => 12), true)?>
	</div>
	<fieldset>
		<div class="fltlft">
			<?php echo JText::_("BUCKETS");?>:
      		<?php echo $this->boxBuckets; ?>
      		<div style="display:none;">
      		<select size="1" class="inputbox" name="folderlist" id="folderlist">
      		</select>
			<button type="button" id="upbutton" title="<?php echo JText::_('UP') ?>"><?php echo JText::_('UP') ?></button>
      		</div>
		</div>
		<div class="fltrt">
			<button type="button" onclick="<?php if ($this->state->get('field.id')):?>window.parent.jInsertFieldValue(document.id('f_url').value,'<?php echo $this->state->get('field.id');?>');<?php else:?>ImageManager.onok();<?php endif;?>window.parent.SqueezeBox.close();"><?php echo JText::_('JA_INSERT') ?></button>
			<button type="button" onclick="window.parent.SqueezeBox.close();"><?php echo JText::_('JCANCEL') ?></button>
		</div>
	</fieldset>

	<iframe id="imageframe" name="imageframe" style="height:360px;" src="index.php?option=<?php echo JACOMPONENT ?>&amp;view=imageslist&amp;tmpl=component&amp;folder=<?php echo $this->state->folder?>&amp;asset=<?php echo JRequest::getCmd('asset');?>&amp;author=<?php echo JRequest::getCmd('author');?>"></iframe>

	<fieldset>
		<table class="properties">
			<tr>
				<td><label for="f_url"><?php echo JText::_('JA_IMAGE_URL') ?></label></td>
				<td><input type="text" id="f_url" value="" /></td>
				<?php if (!$this->state->get('field.id')):?>
					<td><label for="f_align"><?php echo JText::_('JA_ALIGN') ?></label></td>
					<td>
						<select size="1" id="f_align" >
							<option value="" selected="selected"><?php echo JText::_('JA_NOT_SET') ?></option>
							<option value="left"><?php echo JText::_('JGLOBAL_LEFT') ?></option>
							<option value="right"><?php echo JText::_('JGLOBAL_RIGHT') ?></option>
						</select>
					</td>
					<td> <?php echo JText::_('JA_ALIGN_DESC');?> </td>
				<?php endif;?>
			</tr>
			<?php if (!$this->state->get('field.id')):?>
				<tr>
					<td><label for="f_alt"><?php echo JText::_('JA_IMAGE_DESCRIPTION') ?></label></td>
					<td><input type="text" id="f_alt" value="" /></td>
				</tr>
				<tr>
					<td><label for="f_title"><?php echo JText::_('JA_TITLE') ?></label></td>
					<td><input type="text" id="f_title" value="" /></td>
					<td><label for="f_caption"><?php echo JText::_('JA_CAPTION') ?></label></td>
					<td>
						<select size="1" id="f_caption" >
							<option value="" selected="selected" ><?php echo JText::_('JNO') ?></option>
							<option value="1"><?php echo JText::_('JYES') ?></option>
						</select>
					</td>
					<td> <?php echo JText::_('JA_CAPTION_DESC');?> </td>
				</tr>
			<?php endif;?>
		</table>

		<input type="hidden" id="dirPath" name="dirPath" />
		<input type="hidden" id="f_file" name="f_file" />
		<input type="hidden" id="tmpl" name="component" />

	</fieldset>
</form>
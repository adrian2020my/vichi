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
$config = JComponentHelper::getParams('com_media');
$session = JFactory::getSession();
?>
<?php if (count($this->images) > 0 || count($this->folders) > 0) { ?>
<div class="manager">
	<?php echo $this->loadTemplate('up'); ?>
	<?php for ($i=0, $n=count($this->folders); $i<$n; $i++) :
		$this->setFolder($i);
		echo $this->loadTemplate('folder');
	endfor; ?>

	<?php for ($i=0, $n=count($this->images); $i<$n; $i++) :
		$this->setImage($i);
		echo $this->loadTemplate('image');
	endfor; ?>

</div>
<?php } else { ?>
	<div id="media-noimages">
		<?php echo $this->loadTemplate('up'); ?>
		<p><?php echo JText::_('JA_NO_IMAGES_FOUND'); ?></p>
	</div>
<?php } ?>


<?php if ($user->authorise('core.create', 'com_media')): ?>
<div class="upload-manager" style="clear:both; padding:20px 0 0 0;">
	<form action="<?php echo JURI::base(); ?>index.php?option=com_jaamazons3&amp;view=file&amp;task=upload&amp;tmpl=component&amp;<?php echo $session->getName().'='.$session->getId(); ?>&amp;<?php echo JSession::getFormToken();?>=1&amp;asset=<?php echo JRequest::getCmd('asset');?>&amp;author=<?php echo JRequest::getCmd('author');?>" id="uploadForm" name="uploadForm" method="post" enctype="multipart/form-data">
		<fieldset id="uploadform">
			<legend><?php echo $config->get('upload_maxsize')=='0' ? JText::_('JA_UPLOAD_FILES_NOLIMIT') : JText::sprintf('JA_UPLOAD_FILES', $config->get('upload_maxsize')); ?></legend>
			<fieldset id="upload-noflash" class="actions">
				<label for="upload-file" class="hidelabeltxt"><?php echo JText::_('JA_UPLOAD_FILE'); ?></label>
				<input type="file" id="upload-file" name="Filedata[]" multiple />
				<label for="upload-submit" class="hidelabeltxt"><?php echo JText::_('JA_START_UPLOAD'); ?></label>
				<input type="submit" id="upload-submit" value="<?php echo JText::_('JA_START_UPLOAD'); ?>"/>
			</fieldset>
			<input type="hidden" name="return-url" value="<?php echo base64_encode('index.php?option=com_jaamazons3&view=imageslist&tmpl=component&folder='.JRequest::getCmd('folder').'&e_name='.JRequest::getCmd('e_name').'&asset='.JRequest::getCmd('asset').'&author='.JRequest::getCmd('author')); ?>" />
            <input type="hidden" name="folder" value="<?php echo JRequest::getCmd('folder') ?>" />
		</fieldset>
	</form>
</div>
<?php  endif; ?>

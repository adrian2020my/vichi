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
?>
<?php if(JRequest::getCmd('folder') != '' && JRequest::getCmd('folder') != '/'): ?>
<div class="item">
	<a href="index.php?option=<?php echo JACOMPONENT ?>&amp;view=imageslist&amp;tmpl=component&amp;folder=<?php echo $this->state->parent; ?>&amp;asset=<?php echo JRequest::getCmd('asset');?>&amp;author=<?php echo JRequest::getCmd('author');?>">
		<?php echo JHtml::_('image', 'media/folderup_32.png', '..', array(), true); ?>
		<span><?php echo JText::_('JA_UP'); ?></span></a>
</div>
<?php endif; ?>
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
$params = new JRegistry;
?>
		<div class="item" style="display: block; width: 80px; height: 90px; overflow:hidden;">
			<a href="javascript:ImageManager.populateFields('<?php echo $this->baseURL.$this->_tmp_img->path_relative; ?>')" title="<?php echo $this->_tmp_img->name; ?>" >
				<?php echo JHtml::_('image', $this->baseURL.$this->_tmp_img->path_relative, $this->_tmp_img->name, array('style' => 'max-with:60px; max-height:60px;')); ?>
				<span title="<?php echo $this->_tmp_img->name; ?>"><?php echo $this->_tmp_img->name; ?></span>
			</a>
		</div>

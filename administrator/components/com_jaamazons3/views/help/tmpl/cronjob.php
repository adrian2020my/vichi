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
?>
<fieldset>
<legend class="heading"><?php echo JText::_("CRONJOB_SETTING");?></legend>
<h2>1. Cron job</h2>
<div>
Cron job are scheduled tasks that allow uploading data to server at predefined times or intervals on the server.
</div>

<h2>2. Cron job mode</h2>
<div>
<ul>
<li>+Pseudo Cron mode : Uploading will be executed automatically without any configuration.</li>
<li>+System Cron mode : Uploading will be executed after creating cron job tab using URL :[<a href="<?php echo $this->cronUrl; ?>" title="Cron Job Url" target="_blank"><?php echo $this->cronUrl; ?></a>] to upload files on your hosting server</li>
</ul>
</div>

<h3>Note:</h3>
<div class="ja-note" style="color:#E34915;">
 Be careful when scheduling cron jobs. Setting them to run too often may slow down your server.
 You can get upload url for specific folder of profile on "Local File Manager" page. 
</div>
</fieldset>

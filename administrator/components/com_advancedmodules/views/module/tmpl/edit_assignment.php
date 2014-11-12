<?php
/**
 * @package         Advanced Module Manager
 * @version         4.17.0
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2014 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

$this->config->show_assignto_groupusers = (int) (
	$this->config->show_assignto_usergrouplevels
);


$assignments = array(
	'menuitems',
	'homepage',
	'date',
	'groupusers',
	'languages',
	'templates',
	'urls',
	'os',
	'browsers',
	'components',
	'content',
);
foreach ($assignments as $i => $ass)
{
	if ($ass != 'menuitems' && (!isset($this->config->{'show_assignto_' . $ass}) || !$this->config->{'show_assignto_' . $ass}))
	{
		unset($assignments[$i]);
	}
}

$html = array();
if ($this->config->layout == 'slides')
{
	$html[] = JHtml::_('sliders.panel', JText::_('AMM_MODULE_ASSIGNMENT'), 'assignment-options');
	$html[] = '<fieldset class="panelform assignment-options">';
}
else
{
	$html[] = JHtml::_('tabs.panel', JText::_('AMM_MODULE_ASSIGNMENT'), 'tab-assignments');
	$html[] = '<div class="width-100 fltlft">';
	$html[] = '<fieldset class="adminform assignment-options">';
}
$html[] = '<ul class="adminformlist">';
$html[] = $this->render($this->assignments, 'assignments');

$html[] = $this->render($this->assignments, 'mirror_module');
$html[] = '</ul>';
$html[] = '<div style="clear: both;"></div>';
$html[] = '<div id="' . rand(1000000, 9999999) . '___mirror_module.0" class="nntoggler">';
$html[] = '<ul class="adminformlist">';

if (count($assignments) > 1)
{
	$html[] = $this->render($this->assignments, 'match_method');
	$html[] = $this->render($this->assignments, 'show_assignments');
}
else
{
	$html[] = '<input type="hidden" name="show_assignments" value="1" />';
}

foreach ($assignments as $ass)
{
	$html[] = $this->render($this->assignments, 'assignto_' . $ass);
}

$show_assignto_users = 0;
$html[] = '<input type="hidden" name="show_users" value="' . $show_assignto_users . '" />';
$html[] = '<input type="hidden" name="show_usergrouplevels" value="' . (int) $this->config->show_assignto_usergrouplevels . '" />';

$html[] = '</div>';

$html[] = '</ul>';
$html[] = '</fieldset>';
if ($this->config->layout != 'slides')
{
	$html[] = '</div>';
	$html[] = '<div class="clr"></div>';
}

echo implode("\n\n", $html);

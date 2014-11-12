<?php
/**
 * ------------------------------------------------------------------------
 * JA Amazon S3 for joomla 2.5 & 3.1
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// Try extending time, as unziping/ftping took already quite some...
@set_time_limit(240);
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.installer.installer');
jimport('joomla.installer.helper');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
/**
 * Install sub packages and show installation result to user
 * 
 * @return void
 */
class com_jaamazons3InstallerScript
{
	function update($parent) {
		// run update script
	}

	function preflight($type, $parent) {
		// Run preflight if possible (since we know we're not an update)
	}

	function postflight($type, $parent) {
		// run the postflight
	}
    public function install($parent)
	{
		
		// Load Component JA Google Storage language file
		$lang = &JFactory::getLanguage();
		$lang->load('com_jaamazons3');
		$component_name = JText::_('COMPONENT_JA_AMAZONS3');
	
		// Install sub packages
		$app = JFactory::getApplication();
	
		$messages = array();
	
		// Installing sub-extensions
		$p_dir = JPath::clean(JPATH_SITE.'/components/com_jaamazons3/packages');
		$config =JFactory::getConfig();
	
		if (!JFolder::exists($p_dir)){
			$messages[] = JText::_('Package directory(Related modules, plugins) is missing');
		} else {
			$subpackages = JFolder::files($p_dir, '\.zip$');
			$result = true;
			$installer = new JInstaller();
			$extensions = array();
			if ($subpackages) {
				foreach ($subpackages as $zpackage) {
					$subpackage = JInstallerHelper::unpack($p_dir.'/'.$zpackage);
	
					$ext = new stdClass();
					$ext->name = $zpackage;
					$ext->title = JFile::stripExt($zpackage);
					$ext->type = JText::_('Invalid');
					$ext->msg = '';
					$ext->status = 0;
					if ($subpackage) {
						$type = JInstallerHelper::detectType($subpackage['dir']);
						if (! $type) {
							$ext->msg = JText::_("Invalid extension");
							$result = false;
						} else {
							$ext->type = $type;
	
							if (! $installer->install($subpackage['dir'])) {
								$ext->msg = JText::_("Install Fail");
							} else {
								$ext->status = 1;
							}
						}
	
						//Remove tmp folder
						if (! JFile::exists($subpackage['packagefile'])) {
							$subpackage['packagefile'] = $p_dir.'/'.$subpackage['packagefile'];
						}
						if (JFolder::exists($subpackage['extractdir'])) {
							JFolder::delete($subpackage['extractdir']);
						}
						if (JFile::exists($subpackage['packagefile'])) {
							JFile::delete($subpackage['packagefile']);
						}
					} else {
						$ext->msg = JText::_("Invalid extension");
					}
	
					$extensions[] = $ext;
	
				}
			}
			JFolder::delete($p_dir);
			
			//enable extensions
			$db = JFactory::getDbo();
			
			$manifest = $parent->get('manifest');
			$modules = $manifest->xpath('package_elements/modules/module');
			$plugins = $manifest->xpath('package_elements/plugins/plugin');
			//uninstal modules
			foreach($modules as $module){
				$mname = $module->attributes()->module;
				$client = $module->attributes()->client;
				$query = "UPDATE `#__extensions` SET enabled = 1 WHERE `type`='module' AND element = ".$db->Quote($mname);
				$db->setQuery($query);
				$db->query();
			}
			//uninstal plugin
			foreach ($plugins as $plugin) {
				$pname = $plugin->attributes()->plugin;
				$pgroup = $plugin->attributes()->group;
				$query = "UPDATE #__extensions SET enabled = 1 WHERE `type`='plugin' AND element = ".$db->Quote($pname)." AND folder = ".$db->Quote($pgroup);
				$db->setQuery($query);
				$db->query();
			}
		}
	
	    ?>
    
		<div style="text-align: left;">
			<div style="padding:20px; text-align: center;">
				<div style="font-size: 38px;text-descoration: bold;"><?php echo JText::_('ALMOST_DONE'); ?></div>
				<h3><?php echo JText::_('VIDEO_GUIDELINE_MESSAGE'); ?></h3>
				<h2><?php echo JText::_('OVERVIEW_AND_GUIDELINE'); ?></h2>
				<object width="640" height="385"><param name="movie" value="http://www.youtube.com/v/dtknH2ljq5Q?fs=1&amp;hl=en_US"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/dtknH2ljq5Q?fs=1&amp;hl=en_US" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="640" height="385"></embed></object>
			</div>
		
			<?php $rows = 0;?>
			<h3><?php echo JText::_('Installation Status'); ?></h3>
			<table class="adminlist table table-striped">
			  <thead>
			    <tr>
			      <th><?php echo JText::_('Extension'); ?></th>
			      <th><?php echo JText::_('Type'); ?></th>
			      <th><?php echo JText::_('Status'); ?></th>
			      <th>&nbsp;</th>
			    </tr>
			  </thead>
			  <tfoot>
			    <tr>
			      <td colspan="4"></td>
			    </tr>
			  </tfoot>
			  <tbody>
			    <tr class="row0">
			      <td class="key"><strong>JA Amazon S3 Component</strong></td>
			      <td class="key"><strong>Component</strong></td>
			      <td><?php echo '<span style="color:green;">'.JText::_('INSTALLED').'</span>'; ?></td>
			      <th>&nbsp;</th>
			    </tr>
			    <?php if (count($extensions)) : ?>
			    <?php foreach ($extensions as $module) : ?>
			    <tr class="row<?php echo (++ $rows % 2); ?>">
			      <td class="key"><?php echo $module->title; ?></td>
			      <td class="key"><?php echo ucfirst($module->type); ?></td>
			      <td><?php echo ($module->status) ? '<span style="color:green;">'.JText::_('INSTALLED').'</span>' : '<span style="color:red;">'.JText::_('NOT_INSTALLED').'</span>'; ?></td>
			      <th><?php echo $module->msg; ?></th>
			    </tr>
			    <?php endforeach;?>
			    <?php endif;?>
			    
			  </tbody>
			</table>
		</div>
		<?php
	}
	
	public function uninstall($parent)
	{
		// Load Component JA Amazon S3 language file
		$lang = &JFactory::getLanguage();
		$lang->load('com_jaamazons3');
		$component_name = JText::_('COMPONENT_JA_AMAZONS3');
		
		$status = new JObject();
		$status->modules = array ();
		$status->plugins = array ();
		
		$db = JFactory::getDbo();
		$manifest = $parent->get('manifest');
		$modules = $manifest->xpath('package_elements/modules/module');
		$plugins = $manifest->xpath('package_elements/plugins/plugin');
		//uninstal modules
		foreach($modules as $module){
			$mname = $module->attributes()->module;
			$client = $module->attributes()->client;
			$query = "SELECT `extension_id` FROM `#__extensions` WHERE `type`='module' AND element = ".$db->Quote($mname);
			$db->setQuery($query);
			$IDs = $db->loadColumn();
			$result = true;
			if (count($IDs)) {
				foreach ($IDs as $id) {
					$installer = new JInstaller;
					$result &= $installer->uninstall('module', $id);
				}
			}
			$status->modules[] = array ('name'=>$mname, 'client'=>$client, 'result'=>$result);
		}
		//uninstal plugin
		foreach ($plugins as $plugin) {
			$pname = $plugin->attributes()->plugin;
			$pgroup = $plugin->attributes()->group;
			$query = "SELECT `extension_id` FROM #__extensions WHERE `type`='plugin' AND element = ".$db->Quote($pname)." AND folder = ".$db->Quote($pgroup);
			$db->setQuery($query);
			$IDs = $db->loadColumn();
			$result = true;
			if (count($IDs)) {
				foreach ($IDs as $id) {
					$installer = new JInstaller;
					$result &= $installer->uninstall('plugin', $id);
				}
			}
			$status->plugins[] = array ('name'=>$pname, 'group'=>$pgroup, 'result'=>$result);
		}
		?>

		<?php $rows = 0;?>
		<h2><?php echo JText::_('JA_COMPONENT_REMOVAL_STATUS'); ?></h2>
		<table class="adminlist table table-striped">
		  <thead>
		    <tr>
		      <th class="title" colspan="2"><?php echo JText::_('EXTENSION'); ?></th>
		      <th width="30%"><?php echo JText::_('STATUS'); ?></th>
		    </tr>
		  </thead>
		  <tfoot>
		    <tr>
		      <td colspan="3"></td>
		    </tr>
		  </tfoot>
		  <tbody>
		    <tr class="row0">
		      <td class="key" colspan="2"><?php echo $component_name; ?></td>
		      <td><strong><?php echo JText::_('REMOVED'); ?></strong></td>
		    </tr>
		    <?php if (count($status->modules)) : ?>
		    <tr>
		      <th><?php echo JText::_('MODULE'); ?></th>
		      <th><?php echo JText::_('CLIENT'); ?></th>
		      <th></th>
		    </tr>
		    <?php foreach ($status->modules as $module) : ?>
		    <tr class="row<?php echo (++ $rows % 2); ?>">
		      <td class="key"><?php echo $module['name']; ?></td>
		      <td class="key"><?php echo ucfirst($module['client']); ?></td>
		      <td><strong><?php echo ($module['result'])?JText::_('REMOVED'):JText::_('NOT_REMOVED'); ?></strong></td>
		    </tr>
		    <?php endforeach;?>
		    <?php endif; ?>
		    <?php if (count($status->plugins)) : ?>
		    <tr>
		      <th><?php echo JText::_('PLUGIN'); ?></th>
		      <th><?php echo JText::_('GROUP'); ?></th>
		      <th></th>
		    </tr>
		    <?php foreach ($status->plugins as $plugin) : ?>
		    <tr class="row<?php echo (++ $rows % 2); ?>">
		      <td class="key"><?php echo ucfirst($plugin['name']); ?></td>
		      <td class="key"><?php echo ucfirst($plugin['group']); ?></td>
		      <td><strong><?php echo ($plugin['result'])?JText::_('REMOVED'):JText::_('NOT_REMOVED'); ?></strong></td>
		    </tr>
		    <?php endforeach; ?>
		    <?php endif; ?>
		  </tbody>
		</table>
		<?php
	}
}
?>
<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage UpdatesMigration
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2014 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default_update.php 3274 2011-05-17 20:43:48Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if (!class_exists( 'VmConfig' )) require(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'config.php');
VmConfig::loadConfig();

VmConfig::loadJLang('com_virtuemart.sys');
VmConfig::loadJLang('com_virtuemart');

$update = vRequest::getInt('update',0);
$option = vRequest::getString('option');

if($option=='com_virtuemart'){

	if (!class_exists('AdminUIHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'adminui.php');
	if (!class_exists('JToolBarHelper')) require(JPATH_ADMINISTRATOR.DS.'includes'.DS.'toolbar.php');
	AdminUIHelper::startAdminArea($this);
}
 ?>
<link
	rel="stylesheet"
	href="components/com_virtuemart/assets/css/install.css"
	type="text/css" />
<link
	rel="stylesheet"
	href="components/com_virtuemart/assets/css/toolbar_images.css"
	type="text/css" />

<div align="center">
	<table
		width="100%"
		border="0">
		<tr>
			<td
				valign="top"
				align="center"><a
				href="http://virtuemart.net"
				target="_blank"> <img
					border="0"
					align="center"
					src="components/com_virtuemart/assets/images/vm_menulogo.png"
					alt="Cart" /> </a> <br /> <br />
				<h2>
				 Welcome to VirtueMart<br />
The complete e-Commerce shopping cart solution for Joomla</h2>
			</td>
		<tr>
			<td>
				<strong>
					<?php
					if($update){
						echo  vmText::_('COM_VIRTUEMART_UPGRADE_SUCCESSFUL');
					} else {
						echo vmText::_('COM_VIRTUEMART_INSTALLATION_SUCCESSFUL');
					}
					?>
				</strong>

			</td>
		</tr>
		<?php  if (vRequest::get('view','')=='install') { ?>
			<tr>
				<td>
					<strong style="color: #C00">
						<?php
						if ($update) {
							echo vmText::_('COM_VIRTUEMART_UPDATE_AIO');
						} else {
							echo vmText::_('COM_VIRTUEMART_INSTALL_AIO');
						}
						?>
					</strong>
					<?php echo vmText::_('COM_VIRTUEMART_INSTALL_AIO_TIP'); ?>
				</td>
			</tr>
		<?php
		}

		/*
				//We do this dirty here, is just the finish page for installation, we must know if we are allowed to add sample data
				$db = JFactory::getDbo();
				$q = 'SELECT count(*) FROM `#__virtuemart_products` WHERE `virtuemart_product_id`!="0" ';
				$db->setQuery($q);
				$productsExists = $db->loadResult();
				if(!$productsExists){
				?>
				<tr>
					<td>
						<strong>
							<?php
								echo vmText::_('COM_VIRTUEMART_INSTALL_SAMPLE_DATA_OPTION').' '.vmText::_('COM_VIRTUEMART_INSTALL_SAMPLE_DATA');
							?>
						</strong>
						<?php echo vmText::_('COM_VIRTUEMART_INSTALL_SAMPLE_DATA_TIP'); ?>

						<div id="cpanel">
							<?php
								?>
								<div class="icon">
									<a class="btn btn-primary"
									   href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=installSampleData&'.JSession::getFormToken().'=1') ?>">
										<span class="vmicon48 vm_install_48"></span> <br />
										<?php echo vmText::_('COM_VIRTUEMART_INSTALL_SAMPLE_DATA'); ?>
									</a>
								</div>
						</div>
					</td>
				</tr>
			<?php }
		*/
		?>
		<tr>
			<td>
				<?php echo vmText::sprintf('COM_VIRTUEMART_MORE_LANGUAGES','http://virtuemart.net/community/translations'); ?>

			</td>
		</tr>
		<tr>
			<td>
				<a href="http://docs.virtuemart.net"><?php echo vmText::_('COM_VIRTUEMART_DOCUMENTATION'); ?></a>

			</td>
		</tr>
		<tr>
			<td>
				<a href="http://extensions.virtuemart.net"><?php echo  vmText::_('COM_VIRTUEMART_EXTENSIONS_MORE'); ?></a>

			</td>
		</tr>
	</table>
</div>
<?php
if($option=='com_virtuemart'){
	AdminUIHelper::endAdminArea();
}

?>

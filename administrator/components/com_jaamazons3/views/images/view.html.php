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

defined('_JEXEC') or die;

/**
 * HTML View class for the Media component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_media
 * @since 1.0
 */
class jaAmazonS3ViewImages extends JViewLegacy
{
	function display($tpl = null)
	{
		$config = JComponentHelper::getParams(JACOMPONENT);
		$app	= JFactory::getApplication();
		$lang	= JFactory::getLanguage();
		$append = '';
		$assets = JURI::root().'administrator/components/'.JACOMPONENT.'/assets/';

		JHtml::_('behavior.framework', true);
		JHtml::_('script', $assets. 'repo_manager/popup-imagemanager.js', true, true);
		JHtml::_('stylesheet', 'media/popup-imagemanager.css', array(), true);

		if ($lang->isRTL()) {
			JHtml::_('stylesheet', 'media/popup-imagemanager_rtl.css', array(), true);
		}

		$model = JModelLegacy::getInstance ( 'repo', 'jaAmazonS3Model' );
		$lists = $model->_getVars_admin();
		
		$modelBucket = JModelLegacy::getInstance ( 'bucket', 'jaAmazonS3Model' );
		$boxBuckets = $modelBucket->getBoxBuckets('bucket_id', $lists['bucket_id'], "onchange=\"this.form.submit();\"");
		
		$this->assign('boxBuckets', $boxBuckets);
		$this->session = JFactory::getSession();
		$this->config = $config;
		$this->state = $model->getState();
		//$this->folderList = $this->get('folderList');

		parent::display($tpl);
	}
}

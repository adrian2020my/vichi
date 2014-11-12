<?php
/**
 * @desc Modify from component Media Manager of Joomla
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Media component
 *
 * @static
 * @package		Joomla
 * @subpackage	Media
 * @since 1.0
 */
class jaAmazonS3ViewRepolist extends JViewLegacy 
{
	function display($tpl = null)
	{
		$mainframe = JFactory::getApplication('administrator');

		// Do not allow cache
		JResponse::allowCache(false);

		//$style = JRequest::getCmd( 'layout', 'details' );
		$style = "details";

		JHtml::_('behavior.framework', true);

		$document = &JFactory::getDocument();
		$document->addStyleSheet('components/'.JACOMPONENT.'/assets/repo_manager/repolist-'.$style.'.css');

		$document->addScriptDeclaration("
		window.addEvent('domready', function() {
			window.top.document.updateUploader && window.top.document.updateUploader();
			$$('a.img-preview').each(function(el) {
				el.addEvent('click', function(e) {
					new Event(e).stop();
					window.top.document.preview.fromElement(el);
				});
			});
		});");
		
		$modelRepo = JModelLegacy::getInstance ( 'repo', 'jaAmazonS3Model' );
		$bucket = $modelRepo->getActiveBucket();
		$s3Url = jaStorageHelper::getDistributeUrl($bucket);

		$this->assign('baseURL', JURI::root());
		$this->assignRef('bucket', $bucket);
		$this->assignRef('s3Url', $s3Url);
		$this->assignRef('images', $this->get('images'));
		$this->assignRef('documents', $this->get('documents'));
		$this->assignRef('folders', $this->get('folders'));
		$this->assignRef('state', $this->get('state'));

		parent::display($tpl);
	}

	function setFolder($index = 0)
	{
		if (isset($this->folders[$index])) {
			$this->_tmp_folder = &$this->folders[$index];
		} else {
			$this->_tmp_folder = new JObject;
		}
	}

	function setImage($index = 0)
	{
		if (isset($this->images[$index])) {
			$this->_tmp_img = &$this->images[$index];
		} else {
			$this->_tmp_img = new JObject;
		}
	}

	function setDoc($index = 0)
	{
		if (isset($this->documents[$index])) {
			$this->_tmp_doc = &$this->documents[$index];
		} else {
			$this->_tmp_doc = new JObject;
		}
	}
	
	function setStatus($item) {
		$modelRepo = JModelLegacy::getInstance ( 'repo', 'jaAmazonS3Model' );
		return $modelRepo->checkItemStatus($item);
	}
	
}

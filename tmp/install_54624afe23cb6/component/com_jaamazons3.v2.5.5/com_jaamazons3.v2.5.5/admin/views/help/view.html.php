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

jimport ( 'joomla.application.component.view' );


class jaAmazonS3ViewHelp extends JViewLegacy {
	
	function display($tpl = null) {
		jaComponentMenuHeader();
		
		switch ($this->getLayout ()) {
			case 'cronjob' :
				$this->displayCronjob ();
				break;
			case 'help' :
			default :
				$this->displayHelp ();
				break;
		}
		parent::display ( $tpl );

		jaComponentMenuFooter();
	}
	
	function displayHelp() {
		
	}
	
	function displayCronjob() {
		$params = JComponentHelper::getParams(JACOMPONENT);
		$uploadKey = $params->get('upload_secret_key', '');
		
		$cronUrl = JURI::root()."administrator/components/".JACOMPONENT."/cron.php";
		
		$this->assign('cronUrl', $cronUrl);
		$this->assign('params', $params);
	}
}	
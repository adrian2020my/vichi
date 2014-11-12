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

defined ( '_JEXEC' ) or die ( 'Restricted access' );

jimport ( 'joomla.application.component.controller' );

class jaAmazonS3Controller extends JControllerLegacy {
	function display($cachable = false, $urlparams = false) {
		$view = JRequest::getVar("view");
		if (empty($view)) {
			JRequest::setVar("view", "account");
		}
		parent::display();
	}

	function getLink() {
		return "index.php?option=".JACOMPONENT;
	}
	
	function redirectParent($url, $msg, $type = 'message') {
		$messageQueue = array();
		if(is_array($msg)) {
			if(count($msg)) {
				foreach ($msg as $m) {
					$messageQueue[] = array('message' => $m, 'type' => strtolower($type));
				}
			}
		} else {
			$messageQueue[] = array('message' => $msg, 'type' => strtolower($type));
		}
		$session = JFactory::getSession();
		$session->set('application.queue', $messageQueue);
		
		echo "<script type=\"text/javascript\">var win = window.parent ? window.parent : window; win.document.location.href='$url';</script>\n";
		exit();
	}
}

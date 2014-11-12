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

jimport('joomla.application.component.helper');

class jaStorageHelper 
{
	function jaStorageHelper() {
		
	}
	
	function getStorage($account) {
		$app = JFactory::getApplication();
		if(!is_object($account) || !isset($account->acc_accesskey) || !isset($account->acc_secretkey)) {
			JError::raiseError(400, JText::_("THE_ACCOUNT_IS_NOT_VALID!"));
		}
		
		$storage = new AmazonS3(array('key' => $account->acc_accesskey, 'secret' => $account->acc_secretkey));
		if($app->getCfg('force_ssl') != 2) {
			//$storage->disable_ssl();
			$storage->use_ssl = false;//do not use CFRuntime::disable_ssl method since it raise a warning message
		}
		
		return $storage;
	}
		
	function getDistributeUrl($bucket) {
		if(!is_object($bucket)) {
			return '#';
		}
		
		$protocol = (isset($bucket->bucket_protocol) && !empty($bucket->bucket_protocol)) ? $bucket->bucket_protocol : 'http';
		
		if(isset($bucket->bucket_cloudfront_domain) && !empty($bucket->bucket_cloudfront_domain)) {
			$url = $bucket->bucket_cloudfront_domain;
		} else {
			$format = (isset($bucket->bucket_url_format) && !empty($bucket->bucket_url_format)) ? $bucket->bucket_url_format : 'subdomain';
			if($format == 'folder') {
				$url = "{protocol}://s3.amazonaws.com/{bucket}/";
			} else {
				$url = "{protocol}://{bucket}.s3.amazonaws.com/";
			}
		}
		$url = str_replace('{protocol}', $protocol, $url);
		$url = str_replace('{bucket}', $bucket->bucket_name, $url);
		
		//correct distribute url
		if(!preg_match("/^\w+\:\/\//", $url)) {
			$url = $protocol."://".$url;
		}
		$url = rtrim($url, '/') . '/';
		
		return $url;
	}
	
	function cleanPath($path, $removeLastSlashes = true) {
		$path = (string) $path;
		$path = trim($path);
		if(!empty($path)) {
			/*//add slash at the end of path
			$path .= '/';*/
			//clean slashes
			$path = JPath::clean($path, '/');
			//remove first slashes
			$path = preg_replace("/^\/*/", '', $path);
			//remove last slashes
			if($removeLastSlashes) {
				$path = preg_replace("/\/*$/", '', $path);
			}
			//encode url
			//$path = urlencode($path);
		}
		return $path;
	}
	
	function nicetime($date){
		if(empty($date)) {
			return JText::_("NO_DATE_PROVIDED");
		}
	   
		$periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
		$lengths         = array("60","60","24","7","4.35","12","10");
	   
		$now             = time();
		$unix_date       = strtotime($date);
	   
		// check validity of date
		if(empty($unix_date)) {   
			return false;//Bad date
		}
	
		// is it future date or past date
		if($now > $unix_date) {   
			$difference     = $now - $unix_date;
			$tense         = JText::_("AGO");
		} else {
			$difference     = $unix_date - $now;
			$tense         = JText::_("FROM_NOW");
		}
		
		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
			$difference /= $lengths[$j];
		}
	   
		$difference = round($difference);
	   
		if($difference != 1) {
			$periods[$j].= "s";
		}
		
		return "{$difference} {$periods[$j]} {$tense}";
	}
	
	function showIcon($img, $alt='', $title='', $desc = '', $showText = 1) {
		if(empty($title)) {
			$title = $alt;
		}
		if(!empty($desc)) $title .= ' - '.$desc;
		$img = '<img src="components/'.JACOMPONENT.'/assets/images/icons/'.$img.'" border="0" alt="'.$alt.'" title="'.$title.'" />';
		if($showText) {
			$img .= ' ' . $alt;
		}
		return $img;
	}
}
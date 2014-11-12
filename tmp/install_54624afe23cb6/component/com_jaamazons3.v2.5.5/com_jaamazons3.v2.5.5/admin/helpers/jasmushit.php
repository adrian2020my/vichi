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

class jaSmushItHelper 
{
	var $ws_url = 'http://www.smushit.com/ysmush.it/ws.php?';
	var $src_type = 'file';
	var $src = '';
	var $src_size = 0;
	var $dest = '';
	var $dest_size = 0;
	var $percent = 0;
	var $id = '';
	
	var $error = '';
	
	function jaSmushItHelper() {
		
	}
	
	/**
	 * @param string $file - you can provide an absolute path, or an url to your site
	 * @return unknown
	 */
	function smush($file) {
		if(is_file($file)) {
			$this->src_type = 'file';
		} else {
			$this->src_type = 'url';
		}
		
		// reset info
		$this->src = $file;
		$this->src_size = '';
		$this->dest = '';
		$this->dest_size = '';
		$this->percent = '';
		$this->id = '';
		//
		$ws_url = $this->ws_url;
		if($this->src_type == 'url') {
			$ws_url .= 'img=' . urlencode($this->src);
		}
		
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ws_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        if($this->src_type == 'file') {
        	curl_setopt($ch, CURLOPT_POST, true);
        	curl_setopt($ch, CURLOPT_POSTFIELDS, array('files' => '@' . $this->src));
        }
        $json = curl_exec($ch);
        if($json === false) {
			$this->error = curl_error($ch);
			return false;
        }
        curl_close($ch);

        $result = json_decode($json);
        
        if(!$result || !is_object($result)) {
        	// smush fail
        	$this->error = JText::_("Bad response from smushit.com");
        	return false;
        }
        
        if(isset($result->error)) {
        	$this->error = $result->error;
        	return false;
        }
		
        $this->src_size 	= $result->src_size;
        $this->dest 		= $result->dest;
        $this->dest_size 	= $result->dest_size;
        $this->percent 		= $result->percent;
        $this->id 			= $result->id;
        
        return true;
	}
	
	function getData() {
		$arr = array(
			'src' 			=> $this->src,
			'srcSize' 		=> $this->formatBytes($this->src_size, 2),
			'dest' 			=> $this->dest,
			'destSize' 		=> $this->formatBytes($this->dest_size, 2),
			'savings' 		=> $this->formatBytes($this->src_size - $this->dest_size, 2),
			'percent' 		=> $this->percent,
			'id' 			=> $this->id
		);
		
		return (object) $arr;
	}
	
	function getError() {
		return $this->error;
	}

	/**
	 * @link http://www.php.net/manual/en/function.filesize.php#91477
	 * @author nak5ive at DONT-SPAM-ME dot gmail dot com
	 *
	 * @param int $bytes
	 * @param int $precision
	 * @return string - the filesize in a humanly readable format.
	 */
	function formatBytes($bytes, $precision = 2) {
		$units = array('B', 'KB', 'MB', 'GB', 'TB');

		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);

		$bytes /= pow(1024, $pow);

		return round($bytes, $precision) . ' ' . $units[$pow];
	}
}
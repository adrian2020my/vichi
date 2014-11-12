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

class JAFileHelpers {
	function files($path, $filter = '.', $recurse = false, $fullpath = false, $exclude = array('.svn', 'CVS'))
	{
		$exclude[] = '.svn';
		$exclude[] = 'CVS';
		
		$exclude = array_unique($exclude);
		
		$path = preg_replace("#[/\\\\]+$#", '', $path);
		$path = JPath::clean($path);
		
		//check if disabled by ja amazon s3
		if(in_array($path, $exclude)) {
			return array();
		}
		
		//die("400|".print_r($exclude, true));
		
		$arr = JAFileHelpers::_files($path.'/', $path, $filter, $recurse, $fullpath, $exclude);
		if(!$arr) {
			$arr = array();
		}
		return $arr;
	}
	
	function _files($path, $basePath, $filter = '.', $recurse = false, $fullpath = false, $exclude = array('.svn', 'CVS'))
	{
		// Initialize variables
		$arr = array();

		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		// Is the path a folder?
		if (!is_dir($path)) {
			JError::raiseWarning(21, 'JAFileHelpers::files: ' . JText::_('PATH_IS_NOT_A_FOLDER'), 'Path: ' . $path);
			return false;
		}

		// read the source directory
		$handle = opendir($path);
		while (($file = readdir($handle)) !== false)
		{
			if (($file != '.') && ($file != '..') && (!in_array($file, $exclude))) {
				$dir = JPath::clean($path.'/'.$file);
				
				//check if disabled by ja amazon s3
				if(in_array($dir, $exclude)) {
					continue;
				}
				//
				
				$isDir = is_dir($dir);
				if ($isDir) {
					if ($recurse) {
						if (is_integer($recurse)) {
							$arr2 = JAFileHelpers::_files($dir, $basePath, $filter, $recurse - 1, $fullpath, $exclude);
						} else {
							$arr2 = JAFileHelpers::_files($dir, $basePath, $filter, $recurse, $fullpath, $exclude);
						}
						
						$arr = array_merge($arr, $arr2);
					}
				} else {
					if (preg_match("/$filter/i", $file)) {
						if ($fullpath) {
							$arr[] = $dir;
						} else {
							$arr[] = $file;
						}
					}
				}
			}
		}
		closedir($handle);

		asort($arr);
		return $arr;
	}
	
	function folders($path, $filter = '.', $recurse = false, $fullpath = false, $exclude = array('.svn', 'CVS'))
	{
		$exclude[] = '.svn';
		$exclude[] = 'CVS';
		
		$exclude = array_unique($exclude);
		
		$path = preg_replace("#[/\\\\]+$#", '', $path);
		$path = JPath::clean($path);
		
		//check if disabled by ja amazon s3
		if(in_array($path, $exclude)) {
			return array();
		}
		
		$arr = JAFileHelpers::_folders($path.'/', $path, $filter, $recurse, $fullpath, $exclude);
		if(!$arr) {
			$arr = array();
		}
		return $arr;
	}
	
	function _folders($path, $basePath, $filter = '.', $recurse = false, $fullpath = false, $exclude = array('.svn', 'CVS'))
	{
		// Initialize variables
		$arr = array();

		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		// Is the path a folder?
		if (!is_dir($path)) {
			JError::raiseWarning(21, 'JFolder::folder: ' . JText::_('PATH_IS_NOT_A_FOLDER'), 'Path: ' . $path);
			return false;
		}

		// read the source directory
		$handle = opendir($path);
		while (($file = readdir($handle)) !== false)
		{
			if (($file != '.') && ($file != '..') && (!in_array($file, $exclude))) {
				$dir = JPath::clean($path.'/'.$file);
				
				//check if disabled by ja amazon s3
				if(in_array($dir, $exclude)) {
					continue;
				}
				//
				$isDir = is_dir($dir);
				if ($isDir) {
					// Removes filtered directories
					if (preg_match("/$filter/i", $file)) {
						if ($fullpath) {
							$arr[] = $dir;
						} else {
							$arr[] = $file;
						}
					}
					if ($recurse) {
						if (is_integer($recurse)) {
							$arr2 = JAFileHelpers::_folders($dir, $basePath, $filter, $recurse - 1, $fullpath, $exclude);
						} else {
							$arr2 = JAFileHelpers::_folders($dir, $basePath, $filter, $recurse, $fullpath, $exclude);
						}
						
						$arr = array_merge($arr, $arr2);
					}
				}
			}
		}
		closedir($handle);

		asort($arr);
		return $arr;
	}
}
?>
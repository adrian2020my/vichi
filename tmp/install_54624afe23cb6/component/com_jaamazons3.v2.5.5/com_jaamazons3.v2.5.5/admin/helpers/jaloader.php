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
defined( '_JEXEC' ) or die( 'Restricted access' );

class jaLoader {
	// Static variables using for decide error code
	var $ERROR     = -1;
	var $CLASS_NOT_FOUND = -2;
	var $FAILURE   = -3;
	var $SUCCESS   = 0;

	var $debug = false;
	
	function debug() {
		return false;
	}

	/**
   * PHP package import implement. Now we have support following syntax:
   * - namespace.packagename.ClassName
   *
   * @param $class  string
   * @param $path  string overwritten path to load class instead of default path
   * @param $stripExt boolean strip extension if available, default is null. If true will force strip extension, false will skip check extension and null will be auto detect.
   */
	function import($class, $path = null, $stripExt = null) {
		$classPath = realpath(dirname(__FILE__)."/../..");
		$classPath = empty($path) ? $classPath : $path;
		$className = $class;

		if ($stripExt === null && !empty($path)) {
			$stripExt = true;
		}

		// Auto remove file extension if exists
		if ($stripExt && preg_match("/\.\w+$/", $className)) {
			$className = preg_replace("/\.\w+$/", "", $className);
		}

		if (strpos($className, '.') !== false && empty($path)) {
			$pieces = explode('.', $className);
			$className = array_pop($pieces);
			$classPath = $classPath.'/'.implode('/', $pieces);
		}
		
		$fullClassPath = $classPath.'/'.$className.'.php';
		if (file_exists($fullClassPath)) {
			require_once($fullClassPath);
			return 0;
		} else {
			if (jaLoader::debug()) {
				var_dump("class not found in: $fullClassPath");
			}
			return -2;
		}
	}

	/**
   *  Import all .php file found on the $path
   *
   * @param $path  string
   * @param $ext  string file extension to import
   */
	function importAll($path, $ext = "php") {
		$dh = opendir($path);
		$pattern = "/\.$ext$/";
		if (jaLoader::debug()) {
			echo "<h3>Import all file with ext=$ext in $path</h3>";
		}
		// Import files
		while (($file = readdir($dh)) !== false) {
			if (jaLoader::debug()) {
				echo "$file\n";
			}
			if (preg_match($pattern, $file)) {
				jaLoader::import($file, $path);
			}
		}
	}

	/**
   *  Import all .php file found on $path recursively
   *
   * @param $path  string
   */
	function importRecursive($path) {
		$cwd = dir($path);

		// Import all file in current directory
		jaLoader::importAll($path);

		// recursive directory
		while (($entry = $cwd->read()) !== false) {
			if ($entry == "." ||
			$entry == ".." ||
			preg_match("/^\./", $entry)) {
				if (jaLoader::debug()) {
					echo "skip: $entry<br>";
				}
				continue;
			}
			$fullPath = $path.'/'.$entry;
			if (jaLoader::debug()) {
				echo "entry: $entry<br>";
				echo "full path: $fullPath<br>";
			}
			if (is_dir($fullPath)) {
				jaLoader::importAll($fullPath);
				jaLoader::importRecursive($fullPath);
			}
		}
		$cwd->close();
	}
}
?>
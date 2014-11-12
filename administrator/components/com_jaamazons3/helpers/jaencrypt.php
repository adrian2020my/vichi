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


if (!function_exists("jakey_encrypt")) {
	function jakey_encrypt($string, $key) {
		if(function_exists('mcrypt_module_open')) {
			$td = mcrypt_module_open('des', '', 'ecb', '');
			$iv_size = mcrypt_enc_get_iv_size($td);
			$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	
			$ks = mcrypt_enc_get_key_size($td);
	
			/* Create key */
			$key = substr($key, 0, $ks);
	
			/* Intialize encryption */
			mcrypt_generic_init($td, $key, $iv);
	
			/* Encrypt data */
			$encrypted = mcrypt_generic($td, $string);
	
			mcrypt_generic_deinit($td);
			mcrypt_module_close($td);
	
			return base64_encode($encrypted);
		} else {
			return base64_encode($string);
		}
	}
}

if (!function_exists("jakey_decrypt")) {
	function jakey_decrypt($encrypted, $key) {
		if(function_exists('mcrypt_module_open')) {
			$encrypted = base64_decode($encrypted);
		
			$td = mcrypt_module_open('des', '', 'ecb', '');
			$iv_size = mcrypt_enc_get_iv_size($td);
			$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		
			$ks = mcrypt_enc_get_key_size($td);
		
			$key = substr($key, 0, $ks);
		
			/* Intialize encryption */
			mcrypt_generic_init($td, $key, $iv);
		
			/* Encrypt data */
			$decrypt = trim(mdecrypt_generic($td, $encrypted));
			mcrypt_generic_deinit($td);
			mcrypt_module_close($td);
			return $decrypt;
		} else {
			return base64_decode($encrypted);
		}
	}
}

if (!function_exists("jakey_decrypt_params")) {
	function jakey_decrypt_params() {
		$post = JRequest::getVar('key', '');
		
		$params = array();
		if(!empty($post)) {
			$post = explode('&', jakey_decrypt($post, md5 ('1218787810')));
			
			$content = array();
			foreach ($post as $p) {
				$p = explode('=',$p);
				$params[$p[0]] = isset($p[1])? $p[1] : '';
			}
		}
		return $params;
	}
}
?>
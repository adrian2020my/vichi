<?php
/**
 *$JA#COPYRIGHT$
 */

// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' );

jimport ( 'joomla.plugin.plugin' );
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.path');
jimport ( 'joomla.html.parameter' );

class plgSystemPlg_jaamazons3 extends JPlugin
{
	var $_component = "com_jaamazons3";
	var $plgParams = null;
	var $aParams = array();
	
	var $distribute_url = '';
	var $active_bucket_id = '';
	//add version at the end of url to clear cache or not
	var $add_suffix_version = 0;

	function plgSystemPlg_jaamazons3(&$subject, $config)
	{
		$mainframe = JFactory::getApplication('administrator');
		parent::__construct ( $subject, $config );

		$this->plugin = JPluginHelper::getPlugin ( 'system', 'plg_jaamazons3' );
		$this->plgParams = new JRegistry( $this->plugin->params );

	}
	
	function cronJobUpload() {
		$mainframe = JFactory::getApplication('administrator');
		if ($mainframe->isAdmin()) {
			return;
		}
		// run cron job
		$params = JComponentHelper::getParams($this->_component);
		$cron_mode = $params->get('cron_mode', 'off');
		$uploadKey = $params->get('upload_secret_key', '');
		if($cron_mode == "off") {
			return;
		}
		
		if((isset($_GET[$uploadKey])) || ($cron_mode == "pseudo" && !empty($uploadKey))) {
    	
			$cronFile = JPATH_ROOT."/administrator/components/".$this->_component."/cron.php";
			if(is_file($cronFile)) {
				$key = "action=upload&uploadKey={$uploadKey}&checkTime=".time();
				//support command to run cron immediately from front-end
				if(isset($_GET[$uploadKey])) {
					$key .= "&run=1";
				}
				
				
				//$key = urlencode($this->jakey_encrypt($key, md5('1218787810')));
				
				//$url = JURI::root()."administrator/components/{$this->_component}/cron.php?key=".$key;
				$url = JURI::root()."administrator/components/{$this->_component}/cron.php";
				
				$body = JResponse::getBody();
				$scriptUpload = '
				<script type="text/javascript" language="javascript">
				  /*<![CDATA[*/
				  window.addEvent("load", function(){
				  	var myAjax = new Request({
					        method: "GET",
					        url: "'.$url.'",
					        onRequest: function(){ 
					        	//alert("start upload"); 
					        },
					        onSuccess: function(txt){ 
					        	//alert(txt); 
					        },
					        onFailure: function(){ 
					        	//alert("failure upload"); 
					        }
					    });
					myAjax.send({});
				  });
				  /*]]>*/
				</script>
				';
				
				$body = JResponse::getBody();
				$body = str_replace('</body>', $scriptUpload . "\r\n". '</body>', $body);
				JResponse::setBody($body);
			}
		}
	}
	
	function onAfterRender() {
		$mainframe = JFactory::getApplication('administrator');

		$apply_admin = (int) $this->plgParams->get('apply_admin', 0);
		if ($mainframe->isAdmin() && !$apply_admin) {
			return;
		}

		$enabledFor = $this->plgParams->get('enabled', 'both');
		$isSSL = JURI::getInstance()->isSSL();
		if(($enabledFor == 'https' && !$isSSL) || ($enabledFor == 'http' && $isSSL)) {
			return;
		}

		$listProfiles = $this->getActiveProfiles();
		
		if(is_array($listProfiles) && count($listProfiles)) {
			$body = JResponse::getBody();
			
			/***********************************/
			//CONVERT ALL URLs TO ABSOLUTE FORMAT 
			/***********************************/
			// from format: /abc/def/xyz.jpg (absolute path without domain)
			$path = JURI::root(true);
			if(!empty($path)) {
				$host = substr(JURI::root(), 0, - (strlen($path)));
			} else {
				$host = JURI::root();
			}
			
			$host = preg_replace('#[/\\\\]+$#', "", $host);
			if($mainframe->getCfg('force_ssl') == 2){
				$host = str_replace('http:', 'https:', $host);
			}
			
			//find and replace urls
			$pattern = "/(href|src|poster)\s*=\s*(\")\/([^\/][^\'\"]*)(\")/i";
			$body = preg_replace($pattern, '$1=$2'.$host.'/$3$4', $body);
			$pattern = "/(href|src|poster)\s*=\s*(\')\/([^\/][^\'\"]*)(\')/i";
			$body = preg_replace($pattern, '$1=$2'.$host.'/$3$4', $body);
			
			//from format: abc/def/xyz.jpg (relative path)
			$path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']);
			$path = JPath::clean($path, "/");
			$path = preg_replace('#^[/\\\\]+#', '', $path);
			$currUrl = dirname($host ."/". $path)."/";
		
			//$body = preg_replace("/(href|src|poster)\s*=\s*(\'|\")((?!(?:\w+)\:\/*)[^\/\#][^\'\"]*)(\'|\")/i", '$1=$2'.$currUrl.'$3$4', $body);
			//must separate to fix bug replace url if quote and double quote are mixed
			//E.g: document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
			$body = preg_replace("/(href|src|poster)\s*=\s*(\")((?!(?:\w+)\:\/*)[^\/\#][^\'\"]+)(\")/i", '$1=$2'.$currUrl.'$3$4', $body);
			$body = preg_replace("/(href|src|poster)\s*=\s*(\')((?!(?:\w+)\:\/*)[^\/\#][^\'\"]+)(\')/i", '$1=$2'.$currUrl.'$3$4', $body);
			
			foreach ($listProfiles as $profile) {
				$body = $this->_replaceDistributeUrls($profile, $body);
			}
			JResponse::setBody($body);
		}
		
		//$this->uploadFrontend();
		$this->cronJobUpload();
	}
	
	function _replaceDistributeUrls($profile, $body) {
		
		if(is_object($profile) && isset($profile->bucket_id) && isset($profile->bucket_acl) && ($profile->bucket_acl != 'private')) {
			$cache_lifetime = (int) $profile->cache_lifetime;
			$this->add_suffix_version = $cache_lifetime ? 0 : 1;
			
			$distributeUrl = $this->getDistributeUrl($profile);
			//
			$this->distribute_url = rtrim($distributeUrl, '\\/');
			$this->active_bucket_id = $profile->bucket_id;
			$findUrls = $profile->site_url;
			
			/***********************************/			
			//UPDATE LINKS FOR EXCEPTION LIST
			/***********************************/
			$exceptionList = $this->getListItemDisabled($profile);
			//print_r($exceptionList);
			if(count($exceptionList)) {
				foreach ($exceptionList as $url) {
					$body = str_replace($url, $this->lockUrl($url), $body);
				}
			}
			/***********************************/
			//MAKE REPLACE PATTERN
			/***********************************/
			//get allowed extensions from component' configuration
			$exts = $profile->allowed_extension;
			$aExts = explode(",", $exts);
			if(count($aExts) >= 1) {
				$exts= "(?:".implode("|", $aExts).")";
				
				//support multi urls to replaced
				//each url is separte on one line
				$aFindUrl = preg_split("/\r*\n/", $findUrls);
				if(count($aFindUrl)) {
					foreach ($aFindUrl as $findUrl) {
						//remove white spaces
						$findUrl = preg_replace("/\s*/", '', $findUrl);
						if(empty($findUrl)) {
							continue;
						}
						$findUrl = $this->cleanUrl($findUrl);
						//escape Regex characters
						$findUrl = preg_replace("/([\:\-\/\.\?\(\)\[\]\{\}])/", "\\\\$1", $findUrl);
						/**
						 * support urls is inputed like simple regex format:
						 * http://*.joomlart.com
						 * '*' is can be a sub domain, www, www2, ...
						 */
						$findUrl = str_replace('*', '[a-zA-Z0-9\.\_\-]*?', $findUrl);
						//filter urls by allowed extensions
						// /path/to/your/file/style.css?v=1
						$pattern = "/{$findUrl}([^\'\"\?]*?\.{$exts}[^\'\"\)]*?)/";
						//echo "<pre>$pattern</pre>";
						
						/*preg_match_all($pattern, $body, $matches);
						print_r($matches);
						die();*/
						$body = preg_replace_callback($pattern, array($this, 'convertUrl'), $body);
						
					}
					/***********************************/			
					//CORRECT LINKS FOR EXCEPTION LIST
					/***********************************/
					$body = $this->unlockUrl($body);
				}
			}
		}
		return $body;
	}

	function getActiveProfiles() {
		$db = JFactory::getDBO();
		$query = "
			SELECT 
				a.acc_label,
				a.acc_name,
				a.acc_accesskey,
				a.acc_secretkey,
				b.acc_id, 
				b.bucket_name, 
				b.bucket_acl, 
				b.bucket_protocol, 
				b.bucket_url_format,
				b.bucket_cloudfront_domain,
				p.* 
			FROM #__jaamazons3_profile AS p
			INNER JOIN #__jaamazons3_bucket b ON b.id = p.bucket_id 
			INNER JOIN #__jaamazons3_account a ON b.acc_id = a.id 
			WHERE p.profile_status = 1
			";
		$db->setQuery( $query );
		$list = $db->loadObjectList();
		return $list;
	}
	
	function getProfileLocalPath($profile) {
		$basePath = JPath::clean($profile->site_path . '/');
		$basePath = str_replace('{jpath_root}', JPATH_ROOT, $basePath);
		$basePath = JPath::clean($basePath);
		return $basePath;
	}
	
	function getDistributeUrl($bucket) {
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
	
	function getListItemDisabled($profile) {
		$list = array();
			
		if($profile !== false) {
			$basePath = $this->getProfileLocalPath($profile);
			
			$db = JFactory::getDBO();
			//order by path to save efforts when reduce list
			$query = "
				SELECT `path` FROM #__jaamazons3_disabled
				WHERE `profile_id` = '{$profile->id}'
				ORDER BY `path`
				";
			$db->setQuery($query);
			$listDisabled = $db->loadObjectList();
			
			$baseUrl = $profile->site_url;
			$aFindUrl = preg_split("/\r*\n/", $baseUrl);
			if(count($listDisabled)) {
				foreach ($listDisabled as $item) {
					foreach ($aFindUrl as $findUrl) {
						if(JFolder::exists($basePath.'/'.$item->path)) {
							/**
							 * Fix bug lock url: lock url although it is enabled, but other url is disabled 
							 * (Eg: t3-assets is enabled, t3 is disabled 
							 * => t3-assets is disabled
							 */
							$url = $this->cleanUrl($findUrl."/".$item->path."/");
							$list = $this->reduceDisabledItems($list, $url);
						} elseif (JFile::exists($basePath.'/'.$item->path)) {
							$url = $this->cleanUrl($findUrl."/".$item->path);
							$list = $this->reduceDisabledItems($list, $url);
						}
					}
				}
			}
		}
		return $list;
	}
	
	/**
	 * if one folder is disabled,
	 * we don't need take care to all items on this folder
	 *
	 */
	function reduceDisabledItems($list, $addUrl) {
		$found = 0;
		if(count($list)) {
			foreach ($list as $id => $url) {
				if(strpos($url, $addUrl) === 0) {
					// $url is a file on $addUrl
					$list[$id] = $addUrl;
					$found = 1;
					break;
				} elseif (strpos($addUrl, $url) === 0) {
					// $addUrl is a file on $url
					$found = 1;
					break;
				}
			}
		}
		if(!$found) {
			$list[] = $addUrl;
		}
		return $list;
	}
	
	function cleanUrl($url) {
		$url = trim($url);
		$url = str_replace("{juri_root}", rtrim(JURI::root(), '\\/')."/", $url);
		$url = preg_replace("/([^\:]{1})[\/\\\\]+/", "$1/", $url);
		return $url;
	}
	
	/**
	 * lock a disabled urls to not replace them with s3 url
	 *
	 * @param (string) $url
	 */
	function lockUrl($url) {
		return preg_replace("/((?:\w+))\:\/+/i", "$1://[locked]", $url);
	}
	
	function unlockUrl($url) {
		return preg_replace("/((?:\w+))\:\/+\[locked\]/i", "$1://", $url);
	}
	
	function convertUrl($matches) {
		static $aCheck = array();
		static $db;
		if(!is_object($db)) {
			$db = JFactory::getDBO();
		}
		$item = ltrim(JPath::clean($matches[1], '/'), '\\/');
		//remove query string (Eg: style.css?v=1)
		$itemCheck = preg_replace("/\?.*$/", '', $item);
		
		if(!isset($aCheck[$itemCheck]) || $aCheck[$itemCheck] == false) {
			$sql = "SELECT last_update FROM #__jaamazons3_file WHERE bucket_id = '".$this->active_bucket_id."' AND `file_exists`=1 AND path = '{$item}'";
			$db->setQuery($sql);
			$log = $db->loadObject();
			$aCheck[$itemCheck] = is_object($log);
		}
		
		//if item is not uploaded to s3 => using original url
		if($aCheck[$itemCheck]) {
			$url =  $this->distribute_url.'/'.$item;
			if($this->add_suffix_version) {
				$suffix = ((strpos($item, '?') === false) ? '?' : '&') . 'javer='.date('ymdhi');
				$url .= $suffix;
			}
		} else {
			$url = $matches[0];
		}
		
		return $url;
	}

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
?>
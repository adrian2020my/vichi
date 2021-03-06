<?php

/**
* Grid Title Overlay
* @package News Show Pro GK5
* @Copyright (C) 2009-2013 Gavick.com
* @ All rights reserved
* @ Joomla! is Free Software
* @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
* @version $Revision: GK5 1.3.3 $
**/

// access restriction
defined('_JEXEC') or die('Restricted access');

class NSP_GK5_Frontpage_Image_Overlay {
	// necessary class fields
	private $parent;
	private $mode;
	// constructor
	function __construct($parent) {
		$this->parent = $parent;
		// detect the supported Data Sources
		if(stripos($this->parent->config['data_source'], 'com_content_') !== FALSE) {
			$this->mode = 'com_content';
		} else if(stripos($this->parent->config['data_source'], 'k2_') !== FALSE) { 
			$this->mode = 'com_k2';
		} else if(stripos($this->parent->config['data_source'], 'easyblog_') !== FALSE) { 
			$this->mode = 'com_easyblog';
		} else {
			$this->mode = false;
		}
	}
	// static function which returns amount of articles to render - VERY IMPORTANT!!
	static function amount_of_articles($parent) {
		return 1;
	}
	// output generator	
	function output() {	
		// render images
		for($i = 0; $i < count($this->parent->content); $i++) {
			// output the HTML code
			echo '<figure class="gkNspPM gkNspPM-FrontpageImageOverlay" data-textcolor="'.$this->parent->config['portal_mode_frontpage_image_overlay_text_color'].'">';
			//
			if($this->get_image($i)) {
				$img_url = strip_tags($this->get_image($i));
				if(substr($img_url, 0, 6) == 'images') {
					$img_url = '/' . $img_url;
				}
				echo '<span style="background: url(\''.$img_url.'\');" ><span></span></span>';
			}
			//
			if(
				$this->parent->config['portal_mode_frontpage_image_overlay_title'] != 0 &&
				$this->parent->config['portal_mode_frontpage_image_overlay_text'] != 0 &&
				$this->parent->config['portal_mode_frontpage_image_overlay_readon'] != 0
			) {
				echo '<figcaption>';
				
				if($this->parent->config['portal_mode_frontpage_image_overlay_title'] != 0) {
					echo '<h1><a href="'.$this->get_link($i).'">'.$this->parent->content[$i]['title'].'</a></h1>';
				}
				
				if($this->parent->config['portal_mode_frontpage_image_overlay_text'] != 0) {
					echo '<p>' . JString::substr(strip_tags($this->parent->content[$i]['text']), 0, $this->parent->config['portal_mode_frontpage_image_overlay_text_limit']) . '&hellip;</p>';
				}
				
				if($this->parent->config['portal_mode_frontpage_image_overlay_readon'] != 0) {
					echo '<a class="button" href="'.$this->get_link($i).'">';
					
					if($this->parent->config['portal_mode_frontpage_image_overlay_readon_text'] == '') {
						echo JText::_('MOD_NEWS_PRO_GK5_PORTAL_MODE_IMAGE_OVERLAY_READON_TEXT_LABEL');
					} else {
						echo $this->parent->config['portal_mode_frontpage_image_overlay_readon'];
					}
					
					echo '</a>';
				}
				
				echo '</figcaption>';
			}
			echo '</figure>';
		}
	}
	// function used to retrieve the item URL
	function get_link($num) {
		if($this->mode == 'com_content') {
			return ($this->parent->content[$num]['id'] != 0) ? JRoute::_(ContentHelperRoute::getArticleRoute($this->parent->content[$num]['id'], $this->parent->content[$num]['cid'])) : JRoute::_('index.php?option=com_users&view=login');
		} else if($this->mode == 'com_k2') {
			//
			require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');
			//
			return urldecode(JRoute::_(K2HelperRoute::getItemRoute($this->parent->content[$num]['id'].':'.urlencode($this->parent->content[$num]['alias']), $this->parent->content[$num]['cid'].':'.urlencode($this->parent->content[$num]['cat_alias']))));
		} else if($this->mode == 'com_easyblog') {
			//
			require_once (JPATH_SITE.DS.'components'.DS.'com_easyblog'.DS.'helpers'.DS.'router.php');
			//
			return urldecode(JRoute::_(EasyBlogRouter::getEntryRoute($this->parent->content[$num]['id'])));
		} else {
			return false;
		}
	}
	// image generator
	function get_image($num) {		
		// used variables
		$url = false;
		$output = '';
		// select the proper image function
		if($this->mode == 'com_content') {
			// load necessary com_content View class
			if(!class_exists('NSP_GK5_com_content_View')) {
				require_once(JModuleHelper::getLayoutPath('mod_news_pro_gk5', 'com_content/view'));
			}
			// generate the com_content image URL only
			$url = NSP_GK5_com_content_View::image($this->parent->config, $this->parent->content[$num], true, true);
		} else if($this->mode == 'com_k2') {
			// load necessary k2 View class
			if(!class_exists('NSP_GK5_com_k2_View')) {
				require_once(JModuleHelper::getLayoutPath('mod_news_pro_gk5', 'com_k2/view'));
			}
			// generate the K2 image URL only
			$url = NSP_GK5_com_k2_View::image($this->parent->config, $this->parent->content[$num], true, true);
		} else if($this->mode == 'com_easyblog') {
			// load necessary EasyBlog View class
			if(!class_exists('NSP_GK5_com_easyblog_View')) {
				require_once(JModuleHelper::getLayoutPath('mod_news_pro_gk5', 'com_easyblog/view'));
			}
			// generate the EasyBlog image URL only
			
			$url = NSP_GK5_com_easyblog_View::image($this->parent->config, $this->parent->content[$num], true, true);
		}
		// check if the URL exists
		if($url === FALSE) {
			return false;
		} else {
			// if URL isn't blank - return it!
			if($url != '') {
				return $url;
			} else {
				return false;
			}
		}
	}
}

// EOF
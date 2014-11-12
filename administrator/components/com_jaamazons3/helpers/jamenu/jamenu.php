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

/**
 * $menu = array(
 * 	'group1' => array(
 * 		'title' => 'Item 1',
 * 		'link' => '#',
 * 		'class' => 'adminmenu',
 * 		'actions' => '<a href="#">Edit</a>|...',
 * 		'childrens' => array(
 * 			//same structure with parent
 * 		)
 * 	),
 * 	'group2' => array(
 * 		//same structure with group 1
 * 	)
 * );
 *
 */

class JAMenu
{
	var $data = array();
	var $activeItems = array();
	
	function JAMenu($data = array()) {
		$this->data = $data;
		
		//get list active menu items
		$menuId = JRequest::getVar('menuId', '');
		if(!empty($menuId)) {
			$menuId = base64_decode($menuId);
			$this->activeItems = explode(',', $menuId);
		}
	}
	
	function display() {
		$menu = "";
		if(is_array($this->data) && count($this->data)) {
			$menu = $this->_genMenu($this->data, '', 0);
		}
		return $menu;
	}
	
	function _genMenu($items, $menuId = '', $level = 0) {
		$menu = "<ul>";
		$cnt = 0;
		$numItems = count($items);
		foreach ($items as $id => $item) {
			$cnt++;
			$item['id'] = $id;
			if($cnt == 1) {
				$item['isFirst'] = 1;
			} elseif ($cnt == $numItems) {
				$item['isLast'] = 1;
			}
			
			$class = $this->_genClass($item, $level);
			
			//get link
			if(empty($menuId)) {
				$subMenuId = $id;
			} else {
				$subMenuId = "{$menuId},{$id}";
			}
			
			$link = $this->_genLink($item['link'], $subMenuId);
			
			$menu .= "<li class=\"{$class}\">";
			$menu .= "
			<a href=\"".$link."\" class=\"{$class}\" title=\"".addslashes($item['title'])."\">
				<span>".$item['title']."</span>
			</a>";
			
			if(isset($item['actions'])) {
				$menu .= "<div class=\"ja-menu-actions\">{$item['actions']}</div>";
			}
			
			//sub menu
			if($this->_hasChild($item)) {
				$menu .= $this->_genMenu($item['children'], $subMenuId, $level+1);
			}
			$menu .= "</li>";
		}
		$menu .= "</ul>";
		return $menu;
	}
	
	function _genLink($link, $menuId) {
		if($link != '#') {
			$link .= ((strpos($link, '?') !== false) ? "&amp;" : "?") . "menuId=".base64_encode($menuId);
		}
		
		return $link;
	}
	
	function _genClass($item, $level) {
		$class = isset($item['class']) ? $item['class'] : "";
		$class .= ' lv'.$level;
		if($this->_hasChild($item)) {
			$class .= ' havechild';
		}
		if(in_array($item['id'], $this->activeItems)) {
			$class .= ' active';
		}
		if(isset($item['isFirst'])) {
			$class .= ' first';
		}
		if(isset($item['isLast'])) {
			$class .= ' last';
		}
		return $class;
	}
	
	function _hasChild($item) {
		$hasChild = (isset($item['children']) && is_array($item['children']) && count($item['children'])) ? true : false;
		return $hasChild;
	}
}

if(!function_exists('jaComponentMenuHeader')) {
	function jaComponentMenuHeader() {
		// Display menu
		if(! JRequest::getVar("ajax") && JRequest::getVar('tmpl') != 'component' && JRequest::getVar('viewmenu', 1) != 0){
			$file = dirname(__FILE__)."/menu_header.php";
			if(@file_exists($file))
			require_once($file);
		}
	}
}

if(!function_exists('jaComponentMenuFooter')) {
	function jaComponentMenuFooter() {
		// Display footer
		if(! JRequest::getVar("ajax") && JRequest::getVar('tmpl') != 'component' && JRequest::getVar('viewmenu', 1) != 0){
			$file = dirname(__FILE__)."/menu_footer.php";
			if(@file_exists($file))
			require_once($file);
		}
	}
}
?>
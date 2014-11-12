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

//no direct access
defined( '_JEXEC' ) or die( 'Retricted Access' );

?>
<div id="jacom-mainwrap">
<div id="jacom-mainnav">
  <div class="inner">
    <div class="ja-showhide"> <a class="openall opened" title="Open all" onclick="JATreeMenu.openall();" href="javascript:;" id="menu_open"><?php echo JText::_("OPEN_ALL");?></a> <a class="closeall" title="Close all" onclick="JATreeMenu.closeall();" href="javascript:;" id="menu_close"><?php echo JText::_("CLOSE_ALL");?></a> </div>
    <?php
    $queryProfiles = "
    	SELECT p.*, b.bucket_name 
    	FROM #__jaamazons3_profile p
		LEFT JOIN #__jaamazons3_bucket b ON b.id = p.bucket_id
    	ORDER BY  p.is_default DESC, p.profile_name ASC";
    $db = JFactory::getDBO();
    $db->setQuery($queryProfiles);
    $rsProfiles = $db->loadObjectList();
    
    $aMenuProfiles = array();
    $jaStorageHelper = new jaStorageHelper();
    if(is_array($rsProfiles) && count($rsProfiles)) {
    	foreach ($rsProfiles as $profile) {
    		$actions = "";
    		if(empty($profile->bucket_name)) {
    			$actions .= $jaStorageHelper->showIcon("warning_small.png", JText::_('PLEASE_SELECT_A_BUCKET_FOR_THIS_PROFILE'), '', '', 0) . " ";
    		}
    		$actions .= "<a href=\"#\" onclick=\"javascript: jaEditProfile('{$profile->id}', '".JText::_("EDIT_PROFILE", true)."'); return false;\">".JText::_("SETTING")."</a>";
    		if(!$profile->is_default) {
    			$actions .= "&nbsp;|&nbsp;";
    			$actions .= "<a href=\"#\" onclick=\"javascript: jaDeleteProfile('{$profile->id}'); return false;\">".JText::_("DELETE")."</a>";
    		}
    		
    		$aMenuProfiles["profile".$profile->id] = array(
    			'title' => $profile->profile_name,
	    		'link' => "index.php?option=com_jaamazons3&amp;view=localrepo&amp;profile_id=".$profile->id,
	    		'class' => "adminmenu",
	    		'actions' => $actions
    		);
    	}
    }
    
    $aMenus = array(
    	's3settings' => array(
    		'title' => JText::_("AMAZON_S3_SETTINGS"),
    		'link' => "#",
    		'class' => "adminmenu",
    		'children' => array(
    			'accountmanager' => array(
		    		'title' => JText::_("ACCOUNT_MANAGER"),
		    		'link' => "index.php?option=com_jaamazons3&amp;view=account",
		    		'class' => "adminmenu"
		    	),
    			'bucketmanager' => array(
		    		'title' => JText::_("BUCKET_MANAGER"),
		    		'link' => "index.php?option=com_jaamazons3&amp;view=bucket",
		    		'class' => "adminmenu"
		    	),
    			's3browser' => array(
		    		'title' => JText::_("S3_FILES_BROWSER"),
		    		'link' => "index.php?option=com_jaamazons3&amp;view=repo",
		    		'class' => "adminmenu"
		    	),
    		)
    	),
    	'profilemanager' => array(
    		'title' => JText::_("SYNC_PROFILES"),
    		'link' => "#",
    		'class' => "adminmenu",
    		'actions' => "
    		<a href=\"index.php?option=com_jaamazons3&amp;view=profile\">".JText::_("MANAGE")."</a>
    		&nbsp;|&nbsp;
    		<a href=\"#\" onclick=\"javascript: jaEditProfile('0', '".JText::_("CREATE_NEW_PROFILE", true)."'); return false;\">".JText::_("ADD_NEW")."</a>
    		",
    		'children' => $aMenuProfiles
    	),
    	'help' => array(
    		'title' => JText::_("HELP_AND_SUPPORT"),
    		'link' => "#",
    		'class' => "adminmenu",
    		'children' => array(
    			'help2' => array(
		    		'title' => JText::_("HELP"),
		    		'link' => "index.php?option=com_jaamazons3&amp;view=help",
		    		'class' => "adminmenu"
		    	),
    			'cronjob' => array(
		    		'title' => JText::_("CRONJOB_SETTING"),
		    		'link' => "index.php?option=com_jaamazons3&amp;view=help&amp;layout=cronjob",
		    		'class' => "adminmenu"
		    	)
			)
    	)
    );
    
	$menu = new JAMenu($aMenus);
	echo $menu->display();
	?>
    <script type="text/javascript">
		JATreeMenu.initmenu();
		
		function jaEditProfile(profile_id, popup_title) {
			var url = 'index.php?tmpl=component&option=com_jaamazons3&view=profile&viewmenu=0&task=edit&cid[]='+profile_id+'&number=0';
			jaCreatePopup(url, 600, 500, popup_title);
		}
		
		function jaDeleteProfile(profile_id) {
			if(confirm('<?php echo JText::_('DO_YOU_REALLY_WANT_TO_DELETE_THIS_PROFILE', true); ?>')){
				document.location.href = "index.php?option=com_jaamazons3&view=profile&task=remove&cid[]="+profile_id;
			}
			return false;
		}
	</script>
  </div>
</div>
<div id="jacom-maincontent">

<?php
/**
 *$JA#COPYRIGHT$
 */

// Ensure this file is being included by a parent file
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.filesystem.file');

class JElementBuckets extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'buckets';
	
	function fetchElement ( $name, $value, &$node, $control_name ){
		
		$db = &JFactory::getDBO();
		$query = '
			SELECT 
				c.acc_id,
				s.acc_label,
				c.id AS bucket_id,
				c.bucket_name 
			FROM #__jaamazons3_account AS s
			INNER JOIN #__jaamazons3_bucket c ON c.acc_id = s.id
			WHERE 1
			ORDER BY c.acc_id, c.bucket_name
			';
		$db->setQuery( $query );
		$cats = $db->loadObjectList();
		
		$HTMLCats=array();
		$HTMLCats[0]->id = '';
		$HTMLCats[0]->title = JText::_("SELECT_BUCKET");
		
		if(is_array($cats) && count($cats)) {
			$acc_id = 0;
			foreach ($cats as $cat) {
				if($acc_id != $cat->acc_id) {
					$acc_id = $cat->acc_id;
					
					$cat->id = $cat->acc_id;
					$cat->title = $cat->acc_label;
					$optgroup = JHTML::_('select.optgroup', $cat->title, 'id', 'title');
					array_push($HTMLCats, $optgroup);
				}
				$cat->id = $cat->bucket_id;
				$cat->title = $cat->bucket_name;
				array_push($HTMLCats, $cat);
			}
		} else {
			$comS3 = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jaamazons3'.DS.'admin.jaamazons3.php';
			if(!JFile::exists($comS3)) {
				$warning = JText::_('THIS_PLUGIN_REQUIRES_THE_COMPONENT_IS_INSTALLED');
			} else {
				$warning = JText::_('PLEASE_MAKE_SURE_YOU_HAVE_GOT_AT_LEAST_ONE_BUCKET');
			}
			JError::raiseWarning(400, JText::_($warning));
		}
		
		return JHTML::_('select.genericlist',  $HTMLCats, ''.$control_name.'['.$name.'][]', 'class="inputbox"', 'id', 'title', $value );
	}
}
?>
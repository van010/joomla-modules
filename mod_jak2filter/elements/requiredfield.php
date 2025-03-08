<?php
/**
 * ------------------------------------------------------------------------
 * JA K2 Filter Module for J25 & J3.4
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
/**
 * JA Param K2 Helper
 *
 * @since      Class available since Release 1.2.0
 */
class JFormFieldRequiredfield extends JFormField
{
    /*
	 * Category K2 name
	 *
	 * @access	protected
	 * @var		string
	 */
    var $type = 'Requiredfield';

    function getInput() {
	    if (!file_exists((JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php')))
			return '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '"/>
					<span style="color:red; float:left">K2 component is not installed!</span>';

    	$html = array();
    	if (empty($this->value)) $this->value=array();
    	$html[] = '<select id="jform_params_required_field" name="jform[params][required_field][]" multiple="" class="chzn-done" style="display: none;">';
    	$html[] = '<option '.(in_array('searchword', $this->value) ? ' selected="selected" ' : '').' value="searchword">'.JText::_('JAK2_KEYWORD').'</option>';
    	$html[] = '<option '.(in_array('rating', $this->value) ? ' selected="selected" ' : '').' value="rating">'.JText::_('JAK2_RATING').'</option>';
    	$html[] = '<option '.(in_array('dtrange', $this->value) ? ' selected="selected" ' : '').' value="dtrange">'.JText::_('FILTER_TYPE_DATE').'</option>';
    	$html[] = '<option '.(in_array('category_id', $this->value) ? ' selected="selected" ' : '').' value="category_id">'.JText::_('JAK2_CATEGORY').'</option>';
    	$html[] = '<option '.(in_array('created_by', $this->value) ? ' selected="selected" ' : '').' value="created_by">'.JText::_('JAK2_AUTHOR').'</option>';
    	$html[] = '<option '.(in_array('tags_id', $this->value) ? ' selected="selected" ' : '').' value="tags_id">'.JText::_('JAK2_TAGS').'</option>';
    	
    	$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		
    	$query->select('GROUP_CONCAT( e.id SEPARATOR "-jaex-") AS eid, GROUP_CONCAT( e.name SEPARATOR "-jaex-") AS ename, g.id AS gid, g.name AS gname');
		$query->from($db->quoteName('#__k2_extra_fields', 'e'));
		$query->join('LEFT', $db->quoteName('#__k2_extra_fields_groups', 'g'). 'ON (' . $db->quoteName('e.group') . ' = ' . $db->quoteName('g.id') . ')');
		$query->group('gid');
		$db->setQuery($query);
		
		$results = $db->loadObjectList();
    	foreach ($results AS $r) {
    		$html[] = '<optgroup label="'.$r->gname.'">';
    		$exid = explode('-jaex-', $r->eid);
    		$exname = explode('-jaex-', $r->ename);
    		foreach ($exid AS $k => $e) {
    			$html[] = '<option '.(in_array('xf_'.$e, $this->value) ? ' selected="selected" ' : '').' value="xf_'.$e.'">'.$exname[$k].'</option>';
    		}
			$html[] = '</optgroup>';
    	}
    	
    	$html[] = '</select>';
    	return implode('', $html);
    }
}
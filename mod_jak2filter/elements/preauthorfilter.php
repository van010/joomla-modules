<?php
/**
 * ------------------------------------------------------------------------
 * JA K2 Filter Module for J25 & J3.4
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

class JFormFieldPreauthorfilter extends JFormField
{
    var $type = 'Preauthorfilter';

    function getInput()
    {
    	$db = JFactory::getDbo();
    	
		$query = "
			SELECT * FROM #__jak2filter_taxonomy
			WHERE type='author' AND num_items > 0
			";
		$db->setQuery($query);
		$list = $db->loadAssocList();

		$params = $this->getModuleParams();
		$tag_multip = $params->get('filter_by_author_fieldtype', '');
		$pre_tags = $params->get('pre_author', false);
		
		if (in_array($tag_multip, array('select','radio'))) {
			$this->multiple=false;
		} else {
			$this->multiple="multiple";
		}
		
		$html = '<select name="'.$this->name.''.($this->multiple==false ? '' : '[]').'" '.($this->multiple==false ? '' : ' multiple="multiple" ').'>';
		$html .= '<option value="0">- Select -</option>';
		foreach ($list AS $row) {
			$selected='';
			if (is_string($this->multiple) && is_array($pre_tags)) {
				if (is_array($pre_tags)) {
					foreach ($pre_tags AS $prev) {
						if ($prev == $row['asset_id']) {
							$selected = ' selected="" ';
						}
					}
				}
			}
			
			if ($this->multiple == false && !is_array($pre_tags)) {
				if ($pre_tags == $row['asset_id'])
					$selected = ' selected="" ';
			}
			$html .= '<option '.$selected.' value="'.$row['asset_id'].'">'.$row['title'].'</option>';
		}
		$html .= '</select>';
		
    	return $html;
    }
    protected function getModuleParams() {
		$jinput = JFactory::getApplication()->input;
		$modid = $jinput->getInt('id');
		$tableMod = JTable::getInstance('Module', 'JTable');
		$tableMod->load($modid);
		
		$data = isset($tableMod->params) ? $tableMod->params : '';
		$params = new JRegistry($data);
		return $params;
	}
}
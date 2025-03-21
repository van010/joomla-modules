<?php
/**
 * ------------------------------------------------------------------------
 * JA Google Chart 2 Module for J25 & J34
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use Joomla\String\StringHelper;
use Joomla\CMS\Form\Field\ListField;

FormHelper::loadFieldClass('list');

if(!defined('K2_JVERSION')) define('K2_JVERSION', '25');

if (!class_exists('ListFieldLegacy')) {
	if (version_compare(JVERSION, 4, 'ge')) {
		class ListFieldLegacy extends ListField{}
	} else {
		class ListFieldLegacy extends JFormFieldList{}
	}
}

class JFormFieldK2category extends ListFieldLegacy
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'K2category';
	
	protected function getInput()
	{
		$input = parent::getInput();
		if(!$this->checkComponent('com_k2')) {
			if(version_compare(JVERSION, '3.0', 'ge')) {
				$input .= '<br /><span style="color:red;">'.Text::_('K2 component is not installed!').'</span>';
			} else {
				$input .= '<br /><label>&nbsp;</label><span style="color:red">'.Text::_('K2 component is not installed!').'</span>';
			}
		}
		return $input;
	}

	/**
	 * Method to get the field options for category
	 * Use the extension attribute in a form to specify the.specific extension for
	 * which categories should be displayed.
	 * Use the show_root attribute to specify whether to show the global category root in the list.
	 *
	 * @return  array    The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		if(!$this->checkComponent('com_k2')) {
			return parent::getOptions();
		}
		$db = Factory::getDbo();
		// Initialise variables.
		$options = array();
		//$extension = $this->element['extension'] ? (string) $this->element['extension'] : (string) $this->element['scope'];
		$published = (string) $this->element['published'];
		$name = (string) $this->element['name'];

		// Filter over published state or not depending upon if it is present.
		$where = array();
		$where[] = 'trash = 0';
		if ($published)
		{
			$where[] = 'm.published = '.$db->quote($published);
		}
		
        $query = 'SELECT m.* FROM #__k2_categories m WHERE '.implode(' AND ', $where).' ORDER BY parent, ordering';
        $db->setQuery($query);
        $mitems = $db->loadObjectList();
        $children = array();
        if ($mitems)
        {
            foreach ($mitems as $v)
            {
                if (K2_JVERSION != '15')
                {
                    $v->title = $v->name;
                    $v->parent_id = $v->parent;
                }
                $pt = $v->parent;
                $list = @$children[$pt] ? $children[$pt] : array();
                array_push($list, $v);
                $children[$pt] = $list;
            }
        }

        $list = HTMLHelper::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
        
        $options = array();
        foreach ($list as $item)
        {
            $item->treename = StringHelper::str_ireplace('&#160;', '- ', $item->treename);
            @$options[] = HTMLHelper::_('select.option', $item->id, $item->treename);
        }

		if (isset($this->element['show_root']))
		{
			array_unshift($options, HTMLHelper::_('select.option', '0', Text::_('JGLOBAL_ROOT')));
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
	
	protected function checkComponent($component)
    {
        $db = Factory::getDbo();
        $query = " SELECT COUNT(*) FROM #__extensions AS e WHERE e.element ='$component' AND e.enabled=1";
        $db->setQuery($query);
	    return $db->loadResult();
    }
}

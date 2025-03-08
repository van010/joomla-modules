<?php
/**
 *------------------------------------------------------------------------------
 * @package       Module JA Content Listing for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2021 JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github
 *                & Google group to become co-author)
 *------------------------------------------------------------------------------
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;


FormHelper::loadFieldClass('list');

if(!defined('K2_JVERSION')) define('K2_JVERSION', '25');

class JFormFieldK2category extends ListField
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
		if(!$this->checkComponent('com_k2')) {
			$this->required = true;
		}
		$input = parent::getInput();
		if(!$this->checkComponent('com_k2')) {
			if(version_compare(JVERSION, '3.0', 'ge')) {
				$input .= '<br /><span style="color:red;">'.Text::_('K2_NOT_INSTALL').'</span>';
			} else {
				$input .= '<br /><label>&nbsp;</label><span style="color:red">'.Text::_('K2_NOT_INSTALL').'</span>';
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
        $options[] = HTMLHelper::_('select.option', "", Text::_('JOPTION_ALL_CATEGORIES'));
        foreach ($list as $item)
        {
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
        $query = " SELECT COUNT(*) FROM #__extensions AS e WHERE e.element ='$component' AND e.enabled=1 AND e.type='component'";
        $db->setQuery($query);
	    return $db->loadResult();
    }
}

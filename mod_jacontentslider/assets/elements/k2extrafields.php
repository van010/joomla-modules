<?php
/**
 * ------------------------------------------------------------------------
 * JA Content Slider Module for J25 & J34
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

if(!defined('K2_JVERSION')) define('K2_JVERSION', '25');

class JFormFieldK2extrafields extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'K2extrafields';

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

		$db = JFactory::getDbo();
		$query = "
			SELECT f.id, f.name AS fname, f.group, f.type, f.published, g.name AS gname
			FROM #__k2_extra_fields f
			INNER JOIN #__k2_extra_fields_groups g ON g.id = f.group
			WHERE f.published = 1
			AND f.type <> 'csv'
			ORDER BY f.group, f.ordering
			";
		$db->setQuery($query);
		$items = $db->loadObjectList();

		$options = array();
		if(count($items)) {
			$group = 0;
			$i=0;
			foreach($items as $item) {
				$i++;
				if($group != $item->group) {
					$group = $item->group;
					if($i != 1) {
						$options[] = JHtml::_('select.option', '</OPTGROUP>');
					}
					$options[] = JHtml::_('select.option', '<OPTGROUP>', $item->gname);
				}
				$options[] = JHtml::_('select.option', $item->id, $item->fname);
			}
			$options[] = JHtml::_('select.option', '</OPTGROUP>');
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	protected function checkComponent($component)
	{
		$db = JFactory::getDbo();
		$query = " SELECT COUNT(*) FROM #__extensions AS e WHERE e.element ='$component' AND e.enabled=1 AND e.type='component'";
		$db->setQuery($query);
		return $db->loadResult();
	}
}

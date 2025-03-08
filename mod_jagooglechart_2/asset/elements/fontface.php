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

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\ListField;

FormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Supports an HTML select list of categories
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */

if (!class_exists('ListFieldLegacy')) {
	if (version_compare(JVERSION, 4, 'ge')) {
		class ListFieldLegacy extends ListField{}
	} else {
		class ListFieldLegacy extends JFormFieldList{}
	}
}

class JFormFieldFontface extends ListFieldLegacy
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'Category';
	
	protected function getOptions()
	{
		$options = array();
		$options[] = HTMLHelper::_('select.option', 'arial', 'Arial');
		$options[] = HTMLHelper::_('select.option', 'sans', 'SansSerif');
		$options[] = HTMLHelper::_('select.option', 'serif', 'Serif');
		$options[] = HTMLHelper::_('select.option', 'wide', 'Wide');
		$options[] = HTMLHelper::_('select.option', 'narrow', 'Narrow');
		$options[] = HTMLHelper::_('select.option', 'comic', 'Comic Sans MS');
		$options[] = HTMLHelper::_('select.option', 'courier', 'Courier New');
		$options[] = HTMLHelper::_('select.option', 'garamond', 'Garamond');
		$options[] = HTMLHelper::_('select.option', 'georgia', 'Georgia');
		$options[] = HTMLHelper::_('select.option', 'tahoma', 'Tahoma');
		$options[] = HTMLHelper::_('select.option', 'verdana', 'Verdana');
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		
		return $options;
	}
}
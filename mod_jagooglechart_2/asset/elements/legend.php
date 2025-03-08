<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  2.5
 */
class JFormFieldLegend extends FormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Legend';
	
	protected function getInput()
	{
		$html = '';
		if ($this->element['enabler']) {
			$html .= '<fieldset class="btn-group btn-group-yesno ' . ($this->element['enabler'] == 'top' ? 'top-group-enabler' : 'group-enabler') . ' radio" id="' . $this->id . '">';
			$html .= '<input type="radio" ' . ($this->value == 1 ? 'checked="checked" ' : '') . 'value="1" name="' . $this->name . '" id="' . $this->id . '0">';
			$html .= '<label for="' . $this->id . '0" class="btn">' . Text::_('JON') . '</label>';
			$html .= '<input type="radio" ' . ($this->value == 0 ? 'checked="checked" ' : '') . 'value="0" name="' . $this->name . '" id="' . $this->id . '1">';
			$html .= '<label for="' . $this->id . '1" class="btn">' . Text::_('JOFF') . '</label>';
			$html .= '</fieldset>';
		}
		return $html;
	}

	/**
	 * Method to get the field label markup for a spacer.
	 * Use the label text or name from the XML element as the spacer or
	 * Use a hr="true" to automatically generate plain hr markup
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   11.1
	 */
	protected function getLabel()
	{
		
		// Get the label text from the XML element, defaulting to the element name.
		$text = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];
		$text = $this->translateLabel ? Text::_($text) : $text;
		$desc = $this->element['description'] ? (string) $this->element['description'] : '';
		$desc = $this->translateLabel ? Text::_($desc) : $desc;
		$class = 'legend';
		$class .= !empty($this->class) ? ' ' . $this->class : '';
		$class .= $this->element['subgroup'] ? ' sub-legend' : '';
		$class = 'class="' . $class . '"';
		$icon = $this->element['icon'] ? '<span class="fa fa-' . $this->element['icon'] . '"></span>' : '';
		// 
		$expend = $this->element['expend'] ? ' data-expend="' . $this->element['expend'] . '"' : ''; 

		$tooltip = $desc ? ' class="hasTooltip" title="' . htmlentities($desc) . '"' : '';
		$html = "<h3 $class$expend><span$tooltip>$icon$text</span></h3>";

		return $html;
	}

	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		// get template name
		$path = str_replace (JPATH_ROOT, '', __DIR__);
		$path = str_replace ('\\', '/', substr($path, 1));

		$doc = Factory::getDocument();
		$doc->addStyleSheet (Uri::root() . $path . '/assets/css/style.css');
		$doc->addScript (Uri::root() . $path . '/assets/js/legend.js');
		//$doc->addScript ('http://livejs.com/live.js#css');
		return parent::setup($element, $value, $group);
	}

}

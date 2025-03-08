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
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('moduleposition');
/**
 * Form Field class for the Joomla Framework.
 *
 * @since  2.5
 */
class JFormFieldTplhelper extends FormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'TplHelper';
	protected function getInput()
	{
		return '<input type="hidden" id="tplhelper" name="' . $this->name . '" value="' . htmlspecialchars($this->value) . '" />';
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
		return '';
	}

	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		// Define some CONSTANT LANGUAGE to use in script file
		Text::script('JA_GOOGLECHART_GUIDE');
		Text::script('JA_GOOGLECHART_GUIDE_CONTENT');
		Text::script('JA_GOOGLECHART_GUIDE_TITLE');
		
		// get template name
		$path = str_replace (JPATH_ROOT, '', __DIR__);
		$path = str_replace ('\\', '/', substr($path, 1));

		$doc = Factory::getDocument();
		if(version_compare(JVERSION, '3.0', '>='))
			$doc->addStyleSheet (Uri::root() . $path . '/assets/css/style.css');
		if(version_compare(JVERSION, '3.0', '>='))
			$doc->addStyleSheet ('https://fonts.googleapis.com/css?family=Roboto:400,500');
		$script = 'var site_root_url = "' . Uri::root(true) . '";';
		$doc->addScriptDeclaration($script);
		return parent::setup($element, $value, $group);
	}

}

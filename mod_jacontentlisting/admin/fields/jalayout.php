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

// Ensure this file is being included by a parent file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Form\Form as JForm;
use Joomla\CMS\Form\FormField as JFormField;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * Radio List Element.
 *
 * @since      Class available since Release 1.2.0
 */
class JFormFieldJalayout extends JFormField
{
	/**
	 * Element name.
	 *
	 * @var string
	 */
	protected $type = 'Jalayout';
	
	protected function getInput()
	{
		$params = json_decode($this->value);
		if (!$this->value) {
			$this->value = json_encode('');
		}
		
		HTMLHelper::_('stylesheet', 'modules/mod_jacontentlisting/admin/assets/css/style.css');
		HTMLHelper::_('stylesheet', 'modules/mod_jacontentlisting/admin/assets/css/jalayout.css');
		HTMLHelper::_('script', 'modules/mod_jacontentlisting/admin/assets/js/jalayout.js');
		$lang = Factory::getLanguage();
		$extension = 'mod_jacontentlisting';
		$base_dir = JPATH_SITE;
		$language_tag = $lang->getTag();
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);
		
		$nameField = $this->element['name'];
		$fieldType = $this->element['field'];
		$subfolder = $this->element['subfolder'];
		$field_value = !empty($params->layout) ? $params->layout : 'default';
		$xmlparams = $this->findConfig($fieldType, '');
		$extendXml = $this->findConfig($fieldType, $field_value . "/");
		
		/* Get all profiles name folder from folder profiles */
		$profiles = [];
		$jsonData = [];
		$layoutData = [];
		$JAlayoutData = [];
		// get in module
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		
		if (!empty($xmlparams) && file_exists($xmlparams)) {
			$extendParams = null;
			$input_value = !empty($this->value) ? $this->value : '';
			$HTML_Profile = '<input type="hidden" id="' . $this->id . '_settings" name="' . $this->name . '" value="' . htmlspecialchars($input_value) . '" />';
			/* For General Form */
			$options = ['control' => 'jaform'];
			
			if ($nameField == 'jaitem_featured') {
				
				$options = ['control' => 'jftform'];
			}
			
			$paramsForm = JForm::getInstance('jform-' . $nameField, $xmlparams, $options);
			if (!empty($extendXml)) {
				$extendParams = JForm::getInstance('jaext-' . $nameField, $extendXml, $options);
			}
			
			$JAlayoutData['form'] = $paramsForm;
			$JAlayoutData['extendForm'] = $extendParams;
			$JAlayoutData['nameField'] = $nameField;
			$JAlayoutData['params'] = $this->value;
			$JAlayoutData['id'] = $this->id;
			$JAlayoutData['name'] = $this->name;
			$JAlayoutData['value'] = $this->value;
			$JAlayoutData['input'] = $HTML_Profile;
			
			return LayoutHelper::render('tmpl.jalayout', $JAlayoutData, JPATH_ROOT . '/modules/mod_jacontentlisting/admin');
		}
	}
	
	public function renderField($options = array())
	{
		return $this->getInput();
	}
	
	public function getLabel()
	{
		return null;
	}
	
	public function findConfig($type, $typeName, $get_url = false)
	{
		// Build the template and base path for the layout
		$paths = [];
		$data = $this->form->getData();
		$id = $data->get('id');
		$params = !empty($data->get('params')) ? $data->get('params') : new stdClass;
		
		if(empty($params->jasource)){
			$params->jasource = '{"sources": "content"}';
		}
		
		if (isset($params->jasource)) {
			$jasource = new Registry($params->jasource);
			File::write(JPATH_ROOT . "/modules/mod_jacontentlisting/cache/$id-jasource.json", json_encode($jasource));
		}
		
		// template folders
		$tpls = Folder::folders(JPATH_ROOT . '/templates/');
		foreach ($tpls as $tpl) {
			$paths[Uri::root(true) . '/templates/' . $tpl . '/html/mod_jacontentlisting/' . $type . '/'] = JPATH_ROOT . '/templates/' . $tpl . '/html/mod_jacontentlisting/' . $type . '/';
		}
		// in module
		if ($type == 'layouts') {
			$paths[Uri::root(true) . '/modules/mod_jacontentlisting/tmpl/' . $type . '/'] =
				JPATH_ROOT . '/modules/mod_jacontentlisting/tmpl/' . $type . '/';
		} else{
			switch ($jasource->get('sources')){
				case 'eshop':
				case 'hikashop':
				case 'jshopping':
				case 'docman':
				case 'vm':
					$paths[Uri::root(true) . '/modules/mod_jacontentlisting/tmpl/' . $type . '/ecommerces/'] =
						JPATH_ROOT . '/modules/mod_jacontentlisting/tmpl/' . $type . '/ecommerces/';
					break;
				case '': // load item-setting for new install
				case 'k2':
				case 'easyblog':
				case 'content':
					$paths[Uri::root(true) . '/modules/mod_jacontentlisting/tmpl/' . $type . '/contents/'] =
						JPATH_ROOT . '/modules/mod_jacontentlisting/tmpl/' . $type . '/contents/';
					break;
				default:
					$paths[Uri::root(true).'/modules/mod_jacontentlisting/tmpl/'.$type.'/'] =
						JPATH_ROOT.'/modules/mod_jacontentlisting/tmpl/'.$type.'';
					break;
			}
		}
		
		foreach ($paths as $uri => $path) {
			if (is_file($path . $typeName . 'info.xml')) {
				return ($get_url ? $uri : $path) . $typeName . 'info.xml';
			}
		}
		return null;
	}
}

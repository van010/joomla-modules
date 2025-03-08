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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * Radio List Element.
 *
 * @since      Class available since Release 1.2.0
 */
class JFormFieldJasources extends JFormField
{
    /**
     * Element name.
     *
     * @var string
     */
    protected $type = 'Jasources';

    protected function getInput()
    {
        $params = json_decode($this->value);
        if (!$params) {
            $this->value = json_encode('');
        }
        $lang = Factory::getLanguage();
        $extension = 'mod_jacontentlisting';
        $base_dir = JPATH_SITE;
        $language_tag = $lang->getTag();
        $reload = true;
        $lang->load($extension, $base_dir, $language_tag, $reload);
        $lang->load('com_k2', JPATH_ADMINISTRATOR, $language_tag, $reload);

        $nameField = $this->element['name'];
        $subfolder = $this->element['subfolder'];

        // get in module
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        
        $path = JPATH_SITE."/modules/mod_jacontentlisting/". $subfolder;
        if (!Folder::exists($path)) {
            return Text::_('PROFILE_FOLDER_NOT_EXIST');
        }
        HTMLHelper::_('stylesheet', 'modules/mod_jacontentlisting/admin/assets/css/style.css');
        HTMLHelper::_('stylesheet', 'modules/mod_jacontentlisting/admin/assets/css/jalayout.css');
        HTMLHelper::_('script', 'modules/mod_jacontentlisting/admin/assets/js/jalayout.js');
        $field_value = !empty($params->sources) ? $params->sources : 'content';
        $configxml = $field_value.'.xml';
        /* Get all profiles name folder from folder profiles */
        $html = $this->getApdapters($path,$field_value);
        $profiles = [];
        $jsonData = [];
        $layoutData = [];
        $JAlayoutData = [];
        $xmlparams = $path."/".$configxml;

        if (file_exists($xmlparams)) {
            $extendParams = null;
			$input_value = !empty($this->value) ? $this->value : '';
            $HTML_Profile = '<input type="hidden" id="'.$this->id.'_settings" name="'.$this->name.'" value="'.htmlspecialchars($input_value).'" />';
            $HTML_Profile .= $html;
            /* For General Form */
            $options = ['control' => 'jaform'];
            $paramsForm = JForm::getInstance('jform-'.$nameField, $xmlparams, $options);
            if (!empty($extendXml)) {
                $extendParams = JForm::getInstance('jaext-'.$nameField, $extendXml, $options);
            }

            $JAlayoutData['form'] = $paramsForm;
            $JAlayoutData['extendForm'] = $extendParams;
            $JAlayoutData['nameField'] = $nameField;
            $JAlayoutData['params'] = $this->value;
            $JAlayoutData['id'] = $this->id;
            $JAlayoutData['name'] = $this->name;
            $JAlayoutData['value'] = $this->value;
            $JAlayoutData['input'] = $HTML_Profile;

            return LayoutHelper::render('tmpl.jalayout', $JAlayoutData, JPATH_ROOT.'/modules/mod_jacontentlisting/admin');
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
    public function getApdapters($path,$value = '')
    {
        $options = array();
        $content = new \stdClass();
        $content->value = 'content';
        $content->text = Text::_('JA_CONTENT_LISTING_SOURCE_CONTENT_CONTENT');

        $k2 = new \stdClass();
        $k2->value = 'k2';
        $k2->text = Text::_('JA_CONTENT_LISTING_SOURCE_CONTENT_K2');
        $k2->disable = false;

        $easyblog = new \stdClass();
        $easyblog->value = 'easyblog';
        $easyblog->text = Text::_('JA_CONTENT_LISTING_SOURCE_CONTENT_EASYBLOG');
        $easyblog->disable = false;

        $vm = new \stdClass();
        $vm->value = 'vm';
        $vm->text = Text::_('JA_CONTENT_LISTING_SOURCE_CONTENT_VM');
        $vm->disable = false;

        $options[Text::_('JA_CONTENT_LISTING_SOURCE_CONTENT')] = array(
          $content,$k2,$easyblog,$vm
        );
        $attr = '';
        $attr .= !empty($class) ? ' class="form-select ' . $class . '"' : ' class="form-select"';
        $html = '<div class="control-group">';
        $html .= '<div class="control-label">';
        $html .= '<label id="jaform_jasource_settings_sources-lbl" for="jaform_jasource_settings_sources" class="hasPopover" title="" data-content="'.Text::_($this->element['description']).'" data-original-title="'.Text::_($this->element['label']).'">'.Text::_($this->element['label']).'</label>';
        $html .= '</div>';
        $html .= '<div class="controls">';
        $html .=  HTMLHelper::_('select.groupedlist', $options, 'jaform[jasource-settings][sources]',array('list.attr' => $attr, 'id' => 'jaform_jasource_settings_sources', 'list.select' => $value, 'group.items' => null, 'option.key.toHtml' => false, 'option.text.toHtml' => false));
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
}

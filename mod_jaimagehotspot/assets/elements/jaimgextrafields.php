<?php
/**
 * ------------------------------------------------------------------------
 * JA Image Hotspot Module for Joomla 2.5 & 3.4
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filesystem\Folder;


jimport('joomla.form.formfield');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');


class JFormFieldJaimgextrafields extends FormField {

    protected $type = 'Jaimgextrafields';

    public function getControlGroup()
    {
        if ($this->hidden) {
            return $this->getInput();
        }

        return
            '<div class="control-group control-xfgroup span3">'
            . '<div class="controls">' . $this->getInput() . '</div>'
            . '</div>';
    }

    protected function getInput() {
        $description = json_decode($this->value);

        /*
         * Include js and css
         * Url define in xml file
         * */
        $extpath = $this->element['extpath'];
        $doc = Factory::getDocument();
        $doc->addStyleSheet(Uri::root().$extpath.'/imgextrafields.css');

        /*
         * Get extra fields of map from xml file
         * Return string
         * */
        $html = array();

        $html[] = '<div id="extrafieldimg" class="extrafieldimg">';
        $html[] = '<div id="extrafield-action"><input class="btn btn-primary" type="button" name="'.Text::_("JAI_ADD").'" value="'.Text::_("JAI_ADD").'" id="jai_add" />
				<input type="button" class="btn btn-danger" name="remove" value="'.Text::_("JAI_REMOVE").'" id="jai_remove" style="display:none;" /></div>';

        $jaset = Folder::files(dirname(__FILE__).'/../images/ico/', $filter = '.', false, false);

        $htmlpoint = '';
        $maxid = 0;
        $desCount = (is_array($description)) ? count($description) : 0;
        if($desCount>0) {
            foreach ($description as $des) {
                $maxid = max($maxid, $des->imgid);
            }
        }

        $doc->addScriptOptions('imagehotspot', array(
            'maxid' => $maxid,
            'desc' => json_decode($this->value),
            'morethan37' => version_compare(JVERSION, '3.7', 'ge')
        ));

        HTMLHelper::_('behavior.core');
        HTMLHelper::_('jquery.framework');
        HTMLHelper::_('script', 'modules/mod_jaimagehotspot/assets/jquery/jquery-ui_dragable.js');
        $doc->addScript(Uri::root() . 'modules/mod_jaimagehotspot/assets/elements/assets/js/jaimgextrafields.js');

        Text::script('JAI_INSERT_NUMBERIC');
        Text::script('JAI_INSERT_NUMBERIC_LESS_THAN');
        Text::script('JAI_INSERT_NUMBERIC_GREATER_THAN');
        Text::script('JAI_INSERT_NUMBERIC_GREATER_THAN_EX');

        $html[] = $this->getExtrafield();

        $html[] = '</div>';
        /*
         * Show input add position
         * */


        $html[] = '<textarea style="display: none;" rows="6" cols="60" name="' . $this->name . '" id="' . $this->id . '" >'. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') .'</textarea>';
        if (!empty($jaset)) {
            $jasethtml = '<li id="jasetli"><ul id="ja-iconset">';
            foreach ($jaset AS $jset) {
                $jasethtml .= '<li class="jasetimgli"><img class="jasetimg" src="'.Uri::root(true).'/modules/mod_jaimagehotspot/assets/images/ico/'.$jset.'" /></li>';
            }
            $jasethtml .= '</ul></li>';
            $html[] = $jasethtml;
        }
        //$script = implode('', $script);

        return implode("\n", $html);
    }
    public function getExtrafield($obj = NULL){
        /*
         * Check case null
         * */
        $checkobj = true;
        if(!isset($obj)){
            $checkobj = false;
            $obj = new stdClass();
            $obj->id = '';
        }

        /*
         * Get extra fields of map from xml file
         * Return string
         * */
        $html = array();
        $html[] = '<ul class="adminformlist'.$obj->id.' deactive">';
        $extraXml = dirname(__FILE__) . '/jaimgextrafields/imgextrafields.xml';
        if(file_exists($extraXml)){
            $options = array('control' => 'jaform');
            $paramsForm = Form::getInstance('jform', $extraXml, $options);
            $fieldSets = $paramsForm->getFieldsets('params');
            $desdefault = array();
            foreach ($fieldSets as $name => $fieldSet) :
                $html[] = '<li class="'.$fieldSet->name.'">';
                $html[] = '<ul>';

                if (isset($fieldSet->description) && trim($fieldSet->description)){
                    $html[] = '<li class="tip">'.Text::_($fieldSet->description).'</li>';
                }
                $hidden_fields = '';
                foreach ($paramsForm->getFieldset($name) as $field) :
                    $fieldname = $field->fieldname;
                    $desdefault[$field->fieldname] = $field->value;
                    if(!$checkobj){
                        $obj->$fieldname = $field->value?$field->value:'';
                    }
                    if (!$field->hidden):
                        $html[] = '<li>';
                        $html[] = $paramsForm->getLabel($field->fieldname,$field->group);
                        $html[] = $paramsForm->getInput($field->fieldname,$field->group,$obj->$fieldname);
                        $html[] = '</li>';
                    else :
                        $hidden_fields .= $paramsForm->getInput($field->fieldname,$field->group,$obj->$fieldname);
                    endif;
                endforeach;
                $html[] = $hidden_fields;
                $html[] = '</ul>';
                $html[] = '</li>';

            endforeach;
        }

        $doc = Factory::getDocument();
        $doc->addScriptOptions('imagehotspot', array(
            'desc_default' => $desdefault
        ));

        if(!$checkobj){
            $html[] = '<input type="hidden" name="imgid" value="">';
        }else {
            $html[] = '<input type="button" class="btn btn-mini btn-danger" name="remove" value="'.Text::_('JAI_REMOVE', true).'">';
        }
        $html[] = '</ul>';
        return implode("\n", $html);
    }

}
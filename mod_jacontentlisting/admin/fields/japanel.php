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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.form.formfield');

require_once(dirname(__FILE__).'/../behavior.php');
class JFormFieldJapanel extends FormField {
    protected $type = 'Japanel';
    
    protected function getInput() {
        $func = (string) $this->element['function'];
        if(!$func) {
            $func = 'init';
        }
        
        if(method_exists($this, $func)) {
            call_user_func_array(array($this, $func), array());
        }
        return null;
    }
    
    protected function init() {
        $doc = Factory::getDocument();
        $path = URI::root().$this->element['path'];
        if (version_compare(JVERSION, '4', 'lt')
            && !version_compare(JVERSION, '3.4', 'lt')) // joomla 3.4.x not call mootools by default
        {
            HTMLHelper::_('behavior.framework', true);
        }

        if(version_compare(JVERSION, '3.0', 'lt')) {
            HTMLHelper::_('JABehavior.jquery');
            HTMLHelper::_('JABehavior.jquerychosen', '.form-validate select');
            
        }

        if (version_compare(JVERSION, '5.0.3', 'gt')) {
            HTMLHelper::_('jquery.framework');
        }

        $doc->addScript($path.'japanel/depend.js');
        $doc->addStyleSheet($path.'japanel/style.css');
        $doc->addScript($path.'japanel/script.js');
        return null;
    }
    
    protected function depend() {
        $group_name = 'jform';
        preg_match_all('/jform\\[([^\]]*)\\]/', $this->name, $matches);
        
        if(!isset($matches[1]) || empty($matches[1])){
            preg_match_all('/jaform\\[([^\]]*)\\]/', $this->name, $matches);
            $group_name = 'jaform';
        }
        if(!isset($matches[1]) || empty($matches[1])){
            preg_match_all('/jftform\\[([^\]]*)\\]/', $this->name, $matches);
            $group_name = 'jftform';
        }
        
        
        $script = '';
        if(isset($matches[1]) && !empty($matches[1])) {
            foreach ($this->element->children() as $option){
                $elms = preg_replace('/\s+/', '', (string)$option[0]);
                $script .= "
                    JADepend.inst.add('".$option['for']."', {
                        val: '".$option['value']."',
                        elms: '".$elms."',
                        group: '".$group_name . '[' . @$matches[1][0] . ']'."'
                    });
                    JADepend.inst.start();
                    ";
            }
        }
        if(!empty($script)) {
            $doc = Factory::getDocument();
            $doc->addScriptDeclaration("
             window.addEventListener('load', function(){
                ".$script."
            });");
        }
    }
}

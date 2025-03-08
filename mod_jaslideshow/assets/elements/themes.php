<?php
/**
 * $JA#COPYRIGHT$
 */

// Ensure this file is being included by a parent file
defined('_JEXEC') or die('Restricted access');
/**
 * Radio List Element
 *
 * @since      Class available since Release 1.2.0
 */
class JFormFieldThemes extends JFormField
{
    /*
     * @var name element
     */
    protected $type = 'themes';


    /**
     * Fetch Ja Element Theme Param method
     * 
     * @return	object  param
     */
    function getInput()
    {
        $getJaTemplate = new JFormFieldJaDepend;
    	
    	$getJaTemplate->loadAsset();
        
        /* Get all themes name folder from folder themes */
        $themes = array();
        
        // get in module
        $path = JPATH_SITE . '/modules/mod_jaslideshow/tmpl';
        if (!JFolder::exists($path))
            return JText::_('Themes Folder not exist');
        $folders = JFolder::folders($path);
        if ($folders) {
            foreach ($folders as $fname) {
                $themes[$fname] = $fname;
            }
        }
        
        // get in template	 
		
        $template = $getJaTemplate->getActiveTemplate();
        $path = JPATH_SITE . '/templates/' . $template . '/html/mod_jaslideshow';
        if (JFolder::exists($path)) {
            $folders = JFolder::folders($path);
            if ($folders) {
                foreach ($folders as $fname) {
                    $themes[$fname] = $fname;
                }
            }
        }
        
        $HTMLThemes = array();
        if ($themes) {
            foreach ($themes as $fname) {
                //
                $f = new stdClass();
                $f->id = $fname;
                $f->title = $fname;
                array_push($HTMLThemes, $f);
            }
        }
        
        $html = JHTML::_('select.genericlist', $HTMLThemes, @$matches[1][0] . $this->name, 'style="width:150px; position:relative"', 'id', 'title', $this->value);
        return $html;
    }
}
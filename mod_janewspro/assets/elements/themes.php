<?php
/**
 * $JA#COPYRIGHT$
 */

// Ensure this file is being included by a parent file
defined('_JEXEC') or die('Restricted access');
if(!defined('DS')){
	define('DS', DIRECTORY_SEPARATOR);
}
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
        /* Get all themes name folder from folder themes */
        $themes = array();
        
        // get in module
        $path = JPATH_SITE . DS . 'modules' . DS . 'mod_janewspro' . DS . 'tmpl';
        if (!JFolder::exists($path))
            return JText::_('Themes Folder not exist');
        $folders = JFolder::folders($path);
        if ($folders) {
            foreach ($folders as $fname) {
                $themes[$fname] = $fname;
            }
        }
        
        // get in template	 
		
        $template = $this->getActiveTemplate();
        $path = JPATH_SITE . DS . 'templates' . DS . $template . DS . 'html' . DS . 'mod_janewspro';
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
	
	/**
     *
     * Get Template Actived
     * @return string template active name
     */
	function getActiveTemplate(){

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('template');
		$query->from('#__template_styles');
		$query->where('client_id = 0');
		$query->where('home = 1');

		$db->setQuery($query);
		$tpl = $db->loadResult();

		return $tpl;
	}
}
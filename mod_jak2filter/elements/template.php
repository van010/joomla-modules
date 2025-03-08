<?php
/**
 * @version		$Id: template.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

if (file_exists((JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php'))) {
require_once (JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

class K2ElementTemplate extends K2Element
{

    public function fetchElement($name, $value, &$node, $control_name)
    {

        jimport('joomla.filesystem.folder');
        $mainframe = JFactory::getApplication();
        $fieldName = (K2_JVERSION != '15') ? $name : $control_name.'['.$name.']';
        $componentPath = JPATH_SITE.'/components/com_k2/templates';
        $componentFolders = JFolder::folders($componentPath);
        $db = JFactory::getDBO();
        if (K2_JVERSION != '15')
        {
            $query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home = 1";
        }
        else
        {
            $query = "SELECT template FROM #__templates_menu WHERE client_id = 0 AND menuid = 0";
        }
        $db->setQuery($query);
        $defaultemplate = $db->loadResult();

        if (JFolder::exists(JPATH_SITE.'/templates/'.$defaultemplate.'/html/com_k2/templates'))
        {
            $templatePath = JPATH_SITE.'/templates/'.$defaultemplate.'/html/com_k2/templates';
        }
        else
        {
            $templatePath = JPATH_SITE.'/templates/'.$defaultemplate.'/html/com_k2';
        }

        if (JFolder::exists($templatePath))
        {
            $templateFolders = JFolder::folders($templatePath);
            $folders = @array_merge($templateFolders, $componentFolders);
            $folders = @array_unique($folders);
        }
        else
        {
            $folders = $componentFolders;
        }

        $exclude = 'default';
        $options = array();
        foreach ($folders as $folder)
        {
            if (preg_match(chr(1).$exclude.chr(1), $folder))
            {
                continue;
            }
            $options[] = JHTML::_('select.option', $folder, $folder);
        }

        array_unshift($options, JHTML::_('select.option', '', '-- '.JText::_('K2_USE_DEFAULT').' --'));

        return JHTML::_('select.genericlist', $options, $fieldName, 'class="inputbox"', 'value', 'text', $value, $name);

    }

}

class JFormFieldTemplate extends K2ElementTemplate
{
    var $type = 'templatek2';
}

class JElementTemplate extends K2ElementTemplate
{
    var $_name = 'templatek2';
}

}

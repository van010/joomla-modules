<?php
/**
 * $JA#COPYRIGHT$
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
if(!defined('DS')){
	define('DS', DIRECTORY_SEPARATOR);
}
require_once (dirname(__FILE__) . DS . 'helpers' . DS . 'helper.php');

$files = JFolder::files(dirname(__FILE__) . DS . 'helpers' . DS . 'adapter');
if ($files) {
    foreach ($files as $file) {
       //only load php files
       if(strpos($file,".php")){
         require_once (dirname(__FILE__) . DS . 'helpers' . DS . 'adapter' . DS . $file);
	   }
    }
}
$mainframe = JFactory::getApplication();

$helper = new modJaNewsProHelper($module, $params);

if (JRequest::getBool('janajax')) {
    $moduleid = JRequest::getInt('moduleid');
    if ($moduleid != $module->id)
        return;
    
    $group = JRequest::getString('group');
    
    $cookie_value = array();
    
    $arr_catsid = JRequest::getVar('categories', array(), 'default', 'array');
    if ($arr_catsid) {
        $cookie_value[] = 'cookie_catsid=' . implode(',', $arr_catsid);
    }
    
    if (isset($_REQUEST['showimage'])) {
        $cookie_value[] = 'showimage=' . JRequest::getInt('showimage', 0);
    }
    
    if (isset($_REQUEST['introitems'])) {
        $cookie_value[] = 'introitems=' . JRequest::getInt('introitems', 1);
    }
    
    if (isset($_REQUEST['linkitems'])) {
        $cookie_value[] = 'linkitems=' . JRequest::getInt('linkitems', 1);
    }
    $cookie_value[] = 'maxSubCats=-1';
    
    if ($cookie_value) {
        $cookie_value = implode('&', $cookie_value);
        $cookie_name = 'mod' . $moduleid . '_' . $group;
        setcookie($cookie_name, $cookie_value, time() + 30 * 24 * 3600, '/');
        $_COOKIE[$cookie_name] = $cookie_value;
        $_COOKIE['mod' . $moduleid] = isset($_COOKIE['mod' . $moduleid]) ? $_COOKIE['mod' . $moduleid] . $group : $group;
        setcookie('mod' . $moduleid, $_COOKIE['mod' . $moduleid], time() + 30 * 24 * 3600, '/');
    }

    //$helper->set('maxSubCats', -1);				
} elseif (JRequest::getBool('janewspro_linear_ajax')) {
    
    $moduleid = JRequest::getInt('moduleid');
    if ($moduleid != $module->id)
        return;
    
    $catid = JRequest::getVar('subcat');
    if (!$catid)
        return;
    $params->set('catsid', $catid);
    $params->set('k2catsid', $catid);
}

if (isset($_COOKIE['mod' . $module->id]))
    $helper->set('cookie', $_COOKIE['mod' . $module->id]);

$theme = $helper->get('themes', 'default');
if ($theme == 'linear') {
    JHTML::script('modules/' . $module->module . '/tmpl/' . $theme . '/' . 'script.js');
    $helper->jaset('groupbysubcat', 0);
}

if (!defined ('_MODE_JAMODNEWSPRO_ASSETS_')) {
	define ('_MODE_JAMODNEWSPRO_ASSETS_', 1);
	JHTML::stylesheet('modules/'.$module->module.'/assets/css/style.css');	
	
	if (is_file(JPATH_SITE.DS.'templates'.DS.$mainframe->getTemplate().DS.'css'.DS.$module->module.".css"))
	JHTML::stylesheet( 'templates/'.$mainframe->getTemplate().'/css/'.$module->module.".css");
	
	//mootools support joomla 1.7 and 2.5
	JHTML::_('behavior.framework', true);
	
	JHTML::_('behavior.tooltip');
	JHTML::script('modules/'.$module->module.'/assets/js/script.js');
}
if (!defined('_MODE_JAMODNEWSPRO_ASSETS_' . $theme)) {
    define('_MODE_JAMODNEWSPRO_ASSETS_' . $theme, 1);
    
    if (is_file(JPATH_SITE . DS . 'modules' . DS . $module->module . DS . 'tmpl' . DS . $theme . DS . "style.css"))
        JHTML::stylesheet('modules/' . $module->module . '/tmpl/' . $theme . '/style.css');
    
    if (is_file(JPATH_SITE . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'html' . DS . $module->module . DS . $theme . DS . "style.css"))
        JHTML::stylesheet('templates/' . $mainframe->getTemplate() . '/html/' . $module->module . '/' . $theme . '/style.css');
}

$cache_id = '';

$use_cache = 1;//$mainframe->getCfg("caching");
if ( $params->get('jacache') == "1" && $use_cache == "1") {
	$options = array(
		'lifetime' 		=> (int)($params->get( 'jacache_time', 30 ) * 60),
		'caching'		=> true,
	);
		
	jimport('joomla.cache.cache');
	$cache =	new JCache($options);
		
	
	/* Make cache id */
	$cache_id = $helper->_makeId($params, $module->id);
	if($cache_id!=''){		
   		$oldlen = strlen(ob_get_contents());
   	}	
	
	if($helper->check_cache($cache, $cache_id, 'mod_janewspro')){
		if(JRequest::getBool('janewspro_linear_ajax'))	
			exit;		
		if (JRequest::getBool ('janajax')) 
			exit;
		return;
	}
	
}

$helper->_load($params, $module->id);

if (JRequest::getBool('janewspro_linear_ajax')) {
    
    $path = JModuleHelper::getLayoutPath($module->module, $theme . '/blog_item');
    if (file_exists($path)) {
        ob_clean();
        $rows = array();
        if ($helper->articles) {
            foreach ($helper->articles as $secid => $rows) {
                break;
            }
            require ($path);
        }
        
        /* Set cache */
        if ($params->get('jacache') == "1" && $use_cache == "1") {
            
            $maincontent = substr(ob_get_contents(), $oldlen);
            if ($maincontent) {
                $cache->store($maincontent, $cache_id, 'mod_janewspro');
            }
        }
        exit();
    }
}

$path = JModuleHelper::getLayoutPath($module->module, $theme . '/blog');
if (file_exists($path)) {
    if (JRequest::getBool('janajax')) {
        ob_clean();
        require ($path);
        
        /* Set cache */
        if ($params->get('jacache') == "1" && $use_cache == "1") {
            
            $maincontent = substr(ob_get_contents(), $oldlen);
            if ($maincontent) {
                $cache->store($maincontent, $cache_id, 'mod_janewspro');
            }
        }
        exit();
    } else
        require ($path);
        
    /* Set cache */
    if ($params->get('jacache') == "1" && $use_cache == "1") {
        
        $maincontent = substr(ob_get_contents(), $oldlen);
        if ($maincontent) {
            $cache->store($maincontent, $cache_id, 'mod_janewspro');
        }
    }
}
?>
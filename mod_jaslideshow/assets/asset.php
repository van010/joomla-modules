<?php
/**
 * $JA#COPYRIGHT$
 */

$app = JFactory::getApplication();

$basepath = 'modules/' . $module->module . '/assets/';

//load override css
$templatepath = 'templates/'.$app->getTemplate().'/css/'.$module->module;


if (!defined('_MODE_JASLIDESHOW2_ASSETS_')) {
	define('_MODE_JASLIDESHOW2_ASSETS_', 1);
	
	
	JHtml::_('stylesheet', $basepath . 'themes/default/style.css');
	JHtml::_('script',  $basepath . 'script/script.js');
	if (!empty($skin)) {
		if(JFile::exists( JPATH_SITE . '/' . $basepath . 'themes/' . $skin . '/style.css')){
			JHtml::_('stylesheet', $basepath . 'themes/' . $skin . '/style.css');
		}
		if(JFile::exists(JPATH_SITE . '/' . $basepath . 'themes/' . $skin . '/' . $module->module . '.css')){
			JHtml::_('stylesheet',  $basepath . 'themes/' . $skin . '/' . $module->module . '.css');
		}
	
		//add style for T3 v3
		if (JFile::exists(JPATH_SITE . '/' . $templatepath . '-'. $skin .'.css')){
			JHtml::_('stylesheet', $templatepath . '-'. $skin .'.css');
		}
	}
	
	if (JFile::exists(JPATH_SITE . '/' . $templatepath . '.css')){
		JHtml::_('stylesheet', $templatepath . '.css');
	}
	
} elseif (!empty($skin)) {
	if(JFile::exists(JPATH_SITE . '/' . $basepath . 'themes/' . $skin . '/style.css')){
		JHtml::_('stylesheet',  $basepath . 'themes/' . $skin . '/style.css');
	}
	if(JFile::exists(JPATH_SITE . '/' . $basepath . 'themes/' . $skin . '/' . $module->module . '.css')){
		JHtml::_('stylesheet',  $basepath . 'themes/' . $skin . '/' . $module->module . '.css');
	}

	//add style for T3 v3
	if (JFile::exists(JPATH_SITE . '/' . $templatepath . '-'. $skin .'.css')){
		JHtml::_('stylesheet', $templatepath . '-'. $skin .'.css');
	}
}
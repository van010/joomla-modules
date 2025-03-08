<?php
/**
 * ------------------------------------------------------------------------
 * JA K2 Filter Module for J25 & J3.4
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */
 // No direct to access this file
 defined('_JEXEC') or die();

$app = JFactory::getApplication();
$doc = JFactory::getDocument();
$basepath = JURI::root(true).'/modules/' . $module->module . '/assets/';

$doc->addStyleSheet($basepath.'css/style.css');
//load override css
$templatepath = 'templates/'.$app->getTemplate().'/css/'.$module->module.'.css';
if(file_exists(JPATH_SITE . '/' . $templatepath)) {
	$doc->addStyleSheet(JURI::root(true).'/'.$templatepath);
}

//script
$doc->addScript($basepath.'js/jak2filter.js?v=2');
$doc->addScript($basepath.'jquery/jquery-sortable.js');
$doc->addScript($basepath.'sortablejs/Sortable.min.js');
<?php
/**
 * ------------------------------------------------------------------------
 * JA Google Chart 2 Module for J25 & J34
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
 
$app = Factory::getApplication();
$doc = Factory::getDocument();
$basepath = Uri::root(true).'/modules/' . $module->module . '/asset/';

$doc->addStyleSheet($basepath.'style.css');
//load override css
$templatepath = 'templates/'.$app->getTemplate().'/css/'.$module->module.'.css';
if(file_exists(JPATH_SITE . '/' . $templatepath)) {
	$doc->addStyleSheet(Uri::root(true).'/'.$templatepath);
}

//Load the AJAX API
$doc->addScript('https://www.google.com/jsapi');
// Load the Visualization API and the corechart package.
$doc->addScriptDeclaration('google.load("visualization", "51", {packages: ["corechart"]});');
$doc->addScriptDeclaration('google.load("visualization", "51", {packages: ["geochart"]});');
//script
$doc->addScript($basepath.'papaparse.min.js');
$doc->addScript($basepath.'google-chart.js');
<?php
/**
 * $JA#COPYRIGHT$
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

$app = Factory::getApplication();
$doc = Factory::getDocument();
$basepath = Uri::root(true).'/modules/' . $module->module . '/assets/';

$doc->addStyleSheet($basepath.'style.css');
//load override css
$templatepath = 'templates/'.$app->getTemplate().'/css/'.$module->module.'.css';
if(file_exists(JPATH_SITE . '/' . $templatepath)) {
	$doc->addStyleSheet(Uri::root(true).'/'.$templatepath);
}

//script
//$doc->addScript($basepath.'script.js');
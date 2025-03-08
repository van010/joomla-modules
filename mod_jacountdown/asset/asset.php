<?php
/**
 * $JA#COPYRIGHT$
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

$app = Factory::getApplication();
$doc = Factory::getDocument();
$basepath = Uri::root(true).'/modules/' . $module->module . '/asset/';

$doc->addStyleSheet($basepath.'css/style.css');
//load override css
$templatepath = 'templates/'.$app->getTemplate().'/css/'.$module->module.'.css';
if(file_exists(JPATH_SITE . '/' . $templatepath)) {
	$doc->addStyleSheet(Uri::root(true).'/'.$templatepath);
}
$doc->addCustomTag('
<!--[if lt IE 9]>
        <script type="text/javascript" src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
        <script type="text/javascript" src="'.$basepath.'js/excanvas.compiled.js"></script>
    <![endif]-->
');
//script
//$doc->addScript($basepath.'script/scrip.js');
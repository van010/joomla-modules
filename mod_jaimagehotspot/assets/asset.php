<?php
/**
 * ------------------------------------------------------------------------
 * JA Image Hotspot Module for Joomla 2.5 & 3.4
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
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
$lang = Factory::getLanguage();
$basepath = Uri::root(true).'/modules/' . $module->module . '/assets/';
//$description =$params->get('description');
jimport('joomla.filesystem.file');


//Load library jquery
require_once(JPATH_ROOT . '/modules/mod_jaimagehotspot/assets/jabehavior.php');
JaLoadAssets::jquery();

if(!defined('T3')){
    //Load popover and dropdown library
    $doc->addScript($basepath.'js/bootstrap-tooltip.js?v=1');
    $doc->addScript($basepath.'js/bootstrap-popover.js?v=1');
}
if (!defined('_MODE_JAIMAGESHOSTSPOT_')) {
    define('_MODE_JAIMAGESHOSTSPOT_', 1);
    $doc->addScript($basepath.'js/modernizr.custom.63321.js');

    if($lang->isRTL() == 1){
        $doc->addScript($basepath.'js/jquery.dropdown.rtl.js');
    }else{
        $doc->addScript($basepath.'js/jquery.dropdown.js');
    }
    $doc->addStyleSheet($basepath.'css/style.css?v=1');
    if(version_compare(JVERSION, '3.0', 'lt')) {
        $doc->addStyleSheet($basepath.'css/style_nonbs.css');
    }
    if($lang->isRTL() == 1){
        $doc->addStyleSheet($basepath.'css/style.rtl.css');
    }
    //load override css
    $templatepath = 'templates/'.$app->getTemplate().'/css/'.$module->module.'.css';
    if(file_exists(JPATH_SITE . '/' . $templatepath)) {
        $doc->addStyleSheet(Uri::root(true).'/'.$templatepath);
    }

    $doc->addStyleSheet($basepath.'elements/popover/jquery.webui-popover.css');
    $doc->addScript($basepath.'elements/popover/jquery.webui-popover.js');
}

$displaytooltips = $params->get('displaytooltips',1); // still keep number to compatiable with old version.
$multiple = $params->get('displaymultiple', 0);
$mobileLinkIcon = $params->get('mobileLinkIcon', 'window');

// hammer zoom js for mobile
$hammerjs = '';
$activeZFM = $params->get('ActiveZoomForMobile', 0); // default is disabled
// activeZ2CFD => active double click zoom for desktop. depend on zooming mobile function.
$activeZ2CFD = $params->get('Active2ClickZoomForDesktop', 0); // default is disabled
$maxZoom = $params->get('maxZoom', 2);

if ($activeZFM) {
    $doc->addScript($basepath.'js/hammer.min.js');
    $doc->addScript($basepath.'js/jquery.hammer.js');
    // $hammerjs = "addHammerJS ('ja-imagesmap".$module->id."', ".$maxZoom.", ".$activeZ2CFD.");";
}

$trigger = 'hover';
if ($displaytooltips == 1)
	$trigger = 'sticky';
if ($displaytooltips == 2)
	$trigger = 'click';
$hidedelay = (int) $params->get('hidedelay', 2000);

$animation = $params->get('animation', 'pop');
$data = array();
foreach ($description AS $k => $v) {
	$data[$v->imgid] = $v;
}

$menuID = $app->getMenu()->getActive()->id;
$data = json_encode($data);
//escape special characters
$data = preg_replace("/(\\\\(n|r|t)|')/", '\\\\$1', $data);
$data = str_replace('\"', '\\\"', $data);

if (strpos($imgpath, '#joomlaImage://') == true){
    $imgpath = explode('#joomlaImage://', $imgpath)[0];
}else{
    $IMGsize = getimagesize($imgpath);
}

$hotspotConfigs = [
    'jversion' => (int) explode('.', JVERSION)[0],
    'menuId' => $menuID,
    'moduleId' => $module->id,
    'hideDelay' => $hidedelay,
    'trigger' => $trigger,
    'multiple' => $multiple,
    'anim' => $animation,
    'hammerjs_code' => $hammerjs,
    'activeZfm' => $activeZFM,
    'activeZ2CFD' => $activeZ2CFD,
    'maxZoom' => $maxZoom,
    'mobile_link_icon' => $mobileLinkIcon,
];

$hotspotConfigs = json_encode($hotspotConfigs);

$doc->addScriptDeclaration("
    var spotConfigs = $hotspotConfigs;
    var jversion = spotConfigs.jversion;
    var data = '$data';
");
echo "<script src='$basepath" . 'js/frontend-load.js' ."'></script>";
$doc->addScript($basepath.'js/script.js');
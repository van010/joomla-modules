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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;

// load mootools
if (!version_compare(JVERSION, '4', 'ge')){
    HTMLHelper::_('behavior.framework');
}

if (version_compare(JVERSION, '5.0.3', '>')){
    HTMLHelper::_('jquery.framework');
}

$doc = Factory::getDocument();
$imgpath 				= $params->get('imgpath','');
$displayDropdown	 	= $params->get('positionchoiseoffice',1);
$dropdownPosition 		= $params->get('showchoicelocaltion','top-left');
$modules_des 			= $params->get('modules_des','');
$displaytooltips 		= $params->get('displaytooltips',1);
$description 			= $params->get('description', '');

$description = json_decode($description);
if(!is_array($description)) $description = array();

usort($description, function ($a, $b) {
	if(empty($a->title)) {
		$a->title = Text::sprintf('JAI_TITLE_DEFAULT', $a->imgid);
	}
	if(empty($b->title)) {
		$b->title = Text::sprintf('JAI_TITLE_DEFAULT', $b->imgid);
	}
	return $a->title > $b->title ? 1 : -1;
});
include(dirname(__FILE__).'/assets/asset.php');


$css = '';
foreach($description as $i => $desc){
	if(!isset($desc->bgcolor) || !$desc->bgcolor) continue;
	$css .= '
		#ja-imagesmap'.$module->id.' a.point'.$i.' + div.popover {
			background-color: '.$desc->bgcolor.';
		}
		#ja-imagesmap'.$module->id.' a.point'.$i.' + div.popover .popover-title {
			border-bottom: 0px none;
		}
		#ja-imagesmap'.$module->id.' a.point'.$i.' + div.popover .arrow::after {
			border-top-color: '.$desc->bgcolor.';
		}
	';
}
if(!empty($css)) {
	$doc->addStyleDeclaration($css);
}

if($imgpath) {
	$layout = $params->get('layout', 'default');
	$layoutSelect = ModuleHelper::getLayoutPath('mod_jaimagehotspot', $layout.'_select');
	require ModuleHelper::getLayoutPath('mod_jaimagehotspot', $layout);
}
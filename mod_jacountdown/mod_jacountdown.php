<?php
/**
 * $JA#COPYRIGHT$
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
if(!defined('DS')){
	define('DS', DIRECTORY_SEPARATOR);
}

HTMLHelper::_('jquery.framework');

$offset = Factory::getConfig()->get('offset');
$date = Date::getInstance('now', new DateTimeZone($offset));
$now = strtotime((string) $date);

$custom_titles 		= $params->get('custom_titles','');
$custom_message 	= $params->get('custom_message','');
$jalayout 			= $params->get('jalayout','layout1');
$startDate  		= strtotime($params->get('jastartDate'));
$endDate    		= strtotime($params->get('jaendDate'));
$secondsColor    	= "#".$params->get('secondsColor','ffdc50');
$minutesColor    	= "#".$params->get('minutesColor','9cdb7d');
$hoursColor    		= "#".$params->get('hoursColor','378cff');
$daysColor    		= "#".$params->get('daysColor','ff6565');
$secondsGlow 		= "#".$params->get('secondsGlow','ffdc50');

$choisebackground = $params->get('choisebackground','bg_images');
switch($choisebackground){
	case 'bg_images':
		$stylesheets = $params->get('backgroundimages','')?' style="background:url(\''.$params->get('backgroundimages','').'\') no-repeat;"':'';
		break;
	case 'bg_color':
		$stylesheets = $params->get('backgroundcolor','')?' style="background:#'.$params->get('backgroundcolor','').';"':'';
		break;
	default:
		$stylesheets='';
		break;
}

$doc = Factory::getDocument();
$doc->addStyleSheet('modules/'.$module->module.'/tmpl/'.$jalayout.'/css/jacclock.css');
$doc->addScript('modules/'.$module->module.'/tmpl/'.$jalayout.'/js/jacclock.js');

$doc->addScriptDeclaration('
	var jacdsecondsColor = "'.$secondsColor.'";
	var jacdminutesColor = "'.$minutesColor.'";
	var jacdhoursColor = "'.$hoursColor.'";
	var jacddaysColor = "'.$daysColor.'";
	
	var jacdsecondsGlow = "'.$secondsGlow.'";
	
	var jacdstartDate = "'.$startDate.'";
	var jacdendDate = "'.$endDate.'";
	var jacdnow = "'.strtotime('now').'";
	var jacdseconds = "'.$date->second.'";
');

require ModuleHelper::getLayoutPath('mod_jacountdown', $params->get('layout', 'default'));
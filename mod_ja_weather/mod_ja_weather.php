<?php
/**
 * $JA#COPYRIGHT$
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

defined('_JEXEC') or die;

$moduleName = basename(dirname(__FILE__));
$moduleId = $module->id;
$document = Factory::getDocument();
$platform = $params->get('platform', 'opeweathermap');
$layout = $params->get('layout', 'default_layout');

require_once (dirname(__FILE__).'/helper.php');

$helper = new ModJaWeatherHelper($params, $moduleId);
$data = $helper->getData();

if (!empty($data)){
    require(ModuleHelper::getLayoutPath($moduleName, $layout));
}
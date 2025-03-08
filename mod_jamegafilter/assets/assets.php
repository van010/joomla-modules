<?php

/**
 * $JA#COPYRIGHT$
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$doc = Factory::getDocument();
$currentLanguage = Factory::getLanguage();
$isRTL = $currentLanguage->isRtl();

$doc->addStyleSheet('components/com_jamegafilter/assets/css/jquery-ui.min.css');
$doc->addStyleSheet('modules/mod_jamegafilter/assets/css/style.css');

HTMLHelper::_('jquery.framework');
if (!version_compare(JVERSION, '4', 'ge')){
	HTMLHelper::_('jquery.ui');
}
HTMLHelper::_('formbehavior.chosen');

$doc->addScript('components/com_jamegafilter/assets/js/sticky-kit.min.js');
$doc->addScript('components/com_jamegafilter/assets/js/jquery-ui.range.min.js');

if ($isRTL) {
	$doc->addStyleSheet('components/com_jamegafilter/assets/css/jquery.ui.slider-rtl.css');
	$doc->addScript('components/com_jamegafilter/assets/js/jquery.ui.slider-rtl.min.js');
}

$doc->addScript('components/com_jamegafilter/assets/js/jquery.ui.datepicker.js');
$doc->addScript('components/com_jamegafilter/assets/js/jquery.ui.touch-punch.min.js');
$doc->addScript('components/com_jamegafilter/assets/js/libs.js');
$doc->addScript('components/com_jamegafilter/assets/js/megafilter.js');
$doc->addScript('components/com_jamegafilter/assets/js/main.js');
$doc->addScript('components/com_jamegafilter/assets/js/jquery.cookie.js');
$doc->addScript('components/com_jamegafilter/assets/js/script.js');

$lang = Factory::getLanguage();
$lang->load('com_jamegafilter', JPATH_SITE, $lang->getTag());
Text::script('COM_JAMEGAFILTER_TO');
Text::script('COM_JAMEGAFILTER_FROM');



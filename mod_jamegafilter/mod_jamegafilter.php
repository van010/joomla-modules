<?php

/**
 * $JA#COPYRIGHT$
 */
defined('_JEXEC') or die;

JLoader::register('JaMegafilterHelper', JPATH_ADMINISTRATOR . '/components/com_jamegafilter/helper.php');

require_once __DIR__ . '/helper.php';

$helper = new ModJamegafilterHelper();
$helper->display($params);

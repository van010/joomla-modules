<?php
/**
 *------------------------------------------------------------------------------
 * @package       Module JA Content Listing for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2021 https:://JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github
 *                & Google group to become co-author)
 *------------------------------------------------------------------------------
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

JLoader::registerNamespace('JACL', __DIR__.'/helpers', false, false, 'psr4');
JLoader::register('ModJacontentlistingHelper', __DIR__.'/helper.php');

$helper = new ModJacontentlistingHelper($params);
$params = $helper->params;

//get list data
$lassesHelper = JACL\JACL::getInstance($helper->get('sources', 'content', 'jasource'), $params);
$listArticles = $lassesHelper::getList($params);
// add style
$helper->params->set('module_title', $module->title);
$helper->params->set('class_sfx', $helper->get('moduleclass_sfx'));
$helper->params->set('module_id', $module->id);

$helper->renderLayout($listArticles);

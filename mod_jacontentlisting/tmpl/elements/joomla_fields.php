<?php

/**
 *------------------------------------------------------------------------------
 * @package       Module JA Content Listing for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2021 JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github
 *                & Google group to become co-author)
 *------------------------------------------------------------------------------
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

JLoader::register('TagsHelperRoute', JPATH_BASE . '/components/com_tags/helpers/route.php');

$authorised = Factory::getUser()->getAuthorisedViewLevels();

$helper = $displayData['helper'];
$options = $displayData['options'];
$item = $displayData['data'];
$customFields = [];

if ($options->get('show_custom_fields', 0)) {
  foreach ($item->jcfields as $k => $field) {
    $class = $field->name . ' ' . $field->params->get('render_class');
    $contentFields = FieldsHelper::render('com_content.article', 'field.render', ['field' => $field]);

    if (trim($contentFields) === '') continue;
    if (in_array($field->access, $authorised)) {
      $customFields[] = '<li class="list-inline-item ' . $class . '">' . $contentFields . '</li>';
    }
  }
}

if (empty($customFields)) return;
?>
<div class="jacl-item__fields">
  <ul class="jfields">
    <?php echo implode("\n", $customFields); ?>
  </ul>
</div>
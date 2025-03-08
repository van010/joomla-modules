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
use Joomla\CMS\Uri\Uri;

$helper = $displayData['helper'];
$options = $displayData['options'];
$items = $displayData['data'];
$doc = Factory::getDocument();
$doc->addScript(Uri::root(true)."/modules/mod_jacontentlisting/tmpl/layouts/layout-05/isotope.pkgd.min.js");
$doc->addScript(Uri::root(true)."/modules/mod_jacontentlisting/tmpl/layouts/layout-05/imagesloaded.pkgd.min.js");

$modid = $helper->get('module_id');

$item_per_row = intval($options->get('item_per_row', 3));
$item_per_row_w = $item_per_row > 0 ? floor(12/$item_per_row) : 4;
$item_content_gutters = $options->get('item_content_gutters', 'normal-gutters');

$featured_index = $options->get('feature_item', 1);
$featured_size = $options->get('feature_item_size', 1);
$featured_item_enabled = $helper->get('jaitem_featured_enabled') == 1;

$featured_size_w = ($item_per_row_w * $featured_size) > 12 ? 12 : ($item_per_row_w * $featured_size);
?>
<div class="jacl-layout layout-5 <?php echo "mod".$modid;?> <?php echo $helper->get('class_sfx') ?>">
  <?php $helper->render('elements/heading.php', $options, $helper->get('module_title')) ?>
  <?php $helper->renderCategory(); ?>
  <div class="jacl-layout__body">
      <!-- Masonry Layout -->
      <div class="jacl-isotope jacl-row <?php echo $item_content_gutters; ?>">
        <div class="jacl-col jacl-col-size jacl-col-md-<?php echo $item_per_row_w ?>"></div>
        <?php for ($i=0; $i<count($items); $i++) : ?>
          <?php if($featured_index == $i+1 && $featured_item_enabled) :?>
          <!-- Highlight Item -->
            <div class="jacl-col highlight-item jacl-col-md-<?php echo $featured_size_w; ?>">
              <?php $helper->renderItem($items[$i], 'jaitem_featured'); ?>
            </div>
            <!-- // Highlight Item -->
          <?php else : ?>
            <div class="jacl-col jacl-col-md-<?php echo $item_per_row_w ?>">
              <?php $helper->renderItem($items[$i]); ?>
            </div>
          <?php endif ;?>
        <?php endfor ?>
      </div>
  </div>
</div>
<?php
$doc = Factory::getDocument();
$hideDuplicate = (bool) $options->get('hide_duplicate_article');
if ($hideDuplicate){
  $doc->addScript(Uri::root(true)."/modules/mod_jacontentlisting/admin/assets/js/script.js");
}
?>
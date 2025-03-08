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
$doc->addStyleSheet(Uri::root(true)."/modules/mod_jacontentlisting/tmpl/layouts/layout-09/owl.carousel.min.css");
$doc->addStyleSheet(Uri::root(true)."/modules/mod_jacontentlisting/tmpl/layouts/layout-09/owl.theme.default.css");
$doc->addScript(Uri::root(true)."/modules/mod_jacontentlisting/tmpl/layouts/layout-09/owl.carousel.min.js");

$modid = $helper->get('module_id');

// number of items
$item_per_row = intval($options->get('item_per_row', 3));
$item_per_row_md = intval($options->get('item_per_row_md', 0));
if (!$item_per_row_md) $item_per_row_md = $item_per_row;
$item_per_row_lg = intval($options->get('item_per_row_lg', 0));
if (!$item_per_row_lg) $item_per_row_lg = $item_per_row_md;

$featured_index = $options->get('feature_item', 1);
$featured_item_enabled = $helper->get('jaitem_featured_enabled') == 1;
$indicators = $options->get('indicators',0) ? "true" : "false";
$control = $options->get('calrousel_control',0) ? "true" : "false";
$autoplay = $options->get('calrousel_autoplay',0) ? "true" : "false";
$autoplayTimeout = $options->get('calrousel_duration',5000);
$item_content_gutters = $options->get('item_content_gutters', '12');

$module_id = $helper->get('module_id');

if($item_content_gutters == 'no-gutters') {
  $owl_margin = '0';
} elseif ($item_content_gutters == 'small-gutters') {
  $owl_margin = '3';
} elseif ($item_content_gutters == 'normal-gutters') {
  $owl_margin = '32';
} elseif ($item_content_gutters == 'large-gutters') {
  $owl_margin = '36';
} else {
  $owl_margin = '40';
}
?>
<div id="layout-09-carousel" class="jacl-layout layout-9 <?php echo "mod".$modid;?> <?php echo $helper->get('class_sfx') ?>">
	<?php $helper->render('elements/heading.php', $options, $helper->get('module_title')) ?>
  <?php $helper->renderCategory(); ?>
	<!-- The slideshow -->
	<div class="owl-carousel owl-theme <?php echo "jacl-carousel-".$module_id;?>">
		<?php foreach ($items as $i => $item):?>
			<div class="item<?php if($featured_index == $i+1 && $featured_item_enabled) echo ' highlight-item' ;?>" >
				<?php if (!empty($item)) $helper->renderItem($item, $featured_index == $i+1 && $featured_item_enabled ? 'jaitem_featured' : 'jaitem'); ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
<script>

jQuery(document).ready(function($) {
	$('.jacl-carousel-<?php echo $module_id;?>').owlCarousel({
  loop: true,
  margin: 10,
  dots: <?php echo $indicators; ?>,
  nav: <?php echo $control; ?>,
  autoplay: <?php echo $autoplay; ?>,
  autoplayTimeout: <?php echo $autoplayTimeout;?>,
  autoplayHoverPause: true,
  smartSpeed: 800,
  responsive: {
    0: {
      items: 1
    },
    600: {
      items: 2
    },
    768: {
      items: <?php echo $item_per_row_md;?>
    },
    1000: {
      items: <?php echo $item_per_row_lg;?>,
      margin: <?php echo $owl_margin; ?>
    }
  }
})
});
</script>
<?php
$doc = Factory::getDocument();
$hideDuplicate = (bool) $options->get('hide_duplicate_article');
if ($hideDuplicate){
  $doc->addScript(Uri::root(true)."/modules/mod_jacontentlisting/admin/assets/js/script.js");
}
?>
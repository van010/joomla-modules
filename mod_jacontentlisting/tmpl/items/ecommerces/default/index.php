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

$item = $displayData['data'];
$helper = $displayData['helper'];
$options = $displayData['options'];
$params = $helper->params;
$jaSource = $params->get('jasource');
if(!$options) return;
$item_image = json_decode($item->images || '{}');
$class = $options->get('item_cat_style');
$class .= ' ' . $options->get('item_content_align');
$class .= ' ' . $options->get('class', '');
$show_cat = $options->get('show_cat', 1);
$show_image = $options->get('show_intro_image', 1);
if (!empty($item_image->image_intro) && $show_image) $class .= ' has-media';
?>

<div class="jacl-item <?php echo trim($class); ?>">
	<div class="jacl-item__inner">
		<?php if($show_image ): ?>
		<!-- Item media -->
		<?php $helper->render('elements/media.php', $options, $item); ?>
		<!-- // Item media -->
		<?php endif ?>

		<div class="jacl-item__body">

			<?php if($show_cat): ?>
			<!-- Item category -->
      <?php $helper->render('elements/category.php', $options, $item); ?>
			<!-- // Item category -->
			<?php endif ?>

			<!-- Item title -->
      	<?php $helper->render('elements/item_title.php', $options, $item); ?>

			<!-- Item intro -->
        <?php $helper->render('elements/introtext.php', $options, $item); ?>

      <!--Rating-->
      <?php $helper->render('elements/rating.php', $options, $item) ?>

      <!--Item price, instock-->
      <?php $helper->render('elements/instock_price.php', $options, $item) ?>

      <!-- Item Manufacture -->
      <?php $helper->render('elements/info_block/manufacture.php', $options, $item); ?>

      <!--add to cart-->
      <?php $helper->render('elements/addtocart.php', $options, $item) ?>

			<!-- Item meta: admin, date, v.v -->
        <?php $helper->render('elements/meta.php', $options, $item); ?>

			<!-- Item tags -->
        <?php $helper->render('elements/tag.php', $options, $item); ?>

			<!-- Item readmore -->
        <?php /*$helper->render('elements/readmore.php', $options, $item); */?>
		</div>

	</div>
</div>
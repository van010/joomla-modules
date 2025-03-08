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

$item_image = json_decode($item->images || '{}');
$show_cat = $options->get('show_cat', 1);
$cat_pos = $options->get('cat_position', 0);
$item_border = $options->get('item_border');
$media_align = $options->get('item_media_align', 'media-align-left');
$show_image = $options->get('show_intro_image', 1);

$class = '';
if($show_cat) $class .= $options->get('item_cat_style');
if($item_border) $class .= ' has-border';
if(!$show_cat) $class .= ' cat-hide';
if($cat_pos) $class .= ' inside-media';
if(!$show_image) $class .= ' hide-media';
$class .= ' ' . $options->get('item_content_align');
$class .= ' ' . $options->get('item_horizontal_ratio', '');
$class .= ' ' . $options->get('item_media_spacer', 'spacer-normal');
$class .= ' ' . $options->get('class', '');		


if (!empty($item_image->image_intro) && $show_image) $class .= 'has-media';
?>

<div class="jacl-item item-style-3<?php echo ' ' . trim($class); ?><?php echo ' ' . trim($media_align); ?>">
	<div class="jacl-item__inner">
		<?php if($show_image ): ?>      
			<!-- Item media -->
			<div class="jacl-item-wrap__media">
				<?php $helper->render('elements/media.php', $options, $item); ?>
				<?php if(($show_cat) && ($cat_pos)) : ?>
				<!-- Item category -->
	      <?php $helper->render('elements/category.php', $options, $item); ?>
				<!-- // Item category -->
				<?php endif ?>
			</div>
			<!-- // Item media -->
		<?php endif ?>

		<div class="jacl-item__body">
			<?php if( ($show_cat) && ($cat_pos == 0)) : ?>
				<!-- Item category -->
				<?php $helper->render('elements/category.php', $options, $item); ?>
				<!-- // Item category -->
			<?php endif ?>

			<!-- Item title -->
        	<?php $helper->render('elements/item_title.php', $options, $item); ?>
			<!-- // Item title -->

			<!-- Item intro -->
        	<?php $helper->render('elements/introtext.php', $options, $item); ?>
			<!-- // Item intro -->
			
			<!-- Item meta -->
        	<?php $helper->render('elements/meta.php', $options, $item); ?>
			<!-- // Item meta -->

			<!-- Item tags -->
			<?php $helper->render('elements/tag.php', $options, $item); ?>
			<!-- // Item tags -->

			<!-- item joomla fields -->
			<?php $helper->render('elements/joomla_fields.php', $options, $item); ?>
			<!-- end joomla fields -->

			<!-- Item readmore -->
        	<?php $helper->render('elements/readmore.php', $options, $item); ?>
			<!-- // Item readmore -->

		</div>

	</div>
</div>
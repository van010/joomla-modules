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
	$item_image = !empty($item->images) ? json_decode($item->images) : null;
	$class = $options->get('item_cat_style');
	$class .= ' ' . $options->get('item_content_align');
	$class .= ' ' . $options->get('class', '');
	$show_cat = $options->get('show_cat', 1);
	if ($item_image && !empty($item_image->image_intro)) $class .= ' has-media';
?>

<div class="jacl-item item-style-1 <?php echo trim($class); ?>">
	<div class="jacl-item__inner">        
		<!-- Item media -->
		<?php $helper->render('elements/media.php', $options, $item); ?>
		<!-- // Item media -->

		<div class="jacl-item__body">

			<?php if($show_cat): ?>
				<!-- Item category -->
				<?php $helper->render('elements/category.php', $options, $item); ?>
				<!-- // Item category -->
			<?php endif ?>

			<!-- Item title -->
            <?php $helper->render('elements/item_title.php', $options, $item); ?>
			<!-- // Item title -->

			<!-- Item meta -->
        	<?php $helper->render('elements/meta.php', $options, $item); ?>
			<!-- // Item meta -->

			<!-- Item intro -->
			<?php $helper->render('elements/introtext.php', $options, $item); ?>
			<!-- // Item intro -->
			
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
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

$modid = $helper->get('module_id');

$item_per_row = intval($options->get('item_per_row', 3));
$item_per_row_w = $item_per_row > 0 ? floor(12/$item_per_row) : 4;
$item_content_gutters = $options->get('item_content_gutters', 'normal-gutters');
$featured_item_enabled = $helper->get('jaitem_featured_enabled') == 1;

$number_item= intval($options->get('number_item', 3));
?>
<div class="jacl-layout layout-14 <?php echo "mod".$modid;?> <?php echo $helper->get('class_sfx') ?>">
	<?php $helper->render('elements/heading.php', $options, $helper->get('module_title')) ?>
	<?php $helper->renderCategory(); ?>
	<div class="jacl-layout__body">
		<div class="jacl-row <?php echo $item_content_gutters; ?>">
			<div class="jacl-col-12 jacl-col-lg-4 highlight-item">					
				<?php if (!empty($items[0])) $helper->renderItem($items[0], $featured_item_enabled ? 'jaitem_featured' : 'jaitem'); ?>
			</div>

			<?php $col_1 = 1; if(count($items) > 1) : ?>
				<div class="jacl-col-12 jacl-col-lg-4">
					<div class="jacl-row <?php echo $item_content_gutters; ?>">
						<?php for ($i=1; $i<count($items); $i++) : 
							if($col_1 <= $number_item):
							?>
							<div class="jacl-col-12 jacl-col-md-12 jacl-col-lg-12">
								<?php $helper->renderItem($items[$i]); ?>
							</div>
						<?php endif; $col_1++; endfor ?>
					</div>
				</div>
			<?php endif ;?>

			<?php $col_2 = 1; if(count($items) > ($number_item + 1)) : ?>
				<div class="jacl-col-12 jacl-col-lg-4">
					<div class="jacl-row <?php echo $item_content_gutters; ?>">
						<?php for ($i=($number_item + 1); $i<count($items); $i++) : 
							if($col_2 <= $number_item):
							?>
							<div class="jacl-col-12 jacl-col-md-12 jacl-col-lg-12">
								<?php $helper->renderItem($items[$i]); ?>
							</div>
						<?php endif; $col_2++; endfor ?>
					</div>
				</div>
			<?php endif ;?>

		</div>

		<?php 
		$total_num = 1 + ($number_item * 2);

		if(count($items) > $total_num) : ?>
			<div class="jacl-row <?php echo $item_content_gutters; ?> other-items">

			<?php for ($i=$total_num; $i<count($items); $i++) : ?>
				<div class="jacl-col-12 jacl-col-md-4">
					<?php $helper->renderItem($items[$i]); ?>
				</div>
			<?php endfor ?>

			</div>
		<?php endif ;?>
	</div>
</div>
<?php
$doc = Factory::getDocument();
$hideDuplicate = (bool) $options->get('hide_duplicate_article');
if ($hideDuplicate){
  $doc->addScript(Uri::root(true)."/modules/mod_jacontentlisting/admin/assets/js/script.js");
}
?>
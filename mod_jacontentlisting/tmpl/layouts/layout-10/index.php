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
$size_highlight = $options->get('highlight-size', 8);
$size_highlight_child = $size_highlight == 6 ? '6' : '4';
$item_content_gutters = $options->get('item_content_gutters', 'normal-gutters');
$featured_item_enabled = $helper->get('jaitem_featured_enabled') == 1;
?>
<div class="jacl-layout layout-10 <?php echo "mod".$modid;?> <?php echo $helper->get('class_sfx') ?>">
	<?php $helper->render('elements/heading.php', $options, $helper->get('module_title')) ?>
	<?php $helper->renderCategory(); ?>
	<div class="jacl-layout__body">
		<div class="jacl-row <?php echo $item_content_gutters; ?>">
			<?php /* first 3 items */ ?>
			<div class="jacl-col-12 jacl-col-md-7 jacl-col-lg-<?php echo $size_highlight ;?> highlight-item">					
				<?php if (!empty($items[0])) $helper->renderItem($items[0], $featured_item_enabled ? 'jaitem_featured' : 'jaitem'); ?>
			</div>
			<div class="jacl-col-12 jacl-col-md-5 jacl-col-lg-<?php echo $size_highlight_child ;?> col-child">
				<?php if (!empty($items[1])) $helper->renderItem($items[1], $featured_item_enabled ? 'jaitem_featured' : 'jaitem'); ?>
				<?php if (!empty($items[2])) $helper->renderItem($items[2], $featured_item_enabled ? 'jaitem_featured' : 'jaitem'); ?>
			</div>
		</div>
		<?php if(count($items) > 3) : ?>
			<div class="jacl-row <?php echo $item_content_gutters; ?>">
			<?php for ($i=3; $i<count($items); $i++) : ?>
				<div class="jacl-col-12 jacl-col-md-4 jacl-col-lg-<?php echo $item_per_row_w ?>">
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
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

$item_per_row = intval($options->get('item_per_row', 4));
$item_per_row_w = $item_per_row > 0 ? floor(12/$item_per_row) : 4;
$item_content_gutters = $options->get('item_content_gutters', 'normal-gutters');
$featured_item_enabled = $helper->get('jaitem_featured_enabled') == 1;
?>
<div class="jacl-layout layout-3 <?php echo "Mod".$modid;?> <?php echo $helper->get('class_sfx') ?>">
	<?php $helper->render('elements/heading.php', $options, $helper->get('module_title')) ?>

	<?php $helper->renderCategory(); ?>
	
	<div class="jacl-layout__body">
		<div class="jacl-row <?php echo $item_content_gutters; ?>">
			<?php /* first 5 items */ ?>
			<div class="jacl-col-12 jacl-col-lg-6 highlight-item">
				<?php if (!empty($items[0])) $helper->renderItem($items[0], $featured_item_enabled ? 'jaitem_featured' : 'jaitem'); ?>
			</div>

			<div class="jacl-col-12 jacl-col-lg-6">
				<div class="jacl-row sub-items <?php echo $item_content_gutters; ?>">
					<div class="jacl-col-12 jacl-col-md-6"><?php if (!empty($items[1])) $helper->renderItem($items[1]); ?></div>
					<div class="jacl-col-12 jacl-col-md-6"><?php if (!empty($items[2])) $helper->renderItem($items[2]); ?></div>
				</div>
				<div class="jacl-row sub-items <?php echo $item_content_gutters; ?>">
					<div class="jacl-col-12 jacl-col-md-6"><?php if (!empty($items[3])) $helper->renderItem($items[3]); ?></div>
					<div class="jacl-col-12 jacl-col-md-6"><?php if (!empty($items[4])) $helper->renderItem($items[4]); ?></div>
				</div>
			</div>

		</div>

		<?php if(count($items) > 5) :?>
		<div class="jacl-row <?php echo $item_content_gutters; ?> other-items">
		<?php for ($i=5; $i<count($items); $i++) : ?>
			<div class="jacl-col-12 jacl-col-md-6 jacl-col-lg-<?php echo $item_per_row_w; ?>">
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
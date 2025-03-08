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
use Joomla\CMS\Router\Route;


$helper = $displayData['helper'];
$options = $displayData['options'];
$items = $displayData['data'];
$modid = $helper->get('module_id');
$featured_index = $options->get('feature_item', 1);
$col_lg = $options->get('item_per_row_lg') ? $options->get('item_per_row_lg') : $options->get('item_per_row');
$num_col_lg = 'cols-'.$col_lg;
$item_content_gutters = $options->get('item_content_gutters', 'normal-gutters');
$featured_item_enabled = $helper->get('jaitem_featured_enabled') == 1;

$btn_viewmore = $options->get('btn_viewmore','');
?>

<div class="jacl-layout layout-1 <?php echo "mod".$modid;?> <?php echo $helper->get('class_sfx') ?>">
	<?php $helper->render('elements/heading.php', $options, $helper->get('module_title')) ?>
	
	<div class="jacl-layout__body">
		<?php $helper->renderCategory(); ?>
		<div class="jacl-row <?php echo $item_content_gutters.' '.$num_col_lg; ?>">
		<?php foreach ($items as $i => $item) : ?>
			<div class="<?php echo $helper->getColClasses($options); ?> <?php if($featured_index == $i+1 && $featured_item_enabled) echo 'highlight-item' ;?>">
				<?php
					$helper->renderItem($item, $featured_index == $i+1 && $featured_item_enabled ? 'jaitem_featured' : 'jaitem');
				?>
			</div>
		<?php endforeach; ?>
		</div>

		<?php if($btn_viewmore):?>
		<?php 
			$btn_viewmore_menuid = $options->get('btn_viewmore_menuid','');
			$btn_viewmore_text = $options->get('btn_viewmore_label','View more items');
			$menu_link = Route::_("index.php?Itemid={$btn_viewmore_menuid}");	
		?>
		<div class="jacl-actions">
			<a href="<?php echo $menu_link; ?>" alt="<?php echo $btn_viewmore_text; ?>" class="btn btn-dark btn-rounded"><?php echo $btn_viewmore_text; ?></a>
		</div>
		<?php endif; ?>

	</div>
</div>
<?php
$doc = Factory::getDocument();
$hideDuplicate = (bool) $options->get('hide_duplicate_article');
if ($hideDuplicate){
  $doc->addScript(Uri::root(true)."/modules/mod_jacontentlisting/admin/assets/js/script.js");
}
?>
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
defined('JPATH_BASE') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

$helper = $displayData['helper'];
$options = $displayData['options'];
$item = $displayData['data'];
$moduleParams = $helper->params->get('jasource');
$sort_by_hits = $moduleParams->get('sort_by_hits');

?>
<?php if ($helper->get('show_category','jaitem')) : ?>
<div class="jacl-item__cat <?php echo $options->get('item_cat_style'); ?>">
    <?php if ($sort_by_hits): ?>
    <span class="jacl-item__rank"><?php echo Text::sprintf('MOD_JA_CONTENT_LISTING_SORT_HITS', $item->rank)?></span>
    <?php endif; ?>
	<a href="<?php echo $item->displayCategoryLink; ?>" itemprop="genre"><?php echo $item->category_title ?></a>
</div>
<?php endif; ?>
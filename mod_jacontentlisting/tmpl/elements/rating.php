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

use Joomla\CMS\Language\Text;

$maxrating = VmConfig::get('vm_maximum_rating_scale', 5);

$item = $displayData['data'];
$options = $displayData['options'];
$show_rating = $options->get('show_rating');

if ($show_rating == 0) return;

$rating = $item->rating;
$ratingwidth = $item->width_rating;
$ratingCount = $item->ratingCount > 1
  ? $item->ratingCount .' '.Text::_('MOD_JACL_REVIEWS') . 's'
  : $item->ratingCount .' '.Text::_('MOD_JACL_REVIEWS');
?>

<?php  if (empty($rating)) :?>
  <div class="jacl-item__rating">
    <span class="no-rating"><?php echo vmText::_('COM_VIRTUEMART_UNRATED'); ?></span>
  </div>
<?php else:?>
  <div class="jacl-item__rating" title="<?php echo (vmText::_("COM_VIRTUEMART_RATING_TITLE") . round($item->rating) . '/' . $maxrating)?>">
    <div class="rating-box" >
      <div class="stars-orange" style="width:<?php echo $ratingwidth.'px'; ?>"></div>
    </div>
    <span><?php echo $ratingCount;?></span>
  </div>
<?php endif ;?>
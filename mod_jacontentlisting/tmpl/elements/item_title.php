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
$helper = $displayData['helper'];
$options = $displayData['options'];
$item = $displayData['data'];

$tagTitle = $options->get('tag_titles', 'h4');
$linkTitle = $options->get('show_link_title', 1);
if ($options->get('show_title') !== null && $options->get('show_title') == 0){
  return;
}
?>
<<?php echo $tagTitle ?> class="jacl-item__title">
  <?php if($linkTitle): ?>
	  <a href="<?php echo $item->link; ?>" title="<?php echo $item->title; ?>"><?php echo $item->title ?></a>
  <?php else : ?>
    <?php echo $item->title ?>
  <?php endif ?>
</<?php echo $tagTitle ?>>

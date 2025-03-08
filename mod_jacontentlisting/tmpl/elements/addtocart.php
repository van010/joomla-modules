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

$item = $displayData['data'];
$options = $displayData['options'];
if ($options->get('show_addtocart') == 0) return;
?>

<div class="vm-product-addtocart">
  <?php
  $item->prices = (array) $item->prices;
  echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$item,'rowHeights'=>0));
  ?>
</div>

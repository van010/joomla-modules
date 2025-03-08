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

$currency = CurrencyDisplay::getInstance();

$item = $displayData['data'];
$options = $displayData['options'];

$show_instock = (bool) $options->get('show_instock');
$show_price = (bool) $options->get('show_price');
$prices = $item->prices;
$stock = $item->stocks;

switch ($show_price){
  case 'sale_price_with_discount':
    $price = $prices->frontend_salesPriceWithDiscount;
    $flPrice = $prices->salesPriceWithDiscount;
    $class = 'jacl-salesprice-with-discount';
    break;
  case 'base_price':
    $price = $prices->frontend_basePrice;
    $flPrice = $prices->basePrice;
    $class = 'jacl-baseprice';
    break;
  case 'base_price_with_tax':
    $price = $prices->frontend_basePriceWithTax;
    $flPrice = $prices->basePriceWithTax;
    $class = 'jacl-baseprice-with-tax';
    break;
  case 'sale_price':
  default:
    $price = $prices->frontend_price;
    $flPrice = $prices->price;
    $class = 'jacl-price';
    break;
}

if (empty($price) || $flPrice == 0) {
  $price = $prices->frontend_price;
}
?>

<?php if (($show_price && isset($item->prices)) || $show_instock): ?>
  <div class="jacl-item__price <?php echo $class ?>">
    <?php if ($show_price && isset($item->prices)): ?>      
      <span class="price">
        <?php echo $price ?>
      </span>
    <?php endif; ?>

    <?php if ($show_instock): ?>
      <span class="instock">
        <?php echo $stock->frontend_stock?>
      </span>
    <?php endif; ?>
  </div>
<?php endif; ?>

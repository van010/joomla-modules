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
namespace JACL\Adapter;

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;


defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.filesystem.file');

$configPath = JPATH_ROOT . '/administrator/components/com_virtuemart/helpers/config.php';

if (!class_exists('VmConfig') && !File::exists($configPath)){
	return;
}

require_once $configPath;

\VmConfig::loadConfig();

class VmHelper{
	public static $db;
  public static $badges;

	public function __construct()
	{
		self::$db = Factory::getDbo();
		self::$badges = ['hot', 'sale', 'new'];
	}

	public static function writeData($file, $data, $writeNew=false){
		$path = JPATH_ROOT . '/cache/mod_jacontentlisting/';
		if ($writeNew){
			File::write($path . $file, json_encode($data));
			return;
		}
		if (!is_file($path . $file)){
			file_put_contents($path . $file, json_encode($data).PHP_EOL, FILE_APPEND | LOCK_EX);
		}
	}

	public static function getList($params){
		$params_ = $params->get('jasource');
		$catId = $params_->get('vmcatid', [], 'array');
		$catId = array_unique($catId);
		$productGroup = $params_->get('product_group', 'featured');
		$itemDisplay = $params->get('count') == 0
			? self::countVmProducts() : $params->get('count');

		$productModel = \VmModel::getModel('Product');
		$rateModel = \VmModel::getModel('Ratings');

		if (is_array($catId)){
			$products = [];
			$productIds = [];
			foreach ($catId as $cat){
				$items = $productModel->getProductsListing($productGroup, $itemDisplay, true, true, false, true, $cat);
				if (count($items)){
					foreach ($items as $item){
            $productIds = array_unique($productIds);
            if (!in_array($item->virtuemart_product_id, $productIds)){
              $productIds[] = $item->virtuemart_product_id;
              $products[] = $item;
            }
					}
				}
			}
			$productModel->addImages($products);
		}

		if(empty($products)){
			return [];
		}

    $cloneItems = [];
		foreach ($products as $k => $item){
      $cloneItems[$k] = new \stdClass();
			$item->id = $item->virtuemart_product_id;
      $cloneItems[$k]->id = $item->virtuemart_product_id;
			$cloneItems[$k]->title = $item->product_name;
      $cloneItems[$k]->introtext = $item->product_s_desc;
      $cloneItems[$k]->category_title = $item->category_name;
      $cloneItems[$k]->catid = $item->virtuemart_category_id;
      $cloneItems[$k]->displayCategoryLink = Uri::getInstance()->toString(array('scheme', 'host', 'port'))
      . Route::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $item->virtuemart_category_id);

			$cloneItems[$k]->slug = $item->slug;
			$cloneItems[$k]->virtuemart_product_id = $item->id;
			$cloneItems[$k]->product_name = $item->product_name;

      $cloneItems[$k]->created = $item->created_on;
      $cloneItems[$k]->modified = $item->modified_on;
      $cloneItems[$k]->publish_up = $item->product_available_date;

			$rating = $rateModel->getRatingByProduct($item->id);
      $cloneItems[$k]->rating = (!empty($rating) ? $rating->rating : 0);
      $cloneItems[$k]->width_rating = $cloneItems[$k]->rating * 12;
      $cloneItems[$k]->ratingCount = isset($rating->ratingcount) ? $rating->ratingcount : 0;

      $cloneItems[$k]->stocks = self::getStock($item->product_in_stock);
      $badges = self::getBadge($item->id);
      $cloneItems[$k]->badges = $badges;

			$price = self::getPrice($item);
      $cloneItems[$k]->prices = $price;

      $cloneItems[$k]->link = Route::_($item->link);
      $cloneItems[$k]->mf_info = self::getManufacture($item->virtuemart_manufacturer_id);

      $cloneItems[$k]->params = [];
      $cloneItems[$k]->addToCartButton = $item->addToCartButton;
      $cloneItems[$k]->orderable = $item->orderable;
      $cloneItems[$k]->min_order_level = $item->min_order_level;
      $cloneItems[$k]->show_notify = $item->show_notify;
      $cloneItems[$k]->orderable = $item->orderable;

      $cloneItems[$k]->images = empty($item->images) ? self::getImages() : $item->images;
			// check config use images
			$image_params = $params->get('jaitem')->get('item_media_path','intro');
			switch ($image_params){
				case "first_img":
				case "intro":
				case "full":
					$imgsConfig = new \stdClass();
					$imgsConfig->image_intro = $item->images[0]->file_url;
					$imgsConfig->image_fulltext = $imgsConfig->image_intro;
					$imgsConfig->float_intro = '';
					$imgsConfig->image_intro_alt = '';
					$imgsConfig->image_intro_caption = '';
					$imgsConfig->float_fulltext = '';
					$imgsConfig->image_fulltext_alt = '';
					$imgsConfig->image_fulltext_caption = '';
          $cloneItems[$k]->images = json_encode($imgsConfig);
					break;
				default:
					break;
			}
		}
    return array_slice($cloneItems, 0, $itemDisplay);
	}

	public static function getCurrentLang(){
		$lang = Factory::getLanguage();
		$currLangTag = $lang->getTag();
    $currLangCode = str_replace('-', '_', $currLangTag);
		return $currLangCode;
	}

	public static function getPrice($vmProduct){
		if (empty($vmProduct)) return [];

		require_once VMPATH_ADMIN . '/helpers/currencydisplay.php';
		$currency = \CurrencyDisplay::getInstance();
		$price = new \stdClass();

		$price->price = $vmProduct->prices['salesPrice'];
		$price->costPrice = $vmProduct->prices['costPrice'];
		$price->basePrice = $vmProduct->prices['basePrice'];
		$price->basePriceWithTax = $vmProduct->prices['basePriceWithTax'];
		$price->salesPriceWithDiscount = $vmProduct->prices['salesPriceWithDiscount'];

		$priceDisplay = $currency->priceDisplay($price->price);
		$basePriceDisplay = $currency->priceDisplay($price->basePrice);
		$basePriceWithTax = (float) $price->basePriceWithTax >= (float) $price->price
			? $currency->priceDisplay($price->basePriceWithTax) : "";
		$salesPriceWithDiscount = $currency->priceDisplay($price->salesPriceWithDiscount);
		$symbol = $currency->getSymbol();

    /*
     * frontend_price: sales price
     * frontend_basePrice: base price
     * frontend_basePriceWithTax: base price with tax
	   * frontend_salesPriceWithDiscount: sales Price With Discount
     * */
		$price->frontend_price = str_replace($symbol, '<span class="currency">'.$symbol.'</span>', $priceDisplay);
		$price->frontend_basePrice = str_replace($symbol, '<span class="currency">'.$symbol.'</span>', $basePriceDisplay);
		$price->frontend_basePriceWithTax = str_replace($symbol, '<span class="currency">'.$symbol.'</span>', $basePriceWithTax);
		$price->frontend_salesPriceWithDiscount = str_replace($symbol, '<span class="currency">'.$symbol.'</span>', $salesPriceWithDiscount);

		return $price;
	}

	public static function getStock($stockQuan){
		$stock = new \stdClass();
		$stock->instock = $stockQuan;
		$stock->frontend_stock = $stockQuan == 0
			? '<span class="out-of-stock">Out of Stock</span>'
			: '<span>Instock:</span><span class="in-stock">'. $stockQuan .'</span>';
		return $stock;
	}

	// handle images for some templates can not get images from Com-virtuemart
	public static function getImages(){
		$images = new \stdClass();

		return $images;
	}

	public static function countVmProducts(){
		$query = "SELECT COUNT(DISTINCT `virtuemart_product_id`) AS countId";
		$query .= " FROM #__virtuemart_products";
		$query .= " WHERE published = 1";
		self::$db->setQuery($query);
		return self::$db->loadResult();
	}

  /**
   *
   * method get badges of vm products
   *
   * @param integer $productId
   *
   * @return array
   */
	public static function getBadge($productId){
		$query = 'SELECT `customfield_value`';
    $query .= ' FROM #__virtuemart_product_customfields';
    $query .= ' WHERE virtuemart_product_id = ' . (int) $productId;
    self::$db->setQuery($query);
    $badges = self::$db->loadObjectList();

    if (empty($badges)) return [];

    $listBadges = [];
    foreach ($badges as $k => $badge){
      if (in_array(strtolower($badge->customfield_value), self::$badges)){
        $listBadges[$k] = new \stdClass();
        $listBadges[$k]->badge = $badge->customfield_value;
        $listBadges[$k]->badgeClass = 'badge-' . strtolower($badge->customfield_value);
      }
    }
    return $listBadges;
	}

  /**
   *
   * method get products manufactures
   *
   * @param array $id
   *
   * @return array
   *
   */
  public static function getManufacture($ids){
    $mfModel = \VmModel::getModel('manufacturer');
    if (is_array($ids)) {
      $mfInfo = [];
      foreach ($ids as $k => $id) {
        $result = $mfModel->getManufacturer($id);
        $mfInfo[$k] = new \stdClass();
        $mfInfo[$k]->mf_name = $result->mf_name;
        // $mfInfo[$k]->mf_desc = $result->mf_desc;
        $mfInfo[$k]->mf_link = Uri::getInstance()->toString(array('scheme', 'host', 'port'))
          . Route::_('index.php?option=com_virtuemart&view=category&virtuemart_manufacturer_id=' . $id);
      }
      return $mfInfo;
    }
    return [];
  }
}
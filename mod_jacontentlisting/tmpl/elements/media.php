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

use Joomla\CMS\Uri\Uri;

$helper = $displayData['helper'];
$modid = $helper->get('module_id');
$adapter = $helper->get('sources','content','jasource');

if ($adapter === 'easyblog'){
	$all_params    = $this->params->get('jasource');
	$other_img_src = $all_params->get('other_img_src');
}

$options = $displayData['options'];
$item = $displayData['data'];

// if empty module id, menu blog layout in use
// override handle image in adapter > content.php, line 236 -> 250
if (empty($modid)){
	$imgParams = $options->get('item_media_path', 'intro');

	switch ($imgParams) {
		case "full":
			$imagesConfig = json_decode($item->images);
			$imagesConfig->image_intro = $imagesConfig->image_fulltext;
			$item->images = json_encode($imagesConfig);
			break;
		case "first_img":
			$item->images = \ModJacontentlistingHelper::getFirstImageArticle($item);
			break;
		default:
			break;
	}
}
// end override

$show_image = true;
$item_image = !empty($item->images) ? json_decode($item->images) : null;
if($options->get('ignore_image',false)){
	$show_image = false;
}

if (!$show_image) return ;
if (empty($item_image) || empty($item_image->image_intro)) return ;

$show_badge = (bool) $options->get('show_badge');
if (isset($item->badges)){
	$badges = $item->badges;
}

// handle image intro for image in joomla source
$img_intro = $item_image->image_intro;
if (!preg_match("/http/i",$item_image->image_intro,$matches)) {

    $imgIntro = $item_image->image_intro;
    if (strpos($imgIntro, '#joomlaImage://local-images') !== false) {
        $arr_img_intro = explode('#joomlaImage://local-images', $imgIntro);
        $parse_img_url = parse_url($arr_img_intro[1], PHP_URL_QUERY);
        $imgIntro = $arr_img_intro[0] . '?' . $parse_img_url;
        /*parse_str($parse_img_url, $params);
        $imgIntro = $arr_img_intro[0] . '?width=' . $params['width'] . '&height=' . $params['height'];*/
    }

    $img_intro = Uri::root() . $imgIntro;
    if ($adapter === 'easyblog' && isset($item_image->img_src)
        && $item_image->img_src === 'amazon') {
        $img_intro = $other_img_src . $item_image->image_intro;
    }
}
?>

<div class="jacl-item__media <?php echo $options->get('item_media_style'); ?> <?php echo $options->get('item_media_ratio'); ?>">
    <a href="<?php echo $item->link; ?>" title="<?php echo $item->title; ?>"><img src="<?php echo $img_intro; ?>" alt="<?php echo $item->title; ?>"></a>
    <?php if (!empty($badges) && $show_badge): ?>
        <div class="jacl-item__badge">
            <?php foreach ($badges as $badge) :?>
                <span class="badge <?php echo $badge->badgeClass ?>">
        <?php echo $badge->badge?>
      </span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <span class="item-media-mask"></span>
</div>
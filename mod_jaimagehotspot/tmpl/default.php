<?php
/**
 * ------------------------------------------------------------------------
 * JA Image Hotspot Module for Joomla 2.5 & 3.4
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

$pre_class_font_awesome = ' fa fa-';
if (version_compare(JVERSION, '5.0', '>=')){
	$pre_class_font_awesome = ' fas fa-';
}

Text::script('JAI_MOBILE_POPUP_LINK');
?>

<div class="jai-map-wrap<?php echo $params->get( 'moduleclass_sfx' );?>" id="ja-imagesmap<?php echo $module->id;?>">
	<?php if($modules_des):?>
	<div class="jai-map-description">
	<?php echo $modules_des;?>
	</div>
	<?php endif;?>
	
	<?php if(in_array($dropdownPosition, array('top-left', 'top-right', 'middle-left', 'middle-right'))): ?>
		<?php require $layoutSelect; ?>
	<?php endif; ?>
	<div class="jai-map-container <?php echo $displaytooltips ? 'always-popup' : 'hover-popup'; ?>">
	    <div class="jai-map-container-scale">
		<?php
		foreach($description as $i => $des):
			$iconsize = isset($des->iconsize) ? +$des->iconsize : 30;
			$fontsize = $iconsize / 5 * 4;
	        $offsetx = isset($des->offsetx) ? +$des->offsetx : 10;
	        $offsety = isset($des->offsety) ? +$des->offsety : 10;
	        $iconanchorx = isset($des->iconanchorx) ? +$des->iconanchorx : 0;
	        $iconanchory = isset($des->iconanchory) ? +$des->iconanchory : 0;
	        $anchorx = isset($des->iconanchorx) ? $des->iconanchorx : 0;
	        $anchory = isset($des->iconanchory) ? $des->iconanchory : 0;
	        $color = isset($des->iconcolor) ? $des->iconcolor : '#ffffff';
	        
	        $bgStyle = "";
	        $bgStyle .= "height: {$iconsize}px;";
	        $bgStyle .= "width: {$iconsize}px;";
	        $bgStyle .= "font-size: {$fontsize}px;";
	        $bgStyle .= "line-height: {$iconsize}px;";
	        $bgStyle .= "transform: translate({$iconanchorx}%, {$iconanchory}%)";

			$bgimg='';
			$bgcolor = ($des->bgcolor === 'dark') ? 'dark':'light';
			$classpoint = isset($des->classpoint) ? $des->classpoint:'';
			if (!empty($des->ptype) && $des->ptype == 'image') {
				$classpoint .= ' point-img ';
				$bgimg = 'background-image:url(\''.$des->ptype_image.'\');';
			}
			if (!empty($des->ptype) && $des->ptype == 'jaset') {
				$classpoint .= ' point-ico ';
				$bgimg = 'background-image:url(\''.$des->jasetimage.'\');';
			}
			if ($des->ptype != 'icon' && $des->ptype != 'image' && $des->ptype != 'jaset') {
				$classpoint .= $pre_class_font_awesome . 'map-marker';
			} elseif (!empty($des->ptype) && $des->ptype == 'icon') {
				if (!empty($des->icon)){
					$classpoint .= $pre_class_font_awesome.$des->icon;
				} else {
					$classpoint .= $pre_class_font_awesome.'map-marker';
				}
			}
		?>
			<a style="text-align:center;background-size:cover;color:<?php echo (!empty($des->iconcolor) ? $des->iconcolor : '#000'); ?>;top:<?php echo $des->offsety; ?>%;left:<?php echo $des->offsetx; ?>%"
			   class="point <?php echo 'point'.$i; ?>"
			   href="javascript:void(0)"
			   id="<?php echo 'ja-marker-'.$des->imgid; ?>"
			   data-bgcolor="<?php echo $bgcolor; ?>"
			   data-content_url="<?php echo $des->content_url; ?>"
			   data-link="<?php echo $des->link; ?>"
			   data-vwidth="<?php echo $des->vwidth; ?>"
			   data-vheight="<?php echo $des->vheight; ?>"
			   title="">
			   	<span class="bg <?php echo $classpoint ?>" style="<?php echo $bgimg . $bgStyle ?>"></span>
				<span class="hidden">Point</span>
			</a>
        <?php endforeach; ?>
        <img id="ja-hotspot-image-<?php echo $module->id;?>" src="<?php echo $imgpath;?>" alt="<?php echo htmlspecialchars($module->title) ?>"/>
        </div>
    </div>

	<?php if(in_array($dropdownPosition, array('bottom-left', 'bottom-right'))): ?>
		<?php require $layoutSelect; ?>
    <?php endif; ?>
	
</div>
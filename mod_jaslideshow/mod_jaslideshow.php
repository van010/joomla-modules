<?php
/**
 * $JA#COPYRIGHT$
 */
defined('_JEXEC') or die('Restricted access');

//INCLUDING ASSET
require_once(dirname(__FILE__).'/assets/behavior.php');
JHTML::_('behavior.framework', true);

jimport('joomla.filesystem.file');

require_once (dirname(__FILE__) . '/helpers/smarttrim.php');
require_once (dirname(__FILE__) . '/helpers/helper.php');
require_once (dirname(__FILE__) . '/helpers/jaimage.php');

$t3_exists = false;
if (!class_exists("T3Common")) {
	if (JFile::exists(JPATH_SITE . '/plugins/system/jat3/jat3/core/common.php')) {
		require_once (JPATH_SITE . '/plugins/system/jat3/jat3/core/common.php');
		$t3_exists = true;    
	}
} else {
	$t3_exists = true;
}

$helper = ModJASlideshow3::getInstance();
$tmpParams = $helper->loadConfig($params);
$mainframe = JFactory::getApplication();

$folder = $params->get('folder', '');
$startItem = $tmpParams->get('source-images-startItem', 0);

if ((int) $startItem > 0) {
	$startItem = (int) $startItem - 1;
}

$skin = $tmpParams->get('skin', '');
$skin_name = "";
if (!empty($skin)) {
	$skin_name = "_" . $skin;
}
/*if (empty($skin_name)) {
	$skin_name = $params->get("moduleclass_sfx", "");
}*/

//do not use include_once in this case, 
//since this module can use many skin (each skin load individual css file) for each instance
include(dirname(__FILE__).'/assets/asset.php');

//Main image
$mainWidth = $tmpParams->get('mainWidth', 360);
$mainHeight = $tmpParams->get('mainHeight', 240);

//Set value for params
$params->set("mainWidth", $mainWidth);
$params->set("mainHeight", $mainHeight);
//End set


//Navigation
$navigation = $tmpParams->get('navigation', '');
$orderby = $tmpParams->get('orderby', '');
$sort = $tmpParams->get('source-images-sort', '');

//Thumbnail info
$showItem = $tmpParams->get('showItem', 0);
$thumbWidth = $tmpParams->get('nav_thumb-width', 60);
$thumbHeight = $tmpParams->get('nav_thumb-height', 60);
$navWidth = $tmpParams->get('source-articles-navwidth', 60);
$navHeight = $tmpParams->get('source-articles-navheight', 60);

//Set value for params
$params->set("thumbWidth", $thumbWidth);
$params->set("thumbHeight", $thumbHeight);
$params->set("source-articles-navwidth", $navWidth);
$params->set("source-articles-navheight", $navHeight);
///end

$thumbSpace = $tmpParams->get('itemSpace', '3:3');
$thumbSpaces = preg_split('/:/', $thumbSpace);
$thumbSpaces[0] = isset($thumbSpaces[0]) ? intval($thumbSpaces[0]) : 3;
$thumbSpaces[1] = isset($thumbSpaces[1]) ? intval($thumbSpaces[1]) : 3;

$thumbOpacity = $tmpParams->get('itemOpacity', '0.8');

//Animation
$duration = $tmpParams->get('duration', 400);
$autoplay = $tmpParams->get('autoplay', 0);
$interval = $tmpParams->get('interval', 5000);
$effect = $tmpParams->get('effect', 'Fx.Transitions.Quad.easeInOut');
$animation = $tmpParams->get('animation', 'move');
$fbanimation = $tmpParams->get('fallback_anim', 'fade');
$moveDirection = $tmpParams->get('move_direction', 'horizontal');
$numberSlices = $tmpParams->get('number_slices', 8);
$numberBoxCols = $tmpParams->get('number_box_cols', 8);
$numberBoxRows = $tmpParams->get('number_box_rows', 4);
$animationRepeat = $tmpParams->get('animationRepeat', "yes");
$animationRepeat = ($animationRepeat == "yes") ? "true" : "false";

if (!strpos($effect, "Transitions")) {
	$effect = 'Fx.Transitions.' . $effect;
}
//Description
$showDescription = $tmpParams->get('showdesc', '');
$showdescwhen = $tmpParams->get('showdescwhen', 'always');
$showProgressBar = $tmpParams->get('showprogressbar', 0);
$readmoretext = $tmpParams->get('readmoretext', 'Readmore');
$show_readmore = $tmpParams->get('show_readmore', '1');
$descOpacity = $tmpParams->get('descOpacity', 0.8);
if ($show_readmore == '0') {
	$readmoretext = "";
}

//Overlapping items
$container = $tmpParams->get('container', 0);
$overlapOpacity = $tmpParams->get('overlapOpacity', 0.4);

//Control buttons
$control = $tmpParams->get('control', 0);

$navDescmaxlength = $tmpParams->get('source-articles-nav_descmaxlength', '80');

$navShowdesc = $tmpParams->get('source-articles-nav_showdesc', '80');
$navShowDate = $tmpParams->get('source-articles-nav_showdate', '0');
$maskWidth = $tmpParams->get('masker-width', 'auto');
$maskWidth = $maskWidth == 'auto' || $maskWidth == '100%' ? $mainWidth : $maskWidth;
$maskHeigth = $tmpParams->get('masker-height', 'auto');
$maskHeigth = $maskHeigth == 'auto' || $maskHeigth == '100%' ? $mainHeight : $maskHeigth;
$maskAlignment = $tmpParams->get('masker-alignment', 'bottom');
$edgemargin = $tmpParams->get('edge-margin', 0);
$includeTags = $tmpParams->get('includeTags', '');

$preloadImgUrl = 'modules/' . $module->module . '/assets/themes/' . $skin . '/preloader.gif';

$titleMaxChars = (int) $tmpParams->get('title_max_chars', 60);
$descMaxChars = (int) $tmpParams->get('maxchars', 60);
$showPreNext = $tmpParams->get('show_btnprenext', '0');
$navShowthumb = $tmpParams->get('nav_showthumb', '0');
$navAlignment = $tmpParams->get('navigation-alignment', 'horizontal');
$classNav = ' ja-' . $navAlignment;

// get instance
$source = $params->get('source', 'images');

// call and execute
$list = $helper->callMethod("getList" . ucfirst($source), $params);

//Check limitted items
if (isset($list['mainImageArray'])) {
	$total_items = count($list['mainImageArray']);
} else {
	$total_items = count($list);
}
if ((int) $showItem > $total_items) {
	$showItem = $total_items;
}
if ($total_items && (int) $startItem >= $total_items) {
	$startItem = (int) $total_items - 1;
}
//Fix error show limitted items
$folder = null;

$target = 'target="_' . $params->get('open_target', 'parent') . '"';

$app =  JFactory::getApplication();
$template_name = $app->getTemplate();
$jdoc = JFactory::getDocument();
$direction = "";
if ($t3_exists) {
	$t = T3Common::isRTL();
	$direction = T3Common::isRTL() ? "rtl" : "ltr";
} else {
	$direction = $jdoc->getDirection();
}

if( $source  == 'images' && !empty($list) ){
	$images		   = $list['mainImageArray'];
	$titles 	   = isset($list['title']) ? $list['title'] : array();
	$captionsArray = $list['captionsArray'];
	$urls		   = $list['urls'];
	$targets 	   = $list['targets'];
	$thumbArray	   = $list['thumbArray'];
	require( JModuleHelper::getLayoutPath( $module->module, 'default' ) );
	unset($list);
?>
<script type="text/javascript">
	var Ja_direction = '';
	var cookie_path = '/';
	var cur_template_name = '<?php echo $template_name; ?>';
	var Ja_maskAlign_<?php echo $module->id; ?> = '<?php echo $maskAlignment; ?>';
	
	window.jasliderInst = window.jasliderInst || [];
	
	window.addEvent('domready', function(){
		if(typeof(tmpl_name) =='undefined'){
		  cookie_path = "<?php echo $template_name."_direction"; ?>";
		}
		else{
		  cookie_path = tmpl_name+"_direction";
		}
		
		Ja_direction = Cookie.read(cookie_path);
		if( Ja_direction == '' || Ja_direction == null){
			Ja_direction = '<?php echo $direction; ?>';
		}
		
		var style_l_value = 'auto';
		if(cur_template_name == 'ja_norite'){
			style_l_value = '0';
		}

		if(Ja_direction == 'rtl'){
			setStyleLinkWithRTLDirection();
			$('ja-slide-<?php echo $module->id;?>').getElement(".ja-slide-main").setStyle('left',style_l_value);
			$('ja-slide-<?php echo $module->id;?>').getElement(".ja-slide-main").setStyle('right','auto');
			if(Ja_maskAlign_<?php echo $module->id; ?> == 'right')
			{
				Ja_maskAlign_<?php echo $module->id; ?> = 'left';
			}
			else if(Ja_maskAlign_<?php echo $module->id; ?> == 'left')
			{
				Ja_maskAlign_<?php echo $module->id; ?> = 'right';
			}

		}
		
		window.jasliderInst.push(new JASlider('ja-slide-<?php echo $module->id;?>', {
			slices: <?php echo $numberSlices; ?>,
			boxCols: <?php echo $numberBoxCols; ?>,
			boxRows: <?php echo $numberBoxRows; ?>,
			
			animation: '<?php echo $animation; ?>',
			fbanim: '<?php echo $fbanimation; ?>',
			direction: '<?php echo $moveDirection; ?>',
			
			interval: <?php echo $interval; ?>,
			duration: <?php echo $duration; ?>,
			transition: <?php echo $effect; ?>,
			
			repeat: '<?php echo $animationRepeat; ?>',
			autoPlay: <?php echo $autoplay; ?>,
			
			mainWidth: <?php echo $mainWidth; ?>,
			mainHeight: <?php echo $mainHeight; ?>,
			
			rtl:( typeof Ja_direction == 'string') ? Ja_direction : '',
			
			startItem: <?php echo $startItem; ?>,
			
			thumbItems: <?php echo $showItem; ?>,
			thumbType: '<?php echo $navigation; ?>',
			thumbWidth: <?php echo $navWidth; ?>,
			thumbHeight: <?php echo $navHeight; ?>,
			thumbSpaces: [<?php echo implode(',', $thumbSpaces); ?>],
			thumbOpacity: <?php echo $thumbOpacity; ?>,
			thumbTrigger: 'click',
			thumbOrientation: '<?php echo $navAlignment; ?>',
			
			
			maskStyle: <?php echo $container; ?>,
			maskWidth: <?php echo  $maskWidth; ?>,
			maskHeigth:<?php echo  $maskHeigth; ?>,
			maskOpacity: <?php echo $descOpacity; ?>,
			maskAlign: Ja_maskAlign_<?php echo $module->id; ?>,
			maskTransitionStyle: '<?php echo $tmpParams->get('masker-transition-style', 'opacity'); ?>',
			maskTransition: <?php echo $tmpParams->get('marker-transition', 'Fx.Transitions.linear'); ?>,
			
			showDesc: '<?php echo $showDescription; ?>',
			descTrigger: '<?php echo $showdescwhen; ?>',
			
			showControl: <?php echo $control; ?>,
			edgemargin:<?php echo $edgemargin; ?>,
			showNavBtn: true,
			navBtnOpacity: <?php echo $overlapOpacity; ?>,
			navBtnTrigger: 'click',
			
			showProgress: <?php echo $showProgressBar; ?>,
			
			urls:['<?php echo implode('\',\'', $urls); ?>'],
			targets:['<?php echo implode('\',\'', $targets); ?>']
		}));
	});
</script>

<?php
} else {
    if($params->get('showvideos',0) == 1){
        require( JModuleHelper::getLayoutPath( $module->module, 'default_videos' ) );
    }else{
        require( JModuleHelper::getLayoutPath( $module->module, 'default_articles' ) );
    }

	$urls = array();
	$targets = array();
	foreach($list as $lis){
	  $urls[] =  $lis->link;
	  $targets[] = $target;
	}
	 unset($list);
?>

<script type="text/javascript">
	var Ja_direction = '';
	var cookie_path = '/';
	var cur_template_name = '<?php echo $template_name; ?>';
	window.jasliderInst = window.jasliderInst || [];
	
	window.addEvent('domready', function(){
		var Ja_maskAlign_<?php echo $module->id; ?>= '<?php echo $maskAlignment; ?>';
		if(typeof(tmpl_name) =='undefined'){
			cookie_path = "<?php echo $template_name."_direction"; ?>";
		} else{
			cookie_path = tmpl_name+"_direction";
		}
		
		Ja_direction = Cookie.read(cookie_path);
		if( Ja_direction == '' || Ja_direction == null){
			Ja_direction = '<?php echo $direction; ?>';
		}
		var style_l_value = 'auto';
		if(cur_template_name == 'ja_norite'){
			style_l_value = '0';
		}
		if(Ja_direction == 'rtl'){
			setStyleLinkWithRTLDirection();
			$('ja-slide-articles-<?php echo $module->id;?>').getElement(".ja-slide-main").setStyle('left',style_l_value);
			$('ja-slide-articles-<?php echo $module->id;?>').getElement(".ja-slide-main").setStyle('right','auto');
			if(Ja_maskAlign_<?php echo $module->id; ?> == 'right'){
				Ja_maskAlign_<?php echo $module->id; ?> = 'left';
			}
			else if(Ja_maskAlign_<?php echo $module->id; ?> == 'left'){
				Ja_maskAlign_<?php echo $module->id; ?> = 'right';
			}
		}

		window.jasliderInst.push(new JASlider('ja-slide-articles-<?php echo $module->id;?>', {
			slices: <?php echo $numberSlices; ?>,
			boxCols: <?php echo $numberBoxCols; ?>,
			boxRows: <?php echo $numberBoxRows; ?>,
			
			animation: '<?php echo $animation; ?>',
			fbanim: '<?php echo $fbanimation; ?>',
			direction: '<?php echo $moveDirection; ?>',
			
			interval: <?php echo $interval; ?>,
			duration: <?php echo $duration; ?>,
			transition: <?php echo $effect; ?>,
			
			repeat: '<?php echo $animationRepeat; ?>',
			autoPlay: <?php echo $autoplay; ?>,
			
			mainWidth: <?php echo $mainWidth; ?>,
			mainHeight: <?php echo $mainHeight; ?>,
			
			rtl:( typeof Ja_direction == 'string') ? Ja_direction : '',
			
			startItem: <?php echo $startItem; ?>,
			
			thumbItems: <?php echo $showItem; ?>,
			thumbType: '<?php echo $navigation; ?>',
			thumbWidth: <?php echo $navWidth; ?>,
			thumbHeight: <?php echo $navHeight; ?>,
			thumbSpaces: [<?php echo implode(',', $thumbSpaces); ?>],
			thumbOpacity: <?php echo $thumbOpacity; ?>,
			thumbTrigger: 'click',
			thumbOrientation: '<?php echo $navAlignment; ?>',
			
			
			maskStyle: <?php echo $container; ?>,
			maskWidth: <?php echo  $maskWidth; ?>,
			maskHeigth:<?php echo  $maskHeigth; ?>,
			maskOpacity: <?php echo $descOpacity; ?>,
			maskAlign: Ja_maskAlign_<?php echo $module->id; ?>,
			maskTransitionStyle: '<?php echo $tmpParams->get('masker-transition-style', 'opacity'); ?>',
			maskTransition: <?php echo $tmpParams->get('marker-transition', 'Fx.Transitions.linear'); ?>,
			
			showDesc: '<?php echo $showDescription; ?>',
			descTrigger: '<?php echo $showdescwhen; ?>',
			edgemargin:<?php echo $edgemargin; ?>,
			showControl: <?php echo $control; ?>,
			
			showNavBtn: true,
			navBtnOpacity: <?php echo $overlapOpacity; ?>,
			navBtnTrigger: 'click',
			
			showProgress: <?php echo $showProgressBar; ?>,
			
			urls:['<?php echo implode('\',\'', $urls); ?>'],
			targets:['<?php echo implode('\',\'', $targets); ?>']
		}));
	});
</script>
<?php } ?>
<script type="text/javascript">
	function setStyleLinkWithRTLDirection()
	{
		var links = document.getElementsByTagName ('link');
		<?php
			$filename = "mod_jaslideshow_rtl.css";
			$tplpath = '/templates/' . $mainframe->getTemplate () . '/css/';
			$tplurl = '/templates/' . $mainframe->getTemplate () . '/css/';
			$modurl = 'modules/'.$module->module.'/assets/themes/default/';
			$cssurl = $tplurl;
			if (! file_exists ( JPATH_SITE . $tplpath . $filename )) {
				$cssurl = $modurl;
			}
			$cssurl = JURI::base () . $cssurl;
		?>
		
		var script = document.createElement('link');
		script.setAttribute('type', 'text/css');
		script.setAttribute('rel', 'stylesheet');
		script.setAttribute('href', '<?php echo $cssurl . $filename;?>');
		document.getElementsByTagName("head")[0].appendChild(script);
	}
</script>
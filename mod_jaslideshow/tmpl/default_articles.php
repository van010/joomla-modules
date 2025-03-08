<?php
/**
 * $JA#COPYRIGHT$
 */
defined('_JEXEC') or die('Restricted access');
?>
<div class="ja-slidewrap<?php echo $skin_name;?><?php echo $params->get( 'moduleclass_sfx' );?> ja-<?php echo $source;?>" id="ja-slide-articles-<?php echo $module->id;?>" style="visibility:hidden">
  <div class="ja-slide-main-wrap<?php echo ($navAlignment == 'vertical_left' ? ' ja-slide-right' : '' )?>">
   <div class="ja-slide-mask">
  </div>
    <div class="ja-slide-main">
      <?php foreach( $list as $k=> $item ) :
	 	// parse image in the article's content.
	   	// $helper->parseImages( $item, $params );
		// $list[$k] = $item;
	 ?>
      <div class="ja-slide-item">
      <?php echo $helper->renderImage ($item->title, $item->mainImage, $params, $mainWidth, $mainHeight );?>
      </div>
      <?php endforeach; ?>
    </div>

	<?php if ( $animation=='move' && $container ) :?>
		<div class="but_prev ja-slide-prev"></div>
		<div class="but_next ja-slide-next"></div>
	<?php endif; ?>
	<div class="ja-slide-progress"></div>
	<div class="ja-slide-loader"></div>

 <!-- JA SLIDESHOW 3 MARK -->
  <div class="maskDesc">
  	<div class="inner">

  	</div>
    </div>
  </div>
  <!-- END JA SLIDESHOW 3 MARK -->
  <?php if( $showDescription ) : ?>
  <div class="ja-slide-descs">
     <?php foreach( $list as $item ) : ?>
  		 <div class="ja-slide-desc">
			 <a <?php echo $target; ?>  href="<?php echo   $item->link; ?>">
			 	<span><?php echo  $helper->trimString( $item->title, $titleMaxChars );?></span>
             </a>
              <?php echo $helper->trimString( $item->introtext, $descMaxChars, $includeTags ); ?>
			  	  <?php if ($showDescription=='desc' && $readmoretext!=''): ?>
				<a <?php echo $target; ?> class="readon readmore" href="<?php echo   $item->link; ?>">
					<?php echo $readmoretext;?>
				</a><?php endif; ?>
          </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- JA SLIDESHOW 3 NAVIAGATION -->
  <?php if ( $navigation == "thumbs"){ ?>
  <div class="ja-slide-thumbs-wrap<?php echo $classNav;?>">
    <div class="ja-slide-thumbs">
     <?php foreach( $list as $key => $item ) : ?>
        <div class="ja-slide-thumb">
        	<div  class="ja-slide-thumb-inner">
			<?php if( $navShowthumb	== 1 ) : ?>
        	 <?php if (file_exists(JPATH_SITE.'/'.$item->thumbnail)) {?>
        	 <?php echo $helper->renderImage ( $item->title, $item->thumbnail, $params,
											  $thumbWidth, $thumbHeight, 'align="left"' ); ?>
			<?php } ?>
            <?php endif; ?>
             <h3><?php echo  $helper->trimString( $item->title, $titleMaxChars );?></h3>
             <?php if( $navShowDate ) :  ?>
             <span class="ja-createdate clearfix">
				<?php echo JTEXT::_("POSTED_DATE"). "&nbsp;	". JHTML::_('date', $item->date, JText::_('DATE_FORMAT_LC4')); ?>
             </span>
             <?php endif; ?>
             <?php if( $navShowdesc ):  ?>
             <?php echo $helper->trimString( strip_tags($item->introtext), $navDescmaxlength ); ?>
             <?php endif; ?>
        	</div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="ja-slide-thumbs-mask" style=" display:none ">
    	<div class="ja-slide-thumbs-mask-left">&nbsp;</div>
        <div class="ja-slide-thumbs-mask-center">&nbsp;</div>
        <div class="ja-slide-thumbs-mask-right">&nbsp;</div>
    </div>

    <p class="ja-slide-thumbs-handles">
     <?php foreach( $list as $item ) :  ?>
        <span>&nbsp;</span>
      <?php endforeach; ?>
    </p>
 	<!-- JA SLIDESHOW 3 NAVIAGATION -->

  </div>
  <?php }
  elseif ($navigation == "number")
  {
    ?>
	<div class="ja-slide-thumbs-wrap<?php echo $classNav;?>">
    <div class="ja-slide-thumbs">
     <?php foreach( $list as $key => $item ) : ?>
        <div class="ja-slide-thumb">
      	 <span><?php echo ($key+1);?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="ja-slide-thumbs-mask" style=" display:none ">
    	<div class="ja-slide-thumbs-mask-left">&nbsp;</div>
        <div class="ja-slide-thumbs-mask-center">&nbsp;</div>
        <div class="ja-slide-thumbs-mask-right">&nbsp;</div>
    </div>

    <p class="ja-slide-thumbs-handles">
     <?php foreach( $list as $item ) :  ?>
        <span>&nbsp;</span>
      <?php endforeach; ?>
    </p>
 	<!-- JA SLIDESHOW 3 NAVIAGATION -->

  </div>
	<?php
  }
  else
  {
     ?>
	<p class="ja-slide-thumbs-handles">
     <?php foreach( $list as $item ) :  ?>
        <span>&nbsp;</span>
      <?php endforeach; ?>
    </p>
	 <?php
  }
  ?>
  <!-- JA SLIDESHOW 3 BUTTONS CONTROL -->
  <?php if ($control): ?>
  <div class="ja-slide-buttons clearfix">
    <span class="ja-slide-prev">&laquo; <?php echo JText::_('PREVIOUS');?></span>
    <span class="ja-slide-playback">&lsaquo; <?php echo JText::_('PLAYBACK');?></span>
    <span class="ja-slide-stop"><?php echo JText::_('STOP');?></span>
    <span class="ja-slide-play"><?php echo JText::_('PLAY');?> &rsaquo;</span>
    <span class="ja-slide-next"><?php echo JText::_('NEXT');?>  &raquo;</span>
  </div>
  <?php endif; ?>

</div>

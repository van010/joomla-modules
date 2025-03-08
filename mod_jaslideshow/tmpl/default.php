<?php
/**
 * $JA#COPYRIGHT$
 */
defined('_JEXEC') or die('Restricted access');
?>
<div class="ja-slidewrap<?php echo $skin_name;?><?php echo $params->get( 'moduleclass_sfx' );?>" id="ja-slide-<?php echo $module->id;?>" style="visibility:hidden">
  <div class="ja-slide-main-wrap ja-slide-right">
    <div class="ja-slide-main">
      <?php for ($i=0;$i<count($images); $i++) {?>
      <div class="ja-slide-item"><img src="<?php echo $folder.$images[$i];?>"  alt="<?php echo str_replace('"', '"/', strip_tags($captionsArray[$i]) );?>"/>
      </div>
      <?php }?>
    </div>
  <?php if ( $animation=='move' && $container ) :?>
    <div class="but_prev ja-slide-prev"></div>
    <div class="but_next ja-slide-next"></div>
  <?php endif; ?>
  <div class="ja-slide-progress"></div>
  <div class="ja-slide-loader"></div>
  <div class="maskDesc"></div>
  </div>

  <?php if($showDescription){?>
  <div class="ja-slide-descs">
    <?php for ($i=0;$i<count($captionsArray); $i++) {?>
      <div class="ja-slide-desc">
	  <?php echo $helper->trimString( $captionsArray[$i], $descMaxChars, $includeTags );?>
	  <?php if ($readmoretext!=''){?>
	  <div class="inner">
	  <a class="readon" title="" href="<?php echo $urls[$i]; ?>"><span><?php echo $readmoretext;?></span></a>
	  </div>
	  <?php }?>
	  </div>
    <?php }?>
  </div>
  <?php }?>
  <?php if ($navigation == "thumbs"){ ?>
  <div class="ja-slide-mask">
  </div>
  <div class="ja-slide-thumbs-wrap<?php echo $classNav;?> ja-slide-thumbnail ">
    <div class="ja-slide-thumbs">
      <?php for ($i=0;$i<count($images); $i++) {?>
        <div class="ja-slide-thumb">
        <?php if ($navShowthumb == 1){
		?>
		<div class="ja-slide-thumb-inner">
		<img src="<?php echo $thumbArray[$i]?>" alt="Photo Thumb" />
			<div class="ja-slide-thumb-desc">
				<h3><?php echo $titles[$i];?></h3>
				<?php if($showDescription){?>
					  <?php echo $helper->trimString( $captionsArray[$i], $descMaxChars, $includeTags );?>
				  <?php }?>
			</div>	  
		</div>
        <?php
		}?>
        </div>
      <?php }?>
    </div>

    <div class="ja-slide-thumbs-mask">
		<span class="ja-slide-thumbs-mask-left">&nbsp;</span>
		<span class="ja-slide-thumbs-mask-center">&nbsp;</span>
		<span class="ja-slide-thumbs-mask-right">&nbsp;</span>
	</div>

    <p class="ja-slide-thumbs-handles">
      <?php for ($i=0;$i<count($images); $i++) {?>
        <span>&nbsp;</span>
      <?php }?>
    </p>
  </div>
  <?php }
   elseif($navigation == "number")
   {
     ?>
	 <div class="ja-slide-mask">
  </div>
  <div class="ja-slide-thumbs-wrap<?php echo $classNav;?>">
    <div class="ja-slide-thumbs">
      <?php for ($i=0;$i<count($images); $i++) {?>
        <div class="ja-slide-thumb">
         <span><?php echo ($i+1);?></span>
        </div>
      <?php }?>
    </div>

    <div class="ja-slide-thumbs-mask"><span class="ja-slide-thumbs-mask-left">&nbsp;</span><span class="ja-slide-thumbs-mask-center">&nbsp;</span><span class="ja-slide-thumbs-mask-right">&nbsp;</span></div>

    <p class="ja-slide-thumbs-handles">
      <?php for ($i=0;$i<count($images); $i++) {?>
        <span>&nbsp;</span>
      <?php }?>
    </p>
  </div>
  <?php
  }
  ?>

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


<?php
/**
 * $JA#COPYRIGHT$
 */
// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 

$cls_sufix = trim($params->get('blog_theme',''));		
if($cls_sufix) $cls_sufix = '-'.$cls_sufix;


$showcreator			= 	$helper->get( 'showcreator', 0 );
$showdate 				= 	$helper->get( 'showdate', 0 );
$maxchars 				= 	intval (trim( $helper->get( 'maxchars', 200 ) ));
$showreadmore 			= 	intval (trim( $helper->get( 'showreadmore', 1 ) ));
$showsubcattitle 		= 	trim( $helper->get( 'showsubcattitle' ));
$enabletimestamp		=	$helper->get( 'timestamp', 0 );
$showtooltip			= 	intval (trim( $helper->get( 'showtooltip', 1 ) ));
$showhits				= 	intval (trim( $helper->get( 'showhits', 1 ) ));
?>
<div class="ja-box <?php echo $cls_sufix;?><?php if (isset($y) && $y==0) echo ' ja-box-first' ?>">
	<div class="ja-box-inner clearfix">
		<?php if ($groupbysubcat && $showsubcattitle && count($rows)) : ?>					
			<div class="ja-zincat clearfix">
				<h3>
					<a href="<?php echo $cat->link?>" title="<?php echo trim(strip_tags($cat->description));?>">
						<span><?php echo $cat->title;?></span>
					</a>
				</h3>
			</div>
		<?php endif; ?>
		
		<?php if($rows){?>
			<?php $numrow = 0;?>
			<?php foreach ($rows as $row){?>									
				<div class="ja-zinpulse row<?php echo $numrow?> clearfix">
					<?php if($showhits):?>
					<div class="ja-zinpulse-cell ja-zinpulse-bar">
						<div class="ja-zinpulse-cell-inner clearfix" title="<?php echo $row->hits?> <?php echo JText::_('hits')?>">
							<span style="width: <?php echo $helper->statistic($row->hits)?>%">&nbsp;</span>
						</div>
					</div>
					<?php endif;?>
					
					<div class="ja-zinpulse-cell ja-zinpulse-news">
						<div class="ja-zinpulse-cell-inner clearfix">
							<?php if($showimage) echo $row->image; ?>
							<?php 
								$introtext = "";					
								if(strlen(strip_tags($row->introtext))>300){
									$intro_process = strip_tags($row->introtext);
									$intro_process = substr($intro_process, 0, 300);
									$last_space = strrpos($intro_process, ' ');
									$introtext = substr($intro_process, 0, $last_space).'...';
								}else{
									$introtext = $row->introtext;
								}					
							?>							
							<h4 <?php if($showtooltip){?>class="ja-zintitle editlinktip jahasTip" title="<?php echo trim(strip_tags($row->title), '"'); ?>::<?php echo htmlspecialchars($row->image.$introtext)?>"<?php }?>>
								<a href="<?php echo $row->link; ?>">
									<?php echo $row->title; ?>
								</a>
							</h4>
							
							<?php if ($showcreator) : ?>
								<span class="createdby"><?php echo $row->author;?></span>						
							<?php endif; ?>
							
							<?php if ($showreadmore) : ?>
							<p class="readmore">
							<a href="<?php echo $row->link; ?>" title="<?php echo JTEXT::_('JANEWSPRO_READ_MORE');?>">
								<span><?php echo JTEXT::_('JANEWSPRO_READ_MORE');?></span>
							</a>
							</p>
							<?php endif; ?>										
						</div>				
					</div>
					
					<div class="ja-zinpulse-cell ja-zinpulse-cat">
						<div class="ja-zinpulse-cell-inner clearfix">							
							<?php if ($groupbysubcat) : ?>					
								<a href="<?php echo $cat->link?>" title="<?php echo trim(strip_tags($cat->description));?>">
									<span><?php echo $cat->title;?></span>
								</a>
							
							<?php endif; ?>
						</div>
					</div>
					
					<?php if ( $showcreator || $showdate ) : ?>
					<div class="ja-zinpulse-cell ja-zinpulse-meta">
						<div class="ja-zinpulse-cell-inner clearfix">
							<?php if ($showdate) : ?>
								<span class="create">
									<?php echo $row->created?> 
								</span>
							<?php endif; ?>
						</div>
					</div>
					<?php endif; ?>
				</div>
				<?php $numrow = 1- $numrow;?>
			<?php }?>
		<?php }?>
		
	</div>
</div>

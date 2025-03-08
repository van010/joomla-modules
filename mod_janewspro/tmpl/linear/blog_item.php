<?php
/**
 * $JA#COPYRIGHT$
 */
// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 

$cls_sufix = trim($params->get('blog_theme',''));		
if($cls_sufix) $cls_sufix = '-'.$cls_sufix;
$class = '';
if(JRequest::getInt('subcat', 0)) $class = 'subcontents-'.JRequest::getInt('subcat');

$showcreator			= 	$helper->get( 'showcreator', 0 );
$showdate 				= 	$helper->get( 'showdate', 0 );
$maxchars 				= 	intval (trim( $helper->get( 'maxchars', 200 ) ));
$showreadmore 			= 	intval (trim( $helper->get( 'showreadmore', 1 ) ));
$showsubcattitle 		= 	trim( $helper->get( 'showsubcattitle', 1));

$params_new = new JRegistry('');
$catid = $secid;
$cooki_name = 'mod'.$moduleid.'_'.$catid;
/*if(isset($_COOKIE[$cooki_name]) && $_COOKIE[$cooki_name]!=''){
	$cookie_user_setting = $_COOKIE[$cooki_name];
	$arr_values = explode('&', $cookie_user_setting);
	if($arr_values){
		foreach ($arr_values as $row){
			list($k, $value) = explode('=', $row);
			if($k!=''){
				$params_new->set($k, $value);
			}
		}
	}
}*/
				
$introitems 	= 	intval (trim( $params_new->get( 'introitems', $helper->get( 'introitems', 1 ) )));
$linkitems 		= 	intval (trim( $params_new->get( 'linkitems', $helper->get( 'linkitems', 0 ) ) ));
$showimage		= 	intval (trim( $params_new->get( 'showimage', $helper->get( 'showimage', 1 ) ) ));	
$showtooltip	= 	intval (trim( $helper->get( 'showtooltip', 1 ) ));
?>
<?php 
if (!defined ('_MODE_JANEWPRO_ASSETS_SLIDE_CATS')) {
	define ('_MODE_JANEWPRO_ASSETS_SLIDE_CATS', 1);
	JHTML::script('modules/'.$module->module.'/tmpl/'.$theme.'/'.'script.js');
}
?>
<div class="ja-box column ja-zintheme<?php echo $cls_sufix;?><?php if (isset($y) && $y==0) echo ' ja-box-first' ?> <?php echo $class?>" style="clear: both;">
	<div class="ja-box-inner clearfix">
		<?php
		foreach ($rows as $i=>$row){	
			if($i>=$introitems) break;												
			?>
			
			<div class="ja-zincontent clearfix">
			
				<?php if($showimage) echo $row->image; ?>
				
				<h4 class="ja-zintitle">
					<a href="<?php echo $row->link;?>" title="<?php echo strip_tags($row->title);?>"><?php echo $row->title;?></a>
				</h4>
				
				<?php if ( $showcreator || $showdate ) : ?>
				<p class="ja-zinmeta">
					<?php if ($showdate) : ?>
						<span class="createdate">
							<?php echo $row->created?> 
							<?php if ($showcreator) : ?> &nbsp;|&nbsp; <?php endif; ?> 
						</span>
					<?php endif; ?>
					<?php if ($showcreator) : ?>
						<span class="createby"><?php echo $row->author;?></span>						
					<?php endif; ?>
				</p>
				<?php endif; ?>

				
				<?php 
				     if($maxchars > strlen($row->introtext1)) {
				      echo $row->introtext;
				     } else {
				      echo $row->introtext1;
				     }
				?>
				<?php if ($showreadmore) : ?>
				<p class="readmore">
				<a href="<?php echo $row->link; ?>" title="<?php echo JTEXT::_('JANEWSPRO_READ_MORE');?>">
					<span><?php echo JTEXT::_('JANEWSPRO_READ_MORE');?></span>
				</a>
				</p>
				<?php endif; ?>
			</div>			
			<?php unset($rows[$i])?>
		<?php }?>
			
		<?php if($rows){?>
		<div class="ja-zinlinks clearfix">
			<strong><?php echo JTEXT::_('MORE')?></strong>			
			<ul class="jazin-links">
				<?php foreach ($rows as $row){?>	
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
					<li>
						<span <?php if($showtooltip){?>class="editlinktip jahasTip" title="<?php echo trim(strip_tags($row->title), '"'); ?>::<?php echo htmlspecialchars($row->image.$introtext)?>"<?php }?>>
				  			<a href="<?php echo $row->link; ?>">
				  				<?php echo $row->title; ?>
				  			</a>
			  			</span>
		  			</li>
				<?php }?>
			</ul>
		</div>
		<?php }?>
		
	</div>
</div>

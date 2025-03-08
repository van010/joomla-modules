<?php
/**
 * $JA#COPYRIGHT$
 */
// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 

$db  = JFactory::getDBO();
$moduleid = $module->id;
$showcattitle 			= 	trim( $helper->get( 'showcattitle' ));
$showusersetting		=	intval (trim( $helper->get( 'showusersetting', 0 ) ));
$groupbysubcat			=	intval (trim( $helper->get( 'groupbysubcat', 0 ) ));
/* Cols by Parent group */
$cols 	= intval (trim( $helper->get( 'cols', 1 ) ));
if(!$cols) $cols = 1;
$width = 99.9/$cols;

$t = 0;
$count_sections = count($helper->_section);
?>

<div class="ja-zinwrap <?php echo $theme?>" id="ja-zinwrap-<?php echo $module->id?>">
	<div class="ja-zin clearfix">
	
	<?php if($count_sections){?>
		<?php 
		for ($z = 0; $z < $cols; $z ++) {
			$k = $z;
			?>
			
			<div class="items-row clearfix">							
			<?php 
			$j = 1;
			
			foreach ($helper->_section as $secid=>$section){?>		
				<?php 
				
				$params_new = new JRegistry('');
				$catid = $secid;
				
				$cooki_name = 'mod'.$moduleid.'_'.$catid;
				if(isset($_COOKIE[$cooki_name]) && $_COOKIE[$cooki_name]!=''){
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
				}												
				$showimage		= 	intval (trim( $params_new->get( 'showimage', $helper->get( 'showimage', 1 ) ) ));
				if(!intval($secid) && in_array(substr($secid, 0, 3), array('sec', 'cat'))){
					$catid = substr($secid, 3);
				}		
				?>					
			
				<div class="ja-zinsec clearfix <?php echo isset($helper->_themes[$catid])?$helper->_themes[$catid]:''?>">
					
					<?php if($showcattitle){?>
			    	<h2>
						<a href="<?php echo $helper->cat_link[$secid];?>" title="<?php echo trim(strip_tags($helper->cat_desc[$secid]));?>">
							<span><?php echo $helper->cat_title[$secid];?></span>
						</a>
					</h2>
					<?php }?>
						
					<?php if(!$groupbysubcat){?>
							<?php
					    		$rows = $helper->articles[$secid];
					    		$path = JModuleHelper::getLayoutPath( 'mod_janewspro', $helper->get('themes', 'default').DS.'blog_item' );
								if (file_exists($path)) {
				                    require($path);
				                }
			                ?>
		                
					<?php }else{?> <!-- Group by sub category -->
					
			    		<?php foreach ( $helper->_categories[$secid] as $cat):?>
			    			<?php 
			    			if(!isset($helper->articles[$secid][$cat->id])) $helper->articles[$secid][$cat->id] = array();
							$rows = $helper->articles[$secid][$cat->id];
							$path = JModuleHelper::getLayoutPath( 'mod_janewspro', $helper->get('themes', 'default').DS.'blog_item' );
							if (file_exists($path)) {
								require($path);
							}
			    			?>
			    		<?php endforeach;?>
			    		
			    		
			    	<?php }?>
			    </div>
				
				
				<?php 
				$k += $cols;
				$t++;
				$j++;
				unset($helper->_section[$secid]);
				if($j >= ($count_sections / $cols) && $k>=$count_sections){
					break;
				}
				?>
			<?php }?>
			</div>
			
		<?php }?>
		
	<?php }?>
	</div>
</div>

<script type="text/javascript">	
	window.addEvent('load', function(){
		document.getElements('.jahasTip').each(function(el) {
		var title = el.get('title');
		if (title) {
		 var parts = title.split('::', 2);
		 el.store('tip:title', parts[0]);
		 el.store('tip:text', parts[1]);
		}
	   });
	   var JTooltips = new Tips(document.getElements('.jahasTip'), { maxTitleChars: 50, fixed: false});
		//new Tips($('ja-zinwrap-<?php echo $module->id?>').getElements('.jahasTip'), { maxTitleChars: 50, fixed: false, className: 'tool-tip janews-tool'});
	})
</script>
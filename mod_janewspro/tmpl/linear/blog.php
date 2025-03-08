<?php
/**
 * $JA#COPYRIGHT$
 */
// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 

$db  = JFactory::getDBO();
$showcattitle 			= 	trim( $helper->get( 'showcattitle', 1 ));
$moduleid = $module->id;

/* Cols by Parent group */
$cols 	= intval (trim( $helper->get( 'cols', 1 ) ));
if(!$cols) $cols = 1;
$width = 99.9/$cols;

/* Cols by sub categorogy */
$subcols 	= intval (trim( $helper->get( 'subcols', 1 ) ));
if(!$subcols) $subcols = 1;
$subwidth = 99.9/$subcols;
$t = 0;
$count_sections = count($helper->_section);

$doc = JFactory::getDocument();
$direction = 'left';
if($doc->direction=='rtl') $direction = 'right';


?>
<?php 
if (!defined ('_MODE_JANEWPRO_ASSETS_SLIDE_CATS')) {
	define ('_MODE_JANEWPRO_ASSETS_SLIDE_CATS', 1);
	JHTML::script('modules/'.$module->module.'/tmpl/'.$theme.'/'.'script.js');
}
?>
<div class="ja-zinwrap <?php echo $theme?>" id="ja-zinwrap-<?php echo $module->id?>">
	<div class="ja-zin clearfix">	   
	<?php if($count_sections){?>
		<?php 
		$col = 0;
		for ($z = 0; $z < $cols; $z ++) {
			$cls = $cols==1?'full':($z==0?'left':($z==$cols-1?'right':'center'));
			$k = $z;
			$pos = $z==0?'col-first':($z == $cols-1?'col-last':'');
			$col++;
			?>
			
			<div class="items-row">							
			<?php 
			$j = 1;
			
			foreach ($helper->_section as $secid=>$section){?>						
				<?php 
				$catid = $secid;
				$showcreator			= 	$helper->get( 'showcreator', 0 );
				$showdate 				= 	$helper->get( 'showdate', 0 );
				$maxchars 				= 	intval (trim( $helper->get( 'maxchars', 200 ) ));
				$showreadmore 			= 	intval (trim( $helper->get( 'showreadmore', 1 ) ));
				$showsubcattitle 		= 	trim( $helper->get( 'showsubcattitle', 1));
				
				$params_new = new JRegistry('');				
								
				$introitems 	= 	intval (trim( $params_new->get( 'introitems', $helper->get( 'introitems', 1 ) )));
				$linkitems 		= 	intval (trim( $params_new->get( 'linkitems', $helper->get( 'linkitems', 0 ) ) ));
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
					
					<!-- Subcategories List-->
					<?php $categories = $helper->_categories_org[$secid];?>
					<?php if($categories){?>												
					
						<div id="ja-cats-slide-mainwrap-<?php echo $moduleid?>-<?php echo $secid?>" class="ja-cats-slide-mainwrap clearfix">
							
							<!-- Control Buttons -->
							<div class="ja-newspro-control" style="display: none">
								<a href="javascript:void(0)" class="ja-newspro-control-prev prev">
									<span><?php echo JText::_('Prev')?></span>
								</a> 
								<a href="javascript:void(0)" class="ja-newspro-control-next next">
									<span><?php echo JText::_('Next')?></span>					
								</a>
							</div>
							
							<div class="subcats-selection-wrap">
								
								<ul class="subcats-selection">
									<li class="active">
										<a href="<?php echo $helper->cat_link[$secid]?>" onclick="return false" rel="<?php echo $secid?>" class="subcat-title" title="<?php echo trim(strip_tags($helper->cat_desc[$secid]));?>">
											<span><?php echo JText::_('All')?></span>
										</a>
										<a  href="<?php echo $helper->cat_link[$secid]?>" class="subcat-more"><span><?php echo JText::_('More')?></span></a>
									</li>
									<?php foreach( $categories as $cat ): ?>
									<li>
										<a href="<?php echo $cat->link?>" onclick="return false" rel="<?php echo $cat->id?>" class="subcat-title" title="<?php echo trim(strip_tags($cat->description));?>">
											<span><?php echo $cat->title?></span>
										</a>
										<a href="<?php echo $cat->link?>" class="subcat-more"><span><?php echo JText::_('More')?></span></a>
									</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>															
					<?php }?>
					<!-- End -->
							
							
					<!-- Articles List-->
					<div id="ja-articles-mainwrap-<?php echo $moduleid?>-<?php echo $secid?>" class="ja-articles-mainwrap">
						<div class="ja-articles active clearfix" id="ja-articles-<?php echo $moduleid?>-<?php echo $secid?>">	
							<?php
					    		$rows = $helper->articles[$secid];
					    		$path = JModuleHelper::getLayoutPath( 'mod_janewspro', $helper->get('themes', 'default').DS.'blog_item' );
								if (file_exists($path)) {
				                    require($path);
				                }
			                	?>
		                </div>
		                <img class="ja-newspro-loading" src="modules/mod_janewspro/tmpl/linear/images/loading.gif" alt="Loading..."/>
	                </div>
	                <!-- End -->
	      			          
	                
	                <script type="text/javascript">
						window.addEvent('load', function(){
							new JANEWSPRO_LINEAR({'moduleid': <?php echo $moduleid?>, 'secid': '<?php echo $secid?>', 'direction': '<?php echo $direction?>', 'duration':<?php echo (int)$helper->get('duration', 400)?>});		
						});
						
					</script>
					
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
	var janewspro = null;
	var jasiteurl = '<?php echo JURI::root()?>';
	document.getElements('.jahasTip').each(function(el) {
		var title = el.get('title');
		if (title) {
		 var parts = title.split('::', 2);
		 el.store('tip:title', parts[0]);
		 el.store('tip:text', parts[1]);
		}
	});
	window.addEvent('load', function(){
		 var JTooltips = new Tips(document.getElements('.jahasTip'), { maxTitleChars: 50, fixed: false});
		//new Tips($('ja-zinwrap-<?php echo $module->id?>').getElements('.jahasTip'), { maxTitleChars: 50, fixed: false, className: 'tool-tip janews-tool'});		
	})
</script>
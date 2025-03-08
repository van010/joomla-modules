<?php
/**
 * ------------------------------------------------------------------------
 * JA Google Chart 2 Module for J25 & J34
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */
 
defined('_JEXEC') or die('Restricted access');
?>
<div id="<?php echo $container ?>" class="ja-google-chart<?php echo $params->get( 'moduleclass_sfx' );?>" style="height:<?php echo $height ?>px;"></div>
<?php if(!empty($chart_description)): ?>
	<div class="ja-google-chart-intro"><?php echo $chart_description; ?></div>
<?php endif; ?>
<script>
	 jQuery(document).ready(function($){
		setTimeout(function(){
			$('#<?php echo $container; ?>').children().children().css('width','');
		}, 1000);
	 });
</script>
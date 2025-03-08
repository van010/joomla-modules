<?php
/**
 * $JA#COPYRIGHT$
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>

<div id="clock-<?php echo $module->id ?>" class="clock">

	<div class="clock_days">
		<canvas height="190px" width="190px" class="canvas_days"></canvas>
		<div class="text">
			<p class="val">0</p>
			<p class="type_days"><?php echo Text::_('JACD_DAYS')?></p>
		</div>
	</div>
	<div class="clock_hours">
		<canvas height="190px" width="190px" class="canvas_hours"></canvas>
		<div class="text">
			<p class="val">0</p>
			<p class="type_hours"><?php echo Text::_('JACD_HOURS')?></p>
		</div>
	</div>
	<div class="clock_minutes">
		<canvas height="190px" width="190px" class="canvas_minutes"></canvas>
		<div class="text">
			<p class="val">0</p>
			<p class="type_minutes"><?php echo Text::_('JACD_MINUTES')?></p>
		</div>
	</div>
	<div class="clock_seconds">
		<canvas height="190px" width="190px" class="canvas_seconds"></canvas>
		<div class="text">
			<p class="val">0</p>
			<p class="type_seconds"><?php echo Text::_('JACD_SECONDS')?></p>
		</div>
	</div>
	
</div><!--/clock -->
<script>
jQuery(document).ready(function($) {
	var el = '#clock-<?php echo $module->id ?>';

	JBCountDown_3(el, {
		secondsColor : '<?php echo $secondsColor ?>',
		minutesColor : '<?php echo $minutesColor ?>',
		hoursColor   : '<?php echo $hoursColor ?>',
		daysColor    : '<?php echo $daysColor ?>',

		startDate   : <?php echo $startDate ?>,
		endDate     : <?php echo $endDate ?>,
		now         : <?php echo $now ?>,
	});
});
</script>
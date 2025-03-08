<?php
/**
 * $JA#COPYRIGHT$
 */

// no direct access
defined('_JEXEC') or die;
?>
<div id="clock-<?php echo $module->id ?>" class="clock">
	<canvas class="canvas_seconds" width="267px" height="267px"> </canvas>
	<p class="val">0</p>
</div>

<script>
jQuery(document).ready(function($) {
	var el = '#clock-<?php echo $module->id ?>';

	JBCountDown_4(el, {
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
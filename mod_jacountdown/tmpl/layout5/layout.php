<?php
/**
 * $JA#COPYRIGHT$
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<div id="clock-<?php echo $module->id ?>" class="clock">
	<!-- Days -->
	<div class="clock_days">
		<div class="bgLayer">
			<canvas class="canvas_days" width="122" height="122">
				Your browser does not support the HTML5 canvas tag.
			</canvas>
			<p class="val">0</p>
			<p class="type_days"><?php echo Text::_('JACD_DAYS')?></p>
		</div>
	</div>
	<!-- Days -->
	<!-- Hours -->
	<div class="clock_hours">
		<div class="bgLayer">
			<canvas class="canvas_hours" width="122" height="122">
				Your browser does not support the HTML5 canvas tag.
			</canvas>

			<p class="val">0</p>
			<p class="type_hours"><?php echo Text::_('JACD_HOURS')?></p>
		</div>
	</div>
	<!-- Hours -->
	<!-- Minutes -->
	<div class="clock_minutes">
		<div class="bgLayer">
			<canvas class="canvas_minutes" width="122" height="122">
				Your browser does not support the HTML5 canvas tag.
			</canvas>
			<div class="text">
				<p class="val">0</p>
				<p class="type_minutes"><?php echo Text::_('JACD_MINUTES')?></p>
			</div>
		</div>
	</div>
	<!-- Minutes -->
	<!-- Seconds -->
	<div class="clock_seconds">
		<div class="bgLayer">
			<canvas class="canvas_seconds" width="122" height="122">
				Your browser does not support the HTML5 canvas tag.
			</canvas>
			<p class="val">0</p>
			<p class="type_seconds"><?php echo Text::_('JACD_SECONDS')?></p>
		</div>
	</div>
	<!-- Seconds -->
</div>
<script>
jQuery(document).ready(function($) {
	var el = '#clock-<?php echo $module->id ?>';

	JBCountDown_5(el, {
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
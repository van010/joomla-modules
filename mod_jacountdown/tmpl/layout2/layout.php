<?php
/**
 * $JA#COPYRIGHT$
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<div id="clock-<?php echo $module->id ?>" class="clock-wrapper">
	<div class="clock">
		<canvas class="canvas_seconds" width="518px" height="518px"></canvas>
	</div>

	<div class="timer">
		<p><span class="days">0</span> <?php echo Text::_('JACD_DAYS') ?> <span class="hrs">0</span> <?php  echo Text::_('JACD_HOURS')?> <span class="mins">0</span> <?php echo Text::_('JACD_MINUTES_2') ?> <span class="secs">0</span> <?php echo Text::_('JACD_SECONDS') ?></p>
	</div>  
</div>
<script>
jQuery(document).ready(function($) {
	var el = '#clock-<?php echo $module->id ?>';

	JBCountDown_2(el, {
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
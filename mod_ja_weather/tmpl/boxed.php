<?php
	/**
	 * $JA#COPYRIGHT$
	 */
	defined('_JEXEC') or die;

	use Joomla\CMS\Factory;
	use Joomla\CMS\Uri\Uri;
	use Joomla\CMS\Language\Text;

	$document = Factory::getDocument();
	$base_path = Uri::root(true) . '/modules/' . $module->module . '/asset/';

	$document->addStyleSheet($base_path . "css/style.css");
	$document->addStyleSheet($base_path . "css/boxed.css");

	$clock_format = $params->get('clock_format');
	$currentTime = date($clock_format, time());
	$iconFolder = $params->get('icon_set');
	$labelOption = intval($params->get('label_field'));
	$showInfo = intval($params->get('section_display'));
	
	$img = Uri::root() . $params->get('imgpath');
	
	$labelIcon =  $helper->labelIcon($labelOption);
	$displayForecast = intval($params->get('forecast'));
?>
<div class="jaw-wapper ja-weather-wrapper-<?php echo $module->id ?> boxed-layout">
	<div class="location-list">
		<?php foreach ($data->forecast as $key => $value) :
			$current = $value->current;
			?>
			<button class="weather-bar-item weather-button <?php echo $key === 0 ? 'active' : '' ?>"  data-index="<?php echo $key ?>">
				<?php echo $value->location . ' ' . $value->country ?>
			</button>
		<?php endforeach; ?>
	</div>

	<div class="ja-weather-wrapper">
		<?php foreach ($data->forecast as $key => $value):
			$current = $value->current;
			$icon = $current->weather->icon;
			$code = $current->weather->code;
			$weatherIcon = $helper->weatherIcon($iconFolder, $code, $icon);
			?>
			<div class="ja-weather-content <?php echo $key === 0 ? 'active' : '' ?>" data-index="<?php echo $key ?>">
				<div class="weather-side">
					<div class="date-container">
						<h2 class="date-dayname"><?php echo date('l') ?></h2>
						<span class="date-day"><?php echo $current->local_time ?></span>
						<span class="location"><?php echo $value->location; ?>, <?php echo $value->country; ?></span>
					</div>

					<div class="weather-container">
						<img class="weather-icon" src="<?php echo $weatherIcon ?>"/>
						<h3 class="weather-temp"><?php echo $current->temp ?></h3>
						<h4 class="weather-desc"><span><?php echo $current->weather->description; ?></span></h4>
					</div>
				</div>
				<div class="info-side">
					<div class="today-info-container">
						<?php if ($showInfo === 1): ?>
							<div class="today-info">
							<div class="humidity">
								<span class="title" title="<?php echo Text::_('TITLE_HUMIDITY') ?>">
									<?php echo Text::_($labelIcon['humidity']) ?></span>
									<span class="value"><?php echo $current->humidity; ?></span>
								</div>
							<div class="uv-index">
								<span class="title"
								      title="<?php echo Text::_('TITLE_UV_INDEX') ?>">
									<?php echo Text::_($labelIcon['uv']) ?></span>
									<span class="value <?php echo $current->uvi_class ?>">
										<?php echo $current->uvi; ?>
								</span>
							</div>
							<div class="pressure">
								<span class="title" title="<?php echo Text::_('TITLE_PRESSURE') ?>">
									<?php echo Text::_($labelIcon['pressure']) ?></span>
								<span class="value"><?php echo $current->pressure?></span>
							</div>
							<div class="wind">
								<span class="title" title="<?php echo Text::_('TITLE_WIND_SPEED') ?>">
									<?php echo Text::_($labelIcon['windSpeed']) ?></span>
								<span class="value">
                  <?php echo $current->wind_speed . '&#9;&bull;&#9;' . $current->wind_deg ?>
                  <?php echo isset($current->wind_deg_icon) ? '&#9;' . $current->wind_deg_icon : '' ?>
                </span>
							</div>
						</div>
						<?php endif; ?>
					</div>
						<?php if ($displayForecast !== 0): ?>
							<div class="week-container">
						<ul class="week-list">
							<?php foreach ($value->data as $key => $value) : ?>
								<?php if ($key > 0 && $key < $displayForecast+1) : ?>
									<li class="<?php echo $key == 1 ? 'active' : '' ?>">
										<img class="day-icon" alt="<?php echo $value->code; ?>" src="<?php echo $helper->weatherIcon($iconFolder, $value->code, $value->icon) ?>" />
										<span class="day-name"><?php echo $value->forecast_date; ?></span>
										<span class="day-temp"><?php echo $value->temp_max ?></span>
									</li>
								<?php endif; ?>
							<?php endforeach; ?>
							<div class="clear"></div>
						</ul>
					</div>
						<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
<script>
	jQuery(document).ready(function ($) {
		var $wrapper = $('.ja-weather-wrapper-<?php echo $module->id ?>');
		$wrapper.find('.weather-bar-item').on('click', function (e) {
			e.preventDefault();
			var $el = $(e.currentTarget);
			var index = $el.attr('data-index');

			var $target = $wrapper.find('.ja-weather-content[data-index="' + index + '"]');
			$wrapper.find('.weather-bar-item').removeClass('active');

			$el.addClass('active');
			$wrapper.find('.ja-weather-content').removeClass('active');

			$target.addClass('active');
		});
	})
</script>
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

	$clock_format = $params->get('clock_format');
	$currentTime = date($clock_format, time());
	$iconFolder = $params->get('icon_set');
	$listParams = $params->get('section_display');
	$labelOption = intval($params->get('label_field'));
	$labelIcon =  $helper->labelIcon($labelOption);
	$showInfo = intval($params->get('section_display'));
	$displayForecast = intval($params->get('forecast'));

	$img = Uri::root() . $params->get('imgpath');

	$display = $params->get('weatherStatus');
?>

<div id="main" class="jaw-wrapper ja-weather-wrapper-<?php echo $module->id ?> small-layout">

	<div class="location-list">
		<?php foreach ($data->forecast as $key => $value) :
			$current = $value->current;
			?>
			<button class="weather-bar-item weather-button <?php echo $key === 0 ? 'active' : '' ?>" data-index="<?php echo $key ?>">
				<?php echo $value->location . ' ' . $value->country ?>
			</button>
		<?php endforeach; ?>
	</div>

	<div class="jaw-content-list">
		<?php foreach ($data->forecast as $key => $value):
			$current = $value->current;
			$icon = $current->weather->icon;
			$code = $current->weather->code;
			$weatherIcon = $helper->weatherIcon($iconFolder, $code, $icon);
			?>
			<div class="ja-weather-content <?php echo $key === 0 ? 'active' : '' ?>" data-index="<?php echo $key ?>">

				<!-- Current weather -->
				<div class="jaw-current-weather">

					<div class="weather-today-info">

						<div class="jaw-main-info">
							<div class="localtion"><?php echo $value->location; ?>, <?php echo $value->country; ?></div>
							<div class="weather-info">
								<span class="weather-icon"><img src="<?php echo $weatherIcon ?>"/></span>
								<span class="degree"><?php echo $current->temp ?></span>
								<span class="weather-desc"><?php echo $current->weather->description; ?></span>
							</div>
						</div>
						<?php if ($showInfo === 1): ?>
							<div class="ja-other-info">
								<div class="humidity">
									<span class="title" title="<?php echo Text::_('TITLE_HUMIDITY') ?>">
										<?php echo Text::_($labelIcon['humidity']) ?></span>
									<span class="value"><?php echo $current->humidity; ?></span>
								</div>
								<div class="uv-index">
							<span class="title" title="<?php echo Text::_('TITLE_UV_INDEX') ?>">
								<?php echo Text::_($labelIcon['uv']) ?></span>
									<span class="value <?php echo $current->uvi_class ?>">
									<?php echo $current->uvi; ?>
							</span>
								</div>
								<div class="pressure">
							<span class="title" title="<?php echo Text::_('TITLE_PRESSURE') ?>">
								<?php echo Text::_($labelIcon['pressure']) ?></span>
									<span class="value"><?php echo $current->pressure ?></span>
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

					<?php if(!empty($img)): ?>
						<div class="jaw-bg" style="background-image: url(<?php echo $helper->imgBackground($img) ?>)"></div>
					<?php endif ?>
				</div>
				<!-- // Current weather -->
				<?php if ($displayForecast !== 0): ?>
					<div class="weather-stats">
					<div class="weather-stats-today">
					</div>
					<?php foreach ($value->data as $key => $value) : ?>
						<?php if ($key > 0 && $key < $displayForecast+1): ?>
							<div class="weather-forecast-stat">
								<div>
									<span class="date">
										<span><?php echo !empty($value->forecast_date) ? $value->forecast_date : $value->dt; ?></span>
									</span>
									<span>
										<img class="" alt="Weather icon" src="<?php echo $helper->weatherIcon($iconFolder, $value->code, $value->icon) ?>"/>
									</span>
									<span class="temp-max" style="display: inline">
                    <?php echo $value->temp_max ?></span>
									<span class="desc"><?php echo $value->temp_min ?></span>
								</div>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
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
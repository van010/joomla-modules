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
	$labelOption = intval($params->get('label_field'));
	$showInfo = intval($params->get('section_display'));
	$displayForecast = intval($params->get('forecast'));

	$img = $params->get('imgpath');
	$imgPath = Uri::root() . $img;

	$labelIcon = $helper->labelIcon($labelOption);
?>

<div class="jaw-wrapper ja-weather-wrapper-<?php echo $module->id ?> list-layout">

	<!-- WEATHER TAB -->
	<div class="location-list">
		<?php foreach ($data->forecast as $key => $value) :
			$current = $value->current;
			?>
			<button class="weather-bar-item weather-button <?php echo $key === 0 ? 'active' : '' ?>" data-index="<?php echo $key ?>">
				<?php echo $value->location . ' ' . $value->country ?>
			</button>
		<?php endforeach; ?>
	</div>
	<!-- // WEATHER TAB -->

	<!-- WEATHER CONTENT -->
	<div class="ja-weather-content-list">
		<?php foreach ($data->forecast as $key => $value) :
			$current = $value->current;
			$icon = $current->weather->icon;
			$code = $current->weather->code;
			$weatherIcon = $helper->weatherIcon($iconFolder, $code, $icon);
			?>

			<div class="city ja-weather-content <?php echo $key === 0 ? 'active' : '' ?>" data-index="<?php echo $key ?>">
				<!-- Begin: Current weather -->
				<div class="jaw-current-weather">
					<div class="ja-main-info">
						<h3 class="local-name">
							<span><?php echo $value->location; ?>,</span>
							<span><?php echo $value->country; ?></span><br/>
						</h3>

						<div class="local-time"><?php echo $current->local_time ?></div>

						<div class="curr-info">
							<img src="<?php echo $weatherIcon ?>" class="min-temperature"/>

							<div class="curr-temp">
								<?php if ($platform === 'weatherbit') : ?>
									<span class="min"><?php echo $current->temp; ?>-</span>
									<span class="max"><?php echo $current->feels_like ?></span>
								<?php endif; ?>

								<?php if ($platform === 'openweathermap') : ?>
									<span class="min"><?php echo $current->temp; ?>&nbsp;-&nbsp;</span>
									<span class="max"><?php echo $current->feels_like; ?></span>
								<?php endif; ?>
							</div>

							<div class="curr-desc"><?php echo $current->weather->description; ?></div>

						</div> <!-- // Current information -->
					</div>
					<?php if($showInfo === 1):?>
						<div class="ja-other-info">
							<ul>
								<li title="<?php echo Text::_('TITLE_FEELS_LIKE') ?>">
									<span class="jaw-label"><?php echo Text::_($labelIcon['feelsLike']) ?></span>
									<span class="value"><?php echo $current->feels_like; ?></span>
								</li>
								<li title="<?php echo Text::_('TITLE_HUMIDITY') ?>">
									<span class="jaw-label"><?php echo Text::_($labelIcon['humidity']) ?></span>
									<span class="value"><?php echo $current->humidity; ?></span>
								</li>
								<!-- The Dew Point is the temperature to which air must be cooled to become saturated with water vapor -->
								<?php if (!empty($current->dew_point)) : ?>
									<li title="<?php echo Text::_('TITLE_DEW_POINT') ?>">
										<span class="jaw-label"><?php echo Text::_($labelIcon['dewPoint']) ?></span>
										<span class="value"><?php echo $current->dew_point; ?></span>
									</li>
								<?php endif; ?>
								<li title="<?php echo Text::_('TITLE_WIND_SPEED') ?>">
									<span class="jaw-label"><?php echo Text::_($labelIcon['windSpeed']) ?></span>
									<span class="value">
								<?php echo $current->wind_speed . '&#9;&bull;&#9;' . $current->wind_deg ?>
								<?php if (isset($current->wind_deg_icon)) echo '&#9;' . $current->wind_deg_icon ?>
								</span>
								</li>
							</ul>

							<ul>
								<li title="<?php echo Text::_('TITLE_UV_INDEX') ?>">
									<span class="jaw-label"><?php echo Text::_($labelIcon['uv']) ?></span>
									<span class="<?php echo $current->uvi_class ?>">
                  <?php echo $current->uvi; ?></span>
								</li>
								<li title="<?php echo Text::_('TITLE_PRESSURE') ?>">
									<span class="jaw-label"><?php echo Text::_($labelIcon['pressure']) ?></span>
									<span class="value"><?php echo $current->pressure; ?></span>
								</li>
								<?php if (!empty($current->pop)) : ?>
									<li title="<?php echo Text::_('TITLE_PRECIP_PROB') ?>">
										<span class="jaw-label"><?php echo Text::_($labelIcon['precip']) ?></span>
										<span class="value"><?php echo $current->pop; ?></span>
									</li>
								<?php endif; ?>

								<?php if (!empty($current->rain)) : ?>
									<li title="<?php echo Text::_('TITLE_RAIN_VOLUME') ?>">
										<span class="jaw-label"><?php echo Text::_($labelIcon['rainVolume']) ?></span>
										<span class="value"><?php echo $current->rain; ?></span>
									</li>
								<?php endif; ?>
								<?php if (!empty($current->snow)) : ?>
									<li title="<?php echo Text::_('TITLE_SNOW_VOLUME') ?>">
										<span class="jaw-label"><?php echo Text::_($labelIcon['snowVolume']) ?></span>
										<span class="value"><?php echo $current->snow; ?></span>
									</li>
								<?php endif; ?>
							</ul>

							<ul>
								<li title="<?php echo Text::_('TITLE_SUNRISE') ?>">
									<span class="jaw-label"><?php echo Text::_($labelIcon['sunrise']) ?></span>
									<span class="value"><?php echo $current->sunrise; ?></span>
								</li>
								<li title="<?php echo Text::_('TITLE_SUNSET') ?>">
									<span class="jaw-label"><?php echo Text::_($labelIcon['sunset']) ?></span>
									<span class="value"><?php echo $current->sunset; ?></span>
								</li>
								<?php if (isset($current->moon_phase) && !empty($current->moon_phase)) : ?>
									<li title="<?php echo Text::_('TITLE_MOON_PHASE') ?>">
										<span class="jaw-label"><?php echo Text::_($labelIcon['moonPhase']) ?></span>
										<span class="value"><?php echo $current->moon_phase; ?></span>
									</li>
								<?php endif; ?>
							</ul>
						</div>
					<?php endif ?>

					<?php if(!empty($img)): ?>
						<div class="jaw-bg" style="background-image: url(<?php echo $helper->imgBackground($img) ?>)"></div>
					<?php endif ?>
				</div>
				<!-- End: Current weather -->
				<!-- Other date -->
				<?php if ($displayForecast !== 0): ?>
					<div class="jaw-other-date">
						<?php $forecast = $value->data; ?>
						<div class="weather-table">
							<div class="weather-list-head">
								<div class="jaw-col-1"></div>
								<div class="jaw-col-2"></div>
								<div class="jaw-col-3"><?php echo Text::_('W_FORECAST')?></div>
								<div class="jaw-col-4"><?php echo Text::_('W_LOW_TEMP')?></div>
								<div class="jaw-col-5"><?php echo Text::_('W_HIGH_TEMP')?></div>
								<div class="jaw-col-6"><?php echo Text::_('W_MOON')?></div>
							</div>
							<div class="weather-list-ct">
								<?php foreach ($forecast as $key => $value) : ?>
									<?php if ($key > 0 && $key < $displayForecast+1): ?>
										<div class="weather-row">
											<div class="jaw-col-1">
												<span class="date"><?php echo !empty($value->forecast_date) ? $value->forecast_date : $value->dt; ?></span>
												<?php if (!empty($value->pop_) && $value->pop_ > 0):?>
												<div>
													<span class="precip-icon"><img title="Precipitation Probability" src="<?php echo $helper->precipIcon($iconFolder) ?>" alt="<?php echo $value->pop ?>"></span>
													<span><?php echo $value->pop; ?></span>
												</div>
												<?php endif; ?>
											</div>
											<div class="jaw-col-2">
												<img src="<?php echo $helper->weatherIcon($iconFolder, $value->code, $value->icon) ?>" alt="Weather icon" />
											</div>
											<div class="jaw-col-3"><?php echo $value->description . '&#9;&#9837;&#9;' ?></div>
											<div class="jaw-col-4 temp-min"><?php echo $value->temp_min ?></div>
											<div class="jaw-col-5 temp-max"><?php echo $value->temp_max ?></div>
											<div class="jaw-col-6" title="<?php echo $value->moon_phase_status ?>"><?php echo $value->moon_phase_icon; ?></div>
										</div>
									<?php endif; ?>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				<?php endif; ?>
				<!-- // Other date -->
			</div>
		<?php endforeach; ?>
	</div>
	<!-- // WEATHER CONTENT -->
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
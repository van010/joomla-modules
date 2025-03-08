<?php
	/**
	 * $JA#COPYRIGHT$
	 */
	defined('_JEXEC') or die;

	use Joomla\CMS\Factory;
	use Joomla\CMS\Uri\Uri;

	$document = Factory::getDocument();
	$base_path = Uri::root(true) . '/modules/' . $module->module . '/asset/';

  $document->addStyleSheet($base_path . "css/style.css");
	$document->addStyleSheet($base_path . "css/widget.css");

	$clock_format = $params->get('clock_format');
	$currentTime = date($clock_format, time());
	$iconFolder = $params->get('icon_set');
	$labelOption = intval($params->get('label_field'));
	$displayForecast = $params->get('forecast');
	$showInfo = intval($params->get('section_display'));

	$img = $params->get('imgpath');
	$imgPath = Uri::root() . $img;

	$labelIcon =  $helper->labelIcon($labelOption);
?>
<div class="jaw-wrapper ja-weather-wrapper-<?php echo $module->id ?> small-widget <?php echo $helper->imgBackground($img)?'has-bg': '';?>">

  <?php if (count($data->forecast) > 1) : ?>
	<!-- WEATHER TAB -->
	<div class="location-list">
		<?php foreach ($data->forecast as $key => $value):
			$current = $value->current;
			?>
			<button class="weather-bar-item weather-button <?php echo $key === 0 ? 'active' : '' ?>" data-index="<?php echo $key ?>">
				<?php echo $value->location . ' ' . $value->country ?>
			</button>
		<?php endforeach; ?>
	</div>
	<!-- // WEATHER TAB -->
  <?php endif ?>

	<!-- WEATHER CONTENT -->
	<div class="jaw-content-list">
		<?php foreach ($data->forecast as $key => $value ) :
			$current = $value->current;
			$code = $current->weather->code;
			$icon = $current->weather->icon;
			$weatherIcon = $helper->weatherIcon($iconFolder, $code, $icon);
			?>
			<div class="jaw-widget ja-weather-content <?php echo $key === 0 ? 'active' : '' ?>" data-index="<?php echo $key ?>">

				<!-- Begin: Current weather -->
				<div class="jaw-current-weather">
          
					<div class="ja-main-info">
						<h3 class="local-name">
							<span><?php echo $value->location; ?>,</span>
							<span><?php echo $value->country; ?></span>
						</h3>

            <div class="jaw-location-info">
              <span class="weather-icon"><img src="<?php echo $weatherIcon ?>" class="min-temperature"/></span>
							<div class="current-temp">
								<?php if ($platform === 'weatherbit') : ?>
									<span class="max"><?php echo $current->feels_like ?></span>
								<?php endif; ?>

								<?php if ($platform === 'openweathermap') : ?>
									<span class="max"><?php echo $current->feels_like; ?></span>
								<?php endif; ?>

                <div class="weather-desc"><span><?php echo $current->weather->description; ?></span></div>
							</div>
            </div>

					</div>

					<?php if(!empty($img)): ?>
						<div class="jaw-bg" style="background-image: url(<?php echo $helper->imgBackground($img) ?>)"></div>
					<?php endif ?>

				</div>
				<!-- End: Current weather -->

			</div>

		<?php endforeach; ?>
	</div>
	<!-- //WEATHER CONTENT -->

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
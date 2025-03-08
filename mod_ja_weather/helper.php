<?php
	
	use Joomla\CMS\Factory;
	use Joomla\CMS\Uri\Uri;
	use Joomla\CMS\Table\Table;
	use Joomla\Registry\Registry;
	use Joomla\CMS\Language\Text;
	use Joomla\CMS\Filesystem\File;
	use Joomla\CMS\HTML\HTMLHelper;
	use Joomla\CMS\Http\HttpFactory;
	use Joomla\CMS\Filesystem\Folder;
	
	defined('_JEXEC') or die;
	
	
	class ModJaWeatherHelper
	{
		protected static $currentApis = array();
		protected static $error = false;
		protected static $params;
		protected static $moduleId;
		protected static $moduleDir;
		protected static $platform;
		protected static $tempUnit;
		protected static $forecastLimit;
		protected static $getDataBy;
		protected static $separator;
		protected static $cacheTime;
		protected static $dateFormat;
		protected static $forecastDateFormat;
		protected static $clockFormat;
		protected static $fullDateDormat;
		protected static $icon_url;
		
		private static $weatherBitCurrentApi = 'https://api.weatherbit.io/v2.0/current?{}&lang={}&key={}&units={}&include={}';
		private static $weatherBitForecastApi = 'https://api.weatherbit.io/v2.0/forecast/daily?{}&days={}&lang={}&key={}&units={}&include={}';
		private static $openWeatherCurrentApi = 'https://api.openweathermap.org/data/2.5/weather?{}&lang={}&appid={}&units={}';
		private static $openWeatherForecastApi = 'https://api.openweathermap.org/data/3.0/onecall?{}&lang={}&exclude={}&units={}&appid={}';
		private static $wbEndpointsArr = array(
			'location_id' => ['', 'city_id={}'],
			'location_name' => ['', 'city={}'],
			'location_latLon' => ['', 'lat={}&lon={}'],
		);
		private static $owEndpointsArr = array(
			'location_id' => ['', 'id={}'],
			'location_name' => ['', 'q={}'],
			'location_latLon' => ['', 'lat={}&lon={}'],
			'all' => '',
			'current' => 'minutely,hourly,daily',
			'minutely' => 'hourly,daily',
			'hourly' => 'minutely,daily',  # forecast by hour from 5:am current day to + 2 days next at 4 am
			'daily' => 'minutely,hourly',  # forecast by day from current day to 7 days next
		);
		
		public function __construct($params, $id)
		{
			self::init($params, $id);
		}
		
		private static function init($params, $id)
		{
			self::$params = $params;
			self::$moduleId = $id;
			self::$moduleDir = basename(dirname(__FILE__));
			self::$platform = $params->get('platform', 'openweathermap');
			self::$getDataBy = $params->get('getdataby');
			self::$tempUnit = self::$platform === 'openweathermap'
				? $params->get('temperature_unit', 'metric')
				: $params->get('temperature_unit', 'M');
			self::$separator = $params->get('separator');
			self::$forecastLimit = self::$platform === 'openweathermap' ? 'daily' : 7;
			self::$icon_url = self::$platform === 'openweathermap'
				? 'https://openweathermap.org/img/w/%s.png'
				: 'https://www.weatherbit.io/static/img/icons/%s.png';
			self::$cacheTime = $params->get('cache_time', 3600);
			# self::$dateFormat = $params->get('date_format', 'd M Y, ');
			self::$dateFormat = 'd M Y';
			self::$forecastDateFormat = $params->get('forecast_date_format', 'D, j m');
			self::$clockFormat = $params->get('clock_format', 'D H:i a');
			# self::$fullDateDormat = self::format_str('{}{}', [self::$dateFormat, self::$clockFormat], false);
			self::$fullDateDormat = self::format_str('{}', [self::$dateFormat], false);
		}
		
		public function getData()
		{
			if (self::$error == true) {
				return '';
			}
			return self::addDataToCache(self::$cacheTime);
		}
		
		private static function getApis()
		{
			if (self::validateEmptyParams() == false) return '';
			$variables = array(
				'language' => self::$params->get('language', 'en'),
				'tempUnit' => self::$params->get('temperature_unit'),
				'owForecastLimit' => "daily",
				'wbForecastLimit' => self::$params->get('forecast') === 'daily'
					? 7 : self::$params->get('forecast'),
				'apiKey' => self::validateEmptyParams()[1],
				'owApiKey' => !empty(self::$params->get('apiKey')) && self::$platform === 'openweathermap'
					? self::$params->get('apiKey') : '3db8e43da281815258ab69bf2faed50e',
				'wbApiKey' => !empty(self::$params->get('apiKey')) && self::$platform === 'weatherbit'
					? self::$params->get('apiKey') : '6199cd4c36cd45858eec40473224dbad',
			);
			$apis = array();
			$param = self::validateEmptyParams()[0];
			$arrParam = array_values(array_filter(preg_split("/\\r\\n|\\r|\\n/", $param)));
			
			foreach ($arrParam as $key => $value) {
				if (is_int(strpos(self::$getDataBy, 'lat'))) {
					$value = is_int(strpos($value, ',')) ? explode(', ', $value) : explode(' ', $value);
					$lat = $value[0];
					$lon = $value[1];
					if (count($value) !== 2) {
						self::throwErrors(self::$getDataBy);
						break;
					}
					$paramApi = self::format_str(self::$owEndpointsArr[self::$getDataBy][1], [$lat, $lon]);
					$owCurrentApi = self::format_str(self::$openWeatherCurrentApi,
						[$paramApi, $variables['language'], $variables['owApiKey'], $variables['tempUnit']], false);
					self::$currentApis['openweathermap'][$key] = $owCurrentApi;
				} else {
					$paramApi = self::format_str(self::$wbEndpointsArr[self::$getDataBy][1], [$value]);
					$owParamApi = self::format_str(self::$owEndpointsArr[self::$getDataBy][1], [$value], false);
					
					$owCurrentApi = self::format_str(self::$openWeatherCurrentApi,
						[$owParamApi, $variables['language'], $variables['owApiKey'], $variables['tempUnit']], false);
					self::$currentApis['openweathermap'][$key] = $owCurrentApi;
				}
				
				switch (self::$platform) {
					case 'openweathermap':
						if (self::$getDataBy !== 'location_latLon') {
							# access current openweather API to get Coordinates
							$owCurrentData = self::crawlData($owCurrentApi);
							if (empty($owCurrentData)) {
								return '';
							}
							$lat = $owCurrentData->coord->lat;
							$lon = $owCurrentData->coord->lon;
							$paramApi = self::format_str('lat={}&lon={}', [$lat, $lon]);
						}
						$owForecastApi = self::format_str(self::$openWeatherForecastApi,
							[$paramApi, $variables['language'], self::$owEndpointsArr[$variables['owForecastLimit']]
								, $variables['tempUnit'], $variables['apiKey']], false);
						$apis[$key] = $owForecastApi;
						break;
					case 'weatherbit':
						# handle all API for weatherbit
						$wbForecastApi = self::format_str(self::$weatherBitForecastApi,
							[$paramApi, $variables['wbForecastLimit'], $variables['language'],
								$variables['apiKey'], $variables['tempUnit'], '']);
						
						$apis[$key] = $wbForecastApi;
						break;
				}
			}
			return $apis;
		}
		
		private static function addDataToCache($cacheTime)
		{
			# implement auto refresh cache
			$now = time();
			$cacheFile = self::createCacheFile('forecast')[0];
			$cachePath = self::createCacheFile('')[1];
			
			$updateTimeFile = self::format_str('{}/{}-lastUpdate.txt', [$cachePath, self::$moduleId]);
			$lastUpdate = file_exists($updateTimeFile) ? +file_get_contents($updateTimeFile) : 0;
			
			HTMLHelper::_('behavior.core');
			HTMLHelper::_('jquery.framework');
			$doc = Factory::getDocument();
			
			if (!is_array(self::getApis())){
				return null;
			}
			
			# call ajax and update cache
			if ($lastUpdate < ($now - $cacheTime) && file_exists($cacheFile)) {
				echo '<script>console.log("Ajax update cache")</script>';
				$doc->addScript(Uri::root() . '/modules/mod_ja_weather/asset/updateWeatherData.js');
				$doc->addScriptDeclaration(';updateWeatherData(' . self::$moduleId . ');');
			}
			if (is_file($cacheFile)) {
				$cache = json_decode(file_get_contents($cacheFile));
				return (object)$cache;
			}
			if (count(self::getApis()) < 3) {
				# update data directly
				$cache = self::getForecastData(self::getApis());
				$objCache = json_decode(json_encode($cache), FALSE);
				if (isset($cache) && !empty($cache)) {
					File::write($updateTimeFile, $now);
					File::write($cacheFile, json_encode($cache));
				}
				return $objCache;
			}
			# first load uses ajax
			echo '<p class="alert alert-success">' . Text::_('MOD_JAWEATHER_FIRST_LOAD');
			echo '<script>console.log("first loading...")</script>';
			$doc->addScript(Uri::root() . '/modules/mod_ja_weather/asset/updateWeatherData.js');
			$doc->addScriptDeclaration(';updateWeatherData(' . self::$moduleId . ');');
			if (is_file($cacheFile)) {
				$cache = json_decode(file_get_contents($cacheFile));
				return (object)$cache;
			}
		}
		
		# get and customize data
		private static function getForecastData($arrApis)
		{
			if (empty($arrApis)) return '';
			$results = array();
			foreach ($arrApis as $key => $value) {
				$owCurrentData = self::crawlData(self::$currentApis['openweathermap'][$key]);
				if (self::$error == true) {
					return '';
				}
				
				$results['forecast'][$key] = self::crawlData($value);
				if (self::$error == true) {
					return '';
				}
				# get location name and country for both watherbit and openweather data
				$results['forecast'][$key]->location = $owCurrentData->name;
				$results['forecast'][$key]->country = $owCurrentData->sys->country;
				$results['forecast'][$key]->timezone_ = $owCurrentData->timezone;  # get timezone for weatherbit data
			}
			
			return self::customizeData($results['forecast']);
		}
		
		private static function customizeData($data)
		{
			foreach ($data as $key => $value) {
				$results['forecast'][$key] = self::$platform === 'openweathermap'
					? self::customizeOpenWeather($value, self::$tempUnit)
					: self::customizeWeatherBit($value, self::$tempUnit);
			}
			return $results;
		}
		
		public static function updateCacheAjax()
		{
			$input = Factory::getApplication()->input;
			$id = $input->getInt('id', 0);
			$module = Table::getInstance('module');
			$module->load($id);
			$params = new Registry($module->params);
			
			$now = time();
			$cacheTime = $params->get('cache_time', 3600);
			$platform = $params->get('platform');
			
			$cachePath = self::createCacheFile('')[1] . 'mod_ja_weather/';
			$cacheFile = self::format_str('{}{}-{}-{}.json', [$cachePath, $id, $platform, 'forecast']);
			$updateTimeFile = self::format_str('{}{}-lastUpdate.txt', [$cachePath, $id]);
			$lastUpdate = file_exists($updateTimeFile) ? +file_get_contents($updateTimeFile) : 0;
			if ($lastUpdate < ($now - $cacheTime)) {
				$cache = self::setCache($input, $module, $updateTimeFile);
				File::write($cacheFile, json_encode($cache));
			}
		}
		
		private static function setCache($input, $module, $updateTimeFile)
		{
			# get APIs
			File::write($updateTimeFile, time());
			$id = $input->getInt('id', 0);
			$module->load($id);
			if (!$module->id || $module->module !== 'mod_ja_weather' || !$module->published) {
				die('Error! Unknown module');
			}
			$params = new Registry($module->params);
			$getDataBy = $params->get('getdataby');
			$platform = $params->get('platform', 'openweathermap');
			$apiKey = $params->get('apiKey');
			self::$separator = $params->get('separator');
			self::$platform = $platform;
			self::$tempUnit = $platform === 'openweathermap'
				? $params->get('temperature_unit', 'metric')
				: $params->get('temperature_unit', 'M');
			self::$cacheTime = $params->get('cache_time', 3600);
			self::$dateFormat = $params->get('date_format', 'd M Y');
			self::$clockFormat = $params->get('clock_format', 'D H:i a');
			# self::$fullDateDormat = self::format_str('{}{}', [self::$dateFormat, self::$clockFormat], false);
			self::$fullDateDormat = self::format_str('{}', [self::$dateFormat], false);
			self::$forecastDateFormat = $params->get('forecast_date_format', 'D, j m');
			self::$forecastLimit = $platform === 'openweathermap'
				? 'daily'
				: ($params->get('forecast', 7) === 'daily' ? 7 : 7);
			
			if (empty($apiKey)) {
				die('Empty Api key. Please Insert API key!');
			}
			$arrParams = array(
				'location_id' => $params->get('locationId'),
				'location_name' => $params->get('locationName'),
				'location_latLon' => $params->get('locationLatLon'),
				'language' => $params->get('language', 'en'),
				'tempUnit' => $params->get('temperature_unit'),
				'owForecastLimit' => 'daily',
				'owApiKey' => $platform === 'openweathermap'
					? $apiKey : '3db8e43da281815258ab69bf2faed50e',
				'wbApiKey' => $platform === 'weatherbit'
					? $apiKey : '6199cd4c36cd45858eec40473224dbad',
			);
			$errorParam = ucfirst(str_replace('_', ' ', $getDataBy));
			if (empty($arrParams[$getDataBy])) {
				die("Empty " . $errorParam . '. Please Insert ' . $errorParam . ' !');
			}
			$arrMainParams = array_values(array_filter(preg_split("/\\r\\n|\\r|\\n/", $arrParams[$getDataBy])));
			
			# get forecast api: openweather and weatherbit, current api: openweather
			$apis = array();
			foreach ($arrMainParams as $key => $value) {
				if (is_int(strpos($getDataBy, 'lat'))) {
					$value = is_int(strpos($value, ',')) ? explode(', ', $value) : explode(' ', $value);
					$lat = $value[0];
					$lon = $value[1];
					if (count($value) !== 2) {
						die('Wrong ' . $errorParam . '. Check it again!');
					}
					$paramApi = self::format_str(self::$owEndpointsArr[$getDataBy][1], [$lat, $lon]);
					$owCurrentApi = self::format_str(self::$openWeatherCurrentApi,
						[$paramApi, $arrParams['language'], $arrParams['owApiKey'], $arrParams['tempUnit']], false);
					self::$currentApis['openweathermap'][$key] = $owCurrentApi;
				} else {
					$paramApi = self::format_str(self::$wbEndpointsArr[$getDataBy][1], [$value]);
					$owParamApi = self::format_str(self::$owEndpointsArr[$getDataBy][1],
						[$value], false);
					$owCurrentApi = self::format_str(self::$openWeatherCurrentApi,
						[$owParamApi, $arrParams['language'], $arrParams['owApiKey'], $arrParams['tempUnit']], false);
					self::$currentApis['openweathermap'][$key] = $owCurrentApi;
				}
				switch ($platform) {
					case 'openweathermap':
						if ($getDataBy !== 'location_latLon') {
							$owCurrentData = self::crawlData($owCurrentApi);
							if (empty($owCurrentData)) {
								die('Something went wrong in API');
							}
							$lat = $owCurrentData->coord->lat;
							$lon = $owCurrentData->coord->lon;
							$paramApi = self::format_str('lat={}&lon={}', [$lat, $lon]);
						}
						$owForecastApi = self::format_str(self::$openWeatherForecastApi,
							[$paramApi, $arrParams['language'], self::$owEndpointsArr[$arrParams['owForecastLimit']]
								, $arrParams['tempUnit'], $apiKey], false);
						$apis[$key] = $owForecastApi;
						break;
					case 'weatherbit':
						$wbForecastApi = self::format_str(self::$weatherBitForecastApi,
							[$paramApi, self::$forecastLimit, $arrParams['language'],
								$apiKey, $arrParams['tempUnit'], '']);
						$apis[$key] = $wbForecastApi;
						break;
				}
			}
			# get forecast data
			$results = array();
			foreach ($apis as $key => $value) {
				$owCurrentData_ = self::crawlData(self::$currentApis['openweathermap'][$key]);
				if (self::$error == true) {
					die('Something went wrong in current openweather API!');
				}
				$results['forecast'][$key] = self::crawlData($value);
				if (self::$error == true) {
					die('Something went wrong in current ' . $platform . ' API!');
				}
				$results['forecast'][$key]->location = $owCurrentData_->name;
				$results['forecast'][$key]->country = $owCurrentData_->sys->country;
				$results['forecast'][$key]->timezone_ = $owCurrentData_->timezone;
			}
			return self::customizeData($results['forecast']);
		}
		
		private static function customizeWeatherBit($data, $tempUnit)
		{
			$current = $data->data[0];
			$local_time = self::handleLocalTime('H:i', $data->timezone);  # 24hour format
			
			$forecast_data = array(
				'tempUnit' => $tempUnit,
				'type' => 'forecast',
				'separator' => self::$separator,
				'location' => $data->city_name,
				'country' => $data->country_code,
				'general' => [
					'lat' => $data->lat,
					'lon' => $data->lon,
					'timezone' => $data->timezone,
					'state_code' => $data->state_code,
					'country_code' => $data->country_code,
				],
				'current' => [
					'dt' => $current->datetime,
					'local_time' => self::handleLocalTime(self::$fullDateDormat, $data->timezone),
					'part_of_day' => self::handlePartOfDay($local_time),
					'period_of_day' => self::handlePeriodsOfDay($local_time),
					'sunrise' => isset($data->timezone_) ?
						self::handleSunTime($current->sunrise_ts, $data->timezone_, self::$clockFormat) : '',
					'sunset' => isset($data->timezone_) ?
						self::handleSunTime($current->sunset_ts, $data->timezone_, self::$clockFormat) : '',
					'moon_phase' => self::format_str(
						'{} &bull; {}',
						[self::handleMoonPhases($current->moon_phase_lunation)[0],
							self::handleMoonPhases($current->moon_phase_lunation)[1]], false),
					'moon_phase_status' => self::handleMoonPhases($current->moon_phase_lunation)[0],
					'moon_phase_icon' => self::handleMoonPhases($current->moon_phase_lunation)[1],
					'moon_code' => $current->moon_phase_lunation,
					'weather' => [
						'icon' => $current->weather->icon,
						'description' => ucwords($current->weather->description),
						'main' => ucwords($current->weather->description),
						'code' => $current->weather->code,
					],
					'pop' => self::format_str('{}%', [round($current->pop)]),  # Probability of Precipitation (%)
					'pop_' => [round($current->pop)],
					'clouds' => isset($current->clouds)
						? self::format_str('{}%', [$current->clouds]) : '',
					'temp' => self::featuresRelatedTempUnit($tempUnit, $current->temp),
					'max_temp' => self::featuresRelatedTempUnit($tempUnit, $current->max_temp),
					'min_temp' => self::featuresRelatedTempUnit($tempUnit, $current->min_temp),
					'high_temp' => self::featuresRelatedTempUnit($tempUnit, $current->high_temp),
					'low_temp' => self::featuresRelatedTempUnit($tempUnit, $current->low_temp),
					'feels_like' => self::featuresRelatedTempUnit($tempUnit, $current->max_temp),
					'humidity' => self::format_str('{}%', [$current->rh]),
					'dew_point' => self::format_str('{}&deg;C', [$current->dewpt]),
					'wind_speed' => self::format_str('{} m/s', [round($current->wind_spd, 2)]),
					'wind_deg' => $current->wind_cdir,
					'wind_deg_icon' => self::handleWindDirSymbols($current->wind_cdir),
					'uvi' => isset($current->uv)
						? self::format_str('{} - {}'
							, [round($current->uv, 1), self::handleUvIndex($current->uv)[0]])
						: '',
					'uvi_background' => self::handleUvIndex($current->uv)[1],
					'uvi_color' => self::handleUvIndex($current->uv)[2],
					'uvi_class' => self::handleUvIndex($current->uv)[3],
					'ozone' => isset($current->ozone)
						? self::format_str('{} DU', [round($current->ozone, 2)]) : '',
					'pressure' => self::format_str('{} mb', [$current->pres]),
					'rain' => $current->precip !== 0
						? self::format_str('{} mm', [round($current->precip, 1)]) : '',
					'snow' => $current->snow !== 0
						? self::format_str('{} mm', [round($current->snow, 1)]) : '',
					'visibility' => self::format_str('{} Km', [$current->vis]),
				],
				'data' => array(),
			);
			$forecast_daily = $data->data;
			foreach ($forecast_daily as $key => $value) {
				$forecast_data['data'][$key]['separator'] = self::$separator;
				$forecast_data['data'][$key]['dt'] = date('D d/m', strtotime($value->datetime));
				$forecast_data['data'][$key]['forecast_date'] = self::_handleDate(self::$forecastDateFormat, strtotime($value->datetime));
				$forecast_data['data'][$key]['date_time'] = strtotime($value->datetime);
				$forecast_data['data'][$key]['sunrise'] = isset($data->timezone_)
					? self::handleSunTime($value->sunrise_ts, $data->timezone_, self::$clockFormat) : '';
				$forecast_data['data'][$key]['sunset'] = isset($data->timezone_)
					? self::handleSunTime($value->sunset_ts, $data->timezone_, self::$clockFormat) : '';
				$forecast_data['data'][$key]['moon_phase'] = self::format_str(
					'{} &bull; {}', [self::handleMoonPhases($value->moon_phase_lunation)[0],
					self::handleMoonPhases($value->moon_phase_lunation)[1]], false);
				$forecast_data['data'][$key]['moon_phase_icon'] = self::handleMoonPhases($value->moon_phase_lunation)[1];
				$forecast_data['data'][$key]['moon_code'] = $value->moon_phase_lunation;
				$forecast_data['data'][$key]['moon_phase_status'] = self::handleMoonPhases($value->moon_phase_lunation)[0];
				$forecast_data['data'][$key]['icon'] = $value->weather->icon;
				$forecast_data['data'][$key]['code'] = $value->weather->code;
				$forecast_data['data'][$key]['description'] = ucwords($value->weather->description);
				$forecast_data['data'][$key]['main'] = '';
				$forecast_data['data'][$key]['pop'] = self::format_str('{}%', [round($value->pop)]);
				$forecast_data['data'][$key]['pop_'] = round($value->pop);
				$forecast_data['data'][$key]['clouds'] = isset($value->clouds)
					? self::format_str('{}%', [$value->clouds]) : '';
				$forecast_data['data'][$key]['temp'] = self::featuresRelatedTempUnit($tempUnit, $value->temp);
				$forecast_data['data'][$key]['temp_max'] = self::featuresRelatedTempUnit($tempUnit, $value->max_temp);
				$forecast_data['data'][$key]['temp_min'] = self::featuresRelatedTempUnit($tempUnit, $value->min_temp);
				$forecast_data['data'][$key]['high_temp'] = self::featuresRelatedTempUnit($tempUnit, $value->high_temp);
				$forecast_data['data'][$key]['low_temp'] = self::featuresRelatedTempUnit($tempUnit, $value->low_temp);
				$forecast_data['data'][$key]['feels_like'] = self::featuresRelatedTempUnit($tempUnit, $value->max_temp);
				$forecast_data['data'][$key]['humidity'] = self::format_str('{}%', [$value->rh]);
				$forecast_data['data'][$key]['dew_point'] = self::format_str('{}&deg;C', [$value->dewpt]);
				$forecast_data['data'][$key]['wind_speed'] = self::format_str('{} m/s', [round($value->wind_spd, 2)]);
				$forecast_data['data'][$key]['wind_deg'] = $value->wind_cdir;
				
				$forecast_data['data'][$key]['uvi'] = isset($value->uv)
					? self::format_str('{} [0-11+]', [round($value->uv, 1)]) : '';
				$forecast_data['data'][$key]['wind_deg'] = self::format_str('{} mb', [$value->pres]);
				$forecast_data['data'][$key]['rain'] = isset($value->precip)
					? self::format_str('{} mm', [round($value->precip)]) : '';
				$forecast_data['data'][$key]['snow'] = isset($value->snow)
					? self::format_str('{} mm', [round($value->snow, 1)]) : '';
				$forecast_data['data'][$key]['ozone'] = isset($value->ozone)
					? self::format_str('{} DU', [round($value->ozone, 2)]) : '';
			}
			# return array to synchronize with multiple forecast
			return $forecast_data;
		}
		
		private static function customizeOpenWeather($data, $tempUnit)
		{
			$current = $data->current;
			$currentDayWeather = $data->daily[0]->temp;
			$weather = $current->weather[0];
			$localTime = self::handleLocalTime('H:i', $data->timezone);  # 24h format
			
			$forecastBy = self::$forecastLimit;
			$dataForecastBy = $data->$forecastBy;
			
			$currentMoonPhase = $dataForecastBy[0]->moon_phase;
			$moonPhaseIcon = self::handleMoonPhases($currentMoonPhase)[1];
			$moonPhase = self::format_str('{} &bull; {}',
				[self::handleMoonPhases($currentMoonPhase)[0], $moonPhaseIcon], false);
			
			$forecast_data = array(
				'forecast_limit' => self::$forecastLimit,
				'temp_unit' => $tempUnit,
				'type' => 'forecast',
				'separator' => self::$separator,
				'location' => $data->location,
				'country' => $data->country,
				'general' => [
					'lat' => $data->lat,
					'lon' => $data->lon,
					'timezone' => $data->timezone,
					'timezone_offset' => $data->timezone_offset,  # shift in seconds from UTC
				],
				'current' => [
					'dt' => date(self::$dateFormat, $current->dt),
					'local_time' => self::handleLocalTime(self::$fullDateDormat, $data->timezone),
					'part_of_day' => self::handlePartOfDay($localTime),
					'period_of_day' => self::handlePeriodsOfDay($localTime),
					'sunrise' => self::handleSunTime($current->sunrise, $data->timezone_offset, self::$clockFormat),
					'sunset' => self::handleSunTime($current->sunset, $data->timezone_offset, self::$clockFormat),
					'moon_phase' => $moonPhase,
					'moon_code' => $currentMoonPhase,
					'moon_phase_icon' => $moonPhaseIcon,
					'moon_phase_status' => self::handleMoonPhases($currentMoonPhase)[0],
					'weather' => [
						'code' => $weather->id,
						'main' => ucwords($weather->main),
						'description' => ucwords($weather->description),
						'icon' => $weather->icon,
					],
					'pop' => self::format_str('{}%', [round($data->daily[0]->pop)]),
					'pop_' => round($data->daily[0]->pop),
					'clouds' => isset($current->clouds)
						? self::format_str('{}%', [$current->clouds]) : '',
					/*'temp' => self::featuresRelatedTempUnit($tempUnit, $current->temp),*/
					/*'feels_like' => self::featuresRelatedTempUnit($tempUnit, $current->feels_like),*/
					'temp' => self::featuresRelatedTempUnit($tempUnit, $currentDayWeather->min),
					'feels_like' => self::featuresRelatedTempUnit($tempUnit, $currentDayWeather->max),
					'humidity' => self::format_str('{}%', [$current->humidity]),
					'dew_point' => isset($current->dew_point)
						? self::featuresRelatedTempUnit($tempUnit, $current->dew_point)
						: '',
					'wind_speed' => self::featuresRelatedTempUnit($tempUnit, $current->wind_speed, false, '{} m/s'),
					'wind_deg' => self::format_str('{}&deg;', [$current->wind_deg]),
					'uvi' => isset($current->uvi)
						? self::format_str('{} - {}', [round($current->uvi, 1), self::handleUvIndex($current->uvi)[0]], false)
						: '',
					'uvi_background' => self::handleUvIndex($current->uvi)[1],
					'uvi_color' => self::handleUvIndex($current->uvi)[2],
					'uvi_class' => self::handleUvIndex($current->uvi)[3],
					'pressure' => self::format_str('{} hPa', [$current->pressure]),
					'rain' => isset($current->rain)
						? self::format_str('{} mm', [self::handleRainObj($current->rain)]) : '',
					'snow' => isset($current->snow)
						? self::format_str('{} mm', [round($current->snow, 1)]) : '',
					'wind_gust' => isset($current->wind_gust)
						? self::featuresRelatedTempUnit($tempUnit, $current->wind_gust, false, '{} m/s')
						: '',
					'visibility' => self::format_str('{} km', [round($current->visibility / 1000, 2)]),
				],
				'data' => array(),
			);
			
			foreach ($dataForecastBy as $key => $value) {
				$forecast_data['data'][$key]['separator'] = self::$separator;
				$forecast_data['data'][$key]['dt'] = date('D m/d', $value->dt);
				$forecast_data['data'][$key]['forecast_date'] = self::_handleDate(self::$forecastDateFormat, $value->dt);
				$forecast_data['data'][$key]['date_time'] = $value->dt;
				$forecast_data['data'][$key]['sunrise'] = self::handleSunTime(
					$value->sunrise, $data->timezone_offset, self::$clockFormat);
				$forecast_data['data'][$key]['sunset'] = self::handleSunTime(
					$value->sunset, $data->timezone_offset, self::$clockFormat);
				$forecast_data['data'][$key]['moonrise'] = self::handleSunTime(
					$value->moonrise, $data->timezone_offset, self::$clockFormat);
				$forecast_data['data'][$key]['moonset'] = self::handleSunTime(
					$value->moonset, $data->timezone_offset, self::$clockFormat);
				
				$forecast_data['data'][$key]['main'] = $value->weather[0]->main;
				$forecast_data['data'][$key]['description'] = ucwords($value->weather[0]->description);
				$forecast_data['data'][$key]['icon'] = $value->weather[0]->icon;
				$forecast_data['data'][$key]['code'] = $value->weather[0]->id;
				
				$forecast_data['data'][$key]['humidity'] = self::format_str('{}%', [$value->humidity]);
				$forecast_data['data'][$key]['pressure'] = self::format_str('{} hPa', [$value->pressure]);
				$forecast_data['data'][$key]['wind_speed'] = self::format_str('{} m/s', [$value->wind_speed]);
				$forecast_data['data'][$key]['wind_deg'] = self::format_str('{}&deg;', [$value->wind_deg]);
				$forecast_data['data'][$key]['clouds'] = self::format_str('{}%', [$value->clouds]);
				$forecast_data['data'][$key]['pop'] = self::format_str('{}%', [round($value->pop)]);
				$forecast_data['data'][$key]['pop_'] = round($value->pop);
				$forecast_data['data'][$key]['dew_point'] = self::featuresRelatedTempUnit($tempUnit, $value->dew_point);
				$forecast_data['data'][$key]['temp'] = self::featuresRelatedTempUnit($tempUnit, $value->temp->day);
				$forecast_data['data'][$key]['feels_like'] = self::featuresRelatedTempUnit($tempUnit, $value->feels_like->day);
				$forecast_data['data'][$key]['temp_night'] = self::featuresRelatedTempUnit($tempUnit, $value->temp->night);
				$forecast_data['data'][$key]['temp_max'] = self::featuresRelatedTempUnit($tempUnit, $value->temp->max);
				$forecast_data['data'][$key]['temp_min'] = self::featuresRelatedTempUnit($tempUnit, $value->temp->min);
				$forecast_data['data'][$key]['moon_phase'] = self::format_str('{} &bull; {}',
					[self::handleMoonPhases($value->moon_phase)[0], self::handleMoonPhases($value->moon_phase)[1]], false);
				$forecast_data['data'][$key]['moon_phase_icon'] = self::handleMoonPhases($value->moon_phase)[1];
				$forecast_data['data'][$key]['moon_phase_status'] = self::handleMoonPhases($value->moon_phase)[0];
				$forecast_data['data'][$key]['moon_code'] = $value->moon_phase;
				
				$forecast_data['data'][$key]['wind_gust'] =
					isset($value->wind_gust) && ($tempUnit === 'metric' || $tempUnit === 'standard')
						? self::format_str('{} m/s', [$value->wind_gust])
						: self::format_str('{} mi/h', [$value->wind_gust]);
				
				if (isset($value->rain)) {
					$forecast_data['data'][$key]['rain'] = self::format_str('{} mm', [round($value->rain, 1)]);
				}
				if (isset($value->snow)) {
					$forecast_data['data'][$key]['snow'] = self::format_str('{} mm', [round($value->snow, 1)]);
				}
			}
			return $forecast_data;
		}
		
		#===================================
		# validate Parameters
		#===================================
		private static function validateEmptyParams()
		{
			$apiKey = self::$params->get('apiKey');
			$data = array(
				'location_id' => self::$params->get('locationId'),
				'location_name' => self::$params->get('locationName'),
				'location_latLon' => self::$params->get('locationLatLon')
			);
			if (empty($apiKey)) {
				self::throwErrors('EMPTY_API_KEY');
				return false;
			}
			if (empty($data[self::$getDataBy])) {
				self::throwErrors('EMPTY_' . strtoupper(self::$getDataBy));
				return false;
			}
			return [$data[self::$getDataBy], $apiKey];
		}
		
		private static function crawlData($api)
		{
			$app = Factory::getApplication();
			$option = new Registry();
			try {
				$response = HttpFactory::getHttp($option)->get($api);
			} catch (RuntimeException $e) {
				self::throwErrors(self::$getDataBy);
				# $app->enQueueMessage("Could not open this url: " . $api);
				return '';
			}
			if ($response->code !== 200) {
				# echo "<p style='color:red;'>" .'debug in crawldata'. "</p>";
				# $app->enQueueMessage("Could not open this url: " . $api);
				self::throwErrors('INSERT_API_KEY');
				return '';
			}
			return json_decode($response->body);
		}
		
		public static function throwErrors($msg)
		{
			self::$error = true;
			$msg = strtoupper(trim($msg, " \n\r\t\v\0"));
			$msgWarning = '<p class="alert alert-warning">' . Text::_('MOD_JAWEATHER_ERROR_' . $msg);
			if ($msg === 'INSERT_API_KEY') {
				echo '<p class="alert alert-warning">' . Text::_('MOD_JAWEATHER_ERROR_' . $msg . '_' . strtoupper(self::$platform)) . '</p>';
			} else echo $msgWarning;
		}
		
		private static function createCacheFile($type)
		{
			$cachePath = JPATH_ROOT . '/cache/' . self::$moduleDir;
			if (!Folder::exists($cachePath)) {
				Folder::create(JPATH_CACHE . '/' . self::$moduleDir . '/');
			}
			$cacheFile = self::format_str('{}/{}-{}-{}.json',
				[$cachePath, self::$moduleId, self::$platform, $type]);
			
			return [$cacheFile, $cachePath];
		}
		
		private static function format_str($msg, $vars, $replaceSpace = true)
		{
			$vars = (array)$vars;
			$msg = preg_replace_callback('#\{\}#', function ($r) {
				static $i = 0;
				return '{' . ($i++) . '}';
			}, $msg);
			$result = str_replace(
				array_map(function ($k) {
					return '{' . $k . '}';
				}, array_keys($vars)), array_values($vars), $msg
			);
			if ($replaceSpace) return str_replace(' ', '', $result);
			return $result;
		}
		
		private static function handleRainObj($rainObj)
		{
			$rainArr = (array)$rainObj;
			return round($rainArr['1h'], 1) ?? round($rainArr['2h'], 1);
		}
		
		private static function handleLocalTime($format, $timezone)
		{
			$localTime = new DateTime('now', new DateTimeZone($timezone));
			return $localTime->format($format);
		}
		
		private static function handlePartOfDay($localTime)
		{
			$localTs = strtotime($localTime);
			$_6h = strtotime('6:00');
			$_18h = strtotime('18:00');
			
			if ($localTs > $_6h && $localTs < $_18h) {
				return 'Day';
			}
			return 'Night';
		}
		
		private static function handlePeriodsOfDay($localTime)
		{
			$local_ts = strtotime($localTime);
			$_0h = strtotime('00:00');  # midnight
			$_6h = strtotime('6:00');  # dawn
			$_12h = strtotime('12:00');  # midday or noon
			$_18h = strtotime('18:00');  # dusk
			$_23h59 = strtotime('23:59');
			
			if ($local_ts >= strtotime('23:55') && $local_ts <= strtotime('00:05')) {
				return 'Midnight';
			}
			if ($local_ts >= strtotime('5:55') && $local_ts <= strtotime('6:05')) {
				return 'Dawn';
			}
			if ($local_ts >= strtotime('11:55') && $local_ts <= strtotime('12:05')) {
				return 'Midday';
			}
			if ($local_ts > $_0h && $local_ts < $_12h) {
				return 'Morning';
			}
			if ($local_ts > $_12h && $local_ts < $_18h) {
				return 'Afternoon';
			}
			if ($local_ts > $_18h && $local_ts < $_23h59) {
				return 'Evening';
			}
		}
		
		private static function handleSunTime($sunTime, $timezoneOffset, $format, $option = 'suntime')
		{
			$sunTime += $timezoneOffset;
			if ($option === 'time') {
				return gmdate($format, $sunTime);
			}
			if (is_int($sunTime)) {
				return gmdate($format, $sunTime);
			}
			if (is_string($sunTime)) {
				$sunTime = strtotime($sunTime);  # convert to timestamp
				$sunTime += $timezoneOffset;
				return date($format, $sunTime);
			}
		}
		
		private static function handleMoonPhases($moonPhase)
		{
			if ($moonPhase === 0 || $moonPhase === 1) {
				return [ucwords('New moon'), '&#127761;'];
			}
			if (.24 <= $moonPhase && $moonPhase <= .25) {
				return [ucwords('First quarter moon'), '&#127763;'];
			}
			if (.47 <= $moonPhase && $moonPhase <= .52) {
				return [ucwords('Full moon'), '&#127765;'];
			}
			if (.72 <= $moonPhase && $moonPhase <= .75) {
				return [ucwords('Last quarter moon'), '&#127767;'];
			}
			if (0 < $moonPhase && $moonPhase < .24) {
				return [ucwords('Waxing crescent'), '&#127762;'];
			}
			if (.25 < $moonPhase && $moonPhase < .47) {
				return [ucwords('Waxing Gibbous'), '&#127764;'];
			}
			if (.5 < $moonPhase && $moonPhase < .72) {
				return [ucwords('Waning Gibbous'), '&#127766;'];
			}
			return [ucwords('Waning Crescent'), '&#127768;'];
		}
		
		private static function handleWindDirSymbols($windCdir)
		{
			switch ($windCdir) {
				case 'N':
					return '&#8639;';
				case 'S':
					return '&#8642;';
				case 'E':
					return '&#8640;';
				case 'W':
					return '&#8636;';
				case 'NW':
					return '&#8598;';
				case 'NE':
					return '&#8599;';
				case 'SE':
					return '&#8600;';
				case 'SW':
					return '&#8601;';
				default:
					return '';
			}
		}
		
		private static function handleUvIndex($uv)
		{
			$uv = round($uv, 0);
			switch ($uv) {
				case $uv === 0:
					return [ucwords('low'), '#0EFF76', '#090909', 'uv-low'];
				case ($uv <= 2):
					return [ucwords('low'), '#0EFF76', '#090909', 'uv-low'];
				case $uv >= 3 && $uv <= 5:
					return [ucwords('moderate'), '#FFFF00', '#090909', 'uv-moderate'];
				case $uv >= 6 && $uv <= 7:
					return [ucwords('high'), '#FD9103', '#090909', 'uv-high'];
				case $uv >= 8 && $uv <= 10:
					return [ucwords('very High'), '#C16767', '#FFFFFF', 'uv-very-high'];
				default:
					return [ucwords('extreme'), '#9F0B0B', '#FFFFFF', 'uv-extreme'];
			}
		}
		
		private static function featuresRelatedTempUnit($tempUnit, $temp, $option = true, $msg = '')
		{
			if (!$option) {
				return self::format_str($msg, [round($temp)]);
			}
			if ($tempUnit === 'M' || $tempUnit === 'metric') {
				return self::format_str('{}&deg;C', [round($temp)]);
			}
			if ($tempUnit === 'I' || $tempUnit === 'imperial') {
				return self::format_str('{}&deg;F', [round($temp)]);
			}
			
			return self::format_str('{}K', [round($temp)]);
		}
		
		public function dateFormat($format, $time){
			if (!is_int($time)){
				$time = strtotime($time);
			}
			return date($format, $time);
		}
		
		#===================================
		# Handle Icons
		#===================================
		public function labelIcon($option){
			# /var/www
			$iconPath = JPATH_ROOT . "/modules/mod_ja_weather/set-icons/label-icons/";
			$iconLink = Uri::root() . "modules/mod_ja_weather/set-icons/label-icons/{}";
			
			$feelsLike = self::format_str($iconLink, ['feelsLike.svg']);
			$humidity = self::format_str($iconLink, ['humidity.svg']);
			$dewPoint = self::format_str($iconLink, ['dewPoint.svg']);
			$windSpeed = self::format_str($iconLink, ['windSpeed.svg']);
			$uv = self::format_str($iconLink, ['uv.svg']);
			$pressure = self::format_str($iconLink, ['pressure.svg']);
			$precip = self::format_str($iconLink, ['precip.svg']);
			$rainVolume = self::format_str($iconLink, ['rainVolume.svg']);
			$snowVolume = self::format_str($iconLink, ['snowVolume.svg']);
			$sunrise = self::format_str($iconLink, ['sunrise.svg']);
			$sunset = self::format_str($iconLink, ['sunset.svg']);
			$moonPhase = self::format_str($iconLink, ['moonPhase.svg']);
			$partDay = self::format_str($iconLink, ['dayNight.jpg']);
			
			# width="50" height="50"
			$labelMapping = array(
				'text' => [
					'feelsLike' => 'WEATHER_FEELS_LIKE',
					'humidity' => 'WEATHER_HUMIDITY',
					'dewPoint' => 'WEATHER_DEW_POINT',
					'windSpeed' => 'WEATHER_WIND_SPEED',
					'uv' => 'WEATHER_UV_INDEX',
					'pressure' => 'WEATHER_PRESSURE',
					'precip' => 'WEATHER_PRECIP_PROB',
					'rainVolume' => 'WEATHER_RAIN_VOLUME',
					'snowVolume' => 'WEATHER_SNOW_VOLUME',
					'sunrise' => 'WEATHER_SUNRISE',
					'sunset' => 'WEATHER_SUNSET',
					'moonPhase' => 'WEATHER_MOON_PHASE',
					'partDay' => 'WEATHER_PART_OF_DAY',
				],
				'icon' => [
					'feelsLike' => "<img class='weather-label-icon' src=\"{$feelsLike}\" >",
					'humidity' => "<img class='weather-label-icon' src=\"{$humidity}\" >",
					'dewPoint' => "<img class='weather-label-icon' src=\"{$dewPoint}\" >",
					'windSpeed' => "<img class='weather-label-icon' src=\"{$windSpeed}\" >",
					'uv' => "<img class='weather-label-icon' src=\"{$uv}\" >",
					'pressure' => "<img class='weather-label-icon' src=\"{$pressure}\" >",
					'precip' => "<img class='weather-label-icon' src=\"{$precip}\" >",
					'rainVolume' => "<img class='weather-label-icon' src=\"{$rainVolume}\" >",
					'snowVolume' => "<img class='weather-label-icon' src=\"{$snowVolume}\" >",
					'sunrise' => "<img class='weather-label-icon' src=\"{$sunrise}\" >",
					'sunset' => "<img class='weather-label-icon' src=\"{$sunset}\" >",
					'moonPhase' => "<img class='weather-label-icon' src=\"{$moonPhase}\" >",
					'partDay' => "<img class='weather-label-icon' src=\"{$partDay}\" >",
				]
			);

			if ($option === 1){
				return $labelMapping['icon'];
			}
			return $labelMapping['text'];
		}
		
		public function imgBackground($imgPath){
			return explode('#', $imgPath, 2)[0];
		}
		
		private function moonIcon_($moonCode){
			switch ($moonCode){
				case $moonCode === 0:
				case $moonCode === 1:
					return [ucwords('New moon'), 'new'];
				case .24 <= $moonCode && $moonCode <= .25:
					return [ucwords('First quarter moon'), '1stQuarter'];
				case .47 <= $moonCode && $moonCode <= .52:
					return [ucwords('Full moon'), 'full'];
				case .73 <= $moonCode && $moonCode <= .75:
					return [ucwords('Third quarter moon'), '3rdQuarter'];
				# waxing crescent
				case 0 < $moonCode && $moonCode <= .04:
					return [ucwords('Waxing crescent'), 'waxCres1'];
				case .04 < $moonCode && $moonCode <= .08:
					return [ucwords('Waxing crescent'), 'waxCres2'];
				case .08 < $moonCode && $moonCode <= .12:
					return [ucwords('Waxing crescent'), 'waxCres3'];
				case .12 < $moonCode && $moonCode <= .16:
					return [ucwords('Waxing crescent'), 'waxCres4'];
				case .16 < $moonCode && $moonCode <= .2:
					return [ucwords('Waxing crescent'), 'waxCres5'];
				case .2 < $moonCode && $moonCode <= .24:
					return [ucwords('Waxing crescent'), 'waxCres6'];
				# waxing gibbous
				case .25 < $moonCode && $moonCode <= .286:
					return [ucwords('Waxing gibbous'), 'waxGib1'];
				case .286 < $moonCode && $moonCode <= .323:
					return [ucwords('Waxing gibbous'), 'waxGib2'];
				case .323 < $moonCode && $moonCode <= .36:
					return [ucwords('Waxing gibbous'), 'waxGib3'];
				case .36 < $moonCode && $moonCode <= .396:
					return [ucwords('Waxing gibbous'), 'waxGib4'];
				case .396 < $moonCode && $moonCode <= .433:
					return [ucwords('Waxing gibbous'), 'waxGib5'];
				case .433 < $moonCode && $moonCode <= .47:
					return [ucwords('Waxing gibbous'), 'waxGib6'];
				# waning gibbous
				case .5 < $moonCode && $moonCode <= .536:
					return [ucwords('Waning gibbous'), 'wanGib1'];
				case .536 < $moonCode && $moonCode <= .573:
					return [ucwords('Waning gibbous'), 'wanGib2'];
				case .573 < $moonCode && $moonCode <= .61:
					return [ucwords('Waning gibbous'), 'wanGib3'];
				case .61 < $moonCode && $moonCode <= .647:
					return [ucwords('Waning gibbous'), 'wanGib4'];
				case .647 < $moonCode && $moonCode <= .683:
					return [ucwords('Waning gibbous'), 'wanGib5'];
				case .683 < $moonCode && $moonCode < .73:
					return [ucwords('Waning gibbous'), 'wanGib6'];
				# waning crescent
				case .75 < $moonCode && $moonCode < .791:
					return [ucwords('Waning Crescent'), 'wanCres1'];
				case .791 < $moonCode && $moonCode < .833:
					return [ucwords('Waning Crescent'), 'wanCres2'];
				case .833 < $moonCode && $moonCode < .875:
					return [ucwords('Waning Crescent'), 'wanCres3'];
				case .875 < $moonCode && $moonCode < .917:
					return [ucwords('Waning Crescent'), 'wanCres4'];
				case .917 < $moonCode && $moonCode < .958:
					return [ucwords('Waning Crescent'), 'wanCres5'];
				case .958 < $moonCode && $moonCode < 1:
					return [ucwords('Waning Crescent'), 'wanCres6'];
			}
		}
		
		public function moonIcon($iconFolder, $moonCode){
			$parentFolder = 'set-icons';
			$setMoon = [
				'animated-icons' => 'moon'
			];
			# $iconPath = JPATH_ROOT . "/modules/mod_ja_weather/$parentFolder/$iconFolder/";
			$iconLink = Uri::root() . "modules/mod_ja_weather/$parentFolder/{}/moon/";
			$moonIcon = $this->moonIcon_($moonCode)[1] . '.svg';
			$iconPath = self::format_str($iconLink, [$iconFolder]) . $moonIcon;
			
			$desc = $this->moonIcon_($moonCode)[1];
			$iconDefault = '';
			return [$iconPath, $desc];
			
		}
		
		public function precipIcon($iconFolder){
			$parentFolder = 'set-icons';
			$iconLink = Uri::root() . "modules/mod_ja_weather/$parentFolder/{}/";
			$iconDefault = "u00d.png";
			$precipIcon = $this->checkIconFormat($iconFolder, 'precip') ?? $iconDefault;
			$precipIcon = self::format_str($iconLink, [$iconFolder]) . $precipIcon;
			return $precipIcon;
		}
		
		public function weatherIcon($iconFolder, $code, $icon)
		{
			# use icon from API
			if ($iconFolder === 'static-icons-2'){
				return sprintf(self::$icon_url, $icon);
			}
			$iconsCodeJson = JPATH_ROOT . '/modules/mod_ja_weather/asset/icons-mapping.json';
			$iconsCode = file_get_contents($iconsCodeJson);
			$iconsCode = (array) json_decode($iconsCode);
			
			$parentFolder = 'set-icons';
			$iconPath = JPATH_ROOT . "/modules/mod_ja_weather/$parentFolder/$iconFolder/";
			$iconLink = Uri::root() . "modules/mod_ja_weather/$parentFolder/{}/";
			# $iconDefaultPath = JPATH_ROOT . "/modules/mod_ja_weather/$parentFolder/default";
			# $iconDefault = Uri::root() . "modules/mod_ja_weather/$parentFolder/default/u00d.png";
			$iconDefault = "u00d.png";
			
			$code = (string)$code;
			$iconData = (object)$iconsCode[$code];
			
			if ($iconFolder === 'default' || self::dirIsEmpty($iconPath) || !file_exists($iconPath)){
				$iconFolder = 'default';
				if (strpos($icon, 'd')){
					# $iconDay = self::format_str($iconLink, [$iconFolder]) . $iconData->icon[0] . '.png';
					$iconDay = self::checkIconFormat($iconFolder, $iconData->icon[0]) ?? $iconDefault;
					$iconDay = self::format_str($iconLink, [$iconFolder]) . $iconDay;
					# echo '<img src=" '. $iconDay . '" width="50" height="50">';
					return $iconDay;
				}
				$iconNight = self::checkIconFormat($iconFolder, $iconData->icon[1]) ?? $iconDefault;
				$iconNight = self::format_str($iconLink, [$iconFolder]) . $iconNight;
				return $iconNight;
			}
			if (strpos($icon, 'd')){
				$iconDay = self::checkIconFormat($iconFolder, $iconData->icon[0]) ?? $iconDefault;
				$iconDay = self::format_str($iconLink, [$iconFolder]) . $iconDay;
				return $iconDay;
			}
			$iconNight = self::checkIconFormat($iconFolder, $iconData->icon[0]) ?? $iconDefault;
			$iconNight = self::format_str($iconLink, [$iconFolder]) . $iconNight;
			return $iconNight;
		}
		
		public static function checkIconFormat($iconFolder, $icon){
			$iconFormats = ['.png', '.svg', '.jpg', '.jpeg'];
			$iconPath = JPATH_ROOT . "/modules/mod_ja_weather/set-icons/$iconFolder/";
			/*if (!$moon){
				$iconPath = JPATH_ROOT . "/modules/mod_ja_weather/set-icons/$iconFolder/";
			}else{
				$iconPath = JPATH_ROOT . "/modules/mod_ja_weather/set-icons/$iconFolder/moon/";
			}*/
			foreach ($iconFormats as $key => $format) {
				$icon_ = $icon . $format;
				$iconPath_ = $iconPath . $icon_;
				if (file_exists($iconPath_)){
					return $icon_;
				}
			}
		}
		
		public static function dirIsEmpty($dir){
			/*Folder::folders($dir);
			Folder::files($dir);
			File::getExt($file);*/
			if(!is_readable($dir)){
				return null;
			}
			return (count(scandir($dir)) == 2);
		}
		
		public static function _handleDate($dateFormat, $time){
			# convert time str to timestamp
			if (!is_int($time)){
				$time = strtotime($time);
			}
			$date_ = date($dateFormat, $time);
			$date_ = explode(',', $date_);
			switch (strtolower($date_[0])){
				case 'mon':
					return Text::_('W_FORECAST_MON') . ',' . $date_[1];
				case 'tue':
					return Text::_('W_FORECAST_TUE') . ',' . $date_[1];
				case 'wed':
					return Text::_('W_FORECAST_WED') . ',' . $date_[1];
				case 'thu':
					return Text::_('W_FORECAST_THU') . ',' . $date_[1];
				case 'fri':
					return Text::_('W_FORECAST_FRI') . ',' . $date_[1];
				case 'sat':
					return Text::_('W_FORECAST_SAT') . ',' . $date_[1];
				case 'sun':
					return Text::_('W_FORECAST_SUN') . ',' . $date_[1];
				default:
					return date($dateFormat, $time);
			}
		}
	}
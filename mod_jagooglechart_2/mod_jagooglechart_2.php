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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\ModuleHelper;

//INCLUDING ASSET
require_once(dirname(__FILE__).'/asset/behavior.php');
include_once(dirname(__FILE__).'/asset/asset.php');

if($params->get('chartData', 'csv') == 'googlesheet'){
    $data_input = 'googlesheet';
}else{
    $data_input = $params->get("data_input", '');
}

if (!empty($data_input)) {
	$chart_description = $params->get("chart_description", '');
	//PREPARING CHART OPTIONS
	$options = $params->toArray();
	$width = $params->get("option_width", '100%');
	if(strpos($width, '%') === false) {
		$width = intval($width).'px';
	}
	$height = (int) $params->get("option_height", 600);
	
	// Custom Colors
	if ($params->get('colors_custom','') != '') {
		$customColors = explode(',',$params->get('colors_custom',''));
		if(is_array($customColors)) {
			$customColors = array_map('trim', $customColors);
			$options['option_colors'] = $customColors;
		}
	}
	
	// Custom Scales
	//hAxis custom scales
	if($params->get('hAxis_ticks','') != ''){
		$hAxiscustomScales = explode(',', $params->get('hAxis_ticks',''));
		if(is_array($hAxiscustomScales)) {
			$customScales = array_map('trim', $hAxiscustomScales);
			$options['option_hAxis_ticks'] = $hAxiscustomScales;
		}
  }
	//vAxis custom scales
	if($params->get('vAxis_ticks','') != ''){
		$vAxiscustomScales = explode(',', $params->get('vAxis_ticks',''));
		if(is_array($vAxiscustomScales)) {
			$vAxiscustomScales = array_map('trim', $vAxiscustomScales);
			$options['option_vAxis_ticks'] = $vAxiscustomScales;
		}
  }

	//DRAW CHART
	$funcChart = sprintf('jaDrawChart%d', $module->id);
	$container = 'ja-google-chart-wrapper-'.$module->id;
	
	$chartType = $params->get('chartType','AreaChart');
	$options["chartType"] = $chartType;
	
	// ComboChart Target Line
	if ($chartType === "ComboChart") {
		$targetLine = $params->get('series_targetLine','');
		if(!empty($targetLine)) {
			$options["option_seriesType"] = 'bars';
			$options["option_series_".$targetLine."_type"] = "line";
		}
	}
	
	// PieChart exploding slides.
	if ($chartType === 'PieChart') {
		if($params->get('option_explode','0') == '1'){
			if($params->get('option_slices_explode','') != ''){
				$slices_explode = explode(',',$params->get('option_slices_explode',''));
				foreach($slices_explode as $slice){
					$options["option_slices_".$slice."_offset"] = (float) rand(1,99) / 1000;
				}
			}
		}
	}
	
	// GeoChart ColorAxis settings.
	if ($chartType === 'GeoChart') {
		$options["option_colorAxis_color"] = array($params->get('geo_colorAxis_fromColor', '#FFFFFF'), $params->get('geo_colorAxis_toColor', '#35A339'));
	}
	
	// TrendlineChart Settings
	if ($chartType === "TrendlineChart") {
		$options["chartType"] = "ScatterChart";
	}
	
	// Waterfall CHART
	if($params->get('bar_setting','0') == '1') {
		$options['option_bar_groupWidth'] = '100%';
	}
	
	// accept null value.
	$options['option_interpolateNulls'] = "true";
	
	$js = "
			google.setOnLoadCallback({$funcChart});
			// global chart data variable
			
			function ".$funcChart." () {
				var googleChartData = ".json_encode($options).";
				if (!googleChartData.chartType) return;
				drawGoogleChart(googleChartData, '$container');
			}
		";
	$doc = Factory::getDocument();
	$doc->addScriptDeclaration($js);
	
	require ModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));
} else {
	echo Text::_('JA_GOOGLE_CHART_MISSING_DATA_INPUT');
}


function drawGoogleChart(input, container) {
	var textData = input['chartData'].split('\n');
	var arrData = [];
	var data = new google.visualization.DataTable();
	
	if (input.chartData == 'csv') {
		var textData = input.data_input.trim();
		if (textData.split('\n').length < 2) return;
	
		var arrData = Papa.parse(textData).data;
		renderChart(arrData, input, container);
	} else {
		var data_input_url = input.data_input_url;
		Papa.parse(data_input_url, {
			download: true,
			complete: function(results) {
				var arrData = results.data;
				renderChart(arrData, input, container);
			},
		});
	}
}

function renderChart(arrData, input, container) {
	var cols = arrData.shift();

	arrData = arrData.map(function(item) {
		return item.map(function(i) {
			return isNaN(i) ? i : +i;
		});
	});
	
	var bingo = ['year', 'years', 'Year', 'Years'];
	if (bingo.some(substring => cols.includes(substring))){
		arrData.forEach(function (val, idx) {
			arrData[idx][0] = String(val[0]);
		})
	}

	var data = new google.visualization.DataTable();
	arrData[0].forEach(function(value, idx) {
		var col_year = String(cols[idx]).toLowerCase();
		if (bingo.includes(col_year)){
			data.addColumn('string', cols[idx]);
		}else{
			data.addColumn(isNaN(value) ? 'string' : 'number', cols[idx]);
		}
	});
	
	// Maximum and Minimum line.
	if (input['enable_max_line'] == '1' || input['enable_min_line']) {
		var $type = input.chartType, arrTypes = ['AreaChart', 'LineChart', 'ColumnChart', 'BarChart', 'SteppedAreaChart', 'CandlestickChart'];
		if (jQuery.inArray($type, arrTypes) != -1 && (input['enable_max_line'] == '1' || input['enable_min_line'] == '1')) {
			input.chartType = 'ComboChart';
			var $seriesType = $type.replace(/Chart/,'');
			if ($seriesType == 'Column' || $seriesType == 'Bar') {
				$seriesType = 'Bars';
			}
			input['option_seriesType'] = $seriesType[0].toLowerCase() + $seriesType.slice(1);
			input['option_series'] = {};
		} else {
			if (input['option_series']) {
				delete input['option_series'];
			}
			if (input['option_seriesType']) {
				delete input['option_seriesType'];
			}
		}
	}
	
	if (input['option_series']) {
		var firtRow = [], endrow = [];
		var maxCol = minCol = arrData[0][0]; 
		for (var i=0; i<arrData.length;i++) {
			if (arrData[i][0] > maxCol) {
				maxCol = arrData[i][0];
			}
			if (arrData[i][0] < minCol) {
				minCol = arrData[i][0];
			}
		}
		if (!isNaN(maxCol)) {
			maxCol = maxCol + 1;
		} else {
			maxCol = null;
		}
		if (!isNaN(minCol)) {
			minCol = minCol - 1;
		} else {
			minCol = null;
		}
		for (var j=0; j<arrData[0].length; j++) {
			if (j == 0) {
				firtRow.unshift(minCol);
				endrow.unshift(maxCol);
			} else {
				firtRow.push(null);
				endrow.push(null);
			}
		}
		
		var $lineDashStyle = [6,4];
		if (input['enable_max_line'] == '1' && input['enable_min_line'] != '1') {
			data.addColumn('number', 'Maximum');
			jQuery.each(arrData, function(key,value) {
				value.push(null);
			});
			
			firtRow.push(parseInt(input['max_value']));
			endrow.push(parseInt(input['max_value']));
			arrData.unshift(firtRow);
			arrData.push(endrow);
			
			input['option_series'][arrData[0].length-2] = {type: 'line', color: input['max_color'], lineDashStyle : $lineDashStyle, pointSize : 0, opacity : 0.7}; 
		} else if (input['enable_max_line'] != '1' && input['enable_min_line'] == '1') {
			data.addColumn('number', 'Minimum');
			jQuery.each(arrData, function(key,value) {
				value.push(null);
			});
			firtRow.push(parseInt(input['min_value']));
			endrow.push(parseInt(input['min_value']));
			arrData.unshift(firtRow);
			arrData.push(endrow);
			
			input['option_series'][arrData[0].length-2] = {type: 'line',color: input['min_color'], lineDashStyle : $lineDashStyle, pointSize : 0, opacity : 0.7}; 
		} else if (input['enable_max_line'] == '1' && input['enable_min_line'] == '1') {
			data.addColumn('number', 'Maximum');
			data.addColumn('number', 'Minimum');
			jQuery.each(arrData, function(key,value) {
				value.push(null);
				value.push(null);
			});
			
			firtRow.push(parseInt(input['max_value']));
			firtRow.push(parseInt(input['min_value']));
			endrow.push(parseInt(input['max_value']));
			endrow.push(parseInt(input['min_value']));
			arrData.unshift(firtRow);
			arrData.push(endrow);
			
			input['option_series'][arrData[0].length-3] = {type: 'line',color: input['max_color'], lineDashStyle : $lineDashStyle, pointSize : 0, opacity : 0.7}; 
			input['option_series'][arrData[0].length-2] = {type: 'line',color: input['min_color'], lineDashStyle : $lineDashStyle, pointSize : 0, opacity : 0.7}; 
		} else {
			input.chartType = $('select[name="jform\[params\]\[chartType\]"]').val();
			delete input['option_series'];
			if (input['option_seriesType']) {
				delete input['option_seriesType'];
			}
		}
		input['option_hAxis_viewWindow'] = {min: minCol, max:maxCol};
	} else {
		if (input['option_series']) {
			delete input['option_series']
		}
		if (input['option_seriesType']) {
			delete input['option_seriesType'];
		}
	}
	
	data.addRows (arrData);
	// Create our data table.
	var chartOptions = {};
	// build chart options
	for (var name in input) {
		if (!name.match(/^option_/)) continue;
		var arr = name.split('_');
		// ignore the prefix option_
		arr.shift();
		var propObj = chartOptions;
		for (var i=0; i<arr.length-1; i++) {
			if (propObj[arr[i]] == undefined) propObj[arr[i]] = {};
			propObj = propObj[arr[i]];
		}
		propObj[arr[arr.length-1]] = input[name];
	}

	chartOptions.tooltip = {trigger: 'selection'};
	
	chart = new google.visualization[input.chartType](document.getElementById(container));

	if (input.chartType === 'AreaChart' 
		&& input.option_chartArea_tooltip_trigger === 'selection') {

		chartOptions.tooltip = {trigger: 'selection'};

		if (input.option_chartArea_set_selection === '1') {
			var selections = input.option_chartArea_selections.split('\n');
			
			selections = selections.filter(function(sel) {
				return sel.split(',').length > 1;
			}).map(function(sel) {
				var selection = sel.split(',');
				return {
					row: selection[0] || null,
					column: selection[1] || null,
				}
			});

			google.visualization.events.addListener(chart, 'ready', function(e) {
				chart.setSelection(selections);
			});
		}
	}

	if (input.chartType === 'PieChart' 
		&& input.option_PieChart_tooltip_trigger === 'selection') {

		chartOptions.tooltip = {trigger: 'selection'};
		if (input.option_PieChart_set_selection === '1') {
			var rawSelections = input.option_PieChart_selections.split(',');

			var selections = rawSelections.filter(function(sel) {
				return sel.length && !isNaN(sel);
			}).map(function(sel) {
				return {
					row: +sel,
				};
			});

			google.visualization.events.addListener(chart, 'ready', function(e) {
				chart.setSelection(selections);
			});
		}
	}
	
	chart.draw(data, chartOptions);
}
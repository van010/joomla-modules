// Preview
jQuery(document).ready(function($) {
	var statisticsOverview = {
		init: function() {
			drawChart();
		}
	};
	
	var chart_preview = document.createElement('div');
	chart_preview.id = "ja-googlechart-preview";
	chart_preview.setAttribute('class','chart_preview');
	
	if(parseFloat(jversion) >= 4){
		$('#fieldset-chart_settings').prepend(chart_preview)
	}else{
		$("#myTabContent").prepend(chart_preview);
	}
	
	
	var $inputs = $('#attrib-chart_settings').find('input, textarea, select');
	var $custom_colors = $inputs.filter('.minicolors');
	
	// global chart data variable
	var googleChartData = {};
	googleChartData['option_interpolateNulls'] = true;
	 
	var get_val = function($input) {
		var name = $input.attr('name').match(/\[([^\]]*)\]$/);
		if (name) {
			if (name[1].match(/(_fontName)$/)) {
				if($input.val() === 'sans') {
					$input.val('sans-serif');
				}
			}
			if (name[1] == 'option_explode') {
				if ($input.attr('checked') == 'checked' && $input.val() == '0') {
					$('input[name="jform\[params\]\[option_slices_explode\]"]').val('');
				}
			}
			if (name[1] == 'option_slices_explode') {
				var slices = $input.val().split(',');
				for ( var i = 0; i < slices.length; i++) {
					if ($('input[name="jform\[params\]\[option_explode\]"]:checked').val() == '1'){
						googleChartData['option_slices_'+slices[i]+'_offset'] = Math.random() / 10;
					} else {
						break;
					}
				}
			} 
			if (name[1] == 'chartType' && $input.val() == 'ComboChart') {
				googleChartData['option_seriesType'] = 'bars';
			} else {
				if (googleChartData['option_seriesType']) delete googleChartData['option_seriesType']; 
			}
			
			if (name[1] == 'option_series_targetLine' && $input.val() != '' && $('select[name="jform\[params\]\[chartType\]"]').val() == 'ComboChart') {
				$.each(googleChartData, function(key,value){
					if (key.match(/series_[a-zA-Z0-9]*_type/)) {
						delete googleChartData[key];
					}
				});
				googleChartData['option_series_'+$input.val()+'_type'] = 'line';
			}
			
			if (name[1] == 'hAxis_ticks') {
				if ($input.val() != '') {
					googleChartData['option_hAxis_ticks'] = $input.val().split(',');
				} else {
					if (googleChartData['option_hAxis_ticks']) delete googleChartData['option_hAxis_ticks'];				}
			}
			
			if (name[1] == 'vAxis_ticks') {
				if ($input.val() != '') {
					googleChartData['option_vAxis_ticks'] = $input.val().split(',');
				} else {
					if (googleChartData['option_vAxis_ticks']) delete googleChartData['option_vAxis_ticks'];
				}
				
			}
			
			if (name[1] == 'colors_custom' && $input.val() != '') {
				googleChartData['option_colors'] = $input.val();
			}
			
			if (name[1] == 'bar_setting') {
				if ($input.attr('checked') == 'checked') {
					if($input.val() == '1') {
						googleChartData['option_bar_groupWidth'] = '100%';
					} else {
						if (googleChartData['option_bar_groupWidth']) delete googleChartData['option_bar_groupWidth'];
					}
				}
			}
			
			if ($input.attr('type') == 'radio') {
				if($input.attr('checked') == 'checked') {
					googleChartData[name[1]] = $input.val();
				}
			} else {
				googleChartData[name[1]] = $input.val();
			}
		}
	};
		
	
	$inputs.each(function() {
		get_val($(this));
	});

	// Draw the Chart
	var drawChart = function() {
		if(!googleChartData.chartType) return;
		drawGoogleChart(googleChartData, 'ja-googlechart-preview');
	};
	google.setOnLoadCallback(drawGoogleChart);
	var drawGoogleChart = function(input, container) {
		var $type = $('select[name="jform\[params\]\[chartType\]"]').val(), 
			arrTypes = ['AreaChart', 'LineChart', 'ColumnChart', 'BarChart', 'SteppedAreaChart', 'CandlestickChart'];
		if ($.inArray($type, arrTypes) != -1 && (input['enable_max_line'] == '1' || input['enable_min_line'] == '1')) {
			$('.ja_maxmin_settings').parents('.sub-group').show();
			input.chartType = 'ComboChart';
			var $seriesType = $type.replace(/Chart/,'');
			if ($seriesType == 'Column' || $seriesType == 'Bar') {
				$seriesType = 'Bars';
			}
			input['option_seriesType'] = $seriesType[0].toLowerCase() + $seriesType.slice(1);
			input['option_series'] = {};
		} else {
			//$('.ja_maxmin_settings').parents('.sub-group').hide();
			if (input['option_series']) {
				delete input['option_series'];
			}
			if (input['option_seriesType']) {
				delete input['option_seriesType'];
			}
		}
		
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
	};

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
			}else {
				data.addColumn(isNaN(value) ? 'string' : 'number', cols[idx]);
			}
		});
		
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
				if (typeof maxCol == 'string') {
					maxCol = (maxCol + 1).toString();
				} else {
					maxCol = maxCol + 1;
				}
			} else {
				maxCol = null;
			}
			if (!isNaN(minCol)) {
				if (typeof minCol == 'string') {
					minCol = (minCol - 1).toString();
				} else {
					minCol = minCol - 1;
				}
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
				$.each(arrData, function(key,value) {
					value.push(null);
				});
				
				firtRow.push(parseInt(input['max_value']));
				endrow.push(parseInt(input['max_value']));
				arrData.unshift(firtRow);
				arrData.push(endrow);
				
				input['option_series'][arrData[0].length-2] = {type: 'line', color: input['max_color'], lineDashStyle : $lineDashStyle, pointSize : 0, opacity : 0.7}; 
			} else if (input['enable_max_line'] != '1' && input['enable_min_line'] == '1') {
				data.addColumn('number', 'Minimum');
				$.each(arrData, function(key,value) {
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
				$.each(arrData, function(key,value) {
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
			input.chartType = $('select[name="jform\[params\]\[chartType\]"]').val();
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

		// add fontsize for text axis
		chartOptions.vAxis.textStyle.fontSize = chartOptions.vAxis.textStyle.size;

		if (chartOptions.colors) {
			chartOptions.colors = chartOptions.colors.split(',');
		}
		
		if (input.chartType === 'TrendlineChart') {
			input.chartType = 'ScatterChart';
		}
		
		chart = new google.visualization[input.chartType](document.getElementById(container));
		chart.draw(data, chartOptions);
	}
	
	setTimeout (function() {
		$custom_colors.minicolors('settings', {
	  		change: function(value){	
				$(this).trigger('change');
	  		}
	  	}).prop ('maxlength', 0).prop ('maxlength', 7);
	}, 500);

	// Tracking change
	$inputs.on('change', function() {
		get_val($(this));
		drawChart();
	});

	$(window).on('load',function(){
		// Only display chart preview on the chart settings tab.
		if ($('a[href="#attrib-chart_settings"]').tab('show')) {
			$('#ja-googlechart-preview').css('display','block');
		}
		$('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
			e.target;
			if ($(this).attr('href') != '#attrib-chart_settings') {
				$('#ja-googlechart-preview').css('display','none');
			} else {
				$('#ja-googlechart-preview').css('display','block');
			}
		});
		
		// Draw chart with the current settings.
		setTimeout(function(){
			statisticsOverview.init();
		},10);
		
		// Guide PopUp
		var guide = document.createElement('div');
		guide.setAttribute('class','ja-googlechart-guide');
		var contentGuide = '<div class="ja-ggChart-contentGuide">';
		// Button trigger modal 
		contentGuide += '<button type="button" class="btn btn-info" data-toggle="modal" data-target="#ja-googlechart-guide">'+Joomla.JText._('JA_GOOGLECHART_GUIDE')+'</button>';

		// Modal
		contentGuide += '<div class="modal fade" id="ja-googlechart-guide" tabindex="-1" role="dialog" aria-labelledby="ja-googlechart-guideLabel">';
		contentGuide += '<div class="modal-dialog" role="document">';
		contentGuide += '<div class="modal-content">';
		contentGuide += '<div class="modal-header">';
		contentGuide += '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
		contentGuide += '<h4 class="modal-title" id="ja-googlechart-guidelLabel">How to add data for each chart type ?</h4>';
		contentGuide += '</div>';
		contentGuide += '<div class="modal-body">';
		contentGuide += '<p>JA Goole Chart supports multiple chart types, for each chart types, it has different data formart, access the documentation to get detail instruction:</p>';
		contentGuide += '<p><a class="btn btn-success" href="https://www.joomlart.com/documentation/joomla-module/ja-google-chart#data-format" target="_blank">Data format instruction</a></p>';
		contentGuide += '</div>';
		contentGuide += '<div class="modal-footer">';
		contentGuide += '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
		contentGuide += '</div>';
		contentGuide += '</div>';
		contentGuide += '</div>';
		contentGuide += '</div>';
		
		guide.innerHTML = contentGuide;
		$("#jform_params_chartType_chzn").parent().append(guide);
	});

	function initialize(url) {
		var query = new google.visualization.Query(url);
		query.send(raw);
	}

	function raw(response) {
		var data1 = response.getDataTable();
		var data = new google.visualization.DataTable();
		var Nf = data1.Nf, Mf = data1.Mf, array = [];
		
		var chartOptions = {};
		
		// build chart options
		for (var name in googleChartData) {
			if (!name.match(/^option_/)) continue;
			var arr = name.split('_');
			// ignore the prefix option_
			arr.shift();
			var propObj = chartOptions;
			for (var i=0; i<arr.length-1; i++) {
				if (propObj[arr[i]] == undefined) propObj[arr[i]] = {};
				propObj = propObj[arr[i]];
			}
			propObj[arr[arr.length-1]] = googleChartData[name];
		}
			
		if (chartOptions.colors) {
			chartOptions.colors = chartOptions.colors.split(',');
		}
		
		if (googleChartData.chartType === 'TrendlineChart') {
			googleChartData.chartType = 'ScatterChart';
		}
		
		// Max and Min line 
		var $type = $('select[name="jform\[params\]\[chartType\]"]').val(), 
			arrTypes = ['AreaChart', 'LineChart', 'ColumnChart', 'BarChart', 'SteppedAreaChart', 'CandlestickChart'];
		
		if ($.inArray($type, arrTypes) != -1 && (googleChartData['enable_max_line'] == '1' || googleChartData['enable_min_line'] == '1')) {
			$('.ja_maxmin_settings').parents('.sub-group').show();
			googleChartData.chartType = 'ComboChart';
			var seriesType = $type.replace(/Chart/,'');
			
			if (seriesType == 'Column' || seriesType == 'Bar') {
				seriesType = 'Bars'
			}
			
			chartOptions.seriesType = seriesType[0].toLowerCase()+seriesType.slice(1);
			chartOptions['series'] = {};
		} else {
			//$('.ja_maxmin_settings').parents('.sub-group').hide();
			if (chartOptions.seriesType) delete chartOptions.seriesType;
			if (chartOptions.series) delete chartOptions.series;
		}

		for (var i = 0; i < Mf.length; i++){
			if(Mf[i].label != ''){
				data.addColumn(Mf[i].type, Mf[i].label);
			} else {
				break;
			}
		}
		
		if (data.Mf.length > 0){
			for (var j = 0; j < Nf.length; j++){
				var row = [];
				for (var k = 0; k < Nf[j].c.length; k++) {
					if (Nf[j].c[k].v == null) {
						Nf[j].c[k].v = 0;
					}
					row[k] = Nf[j].c[k].v;
				}
				array.push(row);
			}
			
			if (chartOptions.series) {
				var firtRow = [], endRow = [];
				var maxCol = minCol = array[0][0]; 
				for (var i=0; i<array.length;i++) {
					if (array[i][0] > maxCol) {
						maxCol = array[i][0];
					}
					if (array[i][0] < minCol) {
						minCol = array[i][0];
					}
				}
				
				if (!isNaN(maxCol)) {
					if (typeof maxCol == 'string') {
						maxCol = (maxCol + 1).toString();
					} else {
						maxCol = maxCol + 1;
					}
				} else {
					maxCol = null;
				}
				
				if (!isNaN(minCol)) {
					if (typeof minCol == 'string') {
						minCol = (minCol - 1).toString();
					} else {
						minCol = minCol - 1;
					}
				} else {
					minCol = null;
				}
				for (var j=0; j<array[0].length; j++) {
					if (j == 0) {
						firtRow.unshift(minCol);
						endRow.unshift(maxCol);
					} else {
						firtRow.push(null);
						endRow.push(null);
					}
				}
				
				var lineDashStyle = [6,4];
				if (googleChartData['enable_max_line'] == '1' && googleChartData['enable_min_line'] != '1') {
					data.addColumn('number', 'Maximum');
					$.each(array, function(key, value) {
						value.push(null);
					});
					firtRow.push(parseInt(googleChartData['max_value']));
					endRow.push(parseInt(googleChartData['max_value']));
					array.unshift(firtRow);
					array.push(endRow);
					
					chartOptions.series[array[0].length - 2] = {type: 'line', color: googleChartData['max_color'], lineDashStyle : lineDashStyle, pointSize : 0, opacity : 0.7};
				} else if (googleChartData['enable_max_line'] != '1' && googleChartData['enable_min_line'] == '1') {
					data.addColumn('number', 'Minimum');
					$.each(array, function(key, value) {
						value.push(null);
					});
					firtRow.push(parseInt(googleChartData['min_value']));
					endRow.push(parseInt(googleChartData['min_value']));
					array.unshift(firtRow);
					array.push(endRow);
					
					chartOptions.series[array[0].length - 2] = {type: 'line', color: googleChartData['min_color'], lineDashStyle : lineDashStyle, pointSize : 0, opacity : 0.7};
				} else if (googleChartData['enable_max_line'] == '1' && googleChartData['enable_min_line'] == '1') {
					data.addColumn('number', 'Maximum');
					data.addColumn('number', 'Minimum');
					$.each(array, function(key,value) {
						value.push(null);
						value.push(null);
					});
					
					firtRow.push(parseInt(googleChartData['max_value']));
					firtRow.push(parseInt(googleChartData['min_value']));
					endRow.push(parseInt(googleChartData['max_value']));
					endRow.push(parseInt(googleChartData['min_value']));
					array.unshift(firtRow);
					array.push(endRow);
					
					chartOptions.series[array[0].length-3] = {type: 'line',color: googleChartData['max_color'], lineDashStyle : lineDashStyle, pointSize : 0, opacity : 0.7}; 
					chartOptions.series[array[0].length-2] = {type: 'line',color: googleChartData['min_color'], lineDashStyle : lineDashStyle, pointSize : 0, opacity : 0.7}; 
				}
			} else {
				googleChartData.chartType = $type;
			}
			
			var charts = new google.visualization[googleChartData.chartType](document.getElementById('ja-googlechart-preview'));
			data.addRows(array);
			charts.draw(data,chartOptions);
		} else {
			for (var i=1;i<Nf.length; i++) {
				for (var j=0; j<Nf[i].c.length; j++) {
					if (data1.Nf[i].c[j] == null) {
						data1.Nf[i].c[j] = {v:0};
					}
					
					if (data1.Nf[i].c[j].v == null) {
						data1.Nf[i].c[j].v = 0;
					}
				}
			}
			var charts = new google.visualization[googleChartData.chartType](document.getElementById('ja-googlechart-preview'));
			charts.draw(data1, chartOptions);
		}
	}	
});

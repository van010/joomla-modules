
// Legend
jQuery(document).ready(function($){
	// group index for legend
	var $legends = $('h3.legend'),
		group = 0,
		jClass = 'j3';
	if($('#attrib-chart_settings').length) {
		if(parseFloat(jversion) >= 4){
			jClass = ' j4 j5 ';
			$('#fieldset-chart_settings').addClass('ja-panel');
		}else{
			$('#attrib-chart_settings').addClass('ja-panel');
		}
	}
	
	$('body.admin').addClass(jClass);

	console.log($('body.admin'));
	$legends.each (function () {

		var $legend = $(this),
			$legendGroup = $legend.closest('.control-group'),
			isSub = $legend.is('.sub-legend');
		// add legend class
		$legendGroup.addClass(isSub ? 'sub-legend-group' : 'top-legend-group');
		var	$params = $legendGroup.nextUntil (function() {
				var $next = $(this),
					$nextIsLegend = $next.has('h3.legend').length,
					$nextIsSubLegend = $nextIsLegend && $next.find('h3.legend').is('.sub-legend');
				if (!isSub && $nextIsLegend && $nextIsSubLegend) {
					$next.find('h3.legend').data('top-legend', $legend);
				} 

				return !$next.is('.control-group') || ($nextIsLegend && (isSub || !$nextIsSubLegend));
			});
		// store its legend
		$params.data('legend', $legend);
		$legend.data('params', $params);

		if ($legend.data('expend')) {
			$legend.closest('.control-group').addClass('group-expend');
		} else {
			$legend.closest('.control-group').addClass('group-collapse');
			$legendGroup.data('expend', 0);
		}

	});
	// grouping legend and params
	$('.ja-panel').each(function(){
		var $pane = $(this),
			$topLegends = $pane.find('.top-legend-group');
		const fieldSetting = $pane.find('#fieldset-chart_settings');

		if (fieldSetting.length){
			$('<div />').addClass('group-legends').appendTo(fieldSetting).append($topLegends);
		}else{
			$('<div />').addClass('group-legends').appendTo($pane).append($topLegends);
		}

		$topLegends.find('.controls').hide();
		$pane.find('.top-group').hide();
		$topLegends.each(function(){
			var $legend = $(this).find('h3'),
				$params = $legend.data('params'),
				$subLegends = $params.filter('.sub-legend-group'),
				$topGroup = $('<div />');

			if (fieldSetting.length){
				$topGroup.addClass('top-group').appendTo(fieldSetting);
			}else{
				$topGroup.addClass('top-group').appendTo($pane);
			}

			var $subGroupDirect = $('<div />').addClass('sub-group sub-group-direct').appendTo($topGroup).append(
				$('<div />').addClass('sub-group-inner').append($params)
			);
			$subLegends.each(function() {
				var $subLegendGroup = $(this),
					$subLegend = $subLegendGroup.find('h3'),
					$params = $subLegend.data('params');
				$('<div />').addClass('sub-group').appendTo($topGroup).append(
						$('<div />').addClass('sub-group-inner').append($subLegendGroup).append(
							$('<div />').addClass('sub-group-params').append($params)
						)
					);
			});
			// remove empty group
			if (!$subGroupDirect.find('.sub-group-inner').children().length) $subGroupDirect.remove();
			// add sub-group-direct class to top-group-enabler
			$topGroup.find('.top-group-enabler').closest('.sub-group').addClass('sub-group-direct');
			// store for later use
			$(this).data('top-group', $topGroup);
		});
	});


	// show/hide top group
	var showTopGroup = function ($legendGroup) {
		var $topGroup = $legendGroup.data('top-group'),
			$tabPane = $legendGroup.closest('.ja-panel'),
			$otherLegendGroups = $tabPane.find('.top-legend-group').not($legendGroup),
			$otherTopGroups = $tabPane.find('.top-group').not($topGroup);
		$otherTopGroups.removeClass('active').hide();
		$topGroup.addClass('active').fadeIn();
		$legendGroup.addClass('active');
		$otherLegendGroups.removeClass('active');		
	}

	$('.top-legend-group').on('click', function(){
		showTopGroup($(this));
		if (localStorage) {
			localStorage.setItem('last_active_group','#' + $(this).closest('.ja-panel').attr('id') 
													+ ' .top-legend-group:nth-child(' + ($(this).index() + 1) + ')');
		}
	});

	// last active
	var $lastActiveGroup;
	if (localStorage && localStorage.getItem('last_active_group')) {
		$lastActiveGroup = $(localStorage.getItem('last_active_group'));
	}

	setTimeout(function(){
		$('.ja-panel .top-legend-group:first-child').trigger('click');
		if($lastActiveGroup){
			$lastActiveGroup.trigger('click');
		}
		
	}, 500);


	// show/hide sub group
	var toggleSubGroup = function ($legendGroup) {
		var $groupParams = $legendGroup.next();
		if ($legendGroup.data('expend')) {
			$legendGroup.removeClass('group-expend').addClass('group-collapse').data('expend', 0);
			$legend.data('params').slideUp();
			$groupParams.slideUp();
		} else {
			$groupParams.slideDown();
			$legendGroup.removeClass('group-collapse').addClass('group-expend').data('expend', 1);
		}	
	}

	$('.sub-legend-group').on('click', function(e){
		console.log(e.target);
		// click on enabler
		if ($(e.target).parent().is('fieldset')) return;
		toggleSubGroup($(this));
	});


	// Enabler
	var toggleEnablerGroup = function ($enabler) {
		var enabled = $enabler.find('input:checked').val(),
			isTopEnabler = $enabler.is('.top-group-enabler'),
			$legendGroup = $enabler.closest('.control-group'),
			$groupParams = $legendGroup.next();
			console.log($enabler);
		if (enabled == '1') {
			if (isTopEnabler) {
				$legendGroup.closest('.top-group').find('.sub-group').show();
			}
			$groupParams.show();
		} else {
			if (isTopEnabler) {
				$legendGroup.closest('.top-group').find('.sub-group').slice(1).hide();
			}
			$groupParams.hide();
		}
	}
	$('.group-enabler, .top-group-enabler').on ('click', function(e) {
		toggleEnablerGroup($(this));
		e.stopPropagation();
	});

	// first enabler
	$('.group-enabler, .top-group-enabler').each (function () {
		toggleEnablerGroup ($(this));
	});
});



// tracking change for group
jQuery(document).ready(function($) {
	var $inputs = $('#myTabContent').find('input, textarea, select'),
		tplhelperValue = $('#tplhelper').val(),
		tplhelper = null;
	try {
		tplhelper = JSON.parse (tplhelperValue);
	} catch (e) {

	}

	if (tplhelper === null || typeof tplhelper !== 'object') tplhelper = {};
	// get origin value
	$inputs.each (function() {
		var $input = $(this),
			$val = $input.attr('type') == 'radio' ? $input.closest('fieldset').find('input:checked').val() : $input.val();
		$input.data('org-value', $val);
	});

	//tracking change
	$inputs.on('change', function () {
		var $input = $(this), val = $input.val(),
			groupid = $input.closest('.ja-panel').attr('id').substr(),
			group = groupid.substr(0, 7) == 'attrib-' ? groupid.substr(7) : null; 	
		// track change
		if ($input.data('org-value') != val) {
			var $legend = $input.closest('.control-group').addClass('modified').data('legend');
			if ($legend) {
				$legend.closest('.control-group').addClass('modified');
				// if sub legend, add modified to top legend
				if ($legend.is('.sub-legend')) {
					$legend.data('top-legend').closest('.control-group').addClass('modified');
				}
			}
			
			// add change for legend

			// update change status for hidden tplhelper
			if (group) {
				tplhelper[group] = 1;
				$('#tplhelper').val(JSON.stringify(tplhelper));
			}
		} else {
			var $legend = $input.closest('.control-group').removeClass('modified').data('legend');
			// detect legend changed
			if($legend){
				if ($legend.data('params').filter('.modified').length == 0) {
					$legend.closest('.control-group').removeClass('modified');
					// if sub-legend, check for top-legend
					if ($legend.is('.sub-legend')) {
						if ($legend.data('top-legend').data('params').filter('.modified').length == 0) {
							$legend.data('top-legend').closest('.control-group').removeClass('modified');
						}
					}
				}
			}
			if (group) {
				if ($input.closest('.ja-panel').find('.control-group').filter('.modified').length == 0) {
					// turn off change
					tplhelper[group] = 0;
					$('#tplhelper').val(JSON.stringify(tplhelper));
				}
			}
		}
	})

});

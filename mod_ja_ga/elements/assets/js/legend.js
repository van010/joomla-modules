
// Legend
jQuery(document).ready(function($){
	// group index for legend
	var $legends = $('h3.legend'),
		group = 0;

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
	});

	var j3TabPane = $('.tab-pane');
	var j4TabPane = "[role='tabpanel']";
	var $tabPane = $(j4TabPane).length !== 0 ? $(j4TabPane) : j3TabPane;
	// grouping legend and params
	$tabPane.each(function(){
		var $pane = $(this),
			$topLegends = $pane.find('.top-legend-group');
		var fieldSet = $pane.find("fieldset.options-form").length === 0
			? $pane : $pane.find("fieldset.options-form");
		$('<div />').addClass('group-legends').appendTo(fieldSet).append($topLegends);
		$topLegends.each(function(){
			var $legend = $(this).find('h3'),
				$params = $legend.data('params'),
				$subLegends = $params.filter('.sub-legend-group'),
				$topGroup = $('<div />');

			$topGroup.addClass('top-group').appendTo(fieldSet);
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
		var j3TabPane = $legendGroup.closest('.tab-pane');
		var $topGroup = $legendGroup.data('top-group'),
			$tabPane = j3TabPane.length !== 0
				? j3TabPane : $legendGroup.closest(j4TabPane);
		$otherLegendGroups = $tabPane.find('.top-legend-group').not($legendGroup),
			$otherTopGroups = $tabPane.find('.top-group').not($topGroup);
		if ($otherTopGroups){
			$otherTopGroups.removeClass('active').hide();
		}
		if ($topGroup){
			$topGroup.addClass('active').fadeIn();
		}
		$legendGroup.addClass('active');
		$otherLegendGroups.removeClass('active');

		$(document).trigger('switchLegendGroup', $legendGroup);
	}

	$('.top-legend-group').on('click', function(){
		showTopGroup($(this));
		if (localStorage && $(this).closest('.tab-pane').length !== 0) {
			localStorage.setItem('last_active_group','#' + $(this).closest('.tab-pane').attr('id')
				+ ' .top-legend-group:nth-child(' + ($(this).index() + 1) + ')');
		}else{
			localStorage.setItem('last_active_group','#' + $(this).closest(j4TabPane).attr('id')
				+ ' .top-legend-group:nth-child(' + ($(this).index() + 1) + ')');
		}
	});

	// last active
	var $lastActiveGroup;
	if (localStorage && localStorage.getItem('last_active_group')) {
		$lastActiveGroup = $(localStorage.getItem('last_active_group'));
	}

	setTimeout(function(){
		var $j3TabPane = $('.tab-pane .top-legend-group:first-child');
		var $tabPane = $j3TabPane.length !== 0
			? $j3TabPane
			: $("[role='tabpanel'] .top-legend-group:first-child");
		$tabPane.trigger('click');
		if ($lastActiveGroup){
			$lastActiveGroup.trigger('click');
		}
	}, 500);

	// Enabler
	var toggleEnablerGroup = function ($enabler) {
		var enabled = $enabler.find('input:checked').val(),
			isTopEnabler = $enabler.is('.top-group-enabler'),
			$legendGroup = $enabler.closest('.control-group'),
			$groupParams = $legendGroup.next();
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
	var j4TabPane = "[role='tabpanel']";
	var j3TabContent = $('#myTabContent').find('input, textarea, select');
	var j4TabContent = $('.main-card').find('input, textarea, select');
	if($(j4TabPane)){
		$('joomla-tab-element#attrib-config').addClass('ja jaga-attr-config');
	}
	if ($('div#attrib-config').length != 0){
		$('div#attrib-config').addClass('j3 jaga-attr-config')
	}
	var $inputs = j3TabContent.length !== 0 ? j3TabContent : j4TabContent,
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
			groupid = $input.closest('.tab-pane').length !== 0
				? $input.closest('.tab-pane').attr('id').substr()
				: $input.closest(j4TabPane).attr('id').substr(),
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
				if ($input.closest('.tab-pane').find('.control-group').filter('.modified').length == 0) {
					// turn off change
					tplhelper[group] = 0;
					$('#tplhelper').val(JSON.stringify(tplhelper));
				}
			}
		}
	});
	jQuery('#jform_params_authorize-lbl a, #jform_params_clear_cache-lbl a').on('click', function(event){
		event.preventDefault();
		if (jQuery('body').hasClass('contentpane') === true) {
			// in case is iframe.
			window.parent.location.href = jQuery(this).attr('href');
		} else {
			window.location.href = jQuery(this).attr('href');
		}
	});
});

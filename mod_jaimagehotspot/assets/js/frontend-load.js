
var $ = jQuery;
var conf = spotConfigs;
var jPath = Joomla.getOptions('system.paths');
var rootURL = jPath.root + '/';
var newIds = [];

var moduleId = parseInt(conf.moduleId);
var menuID = parseInt(conf.menuId);
var jadir = jQuery('html').attr('dir');
var pinchDuration = 0;
var activeZfm = parseInt(conf.activeZfm);
var activeZ2CFD = parseInt(conf.activeZ2CFD);
var maxZoom = parseInt(conf.maxZoom);
var mod_img_spot_id_ = `ja-imagesmap${moduleId}`;
var mod_img_spot_id = `#${mod_img_spot_id_}`;
var spot_img_id_ = `ja-hotspot-image-${moduleId}`;
var spot_img_id = `#${spot_img_id_}`;
var dropdown_id = '#cd-dropdown';

var desc = $.parseJSON(data);
var jaihp_settings = {
	hideDelay: conf.hideDelay,
	trigger: conf.trigger,
	multiple: conf.multiple,
	anim: conf.anim
};

$(window).on('load', function () {
	if (activeZfm) {
		addHammerJS(mod_img_spot_id_, maxZoom, activeZ2CFD);
	}
	if (newIds.length > 0) {
		for (let i=0; i<newIds.length; i++){
			if (!newIds[i]) continue ;
			scaleIconFollowScreen(newIds[i].imgId, newIds[i].mod_img_spot_id);
			handleMultipleHappen(newIds[i].mod_img_spot_id);
		}
	}
	scaleIconFollowScreen(spot_img_id_, mod_img_spot_id_);
	// Remove Chosen Select.
	var chosenSelect = $(`${mod_img_spot_id} ${dropdown_id}`);
	if (chosenSelect.hasClass('chzn-done')) {
		chosenSelect.chosen('destroy');
	}
	chosenSelect.jadropdown({
		gutter: 0,
		stack: false
	});

	$(`${mod_img_spot_id} .cd-dropdown ul li`).click(function () {
		var target = $(this).attr('data-value');
		setTimeout(function () {
			WebuiPopovers.show(`${mod_img_spot_id} #${target} .bg`);
		}, 100);
	});

	$(`${mod_img_spot_id} a.point`).each(function () {
		var data = desc[$(this).attr('id').replace('ja-marker-', '')];
		var _e = $(this).find('.bg');
		// around here little messy. we should clean up a bit in future
		// this only work on mobile + popup type = window + type = website.
		if (conf.mobile_link_icon === 'window' && is_mobile_device()
			&& data.content_url && data.content_type === 'website') {
			_e.click(function (event) {
				window.open($(this).parent().data('content_url'), '_blank');
				event.preventDefault();
				return false;
			});
		} else // normal function.
		{
			OverAllData(_e, data, jaihp_settings);
		}

		if (jaihp_settings.trigger === 'hover' && !is_mobile_device()) {
			// add click to the url if not click event.
			_e.click(function (event) {
				if ($(this).parent().data('link') && data.content_type === 'default') {
					window.open($(this).parent().data('link'), '_blank');
					return;
				}
				if ($(this).parent().data('content_url'))
					window.open($(this).parent().data('content_url'), '_blank');
				event.preventDefault();
				return false;
			});
		}
		if (jaihp_settings.trigger === 'sticky') {
			_e.off().unbind().click(function (event) {
				_e.webuiPopover('destroy');
				_e.off().unbind();
				var jaihp_settings2 = jaihp_settings;
				jaihp_settings2.trigger = 'hover';
				OverAllData(_e, data, jaihp_settings2);
				event.preventDefault();
				return false;
			});
		}
	});
})

function handleMultipleHappen(mod_img_spot_id_, ){
	var mod_img_spot_id = `#${mod_img_spot_id_}`;
	// Remove Chosen Select.
	var chosenSelect = $(`${mod_img_spot_id} ${dropdown_id}`);
	if (chosenSelect.hasClass('chzn-done')) {
		chosenSelect.chosen('destroy');
	}
	chosenSelect.jadropdown({
		gutter: 0,
		stack: false
	});

	$(`${mod_img_spot_id} .cd-dropdown ul li`).click(function () {
		var target = $(this).attr('data-value');
		setTimeout(function () {
			WebuiPopovers.show(`${mod_img_spot_id} #${target} .bg`);
		}, 100);
	});

	$(`${mod_img_spot_id} a.point`).each(function () {
		// var data = desc[$(this).attr('id').replace('ja-marker-', '').split('-')[0]];
		var data = desc[$(this).attr('id').replace('ja-marker-', '')];
		var _e = $(this).find('.bg');
		// around here little messy. we should clean up a bit in future
		// this only work on mobile + popup type = window + type = website.
		if (conf.mobile_link_icon === 'window' && is_mobile_device()
			&& data.content_url && data.content_type === 'website') {
			_e.click(function (event) {
				window.open($(this).parent().data('content_url'), '_blank');
				event.preventDefault();
				return false;
			});
		} else // normal function.
		{
			OverAllData(_e, data, jaihp_settings);
		}

		if (jaihp_settings.trigger === 'hover' && !is_mobile_device()) {
			// add click to the url if not click event.
			_e.click(function (event) {
				if ($(this).parent().data('link') && data.content_type === 'default') {
					window.open($(this).parent().data('link'), '_blank');
					return;
				}
				if ($(this).parent().data('content_url'))
					window.open($(this).parent().data('content_url'), '_blank');
				event.preventDefault();
				return false;
			});
		}
		if (jaihp_settings.trigger === 'sticky') {
			_e.off().unbind().click(function (event) {
				_e.webuiPopover('destroy');
				_e.off().unbind();
				var jaihp_settings2 = jaihp_settings;
				jaihp_settings2.trigger = 'hover';
				OverAllData(_e, data, jaihp_settings2);
				event.preventDefault();
				return false;
			});
		}
	});
}

jQuery(window).on('resize', function (event) {
	if (newIds.length > 0) {
		for (let i = 0; i < newIds.length; i++) {
			if (newIds[i]) {
				scaleIconFollowScreen(newIds[i].imgId, newIds[i].mod_img_spot_id);
			}
		}
	}
	scaleIconFollowScreen(spot_img_id_, mod_img_spot_id_);
})

document.addEventListener('DOMContentLoaded', function () {
    reindexModuleId();
})
function reindexModuleId() {
    const hotpotWraps = document.querySelectorAll('div.jai-map-wrap');
    const hotpots_appear_in_page = [];
    hotpotWraps.forEach(function (el, idx) {
        const elId = el.getAttribute('id');
        const elClass = el.getAttribute('class');
        hotpots_appear_in_page[idx] = {elId, elClass};
    })
    const containerScale = document.querySelectorAll('div.jai-map-container-scale');
    if (!containerScale) return;

    // get all duplicate image hotspot module on a page
    hotpots_appear_in_page[3] = {elId: 'ja-imagesmap209', elClass: 'jai-map-wrap'};
    hotpots_appear_in_page[4] = {elId: 'ja-imagesmap211', elClass: 'jai-map-wrap-0'};
    const duplicate_hotpot_module = Object.values(hotpots_appear_in_page.reduce((c, v) => {
        let k = `${v.elId}-${v.elClass}`;
        c[k] = c[k] || [];
        c[k].push(v);
        return c;

    }, {})).reduce(function (c, v) {
        return v.length > 1 ? c.concat(v) : c
    }, []);

    if (!duplicate_hotpot_module) return;

    const unique_dup_arrays = duplicate_hotpot_module.filter((value, index) => {
        const _value = JSON.stringify(value);
        return index === duplicate_hotpot_module.findIndex(obj => {
            return JSON.stringify(obj) === _value;
        });
    });
    for (let i = 0; i < unique_dup_arrays.length; i++) {
        const elClass = unique_dup_arrays[i].elClass;
        const elId = unique_dup_arrays[i].elId;
        const duplicateElements = document.querySelectorAll(`div.${elClass}`);
        duplicateElements.forEach(function (el, idx) {
            const parentEl = el.parentNode.parentNode.parentNode;
            const elId_ = el.getAttribute('id');
            if (elId_ === elId) {
                const mapContainer = el.querySelector('div.jai-map-container-scale');
                if (!mapContainer) return;
				// reindex marker id
                /*const allMarkers = mapContainer.querySelectorAll('a');
                allMarkers.forEach(function (marker, markerIdx) {
                    const markerId = marker.getAttribute('id');
                    marker.setAttribute('id', markerId + '-' + idx);
                });*/
                const imgPresent = mapContainer.querySelector('img');
				const new_img_id = imgPresent.getAttribute('id') + '-' + idx;
				const new_mod_img_spot_id = el.getAttribute('id') + '-' + idx;
                imgPresent.setAttribute('id', new_img_id);
                el.setAttribute('id', new_mod_img_spot_id);
				newIds[idx] = {
					imgId: new_img_id,
					mod_img_spot_id: new_mod_img_spot_id
				};
                parentEl.setAttribute('id', parentEl.getAttribute('id') + '-' + idx);
            }
        })
    }
}
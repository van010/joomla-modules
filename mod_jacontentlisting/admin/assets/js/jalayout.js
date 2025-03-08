jQuery(document).ready(function($) {
	var originalSubmit = Joomla.submitbutton;
	if ($('#module-form').length) $('#module-form').addClass('ja-backend-style');
	if ($('#item-form').length) $('#item-form').addClass('ja-backend-style');
	
	var updateSpacer = function(){
		if ($(document).find('span.spacer').length){
			$(document).find('span.spacer').closest('.control-label').addClass('spacer');
			$(document).find('span.spacer').closest('.control-group').addClass('field-spacer');
		}
	}
	var data = window.jacontentlisting || {};
	//init layout
	// $('.item_count').closest('.control-group').insertAfter($('#jaform_jalayout_settings_layout').closest('.control-group'));
	var params = {};
	params.jalayout = (data.jalayout && data.jalayout != null) ? JSON.parse(data.jalayout) : {};
	params.jaitem = data.jaitem ? JSON.parse(data.jaitem) : {};
	params.jadetail = data.jadetail ? JSON.parse(data.jadetail) : {};
	params.jaitem_featured = data.jaitem_featured ? JSON.parse(data.jaitem_featured) : {};
	params.jasource = data.jasource ? JSON.parse(data.jasource) : {};
	if (!params.jalayout['layout']) params.jalayout['layout'] = "layout-01";
	if (!params.jaitem['layout']) params.jaitem['layout'] = "default";
	if (!params.jaitem_featured['layout']) params.jaitem_featured['layout'] = "default";
	if (!params.jasource['sources']) params.jasource['sources'] = "content";
	
	Joomla.submitbutton = function(task,type) {
		if(task == 'item.setType') {
			originalSubmit(task,type);
			return true;
		}
		if ((task != 'module.cancel' || task != 'item.cancel') && document.formvalidator.isValid(document.adminForm)) {
			//save pointer setting
			jaSave("jasource-settings");
			jaSave("jalayout-settings", );
			jaSave("jaitem-settings");
			jaSave("jaitem-featured-settings");
			originalSubmit(task,type);
		} else if (task == 'module.cancel' || task == 'item.cancel') {
			Joomla.submitform(task, document.adminForm);
		}else {
			if($('.jasource').find('.easyblog_no_cat').length){
				$('.jasource').find('.easyblog_no_cat').show();
			}
			return false;
		}

	};
	var escapeHtml = function (str) {
		if(!str) return "";
	  return str.replace(/&/g, "JAamp;").replace(/</g, "JAlt;").replace(/>/g, "JAgt;").replace(/"/g, "JAquot;").replace(/'/g, "JA#039;");
	}
	var deEscapeHtml = function (str) {
		if(!str) return "";
    return str.replace(/JAamp;/g, "&").replace(/JAlt;/g, "<").replace(/JAgt;/g, ">").replace(/JAquot;/g, "\"").replace(/JA#039;/g, "'");
	}
	var showonFunc = function(){
		$(document).find('[showon]').each(function(e){
			var depend = JSON.parse($(this).attr('showon'));
			var that = this;
			var inputDef = $('input[name="'+depend.field+'"]:checked').val();
			if(inputDef != depend.value){
				$(this).hide();
			}
			$('input[name="'+depend.field+'"]').on('change',function(){
				console.log(this);
				if($(this).val() == depend.value){
					$(that).show();
				}else{
					$(that).hide();
				}
			});
		});
	}
	var initRadio = function(){
		$(document).find('.btn-group label').each(function(){
			let label = $(this),input = $('#' + label.attr('for'));
			if(input.prop('checked')){
				label.closest('.btn-group').find("label").removeClass('active btn-success btn-danger btn-primary');
				if(input.val() == 1){
					label.addClass('active btn-success');
				}else{
					label.addClass('active btn-danger');
				}
			}
			label.off('click').on('click',function(){
				label.closest('.btn-group').find("label").removeClass('active btn-success btn-danger btn-primary');
				if(input.val() == 1){
					label.addClass('active btn-success');
				}else{
					label.addClass('active btn-danger');
				}
			});
			input.trigger('change');
		});
	}
	// event show hide feature field;
	var featured_event = function(val) {
		initFeatured(parseInt(val));
		if (parseInt(val)) {
			$('[name^="jftform["]').each(function(idx, input) {
				$(input).closest('.control-group').slideDown();
				var introtext_show = $('input[name="jftform[jaitem-settings][show_introtext]"]:checked').val();
				if (!parseInt(introtext_show)) {
					$('#jftform_jaitem_settings_introtext_limit').closest('.control-group').hide();
				}
				var show_date = $('input[name="jftform[jaitem-settings][show_date]"]:checked').val();
				if (!parseInt(show_date)) {
					$('#jftform_jaitem_settings_show_date_field').closest('.control-group').hide();
					$('#jftform_jaitem_settings_show_date_format').closest('.control-group').hide();
				}
			});
		} else {
			$('[name^="jftform["]').each(function(idx, input) {
				$(input).closest('.control-group').slideUp();
			});
		}
	}
	// var check add class active to radio
	var initFeatured = function(val){
		$(document).find('#jform_params_jaitem_featured_enabled label').each(function(){
			var label = $(this);
				var input = $('#' + label.attr('for'));

				label.closest('.btn-group').find("label").removeClass('active btn-success btn-danger btn-primary');
				if (val == 0) {
					label.addClass('active btn-danger');
				} else {
					label.addClass('active btn-success');
				}
		});
	}
	// check and render select option chose path intro image on item/post
	var checkImagePathField = function(){
		var jaSource = $('#jaform_jasource_settings_sources').val();
		var output = [],selectValues = {};
		switch (jaSource) {
			case "k2":
				selectValues = { "intro": data.lang.MOD_JACONTENTLISTING_ITEM_MEDIA_PATH_MEDIA_FIELD_IMG, "first_img": data.lang.MOD_JACONTENTLISTING_ITEM_MEDIA_PATH_FIRST_IMG };
				break;
			case "easyblog":
				selectValues = { "intro": data.lang.MOD_JACONTENTLISTING_ITEM_MEDIA_PATH_POST_COVER_IMG, "first_img": data.lang.MOD_JACONTENTLISTING_ITEM_MEDIA_PATH_FIRST_IMG };
				
				break;
			default:
				selectValues = { "intro": data.lang.MOD_JACONTENTLISTING_ITEM_MEDIA_PATH_INTRO_IMG,"full": data.lang.MOD_JACONTENTLISTING_ITEM_MEDIA_PATH_FULL_IMG, "first_img": data.lang.MOD_JACONTENTLISTING_ITEM_MEDIA_PATH_FIRST_IMG };
				break;
		}
		$.each(selectValues, function(key, value)
		{
		  output.push('<option value="'+ key +'">'+ value +'</option>');
		});
		//default Item config
		$('#jaform_jaitem_settings_item_media_path').html(output.join(''));
		$('#jaform_jaitem_settings_item_media_path').trigger('liszt:updated');
		//featured Item config
		$('#jftform_jaitem_settings_item_media_path').html(output.join(''));
		$('#jftform_jaitem_settings_item_media_path').trigger('liszt:updated');
	}
	var jaSave = function($type) {
		// input event
		var data = {};
		var $fields = '[name^="jaform[' + $type + '"]';
		if ($type == 'jaitem-featured-settings') {
			$fields = '[name^="jftform[jaitem-settings"]';
		}
		$($fields).each(function(j, field) {
			var input_type = $(this).prop("type");
			var field_name_arr = field.name.replace(/]/g, "").split("[");
			var field_name = field_name_arr[field_name_arr.length - 1];
			if (!field_name) {
				field_name = field_name_arr[field_name_arr.length - 2];
			}
			switch (input_type) {
				case "radio":
				case "checkbox":
					if ($(this).prop("checked")) {
						data[field_name] = field.value;
					}
					break;
				case "textarea":
					data[field_name] = escapeHtml($(this).val());
					break;
				case "select-multiple":
					data[field_name] = $(this).val();
					break;
					break;
				case "button":
					break;
				default:
					data[field_name] = field.value;
					break;
			}
		});
		$("#jform_params_" + $type.replace(/-/g, "_")).val(JSON.stringify(data));
	};
	var category_highlight = function(){
		var jaSource = $('#jaform_jasource_settings_sources').val();
		if(jaSource != 'content'){
			$('#jaform_jalayout_settings_show_cat_highlight').closest('.control-group').hide();
			$('#jaform_jalayout_settings_show_cat_parent').closest('.control-group').hide();
			$('button[aria-controls="attrib-jaitem_filtering"]:first').hide();
		}else{
			$('#jaform_jalayout_settings_show_cat_highlight').closest('.control-group').show();
			if($('#jaform_jalayout_settings_show_cat_highlight').val()){
				$('#jaform_jalayout_settings_show_cat_parent').closest('.control-group').show();
			}
			$('button[aria-controls="attrib-jaitem_filtering"]:first').show();
		}
		var jaCats = $('#jaform_jasource_settings_catsid').val();
		var options = "";
		if(jaCats && jaCats.length){
			jaCats.forEach(function(val){
				options += "<option value='"+val+"'>"+$('#jaform_jasource_settings_catsid option[value="'+val+'"]' ).text().replace(/(-)|(-\s+)/g,'')+"</option>>"
			});
			if(options){
				$('#jaform_jalayout_settings_show_cat_parent').html(options);
				$('#jaform_jalayout_settings_show_cat_parent').trigger('liszt:updated');
				if(params.jalayout.show_cat_parent) $('#jaform_jalayout_settings_show_cat_parent').val(params.jalayout.show_cat_parent).trigger('liszt:updated');
			}
		}
	}
	
	updateSpacer();
	checkImagePathField();
	// input event
	$('[name^="jaform["],[name^="jftform["]').each(function(j, field) {
		var field_name_arr = field.name.replace(/]/g, "").split("[");
		var formPrefix = field_name_arr[0];
		var typeSetting = field_name_arr[1];
		if (formPrefix == "jftform") {
			typeSetting = 'jaitem-featured-settings';
		}
		var field_name = field_name_arr[field_name_arr.length - 1];
		var type = typeSetting.replace("-settings", "").replace(/-/g, "_");
		if (!field_name) {
			field_name = field_name_arr[field_name_arr.length - 2];
		}
		var input_type = $(this).prop("type");
		switch (input_type) {
			case "radio":
			case "checkbox":
				if (params[type][field_name] == field.value) {
					$(this).prop("checked", true);
					var label = $('[for="'+$(this).attr('id')+'"]');
					if(field.value == 0){
						label.addClass('active btn-danger');
					}else if (field.value == 1) {
						label.addClass('active btn-success');
					}else {
						label.addClass('active btn-primary');
					}

				}
				break;
			case "textarea":
					if(params[type][field_name]) $(this).val(deEscapeHtml(params[type][field_name]));
					break;
			case "button":
				break;
			default:
				if (params[type][field_name]) $(this).val(params[type][field_name]);
				break;
		}
		$(this).trigger('change');
		$(this).on("change", function(e) {
			jaSave(typeSetting);
		});
	});
	$(document).on("change", "#jaform_jalayout_settings_layout,#jaform_jaitem_settings_layout,#jftform_jaitem_settings_layout,#jaform_jadetail_settings_layout", function(e) {
		var idArr = this.id.split("_");
		$("#attrib-layout_settings").addClass('jaloading');
		var url = jacontentlisting['ajaxUrl'];
		var jaType = idArr[1];
		var formcheck = idArr[0];
		if (formcheck == 'jftform') jaType = 'item_featured';
		url += "&method=loadlayout";
		var data = {};
		data.type = $(this).val();
		data.jatype = jaType;
		fetch(url, {
			method: 'POST', // *GET, POST, PUT, DELETE, etc.
			mode: 'cors', // no-cors, *cors, same-origin
			cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
			credentials: 'same-origin', // include, *same-origin, omit
			headers: {
				'Content-Type': 'application/json'
				// 'Content-Type': 'application/x-www-form-urlencoded',
			},
			redirect: 'follow', // manual, *follow, error
			referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
			body: JSON.stringify(data)
		}).then(res => {
			return res.json();
		}).then(data => {
			if (jaType == 'jaitem') jaType = 'item';
			if (jaType == 'jadetail') jaType = 'detail';
			if (jaType == 'jalayout') jaType = 'layout';
			if (data.data && data.success) {
				if (!$(".extra-ja" + jaType).length) {
					$("<div class='extra-ja" + jaType + "'>").insertBefore($(".control-group.ja" + jaType + ".hide"));
				}
				$(".extra-ja" + jaType).html(data.data);
				checkImagePathField();
			} else {
				$(".extra-ja" + jaType).empty();
			}
			$("#attrib-layout_settings").removeClass('jaloading');
			$(".extra-ja" + jaType).find('.radio.btn-group label').addClass('btn');
			// $(".extra-ja" + jaType).find('select').chosen();
			$(".extra-ja" + jaType).find('select').trigger('liszt:updated');
			$(".extra-ja" + jaType).find('select').trigger('chosen:updated');
			// $(".extra-ja" + jaType).find(".btn-group label").each(function() {
			// 	var label = $(this);
			// 	var input = $('#' + label.attr('for'));

			// 	if (!input.prop('checked')) {
			// 		label.closest('.btn-group').find("label").removeClass('active btn-success btn-danger btn-primary');
			// 		if (input.val() == '') {
			// 			label.addClass('active btn-primary');
			// 		} else if (input.val() == 0 || input.val().toLowerCase() == 'false' || input.val().toLowerCase() == 'no') {
			// 			label.addClass('active btn-danger');
			// 		} else {
			// 			label.addClass('active btn-success');
			// 		}
			// 		input.prop('checked', true);
			// 		input.trigger('change');
					
			// 	}
			// });
			initRadio();
			updateSpacer();
			showonFunc();
		});
	});
	$(document).on('change','#jaform_jasource_settings_sources',function(e){
		$("#general").addClass('jaloading');
		var url = jacontentlisting['ajaxUrl'];
			url += "&method=loadsource";
		var data = {},jaType = 'jasource';
		var source_type = $(this).val();
		data.type = source_type;
		data.jatype = jaType;
		fetch(url, {
			method: 'POST', // *GET, POST, PUT, DELETE, etc.
			mode: 'cors', // no-cors, *cors, same-origin
			cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
			credentials: 'same-origin', // include, *same-origin, omit
			headers: {
				'Content-Type': 'application/json'
				// 'Content-Type': 'application/x-www-form-urlencoded',
			},
			redirect: 'follow', // manual, *follow, error
			referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
			body: JSON.stringify(data)
		}).then(res => {
			return res.json();
		}).then(data => {
			if (data.data && data.success) {
				$(".jasource").html(data.data);
				checkImagePathField();
			}
			$("#general").removeClass('jaloading');
			$(".jasource").find('.radio.btn-group label').addClass('btn');
			// $('.jasource').find('select').chosen();
			$('.jasource').find('select').trigger('liszt:updated');
			$('.jasource').find('select').trigger('chosen:updated');
			// $(".jasource").find(".btn-group label").each(function() {
			// 	var label = $(this);
			// 	var input = $('#' + label.attr('for'));

			// 	if (!input.prop('checked')) {
			// 		label.closest('.btn-group').find("label").removeClass('active btn-success btn-danger btn-primary');
			// 		if (input.val() == '') {
			// 			label.addClass('active btn-primary');
			// 		} else if (input.val() == 0 || input.val().toLowerCase() == 'false' || input.val().toLowerCase() == 'no') {
			// 			label.addClass('active btn-danger');
			// 		} else {
			// 			label.addClass('active btn-success');
			// 		}
			// 		input.prop('checked', true);
			// 		input.trigger('change');
			// 	}
			// });
			initRadio();
			updateSpacer();
			showonFunc();
			category_highlight();
		});
	});
	$(document).on('change', 'input[name="jform[params][jaitem_featured_enabled]"]', function(e) {
		featured_event(parseInt($(this).val()));
	});
	//change category 
	$(document).on('change','#jaform_jasource_settings_catsid',function(){
		category_highlight();
	});
	initRadio();
	var featured_show = $('input[name="jform[params][jaitem_featured_enabled]"]:checked').val();
	featured_event(parseInt(featured_show));
	category_highlight();
});
/**
 * ------------------------------------------------------------------------
 * JA K2 Filter Module for J25 & J3.4
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */
function jak2DisplayExtraFields (moduleid, obj, selected_group) {
	var group = obj.attr('rel') ? obj.attr('rel') : '';
	var value = obj.val();
	var parent = obj.parents('.ja-k2filter');
	var parentid = parent.attr('id');
	
	jQuery('#'+parentid+' .exfield').each(function(){
		var item = jQuery(this);
		var magicid = jQuery('#m'+item.attr('id'));
		if(magicid.length){
			if(item.hasClass('opened')) {
				item.removeClass('opened');
				item.addClass('closed');
				magicid.hide();
			}
		}
	});

	if((value != 0 && group != '') || selected_group) {
		if(group == '') {
			group = selected_group;
		}
		jQuery('#'+parentid).find('.heading-group').each(function() {
			var item = jQuery(this);
			if(item.hasClass('heading-group-'+group)) {
				item.removeClass('ui-accordion-disabled ui-state-disabled');
				if(!item.hasClass('ui-state-active')) {
					var accor = jQuery('#ja-extra-field-accordion-'+moduleid);
					accor.accordion('activate', item);
				}
			} else {
				//clear value of extra fields in group that not associated with selected category
				item.addClass('ui-accordion-disabled ui-state-disabled');
				jaK2Reset(moduleid, item.next('.ui-accordion-content'), false);
			}
		});
	} else {
		jQuery('#'+parentid).find('.heading-group').removeClass('ui-accordion-disabled ui-state-disabled');
	}
}

function jaK2Reset(moduleId, container, submitform)
{
	//var form = jQuery('#'+formId);
	if(typeof(container) == 'string') {
		container = jQuery('#'+container);
	}
	//reset input
	container.find('input[type=text], textarea').val('');

	//radio, checkbox
	container.find(':checked').each(function(){
		jQuery(this).removeAttr('checked');
	});

	//reset magic select
	container.find('.ja-magic-select ul li').each(function()
	{
		jQuery(this).removeClass('selected');
	});
	container.find('.ja-magic-select-container').each(function()
	{
		jQuery(this).html('');
	});

	// reset depend filter.
	container.find('select').each(function() {
		var item = jQuery(this);
		var multiple = item.attr('multiple');
		var fval = '';
		if (!multiple) {
			fval = item.context.options[0].value;
		}
		item.val(fval).trigger("liszt:updated");
	});
	
	container.find('input.jak2dependarray, input.jak2dependtxt').val('');

	//reset range slider
	container.find('[name$="_jacheck"]').each(function() {
		var el = jQuery(this);
		var sliders = jQuery('#slider_'+el.attr('id').replace('_jacheck', ''));
		if(sliders) {
			var val = this.value.split('|');
			sliders.slider('values', 0, val[0]);
			sliders.slider('values', 1, val[1]);
		}
	});

	//hide custom date ranger box
	container.find('#ja-custom-daterange-'+moduleId).hide();

	//remove order subclass
	container.find('.fssorts').remove();

	//submit form?
	if(submitform) {
		if(container.prop('tagName').toLowerCase() != 'form') {
			var form = container.parents('form');
		} else {
			var form = container;
		}
		var autofilter = form.find('[name="btnSubmit"]').length;
		if(!autofilter) {
			if(typeof(form.submit) == 'function') {
				form.trigger('filter.submit');
			}
		}
	}
}

function jaMagicInit(lid, fid) {

	jQuery('#'+lid+' li').each(function(idx, item){
		if(jQuery(item).hasClass('selected')) {
			jaMagicAddElement(lid, fid, jQuery(item).html(), jQuery(item).attr('rel'));
		}
	});

	jQuery('#'+lid+' li.active').each(function(idx, item){
		jQuery(item).on('click', function() {
			var elm = jQuery(this);
			var id = elm.attr('rel');
			if(!id) return;
			
			if(elm.hasClass('selected')) {
				elm.removeClass('selected');
				jQuery('#'+lid+'-'+id).remove();
			} else {
				elm.addClass('selected');
				jaMagicAddElement(lid, fid, elm.html(), id);
			}
			var autofilter = jQuery('#'+lid).attr('data-autofilter');
			if(autofilter == 1) {
				jQuery('#'+lid).parents('form').trigger('filter.submit');
			}
		});
		
	});
}

function jaMagicAddElement(lid, fid, label, id) {
	var container = jQuery('#'+lid+'-container');
	var elm = [
		'<span id="'+lid+'-'+id+'">',
			label + '<input type="hidden" name="'+fid+'[]" value="'+id+'" />',
		'</span>'
	].join('');
	
	var elmRemove = '<span rel="'+id+'" class="remove" title="Remove"></span>';

	elm = jQuery(elm);
	elmRemove = jQuery(elmRemove);

	elmRemove.on('click', function() {
		var lid = jQuery(this).parent().attr('id').replace(/^((?:[a-z0-9_]+\-){2}[a-z0-9_]*).*/, '$1');
		jQuery('#'+lid+' li[rel="'+jQuery(this).attr('rel')+'"]').removeClass('selected');
		jQuery(this).parent().remove();
		var autofilter = jQuery('#'+lid).attr('data-autofilter');
		if(autofilter == 1) {
			jQuery('#'+lid).parents('form').trigger('filter.submit');
		}
	});

	elm.append(elmRemove);
	container.append(elm);
}

function jaMagicSelect(controller, lid) {
	controller = jQuery(controller);
	if(controller.hasClass('opened')) {
		controller.removeClass('opened');
		controller.addClass('closed');
		jQuery('#'+lid).hide();
	} else {
		controller.removeClass('closed');
		controller.addClass('opened');
		jQuery('#'+lid).show();
	}
}
function jaMagicSelectClose(controller, lid) {
	controller = jQuery(controller);
	var controllerparent = jQuery('#'+lid).parent().find('.select');
	if(controllerparent.hasClass('opened')) {
		controllerparent.removeClass('opened');
		controllerparent.addClass('closed');
	} else {
		controllerparent.removeClass('closed');
		controllerparent.addClass('opened');
	}
	jQuery('#'+lid).hide();	
}

function jak2AjaxSubmit(form, K2SitePath, cache) {
	var formid = jQuery(form).attr('id');
	//if Container K2 does not exist, submit form to redirect to K2 Filter result page
	if(jQuery('#k2Container').length) {
		jak2AjaxStart();
		var data = jQuery(form).serialize();
		if (cache[data]) {
			jak2AjaxHandle(cache[data], K2SitePath, cache, formid);
		} else {
			jQuery.ajax({
				type: "POST",
				url: jQuery(form).attr('action'),
				data: data,
				success: function(text){
					cache[data] = text;
					jak2AjaxHandle(text, K2SitePath, cache, formid);
				}
			});
		}
	} else {
		jQuery(form).find('input[name="tmpl"]').val('');
		jQuery(form).submit();
	}
}

function jak2AjaxStart() {
	if(!jQuery('#jak2-loading').length) {
		jQuery('body').append('<div id="jak2-loading">Loading</div>');
	}
	jQuery('#jak2-loading').css({'display': 'block'});
}

function jak2GetUrlSharing(form, updateLocation){
	var params = jQuery(form).serialize();
	params = params.replace('task=search&', 'task=shareurl&');
	params = params.replace('&tmpl=component', '');
	jQuery.ajax({
		type: "POST",
		url: jQuery(form).attr('action'),
		data: params,
		success: function(shareurl){
			if (updateLocation) {
				history.replaceState({}, null, shareurl);
			} else {
				jQuery(form).find('.jak2shareurl a').attr('href', shareurl);
			}
		}
	});
}

function jak2AjaxPagination(container, K2SitePath, cache) {
	var pages = container.find('ul.pagination-list li a');
	if(!pages.length) {
		pages = container.find('.k2Pagination ul li a');
	}
	pages.each(function(){
		jQuery(this).click(function(event) {
			event.preventDefault();
			jak2AjaxStart();
			var url = jQuery(this).attr('href');
			if (!url.match(/tmpl=component/)) {
				url = url + '&tmpl=component';
			}

			var prop = url.replace('&tmpl=component', '');
			if (cache[prop]) {
				jak2AjaxHandle(cache[prop], K2SitePath, cache);	
			} else {
				jQuery.ajax({
					type: "GET",
					url: url,
					success: function(text){
						cache[prop] = text;
						jak2AjaxHandle(text, K2SitePath, cache);
					}
				});
			}
			return false;
		});
	});
}

function jak2Highlight(container, searchword) {
	if(typeof(jQuery.fn.highlight) == 'function' && searchword != undefined) {
		searchword = searchword.replace(/[<>#\\]/, '');
		//remove excluded words
		searchword = searchword.replace(/\-\s*(intitle\:|intext\:|inmetadata\:|inmedia\:|inall\:)?\s*("[^"]"|[^\s]+)/g,'');
		//remove special keywords
		searchword = searchword.replace(/(intitle\:|intext\:|inmetadata\:|inmedia\:|inall\:)/g,'');

		var pattern = /(?:"[^"]+"|[^\s]+)/gi;
		var matches = searchword.match(pattern);
		if(matches) {
			for(i=0; i<matches.length; i++) {
				var word = matches[i].replace(/"/g, '');
				if(word != '' && word != 'OR') {
					container.highlight(word);
				}
			}
		}
	}
}
function jak2AjaxHandle(text, K2SitePath, cache, formid) {
	var container = jQuery('#k2Container');
	var message_container = jQuery('#system-message-container');
	var html = jQuery('<div>' + text + '</div>');
	var content = html.find('#k2Container');
	var content_message = html.find('#system-message-container');

	// update module counter by ajax.
	if (formid) {
		var moduleContent = html.find('#ja-module-content').text();
		if (moduleContent) {
			var moduleHtml = '<div>' + JSON.parse(moduleContent).content + '</div>';
			var $form = jQuery('#' + formid);
			var $resultForm = jQuery(moduleHtml).find('#'+formid);
			var $resultRadio = $resultForm.find('input[type=radio].exfield');
			var $resultCheckbox = $resultForm.find('input[type=checkbox].exfield');
			var $resultInputs = $resultRadio.add($resultCheckbox);

			$resultInputs.each(function(idx, elm) {
				var $input = jQuery(elm);
				var id = $input.attr('id');
				var $inputText = $input.siblings('span.input-text');
				var text = $inputText.text();
				
				$form.find('input#' + id).siblings('span.input-text').text(text);
			});

			var $resultMagicItems = $resultForm.find('ul li.magic-item');
			
			$resultMagicItems.each(function(idx, elm) {
				var $elm = jQuery(elm);
				var id = $elm.attr('id');
				var text = $elm.text();

				$form.find('ul li#'+id).text(text);
			});

			var $resultSelects = $resultForm.find('select.select-filter');

			$resultSelects.each(function(idx, elm) {
				var $elm = jQuery(elm);
				var id = $elm.attr('id');
				var $resultOptions = $elm.find('option');
				var $select = $form.find('select#' + id);

				$resultOptions.each(function(i, opt) {
					var $opt = jQuery(opt);
					var rVal = $opt.val();
					var text = $opt.text();
					var $option = $select.find('option[value="'+rVal+'"]');

					if ($option.length) {
						$option.text(text);
					}
				});

				$select.trigger('liszt:updated');
			});
		}
	}

	if(content.length) {
	    // update item list content
		container.html(content.html());
		// update system message if exists
		message_container.html(content_message.html());
		//paging
		jak2AjaxPagination(container, K2SitePath, cache);

		//rating
		container.find('.itemRatingForm a').click(function(event){
			event.preventDefault();
			var itemID = jQuery(this).attr('data-id');
			var log = jQuery('#itemRatingLog' + itemID).empty().addClass('formLogLoading');
			var rating = jQuery(this).html();
			jQuery.ajax({
				url: K2SitePath+"index.php?option=com_k2&view=item&task=vote&format=raw&user_rating=" + rating + "&itemID=" + itemID,
				type: 'get',
				success: function(response){
					log.removeClass('formLogLoading');
					log.html(response);
					jQuery.ajax({
						url: K2SitePath+"index.php?option=com_k2&view=item&task=getVotesPercentage&format=raw&itemID=" + itemID,
						type: 'get',
						success: function(percentage){
							jQuery('#itemCurrentRating' + itemID).css('width', percentage + "%");
							setTimeout(function(){
								jQuery.ajax({
									url: K2SitePath+"index.php?option=com_k2&view=item&task=getVotesNum&format=raw&itemID=" + itemID,
									type: 'get',
									success: function(response){
										log.html(response);
									}
								});
							}, 2000);
						}
					});
				}
			});
		});

		//highlight search team in result
		jak2Highlight(container, jQuery('.ja-k2filter input[name="searchword"]').val());
	} else {
		container.html('No Item found!');
	}
	jQuery('#jak2-loading').css({'display': 'none'});
	jQuery('html, body').animate({scrollTop: container.offset().top});
}

function jaK2ShowDaterange(obj, range) {
	if(jQuery(obj).val() == 'range') {
		jQuery(range).show();
	} else {
		jQuery(range).hide();
	}
}

function createwarning ($obj) {
	if ($obj.parents('.subclass').find('.jak2-error').length) {
		$obj.parents('.subclass').find('.jak2-error').show();
	} else {
		// create if not exists.
		$obj.parents('.subclass').find('.group-label').first().after('<div class="jak2-error">'+$required_warning+'</div>');
	}
}

function checkrequired(params, mid) {
	var $obj, $form, $check, $input, $warning, multiboxcheck;
	if (params == 0) return true;
	$form = jQuery('#'+mid);
	var $mid = mid.replace('jak2filter-form-','');
	if (params == 1) {
		// at least 1 field must chosen.
		$input = $form.find('input[type="text"], textare, select, input[type="radio"], input[type="checkbox"], .ja-magic-select-container, input[name$="jacheck"]');
		$check = false;
		$input.each(function(){
			$obj = jQuery(this);
			if ($obj.attr('type') == 'text') {
				if (jQuery(this).val() != '') $check=true;
				if ($obj.parents('.subclass').find('select').attr('multiple') == 'multiple') $check=false; //reset check if select multipe.
			}
			if ($obj.attr('type') == 'hidden') {
				if ($obj.val() != $obj.prev().val()) $check=true;
			}
			if ($obj.prop("tagName") == 'SELECT') {
				// select type
				if ($obj[0].selectedIndex !== 0 && $obj[0].selectedIndex !== -1) $check=true;
			}
			if ($obj.attr('type') == 'checkbox' || $obj.attr('type') == 'radio')
				if ($obj.is(':checked') == true) $check=true;
			
			if ($obj.hasClass('ja-magic-select-container')) {
				$input = $obj.find('input[type="hidden"]');
				if ($input.length!==0) $check=true;
			}
			if ($check==true) return false; // if at least 1 had value then we stop the each
		});
		if ($check == false) {
			$form.find('.onewarning').show();
		} else {
			$form.find('.onewarning').hide();
		}
		return $check;
	} else {
		// user defined chosen.
		$check = [];
		for (i=0;i<params.length;i++) {
			$obj = $form.find('input[name="'+params[i]+'_to"], input[name="'+params[i]+'_from"], input[name="'+params[i]+'"], input[name="'+params[i]+'_txt"], input[name="'+params[i]+'[]"], select[name="'+params[i]+'_txt"], select[name="'+params[i]+'"], select[name="'+params[i]+'[]"], textarea[name="'+params[i]+'"], div#mg-'+$mid+'-'+params[i]+'-container');
			if ($obj.length == 0) continue; // continue if the field does not exists.
			$warning = false;
		
			if ($obj.attr('type') == 'text') {
				//text field, date field.
				if ($obj.length > 1) {
					if ($obj.eq(0).val() == '' && $obj.eq(1).val() == '') {
						$check.push(false);
						$warning = true;
					}
				} else {
					if ($obj.val() == '') {
						$check.push(false);
						$warning = true;
					}
				}
			}
		
			if ($obj.attr('type') == 'hidden') {
				// jadepend field.
				if ($obj.val() == '') {
					$check.push(false);
					$warning = true;
				}
			}
		
			if ($obj.length > 1) {
				// checkbox, radio.
				if ($obj.eq(0).attr('type') == 'checkbox' || $obj.eq(0).attr('type') == 'radio') {
					multiboxcheck = false;
					$obj.each(function(i, el){
						if (multiboxcheck == false)
							multiboxcheck = el.checked;
					});
					$check.push(multiboxcheck);
					$warning = multiboxcheck == false ? true : false;
				}
			}

			if ($obj.prop("tagName") == 'SELECT') {
				// select type
				if ($obj.attr('multiple') == 'multiple') {
					// multiple
					if (!$obj.find('option:selected').length) {
						$check.push(false);
						$warning = true;
					}
				} else {
					// single
					if ($obj[0].selectedIndex === 0) { // single & multip select.
						$check.push(false);
						$warning = true;
						// should check for date range here then return true if we put all the field.
					}
				}
				
			
				// check for select type with date range.
			}

			if ($obj.prev().hasClass('ui-slider')) {
				// slider type.
				if ($obj.val() == $obj.next().val()) {
					$check.push(false);
					$warning = true;
				}
			}

			// we find magic select seperate.
			if ($obj.hasClass('ja-magic-select-container')) {
				$input = $obj.find('input[type="hidden"]');
				if ($input.length===0) {
					$check.push(false);
					$warning = true;
				}
			}

			if ($warning===true) {
				// return warning here.
				createwarning($obj);
			} else {
				// hide warning if form ok.
				$obj.parents('.subclass').find('.jak2-error').remove();
			}
		}
		for (var i=0;i<$check.length;i++) {
			if ($check[i] == false) {
				return false;
			}
		}
		return true;
	}
}

function expandOption(numb, _id, shtxt) {
	jQuery('ul#jak2filter'+_id+' .subclass').each(function() {
		if (jQuery(this).find('label.lb-checkbox').length > numb) {
			jQuery(this).find('label.lb-checkbox:gt('+(numb-1)+')').hide();
			jQuery(this).append('<label class="ex-showmore">'+shtxt+'</label>');
		}
		if (jQuery(this).find('label.radio').length > numb) {
			jQuery(this).find('label.radio:gt('+(numb-1)+')').hide();
			jQuery(this).append('<label class="ex-showmore">'+shtxt+'</label>');
		}
	});
	jQuery('ul#jak2filter'+_id+' .ex-showmore').off().on('click', function() {
		jQuery(this).parent().find('label').show();
		jQuery(this).remove();
	});
}
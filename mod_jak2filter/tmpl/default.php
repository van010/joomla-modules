<?php
/**
 * ------------------------------------------------------------------------
 * JA K2 Filter Module for J25 & J3.4
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// No direct access.
defined('_JEXEC') or die;

JHtml::_('behavior.multiselect');
JHtml::_('bootstrap.popover');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$jinput = $app->input;
$formid	  = 'jak2filter-form-' . $module->id;
$document = JFactory::getDocument();
// Add styles
$style = '
#'.$formid.' div.ui-accordion-content {
	display:table;
}
';
$document->addStyleDeclaration($style);
$itemid	  = $params->get('set_itemid', 0) ? $params->get('set_itemid', 0) : $jinput->getInt('Itemid');
$ajax_filter = $params->get('ajax_filter', 0);
$option = $jinput->get('option', '');
$issearch = $jinput->get('issearch', 0); // check for prefilter with ajax enabled and not jak2filter component
$share_url   = $params->get('share_url_of_results_page', 0);
$uri = JUri::getInstance();
$host = $uri->toString(array('scheme', 'host'));
$ss_prefilter=false;
if ($issearch && $ajax_filter && $option !== 'com_jak2filter') {
	$ss_prefilter = true; // should send request for prefilter on first load.
}
$shoption = $params->get('shoption', 0);
?>

<form id="<?php echo $formid; ?>" name="<?php echo $formid; ?>" method="POST" action="<?php echo $host . JRoute::_('index.php?option=com_jak2filter&view=itemlist&Itemid=' . $itemid); ?>">
	<div class="subclass">
		<div class="jak2-error onewarning" style="display:none;"><?php echo JText::_('JAK2_ONE_WARNING'); ?></div>
	</div>
	<input type="hidden" name="task" value="search"/>
	<input type="hidden" name="issearch" value="1"/>
	<input type="hidden" name="swr" value="<?php echo $slider_whole_range; ?>"/>
	<?php if (!empty($theme)): ?>
		<input type="hidden" name="theme" value="<?php echo $theme ?>"/>
	<?php endif; ?>
	<?php if ($catMode): ?>
		<!-- include sub category -->
		<input type="hidden" name="isc" value="1"/>
	<?php endif; ?>
	<?php if (!$params->get('display_ordering_box', 1) && $params->get('catOrdering') != "inherit"): ?>
		<input type="hidden" id="ordering" name="ordering" value="<?php echo $params->get('catOrdering'); ?>"/>
	<?php endif; ?>
	<?php if (!$filter_by_category): ?>
		<?php echo $categories; ?>
	<?php endif; ?>

	<ul id="jak2filter<?php echo $module->id; ?>" class="ja-k2filter <?php echo $ja_stylesheet; ?>">
		<?php
		$j	 = 0;
		$col = $params->get('ja_column', 2);
		$colClass = 'jacol-' . $col;
		/*BEGIN: filter by Keyword*/
		if ($filter_by_keyword): ?>
			<li class="<?php echo $colClass ?>">
				<div class="subclass">
					<label class="group-label">
						<?php echo JText::_('JAK2_KEYWORD'); ?> <?php echo highlight_required_field('searchword', $check_required_fields); ?>
						<?php if ($display_keyword_tip): ?>
							<sup>
								<span class="hasPopover" data-original-title="<?php echo JHtml::_('tooltipText','JAK2_HINT') ?>" data-content="<?php echo JHtml::_('tooltipText','JAK2_KEYWORD_HINT') ?>">
									[?]
								</span>
							</sup>
						<?php endif; ?>
					</label>
					<input type="text" name="searchword" id="searchword<?php echo $module->id; ?>" class="inputbox"
						   value="<?php echo htmlspecialchars($jinput->getString('searchword', '')); ?>"
						   placeholder="<?php echo JText::_('SEARCH_BY_KEYWORD', ''); ?>"
						/>
					<?php if ($filter_keyword_option): ?>
						<p class="keyword-options">
							<?php echo $keyword_option; ?>
						</p>
					<?php else: ?>
						<!--<input type="hidden" name="st" value="<?php /*echo $keyword_default_mode; */ ?>" />-->
					<?php endif; ?>
				</div>
			</li>
			<?php
		endif;
		/*END: filter by Keyword*/
		?>

		<?php
		/*BEGIN: filter by date*/
		if ($filter_by_daterange):
			?>
			<li class="<?php echo $colClass ?>">
				<div class="subclass">
					<label class="group-label"><?php echo JText::_('SEARCH_BY_DATE'); ?> <?php echo highlight_required_field('dtrange', $check_required_fields); ?> </label>
					<?php echo $filter_by_daterange; ?>
				</div>
			</li>
		<?php endif; ?>

		<?php
		/*BEGIN: filter by Author*/
		if ($filter_by_author):
			?>
			<li class="<?php echo $colClass ?>">
				<div class="subclass">
					<?php echo $authors_label; ?>
					<?php echo $authors; ?>
				</div>
			</li>
			<?php
		endif;
		/*END: filter by Author*/
		?>

		<?php
		/*BEGIN: filter by Tags*/
		if ($filter_by_tags_display):
			?>
			<li class="<?php echo $colClass ?>">
				<div class="subclass">
					<label class="group-label"><?php echo $filter_by_tags_label; ?></label>
					<?php echo $filter_by_tags_display; ?>
				</div>
			</li>
			<?php
		endif;
		/*END: filter by Tags*/
		?>

		<?php
		/*BEGIN: filter by Rating*/
		if ($filter_by_rating_display):
			?>
			<li class="<?php echo $colClass ?>">
				<div class="subclass">
					<label class="group-label">
						<?php echo JText::_('JAK2_RATING'); ?> <?php echo highlight_required_field('rating', $check_required_fields); ?>
						<ul class="itemRatingList" id="rating_range_<?php echo $module->id; ?>">
							<li style="width:53.4%;" id="presenter_<?php echo $module->id; ?>_rating" class="itemCurrentRating"></li>
							<li><span class="srange one-star" title="" rel="1-stars" href="#">1</span></li>
							<li><span class="srange two-stars" title="" rel="2-stars" href="#">2</span></li>
							<li><span class="srange three-stars" title="" rel="3-stars" href="#">3</span></li>
							<li><span class="srange four-stars" title="" rel="4-stars" href="#">4</span></li>
							<li><span class="srange five-stars" title="" rel="5-stars" href="#">5</span></li>
						</ul>
						<span id="presenter_<?php echo $module->id; ?>_rating_note" class="itemCurrentRatingNote"></span>
					</label>
					<?php echo $filter_by_rating_display; ?>
				</div>
			</li>
			<?php
		endif;
		/*END: filter by Rating*/
		?>

		<?php
		/*BEGIN: filter by Category*/
		if ($filter_by_category) {
			?>
			<li class="<?php echo $colClass ?>">
				<div class="subclass">
					<label class="group-label"><?php echo JText::_('JAK2_CATEGORY'); ?> <?php echo highlight_required_field('category_id', $check_required_fields); ?></label>
					<?php echo $categories; ?>
				</div>
			</li>
			<?php
		}

		/*END: filter by Category*/
		?>

		<?php if ($list): ?>
			<?php if ($ja_stylesheet == 'vertical-layout' && count($list) > 1): ?>
			<li id="ja-extra-field-accordion-<?php echo $module->id; ?>" class="accordion <?php echo $colClass ?>">
				<?php foreach ($list as $glist): ?>
					<?php $groupid = $glist['groupid']; ?>
					<h4 class="heading-group heading-group-<?php echo $groupid ?>"><?php echo $glist['group'] ?></h4>
					<div>
						<ul>
							<?php require JModuleHelper::getLayoutPath('mod_jak2filter', 'default_extrafields'); ?>
						</ul>
					</div>
				<?php endforeach; ?>
			</li>
		<?php else: ?>
			<?php foreach ($list as $glist): ?>
				<?php require JModuleHelper::getLayoutPath('mod_jak2filter', 'default_extrafields'); ?>
			<?php endforeach; ?>
		<?php endif; ?>


		<?php if ($ja_stylesheet == 'vertical-layout' && count($list) > 1): ?>
			<script type="text/javascript">
				/*<![CDATA[*/
				jQuery(document).ready(function () {
					jQuery("#jak2filter<?php echo $module->id;?> .accordion")
						.accordion({
							header: " > h4",
							autoHeight: false,
							collapsible: true,
							icons: {
								header: "collapsed",
								headerSelected: "expanded"
							}
						});
				});
				/*]]>*/
			</script>
		<?php endif; ?>
		<?php endif; ?>

		<?php if ($params->get('display_ordering_box', 1)): ?>
			<li class="<?php echo $colClass; ?>" >
				<div class="subclass">
					<label for="catOrderingSelectBox" class="group-label"><?php echo JText::_('JAK2_ITEM_ORDERING_SELECT_BOX'); ?></label>
					<?php echo $display_ordering; ?>
				</div>
			</li>

			<?php if ($filter_sort != false) : ?>
				<?php foreach ($filter_sort as $kfs => $fs): ?>
					<?php if (in_array($kfs, $order_display)): ?>
						<li class="fssorts <?php echo $colClass ?>" data-of="<?php echo(ltrim($fs, 'r')); ?>">
							<div class="subclass">
								<div class="title" style="cursor:move">
								<h6 class="fsname"><?php echo(ltrim($fs, 'r')); ?></h6>
								</div>
								<div class="group-controls">
								<?php if (!in_array($kfs, array('zelevance', 'best', 'modified', 'publishUp', 'zdate', 'featured'))) : // these don't have the ASC ordering ?>
									<div class="controls"><input type="radio" <?php echo(strpos($fs, 'r') !== 0 ? ' checked="" ' : '') ?> name="orders[<?php echo(ltrim($fs, 'r')); ?>]" value="<?php echo ltrim($fs, 'r'); ?>"/><p class="ascending"><?php echo JText::_("JAK2ASC"); ?></p></div>
								<?php endif; ?>
								<?php if (!in_array($kfs, array('adate'))) : ?>
									<?php $fscheck=$fs;if (in_array($kfs, array('zelevance', 'best', 'modified', 'publishUp', 'zdate', 'featured'))) $fscheck='r'.$fs; // these order field don't have r in first letter so can not selected first load. we add then ?>
									<div class="controls"><input type="radio" <?php echo(preg_match('/^r/', $fscheck) ? ' checked="" ' : '') ?> name="orders[<?php echo(ltrim($fscheck, 'r')); ?>]" value="<?php echo 'r' . ltrim($fscheck, 'r'); ?>"/><p class="decrease"><?php echo JText::_("JAK2DESC"); ?></p></div>
								<?php endif; ?>
								<div class="controls"><button type="button" class="delete" onclick="jQuery(this).parents('li').remove();<?php if ($auto_filter): ?>jQuery('#<?php echo $formid; ?>').trigger('filter.submit');<?php endif; ?>"><?php echo JText::_("JAK2DELETE"); ?></button></div>
								</div>
							</div>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		<?php endif; ?>
		<li class="last-item <?php echo $colClass ?>">
			<div class="subclass">
				<?php if ($params->get('auto_filter', 1) == 0): ?>
					<input class="btn" type="button" onclick="jQuery('#<?php echo $formid; ?>').trigger('filter.submit');" name="btnSubmit" value="<?php echo JText::_('JAK2SEARCH'); ?>"/>
				<?php endif; ?>
				<?php if ($params->get('enable_reset_button', 1) == 1): ?>
					<input class="btn" type="button" name="btnReset" value="<?php echo JText::_('RESET'); ?>" onclick="jaK2Reset('<?php echo $module->id; ?>', '<?php echo $formid; ?>', true);"/>
				<?php endif; ?>
				<?php if ($ajax_filter && $share_url): ?>
					<div class="jak2shareurl btn"><a href="<?php echo JURI::current() ?>" target="_blank" title="<?php echo JText::_('JAK2_SHARE_URL_OF_RESULTS_PAGE_DESC', true) ?>"><?php echo JText::_('JAK2_SHARE_URL_OF_RESULTS_PAGE') ?></a></div>
				<?php endif; ?>
			</div>
		</li>
	</ul>
	<?php if ($params->get('ajax_filter', 0) == 1): ?>
		<?php if ($params->get('update_counter')): ?>
			<input type="hidden" name="jamoduleid" value="<?php echo $module->id ?>">
		<?php endif ?>
		<input type="hidden" id="jatmpl" name="tmpl" value="component"/>
	<?php endif; ?>
	<input type="text" style="display: none;">
</form>

<script type="text/javascript">
	/*<![CDATA[*/

	var $required_fields<?php echo $module->id; ?> = <?php echo $required_fields; ?>;
	var $required_warning = "<?php echo JText::_('JAK2_REQUIRED_WARNING'); ?>";
	var $cache<?php echo $module->id ?> = {};
	//validate date function
	function isDate(txtDate) {
		var reg = /^(\d{4})([\/-])(\d{1,2})\2(\d{1,2})$/;
		return reg.test(txtDate);
	}

	//validate startdate and enddate before submit form
	function validateDateRange(obj) {
		var from, to, field;
		obj = jQuery(obj);
		
		if (isDate(obj.val())) {
			obj.removeClass('date-error');
		} else {
			obj.addClass('date-error');
			return;
		}

		field = obj.data('field');
		if (obj.hasClass('k2datefrom')) {
			from = obj;
			to = jQuery('.k2dateto[data-field="'+field+'"]');
		} else if (obj.hasClass('k2dateto')) {
			from = jQuery('.k2datefrom[data-field="'+field+'"]');
			to = obj;
		} else {
			return;
		}
		<?php if($auto_filter): ?>
		if (from.val() && to.val()) {
			jQuery('#<?php echo $formid; ?>').trigger('filter.submit');	
		}
		<?php endif; ?>
	}
	
	jQuery(window).on('load', function() {
		<?php if(!empty($ss_prefilter)): ?>
			jQuery('#<?php echo $formid; ?>').trigger('filter.submit');
		<?php endif; ?>
	});
	
	jQuery(document).ready(function () {
		jQuery('#ja-extra-field-accordion-<?php echo $module->id;?>').find('.ui-accordion-disabled.ui-state-disabled').each(function(){
			if (jQuery(this).next().find('.chzn-container.chzn-container-single').length)
				jQuery(this).next().find('.chzn-container.chzn-container-single').attr('style', 'width:auto;');
		});
		
		jQuery('.fsname').each(function () {
			jQuery(this).text(jQuery('#jak2filter<?php echo $module->id;?>').find('option[value=' + jQuery(this).text() + ']').text());
		});

		var sortableEl = document.getElementById('jak2filter<?php echo $module->id;?>');
		new Sortable(sortableEl, {
			draggable: "li.fssorts",
			handle: ".title",
			ghostClass: 'sortablejs-ghost',
			animation: 150,
			onEnd: function() {
				<?php if($auto_filter): ?> jQuery('#<?php echo $formid; ?>').trigger('filter.submit'); <?php endif; ?>
			}
		});

		jQuery('#jak2filter<?php echo $module->id;?> #ordering').change(function () {
			if (jQuery(this).val() && !jQuery('#jak2filter<?php echo $module->id;?>').find('li[data-of=' + jQuery(this).val() + ']').length) {
				var strout = '';
				
				if (jQuery.inArray(jQuery(this).val(), ['zelevance', 'best', 'modified', 'publishUp', 'zdate', 'featured']) === -1) {
					strout = '<div class="group-controls">'+
						'<div class="controls">'+
							'<input <?php if($auto_filter): ?>onclick="jQuery(\'#<?php echo $formid; ?>\').trigger(\'filter.submit\');"<?php endif; ?> type="radio" checked="" name="orders[' + jQuery(this).val() + ']" value="' + jQuery(this).val() + '" /><p class="ascending"><?php echo JText::_("JAK2ASC"); ?></p>'+
						'</div>';
					if (jQuery.inArray(jQuery(this).val(), ['adate']) === -1)
						strout += '<div class="controls">'+
							'<input <?php if($auto_filter): ?>onclick="jQuery(\'#<?php echo $formid; ?>\').trigger(\'filter.submit\');"<?php endif; ?> type="radio" name="orders[' + jQuery(this).val() + ']" value="r' + jQuery(this).val() + '" /><p class="decrease"><?php echo JText::_("JAK2DESC"); ?></p>'+
						'</div>';
					strout += '</div>';
				} else {
					strout = '<div class="group-controls"><div class="controls"><input <?php if($auto_filter): ?>onclick="jQuery(\'#<?php echo $formid; ?>\').trigger(\'filter.submit\');"<?php endif; ?> type="radio" checked="checked" name="orders[' + jQuery(this).val() + ']" value="r' + jQuery(this).val() + '" /><p class="decrease"><?php echo JText::_("JAK2DESC"); ?></p></div></div>';
				}
				jQuery(this).parents('li').after('<li class="fssorts <?php echo $colClass; ?>" data-of="' + jQuery(this).val() + '"> ' +
					'<div class="subclass">'+
						'<div class="title" style="cursor:move"><h6>' + jQuery(this).find('option:selected').html() + '</h6></div>' +
						strout+ 
						'<div class="controls"><button type="button" class="delete" onclick="jQuery(this).parents(\'li\').remove();<?php if($auto_filter): ?>jQuery(\'#<?php echo $formid; ?>\').trigger(\'filter.submit\');<?php endif; ?>"><?php echo JText::_("JAK2DELETE"); ?></button></div>'+
					'</div>'+
				'</li>');
			}
			jQuery(this).val('').trigger("liszt:updated");
			<?php if($ja_stylesheet == 'horizontal-layout' && $params->get('ja_column') && $params->get('ja_column') > 0){ ?>horizon_calculate<?php echo $module->id; ?>();<?php } ?>
			<?php if($auto_filter): ?>
				// if auto_filter we submit form.
				jQuery('#<?php echo $formid; ?>').trigger('filter.submit');
			<?php endif; ?>
		});

		if ( jQuery.browser.msie ) {
			jQuery('#searchword<?php echo $module->id;?>').on('keyup', function(event) {
				var keycode = event.keyCode ? event.keyCode : event.which;
				if(keycode == 13 ) {
					jQuery('#<?php echo $formid; ?>').trigger('filter.submit')
				}
			});
		}

		function resetChildren($current, $next) {
			if ($next) {
				var $data = $jak2depend[parseInt($next.data('exfield'))][$current.val()];
				if ($data != undefined && $data != 'undefined') {
					$next.html('');
					$next.append('<option value="">'+$next.data('extitle')+'</option>');
					for (var i=0;i<$data.length;i++) {
						$next.append('<option value="'+$data[i][0]+'">'+$data[i][1]+'</option>');
					}
					$next.chosen("destroy").chosen();
				} else {
					$next.html('');
					$next.append('<option value="">'+$next.data('extitle')+'</option>');
					$next.chosen("destroy").chosen();
				}
				var $_next = '#K2ExtraField_'+$next.data('exfield')+'_'+(parseInt($next.data('dependlv'))+1);
				if (jQuery($_next).length) resetChildren($next, jQuery($_next));
			}
		}

		jQuery('#jak2filter<?php echo $module->id;?> .jak2depend').change(function() {
			var elm, dependlv, exfield, autofield, $next, $prev, $ar, moduleid, dependarray, dependtxt, form;
			elm  = jQuery(this);
			dependlv = elm.data('dependlv');
			exfield = elm.data('exfield');
			autofield = elm.data('autofield');
			moduleid = <?php echo $module->id;?>;
			$next = jQuery('#jak2filter'+moduleid+' #K2ExtraField_'+exfield+'_'+(dependlv + 1));
			if ($next.length) resetChildren(elm, $next);

			dependarray = jQuery('#jak2filter'+moduleid+' #xf_'+ exfield +'_array');
			dependarray.val('');

			jQuery('#jak2filter<?php echo $module->id;?> .jak2depend[data-exfield="'+exfield+'"]').each(function(){
				$ar = dependarray.val();
				if (jQuery(this).val() != '')
					$ar += jQuery(this).val()+',';
				dependarray.val($ar);
			});

			dependtxt = jQuery('#jak2filter'+moduleid+' #xf_'+exfield+'_txt');
			dependtxt.val(elm.val());

			if (elm.val() == '') { // get the prev value if choose no value at current.
				$prev = jQuery('#jak2filter'+moduleid+' #K2ExtraField_'+exfield+'_'+(dependlv-1));
				if ($prev.length) {
					dependtxt.val($prev.val());
				}
			}

			<?php if($auto_filter): ?>
			form = jQuery('#<?php echo $formid; ?>')
			if (elm.data('autofield') == 'all') {
				// always submit form if all defined.
				form.trigger('filter.submit'); 
			} else if (dependlv == autofield) { 
				// submit form at number defined
				form.trigger('filter.submit');
			} else if (!$next.length) {
				// if auto_filter and the last one choose we submit form.
				form.trigger('filter.submit');
			}
			<?php endif; ?>
		});

		if (jQuery('#jak2filter<?php echo $module->id;?>').find('#category_id').length) {
			jak2DisplayExtraFields(<?php echo $module->id;?>, jQuery('#jak2filter<?php echo $module->id;?>').find('#category_id'), <?php echo $selected_group; ?>);
		}

		<?php if($auto_filter): ?>
		var f = jQuery('#<?php echo $formid; ?>');
		f.find('input').each(function () {
			var el = jQuery(this);
			el.on('change', function () {
				if (el.hasClass('k2datefrom') || el.hasClass('k2dateto') || el.hasClass('date-error')) return; // handle date range on other place

				jQuery('#<?php echo $formid; ?>').trigger('filter.submit');
			});
		});
		f.find('select').each(function () {
			var el = jQuery(this);
			el.on('change', function () {
				if (el.hasClass('jak2depend') || el.attr('id') == 'ordering') {
					// handle change depend filter and ordering on other place
					return;
				}
				if (el.attr('id') == 'dtrange' && el.val() == 'range') {
					var sDate = jQuery('#sdate_<?php echo $module->id; ?>');
					var eDate = jQuery('#edate_<?php echo $module->id; ?>');
					if (sDate.val() & eDate.val()) {
						if (isDate(sDate.val())) {
							sDate.removeClass('date-error');
						} else {
							sDate.addClass('date-error');
							return;
						}

						if (isDate(eDate.val())) {
							eDate.removeClass('date-error');
						} else {
							eDate.addClass('date-error');
							return;
						}

						if (sDate.val() && eDate.val()) {
							jQuery('#<?php echo $formid; ?>').trigger('filter.submit');
						}
					}
				} else {
					jQuery('#<?php echo $formid; ?>').trigger('filter.submit');
				}
			});
		});
		f.find('textarea').each(function () {
			var el = jQuery(this);
			el.on('change', function () {
				jQuery('#<?php echo $formid; ?>').trigger('filter.submit');
			});
		});
		<?php endif; ?>

		<?php if($ajax_filter): ?>
		jQuery('#<?php echo $formid; ?>').on('filter.submit', function () {
			var $check = checkrequired($required_fields<?php echo $module->id; ?>, "<?php echo $formid; ?>");
			if ($check == true) {
				jak2AjaxSubmit(this, '<?php echo JURI::root(true).'/'; ?>', $cache<?php echo $module->id ?>);
				<?php if($share_url): ?>
				jak2GetUrlSharing(this, <?php echo !!$params->get('update_browser_url') ?>);
				<?php endif; ?>
			} else {
				var top = jQuery('#<?php echo $formid; ?>').find('.jak2-error:visible').first().offset().top - 100;
				jQuery("html, body").animate({scrollTop: top});
				return false;
			}
		});

		if (jQuery('#k2Container').length) {
			jak2AjaxPagination(jQuery('#k2Container'), '<?php echo JURI::root(true).'/'; ?>', $cache<?php echo $module->id ?>, $cache<?php echo $module->id ?>);
			<?php if($share_url): ?>
			jak2GetUrlSharing('#<?php echo $formid; ?>');
			<?php endif; ?>
		}

		<?php else: ?>
		jQuery('#<?php echo $formid; ?>').on('filter.submit', function () { 
			var $check = checkrequired($required_fields<?php echo $module->id; ?>, "<?php echo $formid; ?>");
			if ($check == true) {
				jQuery('#<?php echo $formid; ?>').submit();
			} else {
				var top = jQuery('#<?php echo $formid; ?>').find('.jak2-error:visible').first().offset().top - 100;
				jQuery("html, body").animate({scrollTop: top});
				return false;
			}
		});
		<?php endif; ?>
		<?php if (!empty($shoption)): ?>
			expandOption(<?php echo $shoption ?>, <?php echo $module->id; ?>, "<?php echo JText::_('SHOW_MORE_TXT'); ?>");
		<?php endif; ?>
	});
	/*]]>*/
</script>
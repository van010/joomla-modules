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

// no direct access
defined('_JEXEC') or die;

require_once(JPATH_ADMINISTRATOR.'/components/com_jak2filter/models/model.php');
require_once(JPATH_SITE.'/components/com_jak2filter/models/itemlist.php');

/**
 * @package		Joomla.Site
 * @subpackage	mod_jak2filter
 * @since		1.5
 */
class modJak2filterHelper
{
	const COUNT_ITEMS_TXT = ' (%s)';
	protected $module = 0;
	protected $params = null;
	protected $comParams = null;
	protected $display_counter = 0;
	protected $update_counter = 0;
	protected $disable_option_empty = 0;
		
	protected $enable_reset_button = 0;

	public $activeCats = array();
	
	public function __construct($module) {
		$this->module = $module;
		$this->params = new JRegistry($module->params);
		$this->comParams = JComponentHelper::getParams('com_jak2filter');

		$this->display_counter		= (int) $this->params->get('display_counter');
		$this->update_counter		= (int) $this->params->get('update_counter');
		$this->disable_option_empty		= (int) $this->params->get('disable_option_empty');
		$ajax_filter = (int) $this->params->get('ajax_filter', 0);
		if($ajax_filter) {
			$this->disable_option_empty = 0;
		}
		
		$this->enable_reset_button		= (int) $this->params->get('enable_reset_button');
		
		$this->update_counter &= $this->display_counter;//only enable if display_counter option is enabled
		if(!$this->display_counter) {
			$this->disable_option_empty = 0;
		}

		$mode = $this->params->get('form_mode', 'normal');
		if($mode == 'dynamic') {
			$app = JFactory::getApplication();
			$jinput = JFactory::getApplication()->input;
			$menu = $app->getMenu();
			$active = $menu->getActive();
			if(!$active) {
				$Itemid = $jinput->get('Itemid', 0);
				if($Itemid) {
					$active = $menu->getItem($Itemid);
				}
			}
			if($active) {
				$uri = JURI::getInstance($active->link);
				$option = $jinput->get('option', '');
				if($option == 'com_k2') {
					$categories = $active->params->get('categories', array());
					if(!empty($categories) && $this->params->get('catMode', 1)) {
						$catCatalogMode = $active->params->get('catCatalogMode', 1);
						if($catCatalogMode) {
							$helper = new JAK2FilterModelItemlist();
							$categories = $helper->getCategoryTree($categories);
						}

						$cat_id = $jinput->getString('category_id', 0);
						if(!$cat_id) {
							$jinput->getString('category_id', implode(',', $categories));
						}
					}

					$this->activeCats = $categories;
				} elseif ($option == 'com_jak2filter') {
					$categories = $jinput->getString('category_id', 0);
					if($categories) {
						$categories = explode(',', $categories);
						$categories = array_map(function($catid) {
							return (int) $catid;
						}, $categories);

						$categories = array_filter($categories, function($catid) {
							return $catid;
						});

						array_unique($categories);

						$isc = $jinput->get('isc', 0);
						if($isc && $this->params->get('catMode', 1)) {
							$helper = new JAK2FilterModelItemlist();
							$categories = $helper->getCategoryTree($categories);
						}
						$this->activeCats = $categories;
					}
				}
			}
		}
	}
	
	/**
	 * Get a list of the K2 Extra Fields.
	 *
	 * @param	JRegistry	$params	The module options.
	 *
	 * @return	array
	 * @since	1.5
	 */
	public function getList($fields, $fields_type)
	{				
		$items = array();
		$ja_stylesheet = $this->params->get('ja_stylesheet');

		if(is_array($fields) && count($fields)>0)
		{
			$xgroup_order = $this->params->get('xgroup_order', array());
			$gindex = 0;

			if(!empty($this->activeCats)) {
				$activeGroups = $this->getextraFieldsGroupsByCat($this->activeCats);
			}

			foreach ($fields_type as $index => $value)
			{
				$field = explode(":", $value);
				if(!empty($field) && count($field)>1)
				{
					$fieldId = $field[0];
					$fieldType = $field[1];
					$fn = ucfirst(strtolower($fieldType));
					$func = 'get'.$fn;
					
					if(!in_array($fieldId, $fields) || !method_exists($this, $func)) continue;

					$row = $this->getExtraField($fieldId);
					if(!$row) continue;
					$group = $row->group;
					if(isset($activeGroups) && !in_array($group, $activeGroups)) {
						//dynamically get extra fields
						continue;
					}
					if(!isset($items[$group])) {
						$order = isset($xgroup_order[$gindex]) ? $xgroup_order[$gindex] : $gindex;
						$gindex++;
						$items[$group] = array('groupid' => $group, 'group' => $row->group_name, 'order' => $order, 'items' => array());
					}

					$fieldname = 'xf_'.$row->id;
					$row->jatype = 'xfield';
					$row->ff_type = $fieldType;//form field type
					$row->index = $index;
					$html = $this->getLabel($row->ff_type, $fieldname, $row->name, $row->group);
					$html .= call_user_func_array(array($this, $func), array($fieldname, $row));
					
					$items[$group]['items'][$fieldType.'_'.$fieldId] = $html;
				}
			}
		}
		if(!empty($items) && count($items) > 1) {
			//sort group
			usort($items, array($this, 'groupcmp'));
		}

		if($ja_stylesheet == 'vertical-layout' && count($items) > 1) {
			//include asset
			if(!defined('JAK2FILTER_ASSET_ACCORDION')) {
				define('JAK2FILTER_ASSET_ACCORDION', 1);
				$doc = JFactory::getDocument();
				$basepath = JURI::root(true).'/modules/' . $this->module->module . '/assets/';
				JHTML::_('JABehavior.jquery');
				JHTML::_('JABehavior.jqueryui');
				$doc->addStyleSheet($basepath.'jquery/jquery.ui.css');
				$doc->addScript($basepath.'jquery/jquery.ui.accordion.js');
			}
		}
		
		return $items;
	}
	/*
	 * Get extra field from k2
	 * */
	public function getExtraField($id){
		$db	=	JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("ef.`id`, ef.`name`, ef.`value`, ef.`type`, ef.`group`, efg.`name` AS group_name")
			->from("#__k2_extra_fields AS ef")
			->leftJoin("#__k2_extra_fields_groups AS efg ON efg.id = ef.group")
			->where("ef.id = ".$db->quote($id));
		$db->setQuery($query);
		$rows	=	$db->loadObject();
		return $rows;
	}
	
	/*
	 * Return html of field
	 * */
	public function getTextfield($fieldname, $field){
		$html = $this->getMultiLevelField($fieldname, $field);
		if($html) {
			return $html;
		}
		$input = JFactory::getApplication()->input;

		$selected_values = $input->get($fieldname."_txt",'', 'STRING');
		
		$values = json_decode($field->value);
		if($values && $this->params->get('auto_complete', 0)){
			JHtml::_('script', 'jui/jquery.autocomplete.min.js', array('version' => 'auto', 'relative' => true));
		}
		$html = '';
		foreach ($values as $f)
        {
           	if($selected_values){
           		$f->value = $selected_values;
           	} else {
				//do not use default value for search form
				$f->value = '';
			}
           	$id = "auto_".$this->module->id."_".$fieldname."_txt";

        	$html .= "\n\t<input type=\"text\" class=\"exfield exgroup{$field->group}\" name=\"{$fieldname}_txt\" id=\"".$id."\" value=\"{$f->value}\" placeholder=\"{$field->name}\" />";
			if($this->params->get('auto_complete', 0)==1){
				$url = 'index.php?option=com_jak2filter&view=suggestions&m_id='.$this->module->id.'&xf_id='.$field->id.'&tmpl=component';
				$html .= "
					<script>
					var completer_" . $this->module->id . '_' . $fieldname."= jQuery('#".$id."').autocomplete({
						serviceUrl: '" . JRoute::_($url, false) . "',
						paramName: '".$id."',
						minChars: 1,
						maxHeight: 400,
						zIndex: 9999,
						deferRequestBy: 500,
						onSearchComplete: function () {
							jQuery('.autocomplete-suggestion').on('mouseover', function() {
								var text = jQuery(this).text();
								jQuery('#".$id."').val(text);
							});
						}
					});
				";
				$html .= '</script>';				
			}
        }
        
        return $html;
	}
	
	protected function getExtraFieldParam($param, $index) {
		$vals = $this->params->get($param);
		$val = isset($vals[$index]) ? $vals[$index] : null;
		return $val;
	}

    protected function jak2filterNumberFormat($number, $field){
        $decimals = $this->getExtraFieldParam('range_slider_format_decimals', $field->index) ? $this->getExtraFieldParam('range_slider_format_decimals', $field->index) : 0;
        $dec_point = $this->getExtraFieldParam('range_slider_format_decimal_point', $field->index) ? $this->getExtraFieldParam('range_slider_format_decimal_point', $field->index) : '.';
        $thousands_sef = $this->getExtraFieldParam('range_slider_format_thousands_sep', $field->index) ? $this->getExtraFieldParam('range_slider_format_thousands_sep', $field->index) : ',';
        $number_txt = '';
        $number_txt .= $this->getExtraFieldParam('range_slider_format_prefix', $field->index);
        if($this->getExtraFieldParam('range_slider_format', $field->index)){
            $number_txt .= number_format($number, $decimals, $dec_point, $thousands_sef);
        }else{
            $number_txt .= $number;
        }
        $number_txt .= $this->getExtraFieldParam('range_slider_format_suffix', $field->index);
        return $number_txt;
    }

	public function getValuerange($fieldname, $field){
		$input = JFactory::getApplication()->input;
		
		$selected_values = $input->get($fieldname."_range", null, 'string');
		
		$field->value = json_decode($field->value);
		
		$ranges = $this->getExtraFieldParam('filter_by_fieldrange', $field->index);
		
		if(isset($ranges)){
			$ranges = explode("|", $ranges);
			$ranges = array_filter($ranges);
			sort($ranges,SORT_NUMERIC);
			$options[] = JHTML::_('select.option', 0, JText::sprintf('JAK2_SELECT_OPTION', $field->name));
		
			for($i = 0; $i<count($ranges);$i++){
				if($i==0){
					$options[] = JHTML::_('select.option','|'.$ranges[$i],JText::_('LESS_THAN').' '. $this->jak2filterNumberFormat($ranges[$i], $field) );
				}
				else if($i==(count($ranges)-1)){
					$options[] = JHTML::_('select.option',$ranges[$i-1].'|'.$ranges[$i],$this->jak2filterNumberFormat($ranges[$i-1], $field).JText::_('JA_K2FILTER_TO').$this->jak2filterNumberFormat($ranges[$i], $field));
					$options[] = JHTML::_('select.option',$ranges[$i].'|',JText::_('MORE_THAN').' '.$this->jak2filterNumberFormat($ranges[$i], $field) );
				}
				else{
					$options[] = JHTML::_('select.option',$ranges[$i-1].'|'.$ranges[$i],$this->jak2filterNumberFormat($ranges[$i-1], $field).JText::_('JA_K2FILTER_TO').$this->jak2filterNumberFormat($ranges[$i], $field));
				}
				
			}
			
        	return JHTML::_('select.genericlist', $options, $fieldname.'_range', array('class'=>'exfield exgroup'.$field->group), 'value', 'text', $selected_values);
		}
		return '';
	}
	
	public function getLabelRangeSlider($fieldname, $fieldtitle, $group = 0) {
     	$id = 'presenter_'.$this->module->id.'_'.$fieldname.'_range';
		$label = "\n\t<label class=\"group-label\">".$fieldtitle;
     	$label .= '<span class="presenter" id="'.$id.'" ></span>';
     	$label .= '<div class="clearfix"></div>';
     	$label .= '</label>';
		return $label;
	}
	
	public function getRangeSlider($fieldname, $field){
		$jinput = JFactory::getApplication()->input;
		//include asset
		if(!defined('JAK2FILTER_ASSET_RANGESLIDER')) {
			define('JAK2FILTER_ASSET_RANGESLIDER', 1);
			$doc = JFactory::getDocument();
			$basepath = JURI::root(true).'/modules/' . $this->module->module . '/assets/';
			JHTML::_('JABehavior.jquery');
			JHTML::_('JABehavior.jqueryui');
			$doc->addStyleSheet($basepath.'jquery/jquery.ui.css');
			$doc->addScript($basepath.'jquery/jquery.ui.slider.js');
			$doc->addScript($basepath.'jquery/jquery.number.min.js');
			$doc->addScript($basepath.'jquery/jquery.ui.touch-punch.min.js');
		}
		//
		if($field->jatype != 'xfield') {
			$name = $fieldname;

			$attrs = $field->attrs;
			$min = $attrs['min'];
			$max = $attrs['max'];
			$start = $attrs['start'];
			$stop = $attrs['stop'];
			$step = $attrs['step'];
		} else {
			$name = $fieldname.'_range';
			$min = (float) $this->getExtraFieldParam('rangeslider_min', $field->index);
			$max = (float) $this->getExtraFieldParam('rangeslider_max', $field->index);
			$start = (float) $this->getExtraFieldParam('rangeslider_start', $field->index);
			$stop = (float) $this->getExtraFieldParam('rangeslider_stop', $field->index);
			$step = (float) $this->getExtraFieldParam('rangeslider_step', $field->index);
            $prefix = $this->getExtraFieldParam('range_slider_format_prefix', $field->index);
            $suffix = $this->getExtraFieldParam('range_slider_format_suffix', $field->index);
            $decimals = $this->getExtraFieldParam('range_slider_format_decimals', $field->index) ? $this->getExtraFieldParam('range_slider_format_decimals', $field->index) : 0;
            $dec_point = $this->getExtraFieldParam('range_slider_format_decimal_point', $field->index) ? $this->getExtraFieldParam('range_slider_format_decimal_point', $field->index) : '.';
            $thousands_sef = $this->getExtraFieldParam('range_slider_format_thousands_sep', $field->index) ? $this->getExtraFieldParam('range_slider_format_thousands_sep', $field->index) : ',';
		}
		$id = $this->module->id.'_'.$name;
		$idSlider = 'slider_'.$id;
		$idPresentor = 'presenter_'.$id;
		$idRange = 'rating_range_'.$this->module->id;
		$value = $jinput->get($name,'', 'RAW');
		
		//$field->value = json_decode($field->value);
		
		if(!empty($value)) {
			$values = explode('|', $value);
			if(!empty($values[0])) $start = (int) $values[0];
			if(isset($values[1])) $stop = (int) $values[1];
		}
		
		$auto_filter		= (int) $this->params->get('auto_filter');
		$formid 			= 'jak2filter-form-'.$this->module->id;
		$instantSearch = '';
		if($auto_filter) {
			$instantSearch = ',
			stop: function (event, ui) {
 				$("#'.$formid.'").trigger(\'filter.submit\'); // jQuery submit;
			}';
		}
		
		if($name == 'rating') {
			$presentor = '$( "#'.$idPresentor.'" ).css( {left: ((ui.values[ 0 ] - 1) * 20)+"%" , 
			width: ((ui.values[ 1 ]-ui.values[ 0 ] + 1) * 20)+"%"} );';
			$presentor .= '
			$("#'.$idRange.'").find("span.srange").removeClass("active");
			if (ui.values[0] == ui.values[1]) {
				$( "#'.$idPresentor.'_note" ).html("(" + ui.values[ 0 ] + "'.JText::_('JAK2FILTER_STARS').')");
			} else {
				$( "#'.$idPresentor.'_note" ).html("(" + ui.values[ 0 ] + " - " + ui.values[ 1 ] + "'.JText::_('JAK2FILTER_STARS').')");
			}
			if(ui.values[0] >= 2) {
				$("#'.$idRange.'").find("span[rel=\'"+(ui.values[0]-1)+"-stars\']").addClass("active");
			}
			';
			$presentorFirst = '$( "#'.$idPresentor.'" ).css( {left: (($( "#'.$idSlider.'" ).slider( "values", 0 ) - 1) * 20)+"%", 
			width: (($( "#'.$idSlider.'" ).slider( "values", 1 ) - $( "#'.$idSlider.'" ).slider( "values", 0 ) + 1) * 20)+"%"} );';
			$presentorFirst .= '
			if ($( "#'.$idSlider.'" ).slider( "values", 0 ) == $( "#'.$idSlider.'" ).slider( "values", 1 )) {
				$( "#'.$idPresentor.'_note" ).html("(" + $( "#'.$idSlider.'" ).slider( "values", 1 ) + "'.JText::_('JAK2FILTER_STARS').')");
			} else {
				$( "#'.$idPresentor.'_note" ).html("(" + $( "#'.$idSlider.'" ).slider( "values", 0 ) + " - " + $( "#'.$idSlider.'" ).slider( "values", 1 ) + "'.JText::_('JAK2FILTER_STARS').')");
			}
			if($( "#'.$idSlider.'" ).slider( "values", 0 ) >= 2) {
				$("#'.$idRange.'").find("span[rel=\'"+($( "#'.$idSlider.'" ).slider( "values", 0 )-1)+"-stars\']").addClass("active");
			}';
			
		} else {
            if($this->getExtraFieldParam('range_slider_format', $field->index)){
                $presentor = '$( "#'.$idPresentor.'" ).html( "' . $prefix . '" + $.number(ui.values[ 0 ], '. (int)$decimals .', "'. $dec_point .'", "'. $thousands_sef .'") + "' . $suffix . '" + " - " + "' . $prefix . '" +$.number(ui.values[ 1 ], '. (int)$decimals .', "'. $dec_point .'", "'. $thousands_sef .'") + "' . $suffix . '" );';
                $presentorFirst = '$( "#'.$idPresentor.'" ).html( "' . $prefix . '" + $.number($( "#'.$idSlider.'" ).slider( "values", 0 ), '. (int)$decimals .', "'. $dec_point .'", "'. $thousands_sef .'") + "' . $suffix . '"+ " - " + "' . $prefix . '" +$.number($( "#'.$idSlider.'" ).slider( "values", 1 ), '. (int)$decimals .', "'. $dec_point .'", "'. $thousands_sef .'") + "' . $suffix . '" );';
            }else{
                $presentor = '$( "#'.$idPresentor.'" ).html( "' . $prefix . '" + ui.values[ 0 ] + " - " + ui.values[ 1 ] + "' . $suffix . '" );';
                $presentorFirst = '$( "#'.$idPresentor.'" ).html( "' . $prefix . '" + $( "#'.$idSlider.'" ).slider( "values", 0 ) + "' . $suffix . '" + " - " + "' . $prefix . '" +$( "#'.$idSlider.'" ).slider( "values", 1 ) + "' . $suffix . '" );';
            }
		}
		
		$html = '
		<script type="text/javascript">
		(function($) {
			$(document).ready(function(){
				$( "#'.$idSlider.'" ).slider({
					range: true,
					min: '.$min.',
					max: '.$max.',
					step: '.$step.',
					values: [ '.$start.', '.$stop.' ],
					slide: function( event, ui ) {
						$( "#'.$id.'" ).val( ui.values[ 0 ] + "|" + ui.values[ 1 ] );
						'.$presentor.'
					},
					change: function( event, ui ) {
						$( "#'.$id.'" ).val( ui.values[ 0 ] + "|" + ui.values[ 1 ] );
						'.$presentor.'
					}
					'.$instantSearch.'
				});
				$( "#'.$id.'" ).val( $( "#'.$idSlider.'" ).slider( "values", 0 ) + "|" + $( "#'.$idSlider.'" ).slider( "values", 1 ) );
				'.$presentorFirst.'
			});
		})(jQuery);
		</script>
		<div id="'.$idSlider.'"></div>
		<input type="hidden" name="'.$name.'" id="'.$id.'" value="" />
		<input type="hidden" name="'.$name.'_jacheck" id="'.$id.'_jacheck" value="'.$min.'|'.$max.'" />';
		return $html;
	}
	
	public function getRatings() {
		$attrs = array(
			'min' => 0,
			'max' => 5,
			'start' => 0,
			'stop' => 5,
			'step' => 1
		);
		$field = $this->createFieldObject('rating', 'rating', 'rangeslider', '[]', $attrs);
		return $this->getRangeSlider('rating', $field);
	}

	public function getXFieldDatatype($field, $default = 'string') {
		static $dt = array();
		if(!count($dt)) {
			$xfDataType = $this->comParams->get('extra_fields_data_type', array());
			if(is_array($xfDataType) && count($xfDataType)) {
				foreach($xfDataType as $val) {
					@list($xfid, $type) = explode(':', $val);
					$dt[$xfid] = $type;
				}
			}
		}

		$type = isset($dt[$field->id]) ? $dt[$field->id] : $default;
		return $type;
	}

	private function getXFieldValues($field)
	{
		$sortMode = $this->comParams->get('extra_fields_sort_mode', array());
		$sort = 'alpha';
		if(!empty($sortMode)) {
			foreach($sortMode as $sm) {
				if(strpos($sm, $field->id.':') === 0) {
					$sort = str_replace($field->id.':', '', $sm);
					break;
				}
			}
		}
		$datatype = $this->getXFieldDatatype($field);
		if($field->type == 'labels') {
			$db = JFactory::getDbo();
			$direction = ($sort == 'ralpha') ? 'DESC' : 'ASC';
			$query = "SELECT t.labels AS name, t.labels AS `value`, t.`num_items` AS `num_items`
					FROM #__jak2filter_taxonomy t, #__k2_extra_fields e
					WHERE t.`labels` <> ''
					AND t.`asset_id` = e.`id`
					AND t.`type` = 'xfield' 
					AND t.`asset_id` = " . $field->id . "
					AND e.`group` = " . $field->group . "
					AND num_items > 0
					ORDER BY t.`labels` ".$direction;
			$db->setQuery($query);
			$values = $db->loadObjectList();
		} else {
			$values = json_decode($field->value);
			if($datatype == 'number') {
				if($sort == 'alpha') {
					usort($values, array($this, 'numbecmp'));
				} elseif ($sort == 'ralpha') {
					usort($values, array($this, 'numbercmp'));
				}
			} else {
				if($sort == 'alpha') {
					usort($values, array($this, 'strcmp'));
				} elseif ($sort == 'ralpha') {
					usort($values, array($this, 'strrcmp'));
				}
			}
		}

		foreach ($values as $id => &$f)
		{
			if ($field->type == 'labels') {
				if(!$this->display_counter) {
					$num_items = '';
				} elseif($this->update_counter) {
					$num_items = $this->getNumItems('xfield', $field->id, 0, $f->value);
				} else {
					$num_items = $f->num_items;
				}
			} else {
				if(empty($f->name)) {
					unset($values[$id]);
					continue;
				}
				$num_items = $this->getNumItems('xfield', $field->id, $f->value);
			}
			$this->bindListData($f, $num_items);
		}
		//@sort( $values, SORT_REGULAR );
		return $values;
	}

	private function bindListData(&$f, $num_items) {
		$f->disabled = ($this->disable_option_empty && !$num_items) ? true : false;
		$f->num_items = $num_items;
		$f->num_items_txt = (!empty($num_items)) ? sprintf(self::COUNT_ITEMS_TXT, $num_items) : '';
	}

	public function numbecmp($a, $b) {
		return (float) $a->name > (float) $b->name;
	}

	public function numbercmp($a, $b) {
		return (float) $a->name < (float) $b->name;
	}

	public function strcmp($a, $b) {
		return strcmp($a->name, $b->name);
	}

	public function strrcmp($a, $b) {
		return strcmp($b->name, $a->name);
	}

	public function groupcmp($a, $b) {
		return $a['order'] > $b['order'];
	}
	
	public function getCheckbox($fieldname, $field) {
		$input = JFactory::getApplication()->input;
		$selected_values = $input->get($fieldname, array(), 'array');

		if($field->jatype == 'xfield') {
			$values = $this->getXFieldValues($field);
		} else {
			$values = $field->value;
		}

       	$html = '';
		foreach ($values as $k => $f)
        {
           	$checked = '';
        	if(in_array($f->value, $selected_values)){
        		$checked = 'checked="checked"';
        	}
			$disabled = $f->disabled ? 'disabled="disabled"' : '';

			if ($this->disable_option_empty != 2 || empty($disabled)) {
				$html .= "\n\t<label class=\"lb-checkbox\" for=\"{$fieldname}_{$k}\"><input type=\"checkbox\" class=\"exfield exgroup{$field->group}\" name=\"{$fieldname}[]\" id=\"{$fieldname}_{$k}\" value=\"{$f->value}\" {$checked} {$disabled}/><span class=\"input-text\"> {$f->name}{$f->num_items_txt}</span></label>";
			}
        }
        return $html;
		
	}
	
	public function getSelect($fieldname, $field){

		$input = JFactory::getApplication()->input;

		$selected_values = $input->get($fieldname, null, 'RAW');
		if($field->jatype == 'xfield') {
			$values = $this->getXFieldValues($field);
			$attrs = array('class'=>'exfield select-filter exgroup'.$field->group);
		} else {
			$values = $field->value;
			$attrs = isset($field->attrs) ? $field->attrs : array();
			sort( $values, SORT_REGULAR );
		}
        
		$html[] = JHTML::_('select.option', 0, JText::sprintf('JAK2_SELECT_OPTION', $field->name));
		foreach ($values as $f) {
			if ($this->disable_option_empty != 2 || !$f->disabled) {
				$html[] = JHTML::_('select.option', $f->value, $f->name . $f->num_items_txt, 'value', 'text', $f->disabled);
			}
        }
		
        return JHTML::_('select.genericlist', $html, $fieldname, $attrs, 'value', 'text', $selected_values);
	}
	
	public function getMultipleSelect($fieldname, $field){
		$input = JFactory::getApplication()->input;
		$selected_values = $input->get($fieldname, array(), 'array');

		if($field->jatype == 'xfield') {
			$values = $this->getXFieldValues($field);
			$attrs = array('class'=>'exfield select-filter multiple exgroup'.$field->group);
		} else {
			$values = $field->value;
			$attrs = isset($field->attrs) ? $field->attrs : array();
		}
		$attrs['multiple'] = 'multiple';
     	$options = array();
        foreach ($values as $f) {
			if ($this->disable_option_empty != 2 || !$f->disabled) {
				$options[] = JHTML::_('select.option', $f->value, $f->name . $f->num_items_txt, 'value', 'text', $f->disabled);
			}
        }
	
        return JHTML::_('select.genericlist', $options, $fieldname.'[]', $attrs, 'value', 'text', $selected_values);
	}

	public function getRadioKeywords($selected='') {
		$jinput = JFactory::getApplication()->input;
        $options = array();
		$options[] = JHtml::_('select.option', 'exact', JText::_('MOD_JA_K2_FILTER_KEYWORD_EXACT'), 'value', 'text');
		$options[] = JHtml::_('select.option', 'all', JText::_('MOD_JA_K2_FILTER_KEYWORD_ALL'), 'value', 'text');
		$options[] = JHtml::_('select.option', 'any', JText::_('MOD_JA_K2_FILTER_KEYWORD_ANY'), 'value', 'text');

		$selected = $selected ? $selected : $jinput->get("st", $this->params->get('keyword_default_mode', 'exact'));
	
        return JHtml::_('select.radiolist', $options, 'st', array(), 'value', 'text', $selected);
	}

	public function getLabelMagicSelect($fieldname, $fieldtitle, $group = 0){
		$buttonid = 'g-'.$this->module->id.'-'.$fieldname;
		$listid = 'mg-'.$this->module->id.'-'.$fieldname;

		$css = 'select closed exfield';
		if($group) $css .= ' exgroup'.$group;
		$txt = JText::_('JAADD').' '.$fieldtitle;
		$label = "\n\t<label class=\"group-label\">".$fieldtitle;
		$label .= '<button type="button" id="'.$buttonid.'" class="'.$css.'" href="#" onclick="jaMagicSelect(this, \''.$listid.'\'); return false;" title="'.addslashes($txt).'">'.$txt.'</button>';
		$label .= '</label>';
		return $label;
	}
	
	public function getMagicSelect($fieldname, $field){
		$input = JFactory::getApplication()->input;
		$selected_values = $input->get($fieldname, array(), 'array');

		if($field->jatype == 'xfield') {
			$values = $this->getXFieldValues($field);
		} else {
			$values = $field->value;
		}

		$auto_filter		= (int) @$this->params->get('auto_filter');
		$listid = 'mg-'.$this->module->id.'-'.$fieldname;
		$html = '<div class="ja-magic-select" id="'.$listid.'" data-autofilter="'.$auto_filter.'"><ul>';

		foreach ($values as $f)
		{
			$cls = '';
			if(is_array($selected_values) && in_array($f->value, $selected_values)) {
				$cls = 'selected';
			}
			$cls .= $f->disabled ? ' disabled' : ' active';

			if ($this->disable_option_empty != 2 || !$f->disabled) {
				$html .= '<li id="'.$listid . '-' . $f->value .'" rel="'.$f->value.'" class="magic-item'.$cls.'">'.$f->name . $f->num_items_txt.'</li>';
			}
		}
		$html .= '</ul>';
		$html .= '<span class="btn-close" onclick="jaMagicSelectClose(this, \''.$listid.'\'); return false;">Close</span>';
		$html .= '<span class="arrow">&nbsp;</span>';
		$html .= '</div>';
		$html .= '<div id="'.$listid.'-container" class="ja-magic-select-container"></div>';

		$html .= '
<script type="text/javascript">
jQuery(document).ready( function(){
	jaMagicInit(\''.$listid.'\', \''.$fieldname.'\');
});
</script>';

        return $html;
	}
	
	public function getRadio($fieldname, $field){
		$input = JFactory::getApplication()->input;
		$selected_values = $input->get($fieldname, null);

		if($field->jatype == 'xfield') {
			$values = $this->getXFieldValues($field);
		} else {
			$values = $field->value;
		}
        $options = array();
		foreach ($values as $f) {
			if ($this->disable_option_empty != 2 || !$f->disabled) {
				$options[] = JHtml::_('select.option', $f->value, '<span class="input-text">' . $f->name . $f->num_items_txt . '</span>', 'value', 'text', $f->disabled);
			}
        }
		
        return JHtml::_('select.radiolist', $options, $fieldname, array('class'=>'exfield exgroup'.$field->group), 'value', 'text',$selected_values);
	}
	
	public function getDateField($name, $attrs) {
		$input = JFactory::getApplication()->input;
		$placeholder = isset($attrs['placeholder']) ? $attrs['placeholder'] : '';
		$class = isset($attrs['class']) ? $attrs['class'] : '';
		$custom = isset($attrs['custom']) ? $attrs['custom'] : '';
		$auto_filter = (int) $this->params->get('auto_filter');

		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::root(true) . '/modules/mod_jak2filter/assets/css/flatpickr.min.css');
		$document->addScript(JUri::root(true) . '/modules/mod_jak2filter/assets/js/flatpickr.min.js');
		$selected_values = $input->get($name, '');
		
		$html = '<div style="position:relative;">';
		
		$html .= '<input data-input type="text" data-k2-datetimepicker '
			. 'class="'.$class.'" id="'.$name.'_'.$this->module->id.'" '
			. 'name="'.$name.'" value="'.$selected_values.'" '
			. 'placeholder="'.$placeholder.'" '
			. 'onchange="validateDateRange(this);" '
			. $custom. ' '
			. 'style="cursor: pointer !important"/>';
		
		$html .= '<i class="fa fa-calendar input-button" title="toggle" data-toggle style="position: absolute;top: 10px;right: 8px;"></i>';
		$html .='</div>';
		
		$html .= '<script>jQuery("#'.$name.'_'.$this->module->id.'").flatpickr({allowInput:true})</script>';
		return $html;
	}
	
	public function getDate($fieldname, $field){
		$field->value = json_decode($field->value);
		return $this->getDateField($fieldname, array('class'=>'exfield exgroup'.$field->group));
	}

	public function getDaterange($fieldname, $field) {
		$field->value = json_decode($field->value);

		$datefrom = $this->getDateField($fieldname.'_from', array('custom'=>'data-field="'.$fieldname.'"', 'class'=>'k2datefrom exfield exgroup'.$field->group, 'placeholder'=> JText::_('JA_K2_FROM')));
		$dateto   = $this->getDateField($fieldname.'_to', array('custom'=>'data-field="'.$fieldname.'"', 'class'=>'k2dateto exfield exgroup'.$field->group, 'placeholder'=> JText::_('JA_K2_TO')));
		return $datefrom.'<label>-</label>'.$dateto;
	}
	
	public function getSearchDateCreate() {
		$options = array();
		$options[] = JHtml::_('select.option', '', JText::_('SELECT_DATE_RANGE'));
		$options[] = JHtml::_('select.option', 'today', JText::_('TODAY'));
		$options[] = JHtml::_('select.option', '-1d', JText::_('YESTERDAY'));
		$options[] = JHtml::_('select.option', '-7d', JText::_('LAST_7_DAYS'));
		$options[] = JHtml::_('select.option', '-1m', JText::_('LAST_30_DAYS'));
		$options[] = JHtml::_('select.option', 'tw', JText::_('THIS_WEEK'));
		$options[] = JHtml::_('select.option', 'lw', JText::_('LAST_WEEK'));
		$options[] = JHtml::_('select.option', 'tm', JText::_('THIS_MONTH'));
		$options[] = JHtml::_('select.option', 'lm', JText::_('LAST_MONTH'));
		$options[] = JHtml::_('select.option', 'range', JText::_('CUSTOM_RANGE'));

		$input = JFactory::getApplication()->input;
		$dtrange = $input->get('dtrange', '');
		$displayRange = $dtrange == 'range' ? 'block' : 'none';

		$attrbs = array('onchange' => "jaK2ShowDaterange(this, '#ja-custom-daterange-{$this->module->id}');");
		$datefrom = $this->getDateField('sdate', array('custom'=>'data-field="dtrange"','class'=>'k2datefrom','placeholder'=> JText::_('JA_K2_FROM')));
        $dateto = $this->getDateField('edate', array('custom'=>'data-field="dtrange"','class'=>'k2dateto','placeholder'=> JText::_('JA_K2_TO')));
		$html = '<div>
				'.JHtml::_('select.genericlist', $options, 'dtrange', $attrbs, 'value', 'text', $dtrange).'
				</div>
				<div id="ja-custom-daterange-'.$this->module->id.'" style="display:'.$displayRange.'; margin-top:20px;">
					<label>'.JText::_('JA_K2_FROM').'</label>'.$datefrom.'
					<label>'.JText::_('JA_K2_TO').'</label>'.$dateto.'
				</div>';
		return $html;
	}

	/**
	 * Get extra field groups
	 * @param $extrafields
	 * @return mixed
	 */
	public function getextraFieldsGroups($extrafields){
		$db = JFactory::getDbo();
		if(is_array($extrafields) && count($extrafields)>0){	
			$extrafields = implode(',', $extrafields);
		}
		$query = "SELECT `group` FROM #__k2_extra_fields WHERE `id` IN ($extrafields) GROUP BY `group`";
		
		$db->setQuery($query);
		$result = $db->loadColumn();
		return $result;
	}

	public function getextraFieldsGroupsByCat($categories){
		$db = JFactory::getDbo();
		if(is_array($categories) && count($categories)>0){
			$categories = implode(',', $categories);
		}
		$query = "SELECT `extraFieldsGroup` FROM #__k2_categories WHERE `id` IN ($categories) GROUP BY `extraFieldsGroup`";

		$db->setQuery($query);
		$result = $db->loadColumn();
		return $result;
	}

	/**
     * Get data from Sub Category K2 database
     * @param int $parent parent category id
     * @return array list object categories child
     */
    function fetchChild($parent)
    {
        $mainframe = JFactory::getApplication();
    	$user = JFactory::getUser();
        $aid = (int)$user->get('aid');
    	$db = JFactory::getDBO();
        $query = "SELECT id, extraFieldsGroup, alias, published, trash, name AS title, parent AS parent_id 
        		FROM #__k2_categories WHERE parent = '{$parent}' ";
    	$query .= " AND published=1 
								AND trash=0";
        $query .=" ORDER BY ordering ASC";
        $db->setQuery($query);
        $cats = $db->loadObjectList();

        return $cats;
    }

    /**
     *
     * Show element data on K2
     * @param int $id
     * @param strig $indent
     * @param array $list
     * @param int $maxlevel
     * @param int $level
     * @param int $type
     * @return array list categories element
     */
    function _fetchElement($id, $indent, $list, $maxlevel = 9999, $level = 0, $type = 1)
    {
        $children = $this->fetchChild($id);

        if (@$children && $level <= $maxlevel) {
            foreach ($children as $v) {
                $id = $v->id;

                if ($type) {
                    $pre = '|_&nbsp;';
                    $spacer = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                } else {
                    $pre = '- ';
                    $spacer = '&nbsp;&nbsp;';
				}

                if ($v->parent_id == 0) {
                    $txt = $v->title;
                } else {
                    $txt = $pre . $v->title;
				}
                $pt = $v->parent_id;
                $list[$id] = $v;
                $list[$id]->treename = "{$indent}{$txt}";
                $list[$id]->children = count(@$children);
                $list[$id]->haschild = true;
                $list = $this->_fetchElement($id, $indent . $spacer, $list, $maxlevel, $level + 1, $type);
			}
        } else {
			if(isset($list[$id])) {
				$list[$id]->haschild = false;
			}
        }
        return $list;
    }

	/**
	 * Get parent category Associated "Extra Fields Group"
	 * @param null $groupcategories
	 * @param null $row
	 * @param bool $hideTrashed
	 * @param bool $hideUnpublished
	 * @return array|mixed
	 */
	public function categoriesTree($groupcategories=NULL,$row = NULL, $hideTrashed = false, $hideUnpublished = true)
    {
		$categories = $this->_fetchElement(0, '', array());
		$mitems = array();
		foreach ($categories as $item) {
			if(isset($item->id) && $item->id > 0){
				if (!empty($groupcategories) && count($groupcategories)==0 || (is_array($groupcategories) && in_array($item->id, $groupcategories)) || (!is_array($groupcategories) && $item->id == $groupcategories)) {
					if ($item->trash)
						$item->treename .= ' [**'.JText::_('K2_TRASHED_CATEGORY').'**]';
					if (!$item->published)
						$item->treename .= ' [**'.JText::_('K2_UNPUBLISHED_CATEGORY').'**]';
					$num_items = $this->getNumItems('category', $item->id);
					$disabled = ($this->disable_option_empty && empty($num_items)) ? true : false;
					$num_items = (!empty($num_items)) ? sprintf(self::COUNT_ITEMS_TXT, $num_items) : '';
					if (!($this->disable_option_empty==2 && empty($num_items)))
						$mitems[] = JHTML::_('select.option', $item->id, $item->treename.$num_items,array('option.attr' => 'rel', 'disable' => $disabled, 'attr'=>array('rel' => $item->extraFieldsGroup)));
				}
			}
        }
        
        return $mitems;
    }
    
    /*
     * Return sub-category list
    */
    public function getSubCategories($category,$hidden = false){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query -> select('id') -> from ($db->quoteName('#__k2_categories')) -> where($db->quoteName('parent').'='.(int) $category);
        $db->setQuery($query);
        $SubCategories = $db->loadAssoclist();
        
        return $SubCategories;
    }

	/*
	 * Return category list
	 * */
	public function getCategories($groupcategories,$hidden = false){
		$jinput = JFactory::getApplication()->input;
		if(!empty($this->activeCats)) {
			//Dynamically select categories
			$groupcategories = $this->activeCats;
			//$hidden = true;
		}
		
		if(!$hidden){
			$cat_id = $jinput->getString('category_id',0);
			//$categories_option[]=JHTML::_('select.option', 0, JText::_('SELECT_CATEGORY_FRONT'));

			if(is_array($groupcategories)){
				if(($key = array_search(0, $groupcategories)) !== false) {
					unset($groupcategories[$key]);
				}
				$categories_option[]=JHTML::_('select.option', implode(",",$groupcategories), JText::_('SELECT_CATEGORY_FRONT'));
			}else{
				$categories_option[]=JHTML::_('select.option', 0, JText::_('SELECT_CATEGORY_FRONT'));
			}
			
			$categories = $this->categoriesTree($groupcategories,NULL, true, true);
			
			if(!empty($categories) && count($categories)>1){
				$categories_options=@array_merge($categories_option, $categories);
			}else{
				$categories_options = $categories;
			}
			
			$attribs = array('option.attr' => 'rel', 'option.key' => 'value', 'option.text' => 'text', 
							'list.attr' => array('class' => 'inputbox select-filter', 'onchange'=>'jak2DisplayExtraFields('.$this->module->id.',jQuery(this));'),
							'list.select' => $cat_id);
			
			//Important Note: do not add more parametter after $attribs, add them into $attribs
			$categories_html = JHTML::_('select.genericlist',  $categories_options, 'category_id', $attribs);
		} else {
			$cat_ids = is_array($groupcategories)?implode(',',$groupcategories):$groupcategories;
			$categories_html = '<input type="hidden" name="category_id" value="'.$cat_ids.'" />';
		}
		return $categories_html;
	}
	/**
	 * Hidden categories
	 * */
	/*
	 * Return author list selected
	 * */
	public function getAuthors($filter_author_display){
		$excluded_author = explode(',', $this->params->get('excluded_author', ''));
		$excluded_author = array_filter($excluded_author, function($item) {
			return (int) $item > 0;
		});

		$exclude = count($excluded_author) ? ' AND u.id NOT IN (' . implode(',', $excluded_author) . ')' : ''; 

		$db    =JFactory::getDBO();
		$display = $filter_author_display == 'author_display_name' ? 'u.name' : 'u.username';
		$query = "SELECT DISTINCT $display AS `name`, u.id AS `value`
		    FROM `#__users` AS u
		    INNER JOIN `#__k2_items` AS i ON u.id = i.created_by
	        WHERE i.published = 1
	        $exclude
	        ORDER BY u.id";
		
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

		if(!empty($rows) && count($rows)>0){
			foreach ($rows as &$row) {
				$num_items = $this->getNumItems('author', $row->value);
				$this->bindListData($row, $num_items);
			}
		}

		$fftype = $this->params->get('filter_by_author_fieldtype', 'select');
		$field = $this->createFieldObject(JText::_('JAK2_AUTHOR'), 'author', $fftype, $rows, array('class'=>'inputbox'));

		$func = 'get'.ucfirst($fftype);
		return call_user_func_array(array($this, $func), array('created_by', $field));
	}
	/**
	 * Get Tags in categories
	 * */
	public function getTags(){
		$mainframe = JFactory::getApplication();
		$jinput = $mainframe->input;
		$db = JFactory::getDbo();
		
		$cat_ids = $this->params->get('k2catsid',null);
		if(!empty($this->activeCats)) {
			//Dynamically get tags
			$cat_ids = $this->activeCats;
		}
		
		if($this->params->get('catMode', 0)) {
			$model = new JAK2FilterModelItemlist();
			$cat_ids = $model->getCategoryTree($cat_ids);
		}
		$cat_ids = is_array($cat_ids) ? implode(',',$cat_ids) : $cat_ids;
		
		$query ="SELECT t.id as value,t.name as name".
				" FROM #__k2_tags AS t".
				" LEFT JOIN #__k2_tags_xref AS tx ON t.id = tx.tagID";
		if ($cat_ids) {
			$query .= " LEFT JOIN #__k2_items as ki ON tx.itemID = ki.id";
			$query .= " WHERE ki.catid IN ($cat_ids) AND t.published=1";
		} else {
			$query .= " WHERE t.published=1";
		}
		$query .=" GROUP BY t.id";
		$db->setQuery( $query );
		
		$rows = $db->loadObjectList();

		if(!empty($rows) && count($rows)>0){
			foreach ($rows as &$row) {
				$num_items = $this->getNumItems('tag', $row->value);
				$this->bindListData($row, $num_items);
			}
		}

		 
		/*$tags_id = $jinput->getInt('tags_id',0);
		
		if($jinput->getString('tag') && $jinput->getString('option')=='com_k2'){
			$query ='SELECT id'.
				    ' FROM #__k2_tags';
			$query .= ' WHERE name LIKE "%'.$jinput->getString('tag').'%"';
			
			$db->setQuery( $query );
			
			$tags_id = $db->loadResult();
		}*/

		$fftype = $this->params->get('filter_by_tags_fieldtype', 'select');
		$field = $this->createFieldObject(JText::_('JAK2_TAGS'), 'tag', $fftype, $rows, array('class'=>'inputbox select-filter'));

		$func = 'get'.ucfirst($fftype);
		return call_user_func_array(array($this, $func), array('tags_id', $field));
				
	}
	
	public function getNumItems($type, $id, $optid = 0, $label = '') {
		static $data = null;
		if(!$this->display_counter) return '';
		
		$filter = '_all_';
		$byLabel = false;
		switch ($type) {
			case 'category':
				$filter = 'category_id';
				break;
			case 'author':
				$filter = 'created_by';
				break;
			case 'tag':
				$filter = 'tags_id';
				break;
			case 'xfield':
				$filter = 'xf_'.$id;
				if(!$optid) {
					$byLabel = true;
				}
				break;
		}
		if(!(is_array($data) && isset($data[$filter]))) {
			$data[$filter] = $this->getCounter(NULL, $filter, $byLabel);
		}
		if(is_array($data) && isset($data[$filter])) {
			if($byLabel) {
				$tkey = sprintf('%s_%d_%s', $type, $id, $label);
			} else {
				$tkey = sprintf('%s_%d_%d', $type, $id, $optid);
			}
			if(isset($data[$filter][$tkey])) {
				return $data[$filter][$tkey];
			}
		}
		return '';
	}
	
	public function getCounter($ordering = NULL, $filter = '', $byLabel = false) {
		static $sdata = null;
		static $sdataLabel = null;

		$user = JFactory::getUser();
		$jinput = JFactory::getApplication()->input;
        $aid = $user->get('aid');
        $db = JFactory::getDBO();
        $params = K2HelperUtilities::getParams('com_k2');
        $task = $jinput->get('task', '', 'CMD');
        
		$model = new JAK2FilterModelItemlist();
        
        // update get counter match with dynamic mode.
		$mode = $this->params->get('form_mode', 'normal');
		if($mode == 'dynamic') {
			$groupcategories = $this->activeCats;
		} else {
			$groupcategories = $this->params->get('k2catsid',null);
		}
		if($this->params->get('catMode', 0)) {
			$groupcategories = $model->getCategoryTree($groupcategories);
		}
		$filter_categories = is_array($groupcategories)?implode(',',$groupcategories):$groupcategories;
        
        //
        $where = '';
        if($this->update_counter) {
			$badchars = array('#', '>', '<', '\\');
	        $search = JString::trim(JString::str_ireplace($badchars, '', $jinput->get('searchword', null, 'STRING')));
			
			$category_id = explode(',', $jinput->getString('category_id'));
			$catids = array_map(function($catid) {
				return $catid;
			}, $category_id);

			$where = $model->prepareSearch($search, $filter, $filter_categories, $catids);
        }
		
		if(empty($where)) {
			if($byLabel) {
				if(is_null($sdataLabel)) {
					//cache data for the case of search condition is empty
					$query = "SELECT `num_items`, CONCAT_WS('_', `type`, `asset_id`, `labels`) AS tkey FROM #__jak2filter_taxonomy";
					$db->setQuery($query);
					$sdataLabel = $db->loadAssocList('tkey', 'num_items');
				}
				return $sdataLabel;
			} else {
				if(is_null($sdata)) {
					//cache data for the case of search condition is empty
					$query = "SELECT `num_items`, CONCAT_WS('_', `type`, `asset_id`, `option_id`) AS tkey FROM #__jak2filter_taxonomy";
					$db->setQuery($query);
					$sdata = $db->loadAssocList('tkey', 'num_items');
				}
				return $sdata;
			}
		} else {
			$tags_id = $jinput->get('tags_id');
		
			$rating = $jinput->get('rating', '', 'STRING');
	
	        $jnow = JFactory::getDate();
	        $now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
	        $nullDate = $db->getNullDate();
	
	        if ($jinput->get('format') == 'feed')
	            $limit = $params->get('feedLimit');
	
	        $subquery = "SELECT i.id";
	        $subquery .= " FROM #__k2_items as i RIGHT JOIN #__k2_categories AS c ON c.id = i.catid";
	
	        if ($ordering == 'best' || $rating)
	            $subquery .= " LEFT JOIN #__k2_rating r ON r.itemID = i.id";
	
			if ($task == 'tag' || $tags_id)
				$subquery .= " LEFT JOIN #__k2_tags_xref AS tags_xref ON tags_xref.itemID = i.id LEFT JOIN #__k2_tags AS tags ON tags.id = tags_xref.tagID";
	
	        $subquery .= " WHERE i.published = 1 AND ";
	
	        if (K2_JVERSION != '15')
	        {
	            $subquery .= "i.access IN(".implode(',', $user->getAuthorisedViewLevels()).")"." AND i.trash = 0"." AND c.published = 1"." AND c.access IN(".implode(',', $user->getAuthorisedViewLevels()).")"." AND c.trash = 0";
	
	            $mainframe = JFactory::getApplication();
	            $languageFilter = $mainframe->getLanguageFilter();
	            if ($languageFilter)
	            {
	                $languageTag = JFactory::getLanguage()->getTag();
	                $subquery .= " AND c.language IN (".$db->quote($languageTag).",".$db->quote('*').") 
							AND i.language IN (".$db->quote($languageTag).",".$db->quote('*').")";
	            }
	        }
	
	        if (!($task == 'user' && !$user->guest && $user->id == $jinput->get('id', 0, 'INT')))
	        {
	            $subquery .= " AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." )";
	            $subquery .= " AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." )";
	        }
	        
	        $subquery .= $where;
	        
	        //GET DYNAMIC COUNTER
			$field = $byLabel ? 'labels' : 'option_id';
			$query = "
				SELECT COUNT(tm.item_id) AS num_items, CONCAT_WS('_', `type`, `asset_id`, ".$db->quoteName($field).") AS tkey
				FROM #__jak2filter_taxonomy t
				INNER JOIN #__jak2filter_taxonomy_map tm ON tm.node_id = t.id
				WHERE tm.item_id IN (
				".$subquery."
				)
				GROUP BY tkey
				";
			$db->setQuery($query);
			$data = $db->loadAssocList('tkey', 'num_items');
			return $data;
		}
		
	}

	public function getOrderingList() {
		$jinput = JFactory::getApplication()->input;
		$options = array();
		$options[] = JHtml::_('select.option', '', JText::_('JDEFAULT'));

		$activeGroups = null;
		if(!empty($this->activeCats)) {
			$activeGroups = $this->getextraFieldsGroupsByCat($this->activeCats);
		}
		$fields = jaK2GetOrderFields($activeGroups);

		$default = array('date', 'rdate', 'publishUp', 'alpha', 'ralpha', 'order', 'rorder', 'featured', 'hits', 'best', 'modified', 'rand');
		$display = $this->params->get('show_order_field', $default);
		if(empty($display)) {
			$options2 = $fields;
		} else {
			$options2 = array();
			$openGroup = false;

			foreach($fields as $field) {
				if(in_array($field->value, $display) || $field->value == '<OPTGROUP>' || $field->value == '</OPTGROUP>') {
					if($field->value == '</OPTGROUP>' && $openGroup) {
						//remove empty group
						$openGroup = false;
						array_pop($options2);
					} else {
						$openGroup = ($field->value == '<OPTGROUP>');
						$options2[] = $field;
					}
				}
			}
		}

		$options = array_merge($options, $options2);

		$attribs = array('class' => 'inputbox');
		$ordering = $jinput->get('ordering', '');
		if(!$ordering) {
			if($this->params->get('catOrdering', 'inherit') == 'inherit') {
				$ordering = $this->comParams->get('catOrdering', 'inherit');
			} else {
				$ordering = $this->params->get('catOrdering', 'inherit');
			}
		}

		$html = JHTML::_('select.genericlist',  $options, 'ordering', $attribs, 'value', 'text', $ordering);
		return $html;
	}

	/**
	 * @param $name - form field name
	 * @param $jatype - JA field type (author, tags, category, xfield, ...)
	 * @param $ff_type - Form field type (select, checkbox, ...)
	 * @param $value - field value
	 * @param array $attrs - form field attributes
	 * @return stdClass
	 */
	private function createFieldObject($name, $jatype, $ff_type, $value, $attrs = array()) {
		$obj = new stdClass();
		$obj->id = 0;
		$obj->name = $name;
		$obj->jatype = $jatype;
		$obj->ff_type = $ff_type;
		$obj->value = $value;
		$obj->group = 0;
		$obj->group_name = '';
		$obj->index = '';
		$obj->attrs = $attrs;
		return $obj;
	}

	public function getLabel($fieldtype, $fieldname, $fieldtitle, $group=0) {
		$required_fields = $this->params->get('required_field', array());
		$required_defined = $this->params->get('required_defined', 0);
		if ($required_defined==2 && in_array($fieldname, $required_fields)) $fieldtitle .= ' *';
		$fn = ucfirst(strtolower($fieldtype));
		$funcLabel = 'getLabel'.$fn;
		if(method_exists($this, $funcLabel)) {
			$html = call_user_func_array(array($this, $funcLabel), array($fieldname, $fieldtitle, $group));
		} else {
			$html = "\n\t<label class=\"group-label\">{$fieldtitle}</label>";
		}
		return $html;
	}
	
	public function getJak2depend($fieldname, $extraField) {
		$input = JFactory::getApplication()->input;

		$dependarray = $input->get($fieldname."_array",'', 'STRING');
		$selected_values = explode(',', rtrim($dependarray, ','));
		$selected_txt = $input->get($fieldname."_txt",'', 'STRING');

		$defaultValues = json_decode($extraField->value);
		$default = $defaultValues[0];
		if($selected_values){
			$default->value = $selected_values;
		} else {
			//do not use default value for search form
			$default->value = '';
		}

		$extension = 'com_jak2filter';

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.id, a.title, a.level, a.lft, a.rgt')
			->from('#__categories AS a')
			->where('a.parent_id = 1')
			->where('extension = ' . $db->quote($extension))
			->where($db->quoteName('alias').' = ' . $db->quote($default->alias));
		$db->setQuery($query);
		$cat = $db->loadObject();
		if($cat) {
			$items = $this->getMultiLevelOptions($extension, $cat, array('filter.published' => 1), 'depend');

			$options = array();
			$options[] = JHtml::_('select.option', '', JText::sprintf('JAK2_SELECT_OPTION', $extraField->name));
			$lvarr = array();
			$lv=0;//detect first lv.
			$numb=array();
			foreach ($items as $item) {
				if ($lv==0)
					$lv = $item->level;
				$num_items = $this->getNumItems('xfield', $extraField->id, 0, $item->id);
				$this->bindListData($item, $num_items);
// 				if (!($this->disable_option_empty==2 && empty($num_items))) {
					if ($lv!=0 && $lv==$item->level) // only get first lv.
						$options[] = JHtml::_('select.option', $item->id, $item->title, 'value', 'text', $item->disabled);
					$numb[$item->level] = $item->level;
					$lvarr[$item->parent_id][] = array($item->id, $item->title, $item->disabled);
// 				}
			}
			$active = $default->value;

			$title = ($this->getExtraFieldParam('jak2depend_title', $extraField->index));
			$jak2depend_numfilter = $this->getExtraFieldParam('jak2depend_numfilter', $extraField->index);
			$html = '$jak2depend['.$extraField->id.'] = '.json_encode($lvarr).';';
			JFactory::getDocument()->addScriptDeclaration($html);

			$numb = count($numb);
			$html = '';
			$title = explode(',',$title);
			$params = $this->params;
			$ja_column = '';
			if($params->get('ja_stylesheet') == 'horizontal-layout' && $params->get('ja_column') && $params->get('ja_column') > 0){
				$ja_column	= 'width:'.round(100/$params->get('ja_column'),2).'%;';
			}
			$style = '';
			if($ja_column){
				$style ='style="'.$ja_column.'"';
			}
			$colClass = "jacol-" . $params->get('ja_column', 2);

			for ($i=1;$i<$numb;$i++) {
				$_option = array();
				$extitle='';
				if (!empty($title[($i-1)])) {
					$extitle = '<label class="group-label">'.$title[($i-1)].'</label>';
					$_option[] = JHtml::_('select.option', '', JText::sprintf('JAK2_SELECT_OPTION', $title[($i-1)]));
				} else
					$_option[] = JHtml::_('select.option', '', JText::sprintf('JAK2_SELECT_OPTION', ''));
				foreach ($items as $item) {
					if (!empty($selected_values[($i-1)]) && $item->parent_id == $selected_values[($i-1)]) 
						$_option[] = JHtml::_('select.option', $item->id, $item->title, 'value', 'text', $item->disabled);
				}

				$html.= '</div></li><li class="'.$colClass.'">'.$extitle.''.JHtml::_('select.genericlist', $_option, '', 'class="exfield jak2depend exgroup'.$extraField->group.'" data-extitle="'.JText::sprintf('JAK2_SELECT_OPTION', $title[($i-1)]).'" data-autofield="'.$jak2depend_numfilter.'" data-exfield="'.$extraField->id.'" data-dependlv="'.($i+1).'"', 'value', 'text', $active, 'K2ExtraField_'.$extraField->id.'_'.($i+1).'').'<div class="subclass">';
			}

			return '<input type="hidden" name="'.$fieldname.'_array" class="jak2dependarray" value="'.$dependarray.'" id="'.$fieldname.'_array" /><input type="hidden" name="'.$fieldname.'_txt" class="jak2dependtxt" value="'.$selected_txt.'" id="'.$fieldname.'_txt" />'.JHtml::_('select.genericlist', $options, '', 'class="exfield jak2depend exgroup'.$extraField->group.'" data-exfield="'.$extraField->id.'" data-autofield="'.$jak2depend_numfilter.'" data-dependlv="1"', 'value', 'text', $active, 'K2ExtraField_'.$extraField->id.'_1').$html;
		} else {
			return false;
		}
	}

	protected function getMultiLevelField($fieldname, $extraField) {

		$input = JFactory::getApplication()->input;

		$selected_values = $input->get($fieldname."_txt",'', 'STRING');

		$defaultValues = json_decode($extraField->value);
		$default = $defaultValues[0];
		if($selected_values){
			$default->value = $selected_values;
		} else {
			//do not use default value for search form
			$default->value = '';
		}

		$extension = 'com_jak2filter';

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.id, a.title, a.level, a.lft, a.rgt')
			->from('#__categories AS a')
			->where('a.parent_id = 1')
			->where('extension = ' . $db->quote($extension))
			->where($db->quoteName('alias').' = ' . $db->quote($default->alias));
		$db->setQuery($query);
		$cat = $db->loadObject();
		if($cat) {
			$items = $this->getMultiLevelOptions($extension, $cat);

			$options = array();
			$options[] = JHtml::_('select.option', '', JText::sprintf('JAK2_SELECT_OPTION', $extraField->name));
			foreach ($items as $item) {
				$num_items = $this->getNumItems('xfield', $extraField->id, 0, $item->id);
				$this->bindListData($item, $num_items);
				if (!($this->disable_option_empty==2 && empty($num_items)))
					$options[] = JHtml::_('select.option', $item->id, $item->title.$item->num_items_txt, 'value', 'text', $item->disabled);
			}
			$active = $default->value;


			return JHtml::_('select.genericlist', $options, $fieldname.'_txt', 'class="exfield exgroup'.$extraField->group.'"', 'value', 'text', $active, 'K2ExtraField_'.$extraField->id);
		} else {
			return false;
		}
	}

	public function getMultiLevelOptions($extension, $parent = null, $config = array('filter.published' => 1), $jaMultitype='default')
	{
		$config = (array) $config;
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.id, a.title, a.level, a.parent_id')
			->from('#__categories AS a')
			->where('a.parent_id > 0');

		// Filter on extension.
		$query->where('extension = ' . $db->quote($extension));

		// Filter on parent.
		if ($parent)
		{
			$query->where('a.lft > ' . (int) $parent->lft);
			$query->where('a.rgt < ' . (int) $parent->rgt);
		}
		// Filter on the published state
		if (isset($config['filter.published']))
		{
			if (is_numeric($config['filter.published']))
			{
				$query->where('a.published = ' . (int) $config['filter.published']);
			}
			elseif (is_array($config['filter.published']))
			{
				JArrayHelper::toInteger($config['filter.published']);
				$query->where('a.published IN (' . implode(',', $config['filter.published']) . ')');
			}
		}

		// Filter on the language
		if (isset($config['filter.language']))
		{
			if (is_string($config['filter.language']))
			{
				$query->where('a.language = ' . $db->quote($config['filter.language']));
			}
			elseif (is_array($config['filter.language']))
			{
				foreach ($config['filter.language'] as &$language)
				{
					$language = $db->quote($language);
				}

				$query->where('a.language IN (' . implode(',', $config['filter.language']) . ')');
			}
		}

		$query->order('a.lft');

		$db->setQuery($query);
		$items = $db->loadObjectList();
		if ($jaMultitype == 'default')
 			foreach ($items as &$item)
 			{
 				$repeat = ($item->level - 2 >= 0) ? $item->level - 2 : 0;
 				$item->title = str_repeat('- ', $repeat) . $item->title;
 			}

		return $items;
	}
}

function highlight_required_field($field, $arr) {
	if (in_array($field, $arr)) return '*';
	else return '';
}

function jaK2GetOrderFieldsValues() {
	$input = JFactory::getApplication()->input;
	$order_values = $input->get('ordering', '', 'STRING');
	$order_fields = $input->get('orders', array(), 'ARRAY');
// 	if (!array_key_exists(ltrim($order_values, 'r'), $order_fields) && !empty($order_values))
// 		$order_fields[] = $order_values;
	return $order_fields;
}

function jaK2GetOrderFields($activeGroups = null) {
	$options = array();
	$options[] = JHtml::_('select.option', 'zelevance', JText::_('RELEVANCE'));
	$options[] = JHtml::_('select.option', 'adate', JText::_('JAK2_OLDEST_FIRST'));
	$options[] = JHtml::_('select.option', 'zdate', JText::_('JAK2_MOST_RECENT_FIRST'));
	$options[] = JHtml::_('select.option', 'publishUp', JText::_('JAK2_RECENTLY_PUBLISHED'));
	$options[] = JHtml::_('select.option', 'alpha', JText::_('JAK2_TITLE_ALPHABETICAL'));
// 	$options[] = JHtml::_('select.option', 'ralpha', JText::_('JAK2_TITLE_REVERSEALPHABETICAL'));
	$options[] = JHtml::_('select.option', 'order', JText::_('JAK2_ORDERING'));
// 	$options[] = JHtml::_('select.option', 'rorder', JText::_('JAK2_ORDERING_REVERSE'));
	$options[] = JHtml::_('select.option', 'featured', JText::_('JAK2_FEATURED_FIRST'));
	$options[] = JHtml::_('select.option', 'hits', JText::_('JAK2_MOST_POPULAR'));
	$options[] = JHtml::_('select.option', 'best', JText::_('JAK2_HIGHEST_RATED'));
	$options[] = JHtml::_('select.option', 'modified', JText::_('JAK2_LATEST_MODIFIED'));
	$options[] = JHtml::_('select.option', 'zand', JText::_('JAK2_RANDOM_ORDERING'));
	//Extra Fields
	$db = JFactory::getDbo();
	$aXFSupported = array('select', 'multipleSelect', 'radio', 'labels', 'textfield', 'date');
	$query = "SELECT f.`id`, f.name, f.group, g.name AS group_name
			FROM #__k2_extra_fields f
			INNER JOIN #__k2_extra_fields_groups g ON f.group = g.id
			WHERE f.`type` IN ('".implode("','", $aXFSupported). "')
			ORDER BY g.name, f.name";
	$db->setQuery($query);
	$items = $db->loadObjectList();
	if(!empty($items)) {
		$params = JComponentHelper::getParams('com_jak2filter');
		$xfDataType = $params->get('extra_fields_data_type');

		$dt = array();
		if(is_array($xfDataType) && count($xfDataType)) {
			foreach($xfDataType as $val) {
				@list($xfid, $type) = explode(':', $val);
				$dt[$xfid] = $type;
			}
		}

		$group = '';
		foreach($items as $item) {
			if(is_array($activeGroups) && !in_array($item->group, $activeGroups)) {
				//get Dynamic order by options
				continue;
			}
			$type = isset($dt[$item->id]) ? $dt[$item->id] : 'string';
			if($group != $item->group_name) {
				if($group != '') {
					$options[] = JHtml::_('select.option', '</OPTGROUP>', $group);
				}
				$group = $item->group_name;
				$options[] = JHtml::_('select.option', '<OPTGROUP>', $group);
			}
			if($type == 'number') {
				$options[] = JHtml::_('select.option', 'xf'.$item->id, JText::sprintf('XFIELD_ORDER_ASCENDING', $item->name));
// 				$options[] = JHtml::_('select.option', 'rxf'.$item->id, JText::sprintf('XFIELD_ORDER_DESCENDING', $item->name));
			} else {
				$options[] = JHtml::_('select.option', 'xf'.$item->id, JText::sprintf('XFIELD_ORDER_ALPHABETICAL', $item->name));
// 				$options[] = JHtml::_('select.option', 'rxf'.$item->id, JText::sprintf('XFIELD_ORDER_REVERSE_ALPHABETICAL', $item->name));
			}
		}
		$options[] = JHtml::_('select.option', '</OPTGROUP>', $group);
	}

	return $options;
}
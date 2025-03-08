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

defined('JPATH_PLATFORM') or die;

/*jimport('joomla.form.formfield');
include_once(JPATH_LIBRARIES.'/joomla/form/fields/checkboxes.php');*/
/**
 * Form Field class for the Joomla Platform.
 * Supports a one line text field.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @link        http://www.w3.org/TR/html-markup/input.text.html#input.text
 * @since       11.1
 */

class JFormFieldK2extrafield extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  11.1
	 */
	protected $type = 'K2extrafield';
		public function getControlGroup()
		{
			if ($this->hidden)
			{
				return $this->getInput();
			}

			return
				'<div class="control-group control-xfgroup">'
				. '<div class="controls">' . $this->getInput() . '</div>'
				. '</div>';
		}
	
	/**
	 * Method to get the field input markup for check boxes.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	function getLabel(){
	}
	function legend(){
		return '<legend class="jalegend">' . JText::_($this->element['label']) .'</legend>';
	}
	protected function getInput()
	{
		if( JFile::exists(JPATH_ROOT.'/components/com_k2/k2.php')){
		// Initialize variables.
		$html = array();

		$this->getModuleParams();
		// Initialize some field attributes.
		$class = $this->element['class'] ? ' class="checkboxes ' . (string) $this->element['class'] . '"' : ' class="checkboxes"';

		// Start the checkbox field output.
		$html[] = '<fieldset id="' . $this->id . '"' . $class . '>';
		$html[] = $this->legend();
		// Get the field options.
		$options = $this->getOptions();

		// Build the checkbox field output.
		
		$group = 0;
		$params = $this->getModuleParams();
		$groups = array();
		foreach ($options as $i => $option)
		{
			if($group != $option->group) {
				$group = $option->group;
				$groups[$option->group]=$option->gname;	
			}
		}

		$xgroup_order = $params->get('xgroup_order', array());
		$xgroup = $params->get('xgroup', array());

		$gindex = 0;
		foreach($groups AS $key=>$g){
			$html[] = '<h4 class="jagroup">
							<input class="xgroup-status" type="checkbox" name="jform[params][xgroup][]" value="'.$key.'" '.(in_array($key, $xgroup) ? 'checked' : '').'/>
							<span xonclick="$(\'xfield_group_' . $key . '\').toggle();" title="'.JText::_('Collapse / Expand', true).'">
							'.JText::_('EXTRA_FIELDS_GROUP').$g.'
							</span>
						</h4>';
			$html[] = '<div class="xgroup-container"></div>';
			$html[] = '<script id="tpl_xfield_group_' . $key . '" type="text/template" />';
			$html[] = '<ul class="extrafields" id="xfield_group_' . $key . '">';
			$k=0;

			$order = isset($xgroup_order[$gindex]) ? $xgroup_order[$gindex] : $gindex;
			$gindex++;
			$html[] = '
			<li class="odd">
					<a href="#" onclick="return jaSelectXfieldGroup(\'xfield_group_' . $key . '\', true);">'
					. JText::_('JGLOBAL_SELECTION_ALL') .
					'</a>
					&nbsp;|&nbsp;
					<a href="#" onclick="return jaSelectXfieldGroup(\'xfield_group_' . $key .'\', false);">'
					. JText::_('JGLOBAL_SELECTION_NONE') .
					'</a>
					<div class="ordering">'
					. JText::_('JFIELD_ORDERING_LABEL') .
					' <input type="text" name="'.$this->getName('xgroup_order').'" value="'.$order.'" size="2" style="float:none;" />
					</div>
			</li>';
			foreach($options as $i => $option){
				if($option->group == $key){
					if($k%2 == 0){
						$class_li = 'class="even"';
					}else{
						$class_li = 'class="odd"';
					}
					
					$k++;
					// Initialize some option attributes.
					$checked = (in_array((string) $option->value, (array) $this->value) ? ' checked="checked"' : '');
					$class = !empty($option->class) ? ' class="' . $option->class . '"' : '';
					$disabled = !empty($option->disable) ? ' disabled="disabled"' : '';
					// Initialize some JavaScript option attributes.
					$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';

					$field = $this->getFieldType($option, $params);
					if(!$field) continue;
					$html[] = '<li '.$class_li.'>';
					$html[] = '<input type="checkbox" id="' . $this->id . $i . '" name="' . $this->name . '"' . ' value="'
						. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $onclick . $disabled . '/>';
					$html[] = '<label ' . $class . '>' . JText::_($option->text) . '</label><br />';
					
					$html[] = $field;
					$html[] = '</li>';
				}
			}
			$html[] = '</ul>';
			$html[] = '</script>';
		}
		
		// End the checkbox field output.
		$html[] = '</fieldset>';
		$html[] = '<script type="text/javascript">
					/*<![CDATA[*/
						function jaSelectXfieldGroup(name, checked)
						{		
							$$("#" + name + " li > input[type=checkbox]").each(function(el) { el.checked = checked; });
							return false;
						}
					/*]]>*/
					</script>';
			
		return implode($html);
		}
		return;
		
	}
	
	protected function getModuleParams() {
		$jinput = JFactory::getApplication()->input;
		$modid = $jinput->getInt('id');
		$tableMod = JTable::getInstance('Module', 'JTable');
		$tableMod->load($modid);
		
		$data = isset($tableMod->params) ? $tableMod->params : '';
		$params = new JRegistry($data);
		return $params;
	}
	
	protected function getOptions()
	{
		$db = JFactory::getDbo();
		$query = "
			SELECT f.id, f.name AS fname, f.group, f.type, f.published, g.name AS gname, f.value
			FROM #__k2_extra_fields f
			INNER JOIN #__k2_extra_fields_groups g ON g.id = f.group
			WHERE f.published = 1
			AND f.type <> 'csv'
			ORDER BY f.group, f.ordering
			";
		$db->setQuery($query);
		$list = $db->loadAssocList();
		// Initialize variables.
		$options = array();

		if(count($list)) {
			foreach ($list as $option)
			{
	
				// Create a new option object based on the <option /> element.
				$tmp = JHtml::_(
					'select.option', $option['id'], $option['fname'] . ' (ID: '.$option['id'].') <span class="ja-field-note">'.JText::sprintf('K2_FIELD_TYPE', $option['type']).'</span>', 'value', 'text',
					($option['published'] == 0)
				);
	
				// Set some option attributes.
				$tmp->class = '';
	
				// Set some JavaScript option attributes.
				$tmp->onclick 	= '';
				$tmp->title		= $option['fname'];
				$tmp->type 		= $option['type'];
				$tmp->group 	= $option['group'];
				$tmp->gname 	= $option['gname'];
				$tmp->exvalue 	= $option['value'];
	
				// Add the option object to the result set.
				$options[] = $tmp;
			}
		}

		reset($options);

		return $options;
	}
	
	protected function getFieldType($option, $params) {
		//$name = $this->element['name'].'_type'.$option->value;
		$prefix = $option->value.':';
		$name = 'filter_by_fieldtype';
		$id = $name.'_'.$option->value;
		$idContainer = $id.'_container_';
		$typeOptions = array();
		$type = $option->type;
		
		$showRangeField = 0;
		$attribs = array();
		$attribs['onchange'] = "jaExtraFieldParams('{$idContainer}', this);";
		
		switch ($type) {
			case 'select':
			case 'multipleSelect':
			case 'radio':
				$typeOptions[] = JHTML::_('select.option', $prefix.'checkbox', JText::_('FILTER_TYPE_CHECKBOX'));
				$typeOptions[] = JHTML::_('select.option', $prefix.'select', JText::_('FILTER_TYPE_DROPDOWN_SELECTION'));
				$typeOptions[] = JHTML::_('select.option', $prefix.'multipleSelect', JText::_('FILTER_TYPE_MULTISELECT_LIST'));
				$typeOptions[] = JHTML::_('select.option', $prefix.'radio', JText::_('FILTER_TYPE_RADIO_BUTTONS'));
				$typeOptions[] = JHTML::_('select.option', $prefix.'magicSelect', JText::_('FILTER_TYPE_MAGIC_SELECTION'));
			break;
			case 'date':
				$typeOptions[] = JHTML::_('select.option', $prefix.'date', JText::_('FILTER_TYPE_DATE'));
				$typeOptions[] = JHTML::_('select.option', $prefix.'daterange', JText::_('FILTER_TYPE_DATERANGE'));
				break;
			case 'csv':
			case 'link':
				//unsupported extra field types
				break;
			case 'labels':
				$showRangeField = 1;
				$typeOptions[] = JHTML::_('select.option', $prefix.'magicSelect', JText::_('FILTER_TYPE_MAGIC_SELECTION'));
				$typeOptions[] = JHTML::_('select.option', $prefix.'checkbox', JText::_('FILTER_TYPE_CHECKBOX'));
				$typeOptions[] = JHTML::_('select.option', $prefix.'select', JText::_('FILTER_TYPE_DROPDOWN_SELECTION'));
				$typeOptions[] = JHTML::_('select.option', $prefix.'multipleSelect', JText::_('FILTER_TYPE_MULTISELECT_LIST'));
				$typeOptions[] = JHTML::_('select.option', $prefix.'textfield', JText::_('FILTER_TYPE_TEXT_FIELD'));
				break;	
			case 'textfield':
			case 'jak2depend':
				$alias = json_decode($option->exvalue);
				$alias = $alias[0]->alias;
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('a.id, a.title, a.level, a.lft, a.rgt')
					->from('#__categories AS a')
					->where('a.parent_id = 1')
					->where('extension = ' . $db->quote('com_jak2filter'))
					->where($db->quoteName('alias').' = ' . $db->quote(strtolower($alias)));
				$db->setQuery($query);
				$cat = $db->loadObject();
				if ($cat) {
					$typeOptions[] = JHTML::_('select.option', $prefix.'textfield', JText::_('FILTER_TYPE_TEXT_FIELD'));
					$typeOptions[] = JHTML::_('select.option', $prefix.'jak2depend', JText::_('FILTER_TYPE_DEPEND_RANGE'));
					break;
				}
			case 'textarea':
			
			default:
				$showRangeField = 1;
				$typeOptions[] = JHTML::_('select.option', $prefix.'textfield', JText::_('FILTER_TYPE_TEXT_FIELD'));
				$typeOptions[] = JHTML::_('select.option', $prefix.'valuerange', JText::_('FILTER_TYPE_VALUE_RANGE'));
				$typeOptions[] = JHTML::_('select.option', $prefix.'rangeslider', JText::_('FILTER_TYPE_RANGE_SLIDER'));
				break;
		}

		if(!count($typeOptions)) return '';
		
		//$list = '<span>'.JText::_('SELECT_SEARCH_TYPE').'</span>';
		$values = $params->get($name);
		$val = $prefix.$type;
		$selectedIndex = -1;
		if(is_array($values) && count($values)) {
			foreach ($values as $index => $valSelected) {
				if (strpos($valSelected, $prefix) === 0) {
					$selectedIndex = $index;
					$val = $valSelected;
					break;
				}
			}
		}
		
		$cssValueRange = ($val == $prefix.'valuerange') ? ' display:block;' : ' display:none;';
		$cssRangeSlider = ($val == $prefix.'rangeslider') ? ' display:block;' : ' display:none;';
		$cssValueDepend = ($val == $prefix.'jak2depend') ? ' display:block;' : ' display:none;';
        $cssRange_Slider = ($val == $prefix.'valuerange' || $val == $prefix.'rangeslider') ? ' display:block;' : ' display:none;';
		
		$attribs['class'] = 'hasTip';
		$attribs['title'] = JText::sprintf('SELECT_SEARCH_TYPE', $option->title, $option->title);
		$list = JHTML::_('select.genericlist', $typeOptions, $this->getName($name), $attribs, 'value', 'text', $params->get($name, $val), $id);
		
		//EXTRA PARAMS FOR EACH FIELD TYPE
		
		//multi params
		$list .= '<div id="'.$idContainer.'jak2depend" class="params-container" style="'.$cssValueDepend.'">';
		$list .= $this->getFieldTypeParam($option, 'jak2depend_numfilter', JText::_('JA_NUMFILTER'), JText::_('JA_NUMFILTER_DESC'), $params, $selectedIndex);
		$list .= $this->getFieldTypeParam($option, 'jak2depend_title', JText::_('JA_DEPEND_TITLE'), JText::_('JA_DEPEND_TITLE_DESC'), $params, $selectedIndex);
		$list .= '</div>';
		
		//value range params
		$list .= '<div id="'.$idContainer.'valuerange" class="params-container" style="'.$cssValueRange.'">';
		$list .= $this->getFieldTypeParam($option, 'filter_by_fieldrange', JText::_('RANGE'), JText::_('JA_VALUE_RANGE_DESC'), $params, $selectedIndex, 'size="30"');
		$list .= '</div>';
		
		//range slider params
		$list .= '<div id="'.$idContainer.'rangeslider" class="params-container" style="'.$cssRangeSlider.'">';
		$list .= $this->getFieldTypeParam($option, 'rangeslider_min', JText::_('RANGE_SLIDER_MIN'), JText::_('RANGE_SLIDER_MIN_DESC'), $params, $selectedIndex);
		$list .= $this->getFieldTypeParam($option, 'rangeslider_max', JText::_('RANGE_SLIDER_MAX'), JText::_('RANGE_SLIDER_MAX_DESC'), $params, $selectedIndex);
		$list .= $this->getFieldTypeParam($option, 'rangeslider_start', JText::_('RANGE_SLIDER_START'), JText::_('RANGE_SLIDER_START_DESC'), $params, $selectedIndex);
		$list .= $this->getFieldTypeParam($option, 'rangeslider_stop', JText::_('RANGE_SLIDER_STOP'), JText::_('RANGE_SLIDER_STOP_DESC'), $params, $selectedIndex);
		$list .= $this->getFieldTypeParam($option, 'rangeslider_step', JText::_('RANGE_SLIDER_STEP'), JText::_('RANGE_SLIDER_STEP_DESC'), $params, $selectedIndex);
		$list .= '</div>';

        //label format
        $list .= '<div id="'.$idContainer.'format" class="params-container" style="'.$cssRange_Slider.'">';
        $list .= $this->getFieldTypeParam($option, 'range_slider_format_prefix', JText::_('RANGE_SLIDER_FORMAT_PREFIX'), JText::_('RANGE_SLIDER_FORMAT_PREFIX_DESC'), $params, $selectedIndex);
        $list .= $this->getFieldTypeParam($option, 'range_slider_format_suffix', JText::_('RANGE_SLIDER_FORMAT_SUFFIX'), JText::_('RANGE_SLIDER_FORMAT_SUFFIX_DESC'), $params, $selectedIndex);
        $list .= $this->getRequiredParam($option, 'range_slider_format', JText::_('RANGE_SLIDER_FORMAT'), JText::_('RANGE_SLIDER_FORMAT_DESC'), $params, $selectedIndex);
        $list .= $this->getFieldTypeParam($option, 'range_slider_format_decimals', JText::_('RANGE_SLIDER_FORMAT_DECIMALS'), JText::_('RANGE_SLIDER_FORMAT_DECIMALS_DESC'), $params, $selectedIndex);
        $list .= $this->getNumberFormatParam($option, 'range_slider_format_decimal_point', JText::_('RANGE_SLIDER_FORMAT_DECIMAL_POINT'), JText::_('RANGE_SLIDER_FORMAT_DECIMAL_POINT_DESC'), $params, $selectedIndex, '.');
        $list .= $this->getNumberFormatParam($option, 'range_slider_format_thousands_sep', JText::_('RANGE_SLIDER_FORMAT_THOUSANDS_SEP'), JText::_('RANGE_SLIDER_FORMAT_THOUSANDS_SEP_DESC'), $params, $selectedIndex, ',');
        $list .= '</div>';
		return $list;
	}
	
	protected function getFieldTypeParam($option, $field, $label, $desc, $params, $selectedIndex, $attrs = 'size="10"') {
		$val = '';
		$values = $params->get($field);
		if(is_array($values) && count($values) && isset($values[$selectedIndex])) {
			$val = $values[$selectedIndex];
		}
		$fieldId = $field . '_' . $option->value;
		
		$txt = '<div><span class="hasTip" title="'.$label.'::'.$desc.'">'.$label.'</span> <input type="text" id="'.$fieldId.'" name="'.$this->getName($field).'" value="'.$val.'" '.$attrs.' /></div>';
		return $txt;
	}

    protected function getNumberFormatParam($option, $field, $label, $desc, $params, $selectedIndex, $default = '', $attrs = '') {
        $val = $default;
        $values = $params->get($field);
        if(is_array($values) && count($values) && isset($values[$selectedIndex])) {
            $val = $values[$selectedIndex];
        }
        $fieldId = $field . '_' . $option->value;
        $list = array();
		$list[] = JHTML::_('select.option', '.', JText::_('NUMBER_FORMAT_SEPARATOR_DOT'));
		$list[] = JHTML::_('select.option', ',', JText::_('NUMBER_FORMAT_SEPARATOR_COMMA'));
		$list[] = JHTML::_('select.option', ' ', JText::_('NUMBER_FORMAT_SEPARATOR_SPACE'));
		$txt = '<div><span class="hasTip" title="'.$label.'::'.$desc.'">'.$label.'</span>'.JHTML::_('select.genericlist', $list, $this->getName($field), $attrs, 'value', 'text', $val, $fieldId).'</div>';
        return $txt;
    }

    protected function getRequiredParam($option, $field, $label, $desc, $params, $selectedIndex, $attrs = 'size="1"') {
        $val = '';
        $values = $params->get($field);
        if(is_array($values) && count($values) && isset($values[$selectedIndex])) {
            $val = $values[$selectedIndex];
        }
        $fieldId = $field . '_' . $option->value;
        $list = array();
        $list[] = JHTML::_('select.option', 1, JText::_('JYES'));
        $list[] = JHTML::_('select.option', 0, JText::_('JNO'));
        $txt = '<div><span class="hasTip" title="'.$label.'::'.$desc.'">'.$label.'</span>'.JHTML::_('select.genericlist', $list, $this->getName($field), $attrs, 'value', 'text', $val, $fieldId).'</div>';
        return $txt;
    }
}

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

class JFormFieldPrek2extrafield extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  11.1
	 */
	protected $type = 'Prek2extrafield';
	protected $prefiltype = array('checkbox', 'multipleSelect', 'magicSelect');
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

		$gindex = 0;
		$filter_by_fieldtype = $params->get('filter_by_fieldtype', array());
		$pre_filter_by_extrafield = $params->get('pre_filter_by_extrafield', array());
		$filter_by_fieldrange = $params->get('filter_by_fieldrange', array());
		$rangeslider_min = $params->get('rangeslider_min', array());
		$rangeslider_max = $params->get('rangeslider_max', array());
		$rangeslider_start = $params->get('rangeslider_start', array());
		$rangeslider_stop = $params->get('rangeslider_stop', array());
		$rangeslider_step = $params->get('rangeslider_step', array());
		echo '<script>
			jQuery(document).ready(function(){
				setTimeout(function(){jQuery("select.jform_params_range").trigger("chosen:updated");}, 2000);
			});
		</script>';
		$filbttype=array();
		foreach ($filter_by_fieldtype AS $kfbt => $vfbt) {
			$expl = explode(':', $vfbt);
			$filbttype[$expl[0]] = $expl[1];
		}

		$prexgroup = $params->get('prexgroup', array());
		foreach($groups AS $key=>$g){
			$html[] = '<h4 class="jagroup">
							<input class="xgroup-status" type="checkbox" name="jform[params][prexgroup][]" value="'.$key.'" '.(in_array($key, $prexgroup) ? 'checked' : '').'/>
							<span xonclick="$(\'pre_xfield_group_' . $key . '\').toggle();" title="'.JText::_('Collapse / Expand', true).'">
							'.JText::_('EXTRA_FIELDS_GROUP').$g.'
							</span>
						</h4>';
			$html[] = '<div class="xgroup-container"></div>';
			$html[] = '<script id="tpl_prexfield_group_' . $key . '" type="text/template" />';
			$html[] = '<ul class="extrafields" id="pre_xfield_group_' . $key . '">';
			$k=0;

			$order = isset($xgroup_order[$gindex]) ? $xgroup_order[$gindex] : $gindex;
			$gindex++;
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

					$html[] = '<li '.$class_li.'>';
					$html[] = '<label ' . $class . '>' . JText::_($option->text) . '</label><br />';
					
					$exvalue = $this->getTaxonomyValueByExid($option->value);
					$multiple = '';
					$defaultop = '<option value="">- ' . JText::_('JAK2SELECT') . ' -</option>';
					$arr_value = '';
					$ex_fieldname = '';
					$to_option='';
					$items=array();
                    if (!empty($filbttype[$option->value]))
    					if ($filbttype[$option->value] == 'textfield') {
    						$ex_fieldname = '_txt';
    						$extension = 'com_jak2filter';
    						$defaultValues = json_decode($option->json);
    						$default = $defaultValues[0];
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
    							$exvalue=array();
    							foreach ($items as $item) {
    								$multioptions=array();
    								$multioptions['option_id'] = $item->id;
    								$multioptions['labels'] = $item->title;
    								$exvalue[] = $multioptions;
    							}
    						} 
    					}
					if (!empty($filbttype[$option->value]))
						if ($filbttype[$option->value] == 'valuerange') {
							$exvalue=array();
							if (!empty($filter_by_fieldrange[$option->value])) {
								$ranges = $filter_by_fieldrange[$option->value];
								if(isset($ranges)){
									$ranges = explode("|", $ranges);
									$ranges = array_filter($ranges);
									sort($ranges,SORT_NUMERIC);
									
									for($i = 0; $i<count($ranges);$i++){
										$rangeoption = array();
										if($i==0){
											$rangeoption['labels'] = JText::_('LESS_THAN').' '.$ranges[$i];
											$rangeoption['option_id'] = '|'.$ranges[$i];
											$exvalue[] = $rangeoption;
										}
										else if($i==(count($ranges)-1)){
											$rangeoption['labels'] = $ranges[$i-1].JText::_('JA_K2FILTER_TO').$ranges[$i];
											$rangeoption['option_id'] = $ranges[$i-1].'|'.$ranges[$i];
											$exvalue[] = $rangeoption;
											$rangeoption['labels'] = JText::_('MORE_THAN').' '.$ranges[$i];
											$rangeoption['option_id'] = $ranges[$i].'|';
											$exvalue[] = $rangeoption;
										}
										else{
											$rangeoption['labels'] = $ranges[$i-1].JText::_('JA_K2FILTER_TO').$ranges[$i];
											$rangeoption['option_id'] = $ranges[$i-1].'|'.$ranges[$i];
											$exvalue[] = $rangeoption;
										}
									}
								}
							}
						}
					if (!empty($filbttype[$option->value]))
						if ($filbttype[$option->value] == 'rangeslider') {
							$exvalue=array();
							foreach ($filter_by_fieldtype AS $kf => $vf) {
								if (preg_match('/rangeslider/',$vf) && preg_match('/^('.$option->value.':)/', $vf)) {
									if (trim($rangeslider_min[$kf]) != '' 
										&& trim($rangeslider_max[$kf]) != ''
										&& trim($rangeslider_step[$kf]) != '') {
										$rangemin = $rangeslider_min[$kf];
										$rangemax = $rangeslider_max[$kf];
										while ($rangemin<=$rangemax) {
											$rangeoption=array();
											$rangeoption['option_id'] = $rangemin;
											$rangeoption['labels'] = $rangemin;
											$exvalue[] = $rangeoption;
											$rangemin+=$rangeslider_step[$kf];
										}
										if ($rangemin > $rangemax) {
											$rangeoption=array();
											$rangeoption['option_id'] = $rangemax;
											$rangeoption['labels'] = $rangemax;
											$exvalue[] = $rangeoption;
										}
									}
								}
							}
						}
					if (!empty($filbttype[$option->value]))
						if (in_array($filbttype[$option->value], array('daterange', 'rangeslider')))
							$ex_fieldname = '_from';
					if (!empty($filbttype[$option->value]))
						if (in_array($filbttype[$option->value], $this->prefiltype)) {
							$multiple = 'multiple="multiple"';
							$defaultop = '';
							$arr_value = '[]';
						}
					$optex = array();
					
					foreach ($exvalue AS $kex => $vex) {
						$selected='';
						if (isset($pre_filter_by_extrafield->{'xf_'.$option->value.$ex_fieldname}) && is_array($pre_filter_by_extrafield->{'xf_'.$option->value.$ex_fieldname})) {
							if (in_array($vex['option_id'], $pre_filter_by_extrafield->{'xf_'.$option->value.$ex_fieldname}) ||
								in_array($vex['labels'], $pre_filter_by_extrafield->{'xf_'.$option->value.$ex_fieldname})) {
								$selected=' selected="selected" ';
							}
						} else {
							if (!empty($pre_filter_by_extrafield->{'xf_'.$option->value.$ex_fieldname}) && 
								($pre_filter_by_extrafield->{'xf_'.$option->value.$ex_fieldname} == $vex['option_id'] ||
								$pre_filter_by_extrafield->{'xf_'.$option->value.$ex_fieldname} == $vex['labels'])) {
								$selected=' selected="selected" ';
							}
						}
						$optex[] = '<option '.$selected.' value="'.(empty($vex['option_id']) ? $vex['labels'] : $vex['option_id']).'">'.$vex['labels'].'</option>';
					}
					$optex_to=array();
					foreach ($exvalue AS $kex => $vex) {
						$selected='';
						if (!empty($pre_filter_by_extrafield->{'xf_'.$option->value.'_to'}) && 
								($pre_filter_by_extrafield->{'xf_'.$option->value.'_to'} == $vex['option_id'] ||
								$pre_filter_by_extrafield->{'xf_'.$option->value.'_to'} == $vex['labels'])) {
								$selected=' selected="selected" ';
							}
						$optex_to[] = '<option '.$selected.' value="'.(empty($vex['option_id']) ? $vex['labels'] : $vex['option_id']).'">'.$vex['labels'].'</option>';
					}
					if (!empty($filbttype[$option->value]))
						if (in_array($filbttype[$option->value], array('daterange', 'rangeslider'))) {
							$to_option = '
							To <select class="jform_params_range" '.$multiple.' id="jform_params_'.$option->value.'" name="' . $this->name . '[xf_'.$option->value.'_to]">
								'.$defaultop.'
								'.implode(' ', $optex_to).'
							</select>';
						}
					$html[] = '
					<div>
						<select class="jform_params_range" id="jform_params_'.$option->value.'" name="' . $this->name . '[xf_'.$option->value.$ex_fieldname.']'.$arr_value.'" 
							'.$multiple.'>
							'.$defaultop.'
							'.implode(' ', $optex).'
						</select>
						'.$to_option.'
					</div>';
					$html[] = '</li>';
				}
			}
			$html[] = '</ul>';
			$html[] = '</script>';
		}
		
		// End the checkbox field output.
		$html[] = '</fieldset>';
			
		return implode($html);
		}
		return;
		
	}
	
	protected function getTaxonomyValueByExid($id)
	{
		$db = JFactory::getDbo();
		$query = "
			SELECT * FROM #__jak2filter_taxonomy
			WHERE type='xfield' AND asset_id = ".$id." AND num_items > 0
			";
		$db->setQuery($query);
		$list = $db->loadAssocList();
		return $list;
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
					'select.option', $option['id'], $option['fname'] . ' (ID: '.$option['id'].')', 'value', 'text',
					($option['published'] == 0)
				);
	
				// Set some option attributes.
				$tmp->class = '';
				
				// Set some JavaScript option attributes.
				$tmp->onclick 	= '';
				$tmp->title		= $option['fname'];
				$tmp->type 		= $option['type'];
				$tmp->json 		= $option['value'];
				$tmp->group 	= $option['group'];
				$tmp->gname 	= $option['gname'];
	
				// Add the option object to the result set.
				$options[] = $tmp;
			}
		}

		reset($options);
		
		return $options;
	}
	
	public function getMultiLevelOptions($extension, $parent = null, $config = array('filter.published' => array(0, 1)))
	{
		$config = (array) $config;
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.id, a.title, a.level')
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
// 				JArrayHelper::toInteger($config['filter.published']);
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
		foreach ($items as &$item)
		{
			$repeat = ($item->level - 2 >= 0) ? $item->level - 2 : 0;
			$item->title = str_repeat('- ', $repeat) . $item->title;
		}

		return $items;
	}
	
}

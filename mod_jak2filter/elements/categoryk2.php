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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
/**
 * JA Param K2 Helper
 *
 * @since      Class available since Release 1.2.0
 */
class JFormFieldCategoryk2 extends JFormField
{
    /*
	 * Category K2 name
	 *
	 * @access	protected
	 * @var		string
	 */
    var $type = 'Categoryk2';


    /**
     * Fetch Ja Element K2 Catetgory Param method
     *
     * @return	object  param
     */
    function getInput()
    {
    	$document = JFactory::getDocument();
		if($this->multiple) {
			$document->addScriptDeclaration('
			function selectedparents(obj){
				if (document.getElementsByName("jform[params][parentMode]")[0].checked == true) {
					var percats = obj.getSelected().getProperty("percat").toString().split(",");
					percats.unique().each(function(parentsid){
						if(parentsid >0){
							for (var i = 0; i < obj.options.length; i++)
							{
								if (obj.options[i].value === parentsid)
								{
									if(!obj.options[i].selected){
										obj.options[i].set("selected","selected");
										selectedparents(obj);
										break;
									}
								}
							}
						}
					});
					jQuery("#jform_params_k2catsid").trigger("liszt:updated");
				}
			}

			jQuery(window).load(function() {
				jQuery("#jform_params_parentMode0").onclick = function () {
					jQuery("#jform_params_k2catsid").val("").trigger("liszt:updated");
				}

				jQuery("#jform_params_parentMode1").onclick = function () {
					jQuery("#jform_params_k2catsid").val("").trigger("liszt:updated");
				}
			});');
		}

        $flag = false;
        if (!$this->checkComponent('com_k2')) {
			return '<input type="hidden" name="' . $this->name . '" id="' . $this->id . '"/>
					<span style="color:red; float:left">K2 component is not installed!</span>';
        }

		$attr = '';
		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true')
		{
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

        $categories = JFormFieldCategoryK2::_fetchElement(0, '', array());
        $HTMLSelect = '<select onchange="return selectedparents(this);" name="' . $this->name . '" id="' . $this->id . '" '.$attr.'>';

        $HTMLCats = '';
        $value = $this->value;
        foreach ($categories as $item) {
			if(isset($item->id) && $item->id > 0){
				$check = '';
				if ((is_array($value) && in_array($item->id, $value)) || (!is_array($value) && $item->id == $value)) {
					$flag = true;
					$check = 'selected="selected"';
				}

				$class = ' percat="'.$item->parent.'"';

				if ($item->parent != 0)
					$class = ' class="subcat" percat="'.$item->parent.'" ';

				$HTMLCats .= '<option value="' . $item->id . '" ' . $check . ' ' . $class . '>' . '&nbsp;&nbsp;&nbsp;' . $item->treename . ' (ID: ' . $item->id . ')' . '</option>';
			}	
        }
        if ($flag == true) {
            $HTMLSelect .= '<option value="0">' . JText::_("SELECT_CATEGORY") . '</option>';
        } else {
            $HTMLSelect .= '<option value="0" selected="selected">' . JText::_("SELECT_CATEGORY") . '</option>';
        }
        $HTMLSelect .= $HTMLCats;
        $HTMLSelect .= '</select>';
        return $HTMLSelect;
    }


    /**
     *
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
        $query = "SELECT * FROM #__k2_categories WHERE parent = '{$parent}' ";
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
        $children = JFormFieldCategoryK2::fetchChild($id);

        if (@$children && $level <= $maxlevel) {
            foreach ($children as $v) {
                $id = $v->id;

                if ($type) {
                    $pre = '<sup>|_</sup> ';
                    $spacer = '.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                } else {
                    $pre = '- ';
                    $spacer = '&nbsp;&nbsp;';
                }

                if ($v->parent == 0) {
                    $txt = $v->name;
                } else {
                    $txt = $pre . $v->name;
                }
                $pt = $v->parent;
                $list[$id] = $v;
                $list[$id]->treename = "{$indent}{$txt}";
                $list[$id]->children = count(@$children);
                $list[$id]->haschild = true;
                $list = JFormFieldCategoryK2::_fetchElement($id, $indent . $spacer, $list, $maxlevel, $level + 1, $type);
            }
        } else {
			if(isset($list[$id])) {
				$list[$id]->haschild = false;
			}
        }
        return $list;
    }


    /**
     *
     * Check component is existed
     * @param string $component component name
     * @return int return > 0 when component is installed
     */
    function checkComponent($component)
    {
        $db = JFactory::getDBO();
        $query = " SELECT Count(*) FROM #__extensions as e WHERE e.element ='$component' and e.enabled=1";
        $db->setQuery($query);
	    return $db->loadResult();
    }
}
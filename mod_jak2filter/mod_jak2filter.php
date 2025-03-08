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

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';
require_once JPATH_ROOT.'/components/com_jak2filter/helpers/helper.php';
require_once JPATH_ROOT.'/components/com_jak2filter/helpers/upgrade.php';

/**
 * check if k2 component is installed
 */
if( JFile::exists(JPATH_BASE.'/components/com_k2/k2.php')){
	//check upgrade
	$helperUpgrade = new JAK2FilterHelperUpgrade();
	$helperUpgrade->checkUpdate();

	//INCLUDING ASSET
	require_once(dirname(__FILE__) . '/assets/behavior.php');
	include_once(dirname(__FILE__) . '/assets/asset.php');
	
	$app	= JFactory::getApplication();
	$jinput = $app->input;
	$db = JFactory::getDbo();
	$componentParams = JComponentHelper::getParams('com_jak2filter');
	JFactory::getDocument()->addScriptDeclaration('
	if ($jak2depend == undefined || $jak2depend == "undefined")
		var $jak2depend = new Array();
	');
	$menu	= $app->getMenu();
	$active	= $menu->getActive();
	$active_id = isset($active) ? $active->id : $menu->getDefault()->id;
	$path	= isset($active) ? $active->tree : array();

	$maximum_keyword 		= (int) $componentParams->get('maximum_keyword', 20);
	$showAll				= $params->get('showAllChildren');
	$class_sfx				= htmlspecialchars($params->get('moduleclass_sfx',''));
	$use_standard_fields   	= (int) $params->get('use_standard_fields', 1);
	$filter_by_extrafield	= $params->get('filter_by_extrafield', array());
	$filter_by_fieldtype 	= $params->get('filter_by_fieldtype');
	$keyword_default_mode 	= $params->get('keyword_default_mode', '');
	$slider_whole_range 	= (int) $componentParams->get('range_option', 0);

	$exgroups = '';
	if($use_standard_fields) {
		$filter_by_keyword   = (int) $params->get('filter_by_keyword', 1);
		$display_keyword_tip = (int) $params->get('display_keyword_tip', 1);
		$filter_by_category  = (int) $params->get('filter_by_category', 1);
		$filter_by_author    = (int) $params->get('filter_by_author', 1);
		$filter_by_tags		 = (int) $params->get('filter_by_tags', 0); 
		$filter_by_rating	 = (int) $params->get('filter_by_rating', 0);
		$filter_keyword_option	 = 0;//(int) $params->get('keyword_options', 1);
		$search_by_date	   	= (int) $params->get('search_by_date', 0);
		$catMode	 = (int) $params->get('catMode', 0);
	} else {
		$filter_by_keyword = 0;
		$display_keyword_tip = 0;
		$filter_by_category = 0;
		$filter_by_author = 0;
		$filter_by_tags = 0;
		$filter_by_rating = 0;
		$catMode	 = 0;
		$filter_keyword_option = 0;
		$search_by_date = 0;
	}
	
	$ja_stylesheet			= $params->get('ja_stylesheet');
	$auto_filter		= (int) $params->get('auto_filter');
	
	$ja_column = '';
	if($ja_stylesheet == 'horizontal-layout' && $params->get('ja_column') && $params->get('ja_column') > 0){
		$ja_column	= 'width:'.round(100/$params->get('ja_column'),2).'%;';
	}
	
	$required_defined = $params->get('required_defined', '0');
	$required_fields = $params->get('required_field', array());
	$check_required_fields=$required_fields;
	$required_fields = json_encode($required_fields);
	if ($required_defined != 2) {
		$required_fields = $required_defined;
		$check_required_fields=array();
	}
	
	$helper = new modJak2filterHelper($module);
	
	/**
	 * Get list
	 */
	$list	= $helper->getList($filter_by_extrafield, $filter_by_fieldtype);
	if($slider_whole_range && count($list) > 1) {
		/**
		 * if form contains extra field from many Extra field group,
		 * then Search in whole range setting must be disabled to ensure that search return correct result
		 */
		if(count($helper->activeCats)) {
			$activeGroups = $helper->getextraFieldsGroupsByCat($helper->activeCats);
			if(count($activeGroups) > 1) {
				$slider_whole_range = 0;
			}
		} else {
			$slider_whole_range = 0;
		}
	}
	//search by date
	$filter_by_daterange = '';
	if($use_standard_fields && $search_by_date)
	{
		$filter_by_daterange = $helper->getSearchDateCreate();
	}
	
	//keyword
	$keyword_option =  '';
	if($use_standard_fields && $filter_keyword_option) {
		$keyword_option = $helper->getRadioKeywords();
	}
		
	//category
	$categories = '';
	$groupcategories = $params->get('k2catsid',null);
    $fetch_subcategories = $params->get('subCats','0');
    //Fetch all sub-categories
    if($fetch_subcategories != 0){
        if(is_array($groupcategories)){
            foreach($groupcategories as $category){
                $subCats = $helper->getSubCategories($category);
                if(!empty($subCats)){
                    foreach($subCats as $k => $cat){
                        if(!in_array($cat['id'], $groupcategories)){
                            $groupcategories[] = $cat['id'];    
                        }
                    }
                }
            }
        }
    }
	
	if($use_standard_fields) {
		if($filter_by_category){		
			$categories = $helper->getCategories($groupcategories);
		} elseif ($groupcategories) {
			$categories  = $helper->getCategories($groupcategories,true);
		}
	} else {
		if(is_array($filter_by_extrafield) && count($filter_by_extrafield)) {
			$query = "
				SELECT DISTINCT ".$db->quoteName('c.id')." 
				FROM #__k2_categories c
				INNER JOIN #__k2_extra_fields xf ON xf.group = c.extraFieldsGroup
				WHERE xf.id IN (".implode(',', $filter_by_extrafield).")";
			$db->setQuery($query);
			
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
			   $cids = $db->loadColumn(0);
			}
			else
			{
			   $cids = $db->loadResultArray(0);
			}
			if(count($cids)) {
				$categories  = '<input type="hidden" name="category_id" value="'.implode(',', $cids).'" />';
			}
		}
	}

	$selected_group = 0;
	$gets = $jinput->getArray();
	if(count($gets)) {
		$xf = array();
		foreach($gets as $key => $val) {
			if(preg_match('/^xf_[0-9]+$/', $key)) {
				$xf[] = (int) (str_replace('xf_', '', $key));
			}
		}
		if(count($xf)) {
			$query = "SELECT `group` FROM #__k2_extra_fields WHERE `id` IN (".implode(',', $xf).") ORDER BY `group` DESC";
			$db->setQuery($query);
			$selected_group = $db->loadResult();
		}
	}

	//author
	$authors = '';
	if($use_standard_fields && $filter_by_author){
		$filter_author_display = $params->get('filter_author_display','author_display_name');
		$authors = $helper->getAuthors($filter_author_display);
		$authors_label = $helper->getLabel($params->get('filter_by_author_fieldtype', 'select'), 'created_by', JText::_('JAK2_AUTHOR'));
	}
	
	//tags
	$filter_by_tags_display = '';
	if($use_standard_fields && $filter_by_tags){
		$filter_by_tags_display = $helper->getTags();
		$filter_by_tags_label = $helper->getLabel($params->get('filter_by_tags_fieldtype', 'select'), 'tags_id', JText::_('JAK2_TAGS'));
	}
	
	//rating
	$filter_by_rating_display = '';
	if($use_standard_fields && $filter_by_rating){
		$filter_by_rating_display = $helper->getRatings();
	}

	//ordering
	$display_ordering = $helper->getOrderingList();
	$filter_sort = jaK2GetOrderFieldsValues();
	$order_display = $params->get('show_order_field', array());
	if (empty($order_display)) {
		preg_match_all('/value\=\"(.*?)\"/', $display_ordering, $mt); // get ordering from default display ordering list.
		if (!empty($mt[1])) {
			foreach ($mt[1] AS $m) {
				if (!empty($m)) $order_display[] = $m;
			}
		}
	}
	
	//theme
	$theme = $params->get('theme', '');
	
	// *** start pre filter.
	$pre_k2filter = (int)$params->get('pre_k2filter', 0);
	$option = $jinput->get('option');
	
	// only using the prefilter if enabled and current jak2filter menu.
	if ($pre_k2filter && ($option == 'com_jak2filter' || $option == 'com_k2')) {
		$link = array();
		$pre_order_field = $params->get('pre_order_field', array());
		$filter_by_fieldtype = $params->get('filter_by_fieldtype', array());
		$filter_by_extrafield = $params->get('filter_by_extrafield', array());
		$exkey=array();
		
		// get extrafield id.
		foreach ($filter_by_fieldtype AS $kfbt => $vfbt) {
			$expl = explode(':', $vfbt);
			if (in_array($expl[1], array('valuerange', 'rangeslider'))) {
				$exkey[] = $expl[0];
			}
		}
		
		// get pre_ordering.
		if (!empty($pre_order_field)) {
			foreach ($pre_order_field AS $kpof => $pof) {
				$link['orders['.$pof.']'] = $pof;
			}
		}
		
		// get pre rating.
		$pre_srating = $params->get('pre_srating', 0);
		$pre_erating = $params->get('pre_erating', 5);
		$prerating = $jinput->get('rating', false);
		if (empty($prerating) && $params->get('filter_by_fieldtype') && !($pre_srating == '0' && $pre_erating == '5')) {
			$link['rating'] = $pre_srating.'|'.$pre_erating;
		}

		// get pre date range.
		$pre_dtrange = $params->get('pre_dtrange', '');
		$predtrange = $jinput->get('dtrange', false);
		if (empty($predtrange) && $params->get('search_by_date') && !empty($pre_dtrange)) $link['dtrange'] = $pre_dtrange;
		if (empty($predtrange) && $params->get('search_by_date') && $pre_dtrange == 'range') {
			$pre_sdate = $params->get('pre_sdate', '');
			if (!empty($pre_sdate)) $link['sdate'] = $pre_sdate;
		
			$pre_edate = $params->get('pre_edate', '');
			if (!empty($pre_edate)) $link['edate'] = $pre_edate;
		}
		
		// get pre category
		$pre_k2catsid = $params->get('pre_k2catsid', '');
		$precategory_id = $jinput->get('category_id', false);
		if (empty($precategory_id) && $params->get('filter_by_category') && !empty($pre_k2catsid)) $link['category_id'] = $pre_k2catsid;
		
		// get pre search words.
		$pre_searchword = $params->get('pre_searchword', '');
		$presearchword = $jinput->get('searchword', false);
		if (empty($presearchword) && $params->get('filter_by_keyword') && !empty($pre_searchword)) $link['searchword'] = $pre_searchword;
		
		// get pre author
		$pre_author = $params->get('pre_author', '');
		$author_multip = $params->get('filter_by_author_fieldtype', '');
		$authormultiple=true;
		if (in_array($author_multip, array('select','radio'))) {
			$authormultiple=false;
		}
		
		// get pre created by
		$precreated_by = $jinput->get('created_by', false);
		if (($authormultiple == false && !is_array($pre_author))
			|| ($authormultiple == true && is_array($pre_author))
			) {
			if (empty($precreated_by) && $params->get('filter_by_author') && !empty($pre_author)) $link['created_by'] = $pre_author;
		}
		
		// get pre tag
		$pre_tags = $params->get('pre_tags', '');
		$tag_multip = $params->get('filter_by_tags_fieldtype', '');
		$tagmultiple=true;
		if (in_array($tag_multip, array('select','radio'))) {
			$tagmultiple=false;
		}
		if (($tagmultiple == false && !is_array($pre_tags))
			|| ($tagmultiple == true && is_array($pre_tags))
			) {
			$pretags_id = $jinput->get('tags_id', false);
			if (empty($pretags_id) && $params->get('filter_by_tags') && !empty($pre_tags)) $link['tags_id'] = $pre_tags;
		}
		
		// get pre extrafield
		$pre_filter_by_extrafield = $params->get('pre_filter_by_extrafield', array());
		foreach ($pre_filter_by_extrafield AS $kex => $vex) {
			$exnumb = preg_replace("/[^0-9]/","",$kex);
			$pre_ex = $jinput->get($kex, false);
			foreach ($list AS $lis) {
				if (is_array($lis['items']) && !empty($lis['items'])) {
					foreach ($lis['items'] AS $klis => $vlis) {
						if (preg_match('/(_'.$exnumb.')$/',$klis)) {
							if (in_array($exnumb, $filter_by_extrafield)) {
								if (in_array($exnumb, $exkey)) {
									if (!$jinput->get('xf_'.$exnumb.'_range')) {
										if (trim($pre_filter_by_extrafield->{'xf_'.$exnumb.'_from'}) != '' 
											&& trim($pre_filter_by_extrafield->{'xf_'.$exnumb.'_to'}) != ''
											&& ($trim->{'xf_'.$exnumb.'_from'} < $pre_filter_by_extrafield->{'xf_'.$exnumb.'_to'})) {
											$link['xf_'.$exnumb.'_range'] = $pre_filter_by_extrafield->{'xf_'.$exnumb.'_from'}.'|'.$pre_filter_by_extrafield->{'xf_'.$exnumb.'_to'};
										} else {
											$link['xf_'.$exnumb.'_range'] = $vex;
										}
									}
								} else {
									if (empty($pre_ex) && !empty($vex))
										$link[$kex] = $vex;
								}
							}
						}
					}
				}
			}
		}
		
		// prepare the redirect link
		$url_link = '';
		foreach ($link AS $k => $l) {
			if (is_array($l)) {
				foreach ($l AS $kl => $lv) {
					$url_link .= "&{$k}[]={$lv}";
				}
			} else {
				$url_link .= "&{$k}={$l}";
			}
		}
	
		$redirect_link = JUri::getInstance();
		$full_url='';
		// check if already redirect. so won't redirect anymore
		if (!preg_match('/issearch/', $redirect_link)) {
			// check if already has ? sign.
			if (!preg_match('/\?/', $redirect_link))
				$url_link = '?'.ltrim($url_link, '&');
			$full_url = $redirect_link.$url_link.'&issearch=1';
		
			if (!empty($link)) // don't have any params has set is admin so won't redirect.
				$app->redirect($full_url);
		}
	}
	// *** end pre filter.
	
	require JModuleHelper::getLayoutPath('mod_jak2filter', $params->get('layout', 'default'));
}else{
	echo JText::_('COMPONENT_K2_NOT_FOUND');
}
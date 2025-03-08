<?php
/**
* ------------------------------------------------------------------------
* Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
* @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
* Author: J.O.O.M Solutions Co., Ltd
* Websites: http://www.joomlart.com - http://www.joomlancers.com
* This file may not be redistributed in whole or significant part.
* ------------------------------------------------------------------------
*/
// No direct to access this file
defined('_JEXEC') or die();

class JFormFieldInherit extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Inherit';

	/**
	 * Method to get the radio button field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$isJoomla3x = version_compare(JVERSION, '3.0', 'ge');
		$jsDisable = '';
		$jsEnable = '';
		if($isJoomla3x) {
			$jsDisable = "
			jQuery('#jformparamstheme').attr('disabled', true).trigger('liszt:updated');
			jQuery('#jform_params_leadingImgSize').attr('disabled', true).trigger('liszt:updated');
			jQuery('#jform_params_primaryImgSize').attr('disabled', true).trigger('liszt:updated');
			jQuery('#jform_params_secondaryImgSize').attr('disabled', true).trigger('liszt:updated');
			jQuery('#jform_params_linksImgSize').attr('disabled', true).trigger('liszt:updated');
			jQuery('#jform_params_catFeaturedItems').attr('disabled', true).trigger('liszt:updated');
			jQuery('#jform_params_catOrdering').attr('disabled', true).trigger('liszt:updated');
			jQuery('#jform_params_catPagination').attr('disabled', true).trigger('liszt:updated');
			";
			$jsEnable = "
			jQuery('#jformparamstheme').attr('disabled', false).trigger('liszt:updated');
			jQuery('#jform_params_leadingImgSize').attr('disabled', false).trigger('liszt:updated');
			jQuery('#jform_params_primaryImgSize').attr('disabled', false).trigger('liszt:updated');
			jQuery('#jform_params_secondaryImgSize').attr('disabled', false).trigger('liszt:updated');
			jQuery('#jform_params_linksImgSize').attr('disabled', false).trigger('liszt:updated');
			jQuery('#jform_params_catFeaturedItems').attr('disabled', false).trigger('liszt:updated');
			jQuery('#jform_params_catOrdering').attr('disabled', false).trigger('liszt:updated');
			jQuery('#jform_params_catPagination').attr('disabled', false).trigger('liszt:updated');
			";
		}

		$document = JFactory::getDocument();
		$document->addScriptDeclaration("

			function setFieldState(fid){
				if(fid.value == 1) {
					disableParams();
				} else {
					enableParams();
				}
			}
    		window.addEvent('domready', function() {
    			var fid = $('".$this->id."');
    			setFieldState(fid);
    		});

    		function disableParams(){
				$('jform_params_num_leading_items').setProperty('disabled','disabled');
				$('jform_params_num_leading_columns').setProperty('disabled','disabled');
				$('jform_params_leadingImgSize').setProperty('disabled','disabled');
				$('jform_params_num_primary_items').setProperty('disabled','disabled');
				$('jform_params_num_primary_columns').setProperty('disabled','disabled');
				$('jform_params_primaryImgSize').setProperty('disabled','disabled');
				$('jform_params_num_secondary_items').setProperty('disabled','disabled');
				$('jform_params_num_secondary_columns').setProperty('disabled','disabled');
				$('jform_params_secondaryImgSize').setProperty('disabled','disabled');
				$('jform_params_num_links').setProperty('disabled','disabled');
				$('jform_params_num_links_columns').setProperty('disabled','disabled');
				$('jform_params_linksImgSize').setProperty('disabled','disabled');
				$('jform_params_catFeaturedItems').setProperty('disabled','disabled');
				$('jform_params_catOrdering').setProperty('disabled','disabled');
				$('jform_params_catPagination').setProperty('disabled','disabled');
				$('jform_params_catPaginationResults0').setProperty('disabled','disabled');
				$('jform_params_catPaginationResults1').setProperty('disabled','disabled');
				$('jform_params_enableHighlightSearchTerm0').setProperty('disabled','disabled');
				$('jform_params_enableHighlightSearchTerm1').setProperty('disabled','disabled');
				$('jform_params_catFeedLink0').setProperty('disabled','disabled');
				$('jform_params_catFeedLink1').setProperty('disabled','disabled');
				$('jform_params_catFeedIcon0').setProperty('disabled','disabled');
				$('jform_params_catFeedIcon1').setProperty('disabled','disabled');
				$('jformparamstheme').setProperty('disabled','disabled');
				$('jform_params_catItemTitle0').setProperty('disabled','disabled');
				$('jform_params_catItemTitle1').setProperty('disabled','disabled');
				$('jform_params_catItemTitleLinked0').setProperty('disabled','disabled');
				$('jform_params_catItemTitleLinked1').setProperty('disabled','disabled');
				$('jform_params_catItemFeaturedNotice0').setProperty('disabled','disabled');
				$('jform_params_catItemFeaturedNotice1').setProperty('disabled','disabled');
				$('jform_params_catItemAuthor0').setProperty('disabled','disabled');
				$('jform_params_catItemAuthor1').setProperty('disabled','disabled');
				$('jform_params_catItemDateCreated0').setProperty('disabled','disabled');
				$('jform_params_catItemDateCreated1').setProperty('disabled','disabled');
				$('jform_params_catItemRating0').setProperty('disabled','disabled');
				$('jform_params_catItemRating1').setProperty('disabled','disabled');
				$('jform_params_catItemIntroText0').setProperty('disabled','disabled');
				$('jform_params_catItemIntroText1').setProperty('disabled','disabled');
				$('jform_params_catItemIntroTextWordLimit').setProperty('disabled','disabled');
				$('jform_params_catItemExtraFields0').setProperty('disabled','disabled');
				$('jform_params_catItemExtraFields1').setProperty('disabled','disabled');
				$('jform_params_catItemHits0').setProperty('disabled','disabled');
				$('jform_params_catItemHits1').setProperty('disabled','disabled');
				$('jform_params_catItemCategory0').setProperty('disabled','disabled');
				$('jform_params_catItemCategory1').setProperty('disabled','disabled');
				$('jform_params_catItemTags0').setProperty('disabled','disabled');
				$('jform_params_catItemTags1').setProperty('disabled','disabled');
				$('jform_params_catItemDateModified0').setProperty('disabled','disabled');
				$('jform_params_catItemDateModified1').setProperty('disabled','disabled');
				$('jform_params_catItemReadMore0').setProperty('disabled','disabled');
				$('jform_params_catItemReadMore1').setProperty('disabled','disabled');
				$('jform_params_catItemCommentsAnchor0').setProperty('disabled','disabled');
				$('jform_params_catItemCommentsAnchor1').setProperty('disabled','disabled');
				$('jform_params_catItemK2Plugins0').setProperty('disabled','disabled');
				$('jform_params_catItemK2Plugins1').setProperty('disabled','disabled');
				".$jsDisable."
			}

			function enableParams(){
				$('jform_params_num_leading_items').removeProperty('disabled');
				$('jform_params_num_leading_columns').removeProperty('disabled');
				$('jform_params_leadingImgSize').removeProperty('disabled');
				$('jform_params_num_primary_items').removeProperty('disabled');
				$('jform_params_num_primary_columns').removeProperty('disabled');
				$('jform_params_primaryImgSize').removeProperty('disabled');
				$('jform_params_num_secondary_items').removeProperty('disabled');
				$('jform_params_num_secondary_columns').removeProperty('disabled');
				$('jform_params_secondaryImgSize').removeProperty('disabled');
				$('jform_params_num_links').removeProperty('disabled');
				$('jform_params_num_links_columns').removeProperty('disabled');
				$('jform_params_linksImgSize').removeProperty('disabled');
				$('jform_params_catFeaturedItems').removeProperty('disabled');
				$('jform_params_catOrdering').removeProperty('disabled');
				$('jform_params_catPagination').removeProperty('disabled');
				$('jform_params_catPaginationResults0').removeProperty('disabled');
				$('jform_params_catPaginationResults1').removeProperty('disabled');
				$('jform_params_enableHighlightSearchTerm0').removeProperty('disabled');
				$('jform_params_enableHighlightSearchTerm1').removeProperty('disabled');
				$('jform_params_catFeedLink0').removeProperty('disabled');
				$('jform_params_catFeedLink1').removeProperty('disabled');
				$('jform_params_catFeedIcon0').removeProperty('disabled');
				$('jform_params_catFeedIcon1').removeProperty('disabled');
				$('jformparamstheme').removeProperty('disabled');
				$('jform_params_catItemTitle0').removeProperty('disabled');
				$('jform_params_catItemTitle1').removeProperty('disabled');
				$('jform_params_catItemTitleLinked0').removeProperty('disabled');
				$('jform_params_catItemTitleLinked1').removeProperty('disabled');
				$('jform_params_catItemFeaturedNotice0').removeProperty('disabled');
				$('jform_params_catItemFeaturedNotice1').removeProperty('disabled');
				$('jform_params_catItemAuthor0').removeProperty('disabled');
				$('jform_params_catItemAuthor1').removeProperty('disabled');
				$('jform_params_catItemDateCreated0').removeProperty('disabled');
				$('jform_params_catItemDateCreated1').removeProperty('disabled');
				$('jform_params_catItemRating0').removeProperty('disabled');
				$('jform_params_catItemRating1').removeProperty('disabled');
				$('jform_params_catItemIntroText0').removeProperty('disabled');
				$('jform_params_catItemIntroText1').removeProperty('disabled');
				$('jform_params_catItemIntroTextWordLimit').removeProperty('disabled');
				$('jform_params_catItemExtraFields0').removeProperty('disabled');
				$('jform_params_catItemExtraFields1').removeProperty('disabled');
				$('jform_params_catItemHits0').removeProperty('disabled');
				$('jform_params_catItemHits1').removeProperty('disabled');
				$('jform_params_catItemCategory0').removeProperty('disabled');
				$('jform_params_catItemCategory1').removeProperty('disabled');
				$('jform_params_catItemTags0').removeProperty('disabled');
				$('jform_params_catItemTags1').removeProperty('disabled');
				$('jform_params_catItemDateModified0').removeProperty('disabled');
				$('jform_params_catItemDateModified1').removeProperty('disabled');
				$('jform_params_catItemReadMore0').removeProperty('disabled');
				$('jform_params_catItemReadMore1').removeProperty('disabled');
				$('jform_params_catItemCommentsAnchor0').removeProperty('disabled');
				$('jform_params_catItemCommentsAnchor1').removeProperty('disabled');
				$('jform_params_catItemK2Plugins0').removeProperty('disabled');
				$('jform_params_catItemK2Plugins1').removeProperty('disabled');
				".$jsEnable."
			}
    	");
		return parent::getInput();
	}
}

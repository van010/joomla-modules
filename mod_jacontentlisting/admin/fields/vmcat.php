<?php
/**
 *------------------------------------------------------------------------------
 * @package       Module JA Content Listing for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2021 JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github
 *                & Google group to become co-author)
 *------------------------------------------------------------------------------
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;


FormHelper::loadFieldClass('list');

jimport('joomla.filesystem.file');

require_once JPATH_ROOT . '/modules/mod_jacontentlisting/admin/fields/easyblogcat.php';


class JFormFieldVmcat extends ListField{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'vmcat';
	public $checkVir;

	public function __construct()
	{
		$r = new ReflectionMethod('JFormFieldEasyblogcat', 'checkComponent');
		$r->setAccessible(true);
		$this->checkVir = $r->invoke(new JFormFieldEasyblogcat(), 'com_virtuemart');
	}

	protected function getInput(){
		$input = parent::getInput();
    if (!$this->checkVir){
	    if(version_compare(JVERSION, '3.0', 'ge')) {
		    $input .= '<br /><span style="color:red;">'.Text::_('VM_NOT_INSTALL').'</span>';
	    } else {
		    $input .= '<br /><label>&nbsp;</label><span style="color:red">'.Text::_('VM_NOT_INSTALL').'</span>';
	    }
    }else{
	    $input .= '<br /><span class="vm_no_cat" style="color:red; display:none;">'.Text::_('VM_NO_CAT').'</span>';
    }
    return $input;
	}

	protected function getOptions()
	{
		if (!$this->checkVir){
			return parent::getOptions();
		}
    $lang = Factory::getLanguage();
    $languages = JLanguageHelper::getLanguages('lang_code');
    $currLangTag = $lang->getTag();
		$currLangCode = str_replace('-', '_', $currLangTag);
    $currLangCode = strtolower($currLangCode);

		$db = Factory::getDbo();
	  $query = "SELECT a.category_name AS title, a.virtuemart_category_id AS id, b.category_parent_id AS parent_id";
		$query .= " FROM `#__virtuemart_categories_$currLangCode` AS a";
		$query .= " INNER JOIN `#__virtuemart_category_categories` AS b";
		$query .= " ON a.virtuemart_category_id = b.category_child_id";
		$query .= " INNER JOIN `#__virtuemart_categories` AS c";
		$query .= " ON a.virtuemart_category_id = c.virtuemart_category_id";
		$query .= " WHERE c.published = 1";
    $query .= " ORDER BY a.category_name";
		$vmCats = $db->setQuery($query)->loadObjectList();

		if (empty($vmCats)){
			$query = "SELECT a.category_name AS title, a.virtuemart_category_id AS id, b.category_parent_id AS parent_id";
      $query .= " FROM `#__virtuemart_categories_en_gb` AS a";
			$query .= " INNER JOIN `#__virtuemart_category_categories` AS b";
			$query .= " ON a.virtuemart_category_id = b.category_child_id";
			$query .= " INNER JOIN `#__virtuemart_categories` AS c";
			$query .= " ON a.virtuemart_category_id = c.virtuemart_category_id";
			$query .= " WHERE c.published = 1";
			$query .= " ORDER BY a.category_name";
			$vmCats = $db->setQuery($query)->loadObjectList();
		}

		$children = [];
		if ($vmCats){
			foreach ($vmCats as $cat){
				$pCatId = $cat->parent_id;
				$list = @$children[$pCatId] ? $children[$pCatId] : [];
				array_push($list, $cat);
				$children[$pCatId] = $list;
			}
		}
		$list = HTMLHelper::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);

		$options = [];
		foreach ($list as $item){
			@$options[] = HTMLHelper::_('select.option', $item->id, $item->treename);
		}
		if (isset($this->element['show_root'])){
			array_unshift($options, HTMLHelper::_('select.option', '0', Text::_('JGLOBAL_ROOT')));
		}
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
<?php
	/**
	 * ------------------------------------------------------------------------
	 * JA Content Slider Module for J25 & J34
	 * ------------------------------------------------------------------------
	 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
	 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
	 * Author: J.O.O.M Solutions Co., Ltd
	 * Websites: http://www.joomlart.com - http://www.joomlancers.com
	 * ------------------------------------------------------------------------
	 */

// no direct access
	use Joomla\CMS\MVC\Model\BaseDatabaseModel;
	
	defined('_JEXEC') or die('Restricted access');
if(version_compare(JVERSION, '4', 'lt')){
	$com_path = JPATH_SITE . '/components/com_content/';

	JLoader::register('ContentHelperRoute', $com_path . 'helpers/route.php');
	JModelLegacy::addIncludePath($com_path . 'models', 'ContentModel');
}

// no direct access
	defined('_JEXEC') or die();

	if (!class_exists('ContentHelperRoute')) {
		if (version_compare(JVERSION, '4', 'ge')) {
			abstract class ContentHelperRoute extends \Joomla\Component\content\Site\Helper\RouteHelper
			{
			}
		} else {
			JLoader::register('ContentHelperRoute', $com_path . '/helpers/route.php');
		}
	}
	
	/**s
	 *
	 * JA Module Content Slider Helper Class
	 * @author JoomlArt
	 *
	 */
	class modJacontentsliderHelper
	{
		
		
		/**
		 * get instance of modJacontentsliderHelper
		 * @return object
		 */
		function getInstance()
		{
			static $__instance = null;
			if (!$__instance) {
				$__instance = new modJacontentsliderHelper();
			}
			return $__instance;
		}
		
		
		/**
		 * get Total contents by category.
		 *
		 * @params object.
		 * @return int
		 */
		function getTotalContents($catid = 0)
		{
			// Get the dbo
			$db = JFactory::getDbo();
			
			// Get an instance of the generic articles model
			
			if (version_compare(JVERSION, '4.0', 'ge')){
				$model = BaseDatabaseModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
			}elseif (version_compare(JVERSION, '3.0', 'ge')) {
				$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
				//$model = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request' => true));
			} else{
				$model = JModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
			}

			// Set application parameters in model
			$appParams = JFactory::getApplication()->getParams();
			$model->setState('params', $appParams);
			
			$model->setState(
				'list.select',
				'a.id, a.title, a.alias, a.introtext, ' .
				'a.checked_out, a.checked_out_time, ' .
				'a.catid, a.created, a.created_by, a.created_by_alias, ' .
				// use created if modified is 0
				'CASE WHEN a.modified = 0 THEN a.created ELSE a.modified END as modified, ' .
				'a.modified_by,' .
				// use created if publish_up is 0
				'CASE WHEN a.publish_up = 0 THEN a.created ELSE a.publish_up END as publish_up, ' .
				'a.publish_down, a.attribs, a.metadata, a.metakey, a.metadesc, a.access, ' .
				'a.hits, a.featured,' . ' LENGTH(a.fulltext) AS readmore '
			);
			
			$model->setState('filter.published', 1);
			
			// Access filter
			$access = !JComponentHelper::getParams('com_content')->get('show_noauth');
			$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
			$model->setState('filter.access', $access);
			
			// Category filter
			if ($catid) {
				if ($catid[0] != "") {
					$model->setState('filter.category_id', $catid);
				}
			}
			
			$items = $model->getItems();
			return count($items);
		}
		
		/**
		 *
		 * Create article link
		 * @param object $item article
		 * @return string article link
		 */
		function articleLink($item)
		{
			$access = !JComponentHelper::getParams('com_content')->get('show_noauth');
			$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
			$item->slug = $item->id . ':' . $item->alias;
			$item->catslug = $item->catid . ':' . $item->category_alias;
			
			if ($access || in_array($item->access, $authorised)) {
				// We know that user has the privilege to view the article
				$item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
			} else {
				$item->link = JRoute::_('index.php?option=com_user&view=login');
			}
			
			return $item->link;
		}
		
		/**
		 *
		 * Get List Item to display in Slider
		 * @param array $catid array categories article or K2
		 * @param object $params
		 * @param string $source the source data
		 * @return array list items
		 */
		public static function getListItems($catid, $params, $source = 'content')
		{
			$helper = new modJacontentsliderHelper();
			if ($source == 'folder') {
				return $helper->getListFolder($params);
			} else {
				$callMethod = 'getList' . ucfirst($source);
				if (method_exists($helper, $callMethod)) {
					return $helper->$callMethod($catid, $params);
				}
			}
			return array();
		}
		
		
		/**
		 *
		 * Get list K2 items
		 * @param string $catid categories id
		 * @param object $params
		 * @return array
		 */
		function getListK2($catids, $params)
		{
			if (file_exists(JPATH_SITE . '/components/com_k2/helpers/route.php')) {
				require_once(JPATH_SITE . "/components/com_k2/helpers/route.php");
				require_once(JPATH_ADMINISTRATOR . "/components/com_k2/models/model.php");
				require_once(JPATH_SITE . "/components/com_k2/models/item.php");
			}
			$db = JFactory::getDBO();
			$app = JFactory::getApplication();
			$date = JFactory::getDate();
			//$now = $date->toMySQL();
			if (version_compare(JVERSION, '3.0', 'ge')) {
				$now = $date->toSql();
			} else if (version_compare(JVERSION, '2.5', 'ge')) {
				$now = $date->toMySQL();
			} else {
				$now = $date->toMySQL();
			}
			
			$user = JFactory::getUser();
			$aid = $user->get('aid') ? $user->get('aid') : 1;
			$jnow = JFactory::getDate();
			//$now = $jnow->toMySQL();
			if (version_compare(JVERSION, '3.0', 'ge')) {
				$now = $jnow->toSql();
			} else if (version_compare(JVERSION, '2.5', 'ge')) {
				$now = $jnow->toMySQL();
			} else {
				$now = $jnow->toMySQL();
			}
			$nullDate = $db->getNullDate();
			
			$sql = array();
			
			//do not select category
			/*if ((!empty($catids)) && ($catids[0]==0)) {
				return $data = array();
			}*/
			
			if (!empty($catids)) {
				$catids_new = $catids;
				foreach ($catids as $k => $catid) {
					if ($catid != 0) {
						$subcatids = modJacontentsliderHelper::getK2CategoryChildren($catid, true);
						if ($subcatids) {
							$catids_new = array_merge($catids_new, array_diff($subcatids, $catids_new));
						}
					}
				}
			}
			$query = array();
			
			JArrayHelper::toInteger($catids_new);
			
			$select = 'SELECT items.*, cate.name AS cateName, cate.alias AS categoryalias, cate.id as cateId';
			$from = ' FROM #__k2_categories as cate INNER JOIN #__k2_items as items ON cate.id = items.catid';
			$where = ' WHERE cate.published = 1 AND items.published = 1';
			
			$where .= " AND items.access <= {$aid}"
				. " AND items.trash = 0"
				. " AND cate.access <= {$aid}"
				. " AND cate.trash = 0";
			
			$where .= " AND ( items.publish_up = " . $db->Quote($nullDate) . " OR items.publish_up <= " . $db->Quote($now) . " )";
			$where .= " AND ( items.publish_down = " . $db->Quote($nullDate) . " OR items.publish_down >= " . $db->Quote($now) . " )";
			
			$pattern = '{show_me_your_p__sy}';
			if (!empty($catids_new)) {
				$where .= $pattern; //AND items.catid IN '.$arr_cate.'
			}
			
			// language filter
			$lang = JFactory::getLanguage();
			$languageTag = $lang->getTag();
			if ($app->getLanguageFilter()) {
				$where .= " AND items.language IN ('{$languageTag}','*') ";
			}
			
			// order by
			$order = $params->get('sort_order_field', 'created');
			$orderDir = $params->get('sort_order', 'DESC');
			switch ($order) {
				case 'created':
					$orderBy = " items.created {$orderDir}";
					break;
				case 'ordering':
					$orderBy = " items.ordering {$orderDir}";
					break;
				case 'hits':
					$orderBy = " items.hits {$orderDir}";
					break;
				case 'modified':
					$orderBy = " items.modified {$orderDir}";
					break;
				default:
					$orderBy = " RAND() ";
					break;
			}
			
			$query = $select . $from . $where . ' ORDER BY ' . $orderBy;
			
			
			$query .= ' LIMIT 0,' . $params->get('maxitems', 10);
			
			// Category filter
			$data = array();
			
			if (!empty($catids_new)) {
				foreach ($catids_new as $cat_id) {
					$queryk2 = str_replace($pattern, ' AND items.catid = ' . $cat_id, $query);
					$db->setQuery($queryk2);
					$data = array_merge($data, $db->loadObjectlist());
				}
			} else {
				$db->setQuery($query);
				$data = $db->loadObjectlist();
			}
			if (empty($data)) {
				$data = array();
			}
			
			$config = array();
			$model = new K2ModelItem($config);
			foreach ($data as $i => $row) {
				
				$data[$i]->id = $row->id;
				$data[$i]->text = $data[$i]->introtext;
				$data[$i]->title = $data[$i]->title;
				$data[$i]->introtext = $row->introtext;
				$data[$i]->catid = $row->catid;
				$data[$i]->cateName = $row->cateName;
				$data[$i]->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($row->id . ':' . urlencode($row->alias), $row->catid . ':' . urlencode($row->categoryalias))));
				
				$data[$i]->featured = $row->featured;
				// Get rating data from K2 Components
				$sqlRating = "SELECT * FROM #__k2_rating WHERE itemID = '" . intval($data[$i]->id) . "'  ";
				$db->setQuery($sqlRating);
				$rating = $db->loadRow();
				$data[$i]->rating = $rating;
				$image = modJacontentsliderHelper::parseImages($data[$i], $params, 'k2');
				if ($image) {
					$data[$i]->image = modJacontentsliderHelper::renderImage($row->title, $data[$i]->link, $image, $params, $params->get('iwidth'), $params->get('iheight'));
				} else {
					$data[$i]->image = '';
				}
				$data[$i] = modJacontentsliderHelper::processIntrotext($data[$i], $params->get('numchar', 0));
				$data[$i]->params = new JRegistry($data[$i]->params);
				if ($params->get('show_extra_fields', 0)) {
					$data[$i]->extra_fields = $model->getItemExtraFields($data[$i]->extra_fields, $data[$i]);
				}
			}
			
			$last_element[] = $data[count($data)-1];
			$data = array_merge($last_element, $data);
			array_pop($data);
			
			return $data;
		}
		
		
		/**
		 *
		 * Get K2 category children
		 * @param int $catid
		 * @param boolean $clear if true return array which is removed value construction
		 * @return array
		 */
		function getK2CategoryChildren($catid, $clear = false)
		{
			
			static $array = array();
			if ($clear)
				$array = array();
			$user = JFactory::getUser();
			$aid = $user->get('aid') ? $user->get('aid') : 1;
			$catid = (int)$catid;
			$db = JFactory::getDBO();
			$query = "SELECT * FROM #__k2_categories WHERE parent={$catid} AND published=1 AND trash=0 AND access<={$aid} ORDER BY ordering ";
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			if (empty($rows)) {
				$rows = array();
			}
			foreach ($rows as $row) {
				array_push($array, $row->id);
				if (modJacontentsliderHelper::hasK2Children($row->id)) {
					modJacontentsliderHelper::getK2CategoryChildren($row->id);
				}
			}
			return $array;
		}
		
		
		/**
		 *
		 * Check category has children
		 * @param int $id
		 * @return boolean
		 */
		function hasK2Children($id)
		{
			
			$user = JFactory::getUser();
			$aid = $user->get('aid') ? $user->get('aid') : 1;
			$id = (int)$id;
			$db = JFactory::getDBO();
			$query = "SELECT * FROM #__k2_categories WHERE parent={$id} AND published=1 AND trash=0 AND access<={$aid} ";
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			
			if (count($rows)) {
				return true;
			} else {
				return false;
			}
		}
		
		
		/**
		 *
		 * Get List Articles
		 * @param array $catid
		 * @param object $params
		 * @return array list articles
		 */
		function getListContent($catid, $params)
		{
			$mainframe = JFactory::getApplication();
			$app = JFactory::getApplication();
			// Get the dbo
			$db = JFactory::getDbo();
			
			// Get an instance of the generic articles model
			
			if (version_compare(JVERSION, '4.0', 'ge')){
				$model = BaseDatabaseModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
			}elseif (version_compare(JVERSION, '3.0', 'ge')) {
				$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
				//$model = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request' => true));
			} else{
				$model = JModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
			}

			/* cc.description as catdesc, cc.title as cattitle */
			// Set application parameters in model
			$appParams = JFactory::getApplication()->getParams();
			$model->setState('params', $appParams);
			
			
			$model->setState(
				'list.select',
				'a.id, a.title, a.alias,a.images, a.introtext, ' .
				'a.checked_out, a.checked_out_time, ' .
				'a.catid, a.created, a.created_by, a.created_by_alias, ' .
				// use created if modified is 0
				'CASE WHEN a.modified = 0 THEN a.created ELSE a.modified END as modified, ' .
				'a.modified_by,' .
				// use created if publish_up is 0
				'CASE WHEN a.publish_up = 0 THEN a.created ELSE a.publish_up END as publish_up, ' .
				'a.publish_down, a.attribs, a.metadata, a.metakey, a.metadesc, a.access, ' .
				'a.hits, a.featured,' . ' LENGTH(a.fulltext) AS readmore '
			);
			
			// Set the filters based on the module params
			$model->setState('list.start', 0);
			
			$model->setState('filter.published', 1);
			
			// Filter by language
			$model->setState('filter.language', $app->getLanguageFilter());
			
			$access = !JComponentHelper::getParams('com_content')->get('show_noauth');
			$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
			$model->setState('filter.access', $access);
			
			if ($params->get('sort_order_field', 'created') == "ordering") {
				$model->setState('list.ordering', 'a.ordering');
			} else {
				$model->setState('list.ordering', $params->get('sort_order_field', 'created'));
			}
			
			$model->setState('list.direction', $params->get('sort_order', 'DESC'));
			
			// Category filter
			$data = array();

			if (!empty($catid) && intval($catid[0]) > 0) {
				for ($i = 0; $i < count($catid); $i++) {
					if (intval($catid[$i]) > 0) {
						$model->setState('filter.category_id', $catid[$i]);
						$model->setState('list.limit', $params->get('maxitems', 10));
						$data = array_merge($data, $model->getItems());
					}
				}
			} else {
				$data = array_merge($data, $model->getItems());
			}
			// re-mapping data to handle js set direction for 1st article
			// 1st article was rolled to left: -140 when js init => it was hidden
			$last_element[] = $data[count($data)-1];
			$data = array_merge($last_element, $data);
			array_pop($data);
			
			$thumbnailMode = $params->get('source-articles-images-thumbnail_mode', 'crop');
			$aspect = $params->get('source-articles-images-thumbnail_mode-resize-use_ratio', '1');
			$crop = $thumbnailMode == 'crop' ? true : false;
			
			$jaimage = JAImage::getInstance();
			foreach ($data as $i => $row) {
				$data[$i]->text = $data[$i]->introtext;
				$mainframe->triggerEvent('onPrepareContent', array(&$data[$i], &$params, 0), true);
				$data[$i]->introtext = $data[$i]->text;
				$data[$i]->catid = $row->catid;
				$data[$i]->title = $row->title;
				$data[$i]->cateName = $row->category_title;
				$data[$i]->link = modJacontentsliderHelper::articleLink($row);
				
				$image = modJacontentsliderHelper::parseImages($data[$i], $params);
				if ($image) {
					$data[$i]->image = modJacontentsliderHelper::renderImage($row->title, $data[$i]->link, $image, $params, $params->get('iwidth'), $params->get('iheight'));
				} else {
					$data[$i]->image = '';
				}
				$data[$i] = modJacontentsliderHelper::processIntrotext($data[$i], $params->get('numchar', 0));
				
			}
			return $data;
		}
		
		/**
		 *
		 * Get List Images in Folder
		 * @param object $params
		 * @return array list images
		 */
		function getListFolder($params)
		{
			$folder = $params->get('folder_images');
			$path = JPath::clean(JPATH_ROOT . '/' . $folder);
			
			$data = array();
			if (JFolder::exists($path)) {
				$files = JFolder::files($path, "\.(jpg|png|gif|jpeg|bmp)$");
				$i = 0;
				
				foreach ($files as $file) {
					$image = JURI::root() . JPath::clean($folder . '/' . $file, '/');
					$item = new stdClass();
					$item->text = '';
					$item->introtext = $item->text;
					$item->catid = 1;
					$item->title = $file;
					$item->cateName = '';
					$item->link = $image;
					$item->image = modJacontentsliderHelper::renderImage($item->title, $item->link, $image, $params, $params->get('iwidth'), $params->get('iheight'));
					
					$data[$i] = $item;
					$data[$i] = modJacontentsliderHelper::processIntrotext($data[$i], $params->get('numchar', 0));
					$i++;
				}
			}
			
			return $data;
		}
		
		
		/**
		 * parser a image in the content.
		 * @param object $row object content
		 * @param object $params
		 * @return string image
		 */
		function parseImages(&$row, $params, $context = 'content')
		{
			//check if the context is k2 to get k2 images first
			if ($context == 'k2') {
				$arrImages = $this->getK2Images($row, $context);
				if (!empty($arrImages)) {
					return $arrImages['imageGeneric'];
				}
			}
			//check if there is image intro or image fulltext
			$images = "";
			if (isset($row->images)) {
				$images = json_decode($row->images);
			}
			if ((isset($images->image_fulltext) and !empty($images->image_fulltext)) || (isset($images->image_intro) and !empty($images->image_intro))) {
				$image = (isset($images->image_intro) and !empty($images->image_intro)) ? $images->image_intro : ((isset($images->image_fulltext) and !empty($images->image_fulltext)) ? $images->image_fulltext : "");
				return $image;
				
			} else {
				$text = $row->introtext . $row->text;
				$regex = "/\<img.+?src\s*=\s*[\"|\']([^\"]*)[\"|\'][^\>]*\>/";
				preg_match($regex, $text, $matches);
				$images = (count($matches)) ? $matches : array();
				if (count($images)) {
					return $images[1];
				}
			}
			return;
		}
		
		/**
		 *
		 * Get image in k2 item
		 * @param object $item
		 * @param string $context
		 * @return array
		 */
		function getK2Images($item, $context = 'content')
		{
			jimport('joomla.filesystem.file');
			//Image
			$arr_return = array();
			
			if ($context == 'k2') {
				if (JFile::exists(JPATH_SITE . '/media/k2/items/cache/' . md5("Image" . $item->id) . '_XS.jpg'))
					$arr_return['imageXSmall'] = JURI::root() . 'media/k2/items/cache/' . md5("Image" . $item->id) . '_XS.jpg';
				
				if (JFile::exists(JPATH_SITE . '/media/k2/items/cache/' . md5("Image" . $item->id) . '_S.jpg'))
					$arr_return['imageSmall'] = JURI::root() . 'media/k2/items/cache/' . md5("Image" . $item->id) . '_S.jpg';
				
				if (JFile::exists(JPATH_SITE . '/media/k2/items/cache/' . md5("Image" . $item->id) . '_M.jpg'))
					$arr_return['imageMedium'] = JURI::root() . 'media/k2/items/cache/' . md5("Image" . $item->id) . '_M.jpg';
				
				if (JFile::exists(JPATH_SITE . '/media/k2/items/cache/' . md5("Image" . $item->id) . '_L.jpg'))
					$arr_return['imageLarge'] = JURI::root() . 'media/k2/items/cache/' . md5("Image" . $item->id) . '_L.jpg';
				
				if (JFile::exists(JPATH_SITE . '/media/k2/items/cache/' . md5("Image" . $item->id) . '_XL.jpg'))
					$arr_return['imageXLarge'] = JURI::root() . 'media/k2/items/cache/' . md5("Image" . $item->id) . '_XL.jpg';
				
				if (JFile::exists(JPATH_SITE . '/media/k2/items/cache/' . md5("Image" . $item->id) . '_Generic.jpg'))
					$arr_return['imageGeneric'] = JURI::root() . 'media/k2/items/cache/' . md5("Image" . $item->id) . '_Generic.jpg';
			} else {
				//com content
			}
			
			return $arr_return;
		}
		
		/**
		 *
		 * Render image before display it
		 * @param string $title
		 * @param string $link
		 * @param string $image
		 * @param object $params
		 * @param int $width
		 * @param int $height
		 * @param string $attrs
		 * @param string $returnURL
		 * @return string image
		 */
		function renderImage($title, $link, $image, $params, $width = 0, $height = 0, $attrs = '', $returnURL = false)
		{
			global $database, $_MAMBOTS, $current_charset;
			if ($image) {
				$title = strip_tags($title);
				$thumbnailMode = $params->get('source-articles-images-thumbnail_mode', 'crop');
				$aspect = $params->get('source-articles-images-thumbnail_mode-resize-use_ratio', '1');
				$crop = $thumbnailMode == 'crop' ? true : false;
				$jaimage = JAImage::getInstance();
				
				if ($thumbnailMode != 'none' && $jaimage->sourceExisted($image)) {
					$imageURL = $jaimage->resize($image, $width, $height, $crop, $aspect);
					if ($returnURL) {
						return $imageURL;
					}
					if ($imageURL != $image && $imageURL) {
						$width = $width ? "width=\"$width\"" : "";
						$height = $height ? "height=\"$height\"" : "";
						$image = "<img src=\"{$imageURL}\"  alt=\"{$title}\" title=\"{$title}\" {$width} {$height} {$attrs} />";
					} else {
						$image = "<img $attrs src=\"{$image}\"  $attrs  alt=\"{$title}\" title=\"{$title}\" />";
					}
				} else {
					if ($returnURL) {
						return $image;
					}
					$width = $width ? "width=\"$width\"" : "";
					$height = $height ? "height=\"$height\"" : "";
					$image = "<img $attrs src=\"$image\" alt=\"{$title}\"   title=\"{$title}\" {$width} {$height} />";
				}
			} else {
				$image = '';
			}
			$image = '<a href="' . $link . '" title="" class="ja-image">' . $image . '</a>';
			// clean up globals
			return $image;
		}
		
		
		function mb_string_intersect($string1, $string2, $minChars = 5)
		{
			assert('$minChars > 1');
			
			$string1 = trim($string1);
			$string2 = trim($string2);
			
			$length1 = mb_strlen($string1);
			$length2 = mb_strlen($string2);
			
			if ($length1 > $length2) {
				// swap variables, shortest first
				
				$string3 = $string1;
				$string1 = $string2;
				$string2 = $string3;
				
				$length3 = $length1;
				$length1 = $length2;
				$length2 = $length3;
				
				unset($string3, $length3);
			}
			
			if ($length2 > 255) {
				return null; // to much calculation required
			}
			
			for ($l = $length1; $l >= $minChars; --$l) { // length
				for ($i = 0, $ix = $length1 - $l; $i <= $ix; ++$i) { // index
					$substring1 = mb_substr($string1, $i, $l);
					$found = mb_strpos($string2, $substring1);
					if ($found !== false) {
						return trim(mb_substr($string2, $found, mb_strlen($substring1)));
					}
				}
			}
			
			return null;
		}
		
		
		/**
		 * Process introtext
		 * @param string $introtext
		 * @param int $maxchars
		 * @return string
		 */
		
		function processIntrotext(&$row, $maxchars)
		{
			
			if (trim($maxchars) == '-1') {
				$row->introtext1 = preg_replace("/<img[^>]+\>/i", "", $row->introtext);
				return $row;
			}
			
			if (function_exists('mb_substr')) {
				
				$doc = JDocument::getInstance();
				$row->introtext = SmartTrim::mb_trim(strip_tags($row->introtext), 0, $maxchars, $doc->_charset);
				$row->introtext = stripslashes($row->introtext);
				$row->introtext1 = stripslashes($row->introtext);
			} else {
				$row->introtext = SmartTrim::trim(strip_tags($row->introtext), 0, $maxchars);
				$row->introtext = stripslashes($row->introtext);
				$row->introtext1 = stripslashes($row->introtext);
			}
			return $row;
		}
		
		public static function replaceImage(&$row, $maxchars, $showimage, $width = 0, $height = 0)
		{
			// expression to search for
			
			if (trim($maxchars) == '-1') {
				$row->introtext1 = preg_replace("/<img[^>]+\>/i", "", $row->introtext);
			} else {
				$row->introtext1 = strip_tags($row->introtext);
			}
			
			// clean up globals
			return $row->image;
		}
		
		/**
		 *
		 */
		function processImage(&$row, $width, $height)
		{
			/* for 1.5 - don't need to use image parameter */
			return 0;
			/* End 1.5 */
		}
	}
	
	if (!class_exists('SmartTrim')) {
		/**
		 * Smart Trim String Helper
		 *
		 */
		class SmartTrim
		{
			
			
			/**
			 *
			 * process string smart split
			 * @param string $strin string input
			 * @param int $pos start node split
			 * @param int $len length of string that need to split
			 * @param string $hiddenClasses show and redmore with property display: none or invisible
			 * @param string $encoding type of string endcoding
			 * @return string string that is smart splited
			 */
			public static function mb_trim($strin, $pos = 0, $len = 10000, $hiddenClasses = '', $encoding = 'utf-8')
			{
				mb_internal_encoding($encoding);
				$strout = trim($strin);
				
				$pattern = '/(<[^>]*>)/';
				$arr = preg_split($pattern, $strout, -1, PREG_SPLIT_DELIM_CAPTURE);
				$left = $pos;
				$length = $len;
				$strout = '';
				for ($i = 0; $i < count($arr); $i++) {
					/*$arr [$i] = trim ( $arr [$i] );*/
					if ($arr[$i] == '')
						continue;
					if ($i % 2 == 0) {
						if ($left > 0) {
							$t = $arr[$i];
							$arr[$i] = mb_substr($t, $left);
							$left -= (mb_strlen($t) - mb_strlen($arr[$i]));
						}
						
						if ($left <= 0) {
							if ($length > 0) {
								$t = $arr[$i];
								$arr[$i] = mb_substr($t, 0, $length);
								$length -= mb_strlen($arr[$i]);
								if ($length <= 0) {
									$arr[$i] .= '...';
								}
								
							} else {
								$arr[$i] = '';
							}
						}
					} else {
						if (SmartTrim::isHiddenTag($arr[$i], $hiddenClasses)) {
							if ($endTag = SmartTrim::getCloseTag($arr, $i)) {
								while ($i < $endTag)
									$strout .= $arr[$i++] . "\n";
							}
						}
					}
					$strout .= $arr[$i] . "\n";
				}
				//echo $strout;
				return SmartTrim::toString($arr, $len);
			}
			
			
			/**
			 *
			 * process simple string split
			 * @param string $strin string input
			 * @param int $pos start node
			 * @param int $len length of string that need to split
			 * @param string $hiddenClasses show and redmore with property display: none or invisible
			 * @return string
			 */
			function trim($strin, $pos = 0, $len = 10000, $hiddenClasses = '')
			{
				$strout = trim($strin);
				
				$pattern = '/(<[^>]*>)/';
				$arr = preg_split($pattern, $strout, -1, PREG_SPLIT_DELIM_CAPTURE);
				$left = $pos;
				$length = $len;
				$strout = '';
				for ($i = 0; $i < count($arr); $i++) {
					/*$arr [$i] = trim ( $arr [$i] );*/
					if ($arr[$i] == '')
						continue;
					if ($i % 2 == 0) {
						if ($left > 0) {
							$t = $arr[$i];
							$arr[$i] = substr($t, $left);
							$left -= (strlen($t) - strlen($arr[$i]));
						}
						
						if ($left <= 0) {
							if ($length > 0) {
								$t = $arr[$i];
								$arr[$i] = substr($t, 0, $length);
								$length -= strlen($arr[$i]);
								if ($length <= 0) {
									$arr[$i] .= '...';
								}
								
							} else {
								$arr[$i] = '';
							}
						}
					} else {
						if (SmartTrim::isHiddenTag($arr[$i], $hiddenClasses)) {
							if ($endTag = SmartTrim::getCloseTag($arr, $i)) {
								while ($i < $endTag)
									$strout .= $arr[$i++] . "\n";
							}
						}
					}
					$strout .= $arr[$i] . "\n";
				}
				//echo $strout;
				return SmartTrim::toString($arr, $len);
			}
			
			
			/**
			 * Check is Hidden Tag
			 * @param string tag
			 * @param string type of hidden
			 * @return boolean
			 */
			public static function isHiddenTag($tag, $hiddenClasses = '')
			{
				//By pass full tag like img
				if (substr($tag, -2) == '/>')
					return false;
				if (in_array(SmartTrim::getTag($tag), array('script', 'style')))
					return true;
				if (preg_match('/display\s*:\s*none/', $tag))
					return true;
				if ($hiddenClasses && preg_match('/class\s*=[\s"\']*(' . $hiddenClasses . ')[\s"\']*/', $tag))
					return true;
			}
			
			
			/**
			 *
			 * Get close tag from content array
			 * @param array $arr content
			 * @param int $openidx
			 * @return int 0 if find not found OR key of close tag
			 */
			function getCloseTag($arr, $openidx)
			{
				/*$tag = trim ( $arr [$openidx] );*/
				$tag = $arr[$openidx];
				if (!$openTag = SmartTrim::getTag($tag))
					return 0;
				
				$endTag = "<$openTag>";
				$endidx = $openidx + 1;
				$i = 1;
				while ($endidx < count($arr)) {
					if (trim($arr[$endidx]) == $endTag)
						$i--;
					if (SmartTrim::getTag($arr[$endidx]) == $openTag)
						$i++;
					if ($i == 0)
						return $endidx;
					$endidx++;
				}
				return 0;
			}
			
			
			/**
			 *
			 * Get tag in content
			 * @param string $tag
			 * @return string tag
			 */
			public static function getTag($tag)
			{
				if (preg_match('/\A<([^\/>]*)\/>\Z/', trim($tag), $matches))
					return ''; //full tag
				if (preg_match('/\A<([^ \/>]*)([^>]*)>\Z/', trim($tag), $matches)) {
					//echo "[".strtolower($matches[1])."]";
					return strtolower($matches[1]);
				}
				//if (preg_match ('/<([^ \/>]*)([^\/>]*)>/', trim($tag), $matches)) return strtolower($matches[1]);
				return '';
			}
			
			
			/**
			 *
			 * convert array to string
			 * @param array $arr
			 * @param int $len
			 * @return string
			 */
			public static function toString($arr, $len)
			{
				$i = 0;
				$stack = new JAStack();
				$length = 0;
				while ($i < count($arr)) {
					/*$tag = trim ( $arr [$i ++] );*/
					$tag = $arr[$i++];
					if ($tag == '')
						continue;
					if (SmartTrim::isCloseTag($tag)) {
						if ($ltag = $stack->getLast()) {
							if ('</' . SmartTrim::getTag($ltag) . '>' == $tag)
								$stack->pop();
							else
								$stack->push($tag);
						}
					} else if (SmartTrim::isOpenTag($tag)) {
						$stack->push($tag);
					} else if (SmartTrim::isFullTag($tag)) {
						//echo "[TAG: $tag, $length, $len]\n";
						if ($length < $len)
							$stack->push($tag);
					} else {
						$length += strlen($tag);
						$stack->push($tag);
					}
				}
				
				return $stack->toString();
			}
			
			
			/**
			 *
			 * Check is open tag
			 * @param string $tag
			 * @return boolean
			 */
			public static function isOpenTag($tag)
			{
				if (preg_match('/\A<([^\/>]+)\/>\Z/', trim($tag), $matches))
					return false; //full tag
				if (preg_match('/\A<([^ \/>]+)([^>]*)>\Z/', trim($tag), $matches))
					return true;
				return false;
			}
			
			
			/**
			 *
			 * Check is full tag
			 * @param string $tag
			 * @return boolean
			 */
			public static function isFullTag($tag)
			{
				//echo "[Check full: $tag]\n";
				if (preg_match('/\A<([^\/>]*)\/>\Z/', trim($tag), $matches))
					return true; //full tag
				return false;
			}
			
			
			/**
			 *
			 * Check is close tag
			 * @param string $tag
			 * @return boolean
			 */
			public static function isCloseTag($tag)
			{
				if (preg_match('/<\/(.*)>/', $tag))
					return true;
				return false;
			}
		}
	}
	if (!class_exists('JAStack')) {
		
		/**
		 * News Pro Module JAStack Helper
		 */
		class JAStack
		{
			/*
			 * array
			 */
			var $_arr = null;
			
			
			/**
			 * Constructor
			 *
			 * For php4 compatability we must not use the __constructor as a constructor for plugins
			 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
			 * This causes problems with cross-referencing necessary for the observer design pattern.
			 *
			 */
			public function __construct()
			{
				$this->_arr = array();
			}
			
			
			/**
			 *
			 * Push item value into array
			 * @param observe $item value of item that will input to stack
			 * @return unknown
			 */
			function push($item)
			{
				$this->_arr[count($this->_arr)] = $item;
			}
			
			
			/**
			 *
			 * Pop item value from array
			 * @param observe $item value of item that will pop from stack
			 * @return unknow value of item that is pop from array
			 */
			function pop()
			{
				if (!$c = count($this->_arr))
					return null;
				$ret = $this->_arr[$c - 1];
				unset($this->_arr[$c - 1]);
				return $ret;
			}
			
			
			/**
			 *
			 * Get value of last element in array
			 * @return unknown value of last element in array
			 */
			function getLast()
			{
				if (!$c = count($this->_arr))
					return null;
				return $this->_arr[$c - 1];
			}
			
			
			/**
			 *
			 * Convert array to string
			 * @return string
			 */
			function toString()
			{
				$output = '';
				foreach ($this->_arr as $item) {
					$output .= $item;
				}
				return $output;
			}
		}
	}
?>
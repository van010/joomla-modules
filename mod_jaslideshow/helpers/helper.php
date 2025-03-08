<?php
/**
 * $JA#COPYRIGHT$
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE . '/components/com_content/helpers/route.php');
jimport('joomla.application.component.model');
jimport('joomla.html.parameter');

if (JFile::exists(JPATH_SITE . '/components/com_k2/helpers/route.php')) {
	require_once(JPATH_SITE . '/components/com_k2/helpers/route.php');
}


/**
 * mod JA Silde Show Helper class.
 */
class ModJASlideshow3
{

	/**
	 * @var string $condition;
	 *
	 * @access private
	 */
	var $conditons = '';

	/**
	 * @var string $order
	 *
	 * @access private
	 */
	var $order = 'a.ordering';
	/**
	 * @var string $mode
	 *
	 * @access private
	 */
	var $mode = 'DESC';
	/**
	 * @var object $mod_params
	 *
	 * @access private
	 */
	var $mod_params = null;
	/**
	 * @var object $_params
	 *
	 * @access private
	 */
	var $_params = null;

	/**
	 * @var string $limit
	 *
	 * @access private
	 */
	var $limit = '5';


	/**
	 *
	 * reference to the global SlideShowHelper object
	 * @Returns a reference to the global SlideShowHelper object
	 */
	public static function &getInstance()
	{
		static $instance = null;
		if (!$instance) {
			$instance = new ModJASlideshow3();
		}
		return $instance;
	}


	/**
	 *
	 * Load configuration of module
	 * @param object $params
	 * @param string $modulename
	 * @param object param of module
	 */
	public function loadConfig($params, $modulename = "mod_jaslideshow")
	{
		$mainframe = JFactory::getApplication();
		$use_cache = $mainframe->getCfg("caching");
		$this->mod_params = $params;
		if ($params->get('enable_cache') == "1" && $use_cache == "1") {
			$cache = JFactory::getCache();
			$cache->setCaching(true);
			$cache->setLifeTime($params->get('cache_time', 30) * 60);
			$this->_params = $cache->get(array((new ModJASlideshow3()), 'loadProfile'), array($params, $modulename));
		} else {
			$this->_params = ModJASlideshow3::loadProfile($params, $modulename);
		}
		return $this->_params;
	}


	/**
	 *
	 * Load profile for configuration
	 * @param object $params
	 * @param string $modulename
	 * @return object new param
	 */
	public static function loadProfile($params, $modulename = "mod_jaslideshow")
	{
		$mainframe = JFactory::getApplication();
		$profilename = $params->get('profile', 'default');
		$params_new = new JRegistry('');

		if (!empty($profilename)) {
			$path = JPATH_ROOT . "/modules/" . $modulename . "/admin/japrofile/config.xml";
			$ini_file = JPATH_ROOT . "/modules/" . $modulename . "/profiles/" . $profilename . ".ini";
			$config_content = "";
			if (JFile::exists($ini_file)) {
				$config_content = JFile::read($ini_file);
			}

			if (empty($config_content)) {
				$config_content = '';
				$ini_file_in_temp = JPATH_SITE . '/templates/' . $mainframe->getTemplate() . '/html/' . $modulename . '/' . $profilename . ".ini";
				if (JFile::exists($ini_file_in_temp)) {
					$config_content = JFile::read($ini_file_in_temp);
				}
			}

			if (JFile::exists($path)) {
				$params_new = new JRegistry($config_content);
			}
		}
		$params_new->set('enable_cache', $params->get('enable_cache', "1"));
		$params_new->set('cache_time', $params->get('cache_time', "30"));
		$params_new->set('open_target', $params->get('open_target', "parent"));
		$params_new->set('source-articles-images-thumbnail_mode', $params->get('source-articles-images-thumbnail_mode', "crop"));
		$params_new->set('source-articles-images-thumbnail_mode-resize-use_ratio', $params->get('source-articles-images-thumbnail_mode-resize-use_ratio', "1"));
		$params_new->set('source', $params->get('source', "articles"));
		$params_new->set('source-articles-display_model', $params->get('source-articles-display_model', '0'));
		$params_new->set('source-articles-display_model-modcats-category', $params->get('source-articles-display_model-modcats-category', '0'));
		$params_new->set('source-articles-sort_order_field', $params->get('source-articles-sort_order_field', "created"));
		$params_new->set('source-articles-sort_order', $params->get('source-articles-sort_order', "ASC"));
		$params_new->set('source-articles-max_items', $params->get('source-articles-max_items', 5));
		$params_new->set('source-images-orderby', $params->get('source-images-orderby', 0));
		$params_new->set('source-images-sort', $params->get('source-images-sort', 0));

		return $params_new;
	}


	/**
	 * magic method
	 *
	 * @param string method  method is calling
	 * @param string $params.
	 * @return unknown
	 */
	function callMethod($method, $params)
	{
		if (method_exists($this, $method)) {
			if (is_callable(array($this, $method))) {
				return call_user_func(array($this, $method), $params);
			}
		}
		return false;
	}


	/**
	 * get listing items from rss link or from list of categories.
	 *
	 * @param JParameter $params
	 * @return array
	 */
	function getListArticles($params)
	{
		// check cache was endable ?
		if ($params->get('enable_cache')) {
			$cache = JFactory::getCache();
			$cache->setCaching(true);
			$cache->setLifeTime($params->get('cache_time', 30) * 60);
			$rows = $cache->get(array($this, 'getArticles'), array($params));
		} else {
			$rows = $this->getArticles($params);
		}

		return $rows;
	}

	/**
	 * get listing items from rss link or from list of categories.
	 *
	 * @param JParameter $params
	 * @return array
	 */

	function getListCom_k2($params)
	{
		$rows = $this->getListK2($params);
		return $rows;
	}

	/**
	 * get list k2 items follow setting configuration.
	 *
	 * @param JParameter $param
	 * @return array
	 */
	public function getListK2($params)
	{
		if (!ModJASlideshow3::checkComponent('com_k2')) {
			return array();
		}

		$catsid = $params->get('k2catsid');
		$catids = array();
		if (!is_array($catsid)) {
			$catids[] = $catsid;
		} else {
			$catids = $catsid;
		}

		JArrayHelper::toInteger($catids);
		if ($catids) {
			if ($catids && count($catids) > 0) {
				foreach ($catids as $k => $catid) {
					if (!$catid)
						unset($catids[$k]);
				}
			}
		}

		jimport('joomla.filesystem.file');

		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$aid = $user->get('aid') ? $user->get('aid') : 1;
		$db = JFactory::getDBO();

		$jnow = JFactory::getDate();
		$now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
		$nullDate = $db->getNullDate();

		$query = "SELECT i.*, c.name AS categoryname,c.id AS categoryid, c.alias AS categoryalias, c.name as cattitle, c.params AS categoryparams";
		$query .= "\n FROM #__k2_items as i LEFT JOIN #__k2_categories c ON c.id = i.catid";
		$query .= "\n WHERE i.published = 1 AND i.access <= {$aid} AND i.trash = 0 AND c.published = 1 AND c.access <= {$aid} AND c.trash = 0";
		$query .= "\n AND ( i.publish_up = " . $db->Quote($nullDate) . " OR i.publish_up <= " . $db->Quote($now) . " )";
		$query .= "\n AND ( i.publish_down = " . $db->Quote($nullDate) . " OR i.publish_down >= " . $db->Quote($now) . " )";

		if ($catids) {
			$catids_new = $catids;
			foreach ($catids as $k => $catid) {
				$subcatids = ModJASlideshow3::getK2CategoryChildren($catid, true);
				if ($subcatids) {
					$catids_new = array_merge($catids_new, array_diff($subcatids, $catids_new));
				}
			}
			$catids = implode(',', $catids_new);
			$query .= "\n AND i.catid IN ($catids)";
		}

		$featured = $params->get('source-articles-display_model', 2);
		switch ($featured) {
			case 0:
				$query .= " AND i.featured = 0 ";
				break;
			case 1:
				$query .= " AND i.featured = 1 ";
				break;
		}
		
		// language filter
		$lang = JFactory::getLanguage();
		$languageTag = $lang->getTag();
		if ($app->getLanguageFilter()) {
			$query .= " AND i.language IN ('{$languageTag}','*') ";
		}

		// Set ordering
		$ordering = $params->get('source-articles-sort_order_field', 'created');
		switch ($ordering) {

			case 'created':
				$orderby = 'i.created';
				break;

			case 'hits':
				$orderby = 'i.hits';
				break;

			case 'ordering':
				if (JRequest::getInt('featured') == '2')
					$orderby = 'i.featured_ordering';
				else
					$orderby = 'c.ordering, i.ordering';
				break;
		}
		$dir = $params->get('source-articles-sort_order', 'DESC');
		$query .= " ORDER BY " . $orderby . " " . $dir . " ";

		if ((int)trim($params->get('source-articles-max_items', 5)) == 0) {
			$query = str_replace("i.published = 1 AND", "i.published = 10 AND", $query);
		}
		$db->setQuery($query, 0, (int)trim($params->get('source-articles-max_items', 5)));

		$items = $db->loadObjectList();

		if ($items) {

			$i = 0;
			$showHits = $params->get('show_hits', 0);
			$showHits = $showHits == "1" ? true : false;
			$showimg = $params->get('show_image', 1);
			$w = (int)$params->get('width', 80);
			$h = (int)$params->get('height', 96);
			$showdate = $params->get('show_date', 1);

			$thumbnailMode = $params->get('thumbnail_mode', 'crop');
			$aspect = $params->get('use_ratio', '1');
			$crop = $thumbnailMode == 'crop' ? true : false;
			$lists = array();

			foreach ($items as &$item) {

				$item->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($item->id . ':' . urlencode($item->alias), $item->catid . ':' . urlencode($item->categoryalias))));

				$item->text = htmlspecialchars($item->title);

				if ($showdate) {
					$item->date = $item->modified == null || $item->modified == "" || $item->modified == "0000-00-00 00:00:00" ? $item->created : $item->modified;
				}
				$this->parseImagesK2($item, $params);

				//Author
				$author = JFactory::getUser($item->created_by);
				$item->creater = $author->name;

				if ($showHits) {
					$item->hits = isset($item->hits) ? $item->hits : 0;
				} else {
					$item->hits = null;
				}
			}
		}
		return $items;
	}

	/**
	 * parser a image in the content.
	 * @param object $row object content
	 * @param object $params
	 * @return string image
	 */
	function parseImagesK2(&$row, $params)
	{

		$text = $row->introtext . $row->fulltext;
		$row->date = strtotime($row->modified) ? $row->created : $row->modified;
		$row->thumbnail = '';
		$row->mainImage = '';
		$images = "";
		//check introimage va fullimage
		if (isset($row->images)) {
			$images = json_decode($row->images);
		}
		if ((isset($images->image_fulltext) and !empty($images->image_fulltext)) || (isset($images->image_intro) and !empty($images->image_intro))) {
			$row->mainImage = (isset($images->image_fulltext) and !empty($images->image_fulltext)) ? $images->image_fulltext : ((isset($images->image_intro) and !empty($images->image_intro)) ? $images->image_intro : "");
			$row->thumbnail = (isset($images->image_intro) and !empty($images->image_intro)) ? $images->image_intro : ((isset($images->image_fulltext) and !empty($images->image_fulltext)) ? $images->image_fulltext : "");
		} else {
			$data = $this->parseImageNew($text);
			if (!empty($data) && !empty($data["mainImage"])) {
				$row->mainImage = isset($data["mainImage"]) ? $data["mainImage"] : "";
				$row->thumbnail = isset($data["thumbnail"]) ? $data["thumbnail"] : $row->mainImage;
			} else {
				$data = $this->parserCustomTag($text);
				if (isset($data[1][0])) {
					$tmp = $this->parseParams($data[1][0]);
					$row->mainImage = isset($tmp['main']) ? $tmp['main'] : '';
					$row->thumbnail = isset($tmp['thumb']) ? $tmp['thumb'] : '';
				} else {
					$regex = "/\<img.+src\s*=\s*\"([^\"]*)\"[^\>]*\>/";
					preg_match($regex, $text, $matches);
					$images = (count($matches)) ? $matches : array();
					if (count($images)) {
						$row->mainImage = $images[1];
						$row->thumbnail = $images[1];
					}
				}
			}
		}

		$arrImages = $this->getK2Images($row, 'k2');

		if (!empty($arrImages)) {

			//return $arrImages['imageGeneric'];
			$row->mainImage = str_replace(JURI::base(), '', $arrImages['imageGeneric']);
			$row->thumbnail = str_replace(JURI::base(), '', $arrImages['imageGeneric']);
		}
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
	 * Get K2 category children
	 * @param int $catid
	 * @param boolean $clear if true return array which is removed value construction
	 * @return array
	 */
	function getK2CategoryChildren($catid, $clear = false)
	{

		static $array = array();
		if ($clear){
			$array = array();
		}

		$user = JFactory::getUser();
		$aid = $user->get('aid') ? $user->get('aid') : 1;
		$catid = (int)$catid;
		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__k2_categories WHERE parent={$catid} AND published=1 AND trash=0 AND access<={$aid} ORDER BY ordering ";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $row) {
			array_push($array, $row->id);
			if (ModJASlideshow3::hasK2Children($row->id)) {
				ModJASlideshow3::getK2CategoryChildren($row->id);
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
	 * get articles from list of categories and follow up owner paramer.
	 *
	 * @param JParameter $params.
	 * @return array list of articles
	 */
	function getArticles($params)
	{

		$this->setOrder($params->get('source-articles-sort_order_field', 'created'), $params->get('source-articles-sort_order', 'DESC'));
		$this->setLimit($params->get('source-articles-max_items', 5));
		$rows = $this->fetchListArticles($params);
		return $rows;
	}


	/**
	 *
	 * parser jaimage tag
	 * @param string $text
	 * @return array
	 */
	function parserCustomTag($text)
	{
		if (preg_match("#{jaimage(.*)}#s", $text, $matches, PREG_OFFSET_CAPTURE)) {
			return $matches;
		}
		return null;
	}


	/**
	 * get list articles follow setting configuration.
	 *
	 * @param JParameter $param
	 * @return array
	 */
	public function fetchListArticles($params)
	{
		$mainframe = JFactory::getApplication();
		$app = JFactory::getApplication();
		// Get the dbo
		$db = JFactory::getDbo();
		if (!class_exists("ContentModelArticles")) {
			require_once JPATH_BASE . "/components/com_content/models/articles.php";
		}
		// Get an instance of the generic articles model
		//$model = JModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
		if (version_compare(JVERSION, '3.0', 'ge')) {
			$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		} else if (version_compare(JVERSION, '2.5', 'ge')) {
			$model = JModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
		} else {
			$model = JModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
		}
		// Set application parameters in model
		$appParams = JFactory::getApplication()->getParams();
		$model->setState('params', $appParams);

		$model->setState('list.select', 'a.fulltext, a.id, a.title, a.alias,a.images, a.introtext, a.state, a.catid, a.created, a.created_by, a.created_by_alias,' .
			' a.modified, a.modified_by,a.publish_up, a.publish_down, a.attribs, a.metadata, a.metakey, a.metadesc, a.access,' .
			' a.hits, c.alias AS category_alias, a.featured,' .
			' LENGTH(a.fulltext) AS readmore');

		// Set the filters based on the module params

		$model->setState('list.start', 0);
		if ($this->limit) {
			$model->setState('list.limit', (int)trim($this->limit));
		}

		$model->setState('filter.published', 1);
		
		// Filter by language
		$model->setState('filter.language', $app->getLanguageFilter());				

		// Access filter
		$access = !JComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
		$model->setState('filter.access', $access);
		$model->setState('a.state', 1);
		$categories = $params->get('source-articles-display_model-modcats-category', '');
		JArrayHelper::toInteger($categories);
		if (count($categories) > 0 && $categories[0] > 0) {
			if ($categories && $categories[0] > 0) {
				$catids_new = $categories;
				foreach ($categories as $k => $catid) {
					$subcatids = $this->getCategoryChildren($catid, true);
					if ($subcatids) {
						$catids_new = array_merge($catids_new, array_diff($subcatids, $catids_new));
					}
				}
				$categories = $catids_new;
			}
			$model->setState('filter.category_id', $categories);
		}
		if ($params->get("source-articles-display_model", "0") == '1') {
			$model->setState('filter.featured', 'only');
		} else if ($params->get("source-articles-display_model", '0') == '0') {
			$model->setState('filter.featured', 'hide');
		}

		$dir = !empty($this->mode) ? $this->mode : 'DESC';
		$model->setState('list.ordering', $this->order);
		$model->setState('list.direction', $dir);
		$items = $model->getItems();

		JPluginHelper::importPlugin('content');
		$dispatcher = JDispatcher::getInstance();
		$params = $mainframe->getParams('com_content');
		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');

		if ($items) {

			foreach ($items as $key => $item) {
				$item->slug = $item->id . ':' . $item->alias;
				$item->catslug = $item->catid . ':' . $item->category_alias;
				ModJASlideshow3::parseImages($item, $params);

				if ($access || in_array($item->access, $authorised)) {
					// We know that user has the privilege to view the article
					$item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
				} else {
					// Angie Fixed Routing
					$app = JFactory::getApplication();
					$menu = $app->getMenu();
					$menuitems = $menu->getItems('link', 'index.php?option=com_users&view=login');
					if (isset($menuitems[0])) {
						$Itemid = $menuitems[0]->id;
					} elseif (JRequest::getInt('Itemid') > 0) { //use Itemid from requesting page only if there is no existing menu
						$Itemid = JRequest::getInt('Itemid');
					}
					$item->link = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $Itemid);
				}
				$item->text = htmlspecialchars($item->introtext);
				if ($this->mod_params->get('showvideos', 0) == 1) {
					//Check if used plugin JAAV
					if (!(strpos($item->fulltext . $item->introtext, '{jamedia') === false)) {
						$item->video = $this->parseString($item->fulltext . $item->introtext);
					} else {
						//Check if isset iframe
						if ($this->parseVideo($item->fulltext . $item->introtext)) {
							$item->video = $this->parseVideo($item->fulltext . $item->introtext);
						} else {
							$item->video = null;
						}
					}
				}
				$item->introtext = JHtml::_('content.prepare', $item->introtext);
			}
		}

		return $items;
	}

	function renderVideo($item, $width, $height)
	{
		if (isset($item['vid'])) {
			switch ($item['mtype']) {
				case 'vimeo':
					$item['src'] = $this->vimeoCodeEmbed($item['vid']);
					$context = '<div class="ja-slide-video" style="padding-bottom: ' . ($height / $width * 100) . '%"><iframe id="vimeo-' . JApplication::stringURLSafe($item['vid']) . '" src="' . $item['src'] . '" data-src="' . $item['src'] . '" style="border: none;" allowFullScreen></iframe></div>';
					break;
				case 'youtube':
					$item['src'] = $this->youtubeCodeEmbed($item['vid']);
					$context = '<div class="ja-slide-video" style="padding-bottom: ' . ($height / $width * 100) . '%"><iframe id="youtube-' . JApplication::stringURLSafe($item['vid']) . '" wmode="Opaque" src="' . $item['src'] . '" data-src="' . $item['src'] . '" style="border: none;" allowFullScreen></iframe></div>';
					break;
				default:
					return;
					break;
			}
			return $context;
		}
	}

	public function parseString($content)
	{
		if (preg_match_all("#{jamedia[^>]+}#iU", $content, $matches)) {
			$data = JUtility::parseAttributes($matches[0][0]);
			if(!empty($data['src']) && !isset($data['vid'])){

				if(strpos($data['src'], 'vimeo.com') !== false){
					$vid = str_replace(
						array(
							'http:',
							'https:',
							'//player.vimeo.com/video/',
							'//vimeo.com/'), '', $data['src']);
				} else {
					$vid = str_replace(
						array(
							'http:',
							'https:',
							'//youtu.be/',
							'//www.youtube.com/embed/',
							'//youtube.googleapis.com/v/',
							'//www.youtube.com/watch?v='), '', $data['src']);
				}

				//clean
				$vid = preg_replace('@(\/|\?).*@i', '', $vid);

				if (!(empty($vid))) {
					$data['vid'] = $vid;
				}
			}

			return $data;
		}
		return null;
	}

	public function parseVideo($content)
	{
		$data = array();
		if (preg_match_all('@<iframe\s[^>]*src=[\"|\']([^\"\'\>]+)[^>].*?</iframe>@ms', $content, $iframesrc) > 0) {
			if (isset($iframesrc[1])) {

				$data = array();

				if(strpos($iframesrc[1][0], 'vimeo.com') !== false){
					$vid = str_replace(
						array(
							'http:',
							'https:',
							'//player.vimeo.com/video/'), '', $iframesrc[1][0]);

					$data['mtype'] = 'vimeo';
				} else {
					$vid = str_replace(
						array(
							'http:',
							'https:',
							'//youtu.be/',
							'//www.youtube.com/embed/',
							'//youtube.googleapis.com/v/'), '', $iframesrc[1][0]);

					$data['mtype'] = 'youtube';
				}

				//clean
				$vid = preg_replace('@(\/|\?).*@i', '', $vid);

				if (!(empty($vid))) {
					$data['src'] = $iframesrc[1][0];
					$data['vid'] = $vid;
				}
			}

			return $data;
		}

		return;
	}

	function youtubeCodeEmbed($vCode)
	{
		return '//youtube.com/embed/' . $vCode . '?controls=0&amp;showinfo=0&amp;wmode=transparent';
	}

	function vimeoCodeEmbed($vCode)
	{
		return '//player.vimeo.com/video/' . $vCode . '?title=0&amp;portrait=0&amp;byline=0';
	}

	function youtubeThumb($vCode)
	{
		return 'img.youtube.com/vi/' . $vCode . '/0.jpg';
	}

	function vimeoThumb($vCode)
	{
		return 'vimeo.com/api/v2/video/' . $vCode . '.json';
	}

	function getVimeoThumb($data)
	{
		$data = @file_get_contents($data);
		$data = json_decode($data);
		if (empty($data))
			return '';
		return $data[0]->thumbnail_large;
	}

	function renderVideoThumb($item, $params, $thumbWidth, $thumbHeight)
	{
		if (!JFolder::exists(JPATH_SITE . '/cache/jasl_video')) {
			JFolder::create(JPATH_SITE . '/cache/jasl_video');
		}

		$title = '';
		if (isset($item['title'])) {
			$title = $item['title'];
		}

		if (isset($item['vid'])) {
			switch ($item['mtype']) {
				case 'vimeo':

					//check for caching
					if (($isjpg = is_file(JPATH_SITE . '/cache/jasl_video/' . $item['vid'] . '.jpg')) ||
						is_file(JPATH_SITE . '/cache/jasl_video/' . $item['vid'] . '.png')
					) {
						$thumb = $this->renderImage($title, 'cache/jasl_video/original_' . $item['vid'] . ($isjpg ? '.jpg' : '.png'), $params, $thumbWidth, $thumbHeight, '');
					} else {

						$thumb = $this->vimeoThumb($item['vid']);
						if ($thumb_url = $this->getVimeoThumb('http://' . $thumb)) {

							//cache it
							$thumb_ext = substr($thumb_url, strrpos($thumb_url, '.'));
							@file_put_contents(JPATH_SITE . '/cache/jasl_video/original_' . $item['vid'] . $thumb_ext, @file_get_contents($thumb_url));

							$thumb = $this->renderImage($title, 'cache/jasl_video/original_' . $item['vid'] . $thumb_ext, $params, $thumbWidth, $thumbHeight, '');

						} else {
							$thumb = '';
						}
					}
					break;

				case 'youtube':

					//cache it
					if (!is_file(JPATH_SITE . '/cache/jasl_video/' . $item['vid'] . '.jpg')) {
						$thumb = $this->youtubeThumb($item['vid']);
						@file_put_contents(JPATH_SITE . '/cache/jasl_video/' . $item['vid'] . '.jpg', @file_get_contents('http://' . $thumb));
					}

					$thumb = $this->renderImage($title, 'cache/jasl_video/' . $item['vid'] . '.jpg', $params, $thumbWidth, $thumbHeight, '');
					break;

				default:
					return;
					break;
			}

			return $thumb;
		}
	}


	/**
	 *
	 * Get category children
	 * @param int $catid
	 * @param boolean $clear if true return array which is removed value construction
	 * @return array
	 */
	function getCategoryChildren($catid, $clear = false)
	{

		static $array = array();
		if ($clear)
			$array = array();
		$user = JFactory::getUser();
		$aid = $user->get('aid') ? $user->get('aid') : 1;
		$catid = (int)$catid;
		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__categories WHERE parent_id={$catid} AND published=1 AND access={$aid} ";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $row) {
			array_push($array, $row->id);
			if ($this->hasChildren($row->id)) {
				$this->getCategoryChildren($row->id);
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
	function hasChildren($id)
	{

		$user = JFactory::getUser();
		$aid = $user->get('aid') ? $user->get('aid') : 1;
		$id = (int)$id;
		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__categories WHERE parent_id={$id} AND published=1 AND access={$aid} ";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (count($rows)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * add sort order sql
	 *
	 * @param string $order is article's field.
	 * @param string $mode is DESC or ASC
	 * @return .
	 */
	function setOrder($order, $mode)
	{
		$this->order = ' a.' . $order;
		$this->mode = $mode;
		return $this;
	}


	/**
	 * add set limit sql
	 *
	 * @param integer $limit.
	 * @return .
	 */
	function setLimit($limit)
	{
		$this->limit = $limit;
		return $this;
	}


	/**
	 * trim string with max specify
	 *
	 * @param string $title
	 * @param integer $max.
	 */
	function trimString($title, $maxchars = 60, $includeTags = NULL)
	{
		if (!empty($includeTags)) {
			$title = $this->trimIncludeTags($title, $this->buildStrTags($includeTags));
		}
		if (function_exists('mb_substr')) {
			$doc = JDocument::getInstance();
			return SmartTrim::mb_trim(($title), 0, $maxchars, $doc->_charset);
		} else {
			return SmartTrim::trim(($title), 0, $maxchars);
		}
	}


	/**
	 *
	 * Build Tags
	 * @param unknown_type $strTags
	 * @return string
	 */
	public function buildStrTags($strTags = "")
	{
		$strOut = "";
		if (!empty($strTags) && !is_array($strTags)) {
			$arrStr = explode(",", $strTags);
			if (!empty($arrStr)) {
				foreach ($arrStr as $key => $item) {
					$strOut .= "<" . $item . ">";
				}
			}
		} elseif (!empty($strTags) && is_array($strTags)) {
			$strOut = implode(",", $strTags);
			$strOut = str_replace(",", "", $strOut);
		}
		return $strOut;
	}


	/**
	 *
	 * Clear space in tags
	 * @param string $strContent
	 * @param string $listTags
	 * @return string the stripped string.
	 */
	function trimIncludeTags($strContent, $listTags = "")
	{
		$strOut = strip_tags($strContent, $listTags);
		return $strOut;
	}


	/**
	 * detect and get link with each resource
	 *
	 * @param string $item
	 * @param bool $useRSS.
	 * @return string.
	 */
	function getLink($item)
	{
		return JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
	}


	/**
	 * get parameters from configuration string.
	 *
	 * @param string $string;
	 * @return array.
	 */
	function parseParams($string)
	{
		$string = html_entity_decode($string, ENT_QUOTES);
		$regex = "/\s*([^=\s]+)\s*=\s*('([^']*)'|\"([^\"]*)\"|([^\s]*))/";
		$params = null;
		if (preg_match_all($regex, $string, $matches)) {
			for ($i = 0; $i < count($matches[1]); $i++) {
				$key = $matches[1][$i];
				$value = $matches[3][$i] ? $matches[3][$i] : ($matches[4][$i] ? $matches[4][$i] : $matches[5][$i]);
				$params[$key] = $value;
			}
		}
		return $params;
	}


	/**
	 * parser a image in the content of article.
	 * @param object $row article
	 * @param object $params
	 * @return unknown
	 */
	function parseImages(&$row, $params)
	{
		$row->link = $this->getLink($row);
		$text = $row->introtext . $row->fulltext;
		$row->date = strtotime($row->modified) ? $row->created : $row->modified;
		$row->thumbnail = '';
		$row->mainImage = '';

		//check introimage va fullimage
		$images = json_decode($row->images);

		if ((isset($images->image_fulltext) and !empty($images->image_fulltext)) || (isset($images->image_intro) and !empty($images->image_intro))) {
			$row->mainImage = (isset($images->image_fulltext) and !empty($images->image_fulltext)) ? $images->image_fulltext : ((isset($images->image_intro) and !empty($images->image_intro)) ? $images->image_intro : "");
			$row->thumbnail = (isset($images->image_intro) and !empty($images->image_intro)) ? $images->image_intro : ((isset($images->image_fulltext) and !empty($images->image_fulltext)) ? $images->image_fulltext : "");
		} else {
			$data = $this->parseImageNew($text);
			if (!empty($data) && !empty($data["mainImage"])) {
				$row->mainImage = isset($data["mainImage"]) ? $data["mainImage"] : "";
				$row->thumbnail = isset($data["thumbnail"]) ? $data["thumbnail"] : $row->mainImage;
			} else {
				$data = $this->parserCustomTag($text);
				if (isset($data[1][0])) {
					$tmp = $this->parseParams($data[1][0]);
					$row->mainImage = isset($tmp['main']) ? $tmp['main'] : '';
					$row->thumbnail = isset($tmp['thumb']) ? $tmp['thumb'] : '';
				} else {
					$regex = "/\<img.+src\s*=\s*\"([^\"]*)\"[^\>]*\>/";
					preg_match($regex, $text, $matches);
					$images = (count($matches)) ? $matches : array();
					if (count($images)) {
						$row->mainImage = $images[1];
						$row->thumbnail = $images[1];
					}
				}
			}
		}
	}


	/**
	 * parser a image in the content of article.
	 * @param string $text
	 * @return array image
	 */
	function parseImageNew($text)
	{
		$data = array();
		$regex = "/\<img.\.*+class\s*=\s*\"jaslideshow-main\s*\".\.*+src\s*=\s*\"([^\"]*)\"[^\>]*\>/";
		$regex2 = "/\<img.\.*+class\s*=\s*\"jaslideshow-thumb\s*\".\.*+src\s*=\s*\"([^\"]*)\"[^\>]*\>/";
		preg_match($regex, $text, $matches);
		preg_match($regex2, $text, $matches2);
		$images = (count($matches)) ? $matches : array();
		$images2 = (count($matches2)) ? $matches2 : array();
		if (!empty($images)) {
			$data["mainImage"] = isset($images[1]) ? trim($images[1]) : "";
		}
		if (!empty($images2)) {
			$data["thumbnail"] = isset($images[1]) ? trim($images2[1]) : "";
		}
		return $data;
	}


	/**
	 *
	 * render image from image source.
	 * @param string $title
	 * @param string $image image path
	 * @param object $params
	 * @param int $width
	 * @param int $height
	 * @param string $attrs attributes of image
	 * @param boolean $returnURL
	 * @return string image path
	 */
	function renderImage($title, $image, $params, $width = 0, $height = 0, $attrs = '', $returnURL = false)
	{
		global $database, $_MAMBOTS, $current_charset;
		if ($image) {
			$title = strip_tags($title);
			$thumbnailMode = $params->get('source-articles-images-thumbnail_mode', 'crop');
			$aspect = $params->get('source-articles-images-thumbnail_mode-resize-use_ratio', '1');
			$aspect = $aspect == '1' ? true : false;
			$crop = $thumbnailMode == 'crop' ? true : false;
			$jaimage = JAImage::getInstance();
			if ($thumbnailMode != 'none' && $jaimage->sourceExited($image)) {
				$imageURL = $jaimage->resize($image, $width, $height, $crop, $aspect);
				if ($returnURL) {
					return $imageURL;
				}
				if ($imageURL == $image) {
					$width = $width ? "width=\"$width\"" : "";
					$height = $height ? "height=\"$height\"" : "";
					$image = "<img src=\"$imageURL\"   alt=\"{$title}\" title=\"{$title}\" $width $height $attrs />";
				} else {
					$image = "<img src=\"$imageURL\"  $attrs  alt=\"{$title}\" title=\"{$title}\" />";
				}
			} else {
				if ($returnURL) {
					return $image;
				}
				$width = "";
				$height = "";
				if ($params->get('source-articles-images-thumbnail_mode', 'crop') != 'none') {
					$width = $width ? "width=\"$width\"" : "";
					$height = $height ? "height=\"$height\"" : "";
				}
				$image = "<img $attrs src=\"$image\" alt=\"{$title}\" title=\"{$title}\" $width $height />";
			}
		} else {
			$image = '';
		}
		// clean up globals
		return $image;
	}

	/**
	 *
	 * Get all image from image source and render them
	 * @param object $params
	 * @return array image list
	 */
	function getListImages($params)
	{
		$folder = $params->get('folder', 'images/');
		$thumbWidth = $params->get('thumbWidth', 60);
		$thumbHeight = $params->get('thumbHeight', 60);
		$mainWidth = $params->get('mainWidth', 360);
		$mainHeight = $params->get('mainHeight', 240);

		$data = array();
		$jaGallery = trim($params->get('description', ''));
		if (!$this->isJson($jaGallery)) {
			//Parse old data format
			$items = $this->parseDescNew($jaGallery);
			//Parse description. Description in format: [desc img="imagename" url="link"]Description goes here[/desc]
			$orderby = $params->get('source-images-orderby', '0');
			$sort = $params->get('source-images-sort', '0');

			$images = $this->readDirectory($folder, $orderby, $sort);
			foreach ($images as $k => $img) {
				if(!isset($items[$img])){ // only display the images are showed in the module setting
					continue; 
				}
				$data['title'][] = '';
				if ($img) {
					$data['captionsArray'][] = (isset($items[$img]) && isset($items[$img]['caption'])) ? str_replace("'", "\'", $items[$img]['caption']) : '';
				}
				// URL of image proccess
				$url = JRoute::_((isset($items[$img]) && isset($items[$img]['url'])) ? $items[$img]['url'] : '');
				$id = (isset($items[$img]) && isset($items[$img]['id'])) ? $items[$img]['id'] : '';
				if ($id)
					$url = JRoute::_(ContentHelperRoute::getArticleRoute($id));
				$data['urls'][] = $url;
				// Target of URL
				$target = JRoute::_((isset($items[$img]) && isset($items[$img]['target'])) ? $items[$img]['target'] : '');
				$id = (isset($items[$img]) && isset($items[$img]['id'])) ? $items[$img]['id'] : '';
				if ($id)
					$target = JRoute::_(ContentHelperRoute::getArticleRoute($id));
				$data['targets'][] = $target;

				$data['thumbArray'][] = $this->renderImage('', $folder . '/' . $img, $params, $thumbWidth, $thumbHeight, '', true);
				$data['mainImageArray'][] = $this->renderImage('', $folder . '/' . $img, $params, $mainWidth, $mainHeight, '', true);

			}
		} else {
			$target = $params->get('open_target', 'parent');
			$items = $jaGallery ? json_decode($jaGallery) : '';
			
			if (!empty($items)) {
				//Sort images
				usort($items, array('ModJASlideshow3', 'sortImage2'));
				//Convert data type
				for ($i = 0; $i < count($items); $i++) {
					if(file_exists(JPATH_SITE.'/'.$folder.$items[$i]->image) && (!isset($items[$i]->show) || (isset($items[$i]->show) && $items[$i]->show == 'true'))){
						$data['title'][] = $items[$i]->title;
						$data['captionsArray'][] = $items[$i]->description;
						$data['urls'][] = $items[$i]->link;
						$data['targets'][] = $target;
						$data['thumbArray'][] = $this->renderImage('', $folder . $items[$i]->image, $params, $thumbWidth, $thumbHeight, '', true);
						$data['mainImageArray'][] = $this->renderImage('', $folder . $items[$i]->image, $params, $mainWidth, $mainHeight, '', true);
					}
				}
			}
		}

		return $data;
	}

	/*
	* Check data format for update data type from old version to json format
	* @string data string 
	* @return boolean
	*/
	function isJson($string)
	{
		return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
	}


	/**
	 *
	 * Get all image from resource
	 * @param strinh $folder folder path
	 * @param string $orderby
	 * @param string $sort
	 * @return array images
	 */
	function readDirectory($folder, $orderby, $sort)
	{
		$imagePath = JPATH_SITE . "/" . $folder;
		$imgFiles = JFolder::files($imagePath);
		$folderPath = $folder . '/';
		$imageFile = array();
		$i = 0;
		if (empty($imgFiles)) {
			$imgFiles = array();
		}
		foreach ($imgFiles as $file) {
			$i_f = $imagePath . '/' . $file;
			if (preg_match("/bmp|gif|jpg|png|jpeg/", $file) && is_file($i_f)) {
				$imageFile[$i][0] = $file;
				$imageFile[$i][1] = filemtime($i_f);
				$i++;
			}
		}

		$images = $this->sortImage($imageFile, $orderby, $sort);
		return $images;
	}


	/**
	 *
	 * Sort images
	 * @param array $image
	 * @param string $orderby
	 * @param string $sort
	 * @return array image that is sorted
	 */
	function sortImage($image, $orderby, $sort)
	{
		$sortObj = array();
		$imageName = array();
		if ($orderby == 1) {
			for ($i = 0; $i < count($image); $i++) {
				$sortObj[$i] = $image[$i][1];
				$imageName[$i] = $image[$i][0];
			}
		} else {
			for ($i = 0; $i < count($image); $i++) {
				$sortObj[$i] = $image[$i][0];
			}
			$imageName = $sortObj;
		}
		if ($sort == 1)
			array_multisort($sortObj, SORT_ASC, $imageName);
		elseif ($sort == 2)
			array_multisort($sortObj, SORT_DESC, $imageName);
		else
			shuffle($imageName);
		return $imageName;
	}

	/*
	* Sort images
	*/
	public function sortImage2($img1, $img2)
	{
		//Get module params
		$params = $this->_params;
		
		//0 : Name , 1 : Time
		$orderby = $params->get('source-images-orderby', 0);
		//0 : RANDOM , 1 : ASC , 2 : DESC
		$orderby_type = $params->get('source-images-sort', 0);
		
		if(!$orderby_type) {
			return rand(0, 1);
		}

		if ($orderby == 1) {
			//Order by Time
			if ($orderby_type == 1) {
				return filemtime(str_replace(JUri::base(), '', $img1->imageSrc)) > filemtime(str_replace(JUri::base(), '', $img2->imageSrc));
			} elseif ($orderby_type == 2) {
				return filemtime(str_replace(JUri::base(), '', $img2->imageSrc)) < filemtime(str_replace(JUri::base(), '', $img1->imageSrc));
			}
		} else {
			//Order by Name
			if ($orderby_type == 1) {
				return strcmp($img1->image, $img2->image);
			} elseif ($orderby_type == 2) {
				return strcmp($img2->image, $img1->image);
			}
		}
	}

	/**
	 *
	 * Get file path
	 * @param string $name name of file
	 * @param string $modPath path of module
	 * @param string $tmplPath path of template
	 * @return string path of file
	 */
	function getFile($name, $modPath, $tmplPath = '')
	{
		if (!$tmplPath) {
			$mainframe = JFactory::getApplication();
			$tmplPath = 'templates/' . $mainframe->getTemplate() . '/css/';
		}
		if (JFile::exists(JPATH_SITE . '/' . $tmplPath . $name)) {
			return $tmplPath . $name;
		}
		return $modPath . $name;
	}

	/**
	 *
	 * Parse description
	 * @param string $description
	 * @return array
	 */
	function parseDescNew($description)
	{

		$regex = '#\[desc ([^\]]*)\]([^\[]*)\[/desc\]#m';
		$description = str_replace(array("{{", "}}"), array("<", ">"), $description);
		preg_match_all($regex, $description, $matches, PREG_SET_ORDER);

		$items = array();
		foreach ($matches as $match) {
			$params = $this->parseParams($match[1]);
			if (is_array($params)) {
				$img = isset($params['img']) ? trim($params['img']) : '';
				if (!$img)
					continue;
				$url = isset($params['url']) ? trim($params['url']) : '';
				$target = isset($params['target']) ? trim($params['target']) : '';
				$items[$img] = array('url' => $url, 'caption' => str_replace("\n", "<br />", trim($match[2])), 'target' => $target);
			}
		}

		return $items;
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
<?php
/**
 *------------------------------------------------------------------------------.
 *
 * @copyright     Copyright (C) 2004-2021 JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github
 *                & Google group to become co-author)
 *------------------------------------------------------------------------------
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Layout\LayoutHelper;


require_once(dirname(__FILE__) . '/helpers/jaimage.php');

// fix for j5.0.0-beta3
$jversion = JVERSION;
if (strpos('-beta', JVERSION) != 0){
	$jversion = explode('-beta', JVERSION)[0];
}

if (version_compare(JVERSION, 4, 'ge')) {
    \JLoader::registerAlias('ContentHelperRoute', 'Joomla\\Component\\Content\\Site\\Helper\\RouteHelper');
} else {
    \JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
}
/**
 * JA Content Listing Module Helper.
 *
 * @since 		1.6
 */
class ModJacontentlistingHelper
{
	/*
	* @var string module name
	*/
	public $_module = null;

    public $params;
    public $item;
    public $print;

	/*
	* @var object parametters of module
	*/
	public $_params = null;
	/*
	* @var array categories
	*/
	public $_categories = [];
	/*
	* @var object section
	*/
	public $_section = null;
	/*
	* @var array articles
	*/
	public $articles = [];
	/*
	* @var string category link
	*/
	public $cat_link = null;
	/*
	* @var string category title
	*/
	public $cat_title = null;
	/*
	* @var string category desc
	*/
	public $cat_desc = null;
	/*
	* @var array categories
	*/
	public $_categories_org = [];
	/*
	* @var int module id
	*/
	public $moduleid = 0;
	/*
	* @var array theme list
	*/
	public $_themes = [];
	/*
	* @var int total hits of article
	*/
	public $_totalHits = 0;

	public $resize_img = 0;
	/* get type layout */

	protected $type = '';
	/**
	 * Callback for escaping.
	 *
	 * @var string
	 *
	 * @deprecated 13.3
	 */
	protected $_escape = 'htmlspecialchars';

	/**
	 * Charset to use in escaping mechanisms; defaults to urf8 (UTF-8).
	 *
	 * @var string
	 */
	protected $_charset = 'UTF-8';

	/**
	 * Constructor.
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $params The object config of plugin
	 */
	public function __construct($params = null)
	{
		if ($params->get('jasource', '')) {
			$params->set('jasource', new Registry($params->get('jasource', '')));
		}
		if ($params->get('jalayout', '')) {
			$params->set('jalayout', new Registry($params->get('jalayout', '')));
		}
		if ($params->get('jaitem', '')) {
			$params->set('jaitem', new Registry($params->get('jaitem', '')));
		}
		if ($params->get('jadetail', '')) {
			$params->set('jadetail', new Registry($params->get('jadetail', '')));
		}
		if ($params->get('jaitem_featured', '')) {
			$params->set('jaitem_featured', new Registry($params->get('jaitem_featured', '')));
		}
		if (!empty($params->get('jasource'))) {
			$this->resize_img = $params->get('jasource')->get('resize_img', 0);
		}

		$this->params = $params;
	}

	/**
	 * (non-PHPdoc).
	 *
	 * @see JObject::get()
	 * method get param of ja JA Content Listing module
	 *
	 * @param string  $name    name of param
	 * @param observe $default default value of param
	 *
	 * @return observe value of param
	 */
	public function get($name, $default = null, $group = null)
	{
		if ($group) {
			$groupdata = $this->params->get($group);

			return $groupdata ? $groupdata->get($name, $default) : $default;
		}
		return $this->params->get($name, $default);
	}

	/**
	 * (non-PHPdoc).
	 *
	 * @see JObject::get()
	 * method get param of ja JA Content Listing module
	 *
	 * @param string  $name    name of param
	 * @param observe $default default value of param
	 *
	 * @return observe value of param
	 */
	public function set($name, $default = null, $group = null)
	{
		if ($group) {
			$groupdata = $this->params->get($group);
			return $groupdata ? $groupdata->set($name, $default) : $default;
		}
		return $this->params->set($name, $default);

	}

	public static function find($which, $get_url = false)
	{
		$template = Factory::getApplication()->getTemplate();

		// Build the template and base path for the layout
		$paths = [];
		// for T3: local folder
		$paths[Uri::base(true) . '/templates/' . $template . '/local/html/mod_jacontentlisting/'] = JPATH_THEMES . '/' . $template . '/local/html/mod_jacontentlisting/';
		// config template
		$paths[Uri::base(true) . '/templates/' . $template . '/html/mod_jacontentlisting/'] = JPATH_THEMES . '/' . $template . '/html/mod_jacontentlisting/';
		// current template
		$paths[Uri::base(true) . '/templates/' . $template . '/html/mod_jacontentlisting/'] = JPATH_THEMES . '/' . $template . '/html/mod_jacontentlisting/';
		// in module
		$paths[Uri::base(true) . '/modules/mod_jacontentlisting/tmpl/'] = JPATH_BASE . '/modules/mod_jacontentlisting/tmpl/';

		foreach ($paths as $uri => $path) {
			if (is_file($path . $which)) {
				return ($get_url ? $uri : $path) . $which;
			}
		}

		return null;
	}

	public static function addAssets($path)
	{
		$doc = Factory::getDocument();
		$direction = $doc->direction;
		HTMLHelper::_('jquery.framework');
		$tplActive = Factory::getApplication()->getTemplate();
		if (file_exists(JPATH_ROOT . '/modules/mod_jacontentlisting/assets/style.css')) {
			$doc->addStyleSheet(Uri::root(true) . '/modules/mod_jacontentlisting/assets/style.css');
		}
		if (file_exists(JPATH_THEMES . '/' . $tplActive . '/css/mod_jacontentlisting.css')) {
			$doc->addStyleSheet('templates/' . $tplActive . '/css/mod_jacontentlisting.css');
		}

		// assets for RTL
		if (file_exists(JPATH_ROOT . '/modules/mod_jacontentlisting/assets/rtl.css') && ($direction == 'rtl')) {
			$doc->addStyleSheet(Uri::root(true) . '/modules/mod_jacontentlisting/assets/rtl.css');
		}

		// assets for $path
		$css = self::find($path . '/style.css', true);
		$cssrtl = self::find($path . '/style_rtl.css', true);

		if ($cssrtl && ($direction == 'rtl')) {
			$doc->addStyleSheet($cssrtl);
		} else {
			$doc->addStyleSheet($css);
		}

		$js = self::find($path . '/script.js', true);
		if ($js) {
			$doc->addScript($js);
		}
	}

	public static function loadlayoutAjax()
	{
		$html = '';
		$input = Factory::getApplication()->input;
		$data = json_decode(file_get_contents('php://input'), true);
		$typeName = $data['type'];
		$type = $data['jatype'];
		switch ($type) {
			case 'jalayout':
				$jatype = 'layouts';
				break;
			case 'jaitem':
			case 'item_featured':
				$jatype = 'items';
				break;
			case 'jadetail':
				$jatype = 'details';
				break;

			default:
				$jatype = '';
				break;
		}
		$layoutConfig = self::find($jatype . '/' . $typeName . '/info.xml');
		$options = ['control' => 'jaform'];
		if ($type == 'item_featured') {
			$options = ['control' => 'jftform'];
		}

		if ($layoutConfig) {
			$data['form'] = JForm::getInstance('jaext-' . $type, $layoutConfig, $options);
			$html = LayoutHelper::render('tmpl.jafield', $data, JPATH_ROOT . '/modules' . '/mod_jacontentlisting' . '/admin');
		}

		return $html;
	}

	public static function loadsourceAjax()
	{
		$html = '';
		$input = Factory::getApplication()->input;
		$data = json_decode(file_get_contents('php://input'), true);
		$typeName = $data['type'];
		$type = $data['jatype'];
		$layoutConfig = JPATH_BASE . '/modules/mod_jacontentlisting/helpers/adapter/' . $typeName . '.xml';
		$options = ['control' => 'jaform'];

		if (file_exists($layoutConfig)) {
			$data['form'] = JForm::getInstance('jform-' . $type, $layoutConfig, $options);
			$html = LayoutHelper::render('tmpl.jafield', $data, JPATH_ROOT . '/modules' . '/mod_jacontentlisting' . '/admin');
		}
		return $html;
	}

	public static function loaditemsettingAjax()
	{
		$html = '';
	}

	public static function _cleanIntrotext($introtext)
	{
		$introtext = str_replace(['<p>', '</p>'], ' ', $introtext);
		$introtext = strip_tags($introtext, '<a><em><strong>');
		$introtext = trim($introtext);

		return $introtext;
	}

	/**
	 * Method to truncate introtext.
	 *
	 * The goal is to get the proper length plain text string with as much of
	 * the html intact as possible with all tags properly closed.
	 *
	 * @param string $html      The content of the introtext to be truncated
	 * @param int    $maxLength The maximum number of characters to render
	 *
	 * @return string The truncated string
	 *
	 * @since   1.6
	 */
	public static function truncate($html, $maxLength = 0)
	{
		$baseLength = strlen($html);
		$str_words = str_word_count($html);
		// First get the plain text string. This is the rendered text we want to end up with.
		$ptString = HTMLHelper::_('string.truncate', $html, $maxLength, $noSplit = true, $allowHtml = false);
		$splitCharacter = str_split($ptString);

		for ($maxLength; $maxLength < $baseLength;) {
			// Now get the string if we allow html.
			$htmlString = HTMLHelper::_('string.truncate', $html, $maxLength, $noSplit = true, $allowHtml = true);
			// Now get the plain text from the html string.
			$htmlStringToPtString = HTMLHelper::_('string.truncate', $htmlString, $maxLength, $noSplit = true, $allowHtml = false);

			// If the new plain text string matches the original plain text string we are done.
			if ($ptString === $htmlStringToPtString) {
				$htmlString = str_replace('...', "<span class='more_text'>...</span>", strip_tags($htmlString));

				return "<p>$htmlString</p>";
			}

			// Get the number of html tag characters in the first $maxlength characters
			$diffLength = strlen($ptString) - strlen($htmlStringToPtString);

			// Set new $maxlength that adjusts for the html tags
			$maxLength += $diffLength;
			if ($baseLength <= $maxLength || $diffLength <= 0) {
				$htmlString = str_replace('...', "<span class='more_text'>...</span>", strip_tags($htmlString));

				return "<p>$htmlString</p>";
			}
		}

		return $html;
	}

	public function escape($var)
	{
		if (in_array($this->_escape, ['htmlspecialchars', 'htmlentities'])) {
			return call_user_func($this->_escape, $var, ENT_COMPAT, $this->_charset);
		}
		return call_user_func($this->_escape, $var);
	}

	// render
	public function render($path, $options = null, $data = null)
	{
		// find layout
		if (preg_match('/\.php$/', $path)) {
			$layout_path = self::find($path);
		} else {
			$layout_path = self::find($path . '/index.php');
			// add module assets
			self::addAssets($path);
		}

		if (!$layout_path) {
			return;
		}

		// render
		// echo LayoutHelper::render($layout_path, ['data' => $data, 'options' => $options, 'helper' => $this]);
		$displayData = ['data' => $data, 'options' => $options, 'helper' => $this];
		ob_start();
		include $layout_path;
		$layoutOutput = '';
		$layoutOutput .= ob_get_contents();
		ob_end_clean();

		echo $layoutOutput;
	}

	public function renderItem($item, $groupname = 'jaitem')
	{
		if ($this->params->get('jasource') !== null) {
			$source = $this->params->get('jasource')->get('sources');
		} else {
			$source = 'content';
		}
		$layoutName = $this->get('layout', 'default', $groupname);
		switch ($source) {
			case 'eshop':
			case 'hikashop':
			case 'jshopping':
			case 'docman':
			case 'vm':
				$path = 'items/ecommerces/' . $layoutName;
				break;
			case 'content':
			case 'k2':
			case 'easyblog':
				$path = 'items/contents/' . $layoutName;
				break;
			default:
				$path = 'items/' . $layoutName;
				break;
		}
		$options = $this->get($groupname);
		$this->render($path, $options, $item);
	}

	public function renderDetail($item, $groupname = 'jadetail')
	{
		$layoutName = $this->get('layout', 'default', $groupname);
		$options = $this->params;
		$this->render('details/' . $layoutName, $options, $item);
	}

	public function renderLayout($items)
	{
		if (empty($items)) {
			echo Text::_('MOD_JACONTENTLISTING_NO_ARTICLE_ON_CAT');
			return true;
		}
		$options = $this->get('jalayout');
		$layoutName = $this->get('layout', 'default', 'jalayout');
		$heading_desc = $this->get('heading_desc', '', "jalayout");
		if ($heading_desc) {
			$this->set('heading_desc', $this->JaConvertString($heading_desc), "jalayout");
		}
		// handle resize img
		if ((bool) $this->resize_img &&
				file_exists(JPATH_ROOT . '/modules/mod_jacontentlisting/admin/assets/js/ajax_load_img_size.js')) {
			$doc = Factory::getDocument();
			$mod_id = $this->params->get('module_id');
			$debugMode = defined('JDEBUG') && (bool) JDEBUG;
			$doc->addScript(Uri::root(true) . '/modules/mod_jacontentlisting/admin/assets/js/ajax_load_img_size.js');
			$doc->addScriptDeclaration(";handleImgAjax({mod_id: \"$mod_id\", do_resize: {$this->resize_img}, debug: \"{$debugMode}\"});");
		}
		$this->render('layouts/' . $layoutName, $options, $items);
	}

	public function getColClass($prefix, $numcols)
	{
		if ($numcols) {
			if (12 % $numcols == 0) {
				return $prefix . floor(12 / $numcols);
			} else {
				return $prefix . $numcols . 'c';
			}
		}

		return '';
	}

	public function getColClasses($options)
	{
		$item_per_row = intval($options->get('item_per_row', 3));
		$item_per_row_md = intval($options->get('item_per_row_md', 0));
		$item_per_row_lg = intval($options->get('item_per_row_lg', 0));

		$col_class = 'jacl-col-12';
		$col_class .= ' ' . $this->getColClass('jacl-col-sm-', $item_per_row);
		$col_class .= ' ' . $this->getColClass('jacl-col-md-', $item_per_row_md);
		$col_class .= ' ' . $this->getColClass('jacl-col-lg-', $item_per_row_lg);

		return trim($col_class);
	}
	/**
	 * @item article Item get image 
	 *
	 * @param Object $params of item settings
	 *
	 * @return true
	 *
	 * @since  1.6
	 */
	public static function getFirstImageArticle($item, $context = 'introtext')
	{
		$imgConf = !empty($item->images) ? json_decode($item->images) : new \stdClass;
		preg_match('/<img.*?src=["\']+(.*?)["\']+/i', $item->$context, $src_context);
		
		$src_content = '';
		if (!empty($item->content)){
			preg_match('/<img.*?src=["\']+(.*?)["\']+/i', $item->content, $src_content);
		}
		$src = !empty($src_context) ? $src_context : $src_content;

		if(!empty($src[1])){
			$imgConf->image_intro = $src[1];
			if (!is_file(JPATH_ROOT . '/'. $src[1])){
				$destination = JPATH_ROOT .'/'.self::get_img_path($src[1]);
				// crawl other image sources for resize function
				if (!is_file($destination)){
					self::crawl_img($src[1], $destination);
				}
			}
		}else{
			$imgConf->image_intro = "";
		}
		$item->images = json_encode($imgConf);
		return  $item->images;
	}


	public function renderCategory($catid = null)
	{
		$options = new \stdClass;
		if (!$catid) $catid = $this->get('show_cat_parent', '', 'jalayout');
		$show_cat_highlight = $this->get('show_cat_highlight', 0, 'jalayout');
		if ($show_cat_highlight == 0) return "";
		$item = $this->getCategory($catid);
		$this->render('elements/list_cat.php', $options, $item);
	}

	public function getCategory($catid)
	{
		$source = $this->get('sources', 'default', 'jasource');
		$data = '';
		$source = 'content';
		switch ($source) {
			case "content":
				$data = $this->getContentCat($catid);
				break;
			case "k2":
				// code...
				break;
			case "easyblog":
				// code...
				break;
			default:
				break;
		}

		return $data;
	}

	protected function getContentCat($catid = '')
	{
		if (!$catid) return null;
		$db = Factory::getDbo();
		$primary = $db->getQuery(true)
			->select('a.id AS id, a.title AS title, a.level')
			->from('#__categories AS a')
			->where('a.extension = ' . $db->quote('com_content'))
			->where(' a.id = ' . $catid);
		$primary_cat = $db->setQuery($primary)->loadObjectList();

		$query = $db->getQuery(true)
			->select('a.id AS id, a.title AS title, a.level')
			->from('#__categories AS a')
			->where('a.extension = ' . $db->quote('com_content'))
			->where(' a.parent_id = ' . $catid);
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$data = (object) array_merge((array) $primary_cat, (array) $rows);
		foreach ($data as $row) {
			$row->cat_link = Route::_(ContentHelperRoute::getCategoryRoute($row->id));
		}
		return $data;
	}
	
	public function JaConvertString($str)
	{
		$str = str_replace("JAamp;", "&", $str);
		$str = str_replace("JAlt;", "<", $str);
		$str = str_replace("JAgt;", ">", $str);
		$str = str_replace("JAquot;", "\"", $str);
		$str = str_replace("JA#039;", "'", $str);
		return $str;
	}

	public static function renderImages(&$row, $params, $width=0, $height=0, $maxchars=0)
	{
		$image = self::parseImage($row, 'content');
		if (!empty($image)){
			$img_helper = JAImage::getInstance();

			$thumbnailMode = $params->get('thumbnailMode', 'crop');
			$aspect = $params->get('thumbnail_mode_resize_ratio', '1');
			$crop = $thumbnailMode === 'crop' ? 1 : 0;

			if ($thumbnailMode !== 'none' && $img_helper->sourceExisted($image)){
				$img_url = $img_helper->resize($image, $width, $height, $crop, $aspect);
				if ($img_url === $image){
					$width = $width ? "width=\"$width\"" : '';
					$height = $height ? "height=\"$height\"" : '';
					$image = "<img $width $height src=\"$img_url\" alt=\"{$row->title}\" title=\"{$row->title}\" />";
				}else{
					$image = "<img src=\"$img_url\" alt=\"{$row->title}\" title=\"{$row->title}\" />";
				}
			}else{
				$width = $width ? "width=\"$width\"" : '';
				$height = $height ? "height=\"$height\"" : '';
				$image = "<img $width $height src=\"$image\" alt=\"{$row->title}\" title=\"{$row->title}\" />";
			}
		}else{
			$image = '';
		}
		$regex1 = "/\<img.*\/\>/";
		$row->introtext = trim(preg_replace($regex1, '', $row->introtext));
		return $image;
	}

	public static function resizeImgAjax()
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$items = $input->get('jacl_items', '', 'RAW'); // == $_GET['jacl_items']
		$items = self::decrypt_encode($items);

		if (empty($items)){
			echo json_encode(['msg' => 'empty item', 'code' => 400]);
			die();
		}

		// start render images then save to /images/resized
		$all_img_resized = [];
		foreach($items as $k => $item){
			$item_ = (object) [
				'images' => (object) $item,
				'title' => $item['title'],
				'introtext' => ''
			];
			$img_resized = self::renderImages($item_, new Registry([]), $item['width'], $item['height']);
			$all_img_resized[$item['image_id']] = $img_resized;
		}
		echo json_encode($all_img_resized);
		die();
	}

	protected static function crawl_img($img_src, $destination=null){
		if (empty($destination)){
			$destination = JPATH_ROOT .'/'.self::get_img_path($img_src);
		}
		$ch = curl_init($img_src);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$image_data = curl_exec($ch);
		curl_close($ch);
		if (!empty($image_data)){
			File::write($destination, $image_data);
		}
	}

	public static function get_img_path($img_src) {
		$url = parse_url($img_src);
		$img_src_clean = $url['path'];
		$regex = '/\/images\/(.+)$/';
		preg_match($regex, $img_src_clean, $matches);
		return substr($matches[0], 1);
	}
	/**
	 * @item article Item get image
	 * 
	 */
	public static function parseImage($data, $context='content')
	{
		if ($context === 'k2') return $data;

		$images = "";

		if (isset($data->images)){
			$images = is_string($data->images) ? json_decode($data->images) : $data->images;
		}
		
		if (!empty($images->image_intro)){
			return $images->image_intro;
		}
		elseif(!empty($images->image_fulltext))
		{
			return $images->image_fulltext;
		}
		else
		{
			$regex = '/<img.+src\s*=\s*"([^"]*)"[^>]*>/';
			preg_match($regex, $data->introtext . $data->fulltext, $matches);
			$image = count($matches) > 1 ? $matches[1] : '';
			return $image;
		}
	}

	protected static function decrypt_aes_256($encrypted_data)
	{
		$decrypted_data = openssl_decrypt($encrypted_data, 'AES-256-CBC', 'secretKey', OPENSSL_RAW_DATA, 'initializationVector');
		$decode_data = json_decode($decrypted_data);
		return $decode_data;
	}

	protected static function decrypt_encode($data)
	{
		return json_decode(urldecode($data), true);
	}
}

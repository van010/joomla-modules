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
namespace JACL\Adapter;

use Joomla\CMS\Access\Access as JAccess;
use Joomla\CMS\Component\ComponentHelper as JComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel as JModelLegacy;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use Joomla\CMS\Router\Route as JRoute;
use Joomla\CMS\Filesystem\File;

defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.filesystem.file');

$engine = JPATH_ADMINISTRATOR . '/components/com_easyblog/includes/easyblog.php';

if (!File::exists($engine)) {
    return;
}

require_once($engine);

class EasyblogHelper
{
    public function __construct()
    {

    }

    /**
     * Get Articles.
     *
     * @param object $params
     *
     * @return object Article
     */
    public static function getList($params)
    {
        // Get the default sorting and ordering
        $sort = self::normalizeSorting($params->get('jasource')->get('sort_order', 'desc'));
        $order = self::normalizeOrdering($params->get('jasource')->get('ordering', 'latest'));
        $count = $params->get('count');
        switch ($params->get('jasource')->get('featured', 'show')) {
            case 'only':
                $featured = "featured";
                break;
            case 'hide':
                $featured = "latestOnly";
                break;
            
            default:
                $featured = "all";
                break;
        }
        // Get the total number of posts to display
        $limit = (int) trim($params->get('count', 5));

        // Determines if the user wants to filter items by specific ategories
        $categories = $params->get('jasource')->get('catsid', array(), 'array');
        $subCat = $params->get('jasource')->get('maxSubCats', -1);

        $includeAuthors = $params->get('inclusion_authors', array());
        $excludeAuthors = $params->get('exclusion_authors', array());
        $options = array($order,$sort);
        
        $model = \EB::model('Blog');
        // Determines if we should display featured or latest entries
        $type = $featured;

        $result = array();

        if ($categories && !is_array($categories)) {
            $categories = (int) $categories;
        }

        $excludeIds = array();

        // If type equal to latest only, we need to exclude featured post as well
        if ($type == 'latestOnly') {
            // Retrieve a list of featured blog posts on the site.
            $featured = $model->getFeaturedBlog();

            foreach ($featured as $item) {
                $excludeIds[] = $item->id;
            }
        }

        $inclusion = '';

        // Get a list of category inclusions
        $inclusion  = \EB::getCategoryInclusion($categories);


        // Include child category in the inclusions
        if ($subCat && !empty($inclusion)) {

            $tmpInclusion = array();

            foreach ($inclusion as $includeCatId) {

                // Retrieve nested categories
                $category = new \stdClass();
                $category->id = $includeCatId;
                $category->childs = null;

                \EB::buildNestedCategories($category->id, $category);

                $linkage = '';
                \EB::accessNestedCategories($category, $linkage, '0', '', 'link', ', ');

                $catIds = array();
                $catIds[] = $category->id;
                \EB::accessNestedCategoriesId($category, $catIds);

                $tmpInclusion = array_merge($tmpInclusion, $catIds);
            }

            $inclusion = $tmpInclusion;
        }

        // Let's get the post now
        if (($type == 'all' || $type == 'latestOnly')) {
            $result = $model->getBlogsBy('', '', $options, $count, EBLOG_FILTER_PUBLISHED, null, null, $excludeIds, false, false, false, array(), $inclusion, '', '', false, array(), array(), false, array(), array('paginationType' => 'none'));
        }

        // If not latest posttype, show featured post.
        if ($type == 'featured') {
            $result = $model->getFeaturedBlog($inclusion, $count);
        }
        // If there's nothing to show at all, don't display anything
        if (!$result) {
            return $result;
        }
        $posts = array();

        if (!$result) {
            return $posts;
        }

        $posts = \EB::formatter('list', $result);
        if(!empty($posts)){
            foreach ($posts as $k => $post) {
                $post->introtext = $post->intro;
                $post->images  = "";
                $post->category_title  =$post->category->title;
                $post->catid  =$post->category->id;
                $post->created_by_alias  = "";
                $authors = $post->author;
                $post->author = $authors->nickname;
                $post->authors = $authors;
                $post->link = $post->getPermalink();
                $post->displayCategoryLink = $post->category->getPermalink();
                // check config use image
                $image_params = $params->get('jaitem')->get('item_media_path','intro');
                
                // handle for featured item Thumbnail image if it sets differently Global Settings
                if ($params->get('jaitem_featured_enabled') !== 0 && $k === 0){
                    $image_params = $params->get('jaitem_featured')->get('item_media_path','intro');
                }
	            switch ($image_params) {
		            case "intro":
		            case "full":
			            $imagesConfig = $post->images ? json_decode($post->images) : new \stdClass;
			            if(strpos($post->image, 'post:') !== false){
				            $imagesConfig->image_intro = str_replace("post:", 'images/easyblog_articles/', $post->image);
			            }
                        if (strpos($post->image, 'user:') !== false){
                            $imagesConfig->image_intro = str_replace("user:", 'images/easyblog_images/', $post->image);
                        }
			            if (strpos($post->image, 'amazon:') !== false){
				            $imagesConfig->image_intro = str_replace("amazon:", 'images/easyblog_articles/', $post->image);
				            $imagesConfig->img_src = 'amazon';
			            }
			            $post->images = json_encode($imagesConfig);
			            break;
		
		            case "first_img":
			            $post->images = \ModJacontentlistingHelper::getFirstImageArticle($post);
			            break;
		            default:
			            break;
	            }
            }
        }
        return $posts;
    }

    /**
     * Fix ordering value that was reversed on 5.0
     *
     * @since   5.1
     * @access  public
     */
    public static function normalizeOrdering($ordering)
    {
        if ($ordering == 'asc' || $ordering == 'desc') {
            return 'created';
        }

        return $ordering;
    }

    /**
     * Fix sorting value that was reversed on 5.0
     *
     * @since   5.1
     * @access  public
     */
    public static function normalizeSorting($sorting)
    {
        if ($sorting == 'latest' || $sorting == 'alphabet' || $sorting == 'popular') {
            return 'desc';
        }

        return $sorting;
    }
    public static function getPosts(){

    }
    
}

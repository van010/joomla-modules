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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel as JModelLegacy;
use Joomla\CMS\Component\ComponentHelper as JComponentHelper;

// no direct access
defined('_JEXEC') or die('Restricted access');

$com_path = JPATH_SITE . '/components/com_content/';

if(!class_exists('ContentHelperRoute')){
	if(version_compare(JVERSION, '4', 'ge')){
		// abstract class ContentHelperRoute extends \Joomla\Component\Content\Site\Helper\RouteHelper{};
        \JLoader::registerAlias('ContentHelperRoute', 'Joomla\\Component\\Content\\Site\\Helper\\RouteHelper');
	}else{
		\JLoader::register('ContentHelperRoute', $com_path . '/helpers/route.php');
	}
}

if(!class_exists('FieldsHelper')){
    if(version_compare(JVERSION, '4', 'ge')){
        \JLoader::registerAlias('FieldsHelper', 'Joomla\\Component\\Fields\\Administrator\\Helper\\FieldsHelper');
	}else{
		\JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
	}
}

JModelLegacy::addIncludePath($com_path . 'models', 'ContentModel');

class ContentHelper
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
        $params = self::getItemListByLayout($params);
        // Get an instance of the generic articles model
        $articles = JModelLegacy::getInstance('Articles', 'ContentModel', ['ignore_request' => true]);
        if(empty($articles)) return;
        $jasourceParams = $params->get('jasource');
        // Set application parameters in model
        $app = Factory::getApplication();
        $input = $app->input;
        $appParams = $app->getParams();
        $articles->setState('params', $appParams);
        $articles->setState('list.start', $jasourceParams->get('limitstart', 0));
        $articles->setState('filter.published', 1);

        // Set the filters based on the module params
        $articles->setState('list.limit', (int) $params->get('count', 5));
        $articles->setState('load_tags', 1);

        // Access filter
        $access = !JComponentHelper::getParams('com_content')->get('show_noauth');
        $authorised = Access::getAuthorisedViewLevels(Factory::getUser()->get('id'));
        $articles->setState('filter.access', $access);

        $catids = $jasourceParams->get('catsid');
        $articles->setState('filter.category_id.include', 1);

        // Prep for Normal or Dynamic Modes
        $mode = $jasourceParams->get('mode', 'normal');

        switch($mode){
            case 'dynamic':
                $option = $input->get('option');
                $view = $input->get('view');
                if ($option === 'com_content'){
                    switch ($view){
                        case 'category':
                        case 'categories':
                            $catids = array($input->getInt('id'));
                            break;
                        case 'article':
                            if ($jasourceParams->get('show_on_article_page', 1)){
                                $article_id = $input->getInt('id');
                                $catid      = $input->getInt('catid');
                                if (!$catid){
                                    // Get an instance of the generic article model
                                    $article = JModelLegacy::getInstance('Article', 'ContentModel', ['ignore_request' => true]);
                                    $article->setState('params', $appParams);
                                    $article->setState('filter.published', 1);
                                    $article->setState('article.id', (int) $article_id);
                                    $item   = $article->getItem();
                                    $catids = array($item->catid);
                                }else{
                                    $catids = array($catid);
                                }
                            }else{
                                return;
                            }
                            break;
                        default:
                            break;
                    }
                }
                break;
            default:
                $catids = $jasourceParams->get('catsid');
                $articles->setState('filter.category_id.include', 1);
                break;
        }

        // Category filter
        if ($catids) {
            if ($jasourceParams->get('show_child_category_articles', 0) && (int) $jasourceParams->get('maxSubCats', 0) > 0) {
                // Get an instance of the generic categories model
                $categories = JModelLegacy::getInstance('Categories', 'ContentModel', ['ignore_request' => true]);
                $categories->setState('params', $appParams);
                $levels = $jasourceParams->get('maxSubCats', 1) ?: 9999;
                $categories->setState('filter.get_children', $levels);
                $categories->setState('filter.published', 1);
                $categories->setState('filter.access', $access);
                $additional_catids = [];

                foreach ($catids as $catid) {
                    $categories->setState('filter.parentId', $catid);
                    $recursive = true;
                    $items = $categories->getItems($recursive);

                    if ($items) {
                        foreach ($items as $category) {
                            $condition = (($category->level - $categories->getParent()->level) <= $levels);

                            if ($condition) {
                                $additional_catids[] = $category->id;
                            }
                        }
                    }
                }
                $catids = array_unique(array_merge($catids, $additional_catids));
            }
            $articles->setState('filter.category_id', $catids);
        }

        // Ordering
        $ordering = $jasourceParams->get('ordering', 'a.ordering');

        switch ($ordering) {
            case 'random':
                $articles->setState('list.ordering', Factory::getDbo()->getQuery(true)->Rand());
                break;

            case 'rating_count':
            case 'rating':
                $articles->setState('list.ordering', $ordering);
                $articles->setState('list.direction', $jasourceParams->get('sort_order', 'ASC'));

                if (!JPluginHelper::isEnabled('content', 'vote')) {
                    $articles->setState('list.ordering', 'a.ordering');
                }

                break;

            default:
                $articles->setState('list.ordering', $ordering);
                $articles->setState('list.direction', $jasourceParams->get('sort_order', 'ASC'));
                break;
        }

        // Filter by multiple tags
        $articles->setState('filter.tag', $params->get('filter_tag', array()));
        $articles->setState('filter.author_id', $params->get('created_by', array()));
        $articles->setState('filter.author_id.include', $params->get('author_filtering_type', 1));

        $articles->setState('filter.featured', $jasourceParams->get('featured', 'show'));
        // Filter by language
        $articles->setState('filter.language', $app->getLanguageFilter());

        $items = $articles->getItems();
        // Display options
        $show_date = $params->get('jaitem')->get('show_date', 0);
        $show_date_field = $params->get('jaitem')->get('show_date_field', 'created');
        $show_date_format = $params->get('jaitem')->get('show_date_format', 'Y-m-d H:i:s');
        $show_category = $params->get('jaitem')->get('show_category', 0);
        $show_hits = $params->get('jaitem')->get('show_hits', 0);
        $show_author = $params->get('jaitem')->get('show_author', 0);
        $show_introtext = $params->get('jaitem')->get('show_introtext', 0);
        $introtext_limit = $params->get('jaitem')->get('introtext_limit', 100);
        $feature_introtext_limit = $params->get('jaitem_featured_enabled',0) ? $params->get('jaitem_featured')->get('show_introtext', 0) : 0;

        // Find current Article ID if on an article page
        $option = $app->input->get('option');
        $view = $app->input->get('view');

        if ($option === 'com_content' && $view === 'article') {
            $active_article_id = $app->input->getInt('id');
        } else {
            $active_article_id = 0;
        }
        if(!empty($items)){
            // Prepare data for display using display options
            foreach ($items as &$item) {
                $item->slug = $item->id.':'.$item->alias;

                /* @deprecated Catslug is deprecated, use catid instead. 4.0 */
                $item->catslug = $item->catid.':'.$item->category_alias;
                if(!isset($item->category_language)) $item->category_language = "*";

                $item->link = Route::_(\ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
                /*if ($access || in_array($item->access, $authorised)) {
                    // We know that user has the privilege to view the article
                    $item->link = Route::_(\ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
                } else {
                    $menu = $app->getMenu();
                    $menuitems = $menu->getItems('link', 'index.php?option=com_users&view=login');

                    if (isset($menuitems[0])) {
                        $Itemid = $menuitems[0]->id;
                    } elseif ($app->input->getInt('Itemid') > 0) {
                        // Use Itemid from requesting page only if there is no existing menu
                        $Itemid = $app->input->getInt('Itemid');
                    }

                    $item->link = Route::_('index.php?option=com_users&view=login&Itemid='.$Itemid);
                }*/

                // Used for styling the active article
                $item->active = $item->id == $active_article_id ? 'active' : '';
                $item->displayDate = '';
                if ($show_date) {
                    $item->displayDate = HTMLHelper::_('date', $item->$show_date_field, $show_date_format);
                }
                // check config use image
                $image_params = $params->get('jaitem')->get('item_media_path','intro');
                switch ($image_params) {
                    case "full":
                        $imagesConfig = json_decode($item->images);
                        $imagesConfig->image_intro = $imagesConfig->image_fulltext;
                        $item->images = json_encode($imagesConfig);
                        break;
                    
                    case "first_img":
                        $item->images = \ModJacontentlistingHelper::getFirstImageArticle($item);
                        break;
                    default:
                        break;
                }
                if ($item->catid) {
                    $item->displayCategoryLink = Route::_(\ContentHelperRoute::getCategoryRoute($item->catid, $item->category_language));
                    $item->displayCategoryTitle = $show_category ? '<a href="'.$item->displayCategoryLink.'">'.$item->category_title.'</a>' : '';
                } else {
                    $item->displayCategoryTitle = $show_category ? $item->category_title : '';
                }
                $item->cat_attrib = self::getCategoryAttrib($item->catid);
                $item->displayHits = $show_hits ? $item->hits : '';
                $item->displayAuthorName = $show_author ? $item->author : '';

//                $item->introtext = HTMLHelper::_('content.prepare', $item->introtext, '', 'mod_jacontentlisting.content');
                $item->introtext = \ModJacontentlistingHelper::_cleanIntrotext($item->introtext);
                $item->displayIntrotext = str_replace("...", "", \ModJacontentlistingHelper::truncate($item->introtext, $introtext_limit));
                $item->displayReadmore = $item->alternative_readmore;
                $item->jcfields = \FieldsHelper::getFields('com_content.article', $item, true);
            }
        }

        $sort_by_hits = $jasourceParams->get('sort_by_hits', 0);
        $sort_hits_order = $jasourceParams->get('sort_hits_order', 'desc');
        if ($sort_by_hits) {
            return self::sortItemsByHits($items, $sort_hits_order);
        }

        return $items;
    }
    

    /*
     * Sort items by Hits
     *
     * @return object
     */
    public static function sortItemsByHits($items, $mode='desc')
    {
        usort($items, function ($a, $b) use ($mode){
            if ($mode !== 'desc') {
                return $a->hits - $b->hits;
            }
            return -$a->hits + $b->hits;
        });
        foreach ($items as $key => $item) {
            if ($mode === 'desc') {
                $items[$key]->rank = $key+1;
            } else {
                $items[$key]->rank = count($items)-$key;
            }
        }
        return $items;
    }

    public static function getItemListByLayout($params)
    {
        $layoutName = $params->get('jalayout')->get('layout');
        $layoutConfig = \ModJacontentlistingHelper::find("layouts/".$layoutName."/info.xml");
        $xml = simplexml_load_file($layoutConfig,'SimpleXMLElement',LIBXML_NOCDATA);
        $items = $xml->layout ? $xml->layout->items : 0;
        return $params;
    }

    /**
     * Get total hits of Article item.
     *
     * @return int
     */
    public function getTotalHits()
    {
        $db = \Factory::getDBO();

        $query = 'SELECT MAX(hits)'.' FROM #__content';
        $db->setQuery($query);

        return $db->loadResult();
    }

    public static function getCategoryAttrib($catid)
    {
        $categories = Categories::getInstance('Content', []);
        //get category by id
        $catData = $categories->get($catid);
        
        return json_decode($catData->get('params'));
    }
    
}

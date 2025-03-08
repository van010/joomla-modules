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

/*
 * $JA#COPYRIGHT$.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filesystem\File;


/**
 * Module JA K2 Helper.
 *
 * @since       1.6
 */


$com_path = JPATH_SITE.'/components/com_k2/';

\JLoader::register('K2HelperRoute', $com_path.'helpers/route.php');
\JLoader::register('K2HelperUtilities', $com_path.'helpers/utilities.php');

class K2Helper
{
    /**
     * Get data K2 item.
     *
     * @param object $helper object from JAHelperPro
     * @param object $params
     *
     * @return object $helper object include data of item K2
     */
    public static function getList($globalParams,$format= 'html')
    {
        $app = Factory::getApplication();
        $db = Factory::getDbo();
        $params = $globalParams->get('jasource');
        $jaitems = $globalParams->get('jaitem');
        $jnow = Factory::getDate();
        $now = (K2_JVERSION != '15') ? $jnow->toSql() : $jnow->toMySQL();
        $nullDate = $db->getNullDate();

        $componentParams = \JComponentHelper::getParams('com_k2');

        $limit = $globalParams->get('count', 10);
        $cid = $params->get('k2catsid', null);
        $ordering = $params->get('ordering', '');
        $limitstart = \JRequest::getInt('limitstart');
        // Display options
        $show_date = $globalParams->get('jaitem')->get('show_date', 0);
        $show_date_field = $globalParams->get('jaitem')->get('show_date_field', 'created');
        $show_date_format = $globalParams->get('jaitem')->get('show_date_format', 'Y-m-d H:i:s');
        $show_category = $globalParams->get('jaitem')->get('show_category', 0);
        $show_hits = $globalParams->get('jaitem')->get('show_hits', 0);
        $show_author = $globalParams->get('jaitem')->get('show_author', 0);

         // Get ACL
        $user = Factory::getUser();
        if (K2_JVERSION != '15') {
            $userLevels = array_unique($user->getAuthorisedViewLevels());
            $aclCheck = 'IN('.implode(',', $userLevels).')';
        } else {
            $aid = $user->get('aid');
            $aclCheck = '<= '.$user->get('aid');
        }

        $query = "SELECT i.*,";
        if($show_author){
            $query .= " u.userName as author, ";
        }
        if ($ordering == 'modified') {
            $query .= " CASE WHEN i.modified = 0 THEN i.created ELSE i.modified END AS lastChanged,";
        }

        $query .= " c.name AS category_title, c.id AS categoryid, c.alias AS categoryalias, c.params AS categoryparams";

        if ($ordering == 'best') {
            $query .= ", (r.rating_sum/r.rating_count) AS rating";
        }

        if ($ordering == 'comments') {
            $query .= ", COUNT(comments.id) AS numOfComments";
        }

        $query .= " FROM #__k2_items AS i RIGHT JOIN #__k2_categories AS c ON c.id = i.catid";

        if ($ordering == 'best') {
            $query .= " LEFT JOIN #__k2_rating AS r ON r.itemID = i.id";
        }

        if ($ordering == 'comments') {
            $query .= " LEFT JOIN #__k2_comments AS comments ON comments.itemID = i.id";
        }

        if ($show_author) {
            $query .= " LEFT JOIN #__k2_users AS u ON u.userID = i.created_by";
        }

        $tagsFilter = $params->get('tags');
        if ($tagsFilter && is_array($tagsFilter) && count($tagsFilter)) {
            $query .= " INNER JOIN #__k2_tags_xref tags_xref ON tags_xref.itemID = i.id";
        }
        $query .= " WHERE i.published = 1
            AND i.access {$aclCheck}
            AND i.trash = 0
            AND c.published = 1
            AND c.access {$aclCheck}
            AND c.trash = 0
            AND (i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now).")
            AND (i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now).")";
        if (!empty($cid[0])) {
            if ($params->get('getChildren')) {
                $itemListModel = \JModelLegacy::getInstance('Itemlist', 'K2Model');
                $categories = $itemListModel->getCategoryTree($cid);
                sort($categories);
                $sql = @implode(',', $categories);
                $query .= " AND i.catid IN ({$sql})";
            } else {
                if (is_array($cid)) {
                    sort($cid);
                    $query .= " AND i.catid IN(".implode(',', $cid).")";
                } else {
                    $query .= " AND i.catid = ".(int) $cid;
                }
            }
        }
        if ($params->get('featured') == 'hide') {
            $query .= " AND i.featured != 1";
        }

        if ($params->get('featured') == 'only') {
            $query .= " AND i.featured = 1";
        }

        if ($params->get('videosOnly')) {
            $query .= " AND (i.video IS NOT NULL AND i.video!='')";
        }

        if ($ordering == 'comments') {
            $query .= " AND comments.published = 1";
        }

        switch ($ordering) {

            case 'date':
                $orderby = 'i.created ASC';
                break;

            case 'rdate':
                $orderby = 'i.created DESC';
                break;

            case 'alpha':
                $orderby = 'i.title';
                break;

            case 'ralpha':
                $orderby = 'i.title DESC';
                break;

            case 'order':
                if ($params->get('featured') == 'only') {
                    $orderby = 'i.featured_ordering';
                } else {
                    $orderby = 'i.ordering';
                }
                break;

            case 'rorder':
                if ($params->get('featured') == 'only') {
                    $orderby = 'i.featured_ordering DESC';
                } else {
                    $orderby = 'i.ordering DESC';
                }
                break;

            case 'hits':
                if ($params->get('popularityRange')) {
                    if ($params->get('popularityRange') == 'today') {
                        $date = $jnow->toFormat('%Y-%m-%d').' 00:00:00';
                        $query .= " AND i.publish_up > '{$date}'";
                    } else {
                        $query .= " AND i.created > DATE_SUB('{$now}', INTERVAL ".$params->get('popularityRange')." DAY)";
                    }
                }
                $orderby = 'i.hits DESC';
                break;

            case 'rand':
                $orderby = 'RAND()';
                break;

            case 'best':
                $orderby = 'rating DESC';
                break;

            case 'comments':
                if ($params->get('popularityRange')) {
                    $query .= " AND i.created > DATE_SUB('{$now}', INTERVAL ".$params->get('popularityRange')." DAY)";
                }
                $orderby = 'numOfComments DESC';
                break;

            case 'modified':
                $orderby = 'lastChanged DESC';
                break;

            case 'publishUp':
                $orderby = 'i.publish_up DESC';
                break;

            default:
                $orderby = 'i.id DESC';
                break;
        }

        if ($tagsFilter && is_array($tagsFilter) && count($tagsFilter)) {
            $query .= ' GROUP BY i.id';
        }

        $query .= ' ORDER BY '.$orderby;
        $db->setQuery($query, 0, $limit);
        $items = $db->loadObjectList();
        // Render the query results
        $model = \JModelLegacy::getInstance('Item', 'K2Model');

        // Import plugins
        if ($params->get('JPlugins', 1)) {
            \JPluginHelper::importPlugin('content');
        }
        if ($params->get('K2Plugins', 1)) {
            \JPluginHelper::importPlugin('k2');
        }
        $dispatcher = \JDispatcher::getInstance();

        if (count($items)) {
            foreach ($items as $item) {

                // Item (read more...) link
                $item->link = urldecode(Route::_(\K2HelperRoute::getItemRoute($item->id.':'.urlencode($item->alias), $item->catid.':'.urlencode($item->categoryalias))));

                // Category link
                if ($params->get('itemCategory')) {
                    $item->categoryLink = urldecode(Route::_(\K2HelperRoute::getCategoryRoute($item->catid.':'.urlencode($item->categoryalias))));
                }

                // Title cleanup
                $item->title = \JFilterOutput::ampReplace($item->title);

                // Tags
                if ($params->get('itemTags')) {
                    $tags = $model->getItemTags($item->id);
                    for ($i = 0; $i < count($tags); $i++) {
                        $tags[$i]->link = Route::_(\K2HelperRoute::getTagRoute($tags[$i]->name));
                    }
                    $item->tags = $tags;
                }

                // Introtext
                $item->text = '';
                if ($jaitems->get('show_introtext')) {
                    // Word limit
                    if ($jaitems->get('introtext_limit')) {
                        $item->text .= \K2HelperUtilities::wordLimit($item->fulltext, $jaitems->get('introtext_limit',100));
                    }
                }
                // Item image
                if ($params->get('itemImage',1)) {
                    $images = new \JObject();
                    $images->imageXSmall = '';
                    $images->imageSmall = '';
                    $images->imageMedium = '';
                    $images->imageLarge = '';
                    $images->imageXLarge = '';

                    $imageTimestamp = '';
                    $dateModified = ((int) $item->modified) ? $item->modified : '';
                    if ($componentParams->get('imageTimestamp', 1) && $dateModified) {
                        $imageTimestamp = '?t='.strftime("%Y%m%d_%H%M%S", strtotime($dateModified));
                    }

                    $imageFilenamePrefix = md5("Image".$item->id);
                    $imagePathPrefix = 'media/k2/items/cache/'.$imageFilenamePrefix;

                    // Check if the "generic" variant exists
                    if (File::exists(JPATH_SITE.'/media/k2/items/cache/'.$imageFilenamePrefix.'_Generic.jpg')) {
                        $images->imageGeneric = $imagePathPrefix.'_Generic.jpg'.$imageTimestamp;
                        $images->imageXSmall  = $imagePathPrefix.'_XS.jpg'.$imageTimestamp;
                        $images->imageSmall   = $imagePathPrefix.'_S.jpg'.$imageTimestamp;
                        $images->imageMedium  = $imagePathPrefix.'_M.jpg'.$imageTimestamp;
                        $images->imageLarge   = $imagePathPrefix.'_L.jpg'.$imageTimestamp;
                        $images->imageXLarge  = $imagePathPrefix.'_XL.jpg'.$imageTimestamp;

                        $images->imageProperties = new \stdClass;
                        $images->imageProperties->filenamePrefix = $imageFilenamePrefix;
                        $images->imageProperties->pathPrefix = $imagePathPrefix;
                    }

                    // Select the size to use
                    $image = 'image'.$params->get('itemImgSize', 'Large');
                    if (!empty($images->$image)) {
                        $images->image_intro = $images->$image;
                    } else {
                        $images->image_intro = null;
                    }
                    $item->images = json_encode($images);
                }
                // check config use image
                $image_params = $jaitems->get('item_media_path','intro');
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
                // Video
                if ($params->get('itemVideo') && $format != 'feed') {
                    $params->set('vfolder', 'media/k2/videos');
                    $params->set('afolder', 'media/k2/audio');

                    // Create temp object to parse plugins
                    $mediaTempText = new JObject();
                    $mediaTempText->text = $item->video;
                    if ($params->get('JPlugins', 1)) {
                        if (K2_JVERSION == '15') {
                            $dispatcher->trigger('onPrepareContent', array(
                            &$mediaTempText,
                            &$params,
                            $limitstart
                        ));
                        } else {
                            $dispatcher->trigger('onContentPrepare', array(
                            'mod_k2_content.item-media',
                            &$mediaTempText,
                            &$params,
                            $limitstart
                        ));
                        }
                    }
                    if ($params->get('K2Plugins', 1)) {
                        $dispatcher->trigger('onK2PrepareContent', array(
                        &$mediaTempText,
                        &$params,
                        $limitstart
                    ));
                    }
                    $item->video = $mediaTempText->text;
                }

                // Extra fields
                if ($params->get('itemExtraFields')) {
                    $item->extra_fields = $model->getItemExtraFields($item->extra_fields, $item);

                    // Plugin rendering in extra fields
                    if (is_array($item->extra_fields)) {
                        foreach ($item->extra_fields as $key => $extraField) {
                            if ($extraField->type == 'textarea' || $extraField->type == 'textfield') {
                                // Create temp object to parse plugins
                                $extraFieldTempText = new JObject();
                                $extraFieldTempText->text = $extraField->value;
                                if ($params->get('JPlugins', 1)) {
                                    if (K2_JVERSION == '15') {
                                        $dispatcher->trigger('onPrepareContent', array(
                                            &$extraFieldTempText,
                                            &$params,
                                            $limitstart
                                        ));
                                    } else {
                                        $dispatcher->trigger('onContentPrepare', array(
                                            'mod_k2_content.item-extrafields',
                                            &$extraFieldTempText,
                                            &$params,
                                            $limitstart
                                        ));
                                    }
                                }
                                if ($params->get('K2Plugins', 1)) {
                                    $dispatcher->trigger('onK2PrepareContent', array(
                                        &$extraFieldTempText,
                                        &$params,
                                        $limitstart
                                    ));
                                }
                                $extraField->value = $extraFieldTempText->text;
                            }
                        }
                    }
                }

                // Attachments
                if ($params->get('itemAttachments')) {
                    $item->attachments = $model->getItemAttachments($item->id);
                }

                // Comments counter
                if ($params->get('itemCommentsCounter')) {
                    $item->numOfComments = $model->countItemComments($item->id);
                }

                // Plugins
                if ($format != 'feed') {
                    $params->set('parsedInModule', 1); // for plugins to know when they are parsed inside this module

                    $item->event = new \stdClass;

                    $item->event->BeforeDisplay = '';
                    $item->event->AfterDisplay = '';
                    $item->event->AfterDisplayTitle = '';
                    $item->event->BeforeDisplayContent = '';
                    $item->event->AfterDisplayContent = '';

                    // Joomla Plugins
                    if ($params->get('JPlugins', 1)) {
                        if (K2_JVERSION != '15') {
                            $item->event->BeforeDisplay = '';
                            $item->event->AfterDisplay = '';

                            $results = $dispatcher->trigger('onContentAfterTitle', array('mod_k2_content', &$item, &$params, $limitstart));
                            $item->event->AfterDisplayTitle = trim(implode("\n", $results));

                            $results = $dispatcher->trigger('onContentBeforeDisplay', array('mod_k2_content', &$item, &$params, $limitstart));
                            $item->event->BeforeDisplayContent = trim(implode("\n", $results));

                            $results = $dispatcher->trigger('onContentAfterDisplay', array('mod_k2_content', &$item, &$params, $limitstart));
                            $item->event->AfterDisplayContent = trim(implode("\n", $results));

                            $dispatcher->trigger('onContentPrepare', array('mod_k2_content', &$item, &$params, $limitstart));
                        } else {
                            $results = $dispatcher->trigger('onBeforeDisplay', array(&$item, &$params, $limitstart));
                            $item->event->BeforeDisplay = trim(implode("\n", $results));

                            $results = $dispatcher->trigger('onAfterDisplay', array(&$item, &$params, $limitstart));
                            $item->event->AfterDisplay = trim(implode("\n", $results));

                            $results = $dispatcher->trigger('onAfterDisplayTitle', array(&$item, &$params, $limitstart));
                            $item->event->AfterDisplayTitle = trim(implode("\n", $results));

                            $results = $dispatcher->trigger('onBeforeDisplayContent', array(&$item, &$params, $limitstart));
                            $item->event->BeforeDisplayContent = trim(implode("\n", $results));

                            $results = $dispatcher->trigger('onAfterDisplayContent', array(&$item, &$params, $limitstart));
                            $item->event->AfterDisplayContent = trim(implode("\n", $results));

                            $dispatcher->trigger('onPrepareContent', array(&$item, &$params, $limitstart));
                        }
                    }

                    // Initialize K2 plugin events
                    $item->event->K2BeforeDisplay = '';
                    $item->event->K2AfterDisplay = '';
                    $item->event->K2AfterDisplayTitle = '';
                    $item->event->K2BeforeDisplayContent = '';
                    $item->event->K2AfterDisplayContent = '';
                    $item->event->K2CommentsCounter = '';

                    // K2 Plugins
                    if ($params->get('K2Plugins', 1)) {
                        $results = $dispatcher->trigger('onK2BeforeDisplay', array(&$item, &$params, $limitstart));
                        $item->event->K2BeforeDisplay = trim(implode("\n", $results));

                        $results = $dispatcher->trigger('onK2AfterDisplay', array(&$item, &$params, $limitstart));
                        $item->event->K2AfterDisplay = trim(implode("\n", $results));

                        $results = $dispatcher->trigger('onK2AfterDisplayTitle', array(&$item, &$params, $limitstart));
                        $item->event->K2AfterDisplayTitle = trim(implode("\n", $results));

                        $results = $dispatcher->trigger('onK2BeforeDisplayContent', array(&$item, &$params, $limitstart));
                        $item->event->K2BeforeDisplayContent = trim(implode("\n", $results));

                        $results = $dispatcher->trigger('onK2AfterDisplayContent', array(&$item, &$params, $limitstart));
                        $item->event->K2AfterDisplayContent = trim(implode("\n", $results));

                        $dispatcher->trigger('onK2PrepareContent', array(&$item, &$params, $limitstart));

                        if ($params->get('itemCommentsCounter')) {
                            $results = $dispatcher->trigger('onK2CommentsCounter', array(&$item, &$params, $limitstart));
                            $item->event->K2CommentsCounter = trim(implode("\n", $results));
                        }
                    }
                }

                // Restore the intotext variable after plugins are executed
                $item->introtext = $item->text;

                // Remove the plugin tags
                $item->introtext = preg_replace("#{(.*?)}(.*?){/(.*?)}#s", '', $item->introtext);

                // Author (user)
                if ($params->get('itemAuthor')) {
                    if (!empty($item->created_by_alias)) {
                        $item->author = $item->created_by_alias;
                        $item->authorGender = null;
                        $item->authorDescription = null;
                        if ($params->get('itemAuthorAvatar')) {
                            $item->authorAvatar = \K2HelperUtilities::getAvatar('alias');
                        }
                        $item->authorLink = Uri::root(true);
                    } else {
                        $author = Factory::getUser($item->created_by);
                        $item->author = $author->name;

                        $query = "SELECT `description`, `gender` FROM #__k2_users WHERE userID=".(int)$author->id;
                        $db->setQuery($query, 0, 1);

                        $result = $db->loadObject();
                        if ($result) {
                            $item->authorGender = $result->gender;
                            $item->authorDescription = $result->description;
                        } else {
                            $item->authorGender = null;
                            $item->authorDescription = null;
                        }

                        if ($params->get('itemAuthorAvatar')) {
                            $item->authorAvatar = \K2HelperUtilities::getAvatar($author->id, $author->email, $componentParams->get('userImageWidth'));
                        }

                        $item->authorLink = Route::_(\K2HelperRoute::getUserRoute($item->created_by));
                    }
                }

                // Author (user) avatar
                if ($params->get('itemAuthorAvatar') && !isset($item->authorAvatar)) {
                    if (!empty($item->created_by_alias)) {
                        $item->authorAvatar = \K2HelperUtilities::getAvatar('alias');
                        $item->authorLink = Uri::root(true);
                    } else {
                        $jAuthor = Factory::getUser($item->created_by);
                        $item->authorAvatar = \K2HelperUtilities::getAvatar($jAuthor->id, $jAuthor->email, $componentParams->get('userImageWidth'));
                        $item->authorLink = Route::_(\K2HelperRoute::getUserRoute($item->created_by));
                    }
                }

                if ($item->catid) {
                    $item->displayCategoryLink = Route::_(\K2HelperRoute::getCategoryRoute($item->catid.':'.urlencode($item->categoryalias)));
                    $item->displayCategoryTitle = $show_category ? '<a href="'.$item->displayCategoryLink.'">'.$item->category_title.'</a>' : '';
                } else {
                    $item->displayCategoryTitle = $show_category ? $item->category_title : '';
                }
                // Populate the output array
                $rows[] = $item;
            }
            return $rows;
        }
    }

    /**
     * Get Articles of K2.
     *
     * @param array  $catids categories of K2
     * @param object $helper
     * @param object $params
     *
     * @return object
     */
    public function getArticles($catids, &$helper, $params)
    {
        jimport('joomla.filesystem.file');
        $limit = (int) $params->get('introitems', $helper->get('introitems')) + (int) $params->get('linkitems', $helper->get('linkitems'));
        if (!$limit) {
            $limit = 4;
        }
        $ordering = $helper->get('ordering', '');

        //get params of K2 component
        $componentParams = JComponentHelper::getParams('com_k2');
        $limitstart = 0;

        $user = Factory::getUser();
        $app = Factory::getApplication();
        $aid = $user->get('aid') ? $user->get('aid') : 1;
        $db = Factory::getDBO();

        $jnow = Factory::getDate();
        //$now              = $jnow->toMySQL();
        if (version_compare(JVERSION, '3.0', 'ge')) {
            $now = $jnow->toSql();
        } elseif (version_compare(JVERSION, '2.5', 'ge')) {
            $now = $jnow->toMySQL();
        } else {
            $now = $jnow->toMySQL();
        }
        $nullDate = $db->getNullDate();

        $query = 'SELECT i.*, c.name AS categoryname,c.id AS categoryid, c.alias AS categoryalias, c.name as cattitle, c.params AS categoryparams';
        $query .= "\n FROM #__k2_items as i LEFT JOIN #__k2_categories c ON c.id = i.catid";
        $query .= "\n WHERE i.published = 1 AND i.access <= {$aid} AND i.trash = 0 AND c.published = 1 AND c.access <= {$aid} AND c.trash = 0";
        $query .= "\n AND i.catid IN ($catids)";
        $query .= "\n AND ( i.publish_up = ".$db->Quote($nullDate).' OR i.publish_up <= '.$db->Quote($now).' )';
        $query .= "\n AND ( i.publish_down = ".$db->Quote($nullDate).' OR i.publish_down >= '.$db->Quote($now).' )';

        if ($helper->get('featured') == 'hide') {
            $query .= "\n AND i.featured = 0";
        }

        if ($helper->get('featured') == 'only') {
            $query .= "\n AND i.featured = 1";
        }

        // language filter
        $lang = Factory::getLanguage();
        $languages = JLanguageHelper::getLanguages('lang_code');
        $languageTag = $lang->getTag();
        if ($app->getLanguageFilter()) {
            $query .= " AND i.language IN ('{$languageTag}','*') ";
        }

        if ($helper->get('timerange') > 0) {
            $datenow = Factory::getDate();
            //$date         = $datenow->toMySQL();
            if (version_compare(JVERSION, '3.0', 'ge')) {
                $date = $datenow->toSql();
            } elseif (version_compare(JVERSION, '2.5', 'ge')) {
                $date = $datenow->toMySQL();
            } else {
                $date = $datenow->toMySQL();
            }
            $query .= " AND i.created > DATE_SUB('{$date}',INTERVAL ".$helper->get('timerange').' DAY) ';
        }

        $sort_order = $helper->get('sort_order', 'DESC');
        switch ($ordering) {
            case 'ordering':
                $ordering = 'ordering '.$sort_order;
                break;

            case 'rand':
                $ordering = 'RAND()';
                break;

            case 'hits':
                $ordering = 'hits '.$sort_order;
                break;

            case 'created':
                $ordering = 'created '.$sort_order;
                break;

            case 'modified':
                $ordering = 'modified '.$sort_order;
                break;

            case 'title':
                $ordering = 'title '.$sort_order;
                break;
        }

        if ($ordering == 'RAND()') {
            $query .= "\n ORDER BY ".$ordering;
        } else {
            $query .= "\n ORDER BY i.".$ordering;
        }
        $db->setQuery($query, 0, $limit);
        $rows = $db->loadObjectList();

        $autoresize = intval(trim($helper->get('autoresize', 0)));

        $width_img = (int) $helper->get('width', 100) < 0 ? 100 : $helper->get('width', 100);
        $height_img = (int) $helper->get('height', 100) < 0 ? 100 : $helper->get('height', 100);
        $img_w = intval(trim($width_img));
        $img_h = intval(trim($height_img));

        //$img_w                = intval(trim($helper->get('width', 100)));
        //$img_h                = intval(trim($helper->get('height', 100)));

        $img_align = $helper->get('align', 'left');
        $showimage = $params->get('showimage', $helper->get('showimage', 0));
        $maxchars = intval(trim($helper->get('maxchars', 200)));
        $hiddenClasses = trim($helper->get('hiddenClasses', ''));
        $showdate = $helper->get('showdate', 0);
        $enabletimestamp = $helper->get('timestamp', 0);

        if (count($rows)) {
            foreach ($rows as $j => $row) {
                $row->introtext1 = '';

                $row->cat_link = urldecode(Route::_(\K2HelperRoute::getCategoryRoute($row->categoryid.':'.urlencode($row->categoryalias))));

                //Clean title
                $row->title = \JFilterOutput::ampReplace($row->title);

                //Images
                $image = '';
                if (File::exists(JPATH_SITE.'/media/k2/items/cache/'.md5('Image'.$row->id).'_XL.jpg')) {
                    $image = Uri::root().'media/k2/items/cache/'.md5('Image'.$row->id).'_XL.jpg';
                } elseif (File::exists(JPATH_SITE.'/media/k2/items/cache/'.md5('Image'.$row->id).'_XS.jpg')) {
                    $image = Uri::root().'media/k2/items/cache/'.md5('Image'.$row->id).'_XS.jpg';
                } elseif (File::exists(JPATH_SITE.'/media/k2/items/cache/'.md5('Image'.$row->id).'_L.jpg')) {
                    $image = Uri::root().'media/k2/items/cache/'.md5('Image'.$row->id).'_L.jpg';
                } elseif (File::exists(JPATH_SITE.'/media/k2/items/cache/'.md5('Image'.$row->id).'_S.jpg')) {
                    $image = Uri::root().'media/k2/items/cache/'.md5('Image'.$row->id).'_S.jpg';
                } elseif (File::exists(JPATH_SITE.'/media/k2/items/cache/'.md5('Image'.$row->id).'_M.jpg')) {
                    $image = Uri::root().'media/k2/items/cache/'.md5('Image'.$row->id).'_M.jpg';
                } elseif (File::exists(JPATH_SITE.'/media/k2/items/cache/'.md5('Image'.$row->id).'_Generic.jpg')) {
                    $image = Uri::root().'media/k2/items/cache/'.md5('Image'.$row->id).'_Generic.jpg';
                }

                if ($image != '') {
                    $thumbnailMode = $helper->get('thumbnail_mode', 'crop');
                    $aspect = $helper->get('use_ratio', '1');
                    $crop = $thumbnailMode == 'crop' ? true : false;
                    $align = $img_align ? "align=\"$img_align\"" : '';

                    $jaimage = JAImage::getInstance();
                    if ($thumbnailMode != 'none' && $jaimage->sourceExisted($image)) {
                        $imageURL = $jaimage->resize($image, $img_w, $img_h, $crop, $aspect);
                        $imageURL = str_replace(Uri::base(), '', $imageURL);
                        $row->image = $imageURL ? "<img class=\"$img_align\" src=\"".$imageURL."\" alt=\"{$row->title}\" $align />" : '';
                    } else {
                        $width = $img_w ? "width=\"$img_w\"" : '';
                        $height = $img_h ? "height=\"$img_h\"" : '';
                        $row->image = "<img class=\"$img_align\" src=\"".$image."\" alt=\"{$row->title}\" $img_w $img_h $align />";
                    }

                    if ($maxchars && strlen($row->introtext) > $maxchars) {
                        $doc = JDocument::getInstance();
                        if (function_exists('mb_substr')) {
                            $row->introtext1 = SmartTrim::mb_trim($row->introtext, 0, $maxchars, $doc->_charset);
                        } else {
                            $row->introtext1 = SmartTrim::trim($row->introtext, 0, $maxchars);
                        }
                    } elseif ($maxchars == 0) {
                        $row->introtext1 = '';
                    }
                    $helper->replaceImage($row, $img_align, $autoresize, $maxchars, $showimage, $img_w, $img_h, $hiddenClasses);
                } else {
                    $row->image = $helper->replaceImage($row, $img_align, $autoresize, $maxchars, $showimage, $img_w, $img_h, $hiddenClasses);
                    if ($maxchars == 0) {
                        $row->introtext1 = '';
                    }
                }

                // Introtext
                $row->text = $row->introtext;

                //Read more link
                $row->link = urldecode(Route::_(\K2HelperRoute::getItemRoute($row->id.':'.urlencode($row->alias), $row->catid.':'.urlencode($row->categoryalias))));

                $helper->_params->set('parsedInModule', 1);

                $dispatcher = \JDispatcher::getInstance();

                if ($helper->get('JPlugins', 1)) {
                    //Plugins
                    $results = $dispatcher->trigger('onBeforeDisplay', [&$row, &$helper->_params, $limitstart]);
                    $row->event = new \stdClass();
                    $row->event->BeforeDisplay = trim(implode("\n", $results));

                    $results = $dispatcher->trigger('onAfterDisplay', [&$row, &$helper->_params, $limitstart]);
                    $row->event->AfterDisplay = trim(implode("\n", $results));

                    $results = $dispatcher->trigger('onAfterDisplayTitle', [&$row, &$helper->_params, $limitstart]);
                    $row->event->AfterDisplayTitle = trim(implode("\n", $results));

                    $results = $dispatcher->trigger('onBeforeDisplayContent', [&$row, &$helper->_params, $limitstart]);
                    $row->event->BeforeDisplayContent = trim(implode("\n", $results));

                    $results = $dispatcher->trigger('onAfterDisplayContent', [&$row, &$helper->_params, $limitstart]);
                    $row->event->AfterDisplayContent = trim(implode("\n", $results));

                    $dispatcher->trigger('onPrepareContent', [&$row, &$helper->_params, $limitstart]);
                    $row->introtext = $row->text;
                }

                //Init K2 plugin events
                $row->event->K2BeforeDisplay = '';
                $row->event->K2AfterDisplay = '';
                $row->event->K2AfterDisplayTitle = '';
                $row->event->K2BeforeDisplayContent = '';
                $row->event->K2AfterDisplayContent = '';
                $row->event->K2CommentsCounter = '';

                //K2 plugins
                if ($helper->get('K2Plugins', 1)) {
                    \JPluginHelper::importPlugin('k2');

                    $results = $dispatcher->trigger('onK2BeforeDisplay', [&$row, &$helper->_params, $limitstart]);
                    $row->event->K2BeforeDisplay = trim(implode("\n", $results));

                    $results = $dispatcher->trigger('onK2AfterDisplay', [&$row, &$helper->_params, $limitstart]);
                    $row->event->K2AfterDisplay = trim(implode("\n", $results));

                    $results = $dispatcher->trigger('onK2AfterDisplayTitle', [&$row, &$helper->_params, $limitstart]);
                    $row->event->K2AfterDisplayTitle = trim(implode("\n", $results));

                    $results = $dispatcher->trigger('onK2BeforeDisplayContent', [&$row, &$helper->_params, $limitstart]);
                    $row->event->K2BeforeDisplayContent = trim(implode("\n", $results));

                    $results = $dispatcher->trigger('onK2AfterDisplayContent', [&$row, &$helper->_params, $limitstart]);
                    $row->event->K2AfterDisplayContent = trim(implode("\n", $results));

                    $dispatcher->trigger('onK2PrepareContent', [&$row, &$helper->_params, $limitstart]);
                    $row->introtext = $row->text;
                }

                //Clean the plugin tags
                $row->introtext = preg_replace('#{(.*?)}(.*?){/(.*?)}#s', '', $row->introtext);
                $row->introtext = '<p>'.$row->introtext.'</p>';

                //Author
                if ($helper->get('showcreator')) {
                    if (!empty($row->created_by_alias)) {
                        $row->author = $row->created_by_alias;
                        $row->authorGender = null;
                    } else {
                        $author = Factory::getUser($row->created_by);
                        $row->author = $author->name;
                        $query = 'SELECT `gender` FROM #__k2_users WHERE userID='.(int) $author->id;
                        $db->setQuery($query, 0, 1);
                        $row->authorGender = $db->loadResult();
                        //Author Link
                        $row->authorLink = Route::_(\K2HelperRoute::getUserRoute($row->created_by));
                    }
                }

                $row->created = ($row->created != '' && $row->created != '0000-00-00 00:00:00') ? $row->created : $row->modified;
                if ($enabletimestamp) {
                    $row->created = $helper->generatTimeStamp($row->created);
                } else {
                    $row->created = HTMLHelper::_('date', $row->created);
                }

                $rows[$j] = $row;
            }
        }

        return $rows;
    }

    /**
     * Get category detail.
     *
     * @param int $catid
     *
     * @return object category detail
     */
    public function getCategory($catid)
    {
        $user = Factory::getUser();
        $aid = $user->get('aid') ? $user->get('aid') : 1;

        $db = Factory::getDBO();
        $query = "SELECT *, name as title FROM #__k2_categories WHERE id={$catid} AND published=1 AND trash=0 AND access<={$aid} ";

        $db->setQuery($query);
        $row = $db->loadObject();

        if ($db->getErrorNum()) {
            echo $db->stderr();

            return false;
        }

        return $row;
    }

    /**
     * Get total hits of K2 item.
     *
     * @return int
     */
    public function getTotalHits()
    {
        $db = Factory::getDBO();
        $query = 'SELECT MAX(hits)'.' FROM #__k2_items';
        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Get K2 category children.
     *
     * @param int  $catid
     * @param bool $clear if true return array which is removed value construction
     *
     * @return array
     */
    public function getK2CategoryChildren($catid, $clear = false)
    {
        static $array = [];
        if ($clear) {
            $array = [];
        }
        $user = Factory::getUser();
        $aid = $user->get('aid') ? $user->get('aid') : 1;
        $catid = (int) $catid;
        $db = Factory::getDBO();
        $query = "SELECT * FROM #__k2_categories WHERE parent={$catid} AND published=1 AND trash=0 AND access<={$aid} ORDER BY ordering ";
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        foreach ($rows as $row) {
            array_push($array, $row->id);
            if (JAK2HelperPro::hasK2Children($row->id)) {
                JAK2HelperPro::getK2CategoryChildren($row->id);
            }
        }

        return $array;
    }

    /**
     * Check category has children.
     *
     * @param int $id
     *
     * @return bool
     */
    public function hasK2Children($id)
    {
        $user = Factory::getUser();
        $aid = $user->get('aid') ? $user->get('aid') : 1;
        $id = (int) $id;
        $db = Factory::getDBO();
        $query = "SELECT * FROM #__k2_categories WHERE parent={$id} AND published=1 AND trash=0 AND access<={$aid} ";
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        if (count($rows)) {
            return true;
        } else {
            return false;
        }
    }
}

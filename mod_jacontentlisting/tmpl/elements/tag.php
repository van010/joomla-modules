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
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

JLoader::register('TagsHelperRoute', JPATH_BASE.'/components/com_tags/helpers/route.php');

$authorised = Factory::getUser()->getAuthorisedViewLevels();

$helper = $displayData['helper'];
$options = $displayData['options'];
$item = $displayData['data'];
?>

<?php if ($options->get('show_tags', 0) && !empty($item->tags)) : ?>
<?php if(empty($item->tags->itemTags)) return; ?>
<?php $tags = $item->tags->itemTags;
?>
<div class="jacl-item__tags">
    <ul class="tags list-inline">
        <?php foreach ($tags as $i => $tag) : ?>
            <?php if (in_array($tag->access, $authorised)) : ?>
                <?php $tagParams = new Registry($tag->params); ?>
                <?php $link_class = $tagParams->get('tag_link_class', 'badge badge-info'); ?>
                <li class="list-inline-item tag-<?php echo $tag->tag_id; ?> tag-list<?php echo $i; ?>" itemprop="keywords">
                    <a href="<?php echo Route::_(TagsHelperRoute::getTagRoute($tag->tag_id.':'.$tag->alias)); ?>" class="<?php echo $link_class; ?>">
                        <?php echo $this->escape($tag->title); ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
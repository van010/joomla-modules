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
defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$helper = $displayData['helper'];
$options = $displayData['options'];
$item = $displayData['data'];
$params =  $item->params;

$authorId = $item->created_by;
$db = Factory::getDbo();
$query = $db->getQuery(true)
  ->select('username')
  ->from('#__users')
  ->where('id = ' . $authorId)->where('block = 0');
$db->setQuery($query);
$username = $db->loadResult();

if (isset($item->displayCategoryLink) && !empty($item->displayCategoryLink)){
  $shortLink = $item->displayCategoryLink;
}

if(empty($item->created_by_alias) && empty($item->author)){
  return;
}
?>
<li class="item-author" itemprop="author" itemscope itemtype="https://schema.org/Person">
  <?php $author = !empty($item->created_by_alias) ? $item->created_by_alias : $item->author; ?>
	<?php $author = '<span itemprop="name">'.$author.'</span>'; ?>
	<?php if (!empty($item->contact_link) && $params->get('link_author') == true) : ?>
		<?php echo Text::sprintf('MOD_CONTENT_LISTING_BY', HTMLHelper::_('link', $item->contact_link, $author, ['itemprop' => 'url'])); ?>
	<?php else : ?>
    <?php if ($username && isset($shortLink)): ?>
      <?php echo Text::sprintf('MOD_CONTENT_LISTING_BY', HTMLHelper::_('link', $shortLink . "/author/$username", $author, ['itemprop' => 'url'])); ?>
    <?php else: ?>
      <?php echo Text::sprintf('MOD_CONTENT_LISTING_BY', $author); ?>
    <?php endif; ?>
	<?php endif; ?>
</li>

<li style="display: none;" itemprop="publisher" itemtype="http://schema.org/Organization" itemscope>
	<?php $author = ($item->created_by_alias ?: $item->author); ?>
	<?php $author = '<span itemprop="name">'.$author.'</span>'; ?>
	<?php echo $author; ?>
</li>
      
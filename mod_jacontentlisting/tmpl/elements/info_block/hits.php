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

use Joomla\CMS\Language\Text;

$item = $displayData['data'];
if (!isset($item->hits)) return;
?>
<li class="item-hits">
	<meta itemprop="interactionCount" content="UserPageVisits:<?php echo $item->hits; ?>">
	<?php echo $item->hits <= 1 ? Text::_('MOD_CONTENT_LISTING_HIT'): Text::_('MOD_CONTENT_LISTING_HITS'); ?>
	<span><?php echo $item->hits; ?></span>
</li>

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

use Joomla\CMS\Language\Text;

$helper = $displayData['helper'];
$options = $displayData['options'];
$item = $displayData['data'];
?>

<?php if ($options->get('show_readmore')) : ?>
<div class="jacl-item__readmore">
  <a class="readmore-link" href="<?php echo $item->link; ?>" title="<?php echo $item->title; ?>">
    <?php echo Text::_('MOD_CONTENT_LISTING_READ_MORE'); ?>
  </a>
</div>
<?php endif; ?>



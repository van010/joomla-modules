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

use Joomla\CMS\HTML\HTMLHelper;

$helper = $displayData['helper'];
$options = $displayData['options'];
$item = $displayData['data'];
if($options->get('show_author',0) || $options->get('show_date',0) || $options->get('show_hits',0)):
$show_date_field = $options->get('show_date_field', 'created');
?>

<div class="jacl-item__meta">
  <ul>
    <?php if ($options->get('show_author')) : ?>
      <?php $helper->render('elements/info_block/author.php', $options, $item); ?>
    <?php endif; ?>

    <?php if ($options->get('show_date')) : ?>
    <li class="item-date">
      <?php echo HTMLHelper::_('date', $item->$show_date_field, $options->get('show_date_format') ?? 'M d'); ?>
    </li>
    <?php endif; ?>

    <?php if ($options->get('show_hits')) : ?>
      <?php $helper->render('elements/info_block/hits.php', $options, $item); ?>
    <?php endif; ?>

  </ul>
</div>
<?php endif;?>
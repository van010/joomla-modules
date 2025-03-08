<?php
/**
 *------------------------------------------------------------------------------
 * @package       Module JA Content Listing for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2022 JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github
 *                & Google group to become co-author)
 *------------------------------------------------------------------------------
 */
defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;

$item = $displayData['data'];
$options = $displayData['options'];

$show_mf = (bool) $options->get('show_mf');
$mf_info = $item->mf_info;

if (!$show_mf || !$mf_info) return;
?>

<div class="jacl-item__manufacture">
  <span class="field-label"><?php echo Text::_('COM_VIRTUEMART_SEF_MANUFACTURER'); ?>:</span>
  <?php foreach ($mf_info as $mf): ?>
    <a href="<?php echo $mf->mf_link ?>" title="<?php echo $mf->mf_name; ?>"><span><?php echo $mf->mf_name; ?></span></a>
  <?php endforeach; ?>
</div>
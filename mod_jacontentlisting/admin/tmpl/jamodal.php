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

$id = $displayData['id'];
$name = $displayData['name'];
$label = $displayData['label'];
$modal_id = $displayData['modalId'];
$val = $displayData['value'];
$data = $displayData['data'];
$attr = 'data-dismiss="modal"';
if(version_compare(JVERSION, '4', 'ge')){
  $attr = 'data-bs-dismiss="modal"';
}
?>
<div id="<?php echo $modal_id; ?>-selected" class="modal jacontentlisting__modal fade" tabindex="-1" data-name="<?php echo $id; ?>">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?php echo Text::_($label); ?> Modal</h5>
        <button type="button" class="close" <?php echo $attr;?> aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <?php if (!empty($data)):?>
        <ul class="thumb-list">
      <?php foreach ($data as $item) :?>
      <?php $active = ($val == $item['name']) ? 'active' : ''; ?>
        <li class="<?php echo $active; ?>" data-val="<?php echo $item['name']; ?>" data-name="<?php echo $id; ?>">
          <div class="<?php echo $modal_id; ?>-<?php echo $item['name']; ?>">
              <img src="<?php echo $item['thumb']; ?>" alt="<?php echo $item['name']; ?>">
              <span class="thumb-name"><?php echo $item['name']; ?></span>
          </div>
         </li>
      <?php endforeach; ?>
        </ul>

      <?php else:?>
        <p>No layout exist.</p>
      <?php endif; ?>
      </div>
    </div>
  </div>
</div>
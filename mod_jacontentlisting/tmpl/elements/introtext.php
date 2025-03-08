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

$helper = $displayData['helper'];
$options = $displayData['options'];
$item = $displayData['data'];

$show_introtext = $options->get('show_introtext',0);
$introtext_limit = $options->get('introtext_limit',100);
$item->displayIntrotext = \ModJacontentlistingHelper::truncate($item->introtext, $introtext_limit);
?>

<?php if ($show_introtext) : ?>
  <div class="jacl-item__introtext">
    <?php echo $item->displayIntrotext; ?>
  </div>
<?php endif; ?>
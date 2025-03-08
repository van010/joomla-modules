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

$options = $displayData['options'];
$title = $displayData['data'];
$heading_desc = !empty($options->get('heading_desc')) ? $options->get('heading_desc') : '';

?>
<?php if ($options->get('show_heading')) :?>
<div class="container">
	<div class="mod-heading heading-<?php echo $options->get('heading_style'); ?>">
	    <h2 class="heading-title"><span><?php echo $title; ?></span></h2>
	    <div class="heading-desc">
	        <?php echo htmlspecialchars_decode($heading_desc); ?>
	    </div>
	</div>
</div>
<?php endif; ?>
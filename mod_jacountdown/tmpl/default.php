<?php
/**
 * $JA#COPYRIGHT$
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

?>
<div class="ja-countdown <?php echo $jalayout;?> <?php echo $params->get('moduleclass_sfx');?>"<?php echo $stylesheets;?>>

<?php if($custom_titles) : ?>
<h1><?php echo $custom_titles;?></h1>
<?php endif;?>

<?php if($custom_message): ?>
<?php echo $custom_message;?>
<?php endif;?>

<?php 
	require ModuleHelper::getLayoutPath('mod_jacountdown/', $jalayout.'/layout');
?>
</div>
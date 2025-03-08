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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

$paramsForm = $displayData['form'];
$extendParams = $displayData['extendForm'];
$nameField = $displayData['nameField'];
$params = $displayData['params'];
$HTML_Profile = $displayData['input'];

// JVERSION
?>
<script type="text/javascript">
var jacontentlisting = window.jacontentlisting || {};
	jacontentlisting['<?php echo $nameField; ?>'] = '<?php echo $params; ?>';
	jacontentlisting['url'] = '<?php echo Uri::root(); ?>';
	jacontentlisting['ajaxUrl'] = '<?php echo Uri::root().'index.php?option=com_ajax&module=jacontentlisting&format=json'; ?>';
	jacontentlisting['lang'] = {};
	jacontentlisting.lang['MOD_JACONTENTLISTING_ITEM_MEDIA_PATH_INTRO_IMG'] = '<?php echo Text::_('MOD_JACONTENTLISTING_ITEM_MEDIA_PATH_INTRO_IMG'); ?>';
	jacontentlisting.lang['MOD_JACONTENTLISTING_ITEM_MEDIA_PATH_MEDIA_FIELD_IMG'] = '<?php echo Text::_('MOD_JACONTENTLISTING_ITEM_MEDIA_PATH_MEDIA_FIELD_IMG'); ?>';
	jacontentlisting.lang['MOD_JACONTENTLISTING_ITEM_MEDIA_PATH_POST_COVER_IMG'] = '<?php echo Text::_('MOD_JACONTENTLISTING_ITEM_MEDIA_PATH_POST_COVER_IMG'); ?>';
	jacontentlisting.lang['MOD_JACONTENTLISTING_ITEM_MEDIA_PATH_FULL_IMG'] = '<?php echo Text::_('MOD_JACONTENTLISTING_ITEM_MEDIA_PATH_FULL_IMG'); ?>';
	jacontentlisting.lang['MOD_JACONTENTLISTING_ITEM_MEDIA_PATH_FIRST_IMG'] = '<?php echo Text::_('MOD_JACONTENTLISTING_ITEM_MEDIA_PATH_FIRST_IMG'); ?>';
</script>
<?php echo $HTML_Profile; ?>
<?php if($nameField == 'jasource'):?>
<div class="<?php echo $nameField; ?>">
<?php endif; ?>
<?php

$fieldSets = $paramsForm->getFieldsets();
$extendFieldSets = $extendParams ? $extendParams->getFieldsets() : '';
foreach ($fieldSets as $name => $fieldSet) :
    if (isset($fieldSet->description) && trim($fieldSet->description)) {
        echo '<p class="tip">'.Text::_($fieldSet->description).'</p>';
    }

    $hidden_fields = '';
    foreach ($paramsForm->getFieldset($name) as $field) :
        if (!$field->hidden): ?>
			<div class="control-group">
				<div class="control-label">
				<?php echo $paramsForm->getLabel($field->fieldname, $field->group);  ?>
				</div>	
				<div class="controls">
				<?php echo $paramsForm->getInput($field->fieldname, $field->group);?>
				</div>
			</div>
		<?php else :
            $hidden_fields .= $paramsForm->getInput($field->fieldname, $field->group);
        endif;
    endforeach;
    ?>
<?php if (!empty($hidden_fields)):?>
<div class="control-group hide">
	<div class="control-label"></div>
	<div class="controls">
		<?php echo $hidden_fields; ?>
	</div>
</div>
<?php endif; ?>
<?php
endforeach;
?>
<div class="extra-<?php echo $nameField; ?>">
	<?php 	
	if (!empty($extendFieldSets)):
 	?>
	<?php foreach ($extendFieldSets as $name2 => $fieldSet2) :
        if (isset($fieldSet2->description) && trim($fieldSet2->description)) {
            echo '<p class="tip">'.Text::_($fieldSet2->description).'</p>';
        }

        $hidden_fields = '';
        foreach ($extendParams->getFieldset($name2) as $field2) :
          if (!$field2->hidden):?>
					<div class="control-group">
						<div class="control-label">
						<?php echo $extendParams->getLabel($field2->fieldname, $field2->group); ?>
						</div>	
						<div class="controls">
						<?php echo $extendParams->getInput($field2->fieldname, $field2->group); ?>
						</div>
					</div>
			<?php else :
            $hidden_fields .= $extendParams->getInput($field2->fieldname, $field2->group);
          endif;
        endforeach; ?>
	<?php if (!empty($hidden_fields)):?>
	<div class="control-group hide">
		<div class="control-label"></div>
		<div class="controls">
			<?php echo $hidden_fields; ?>
		</div>
	</div>
	<?php endif; ?>
	<?php endforeach; ?>
	<?php endif; ?>

</div>
<?php if($nameField == 'jasource'):?>
</div>
<?php endif; ?>
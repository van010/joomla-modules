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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$lang = Factory::getLanguage();
$extension = 'mod_jacontentlisting';
$base_dir = JPATH_SITE;
$language_tag = $lang->getTag();
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);
$lang->load('com_k2', JPATH_ADMINISTRATOR, $language_tag, $reload);
$paramsForm = $displayData['form'];
$fieldSets = $paramsForm->getFieldsets();
foreach ($fieldSets as $name => $fieldSet) :
    if (isset($fieldSet->description) && trim($fieldSet->description)) {
        echo '<p class="tip">'.Text::_($fieldSet->description).'</p>';
    }

    $hidden_fields = '';
    foreach ($paramsForm->getFieldset($name) as $field) :
        if (!$field->hidden):?>
            <?php if($field->showon){
                $showonData = explode(":", $field->showon);
                $showon = new \JObject();
                $showon->value =  $showonData[1];
                $showon->field = str_replace($field->fieldname, $showonData[0], $field->name);
                $showon->id = "#".str_replace($field->fieldname, $showonData[0], $field->id);
            }

            ?>
                <div class="control-group"<?php echo $field->showon ? " showon='".json_encode($showon)."'" : ""; ?>>
					<div class="control-label">
                        <?php echo Text::_($paramsForm->getLabel($field->fieldname, $field->group)); ?>
					</div>	
					<div class="controls">
				        <?php echo $paramsForm->getInput($field->fieldname, $field->group); ?>
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

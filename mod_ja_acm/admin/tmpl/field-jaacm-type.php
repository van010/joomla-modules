<?php
/**
 * ------------------------------------------------------------------------
 * JA ACM Module
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

use Joomla\CMS\Language\Text;

$form       = $displayData['form'];
$fieldsets  = $displayData['fieldsets'];
$sampledata = $displayData['sample-data'];
$helper = $displayData['helper'];
?>

<?php
if (!is_array($fieldsets)) return;
foreach ($fieldsets as $name => $fieldset) :
	$multiple           = isset($fieldset->multiple) ? $fieldset->multiple : false;
  $basic = isset($fieldset->name) ? $fieldset->name : '';
	$support_layouts    = isset($fieldset->layouts) ? ' data-layouts="' . $fieldset->layouts . '"' : '';
	$horizontal         = isset($fieldset->horizontal) ? $fieldset->horizontal : false;
?>

<input name="jatools-sample-data" type="hidden" value="<?php echo htmlspecialchars($sampledata ?? '', ENT_COMPAT, 'UTF-8') ?>" data-ignoresave="1" />

<div class="jatools-group clearfix<?php if ($multiple): ?> jatools-multiple<?php endif ?><?php if ($horizontal): ?> jatools-hoz<?php endif ?>"<?php echo $support_layouts ?>>

    <!-- Fieldset Header-->
	<div class="jatools-group-header clearfix">
        <!-- Display Field Header-->
		<h3 class="fieldset-title">
            <?php echo Text::_($fieldset->label) ?>
        </h3>
        <!-- Display Field Description-->
		<p class="fieldset-desc">
            <?php echo Text::_($fieldset->description) ?>
        </p>
	</div>

	<?php
	$fields = $form->getFieldset($name);
	?>
	<div id="acm-sortable1">
    <!-- Fieldset Body-->
		<div class="jatools-row clearfix">
			<?php foreach ($fields as $field) : ?>
				<?php
				$field->setValue($helper->get($field->name));
				$layouts = $field->element['layouts'] ? ' data-layouts="' . $field->element['layouts'] . '"' : '';
				$label = $field->getLabel();
				$input = $field->getInput();
				?>
				<div class="control-group"<?php echo $layouts ?>>
					<?php if ($label) : ?>
						<div class="control-label"><?php echo $label ?></div>
						<div class="controls"><?php echo $input ?></div>
					<?php else : ?>
						<?php echo $input ?>
					<?php endif ?>
				</div>
			<?php endforeach ?>
		</div>
		<?php if ($multiple):?>
		<div class="btn btn-danger jatools-btn-del"><?php echo Text::_('MOD_JA_ACM_BTN_DEL') ?></div>
		<?php endif ?>
	</div>
	<?php if ($multiple):?>
	<div class="jatools-row-actions clearfix">
			<div class="btn btn-primary jatools-btn-add"><?php echo Text::_('MOD_JA_ACM_BTN_ADD') ?></div>
	</div>
	<?php endif ?>
</div>

<?php endforeach ?>
<script>
  (function ($) {
    $(document).ready(function () {
      /*if ($('.jatools-row-actions').length > 1
        || (<?php echo count($fieldsets) ?> === 1 && '<?php echo $basic ?>'.includes('basic'))){
        $('.jatools-row-actions:first').remove();
      }*/

      // if ($('.jatools-row-actions').length > 1 ||$('.btn-clone-row').length || $('.btn-clone').length){
      if ($('.jatools-row-actions').length > 1 ||$('.btn-clone-row').length){
        $('.jatools-row-actions:first').remove();
      }

      var toolGroup = $('.jatools-multiple').find('#acm-sortable1 > .jatools-row');
      var toolMultiple = $('.jatools-multiple #acm-sortable1');

      if (toolGroup.length > 1) {
        toolGroup.mouseenter(function () {
          $(this).css('cursor', 'move');
        }).mouseleave(function () {
          $(this).css('cursor', '');
        })
        toolMultiple.sortable().disableSelection();
      }
    })
  })(jQuery)
</script>

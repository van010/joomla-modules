<?php
/**
 * $JA#COPYRIGHT$
 */
// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' );

$javersion = new JVersion;

?>
<script type="text/javascript">
	var JAFileConfig = window.JAFileConfig || {};
	JAFileConfig.profiles = <?php echo json_encode($jsonData)?>;
	JAFileConfig.mod_url = '<?php echo JURI::root() , '/', $extpath; ?>/admin/japrofile/helper.php';
	JAFileConfig.langs = <?php echo json_encode(array(
		'confirmCancel' => JText::_('ARE_YOUR_SURE_TO_CANCEL'),
		'enterName'	=> JText::_('ENTER_PROFILE_NAME'),
		'invalidName' => JText::_('PROFILE_NAME_NOT_EMPTY'),
		'confirmDelete' => JText::_('CONFIRM_DELETE_PROFILE')
	))?>;
	
	JAFileConfig.inst = null;

	window.addEvent('load', function(){
		JAFileConfig.inst = new JAProfileConfig('jformparams<?php echo str_replace('holder', '', $this->fieldname);?>');
		JAFileConfig.inst.changeProfile($('jformparams<?php echo str_replace('holder', '', $this->fieldname);?>').value);
	});
</script>

<div class="ja-profile">	
	<?php echo $HTML_Profile?>
	<div class="profile_action">
		<span class="clone">
			<a href="javascript:void(0)" onclick="JAFileConfig.inst.cloneProfile()" title="<?php echo JText::_('CLONE_DESC')?>"><?php echo JText::_('Clone')?></a>
		</span>
		| 
		<span class="delete">
			<a href="javascript:void(0)" onclick="JAFileConfig.inst.deleteProfile()" title="<?php echo JText::_('DELETE_DESC')?>"><?php echo JText::_('Delete')?></a>
		</span>	
	</div>
</div>

<?php if($javersion->isCompatible('3.0')) : ?>
	</div>
</div>
<?php else : ?>
</li>
<?php endif; ?>


<?php		
$fieldSets = $paramsForm->getFieldsets('params');

foreach ($fieldSets as $name => $fieldSet) :
	if (isset($fieldSet->description) && trim($fieldSet->description)){
		echo '<p class="tip">'.JText::_($fieldSet->description).'</p>';
	}
	
	$hidden_fields = '';
	foreach ($paramsForm->getFieldset($name) as $field) :
		if (!$field->hidden): 
			if($javersion->isCompatible('3.0')) : ?>
				<div class="control-group">
					<div class="control-label">
			<?php else: ?> 
				<li>
			<?php endif;
				echo $paramsForm->getLabel($field->fieldname,$field->group);
			
				if($javersion->isCompatible('3.0')) : ?>
					</div>	
					<div class="controls">
				<?php endif;
					echo $paramsForm->getInput($field->fieldname,$field->group);
				
				if($javersion->isCompatible('3.0')) : ?>
					</div>
				</div>
				<?php else: ?> 
					</li>
				<?php endif;

		else : 
			$hidden_fields .= $paramsForm->getInput($field->fieldname,$field->group);	
		endif;
	endforeach;
	echo $hidden_fields; 
endforeach; 
?>	

<?php 
	if($javersion->isCompatible('3.0')) : ?>
		<div class="control-group hide">
			<div class="control-label"></div>
				<div class="controls">
	<?php else: ?> 
		<li>
	<?php endif; ?>
<script type="text/javascript">
	// <![CDATA[ 
	window.addEvent('load', function(){
		Joomla.submitbutton = function(task){
			if (task == 'module.cancel' || document.formvalidator.isValid(document.id('module-form'))) {	
				if(task != 'module.cancel' && document.formvalidator.isValid(document.id('module-form'))){
					JAFileConfig.inst.saveProfile(task);
				}else if(task == 'module.cancel' || document.formvalidator.isValid(document.id('module-form'))){
					Joomla.submitform(task, document.getElementById('module-form'));
				}
				if (self != top) {
					window.top.setTimeout('window.parent.SqueezeBox.close()', 1000);
				}
			} else {
				alert('Invalid form');
			}
		}
	});
	// ]]> 
</script>

<?php
/**
* ------------------------------------------------------------------------
* Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
* @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
* Author: J.O.O.M Solutions Co., Ltd
* Websites: http://www.joomlart.com - http://www.joomlancers.com
* This file may not be redistributed in whole or significant part.
* ------------------------------------------------------------------------
 */
 // No direct to access this file
 defined('_JEXEC') or die();
foreach ($glist['items'] as $key => $exfield):
	$magicSelect = '';
	$fieldTypes = explode("_",$key);
	if($fieldTypes[0] == 'magicSelect'){
		$magicSelect = ' class="magic-select"';
	}

	$colClass = "jacol-" . $params->get('ja_column', 2);
	?>
	<li class="<?php echo $key ?> <?php echo $colClass?>" <?php echo $magicSelect;?>>
		<div class="subclass">
			<?php echo $exfield; ?>
		</div>
	</li>
	<?php
endforeach;
?>
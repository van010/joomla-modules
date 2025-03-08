<?php
/**
 * ------------------------------------------------------------------------
 * JA System Social Feed plugin for Joomla 2.5 & J3.5
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

// Ensure this file is being included by a parent file
defined('_JEXEC') or die( 'Restricted access' );

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Radio List Element
 *
 * @since      Class available since Release 1.2.0
 */
class JFormFieldJaDepend extends FormField
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $type = 'JaDepend';
	
	/**
	 * Check and load assets file if needed
	 */
	function loadAsset(){
		
		if (!defined ('_JA_DEPEND_ASSET_')) {
			define ('_JA_DEPEND_ASSET_', 1);
			$uri = str_replace(DIRECTORY_SEPARATOR, '/', str_replace( JPATH_SITE, Uri::base(), dirname(__FILE__) ));
			$uri = str_replace('/administrator/', '', $uri);
			//mootools support joomla 1.7 and 2.5
			HTMLHelper::_('behavior.framework', true);
			HTMLHelper::script($uri.'/assets/js/jadepend.js');
		}
		
	}

	protected function getInput(){
		$this->loadAsset();
		
		$func 	= (string)$this->element['function'] ? (string)$this->element['function'] : '';
		$value 	= $this->value ? $this->value : (string) $this->element['default'];

		if (substr($func, 0, 1) == '@'){
			$func = substr($func, 1);
			if (method_exists($this, $func)) {
				return $this->$func();
			}
		} else {
			$subtype = ( isset( $this->element['subtype'] ) ) ? trim($this->element['subtype']) : '';
			if (method_exists ($this, $subtype)) {
				return $this->$subtype ();
			}
		}

		return null;
	}
	
    /**
     *
     * Get Label of element param
     * @return string label
     */
    function getLabel()
    {
    	$func 	= (string)$this->element['function']?(string)$this->element['function']:'';
    	if (substr($func, 0, 1) == '@' || !isset( $this->label ) || !$this->label){
    		return;
    	} else {
    		return parent::getLabel ();
    	}
    }
	
	/**
	 * render js to control setting form.
     * @param	string	$name The name of element param
     * @param	string	$value	The value of element
     * @param	object	$node The node of element
     * @param	string	$control_name
     * @return	string  group param
	 */
	function group(){
		preg_match_all('/jform\\[([^\]]*)\\]/', $this->name, $matches);
		$group_name = 'jform';
		
		if(!isset($matches[1]) || empty($matches[1])){
			preg_match_all('/jaform\\[([^\]]*)\\]/', $this->name, $matches);
			$group_name = 'jaform';
		}
		
		if(isset($matches[1]) && !empty($matches[1])):

			?>
		<span class="hideanchor"></span>
		<script type="text/javascript">
		jQuery(window).on('load', function(){
			<?php 
			foreach ($this->element->children() as $option){
				$elms = preg_replace('/\s+/', '', (string)$option[0]);
				?>
				JADepend.inst.add('<?php echo $option['for']; ?>', {
					val: '<?php echo $option['value']; ?>',
					elms: '<?php echo $elms?>',
					group: '<?php echo $group_name . '[' . @$matches[1][0] . ']'; ?>'
				});
				<?php
			}
			?>
		});
		</script>
		<?php
		endif;
	}
} 
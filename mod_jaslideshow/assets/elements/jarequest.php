<?php
/**
 * $JA#COPYRIGHT$
 */

defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.form.formfield');

class JFormFieldJarequest extends JFormField {
    protected $type = 'Jarequest';    
    protected function getInput() {
		$params = $this->form->getValue('params');
		//remove request param lable
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration("$(window).addEvent('load', function(){jQuery('#jform_params_jarequest-lbl').parent().remove();});");
		$task = JRequest::getString('jatask', '');
		$jarequest = strtolower(JRequest::getString('jarequest'));
		//process
        if ($jarequest && $task) {			
			
			//load file to excute task
			require_once(dirname(dirname(dirname(__FILE__))).'/admin/jarequest/'.$jarequest.'.php');
            $obLevel = ob_get_level();
			if($obLevel){
				while ($obLevel > 0 ) {
					ob_end_clean();
					$obLevel --;
				}
			}else{
				ob_clean();
			}
            $obj = new $jarequest();
			
			$data = $obj->$task($params);
			echo json_encode($data);
			
            exit;
        }
    }    
    
}
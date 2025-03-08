<?php
/**
 * $JA#COPYRIGHT$
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');
jimport('joomla.filesystem.folder');

class JFormFieldJafolder extends JFormField {

    protected $type = 'jafolder';

    public function getInput() {        
        $jaFolder = array();
		$jaFolder[0] = new stdClass();
        $jaFolder[0]->name = 'images';
        $jaFolder[0]->text = 'images';
        $jaFolder[0]->value = 'images/';
        
        $this->buildTree('images', 0, '', $jaFolder);

        // Initialize field attributes.
        $class = $this->element['class'] ? (string) $this->element['class'] : '';
        $attr = '';
        $attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';
        $attr .= ' class="inputbox ' . $class . '"';       
		if(substr($this->value, -1) != '/'){
			$this->value = $this->value . '/';
		}
	  
        return JHTML::_('select.genericlist', $jaFolder, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);        
    }

    public function buildTree($folder, $depth, $path, &$jaFolder) {
        if($path){
			$folder = $path . '/' . $folder;
		}
        $subs = JFolder::folders(JPATH_ROOT . '/' . $folder);
		if(!empty($subs)){
			foreach ($subs as $sub) {
				$obj = new stdClass();
				$obj->name = $sub;
				$obj->text = str_repeat('- - ', $depth + 1) . $sub;
				$obj->value = $folder . '/' . $sub . '/';
				$jaFolder[] = $obj;
				$this->buildTree($sub, $depth + 1, $folder, $jaFolder);
			}
		}
    }

}
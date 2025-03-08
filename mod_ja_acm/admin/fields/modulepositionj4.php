<?php

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\ModulepositionField;

if(version_compare(JVERSION, '4', 'ge')){
	class JHtmlModules extends \Joomla\Component\Modules\Administrator\Helper\ModulesHelper{};
}else{
	JLoader::register('ModulesHelper', JPATH_ADMINISTRATOR . '/components/com_modules/helpers/modules.php');
	JLoader::register('JHtmlModules', JPATH_ADMINISTRATOR . '/components/com_modules/helpers/html/modules.php');

}


/**
 * List of checkbox base on other fields
 *
 * @since      Class available since Release 1.2.0
 */
class JFormFieldModulePositionj4 extends ModulepositionField
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $type = 'modulepositionj4';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	function getInput()
	{
		
		$clientId = Factory::getApplication()->input->get('client_id', 0, 'int');
		if(version_compare(JVERSION, '4', 'ge')){
			$options = JHtmlModules::getPositions($clientId);
			$html = HTMLHelper::_(
				'select.genericlist', $options, $this->name,
				array('id' => $this->id, 'group.id' => 'id', 'list.attr' => '', 'list.select' => $this->value)
			);
		}else{
			$options = JHtmlModules::positions($clientId);
			$html = HTMLHelper::_(
				'select.groupedlist', $options, $this->name,
				array('id' => $this->id, 'group.id' => 'id', 'list.attr' => '', 'list.select' => $this->value)
			);
		}
		return $html;
	}
}
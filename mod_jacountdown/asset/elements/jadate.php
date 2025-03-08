<?php
/**
 * $JA#COPYRIGHT$
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

jimport('joomla.form.formfield');
if(!defined('DS')){
	define('DS', DIRECTORY_SEPARATOR);
}
class JFormFieldJadate extends FormField
{
    /*
	 * Category name
	 *
	 * @access	protected
	 * @var		string
	 */
    var $type = 'Jadate';


    function getInput()
    {

        $uri = $this->getCurrentURL();
        $this->loadjscss($uri);
        $value = $this->value ? $this->value : (string) $this->element['default'];
        $string = '<input id="jadate'.$this->name.'" value="' . $value . '" name="' . $this->name . '" >';
        return $string;
    }


    /**
     * get current url
     */
    function getCurrentURL()
    {
        $uri = str_replace(DS, "/", str_replace(JPATH_SITE, Uri::base(), dirname(__FILE__)));
        $uri = str_replace("/administrator", "", $uri);
        return $uri;
    }


    /**
     * load css and js file
     */
    function loadjscss($uri)
    {	
		$document = Factory::getDocument();
        if (!defined('_JA_PARAM_HELPER_JADATE_')) {
            define('_JA_PARAM_HELPER_JADATE_', 1);
            HTMLHelper::script($uri . '/jadate/Locale.en-US.DatePicker.js');
			HTMLHelper::script($uri . '/jadate/Picker.js');
			HTMLHelper::script($uri . '/jadate/Picker.Attach.js');
			HTMLHelper::script($uri . '/jadate/Picker.Date.js');
			HTMLHelper::stylesheet($uri . '/jadate/datepicker.css');
        }
		$document->addScriptDeclaration("
				$(document).ready(function(){
					new Picker.Date('jadate".$this->name."', {
						timePicker: true,
						minDate:Date.now(),
						positionOffset: {x: 5, y: 0},
						pickerClass: 'datepicker custom',
						format:  '%Y-%m-%d %H:%M:%S',
						useFadeInOut: !Browser.ie
					});
				});
		");

    }
}
?>
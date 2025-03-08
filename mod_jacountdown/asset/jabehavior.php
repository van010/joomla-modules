<?php 

/**
 * $JA#COPYRIGHT$
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$path = str_replace(JPATH_ROOT, '', dirname(__FILE__));
$path = preg_replace('#[/\\\\]+#', '/', $path.'/');
$path = preg_replace('#^[/\\\\]+#', '', $path);

define('JA_BEHAVIOR_URL', $path);

class JaLoadAssets{
    
    /**
     * @var array   array containing information for loaded files
     */
    protected static $loaded = array();

    public static function greater_than_j30(){
        return version_compare(JVERSION, '3.0', '>=');
    }

    public static function greater_than_j50(){
        return version_compare(JVERSION, '5.0', '>=');
    }

    public static function loadFontAwesome(){
        $doc = Factory::getDocument();
        if (self::greater_than_j50()){
            $doc->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');
        }else {
            $doc->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
        }
    }

    /**
     * load mootools
     */
    public static function framework($extras=false, $debug=null){
        HTMLHelper::_('behavior.framework', $extras, $debug);
    }

    public static function jquery($noConflict=true, $debug=null){
        if (self::greater_than_j30()){
            HTMLHelper::_('jquery.framework', $noConflict, $debug);
        }else{
            self::jquery25($noConflict, $debug);
        }
    }

    /**
     * Method to load the jQuery UI JavaScript framework into the document head
     */
    public static function jqueryui(array $components = array('core'), $debug = null)
    {
        if(self::greater_than_j30()) {
            HTMLHelper::_('jquery.ui', $components, $debug);
        } else {
            self::jqueryui25($components, $debug);
        }
    }

    public static function jquery25($noConflict=true, $debug=null){
        // Only load once
        if (!empty(self::$loaded[__METHOD__]))
        {
            return;
        }
        
        //check if jquery is loaded by other extension
        $doc = Factory::getDocument();
        $scripts = $doc->get('_scripts');
        if(count($scripts)) {
            $pattern = '/(^|\/)jquery([-_]*\d+(\.\d+)+)?(\.min)?\.js/i';//is jquery core
            foreach ($scripts as $script => $opts) {
                if(preg_match($pattern, $script)) {
                    return;
                }
            }
        }

        // If no debugging value is set, use the configuration setting
        if ($debug === null)
        {
            $config = Factory::getConfig();
            $debug  = (boolean) $config->get('debug');
        }

        HTMLHelper::_('script', JA_BEHAVIOR_URL.'jquery/jquery.min.js');

        // Check if we are loading in noConflict
        if ($noConflict)
        {
            HTMLHelper::_('script', JA_BEHAVIOR_URL.'jquery/jquery-noconflict.js');
        }
        
        self::$loaded[__METHOD__] = true;

        return;
    }
    
    public static function jqueryui25(array $components = array('core'), $debug = null)
    {
        // Set an array containing the supported jQuery UI components handled by this method
        $supported = array('core');//only support core in J2.5

        // Include jQuery
        self::jquery();

        // If no debugging value is set, use the configuration setting
        if ($debug === null)
        {
            $config = Factory::getConfig();
            $debug  = (boolean) $config->get('debug');
        }

        // Load each of the requested components
        foreach ($components as $component)
        {
            // Only attempt to load the component if it's supported in core and hasn't already been loaded
            if (in_array($component, $supported) && empty(self::$loaded[__METHOD__][$component]))
            {
                HTMLHelper::_('script', JA_BEHAVIOR_URL.'jquery/jquery.ui.' . $component . '.min.js');
                self::$loaded[__METHOD__][$component] = true;
            }
        }

        return;
    }
    
    /**
     * Method to load the Chosen JavaScript framework and supporting CSS into the document head
     */
    public static function jquerychosen($selector = '.advandedSelect', $debug = null)
    {
        if(self::greater_than_j30()) {
            HTMLHelper::_('formbehavior.chosen', $selector, $debug);
        } else {
            self::jquerychosen25($selector, $debug);
        }
    }
    
    public static function jquerychosen25($selector = '.advandedSelect', $debug = nulll)
    {
        if (isset(self::$loaded[__METHOD__][$selector]))
        {
            return;
        }

        // Include jQuery
        self::jquery();

        // Add chosen.jquery.js language strings
        Text::script('JGLOBAL_SELECT_SOME_OPTIONS');
        Text::script('JGLOBAL_SELECT_AN_OPTION');
        Text::script('JGLOBAL_SELECT_NO_RESULTS_MATCH');

        // If no debugging value is set, use the configuration setting
        if ($debug === null)
        {
            $config = Factory::getConfig();
            $debug  = (boolean) $config->get('debug');
        }

        HTMLHelper::_('script', JA_BEHAVIOR_URL.'jquery/chosen/jquery.actual.min.js');
        HTMLHelper::_('script', JA_BEHAVIOR_URL.'jquery/chosen/chosen.jquery.js');
        HTMLHelper::_('stylesheet', JA_BEHAVIOR_URL.'jquery/chosen/chosen.css');
        Factory::getDocument()->addScriptDeclaration("
                jQuery(document).ready(function (){
                    jQuery('" . $selector . "').chosen({
                        disable_search_threshold : 10,
                        allow_single_deselect : true
                    }).change(function(){
                        if(typeof(validate) == 'function') {
                            validate();
                        }
                    });
                });
            "
        );

        self::$loaded[__METHOD__][$selector] = true;

        return;
    }
    
    
    /**
     * Method to load the jQuery Easing
     */
    public static function jqueryeasing($debug = null)
    {
        // Include jQuery
        self::jquery();

        // If no debugging value is set, use the configuration setting
        if ($debug === null)
        {
            $config = Factory::getConfig();
            $debug  = (boolean) $config->get('debug');
        }

        HTMLHelper::_('script', JA_BEHAVIOR_URL.'jquery/jquery.easing.1.3.js');

        return;
    }
}

?>
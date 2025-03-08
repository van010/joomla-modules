<?php
/**
 * $JA#COPYRIGHT$
 */

// Ensure this file is being included by a parent file
defined('_JEXEC') or die( 'Restricted access' );

/**
 * Radio List Element
 *
 * @since      Class available since Release 1.2.0
 */
class JFormFieldJaprofile extends JFormField
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $type = 'Japrofile';	

	protected function getInput(){		
		$path = JURI::root().$this->element['path'];
		
		$extpath = $this->element['extpath'];	
        JHtml::_('script', $extpath . '/assets/elements/japrofile/japrofile.js');
		JHtml::_('stylesheet', $extpath . '/assets/elements/japrofile/japrofile.css');

		$jsonData = array();
		$folder_profiles = array();

		/* Get all profiles name folder from folder profiles */
		$profiles = array();
		$jsonData = array();

        // get in module
        jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$path = JPATH_SITE . DIRECTORY_SEPARATOR . $extpath . DIRECTORY_SEPARATOR . 'profiles';
		if (!JFolder::exists($path)){
			return JText::_('PROFILE_FOLDER_NOT_EXIST');
		}
		$files = JFolder::files($path, '.ini');
		if ($files) {
			foreach ($files as $fname) {
				$fname = substr($fname, 0, -4);

				$f = new stdClass();
				$f->id = $fname;
				$f->title = $fname;

				$profiles[$fname] = $f;
				
				$params = new JRegistry(JFile::read($path . DIRECTORY_SEPARATOR . $fname . '.ini'));
				$jsonData[$fname] = $params->toArray();
			}
		}


		$xmlparams = JPATH_SITE . DIRECTORY_SEPARATOR . $extpath . DIRECTORY_SEPARATOR . 'admin/japrofile/config.xml';
		if (file_exists($xmlparams)) {
			/* For General Form */
			$options = array('control' => 'jaform');
			$paramsForm = JForm::getInstance('jform', $xmlparams, $options);

			$HTML_Profile = JHTML::_('select.genericlist', $profiles, '' . $this->name, 'style="width:150px;"', 'id', 'title', $this->value);
			ob_start();
				require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'japrofile' . DIRECTORY_SEPARATOR . 'japrofile.php';
				$content = ob_get_clean();		
			
			return $content;
		}
	}	
} 
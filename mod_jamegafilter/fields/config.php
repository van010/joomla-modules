<?php

/**
 * $JA#COPYRIGHT$
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\ListField;

FormHelper::loadFieldClass('list');

class JFormFieldConfig extends ListField {

	public $type = 'config';

	/**
	 * Method to get the field option groups.
	 *
	 * @return  array  The field option objects as a nested array in groups.
	 *
	 * @credit Joomla CMS
	 */
	protected function getOptions() {
		
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
						->select('id as value, title as text')
						->from($db->quoteName('#__jamegafilter'))
						->where($db->quoteName('published') . ' = ' . $db->quote('1'));
		$db->setQuery($query);
		try {
			$filterOpt = $db->loadObjectList();
		} catch (RuntimeException $e) {
			$filterOpt = array();
		}

		// Merge any additional groups in the XML definition.
		$options = array_merge(parent::getOptions(), $filterOpt);

		return $options;
	}
}

<?php

/**
 * $JA#COPYRIGHT$
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Form\Field\MenuitemField;

FormHelper::loadFieldClass('menuitem');

class JFormFieldFilter extends MenuitemField {

	public $type = 'filter';

	/**
	 * Method to get the field option groups.
	 *
	 * @return  array  The field option objects as a nested array in groups.
	 *
	 * @credit Joomla CMS
	 */
	protected function getGroups() {
		$groups = array();

		$menuType = $this->menuType;

		// Get the menu items.
		$items = $this->getMenuLinks($menuType, 0, 0, $this->published, $this->language);

		// Build group for a specific menu type.
		if ($menuType) {
			// If the menutype is empty, group the items by menutype.
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
							->select($db->quoteName('title'))
							->from($db->quoteName('#__menu_types'))
							->where($db->quoteName('menutype') . ' = ' . $db->quote($menuType));
			$db->setQuery($query);

			try {
				$menuTitle = $db->loadResult();
			} catch (RuntimeException $e) {
				$menuTitle = $menuType;
			}

			// Initialize the group.
			$groups[$menuTitle] = array();

			// Build the options array.
			foreach ($items as $link) {
				$levelPrefix = str_repeat('- ', max(0, $link->level - 1));

				// Displays language code if not set to All
				if ($link->language !== '*') {
					$lang = ' (' . $link->language . ')';
				} else {
					$lang = '';
				}

				$groups[$menuTitle][] = HTMLHelper::_('select.option', $link->value, $levelPrefix . $link->text . $lang, 'value', 'text', in_array($link->type, $this->disable)
				);
			}
		}
		// Build groups for all menu types.
		else {
			// Build the groups arrays.
			foreach ($items as $menu) {
				// Initialize the group.
				$groups[$menu->title] = array();

				// Build the options array.
				foreach ($menu->links as $link) {
					$levelPrefix = str_repeat('- ', $link->level - 1);

					// Displays language code if not set to All
					if ($link->language !== '*') {
						$lang = ' (' . $link->language . ')';
					} else {
						$lang = '';
					}

					$groups[$menu->title][] = HTMLHelper::_('select.option', $link->value, $levelPrefix . $link->text . $lang, 'value', 'text', in_array($link->type, $this->disable)
					);
				}
			}
		}

		// Merge any additional groups in the XML definition.
		$groups = array_merge(parent::getGroups(), $groups);

		return $groups;
	}

	/**
	 * Get a list of menu links for one or all menus.
	 *
	 * @param   string   $menuType   An option menu to filter the list on, otherwise all menu links are returned as a grouped array.
	 * @param   integer  $parentId   An optional parent ID to pivot results around.
	 * @param   integer  $mode       An optional mode. If parent ID is set and mode=2, the parent and children are excluded from the list.
	 * @param   array    $published  An optional array of states
	 * @param   array    $languages  Optional array of specify which languages we want to filter
	 *
	 * @return  array
	 *
	 * @credit Joomla CMS
	 */
	function getMenuLinks($menuType = null, $parentId = 0, $mode = 0, $published = array(), $languages = array()) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
						->select('DISTINCT(a.id) AS value,
					  a.title AS text,
					  a.alias,
					  a.level,
					  a.menutype,
					  a.type,
					  a.published,
					  a.template_style_id,
					  a.checked_out,
					  a.language,
					  a.lft')
						->from('#__menu AS a');

		if (Multilanguage::isEnabled()) {
			$query->select('l.title AS language_title, l.image AS language_image')
							->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');
		}

		// Filter by the type
		if ($menuType) {
			$query->where('(a.menutype = ' . $db->quote($menuType) . ' OR a.parent_id = 0)');
		}

		if ($parentId) {
			if ($mode == 2) {
				// Prevent the parent and children from showing.
				$query->join('LEFT', '#__menu AS p ON p.id = ' . (int) $parentId)
								->where('(a.lft <= p.lft OR a.rgt >= p.rgt)');
			}
		}

		if (!empty($languages)) {
			if (is_array($languages)) {
				$languages = '(' . implode(',', array_map(array($db, 'quote'), $languages)) . ')';
			}

			$query->where('a.language IN ' . $languages);
		}

		if (!empty($published)) {
			if (is_array($published)) {
				$published = '(' . implode(',', $published) . ')';
			}

			$query->where('a.published IN ' . $published);
		}

		$query->where('a.published != -2');
		$query->where($db->quoteName('link') . ' LIKE ' . $db->quote('%index.php?option=com_jamegafilter&view=default%'));
		$query->order('a.lft ASC');

		// Get the options.
		$db->setQuery($query);
		
		$app = Factory::getApplication();

		try {
			$links = $db->loadObjectList();
		} catch (RuntimeException $e) {
			$app->enqueueMessage($e->getMessage(), 'warning');
			return false;
		}

		if (empty($menuType)) {
			// If the menutype is empty, group the items by menutype.
			$query->clear()
							->select('*')
							->from('#__menu_types')
							->where('menutype <> ' . $db->quote(''))
							->order('title, menutype');
			$db->setQuery($query);

			try {
				$menuTypes = $db->loadObjectList();
			} catch (RuntimeException $e) {
				$app->enqueueMessage($e->getMessage(), 'warning');
				return false;
			}

			// Create a reverse lookup and aggregate the links.
			$rlu = array();

			foreach ($menuTypes as &$type) {
				$rlu[$type->menutype] = & $type;
				$type->links = array();
			}

			// Loop through the list of menu links.
			foreach ($links as &$link) {
				if (isset($rlu[$link->menutype])) {
					$rlu[$link->menutype]->links[] = & $link;

					// Cleanup garbage.
					unset($link->menutype);
				}
			}

			return $menuTypes;
		} else {
			return $links;
		}
	}

}

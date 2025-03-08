<?php

/**
 * $JA#COPYRIGHT$
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Component\ComponentHelper;

class ModJamegafilterHelper {

	public $config;
	public $jstemplate;
	public $url;

	function display($params) {
		$app = Factory::getApplication();
		
		$isComInstalled = ComponentHelper::isInstalled('com_jamegafilter');
		if (!$isComInstalled) {
			$app->enqueueMessage(Text::_('MOD_JAMEGAFILTER_COMPONENT_IS_NOT_INSTALLED'), 'error');
			return;
		}

		$menu = Factory::getApplication()->getMenu();
		$item = $menu->getItem($params->get('filter', 0));
		$query = $item->query;
		if($params->get('filter_config', '')){
			$query['id'] = $params->get('filter_config', '');
		}
		if (empty($query['id'])) {
			$app->enqueueMessage(Text::_('MOD_JAMEGAFILTER_PLEASE_CHOOSE_FILTER_MENU'), 'error');
			return;
		}
		
		$q = 'SELECT * FROM #__jamegafilter WHERE id=' . $query['id'];
		$db = Factory::getDbo()->setQuery($q);
		$page = $db->loadObject();
		if (empty($page)) {
			$app->enqueueMessage(Text::_('MOD_JAMEGAFILTER_FILTER_PAGE_IS_NOT_EXIST'), 'error');
			return;
		}

		$isPluginEnabled = PluginHelper::isEnabled('jamegafilter', $page->type);
		if (!$isPluginEnabled) {
			$app->enqueueMessage(Text::_('MOD_JAMEGAFILTER_FILTER_PLUGIN_IS_NOT_ENABLED_OR_INSTALLED'), 'error');
			return;
		}
		
		$num = JaMegafilterHelper::hasMegafilterModule();
		if ($num > 1) {
			$app->enqueueMessage(Text::_('MOD_JAMEGAFILTER_FILTER_EACH_PAGE_MUST_HAS_MAXIMUM_ONE_MEGAFILTER_MODULE'), 'error');
			return;
		}
		
		
		$input = $app->input;
		if ($input->get('option') !== 'com_jamegafilter') {
			$this->loadAssets();
		}

		$input->set('jalayout', $query['jalayout']);

		require_once JPATH_SITE . '/components/com_jamegafilter/views/default/view.html.php';
		$view = new JaMegaFilterViewDefault();
		$view->_addCss($page->type);
		$view->_addLayoutPath($page->type);

		$this->config = $view->_getFilterConfig((array) $page);
		$this->config->isModule = true;
		$this->config->Moduledirection = $params->get('direction', null);
		$this->config->url = Route::_('index.php?Itemid=' . $params->get('filter', 0));
		$this->jstemplate = $view->_loadJsTemplate();

		if ($page->type !== 'blank') {
			PluginHelper::importPlugin('jamegafilter');
      		$dispatcher = Factory::getApplication();
      		$dispatcher->triggerEvent('onBeforeDisplay' . ucfirst($page->type) . 'Items', array($this->jstemplate, $this->config, (array) $page));
		}
	}

	function loadAssets() {
		require_once __DIR__ . '/assets/assets.php';
	}
}

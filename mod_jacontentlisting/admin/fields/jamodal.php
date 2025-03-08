<?php
/**
 *------------------------------------------------------------------------------.
 *
 * @copyright     Copyright (C) 2004-2021 JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github
 *                & Google group to become co-author)
 *------------------------------------------------------------------------------
 */

// Ensure this file is being included by a parent file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\FormField as JFormField;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Filesystem\Folder;


if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Radio List Element.
 *
 * @since      Class available since Release 1.2.0
 */
class JFormFieldJamodal extends JFormField
{
    /**
     * Element name.
     *
     * @var string
     */
    protected $type = 'Jamodal';

    protected function getInput()
    {
        HTMLHelper::_('script', 'modules/mod_jacontentlisting/admin/assets/js/jamodal.js');
        HTMLHelper::_('stylesheet', 'modules/mod_jacontentlisting/admin/assets/css/jamodal.css');
        if (!$this->value) {
            $this->value = $this->element['default'];
        }
        $layoutFielData = self::getConfig();
        if(version_compare(JVERSION, '4', 'ge')){
            Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('bootstrap.modal');
            $attr = ' data-bs-toggle="modal" data-bs-target="#modal-'.$this->id.'-selected" ';
        }else{
            $attr = ' data-toggle="modal" data-target="#modal-'.$this->id.'-selected" ';
        }
        $html = '<input type="text" id="'.$this->id.'" name="'.$this->name.'" value="'.$this->value.'" class="'.$this->class.'" readonly="true" />
	        <button type="button"'.$attr.'class="btn btn-primary">'.Text::_('JSELECT').'</button>';
        $html .= LayoutHelper::render('tmpl.jamodal', $layoutFielData, JPATH_ROOT.'/modules/mod_jacontentlisting/admin');

        return $html;
    }

    public function getAllLayouts($type)
    {
        // Build the template and base path for the layout
        $paths = [];
      $app = Factory::getApplication();
      $input = $app->input;
      $modId = $input->get('id');

      if (is_file(JPATH_ROOT . "/modules/mod_jacontentlisting/cache/$modId-jasource.json")) {
          $jasource = file_get_contents(JPATH_ROOT . "/modules/mod_jacontentlisting/cache/$modId-jasource.json");
          $jasource = json_decode($jasource);
      }else{
          $jasource = json_decode('{"sources": "content"}');
      }

      if ($type == 'layouts'){
        $paths[Uri::root(true).'/modules/mod_jacontentlisting/tmpl/'.$type.'/'] =
          JPATH_ROOT.'/modules/mod_jacontentlisting/tmpl/'.$type.'';
      }else{
        switch ($jasource->sources){
          case 'eshop':
          case 'hikashop':
          case 'jshopping':
          case 'docman':
          case 'vm':
            $paths[Uri::root(true).'/modules/mod_jacontentlisting/tmpl/'.$type.'/ecommerces/'] =
              JPATH_ROOT.'/modules/mod_jacontentlisting/tmpl/'.$type.'/ecommerces';
            break;
          case 'k2':
          case 'easyblog':
          case 'content':
            $paths[Uri::root(true).'/modules/mod_jacontentlisting/tmpl/'.$type.'/contents/'] =
              JPATH_ROOT.'/modules/mod_jacontentlisting/tmpl/'.$type.'/contents';
            break;
          default:
            $paths[Uri::root(true).'/modules/mod_jacontentlisting/tmpl/'.$type.'/'] =
              JPATH_ROOT.'/modules/mod_jacontentlisting/tmpl/'.$type.'';
            break;
        }
      }
        // template folders
        $tpls = Folder::folders(JPATH_ROOT.'/templates/');
        foreach ($tpls as $tpl) {
            $paths[Uri::root(true).'/templates/'.$tpl.'/html/mod_jacontentlisting/'.$type.'/'] = JPATH_ROOT.'/templates/'.$tpl.'/html/mod_jacontentlisting/'.$type.'';
        }
        $allLayouts = [];
        foreach ($paths as $uri => $path) {
            $dirs = array_filter(glob($path.'/*'), 'is_dir');
            if (!empty($dirs)) {
                $allLayouts[$uri] = array_map('basename', $dirs);
            }
        }
        $layoutData = [];
        if ($allLayouts) {
            foreach ($allLayouts as $uri => $layout) {
                foreach ($layout as $fname) {
                    $tempLayout = [
                        'name' => $fname,
                        'config' => $uri.$fname.'/info.xml',
                        'thumb' => $uri.$fname.'/thumb.png',
                    ];
                    $layoutData[$fname] = $tempLayout;
                }
            }
        }

        return $layoutData;
    }

    public function getHeadingLayouts()
    {
        $layoutHeading = [];
        // Build the template and base path for the layout
        $paths = JPATH_ROOT.'/modules/mod_jacontentlisting/admin/assets/images/heading/';
        $files = array_filter(glob($paths.'*'), 'is_file');
        foreach ($files as $file) {
            $fname = str_replace('heading-', '', basename($file, '.png'));
            $f = [
                'name' => $fname,
                'thumb' => Uri::root(true).'/modules/mod_jacontentlisting/admin/assets/images/heading/'.basename($file),
            ];
            $layoutHeading[] = $f;
        }

        return $layoutHeading;
    }

    protected function getConfig()
    {
        switch ($this->element['path']) {
            case 'layout':
                $layouts = self::getAllLayouts('layouts');
                break;
            case 'heading':
               $layouts = self::getHeadingLayouts();
                break;
            case 'item':
            case 'feature_item':
               $layouts = self::getAllLayouts('items');
                break;
            case 'detail':
               $layouts = self::getAllLayouts('details');
                break;
            default:
                $layouts = [];
                break;
        }
        $layoutFielData = [];
        $layoutFielData['id'] = $this->id;
        $layoutFielData['name'] = $this->name;
        $layoutFielData['label'] = $this->element['label'];
        $layoutFielData['modalId'] = 'modal-'.$this->id;
        $layoutFielData['value'] = $this->value;
        $layoutFielData['data'] = $layouts;

        return $layoutFielData;
    }
}

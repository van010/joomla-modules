<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Form\FormField;
use \Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filesystem\Folder;

class JFormFieldIconset extends FormField
{
	protected $type = "iconset";
	
	public function getInput()
	{
		HTMLHelper::_('behavior.core');
		$doc = Factory::getDocument();
		$paramName = $this->name;
		$url = Uri::root(true) . '/modules/mod_ja_weather/asset/';
		
		$iconSets = Folder::folders(JPATH_ROOT . '/modules/mod_ja_weather/set-icons');
		$iconData = array();
		foreach ($iconSets as $set) {
			$chunk = Folder::files(JPATH_ROOT . '/modules/mod_ja_weather/set-icons/' . $set);
			if (!empty($chunk)){
				$files = array_splice($chunk, 0, 20);
				$item = array(
					'name' => $set,
					'files' => $files,
				);
				$iconData[] = $item;
			}
		}
		
		$doc->addScriptOptions('jaweather_icon_data', $iconData);
		$doc->addScript($url . 'js/' . 'iconset.js');
		
		$html = "";
		$html .= '<div id="iconset" class="weather-iconset">';
		$html .= '<select id="jaform_params_ptype" name="'. $paramName .'" class="form-select valid form-control-success"
		aria-describedby="'. $paramName .'-desc" aria-invalid="false">';
		
		foreach($iconSets as $key => $path){
			$childFolder = explode($this->validate . '/', $path);
			$childFolder = end($childFolder);
			$chunk_ = Folder::files(JPATH_ROOT . '/modules/mod_ja_weather/set-icons/' . $childFolder);
			if (!empty($chunk_) && $childFolder !== 'label-icons'){
				if ($this->value === $childFolder){
					$html .= "<option value=\"{$this->value}\" selected='selected' data-index=\"{$this->value}\">{$this->showFolder($this->value)}</option>";
				}else{
					$html .= "<option value=\"{$childFolder}\" data-index=\"{$childFolder}\">{$this->showFolder($childFolder)}</option>";
				}
			}
		}
		$html .= '</select>';
		$html .= '<div class="icons-set-preview"></div>';
		$html .= '</div>';
		
		return $html;
	}
	private function showFolder($folder){
		$folder = str_replace('-', ' ', $folder);
		return ucwords($folder);
	}
}
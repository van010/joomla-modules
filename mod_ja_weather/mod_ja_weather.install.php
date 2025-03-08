<?php

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Filesystem\Folder;

class mod_ja_weatherInstallerScript{
	public function postflight(){
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		
		$src = JPATH_ROOT . '/modules/mod_ja_weather/asset/img/';
		$dest = JPATH_ROOT . '/images/ja-weather/background/';
		Folder::create($dest);
		Folder::copy($src, $dest, '', true);
	}
}
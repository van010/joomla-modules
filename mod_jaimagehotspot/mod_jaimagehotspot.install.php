<?php
/**
 * ------------------------------------------------------------------------
 * JA Image Hotspot Module for Joomla 2.5 & 3.4
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Filesystem\Folder;

class mod_jaimagehotspotInstallerScript
{
    public function postflight()
    {
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        $src = JPATH_ROOT . '/modules/mod_jaimagehotspot/assets/images/sample/';
        $dest = JPATH_ROOT . '/images/joomlart/map/';
				Folder::create($dest);
        Folder::copy($src, $dest, '', true);
        # copy($src . 'worldmap.png', $dest . 'worldmap.png');
        # copy($src . 'worldmap2.png', $dest . 'worldmap2.png');
    }
}
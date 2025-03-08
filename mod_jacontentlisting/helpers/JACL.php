<?php
/**
 *------------------------------------------------------------------------------
 * @package       Module JA Content Listing for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2021 JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github
 *                & Google group to become co-author)
 *------------------------------------------------------------------------------
 */
namespace JACL;
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Filesystem\Path;

class JACL
{
    public $helper;

    public function __construct()
    {
    }

    public static function getInstance($type = 'content', $params = [])
    {
        self::addIncludePath(__DIR__.'/adapter');

        $type = strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $type));

        $class = __NAMESPACE__.'\\Adapter\\'.ucfirst($type).'Helper';

        if (!class_exists($class)) {
            // Search for the class file in the Cache include paths.
            \JLoader::import('joomla.filesystem.path');

            $path = Path::find(self::addIncludePath(), strtolower($type).'.php');

            if ($path !== false) {
                \JLoader::register($class, $path);
            }

            // The class should now be loaded
            if (!class_exists($class)) {
                throw new \RuntimeException('Unable to load Content helper: '.$type, 500);
            }
        }

        return new $class($params);
    }

    /**
     * Add a directory where Cache should search for controllers. You may either pass a string or an array of directories.
     *
     * @param array|string $path a path to search
     *
     * @return array An array with directory elements
     *
     * @since   1.7.0
     */
    public static function addIncludePath($path = '')
    {
        static $paths;

        if (!isset($paths)) {
            $paths = [];
        }

        if (!empty($path) && !in_array($path, $paths)) {
            \JLoader::import('joomla.filesystem.path');
            array_unshift($paths, Path::clean($path));
        }

        return $paths;
    }
}

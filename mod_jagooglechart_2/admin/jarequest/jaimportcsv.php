<?php
/**
 * ------------------------------------------------------------------------
 * JA Google Chart 2 Module for J25 & J34
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;

class jaimportcsv{   
    public function import(){
		$return = array('status'=>0, 'data'=>'', 'message'=>Text::_('MOD_JA_GOOGLE_CHART_ERROR'));
		$jinput = Factory::getApplication()->input;
		$file = $jinput->files->get('file');

		if ($file['error'] > 0) {
			$retrun['message'] = $file['error'];
			return $retrun;
		}

		$ext = File::getExt($file['name']);

		if(strtolower($ext) != 'csv'){
			$return['message'] = Text::_('MOD_JA_GOOGLE_CHART_FILE_TYPE_INVALID');
			return $return;
		}
		
		$return['status'] = 1;
		$return['data'] = trim(file_get_contents($file["tmp_name"]));
		$return['message'] = Text::sprintf('MOD_JA_GOOGLE_CHART_IMPORT_CSV_DONE', $file["name"], $file["type"], $file["size"] / 1024);

		return $return;
	}
}
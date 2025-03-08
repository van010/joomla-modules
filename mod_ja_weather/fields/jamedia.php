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
	
	defined('_JEXEC') or die( 'Restricted access' );
	
	use \Joomla\CMS\Form\Field\MediaField;
	
	class JFormFieldJamedia extends MediaField
	{
		protected $type = 'Media';
		protected static $initialised = false;
	}
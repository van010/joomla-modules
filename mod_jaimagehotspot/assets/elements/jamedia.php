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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Form\Field\MediaField;

require_once(JPATH_ROOT . '/modules/mod_jaimagehotspot/assets/jabehavior.php');


class JFormFieldJamedia extends MediaField
{
    protected $type = 'Media';
    protected static $initialised = false;

    public function renderField($options = array())
    {
        $options['hiddenLabel'] = true;

        return parent::renderField($options);
    }
    
    protected function getInput()
    {
        HTMLHelper::_('jquery.framework');
        HTMLHelper::script('modules/mod_jaimagehotspot/assets/elements/assets/js/preview.js');

        $html = parent::getInput();
        $attr = array(
            'id' => $this->id . '_preview',
            'class' => 'media_preview',
            'style' => 'width:100%',
        );
        
        $src = '';
        if ($this->value && file_exists(JPATH_ROOT . '/' . explode('#', $this->value)[0])) {
            $src = Uri::root() . $this->value;
        }

        $img = HTMLHelper::image($src, Text::_('JLIB_FORM_MEDIA_PREVIEW_ALT'), $attr);
        $previewImg = '<div id="' . $this->id . '_preview_img"' . ($src == '' ? '' : 'style="display;"') . '>' . $img . '</div>';
        $previewImgEmpty = '<div id="' . $this->id . '_preview_empty"' . ($src != '' ? 'style="display:none"' : '') . '>'
            . Text::_('JLIB_FORM_MEDIA_PREVIEW_EMPTY') . '</div>';
        $html .= '<div class="media-preview fltlft" style="clear:both;">';
        $html .= ' ' . $previewImgEmpty;
        $html .= ' ' . $previewImg;
        $html .= '</div>';
        return $html;
    }
}
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

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldJaimportcsv extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'jaimportcsv';

	/**
	 * The number of rows in textarea.
	 *
	 * @var    mixed
	 * @since  3.2
	 */
	protected $rows;

	/**
	 * The number of columns in textarea.
	 *
	 * @var    mixed
	 * @since  3.2
	 */
	protected $columns;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'rows':
			case 'columns':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'rows':
			case 'columns':
				$this->name = (int) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->rows    = isset($this->element['rows']) ? (int) $this->element['rows'] : false;
			$this->columns = isset($this->element['cols']) ? (int) $this->element['cols'] : false;
		}

		return $return;
	}

	/**
	 * Method to get the textarea field input markup.
	 * Use the rows and columns attributes to specify the dimensions of the area.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
        $document = Factory::getDocument();
		// Translate placeholder text
		$hint = $this->translateHint ? Text::_($this->hint) : $this->hint;

		// Initialize some field attributes.
		$class        = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$disabled     = $this->disabled ? ' disabled' : '';
		$readonly     = $this->readonly ? ' readonly' : '';
		$columns      = $this->columns ? ' cols="' . $this->columns . '"' : '';
		$rows         = $this->rows ? ' rows="' . $this->rows . '"' : '';
		$required     = $this->required ? ' required aria-required="true"' : '';
		$hint         = $hint ? ' placeholder="' . $hint . '"' : '';
		$autocomplete = !$this->autocomplete ? ' autocomplete="off"' : ' autocomplete="' . $this->autocomplete . '"';
		$autocomplete = $autocomplete == ' autocomplete="on"' ? '' : $autocomplete;
		$autofocus    = $this->autofocus ? ' autofocus' : '';
		$spellcheck   = $this->spellcheck ? '' : ' spellcheck="false"';

		// Initialize JavaScript field attributes.
		$onchange = $this->onchange ? ' onchange="' . $this->onchange . '"' : '';
		$onclick = $this->onclick ? ' onclick="' . $this->onclick . '"' : '';

        $path = Uri::root().$this->element['path'];
		// Including fallback code for HTML5 non supported browsers.
		if (version_compare(JVERSION, "4.0",'ge')) {
			HTMLHelper::_('behavior.core');
			HTMLHelper::_('bootstrap.modal');
		}else{
			HTMLHelper::_('behavior.framework');
			HTMLHelper::_('behavior.modal');
			HTMLHelper::_('script', 'system/html5fallback.js', false, true);
		}
		
        $document->addStyleSheet(Uri::root().'modules/mod_jagooglechart_2/asset/elements/jaimportcsv/style.css');


		$js = "function jaImportForm(){
			var form = jaBuildForm();
			if(!jQuery('.ja-import-csv-modal').length){
				jQuery(form).appendTo('body');
			}
			// jQuery('#ja-import-csv-layout').addClass('modal fade in show');
			// jQuery('#ja-import-csv-layout').show();
			jQuery('.ja-import-csv-modal').modal('show');

		}

		function jaBuildForm(){
			var html = '';

			html += ' <div class=\"ja-import-csv-modal modal fade\">';
			html += ' <div class=\"modal-dialog\">';
			html += ' <div class=\"modal-content\">';
			html += '<div id=\"ja-import-csv-layout\">';
			html += '<div id=\"ja-import-csv\">';
				html += '<fieldset class=\"panelform\" >';
					html += '<legend>".Text::_("MOD_JA_GOOGLE_CHART_IMPORT_FROM_CSV")."</legend>';
					html += '<form id=\"ja-import-csv-form\" action=\"\" method=\"POST\" enctype=\"multipart/form-data\" >';
						html += '<div class=\"ja-import-csv-input\">';
						html += '<input type=\"file\" name=\"file\" value=\"\" id=\"csv_file\" />';
						html += '</div>';
						html += '<div id=\"ja-import-csv-progress\">';
							html += '<div id=\"ja-import-csv-bar\"></div>';
							html += '<div id=\"ja-import-csv-percent\">0%</div>';
						html += '</div>';
						html += '<br />';
						html += '<input type=\"submit\" onclick=\"jaImportCsvFormSubmit(this.form)\" value=\"".Text::_("MOD_JA_GOOGLE_CHART_LOADING_CSV_BTN")."\" />';
					html += '</form>';
					html += '<div id=\"ja-import-csv-result\">';
						html += '<fieldset>';
							html += '<legend>".Text::_("MOD_JA_GOOGLE_CHART_DATA_LOADED")."</legend>';
							html += '<span id=\"ja-import-csv-error-msg\" ></span>';
							html += '<textarea cols=\"25\" rows=\"10\" id=\"ja-import-csv-data\"></textarea>';
							html += '<input type=\"button\" onclick=\"jaImportCsv()\" value=\"".Text::_("MOD_JA_GOOGLE_CHART_IMPORT_BTN")."\" />';
						html += '</fieldset>';
					html += '</div>';
				html += '</fieldset>';
			html += '</div>';
			html += '</div>';
			html += '</div>';
			html += '</div>';
			html += '</div>';

			return html;
		}

	    function jaImportCsvFormSubmit(form){
		    var bar = document.getElementById('ja-import-csv-bar')
			var percent = document.getElementById('ja-import-csv-percent')
			var result = document.getElementById('ja-import-csv-data')
			var percentValue = '0%';

			var fileInput = document.getElementById('csv_file');
			var form = document.getElementById('ja-import-csv-form');

			form.addEventListener('submit', function(evt) {
			evt.preventDefault();

			// Ajax upload
			var file = fileInput.files[0];

			var fd = new FormData();
			fd.append('file', file);
			var xhr = new XMLHttpRequest();
			xhr.open('POST', location.href+'&jarequest=jaimportcsv&jatask=import', true);

			xhr.upload.onprogress = function(e) {
			  if (e.lengthComputable) {
				var percentValue = (e.loaded / e.total) * 100 + '%';
				percent.innerHTML  = percentValue;
				bar.setAttribute('style', 'width: ' + percentValue);
			  }
			};

			xhr.onload = function() {
			  if (this.status == 200) {
				var response = JSON.parse(this.response);
				if(response.status==1){
					document.getElementById('ja-import-csv-error-msg').innerHTML = response.message;
					result.innerHTML = response.data;
					percent.innerHTML  = '100%';
					bar.setAttribute('style', 'width: 100%;' );
				}else{
					document.getElementById('ja-import-csv-error-msg').innerHTML = response.message;
					result.innerHTML = '';
				}
			  };
			};

			xhr.send(fd);

		  }, false);
		}

		function jaImportCsv(){
			var data = document.getElementById('ja-import-csv-data').value;
			if(!data){
			    document.getElementById('ja-import-csv-error-msg').innerHTML = 'Data null';
			    return;
			}
			document.getElementById('jform_params_data_input').value = data;
			jQuery('.ja-import-csv-modal').modal('hide');
			jQuery('.ja-import-csv-modal').remove();
		}
		jQuery(document).ready(function($){
			$(document).on('hidden.bs.modal','.ja-import-csv-modal',function(){
				$(this).remove();
			});
		})
		";

		$document->addScriptDeclaration($js);
		
		
		$html = '';

		$html .= '<textarea name="' . $this->name . '" id="' . $this->id . '"' . $columns . $rows . $class
			. $hint . $disabled . $readonly . $onchange . $onclick . $required . $autocomplete . $autofocus . $spellcheck . ' >'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';
		$html .= '<br />';
		$html .= '<button type="button" class="img-btn" onclick="jaImportForm(); return false;">'.Text::_("MOD_JA_GOOGLE_CHART_IMPORT_CSV_BTN").'</button>';
		$html .= '<div id="ja-import-csv-layout" style="display: none;"></div>';
		return $html;
	}
}

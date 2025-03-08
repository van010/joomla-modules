<?php
/**
 * $JA#COPYRIGHT$
 */
 
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.form.formfield');


class JFormFieldJagallery extends JFormField {
    protected $type = 'Jagallery';
    
    protected function getInput() {
		Jhtml::_('behavior.modal');		
		Jhtml::_('stylesheet', JURI::root() . 'modules/' . $this->form->getValue('module') . '/assets/elements/jagallery/style.css');
	
		$jaGalleryId = $this->id ;
		$params = $this->form->getValue('params');
		if(!isset($params)){
			$params = new stdClass();
			$params->folder = '';
		}
		if(!isset($params->folder)){
			$params->folder = '';
		}else{
			if(substr(trim($params->folder), -1) != '/'){
				$params->folder = trim($params->folder) . '/';
			}
		}
		//Check data format && convert it to json data if it is older format		
		$updateFormatData = 0;
		
		if($this->value && ! $this->isJson($this->value)){
			$this->value = $this->convertFormatData($this->value);
			if(isset($this->element["updatedata"]) && $this->element["updatedata"]){
				$updateFormatData = 1;
			}
		}		
		//Create element
		$button = '<input type="button" id="jaGetImages" value="'.JText::_("JA_GET_IMAGES").'" style="display: none;" /><br /><div id="listImages" style="width: 100%; overflow: hidden;"></div>';
		$button .= '<textarea style="width: 75%; display: none;" rows="6" cols="75" name="' . $this->name . '" id="' . $jaGalleryId . '" >'. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') .'</textarea>';
		$js = ' 
		var current_jagallery = \''.addslashes($this->value).'\';
		var current_jafolder = "'.$params->folder.'";
		var autoUpdate = '.$updateFormatData.';
		jQuery(document).ready(function(){			 
			jQuery("#jaGetImages").click(function(){
				jaListImages();
			});
			jaListImages();			
			if(autoUpdate){
				Joomla.submitbutton("module.apply");
			}
		});
		
		function jaListImages(){
			var folder_path = jQuery("#jform_params_folder").val();					
			if(folder_path == ""){
				alert("'.JText::_("FOLDER_PATH_REQUIRED").'");
				return;
			}
			orderby = jQuery("#jform_params_source_images_orderby").val();
			sortby = jQuery("#jform_params_source_images_sort").val();
			query = "jarequest=images&path="+folder_path+"&jatask=loadImages&orderby="+orderby+"&sortby="+sortby;
			jQuery.ajax({
				url: location.href,
				data: query,
				type: "post",
				beforeSend: function(){
					jQuery("#listImages").html("<img src=\"'.JURI::root().'modules/mod_jaslideshow/assets/elements/jagallery/loading.gif\" width=\"30\" height=\"30\" />");
				},
				success: function(responseJSON){
						var data = jQuery.parseJSON(responseJSON);
						if (!data.success) {
							jQuery("#'.$jaGalleryId.'").val("");
							jQuery("#listImages").html("<strong style=\'color: red\'>'.JText::_("FOLDER_EMPTY").'</strong>");
							return;	
						}
						else {									
							jaupdateImages(data.images, "#listImages");
							if(folder_path === current_jafolder && current_jagallery != ""){
								var current_data = jQuery.parseJSON(current_jagallery);
								for(var i=0; i<current_data.length; i++){
									for(var j=0; j<data.images.length; j++){
										if(current_data[i].image == data.images[j].image){
											if(typeof(current_data[i].title) !== "undefined"){
												data.images[j].title 		= current_data[i].title;
											}
											if(typeof(current_data[i].link) !== "undefined"){
												data.images[j].link 		= current_data[i].link;
											}
											if(typeof(current_data[i].description) !== "undefined"){		
												data.images[j].description 	= current_data[i].description;
											}
											if(typeof(current_data[i].show) !== "undefined"){
												data.images[j].show 		= current_data[i].show;
											}
											break;
										}
									}
								}
							}
							jaupdateImages(data.images, "#listImages");
						}

				}					
			});
			
			return false;
		}
		
		function jaupdateImages(images, boxID){
			var data = "";
			if(images.length){
				for(var i=0; i<images.length; i++){
					var showImage = "";
					if(images[i].show === true || images[i].show === "true"){
						showImage = "checked";
					}
					data += "<div class=\'img-element\' style=\'width: 100px; height: 150px; float: left; margin: 0 5px;\'>";
					data += 	"<img src=\'"+ encodeURI(images[i].imageSrc) +"\' style=\'max-width: 100px; max-height: 100px;\' />";
					data += 	"<br />";
					data += 	"<span style=\'float: left; display: block; text-align: center\'>";	
					data += 	"'.JText::_("JSHOW").' <input style=\'margin:0 auto;\' type=\'checkbox\' value=\'" + images[i].image + "\' "+showImage+" onchange=\'showImage(this)\' />";	
					data += 	"</span>";
					data += 	"<span onclick=\'jaFormpopup(\"#img-element-data-form\", " + i + ", \"" + images[i].image + "\"); return false;\' class=\'img-btn\' style=\'float: right; text-align: center; display: block; cursor: pointer;\'>'.JText::_("EDIT").' </span>";
					data += "</div>";
				}
				data += "<div id=\'img-element-data-form\' style=\'display: none;\'></div>";
			}
			jQuery(boxID).html(data);
			jQuery("#'.$jaGalleryId.'").val(JSON.stringify(images));			
		}
		
		function showImage(el){
			var showImage = jQuery(el).is(\':checked\');
			var data = jQuery.parseJSON(jQuery("#'.$jaGalleryId.'").val());
			
			if(!data){ 
				data = [];
			}
			if(data.length  > 0){
				for(var i = 0; i<data.length; i++){
					if(data[i]["image"] == jQuery(el).val()){										
						data[i]["show"] = showImage;
						break;
					}					
				}				
			}
			jQuery("#'.$jaGalleryId.'").val(JSON.stringify(data));
		}
		
		function jaFormpopup(el, key, imgname){
			var form = jadataForm(key, imgname);			
			jQuery(el).append(form);
			SqueezeBox.open($("img-element-data-form-"+key),{
                handler:"adopt",
                size:{
                    x:800,
                    y:290
                }
            });
			//update data for image form
			var data = jQuery("#'.$jaGalleryId.'").val();
			var jaimg = new Object();
			jaimg.title = "";
			jaimg.link = "";
			jaimg.description = "";			
			//query = "jarequest=images&task=validData&imgname="+imgname+"&data="+data;
			jQuery.ajax({
				url: location.href,
				data: {jarequest:"images", jatask:"validData", imgname:imgname, data:data},
				type: "post",				
				success: function(responseJSON){					
					var jaResponse = jQuery.parseJSON(responseJSON);					
					jQuery("#img-element-data-form-"+key).find("#imgtitle").val(jaResponse.title);
					jQuery("#img-element-data-form-"+key).find("#imglink").val(jaResponse.link);
					jQuery("#img-element-data-form-"+key).find("#imgdescription").val(jaResponse.description);					
				}					
			});
		}
		
		function jaCloseImgForm(key){
			SqueezeBox.close($("img-element-data-form-"+key));
		}
		
		function jaUpdateImgData(key, imgname){
			var title = jQuery("#img-element-data-form-"+key).find("#imgtitle").val();
			var link = jQuery("#img-element-data-form-"+key).find("#imglink").val();
			var description = jQuery("#img-element-data-form-"+key).find("#imgdescription").val();
			var data = jQuery.parseJSON(jQuery("#'.$jaGalleryId.'").val());
			
			if(!data){ data = [];}
			
			if(data.length  > 0){
				var found = false;

				for(var i = 0; i<data.length; i++){				
					if(data[i]["image"] == imgname){										
						data[i]["title"] = title;	
						data[i]["link"] = link;	
						data[i]["description"] = description;	
						
						found = true;
						break;
					} 				
				}

				if(!found){
    				data_add = new Object();
    				data_add["image"] = imgname;		
	    			data_add["title"] = title;	
					data_add["link"] = link;	
					data_add["description"] = description;
					data.push(data_add);
				}
			} else {
				data_add = new Object();	
				data_add["image"] = imgname;		
    			data_add["title"] = title;	
				data_add["link"] = link;	
				data_add["description"] = description;
				data.push(data_add);
    		}
			
			jQuery("#'.$jaGalleryId.'").val(JSON.stringify(data));
			
			jaCloseImgForm(key);
		}
		
		function jadataForm(key, imgname){
			
			//create form for image data
			var html = "";		
			html += "<div id=\'img-element-data-form-"+key+"\' class=\'img-element-data-form\'>";
				html += "<fieldset class=\'panelform\' >";	
					html += "<ul>";
						html += "<li>";
							html += "<label>'.JText::_("TITLE").'</label>";
							html += "<input type=\'text\' name=\'imgtitle\' id=\'imgtitle\' value=\'\' size=\'50\' />";
						html += "</li>";			
						html += "<li>";
							html += "<label>'.JText::_("LINK").'</label>";
							html += "<input type=\'text\' name=\'imglink\' id=\'imglink\' value=\'\' size=\'50\' />";
						html += "</li>";
						html += "<li>";
							html += "<label>'.JText::_("DESCRIPTION").'</label>";
							html += "<textarea rows=\'6\' cols=\'80\' name=\'imgdescription\' id=\'imgdescription\' ></textarea>";
						html += "</li>";						
					html += "</ul>";
					html += "<div class=\'btn-image-data-popup\' style=\'width: 100%; display: block; float: left; margin-top: 10px;\'>";
					html += "<input onclick=\'jaUpdateImgData("+key+", \""+imgname+"\"); return false;\' type=\'button\' value=\''.JText::_("UPDATE").'\' >";
					html += "<input onclick=\'jaCloseImgForm("+key+"); return false;\' type=\'button\' value=\''.JText::_("CANCEL").'\' >";						
					html += "</div>";						
				html += "</fieldset>";
			html += "</div>";
			
			return html;
		}
		';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);
		return $button;
    }

	/*
	* Check data format for update data type from old version to json format
	* @string data string 
	* @return boolean
	*/
	function isJson($string) 
	{
		return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
	}	
    
	
	function convertFormatData($string)
	{
		$data = array();
		$description = $this->parseDescNew($string);
		
		if(!empty($description)){
			$i = 0;
			foreach($description as $key=>$v){
				$data[$i]								= new stdClass();
				$data[$i]->image 						= $key;
				$data[$i]->title 						= "";
				$data[$i]->link 						= isset($v["url"])?$v["url"]:'';
				$data[$i]->description 	    			= str_replace(array("\n","\r"),"<br />",$v["caption"]);
				$data[$i]->show							= isset($v['show'])?$v['show']:'';
				$i++;			
			}
		}
		if(!empty($data)){
			return json_encode($data);
		}
		return '';
	}
	
	/**
     *
     * Parse description
     * @param string $description
     * @return array
     */
    function parseDescNew($description)
    {

        $regex = '#\[desc ([^\]]*)\]([^\[]*)\[/desc\]#m';
        $description = str_replace(array("{{", "}}"), array("<", ">"), $description);
        preg_match_all($regex, $description, $matches, PREG_SET_ORDER);

        $descriptionArray = array();
        foreach ($matches as $match) {
            $params = $this->parseParams($match[1]);
            if (is_array($params)) {
                $img = isset($params['img']) ? trim($params['img']) : '';
                if (!$img)
                    continue;
                $url = isset($params['url']) ? trim($params['url']) : '';
                $target = isset($params['target']) ? trim($params['target']) : '';
                $show = isset($params['show']) ? trim($params['show']) : '';
                $descriptionArray[$img] = array('url' => $url, 'caption' => str_replace("\n", "<br />", trim($match[2])), 'target' => $target);
            }
        }

        return $descriptionArray;
    }
	
	/**
     * get parameters from configuration string.
     *
     * @param string $string;
     * @return array.
     */
    function parseParams($string)
    {
        $string = html_entity_decode($string, ENT_QUOTES);
        $regex = "/\s*([^=\s]+)\s*=\s*('([^']*)'|\"([^\"]*)\"|([^\s]*))/";
        $params = null;
        if (preg_match_all($regex, $string, $matches)) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                $key = $matches[1][$i];
                $value = $matches[3][$i] ? $matches[3][$i] : ($matches[4][$i] ? $matches[4][$i] : $matches[5][$i]);
                $params[$key] = $value;
            }
        }
        return $params;
    }
	
}
/**
 * $JA#COPYRIGHT$
 */
var requesting = false;
var jausersettingajax = null;
var JANEWSPRO = new Class({
	
	/**
	 * show user setting form.
	 */
    showForm: function (parent) {
        // looking for container which contain setting form.
        var container = parent.getElement('.ja-usersetting-options');
       
        if (container.offsetHeight <= 0) {
        	$$('.ja-usersetting-options').each(function(el){
        		if(el.offsetHeight>0){
        			this.hideElement(el);
        		}
        	}.bind(this));
            this.showElement(container, container.getElement('form.ja-usersetting-form').offsetHeight+20);
        } else {
            this.hideElement(container);
        }
        return false;
    },
    
	_bindingAndprocessingEventForm: function (parent){
    	var forms = parent.getElements('.ja-usersetting form');
    	idReload =  parent.id;   	
    	if(forms.length>0){
    		forms.each(function (form){
    	        // catch exeption
    			var $defined = function(obj){
    			    return (obj != undefined);
    			};
    	        if ($defined(form) == false) {
    	            alert("Could not found the form setting for this module, please try to check again");
    	            return;
    	        }
    	        
    	        // checkbox: click chooise all
    	        if (form.checkall != null) {
    	        	var checkboxs = form.getElements('input.checkbox');
    	            $(form.checkall).addEvent('click', function () {
    	                var doCheck = this.checked;
    	                checkboxs.each(function (elm) {
    	                    elm.checked = doCheck;    	                   
    	                }.bind(this));
    	            });
    	            
    	            checkboxs.each(function (elm) {
	                    elm.addEvent('click', function(){
	                    	if(!this.checked){
	                    		 $(form.checkall).checked = false;	                    		 
	                    	}
	                    	else{
	                    		var doCheck = true;
	                    		checkboxs.each(function (el) {
	                    			if(!el.checked) doCheck = false;
	                    		});
	                    		$(form.checkall).checked = doCheck;
	                    	}
	                    });
	                    
	                }.bind(this));
    	        }
    	        
    	        // if click button cancel.
    	        form.getElement('input.ja-cancel').addEvent('click', function () {
    	            this.hideElement(form.getParent());
    	        }.bind(this));
    	        
    	        // if click button submit.
    	        var submit_bt = form.getElement('input.ja-submit');
    	        submit_bt.addEvent('click', function () {
    	        	submit_bt.disabled = true;
    	        	var link = location.href;
    	        	if(link.indexOf('#')>-1){
    	        		link = link.substr(0, link.indexOf('#'));
    	        	}
    	        	if(link.indexOf('?')>-1) link += '&';
    	        	else link += '?';
    	        	link += 'janajax=1&rand=' + (Math.random() * Math.random());
    	        	
    	        	if(requesting){
    	        		jausersettingajax.cancel();
    	        		requesting = false;
    	        	}		    	
    	        	requesting = true;
    	        	
    	            jausersettingajax = new Request.HTML({
						url:link, 
    	                method: 'get',
						update: document.id(parent),
    	                data: form.toQueryString(),
						
    	                onSuccess: function (data) {
    	            		submit_bt.disabled = false;
    	            		requesting = false;
							
    	                   	/*tooltip*/
    	                   //	var JTooltips = new Tips($$('#' + parent.id + ' .jahasTip'), { maxTitleChars: 50, fixed: false, className: 'tool-tip janews-tool'});
    	                   	document.getElements('.jahasTip').each(function(el) {
							var title = el.get('title');
							if (title) {
							 var parts = title.split('::', 2);
							 el.store('tip:title', parts[0]);
							 el.store('tip:text', parts[1]);
							}
						   });
					   
							var JTooltips = new Tips(document.getElements('.jahasTip'), { maxTitleChars: 50, fixed: false, className: 'tool-tip janews-tool'});
						}.bind(this)
    	            }).send()
    	        }.bind(this))
    			
    		}.bind(this))
    	}		
	},
	
	reloadJS:function(parent){
		
		parent.getElements('script').each(function (script) {
		
            if (script.src) {
                new Element('script', {
                    'type': 'text/javascript',
                    'src': script.src
                }).inject($(document.body).getElement('head'));
            } else {
                eval(script.innerHTML);
            }
        });
	},
	
    showElement: function (obj, height) {
        if (!obj.fx) {
            obj.fx = new Fx.Tween(obj);
        }
        obj.fx.start( 'height', height);
    },
    
    hideElement: function (obj) {
        obj.maxHeight = obj.offsetHeight;
        if (!obj.fx) {
            obj.fx = new Fx.Tween(obj);
        }
        obj.fx.start('height', 0);
    }
});
/**
 * $JA#COPYRIGHT$
 */
var jaajax = null;
var JANEWSPRO_LINEAR = new Class({
	initialize: function(options) {
		
		this.options = Object.append({
			moduleid: '',
			secid: '',
			direction: 'left',
			duration: 400,
			changeTransition:	Fx.Transitions.Pow.easeIn
		}, options || {});
		
		this.panel = $('ja-cats-slide-mainwrap-'+this.options.moduleid+'-'+this.options.secid);
		this.panelArticles = $('ja-articles-mainwrap-'+this.options.moduleid+'-'+this.options.secid);
		this._Wmore = 0;
		if(this.panel==null) return;	
		
		this.panelwrap = this.panel.getElement('ul.subcats-selection');		
		
		
		//this._W = 0;		
		this._W = this.panel.offsetWidth - this.panel.getStyle('padding-left').toInt() - this.panel.getStyle('padding-right').toInt();				
		this._L = this._W;
		
		this.panelArticles.setStyle('height', this.panelArticles.getElement('div.ja-articles').offsetHeight);				
		
		
		var els = this.panelwrap.getChildren();
		this._active = els[0];
		this._index = 0;
		this._total = els.length;
		this._els = els;
		
		var width = 0;
		var left = 0;
		els.each(function (el, index){
			if(el.getPrevious()!=null){
				left += el.getPrevious().getCoordinates().width;
			}
			el.setStyle(this.options.direction, left);
			
			width += el.getCoordinates().width;
		  
		    el.getElement('a.subcat-title').addEvent('click', this.loadArticles.bind(this, el));
		    
		}.bind(this));
		
		this.panelwrap.setStyle('width', width);		
		
		if(this._W<width && this.panelwrap.offsetWidth>this._W){
			this.panel.getElement('.ja-newspro-control').setStyle('display', 'block');
		}
		
		this.next_bt = this.panel.getElement('.ja-newspro-control a.next');
		this.prev_bt = this.panel.getElement('.ja-newspro-control a.prev');
				
		if(this.next_bt!=null){
			this.next_bt.addEvent('click', function () {
		    	this.next();
		    }.bind(this));
		    
		    this.prev_bt.addEvent('click', function () {
		    	this.previous();
		    }.bind(this));		    		    
		}
		
		    
	},
	
    next: function(){
		if(this._active==null) return;
		if(this._active.getStyle(this.options.direction).toInt() + this._W >= this._els[this._total-1].getStyle(this.options.direction).toInt()+this._els[this._total-1].offsetWidth) return;
		
		var left = 0;
		for(var i=this._index; i<this._total; i++){
			left = this._els[i].getStyle(this.options.direction).toInt();		
			if(left>this._L){
				break;
			}
					
			this._active = this._els[i];
			this._index = i;
		}
		
		left = this._active.getStyle(this.options.direction).toInt();
		this._L =  left + this._W;		
		this.run(left);
		
    },
    
    previous: function(){
    	if(this._active==null) return;
		if(this._index==0) return;
		
		var L = this._active.getStyle(this.options.direction).toInt();
		var left = 0;
		for(var i=this._index; i>=0; i--){
			left = this._els[i].getStyle(this.options.direction).toInt();	
			if(left<=L-this._W){
				break;
			}
			this._active = this._els[i];
			this._index = i;
		}
		
		left = this._active.getStyle(this.options.direction).toInt();
		this._L =  left + this._W;		
		this.run(left);		
    },
    
    run: function(left){
    	if(this.changeEffect==null){
			this.changeEffect = new Fx.Tween(this.panelwrap, {duration:this.options.duration});
		}
		//this.changeEffect.stop();
		this.changeEffect.start('margin-'+this.options.direction, -left);	
    },
    
    loadArticles: function(el){
    	
    	var catid = el.getElement('a.subcat-title').getProperty('rel');
    	if(!catid) return true;
		
    	if(el.className == 'active'){
			var cur_active = 1;
		}else{
			var cur_active = 0;
		}
		
    	this._els.removeClass('active');
    	el.addClass('active');
    	this.panelwrap.getElements('.subcat-more').setStyle('display', 'none');		    	
    	el.getElement('.subcat-more').setStyle('display', 'inline');
    	
    	var width = 0;
    	var _left = 0;
    	this._els.each(function (_el){
    		if(_el.getPrevious()!=null){
    			_left += _el.getPrevious().getCoordinates().width;
			}    		
    		_el.setStyle(this.options.direction, _left);
    	
    		width += _el.getCoordinates().width; 
    		
    	}.bind(this));
    	
    	/* Update width for UL */
    	this.panelwrap.setStyle('width', width);		
    	
    	if( (this._W + this._active.getStyle(this.options.direction).toInt()) < (el.getStyle(this.options.direction).toInt() +  el.getCoordinates().width) ){
    		
    		var left = 0;
    		for(var i=this._index; i<this._total; i++){
    			left = this._els[i].getStyle(this.options.direction).toInt();		    			
    			if(left+this._W>(el.getStyle(this.options.direction).toInt() +  el.getCoordinates().width)){
    				this._active = this._els[i];
	    			this._index = i;
    				break;
    			}		    							    			
    		}
    		
    		left = this._active.getStyle(this.options.direction).toInt();
    		this._L =  left + this._W;		
    		this.run(left);		    		
    	}
    	else if(this._active==el){
    		this._active = el;
    		left = this._active.getStyle(this.options.direction).toInt();
    		this._L =  left + this._W;		
    		this.run(left);		
    	}
    	
    	
    	
    	if($('ja-articles-'+this.options.moduleid+'-'+catid)!=null && cur_active ==0){
    		cur_activePanel =  this.panelArticles.getElement('div.active');
    		cur_activePanel.removeClass('active');            		
    		var activePanel = $('ja-articles-'+this.options.moduleid+'-'+catid);
    		activePanel.addClass('active');
    		this.panelArticles.setStyle('height', activePanel.offsetHeight);
    		
    		this.panels = this.panelArticles.getElements('div.ja-articles');		
    		if(this.anim==null) this.anim = new animFade(this);
    		
    		this.anim.move(cur_activePanel, activePanel, false);
    		return;
    	}
    	//this._els.removeClass('active');
    	//el.addClass('active');
		
    	this.loading = this.panelArticles.getElement('.ja-newspro-loading');
    	if(this.loading!=null){		    				    		
    		this.panelArticles.getElement('div.active').setStyle('opacity', 0.3);		    			
    		this.loading.setStyles({'display':'block', 'top': this.panelArticles.offsetHeight/2-10, 'left': this.panelArticles.offsetWidth/2});
    	}
    	
    	var link = location.href;
    	if(link.indexOf('#')>-1){
    		link = link.substr(0, link.indexOf('#'));
    	}
    	if(link.indexOf('?')>-1) link += '&';
    	else link += '?';
    	link += 'janewspro_linear_ajax=1&moduleid='+this.options.moduleid + '&subcat='+ catid;
    	if(requesting){
    		jaajax.cancel();
    		requesting = false;
    	}		    	
    	requesting = true;
    	
        jaajax = new Request({
			url:link,
            method: 'get',
            onSuccess: function (data) {	            	
        		requesting = false;    
        		if(this.loading!=null) this.loading.setStyle('display', 'none');
        		cur_activePanel =  this.panelArticles.getElement('div.active');
        		cur_activePanel.removeClass('active');
        		
        		var activePanel = new Element('div', {'class':'ja-articles active', 'id': 'ja-articles-'+this.options.moduleid+'-'+catid}).inject(this.panelArticles);	            		
        		activePanel.innerHTML = data;
        		this.panelArticles.setStyle('height', activePanel.offsetHeight+20);
        		
        		this.panels = this.panelArticles.getElements('div.ja-articles');				
        		
        		if(this.anim==null) this.anim = new animFade(this);
        		
        		this.anim.move(cur_activePanel, activePanel, false);
        		
        		
               	/*tooltip*/
        		$$('#ja-articles-'+this.options.moduleid+'-'+catid + ' .jahasTip').each(function(el) {
    				var title = el.get('title');
    				if (title) {
    					var parts = title.split('::', 2);
    					el.store('tip:title', parts[0]);
    					el.store('tip:text', parts[1]);
    				}
    			});
               	var JTooltips = new Tips($$('#ja-articles-'+this.options.moduleid+'-'+catid + ' .jahasTip'), { maxTitleChars: 50, fixed: false, className: 'tip-wrap janews-tool'});
				
               	el.addClass('active');
            }.bind(this),
            
            onFailure: function () {
            	if(this.loading!=null) this.loading.setStyle('display', 'none');
            	requesting = false;
                alert('fail request');
            }
        }).send();	 		
    }
	
    
});

var animFade = new Class ({
	initialize: function(tabwrap) {
		this.options = tabwrap.options || {};
		this.tabwrap = tabwrap;
		this.changeEffect = new Fx.Elements(this.tabwrap.panels, {duration: this.options.duration});
		this.tabwrap.panels.setStyles({'opacity':0,'width':'100%'});
	},

	move: function (curTab, newTab, skipAnim) {
		if(this.options.changeTransition != 'none' && !skipAnim)
		{
			if (curTab)
			{
				curOpac = curTab.getStyle('opacity');
				var changeEffect = new Fx.Tween(curTab, {duration: this.options.duration, transition: this.options.changeTransition});
				//changeEffect.stop();
				changeEffect.start('opacity', curOpac,0);
			}
			curOpac = newTab.getStyle('opacity');
			var changeEffect = new Fx.Tween(newTab, {duration: this.options.duration, transition: this.options.changeTransition});
			//changeEffect.stop();
			changeEffect.start('opacity', curOpac,1);
		} else {
			if (curTab) curTab.setStyle('opacity', 0);
			newTab.setStyle('opacity', 1);
		}
	}
});
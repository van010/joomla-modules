/**
 * ------------------------------------------------------------------------
 * JA System Social Feed plugin for Joomla 2.5 & J3.5
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

var JADependForm = new Class({ 	
	
	initialize: function(){
		this.depends = {};
		this.controls = {};
	},
	
	register: function(to, depend){
		var controls = this.controls;
		
		if(!controls[to]){
			controls[to] = [];
			
			var inst = this;
			if(typeof jQuery != 'undefined' && jQuery.fn.chosen){
				jQuery(this.elmsFrom(to)).bind('change', function(e){
					inst.change(this);
				});
			}
			this.elmsFrom(to).addEvent('change', function(e){
				inst.change(this);
			});
		}
		
		if(controls[to].indexOf(depend) == -1){
			controls[to].push(depend);
		}
	},
	
	add: function(control, info){
		
		var depends = this.depends,
			name = info.group + '[' + control + ']';
			
		info = Object.append({
			group: 'params',
			hiderow: true,
			control: name
		}, info);
		
		info.hiderow = !!info.hiderow;
		
		info.elms.split(',').each(function(el){
			var elm = info.group +'[' + el.trim() + ']';
			
			if (!depends[elm]) {
				depends[elm] = {};
			}
			
			if (!depends[elm][name]) {
				depends[elm][name] = [];
			}
			
			depends[elm][name].push(info.val);
			
			this.register(name, elm);
			
		}, this);
	},
});

var JADepend = window.JADepend || {};

JADepend.inst = new JADependForm();
window.addEvent('load', function() {
	setTimeout(JADepend.inst.start.bind(JADepend.inst), 100);
});
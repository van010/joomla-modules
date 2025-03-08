/**
 * $JA#COPYRIGHT$
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
				jQuery(this.elmsFrom(to)).change(function(e){
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
	
	change: function(ctrlelm){
		var controls = this.controls,
			depends = this.depends,
			ctrls = controls[ctrlelm.name];
			
		if(!ctrls){
			ctrls = controls[ctrlelm.name.substr(0, ctrlelm.name.length - 2)];
		}
		
		if(!ctrls){
			return false;
		}
		
		ctrls.each(function(dpd){
			var showup = true;
			
			Object.each(depends[dpd], function(cvals, ctrl){
				if(showup){
					var celms = this.elmsFrom(ctrl);
					showup = showup && celms.every(function(celm){ return !celm._disabled; });
					if(showup){
						showup = showup && this.valuesFrom(celms).some(function(val){ return cvals.contains(val); });
					}
				}
			}, this);
			
			this.elmsFrom(dpd).each(function(delm){
				if(showup){
					this.enable(delm);
				} else {
					this.disable(delm);
				}
			}, this);
			
			if(controls[dpd] && controls[dpd] != dpd){
				this.elmsFrom(dpd)[0].fireEvent('change');
			}
			
		}, this);
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
	
	start: function(){
		$(document.adminForm).getElements('h4.block-head').each(function(el){
			this.closest(el, 'li, div.control-group').addClass('segment')
		}, this);
		
		$(document.adminForm).getElements('.hideanchor').each(function(el){
			this.closest(el, 'li, div.control-group').addClass('hide');
		}, this);

		this.update();
	},
	
	update: function () {
		Object.each(this.controls, function(ctrls, ctrl){
			this.elmsFrom(ctrl).fireEvent('change');
		}, this);
	},
	
	enable: function (el) {
		el._disabled = false; //selector 'li' is J2.5 compactible
		this.closest(el, '.adminformlist > li, div.control-group').setStyle('display', 'block');
	},
	
	disable: function (el) {
		el._disabled = true; //selector 'li' is J2.5 compactible
		this.closest(el, '.adminformlist > li, div.control-group').setStyle('display', 'none');
	},
	
	elmsFrom: function(name){
		var el = document.adminForm[name];
		if(!el){
			el = document.adminForm[name + '[]'];
		}
		
		//Mootools 1.4.5 compatible
		return (typeOf(el) == 'element' && el.get('tag') == 'select') ? $$([el]) : $$(el);
	},
	
	valuesFrom: function(els){
		var vals = [];
		
		((typeOf(els) == 'element' && els.get('tag') == 'select') ? $$([els]) : $$(els)).each(function(el){
			var type = el.type,
				value = (el.get('tag') == 'select') ? el.getSelected().map(function(opt){
					return document.id(opt).get('value');
				}) : ((type == 'radio' || type == 'checkbox') && !el.checked) ? null : el.get('value');

			vals.include(Array.from(value));
		});
		
		return vals.flatten();
	},
	
	closest: function(elm, sel){
		var parents = elm.getParents(sel),
			cur = elm;
			
		while(cur){
			if(parents.contains(cur)){
				return cur;
			}
			
			cur = cur.getParent();
		}

		return null;
	},
	
	segment: function(seg){
		if($(seg).hasClass('close')){
			this.showseg(seg);
		} else {
			this.hideseg(seg);
		}
	},
	
	showseg: function(seg){
		
		var segelm = $(seg),
			snext = this.closest(segelm, 'li, div.control-group').getNext();
		
		while(snext && !snext.hasClass('segment')){
			if(!snext.hasClass('hide')){
				snext.setStyle('display', snext.retrieve('jdisplay') || '');
			}
			snext = snext.getNext();
		}
		
		segelm.removeClass('close').addClass('open');  
	},
	
	hideseg: function(seg){
		var segelm = $(seg),
			snext = this.closest(segelm, 'li, div.control-group').getNext();
		
		while(snext && !snext.hasClass('segment')){
			if(!snext.hasClass('hide')){
				snext.store('jdisplay', snext.getStyle('display')).setStyle('display', 'none');
			}
			snext = snext.getNext();
		}
		
		segelm.removeClass('open').addClass('close');  
	}
});

var JADepend = window.JADepend || {};

JADepend.inst = new JADependForm();
window.addEvent('load', function() {
	setTimeout(JADepend.inst.start.bind(JADepend.inst), 100);
});
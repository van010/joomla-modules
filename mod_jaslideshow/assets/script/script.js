/**
 * $JA#COPYRIGHT$
 */


 var JASlider = new Class({

 	Implements: Options,
 	
 	options: {
 		effects: [
 		
			'slice-down-right',			//animate height and opacity
			'slice-down-left',
			'slice-up-right',
			'slice-up-left',

			'slice-down-right-offset', 			//have an offset for top or bottom, no animate height
			'slice-down-left-offset',
			'slice-up-right-offset',
			'slice-up-left-offset',
			
			'slice-updown-right',				//slide up alternate column
			'slice-updown-left',
			
			'slice-down-center-offset',
			'slice-up-center-offset',
			
			'slice-down-right-inv',				//look like above, slide from an offset, but use the current image instead of the new image
			'slice-down-left-inv',
			'slice-down-center-inv',
			'slice-up-right-inv',
			'slice-up-left-inv',
			'slice-up-center-inv',
			
			'slice-down-random',			//slide and offset fade
			'slice-up-random',
			
			'slice-down-left-wider', 		//slice, wider === fold
			'slice-down-right-wider',
			'slice-down-center-wider',
			
			'slide-in-left',
			'slide-in-right',
			'slide-in-up',
			'slide-in-down',
			'slide-in-left-inv',
			'slide-in-right-inv',
			'slide-in-up-inv',
			'slide-in-down-inv',
			
			'fade',
			'fade-four', //create 4 clone and set to offset of 100px from default possiton, animate to defaalt position and fadein
			
			'box-sort-random', //box, offset from random other position, and animate fade to it position, fadein
			'box-random',
			'box-rain-normal',
			'box-rain-reverse',
			'box-rain-normal-grow',
			'box-rain-reverse-grow',
			'box-rain-normal-jelly',
			'box-rain-reverse-jelly',
			
			'circle-out',
			'circle-in'//,
			//'circle-rotate'
			],
			
			slices: 10,
			boxCols: 8,
			boxRows: 4,
			
		animation: 'move', 							//[move, fade, random], move and fade for old compactible
		fbanim: 'fade',
		direction: 'horizontal', 					//[horizontal, vertical] - slide direction of main item for move animation
		
		interval: 5000,
		duration: 500,
		transition: Fx.Transitions.Quad.easeOut,
		
		repeat: true,								//animation repeat or not
		autoPlay: false,							//auto play
		
		mainWidth: 800,								//width of main item
		mainHeight: 400,
		
		rtl: window.isRTL,							//rtl
		
		startItem: 0,								//start item will be show
		
		thumbItems: 4,								//number of thumb item will be show
		thumbType: false, 							//false - no thumb, other [number, thumb], thumb will animate
		thumbWidth: 160,
		thumbHeight: 160,
		thumbSpaces: [0, 0],
		thumbOpacity: 0.8,
		thumbTrigger: 'click',
		thumbOrientation: 'horizontal',	//thumb orientation
		
		maskStyle: 1,							//0 - fix to main image, 1 - full size
		maskWidth: 360,							//mask - a div over the the main item - used to hold descriptions
		maskHeigth: 50,					
		maskOpacity: 0.8,						//mask opacity
		maskAlign: 'bottom',					//mask align
		maskTransitionStyle: 'opacity',			//mask transition style
		maskTransition: Fx.Transitions.linear,	//mask transition easing
		
		showDesc: false,						//show description or not
		descTrigger: 'always',					//[always, mouseover, load]
		
		showControl: false,						//show navigation controller [next, prev, play, playback]
		
		showNavBtn: false,	// show next prev, on main image image
		navBtnOpacity: 0.4,
		navBtnTrigger: 'click',
		
		showProgress: true,
		
		urls: false, // [] array of url of main items
		targets: false // [] same as urls, an array of target value such as, '_blank', 'parent', '' - default
	},

	blank: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==',
	
	initialize: function (element, options) {
		var slider = $(element);
		
		if(!slider){
			return false;
		}
		
		this.setOptions(options);
		
		var options = this.options,
		mainWrap = slider.getElement('.ja-slide-main-wrap'),
		mainFrame = slider.getElement('.ja-slide-main'),
		mainItems = slider.getElements('.ja-slide-item'),
		iframeItems = mainItems.getElement('iframe');
		
		if(!mainItems.length){
			return false;
		}
		
		var imgItems = mainItems.getElement('img').clean();


		if(mainItems.length != imgItems.length && options.animation == 'slice'){
			options.animation = options.fbanim;
		}
		
		if (options.animation !== 'move'){
			options.maskStyle = 0;
		}

		options.rtl = (typeof options.rtl == 'string') ? (typeof options.rtl == 'rtl') : !!(options.rtl);

		mainWrap.setStyles({
			'width': options.maskStyle ? '100%' : options.mainWidth,
			'height': options.mainHeight
		});
		
		mainItems.setStyles({
			'width': options.mainWidth,
			'height': options.mainHeight
		});
		
		var mainItemSpace = 0,
		isHorz = (options.direction == 'horizontal');
		
		if (options.maskStyle) {	//full size
			mainItemSpace = 10;
			mainItems.setStyle(isHorz ? 'margin-right' : 'margin-bottom',  mainItemSpace);
		}
		
		var mainItem = mainItems[0],
		mainItemSize = isHorz ? (mainItem.getWidth() + mainItem.getStyle('margin-left').toInt() + mainItem.getStyle('margin-right').toInt()) : (mainItem.getHeight() + mainItem.getStyle('margin-top').toInt() + mainItem.getStyle('margin-bottom').toInt()),
		rearSize = Math.ceil(((isHorz ? mainWrap.getWidth() : mainWrap.getHeight()) - mainItemSize) / 2),
		
		vars = {
			slider: slider,
			mainWrap: mainWrap,
			mainFrame: mainFrame,
			mainItems: mainItems,
			iframeItems: iframeItems,
			size: mainItemSize,
			rearSize: rearSize,
			offset: (options.maskStyle ? (rearSize - mainItemSize + mainItemSpace / 2) : 0) - (options.rtl ? mainItemSpace : 0),
			mainItemSpace: mainItemSpace,
			
			total: mainItems.length,
			curIdx: Math.min(options.startItem, mainItems.length - 1),
			nextIdx: -1,
			curImg: '',
			
			running: 0,
			stop: 0,
			timer: 0,
			
			sliceTime: Math.round(Math.max(70, options.duration / options.slices)),
			boxTime: Math.round(Math.max(50, options.duration / Math.max(options.boxCols, options.boxRows))),
			
			modes: (isHorz ? (options.rtl == 'rtl' ? ['right', 'width'] : ['left', 'width']) : ['top', 'height']),
			fxop: {
				duration: options.duration,
				transition: options.transition,
				link: 'cancel'
			}
		};
		
		this.vars = vars;
		
		//Description
		this.initMasker();
		
		//Get initial images
		if(options.animation == 'slice'){
			mainItems.setStyle('display', 'none');
			vars.mainItems = imgItems;
			vars.curImg = vars.mainItems[vars.curIdx];
			vars.sliceImg = new Element('img', {
				'src': vars.curImg.src
			}).inject(new Element('div', {
				'class': 'ja-slide-sliceimg'
			}).inject(vars.mainFrame, 'top'));
			
			var ofsParent = mainFrame.getOffsetParent() || mainWrap,
			opCoord = ofsParent.getCoordinates();
			
			//Set first background
			mainFrame.setStyles({
				position: 'relative',
				left: (opCoord.width - options.mainWidth) / 2 - ofsParent.getStyle('padding-left').toInt() - ofsParent.getStyle('border-left-width').toInt(),
				top: (opCoord.height - options.mainHeight) / 2 - ofsParent.getStyle('padding-top').toInt() - ofsParent.getStyle('border-top-width').toInt(),
				overflow: 'hidden',
				display: 'block',
				width: options.mainWidth,
				height: options.mainHeight
			});
		} 

		if(options.animation == 'move'){
			vars.offset -= mainFrame.getStyle('margin-left').toInt();
			if(isNaN(vars.offset)){
				vars.offset = 0;
			}
			
			if(options.maskStyle){
				mainItems[0].clone().inject(mainFrame);
				mainItems[vars.total - 1].clone().inject(mainFrame, 'top');
			}
			
			mainFrame.setStyle(vars.modes[1], vars.size * (vars.total + 2));
			
			vars.fx = new Fx.Tween(mainFrame, Object.append(Object.clone(vars.fxop), {
				onComplete: this.animFinished.bind(this)
			})).set(vars.modes[0], -vars.curIdx * vars.size + vars.offset);
		} 
		
		if(options.animation == 'fade'){
			var fadeop = Object.append(Object.clone(vars.fxop), {
				property: 'opacity',
				onComplete: function(item){
					if(item.getStyle('opacity') == '1'){
						this.animFinished();
					}
				}.bind(this)
			});
			
			mainItems.each(function(item){
				item.setStyles({
					position: 'absolute',
					top: 0,
					opacity: 0,
					zIndex: 1,
					visibility: 'visible'
				}).store('fx', new Fx.Tween(item, fadeop));
			})[vars.curIdx].setStyles({
				opacity: 1,
				zIndex: 10
			});
		}
		
		this.initMainItemAction();
		this.initMainCtrlButton();
		this.initThumbAction();
		this.initControlAction();
		this.initHoverBehavior();
		this.initProgressBar();
		this.initLoader();
		
		vars.direct = 'next';
		slider.setStyle('visibility', 'visible');
		
		this.prepare(false, vars.curIdx);
		this.animFinished();
	},
	
	stop: function(){
		clearInterval(this.vars.timer);
		this.vars.stop = 1;
		
		if(this.options.showProgress){			//stop the progress bar
			this.vars.progressFx.stop().set(0);
		}
	},
	
	prev: function(force){
		var vars = this.vars;
		if(vars.running && !force){
			return false;
		}
		if(typeOf(vars.iframeItems[vars.curIdx]) != 'null'){			
			var datasrc = vars.iframeItems[vars.curIdx].getProperty('data-src');
			vars.iframeItems[vars.curIdx].setProperties({
				'src':datasrc,
				'alt':datasrc
			});
		}
		this.prepare(force, vars.curIdx -1);
	},
	
	next: function(force){
		var vars = this.vars;
		if(vars.running && !force){
			return false;
		}
		if(typeOf(vars.iframeItems[vars.curIdx]) != 'null'){
			var datasrc = vars.iframeItems[vars.curIdx].getProperty('data-src');
			vars.iframeItems[vars.curIdx].setProperties({
				'src':datasrc,
				'alt':datasrc
			});
		}
		this.prepare(force, vars.curIdx +1);
	},
	
	playback: function(force){
		this.vars.direct = 'prev';
		this.vars.stop = 0;
		this.prev(force);
	},
	
	play: function(force){
		this.vars.direct = 'next';
		this.vars.stop = 0;
		this.next(force);
	},
	
	start: function(){
		var vars = this.vars;
		clearTimeout(vars.timer);
		vars.timer = setTimeout(this[this.vars.direct].bind(this), this.options.interval)
	},

	imgload: function(img, idx){
		if(img.complete && img.naturalWidth !== undefined){
			this.load(idx);
			return;
		}

		var blank = this.blank,
		src = img.src,
		callback = function(){
			this.load(idx);
		}.bind(this),
		onload = function(){
			if(this.src === blank){
				return;
			}

			setTimeout(callback);
		};

		img.addEvent('load', onload).addEvent('error', onload);

		if(img.readyState || img.complete){
			img.src = blank;
			img.src = src;
		}
	},
	
	load: function(idx){
		var vars = this.vars;

		vars.mainItems[idx].store('loaded', 1);
		
		if(vars.nextIdx == idx){
			if(vars.loaderFx){
				vars.loaderFx.start(0);
			}
			
			this.run(false, idx);
		} else if(vars.nextIdx == -1 && vars.loaderFx){
			vars.loaderFx.start(0);
		}
	},
	
	prepare: function(force, idx){
		var vars = this.vars,
		options = this.options;

		if(options.animation === 'slice' && vars.running){
			return false;
		}
		
		if(idx >= vars.total){ 
			idx = 0;
		}
		
		if(idx < 0){
			idx = vars.total - 1;
		}

		var	curImg = vars.mainItems[idx];
		if(curImg.get('tag') != 'img'){
			curImg = curImg.getElement('img');
		}
		
		if(!curImg){
			return this.run(force, idx);
		}
		
		vars.nextIdx = idx;
		
		if(curImg.retrieve('loaded')){
			if(idx == vars.curIdx){
				return false;
			}
			
			this.run(force, idx);
		}
		
		else{
			
			if(vars.loaderFx){
				vars.loader.setStyle('display', 'block');
				vars.loaderFx.start('opacity', 0.3);
			}
			
			this.imgload(curImg, idx);
		}
	},
	
	run: function(force, idx){

		var vars = this.vars,
		options = this.options;

		if(vars.curIdx == idx){
			return false;
		}
		if(typeOf(vars.iframeItems[vars.curIdx]) != 'null'){			
			var datasrc = vars.iframeItems[vars.curIdx].getProperty('data-src');
			vars.iframeItems[vars.curIdx].setProperties({
				'src':datasrc,
				'alt':datasrc
			});
		}
		if(this[options.animation]){
			this[options.animation](force, idx);
		}else{
			this.fade(force, idx);
		}
		
		if (vars.thumbMaskFx) {
			if (idx <= vars.thumbStartIdx || idx >= vars.thumbStartIdx + options.thumbItems - 1) {
				vars.thumbStartIdx = Math.max(0, Math.min(idx - options.thumbItems + 2, vars.total - options.thumbItems));
				
				vars.thumbBoxFx.start(-vars.thumbStartIdx * vars.thumbStep);
				if(vars.handleBoxFx){
					vars.handleBoxFx.start(-vars.thumbStartIdx * vars.thumbStep);
				}
			}
			
			vars.thumbMaskFx.start((idx - vars.thumbStartIdx) * vars.thumbStep - 2000);
			vars.thumbItems.removeClass('active')[idx].addClass('active');
			if(vars.handleItems.length){
				vars.handleItems.removeClass('active')[idx].addClass('active');
			}
		}
		
		if (options.descTrigger === 'load' && options.showDesc) {
			this.hideDescription();
		}
		
		if(options.showProgress){
			vars.progressFx.stop().set(0);
		}

	},
	
	move: function(force, idx){
		var vars = this.vars;
		vars.curIdx = idx;
		vars.mainFrame.setStyle(vars.modes[1], vars.size * (vars.total + 2));
		vars.fx.start(vars.modes[0], -idx * vars.size + vars.offset);
	},
	
	fade: function(force, idx){
		var options = this.options,
		vars = this.vars;
		
		if(idx != vars.curIdx){
			var itemOff = vars.mainItems[vars.curIdx],
			itemOn = vars.mainItems[idx];
			
			itemOff.setStyle('zIndex', 1).retrieve('fx').start(0);
			itemOn.setStyle('zIndex', 10).retrieve('fx').start(1);
		}
		
		vars.curIdx = idx;
	},
	
	slice: function(force, idx){
		
		var options = this.options,
		vars = this.vars,
		container = vars.mainFrame,
		oldImg = vars.curImg;
		
		//Set vars.curImg
		vars.curIdx = idx;
		vars.curImg = vars.mainItems[vars.curIdx];
		
		// Remove any slices & boxs from last transition
		container.getChildren('.ja-slice').destroy();
		container.getChildren('.ja-box').destroy();
		
		//Generate random effect
		var	effect = options.effects[Math.floor(Math.random() * (options.effects.length))];
		if(effect == undefined){
			effect = 'fade';
		}
		
		//Run effects
		var effects = effect.split('-'),
		callfun = 'anim' + effects[0].capitalize();

		if(this[callfun]){
			
			vars.running = true;
			this[callfun](effects, oldImg, vars.curImg);
		}
	},
	
	animFinished: function(){ 
		var options = this.options,
		vars = this.vars;
		
		vars.running = false;
		
		//Trigger the afterChange callback
		if (options.showDesc) {
			this.swapDescription();
			
			if (options.descTrigger === 'load') {
				this.showDescription();
			}
		}

		if(options.urls){
			vars.mainFrame.setStyle('cursor', options.urls[vars.curIdx] ? 'pointer' : '');
		}
		
		if (!vars.stop && (options.autoPlay && (vars.curIdx < vars.total -1 || options.repeat == 'true'))) {
			this.start();
			
			if(options.showProgress){
				vars.progressFx.start(vars.progressWidth);
			}
		}
	},
	
	createSlice: function(img){
		var options = this.options,
		vars = this.vars,
		container = vars.mainFrame;
		
		return new Element('div', {
			'class': 'ja-slice',
			'styles': {
				display: 'block',
				position: 'absolute',
				left: 0,
				width: options.mainWidth,
				height: options.mainHeight, 
				opacity: 0,
				zIndex: 10
			}
		}).adopt(new Element('img', {
			'src': img.src,
			'styles': {
				width: options.mainWidth,
				height: options.mainHeight 
			}
		})).inject(container);
	},

	createSlices: function(img, height, opacity){
		var options = this.options,
		vars = this.vars,
		container = vars.mainFrame,
		width = Math.round(options.mainWidth / options.slices),
		slices = [];
		
		for(var i = 0; i < options.slices; i++){
			var sliceWidth = i == options.slices - 1 ? (options.mainWidth - width * i) : width;
			
			slices.push(new Element('div', {
				'class': 'ja-slice',
				'styles': {
					position: 'absolute',
					left: i * width,
					width: sliceWidth,
					height: height, 
					opacity: opacity,
					zIndex: 10
				}
			}).adopt(new Element('img', {
				'src': img.src,
				'styles': {
					left: -(i * width),
					width: options.mainWidth,
					height: options.mainHeight 
				}
			})));
		}
		
		container.adopt(slices);
		
		return slices;
	},
	
	createBoxes: function(img, opacity){
		var options = this.options,
		vars = this.vars,
		container = vars.mainFrame,
		width = Math.round(options.mainWidth / options.boxCols),
		height = Math.round(options.mainHeight / options.boxRows),
		bwidth,
		bheight,
		boxes = [];
		
		for(var rows = 0; rows < options.boxRows; rows++){
			bheight = rows == options.boxRows - 1 ? options.mainHeight - height * rows : height;
			
			for(var cols = 0; cols < options.boxCols; cols++){
				bwidth = cols == options.boxCols - 1 ? options.mainWidth - width * cols : width;
				
				boxes.push(new Element('div', {
					'class': 'ja-box',
					'styles': {
						position: 'absolute',
						opacity: opacity,
						left: width * cols, 
						top: height * rows,
						width: bwidth,
						height: bheight,
						zIndex: 10
					}
				}).adopt(new Element('img', {
					'src': img.src,
					'styles': {
						left: -(width * cols),
						top: -(height * rows),
						width: options.mainWidth,
						height: options.mainHeight 
					}
				})));
			}
		}
		
		container.adopt(boxes);
		
		return boxes;
	},
	
	createCircles: function(img, opacity){
		var options = this.options,
		vars = this.vars,
		container = vars.mainFrame,
		size = 100,
		radius = Math.ceil(Math.sqrt(Math.pow((options.mainWidth), 2) + Math.pow((options.mainHeight), 2))),
		total = Math.ceil(radius / 100),
		left, top, elm,
		circles = [];
		
		for(var i = 0; i < total; i++){
			left = Math.round((options.mainWidth - size) / 2);
			top = Math.round((options.mainHeight - size) / 2);
			
			elm = new Element('div', {
				'class': 'ja-box',
				'styles': {
					position: 'absolute',
					opacity: opacity,
					left: left, 
					top: top,
					width: size,
					height: size,
					zIndex: 10
				}
			}).adopt(new Element('img', {
				'src': img.src,
				'styles': {
					left: -left,
					top: -top,
					width: options.mainWidth,
					height: options.mainHeight 
				}
			}));
			
			this.css3(elm, {
				'border-radius': radius + 'px'
			});
			
			circles.push(elm);
			
			size += 100;
		}
		
		container.adopt(circles);
		
		return circles;
	},
	
	animSlice: function(effects, oldImg, curImg){
		var options = this.options,
		vars = this.vars,			
		img = curImg,
		height = 0,
		opacity = 0;
		
		if(effects[3] == 'inv'){
			img = oldImg;
			height = options.mainHeight;
			opacity = 1;
		}
		
		//set the background
		vars.sliceImg.set('src', effects[3] == 'inv' ? vars.curImg.src : oldImg.src);
		
		var slices = this.createSlices(img, height, opacity),
		styleOn = { height: options.mainHeight - height, opacity: 1 - opacity / 2},
		last = slices.length -1,
		timeBuff = 100;
		
		// by default, animate is sequence from left to right
		if(effects[2] == 'left'){		// reverse the direction, so animation is sequence from right to left
			slices = slices.reverse();
		} else if(effects[2] == 'random'){	// so randomly
			this.shuffle(slices);
		}
		
		if(effects[3] == 'offset'){										//have offset style - we will not animate height, so set it to fullheight, we animate 'top' or 'bottom' property
			var property = effects[1] == 'up' ? 'top' : 'bottom';
		
		delete styleOn.height;
		styleOn[property] = 0;
		
		$$(slices).setStyle(property, '250px').setStyle('height', options.mainHeight);
	}
	
	else if(effects[1] == 'updown'){
		for(var k = 0, kl = slices.length; k < kl; k++){
			$(slices[k]).setStyle((k & 1) == 0 ? 'top' : 'bottom', '0px');
		}
	}
	
	else if(effects[1] == 'down'){
		$$(slices).setStyle('top', '0px');
	}

	else if(effects[1] == 'up'){
		$$(slices).setStyle('bottom', '0px');
	}
	
	if(effects[3] == 'wider'){
		slices.each(function(slice, i){
			var fxop = vars.fxop,
			orgWidth = slice.getWidth();
			
			slice.setStyles({
				'width': 0,
				'height': options.mainHeight
			});
			
			if(i == last){
				fxop = Object.clone(vars.fxop);
				fxop.onComplete = this.animFinished.bind(this);
			}
			
			setTimeout(function(){
				new Fx.Morph(slice, fxop).start({
					width: orgWidth,
					opacity: 1
				});
			}, timeBuff);
			
			timeBuff += vars.sliceTime;
		}, this);
	}
	else if(effects[2] == 'center'){
		var center = (last) / 2;
		slices.each(function(slice, i){
			var fxop = vars.fxop,
			delay = Math.abs(center - i) * 100;
			
			if(i == last){
				fxop = Object.clone(vars.fxop);
				fxop.onComplete = this.animFinished.bind(this);
			}
			
			setTimeout(function(){
				new Fx.Morph(slice, fxop).start(styleOn);
			}, delay);
			
		}, this);
	} else {
		slices.each(function(slice, i){
			var fxop = vars.fxop;
			if(i == last){
				fxop = Object.clone(vars.fxop);
				fxop.onComplete = this.animFinished.bind(this);
			}
			
			setTimeout(function(){
				new Fx.Morph(slice, fxop).start(styleOn);
			}, timeBuff);
			
			timeBuff += vars.sliceTime;
		}, this);
	}
},

animBox: function(effects, oldImg, curImg){
	var options = this.options,
	vars = this.vars,
	img = vars.curImg,
	height = 0,
	opacity = 0;
	
	if(effects[3] == 'jelly'){
		img = oldImg;
		opacity = 1;
	}
	
	vars.sliceImg.set('src', effects[3] == 'jelly' ? curImg.src : oldImg.src);
	
	var boxes = this.createBoxes(img, opacity),
	last = options.boxCols * options.boxRows -1,
	boxTime = vars.boxTime,
	i = 0,
	timeBuff = 100;
	
	if(effects[1] == 'sort'){
		var width = Math.round(options.mainWidth / options.boxCols),
		height = Math.round(options.mainHeight / options.boxRows),
		boxTime = boxTime / 3;

		this.shuffle(boxes).each(function(box){
			var fxop = vars.fxop,
			styleOn = box.getStyles('top', 'left');
			
			if(i == last){
				fxop = Object.clone(vars.fxop);
				fxop.onComplete = this.animFinished.bind(this);
			}
			
			box.setStyles({
				top: Math.round(Math.random() * options.boxRows / 2) * height,
				left: Math.round(Math.random() * options.boxCols / 2) * width
			});
			
			styleOn['opacity'] = 1;
			
			setTimeout(function(){
				new Fx.Morph(box, fxop).start(styleOn);
			}, timeBuff);
			
			timeBuff += boxTime;
			i++;
		}, this);
	}
	
	else if(effects[1] == 'random'){
		boxTime = boxTime / 3;
		
		this.shuffle(boxes).each(function(box){
			var fxop = vars.fxop;
			if(i == last){
				fxop = Object.clone(vars.fxop);
				fxop.onComplete = this.animFinished.bind(this);
			}
			
			setTimeout(function(){
				new Fx.Morph(box, fxop).start({ opacity: 1 });
			}, timeBuff);
			
			timeBuff += boxTime;
			i++;
		}, this);
	}
	else if(effects[1] == 'rain'){
		var rowIndex = 0,
		colIndex = 0,
		arr2d = [];
		
			// Split boxes into 2D array
			arr2d[rowIndex] = [];
			
			if(effects[2] == 'reverse'){
				boxes = boxes.reverse();
			}
			
			boxes.each(function(box){
				arr2d[rowIndex][colIndex] = box;
				colIndex++;
				if(colIndex == options.boxCols){
					rowIndex++;
					colIndex = 0;
					arr2d[rowIndex] = [];
				}
			});
			
			// Run animation
			var slider = this;
			for(var cols = 0; cols < (options.boxCols * 2); cols++){
				var prevCol = cols;
				for(var rows = 0; rows < options.boxRows; rows++){
					if(prevCol >= 0 && prevCol < options.boxCols){
						
						(function(row, col, time, i) {
							var box = $(arr2d[row][col]),
							w = box.getWidth(),
							h = box.getHeight(),
							fxop = vars.fxop;
							
							if(i == last){
								fxop = Object.clone(vars.fxop);
								fxop.onComplete = slider.animFinished.bind(slider);
							}
							
							if(effects[3] == 'grow'){
								box.setStyles({
									width: 0,
									height: 0
								});
							}
							
							else if(effects[3] == 'jelly'){
								w = 0;
								h = 0;
							}
							
							setTimeout(function(){
								new Fx.Morph(box, fxop).start({ opacity: 1 - opacity, width: w, height: h });
							}, time);
							
						})(rows, prevCol, timeBuff, i);
						i++;
					}
					prevCol--;
				}
				timeBuff += boxTime;
			}
		}
	},
	
	animSlide: function(effects, oldImg, curImg){
		
		var options = this.options,
		vars = this.vars,
		img = curImg;
		
		if(effects[3] == 'inv'){
			img = oldImg;
		}
		
		vars.sliceImg.set('src', effects[3] == 'inv' ? curImg.src : oldImg.src);
		
		var slice = this.createSlice(img),
		fxop = Object.clone(vars.fxop),
		mapOn = { left: 'left', right: 'right', up: 'top', down: 'bottom' },
		mapOff = { left: 'right', right: 'left', up: 'bottom', down: 'top' },
		value = ['left', 'right'].contains(effects[2]) ? options.mainWidth : options.mainHeight,
		styleOn = { opacity: 1},
		styleOff = { opacity: 0.5 };
		
		styleOff[mapOn[effects[2]]] = -value;
		styleOff[mapOff[effects[2]]] = '';
		
		styleOn[mapOn[effects[2]]] = 0;
		
		if(effects[3] == 'inv'){
			styleOn.opacity = 0.5;
			styleOn[mapOn[effects[2]]] = - value;
			
			styleOff.opacity = 1;
			styleOff[mapOn[effects[2]]] = 0;
			styleOff[mapOff[effects[2]]] = '';
		}
		
		slice.setStyles(styleOff);
		
		fxop.onComplete = this.animFinished.bind(this);
		
		new Fx.Morph(slice, fxop).start(styleOn);
	},
	
	animCircle: function(effects, oldImg, curImg){
		
		var options = this.options,
		vars = this.vars,
		img = curImg,
		opacity = 0;
		
		if(effects[1] == 'in'){
			img = oldImg;
			opacity = 1;
		}
		
		vars.sliceImg.set('src', effects[1] == 'in' ? curImg.src : oldImg.src);
		
		var circles = this.createCircles(img, opacity),
		timeBuff = 100,
		last = circles.length -1;
		
		if(effects[1] == 'in'){
			circles = circles.reverse();
		}
		
		circles.each(function(circle, i){
			var fxoptions = vars.fxop;
			if(i == last){
				fxoptions = Object.clone(vars.fxop);
				fxoptions.onComplete = this.animFinished.bind(this);
			}
			
			setTimeout(function(){
				new Fx.Morph(circle, fxoptions).start({opacity: 1 - opacity});
			}, timeBuff);
			
			timeBuff += vars.boxTime;
		}, this);
	},
	
	animFade: function(effects, oldImg, curImg){
		
		var vars = this.vars,
		slice = this.createSlice(curImg),
		fxop = Object.clone(vars.fxop),
		styleOn = {
			opacity: 1
		};
		
		vars.sliceImg.set('src', oldImg.src);
		
		fxop.onComplete = this.animFinished.bind(this);
		
		if(effects[1] == 'four'){
			var	tr = slice.clone(),
			bl = slice.clone(),
			br = slice.clone(),
			style = 'background-position';
			
			vars.mainFrame.adopt(this.shuffle([bl, br, tr]));	//the order here can affect the effect
			
			slice.setStyle(style, '-100px -100px');
			tr.setStyle(style, '100px -100px');
			bl.setStyle(style, '-100px 100px');
			br.setStyle(style, '100px 100px');
			
			styleOn[style] = '0px 0px';
			
			[tr, bl, br].each(function(fslice){
				new Fx.Morph(fslice, vars.fxop).start(styleOn);
			});
		}
		
		new Fx.Morph(slice, fxop).start(styleOn);
	},
	
	shuffle: function(arr){
		for(var j, x, i = arr.length; i; j = parseInt(Math.random() * i), x = arr[--i], arr[i] = arr[j], arr[j] = x);
			return arr;
	},
	
	css3: function(elms, props) {
		var css = {},
		prefixes = ['moz', 'ms', 'o', 'webkit'];
		
		for(var prop in props) {
			// Add the vendor specific versions
			for(var i=0; i<prefixes.length; i++){
				css['-'+prefixes[i]+'-'+prop] = props[prop];
			}
			
			// Add the actual version	
			css[prop] = props[prop];
		}
		
		elms.setStyles(css);
		
		return elms;
	},
	
	showDescription: function(){
		var vars = this.vars;
		
		vars.maskDescFx.start(vars.maskValueOn);
	},
	
	hideDescription: function(){
		var vars = this.vars;
		
		vars.maskDescFx.start(vars.maskValueOff);
	},
	
	swapDescription: function(){
		var vars = this.vars;
		
		vars.maskDesc.getElements('.ja-slide-desc').dispose();
		if (typeOf(vars.desciptions[vars.curIdx]) == 'element'){
			vars.desciptions[vars.curIdx].inject(vars.maskDesc);
		}
	},
	
	initMasker: function(){
		var options = this.options,
		vars = this.vars,
		slider = vars.slider,
		maskDesc = slider.getElement('.maskDesc');
		
		if(!maskDesc){
			return;
		}
		
		if (options.showDesc) {
			maskDesc.setStyles({
				'display': 'block',
				'position': 'absolute',
				'width': options.maskWidth,
				'height': options.maskHeigth,
				'opacity': options.maskOpacity
			});
			
			if (options.animation === 'move' && options.maskStyle) {
				//options.maskAlign = 'left';
				options.maskTransitionStyle = 'opacity';
			}
			
			maskDesc.setStyle(options.maskAlign, Math.max(0, vars.rearSize,options.edgemargin));
			
			var descs = vars.desciptions || slider.getElements('.ja-slide-desc'),
			property = options.maskTransitionStyle == 'opacity' ?  'opacity' : options.maskAlign,
			valueOn = property == 'opacity' ? options.maskOpacity : 1 + options.edgemargin,
			valueOff = property == 'opacity' ? 0.001 : (options.maskAlign == 'top' || options.maskAlign == 'bottom' ? -options.maskHeigth : -options.maskWidth),
			
			maskDescFx = new Fx.Tween(maskDesc, {
				property: property,
				duration: 400,
				transition: options.maskTransition,
				link: 'cancel'
			});

			if (options.descTrigger === 'mouseover') {
				$$([maskDesc, vars.mainFrame]).addEvents({
					'mouseenter': this.showDescription.bind(this),
					'mouseleave': this.hideDescription.bind(this)
				});
				
				maskDescFx.set(valueOff);
			} else {
				maskDesc.setStyle('opacity', options.maskOpacity);
			}
			
			Object.append(vars, {
				maskValueOn: valueOn,
				maskValueOff: valueOff,
				maskDescFx: maskDescFx,
				maskDesc: maskDesc,
				desciptions: descs
			});
			
		} else {
			maskDesc.setStyle('display', 'none');
		}
	},
	
	initThumbAction: function () {
		var options = this.options,
		vars = this.vars;
		
		var thumbWrap = vars.slider.getElement('.ja-slide-thumbs-wrap');
		if(!thumbWrap){
			return false;
		}
		
		if (options.thumbType) {
			var thumbMask = thumbWrap.getElement('.ja-slide-thumbs-mask'),
			thumbBox = thumbWrap.getElement('.ja-slide-thumbs'),
			thumbItems = vars.thumbItems || thumbBox.getElements('.ja-slide-thumb'),
			handleBox = thumbWrap.getElement('.ja-slide-thumbs-handles'),
			handleItems = vars.handleItems || handleBox.getChildren(),
			
			isHorz = (options.thumbOrientation == 'horizontal'),
			thumbAnimStyle = isHorz ? 'left' : 'top',
			thumbStep = isHorz ? options.thumbWidth + options.thumbSpaces[0] : options.thumbHeight + options.thumbSpaces[1],
			thumbStartIdx = typeof vars.thumbStartIdx != 'undefined' ? vars.thumbStartIdx : Math.max(0, Math.min(vars.curIdx - options.thumbItems + 2, vars.total - options.thumbItems)),
			fxoptions = Object.clone(vars.fxop);
			
			fxoptions.property = thumbAnimStyle;
			
			var	thumbMaskFx = new Fx.Tween(thumbMask, fxoptions),
			thumbBoxFx = new Fx.Tween(thumbBox, fxoptions).set(-thumbStartIdx * thumbStep),
			handleBoxFx = null;
			
			if(handleItems.length){
				handleBoxFx = new Fx.Tween(handleBox, fxoptions).set(-thumbStartIdx * thumbStep);
			}
			
			$$([thumbBox, handleBox]).setStyle('left', 0);
			[handleItems, thumbItems].each(function(items){
				if(items.length){
					items.setStyles({
						'width': options.thumbWidth,
						'height': options.thumbHeight,
						'margin-right': options.thumbSpaces[0],
						'margin-bottom': options.thumbSpaces[1]
					}).removeClass('active')[vars.curIdx].addClass('active');

					items.getLast().setStyles({
						'margin-right': '',
						'margin-bottom': ''
					});
				}
			});
			
			if(vars.slider.hasClass('ja-articles')){
				handleItems.setStyles({
					'opacity':'0.001',
					'background':'#FFF'
				});
			}
			
			thumbMask.setStyle(isHorz ? 'width' : 'height', 5000).setStyle(thumbAnimStyle, thumbStartIdx * thumbStep - 2000),
			thumbWrap.setStyles(isHorz ? { 
				'width': thumbStep * options.thumbItems - options.thumbSpaces[0],
				'height': options.thumbHeight
			} : { 
				'width': options.thumbWidth,
				'height': thumbStep * options.thumbItems - options.thumbSpaces[1]
			});
			
			$$([thumbWrap.getElement('.ja-slide-thumbs-mask-left'), thumbWrap.getElement('.ja-slide-thumbs-mask-right')]).setStyles({
				'width': isHorz ? 2000 : options.thumbWidth,
				'height': isHorz ? options.thumbHeight : 2000,
				'opacity': options.thumbOpacity
			});
			
			thumbWrap.getElement('.ja-slide-thumbs-mask-center').setStyles({
				'width': options.thumbWidth,
				'height': options.thumbHeight,
				'opacity': options.thumbOpacity
			});
			
			handleItems.each(function(item, idx){
				item.addEvent(options.thumbTrigger, function(){
					this.prepare(true, idx);
				}.bind(this))
			}, this);
			
			handleBox.addEvent('mousewheel', function (e) {
				if (e.wheel < 0) {
					e.stop();
					this.next(true);
				} else {
					e.stop();
					this.prev(true);
				}
			}.bind(this));
			
			Object.append(vars, {
				thumbStartIdx: thumbStartIdx,
				thumbStep: thumbStep,
				thumbMaskFx: thumbMaskFx,
				thumbBoxFx: thumbBoxFx,
				handleBoxFx: handleBoxFx,				
				thumbItems: thumbItems,
				handleItems: handleItems
			});
			
		} else {
			thumbWrap.setStyle('display', 'none');
		}
	},

	initControlAction: function () {
		var options = this.options,
		slider = this.vars.slider,
		controls = ['prev', 'play', 'stop', 'playback', 'next'],
		btnarr;
		
		for (var j = 0, jl = controls.length; j < jl; j++) {
			if(this[controls[j]]){
				btnarr = slider.getElements('.ja-slide-' + controls[j]);
				
				for (var i = 0, il = btnarr.length; i < il; i++) {
					btnarr[i].addEvent(options.navBtnTrigger, this[controls[j]].bind(this, true))
					.addEvent(options.navBtnTrigger, function(){this.blur();});
				}
			}
		}
	},
	
	initMainCtrlButton: function(){
		var options = this.options,
		vars = this.vars,
		mainWrap = vars.mainWrap,
		mainCtrlBtns = $$([mainWrap.getElement('.ja-slide-prev'), mainWrap.getElement('.ja-slide-next')]);
		
		if(options.showNavBtn){
			
			mainCtrlBtns.setStyles({
				'opacity': options.navBtnOpacity,
				'width': options.direction == 'horizontal' ? Math.max(vars.rearSize - vars.mainItemSpace / 2, 0) : options.mainWidth,
				'height': options.direction == 'horizontal' ? options.mainHeight : Math.max(0, vars.rearSize - vars.mainItemSpace / 2)
			}).removeEvents('mouseenter').removeEvents('mouseleave').addEvents({
				'mouseenter': function () {
					this.setStyle('opacity', options.navBtnOpacity / 2);
				},
				'mouseleave': function () {
					this.setStyle('opacity', options.navBtnOpacity);
				}
			});
			
		} else {
			mainCtrlBtns.setStyle('display', 'none');
		}
	},
	
	initMainItemAction: function(){
		var options = this.options;

		if (options.urls) {
			var vars = this.vars,
			anchor = function(from, limit){
				if(!limit){
					limit = vars.slider;
				}

				while(from && from != limit){
					if(from.get('tag').toLowerCase() == 'a'){
						return from;
					}
					
					from = from.getParent();
				}

				return null;
			},

			handle = function(e){
				var index = vars.mainItems.indexOf(this);
				
				if(index == -1){
					index = vars.curIdx;
				}
				
				var url = options.urls[index],
				target = options.targets[index],
				link = anchor(e.target);

				if(link && link.length){
					return true;
				}

				if (url) {
					e.stop();
					url = url.replace(/&amp;/g,'&');
					if (target.indexOf('_blank') != -1){
						window.open(url, 'JAWindow');
					} else {
						window.location.href = url;
					}
				}
				
				return false;
			};
			
			$$([vars.mainFrame, vars.maskDesc].append(Array.from(vars.mainItems))).addEvent('click', handle);
		}
	},
	
	initHoverBehavior: function(){	
		var vars = this.vars,
		slider = vars.slider,
		buttons = [],
		controls = ['prev', 'play', 'stop', 'playback', 'next'];
		
		for (var j = 0, jl = controls.length; j < jl; j++) {
			buttons.append(Array.from(slider.getElements('.ja-slide-' + controls[j])));
		}
		
		buttons.append(Array.from(vars.handleItems));
		
		$$(buttons).addEvents({
			'mouseenter': function () {
				this.addClass('hover');
			},
			'mouseleave': function () {
				this.removeClass('hover');
			}
		});
	},
	
	initProgressBar: function(){
		var options = this.options,
		vars = this.vars,
		progress = vars.slider.getElement('.ja-slide-progress');
		
		if(!progress){
			options.showProgress = false;
			
			return false;
		}
		
		if(options.showProgress){
			Object.append(vars, {
				progressWidth: options.mainWidth,
				progressFx: new Fx.Tween(progress, {
					property: 'width',
					duration: options.interval - options.duration,
					transition: Fx.Transition.Linear,
					link: 'cancel'
				})	
			});
		} else {
			progress.setStyle('display', 'none');
		}
	},
	
	initLoader: function(){
		var vars = this.vars,
		loader = vars.slider.getElement('.ja-slide-loader');
		
		if(!loader){
			return false;
		}
		
		Object.append(vars, {
			loader: loader,
			loaderFx: new Fx.Tween(loader, {
				property: 'opacity',
				duration: 250,
				link: 'cancel'
			})
		});
	}
});

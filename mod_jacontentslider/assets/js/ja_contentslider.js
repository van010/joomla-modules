/**
 * ------------------------------------------------------------------------
 * JA Content Slider Module for J25 & J34
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

function JS_ContentSlider(options){
  var $ = jQuery;
  this.ef_u = {};
  this.ef_d = {};
  this.ef_l = {};
  this.ef_r = {};
  this.elements = [];
  this.options = Object.assign({
    w: 100,
    h: 200,
    num_elem: 4,
    total: 0,
    url: '',
    mode: 'horizontal',
    duration: 1000,
    interval: 3000,
    auto: 1,
  }, options || []);
  
  this.init = function (options) {
    if (options.total){
      if(options.total < options.num_elem){
        options.num_elem = options.total;
      }
      this.elements = new Array(options.total);
    }else {
      this.elements = [];
    }
    this.current = 0;
    
    const wrapper = $(options.wrapper);
    wrapper.css({
      'position': 'relative',
      'overflow': 'hidden',
    });
    if (options.mode === 'vertical'){
      wrapper.css({
        'width': options.w,
        'height': options.h * options.num_elem,
      })
    }else {
      wrapper.css({
        'width': options.w  * options.num_elem,
        'height': options.h,
      })
    }
    
    const elems = wrapper.find('.content_element');
    elems.css({
      'width': options.w,
      'height': options.h,
      // 'display': 'none',
    });
    for (var i=0; i<=options.num_elem; i++){
      this.ef_u[i] = { 'top': [ i*options.h, (i-1)*options.h] };
      this.ef_d[i] = { 'top': [ (i-1)*options.h, i*options.h] };
      this.ef_l[i] = { 'left': [ i*options.w, (i-1)*options.w] };
      this.ef_r[i] = { 'left': [ (i-1)*options.w, i*options.w] };
    }
  }
  
  this.getDirection = function () {
    if (this.options.mode === 'vertical'){
      if (this.options.direction === 'left' || this.options.direction === 'up'){
        return this.ef_u;
      }
      return this.ef_d;
    }
    if (this.options.direction === 'left' || this.options.direction === 'up'){
      return this.ef_l;
    }
    return this.ef_r;
  }
  
  this.createObj = function (text, idx) {
    const divObj = $('<div>', {
      id: 'jsslide_' + idx,
      class: 'jsslide',
    }).appendTo(this.options.wrapper);
    
    divObj.html(text);
    divObj.css({
      'position': 'absolute',
      'width': this.options.w,
      'height': this.options.h,
    });
    return divObj;
  }
  
  this.add = function (text) {
    const idx = this.elements.length;
    const divObj = this.createObj(text, idx);
    if (this.elements.length > 1){
      divObj.after(this.elements[idx-2]);
    }
    this.hide(divObj);
    this.elements.push(divObj);
  }
  
  this.update = function (text, idx) {
    const divObj = this.createObj(text, idx);
    divObj.css({'z-index': 1});
    this.hide(divObj);
    this.elements[idx] = divObj;
  }
  
  this.hide = function (el) {
    if (this.options.mode === 'vertical'){
      el.css({
        'top': '-999em',
        'left': 0,
      });
    }else{
      el.css({
        'top': 0,
        'left': '-999em',
      });
    }
  }
  
  this.setPos = function (els) {
    if (!els){
      els = this.getRunElems();
    }
    const options = this.options;
    var posT;
    $.each(els, function (idx, el) {
      if (el){
        el = $(el);
        if (options.mode === 'vertical'){
          // left = up
          if (options.direction === 'left' || options.direction === 'up'){
            posT = options.h*idx;
          }else{
            posT = options.h*(idx-1)
          }
          el.css({'top': posT});
          loadImage(el);
        }else{
          // left = up
          if (options.direction === 'left' || options.direction === 'up'){
            el.css({'left': options.w*idx});
          }else{
            el.css({'left': options.w*(idx-1)});
          }
          loadImage(el);
        }
      }
    })
  }
  
  var loadImage = function (el) {
    const holder = el.find('.ja-image');
    if (holder){
      const img = holder.prop('rel');
      if (img !== '' && img != null){
        holder.html(img);
        holder.prop('rel', '');
      }
    }
  }
  
  this.getRunElems = function () {
    var objs = [];
    var direction = this.options.direction;
    var adj = (direction === 'left' || direction === 'up')
      ? 0
      : this.elements.length-1;
    for (var i=0; i<=this.options.num_elem; i++){
      objs[i] = this.elements[(this.current+i+adj) % this.elements.length];
    }
    if (this.options.total <= this.options.num_elem){
      if (direction === 'left' || direction === 'up'){
        objs[this.options.num_elem] = null;
      }else{
        objs[0] = null;
      }
    }
    return objs;
  }
  
  this.start = function () {
    this.clearTimeOut();
    if (!this.elements[this.next()]){
      this.nextRun();
      return;
    }
    if (this.elements[this.next()] === 'fetching'){
      this.nextRun();
      return;
    }
    if (this.running) return;
    this.running = 1;
    
    var objs = this.getRunElems();
    this.setPos(objs);
    
    var direction = this.getDirection();
    var promises = [];
    
    objs.forEach((el, idx) => {
      promises.push(new Promise((resolve, reject) => {
        var status = direction[idx];
        if (!status){
          alert('Error!');
          throw 'Error.!';
        }
        var opt = {};
        for (var key in status){
          opt[key] = status[key][1];
        }
        $(el).stop(true, true).animate(opt, this.options.duration, function () {
          resolve()
        });
      }));
    })
    Promise.all(promises).then(() => {
      this.end();
      this.current = this.nextCurr();
    });
  }
  
  this.end = function () {
    this.running = 0;
    this.nextRun();
  }
  
  this.clearTimeOut = function () {
    if(this.timeOut) {
      clearTimeout(this.timeOut);
      this.timeOut = 0;
    }
  }
  
  this.nextRun = function () {
    this.clearTimeOut();
    // if (this.options.total <= this.options.num_elem) return;
    if (this.options.auto){
      this.timeOut = setTimeout(this.start.bind(this), this.options.interval);
      this.fetchNext();
    }
  }
  
  this.nextCurr = function (){
    if(this.options.direction === 'left' || this.options.direction === 'up'){
      return (this.current+1) % this.elements.length;
    }
    return (this.current+this.elements.length-1) % this.elements.length;
  }
  
  this.next = function () {
    if(this.options.direction === 'left' || this.options.direction === 'up'){
      return (this.current+this.options.num_elem) % this.elements.length;
    }
    return (this.current+this.elements.length-1) % this.elements.length;
  }
  
  this.fetchNext = function(){
    var next = this.next();
    //alert(this.current); && self.elements[this.current]
    if (!this.elements[next]){
      this.elements[next] = 'fetching';
      var url = this.options.url + '?total='+this.options.total+'&news='+next+'&loadajax=1&modid='+this.options.modid;
      new Request(url,{
        method:'get',
        onComplete:function(request) {
          this.update(request,next)
        }.bind(this)
      }).send();
      return;
    }
  };
  
  this.fetchUpdate = function(text,next){
    this.update(text, next);
  };
  
  this.setDirection = function (direction){
    this.options.direction = direction;
  }
  
  this.init(this.options);
};
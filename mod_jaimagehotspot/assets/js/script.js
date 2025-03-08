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

function scaleIconFollowScreen(imgID, wrapper) {
    if (!imgID || !wrapper) return ;
    var imgEl = document.getElementById(imgID);
    if (!imgEl) return ;
    let realW = imgEl.naturalWidth;
    let currentW = jQuery('#'+imgID).width();
    jQuery('#'+wrapper).find('a.point').each(function() {
        var scale = currentW/realW || 1;
        jQuery(this).css('transform', 'scale('+(scale)+')');
    });
}


function addHammerJS (imgID, maxZoom, activeZ2CFD) {
    // should we change how we add the params in the future ?
    // activeZ2CFD => active double click zoom for desktop. depend on zooming mobile function.
    let ele = jQuery('#'+imgID+' .jai-map-container-scale');
    let currentScale = 1; // global scale value;
    let CurrentTranX=0, CurrentTranY=0; // default will be center of image.
    let PrevTranX=0, PrevTranY=0;
    let prevScale = 1; // global past of current scale
    let myElement = document.getElementById(imgID);
    let scaleIMG = ele.find('img');

    let mc = new Hammer.Manager(myElement);
    mc.add( new Hammer.Tap() );
    mc.add( new Hammer.Tap({ event: 'doubletap', taps: 2 }) );
    mc.get('doubletap').recognizeWith('tap');
    mc.add( new Hammer.Pinch() );
    mc.add( new Hammer.Pan() );
    mc.get('pan').set({ direction: Hammer.DIRECTION_ALL });
    
    if (is_mobile_device()) {
        mc.on("panleft panright panup pandown", function(ev) {
            if (currentScale <= 1) return;
            // calcualte 4 edge of the box
            let maxRangeW = (scaleIMG.width() * (currentScale - 1))/2;
            let maxRangeH = (scaleIMG.height() * (currentScale - 1))/2;

            let transValueX = ev.deltaX - PrevTranX;
            let transValueY = ev.deltaY - PrevTranY;

            CurrentTranX+=transValueX;
            CurrentTranY+=transValueY;
            PrevTranX = ev.deltaX;
            PrevTranY = ev.deltaY;

            // prevent move out the range.
            if (CurrentTranX > maxRangeW) CurrentTranX = maxRangeW;
            if (CurrentTranX < -maxRangeW) CurrentTranX = -maxRangeW;
            if (CurrentTranY > maxRangeH) CurrentTranY = maxRangeH;
            if (CurrentTranY < -maxRangeH) CurrentTranY = -maxRangeH;

            ele.css('transform', 'translateX('+CurrentTranX+'px) translateY('+CurrentTranY+'px) scale('+(currentScale)+')');
        });
        mc.on("panend", function(ev) {
            // will update the last position of scale.
            PrevTranX = 0;
            PrevTranY = 0;
        });
        mc.on("pinch", function(ev) {
            // always set prevent click icon
            pinchDuration=1;
            console.log('pinchDuration '+pinchDuration);
            if (ev.isFinal) {
                // release prevent MUST be settimeout.
                setTimeout(function() {
                    prevScale=1; // reset prev scale to default;
                    pinchDuration=0;
                    console.log('pinchDuration '+pinchDuration);
                }, 100);
            }
    
            let currentScaleVal = ev.scale - prevScale;
            // max range scale
            if ((currentScale <= 1 && currentScaleVal < 0) || (currentScale >= maxZoom && currentScaleVal > 0)) return;
    
    //         if (currentScaleVal >= 0.01 || currentScaleVal <= -0.01) {
                // calculate translate xy here.
                let percentScale = (currentScale + currentScaleVal) / currentScale;
                CurrentTranX = Math.floor(CurrentTranX*percentScale);
                CurrentTranY = Math.floor(CurrentTranY*percentScale);

                // calculate scale here.
                currentScale += currentScaleVal;
                prevScale = ev.scale;
    //         }
            ele.css('transform', 'translateX('+CurrentTranX+'px) translateY('+CurrentTranY+'px) scale('+(currentScale)+')');
        });
        jQuery('#'+imgID+' a.point').hammer().bind("pan", function(ev) {
            pinchDuration=1;
        });
        jQuery('#'+imgID+' a.point').hammer().bind("panend", function(ev) {
            setTimeout(function() {
                pinchDuration=0;
            }, 100);
        });
    }

    // if in desktop and disable double click zoom. we stop the function. not add the doubletap action.
    if (!is_mobile_device() && !activeZ2CFD)
        return;

    mc.on("doubletap", function(ev) {
        PrevTranX=0;
        PrevTranY=0;
        CurrentTranX=0;
        CurrentTranY=0;
        if (currentScale == 1) {
            let WillScale = 2;
            CurrentTranX = (scaleIMG.width() / 2 - ev.srcEvent.layerX) * WillScale-1;
            CurrentTranY = (scaleIMG.height() / 2 - ev.srcEvent.layerY) * WillScale-1;

            // calcualte 4 edge of the box
            let maxRangeW = (scaleIMG.width() * (WillScale - 1))/2;
            let maxRangeH = (scaleIMG.height() * (WillScale - 1))/2;

            if (CurrentTranX > maxRangeW) CurrentTranX = maxRangeW;
            if (CurrentTranX < -maxRangeW) CurrentTranX = -maxRangeW;
            if (CurrentTranY > maxRangeH) CurrentTranY = maxRangeH;
            if (CurrentTranY < -maxRangeH) CurrentTranY = -maxRangeH;

            ele.css('transform', 'translateX('+CurrentTranX+'px) translateY('+CurrentTranY+'px) scale('+WillScale+')');
            currentScale=WillScale;
        } else {
            currentScale=1;
            ele.css('transform', 'scale(1)');
        }
    });
}

// only call this function on touch device.
function mobileCenter($e) {
    jQuery('.japopwarper').fadeIn();
    $e.css('position', 'fixed');
    // always center left and right.
    centerObjResize($e);
}

// Center obj with screen
function centerObj(obj) {
    if (obj.length) {
        _left = (jQuery(window).width() - obj.width())/2;
        obj.css('left', _left+'px');
        _top = jQuery(window).scrollTop() + (jQuery(window).height()-obj.height())/2;
        obj.css('top', (_top)+'px');
    }
}

// Center obj fixed.
function centerObjResize(obj) {
    if (obj.length) {
        _left = (jQuery(window).width() - obj.width())/2;
        obj.css('left', _left+'px');
        _top = (jQuery(window).height() - obj.height())/2;
        obj.css('top', _top+'px');
    }
}

function is_iDevice(){
    return navigator.platform.match(/iPhone|iPod|iPad/i);
}

function mobileCenterAfterOrient() {
    // center obj after orientation change
    centerObjResize(jQuery('.webui-popover.in'));
    centerObjResize(jQuery('.japopover.japopmedia'));
}

function detectVideo(_e, $e, pre_font_awesome) {
    var $elm = _e.parent();
    if ($e.find('.jashowvideo').length) {
        $e.find('.jashowvideo').off().unbind().bind('click', function() {
            jQuery('.japopwarper').fadeIn();
            vwidth=$elm.data('vwidth');
            vheight=$elm.data('vheight');
            let idPOP = $elm.attr('id').replace(/[a-zA-Z -]+/, '');
            jQuery('body').append('<div class="japopmedia'+idPOP+' japopover japopmedia webui-popover '+$elm.data('bgcolor')+' touchdv popover fade in" style="position:fixed;z-index: 99999;width:'+vwidth+'px;max-width:'+(vwidth)+'px;height:'+(vheight)+'px;display: block;">'+
            `<span class="popover-close"> <i class="${pre_font_awesome}remove"></i> </span>`+
            '<div class="popover-content">'+jQuery(this).data('ifr')+'</div>'+
            '</div>');
            jQuery('.japopmedia'+idPOP).find('.popover-close').off().unbind().bind('click', function(){
                jQuery(this).parent('.japopmedia').remove();
                jQuery('.japopwarper').fadeOut();
            });
            _left = (jQuery(window).width() - vwidth)/2;
            jQuery('.japopover.japopmedia').css('left', _left+'px');
            _top = (jQuery(window).height() - vheight)/2;
            jQuery('.japopover.japopmedia').css('top', _top+'px');
            WebuiPopovers.hideAll(); // Hide all popovers
        });
    }
}

function OverAllData(_e, data, jaihp_settings) {
    var pre_class_font_awesome = 'fa fa-';
    if (parseInt(jversion) >= 5){
        var pre_class_font_awesome = 'fas fa-';
    }
    var item_settings = {
        content:data.details,
        width:(data.popwidth == undefined ? 'auto' : data.popwidth), // default width
        height:(data.popheight == undefined ? 'auto' : data.popheight), // default height
//      container: jQuery('body'), // container will always be window.
        placement:data.placement ? data.placement : 'auto',
//      cache:true,
        style: /*'japopover popover '+*/data.bgcolor,
        direction:jadir,
        trigger:is_mobile_device() ? 'click' : jaihp_settings.trigger, // always click on mobile device.
        arrow:!is_mobile_device(), // do not show arrow on mobile device.
        multi: (jaihp_settings.trigger == 'sticky' || jaihp_settings.multiple == 1) ? true : false,
        animation:jaihp_settings.anim,
        onShow : function($e) {
            if (is_mobile_device())
                mobileCenter($e);
        },
        onHide: function($e) {
            jQuery('span[data-target="'+$e.attr('id')+'"]').show();
        },
        delay: {hide: jaihp_settings.hideDelay}
    };

    item_settings.type='async';
    item_settings.url=rootURL+'index.php?option=com_ajax&module=jaimagehotspot&format=raw&method=getcontent&Itemid='+menuID;
    item_settings.async= {
        type:'POST',
        data: data,
        before: function(that, xhr, settings) {},//executed before ajax request
        success: function(that, data) {
            if (data.search('twitter') !== -1) {
                that.setContent(that.content);
                var $targetContent = that.getContentElement();
                $targetContent.removeAttr('style');
                that.displayContent();
            } else {
                jQuery('.japopwarper_content').html(data);
                if (jQuery('.japopwarper_content').find('img').length) {
                    if (jQuery('.japopwarper_content').find('img').length > 1)
                        $img = jQuery('.japopwarper_content').find('img').first();
                    else 
                        $img = jQuery('.japopwarper_content').find('img');
                    $img.on('load', function(){
                        var $targetContent = that.getContentElement();
                        $targetContent.html(jQuery('.japopwarper_content').html());
                        // check for video
                        detectVideo(that.$element, $targetContent, pre_class_font_awesome);
                        $targetContent.removeAttr('style');
                        that.displayContent();
                        if (is_mobile_device()) 
                            mobileCenter(that.$target);
                    });
                } else {
                    var $targetContent = that.getContentElement();
                    $targetContent.html(data);
                    $targetContent.removeAttr('style');
                    // check for video
                    detectVideo(that.$element, $targetContent, pre_class_font_awesome);
                    that.displayContent();
                }

            }
            if (is_mobile_device()) 
                mobileCenter(that.$target);
        },//executed after successful ajax request
        error: function(that, xhr, data) {} //executed after error ajax request
    };
    if (data.content_type == 'social') {
        if (data.content_url.match('facebook')) {
            item_settings.width=(data.popwidth == undefined ? 370 : data.popwidth);
            item_settings.height=(data.popheight == undefined ? 105 : data.popheight);
            if (is_mobile_device()) {
                item_settings.width=310;
                item_settings.height=105;
            }
        }
        if (data.content_url.match('twitter')) {
            item_settings.width=(data.popwidth == undefined ? 400 : data.popwidth);
            item_settings.height=(data.popheight == undefined ? 230 : data.popheight);
            item_settings.cache=false;
            item_settings.animation=null;
            if (is_mobile_device()) {
                item_settings.width=310;
                item_settings.height=203;
            }
        }
        if (data.content_url.match('pinterest') || data.content_url.match('instagram')) {
            item_settings.width=(data.popwidth == undefined ? 280 : data.popwidth);
            item_settings.height=(data.popheight == undefined ? 'auto' : data.popheight);
            if (is_mobile_device()) {
                item_settings.width=280;
                item_settings.height='auto';
            }
        }
    }
    if (data.content_type == 'video') { // video only.
        item_settings.width=(data.popwidth == undefined ? 280 : data.popwidth);
        item_settings.height=(data.popheight == undefined ? 315 : data.popheight);
        if (is_mobile_device()) {
            item_settings.width=280;
            item_settings.height=315;
        }
    }
    if (data.content_type == 'website') {
        item_settings.width=(data.popwidth == undefined ? 300 : data.popwidth);
        item_settings.height=(data.popheight == undefined ? 'auto' : data.popheight);
        if (is_mobile_device()) {
            item_settings.width=300;
            item_settings.height='auto';
        }
    }
    if (data.content_type == 'default') {
        item_settings.title=data.title;
        item_settings.width=(data.popwidth == undefined ? 300 : data.popwidth);
        item_settings.height=(data.popheight == undefined ? 'auto' : data.popheight);
        if (is_mobile_device()) {
            item_settings.width=300;
            item_settings.height='auto';
        }

        if (Modernizr.touch && data.link) {
            item_settings.content += '<br><a class="mobile-link" target="_blank" href="' + data.link + '">' + Joomla.JText._('JAI_MOBILE_POPUP_LINK') + '</a>';
        }
    }
    
    // not use ajax if do not have image and default type.
    if (data.content_type == 'default' && data.content_img == '') {
        item_settings.type='html';
        item_settings.url=null;
        item_settings.async=null;
    }
    _e.webuiPopover(item_settings);
}

// check if mobile.
function is_mobile_device() {
    try {
        document.createEvent("TouchEvent");
        if (jQuery(window).width() >= 768)
            return false;
        return true;
    } catch (e) {
        return false;
    }
}
function is_landscape() {
    return (jQuery(window).width() > jQuery(window).height());
}

jQuery(document).ready(function() {
    jQuery('body').prepend('<div class="japopwarper_content" style="display:none;"></div>');
    if (!jQuery('.japopwarper').length) {
        jQuery('.jai-map-container').append('<div class="japopwarper"></div>');
        if (!is_mobile_device()) _ev = 'click';
            _ev = 'touchend';
        jQuery('.japopwarper').off().unbind().on(_ev, function(event) {
            jQuery('.japopwarper').fadeOut();
            jQuery('.japopmedia').remove();
            WebuiPopovers.hideAll(); // Hide all popovers
            event.preventDefault();
            return false;
        });
    }
    jQuery(window).on('resize', function(event) {
        if (is_mobile_device()) setTimeout(mobileCenterAfterOrient(), 1000);
    });
});
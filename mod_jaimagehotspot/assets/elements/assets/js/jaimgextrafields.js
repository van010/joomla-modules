jQuery(document).ready(function ($) {
    var originalSubmit = Joomla.submitbutton;
    var options = Joomla.getOptions('imagehotspot');
    var desc_default = options.desc_default;
    var desc = options.desc;
    var maxid = options.maxid;
    var juri_root = Joomla.getOptions('system.paths').root;

    var pre_class_font_awesome = 'fa fa-';
    if (parseInt(jversion) >= 5){
        var pre_class_font_awesome = 'fas fa-';
    }

    if (!desc) { desc = []; }
    if ($('#jform_params_imgpath_preview_img').css('display') != 'none') {
        var $points = desc.map(function(config) {
            return createPoint(config);
        });

        $('#jform_params_imgpath_preview_img').append($points)        
    } else {
        $('#jai_add').hide();
    }
    $('#jai_add').prop('data-count', maxid);

    Joomla.submitbutton = function (task) {
        var imgid = $('#extrafieldimg .adminformlist input[name="imgid"]').val();
        if (imgid) {
            //save pointer setting
            jaiupdate('update', imgid);
        }
        originalSubmit(task);
    }

    function createPoint(config) {
        config = config || {};

        var $point = $('<span></span>');
        var $hide = $('<span class="hide">Point</span>');
        var $bg = $('<span class="bg"></span>');
        var $anchorx = $('<span class="anchorx"><span class="handlerx"></span></span>');
        var $anchory = $('<span class="anchory"><span class="handlery"></span></span>');

        var iconsize = !isNaN(config.iconsize) && config.iconsize ? +config.iconsize : 30;
        var fontsize = iconsize / 5 * 4;
        var offsetx = !isNaN(config.offsetx) ? +config.offsetx : 10;
        var offsety = !isNaN(config.offsety) ? +config.offsety : 10;
        var iconanchorx = !isNaN(config.iconanchorx) ? +config.iconanchorx : -50;
        var iconanchory = !isNaN(config.iconanchory) ? +config.iconanchory : -50;
        var anchorx = !isNaN(config.iconanchorx) ? config.iconanchorx : 0;
        var anchory = !isNaN(config.iconanchory) ? config.iconanchory : 0;
        var type = config.ptype ? config.ptype : 'icon';
        var color = config.iconcolor ? config.iconcolor : '#ffffff';

        $point.attr('id', 'ja-marker-' + config.imgid);
        $point.addClass('point');
        $point.css({
            'top': offsety + '%',
            'left': offsetx + '%',
        })

        var bgClass = '';
        if (type === 'icon') {
            icon = config.icon ? config.icon : 'map-marker';
            bgClass = pre_class_font_awesome + icon;
        } else if (type === 'image' && config.ptype_image) {
            $bg.css('background-image', 'url('+juri_root+'/'+config.ptype_image+')');
        } else if (config.jasetimage) {
            $bg.css('background-image', 'url('+config.jasetimage+')');
        }

        $bg.addClass(bgClass);
        $bg.css({
            'height': iconsize + 'px',
            'width': iconsize + 'px',
            'font-size': fontsize,
            'line-height': iconsize + 'px',
            'transform': 'translate('+ iconanchorx + '%, ' + iconanchory + '%)',
            'color': color
        })

        $point.append([$bg, $hide, $anchorx, $anchory]);
        return $point;
    }

    var cachePImage = '';
    function checkPointimage() {
        if ($('.adminformlist.active').length) {
            if ($('#jaform_params_ptype').val() == 'image') {
                if ($('#extrafieldimg .adminformlist input[name="imgid"]').val().trim() != '') {
                    if (cachePImage != $('#jaform_params_ptype_image').val()) {
                        cachePImage = $('#jaform_params_ptype_image').val();
                        if ($('#extrafieldimg .adminformlist input[name="imgid"]').val()) {
                            $('span#ja-marker-' + $('#extrafieldimg .adminformlist input[name="imgid"]').val() + ' .bg')
                                .removeClass(pre_class_font_awesome + $('#jaform_params_icon').val())
                                .css({
                                    'background-image': 'url(' + juri_root + '/' + cachePImage + ')',
                                });
                            jaiupdate('update', $('#extrafieldimg .adminformlist input[name="imgid"]').val());
                        }
                    }
                }
            }
        }

        setTimeout(function () {
            checkPointimage();
        }, 1000);
    }

    function initPopover(el) {
        var container = el.prev();
        var basepath = el.parents('.field-media-wrapper').data('basepath');
        container.popover('destroy');
        el.tooltip('destroy');
        var value = el.val();

        if (!value) {
            container.popover();
        } else {
            var imgPreview = new Image(200, 200);
            imgPreview.src = basepath + value;

            container.popover({ content: imgPreview });
            el.tooltip({ placement: 'top', title: value });
        }
    }

    $('#jaform_params_ptype').parent().after($('#jasetli'));

    function jadependafter() {
        // auto choose default value for pointer that already exists from old version.
        if ($('#jaform_params_ptype').val() == null || $('#jaform_params_ptype').val() == 'null')
            $('#jaform_params_ptype').val('icon');
        if ($('#jaform_params_content_type').val() == null || $('#jaform_params_content_type').val() == 'null')
            $('#jaform_params_content_type').val('default');
        if ($('#jaform_params_bgcolor').val() == null || $('#jaform_params_bgcolor').val() == 'null')
            $('#jaform_params_bgcolor').val('light');
        if ($('#jaform_params_placement').val() == null || $('#jaform_params_placement').val() == 'null')
            $('#jaform_params_placement').val('auto');
        // end auto choose.

        if ($('#jaform_params_ptype').val() == 'icon') {
            if ($('#jaform_params_icon').val() == '') {
                $('#jaform_params_icon').val('map-marker');
            }
            $('#jaform_params_ptype_image-lbl').parent().hide();
            $('#jasetli').hide();
            $('#jaform_params_icon-lbl').parent().show();
            $('#jaform_params_iconcolor-lbl').parent().show();
            $('span#ja-marker-' + $('#extrafieldimg .adminformlist input[name="imgid"]').val() + ' .bg')
                .css('background-image', '')
                .css('color', $('#jaform_params_iconcolor').val())
                .addClass(pre_class_font_awesome + $('#jaform_params_icon').val());
        } else if ($('#jaform_params_ptype').val() == 'jaset') {
            $('#jaform_params_icon-lbl').parent().hide();
            $('#jaform_params_iconcolor-lbl').parent().hide();
            $('#jaform_params_ptype_image-lbl').parent().hide();
            $('#jaform_params_ptype_jaset-lbl').parent().show();
            $('#jasetli').show();
            $('.jasetimg').each(function () {
                if ($('#jaform_params_jasetimage').val() == $(this).attr('src')) {
                    $('.jasetimgli').removeClass('active');
                    $(this).parent().addClass('active');
                }
            });
            $('span#ja-marker-' + $('#extrafieldimg .adminformlist input[name="imgid"]').val() + ' .bg')
                .removeClass(pre_class_font_awesome + $('#jaform_params_icon').val())
                .css({
                    'background-image': 'url(' + $('#jaform_params_jasetimage').val() + ')',
                });
        } else {
            $('#jaform_params_ptype_image-lbl').parent().show();
            $('#jaform_params_icon-lbl').parent().hide();
            $('#jaform_params_iconcolor-lbl').parent().hide();
            $('#jasetli').hide();
            $('span#ja-marker-' + $('#extrafieldimg .adminformlist input[name="imgid"]').val() + ' .bg')
                .removeClass(pre_class_font_awesome + $('#jaform_params_icon').val())
                .css({
                    'background-image': 'url(' + juri_root + '/' + $('#jaform_params_ptype_image').val() + ')',
                });
        }

        if ($('#jaform_params_content_type').val() == 'default') {
            $('#jaform_params_content_url-lbl').parent().hide();
            $('#jaform_params_content_img-lbl').parent().show();
            $('#jaform_params_link-lbl').parent().show();
            $('#jaform_params_details-lbl').parent().show();
        } else {
            $('#jaform_params_content_url-lbl').parent().show();
            $('#jaform_params_content_img-lbl').parent().show();
            $('#jaform_params_link-lbl').parent().hide();
            $('#jaform_params_details-lbl').parent().hide();
        }

        if ($('#jaform_params_content_type').val() == 'video') {
            $('#jaform_params_vwidth-lbl').parent().show();
            $('#jaform_params_vheight-lbl').parent().show();
        } else {
            $('#jaform_params_vwidth-lbl').parent().hide();
            $('#jaform_params_vheight-lbl').parent().hide();
        }

        if ($('#jaform_params_content_type').val() == 'website') {
            $('#jaform_params_cutnumber-lbl').parent().show();
        } else {
            $('#jaform_params_cutnumber-lbl').parent().hide();
        }
        $('#jaform_params_content_type, #jaform_params_ptype, #jaform_params_bgcolor, #jaform_params_placement').trigger('liszt:updated');
    }

    $('.jasetimgli').click(function () {
        var src = $(this).find('.jasetimg').attr('src');
        $('#jaform_params_jasetimage').val(src);
        $('.jasetimgli').removeClass('active');
        $(this).addClass('active');
        $('span#ja-marker-' + $('#extrafieldimg .adminformlist input[name="imgid"]').val() + ' .bg')
            .removeClass(pre_class_font_awesome + $('#jaform_params_icon').val())
            .css({
                'background-image': 'url(' + src + ')',
            });

        var imgid = $('#extrafieldimg .adminformlist input[name="imgid"]').val();

        if (imgid) {
            //save current setting before load others
            jaiupdate('update', imgid);
        }
    });

    checkPointimage();

    var addpoint = function () {
        if ($("#jform_params_imgpath_preview_img").find("span.point").length > 0) {
            $("#jform_params_imgpath_preview_img span.point").each(function () {
                $(this).on('click', function () {
                    var imgid = $('#extrafieldimg .adminformlist input[name="imgid"]').val();

                    if (imgid) {
                        //save current setting before load others
                        jaiupdate('update', imgid);
                    }
                    $('#jform_params_imgpath_preview_img span.point')
                        .removeClass('active')
                        .css('z-index', '')
                        .draggable().draggable('destroy');

                    var count = ($(this).data('click_count') || 0) + 1;
                    $(this).data('click_count', count);

                    for (var i = 0; i < desc.length; i++) {
                        if (desc[i]['imgid'] == $(this).attr('id').replace('ja-marker-', '')) {
                            $('#extrafieldimg .adminformlist :input').each(function (j, field) {
                                var field_name = field.name.replace('jaform[params][', '').replace(']', '');
                                if (field_name == 'offsetx' || field_name == 'offsety') {
                                    $(this).prop('autocomplete', 'off');
                                    var input_value = desc[i][field_name] >= 100 ? 100 : desc[i][field_name];
                                    $(this).val(input_value);
                                } else {
                                    var input_type = $(this).prop('type');
                                    switch (input_type) {
                                        case 'radio':
                                        case 'checkbox':
                                            if (desc[i][field_name] == field.value) {
                                                $(this).attr('checked', true);
                                            }
                                            break;
                                        case 'select-one':
                                            $(this).val(desc[i][field_name]).trigger('liszt:updated');
                                            break;
                                        case 'textarea':
                                            $(this).val(desc[i][field_name]);
                                            break;
                                        case 'button':
                                            break;
                                        default:
                                            $(this).val(desc[i][field_name] || desc_default[field_name]);
                                            if (options.morethan37) {
                                                if ($(this).attr('id') == 'jaform_params_content_img' || $(this).attr('id') == 'jaform_params_ptype_image') {
                                                    // initPopover($(this));
                                                }
                                            }
                                            if ($(this).hasClass('minicolors-input')) {
                                                var color = desc[i][field_name] || '#fff';
                                                $(this).next().find('.minicolors-swatch-color').removeAttr('style').attr('style', 'background-color: ' + color);
                                            }
                                            break;
                                    }
                                }
                            });
                        }
                    }

                    var pointer_classes = $('#jaform_params_ptype').find('option');
                    for (var i = 0; i < pointer_classes.length; i++) {
                        $(this).find('.bg').removeClass('ja-marker-' + pointer_classes[i].value);
                    }
                    if ($('#jaform_params_ptype').val()) {
                        $(this).find('.bg').addClass('ja-marker-' + $('#jaform_params_ptype').val());
                    }

                    jadependafter();

                    if ($('#extrafieldimg .adminformlist #jai_cancel').length > 0) {
                        $('#extrafieldimg .adminformlist #jai_cancel').remove();
                    }

                    if ($('#jform_params_imgpath_preview_img .point.point-add').length > 0) {
                        $('#jform_params_imgpath_preview_img .point.point-add').remove();
                    }

                    $('#extrafieldimg #jai_remove').show();

                    $('#extrafieldimg .adminformlist').removeClass('deactive').addClass('active');

                    $(this).draggable({
                        cursor: 'move',
                        containment: 'parent',
                        stop: function () {
                            var left = parseFloat($(this).css('left'));
                            var top = parseFloat($(this).css('top'));
                            var width = $('#jform_params_imgpath_preview').width();
                            var height = $('#jform_params_imgpath_preview').height();

                            $('#jaform_params_offsetx').val((left * 100 / width).toFixed(2));
                            $('#jaform_params_offsety').val((top * 100 / height).toFixed(2));
                            jaiupdate('update', $('#extrafieldimg .adminformlist input[name="imgid"]').val());
                        }
                    }).addClass('active').css('z-index', 3);

                    var $mediaPreview = $('.media-preview');
                    $(this).find('.anchorx').height($mediaPreview.height() * 2);
                    $(this).find('.anchory').width($mediaPreview.width() * 2);
                });

            });
        }
        scaleIconFollowScreen('jform_params_imgpath_preview');
    };

    addpoint();

    var jareset = function () {
        $('#extrafieldimg .adminformlist :input').each(function (j, field) {
            var field_name = field.name.replace('jaform[params][', '').replace(']', '');
            if ($(this).prop('id') == 'jaform_params_offsetx' || $(this).prop('id') == 'jaform_params_offsety') {
                $(this).prop('autocomplete', 'off');
                $(this).val(desc_default[field_name]);
            } else {
                var input_type = $(this).prop('type');
                switch (input_type) {
                    case 'radio':
                    case 'checkbox':
                        if (desc_default[field_name] == field.value) {
                            $(this).prop('checked', true);
                        }
                        break;
                    case 'textarea':
                        $(this).val(desc_default[field_name]);
                        break;
                    case 'button':
                        break;
                    default:
                        $(this).val(desc_default[field_name]);
                        break;
                }
            }
        });
    };

    var jaiupdate = function (task, id) {
        switch (task) {
            case 'add':
                var desc_add = {};
                $('#extrafieldimg .adminformlist :input').each(function (j, field) {
                    var input_type = $(this).prop('type');
                    var field_name = field.name.replace('jaform[params][', '').replace(']', '');
                    switch (input_type) {
                        case 'radio':
                        case 'checkbox':
                            if ($(this).prop('checked')) {
                                desc_add[field_name] = field.value;
                            }
                            break;
                        case 'button':
                            break;
                        default:
                            desc_add[field_name] = field.value;
                            break;
                    }

                });
                desc.push(desc_add);
                break;
            case 'remove':
                desc = $.grep(desc, function (n, i) {
                    return (n.imgid != id);
                });
                break;
            case 'update':
                for (var i = 0; i < desc.length; i++) {
                    if (desc[i]['imgid'] == id) {
                        $('#extrafieldimg .adminformlist :input').each(function (j, field) {
                            var input_type = $(this).prop('type');
                            var field_name = field.name.replace('jaform[params][', '').replace(']', '');
                            switch (input_type) {
                                case 'radio':
                                case 'checkbox':
                                    if ($(this).prop('checked')) {
                                        desc[i][field_name] = field.value;
                                    }
                                    break;
                                case 'button':
                                    break;
                                default:
                                    desc[i][field_name] = field.value;
                                    break;
                            }

                        });
                    }
                }
                break;
            default:
                break;
        }

        $('#jform_params_description').val(JSON.stringify(desc));
    };

    var jaremove = function () {
        $('#extrafieldimg #extrafield-action #jai_remove').click(function () {
            var id = $('#extrafieldimg .adminformlist').find('input[name="imgid"]').val();
            $('#ja-marker-' + id).remove();
            jaiupdate('remove', id);
            jareset();
            $('#extrafieldimg .adminformlist').removeClass('active').addClass('deactive');
            $('#extrafieldimg #jai_remove').hide();
        });
    };
    jaremove();
    $('#jai_add').on('click', function () {
        if ($('#jform_params_imgpath_preview_img').css('display') != 'none') {
            $('#jform_params_imgpath_preview_img span.point')
                .removeClass('active')
                .css('z-index', '')
                .draggable().draggable('destroy');
                
            jareset();

            var pointid = $(this).prop('data-count') + 1;
            $(this).prop('data-count', pointid);

            $('#jaform_params_offsetx').val(10);
            $('#jaform_params_offsety').val(10);
            $('#jaform_params_iconanchorx').val(-50);
            $('#jaform_params_iconanchory').val(-50);
           
            var $preview = $("#jform_params_imgpath_preview_img");
            var $point = createPoint({imgid: pointid});

            $point.find('.anchorx').height($preview.height() * 2);
            $point.find('.anchory').width($preview.width() * 2);
            $preview.append($point);

            $point.draggable({
                cursor: 'move',
                containment: 'parent',
                stop: function () {
                    var left = parseFloat($(this).css('left'));
                    var top = parseFloat($(this).css('top'));
                    var width = $('#jform_params_imgpath_preview').width();
                    var height = $('#jform_params_imgpath_preview').height();

                    $('#jaform_params_offsetx').val((left / width * 100).toFixed(2));
                    $('#jaform_params_offsety').val((top / height * 100).toFixed(2));
                    jaiupdate('update', $('#extrafieldimg .adminformlist input[name="imgid"]').val());
                }
            }).addClass('active').css('z-index', 3);
            
            $('#extrafieldimg .adminformlist input[name="imgid"]').val(pointid);

            $('#extrafieldimg .adminformlist').removeClass('deactive').addClass('active');
            jaiupdate('add', $('#extrafieldimg .adminformlist input[name="imgid"]').val());
            addpoint();
            $('#extrafieldimg #jai_remove').show();
            jadependafter();
        }
    });

    $('#extrafieldimg .adminformlist :input').each(function (j, field) {
        if ($(this).prop('name') != 'imgid') {
            $(this).change(function () {
                var maxwidth = (parseFloat($('#jform_params_imgpath_preview').width()) - parseFloat($('#jform_params_imgpath_preview_img span.point').width())) * 100 / parseFloat($('#jform_params_imgpath_preview').width());
                var maxheight = (parseFloat($('#jform_params_imgpath_preview').height()) - parseFloat($('#jform_params_imgpath_preview_img span.point').height())) * 100 / parseFloat($('#jform_params_imgpath_preview').height());
                maxwidth = Math.floor(maxwidth);
                maxheight = Math.floor(maxheight);

                var imgid = $('#extrafieldimg .adminformlist input[name="imgid"]').val();
                if ($(this).prop('id') == 'jaform_params_ptype') {
                    var active_pointer = $('#jform_params_imgpath_preview_img span.active');
                    if (active_pointer.length) {
                        var pointer_classes = $(this).find('option');
                        for (var i = 0; i < pointer_classes.length; i++) {
                            active_pointer.removeClass('ja-marker-' + pointer_classes[i].value);
                        }
                        if ($(this).val()) {
                            active_pointer.addClass('ja-marker-' + $(this).val());
                        }
                    }
                }
                if ($(this).prop('id') == 'jaform_params_offsetx') {

                    if (isNaN($(this).val())) {
                        alert(Joomla.JText._('JAI_INSERT_NUMBERIC'));
                        return;
                    }
                    if ($(this).val() > maxwidth) {
                        $(this).val(maxwidth);
                        alert(Joomla.JText._('JAI_INSERT_NUMBERIC_LESS_THAN') + maxwidth);
                    }
                    if ($(this).val() < 0) {
                        $(this).val(0);
                        alert(Joomla.JText._('JAI_INSERT_NUMBERIC_GREATER_THAN'));
                    }
                    if ($(this).parent().parent().find('input[name="imgid"]').length > 0) {
                        var imgidchange = $(this).parent().parent().find('input[name="imgid"]').val();
                        if ($('#ja-marker-' + imgidchange)) {
                            $('#ja-marker-' + imgidchange).css('left', $(this).val() + '%');
                        }
                    }
                }
                if ($(this).prop('id') == 'jaform_params_offsety') {
                    if (isNaN($(this).val())) {
                        alert(Joomla.JText._('JAI_INSERT_NUMBERIC'));
                    }
                    if ($(this).val() > maxheight) {
                        $(this).val(maxheight);
                        alert(Joomla.JText._('JAI_INSERT_NUMBERIC_LESS_THAN') + maxheight);
                    }
                    if ($(this).val() < 0) {
                        $(this).val(0);
                        alert(Joomla.JText._('JAI_INSERT_NUMBERIC_GREATER_THAN'));
                    }
                    if ($(this).parent().parent().find('input[name="imgid"]').length > 0) {
                        imgidchange = $(this).parent().parent().find('input[name="imgid"]').val();
                        if ($('#ja-marker-' + imgidchange)) {
                            $('#ja-marker-' + imgidchange).css('top', $(this).val() + '%');
                        }
                    }
                }
                jaiupdate('update', imgid);
            });
        }
    });


    $('#jaform_params_ptype').on('change', function () {
        jadependafter();
    });

    $('#jaform_params_content_type').on('change', function () {
        jadependafter();
    });

    $('#jaform_params_offsetx').on('keydown', function (event) {
        var maxwidth = (parseFloat($('#jform_params_imgpath_preview').width()) - parseFloat($('#jform_params_imgpath_preview_img span.point').width())) * 100 / parseFloat($('#jform_params_imgpath_preview').width());
        maxwidth = Math.floor(maxwidth);

        if (event.which == 38 || event.which == 104) {
            if ((parseFloat($(this).val()) + 1) > maxwidth) {
                $(this).val(maxwidth - 1);
                alert(Joomla.JText._('JAI_INSERT_NUMBERIC_LESS_THAN') + maxwidth);
            }

            $('#jaform_params_offsetx').val((parseInt($('#jaform_params_offsetx').val()) + 1));

            var imgidchange = $(this).parent().parent().find('input[name="imgid"]').val();
            if ($('#ja-marker-' + imgidchange)) {
                $('#ja-marker-' + imgidchange).css('left', $(this).val() + '%');
            }

            jaiupdate('update', $('#extrafieldimg .adminformlist input[name="imgid"]').val());

        } else if (event.which == 40 || event.which == 98) {

            if ((parseFloat($(this).val()) - 1) < 0) {
                $(this).val(0);
                alert(Joomla.JText._('JAI_INSERT_NUMBERIC_GREATER_THAN'));
            }

            $('#jaform_params_offsetx').val((parseInt($('#jaform_params_offsetx').val()) - 1));
            var imgidchange = $(this).parent().parent().find('input[name="imgid"]').val();
            if ($('#ja-marker-' + imgidchange).length > 0) {
                $('#ja-marker-' + imgidchange).css('left', $(this).val() + '%');
            }

            jaiupdate('update', $('#extrafieldimg .adminformlist input[name="imgid"]').val());
        }
    });

    $('#jaform_params_iconcolor').on('change', function (event) {
        var imgidchange = $('#extrafieldimg').find('input[name="imgid"]').val();
        if ($('#ja-marker-' + imgidchange).length) {
            $('#ja-marker-' + imgidchange + ' .bg').css('color', $(this).val());
        }
        jaiupdate('update', $('#extrafieldimg .adminformlist input[name="imgid"]').val());
    });

    $('#jaform_params_icon').on('keyup', function (event) {
        var imgidchange = $('#extrafieldimg').find('input[name="imgid"]').val();
        if ($('#ja-marker-' + imgidchange).length) {
            $('#ja-marker-' + imgidchange + ' .bg').removeAttr('class').addClass('bg ui-draggable active ' + pre_class_font_awesome + $(this).val());
        }
        jaiupdate('update', $('#extrafieldimg .adminformlist input[name="imgid"]').val());
    });

    $('#jaform_params_offsety').on('keydown', function (event) {
        var maxheight = (parseFloat($('#jform_params_imgpath_preview').height()) - parseFloat($('#jform_params_imgpath_preview_img span.point').height())) * 100 / parseFloat($('#jform_params_imgpath_preview').height());
        maxheight = Math.floor(maxheight);

        if (event.which == 38 || event.which == 104) {
            if ((parseFloat($(this).val()) + 1) > maxheight) {
                $(this).val(maxheight - 1);
                alert(Joomla.JText._('JAI_INSERT_NUMBERIC_LESS_THAN') + maxheight);
            }

            $('#jaform_params_offsety').val((parseInt($('#jaform_params_offsety').val()) + 1));

            var imgidchange = $('#extrafieldimg').find('input[name="imgid"]').val();

            if ($('#ja-marker-' + imgidchange)) {
                $('#ja-marker-' + imgidchange).css('top', $(this).val() + '%');
            }

            jaiupdate('update', $('#extrafieldimg .adminformlist input[name="imgid"]').val());

        } else if (event.which == 40 || event.which == 98) {
            if ((parseFloat($(this).val()) - 1) < 0) {
                $(this).val(0);
                alert(Joomla.JText._('JAI_INSERT_NUMBERIC_GREATER_THAN'));
            }

            $('#jaform_params_offsety').val((parseInt($('#jaform_params_offsety').val()) - 1));


            var imgidchange = $('#extrafieldimg').find('input[name="imgid"]').val();

            if ($('#ja-marker-' + imgidchange)) {
                $('#ja-marker-' + imgidchange).css('top', $(this).val() + '%');
            }
            jaiupdate('update', $('#extrafieldimg .adminformlist input[name="imgid"]').val());
        }
    });

    $('#jaform_params_iconsize').on('change', function() {
        var $elm = $(this);
        var value = $elm.val();
        var $preview = $('#jform_params_imgpath_preview_img')
        var $activePoint = $preview.find('.point.active .bg');

        $activePoint.css({
            'height': value + 'px',
            'width': value + 'px',
            'line-height': value + 'px',
            'font-size': (value / 5 * 4) + 'px',
        })
    })

    $('#jaform_params_iconanchorx, #jaform_params_iconanchory').on('change', function() {
        var $preview = $('#jform_params_imgpath_preview_img')
        var $bg = $preview.find('.point.active .bg');
        var x = $('#jaform_params_iconanchorx').val();
        var y = $('#jaform_params_iconanchory').val();
        $bg.css('transform', 'translate('+x+'%,'+y+'%)');
    })

    $('#jform_params_imgpath_preview').click(function () {
        $('#jform_params_imgpath_preview_img span.point')
            .removeClass('active')
            .css('z-index', '')
            .draggable().draggable('destroy');

        jareset();
        $('#extrafieldimg .adminformlist').removeClass('active').addClass('deactive');
        $('#extrafieldimg #jai_remove').hide();
    });
    $('.extrafieldimg').parents('.control-group').addClass('marker-wrap');
});

jQuery(window).on('load', function() {
    jQuery(window).on('resize', function() {
        scaleIconFollowScreen('jform_params_imgpath_preview');
    });
    setTimeout(function() {
        scaleIconFollowScreen('jform_params_imgpath_preview');
    }, 1000);
    
});

function scaleIconFollowScreen(imgID) {
    var realW = document.getElementById(imgID).naturalWidth;
    // var currentW = jQuery('#'+imgID).width();
  if (parseInt(jQuery('#'+imgID).width()) < 100){
    var currentW = 1000;
  }
    jQuery('#jform_params_imgpath_preview_img').find('span.point').each(function() {
        if (currentW != 0 && realW != 0)
            jQuery(this).css('transform', 'scale('+(currentW/realW)+')');
    });
}
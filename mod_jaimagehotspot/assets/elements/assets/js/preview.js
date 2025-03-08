jQuery(document).ready(function($) {
    jQuery('.fltlft').parents('.control-group').addClass('japreview');
    var id = 'jform_params_imgpath';
    var input = document.getElementById('jform_params_imgpath');
    if (!input) {
        return;
    }

    var uriRoot = Joomla.getOptions('system.paths').root;
    var oldVal = input.value;

    setInterval(function() {
        var val = input.value;
        if (oldVal === val) {
            return;
        }

        oldVal = val;
        $('#jform_params_imgpath_preview_img img').attr('src', uriRoot + '/' + val);
        var img = document.getElementById(id + '_preview');
        console.log(img.src);

        var value = document.getElementById(id).value;

        if (value === ""){
            document.querySelector('#jform_params_imgpath_preview_img img').removeAttribute('src');
            // hide add mark
            document.getElementById(id + '_preview_img').style.display = 'none';
            document.getElementById('jai_add').style.display = 'none';

            img.src = '';
            document.getElementById(id + '_preview_empty').style.display = '';
            // document.getElementById(id + '_preview_img').style.display = 'none';
            // remove marker in Img when clicking Clear button
            jQuery('#'+id + '_preview_img span.point').remove();
            jQuery('#extrafieldimg .adminformlist').removeClass('active').addClass('deactive');
            jQuery('#extrafieldimg #jai_remove').hide();
            desc = [];
            jQuery('#jform_params_description').val('[]');

            if(document.querySelector('ul.adminformlist').classList.contains('active')){
                document.querySelector('ul.adminformlist').classList.remove('active').add('deactive');
            }
        }else{
            img.src = uriRoot + '/' + value;
            document.getElementById('jai_add').style.display = 'inline-block';
            document.getElementById(id + '_preview_img').style.display = '';
        }
    }, 500)
})
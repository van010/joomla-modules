
function handleImgAjax(options){
    const $ = jQuery;
    $(document).ready(function (){
        const base_url = Joomla.getOptions('system.paths').root;
        var jacl_items = get_hightlight_items(options.mod_id)[1];

        const url_params = {
            option: 'com_ajax',
            module: 'jacontentlisting',
            method: 'resizeImg',
            mod_id: options.mod_id,
            format: 'json'
        };
        const params_str = new URLSearchParams(url_params).toString();
        const query_str = base_url + '/index.php?' + params_str.replace(/%2C/g, ','); 
        const encode_items = encodeURIComponent(JSON.stringify(jacl_items));

        $.ajax({
            type: 'GET',
            url: query_str,
            data: {jacl_items: encode_items},
            async: true,
            success: (res_) => {
                if (res_ === '' || res_ === undefined) return;
                var res = JSON.parse(res_);
                if (res.msg){
                    console.log(res.msg);
                    return;
                }
                var obj_imgs = get_hightlight_items(options.mod_id)[0];
                replace_resized_imgs(res, obj_imgs);
            },
            error: (err) => {
                if (options.debug !== ''){
                    console.log(`Unable to handle image resizing: ${JSON.stringify(err)}`);
                    console.log('Re-check in: ajax_load_img_size.js > get_hightlight_items()\nAnd data response in php: helper.php > resizeImgAjax()');
                }

            }
        });
    })
}

function replace_resized_imgs(resized_imgs, obj_imgs){
    $.each(obj_imgs, (idx, img) => {
        img.attr('src', $(resized_imgs[`img_${idx}`]).attr('src'));
    });
}

function get_hightlight_items(mod_id){
    const $ = jQuery;
    const parent_div = $(`div.mod${mod_id}`);
    const items_attr = {};
    const all_imgs = [];
    const items = $(parent_div.find('div.jacl-item__inner')).find('img');
    const src_null = ['', 'undefined', 'null', '#'];
    
    if (items.length > 0){
        items.each((idx, el) => {
            const img = $(el);
            if (src_null.includes(img.attr('src'))) return true;
            all_imgs.push(img);
        });
    }

    $(all_imgs).each((idx, el) => {
        const img = $(el);
        const img_src = img.attr('src');
        const img_path = get_img_path(img_src);
        const img_height = img.height();
        const img_width = img.width();

        if (!img_height || !img_width) return true;
        
        items_attr[idx] = {
            image_id: `img_${idx}`,
            image_intro: img_path,
            image_fulltext: img_path,
            title: img.attr('alt'),
            height: Math.round(img_height),
            width: Math.round(img_width),
        };
    });

    return [all_imgs, items_attr];
}

function clean_img_src(src){
    const url = new URL(src);   
    return url.pathname;
}

function get_img_path(img_src){
    const img_src_clean = clean_img_src(img_src);
    const regex = /\/images\/(.+)$/;
    const result = img_src_clean.match(regex);
    return result[0].substring(1);
}

function encrypt_data(data){
    const encrypted_data = CryptoJS.AES.encrypt(JSON.stringify(data), 'secretKey').toString();
    return encrypted_data;
}

function decrypt_data(encrypt_data) {
    const decrypted_data = CryptoJS.AES.decrypt(encrypt_data, 'secretKey').toString(CryptoJS.enc.Utf8);
    const decoded_data = JSON.parse(decrypted_data);
}
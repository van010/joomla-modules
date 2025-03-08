jQuery(document).ready($ => {
  const type = $('#jaform_params_ptype').val();
  renderSetPreview(type);

  $('#jaform_params_ptype').on('change', function (e) {
    const type = $('#jaform_params_ptype').val();
    renderSetPreview(type);
  })

  function renderSetPreview(type) {
    const uri = Joomla.getOptions('system.paths').root + '/modules/mod_ja_weather/set-icons/' + type + '/';
    const iconData = Joomla.getOptions('jaweather_icon_data');
    const data = iconData.find( item => item.name === type);
    const $preview = $('.icons-set-preview');

    const html = [];
    const iconFormats = ['.png', '.svg', '.jpg', '.jpeg'];
    data.files.forEach(file => {
      for (const [i, value] of iconFormats.entries()){
        if (file.toLowerCase().includes(value)){
          html.push(`<img src="${uri + file}" width="50" height="50" />`)
        }
      }
    })
    $preview.html(html.join(''));
  }
})
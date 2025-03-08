function updateWeatherData(id) {
    var baseUrl = Joomla.getOptions('system.paths').root;

    jQuery.ajax({
        url: baseUrl + '?option=com_ajax&module=ja_weather&method=updateCache&format=json&id=' + id,
    });
}
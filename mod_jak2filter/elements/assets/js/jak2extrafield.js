

function jaExtraFieldParams(id, obj) {
    if(obj.value.replace(/^\d+\:/, '') == 'valuerange' || obj.value.replace(/^\d+\:/, '') == 'rangeslider'){
        $(id+'format').show();
    }else{
        $(id+'format').hide();
    }
	for(var i=0; i<obj.options.length; i++) {
		opt = obj.options[i];
		tp = opt.value.replace(/^\d+\:/, '');

		if($(id+tp)) {
			if(opt.selected) {
				$(id+tp).show();
			} else {
				$(id+tp).hide();
			}
		}
	}
	
}

jQuery(document).ready(function($) {
	$('.xgroup-status').each(function(idx, el) {
		var $el = $(el);
		var $label = $el.parents('h4.jagroup');
		var $container = $label.next();
		var $tpl = $container.next();

		if ($el.prop('checked')) {
			$container.html($tpl.html());
		}

		$el.on('change', function() {
			if ($el.prop('checked')) {
				$container.html($tpl.html());
				$container.find('select').chosen();
			} else {
				$container.html('');
			}
		})
	})
})
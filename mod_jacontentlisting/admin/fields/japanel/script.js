(function($)
{
	$(document).ready(function()
	{
		// Turn radios into btn-group
		$(document).find('.radio.btn-group label').addClass('btn');
		$(document).find(".btn-group label").click(function()
		{
			var label = $(this);
			var input = $('#' + label.attr('for'));

			if (!input.prop('checked')) {
				label.closest('.btn-group').find("label").removeClass('active btn-success btn-danger btn-primary');
				if (input.val() == '') {
					label.addClass('active btn-primary');
				} else if (input.val() == 0 || input.val().toLowerCase() == 'false' || input.val().toLowerCase() == 'no') {
					label.addClass('active btn-danger');
				} else {
					label.addClass('active btn-success');
				}
				input.prop('checked', true);
				input.trigger('change');
			}
		});
	})
})(jQuery);
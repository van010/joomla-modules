jQuery(document).ready(function($){
	var jacl_modal = document.createElement("div");
	jacl_modal.setAttribute('class','modal_ja_cl');
	$("body").append(jacl_modal);
	var layoutAct = $('#jaform_jalayout_settings_layout').val();
	var modal = $(".jacontentlisting__modal"),
    // input = modal.get('id').replace("-seleceted", ""),
    modalBody = modal.find(".modal-body"),
    item = modalBody.find("li");
	modal.appendTo(jacl_modal);
	modal.on('show.bs.modal',function(){
		var id = this.id.replace("-selected","");
		var layoutVal = $('#'+$(this).data('name')).val();
		
		$(this).find('li.active').removeClass('active');
		$('.'+id+"-"+layoutVal).parent('li').addClass('active');
	});
	modal.hide();
	item.on('click',function(e){
		e.preventDefault();
		$(this).parent('ul').find('.active').removeClass('active');
		$(this).addClass('active');
		var val = $(this).data("val");
		var input = $(this).data("name");
		$('#' + input).val(val);
		$("#" + input).trigger("change");
		$(this).closest(".jacontentlisting__modal").modal('hide');
	});
});
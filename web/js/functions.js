$(document).ready(function($e)
{
	if($(".datepicker").length>0)
		$(".datepicker").dateDropper();

	$("input[type='text'],input[type='password']").each(function(index, el) {

		if($(this).next().prop("tagName")=="LABEL")
		{
			var label =  $(this).next().text();

			$(this).attr('placeholder', label);	
		}
		
	});

	$(".show-menu").click(function(event) {
		$(this).toggleClass('activo');
		$("#menu-doc").toggleClass('activo');
	});


	if($(window).width() <= 768){

		setTimeout(function(){

			var postSize = $(".post-el").width()+20;
			var postLenght = $(".post-el").length;

			var size = postLenght * postSize + 20;

			$(".contenedor-posts").attr('style','min-width:'+size+'px');

		},100);
	}




});
function tabla(params={})
{
	params["language"]={
		"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
	};
	
	$('.table-js').DataTable(params);

}

function tiempo_mensaje()
{
	flag=false;
	if($('#mensajes ul').length > 0)
	{
		flag=true;
		$("#mensajes .modal").show();
	}
	setTimeout(function(){
		if (flag) {
			$("#mensajes .modal").show();
			$('#mensajes .modal').fadeOut(function(){
				$(this).hide(
					function()
					{
						$("#mensajes ul").remove();
					});
			});
			
		}
	}, 2000);

}

function ajax(params)
{

	$.ajax({
		url: params.url,
		type: params.type,
		dataType: params.datatype,
		data: params.data,
		success:params.success,
	})
	.fail(function() {
		$("#popup").hide();
		$("#mensajes div.modal-content").append('<ul id="error_mensajes"><li>'+params.fail+'</li></ul>');
	})
	.always(function() {
		tiempo_mensaje();
	});
}

function mostrar_popup(texto,tipo="s/n",selector=null)
{
	$("#popup #message").append("<p>"+texto+"</p>");

	switch(tipo)
	{
		case "s/n":
		if(selector==null)
			$("#popup #box_options").append('<div class="contenedor-botones row-center"><div><span class="btn waver-effect" id="accept" href="'+$("."+selector).prop("href")+'">Sí</span></div><div><span class="btn waver-effect" id="decline">No</span></div></div>');
		else	
			$("#popup #box_options").append('<div class="contenedor-botones row-center"><div><a class="btn waver-effect" id="accept" href="'+$("."+selector).prop("href")+'"><span>Sí</span></a></div><div><span class="btn waver-effect" id="decline">No</span></div></div>');
		break;

		case "ok":
		$("#popup #box_options").append('<div class="contenedor-botones row-center"><div><span class="btn waver-effect" id="decline">Ok</span></div></div>');		
		break;
	}
	$("#popup").show();
	
}

function ocultar_popup(selector=null)
{
	$("#popup").hide();
	
	if(selector!=null)
		$("."+selector).removeClass(selector);

	$("#popup #box_options #accept").off();
	$("#popup #box_options #accept").unbind('click');
	$("#popup #message").children().remove();
	$("#popup #box_options").children().remove();
}

function ver_imagen(e) {
	
	e.preventDefault();
	input = $(this);
	

	reader = new FileReader();
	reader.onload = function(event) {	    		

		input.parents(".file-field").find(".js-imagen-preview img").prop("src",event.target.result);
		input.parents(".file-field").find(".js-imagen-preview").show();
		input.parents(".file-field").find(".js-imagen-input").hide();
	};

	if(input.prop("files") && input.prop("files")[0])
		reader.readAsDataURL(input.prop("files")[0]);    
}

function borrar_imagen()
{
	$(this).parents(".file-field").find(".js-imagen-input").show();
	$(this).parents(".file-field").find(".js-imagen-input .btn input").not('input[type="hidden"]').attr("required",1);
	$(this).parents(".file-field").find(".js-imagen-input .btn input").not('input[type="hidden"]').val("");
	$(this).parents(".file-field").find(".js-imagen-preview").hide();
}
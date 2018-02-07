$(document).ready(function()
{
	$(".borrar").on("click",borrar);

	$(".opcion .estado").on("click",estado);

	$("#popup #box_options").on("click","#accept",borrar_usuario);
	$("#popup #box_options").on("click","#decline",cancelar);

	tiempo_mensaje();
		params={
		"info": false,
		"responsive": true,
		"scroll": true,
		"paging" : "simple_numbers",
		"fixedColumns": true,
		
	};

	tabla(params);
});

function borrar(e)
{
	e.preventDefault();

	$(this).addClass("removed");

	texto="El usuario va ser eliminado ¿Está seguro?";

	mostrar_popup(texto,"s/n","removed");
}

function cancelar()
{
	ocultar_popup("removed");
}

function borrar_usuario(e)
{
	e.preventDefault();

	params={

		"url":$(".removed").prop('href'),
		"type":"GET",
		"datatype":"",
		"data":"",
		"entity":"user",
		"success":function(response)
		{			
			ocultar_popup(params.class);

			if(response.status_code==200)
				$(".removed").closest("."+params.entity).remove();

			$("#mensajes div.modal-content").append('<ul id="success_mensajes"><li>'+response.message+'</li></ul>');
			
		},
		"fail":"Ha habido un error, puede que el usuario ya no exista"
	}

	ajax(params);
}
function estado(e)
{
	e.preventDefault();
	opcion=$(this);
	id=$(this).parents(".user").attr("id");

	params={

		"url":opcion.attr("href"),
		"type":"POST",
		"datatype":"json",
		"data": {"id":id},
		"entity":"user",
		"success":function(data)
		{			
			opcion.find('.material-icons').text(data.estado?"block":"check");
			opcion.parents(".user").find("td.estado").text(data.estado?"Activado":"Desactivado");
			mostrar_popup(data.mensaje,"ok");
		},
		"fail":"Ha habido un error"
	}

	ajax(params);	

}

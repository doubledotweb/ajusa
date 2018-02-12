$(document).ready(function()
{
	tiempo_mensaje();
		params={
		"info": false,
		"responsive": true,
		"scroll": true,
		"paging" : "simple_numbers",
		"fixedColumns": true,
		
	};

	tabla(params);
	$(".borrar").on("click",borrar);

	$(".opcion .estado").on("click",estado);

	$("#popup #box_options").on("click","#accept",borrar_destacado);
	$("#popup #box_options").on("click","#decline",cancelar);
});

function borrar(e)
{
	e.preventDefault();

	$(this).addClass("removed");

	texto="El destacado va ser eliminado ¿Está seguro?";

	mostrar_popup(texto,"s/n","removed");
}

function cancelar()
{
	ocultar_popup("removed");
}

function borrar_destacado(e)
{
	e.preventDefault();
	var id=$(".removed").parents(".destacado").attr("id");
	params={

		"url":$(".removed").prop('href'),
		"type":"POST",
		"datatype":"",
		"data":{"id": id},
		"entity":"destacado",
		"success":function(response)
		{			
			ocultar_popup(params.class);

			if(response.status_code==200)
				$(".removed").closest("."+params.entity).remove();

			$("#mensajes div.modal-content").append('<ul id="success_mensajes"><li>'+response.message+'</li></ul>');
			
		},
		"fail":"Ha habido un error, puede que el destacado ya no exista"
	}

	ajax(params);
}

function estado(e)
{
	e.preventDefault();
	opcion=$(this);
	id=$(this).parents(".destacado").attr("id");

	params={

		"url":opcion.attr("href"),
		"type":"POST",
		"datatype":"",
		"data": {"id":id},
		"entity":"destacado",
		"success":function(data)
		{			
			opcion.find('.material-icons').text(data.estado?"block":"check");
			opcion.parents(".destacado").find("td.visible").text(data.estado?"Activado":"Desactivado");
			mostrar_popup(data.mensaje,"ok");
		},
		"fail":"Ha habido un error"
	}

	ajax(params);	

}

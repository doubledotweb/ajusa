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
	

	$("#popup #box_options").on("click","#accept",borrar_descargable);
	$("#popup #box_options").on("click","#decline",cancelar);
});

function borrar(e)
{
	e.preventDefault();

	$(this).addClass("removed");

	texto="El descargable va ser eliminado ¿Está seguro?";

	mostrar_popup(texto,"s/n","removed");
}

function cancelar()
{
	ocultar_popup("removed");
}

function borrar_descargable(e)
{
	e.preventDefault();
	var id=$(".removed").parents(".descargable").attr("id");
	params={

		"url":$(".removed").prop('href'),
		"type":"POST",
		"datatype":"",
		"data":{"id": id},
		"entity":"descargable",
		"success":function(response)
		{			
			ocultar_popup(params.class);

			if(response.status_code==200)
				$(".removed").closest("."+params.entity).remove();

			$("#mensajes div.modal-content").append('<ul id="success_mensajes"><li>'+response.message+'</li></ul>');
			
		},
		"fail":"Ha habido un error, puede que el tip ya no exista"
	}

	ajax(params);
}

function estado(e)
{
	e.preventDefault();
	opcion=$(this);
	id=$(this).parents(".descargable").attr("id");

	params={

		"url":opcion.attr("href"),
		"type":"POST",
		"datatype":"",
		"data": {"id":id},
		"entity":"descargable",
		"success":function(data)
		{			
			opcion.find('.material-icons').text(data.estado?"block":"check");
			opcion.parents(".descargable").find("td.visible").text(data.estado?"Activado":"Desactivado");
			mostrar_popup(data.mensaje,"ok");
		},
		"fail":"Ha habido un error"
	}

	ajax(params);	

}


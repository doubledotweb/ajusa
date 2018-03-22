$(document).ready(function()
{
	tiempo_mensaje();
		params={
		"info": false,
		"responsive": true,
		"scroll": true,
		"paging" : "simple_numbers",
		"fixedColumns": true,
		"ordering": false
		
	};

	tabla(params);
	$(".borrar").on("click",borrar);
	$(".js-estado").on("click",estado);

	$("#popup #box_options").on("click","#accept",borrar_comentario);
	$("#popup #box_options").on("click","#decline",cancelar);
});

function borrar(e)
{
	e.preventDefault();

	$(this).addClass("removed");

	texto="El comentario va ser eliminado ¿Está seguro?";

	mostrar_popup(texto,"s/n","removed");
}

function cancelar()
{
	ocultar_popup("removed");
}

function borrar_comentario(e)
{
	e.preventDefault();
	var id=$(".removed").parents(".comentario").attr("id");
	params={

		"url":$(".removed").prop('href'),
		"type":"POST",
		"datatype":"",
		"data":{"id": id},
		"entity":"comentario",
		"success":function(response)
		{			
			ocultar_popup(params.class);

			if(response.status_code==200)
				$(".removed").closest("."+params.entity).remove();

			$("#mensajes div.modal-content").append('<ul id="success_mensajes"><li>'+response.message+'</li></ul>');
			
		},
		"fail":"Ha habido un error, puede que el comentario ya no exista"
	}

	ajax(params);
}

function estado(e)
{
	e.preventDefault();
	var opcion=$(this);
	var id=$(this).parents(".comentario").attr("id");

	params={

		"url":$(this).attr("href"),
		"type":"POST",
		"datatype":"",
		"data": {"id":id},
		"entity":"comentario",
		"success":function(data)
		{			
			opcion.children("i").text(data.estado);
			opcion.children("i").attr("title",data.estado=="cancel"?"Activar":"Bloquear");
			opcion.parents(".comentario").find(".estado").children("i").text(data.estado=="cancel"?"done":"cancel");
			opcion.parents(".comentario").find(".estado").children("i").attr("title",data.estado=="cancel"?"Activado":"Bloqueado");
			
			mostrar_popup(`El comentario ha sido ${(data.estado=="cancel"?"activado":"bloqueado")}`,"ok");
		},
		"fail":"Ha habido un error"
	}

	ajax(params);	

}
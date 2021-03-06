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
	$(".tip .borrar").on("click",borrar);
	$(".keyword .borrar").on("click",borrar_key);
	

	$(".opcion .estado").on("click",estado);
	$(".opcion .destacado").on("click",destacado);


	if ($("#keywords").length) {
		$("#popup #box_options").on("click","#accept",borrar_keyword);
		$("#popup #box_options").on("click","#decline",cancelar);
	} else {
		$("#popup #box_options").on("click","#accept",borrar_destacado);
		$("#popup #box_options").on("click","#decline",cancelar);
	}
	
});

function borrar(e)
{
	e.preventDefault();

	$(this).addClass("removed");

	texto="El tip va ser eliminado ¿Está seguro?";

	mostrar_popup(texto,"s/n","removed");
}

function borrar_key(e)
{
	e.preventDefault();


	$(this).addClass("removed");
	texto="El keyword va ser eliminado ¿Está seguro?";

	

	mostrar_popup(texto,"s/n","removed");
}

function cancelar()
{
	ocultar_popup("removed");
}

function borrar_destacado(e)
{
	e.preventDefault();
	var id=$(".removed").parents(".tip").attr("id");
	params={

		"url":$(".removed").prop('href'),
		"type":"POST",
		"datatype":"",
		"data":{"id": id},
		"entity":"tip",
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

function borrar_keyword(e)
{
	e.preventDefault();
	var id=$(".removed").parents(".keyword").attr("id");
	console.log(id);
	params={

		"url":$(".removed").prop('href'),
		"type":"POST",
		"datatype":"",
		"data":{"id": id},
		"entity":"tip",
		"success":function(response)
		{			
			ocultar_popup(params.class);

			if(response.status_code==200)
				$(".removed").closest(".keyword").remove();

			$("#mensajes div.modal-content").append('<ul id="success_mensajes"><li>'+response.message+'</li></ul>');
			
		},
		"fail":"Ha habido un error, puede que el keyword ya no exista"
	}

	ajax(params);
}

function estado(e)
{
	e.preventDefault();
	opcion=$(this);
	id=$(this).parents(".tip").attr("id");

	params={

		"url":opcion.attr("href"),
		"type":"POST",
		"datatype":"",
		"data": {"id":id},
		"entity":"tip",
		"success":function(data)
		{			
			opcion.find('.material-icons').text(data.estado?"block":"check");
			opcion.parents(".tip").find("td.visible").text(data.estado?"Activado":"Desactivado");
			mostrar_popup(data.mensaje,"ok");
		},
		"fail":"Ha habido un error"
	}

	ajax(params);	

}

function destacado(e)
{
	e.preventDefault();
	opcion=$(this);
	id=$(this).parents(".tip").attr("id");

	params={

		"url":opcion.attr("href"),
		"type":"POST",
		"datatype":"",
		"data": {"id":id},
		"entity":"tip",
		"success":function(data)
		{			
			opcion.find('.material-icons').attr("style",data.estado?"color:#db9c0d":"");
			opcion.parents(".tip").find("td.destacado").text(data.estado?"Si":"No");
			mostrar_popup(data.mensaje,"ok");
		},
		"fail":"Ha habido un error"
	}

	ajax(params);	

}
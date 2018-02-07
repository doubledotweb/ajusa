$(document).ready(function()
{
	$(".borrar").on("click",borrar);	

	$("#popup #box_options").on("click","#accept",borrar_categoria);
	$("#popup #box_options").on("click","#decline",cancelar);

	tiempo_mensaje();
		params={
		"info": false,
		"responsive": true,
		"scroll": true,
		"paging" : "simple_numbers",
		"fixedColumns": true,
		"order":[[0,"desc"]]
	};

	tabla(params);
});

function borrar(e)
{
	e.preventDefault();

	$(this).addClass("removed");

	texto="La noticia será eliminada ¿Está seguro?";

	mostrar_popup(texto,"s/n","removed");
}

function cancelar()
{
	ocultar_popup("removed");
}

function borrar_categoria(e)
{
	e.preventDefault();
	

	params={

		"url":$(".removed").prop('href'),
		"type":"GET",
		"datatype":"",
		"data":"",
		"entity":"noticia",
		success:function(response)
		{
			ocultar_popup(params.class);

			if(response.status==200)
				$(".removed").parents("tr").remove();

			$("#mensajes div.modal-content").append('<ul id="success_mensajes"><li>'+response.message+'</li></ul>');
			
		},
		fail:"Algo ha ido mal."
	}

	ajax(params);
}

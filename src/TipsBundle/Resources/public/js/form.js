$(document).ready(function(e)
{
	tiempo_mensaje();

	name=$("form").attr("name");

	$('input[maxlength],textarea[maxlength]').on("paste",controlar_pegado);
	$('input[name="'+name+'[archivo_aux]"]').on("change",function()
	{
		var fake=$(this).val();

		var aux=fake.split("\\");

		$(this).parents(".js-archivo-input").find("input.file-path").val(aux[aux.length-1]);
	});	

	$("#"+name+"_delete").on("click",borrar);


	$("form").submit(function(event) {
		if($('input[name="'+name+'[archivo_aux]"]')[0].files[0].size/1024/1024>2)
		{
			event.preventDefault();
			mostrar_popup("El archivo supera el tamaño máximo permitido","ok");
			$("#popup #box_options").on("click","#decline",cancelar);
		}
	});
	
	
});


function controlar_pegado(e)
{
	e.preventDefault();
	var length=$(this).attr("maxlength");

	texto=e.originalEvent.clipboardData.getData("text");

	if(texto.length>length)
		texto=recortar_cadena(texto,length)

	$(this).val(texto);
}



function borrar(e)
{
	e.preventDefault();
	texto="El tip va ser eliminado ¿Está seguro?";

	$("#popup #box_options").on("click","#accept",borrar_tip);
	$("#popup #box_options").on("click","#decline",cancelar);

	mostrar_popup(texto,"s/n");

}


function cancelar()
{
	ocultar_popup();
}

function borrar_tip(e)
{
	e.preventDefault();

	$('button[name="'+name+'[delete]"]').trigger('click');	
}
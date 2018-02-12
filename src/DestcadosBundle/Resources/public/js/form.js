$(document).ready(function(e)
{
	tiempo_mensaje();

	name=$("form").attr("name");

	$('input[maxlength],textarea[maxlength]').on("paste",controlar_pegado);

	$("#"+name+"_delete").on("click",borrar);

	$('select[name="'+name+'[tipo]"]').on("change",cambiar_imagen);


	
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
	texto="El destacado va ser eliminado ¿Está seguro?";

	$("#popup #box_options").on("click","#accept",borrar_destacado);
	$("#popup #box_options").on("click","#decline",cancelar);

	mostrar_popup(texto,"s/n");

}


function cancelar()
{
	ocultar_popup();
}

function borrar_destacado(e)
{
	e.preventDefault();

	$('button[name="'+name+'[delete]"]').trigger('click');	
}

function cambiar_imagen(e)
{
	var cat=$(this).val();


	$('input[name="'+name+'[imagen]"]').each(function(index, el) 
	{
		var value=$(this).val();
		$(this).siblings('img').attr('src', '/bundles/destcados/img/'+cat+'_'+value+".jpg");
	});






}
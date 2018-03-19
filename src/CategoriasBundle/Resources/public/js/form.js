$(document).ready(function()
{	
	tiempo_mensaje();
	$("#js-delete").on("click",borrar);

 	$("#popup #box_options").on("click","#accept",borrar_categoria);
    $("#popup #box_options").on("click","#decline",no_borrar);
    
});


function borrar(e)
{
	e.preventDefault();
	texto="La categoría será eliminada ¿Está seguro?";
	mostrar_popup(texto,"s/n");
}

function no_borrar()
{
	ocultar_popup();
}

function borrar_categoria(e)
{
    e.preventDefault();
    
    $("input").removeAttr('required');

    $("textarea").removeAttr("required");

    $('button[name="'+$("form").attr("name")+'[delete]"]').trigger('click');    
}

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
	
	$("#js-fake-input-keywords").on("keyup",keywords);
	$("#js-fake-select-keywords li").on("click",keywords_selected);
	$("body").on("click",".chip .close",delete_keyword);
	
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

function keywords(e)
{
	if(e.originalEvent.keyCode==27)
		$(this).val("");
	if($(this).val().length>0)
	{	
		var escrito=$(this).val();
		$("#js-fake-select-keywords").show();
		$("#js-fake-select-keywords li").each(function()
		{
			if($(this).text().toLowerCase().includes(escrito))
			{
				$(this).show();
			}
			else
			{
				$(this).hide();
			}
		});
	}
	else
	{
		$("#js-fake-select-keywords").hide();
	}
}

function keywords_selected(e)
{
	e.preventDefault();


	var chip='<div class="chip" >'+$(this).text()+'<i class="borrar material-icons">delete</i></div>';
	var input='<input type="hidden" name="'+name+'[keywords]" value="'+$(this).attr('data-id')+'" >';
	if($('#js-hidden-input-keyword-container input[value="'+$(this).attr('data-id')+'"]').length==0)
	{		
		$("#js-tips-keyword-container").append(chip);
		$("#js-hidden-input-keyword-container").append(input);
	}

}

function delete_keyword(e)
{
	var pos=$(this).index();
	$(this).parents(".chip").remove();

	$("#js-hidden-input-keyword-container input")[pos].remove();
	
}
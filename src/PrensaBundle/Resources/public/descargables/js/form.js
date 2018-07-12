$(document).ready(function(e)
{
	tiempo_mensaje();

	name=$("form").attr("name");

	$('input[maxlength],textarea[maxlength]').on("paste",controlar_pegado);
	$('input[name="'+name+'[categoria]"]').on("change",cambio_categoria);


	$('input[name="'+name+'[archivo_aux]"]').on("change",gestion_archivo);	

	$("#"+name+"_delete").on("click",borrar);

	$(".js-borrar-imagen").on("click",borrar_imagen);


	$("form").submit(enviar_form);
	
	text=$("#croppie").croppie({		
		"viewport": 
		{
        	width: 350,
        	height: 160
        }
    })
	

    $("#croppie-save").on("click",function(e)
    {
    	e.preventDefault();

	    	text.croppie('result', {
			type: 'base64',
			
			// size: { width: 300, height: 300 },
	    	format: 'png'
	    }).then(function (canvas) {
				
				$(".file-field img").attr("src",canvas);
				$('input[name="'+name+'[imagen]"]').val(canvas);
				$("#popup-croppie").hide();
				$(".file-field").find(".js-imagen-preview").show();
				$(".file-field").find(".js-imagen-input").hide();

				
		});

    })


	text.width(300);
	text.height(300);
	
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
	texto="El descargable va ser eliminado ¿Está seguro?";

	$("#popup #box_options").on("click","#accept",borrar_descargable);
	$("#popup #box_options").on("click","#decline",cancelar);

	mostrar_popup(texto,"s/n");

}


function cancelar()
{
	ocultar_popup();
}

function borrar_descargable(e)
{
	e.preventDefault();

	$('button[name="'+name+'[delete]"]').trigger('click');	
}

function cambio_categoria(e)
{
	var categoria=$(this).val();

	if(categoria=="clipping-de-prensa")		

		$('.js-archivo input[name="'+name+'[archivo_aux]"]').attr('accept',".pdf,.doc,.zip");
	
	else

		$('.js-archivo input[name="'+name+'[archivo_aux]"]').attr('accept',"image/*");
	
}

function gestion_archivo(e)
{
	e.preventDefault();
	switch($('input[name="'+name+'[categoria]"]:checked').val())
	{
		
	
	case "clipping-de-prensa":
	
		var fake=$(this).val();

		var aux=fake.split("\\");

		$(this).parents(".js-imagen-input").find("input.file-path").val(aux[aux.length-1]);
	break;

	case "imagen":
	case "logotipo":


		
		
	
		input = $(this);


		reader = new FileReader();
		reader.onload = function(event) {	    		

			//input.parents(".file-field").find(".js-imagen-preview img").prop("src",event.target.result);
			//input.parents(".file-field").find(".js-imagen-preview").show();
			//input.parents(".file-field").find(".js-imagen-input").hide();
			text.croppie("bind",{"url":event.target.result});
			$("#popup-croppie").show();
			//console.log(event);
		};

		if(input.prop("files") && input.prop("files")[0])
			reader.readAsDataURL(input.prop("files")[0]);

	break;
	}

	
}

function enviar_form(event) 
{
	var categoria=$('input[name="'+name+'[categoria]"]:checked').val();

	var archivo_size=$('input[name="'+name+'[archivo_aux]"]')[0].files[0].size/1024/1024;
	var mini_size=0;
	switch(categoria)
	{		
		case "logotipo":
		case "imagen":
			var imagen=$('input[name="'+name+'[imagen]"]').val();

			var head = imagen.substr(0,imagen.indexOf(","));
			mini_size = Math.round((imagen.length - head.length)*3/4)/1024/1024 ;

		break
	
	}	

	

	if( archivo_size>2 || mini_size>2)
	{
		event.preventDefault();
		mostrar_popup("El archivo supera el tamaño máximo permitido","ok");
		$("#popup #box_options").on("click","#decline",cancelar);

		return false;
	}
	


	if(categoria=="imagen"||categoria=="logotipo")
	{
		

			var nombre_imagen=$('input[name="'+name+'[archivo_aux]"]')[0].files[0].name;

			$('input[name="'+name+'[imagen]"]').val(nombre_imagen+"+-+"+$('input[name="'+name+'[imagen]"]').val());
		
	}
}
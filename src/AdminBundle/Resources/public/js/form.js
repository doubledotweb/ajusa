$(document).ready(function()
{
	tiempo_mensaje();
	$("#user_form_delete").on("click",borrar);

	$('button[type="submit"]').on("click",comprobar_contraseñas);	

	name=$("form").attr("name");

	$('input[name="'+name+'[role]"]').on("change",permisos);

});



function renovar(e)
{
	e.preventDefault();

	$("#popup #box_options").on("click","#accept",renovar_pass);
	$("#popup #box_options").on("click","#decline",cancelar);

	$(this).addClass("removed");
	texto="¿Está seguro que desea generar una nueva contraseña?</p><p>Esta acción invalidará la contraseña actualmente almacenada";
	
	mostrar_popup(texto,"s/n","removed");
	
}

function borrar(e)
{
	e.preventDefault();
	texto="El usuario va ser eliminado ¿Está seguro?";

	$("#popup #box_options").on("click","#accept",borrar_usuario);
	$("#popup #box_options").on("click","#decline",cancelar);

	mostrar_popup(texto,"s/n");

}


function cancelar()
{
	ocultar_popup();
}

function borrar_usuario(e)
{
	e.preventDefault();

	$('button[name="user_form[delete]"]').trigger('click');	
}


function comprobar_contraseñas(e)
{

	real=$('input[name="user_form[password]"]');
	verificada=$("#contraseña");

	if(real=="" || verificada=="" || real.val()!=verificada.val())
	{
		e.preventDefault();

		real.addClass('error');
		verificada.addClass('error');
		mostrar_popup("Las contraseñas no coinciden","ok");
		$("#popup #box_options").on("click","#decline",cancelar);
	}


}

function permisos()
{
	name=$("form").attr("name");
	switch($(this).val())
	{
		case "Administrador":

			$('input[name="'+name+'[permisos][]"]').each(function()
			{
				$(this).attr('checked', true);
				$(this).attr("onclick","javascript: return false;");
			});

		break;

		case "Gestor Medio Ambiente":
			$('input[name="'+name+'[permisos][]"]').each(function()
			{
				$(this).removeAttr('checked');
				$(this).attr("onclick","javascript: return false;");
				if($(this).val()=="cerca-de-ti")
					$(this).attr("checked",true);
			});
		break;

		case "Gestor":
			$('input[name="'+name+'[permisos][]"]').each(function()
			{
				$(this).removeAttr('onclick');
				$(this).removeAttr('checked');	
				if($(this).val()=="usuarios")
					$(this).attr("onclick","javascript: return false;");			
			});
		break;
	}
}
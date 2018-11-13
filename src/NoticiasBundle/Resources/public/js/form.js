$(document).ready(function()
{	
	tiempo_mensaje();
	$("#js-delete").on("click",borrar);

	$("#popup #box_options").on("click","#accept",borrar_noticia);
	$("#popup #box_options").on("click","#decline",no_borrar);


	$("#popup").on("click","#decline",function()
	{
		ocultar_popup();
	})

	$("body").on("change",'.image-field input',function(e)
	{
		$(this).parents(".file-field").find('input[type="hidden"]').val("anadir");
	});
	$("body").on("click",".js-borrar-imagen",function(e)
	{
		$(this).parents(".file-field").find('input[type="hidden"]').val("borrar");
	});

	$("body").on("change",'.image-field input',ver_imagen);
	$("body").on("click",".js-borrar-imagen",borrar_imagen);

	$(".js-idiomas").on("change",function()
	{
		var lang=$(this).attr("id").split("_");
		lang=lang[lang.length-1];


		if($(this).is(":checked"))

			$("#js-tabulador-"+lang).addClass('visible')
		else
			$("#js-tabulador-"+lang).removeClass('visible');
	});

	$(".js-tabulador").on("click",function(e)
	{
		e.preventDefault();
		$("a.active").removeClass('active');
		$(this).addClass('active');


		$(".noticia-fields.active").removeClass('active');
		var lang=$(this).attr("id").split("-");
		lang=lang[lang.length-1];


		$(".noticia-fields").hide();

		$("#noticia_"+lang).show();
		$("#noticia_"+lang).addClass('active');


	});

	$('button[type="submit"]').on("click",function(e)
	{
		e.preventDefault();
		boton=$(this);
		if(boton.attr("disabled")=="disabled")
			return;

		boton.attr("disabled","disabled");		

		formdata = new FormData();

		fecha= $('input[name="noticia_form[fecha_publicacion]"]');
		visible=$('input[name="noticia_form[visible]"]');
		destacado=$('input[name="noticia_form[destacado]"]');
		token=$('input[name="noticia_form[_token]"]');
		categorias=$('input[name="noticia_form[categorias][]"]');

		
		formdata.append(destacado.attr("name"),destacado.is(":checked")?1:0);

		categorias.each(function(index,el)
		{
			
			if($(this).is(":checked"))
				formdata.append(categorias.attr("name"),$(this).val());
			
		});


		


		
		formdata.append("boton",$(this).attr("name").replace(/noticia_form\[/gi,"").replace(/\]/gi,""));

		formdata.append(fecha.attr('name'),fecha.val());
		formdata.append(token.attr('name'),token.val());
		if($("form").find('input[name="noticia_form[id]"]').length)
		{
			id=$("form").find('input[name="noticia_form[id]"]');
			formdata.append(id.attr('name'),id.val());
		}
		
		formdata.append(visible.attr('name'),visible.is(":checked")?1:0);



		idioma=$('.noticia-fields.active').attr("id").replace(/noticia_/gi,"");

		seguir=true;
		vacios=[];

		$(".noticia-fields.active input[required], .noticia-fields.active textarea[required]").each(function()
		{
			switch($(this).attr("type"))
			{
				case "text":
				case "textarea":
				if($(this).val()=="")
				{
					seguir=false;
					vacios.push($(this).attr("id"));
				}
				break;

				case "file":
				if($(this).get(0).files.length==0)
				{
					seguir=false;
					vacios.push($(this).attr("id"));
				}
				break;

			}
		});

		formdata.append($('select[name="noticia_form[categoria]"]').attr("name"),$('select[name="noticia_form[categoria]"]').val());



		$('input[name="noticia_form[idioma]['+idioma+']"]').each(anadir_al_form);

		$('.noticia-fields.active').find("input").each(anadir_al_form);
		$('.noticia-fields.active').find("textarea").each(function()
		{
			if($(this).attr("class").search('ckeditor')!=-1)
				formdata.append($(this).attr("name"),CKEDITOR.instances[$(this).attr("id")].getData());							
			else
				formdata.append($(this).attr("name"),$(this).val());

		});
		
		
		

		if(seguir)
		{	
			$(".error").removeClass('error');
			ocultar_popup();

			$.ajax({
				url: window.location.pathname,
				type: 'POST',
				processData: false,
				contentType: false,
				data: formdata,
				success:function(response)
				{
					if(response.status==200)
					{
						if(response.id)
							$("form").append('<input type="hidden" name="noticia_form[id]" value="'+response.id+'">');
						if(response.redirect)
						{
							window.location.pathname=response.redirect;
						}
					}
				}
			})
			.always(function(response)
			{
				if(response.message)
					mostrar_popup(response.message,"ok");

				boton.removeAttr('disabled');
			});	
		}
		else
		{
			ocultar_popup();
			for(i=0;i<vacios.length;i++)
			{
				$("#"+vacios[i]).addClass('error');
			}
			mostrar_popup("Debe rellenar todos los campos","ok");
			boton.removeAttr('disabled');
			
		}

	});

	$("#js-modulos a").on("click",cargar_modulo);
	$("body").on("click","#js-add-enlace",cargar_enlace);
	$("body").on("click",".js-borrar-enlace-despiece",borrar_enlace_despiece);
	$("body").on("click",".fa-arrow-up",posicion_modulos);
	$("body").on("click",".fa-arrow-down",posicion_modulos);

	$("#js-contenido-cuerpo").on("click",".js-borrar-item",function(e)
	{
		e.preventDefault();
		$(this).parents(".elemento-of-noticia").remove();
	})


	$(".search-in-youtube").each(function(index, el) {
		loadImgVideo($(this));
	});	


	$("body").on('blur',".search-in-youtube", function(event) {
		loadImgVideo($(this));
	});


});

function loadImgVideo(el){
	if(el.val().includes("?watch="))
		var id = el.val().split("v=")[1];
	else
		var id = el.val().split("youtu.be/")[1];

	var src = "https://img.youtube.com/vi/"+id+"/maxresdefault.jpg";
	if(el.next().hasClass('img-youtube')){
		el.next().attr('src', src);
	}else{
		$("<img src='"+src+"' class='img-youtube'/>").insertAfter(el);
	}
}

function borrar(e)
{
	e.preventDefault();
	texto="La noticia será eliminada ¿Está seguro?";
	mostrar_popup(texto,"s/n");
}

function no_borrar()
{
	ocultar_popup();
}

function borrar_noticia(e)
{
	e.preventDefault();

	$("input").removeAttr('required');

	$("textarea").removeAttr("required");

	$('button[name="'+$("form").attr("name")+'[delete]"]').trigger('click');    
}

function anadir_al_form()
{
	if($(this).attr("name") != undefined)
	{
		switch($(this).attr("type"))
		{
			case "text":
			case "hidden":
			case "url":    				
			formdata.append($(this).attr("name"),$(this).val());
			break;
			case "checkbox":
			formdata.append($(this).attr("name"),$(this).is(":checked")?1:0);
			break;
			case "file":
			formdata.append($(this).attr("name"),$(this).get(0).files[0]);
			break;

		}
	}
}

function cargar_modulo(e)
{
	e.preventDefault();
	lang=$(".noticia-fields.active").attr("id").replace(/noticia_/gi,"");
	length=$(" .noticia-fields.active #js-contenido-cuerpo > div.elemento-of-noticia").length;
	index=length;
	flag=false;

	

	if( $(this).attr("id")=="js-despiece" && $(this).hasClass('disabled'))
	{
		mostrar_popup("Este módulo solo puede ir detras del módulo texto","ok");
		return ;
	}
	else
	{
		if($(this).attr("id")=="js-texto")
			$(".noticia-fields.active #js-despiece").removeClass('disabled');
		else
			$(".noticia-fields.active #js-despiece").addClass('disabled');	
	}

	input='<div>'+
			'<i class="fa fa-arrow-up" aria-hidden="true" data-action="subir"></i>'+
			'<i class="fa fa-arrow-down" aria-hidden="true" data-action="bajar"></i>'+
		'</div>';
	switch($(this).attr("id"))
	{
		case "js-video":
		input+='<h3>Vídeo</h3><div class="input-field"><label for="noticia_'+lang+'_'+index+'">Video</label><input class="search-in-youtube" id="noticia_'+lang+'_'+index+'" type="url" name="noticia_form[cuerpo]['+lang+']['+index+'][video][valor]" required placeholder="Url del vídeo">'+
		'<div class="input-field"><h4>Pie del video</h4><label for="pie_imagen_'+lang+'" >Pie de vídeo</label><input id="pie_imagen_'+lang+'" type="text"  name="noticia_form[cuerpo]['+lang+']['+index+'][video][pie]" value="" maxlength="140" placeholder="Pie del vídeo"><a class="btn micro js-borrar-item">Borrar vídeo</a></div></div>';

		;
		break;

		case "js-despiece":
		input+='<h3>Despiece</h3>'+
		'<div class="input-field">'+
			'<label for="noticia_'+lang+'_'+index+'">Enlace</label>'+
			'<input class="" maxlength="140" id="noticia_'+lang+'_'+index+'" type="text" name="noticia_form[cuerpo]['+lang+']['+index+'][despiece][titulo]" required placeholder="Título">'+
			'<div id="js-enlaces"></div>'+
			'<a class="btn min mb-10" id="js-add-enlace">+ Enlace</a><br>'+
			'<a class="btn micro js-borrar-item">Borrar despiece</a>'+
		'</div>';

		;
		break;

		case "js-imagen":

		input+='<h3>Imagen</h3><div class="file-field input-field image-field">'+
		'<div id="imagen_cuerpo_'+lang+'_'+index+'" class="js-imagen-input row-start">'+								
		'<div class="file-path-wrapper">'+
		'<input class="file-path validate" type="text">'+
		'</div>'+
		'<div class="btn">'+
		'<span>Seleccionar imagen</span>'+
		'<input type="file" id="noticia_'+lang+'_'+index+'" name="noticia_form[cuerpo]['+lang+']['+index+'][image]" accept="image/*" required>'+
		'<input type="hidden" name="noticia_form[cuerpo]['+lang+']['+index+'][image][accion]" >'+
		'</div>'+

		'</div>'+

		'<div class="js-imagen-preview" style="display:none;">'+
		'<img src="" width="100px" height="auto">'+
		'<i class="material-icons js-borrar-imagen" title="Eliminar imagen">close</i>'+
		'</div>'+

		'<h4>Pie de imagen</h4><div class="input-field">'+
		'<label for="pie_imagen_'+lang+'" >Pie imagen</label>'+
		'<input id="pie_imagen_'+lang+'" type="text" name="noticia_form[cuerpo]['+lang+']['+index+'][image][pie]" value="" maxlength="140" >'+
		'</div>'+
		'<a class="btn micro js-borrar-item">Borrar imagen</a>'+
		'</div>';

		break;
		case "js-destacado":
		input+='<h3>Destacado</h3><div class="input-field destacado"><label for="noticia_'+lang+'_'+index+'">Destacado</label><textarea class="" id="noticia_'+lang+'_'+index+'" type="text" name="noticia_form[cuerpo]['+lang+']['+index+'][destacado]" placeholder="Destacado" required maxlength="255"></textarea><a class="btn micro js-borrar-item">Borrar destacado</a></div>';
		break;
		case "js-texto":
		input+='<h3>Texto</h3><div class="input-field editor"><label for="noticia_'+lang+'_'+index+'">Texto</label><textarea class="ckeditor" id="noticia_'+lang+'_'+index+'" name="noticia_form[cuerpo]['+lang+']['+index+'][texto]" required></textarea><a class="btn micro js-borrar-item">Borrar texto</a></div>';
		flag=true;
		break;
	}

	$("#noticia_"+lang+" #js-contenido-cuerpo").append("<div class='elemento-of-noticia "+$(this).attr("id")+"'>"+input+"</div>");
	if(flag)
	{
		CKEDITOR.replace("noticia_"+lang+"_"+index);
	}
}

function cargar_enlace(e)
{
	e.preventDefault();
	old_parent_pos=$(this).parents(".js-despiece").index();
	parent=$(this).siblings('#js-enlaces');
	lang=$(".noticia-fields.active").attr("id").replace(/noticia_/gi,"");

	if(parent.children().length+1>3)
	{
		mostrar_popup("Máximo de enlaces alcanzado","ok");		
		return;
	}
	
	html=
		
		'<div class="js-enlace-despiece">'+
			'<h4>Enlace '+parseInt(parent.children().length+1)+'</h4>'+
			'<div class="input-field">'+			
				'<label for="texto_despiece_'+lang+'_'+old_parent_pos+'_'+parent.children().length+'" >Texto</label>'+
				'<input id="texto_despiece_'+lang+'_'+old_parent_pos+'_'+parent.children().length+'" type="text"  name="noticia_form[cuerpo]['+lang+']['+old_parent_pos+'][despiece][enlace]['+parent.children().length+'][texto]" value="" maxlength="400" placeholder="Texto" required>'+
			'</div>'+
			'<div class="input-field">'+
				
				'<label for="enalce-despiece_'+lang+'_'+old_parent_pos+'_'+parent.children().length+'" >Enlace</label>'+
				'<input id="enlace-despiece_'+lang+'_'+old_parent_pos+'_'+parent.children().length+'" type="text"  name="noticia_form[cuerpo]['+lang+']['+old_parent_pos+'][despiece][enlace]['+parent.children().length+'][enlace]" value="" maxlength="400" placeholder="enlace">'+
				'<a class="btn micro js-borrar-enlace-despiece mb-10">Borrar enlace</a>'+
			'</div>'+
		'</div>';

	parent.append(html);
			
}	


function borrar_enlace_despiece(e)
{
	e.preventDefault();
	container=$(this).parents("#js-enlaces");

	$(this).parents(".noticia-fields.active .js-enlace-despiece").remove();

	if(container.children().length>0)
	{		
		container.children().each(function(i,val)
		{
			$(this).find("h4").text("Enlace "+(i+1));

			$(this).find("input").each(function()
			{
				name=$(this).attr("name");
				
				name=name.replace(/\[enlace\]\[\d\]/gi,"[enlace]["+i+"]");
				
				$(this).attr("name",name);

			});
		});
	}
}

function posicion_modulos(e)
{
	e.preventDefault();

	elemento=$(this).parents(".noticia-fields.active .elemento-of-noticia");
	current_pos=$(this).parents(".noticia-fields.active .elemento-of-noticia").index();

	new_pos=($(this).attr("data-action")=="subir")?current_pos-1:current_pos+1;

	
	
	if(new_pos>=0 && new_pos<=$(this).parents("#js-contenido-cuerpo").find(".elemento-of-noticia").length)
	{

		switch($(this).attr("data-action"))
		{
			case "subir":
				elemento.insertBefore($(".noticia-fields.active .elemento-of-noticia").get(new_pos));
				for(name in CKEDITOR.instances)
				{
					CKEDITOR.instances[name].destroy();
				}				
				CKEDITOR.replaceAll();
			break;

			case "bajar":
				elemento.insertAfter($(".noticia-fields.active .elemento-of-noticia").get(new_pos));
				for(name in CKEDITOR.instances)
				{
					CKEDITOR.instances[name].destroy();
				}				
				CKEDITOR.replaceAll();
			break;
		}
		


		$(".noticia-fields.active .elemento-of-noticia").each(function(index_ele, el) {
			$(this).find("input,textarea").each(function()
			{
				name=$(this).attr("name");

			 	if(name!=null && name!="")
			 		new_name=name.replace(/\[\d+\]/i,"["+index_ele+"]");

			 	console.log(new_name);
			 	$(this).attr("name",new_name);
			});
		});
		
	}

}
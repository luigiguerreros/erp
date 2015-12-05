$(document).ready(function(){
	
	$('#txtProductoInventario').autocomplete({
		source: "/producto/buscarAutocompleteLimpio/",
		select: function(event, ui){
			$('#txtIdProducto').val(ui.item.id);
			//$('#codigoProducto').html(ui.item.value);
			$('#txtDescripcion').val(ui.item.tituloProducto);
			
		}
	});

	$('#btnAgregar').click(function(e){
		e.preventDefault();
		agregarProducto();
	});

	$('.btnEliminar').live('click',function(e){
		e.preventDefault();
		if (confirm("Esta seguro de Eliminar este Producto")) {
			padre=$(this).parents('tr');
			padre.find('.estado').val(0);
			padre.hide();
		}		
	});

	$('.btnGrabar').live('click',function(e){
		e.preventDefault();
		padre=$(this).parents('tr');
		if (confirm("Esta seguro de Grabar")) {
			var validacion=verificarBloque(padre);
			if (validacion==true) {
				grabarInventario(padre);
			}else{
				alert('El producto ya tiene un bloque asignado')
			}
		}		
	});
});

function agregarProducto(){
	if ($('#txtIdProducto').val()!=0) {
		var vali=verificarBloqueAgregar()
		if (vali==true) {
			if (verificaExistencia()==true) {
				$('#tblProducto tbody').append(crearfila());
			}else{
				alert("El Producto ya fue agregado");
			}
		}else{
			alert("El Producto para este inventario ya tiene bloque");
		}
	}else{
		alert("No ha Seleccionado ningun Producto");
	}
	
}

function verificaExistencia(){
	var idNuevo=$('#txtIdProducto').val();
	var verificacion=true;
	$('.id').each(function(){
		padre=$(this).parents('tr');
		id=padre.find('.id').val();
		estado=parseInt(padre.find('.estado').val());
		if (estado==1) {
			if (id==idNuevo) {
				verificacion=false;
			}
		}
	});
	return verificacion;
}
function crearfila(){
	fila="<tr>"+
			"<td>"+$('#txtProductoInventario').val()+"<input class='id' type='hidden'  value='"+$('#txtIdProducto').val()+"'></td>"+
			"<td>"+$('#txtDescripcion').val()+"</td>"+
			"<td>"+$('#lstInventario option:selected').html()+"<input type='hidden' class='idInventario' value='"+$('#lstInventario').val()+"' ></td>"+
			"<td>"+$('#lstBloques option:selected').html()+"<input type='hidden' class='idBloque' value='"+$('#lstBloques').val()+"' ></td>"+
			"<td><input required='required' type='text' class='numeric malo' ></td>"+
			"<td><input required='required' type='text' class='numeric servicio' ></td>"+	
			"<td><input required='required' type='text' class='numeric vitrina' ></td>"+	
			"<td><input type='hidden' class='estado'  value='1' ><a href='' title='Eliminar'><img src='/imagenes/eliminar.gif' class='btnEliminar'></a> </td>"+	
			"<td><a href='' title='Guardar'><img width='25' height='25' src='/imagenes/grabar.gif' class='btnGrabar'></a></td>"+
		"</tr>";

	$('#txtProductoInventario').val('');
	$('#txtDescripcion').val('');
	$('#txtIdProducto').val(0);

	return fila;
}

function grabarInventario(padre){
	var idProducto=padre.find('.id').val();
	var productoVitrina=padre.find('.vitrina').val();
	var productoMalo=padre.find('.malo').val();
	var estado=padre.find('.estado').val();
	var productoServicio=padre.find('.servicio').val();
	var idInventario=padre.find('.idInventario').val();
	var idBloque=padre.find('.idBloque').val();
	$.ajax({
		url:'/detalleinventario/grabarInventarioPart1',
		type:'post',
		dataType:'json',
		async:false,
		data:{'idProducto':idProducto,'productoVitrina':productoVitrina,'productoMalo':productoMalo,'productoServicio':productoServicio,'estado':estado,'idInventario':idInventario,'idBloque':idBloque},
		success:function(resp){
			console.log(resp);
			if (resp.exito==true) {
				alert('Se Grabo Correctamente');
				padre.find('.vitrina').attr('readonly','readonly').css('background','silver');
				padre.find('.malo').attr('readonly','readonly').css('background','silver');
				padre.find('.servicio').attr('readonly','readonly').css('background','silver');
				padre.find('.btnEliminar').hide();
				padre.find('.btnGrabar').hide();
			}else{
				alert('Hubo un problema y no se pudo grabar');
			}
		}
	});
}

function verificarBloque(padre){
	var verificacion=true;
	var idProducto=padre.find('.id').val();
	var idInventario=padre.find('.idInventario').val();
	var idBloque=padre.find('.idBloque').val();
	$.ajax({
		url:'/detalleinventario/verificarBloque',
		type:'post',
		dataType:'json',
		async:false,
		data:{'idProducto':idProducto,'idInventario':idInventario,'idBloque':idBloque},
		success:function(resp){
			console.log(resp);
			if (resp.exito==false) {
				verificacion=false;
			}
		}
	});

	return verificacion;
}

function verificarBloqueAgregar(){
	var verificacion=true;
	var idProducto=$('#txtIdProducto').val();
	var idInventario=$('#lstInventario').val();
	var idBloque=$('#lstBloques').val();
	$.ajax({
		url:'/detalleinventario/verificarBloque',
		type:'post',
		dataType:'json',
		async:false,
		data:{'idProducto':idProducto,'idInventario':idInventario,'idBloque':idBloque},
		success:function(resp){
			console.log(resp);
			if (resp.exito==false) {
				verificacion=false;
			}
		}
	});

	return verificacion;
}

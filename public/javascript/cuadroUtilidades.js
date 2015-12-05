$(document).ready(function(){
	
	if ($('#cuadroutilidad').val()==1) {
		$('input').attr('disabled','disabled');
	}


	$('.neto').blur(function(){
		padre=$(this).parents('tr');
		//neto=parseFloat($(this).val());
		tipoCambio=parseFloat(padre.find('.tipocambio').val());
		cifVentas=parseFloat(padre.find('.cifVentas').val());
		precioNeto=parseFloat(padre.find('.neto').val());
		preciolista=parseFloat(padre.find('.preciolista').val());
		//costoAlmacen=parseFloat(padre.find('.costoAlmacen').val());
		if ($(this).val()!=0 && $(this).val()!="") {
			
			if(precioNeto <cifVentas){
				$('#resp').html('Error : El Precio Neto no puede ser menor que el Cif de Ventas ').css('color','red');
				padre.find('.preciolista').attr('readonly','readonly').css('background','skyblue').val('0');
				padre.find('.utilidad').val(0);
			// }else if (neto<cifVentas) {
			// 	$('#resp').html('Error : El Precio Neto no puede ser menor que el Cif de Ventas ').css('color','red');
			// 	padre.find('.preciolista').attr('readonly','readonly').css('background','skyblue').val('0');
			// 	padre.find('.utilidad').val(0);
			} else{
				$('#resp').html('');
				padre.find('.lblNeto').html(precioNeto).css('color','blue');
				padre.find('.preciolista').removeAttr('readonly').css('background','none');
				padre.find('.netosoles').val(parseFloat(precioNeto*tipoCambio).toFixed(2));
				
				var utilidades;
				utilidades=utilidad(preciolista,cifVentas);
				padre.find('.utilidad').val(utilidades)
				//padre.find('.utilidad').val(0);
				if (verificacion()==true) {
					
					$('#btnAceptar').removeAttr('disabled').css('background','#0693DE');
				}else{
					$('#btnAceptar').attr('disabled','disabled').css('background','red');
				}				
			}
		}else{
			$('#resp').html('Error : Ingrese un valor ').css('color','red');
			padre.find('.preciolista').attr('readonly','readonly').css('background','skyblue').val('0');
			padre.find('.utilidad').val(0);

		}


	});

	$('.preciolista').blur(function(){
		var padre=$(this).parents('tr');
		tipoCambio=parseFloat(padre.find('.tipocambio').val());
		cifVentas=parseFloat(padre.find('.cifVentas').val());
		precioNeto=parseFloat(padre.find('.neto').val());
		preciolista=parseFloat(padre.find('.preciolista').val());		// var preciocosto=parseFloat(padre.find('.fobUnit').val());
		// var preciolista=parseFloat($(this).val());
		if (precioNeto!=0 && precioNeto!="") {
			if (preciolista<precioNeto) {
				$('#resp').html('Error : El Precio Lista no puede ser menor que el Precio Neto ').css('color','red');
				padre.find('.utilidad').val(0);
				$('#btnAceptar').attr('disabled','disabled').css('background','red');
			}else{
				$('#resp').html('');
				var utilidades;
				utilidades=utilidad(preciolista,cifVentas);
				padre.find('.utilidad').val(utilidades);
				padre.find('.lblUtilidad').html(utilidades).css('color','blue');
				padre.find('.lblPrecioLista').html(preciolista).css('color','blue');
				padre.find('.preciolistasoles').val(parseFloat(preciolista*tipoCambio).toFixed(2));
				if (verificacion()==true) {
					
					$('#btnAceptar').removeAttr('disabled').css('background','#0693DE');
				}else{
					$('#btnAceptar').attr('disabled','disabled').css('background','red');
				}
			}
		}else{
			$('#resp').html('Error : Ingrese un Valor').css('color','red');
			padre.find('.utilidad').val(0);
			$('#btnAceptar').attr('disabled','disabled').css('background','red');
		}
	});
	$('#imprimir').click(function(e){
		e.preventDefault(e);
		$('#imprimir').hide();
		$('#btnAceptar').hide();
		$('.lblPrecioLista').show();
		$('.preciolista').hide();
		$('.lblNeto').show();
		$('.neto').hide();
		$('.lblUtilidad').show();
		$('.utilidad').hide();
		imprSelec('contenedorImpresion');
		$('.lblPrecioLista').hide();
		$('.preciolista').show();
		$('.lblNeto').hide();
		$('.neto').show();
		$('#imprimir').show();
		$('#btnAceptar').show();
		$('.lblUtilidad').hide();
		$('.utilidad').show();
	});
	$('#lstValorizados').change(function(){
		location='/ordencompra/cuadroUtilidad/'+$(this).val();
	});
});
function verificacion(){
	var respuesta=true;
	$('.utilidad').each(function(){
		
		if ($(this).val()>0) {
			console.log($(this).val());
		}else{
			console.log('false');
			respuesta=false;
			
		}

	});
	return respuesta;
}

function utilidad(preciolista,cifVentas){
	
	var respuesta=0;
	if(preciolista!=0){
		respuesta=((preciolista-cifVentas)/cifVentas)*100;
	}
	
	return respuesta.toFixed(2);
}
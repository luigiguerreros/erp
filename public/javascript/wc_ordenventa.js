$(document).ready(function(){
 // Llamadas a funciones
	var click=0;
 	$('.deposito').hide().removeAttr('required');
	$('.cheque').hide().removeAttr('required');
	$('.efectivo').hide().removeAttr('required');
	$('.notacredito').hide().removeAttr('required');

	$('#txtOrdenVenta').autocomplete({
		source: "/ordenventa/PendientesxPagar/",
		select: function(event, ui){
			$('#txtIdOrden').val(ui.item.id);
			$('#idOrdenVenta').val(ui.item.id);
			buscaOrdenCobro();
			cargaDetalleOrdenCobro();
		}
	});

	$('#txtOrdenVentaxId').autocomplete({
		source: "/ordenventa/PendientesxPagar/",
		select: function(event, ui){
			$('#txtIdOrdenVenta').val(ui.item.id);
		}
	});	

	$('#nrorecibo').keyup(function(){
		nrorecibo=$(this).val();
		res=verificarrecibo(nrorecibo);
		
	});

	$('#lstbanco').change(function(){
		idbanco=$(this).val();
		listaCta_banco(idbanco);
	});
	$('#tipocobro').change(function(){
		valor=$(this).val();
		if (valor==1) {
			$('.notacredito').hide().removeAttr('required');
			$('.deposito').hide().removeAttr('required');
			$('.cheque').hide().removeAttr('required');
			$('.efectivo').show().attr('required','required');
			$('#esvalidado').val('1');
		}else if (valor==2) {
		$('.notacredito').hide().removeAttr('required');
			$('.efectivo').hide().removeAttr('required');
			$('.deposito').hide().removeAttr('required');
			$('.cheque').show().attr('required','required');
			$('#esvalidado').val('0');
		}else if (valor==3 || valor==4) {
			$('.notacredito').hide().removeAttr('required');
			$('.efectivo').hide().removeAttr('required');
			$('.cheque').hide().removeAttr('required');
			$('.deposito').show().attr('required','required');
			$('#esvalidado').val('1');
		}else if(valor==""){
			$('.notacredito').hide().attr('required','required');
			$('.cheque').hide().attr('required','required');
			$('.deposito').hide().attr('required','required');
			$('.efectivo').hide().attr('required','required');
			$('#esvalidado').val('1');	
		}else if(valor==10){
			$('.cheque').hide().removeAttr('required');
			$('.deposito').hide().removeAttr('required');
			$('.efectivo').show().attr('required','required');
			$('.notacredito').show().attr('required','required');
			$('#esvalidado').val('1');	
		}
		else{
			$('.notacredito').hide().removeAttr('required');
			$('.cheque').hide().removeAttr('required');
			$('.deposito').hide().removeAttr('required');
			$('.efectivo').show().attr('required','required');
			$('#esvalidado').val('1');	
		}
	});

	$('#Registrar').click(function(e){
		if (click==1) {
			$(this).attr('disabled','disabled');
		}else if ($('#idOrdenVenta').val()=="") {
			e.preventDefault();
			$('#respGeneral').html('Seleccione una Orden de Venta').css('color','red');
		}else if($('#fechapago').val()==""){
			e.preventDefault();
			$('#respGeneral').html('Ingrese la fecha de Pago').css('color','red');
		}else{
			$('#respGeneral').html('');
			click++;
		}
	});
	
});


function buscaOrdenCobro(){
	var ordenVenta = $('#txtIdOrden').val();
	var ruta = "/ordencobro/buscarxOrdenVenta/" + ordenVenta;
	$.getJSON(ruta, function(data){
		$('#razonsocial').val(data.razonsocial);
		$('#idcliente').val(data.idcliente);
		$('#ruc').val(data.ruc);
		$('#codigo').val(data.codcliente);
		$('#codantiguo').val(data.codantiguo);
		$('#codigov').val(data.codigov);
		$('.inline-block input').exactWidth();
	});
}


function cargaDetalleOrdenCobro(){
	var ordenVenta = $('#txtIdOrden').val();
	var ruta = "/ordencobro/buscarDetalleOrdenCobro/" + ordenVenta;
	$.post(ruta, function(data){
		$('#tblDetalleOrdenCobro tbody').html(data);	
	});
}

function verificarrecibo(nrorecibo){
	var valorVerificacion;
	$.ajax({
		url:'/ingresos/verificarrecibo',
		type:'post',
		dataType:'json',
		data:{'nrorecibo':nrorecibo},
		success:function(resp){
		
			if (!resp.verificacion && $('#nrorecibo').val()!='') {
				$('#Registrar').attr('disabled','disabled');
				$('#Registrar').css('background','red');
				$('#respuesta').html('Numero de Codigo ya Exite').css('color','red');
			}else{
				$('#Registrar').removeAttr('disabled');
				$('#Registrar').css('background','#0693DE');
				$('#Registrar').css('background-image','-webkit-linear-gradient(bottom, #71CBFB 0%, #0693DE)');
				$('#respuesta').html('Codigo Correcto').css('color','blue');
			}
			if($('#nrorecibo').val()==''){
				$('#respuesta').html('').css('color','blue');
			}
		}
	});
}

function imprimircliente(){

		$('#imprimirCliente').html(
				'<fieldset>'+
					'<legend>Datos del Cliente</legend>'+
					
						'Código:<input type="text" value="'+$('#codigo').val()+'"  >'+
						'Razon Social:<input type="text"    size="40" value="'+$('#razonsocial').val()+'">'+ 
						'Número de RUC:<input type="text"  value="'+$('#ruc').val()+'">'+
						'Codigo Dakkar:<input type="text" value="'+$('#codantiguo').val()+'">'+
						'Nombre del Cobrador:<input type="text" value="CELESTIUM">'+
					''+
				'</fieldset>'
			);
}

function listaCta_banco(idbanco){
	$.ajax({
		url:'/cta_banco/listaCta_banco',
		type:'post',
		dataType:'html',
		data:{'idbanco':idbanco},
		success:function(resp){
			console.log(resp);
			$('#lstCtaCorriente').html(resp);
		}
	});
}

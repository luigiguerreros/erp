$(document).ready(function(){
	$('#liModoFacturacion').hide();
	$('.gbfacturacion').hide();
	$('#fsObservaciones').hide();
	var idOV="";
	var vPorcentaje="";
	var vTipo="";
	$('#txtOrdenVenta').autocomplete({
		source: "/ordenventa/autocomplete/",
		select: function(event, ui){
			$('#txtIdOrden').val(ui.item.id);
			buscaOrdenVenta();
			cargaDetalleOrdenVenta();
		}
	});
	$('#lstModoFacturacion').change(function(){
		idOV=$('#txtIdOrden').val();
		vPorcentaje=$('#txtPorcentajeFacturacion').val();
		vTipo=$('#lstModoFacturacion').val();
		$('#idOV').val(idOV);
		$('#vPorcentaje').val(vPorcentaje);
		$('#vTipo').val(vTipo);
		cambiaCondicionVenta();
	});

	$('#btnFactura').click(function(e){		
		if (idOV=="" || vPorcentaje=="" || vTipo=="" || $('#txtSerie').val()=="" || $('#textNFactura').val()=="" || $('#lstEmpresa').val()=="") {
			e.preventDefault();
		}else{
			
		}
	});
	$('#txtPorcentajeFacturacion').keyup(function(e){
		idOV=$('#txtIdOrden').val();
		vPorcentaje=$('#txtPorcentajeFacturacion').val();
		vTipo=$('#lstModoFacturacion').val();
		vDoc=$('#lstDoc').val();
		console.log(vDoc);
		$('#idOV').val(idOV);
		$('#vPorcentaje').val(vPorcentaje);
		$('#vTipo').val(vTipo);
		if (vPorcentaje==100) {
			console.log('entro');
			$('#lstModoFacturacion').removeAttr('required');
			$('#lstModoFacturacion').attr('disabled','disabled');
			$('#lstModoFacturacion').val(0);
			porcentaje =100;
			modo = 0;
			idOrden =  $('#txtIdOrden').val();
			if(porcentaje == 100 && porcentaje>0 && modo ==0 && idOrden != ""){
				var url = "/facturacion/listaProductosGuiaRecuperado/" + idOrden;
				$.post(url, {modo: modo, porcentaje: porcentaje}, function(data){
					$('#tblProductosGuia tbody').html(data);
				});
			
			}

		}else{
			$('#lstModoFacturacion').removeAttr('disabled');
			$('#lstModoFacturacion').attr('required','required');
			cambiaCondicionVenta();
		}
		if (vPorcentaje>100 || vPorcentaje==0 ) {
			$('#btnRegistrar').attr('disabled','disabled');
			$('#btnRegistrar').attr('title','El porcentaje es muy Alto');
			$('#btnRegistrar').css('color','red');
			$('#btnRegistrar').val('Deshabilitado');
		}else{
			$('#btnRegistrar').removeAttr('disabled');
			$('#btnRegistrar').removeAttr('title');
			$('#btnRegistrar').css('color','white');
			$('#btnRegistrar').val('Registrar');
		}
		
	});
	
	$('#lstEmpresa').change(function(e){
		valor2=$('#lstDoc').val();
		if (valor2==2) {
			//$('#vPorcentaje').removeAttr('disabled')
			$('#txtPorcentajeFacturacion').val(100);
			//$('#vPorcentaje').attr('disabled','disabled')
		}
	});

	$('#lstDoc').change(function(e){
		valor=$(this).val();
		$('#txtSerie').val("");
		$('#textNFactura').val("");
		if (valor==2) {
			$('#lstModoFacturacion').removeAttr('required');
			$('#lstModoFacturacion').attr('disabled','disabled');
			$('#txtPorcentajeFacturacion').val(100);
			$('#lstModoFacturacion').val(0);
			$('#txtPorcentajeFacturacion').attr('disabled','disabled');
			porcentaje =100;
			modo = 0;
			idOrden =  $('#txtIdOrden').val();
			if(porcentaje == 100 && porcentaje>0 && modo ==0 && idOrden != ""){
				var url = "/facturacion/listaProductosGuiaRecuperado/" + idOrden;
				$.post(url, {modo: modo, porcentaje: porcentaje}, function(data){
					$('#tblProductosGuia tbody').html(data);
				});
			
			}
		}else{
			$('#txtPorcentajeFacturacion').removeAttr('disabled');
		}
	});
	$('#btnRegistrar').click(function(e){
		if ($('#txtDireccionEnvio').val()!="" && $('txtContacto').val()!="") {
			if (idOV=="" || vPorcentaje=="" || vPorcentaje>100 || vTipo=="" || $('#txtSerie').val()=="" || $('#textNFactura').val()=="" || $('#lstEmpresa').val()=="") {
				
			}else{
				window.location='/facturacion/generafactura/';
			}
		}else{
			e.preventDefault();
			alert('Ingrese el Contacto y Direccion');
		}
	});
	
});

function buscaOrdenVenta(){
	var ordenVenta = $('#txtIdOrden').val();
	var ruta = "/ordenventa/buscarfactura/" + ordenVenta;
	$.getJSON(ruta, function(data){
		
		$('#txtCliente').val(data.cliente);
		$('#idcliente').val(data.idcliente);
		$('#txtFechaGuia').val(data.fechaguia);
		$('#txtDireccionEnvio').val(data.direccionfiscal);
		$('#direccionInicial').val(data.direccionfiscal);
		$('#txtContacto').val(data.contacto);
		$('#contactoInicial').val(data.contacto);
		$('.inline-block input').exactWidth();
		if(data.porcentajefacturacion !=0){
			$('#txtPorcentajeFacturacion').val(data.porcentajefacturacion);
			$('#lstModoFacturacion option[value="' + data.modofacturacion + '"]').attr('selected', true);
			//$('.gbfacturacion:last').after(data.observaciones);
			$('.gbfacturacion').show();
		}else{
			$('.gbfacturacion').hide();
		}
		cargaContacto();
		cargaDirecciones()
	});
	$('#lstDireccion').change(function(){
		
		if ($(this).val()!="") {
			$('#txtDireccionEnvio').val($('#lstDireccion option:selected').text()).css('width','350px');
		}else{
			$('#txtDireccionEnvio').val($('#direccionInicial').val());
		}
		
	});
	$('#lstContacto').change(function(){
		
		if ($(this).val()!="") {
			$('#txtContacto').val($('#lstContacto option:selected').text()).css('width','350px');
		}else{
			$('#txtContacto').val($('#contactoInicial').val());
		}
		
	});
}

function cargaDetalleOrdenVenta(){
	var ordenVenta = $('#txtIdOrden').val();
	var ruta = "/facturacion/listaproductosguia/" + ordenVenta;
	$.post(ruta, function(data){
		$('#tblProductosGuia tbody').html(data);	
	});
}
function cambiaCondicionVenta(){
	var porcentaje = parseInt($('#txtPorcentajeFacturacion').val());
	var modo = $('#lstModoFacturacion option:selected').val();
	var idOrden =  $('#txtIdOrden').val();
	if(porcentaje < 100 && porcentaje>0 && modo != "" && idOrden != ""){
		var url = "/facturacion/listaProductosGuiaRecuperado/" + idOrden;
		$.post(url, {modo: modo, porcentaje: porcentaje}, function(data){
			$('#tblProductosGuia tbody').html(data);
		});
	}
}
function cargaDirecciones(){
	var idcliente=$('#idcliente').val();
	$.ajax({
		url:'/cliente/direccion_fiscal',
		type:'post',
		dataType:'html',
		async: false,
		data:{'idcliente':idcliente},
		success:function(resp){
			//console.log(resp);
			$('#lstDireccion').html(resp);
		},
		error:function(error){
			//console.log('error');
		}
	});

}
function cargaContacto(){
	var idcliente=$('#idcliente').val();
	$.ajax({
		url:'/cliente/contactos',
		type:'post',
		dataType:'html',
		async: false,
		data:{'idcliente':idcliente},
		success:function(resp){
			//console.log(resp);
			$('#lstContacto').html(resp);
		},
		error:function(error){
			//console.log('error');
		}
	});

}
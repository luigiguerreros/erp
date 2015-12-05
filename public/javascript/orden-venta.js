$(document).ready(function(){
	msgboxTitle="Orden de Ventas";
	if($('#lstVendedor option:selected').val()!=""){
		$('#liVendedor').hide();
	}
	$('#btnRegistrar').click(function(e){
		if (!confirm('¿ Esta seguro de crear el pedido?')) {
			e.preventDefault();
		}
	});

	if ($('#codigov').val()!=undefined) {
		alert('Su Orden de Venta Creada es: '+$('#codigov').val());
	}
	$('#txtObservaciones').hide();
	$('.gbPorcentajeFacturacion').hide();
	$('.gbLetras').hide();
	$('#ul3').hide();
	$('#frmTransporte').hide();
	$('#liModoFacturacion').hide();
	$('#liMontoContado, #liMontoCredito, #liMontoLetras').hide();
	$('#liCreditoDias').hide();
	$('#txtTransporte').keyup(function(){
		if($(this).val() == ""){
			$('#frmTransporte input').val("");
		}
	});
	/* cancelar la orden de venta*/
	$('#btnCalcelar').click(function(e){
		e.preventDefault();


		/*if ($('.btnEditarCantidad').size()>0) {
			$('.btnEditarCantidad').each(function(e){
				padre=$(this).parents('tr');
				idProductoPadre=padre.find('.txtIdProducto').val();
				cantidadPadre=padre.find('.txtCantidad').val();
				agregaStockdisponible(idProductoPadre,cantidadPadre);
				$('#txtImporteTotal').val(0.00);
				padre.remove();

				
			});
		}else{*/
			window.location = '/vendedor/misordenes/';

		//}
		
		

		
	});
/*Autocomplete Cliente*/
	$('#txtCodigoCliente').autocomplete({
		source: "/cliente/autocompleteClienteZona/",
		minLength: 5,
		select: function(event, ui){
			$('#txtIdCliente').val(ui.item.idcliente);
			$('#txtIdClienteZona').val(ui.item.idclientezona);
			$('#txtRucDni').val(ui.item.rucdni);
			$('#txtDireccion').val(ui.item.direccion);
			$('#txtDistritoCiudad').val(ui.item.distritociudad);
			$('#txtCodCliente').val(ui.item.codigocliente);
			$('#txtCodAntiguo').val(ui.item.codigoantiguo);
			$('#txtTelefono').val(ui.item.telefono);
			//$('#txtAgenciaTransportes').val(ui.item.agenciatransporte);
			$('#txtFaxCelular').val(ui.item.faxcelular);
			$('#txtEmail').val(ui.item.email);
			//$('#txtidclientesucursal').val(ui.item.idclientesucursal);
			$('#txtdireccionenvio').val(ui.item.direccion_fiscal);
			$('#txtdirecciondespacho').val(ui.item.direccion_despacho_contacto);
			$('#txtnombrecontacto').val(ui.item.nombre_contacto);
			$('.inline-block input').exactWidth();
			//$('#txtCodigoCliente').exactWidth();
			cargaTransporte();
			cargaDescuento();
			cargaPosicion();
		}
	});
	
	/*Agregar producto a la guia de pedido*/
	$('#btnAgregarProduco').click(function(e){
		msgboxTitle="Orden de Ventas";
		e.preventDefault();

		var cantidadProducto=parseInt($('#txtCantidadProducto').val());
		var idProducto = $('#txtIdProducto').val();
		var elemento = $('#txtCantidadProducto');
		var saldo = parseFloat($('#idsaldo').val());
		console.log(cantidadProducto);
		console.log(idProducto);
		console.log(elemento);
		console.log('Saldo es: '+saldo);
		//alert(saldo);
		if(!$('#txtCodigoProducto, #txtCantidadProducto').valida()){
			return false;
		}else if(!existeProducto(msgboxTitle)){
			return false;
		}else if(!buscaProductoDetalleOrdenVenta()){
			return false;
		}else if(!verificaStockDisponible(cantidadProducto, idProducto, elemento)){
			return false;		
		}else{
			agregaProducto(saldo);
			//disminuyeStockdisponible(idProducto,cantidadProducto);
			//verificaSaldoDisponible(saldo);
			var contador = parseInt($('#txtContador').val()) + 1;
			$('#txtContador').attr('value', contador);
			$('#txtCodigoProducto').val('').focus();
			$('#txtTituloProducto').val('');
			$('#txtCantidadProducto').val('');
			$('#lstDescuento').val('');
		}
	});
	$('#lstTransporte').change(function(){
		var aaa=$('#txtCodigoCliente').val()+ ' - Saldo: '+ $('#idsaldo').val();
		$('#txtCabeceraCliente').val(aaa);
		console.log();
		if($(this).val() != ''){
			$('.show:eq(0)').click();
			if($('.show:eq(1)').text() != '-'){
				$('.show:eq(1)').click();
			}
		}
	});
	$('#lstVendedor').change(function(){
		if($(this).val() != ''){
			$('.show:eq(1)').click();
			if($('.show:eq(2)').text() != '-'){
				$('.show:eq(2)').click();
			}
		}
	});
	$('#frmGeneracionOrdenVenta').validate({
		rules: {
			modoFacturacion: {
				required: function(element){return $('#txtPorcentajeFacturacion').val() != 100}
			},
			saldoDisponible: {
				required: function(element){return $('#txtSaldoDisponible').val() != '1'}
			},
			condicionLetra: {
				required: '#chkLetras:checked'
			},
			tipoLetra: {
				required: '#chkLetras:checked'
			}
		},
		ignore: '.required-none'
	});

	$('#frmGeneracionOrdenVenta').submit(function(e){
		var creditoDias = $('#txtCreditoDias').val();
		var formaPago = $('#txtFormaPago').val();
		var montoContado = $('#txtMontoContado').val();
		var montoCredito= $('#txtMontoCredito').val();
		var montoLetras= $('#txtMontoLetras').val();
		var condicionLetraText = $.trim($('#lstCondicionLetra option:selected').text());
		var tipoDocumento = $('#lstCondicionLetra option:selected').val();
		var tipoLetraText = $.trim($('#lstTipoLetra option:selected').text());
		var porcentajeFacturacion = $('#txtPorcentajeFacturacion').val();
		var modoFacturacion = $.trim($('#lstModoFacturacion option:selected').text());
		var observacion = '<ul><li><strong>Forma Pago: </strong>' + formaPago+ '</strong>';
		if($('#chkCredito').is(':checked')){
			observacion += "<li><strong>Credito Dias:</strong> " + creditoDias + "</li>";
		}
		if($('#chkLetras').is(':checked')){
			observacion += "<li><strong>Condicion Letra:</strong> " + condicionLetraText + "</li>";
			observacion += "<li><strong>Tipo Letra:</strong> " + tipoLetraText + "</li>";
		}
		if(montoContado != ''){
			observacion += "<li><strong>Monto al Contado:</strong> " + montoContado + "</li>";	
		}
		if(montoCredito != ''){
			observacion += "<li><strong>Monto al Credito:</strong> " + montoCredito + "</li>";	
		}if(montoLetras != ''){
			observacion += "<li><strong>Monto a Letras:</strong> " + montoLetras + "</li>";	
		}
		if(tipoDocumento == 1 &&  porcentajeFacturacion != 100){
			observacion += "<li><strong>Porcentaje Facturacion:</strong> " + porcentajeFacturacion + " %" + "</li>";
			observacion += "<li><strong>Modo Facturacion: </strong>" + modoFacturacion + "</li>";	
		}
		observacion += "</ul>";
		$('#txtObservaciones').val(observacion);
	});
/*Muestra el Cuadro de dialogo luego agrega nuevo transporte para el cliente*/
	$('#btnNuevoTransporte').click(function(e){
		e.preventDefault();
		if($('#txtCodigoCliente').val() == ""){
			$.msgbox(msgboxTitle,'Para agregar un transporte primeramete ingrese el cliente');
			execute();
		}else{
			nuevoTransporte();	
		}
	});
	
/*Cambiar porcentaje de facturacion*/
	$('#btnCambiarPorcentaje').click(function(e){
		e.preventDefault();
		$('#txtPorcentajeFacturacion').attr('readonly', false).val('').focus();
	});
	$('#txtPorcentajeFacturacion').blur(function(){
		$('#txtPorcentajeFacturacion').attr('readonly', true);
		if(this.value == "100" || this.value ==""){
			this.value = 100;
			$('#liModoFacturacion').hide();
		}else{
			$('#liModoFacturacion').show();
		}
	});
	
/*Intercambio de tipo de documento*/	
	$('#lstTipoDocumento').change(function(){
		if($('#lstTipoDocumento option:selected').val()==1){
			$('.gbPorcentajeFacturacion').show();
			$('#txtPorcentajeFacturacion').val('100');
		}else{
			$('.gbPorcentajeFacturacion').hide();
			$('#txtPorcentajeFacturacion').val('100');
			$('#liModoFacturacion').hide();
		}
	});
	
/*Intercambio de forma de pago*/
$('#chkContado, #chkCredito, #chkLetras').on('change', function(){
	$('#lstCondicionLetra option').eq(0).attr('selected','selected');
	$('#lstTipoLetra option').eq(0).attr('selected','selected');
	var element = ($(this).is(':checked'))?'1':'0'
	var contado = ($('#chkContado').is(':checked'))?'1':'0';
	var credito = ($('#chkCredito').is(':checked'))?'1':'0';
	var letras = ($('#chkLetras').is(':checked'))?'1':'0';
	var type = $(this).attr('id');
	var formaPago = $('#txtFormaPago').val();
	if(element == 1){
		if(type == 'chkContado'){
			formaPago += 'Contado ';
		}else if(type == 'chkCredito')	{
			formaPago += 'Credito ';
		}else{
			formaPago += 'Letras ';
		}
	}else{
		if(type == 'chkContado'){
			formaPago = $('#txtFormaPago').val().replace('Contado ', '');
		}else if(type == 'chkCredito')	{
			formaPago = $('#txtFormaPago').val().replace('Credito ', '');
		}else{
			formaPago = $('#txtFormaPago').val().replace('Letras ', '');
		}
	}
	if(contado == 1 && credito == 1 && letras == 1){
		$('#liCreditoDias,.gbLetras,.condicionLetra').show();
		$('#liMontoContado,#liMontoCredito').show();
		$('#liMontoLetras').show();
	}else if(contado == 0 && credito == 0 && letras == 0){
		$('#liCreditoDias,.gbLetras,.condicionLetra').hide();
		$('#liMontoContado, #liMontoCredito, #liMontoLetras').hide();
	}else if(contado == 1 && credito == 0 && letras == 0){
		$('#liCreditoDias,.gbLetras,.condicionLetra').hide();
		$('#liMontoContado, #liMontoCredito, #liMontoLetras').hide();
	}else if(contado == 1 && credito == 1 && letras == 0){
		$('#liMontoContado,#liCreditoDias').show();
		$('.gbLetras,.condicionLetra').hide();
		$('#liMontoCredito, #liMontoLetras').hide();
	}else if(contado == 1 && credito == 0 && letras == 1){
		$('#liMontoContado,.gbLetras,.condicionLetra').show();
		$('#liCreditoDias').hide();
		$('#liMontoCredito, #liMontoLetras').hide();
	}else if(contado == 0 && credito == 1 && letras == 1){
		$('.gbLetras,.condicionLetra,#liCreditoDias').show();
		$('#liMontoContado').hide();
		$('#liMontoCredito, #liMontoLetras').show();
	}else if(contado == 0 && credito == 1 && letras == 0){
		$('#liCreditoDias').show();
		$('.gbLetras,.condicionLetra').hide();
		$('#liMontoContado,#liMontoCredito, #liMontoLetras').hide();
	}else if(contado == 0 && credito == 0 && letras == 1){
		$('#liCreditoDias').hide();
		$('.gbLetras,.condicionLetra').show();
		$('#liMontoContado,#liMontoContado,#liMontoCredito, #liMontoLetras').hide();
	}
	$('#txtFormaPago').val(formaPago);
});
	//Quitar un producto del detalle de la guia de pedido
	$('body').on('click', '.btnEliminarProducto', function(e){
		e.preventDefault();
		$fila = $(this).parents('tr');
		var total =parseFloat($fila.find('.txtTotal').val());
		var importeTotal = parseFloat($('#txtImporteTotal').val());
		$('#txtImporteTotal').val((importeTotal - total).toFixed(4));
		$fila.remove();
		idP=$fila.find('.txtIdProducto').val();
		canP=$fila.find('.txtCantidad').val();
		//agregaStockdisponible(idP,canP);
	});
	
	//Editar la cantidad del detalle de la guia de pedido
	$('body').on('click', '.btnEditarCantidad', function(e){
		e.preventDefault();
		//$(this).parents('tr').find('.txtCantidad').removeAttr('readonly').focus();
		$(this).parents('tr').find('.txtPrecio').removeAttr('readonly').focus();
	});
	//
	$('body').on('click', '.btnEditarPrecio', function(e){
		e.preventDefault();
		$(this).parents('tr').find('.txtPrecio').removeAttr('readonly').focus();
		//$(this).parents('tr').find('.txtCantidad').removeAttr('readonly').focus();
		
	});
	//
	$('.txtPrecio').live('blur',function(){
		padre=$(this).parents('tr');
		valorDescuento=padre.find('.porTxtDescuento').val();
		PrecioDescontado=parseFloat(padre.find('.txtPrecioDescontado').val());
		valorCantidad=parseFloat(padre.find('.txtCantidad').val());
		valorImporte=parseFloat(padre.find('.txtTotal').val());
		valorTotal=parseFloat($('#txtImporteTotal').val());
		nuevoPrecio=parseFloat(padre.find('.txtPrecio').val());

		if (valorDescuento=='') {
			valorDescuento=0;
		}else{
			valorDescuento=parseFloat(padre.find('.porTxtDescuento').val());
		}

		/*console.log(valorDescuento);
		console.log(PrecioDescontado);
		console.log(valorCantidad);
		console.log(valorImporte);
		console.log(valorTotal);
		console.log(nuevoPrecio);*/

		
		nuevoPrecioDescontado=(1-valorDescuento)*nuevoPrecio;
		//console.log('nuevo PrecioDescontado'+nuevoPrecioDescontado);
		nuevoImporte=nuevoPrecioDescontado*valorCantidad;
		nuevoTotal=valorTotal-valorImporte+nuevoImporte;

		padre.find('.txtPrecioDescontado').val(nuevoPrecioDescontado.toFixed(2));
		padre.find('.txtTotal').val(nuevoImporte.toFixed(2));
		//console.log('nuevo Total'+nuevoTotal);
		$('#txtImporteTotal').val(nuevoTotal.toFixed(2));



	});
	//Verificar la cantidad de producto al salir de txtCantidad
	$('body').on('blur', '.txtCantidad', function(e){
		e.preventDefault();
		var parentElement = $(this).parents('tr');
		var idProducto = parentElement.find('.txtIdProducto').val();
		var cantidadProducto = parseInt(parentElement.find('.txtCantidad').val());
		if($(this).hasClass('txtCantidad')){
			if(!verificaStockDisponible(cantidadProducto, idProducto, $(this))){
				return false;
			}

		}

		var precio = parseFloat(parentElement.find('.txtPrecio').val());
		//creo el nuevo precio descontado
		var txtPrecioDescontado=parseFloat(parentElement.find('.txtPrecioDescontado').val());

		var total = ((cantidadProducto * txtPrecioDescontado)).toFixed(2);
		parentElement.find('.txtTotal').val(total);
		var montoTotal=0;
		$('.txtTotal').each(function(){
			montoTotal += parseFloat($(this).val().replace(',',''));
		});
		$('#txtImporteTotal').val(montoTotal.toFixed(2));

		if($(this).hasClass('txtCantidad')){
			if(!verificaSaldoDisponible()){
					return false;
			}
		}

	});

	$(document).keydown(function(e){
	  var code = (e.keyCode ? e.keyCode : e.which);
		  if(code == 116) {
		   e.preventDefault();
		   r=confirm('¿No Recargue la pagina si tiene items agregados?', 'Confirmación', function(r) {
		       if(r)
		        location.reload();
		   });
		  }

	 });


});

msgboxTitle="Orden de Ventas";

//Agrega un producto al detalle de la guia de pedido
function agregaProducto(saldo){
	var validaelijemoneda=$('#txtMoneda').val();
	if(validaelijemoneda=="-1"){
		alert("Antes de registrar productos, debe elegir moneda");
		exit;
	}
	var lblmoneda=$('#lblMoneda').val();
	var tipoCambio=parseFloat($('#txtTipoCambioValor').val()).toFixed(2);
	var contador = $('#txtContador').val();
	var cantidad = parseInt($('#txtCantidadProducto').val());
	var descuento = 0;
	var descuentosolicitado = 0;
	if($('#lstDescuento').val()){
		descuentosolicitado = $('#lstDescuento').val();
		//descuentosolicitado = parseFloat($('#lstDescuento').val()).toFixed(4);
	}
	var importeTotal = parseFloat($('#txtImporteTotal').val()).toFixed(2);
	
	ruta= "/producto/buscar/" + $('#txtIdProducto').val()+"/"+descuentosolicitado;
	console.log(ruta);
	$.ajax({
		url:ruta,
		type:'get',
		dataType:'json',
		success:function(data){
			console.log(data);
		
		var descuentosolicitadoid=descuentosolicitado;
		var descuentosolicitadovalor=data.descuentosolicitado;
		var descuentosolicitadotexto=data.descuentovalor;
		var preciolistasoles=data.preciolista;
		if (lblmoneda=="US $") {
			var preciolista=parseFloat(data.preciolistadolares).toFixed(2);
		}else{
			var preciolista=parseFloat(data.preciolista).toFixed(2);
		};	
		
		var preciosolicitado=((1-descuentosolicitadovalor)*preciolista).toFixed(2);
		var preciototal=parseFloat(preciosolicitado*cantidad).toFixed(2);

		descuentototal=cantidad*(preciolista-preciosolicitado);

		importeTotal=parseFloat(importeTotal)+parseFloat(preciototal);

		$('#tblDetalleOrdenVenta tbody tr:last').before('<tr>'+
			'<td class="codigo">' + 
				data.codigo +
				'<input type="hidden" value="' + data.idproducto + '" name="DetalleOrdenVenta[' + contador + '][idproducto]" class="txtIdProducto">'+
				'<input type="hidden" value="' + descuentosolicitadoid + '" name="DetalleOrdenVenta[' + contador + '][descuentosolicitado]" >'+
				'<input type="hidden" value="' + descuentosolicitadotexto + '" name="DetalleOrdenVenta[' + contador + '][descuentosolicitadotexto]" >'+
				'<input type="hidden" value="' + descuentosolicitadovalor + '" name="DetalleOrdenVenta[' + contador + '][descuentosolicitadovalor]" class="porTxtDescuento text-50">'+
			'</td>'+
			'<td>' + data.nompro + '</td>'+
			//'<td>' + data.codigoalmacen + '</td>'+
			'<td>' + preciolistasoles + '</td>'+
			'<td>'+
				'<input type="text" value="' + cantidad + '" name="DetalleOrdenVenta[' + contador + '][cantsolicitada]" class="txtCantidad numeric text-50" readonly>'+
			'</td>'+
			'<td>'+ lblmoneda +' <input type="text" value="' + preciolista + '" name="DetalleOrdenVenta[' + contador + '][preciolista]" class="txtPrecio numeric text-50" readonly></td>'+
			'<td> ( <b>'+descuentosolicitadotexto+' </b>)</td>'+
			'<td>'+ lblmoneda +' '+
				'<input type="text" value="' + preciosolicitado + '" name="DetalleOrdenVenta[' + contador + '][preciosolicitado]" class="txtPrecioDescontado text-50"  readonly>'+
			'</td>'+						
			'<input type="hidden" value="' + descuentototal.toFixed(2) + '" name="DetalleOrdenVenta[' + contador + '][tipodescuento]" class="txtDescuento text-50">'+ 
			'<td class="center"> '+ lblmoneda +' <input type="text" value="' + preciototal + '" class="txtTotal text-100 right" readonly></td>'+
			'<td><a href="#" class="btnEditarCantidad"><img src="/imagenes/editar.gif"></a></td>'+
			'<td><a href="#" class="btnEliminarProducto"><img src="/imagenes/eliminar.gif"></a></td>'+
		'</tr>');
		
		$('#txtImporteTotal').val(importeTotal.toFixed(2));
		verificaSaldoDisponible();
	},
	error:function(error){
		console.log('error');
	}
	});	
	
}

//Evita que se agrege dos veces un producto al detalle de la guia de pedidos
	function buscaProductoDetalleOrdenVenta(){
		//var codigoProducto = $.trim($('#txtCodigoProducto').val());
		var codigoProducto = $('#txtCodigoProducto').val();
		var existe = $("#tblDetalleOrdenVenta .codigo:contains('"+codigoProducto+"')").length;
		if(existe > 0){
			$.msgbox(msgboxTitle,'El producto <strong>' + codigoProducto + '</strong> ya esta agregado en el<br>detalle de la guia de pedido.');
			$('#msgbox-ok, #msgbox-cancel, #msgbox-close').click(function(){
				$('#txtCantidadProducto').val('');
				$('#txtDescuento').val('');
				$('#txtCodigoProducto').val('').focus();
			});
			return false;
		}else{
			return true;
		}
	}
//Evitar que se ingrese una cantidad de stock, superior al stock diponible.
function verificaStockDisponible(cantidadSalida, idProducto, element){
	//la cantidades  se van aceptar >= que 0 por exigencia de facturacion
	if(cantidadSalida<=0){
		$.msgbox(msgboxTitle,"La cantidad de stock no puede ser menor o igual a cero");
		execute();
		return false;
	}else{
		var ruta = "/producto/cantidadstock/" + idProducto;
		var stockDisponible = 0;
		$.ajax({
			async: false,
			url: ruta,
			type: "POST",
			dataType: "json",
			success: function(data){stockDisponible = data.stockDisponible;}
		});
		if(cantidadSalida > stockDisponible){
			$.msgbox(msgboxTitle,"Cantidad de stock no disponible.<br><strong>Stock disponible: </strong>" + stockDisponible);
			execute();
			$('#msgbox-ok').click(function(){
				element.val('').focus();
			});
			return false;
		}else{
			return true;
		}
	}
}


function verificaSaldoDisponible(){
	//parseFloat($(this).val().replace(',',''));
	var saldo=parseFloat($('#idsaldo').val().replace(',',''));
	var importeTotal=parseFloat($('#txtImporteTotal').val());
	if(importeTotal>saldo){
		$.msgbox("ALERTA EN LA ORDEN","No tiene saldo suficiente,<br> Puede disminuir la Cantidad de Productos !!!");
		$('#txtSaldoDisponible').val('0');
		return false;
	}else{
		$('#txtSaldoDisponible').val('1');
		return true;
	}
}
/*Carga Transporte del cliente*/
function cargaTransporte(){
	console.log($('#txtIdCliente').val());
	var idCliente = $('#txtIdCliente').val();
	var ruta = "/facturacion/buscatransporte/" + idCliente;
	$.post(ruta, function(data){
		$('#lstTransporte').html('<option value="">-- Transporte --' + data);
	});
}


function cargaDescuento(){
	var ruta = "/descuento/listado/";
	$.post(ruta, function(data){
		$('#lstDescuento').html('<option value="">-- Descuento --' + data);
	});
}


function cargaPosicion(){
	var idCliente = $('#txtIdCliente').val();
	var ruta = "/cliente/posicionordenventa/"+ idCliente;
	$.post(ruta, function(data){
		$('#clienteposicion').html(data);
		var saldo=parseFloat($('#idsaldo').val());
		if(saldo<=0){
			$.msgbox("Alerta al Crear la ORDEN","EL CLIENTE TIENE SALDO S./ " + saldo+ "<br> Verifique el Saldo que necesita ANTES de empezar a registrar su orden.");
		}

	});

}


//Muestra el cuadro de dialogo para agregar un nuevo transporte para el cliente
function nuevoTransporte(){
	$.msgbox("Transporte Nuevos", '<div id="msgboxTransporte"></div>', '<a href="#" id="btnAgregarTransporte">Agregar</a>');
	$('#msgboxTransporte').load('/forms/transporte.phtml', function(){
		$('#txtTransporte').focus();
		execute();
	});
}

function agregaStockdisponible(idproducto,cantidad){
	$.ajax({
		url:'/producto/agregaStockdisponible',
		type:'post',
		datatype:'html',
		data:{'idproducto':idproducto,'cantidad':cantidad},
		success:function(resp){
			console.log(resp);
		}
	});
}
function disminuyeStockdisponible(idproducto,cantidad){
	$.ajax({
		url:'/producto/disminuyeStockdisponible',
		type:'post',
		datatype:'html',
		data:{'idproducto':idproducto,'cantidad':cantidad},
		success:function(resp){
			console.log(resp);
		}
	});
}

function cargaSucursales(){
	var idcliente=$('#idcliente').val();
	$.ajax({
		url:'/cliente/direccion_despacho',
		type:'post',
		dataType:'html',
		async: false,
		data:{'idcliente':idcliente},
		succcess:function(resp){
			$('#lstDirecciones').html(resp);
		}
	});
}
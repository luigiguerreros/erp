	//variables globales para esta vista
	var idCliente;
	var idProducto=0;
	var direccionDespacho;
	var direccionEnvio;
	var redondeo=0;
	var contador=0;
	var click=0;
	
$(document).ready(function(){
	redondeo=parseInt($('#redondeo').val());
	idCliente=$('#idCliente').val();
	direccionDespacho=$('#txtDireccionDespacho').val();
	direccionEnvio=$('#txtDireccionEnvio').val();
	contacto=$('#txtContacto').val();
	contador=parseInt($('#contador').val());
	verificarProductosIngresados();
	//variables generales


	cargadireccionDespacho(idCliente);
	cargaDireccionEnvio(idCliente);
	cargaContacto(idCliente);
	
	/********* Autocomplete ******/
	$('#txtProducto').autocomplete({
		source: "/producto/buscarAutocomplete/",
		select: function(event, ui){
			idProducto=ui.item.id;
			$('#idProducto').val(idProducto);
			$('#txtDescripcion').val(ui.item.tituloProducto);
			
		}
	});
	
	$('#txtCliente').autocomplete({
		source: "/cliente/autocomplete2/",
		select: function(event, ui){
			$('#idCliente').val(ui.item.id);
			cargaSucursales(ui.item.id);
			
	}});
	$('#txtVendedor').autocomplete({
		source: "/vendedor/autocompletevendedor/",
		select: function(event, ui){
			$('#idVendedor').val(ui.item.id);
		}
	});
	
	/*************** Botones ***********/
	$('#btnActualizaOrdenVenta').click(function(e){
		e.preventDefault();
		if(confirm('¿Desea realmente Actualizar?')){
			actualizaOrdenVenta();
		}
	});


	$('#btnCambiarMoneda').click(function(e){
		e.preventDefault();
		if(confirm('Se cambiara la moneda para todo el pedido !!!')){
			cambiarmoneda();
		}
	});


	$('#btnResetearOrdenVenta').click(function(e){
		e.preventDefault();
		var idOrdenVenta=$('#idOrdenVenta').val();
		if(confirm('¿Realmente desea RESETEAR la Orden de Venta ::: ESTE PROCESO ES IRREVERSIBLE?')){
			resetearOrdenventa(idOrdenVenta);
		}
	});

	$('#btnSalir').click(function(e){
		e.preventDefault();
		window.location.href='/ventas/guiamadre';
	});
	
	$('.btnEliminar').live('click',function(e){
		e.preventDefault();
		var padre=$(this).parents('tr');
		padre.find('.estadoDetalle').val('0');
		var importe=parseFloat(padre.find('.importe').val()).toFixed(redondeo);
		var total=parseFloat($('#txtTotal').val()).toFixed(redondeo);
		padre.css('display','none');
		var nuevoTotal=total-importe;
		$('#txtTotal').val(nuevoTotal.toFixed(redondeo));
		$('#txtTotalCompleto').val(nuevoTotal.toFixed(redondeo));
	});
	$('.btnEditar').live('click',function(e){
		e.preventDefault();
		padreGeneral=$(this).parents('tr');
		padreGeneral.find('.cantSolicitada').removeAttr('readonly');
		padreGeneral.find('.precioLista').removeAttr('readonly');
		padreGeneral.find('.cantSolicitada').focus();
	});
	$('.cantSolicitada').live('blur',function(e){
		e.preventDefault();
		var padre=$(this).parents('tr');
		var idDetalleOrdenVenta=parseInt(padre.find('.idDetalleOrdenVenta').val());
		var idProducto=parseInt(padre.find('.idProducto').val());
		var cantidadNueva=parseInt($(this).val());
		
		var cantidadInicial=parseInt(padre.find('.cantidadInicial').val());
		var cantidad=0;
		if(parseInt(idDetalleOrdenVenta)==0){
			cantidad=cantidadNueva;
		}else{
			if(cantidadNueva>cantidadInicial){
				cantidad=cantidadNueva-cantidadInicial;	
			}			
		}
		if($(this).val()==""){
			$(this).val(cantidadInicial);
			alert('La Cantidad no puede ser vacio');
		}else if(verificarStockDisponible(idProducto,cantidad)==true){
			if(idDetalleOrdenVenta==0){
				padre.find('.cantidadInicial').val(cantidadNueva);
			}
			calcularTotal(padre);
			$(this).attr('readonly','readonly');
			padre.find('.precioLista').focus();
		}else{
			$(this).val(cantidadInicial);
			alert('No hay suficiente stock');
		}
		
		
		
	});
	$('.precioLista').live('blur',function(e){
		e.preventDefault();
		var padre=$(this).parents('tr');
		var valorDescuento=parseFloat(1-parseFloat(padre.find('.descuentoValor').val()));
		var precioLista=parseFloat(padre.find('.precioLista').val()).toFixed(redondeo);
		var precioInicial=parseFloat(padre.find('.precioInicial').val()).toFixed(redondeo);
		if ($(this).val()!="") {
			var nuevoPrecioSolicitado=(precioLista*valorDescuento).toFixed(redondeo);
			padre.find('.precioSolicitado').val(nuevoPrecioSolicitado);
			calcularTotal(padre);
			
		}else{
			var nuevoPrecioSolicitado=(precioInicial*valorDescuento).toFixed(redondeo);
			padre.find('.precioSolicitado').val(nuevoPrecioSolicitado);
			calcularTotal(padre);
			$(this).val(precioInicial);
		}
		$(this).attr('readonly','readonly');
	});
	$('#btnActualizar').click(function(e){
		if(verificarCantidadRegistros()==false){
			e.preventDefault();
			alert('Tiene que tener al menos un registro para guardar los cambios');
		}else if(confirm('¿Desea Guardar los cambios del detalle?')){
			
			if(click==1){
				$('#btnActualizar').attr('disabled','disabled');
			}
			click++;
		}else{
			e.preventDefault();
		}
	});
	$('#btnAgregar').click(function(e){
		e.preventDefault();
		var idProducto=parseInt($('#idProducto').val());
		var cantidad=parseInt($('#txtCantidad').val());
		var descuentoSolicitado=parseInt($('#lstDescuento').val());
		if($('#txtCantidad').val()==""){
			alert('Ingrese un valor');
		}else if(cantidad==0){
			alert('Ingrese un valor mayor que cero');
		}else if(idProducto==0){
			alert('Seleccione un Producto');
		}else if($('#lstDescuento').val()==""){
			alert('Seleccione un Descuento');
		}else if(verificarProductosIngresados(idProducto)==false){
			alert('El Producto ya Fue agregado');
			limpiar();
		}else if(verificarStockDisponible(idProducto,cantidad)==false){
			alert('No hay suficiente stock');
		}
		else{
			nuevoProducto(idProducto,descuentoSolicitado,cantidad);	
		}
	});
	
	$('#btnAgregarPercepcion').click(function(e){
		//$(this).attr('disabled','disabled');
		var idOrdenVenta=$('#idOrdenVenta').val();
		var tipoAccion=1;
		var respta=verficaExistenciaPercepcion(idOrdenVenta,tipoAccion);
		console.log(respta);
		if (respta.validacion==false && respta.existe==0) {
			alert('No tiene Facturas creadadas');
		}else if (respta.validacion==false && respta.existe==1) {
			alert('No tiene Facturas creadadas, Pero tiene su Percepcion creada');
		}else if(respta.validacion==true && respta.existe==1){
			alert('Ya Tiene su percepcion creada');
		}
		else if(respta.validacion==true && respta.existe==0){
			cargaProgramacion(respta,tipoAccion);
		}
	});
	$('#btnEliminarPercepcion').click(function(e){
		//$(this).attr('disabled','disabled');
		var idOrdenVenta=$('#idOrdenVenta').val();
		var tipoAccion=2;
		var respta=verficaExistenciaPercepcion(idOrdenVenta,tipoAccion);
		
		if (respta.validacion==false && respta.existe==0) {
			alert('No se puede eliminar porque no tiene percepcion');
		}else if(respta.validacion==false && respta.existe==1){
			cargaProgramacion(respta,tipoAccion);
		}
		else if(respta.validacion==true && respta.existe==1){
			alert('No se puede eliminar porque tiene una factura activa');
		}else if(respta.validacion==true && respta.existe==0){
			alert('Tiene una factura activa y no tiene Percepcion ');
		}
	});
	$('.btnAumentarPercepcion').live('click',function(e){
		var idOrdenVenta=$('#idOrdenVenta').val();
		var tipoAccion=1;
		var respta=verficaExistenciaPercepcion(idOrdenVenta,tipoAccion);
		console.log(respta);
		if (respta.validacion==false && respta.existe==0) {
			alert('No tiene Facturas creadadas');
		}else if (respta.validacion==false && respta.existe==1) {
			alert('No tiene Facturas creadadas, Pero tiene su Percepcion creada');
		}else if(respta.validacion==true && respta.existe==1){
			alert('Ya Tiene su percepcion creada');
		}
		else if(respta.validacion==true && respta.existe==0){
			var padre=$(this).parents('tr');
			var idDetalleOrdenCobro=padre.find('.idDetalleOrdenCobro').val();
			var numDoc=padre.find('.numDoc').val();
			var montoPercepcion=$('#percepcion').val();
			var idOrdenGasto=$('#idOrdenGasto').val();
			var datosBusqueda=traerProgramacion(idDetalleOrdenCobro);
			if (datosBusqueda.situacion!="" && datosBusqueda.renovado!=0) {
				alert('Porque favor recargue la pagina los datos estan desactualizados');
			}else{
				aumentarPercepcion(idDetalleOrdenCobro,montoPercepcion,idOrdenGasto,numDoc);
			}
			
		}
			
		
	});
	$('.btnDisminuirPercepcion').live('click',function(e){
		var idOrdenVenta=$('#idOrdenVenta').val();
		var tipoAccion=2;
		var respta=verficaExistenciaPercepcion(idOrdenVenta,tipoAccion);
		//console.log(respta);
		if (respta.validacion==false && respta.existe==0) {
			alert('No se puede eliminar porque no tiene percepcion');
		}else if(respta.validacion==false && respta.existe==1){
			var padre=$(this).parents('tr');
			var idDetalleOrdenCobro=padre.find('.idDetalleOrdenCobro').val();
			var numDoc=padre.find('.numDoc').val();
			var montoPercepcion=$('#percepcion').val();
			var idOrdenGasto=$('#idOrdenGasto').val();
			var saldoDoc=padre.find('.saldo').val();
			var datosBusqueda=traerProgramacion(idDetalleOrdenCobro);
			//console.log(datosBusqueda);
			var importedoc=datosBusqueda.importedoc;
			var saldodoc=datosBusqueda.saldodoc;
			if (parseFloat(montoPercepcion)>parseFloat(saldoDoc).toFixed(redondeo)) {
				alert('La percepcion es mayor que el saldo');
			}else if(parseFloat(montoPercepcion)>parseFloat(saldodoc).toFixed(redondeo)){
				alert('Porque favor recargue la pagina los datos estan desactualizados');
			}else if(parseFloat(importedoc).toFixed(redondeo)!=parseFloat(saldodoc).toFixed(redondeo)){
				alert('Porque favor recargue la pagina los datos estan desactualizados');
			}else if(parseFloat(montoPercepcion)==parseFloat(saldoDoc).toFixed(redondeo)){
				diminuirPercepcion(idDetalleOrdenCobro,montoPercepcion,idOrdenGasto,numDoc);
			}
			
		}
		else if(respta.validacion==true && respta.existe==1){
			alert('No se puede eliminar porque tiene una factura activa');
		}else if(respta.validacion==true && respta.existe==0){
			alert('Tiene una factura activa y no tiene Percepcion ');
		}
			
		
	});
	$('#btnNP').live('click',function(e){
		e.preventDefault();
		var idOrdenVenta=$('#idOrdenVenta').val();
		var montoPercepcion=$('#percepcion').val();
		var idOrdenGasto=$('#idOrdenGasto').val();
		creaProgramacionPercepcion(idOrdenVenta,montoPercepcion,idOrdenGasto);	
	});
	
	/************* Listas *******************/
	$('#lstDireccionDespacho').change(function(e){
		if($(this).val()!=""){
			$('#txtDireccionDespacho').val($('#lstDireccionDespacho option:selected').html());
		}else{
			$('#txtDireccionDespacho').val(direccionDespacho);
		}
		
	});
	$('#lstDireccionEnvio').change(function(e){
		if($(this).val()!=""){
			$('#txtDireccionEnvio').val($('#lstDireccionEnvio option:selected').html());
		}else{
			$('#txtDireccionEnvio').val(direccionEnvio);
		}
		
	});
	$('#lstContacto').change(function(e){
		if($(this).val()!=""){
			$('#txtContacto').val($('#lstContacto option:selected').html());
		}else{
			$('#txtContacto').val(direccionEnvio);
		}
		
	});


/*************** Contenedores *****************/
	$('#contenedor').dialog({
		autoOpen:false,
		modal:true,
		width:1000,
		height:600,
		close:function(){
			$('#contenedor').html('');
		}
	});
});
function verficaExistenciaPercepcion(orden,tipoAccion) {
	var retorno=new Object();
 	$.ajax({
		url:'/ordengasto/verificaPercepcion',
		type:'post',
		async: false,
		dataType:'json',
		data:{'orden':orden},
		success:function(respta){
			//console.log(respta);
			retorno=respta;
			
		},
		error:function(error){
			//console.log('error');
		}
	});
	return retorno;
}
function cargaProgramacion(data,tipoAccion) {
	//console.log(data);
	$.ajax({
		url:'/ordencobro/buscarDetallePercepcion',
		type:'post',
		async: false,
		dataType:'html',
		data:{'idOrdenGasto':data.idOrdenGasto,'montoPercepcion':data.montoPercepcion,'orden':$('#idOrdenVenta').val(),'tipoAccion':tipoAccion},
		success:function(resp){
			//console.log(resp);
			$('#contenedor').html('');
			$('#contenedor').html(resp);
			$('#contenedor').dialog("open");
			
		}
	});
}
function aumentarPercepcion(idDetalleOrdenCobro,montoPercepcion,idOrdenGasto,numDoc) {
	//console.log(data);
	$.ajax({
		url:'/ordengasto/aumentarPercepcion',
		type:'post',
		async: false,
		dataType:'json',
		data:{'idDetalleOrdenCobro':idDetalleOrdenCobro,'montoPercepcion':montoPercepcion,'idOrdenGasto':idOrdenGasto,'numDoc':numDoc},
		success:function(resp){
			console.log(resp);
			if (resp.verificacion==true) {
				$('#contenedor').dialog('close');
				verificarCobro();
				alert('Se grabo Correctamente');
			}else{
				alert('Hubo problemas al momento de grabar');
			}
			
			
		}
	});
}
function creaProgramacionPercepcion(idOrdenVenta,montoPercepcion,idOrdenGasto) {
	//console.log(data);
	$.ajax({
		url:'/ordencobro/creaProgramacionPercepcion',
		type:'post',
		async: false,
		dataType:'json',
		data:{'idOrdenVenta':idOrdenVenta,'montoPercepcion':montoPercepcion,'idOrdenGasto':idOrdenGasto},
		success:function(resp){
			console.log(resp);
			if (resp.verificacion==true) {
				$('#contenedor').dialog('close');
				verificarCobro();
				alert('Se grabo Correctamente');
			}else{
				alert('Hubo problemas al momento de grabar');
			}
			
			
		}
	});
}
function diminuirPercepcion(idDetalleOrdenCobro,montoPercepcion,idOrdenGasto,numDoc) {
	
	$.ajax({
		url:'/ordengasto/disminuirPercepcion',
		type:'post',
		async: false,
		dataType:'json',
		data:{'idDetalleOrdenCobro':idDetalleOrdenCobro,'montoPercepcion':montoPercepcion,'idOrdenGasto':idOrdenGasto,'numDoc':numDoc},
		success:function(resp){
			console.log(resp);
			if (resp.verificacion==true) {
				$('#contenedor').dialog('close');
				verificarCobro();
				alert('Se grabo Correctamente');
			}else{
				alert('Hubo problemas al momento de grabar');
			}
			
			
		}
	});
}
function traerProgramacion(idDetalleOrdenCobro) {
	//console.log(data);
	var retorno=new Object();
	$.ajax({
		url:'/ordencobro/traerProgramacion',
		type:'post',
		async: false,
		dataType:'json',
		data:{'idDetalleOrdenCobro':idDetalleOrdenCobro},
		success:function(resp){
			retorno=resp;			
		}
	});
	return retorno;
}
function calcularTotal(padre){
	var precioLista=parseFloat(padre.find('.precioLista').val()).toFixed(redondeo);
	var cantidad=parseInt(padre.find('.cantSolicitada').val());
	var precioSolicitado=parseFloat(padre.find('.precioSolicitado').val()).toFixed(redondeo);
	var tipoDescuento=(cantidad*(precioLista-precioSolicitado)).toFixed(redondeo);
	var importeInicial=parseFloat(padre.find('.importe').val()).toFixed(redondeo);
	var importeNuevo=cantidad*precioSolicitado;
	var totalInicial=parseFloat($('#txtTotal').val()).toFixed(redondeo);
	var totalNuevo=total=totalInicial-importeInicial+importeNuevo;
	
	padre.find('.tipoDescuento').val(parseFloat(tipoDescuento).toFixed(redondeo));
	padre.find('.importe').val(importeNuevo.toFixed(redondeo));
	$('#txtTotal').val(totalNuevo.toFixed(redondeo));
	$('#txtTotalCompleto').val(totalNuevo.toFixed(redondeo));
}
function verificarProductosIngresados(idProducto){
	var verificacion=true;
	
	$( ".idProducto" ).each(function( index ) {
		//console.log($(this).val());
		padre=$(this).parents('tr');
		estado=parseInt(padre.find('.estadoDetalle').val());
		if(idProducto==parseInt($(this).val()) && estado==1){
			verificacion=false;
		}
	});
	return verificacion;
	
}
function verificarStockDisponible(idProducto,cantidad){
	var verificacion=true;
	$.ajax({
		url:'/producto/cantidadStock/'+idProducto,
		type:'post',
		dataType:'json',
		async: false,
		success:function(resp){
			//console.log(resp);
			if(resp.stockDisponible<cantidad){
				verificacion=false;
			}
		}
	});
	return verificacion;
}
function verificarCantidadRegistros(){
	var verificacion=true;
	var c=0;
	$( ".idProducto" ).each(function( index ) {
		//console.log($(this).val());
		var padre=$(this).parents('tr');
		var estado=parseInt(padre.find('.estadoDetalle').val());
		if(estado==1){
			c++;
		}
	});
	if(c==0){
		verificacion=false;
	}
	return verificacion;
}
function nuevoProducto(idProducto,descuentoSolicitado,cantidad){
	$.ajax({
		url:'/producto/buscar/'+idProducto+'/'+descuentoSolicitado,
		type:'post',
		dataType:'json',
		async: false,
		success:function(resp){
			//console.log(resp);
			$('#tblProductos tbody tr:last').before(crearFila(resp,descuentoSolicitado,cantidad));
			limpiar();
		}
	});
	
}
function cargaSucursales(idCliente){
	$.ajax({
		url:'/cliente/bucaZonasxCliente',
		type:'post',
		dataType:'html',
		data:{'idCliente':idCliente},
		success:function(resp){
			$('#lstSucursal').html('');
			$('#lstSucursal').html(resp);
			cargadireccionDespacho(idCliente);
			cargaDireccionEnvio(idCliente);
			cargaTransporte(idCliente);
			cargaContacto(idCliente);
			$('#txtDireccionDespacho').val('');
			$('#txtDireccionEnvio').val('');
			direccionDespacho="";
			direccionEnvio="";
			
		}
	});
	
}
function cargadireccionDespacho(idCliente){
	$.ajax({
		url:'/cliente/direccion_despacho',
		type:'post',
		dataType:'html',
		data:{'idcliente':idCliente},
		success:function(resp){
			$('#lstDireccionDespacho').html('');
			$('#lstDireccionDespacho').html(resp);

		}
	});
	
}
function cargaDireccionEnvio(idCliente){
	$.ajax({
		url:'/cliente/direccion_fiscal',
		type:'post',
		dataType:'html',
		data:{'idcliente':idCliente},
		success:function(resp){
			$('#lstDireccionEnvio').html('');
			$('#lstDireccionEnvio').html(resp);

		}
	});
	
}
function cargaContacto(idCliente){
	$.ajax({
		url:'/cliente/contactos',
		type:'post',
		dataType:'html',
		data:{'idcliente':idCliente},
		success:function(resp){
			$('#lstContacto').html('');
			$('#lstContacto').html(resp);

		}
	});
	
}
function cargaTransporte(idCliente){
	$.ajax({
		url:'/facturacion/buscatransporte/'+idCliente,
		type:'post',
		dataType:'html',
		success:function(resp){
			$('#lstTransporte').html('');
			$('#lstTransporte').html(resp);
		}
	});
	
}
function actualizaOrdenVenta(idCliente){
	$.ajax({
		url:'/ordenventa/actualizaOrdenVenta',
		type:'post',
		dataType:'json',
		async: false,
		data:$('#frmOrdenVenta').serialize(),
		success:function(resp){
			console.log(resp);
			if(resp.validacion==true){
				alert('Se grabo correctamente');
			}else{
				alert('Hubo problemas al momento de grabar');
			}
		}
	});
	
}

function cambiarmoneda(){
	idOrdenVenta=$('#idOrdenVenta').val();
	idMoneda=$('#txtMoneda').val();
	$.ajax({
		url:'/ordenventa/cambiarmoneda',
		type:'post',
		dataType:'html',
		data:{'idOrdenVenta':idOrdenVenta,'idMoneda':idMoneda},
		success:function(resp){		
			console.log(resp);
		}
	});
}

function resetearOrdenventa(idOrdenVenta){
	idOrdenVenta=$('#idOrdenVenta').val();
	$.ajax({
		url:'/ordenventa/resetearOrdenventa/',
		type:'post',
		dataType:'html',
		data:{'idOrdenVenta':idOrdenVenta},
		success:function(resp){		
			console.log(resp);
		}
	});

	window.location.href='/ordenventa/editarordenventa/'+idOrdenVenta;
}

function verificarCobro(){
	idOrdenVenta=$('#idOrdenVenta').val();
	$.ajax({
		url:'/ordencobro/verificarCobro',
		type:'post',
		dataType:'html',
		data:{'idOrdenVenta':idOrdenVenta},
		success:function(resp){		
			console.log(resp);
		}
	});
}

function limpiar(){
	$('#txtProducto').val('');
	$('#txtDescripcion').val('');
	$('#idProducto').val(0);
	$('#txtCantidad').val('');
	$('#lstDescuento').val('');
}
function crearFila(datos,descuentoSolicitado,cantidad){
	var validaelijemoneda=$('#txtMoneda').val();
	if(validaelijemoneda=="-1"){
		alert("Antes de registrar productos, debe elegir moneda");
		exit;
	}
	var lblmoneda=$('#lblMoneda').val();
	var tipoCambio=parseFloat($('#txtTipoCambioValor').val()).toFixed(2);

	var descto=1-parseFloat(datos.descuentosolicitado);
	
	if (lblmoneda=="US $") {
		var preciolista=parseFloat(datos.preciolistadolares).toFixed(2);
	}else{
		var preciolista=parseFloat(datos.preciolista).toFixed(2);
	}	

	var precioSolicitado=(preciolista*descto).toFixed(redondeo);
	var tipoDescuento=cantidad*(datos.preciolista-precioSolicitado);
	var importe=parseFloat(precioSolicitado*cantidad).toFixed(redondeo);
	var total=parseFloat($('#txtTotal').val()).toFixed(redondeo);
	total=(parseFloat(total)+parseFloat(importe)).toFixed(redondeo);
	contador++;
	$('#txtTotalCompleto').val(total);
	$('#txtTotal').val(total);
	
	var fila='<tr>'+
		'<td>'+datos.codigo+
	
			'<input type="hidden" value="0" class="idDetalleOrdenVenta"  name="detalleOV['+contador+'][iddetalleordenventa]">'+
			'<input type="hidden" value="'+datos.idproducto+'" class="idProducto" name="detalle['+contador+'][idproducto]">'+
			'<input type="hidden" value="1" class="estadoDetalle" name="detalle['+contador+'][estado]">'+
			'<input type="hidden" value="'+datos.descuentovalor+'"  name="detalle['+contador+'][descuentosolicitadotexto]">'+
			'<input type="hidden" value="'+datos.descuentosolicitado+'" class="descuentoValor" name="detalle['+contador+'][descuentosolicitadovalor]">'+
			'<input type="hidden" value="'+descuentoSolicitado+'" name="detalle['+contador+'][descuentosolicitado]">'+
			'<input type="hidden" value="'+tipoDescuento+'" class="tipoDescuento" name="detalle['+contador+'][tipodescuento]">'+
		'</td>'+
		'<td>'+datos.nompro+'</td>'+
		'<td>'+datos.codigoalmacen+'</td>'+
		'<td>'+
			'<input style="text-align:right;" type="text" value="'+cantidad+'" name="detalle['+contador+'][cantsolicitada]" class="cantSolicitada numeric text-50" readonly="readonly">'+
			'<input type="hidden" class="cantidadInicial" value="'+cantidad+'"  name="producto['+contador+'][cantidadInicial]">'+
		'</td>'+
		'<td><input style="text-align:right;" name="detalle['+contador+'][preciolista]"   type="text" value="'+preciolista+'" class="precioLista numeric text-50" readonly="readonly">'+
			'<input type="hidden" class="precioInicial" value="'+preciolista+'"></td>'+
		'<td>'+datos.descuentovalor+'</td>'+
		'<td>'+
			'<input style="text-align:right;" type="text" value="'+precioSolicitado+'" name="detalle['+contador+'][preciosolicitado]" class="precioSolicitado numeric text-50" readonly="readonly" >'+
			
		'</td>'+
		'<td>'+
			'<input style="text-align:right;" type="text" class="importe numeric text-100 " value="'+importe+'" readonly>'+
		'</td>'+
		'<td><a class="btnEditar" href="#"><img src="/imagenes/editar.gif"></a></td>'+
		'<td><a class="btnEliminar" href="#"><img src="/imagenes/eliminar.gif"></a></td>'+
		'</tr>';
	
	return fila;	
}

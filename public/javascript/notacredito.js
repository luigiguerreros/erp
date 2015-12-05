$(document).ready(function(){
	var iddocumento=0;
	
	var montoFactura=0;
	$('#txtFactura').autocomplete({
		source: "/facturacion/autocompletefactura/",
		select: function(event, ui){
			$('#txtIdOrden').val(ui.item.idorden);
			iddocumento=ui.item.id;
			buscaOrden();
			buscaFactura();
		}
	});

	$('#registrar').click(function(e){
		
		saldoFactura=parseFloat($('#saldoEscondido').val());
		Credito=parseFloat($('#credito').val());
		valor=saldoFactura-Credito;
			
		if (valor>0) {
			
			
		}else{
			e.preventDefault();
			alert('No puede ingresar una Cantidad mayor al saldo de la factura !');
		}
	});

});

function buscaOrden(){
	var ordenVenta = $('#txtIdOrden').val();
	var ruta = "/ordenventa/buscarFacturados/" + ordenVenta;
	//console.log(ruta);
	$.ajax({
		url:ruta,
		type:'get',
		dataType:'json',
		success:function(data){
			//console.log(data);
			$('#txtCliente').val(data.cliente);
			$('#txtRucDni').val(data.rucdni);
			$('#codigo').val(data.codigov);
			$('#importe').val(parseFloat(data.importeov).toFixed(2));
			$('#direccion').val(data.cdireccion);
			$('#ubicacion').val(data.lugar);
			$('#telefono').val(data.ctelefono);
			$('#idcliente').val(data.idcliente);
			
			
			
		},
		error:function(error){
			console.log('error');
		}

	});
}

function buscaFactura(){
	
	var ordenVenta = $('#txtIdOrden').val();
	var ruta = "/documento/buscar/" + ordenVenta;
	var modo="";
	var montofacturado=0;
	$.ajax({
		url:ruta,
		type:'get',
		dataType:'json',
		success:function(resp){
			console.log(resp);
			$('#numeroFactura').val(completarIzquierda(resp.serie, 3)+'-'+resp.numdoc)
			$('#porcentajeFacturacion').val(resp.porcentajefactura+'%');
			if (resp.modofactura==1) {
				modo="Precio";
			}else if(resp.modofactura==2){
				modo="Cantidad";
			}
			$('#modoFacturacion').val(modo);
			montofacturado=parseFloat(resp.montofacturado);
			//montoFactura=Math.round(parseFloat(resp.montofacturado)*100)/100;
			$('#montoFactura').val(resp.simbolo+' '+montofacturado.toFixed(2));
			$('#montoIGV').val(resp.simbolo+' '+parseFloat(resp.montoigv).toFixed(2));
			if (resp.saldo==null) {
				montoCredito=0;
			}else{
				montoCredito=parseFloat(resp.saldo);
			}
			
			saldoFactura=montofacturado-montoCredito;
			$('#saldoEscondido').val(saldoFactura);
			$('#saldo').val(resp.simbolo+' '+saldoFactura.toFixed(2));
		},
		error:function(error){
			console.log('error');
		}
	});
}
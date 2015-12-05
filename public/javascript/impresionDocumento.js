$(document).ready(function(){
	var tipodoc;
	var numdocGeneral="";
	var serieGeneral="";
	var ruta;
	var iddocumentogeneral;
	 	
	$('#lstTipoDocumento').change(function(){
		lista=$(this).val();
		tipodoc=lista;
		mostrarInput(lista);
	});

	$('#txtOrdenVenta').autocomplete({
		source: "/ordenventa/PendientesxPagar/",
		select: function(event, ui){
			$('#txtIdOrden').val(ui.item.id);
			Documento=ui.item.id;
			cargaDocumento(Documento,tipodoc);
		}
	});

	$('#txtNumeroLetra').autocomplete({
		source: "/ordenventa/busquedaletras/",
		select: function(event, ui){
			$('#txtIdOrden').val(ui.item.id);
			Documento=ui.item.id;
			cargaDocumento(Documento,tipodoc);
		}
	});

	$('.imprimir').live('click',function(e){
		e.preventDefault();
		if (confirm('¿Desea Realmente Imprimir?')) {
			
			padre=$(this).parents('tr');
			numdocGeneral=padre.find('.numdoc').val();
			serieGeneral=padre.find('.serie').val();
			iddocumentogeneral=$(this).attr('id');
			orden=$('#txtIdOrden').val();

			ruta=rutaDocumento(tipodoc,orden,iddocumentogeneral);
			//console.log(iddocumentogeneral);
			console.log(numdocGeneral);
			console.log(serieGeneral);

			if (numdocGeneral!="" && serieGeneral!="" && tipodoc>4 && numdocGeneral!=undefined) {
				console.log(tipodoc);
				actualizaDocumento(iddocumentogeneral,numdocGeneral,serieGeneral,ruta);
				
			}else if(numdocGeneral!="" && serieGeneral!=""){
				abrirVentana(ruta);
			 	console.log('entro');
			}else{
				alert('Ingrese La Serie y Numero de Documento');
				
			}
		}
	});

	$('.anular').live('click',function(e){
		e.preventDefault();
		padre=$(this).parents('tr');
		iddocumentogeneral=padre.find('.iddocumento').val();
		if (confirm('¿Esta Seguro de Anular?')) {
			anularDocumento(iddocumentogeneral,tipodoc);
		}
	});

});

function cargaDocumento(Documento,tipodoc){
	console.log(tipodoc);
	$.ajax({
		url:'/documento/documentosxOrden',
		type:'post',
		datatype:'html',
		data:{'idordenventa':Documento,'tipodoc':tipodoc},
		success:function(resp){
			$('#contenedorDocumentos').html(resp);
		}

	});
}

function abrirVentana(ruta){ 

	window.open(ruta,"Mi pagina","width=900,height=500,menubar=no,location=no,resizable=no"); 

}
function mostrarInput(lista){
	if (lista==7) {
		$('#txtOrdenVenta').hide();
		$('#txtNumeroLetra').show();
	}else if(lista==0){
		$('#txtOrdenVenta').hide();
		$('#txtNumeroLetra').hide();
	}else{
		$('#txtOrdenVenta').show();
		$('#txtNumeroLetra').hide();
	}
	$('#txtOrdenVenta').val('');
	$('#txtNumeroLetra').val('');
}

function rutaDocumento(lista,idordenventa,iddocumentogeneral){
	if (lista==1) {
		return rut='/documento/generaFactura/'+iddocumentogeneral;
			
	}else if(lista==2){
		return rut='/documento/generaBoleta/'+iddocumentogeneral;
	}
	else if(lista==4){
		return rut='/documento/generaGuia/'+iddocumentogeneral;
	}
	else if(lista==5){
		return rut='/documento/generaNotaCredito/'+iddocumentogeneral;
	}
	else if(lista==6){
		return rut='/documento/generaNotaDevito/'+iddocumentogeneral;
	}
	else if(lista==7){
		return rut='/documento/generaLetra/'+iddocumentogeneral;
	}			
}
function actualizaDocumento(iddocumento,numerodocumento,seriedocumento,ruta){
	$.ajax({
		url:'/documento/actualizaDocumentoJson',
		type:'post',
		datatype:'html',
		data:{'iddocumento':iddocumento,'numdoc':numerodocumento,'serie':seriedocumento},
		success:function(resp){
			console.log(resp);
			abrirVentana(ruta);
		}

	});
}
function anularDocumento(iddocumento,tipodoc){
	$.ajax({
		url:'/documento/anularDocumentos',
		type:'post',
		datatype:'html',
		data:{'iddocumento':iddocumento},
		success:function(resp){
			console.log(resp);
			valorDocumento=$('#txtIdOrden').val();
			cargaDocumento(valorDocumento,tipodoc);
		}

	});
}

$(document).ready(function(){
	var idov=0;
	$('#imprimir').hide();
	$('#btnEditarOrdenVenta').hide();
	$('#OrdenVentaEdicionGlobal').hide();
	$('.field-set').hide();
	
	$('#txtOrdenVenta').autocomplete({
		source: "/ordenventa/ListaGuiaMadre/",
		select: function(event, ui){
			
			var idOrdenVenta=ui.item.id;
			idov=ui.item.id;
			MostrarOrdenVenta(idOrdenVenta);
			$('#imprimir').show();
			$('#btnEditarOrdenVenta').show();
			$('.field-set').show();
		}
	});


	$('#txtOrdenVentaEdicionGlobal').autocomplete({
		source: "/ordenventa/ListaGuiaMadre/",
		select: function(event, ui){
			
			var idOrdenVenta=ui.item.id;
			idov=ui.item.id;
			MostrarOrdenVentaEdicionGlobal(idOrdenVenta);
			$('#OrdenVentaEdicionGlobal').show();
			$('.field-set').show();
		}
	});


	
	$('#imprimir').click(function(e){
		$('.ocultador').hide();
		$('.ocultarImpresion').hide();
		guiar(idov);
		imprSelec('muestra');
		$('.ocultador').show();
		$('.ocultarImpresion').show();
	});

	$('#editarPorcentaje').live('click',function(e){
		e.preventDefault();
		
	});
	$('#btnEditarOrdenVenta').live('click',function(e){
		e.preventDefault();
		window.location.href='/ordenventa/editarordenventa/'+idov;
		
	});
	$('.pestaña').click(function(e){
		e.preventDefault();

	});

	$('.ocultador').live('click',function(){
		valorO=$(this).val();
		if ($(this).attr('checked')=="checked") {
			$('.'+valorO).hide();
			padre=$(this).parents('tr').addClass('ocultarImpresion');
			//padre.find('.contenedorOcultador').addClass('ocultarImpresion');
		}else{
			$('.'+valorO).show();
			padre.removeClass('ocultarImpresion');
		}
	});

});	

function MostrarOrdenVenta(idordenventa){
	var ruta = "/ordenventa/CabeceraGuiaMadre/" + idordenventa;
	$.post(ruta, function(data){
		console.log(data);
		$('#tblDetalleOrdenVenta thead').html(data);	
	});

	cargaDetalleOrdenVenta(idordenventa);
}


function cargaDetalleOrdenVenta(idordenventa){
	var ruta = "/ordenventa/DetalleGuiaMadre/" + idordenventa;
	$.post(ruta, function(data){
		$('#tblDetalleOrdenVenta2 tbody').html(data);	
	});
	cargaDetalleOrdenCobro(idordenventa);
}

function cargaDetalleOrdenCobro(idordenventa){
	var ruta = "/ordencobro/buscarDetalleOrdenCobroGuia/" + idordenventa;
	$.post(ruta, function(data){
		$('#tblDetalleOrdenCobro tbody').html(data);	
	});
}



function guiar(idov){
	esguiado=1;
	$.ajax({
		url:'/ordenventa/guiar',
		type:'post',
		datatype:'html',
		data:{'idov':idov,'esguiado':esguiado},
		success:function(resp){
			console.log(resp);
		}
	});
}

function porcentajeComision(idordenventa,porcentaje){
	
	
	$.ajax({
		url:'/ordenventa/porcentajeComision',
		type:'post',
		dataType:'html',
		data:{'idordenventa':idordenventa,'porcentaje':porcentaje},
		success:function(resp){
			console.log(resp);

		}
	});
}


// Edicion Global de Guia Madre
function MostrarOrdenVentaEdicionGlobal(idordenventa){
	var ruta = "/ordenventa/EdicionGlobal/" + idordenventa;
	$.post(ruta, function(data){
		$('#tblOrdenVenta thead').html(data);	
	});
}
function MostrarDetalleOrdenVentaEdicionGlobal(idordenventa){
	var ruta = "/ordenventa/EdicionGlobalDetalle/" + idordenventa;
	$.post(ruta, function(data){
		$('#tblDetalleOrdenVenta thead').html(data);	
	});
}
function MostrarOrdenVentaAprobaciones(idordenventa){
	var ruta = "/ordenventa/EdicionGlobalAprobaciones/" + idordenventa;
	$.post(ruta, function(data){
		$('#tblDetalleOrdenVenta thead').html(data);	
	});
}
function MostrarOrdenVentaCondicionesCredito(idordenventa){
	var ruta = "/ordenventa/EdicionGlobalCondicionesCredito/" + idordenventa;
	$.post(ruta, function(data){
		$('#tblDetalleOrdenVenta thead').html(data);	
	});
}
function MostrarOrdenVentaProgramacionPagos(idordenventa){
	var ruta = "/ordenventa/EdicionGlobalProgramacionPagos/" + idordenventa;
	$.post(ruta, function(data){
		$('#tblDetalleOrdenVenta thead').html(data);	
	});
}



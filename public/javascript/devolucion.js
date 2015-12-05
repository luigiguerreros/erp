$(document).on('ready',function(){
	var nada="";
	var idOV=$('#txtOV').val();
	var idNDevolucion=$('#txtNDevolucion').val();

	$('.devregistrada').hide();

	//txtOV
	$('#txtOV').autocomplete({
		source: "/ordenventa/ListaEstadoGuia/",
		select: function(event, ui){
			var idOrdenVenta=ui.item.id;
			
		}
	});

	$('#detalle').hide();

	$('#btnAceptar').on('click',function(e){
		aceptar();
	});
	$('.save').live('click',function(e){
		e.preventDefault();
		padre=$(this).parents('tr');

		elemento=$(this); 
		idDD=padre.find('.modificar').attr('id'); 
		cant=padre.find('.modificar').val();
							
		$.ajax({
			url:'/devolucion/actualizaDevolucion/',
			type:'post',
			data:{'idDD':idDD,'cantidad':cant},
		 	success:function(resp){
		 		console.log(resp);
		 		elemento.attr('readonly','readonly');
		 		elemento.css('background-color','red');
		 		aceptar();
		 	},
		 	error:function(error){
		 		console.log(error)
		 	}
		}); 

		
		
	});
	
	$('#nuevaDevolucion').on('click',function(e){
		e.preventDefault();
		$('#txtNDevolucion').removeAttr('readonly');
		$('#txtNDevolucion').attr('value','');
	});	
	
	$('#btnAprobar').click(function(e){
		e.preventDefault();
		if (idOV!="" && idNDevolucion!="") {
			$.ajax({
			url:'/devolucion/grabaAprobacion/',
			type:'post',
			data:{'idNDevolucion':idNDevolucion},
			success:function(resp){
				console.log(resp);
				console.log('entre');
				
				window.location='/devolucion/devolucion';
			},
			error:function(error){
				//console.log(error);

			}
			});
		}
	});

	$('.verdetalle').click(function(e){
		e.preventDefault();
		IDD=$(this).attr('id');
		
		$.ajax({
			url:'/devolucion/listaDetalleDevolucion',
			type:'post',
			data:{'IDD':IDD},
			success:function(resp){
				console.log(resp);
				$('#detalle').hide('Blind');
				$('#mensaje').html(' Devolucion  N°'+IDD);
				$('#tbldetalles tbody').html(resp);
				$('#detalle').show('Blind');
			},
			error:function(error){
				console.log(error);
			}
		});
		$.ajax({
			url:'/devolucion/encabezadoDevolucion',
			type:'post',
			data:{'IDD':IDD},
			success:function(resp){
				console.log(resp);
				
				
				$('#tblEncabezado').html(resp);
				
			},
			error:function(error){
				console.log(error);
			}
		});
	});

	$('#btncerrar').click(function(e){
		$('#detalle').hide('Blind');
	});

	$('#btnCancelar').click(function(e){
		e.preventDefault();
		window.location='/devolucion/listadevoluciones/';
	});

	$('#cancelarDevolucionAprobada').click(function(e){
		e.preventDefault();
		window.location='/devolucion/listadevolucionesAprobadas/';
	});

	$("#seleccion").change(function(){
		var id=$("#seleccion option:selected").text();
		var url='/devolucion/listarDevolucionTotal/'+id;
		window.location=url;
	});

	$(".devregistrada").click(function(e){
		if(!confirm('¿Esta Seguro de Confirmar esta Devolucion?')){
			e.preventDefault();
		}
	});
	$(".devEliminar").click(function(e){
		if(!confirm('¿Esta Seguro de Eliminar la Devolucion?')){
			e.preventDefault();
		}
	});

	function aceptar(){
		idOV=$('#txtOV').val();
		idNDevolucion=$('#txtNDevolucion').val();
		cargaobservaciones(idNDevolucion);

		//$('#tblDetalles tbody').html('');
		
		$.ajax({
			url:'/devolucion/grabaDetalle/',
			type:'post',
			data:{'idOV':idOV,'idNDevolucion':idNDevolucion},
			success:function(resp){
				//console.log(resp);
				$('#tblDetalles tbody').html(resp);
				$('#txtNDevolucion').val(idNDevolucion);
				$('#txtNDevolucion').attr('readonly','readonly');

				if (idNDevolucion=="") {
			
					$.ajax({
						url:'/devolucion/obtieneIdDevolucion/',
						type:'post',
						data:{'idOV':nada},
						success:function(resp){
							//console.log('resp');
							
							$('#txtNDevolucion').val(resp);

						},
						error:function(error){
							console.log('error');

						}
					});
				}

			},
			error:function(error){
				console.log(error);

			}
		});
	}
	$('.editarPrecio').live('click',function(e){
		e.preventDefault();
		padre=$(this).parents('tr');
		padre.find('.precioDevolucion').removeAttr('readonly').css('background-color','red').focus();
	});
	$('.grabarPrecio').live('click',function(e){
		e.preventDefault();
		padreGeneral=$(this).parents('tr');
		
		cambiaPrecioDevolucion(padreGeneral);
	});
	$('#imprimir').click(function(e){
		e.preventDefault();
		$('.devregistrada').show();
		$('#imprimir').hide();
		$('#btncerrar').hide();
		$('table tr td, table tr th').css('font-family','courier');
		$('body').css('color','black');
		imprSelec('detalle');
		$('#imprimir').show();
		$('#btncerrar').show();
		$('table tr td, table tr th').css('font-family','Calibri');
	});

	$('#grabarObservaciones').click(function(e){
		e.preventDefault();
		
		grabarObservaciones($('#txtNDevolucion').val(),$('#observaciones').val());
	});

	$('#btnConsultar').click(function(e){
		ValidarFiltros();
		var idcliente=$('#txtIdCliente').val();
		var idordenventa=$('#txtIdOrdenVenta').val();
		var situacion=$('#txtSituacion').val();
		var fecregini=$('#txtFechaRegistroIni').val();
		var fecregfin=$('#txtFechaRegistroFin').val();
		var fecaprini=$('#txtFechaAprobadoIni').val();
		var fecaprfin=$('#txtFechaAprobadoFin').val();
		var devtotal=$('#txtModoConsulta').val();
		$.ajax({
			url:'/devolucion/DataReporteDevoluciones/',
			type:'post',
			data:{'idcliente':idcliente,'idordenventa':idordenventa,'situacion':situacion,'fecregini':fecregini,'fecregfin':fecregfin,'fecaprini':fecaprini,'fecaprfin':fecaprfin,'devtotal':devtotal},
		 	success:function(resp){
		 		console.log(resp)
		 		$('#tblDevoluciones tbody').html(resp);
		 	},
		 	error:function(error){
		 		console.log(error)
		 	}
		});
	});

	$('#btnImprimir').click(function(e){	
		imprSelec('impresion-contenedor');
	});

	$('#btnLimpiar').click(function(e){
		$('#txtClientexIdCliente').val('');
		$('#txtIdCliente').val('');
		$('#txtOrdenVentaxId').val('');
		$('#txtIdOrdenVenta').val('');
		$('#txtSituacion').val('');
		$('#txtFechaRegistroIni').val('');
		$('#txtFechaRegistroFin').val('');
		$('#txtFechaAprobadoIni').val('');
		$('#txtFechaAprobadoFin').val('');
		$('#txtModoConsulta').val('');
		$('#tblDevoluciones tbody').html('');
	});	


});

function cambiaPrecioDevolucion(padreGeneral){
	var precioDevolucion=padreGeneral.find('.precioDevolucion').val();
	var idDetalleDevolucion=padreGeneral.find('.idDetalleDevolucion').val();
	var cantidadDevuelta=padreGeneral.find('.cantidadDevuelta').val();
	$.ajax({
			url:'/devolucion/cambiaPrecioDevolucion/',
			type:'post',
			data:{'precioDevolucion':precioDevolucion,'idDetalleDevolucion':idDetalleDevolucion,'cantidadDevuelta':cantidadDevuelta},
			success:function(resp){
				console.log(resp);
				$('.precioDevolucion').attr('readonly','readonly').css('background-color','white');
				$('#btnAceptar').click();

			},
			error:function(error){
				console.log(error);

			}
		});
}

function grabarObservaciones(iddevolucion,observaciones){

	$.ajax({
			url:'/devolucion/grabaobservaciones/',
			type:'post',
			data:{'iddevolucion':iddevolucion,'observaciones':observaciones},
			success:function(resp){
				console.log(resp);
				alert('Se Grabo Correctamente');
				

			},
			error:function(error){
				//console.log(error);

			}
		});
}
function cargaobservaciones(iddevolucion){

	$.ajax({
			url:'/devolucion/cargaobservaciones/',
			type:'post',
			data:{'iddevolucion':iddevolucion},
			success:function(resp){
				//console.log(resp);
				$('#observaciones').val(resp);
				

			},
			error:function(error){
				console.log('error');

			}
		});
}

function ValidarFiltros () {
		var idcliente=$('#txtClientexIdCliente').val();
		var idordenventa=$('#txtOrdenVentaxId').val();
		var situacion=$('#txtSituacion').val();
		var fecregini=$('#txtFechaRegistroIni').val();
		var fecregfin=$('#txtFechaRegistroFin').val();
		var fecaprini=$('#txtFechaAprobadoIni').val();
		var fecaprfin=$('#txtFechaAprobadoFin').val();
		var devtotal=$('#txtModoConsulta').val();
		if(idcliente=='' && idordenventa=='' && situacion=='-1' && fecregini=='' && fecregfin=='' && fecaprini=='' && fecaprfin==''){
			alert("DEBE INGRESAR UNA CONDICION DE BUSQUEDA");
			exit;
		}
}


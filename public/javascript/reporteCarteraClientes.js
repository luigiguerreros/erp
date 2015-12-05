$(document).ready(function(){

	var lstZona=$('#lstZona').html();
	var lstRegionCobranza=$('#lstRegionCobranza').html();
	var lstProvincia=$('#lstProvincia').html();
	var lstDistrito=$('#lstDistrito').html();
	
	/*********************/
	cargaDpt();
	/**************** Botones **********************/
	$('#btnConsultarHtml5').click(function(e){
		e.preventDefault();
		llenarEncabezado();
		$('#cabecera').show();
		$('#tblVentas').show();
		cargaConsulta();
		//console.log($('#Parametros').serialize());
	});
	$('#btnLimpiar').click(function(e){
		e.preventDefault();
		limpiar();
	});
	$('#btnImprimirHtml5').click(function(e){
		e.preventDefault();
		imprSelec('contenedorImpresion');
		
	});

	$('#btnConsultarPdf').click(function(e){
		$('#frmParametros').attr('action','/pdf/reporteCarteraClientes');
	});

	$('#btnConsultarExcel').click(function(e){
		//alert(44);
		$('#frmParametros').attr('action','/excel/reporteCarteraClientes');	
	});
	
	/**************  Listas ***********/
	$('#lstCategoriaPrincipal').change(function(){
		idpadre=$(this).val();
		if (idpadre=="") {
			$('#lstRegionCobranza').html(lstRegionCobranza);
			$('#lstRegionCobranza').change();
		}else{
			cargaRegionCobranza(idpadre);
		}

		$('#lstRegionCobranza').change();
	});

	$('#lstRegionCobranza').change(function(){
		idzona=$(this).val();
		//console.log(idzona);
		if (idzona=="") {
			$('#lstZona').html(lstZona);
		}else{
			cargaZonas(idzona);
		}
	});

	$('#lstZona').change(function(){

	});
	
	$('#lstDepartamento').change(function(){
		if($('#lstDepartamento').val()==""){
			$('#lstProvincia').html(lstProvincia);
			$('#lstProvincia').change();
		}
	});

	$('#lstProvincia').change(function(){
		if($('#lstProvincia').val()==""){
			$('#lstDistrito').html(lstDistrito);
		}		
	});

	/******************** Autocomplete ****************************/
	$('#txtOrdenVenta').autocomplete({
		source: "/ordenventa/buscarautocompletecompleto/",
		select: function(event, ui){
			$('#idOrdenVenta').val(ui.item.id);
		}
	});

	$('#txtCliente').autocomplete({
		source: "/cliente/autocomplete2/",
		select: function(event, ui){
			$('#idCliente').val(ui.item.id);
			
	}});

	$('#txtVendedor').autocomplete({
		source: "/vendedor/autocompletevendedor/",
		select: function(event, ui){
			$('#idVendedor').val(ui.item.id);
		}
	});
	
    //});
});
//funciones fuera del evento ready
function cargaRegionCobranza(idpadre){
	$.ajax({
		url:'/zona/listaCategoriaxPadre',
		type:'post',
		async: false,
		dataType:'html',
		data:{'idpadrec':idpadre},
		success:function(resp){
			//console.log(resp);
			$('#lstRegionCobranza').html(resp);
		}
	});
}
function cargaZonas(idzona){
	$.ajax({
		url:'/zona/listaZonasxCategoria',
		type:'post',
		async: false,
		dataType:'html',
		data:{'idzona':idzona},
		success:function(resp){
			$('#lstZona').html(resp);
		}
	});
}
function cargaDpt(){
	$.ajax({
		url:'/departamento/listaroptions/pe',
		type:'post',
		async: false,
		dataType:'html',
		success:function(resp){
			$('#lstDepartamento').html("<option value=''>Seleccione Departamento</option>"+resp);
		}
	});
}
function cargaProv(iddepartamento){
	$.ajax({
		url:'/provincia/listaroptions/'+iddepartamento,
		type:'post',
		async: false,
		dataType:'html',
		success:function(resp){
			$('#lstDepartamento').html("<option value=''>Seleccione Provincia</option>"+resp);
		}
	});
}
function cargaDist(){
	$.ajax({
		url:'/departamento/listaroptions/pe',
		type:'post',
		async: false,
		dataType:'html',
		success:function(resp){
			$('#lstDepartamento').html("<option value=''>Seleccione Departamento</option>"+resp);
		}
	});
}
function cargaConsulta(){
	$.ajax({
		url:'/ventas/listaReporteVentas',
		type:'post',
		dataType:'html',
		data:$('#Parametros').serialize(),
		success:function(resp){
			$('#tblVentas tbody').html(resp);
		}
	});
}

function limpiar(){
	$('#frmParametros')[0].reset();
	$('.encabezado').html('');
	$('#idOrdenVenta').val('');
	$('#idCliente').val('');
	$('#idVendedor').val('');
	$('#cabecera').hide();
	$('#tblVentas').hide();
}
function llenarEncabezado(){
	$('#lblFechaGuiado').html($('#txtFechaGuiadoInicio').val()+' - '+$('#txtFechaGuiadoFin').val());
	$('#lblFechaAprobado').html($('#txtFechaAprobadoInicio').val()+' - '+$('#txtFechaAprobadoFinal').val());
	$('#lblFechaDespacho').html($('#txtFechaDespachoInicio').val()+' - '+$('#txtFechaDespachoFin').val());
	$('#lblFechaCancelado').html($('#txtFechaCanceladoInicio').val()+' - '+$('#txtFechaCanceladoFin').val());
	$('#lblOrdenVenta').html($('#txtOrdenVenta').val());
	$('#lblCliente').html($('#txtCliente').val());
	$('#lblVendedor').html($('#txtVendedor').val());
	$('#lblZonaGeografica').html($('#lstCategoriaPrincipal option:selected').html());
	$('#lblRegionCobranza').html($('#lstRegionCobranza option:selected').html());
	$('#lblZona').html($('#lstZona option:selected').html());
	$('#lblCondicion').html($('#lstCondicion').val());
}
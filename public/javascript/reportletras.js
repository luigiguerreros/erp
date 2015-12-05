$(document).ready(function(){
	listas=$('#listas');
	
	
	
	
	lstCategoriaPrincipal=$('#lstCategoriaPrincipal');
	lstCategoria=$('#lstCategoria');
	lstZona=$('#lstZona');
	lstTipoCobranza=$('#lstTipoCobranza');
	lstvendedor=$('#lstvendedor');
	fechaInicio=$('#fechaInicio');
	fechaFinal=$('#fechaFinal');
	lstTipoCobro=$('#lstTipoCobro');
	lstOctavas=$('#lstOctavas');
	valoropcional=$('#valoropcional');
	IdCliente=$('#txtIdCliente');
	IdOrdenVenta=$('#txtIdOrdenVenta');
	enviar=$('#enviar');
	var idcobrador;
	tblreportes=$('#tblreportes');
	var	parametro;
	var titulo;

	$('#cancelado').click(function(){
		if ($(this).attr('checked')=='checked') {
			$('#fechaPagoInicio').removeAttr('disabled').css('background','skyblue');
			$('#fechaPagoFinal').removeAttr('disabled').css('background','skyblue');
		}else{
			$('#fechaPagoInicio').attr('disabled','disabled').css('background','silver');
			$('#fechaPagoFinal').attr('disabled','disabled').css('background','silver');
		}
		
	});

	$('#imprimir').click(function(e){
		e.preventDefault();
		$('th').css('color:green;');
		$('.ocultar').hide();
		$('.ocultarImpresion').hide();
		$('.mostrarImpresion').show();
		
		$('.tblchildren').show().css('margin','none').css('padding','none');
		$('.filaOculta').hide();
		imprSelec('muestra');
		$('.ocultar').show();
		$('.tblchildren').show();
		$('.ocultarImpresion').show();
		$('.mostrarImpresion').hide();
	});

	lstCategoriaPrincipal.change(function(){
		titulo='Zona Geografica: '+$('#lstCategoriaPrincipal option:selected').text();
		if ($(this).val()) {
			listaZonaCobranza($(this).val());
		}
	});
	lstCategoria.change(function(){
		titulo='Zona Cobranza: '+$('#lstCategoria option:selected').text();
		if ($(this).val()) {
			listaZonasxCategoria($(this).val());
		}
	});
	lstZona.change(function(){
		titulo='Zona: '+$('#lstZona option:selected').text();
	});
	lstvendedor.change(function(){
		titulo='Vendedor: '+$('#lstvendedor option:selected').text();
	});
	$('#lstcobrador').change(function(){
		idcobrador=$(this).val();
		if (idcobrador!="") {
			
			$('#lstCategoriaPrincipal').val('').attr('disabled','disabled');
			$('#lstCategoria').val('').attr('disabled','disabled');
			$('#lstZona').val('').attr('disabled','disabled');
		}else{

			$('#lstCategoriaPrincipal').removeAttr('disabled');
			$('#lstCategoria').removeAttr('disabled');
			$('#lstZona').removeAttr('disabled');
		}
		
	});

	fechaInicio.datepicker({
      changeMonth: true,
      changeYear: true,
      dateFormat: 'yy/mm/dd',
      prevText: '<Ant',
      nextText: 'Sig>',
      //showOn: 'button',
      //clearText: 'Borra',
	  //buttonImage: '/imagenes/calendar.png',
	  //buttonImageOnly: true,
      monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
      dayNamesMin: ['Dom', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
      monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
    });

    fechaFinal.datepicker({
      changeMonth: true,
      changeYear: true,
      dateFormat: 'yy/mm/dd',
      prevText: '<Ant',
      nextText: 'Sig>',
      monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
      dayNamesMin: ['Dom', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
      monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
    });
    $('#fechaPagoInicio').datepicker({
      changeMonth: true,
      changeYear: true,
      dateFormat: 'yy/mm/dd',
      prevText: '<Ant',
      nextText: 'Sig>',
      //showOn: 'button',
      //clearText: 'Borra',
	  //buttonImage: '/imagenes/calendar.png',
	  //buttonImageOnly: true,
      monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
      dayNamesMin: ['Dom', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
      monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
    });

    $('#fechaPagoFinal').datepicker({
      changeMonth: true,
      changeYear: true,
      dateFormat: 'yy/mm/dd',
      prevText: '<Ant',
      nextText: 'Sig>',
      monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
      dayNamesMin: ['Dom', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
      monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
    });

	$('#lstTipoCobro').change(function(){
		if ($(this).val()==3) {
			$('#octava').removeAttr('disabled');
			$('#novena').removeAttr('disabled');
		}else{
			$('#octava').attr('disabled','disabled').removeAttr('checked');
			$('#novena').attr('disabled','disabled').removeAttr('checked');
		}
	});

	$('#octava,#novena').click(function(){
		if ($('#octava').attr('checked')=="checked"  || $('#novena').attr('checked')=="checked") {
			$('#pendiente').removeAttr('checked').attr('disabled','disabled');
			$('#cancelado').removeAttr('checked').attr('disabled','disabled');
			$('#fechaFinal').val('').attr('disabled','disabled');
			$('#fechaInicio').val('').attr('disabled','disabled');
			
		}else{
			$('#pendiente').removeAttr('disabled');
			$('#cancelado').removeAttr('disabled');
			$('#fechaFinal').removeAttr('disabled');
			$('#fechaInicio').removeAttr('disabled');
		}
		
	});
	$('.ocultar').live('click',function(e){
		e.preventDefault();
		padre=$(this).parents('.filaContenedor');
		padre.find('.tblchildren').hide('Blind');
		padre.find('.filaOculta').show('Blind');
	});
	$('.ver').live('click',function(e){
		e.preventDefault();
		
		padre=$(this).parents('.filaContenedor');
		padre.find('.tblchildren').show('Blind');
		padre.find('.filaOculta').hide('Blind');
	});	
	enviar.click(function(e){
		e.preventDefault();
		console.log(lstTipoCobro.val());
		console.log(fechaFinal.val());
		if ($('#pendiente').attr('checked')=="checked") {
			pendiente=1;
		}else{
			pendiente='';
		}
		if ($('#cancelado').attr('checked')=="checked") {
			cancelado=1;
		}else{
			cancelado='';
		}
		if ($('#octava').attr('checked')=="checked") {
			octava=1;
		}else{
			octava='';
		}
		if ($('#novena').attr('checked')=="checked") {
			novena=1;
		}else{
			novena='';
		}
			
		ruta='/reporte/reporteletras';
		$('#tblreportes').css('border','none');
		$('#tblreportes').html('<th style="text-align: center;"><img style="width:250px;heigth:100" src="/imagenes/cargando.gif"></th>');
		
		buscarContenido(ruta,lstZona.val(),lstCategoriaPrincipal.val(),lstCategoria.val(),lstvendedor.val(),lstTipoCobranza.val(),fechaInicio.val(),$('#fechaFinal').val(),lstTipoCobro.val(),titulo,pendiente,cancelado,octava,novena,idcobrador,$('#fechaPagoInicio').val(),$('#fechaPagoFinal').val(),IdCliente.val(),IdOrdenVenta.val());
		
			
		encabezadoReporte();
		
	});
	lstOctavas.change(function(){
		dias=$(this).val();
		fechaFinal.val(hoy());
		fechaInicio.val(antesdehoy(dias));

		
	});
	function esconder(){
		lstCategoriaPrincipal.hide();
		lstCategoria.hide();
		lstZona.hide();
		lstTipoCobranza.hide();
		lstvendedor.hide();
		fechaInicio.hide();
		fechaFinal.hide();
		enviar.hide();
		lstTipoCobro.hide();
		lstOctavas.hide();
	}
	function mostrar(){
		listas.show();
		fechaInicio.show();
		fechaFinal.show();
		enviar.show();
		lstTipoCobranza.show();
		lstTipoCobro.show();
		lstOctavas.show();
	}
	function limpiar(){
		lstCategoriaPrincipal.val('');
		lstCategoria.val('');
		lstZona.val('');
		lstTipoCobranza.val('');
		lstvendedor.val('');
		fechaInicio.val('');
		fechaFinal.val('');
		lstTipoCobro.val('');
		lstOctavas.val('');
	}
	function verificarNulos(){
		if (parametro.val()=="" || fechaInicio.val()=="" || fechaFinal.val()=="" || lstTipoCobro.val()=="") {
			return false;
		}
		else{
			return true;
		}
	}

	$('#btnLimpiar').click(function(){
		$('#txtZonaGeografica').html('');
		$('#txtZonaCobranza').html('');
		$('#txtZona').html('');
		$('#txtTipoCobranza').html('');
		$('#txtVendedor').html('');
		$('#txtTipoCobro').html('');
		$('#txtFechaInicio').html('');
		$('#txtFechaFinal').html('');
		$('#txtOctavas').html('');
		$('#txtNovenas').html('');
		$('#txtPendiente').html('');
		$('#txtCancelado').html('');
		$('#tblreportes').html('');
		idcobrador='';
		$('#lstcobrador').val('').change();
		$('#txtIdCliente').val('');
		$('#txtIdOrdenVenta').val('');
	});

});

function buscarContenido(ruta,idzona,idcategoriaprincipal,idcategoria,idvendedor,idtipocobranza,fechaInicio,fechaFinal,idtipocobro,titulo,pendiente,cancelado,octava,novena,idcobrador,fechaPagoInicio,fechaPagoFinal,IdCliente,IdOrdenVenta){
	//console.log('entro2');
	$.ajax({
		url:ruta,
		type:'post',
		datatype:'html',
		data:{'idzona':idzona,'idcategoriaprincipal':idcategoriaprincipal,'idcategoria':idcategoria,'idvendedor':idvendedor,'idtipocobranza':idtipocobranza,'fechaInicio':fechaInicio,'fechaFinal':fechaFinal,'idtipocobro':idtipocobro,'titulo':titulo,'pendiente':pendiente,'cancelado':cancelado,'octava':octava,'novena':novena,'idcobrador':idcobrador,'fechaPagoFinal':fechaPagoFinal,'fechaPagoInicio':fechaPagoInicio,'IdCliente':IdCliente,'IdOrdenVenta':IdOrdenVenta},
		success:function(resp){
			//console.log(resp);
			//alert('entro3');
			$('#tblreportes').html('');
			$('#tblreportes').html(resp);
			$('#tblreportes').css('border','1px solid');
			//alert('Consulta Finalizada');
		},
		error:function(error){
			//console.log('error');
		},
		complete:function(){
			//console.log('entro final');
		}

	});
}

function hoy()
{
    var fechaActual = new Date();
 
    dia = fechaActual.getDate();
    mes = fechaActual.getMonth() +1;
    anno = fechaActual.getFullYear();
   
 
    if (dia <10) dia = "0" + dia;
    if (mes <10) mes = "0" + mes;  
 
    fechaHoy =  anno+ "/" + mes + "/" + dia;
   
    return fechaHoy;
}
function antesdehoy(dias){
	var fechaActual = new Date();
	nuevafecha=new Date(fechaActual.getTime() - (dias * 24 * 3600 * 1000));

	dia = nuevafecha.getDate();
    mes = nuevafecha.getMonth() +1;
    anno = nuevafecha.getFullYear();
   
 
    if (dia <10) dia = "0" + dia;
    if (mes <10) mes = "0" + mes;  
 
    fechaHoy = anno + "/" + mes + "/" +dia ;
    return fechaHoy;

}
function listaZonaCobranza(idpadrec){
	
	$.ajax({
		url:'/zona/listaCategoriaxPadre',
		type:'post',
		datatype:'html',
		data:{'idpadrec':idpadrec},
		success:function(resp){
			console.log(resp);
			$('#lstCategoria').html(resp);
			
		},
		error:function(error){
			console.log('error');
		},
		complete:function(){

		}

	});
}
function listaZonasxCategoria(idzona){
	
	$.ajax({
		url:'/zona/listaZonasxCategoria',
		type:'post',
		datatype:'html',
		data:{'idzona':idzona},
		success:function(resp){
			console.log(resp);
			$('#lstZona').html(resp);
			
		},
		error:function(error){
			console.log('error');
		},
		complete:function(){

		}

	});
}
function encabezadoReporte(){
	if ($('#lstCategoriaPrincipal').val()!="") {
		$('#txtZonaGeografica').html($('#lstCategoriaPrincipal option:selected').html());
	}else{
		$('#txtZonaGeografica').html('Todo');
	}
	if ($('#lstCategoria').val()!="") {
		$('#txtZonaCobranza').html($('#lstCategoria option:selected').html());
	}else{
		$('#txtZonaCobranza').html('Todo');
	}
	if ($('#lstZona').val()!="") {
		$('#txtZona').html($('#lstZona option:selected').html());
	}else{
		$('#txtZona').html('Todo');
	}
	if ($('#lstTipoCobranza').val()!="") {
		$('#txtTipoCobranza').html($('#lstTipoCobranza option:selected').html());
	}else{
		$('#txtTipoCobranza').html('Todo');
	}
	if ($('#lstvendedor').val()!="") {
		$('#txtVendedor').html($('#lstvendedor option:selected').html());
	}else{
		$('#txtVendedor').html('Todo');
	}
	if ($('#lstcobrador').val()!="") {
		$('#txtCobrador').html($('#lstcobrador option:selected').html());
	}else{
		$('#txtCobrador').html('Todo');
	}
	if ($('#lstTipoCobro').val()!="") {
		$('#txtTipoCobro').html($('#lstTipoCobro option:selected').html());
	}else{
		$('#txtTipoCobro').html('Todo');
	}
	if ($('#fechaInicio').val()!="") {
		$('#txtFechaInicio').html($('#fechaInicio').val());
	}else{
		$('#txtFechaInicio').html('');
	}
	if ($('#fechaFinal').val()!="") {
		$('#txtFechaFinal').html($('#fechaFinal').val());
	}else{
		$('#txtFechaFinal').html('');
	}
	if ($('#octava').attr('checked')=='checked') {
		$('#txtOctavas').html('Octavas');
	}else{
		$('#txtOctavas').html('');
	}
	if ($('#novena').attr('checked')=='checked') {
		$('#txtNovenas').html('Novenas');
	}else{
		$('#txtNovenas').html('');
	}
	if ($('#pendiente').attr('checked')=='checked') {
		$('#txtPendiente').html('Pendiente');
	}else{
		$('#txtPendiente').html('');
	}
	if ($('#cancelado').attr('checked')=='checked') {
		$('#txtCancelado').html('cancelado');
	}else{
		$('#txtCancelado').html('');
	}
	if ($('#txtClientexIdCliente').val()!="") {
		$('#txtCliente').html($('#txtClientexIdCliente').val());
	}else{
		$('#txtCliente').html('');
	}
	if ($('#txtOrdenVentaxId').val()!="") {
		$('#txtOrdenVenta').html($('#txtOrdenVentaxId').val());
	}else{
		$('#txtOrdenVenta').html('');
	}	
}

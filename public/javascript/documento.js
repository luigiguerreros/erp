$(document).on('ready',function(){

	$('#btnCancelar').on('click',function(e){
		e.preventDefault();
		window.location='/documento/listaDocumentos/';
	});

	$("#seleccion").change(function(){
		var id=$("#seleccion option:selected").text();
		var url='/documento/listaDocumentos/'+id;
		window.location=url;
	});
});
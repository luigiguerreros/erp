$(document).ready(function(){
	
	$('#btnImprimir').click(function(e){
		e.preventDefault();
		$('listados').append('<link rel="stylesheet" href="/css/normalize.css">');
		imprSelec('listados');

	});

        $('#txtproducto').attr('required','required');
	$('#btnCargaKardex').click(function(e){
//		if($('#txtproducto').val()==""){
//			alert("Ingrese el Producto");
//			
//		}else{
			var idProducto=$('#idprod').val();
                     	var periodo=$('#periodo').val();
			
			var mesInicial=$('#mesInicial').val();
			var mesFinal=$('#mesFinal').val();
			var sunat=0;
			if ($('#sunat').attr('checked')=="checked") {
				sunat=1;
			}
			
			console.log(sunat);
			
			var txtProductolabel=$('#txtDescripcion').val();
			$('#labelProducto').html(txtProductolabel);
			$('#labelCodigo').html($('#txtproducto').val());
			$('#labelPeriodo').html($('#mesInicial option:selected').html()+' '+periodo );
			
			$('#labelalmacen').html('AlmacÃ©n General');
			$('#labelMetodo').html('Promedio Movil');
			$('#labelTipo').html('Mercaderias');
			cargaKardexValorizadoxProductoFecha(idProducto,periodo,mesInicial,mesFinal,sunat);
//		}
		


		
	});


});

function cargaKardexValorizadoxProductoFecha(idProducto,periodo,mesInicial,mesFinal,sunat){
	var url="/movimiento/kardexValorizadoxProducto/";
	$.post(url,{idproducto:idProducto,ano:periodo,mesInicial:mesInicial,mesFinal:mesFinal,sunat:sunat }, function(data){
		 
		$('#listados').html(data);
	});
}


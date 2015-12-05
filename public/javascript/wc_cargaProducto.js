$(document).ready(function(){

		
	$('#producto').click(function(e){
            
            
//                $.ajax({
//                    url:'/almacen/buscaAlmacen',
//                    type:'post',
//                    dataType:'json',
//                    data:{'idalmacen':idalmacen},
//                    success:function(resp){
//                            $('#labelRazonSocial').html(resp.razsocalm);
//                            $('#labelRuc').html(resp.rucalm);
//                    }
//               });
		
	});

	

});

//Funcion que permite cargar la imagen de un producto.
function cargarImagenes(imagen){
	$('#verImagenes').show();
	$('.img').attr('src',imagen);
}

function cargarAlmacen(idalmacen){
	$.ajax({
		url:'/almacen/buscaAlmacen',
		type:'post',
		dataType:'json',
		data:{'idalmacen':idalmacen},
		success:function(resp){
			$('#labelRazonSocial').html(resp.razsocalm);
			$('#labelRuc').html(resp.rucalm);
		}
	});
}
$(document).on('ready', function(){
	var msgboxTitleProveedor = 'Mantenimiento de Proveedor';
	$('#frmProveedorNuevo, #frmProveedorActualizar').validate({
		invalidHandler: function(form, validator) {
		   var errors = validator.numberOfInvalids();
		   if (errors) {
		        $.msgbox(msgboxTitle, 'Ingrese todos los datos requeridos correctamente');
		     }
		},
		errorElement:'span'
	});
/*cancelar registro*/
	$('#btnCancelar').on('click', function(e){
		e.preventDefault();
		window.location = '/proveedor/listado';
	});
/*Boton de eliminacion*/
	$('.btnEliminar').on('click', function(e){
		e.preventDefault();
		var url = $(this).attr('href');
		$.msgbox(msgboxTitleProveedor, '¿Esta seguro de elimiar el registro?');
		$('#msgbox-ok').click(function(){
			window.location = url;
		});
	});
});
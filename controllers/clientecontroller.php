<?php
class ClienteController extends ApplicationGeneral{
	
	/**
	 * Vista Inicial de Clientes:
	 */
	
	public function index(){
		
	}
	function lista(){
		
		$cliente=$this->AutoLoadModel('cliente');
		$zona=$this->AutoLoadModel('zona');
                
		if (empty($_REQUEST['id'])) {
				$_REQUEST['id']=1;
		}
			
			session_start();
			$_SESSION['P_Cliente']="";
			
			$data['Cliente']=$cliente->listaClientesPaginado($_REQUEST['id']);
			for($i=0;$i<count($data['Cliente']);$i++){
				if($data['Cliente'][$i]['zona']!='' && $data['Cliente'][$i]['zona']!=0){
					$data['Cliente'][$i]['zona']=$zona->nombrexid($data['Cliente'][$i]['zona']);
				}
			}
			
			$paginacion=$cliente->paginadoClientes();
			$data['paginacion']=$paginacion;
			$data['blockpaginas']=round($paginacion/10);
			$this->view->show("/cliente/lista.phtml",$data);
	}

	function busca(){
		$cliente=$this->AutoLoadModel('cliente');
		$zona=$this->AutoLoadModel('zona');
		if (empty($_REQUEST['id'])) {
			$_REQUEST['id']=1;
		}
		session_start();
		$_SESSION['P_Cliente'];
		if (!empty($_REQUEST['txtBusqueda'])) {
			$_SESSION['P_Cliente']=$_REQUEST['txtBusqueda'];
		}
		$parametro=$_SESSION['P_Cliente'];
		$paginacion=$cliente->paginadoClientesxnombre($parametro);
		$data['retorno']=$parametro;
		$data['Cliente']=$cliente->listaClientesPaginadoxnombre($_REQUEST['id'],$parametro);
		for($i=0;$i<count($data['Cliente']);$i++){
				if($data['Cliente'][$i]['zona']!='' && $data['Cliente'][$i]['zona']!=0){
					$data['Cliente'][$i]['zona']=$zona->nombrexid($data['Cliente'][$i]['zona']);

				}
			}
		$data['paginacion']=$paginacion;
                $data['blockpaginas']=round($paginacion/10);
		$data['totregistros']=count($cliente->buscaxnombre($parametro));
		
		$this->view->show("/cliente/busca.phtml",$data);
	}
	/**
	 * ACCIONES MASIVAS PARA CLIENTES:
	 */
	
	/**
	 * GeneraCodigos	:	Crea Codigos a todos los clientes en funcion a la cantidad que existen.	
	 *
	 */
	function Codigos(){
		$ObjCliente=New Cliente();
		$ObjCliente->GeneraCodigoTodos();
	}
	
	/**
	 * Nuevo: Muestra el formulario pra crear nuevos clientes.
	 *
	 */
	function nuevo(){
		$departamento=new Departamento();
		$transporte=new Transporte();
		$zona=$this->AutoLoadModel('zona');
		$vendedor=$this->AutoLoadModel('actor');
		$datos['Departamento']=$departamento->listado();
		$datos['Transporte']=$transporte->listaTodo();
		$datos['TipoCliente']=$this->tipoCliente();
		$datos['Zona']=$zona->listado();
		$datos['Vendedor']=$vendedor->listadoVendedoresTodos();
		$this->view->show("cliente/nuevo.phtml",$datos);
	}
	
	function graba(){
		if (!empty($_SERVER['HTTP_REFERER'])|| $_SESSION['nivelacceso']==1) {
			
		
		$dataCliente = $_REQUEST['Cliente'];
		$idTransporte=$_REQUEST['idTransporte'];
		$dataTransporte = $_REQUEST['Transporte'];
		if (empty($dataCliente['razonsocial'])) {
			$dataCliente['razonsocial']=$dataCliente['nombrecli'].' '.$dataCliente['apellido1'].' '.$dataCliente['apellido2'];
		}

		$dataCliente['estado']=1;
		$cliente = new Cliente();
		$clienteTransporte=new ClienteTransporte();
		$clienteZona=$this->AutoLoadModel('clientezona');
		//$clienteSucursal=$this->AutoLoadModel('clienteSucursal');
		$clienteVendedor=$this->AutoLoadModel('clientevendedor');

		$idCliente=$cliente->grabaCliente($dataCliente);

		$dataClienteZona['idcliente']=$idCliente;
		$dataClienteZona['idzona']=$dataCliente['zona'];

		$dataClienteZona['idcliente']=$idCliente;
		$dataClienteZona['direccion_fiscal']=$dataCliente['direccion'];
		$dataClienteZona['direccion_despacho_contacto']=$dataCliente['direccion_despacho_cliente'];
		$dataClienteZona['nomcontacto']=$dataCliente['nombre_contacto'];
		$dataClienteZona['nombresucursal']=$dataCliente['nombre_contacto'];

		$dataClienteVendedor['idvendedor']=$_REQUEST['valorVendedor'];
		$dataClienteVendedor['idcliente']=$idCliente;

		if($idCliente){
			$cliente->GeneraCodigoNuevo($idCliente);
			//$exito4=$clienteSucursal->grabaClienteSucursal($dataClienteSucursal);
			$exito3=$clienteZona->grabaCliente($dataClienteZona);
			$exito2=$clienteTransporte->grabaClienteTransporte(array("idcliente"=>$idCliente,"idtransporte"=>$idTransporte));
			$exito5=$clienteVendedor->grabaClienteVendedor($dataClienteVendedor);

			if($exito2 && $exito3 && $exito5){
				$ruta['ruta'] = "/cliente/lista/";
				$this->view->show("ruteador.phtml", $ruta);
			}
		}
		}else{
			echo "no tiene acceso";
		}
	}
	
	function agregaTransporte(){
		$idCliente=$_REQUEST['id'];
		$dataTransporte=$_REQUEST['Transporte'];
		$transporte=new Transporte();
		$clienteTransporte=new ClienteTransporte();
		if($dataTransporte['idtransporte']==""){
			$dataTransporte['estado']=1;
			$idTransporte=$transporte->grabar($dataTransporte);	
		}else{
			$idTransporte=$dataTransporte['idtransporte'];
		}
		if($idTransporte and $idCliente){
			$exito=$clienteTransporte->grabaClienteTransporte(array("idcliente"=>$idCliente,"idtransporte"=>$idTransporte,"estado"=>1));
			if($exito){
				$listadoTransporte=$transporte->buscarxCliente($idCliente);
				echo '<option value="">'."-- Transportes --";
				for($i=0;$i<count($listadoTransporte);$i++){
					if($listadoTransporte[$i]['idtransporte'] == $idTransporte){
						echo '<option value="'.$listadoTransporte[$i]['idclientetransporte'].'" selected>'.$listadoTransporte[$i]['trazonsocial'];
					}else{
						echo '<option value="'.$listadoTransporte[$i]['idclientetransporte'].'">'.$listadoTransporte[$i]['trazonsocial'];
					}
				}
			}
		}else{
			$data=$transporte->listatodo();
			echo '<option value="">'."-- Transportes --";
			for($i=0;$i<count($data);$i++){
				if($data[$i]['idtransporte'] == $idTransporte){
					echo '<option value="'.$data[$i]['idtransporte'].'" selected>'.$data[$i]['trazonsocial'];	
				}else{
					echo '<option value="'.$data[$i]['idtransporte'].'">'.$data[$i]['trazonsocial'];
				}
			}
		}
	}
	
	function editar(){
		//if ($_SESSION['nivelacceso']==1) {
		
		$id = $_REQUEST['id'];
		$cliente=new Cliente();
		$distrito=new Distrito();
		$provincia=new Provincia();
		$departamento=new Departamento();
		$transporte=new Transporte();
		$clientezona=$this->AutoLoadModel('clientezona');
		$zona=$this->AutoLoadModel('zona');
		$clienteTransporte=$this->AutoLoadModel('clientetransporte');
		$clienteVendedor=$this->AutoLoadModel('clientevendedor');
		$vendedor=$this->AutoLoadModel('actor');

		$dataCliente=$cliente->buscaCliente($id);
		$dataDistrito=$distrito->buscarxid($dataCliente[0]['iddistrito']);
		$data['Departamento']=$departamento->listado();
		$data['Provincia']=$provincia->listado($dataDistrito[0]['codigodepto']);
		$data['Distrito']=$distrito->listado($dataDistrito[0]['idprovincia']);
		$data['Cliente'] = $cliente->buscaCliente($id);
		$data['ClienteTransporte']=$transporte->buscarxCliente($id);
		$data['TipoCliente']=$this->tipoCliente();
		$data['Transporte']=$transporte->listaTodo();
		$data['Zona']=$zona->listado();
		$data['Sucursal']=$clientezona->listaxidcliente($id);
		$data['Vendedor']=$vendedor->listadoVendedoresTodos();
		$data['clienteVendedor']=$clienteVendedor->buscarxid($id);

		$this->view->show("cliente/editar.phtml", $data);
			
		//}else{
		//	echo "no tiene acceso";
		//}
	}
	
	
	function actualiza(){
		
		if (!empty($_SERVER['HTTP_REFERER']) || $_SESSION['nivelacceso']==1) {
		$dataCliente = $_REQUEST['Cliente'];
		$idClienteTransporte=$_REQUEST['idclientetransporte'];
		$idcliente=$_REQUEST['idCliente'];

		
		//$dataTransporte['idcliente']=$_REQUEST['idCliente'];
		$idTransporte=$_REQUEST['idtransporte'];
		$dataTransporte['idtransporte']=$idTransporte;
		$cliente = new Cliente();
		$clientetransporte= new ClienteTransporte();
		//$clienteZona=$this->AutoLoadModel('clientezona');
		
		$clienteVendedor=$this->AutoLoadModel('clientevendedor');
		if ($dataCliente['tipocliente']==1) {
			$dataCliente['razonsocial']=$dataCliente['nombrecli'].' '.$dataCliente['apellido1'].' '.$dataCliente['apellido2'];
			$dataCliente['nombrecomercial']=$dataCliente['nombrecli'].' '.$dataCliente['apellido1'].' '.$dataCliente['apellido2'];
		}

		$exito1 = $cliente->actualizaCliente($dataCliente,"idcliente=".$idcliente);
		
		$dataClienteZona['idzona']=$dataCliente['zona'];

		
		$dataClienteSucursal=$_REQUEST['Sucursal'];

		$dataClienteVendedor['idvendedor']=$_REQUEST['valorVendedor'];
		//echo $dataClienteVendedor.' '.$idcliente;
		//exit;
		
		if($exito1){
			
			//$exito3=$clienteZona->actualizaCliente($dataClienteZona,"idcliente=".$idcliente);
			//buscamos si tiene un vendedor asiganado 
			$dataBusqueda=$clienteVendedor->buscarxid($idcliente);
			if (count($dataBusqueda)>0) {
				$exito5=$clienteVendedor->actualizaClienteVendedor($idcliente,$dataClienteVendedor);
			}else{
				$dataClienteVendedor['idcliente']=$idcliente;
				$dataClienteVendedor['estado']=1;
				$exito5=$clienteVendedor->grabaClienteVendedor($dataClienteVendedor);
			}
			

			if (!empty($idClienteTransporte) && !empty($idTransporte)) {
				
				$exito2 = $clientetransporte->actualizaClienteTransporte($idClienteTransporte,$dataTransporte);
				if($exito2 && $exito5){
					$ruta['ruta'] = "/cliente/lista/";
					$this->view->show("ruteador.phtml", $ruta);	
				}
			}else{
				if ($exito5) {
					$ruta['ruta'] = "/cliente/lista/";
					$this->view->show("ruteador.phtml", $ruta);
				}
					
			}
			
		}
		}else{
			echo "no tiene acceso";
		}
	}
	
	function eliminar(){
		$id=$_REQUEST['id'];
		$cliente=new Cliente();
		$estado=$cliente->cambiaEstadoCliente($id);
		if($estado){
			$ruta['ruta']="/cliente/lista";
			$this->view->show("ruteador.phtml",$ruta);
		}
	}
	
	function provincia(){
		$id=$_REQUEST['lstDepartamento'];
		$provincia=new Cliente();
		$data['Provincia']=$provincia->listadoProvincia($id);
	}
	function autocomplete(){
		$id=$_REQUEST['id'];
		$cliente=new Cliente();
		$data=$cliente->autocomplete($id);
		echo json_encode($data);
	}
	function autocomplete2(){
		$id=$_REQUEST['term'];
		$cliente=new Cliente();
		$data=$cliente->buscaAutocomplete($id);
		echo json_encode($data);
	}
	function autocompleteClienteZona(){
		$id=$_REQUEST['term'];
		$cliente=new Cliente();
		$data=$cliente->buscaAutocompleteClienteZona($id);
		echo json_encode($data);
	}
	function autocompleteConSucursal(){
		$id=$_REQUEST['term'];
		$cliente=new Cliente();
		$data=$cliente->buscaAutocompleteConSucursal($id);
		echo json_encode($data);
	}
	function buscar(){
		$cli=New Cliente();
		$datos=$cli->listadoClientes();
		$objeto=$this->formatearparakui($datos);
		header("Content-type: application/json");
		//echo "{\"data\":" .json_encode($objeto). "}";
		echo json_encode($objeto);
	}
	function datosclientexid(){
		$id=$_REQUEST['idcliente'];
		$cliente=new Cliente();
		$datos=$cliente->buscacliente($id);
		echo "<ul class='inline-block'>";
		echo "<li><label>Nombres/RazonSocial:</label> ".$datos[0]['nombrecli']." ".$datos[0]['apellido1']." ".$datos[0]['apellido2']." ".$datos[0]['razonsocial']."</li>";
		echo "<li><label>RUC:</label> ".$datos[0]['ruc']."</li>";
		echo "<li><label>Direccion:</label> ".$datos[0]['direccion']."</li>";
		echo "<li><label>Antiguo Codigo:</label> ".$datos[0]['codantiguo']."</li>";
		echo "</ul>";
	}
	function buscarclientezona(){
		$clienteZona= new ClienteZona();
		$cliente=new Cliente();
		$zona=new Zona();
		$datos=$clienteZona->listadoclientezona();
		/*$total=count($datos);
		for($i=0;$i<$total;$i++){
			$datos[$i]['nombrecli']=$cliente->nombrexid($datos[$i]['idcliente']);
			$datos[$i]['nombrezona']=$zona->nombrexid($datos[$i]['idzona']);
		}*/
		$objeto=$this->formatearparakui($datos);
		header("Content-type: application/json");
		//echo "{\"data\":" .json_encode($objeto). "}";
		echo json_encode($objeto);
	}
        function validarCodigo(){
		$cliente=$this->AutoLoadModel('cliente');
			$condicion=$_REQUEST['codigo'];
			$cantidad=$cliente->verificaCodigo($condicion);
			if (empty($condicion) || $condicion==0) {
				$data['error']='No Ingrese valores nulos';
				$data['verificado']=false;
				echo json_encode($data);
			}else if ($cantidad>0) {
				$data['error']='El codigo ya existe';
				$data['verificado']=false;
				echo json_encode($data);
			}else{
				$data['error']='Codigo Aceptado';
				$data['verificado']=true;
				echo json_encode($data);
			}
	}

	function historialcrediticia(){
		$this->view->show("/cliente/posicioncrediticia.phtml");
	}

	function detalleposicion(){
		$idCliente=$_REQUEST['id'];
		$cliente=New Cliente();
		$dataPosicionCliente=$cliente->detalleposicion($idCliente);
		$tamanio=count($dataPosicionCliente);
		echo "<tr>";
			echo "<th>Nro:</th>";
			echo "<th>Linea de Crédito:</th>";
			echo "<th>Saldo disponible:</th>";
			echo "<th>Calificación crediticia:</th>";
			echo "<th>Observaciones:</th>";
			echo "<th>Estado:</th>";
		echo "</tr>";
		for ($i=0; $i < $tamanio; $i++) {
			$situacion=($dataPosicionCliente[$i]['estado']==1)?"ACTIVO":"Historico";
			echo "<tr>";
			echo "<td>".($i+1)."</td>";
			echo "<td>".number_format($dataPosicionCliente[$i]['lineacredito'],2)."</td>";
			echo "<td>".number_format($dataPosicionCliente[$i]['saldo'],2)."</td>";
			switch ($dataPosicionCliente[$i]['calificacion']) {
				case '1': $formacobro="Cliente A1"; break;
				case '2': $formacobro="Buen cliente"; break;
				case '3': $formacobro="Cliente en Observación"; break;
				case '4': $formacobro="Cliente moroso"; break;
				case '5': $formacobro="Cliente incobrable"; break;												
			}
			echo "<td>".$formacobro."</td>";
			echo "<td>".$dataPosicionCliente[$i]['observacion']."</td>";
			if($situacion=="ACTIVO"){
				echo "<th>".$situacion."</th>";
				echo "<input type=\"hidden\" id=\"lineacreditoactiva\" value=\"".number_format($dataPosicionCliente[$i]['lineacredito'],2)."\">";
				echo "<input type=\"hidden\" id=\"idposicioncliente\" value=\"".$dataPosicionCliente[$i]['idposicioncliente']."\">";
				echo "<input type=\"hidden\" id=\"saldoactivo\" value=\"".number_format($dataPosicionCliente[$i]['saldo'],2)."\">";
			}else{
				echo "<td>".$situacion."</td>";
			}
			
			echo "</tr>";
		}
	}

	function posicionordenventa()
	{
		$idCliente=$_REQUEST['id'];
		$cliente=New Cliente();
		$dataPosicionCliente=$cliente->detalleposicionactivo($idCliente);
			switch ($dataPosicionCliente[0]['calificacion']) {
				case '1': $formacobro="Cliente A1"; break;
				case '2': $formacobro="Buen cliente"; break;
				case '3': $formacobro="Cliente en Observación"; break;
				case '4': $formacobro="Cliente moroso"; break;
				case '5': $formacobro="Cliente incobrable"; break;												
			}
		echo " <label>Calificación: </label><input type=\"text\" readonly disabled value=\"".strtoupper($formacobro)."\">";
		echo " <label>Linea de crédito: </label><input type=\"text\" readonly disabled value=\"".number_format($dataPosicionCliente[0]['lineacredito'],2)."\">";
		echo " <label>Saldo disponible: </label><input type=\"text\" id=\"idsaldo\" readonly disabled value=\"".number_format($dataPosicionCliente[0]['saldo'],2)."\">";
	}


	function datosdeudaOrdenVentas(){
		$idCliente=$_REQUEST['id'];
		$cliente=New Cliente();
		$deuda=$cliente->deudaOrdenVenta($idCliente);
		$ultimadeuda=$deuda[0];
		echo " <label>Ultima Orden: </label><input type=\"text\" readonly disabled value=\"".$ultimadeuda['codigov']."\">";
		echo " <label>Monto Ultima Orden: </label><input type=\"text\" readonly disabled value=\"".number_format($ultimadeuda['importeordencobro'],2)."\">";
		echo " <label>Deuda Ultima Orden: </label><input type=\"text\" readonly disabled value=\"".number_format($ultimadeuda['saldo'],2)."\">";
	}

	function datosdeudaTotalOrdenVentas(){
		$idCliente=$_REQUEST['id'];
		$cliente=New Cliente();
		$deuda=$cliente->deudaOrdenVenta($idCliente);
		$total=count($deuda);
		for ($i=0; $i < $total; $i++) { 
			$deudatotal+=$deuda[$i]['saldo'];
		}
		echo " <label>Deuda Total: </label><input type=\"text\" class=\"important\"  id=\"deudatotal\" readonly disabled value=\"".number_format($deudatotal,2)."\">";
	}



	function registrarposicion(){
		$idCliente=$_REQUEST['idcliente'];
		$cliente=$_REQUEST['Cliente'];
		$cliente['idcliente']=$idCliente;
		$ClientePosicion=New ClientePosicion();
		$dataActual=$ClientePosicion->datosPosicion($idCliente);
		$cliente['saldo']=$dataActual[0]['saldo']+($cliente['lineacredito']-$dataActual[0]['lineacredito']);
		$actualiza=$ClientePosicion->actualizaPosicion($idCliente);
		$exito=$ClientePosicion->grabaPosicion($cliente);
		$ruta['ruta'] = "/cliente/historialcrediticia/";
		$this->view->show("ruteador.phtml", $ruta);
	}

	function vistaGlobal()
	{
		if($_REQUEST['idcliente']){
			$id=$_REQUEST['idcliente'];
			$ordenventa=new OrdenVenta();
			$data['data']=$ordenventa->listaOrdenVentaxIdCliente($id);
			$data['nroOrdenes']=count($data['data']);
		}else{
			$data['data']="";
		}
		$this->view->show("/cliente/vistaglobal.phtml",$data);
	}

	function clientevistaglobal(){
		if($_REQUEST['idcliente']){
			$id=$_REQUEST['idcliente'];
			$ordenventa=new OrdenVenta();
			$data['data']=$ordenventa->listaOrdenVentaxIdCliente($id);
			$data['nroOrdenes']=count($data['data']);
		}else{
			$data['data']="";
		}
		$this->view->show("/cliente/clientevistaglobal.phtml",$data);
	}

	function cargaSucursales(){
		$cliente=New Cliente();
		$sucursal=$this->AutoLoadModel('ClienteSucursal');
		$dataCliente=$cliente->consultaClientes();
		$TotalClientes=count($dataCliente);
		for($i=0;$i<$TotalClientes;$i++){
			$dataClienteSucursal['idcliente']=$dataCliente[$i]['idcliente'];
			$dataClienteSucursal['tipooficina']=$dataClienteSucursal['estado']=1;
			$dataClienteSucursal['direccion']=html_entity_decode($dataCliente[$i]['direccion'],ENT_QUOTES,'UTF-8');
			$dataClienteSucursal['distrito']=$dataCliente[$i]['iddistrito'];
			$exito=$sucursal->grabaClienteSucursal($dataClienteSucursal);
		}
	}

	function cargaTransportePower(){
		$cliente=New Cliente();
		$sucursal=$this->AutoLoadModel('ClienteTransporte');
		$dataCliente=$cliente->consultaClientes();
		$TotalClientes=count($dataCliente);
		for($i=0;$i<$TotalClientes;$i++){
			$dataClienteTransporte['idcliente']=$dataCliente[$i]['idcliente'];
			$dataClienteTransporte['estado']=1;
			$dataClienteTransporte['idtransporte']=35;
			$exito=$sucursal->grabaClienteTransporte($dataClienteTransporte);
		}
	}
	function direccion_despacho(){
		$idcliente=$_REQUEST['idcliente'];
		//echo $idcliente;
		$clientezona=$this->AutoLoadModel('clientezona');
		$dataClienteZona=$clientezona->buscaCliente($idcliente);
		$cantidad=count($dataClienteZona);
		$dato="<option value=''>Direcciones Despacho</option>";
		for ($i=0; $i <$cantidad ; $i++) { 
			$dato.="<option value='".$dataClienteZona[$i]['idclientezona']."'>".(html_entity_decode($dataClienteZona[$i]['direccion_despacho_contacto'],ENT_QUOTES,'UTF-8'))."</option>";
		}
		echo $dato;
	}	
	function direccion_fiscal(){
		$idcliente=$_REQUEST['idcliente'];
		//echo $idcliente;
		$clientezona=$this->AutoLoadModel('clientezona');
		$dataClienteZona=$clientezona->buscaCliente($idcliente);
		$cantidad=count($dataClienteZona);
		$dato="<option value=''>Direcciones </option>";
		for ($i=0; $i <$cantidad ; $i++) { 
			$dato.="<option value='".$dataClienteZona[$i]['idclientezona']."'>".(html_entity_decode($dataClienteZona[$i]['direccion_fiscal'],ENT_QUOTES,'UTF-8'))."</option>";
		}
		echo $dato;
	}	
	function contactos(){
		$idcliente=$_REQUEST['idcliente'];
		//echo $idcliente;
		$clientezona=$this->AutoLoadModel('clientezona');
		$dataClienteZona=$clientezona->buscaCliente($idcliente);
		$cantidad=count($dataClienteZona);
		$dato="<option value=''>Contactos</option>";
		for ($i=0; $i <$cantidad ; $i++) { 
			$dato.="<option value='".$dataClienteZona[$i]['idclientezona']."'>".$dataClienteZona[$i]['nomcontacto']."</option>";
		}
		echo $dato;
	}

	function cargaSucursal(){
		$idclientezona=$_REQUEST['idclientesucursal'];
		$clientezona=$this->AutoLoadModel('clientezona');
		$dataSucursal=$clientezona->buscaClienteZona($idclientezona);
		$dataSucursal[0]['nombresucursal']=html_entity_decode($dataSucursal[0]['nombresucursal'],ENT_QUOTES,'UTF-8');
		$dataSucursal[0]['nomcontacto']=html_entity_decode($dataSucursal[0]['nomcontacto'],ENT_QUOTES,'UTF-8');
		$dataSucursal[0]['direccion_fiscal']=html_entity_decode($dataSucursal[0]['direccion_fiscal'],ENT_QUOTES,'UTF-8');
		$dataSucursal[0]['direccion_despacho_contacto']=html_entity_decode($dataSucursal[0]['direccion_despacho_contacto'],ENT_QUOTES,'UTF-8');
		$dataSucursal[0]['nombrecontactodespacho']=html_entity_decode($dataSucursal[0]['nombrecontactodespacho'],ENT_QUOTES,'UTF-8');

		echo json_encode($dataSucursal[0]);
	}
	function grabarSucursal(){
		$clientezona=$this->AutoLoadModel('clientezona');
		$data['nombresucursal']=$_REQUEST['nombresucursal'];
		$data['nomcontacto']=$_REQUEST['nomcontacto'];
		$data['dnicontacto']=$_REQUEST['dnicontacto'];
		$data['telcontac']=$_REQUEST['telcontac'];
		$data['movilcontac']=$_REQUEST['movilcontac'];
		$data['direccion_fiscal']=$_REQUEST['direccion_fiscal'];
		$data['direccion_despacho_contacto']=$_REQUEST['direccion_despacho_contacto'];
		$data['horarioatencion']=$_REQUEST['horarioatencion'];
		$data['nombrecontactodespacho']=$_REQUEST['nombrecontactodespacho'];
		$data['dnidespacho']=$_REQUEST['dnidespacho'];
		$data['telcontacdespacho']=$_REQUEST['telcontacdespacho'];
		$data['movilcontacdespacho']=$_REQUEST['movilcontacdespacho'];
		$data['horarioatenciondespacho']=$_REQUEST['horarioatenciondespacho'];
		$data['idcliente']=$_REQUEST['idCliente'];
		$data['idzona']=$_REQUEST['idzona'];
		$data['tipooficina']=1;
		$exito=$clientezona->grabaCliente($data);
		if ($exito) {
			$dataRespuesta['idsucursal']=$exito;
			$dataRespuesta['validacion']=true;
		}else{
			$dataRespuesta['validacion']=false;
		}
		echo json_encode($dataRespuesta);

	}

	function actualizarSucursal(){
		$clientezona=$this->AutoLoadModel('clientezona');
		$id=$_REQUEST['id'];
		$data['nombresucursal']=$_REQUEST['nombresucursal'];
		$data['nomcontacto']=$_REQUEST['nomcontacto'];
		$data['dnicontacto']=$_REQUEST['dnicontacto'];
		$data['telcontac']=$_REQUEST['telcontac'];
		$data['movilcontac']=$_REQUEST['movilcontac'];
		$data['direccion_fiscal']=$_REQUEST['direccion_fiscal'];
		$data['direccion_despacho_contacto']=$_REQUEST['direccion_despacho_contacto'];
		$data['horarioatencion']=$_REQUEST['horarioatencion'];
		$data['nombrecontactodespacho']=$_REQUEST['nombrecontactodespacho'];
		$data['dnidespacho']=$_REQUEST['dnidespacho'];
		$data['telcontacdespacho']=$_REQUEST['telcontacdespacho'];
		$data['movilcontacdespacho']=$_REQUEST['movilcontacdespacho'];
		$data['horarioatenciondespacho']=$_REQUEST['horarioatenciondespacho'];
		$data['idcliente']=$_REQUEST['idCliente'];
		$data['idzona']=$_REQUEST['idzona'];
		$data['tipooficina']=1;
		$filtro="idclientezona='$id'";
		$exito=$clientezona->actualizaCliente($data,$filtro);
		if ($exito) {
			$dataRespuesta['idsucursal']=$exito;
			$dataRespuesta['nombresucursal']=$_REQUEST['nombresucursal'];
			$dataRespuesta['direccion_fiscal']=$_REQUEST['direccion_fiscal'];
			$dataRespuesta['validacion']=true;
		}else{
			$dataRespuesta['validacion']=false;
		}
		echo json_encode($dataRespuesta);
	}

	function eliminarSucursal(){
		$id=$_REQUEST['idclientesucursal'];

		$clientezona=$this->AutoLoadModel('clientezona');

		$exito=$clientezona->cambiaEstadoClienteZona($id);
		if ($exito) {
			$dataRespuesta['validacion']=true;
		}else{
			$dataRespuesta['validacion']=false;
		}
		echo json_encode($dataRespuesta);
	}

	function llenarZonasInicial(){
		$cliente=$this->AutoLoadModel('cliente');
		$clientezona=$this->AutoLoadModel('clientezona');
		
	}
	function  bucaZonasxCliente(){
		$idCliente=$_REQUEST['idCliente'];
		$clienteZona=$this->AutoLoadModel('clientezona');
		$dataClienteZona=$clienteZona->buscaCliente($idCliente);
		$cantidad=count($dataClienteZona);
		for ($i=0; $i <$cantidad ; $i++) {
			$dato.="<option value='".$dataClienteZona[$i]['idclientezona']."'>".(html_entity_decode($dataClienteZona[$i]['nombresucursal'],ENT_QUOTES,'UTF-8'))."</option>";
		}
		echo $dato;
	}	
}
?>
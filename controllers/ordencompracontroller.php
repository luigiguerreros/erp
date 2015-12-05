<?php
	class OrdencompraController extends ApplicationGeneral{
		function listar(){
			$ordenCompra=new Ordencompra();
			$data['Ordencompra']=$ordenCompra->listadoOrdenescompra();
			$this->view->show("/ordencompra/listar.phtml",$data);
		}
		function listarOptions(){
			$ordenCompra=new Ordencompra();
			$data=$ordenCompra->listadoOrdenecompraNoRegistrado();
			for($i=0;$i<count($data);$i++){
				$codigo="0000".$data[$i]['idordencompra'];
				echo '<option value="'.$data[$i]['idordencompra'].'">'.substr($codigo,strlen($codigo)-5).'</option>';
			}
		}
		function graba(){
			$dataOrdenCompra=$_REQUEST['Ordencompra'];
			$dataDetalleOrdenCompra=$_REQUEST['Detalleordencompra'];
			$ordenCompra=new Ordencompra();
			$detalleOrdenCompra=new Detalleordencompra();
			$producto = new Producto();
			$dataOrdenCompra['estado']=1;
			$exito1=$ordenCompra->grabaOrdenCompra($dataOrdenCompra);
			if($exito1){
				$codigooc=strtoupper($ordenCompra->generaCodigo());
				$dataOrden['codigooc']=$codigooc;
				$actualiza=$ordenCompra->actualizaOrdenCompra($dataOrden,$exito1);
				foreach ($dataDetalleOrdenCompra as $data){;
					$data['idordencompra']=$exito1;
					$data['cantidadrecibidaoc']=$data['cantidadsolicitadaoc'];
					$dataProducto = $producto->buscaProducto($data['idproducto']);
					$stockDisponible['stockdisponible'] = $dataProducto[0]['stockdisponible']+$data['cantidadsolicitadaoc'];
					$exito2=$detalleOrdenCompra->grabaDetalleOrdenCompra($data);
					$exito3=$producto->actualizaProducto($stockDisponible,$data['idproducto']);
				}
				if($exito2 and $exito3){
					$ruta['ruta']="/ordencompra/vistaRespuesta/".$codigooc;
					$this->view->show("ruteador.phtml",$ruta);
				}
			}
		}
		function editar(){
			$id=$_REQUEST['id'];
			if (!empty($_REQUEST['id']) && $_REQUEST['id']>0) {
				$ordenCompra=new Ordencompra();
				$detalleOrdenCompra=new Detalleordencompra();
				$almacen=new Almacen();
				$proveedor=new Proveedor();
				$rutaImagen=$this->rutaImagenesProducto();
				$data['Ordencompra']=$ordenCompra->editaOrdenCompra($id);
				$data['Detalleordencompra']=$detalleOrdenCompra->listaDetalleOrdenCompra($id);
				$data['Empresa']=$almacen->listadoAlmacen();
				$data['RutaImagen']=$rutaImagen;
				$data['Proveedor']=$proveedor->listadoProveedores();
				$this->view->show("/ordencompra/editar.phtml",$data);
			}else{
				$ruta['ruta']="/importaciones/ordencompra";
				$this->view->show("ruteador.phtml",$ruta);
			}
		}
		function valorizarOrden(){
			$id=$_REQUEST['id'];
			if (!empty($_REQUEST['id']) && $_REQUEST['id']>0) {
				
			
				$ordenCompra=new Ordencompra();
				$detalleOrdenCompra=new Detalleordencompra();
				$almacen=new Almacen();
				$proveedor=new Proveedor();
				$empresa=$this->AutoLoadModel('empresa');
				$rutaImagen=$this->rutaImagenesProducto();
				$data['Ordencompra']=$ordenCompra->editaOrdenCompra($id);
				$data['Detalleordencompra']=$detalleOrdenCompra->listaDetalleOrdenCompra($id);
				/*echo '<pre>';
				print_r($data['Ordencompra']);
				exit;*/
				$data['Empresa']=$almacen->listadoAlmacen();
				$data['RutaImagen']=$rutaImagen;
				
				$data['Proveedor']=$proveedor->listadoProveedores();
				$data['Flete']=$empresa->listadoEmpresaxIdTipoEmpresa(1);
				$data['Aduanas']=$empresa->listadoEmpresaxIdTipoEmpresa(3);
				$data['Seguro']=$empresa->listadoEmpresaxIdTipoEmpresa(2);
				$this->view->show("/ordencompra/valorizarOrden.phtml",$data);

			}else{
				$ruta['ruta']="/importaciones/ordencompra";
				$this->view->show("ruteador.phtml",$ruta);
			}
		}
		function actualiza(){
			$idOrdenCompra=$_REQUEST['idOrdenCompra'];
			$dataOrdenCompra=$_REQUEST['Ordencompra'];
			$dataDetalleOrdenCompra=$_REQUEST['Detalleordencompra'];
			$DProducto=$_REQUEST['Producto'];
			$ordenCompra=new Ordencompra();
			$detalleOrdenCompra=new Detalleordencompra();
			$producto=new Producto();
			$exito1=$ordenCompra->actualizaOrdenCompra($dataOrdenCompra,$idOrdenCompra);
			$cont=0;
			if($exito1){
				foreach ($dataDetalleOrdenCompra as $data){
					$cont++;

					$data['idordencompra']=$idOrdenCompra;
					$data['cantidadrecibidaoc']=$data['cantidadsolicitadaoc'];
					if($data['iddetalleordencompra']){
						if ($data['estado']!=1) {
							$cantidad=$DProducto[$cont]['cantidad'];
							$idProducto=$data['idproducto'];
							$dataProducto=$producto->buscaProducto($idProducto);
							$stockDisponible=$dataProducto[0]['stockdisponible'];
							$dataP['stockdisponible']=$stockDisponible-$cantidad;
							$exito=$producto->actualizaProducto($dataP,$idProducto);
						}elseif($data['estado']==1){
							$cantidad=$DProducto[$cont]['cantidad'];
							$idProducto=$data['idproducto'];
							$dataProducto=$producto->buscaProducto($idProducto);
							$stockDisponible=$dataProducto[0]['stockdisponible'];
							$dataP['stockdisponible']=$stockDisponible-$cantidad+$data['cantidadsolicitadaoc'];
							$exito=$producto->actualizaProducto($dataP,$idProducto);	
						}
						$exito2=$detalleOrdenCompra->actualizaDetalleOrdenCompra($data,$data['iddetalleordencompra']);
					}else{
						$exito2=$detalleOrdenCompra->grabaDetalleOrdenCompra($data);

						$idProducto=$data['idproducto'];
						$dataProducto=$producto->buscaProducto($idProducto);
						$stockDisponible=$dataProducto[0]['stockdisponible'];
						$dataP['stockdisponible']=$stockDisponible+$data['cantidadsolicitadaoc'];
						$exito=$producto->actualizaProducto($dataP,$idProducto);	
					}
				}
				if($exito2){
					$ruta['ruta']="/importaciones/ordencompra";
					$this->view->show("ruteador.phtml",$ruta);
				}
			}
		}
		function elimina(){
			$id=$_REQUEST['id'];
			$ordenCompra=new Ordencompra();
			$detalleOrdenCompra=new Detalleordencompra();
			$producto=new Producto();
			//buscamos sus detalles de la orden de compra que le perntenece para aumentar el stockdisponible
			$dataDetalle=$detalleOrdenCompra->buscaDetalleOrdenCompra($id);
			$cantidad=count($dataDetalle);
			for ($i=0; $i <$cantidad ; $i++) { 
				$cantidadsolicitadaoc=$dataDetalle[$i]['cantidadsolicitadaoc'];
				$idProducto=$dataDetalle[$i]['idproducto'];
				$dataProducto=$producto->buscaProducto($idProducto);
				$stockDisponible=$dataProducto[0]['stockdisponible'];
				$data['stockdisponible']=$stockDisponible-$cantidadsolicitadaoc;
				$exito=$producto->actualizaProducto($data,$idProducto);
			}
			

			$estado=$ordenCompra->eliminaOrdenCompra($id);
			if($estado){
				$ruta['ruta']="/importaciones/ordencompra";
				$this->view->show("ruteador.phtml",$ruta);
			}
		}
		function ordenCompraVendedor(){
				$idProveedor = $_REQUEST['idProveedor'];
				$fecha = $_REQUEST['fecha'];
				$fechaInicio = $_REQUEST['fechaInicio'];
				$fechaFinal = $_REQUEST['fechaFinal'];
				$repote=new Reporte();
				$data=$repote->reporteOrdenCompra($idProveedor,$fecha,$fechaInicio,$fechaFinal);
				for($i=0;$i<count($data);$i++){
					echo "<tr>";
						echo "<td>".$data[$i]['codigov']."</td>";
						echo '<td>'.date("d/m/Y",strtotime($data[$i]['fordencompra'])).'</td>';
						echo "<td>".$data[$i]['razonsocial']."</td>";
						echo "<td>".$data[$i]['nombrezona']."</td>";
						echo "<td>".$data[$i]['direccion']."</td>";
							$imagen1=($vbVentas==0)?'':(($vbVentas==1)?'/imagenes/iconos/aprobado.jpg':'/imagenes/iconos/desaprobado.jpg');
							$imagen2=($vbCobranza==0)?'':(($vbCobranza==1)?'/imagenes/iconos/aprobado.jpg':'/imagenes/iconos/desaprobado.jpg');
							$imagen3=($vbCreditos==0)?'':(($vbCreditos==1)?'/imagenes/iconos/aprobado.jpg':'/imagenes/iconos/desaprobado.jpg');
						echo '<td><img src="'.$imagen1.'"</td>';
						echo '<td><img src="'.$imagen2.'"</td>';
						echo '<td><img src="'.$imagen3.'"</td>';
						echo '<td width="100px">'.
							'<a href="/almacen/editar/'.$data[$i]['idalmacen'].'" class="btnEditarAlmacen"><img src="/imagenes/iconos/editar.gif"></a>'.
							'<a href="/almacen/eliminar/"'.$data[$i]['idalmacen'].'" class="btnEliminarAlmacen"><img src="/imagenes/iconos/eliminar.gif"></a>';
						echo "</td>";
						echo "</tr>";
					echo "</tr>";
				}
		}
		function contarNoRegistrado(){
			$ordenCompra=new Ordencompra();
			$cantidadOrdenCompra=$ordenCompra->contarOrdenCompraNoRegistrado();
			echo '{"cantidad":"'.$cantidadOrdenCompra.'"}';
		}
		//Registrar la orden de compra por almacen cuando llega
		function registra(){
			/*Pasos a realizar*/
			/*1.- Cabeceras: Se registra el movimiento y se actualiza la orden de compra*/
			$dataMovimiento=$_REQUEST['Movimiento'];
			$dataMovimiento['idtipooperacion']=2;
			$dataMovimiento['iddocumentotipo']=$dataMovimiento['tipodoc'];
			$dataMovimiento['serie']=$dataMovimiento['serie'];
			if ($dataMovimiento['serie']!="" && $dataMovimiento['ndocumento']!="") {
				$dataMovimiento['essunat']=1;
			}
			
			$idOrdenCompra=$dataMovimiento['idordencompra'];
			$movimiento=new Movimiento();
			$exito1=$movimiento->grabaMovimiento($dataMovimiento);
			$ordenCompra=new Ordencompra();
			//Tipo de Cambio Vigente:
			/*$tc=New TipoCambio();
   			$tcv=$tc->consultavigente(2);
   			$tipocambio=$tcv[0]['venta'];*/
   			//Tipo Cambio de la Orden:
   			$tcv=$ordenCompra->TipoCambioxIdOrdenCompra($idOrdenCompra);
   			$tipocambio=$tcv[0]['tipocambiovigente'];
   			// echo $tipocambio;
   			// exit;
			$oc['registrado']=1;
			$exito2=$ordenCompra->actualizaOrdenCompra($oc,$idOrdenCompra);

			/*2.- Detalle: Se registra el detalle del movimiento y actualiza el detalle de la orden*/
			$dataDetalleMovimiento=$_REQUEST['Detallemovimiento'];
			$detalleMovimiento=new Detallemovimiento();
			$detalleOrdenCompra=new Detalleordencompra();

			/*3.- Actualizando la tabla de productos y su historial*/
			$dataProducto=$_REQUEST['Producto'];
			$producto=new Producto(); // Para actualizar los Stocks y precio valorizado
			$historialProducto=new Historialproducto(); // Para grabar precios de productos

			//Verificando que se grabaron las cabeceras
			if($exito1 and $exito2){
			//if(1==1){
				$items=count($dataDetalleMovimiento);
				for ($i=1; $i <=$items ; $i++) {
					//Definiendo datos a grabar:
					$iddetalleordencompra=$dataDetalleMovimiento[$i]['iddetalleordencompra'];
					$idProducto=$dataDetalleMovimiento[$i]['idproducto'];
					//$precioCosto=$dataDetalleMovimiento[$i]['preciocosto'];
					$precioCosto=$tipocambio*$dataDetalleMovimiento[$i]['preciocosto'];
					$cantidadRecibida=$dataDetalleMovimiento[$i]['cantidadrecibidaoc'];
					$cantidadsolicitada=$dataDetalleMovimiento[$i]['cantidadsolicitadaoc'];
					$importe=round($precioCosto*$cantidadRecibida,2);
					$stockactual=$dataProducto[$i]['stockactual'];
					$stockdisponible=$dataProducto[$i]['stockdisponible'];
					$precioactual=$dataProducto[$i]['precioactual'];
					$stockproducto=$stockactual+$cantidadRecibida;
					$stockDisp=$stockdisponible+$cantidadRecibida-$cantidadsolicitada;
					//$stockdisponible=$stockproducto+$cantidadRecibida;
					$fecha=date("Y/m/d");

					//Actualizar la Orden de Compra, con la cantidad recibida.
						$doc['cantidadrecibidaoc']=$cantidadRecibida;
						$exito3=$detalleOrdenCompra->actualizaDetalleOrdenCompra($doc,$iddetalleordencompra);

					//DetalleMovimiento
						$ddm['idmovimiento']=$exito1;
						$ddm['idproducto']=$idProducto;
						$ddm['pu']=$precioCosto;
						$ddm['cantidad']=$cantidadRecibida;
						$ddm['importe']=$importe;
						$ddm['stockactual']=$stockproducto;
						//Valorizando el producto:
						$preciovalorizado=($precioCosto*$cantidadRecibida+$stockactual*$precioactual)/$stockproducto;
						$preciovalorizado=round($preciovalorizado,2);
						$ddm['preciovalorizado']=$preciovalorizado;
						$exito4=$detalleMovimiento->grabaDetalleMovimieto($ddm);

					//Actualizando datos del producto:
						
						$dp['stockactual']=$stockproducto;
						$dp['stockdisponible']=$stockDisp;
						$dp['esagotado']=0;
						if ($_REQUEST['vbimportaciones']==1) {
							$dp['preciocosto']=$preciovalorizado;
						}
						
						//$dp['stockdisponible']=$stockdisponible;
						$exito5=$producto->actualizaProducto($dp,$idProducto);

					//Creando el historial del Producto
						$dhp['idordencompra']=$idOrdenCompra;
						$dhp['idproducto']=$idProducto;
						$dhp['fentrada']=date("Y/m/d");
						$dhp['cantidadentrada']=$cantidadRecibida;
						$dhp['stockactual']=$stockproducto;	
						$exito6=$historialProducto->grabaHistorialProducto($dhp);

				}
				if($exito3 and $exito4 and $exito5 and $exito6){
					$ruta['ruta']="/almacen/regordencompra";
					$this->view->show("ruteador.phtml",$ruta);
				}
			}
		}
		
		function detalle(){
			$id=$_REQUEST['id'];
			$detalle=new Detalleordencompra();
			$proveedor=new Proveedor();
			$dataProveedor=$proveedor->buscaProveedorxOdenCompra($id);
			$data=$detalle->listaDetalleOrdenCompra($id);

			for($i=0;$i<count($data);$i++){
				echo "<tr>";
						echo "<td>".($i+1)."</td>";
						echo "<td>".$data[$i]['codigopa']."</td>";
						echo "<td>".$data[$i]['nompro']."</td>";
						echo "<td>".$data[$i]['fobdoc']."</td>";
						echo "<td>".$data[$i]['cantidadsolicitadaoc']."</td>";
				echo "</tr>";
			}
			echo '<script>$("#lblProveedor").html("'.$dataProveedor[0]['razonsocialp'].'");</script>';
		}
		
		function confirmar(){
			$id=$_REQUEST['idOrdenCompra'];
			$dataOrdenCompra=$_REQUEST['OrdenCompra'];
			$dataOrdenCompraDetalle=$_REQUEST['Detalleordencompra'];
			//echo '<pre>';
			//print_r($dataOrdenCompraDetalle);
			//exit;
			$dataOrdenCompra['valorizado']=1;
			if ($_REQUEST['conformidad']!='on') {
				
				$dataOrdenCompra['vbimportaciones']=0;
				
			}
			if ($_REQUEST['registrado']==1) {
				$detalleMovimiento=$this->AutoLoadModel('detallemovimiento');
				$dataproducto=$_REQUEST['Producto'];
			}

			$totalDOC=count($dataOrdenCompraDetalle);
			$ordenCompra=new Ordencompra();
			$detalleOrdenCompra=new Detalleordencompra();
			$producto=new Producto();
			$historialProducto=new Historialproducto();
			$exito1=$ordenCompra->actualizaOrdenCompra($dataOrdenCompra,$id);

			for($i=1;$i<=$totalDOC;$i++){
				//Actualizando el DetalleOrdenCompra
				$idDOC=$dataOrdenCompraDetalle[$i]['iddetalleordencompra'];
				$ddoci=$dataOrdenCompraDetalle[$i];
				$exito_doc=$detalleOrdenCompra->actualizaDetalleOrdenCompra($ddoci,$idDOC);
				$idProducto=$dataOrdenCompraDetalle[$i]['idproducto'];
				
				if ($_REQUEST['registrado']==1) {
					$tcv=$ordenCompra->TipoCambioxIdOrdenCompra($id);
   					$tipocambio=$tcv[0]['tipocambiovigente'];

					$filtro="m.idordencompra='$id' and dm.idproducto='$idProducto' ";
					$dataMovimiento=$detalleMovimiento->buscaDetalleMovimientoxFiltro($filtro);
					$iddetallemovimiento=$dataMovimiento[0]['iddetallemovimiento'];
					$precioCosto=($dataOrdenCompraDetalle[$i]['cifunitario'])*($tipocambio);
					$cantidadRecibida=$dataOrdenCompraDetalle[$i]['cantidadrecibidaoc'];
					$stockactual=$dataMovimiento[0]['stockactual']-$dataMovimiento[0]['cantidad'];
					$precioactual=$dataproducto[$i]['preciocosto'];
					$stockproducto=$stockactual+$cantidadRecibida;


					$preciovalorizado=($precioCosto*$cantidadRecibida+$stockactual*$precioactual)/$stockproducto;
					$preciovalorizado=round($preciovalorizado,2);
					$ddm['preciovalorizado']=$preciovalorizado;
					$ddm['pu']=$precioCosto;
					$datop['preciocosto']=$preciovalorizado;
					$exitoM=$detalleMovimiento->actualizaDetalleMovimientoxid($iddetallemovimiento,$ddm);
					
				}
				$datop['fob']=$dataOrdenCompraDetalle[$i]['fobdoc'];
				$exitoP=$producto->actualizaProducto($datop,$idProducto);
			// $preciocosto=$dataOrdenCompraDetalle[$i]['totalunitario'];
			// $idproducto=$dataOrdenCompraDetalle[$i]['idproducto'];

			// $cantidad=$dataOrdenCompraDetalle[$i]['cantidadsolicitadaoc'];
			// $dp=$producto->buscaProducto($idproducto);//Data producto
			// $psv=(($dp[0]['stockactual']*$dp[0]['preciocosto'])+($cantidad*$precioCosto))/($dp[0]['stockactual']+$cantidad);//Precio de stock valorizado

			// /*Datos de la tabla producto*/
			// $dp1['preciocosto']=$precioCosto;
			// $dp1['fob']=$dataOrdenCompraDetalle[$i]['fobdoc'];
			// $dp1['cif']=$dataOrdenCompraDetalle[$i]['cifunitario'];
			// $dp1['precioreferencia01']=($psv*1.10);

			// //Datos para Historial producto
			// $dhp['idproducto']=$idproducto;
			// $dhp['preciofob']=$dataOrdenCompraDetalle[$i]['fobdoc'];
			// $dhp['hpreciocosto']=$precioCosto;
			// $dhp['preciostockvalorizado']=$psv;
			// $exito2=$producto->actualizaProducto($dp1,$idproducto);				
			// $exito3=$historialProducto->grabaHistorialProducto($dhp);
			}
			if($exito_doc and $exito1){
				$ruta['ruta']="/importaciones/ordencompra";
				$this->view->show('ruteador.phtml',$ruta);
			}
		}
		
		function cuadroUtilidad(){
			$ordenCompra=$this->AutoLoadModel('ordencompra');
			$id=$_REQUEST['id'];
			$data['valorizado']=$ordenCompra->OrdenesValorizados();
			
			if (!empty($id)) {
				$data['porcifventas']=$this->configIni('Parametros','PorCifVentas');
				$detalleOrdenCompra=$this->AutoLoadModel('detalleordencompra');
				$dataOrdenCompra=$ordenCompra->OrdenCuadroUtilidad($id);
				$data['Ordencompra']=$dataOrdenCompra;
				$data['Detalleordencompra']=$detalleOrdenCompra->listaDetalleOrdenCompra($id);
				
			}
			$this->view->show('/ordencompra/cuadroUtilidad.phtml',$data);
		}

		function actualizaUtilidad(){
			$producto=$this->AutoLoadModel('producto');
			$detalleordencompra=$this->AutoLoadModel('detalleordencompra');
			$ordencompra=$this->AutoLoadModel('ordencompra');

			$dataProducto=$_REQUEST['Producto'];
			$idProducto=$_REQUEST['idproducto'];
			$iddetalleordencompra=$_REQUEST['iddetalleordencompra'];
			$utilidadTotal=0;
			$tipocambio=1;//$_REQUEST['tipocambio'];
			/*echo '<pre>';
			print_r($dataProducto);
			//print_r($idProducto);
			print_r($_REQUEST['DetalleOrdenCompra']);
			exit;*/
			$dataDetalleOrdenCompra=$_REQUEST['DetalleOrdenCompra'];
			$idordencompra=$_REQUEST['idordencompra'];
			$cantidadProducto=count($dataProducto);
			for ($i=0; $i <$cantidadProducto ; $i++) { 
				$utilidadTotal+=$dataDetalleOrdenCompra[$i]['utilidadDetalle'];
				$dProducto['cifventas']=$dataProducto[$i]['cifventas'];
				$dProducto['preciotope']=$dataProducto[$i]['preciotope'];
				$dProducto['preciolista']=round($dataProducto[$i]['preciolista']*$tipocambio,2);				
				$dProducto['preciolistadolares']=round($dataProducto[$i]['preciolistadolares']*$tipocambio,2);
				$dProducto['cifventasdolares']=$dataProducto[$i]['cifventasdolares'];
				$dProducto['preciotopedolares']=$dataProducto[$i]['preciotopedolares'];
				$dProducto['valortipocambio']=$dataProducto[$i]['valortipocambio'];

				$exito=$producto->actualizaProducto($dProducto,$idProducto[$i]);
				if ($exito) {
					$dDetalleOrdenCompra['utilidadDetalle']=$dataDetalleOrdenCompra[$i]['utilidadDetalle'];
					$dDetalleOrdenCompra['precio_lista']=$dataProducto[$i]['preciolista'];
					$dDetalleOrdenCompra['precio_tope']=$dataProducto[$i]['preciotope'];
					$dDetalleOrdenCompra['precio_listadolares']=$dataProducto[$i]['preciolistadolares'];
					$dDetalleOrdenCompra['precio_topedolares']=$dataProducto[$i]['preciotopedolares'];					
					$exito2=$detalleordencompra->actualizaDetalleOrdenCompra($dDetalleOrdenCompra,$iddetalleordencompra[$i]);
				}
			}
			if ($exito && $exito2) {
				$data['utilidad']=$utilidadTotal;
				$data['cuadroutilidad']=1;
				$exito3=$ordencompra->actualizaOrdenCompra($data,$idordencompra);
				if ($exito3) {
					$ruta['ruta']="/ordencompra/cuadroUtilidad";
					$this->view->show('ruteador.phtml',$ruta);
				}
			}
		}
		function vistaRespuesta(){
			$data['codigooc']=$_REQUEST['id'];
			if ($_REQUEST['id']) {
				$this->view->show("/ordencompra/vistaRespuesta.phtml",$data);
			}else{
				$this->view->show("/index/index.phtml",$data);
			}
			
			
		}
		
		function autoCompleteAprobados(){
			$tex=$_REQUEST['term'];
			$ordenCompra=$this->AutoLoadModel('ordencompra');
			$data=$ordenCompra->autoCompleteAprobados($tex);
			echo json_encode($data);
		}
	}
?>
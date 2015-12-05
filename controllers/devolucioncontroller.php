<?php 
	
	class devolucioncontroller extends ApplicationGeneral{

		function devolucion(){
			$devolucion=$this->AutoLoadModel('devolucion');
			$orden=$this->AutoLoadModel('ordenventa');
			$iddevolucion=$_REQUEST['id'];
			
			$dataDevolucion=$devolucion->listaDevolucionxid($iddevolucion);
			$idordenventa=$dataDevolucion[0]['idordenventa'];

			$dataordenventa=$orden->buscarOrdenVentaxDevoluciones($idordenventa);
			$data['codigov']=$dataordenventa[0]['codigov'];
			$data['iddevolucion']=$iddevolucion;

			$this->view->show('/devolucion/devoluciones.phtml',$data);
		}

		function listadevoluciones(){
			$devolucion=$this->AutoLoadModel('devolucion');
			$orden=$this->AutoLoadModel('ordenventa');
			$condicion="estado=1 and registrado=0";
			$data=$devolucion->listaDevolucionFiltro($condicion);
			
			for ($i=0; $i <count($data) ; $i++) { 
				$dataOrden=$orden->buscarOrdenVentaxId($data[$i]['idordenventa']);
				$data[$i]['codigov']=$dataOrden[0]['codigov'];
			}

			$data2['devolucion']=$data;
			$this->view->show('/devolucion/listadevoluciones.phtml',$data2);
		}

		function listadevolucionesAprobadas(){
			$devolucion=$this->AutoLoadModel('devolucion');
			$orden=$this->AutoLoadModel('ordenventa');
			$condicion="estado=1 and registrado=1 and aprobado=0";

			$data=$devolucion->listaDevolucionFiltro($condicion);
			
			for ($i=0; $i <count($data) ; $i++) { 
				$dataOrden=$orden->buscarOrdenVentaxId($data[$i]['idordenventa']);
				$data[$i]['codigov']=$dataOrden[0]['codigov'];
				$data[$i]['simbolo']=$dataOrden[0]['Simbolo'];
			}

			$data2['devolucion']=$data;
			$this->view->show('/devolucion/listadevolucionesaprobadas.phtml',$data2);
		}

		function grabaDetalle(){
			$detalle=$this->AutoLoadModel('detalleordenventa');
			$orden=$this->AutoLoadModel('ordenventa');
			$devolucion=$this->AutoLoadModel('devolucion');
			$claves=array();
			$columna="";
			$iddevolucion="";
			$cantidadDevuelta=array();
			$dataDevolucion="";

			//recibimos codigo (codigov) y idDevolucion
			$iddevolucion=$_REQUEST['idNDevolucion'];
			$codigov=$_REQUEST['idOV'];
			//recuramos el id orden venta 
			$filtro="codigov='$codigov'";
			$busqueda=$orden->buscarOrdenxParametro($filtro);
			$id=$busqueda[0]['idordenventa'];
			$IdMoneda=$busqueda[0]['IdMoneda'];
			$simbolo=(($IdMoneda==1)?"S/.":"US $");
			//verificamos si esta aprobado
			$verificacion=$devolucion->verificar($iddevolucion);
			if (!empty($busqueda)) {
				if (empty($verificacion)) {

					//buscamos los detalles de la orden de venta
					$dataDetalle=$detalle->listaDetalleOrdenVenta($id);
					if (!empty($id) && !empty($iddevolucion)) {
						//verificamos si existe la devolucion
						$dataDevolucion=$devolucion->listaDevolucion2($id,$iddevolucion);
					}
					
					//verificamos si existe la orden de venta
					$ExiteOrden=$orden->buscarOrdenVAprobadoPorAlmacen($id);
					if (count($ExiteOrden)!=0 && count($dataDevolucion)!=0) {
						
						if (empty($iddevolucion)) {
							//verificamos que haya una sola orden de venta en devoluciones que no este confirmada cuando
							$condicion="estado=1 and registrado=0 and idordenventa='$id'";
							$cantidadExitencia=$devolucion->listaDevolucionFiltro($condicion);
							if (count($cantidadExitencia)>0) {
								$columna='<tr><td colspan="7">Ya existe una devolucion que no ha sido confirmada</td></tr>';
								echo $columna;
								exit;
							}else{
								
								$dataD['idordenventa']=$id;
								$dataD['estado']=1;
								$exito1=$devolucion->grabaDevolucion($dataD);
							}
							
							
							//agregamos cada detalle de la orden de venta a la tabla detalledevolucion
							//con valores vacios en caso de  las cantidades
							for ($i=0; $i <count($dataDetalle) ; $i++) { 
								$dataDet['iddevolucion'] =$exito1;
								$dataDet['idproducto']=$dataDetalle[$i]['idproducto'];
								$dataDet['precio']=$dataDetalle[$i]['preciofinal'];
								$dataDet['cantidad']=0;
								$dataDet['importe']=number_format(0,2);
								$dataDet['estado']=1;
								$PrecioDevolucion[$i]=$dataDetalle[$i]['preciofinal'];
								$exitoG=$devolucion->grabaDetalleDevolucion($dataDet);
								$claves[$i]=$exitoG;
							}
						}else{
							//obtenemos el id de devolucion
							$iddevolucion=$dataDevolucion[0]['iddevolucion'];
							$datosDetalleDevolucion=$devolucion->listaDetalleDevolucion($iddevolucion,"");
							for ($i=0; $i <count($datosDetalleDevolucion) ; $i++) { 
								$claves[$i]=$datosDetalleDevolucion[$i]['iddetalledevolucion'];
								$cantidadDevuelta[$i]=$datosDetalleDevolucion[$i]['cantidad'];
								$PrecioDevolucion[$i]=$datosDetalleDevolucion[$i]['precio'];
							}
						}

						//recuperamos el iddetalledevolucion para mandar a la vista
						for ($i=0; $i <count($dataDetalle) ; $i++) { 
							$columna.='<tr>';
							$columna.='<td>'.($i+1).'<input class="idDetalleDevolucion" type="hidden" value="'.$claves[$i].'"></td>';
							$columna.='<td style="text-align: center;">'.$dataDetalle[$i]['codigopa'].'</td>';
							$columna.='<td>'.$dataDetalle[$i]['nompro'].'</td>';
							$columna.='<td style="text-align: center;">'.$dataDetalle[$i]['cantdespacho'].'</td>';
							$columna.='<td style="text-align: center;">'.($dataDetalle[$i]['cantdespacho']-$dataDetalle[$i]['cantdevuelta']).'</td>';
							$columna.='<td style="text-align: center;">'.$simbolo.''.(number_format($dataDetalle[$i]['preciofinal'],2)).'</td>';
							$columna.='<td style="text-align: right;">'.$simbolo.'
										<input size="6"  class="PrecioDevolucion" type="text" readonly value="'.round($PrecioDevolucion[$i],2).'">
										<a href="#" class="editarPrecio" ><img src="/imagenes/editar.gif"></a>
										<a href="#" class="grabarPrecio" ><img width="20" height="20" src="/imagenes/grabar.gif"></a>
										</td>';
							
							$columna.='<td > <input style="margin:auto;display:block;float:left;margin-right:10px" size="4" id="'.$claves[$i].'" type="text"  class="modificar" ';
							$columna.='';
							$columna.='"> <a href="#" class="save"><img width="20" height="20" src="/imagenes/grabar.gif"></a> </td>';
							$columna.='<td  style="text-align: center;"><input readonly style="border:none;width:65px;" class="cantidadDevuelta" type="text" value="'.$cantidadDevuelta[$i].'"></td>';
							$importe=$cantidadDevuelta[$i]*$PrecioDevolucion[$i];
							$totalDevuelto+=$importe;
							$columna.='<td style="text-align: right;">'.$simbolo.' '.number_format($importe,2).'</td>';
							$columna.='</tr>';

						}
							$columna.='<tr>';
							$columna.='<td colspan="9" style="text-align: right;background:white;font-size:15px;"><b>TOTAL : </b> </td>';
							$columna.='<td style="text-align: right;background:green;color:white;font-size:15px;">'.$simbolo.' '.number_format($totalDevuelto,2).'</td>';
							$columna.='</tr>';
						echo  $columna;
					}else{
						$columna='<tr><td colspan="7">No Existe esa Orden de Venta o N° de Devolucion</td></tr>';
						echo $columna;
					}
				}else{
					$columna='<tr><td colspan="7">Esta Aprobado esta Devolucion</td></tr>';
					echo $columna;
				}
			}else{
					$columna='<tr><td colspan="7">Error al Ingresar Orden Venta</td></tr>';
					echo $columna;
				}
					
		}

		function actualizaDevolucion(){
			$devolucion=$this->AutoLoadModel('devolucion');
			$producto=$this->AutoLoadModel('producto');
			$detalleordenventa=$this->AutoLoadModel('detalleordenventa');

			$iddetalledevolucion=$_REQUEST['idDD'];
			$cantidad=$_REQUEST['cantidad'];
			$total=0;
			
			//obtenemos la cantidad de la devolucion 
			$datosActualizar=$devolucion->listaDetalleDevolucion("",$iddetalledevolucion);
			$codigoProducto=$datosActualizar[0]['idproducto'];
			$iddevolucion=$datosActualizar[0]['iddevolucion'];
			$precio=$datosActualizar[0]['precio'];
			$nuevaCantidad=$datosActualizar[0]['cantidad']+$cantidad;

			//buscamos el iddeordenventa
			$D=$devolucion->listaDevolucionxid($iddevolucion);
			$idordenventa=$D[0]['idordenventa'];

			//optenemos la cantidad de detalle, con la orden de venta y codigo del producto
			$DOV=$detalleordenventa->listaDetalleOrdenVentaxProducto($idordenventa,$codigoProducto);
			$cantidadMaxima=$DOV[0]['cantdespacho']-$DOV[0]['cantdevuelta'];
			
			//$iddetalleordenventa=$DOV[0]['iddetalleordenventa'];
			
			//$dataDOV['cantidadrestantedevuelta']=$cantidadMaxima-$cantidad;

			if ($nuevaCantidad<0) {
				echo 'Sobrepaso el Minimo';
			}elseif ($nuevaCantidad>$cantidadMaxima) {
				echo 'Sobrepaso el Maximo';
			}else{
				$cantidad+=$datosActualizar[0]['cantidad'];
				$importeNuevo=$cantidad*$precio;
				$data['cantidad']=$cantidad;
				$data['importe']=$importeNuevo;
				$exito=$devolucion->actualizaDetalleDevolucion($data,"",$iddetalledevolucion);
				
				if ($exito) {
					$data2=$devolucion->listaDetalleDevolucion($iddevolucion,"");
					for ($i=0; $i <count($data2) ; $i++) { 
						$total+=$data2[$i]['importe'];
					}
					$data3['importetotal']=$total;
					$filtro="iddevolucion='$iddevolucion'";
					$exito2=$devolucion->actualizarDevolucion($data3,$filtro);
				}

				echo 'Aprobado';	
			}
		}

		function obtieneIdDevolucion(){
			$idOV=$_REQUEST['idOV'];
			$devolucion=$this->AutoLoadModel('devolucion');
			$id=$devolucion->nuevoId();
			echo $id;
		}

		function grabaAprobacion(){
			$devolucion=$this->AutoLoadModel('devolucion');
			$idNDevolucion=$_REQUEST['idNDevolucion'];
			$data['registrado']=1;
			
			$data['fecharegistrada']=date('Y/m/d H:i:s');
			$filtro="iddevolucion='$idNDevolucion'";
			$exito=$devolucion->actualizarDevolucion($data,$filtro);
			if ($exito) {
				echo 'correcto';
			}else{
				echo 'desaprobado';
			}
		}

		function listaDetalleDevolucion(){
			$devolucion=$this->AutoLoadModel('devolucion');
			$producto=$this->AutoLoadModel('producto');
			$iddevolucion=$_REQUEST['IDD'];
			$columna='';

			$dataDevolucion=$devolucion->listaDevolucionxid($iddevolucion);
			$dataDetalle=$devolucion->listaDetalleDevolucion($iddevolucion,"");

			$idOV=$dataDevolucion[0]['idordenventa'];
			$OBJ_OrdenVenta=$this->AutoLoadModel('OrdenVenta');
			$dataOrdenVenta=$OBJ_OrdenVenta->buscarOrdenVentaxId($idOV);
			$simbolomoneda=$dataOrdenVenta[0]['Simbolo'];




			for ($i=0; $i <count($dataDetalle) ; $i++) { 
				$dataProducto=$producto->buscaProductoOrdenCompra($dataDetalle[$i]['idproducto']);
				if ($dataDetalle[$i]['cantidad']>0) {
					$columna.='<tr>';
					$columna.='<td style="text-align: center;">'.($i+1).'</td>';
					$columna.='<td style="text-align: center;">'.$dataProducto[0]['codigopa'].'</td>';
					$columna.='<td>'.$dataProducto[0]['nompro'].'</td>';
					$columna.='<td style="text-align: right;">'.$simbolomoneda.' '.number_format($dataDetalle[$i]['precio'],2).'</td>';
					$columna.='<td style="text-align: center;">'.$dataDetalle[$i]['cantidad'].'</td>';
					$columna.='<td style="text-align: right;">'.$simbolomoneda.' '.number_format($dataDetalle[$i]['importe'],2).'</td>';
					$columna.='</tr>';
				}
				
			}
			$columna.='<tr><td colspan="4"></td><td style="text-align: center;">Total</td><td style="text-align: right;">'.$simbolomoneda.' '.number_format($dataDevolucion[0]['importetotal'],2).'</td></tr>';
			echo $columna;

		}
		function encabezadoDevolucion(){
			$devolucion=$this->AutoLoadModel('devolucion');
			$iddevolucion=$_REQUEST['IDD'];
			$dataDevolucion=$devolucion->listaDevolucionxid($iddevolucion);
			$dataCliente=$devolucion->listaOrdenconCliente($dataDevolucion[0]['idordenventa']);

			$columna.='<thead><tr>';
			$columna.='<th colspan="3">Impresion de Devoluciones</th><th>Observaciones :</th><td colspan="3">'.$dataDevolucion[0]['observaciones'].'</td>';
			$columna.='</tr><tr>';
			$columna.='<th>Orden Venta</th>';
			$columna.='<td>'.$dataCliente[0]['codigov'].'</td>';
			$columna.='<th>Fecha y Hora de Impresion</th>';
			$columna.='<td>'.date('d-m-Y H:j:s').'</td>';
			$columna.='<th>Situacion</th>';
			$columna.='<td>'.($dataCliente[0]['situacion']==''?'Pendiente':$dataCliente[0]['situacion']).'</td>';
			$columna.='</tr><tr>';
			$columna.='<th>Razon Social</th>';
			$columna.='<td>'.$dataCliente[0]['razonsocial'].'</td>';
			$columna.='<th>RUC</th>';
			$columna.='<td>'.$dataCliente[0]['ruc'].'</td>';
			$columna.='<th>Fecha de Devolucion</th>';
			$columna.='<td>'.date('d-m-Y H:j:s',strtotime($dataDevolucion[0]['fechaaprobada'])).'</td>';
			$columna.='</tr><tr>';
			$columna.='</tr></thead>';
			echo $columna;


		}
		function eliminarDevolucion(){
			$devolucion=$this->AutoLoadModel('devolucion');
			$iddevolucion=$_REQUEST['id'];

			$exito=$devolucion->eliminarDevolucion($iddevolucion);
			if ($exito) {
				$exito2=$devolucion->eliminarDetalleDevolucion($iddevolucion);
				if ($exito2) {
					$ruta['ruta']="/devolucion/listadevoluciones";
					$this->view->show("ruteador.phtml",$ruta);
				}
			}
		}
		function eliminarDevolucionesAprobadas(){
			$devolucion=$this->AutoLoadModel('devolucion');
			$iddevolucion=$_REQUEST['id'];

			$exito=$devolucion->eliminarDevolucion($iddevolucion);
			if ($exito) {
				$exito2=$devolucion->eliminarDetalleDevolucion($iddevolucion);
				if ($exito2) {
					$ruta['ruta']="/devolucion/listadevolucionesAprobadas";
					$this->view->show("ruteador.phtml",$ruta);
				}
			}
		}

		function grabaconfirmarPedido(){
			$devolucion=$this->AutoLoadModel('devolucion');
			$producto=$this->AutoLoadModel('producto');
			$orden=$this->AutoLoadModel('ordenventa');
			$movimiento=$this->AutoLoadModel('movimiento');
			$detMovimiento=$this->AutoLoadModel('detallemovimiento');
			$detalleordenventa=$this->AutoLoadModel('detalleordenventa');

			$totalDevuelto=0;
			$iddevolucion=$_REQUEST['id'];
			//buscamos el id ordenventa por medio de iddevolucion más abajo lo usaremos tambien
			$dataDevolucion=$devolucion->listaDevolucionxid($iddevolucion);
			$idordenventa=$dataDevolucion[0]['idordenventa'];
		
			$documento=$this->AutoLoadModel('documento');
			$filtro=" (nombredoc=1 or nombredoc=2)";
			$dataBusqueda=$documento->buscadocumentoxordenventaPrimero($idordenventa,$filtro);
			//grabamos el movimiento
			if (!empty($dataBusqueda)) {
				$dataMovimiento['tipodoc']=5;
				$dataMovimiento['iddocumentotipo']=5;
				$dataMovimiento['essunat']=1;
			}
			$dataMovimiento['conceptomovimiento']=3;
			$dataMovimiento['tipomovimiento']=1;
			$dataMovimiento['idtipooperacion']=5;
			$dataMovimiento['idordenventa']=$idordenventa;
			$dataMovimiento['iddevolucion']=$iddevolucion;
			$dataMovimiento['fechamovimiento']=date('Y-m-d H:j:s');
			$exitoM=$movimiento->grabaMovimiento($dataMovimiento);
			//actualizo la tabla devolucion
			$exito=$devolucion->confirmar($iddevolucion);

			if ($exito && $exitoM) {

				//obtenemos los detalles de la devolucion
				$detalledevolucion=$devolucion->listaDetalleDevolucion($iddevolucion,"");
				for ($i=0; $i <count($detalledevolucion) ; $i++) { 
					$idProducto=$detalledevolucion[$i]['idproducto'];
					$cantidadDevuelta=$detalledevolucion[$i]['cantidad'];
					$totalDevuelto+=$detalledevolucion[$i]['importe'];

					//obtenemos el stockacutal de producto
					$dataProducto=$producto->buscaProducto($idProducto);
					$cantidadActual=$dataProducto[0]['stockactual'];
					$cantidadDisponible=$dataProducto[0]['stockdisponible'];
					//actualizamos el stockactual de producto
					$data['stockactual']=$cantidadDevuelta + $cantidadActual;
					$data['stockdisponible']=$cantidadDevuelta + $cantidadDisponible;
					$data['esagotado']=0;
					$exito2=$producto->actualizaProducto($data,$idProducto);

					//grabamos los detalles del movimiento 
					if (!empty($detalledevolucion[$i]['cantidad'])) {
						$dataDetalleMovimiento['cantidad']=$detalledevolucion[$i]['cantidad'];
						$dataDetalleMovimiento['idmovimiento']=$exitoM;
						$dataDetalleMovimiento['pu']=$detalledevolucion[$i]['precio'];
						$dataDetalleMovimiento['preciovalorizado']=$dataProducto[0]['preciocosto'];
						$dataDetalleMovimiento['idproducto']=$detalledevolucion[$i]['idproducto'];
						$dataDetalleMovimiento['stockactual']=$dataProducto[0]['stockactual']+$detalledevolucion[$i]['cantidad'];
						$dataDetalleMovimiento['stockdisponibledm']=$cantidadActual+$detalledevolucion[$i]['cantidad'];
						$dataDetalleMovimiento['importe']=$detalledevolucion[$i]['precio']*$detalledevolucion[$i]['cantidad'];
						$exitoDM=$detMovimiento->grabaDetalleMovimieto($dataDetalleMovimiento);

					}
					

					//obtemos la cantidad devuelta actual 
					$dataP=$detalleordenventa->listaDetalleOrdenVentaxProducto($idordenventa,$idProducto);
					$cantidadActualDevuelta=$dataP[0]['cantdevuelta'];

					//actualiza la cantidad devuelta necesito idordenventa y idproducto
					$filtro="idordenventa='$idordenventa' and idproducto='$idProducto'";
					$dataDOV['cantdevuelta']=$cantidadActualDevuelta+$cantidadDevuelta;
					$exito4=$detalleordenventa->actualizaxFiltro($dataDOV,$filtro);
					if (!$exito4) {
						echo 'cuarto error';
					}
				}
				if ($exito2) {

					//obtengo el importe devuelto de la tabla orden venta
					$dataordenventa=$orden->buscarOrdenVentaxId($idordenventa);
					
					$importeDevueltoActual=$dataordenventa[0]['importedevolucion'];
					//actualizo el total devuelto en la tabla orden venta
					$dataFinal['importedevolucion']=$totalDevuelto+$importeDevueltoActual;
					$exito3=$orden->actualizaOrdenVenta($dataFinal,$idordenventa);
					if ($exito3) {
						//verificamos que tenga su orden tenga factura
						
						if (!empty($dataBusqueda)) {
							//creamos una nota de credito
							$dataDoc['montofacturado']=round($totalDevuelto,2);
							$dataDoc['nombredoc']=5;
							$dataDoc['idordenventa']=$idordenventa;
							$dataDoc['fechadoc']=date('Y-m-d');
							$dataDoc['concepto']=1;
							$dataDoc['iddevolucion']=$iddevolucion;
							$grabaDoc=$documento->grabaDocumento($dataDoc);

							if (!$grabaDoc) {
								echo 'Error al grabar la nota credito';
							}
							
						}	
							
							if (!empty($dataBusqueda)) {
								$dataIngreso['tipocobro']=10;
							}else{
								$dataIngreso['tipocobro']=7;
							}
								
								$ingresos=$this->AutoLoadModel('ingresos');


								$dataIngreso['idOrdenVenta']=$idordenventa;
								$dataIngreso['idcobrador']=122;
								$dataIngreso['montoingresado']=round($totalDevuelto,2);
								$dataIngreso['saldo']=round($totalDevuelto,2);
								$dataIngreso['fcobro']=date('Y-m-d');
								$dataIngreso['idcliente']=$dataordenventa[0]['idcliente'];
								$grabaIngreso=$ingresos->graba($dataIngreso);

							
						


						$ruta['ruta']="/devolucion/listadevolucionesAprobadas";
						$this->view->show("ruteador.phtml",$ruta);
						//echo 'entro';
					}else{
						echo 'tercer error';
					}
					

				}else{
					echo 'segundo error';
				}
				
			}else{
				echo 'primer error';
			}
			
		}
		function desaprobarDevolucion(){
			$devolucion=$this->AutoLoadModel('devolucion');
			$iddevolucion=$_REQUEST['id'];
			$data['aprobado']=0;
			$data['fechaaprobada']="";
			$filtro="iddevolucion='$iddevolucion'";
			$exito=$devolucion->actualizarDevolucion($data,$filtro);
			if ($exito) {
				$ruta['ruta']="/devolucion/listadevolucionesAprobadas";
				$this->view->show("ruteador.phtml",$ruta);
			}
		}
		function confirmarPedido(){
			$devolucion=$this->AutoLoadModel('devolucion');
			$iddevolucion=$_REQUEST['id'];
			$datos['documento']=$this->tipoDocumento();

			$dataDevolucion=$devolucion->listaDevolucionxid($iddevolucion);
			$datos['iddevolucion']=$iddevolucion;
			$datos['codigov']='OV-'.date('y').str_pad($dataDevolucion[0]['idordenventa'] ,6,'0',STR_PAD_LEFT);
			$datos['idordenventa']=$dataDevolucion[0]['idordenventa'];
			$this->view->show('/devolucion/confirmar.phtml',$datos);
		}
		function listarDevolucionTotal(){
			$devolucion=$this->AutoLoadModel('devolucion');
			$orden=$this->AutoLoadModel('ordenventa');
			$contador=0;
			$data=array();

			$pagina=$_REQUEST['id'];
			if (empty($_REQUEST['id'])) {
				$pagina=1;
			}
			session_start();
			$_SESSION['P_Devolucion']="";
			
			$dataDevolucion=$devolucion->listaDevolucionesPaginado("",$pagina);
			$cantidadDevoluciones=count($dataDevolucion);
			for ($i=0; $i <$cantidadDevoluciones ; $i++) { 
				$idordenventa=$dataDevolucion[$i]['idordenventa'];
				$iddevolucion=$dataDevolucion[$i]['iddevolucion'];

				$filtro="estado=1 and idordenventa='$idordenventa'";
				$dataOrden=$orden->buscarOrdenxParametro($filtro);

				$dataDevolucion[$i]['simbolo']=($dataOrden[0]['IdMoneda']==1)?"S/.":"US $";
				$dataDevolucion[$i]['esfacturado']=$dataOrden[0]['esfacturado']==1?'<img style="margin:auto;display:block" width="20" high="20" src="/imagenes/facturar.png">':'';
				$dataDevolucion[$i]['codigov']=$dataOrden[0]['codigov'];
		
			}
			
			$paginacion=$devolucion->paginadoDevoluciones("");
			$data['paginacion']=$paginacion;
			$data['blockpaginas']=round($paginacion/10);
			$data['dataDevolucion']=$dataDevolucion;
			$this->view->show('/devolucion/listadevolucionestotales.phtml',$data);
		}
		function buscaDevoluciones(){
			$devolucion=$this->AutoLoadModel('devolucion');
			$orden=$this->AutoLoadModel('ordenventa');
			$contador=0;
			$data=array();

			if (empty($_REQUEST['id'])) {
				$pagina=1;
			}
			session_start();
			if (!empty($_REQUEST['txtBusqueda'])) {
				$_SESSION['P_Devolucion']=$_REQUEST['txtBusqueda'];
			}		
			$parametro=$_SESSION['P_Devolucion'];
			

			$dataDevolucion=$devolucion->listaDevolucionesPaginado("",$pagina,$parametro);

			$cantidadDevoluciones=count($dataDevolucion);
			for ($i=0; $i <$cantidadDevoluciones ; $i++) { 
				$idordenventa=$dataDevolucion[$i]['idordenventa'];
				$iddevolucion=$dataDevolucion[$i]['iddevolucion'];

				$filtro="estado=1 and idordenventa='$idordenventa'";
				$dataOrden=$orden->buscarOrdenxParametro($filtro);
				$dataDevolucion[$i]['esfacturado']=$dataOrden[0]['esfacturado']==1?'<img style="margin:auto;display:block" width="20" high="20" src="/imagenes/facturar.png">':'';
				$dataDevolucion[$i]['codigov']=$dataOrden[0]['codigov'];
		
			}

			
			
			$data['dataDevolucion']=$dataDevolucion;
			$paginacion=$devolucion->paginadoDevoluciones("",$parametro);
			$data['retorno']=$parametro;
			$data['paginacion']=$paginacion;
			$data['blockpaginas']=round($paginacion/10);
			$data['totregistros']=$devolucion->cuentaDevoluciones("",$parametro);
			$this->view->show('/devolucion/buscadevoluciones.phtml',$data);
		}
		function historialDevoluciones(){

		}
		function cambiaPrecioDevolucion(){
			$precioDevolucion=$_REQUEST['precioDevolucion'];
			$idDetalleDevolucion=$_REQUEST['idDetalleDevolucion'];
			$cantidadDevuelta=$_REQUEST['cantidadDevuelta'];
			$devolucion=$this->AutoLoadModel('devolucion');
			$importe=$cantidadDevuelta*$precioDevolucion;
			//recuperamos el importe y iddevolucion
			$dataDetalle=$devolucion->buscaDetalleDevolucion($idDetalleDevolucion);
			$importeAnterior=$dataDetalle[0]['importe'];
			$idDevolucion=$dataDetalle[0]['iddevolucion'];
			//recuperamos el importeTotal de la devolucion
			$dataDevolucion=$devolucion->listaDevolucionxid($idDevolucion);
			$importeTotalAnterior=$dataDevolucion[0]['importetotal'];
			$nuevoImporte=$importeTotalAnterior-$importeAnterior+$importe;
			
			$data['precio']=$precioDevolucion;
			$data['cantidad']=$cantidadDevuelta;
			$data['importe']=$importe;
			
			$filtro="iddetalledevolucion='$idDetalleDevolucion' ";
			$exito=$devolucion->actualizarDetalleDevolucion($data,$filtro);
			if ($exito) {
				$data2['importetotal']=$nuevoImporte;
				$filtro2="iddevolucion='$idDevolucion'";
				$exito2=$devolucion->actualizarDevolucion($data2,$filtro2);
				if ($exito2) {
					echo 'Se grabo Correctamente';
				}else{echo 'error 2';}
			}else{echo 'error 1';}

		}
		function grabaobservaciones(){
			$devolucion=$this->AutoLoadModel('devolucion');
			$observaciones=$_REQUEST['observaciones'];
			$iddevolucion=$_REQUEST['iddevolucion'];
			$data['observaciones']=$observaciones;
			$filtro="iddevolucion='$iddevolucion'";
			$exito=$devolucion->actualizarDevolucion($data,$filtro);
			echo $exito;
		}
		function cargaobservaciones(){
			$devolucion=$this->AutoLoadModel('devolucion');
			$iddevolucion=$_REQUEST['iddevolucion'];
			
			$filtro="iddevolucion='$iddevolucion'";
			$data=$devolucion->listaDevolucionFiltro($filtro);
			
			echo $data[0]['observaciones'];
		}

		/**************************************************************************
			Nombre : ReporteDevoluciones
			Funcionalidad: Muestra los criterios de busueda para las devoluciones,
							esta asociada a la funcion DataReporteDevoluciones
			Creado por: Fernando Garcia Atuncar
			Fecha :	19.10.2014
		***************************************************************************/
		function ReporteDevoluciones(){
			$this->view->newshow("/devolucion/reportedevolucion");
		}
		function DataReporteDevoluciones(){
			$idcliente=$_REQUEST['idcliente'];
			$idordenventa=$_REQUEST['idordenventa'];
			if ($_REQUEST['situacion']!=-1) {
				$esregistrado=$_REQUEST['situacion']?1:1;
				$esaprobado=$_REQUEST['situacion']?2:1;
			}
			$devtotal=$_REQUEST['devtotal'];
			$fecregini=$_REQUEST['fecregini'];
			$fecregfin=$_REQUEST['fecregfin'];
			$fecaprini=$_REQUEST['fecaprini'];
			$fecaprfin=$_REQUEST['fecaprfin'];
			$devoluciones=$this->AutoLoadModel('Devolucion');
			$dataDevoluciones=$devoluciones->ReporteDevoluciones($idcliente,$idordenventa,$esregistrado,$fecregini,$fecregfin,$esaprobado,$fecaprini,$fecaprfin,$devtotal);
			// echo "<pre>";
			// print_r($_REQUEST);
			// exit;

			$tamanio=count($dataDevoluciones);
			for ($i=0; $i < $tamanio; $i++) {
				$simbolo=$dataDevoluciones[$i]['simbolo'];
				$acumula[$simbolo]['importetotal']+=$dataDevoluciones[$i]['importetotal'];
				$situacion=($dataDevoluciones[$i]['importetotal']==$dataDevoluciones[$i]['importeaprobado'])?'DEV TOTAL':'DEV PARCIAL';
				$html.="
				<tr>
					<td>".$dataDevoluciones[$i]['razonsocial']."</td>
					<td>".$dataDevoluciones[$i]['codigov']."</td>
					<td>".$simbolo." ".$dataDevoluciones[$i]['importeaprobado']."</td>
					<td>".$dataDevoluciones[$i]['devolucion']."</td>
					<td>".$dataDevoluciones[$i]['registrado']."</td>
					<td>".$dataDevoluciones[$i]['fecharegistrada']."</td>
					<td>".$dataDevoluciones[$i]['aprobado']."</td>
					<td>".$dataDevoluciones[$i]['fechaaprobada']."</td>
					<td>".$simbolo." ".$dataDevoluciones[$i]['importetotal']."</td>
					<td>".$situacion."</td>
					<td>".$dataDevoluciones[$i]['observaciones']."</td>
				</tr>";
			}
			$html.="<tr><td colspan='8'></td><td><b>Devolucion US $</b></td><td><b>US $ ".$acumula['US $']['importetotal']."</b></td><td></td>";
			$html.="<tr><td colspan='8'></td><td><b>Devolucion S/.</b></td><td><b>S/. ".$acumula['S/.']['importetotal']."</b></td><td></td>";
			echo $html;
		}
	}
	
 ?>
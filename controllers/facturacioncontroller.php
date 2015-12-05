<?php
Class facturacioncontroller extends ApplicationGeneral{	
/*Generacion de factura*/
	function generaFactura(){
		$empresa=new Almacen();
		$data['Empresa']=$empresa->listadoAlmacen();
		$data['ModoFacturacion']=$this->modoFacturacion();
		$data['tipoDocumento']=$this->tipoDocumento();
		if(count($_REQUEST)==6){
			
			$this->view->show('/facturacion/generacionfactura.phtml', $data);
		}else{
			
			//Generando el documento
			$dataFactura=$_REQUEST['Factura'];
			//validando que no exista y evitar volver a grabar cuando recargan la pagina
			
			$modelpdf=$this->AutoLoadModel('pdf');
			$exitofactura=$modelpdf->listaFacturaEmitidasNoAnuladas($dataFactura['idOrdenVenta']);
			if (count($exitofactura)==0) {
				

				//$dataFactura['nombredoc']=1;
				$documento=new Documento();
				$id=$documento->grabaDocumento($dataFactura);
				//Grabando en la Orden de Venta para que no genere nuevos deocumentos.
				
				$ov=New OrdenVenta();
				$dataOV=$_REQUEST['OrdenVenta'];
				$dataOV['esfacturado']=1;
				$ov->actualizaOrdenVenta($dataOV,$dataFactura['idOrdenVenta']);	
				//actualizamos la serie y numero de del documento en la tabla movimiento
				$movimiento=new Movimiento();
				$filtro=" idtipooperacion='1' and idordenventa='".$dataFactura['idOrdenVenta']."'";
				$dataMovimiento=$movimiento->buscaMovimientoxfiltro($filtro);
				if (!empty($dataMovimiento)) {
					$dataM['iddocumentotipo']=1;
					$dataM['serie']=$dataFactura['serie'];
					$dataM['ndocumento']=$dataFactura['numdoc'];
					$dataM['essunat']=1;

					$exito=$movimiento->actualizaMovimiento($dataM,$filtro);
				}
				
			}

			$this->view->show('/facturacion/generacionfactura.phtml',$data);
		}
	}
	
/*Generacion de guia de remision*/
	function genguiaremi(){
		if(count($_REQUEST)==6){
			$this->view->show('/facturacion/generacionguiaremision.phtml');
		}else{
			$documento=new Documento();
			$ordenVenta=new OrdenVenta();

			$dataGuiaRemision=$_REQUEST['GuiaRemision'];
			$dataGuiaRemision['nombredoc']=4;

			$modelpdf=$this->AutoLoadModel('pdf');
			$exitofactura=$modelpdf->listaGuiasEmitidasNoAnuladas($dataGuiaRemision['idordenventa']);
			if (count($exitofactura)==0) {
				//si usamos esto debemos grabar en la orden de venta
				//$dataOrdenVenta['guiaremision']=1;
				
				$idordenventa=$dataGuiaRemision['idordenventa'];
				$dataOrdenVenta=$_REQUEST['ordenVenta'];
				$dataOrdenVenta['guiaremision']=1;
				$exito=$ordenVenta->actualizaOrdenVenta($dataOrdenVenta,$idordenventa);
				if ($exito) {
					$id=$documento->grabaDocumento($dataGuiaRemision);

					$movimiento=new Movimiento();
					$filtro=" idtipooperacion='1' and idordenventa='".$idordenventa."'";
					$dataMovimiento=$movimiento->buscaMovimientoxfiltro($filtro);
					if (!empty($dataMovimiento) and $dataMovimiento[0]['iddocumentotipo']!=1 and $dataMovimiento[0]['iddocumentotipo']!=2) {
						$dataM['iddocumentotipo']=4;
						$dataM['serie']=$dataGuiaRemision['serie'];
						$dataM['ndocumento']=$dataGuiaRemision['numdoc'];
						$dataM['essunat']=1;

						$exito=$movimiento->actualizaMovimiento($dataM,$filtro);
					}


					$this->view->show('/facturacion/generacionguiaremision.phtml');
				}
			}else{
				$this->view->show('/facturacion/generacionguiaremision.phtml');
			}
		}
	}

/*Nota de credito*/
	function notacredito(){
		$_SESSION['Autenticado']=true;
		$almacen=new Almacen();
		$data['Almacen']=$almacen->listadoAlmacen();
		$this->view->show("facturacion/notacredito.phtml",$data);
	}
	
/*Emision de letras letras*/
	function emiLetras(){
		$_SESSION['Autenticado']=true;
		$ordenVenta=new OrdenVenta();
		$data['OrdenVenta']=$ordenVenta->listarEmisionLetras();
		$data['CondicionLetra']=$this->condicionLetra();
		$data['TipoLetra']=$this->tipoLetra();
		$this->view->show("facturacion/emisionletras.phtml",$data);
	}
	
//Busqueda de transporte por cliente
	function buscatransporte(){
		$id=$_REQUEST['id'];
		$transporte=new Transporte();
		$cliente=new Cliente();
		$data=$transporte->buscarxCliente($id);
		//$dataCliente=$cliente->buscaCliente($id);
		for($i=0;$i<count($data);$i++){
			/*if($data[$i]['idtransporte']==$dataCliente[0]['transporte']){
				echo '<option value="'.$data[$i]['idtransporte'].'" selected>'.$data[$i]['trazonsocial'];	
			}else{*/
				echo '<option value="'.$data[$i]['idclientetransporte'].'">'.$data[$i]['trazonsocial'];
			//}
		}
	}
	
	function generaLetras(){
		$NumeroOrdenVenta=$_REQUEST['ordenVenta'];
		$condicionLetras=$_REQUEST['condicionLetras'];
		$ordenVenta=new OrdenVenta();
		$dataOrdenVenta=$ordenVenta->buscarxid($NumeroOrdenVenta);
		$dataCondicionLetras=$this->buscaCondicionLetra($condicionLetras);
		$arrayCondicionLetras=explode("/",$dataCondicionLetras);
		$actualDate=date("d-m-Y");
		for($i=0;$i<$condicionLetras;$i++){
			echo "<tr>";
				echo "<td>".($i+1)."</td>";
				echo "<td>".date("d-m-Y",strtotime("$actualDate + ".$arrayCondicionLetras[$i]." day"))."</td>";
				echo "<td>".number_format(($dataOrdenVenta[0]['importe']/$condicionLetras),2)."</td>";
			echo "<tr>";
		}
	}
	
	function seguimientoLetras(){
		$this->view->show("/facturacion/seguimientoletras.phtml");
	}	
	function listaProductosGuia(){
		$idGuia=$_REQUEST['id'];

		$dataGuia=$this->AutoLoadModel("OrdenVenta");
		$idTipoCambio=$dataGuia->BuscarCampoOVxId($idGuia,"IdTipoCambioVigente");//PREGUNTAR SI ACTUAL O AL ELEGIDO EN LA COMPRA

		$TipoCambio=$this->AutoLoadModel("TipoCambio");
		$dataTipoCambio=$TipoCambio->consultaDatosTCVigentexTCElegido($idTipoCambio);
		$simboloMoneda=$dataTipoCambio[0]['simbolo'];
		$TC_PrecioVenta=$dataTipoCambio[0]['venta'];

		$porcentaje=$_REQUEST['porcentaje'];
		$modo=$_REQUEST['modo'];
		$detalleOrdenVenta=new detalleOrdenVenta();
		$almacen=new Almacen();
		$data=$detalleOrdenVenta->listaDetalleOrdenVenta($idGuia);
		$total=0;
		$descuento=New Descuento();
		$dataDescuento=$descuento->listado();
		$dataAlmacen=$almacen->listado();
		$cantidadDescuento=count($dataDescuento);
		$cantidadAlmacen=count($dataAlmacen);
		$cantidadDetalles=count($data);

		for ($i=0; $i < $cantidadDescuento; $i++) { 
			$dscto[$dataDescuento[$i]['id']]=$dataDescuento[$i]['valor'];
		}	
		

		for ($x=0; $x <$cantidadAlmacen ; $x++) {
				$dataAlmacen[$x]['importe']=0; 
		}
		
		$varTotal=0;
		for($i=0;$i<$cantidadDetalles;$i++){

			
			echo "<tr>";
				$data[$i]['cantporcentaje']=$data[$i]['cantdespacho']-$data[$i]['cantdevuelta'];
				if($porcentaje!=""){
					if($modo==1){
						$precio=$data[$i]['preciofinal'];
						$data[$i]['preciofinal']=(($precio*$porcentaje)/100);
						$data[$i]['cantporcentaje']=$data[$i]['cantdespacho']-$data[$i]['cantdevuelta'];
					}else{
						$cantidad=$data[$i]['cantdespacho']-$data[$i]['cantdevuelta'];
						$data[$i]['cantporcentaje']=ceil(($cantidad*$porcentaje)/100);
					}
				}
			
			for ($x=0; $x <$cantidadAlmacen ; $x++) {
				
				if ($dataAlmacen[$x]['idalmacen']==$data[$i]['idalmacen']) {
					$subtotal=($data[$i]['preciofinal']*($data[$i]['cantporcentaje']));
					$dataAlmacen[$x]['importe']+=$subtotal;
					$varTotal+=$subtotal;
				}
			}

				$precioneto=(number_format($data[$i]['preciofinal'],2));
				$precioTotal=$precioneto*($data[$i]['cantporcentaje']);
				//$precioTotal=(($data[$i]['precioaprobado'])*($data[$i]['cantaprobada'])-($data[$i]['tdescuentoaprovado']));
				echo '<td>'.$data[$i]['codigov'].'</td>';
				echo '<td>'.$data[$i]['nompro'].'</td>';
				echo '<td>'.($data[$i]['cantdespacho']).'</td>';
				echo '<td>'.($data[$i]['cantdevuelta']).'</td>';
				echo '<td>'.($data[$i]['cantporcentaje']).'</td>';
				echo '<td> '.$simboloMoneda.' '.number_format($data[$i]['preciolista2'],2).'</td>';
				echo '<td>'.$dscto[$data[$i]['descuentoaprobado']].'</td>';
				echo '<td style="text-align:right;">'.$simboloMoneda.' '.number_format($precioneto,2).'</td>';
				echo '<td style="text-align:right;">'.$simboloMoneda.' '.number_format($precioTotal,2).'</td>';
			echo "</tr>";
			$total+=$precioTotal;
		}


		echo '<tr style="color:#f00">';
			echo '<td colspan="8" class="right bold" style="text-align:right;">
					Precio de Venta<br>
					I.G.V.<br>
					Total a Pagar
				</td>';
			echo '<td style="text-align:right;">'.$simboloMoneda.' '.
					number_format(($total/1.18),2).'<br>'.$simboloMoneda.' '.
					number_format($total-($total/1.18),2).'<br>'.$simboloMoneda.' '.
					number_format(($total),2).
				'</td>';
		echo "</tr>.<input type='hidden' name='Factura[montoigv]' value='".(number_format($total-($total/1.18),2))."'>";
		echo "</tr>.<input type='hidden' name='Factura[montofacturado]' value='".($total)."'>";
		
		echo "<tr><td colspan='9'><table>";
		echo "<th>Empresa</th><th>Importe (".$simboloMoneda." )</th><th>Porcentaje (%)</th>";
		for ($x=0; $x <$cantidadAlmacen ; $x++) { 
			if ($dataAlmacen[$x]['importe']!=0) {
				$valor=(($dataAlmacen[$x]['importe']/$varTotal)*100);
				echo "<tr><td>".$dataAlmacen[$x]['razsocalm']."</td><td>".number_format($dataAlmacen[$x]['importe'],2)."</td><td>".round($valor,2)."</td></tr>";
			}
		}
		echo "</table></td></tr>";
	}
	function listaProductosGuiaRecuperado(){
		$idGuia=$_REQUEST['id'];

		$dataGuia=$this->AutoLoadModel("OrdenVenta");
		$idTipoCambio=$dataGuia->BuscarCampoOVxId($idGuia,"IdTipoCambioVigente");//PREGUNTAR SI ACTUAL O AL ELEGIDO EN LA COMPRA

		$TipoCambio=$this->AutoLoadModel("TipoCambio");
		$dataTipoCambio=$TipoCambio->consultaDatosTCVigentexTCElegido($idTipoCambio);
		$simboloMoneda=$dataTipoCambio[0]['simbolo'];
		$TC_PrecioVenta=$dataTipoCambio[0]['venta'];


		$porcentaje=$_REQUEST['porcentaje'];
		$modo=$_REQUEST['modo'];
		$detalleOrdenVenta=new detalleOrdenVenta();
		$data=$detalleOrdenVenta->listaDetalleOrdenVenta($idGuia);
		$total=0;

		$descuento=New Descuento();
		$almacen=New Almacen();
		$dataDescuento=$descuento->listado();
		$dataAlmacen=$almacen->listado();
		$cantidadDescuento=count($dataDescuento);
		$cantidadAlmacen=count($dataAlmacen);
		$cantidadDetalles=count($data);
		for ($i=0; $i < $cantidadDescuento; $i++) { 
			$dscto[$dataDescuento[$i]['id']]=$dataDescuento[$i]['valor'];
		}

		for ($x=0; $x <$cantidadAlmacen ; $x++) {
				$dataAlmacen[$x]['importe']=0; 
		}
		
		$varTotal=0;

		for($i=0;$i<$cantidadDetalles;$i++){
			
			echo "<tr>";
				$data[$i]['cantporcentaje']=$data[$i]['cantdespacho']-$data[$i]['cantdevuelta'];
				
				if($porcentaje!=""){
					if($modo==1){
						$precio=$data[$i]['preciofinal'];
						$data[$i]['preciofinal']=(($precio*$porcentaje)/100);
						$precioneto=round(($data[$i]['preciofinal']),2);		
						$data[$i]['cantporcentaje']=$data[$i]['cantdespacho']-$data[$i]['cantdevuelta'];
					}elseif($modo==2){
						$precioneto=round(($data[$i]['preciofinal']),2);		
						$cantidad=$data[$i]['cantdespacho']-$data[$i]['cantdevuelta'];
						$data[$i]['cantporcentaje']=round((($cantidad*$porcentaje)/100),2);
						
					}
					else{
						$precioneto=round(($data[$i]['preciofinal']),2);
					}
				}
				if(ceil($data[$i]['cantporcentaje'])==$data[$i]['cantporcentaje']){
					$data[$i]['color']='style="background:#E0EDFF"';
					
				}else{
					$data[$i]['color']='style="background:red"';
				}
				for ($x=0; $x <$cantidadAlmacen ; $x++) {
				
					if ($dataAlmacen[$x]['idalmacen']==$data[$i]['idalmacen']) {
						$subtotal=($data[$i]['preciofinal']*($data[$i]['cantporcentaje']));
						$dataAlmacen[$x]['importe']+=$subtotal;
						$varTotal+=$subtotal;
					}
				}
				$precioTotal=$precioneto*($data[$i]['cantporcentaje']);
				//$precioTotal=(($data[$i]['precioaprobado'])*($data[$i]['cantaprobada'])-($data[$i]['tdescuentoaprovado']));
				echo '<td>'.$data[$i]['codigov'].'</td>';
				echo '<td>'.$data[$i]['nompro'].'</td>';
				echo '<td>'.$data[$i]['cantdespacho'].'</td>';
				echo '<td>'.$data[$i]['cantdevuelta'].'</td>';
				echo '<td '.$data[$i]['color'].'>'.$data[$i]['cantporcentaje'].'</td>';
				echo '<td>'.$simboloMoneda.' '.number_format($data[$i]['preciolista2'],2).'</td>';
				echo '<td>'.$dscto[$data[$i]['descuentosolicitado']].'</td>';
				echo '<td>'.$simboloMoneda.' '.number_format($precioneto,2).'</td>';
				echo '<td>'.$simboloMoneda.' '.number_format($precioTotal,2).'</td>';
			echo "</tr>";
			$total+=$precioTotal;
		}
		echo '<tr style="color:#f00">';
			echo '<td colspan="8" class="right bold" style="text-align:right;">
					Precio de Venta<br>
					I.G.V.<br>
					Total a Pagar
				</td>';
			echo '<td class="right">'.$simboloMoneda.' '.
					number_format(($total/1.18),2).'<br>'.$simboloMoneda.' '.
					number_format($total-($total/1.18),2).'<br>'.$simboloMoneda.' '.
					number_format(($total),2).
				'</td>';
		echo "</tr>.<input type='hidden' name='Factura[montoigv]' value='".(number_format($total-($total/1.18),2))."'>";
		echo "</tr>.<input type='hidden' name='Factura[montofacturado]' value='".($total)."'>";

		echo "<tr><td colspan='9'><table>";
		echo  '<th>Empresa</th><th>Importe ('.$simboloMoneda.' )</th><th>Porcentaje (%)</th>';
		for ($x=0; $x <$cantidadAlmacen ; $x++) { 
			if ($dataAlmacen[$x]['importe']!=0) {
				$valor=(($dataAlmacen[$x]['importe']/$varTotal)*100);
				echo "<tr><td>".$dataAlmacen[$x]['razsocalm']."</td><td>".number_format($dataAlmacen[$x]['importe'],2)."</td><td>".round($valor,2)."</td></tr>";
			}
		}
		echo "</table></td></tr>";
	}

	function listaProductosGuiaRemision(){
		$idGuia=$_REQUEST['id'];
		$detalleOrdenVenta=new DetalleOrdenVenta();
		$data=$detalleOrdenVenta->listaDetalleOrdenVenta($idGuia);
		$unidadMedida=$this->unidadMedida();
		for($i=0;$i<count($data);$i++){
			echo "<tr>";
				echo '<td>'.($i+1).'</td>';
				echo '<td>'.$data[$i]['codigov'].'</td>';
				echo '<td>'.$data[$i]['nompro'].'</td>';
				echo '<td>'.$unidadMedida[($data[$i]['unidadmedida'])].'</td>';
				echo '<td>'.$data[$i]['cantdespacho'].'</td>';
			echo "</tr>";
		}
	}
	
	function autocompletefactura(){
		$texIni=$_REQUEST['term'];
		$documento=new documento();
		$data=$documento->autocompletefactura($texIni);
		echo json_encode($data);
	}
	function registraNotaCredito(){
		$data=$_REQUEST['NotaCredito'];
		$idcliente=$_REQUEST['idcliente'];
		$data['nombredoc']=5;
		//el concepto 2 es cuando es por precio y 1 es cuando es por devolucion
		$documento=new Documento();
		$ingresos=new Ingresos();
		$exito=$documento->grabaDocumento($data);
		if($exito){
			//creamos in ingreso
			$dataIngreso['idordenventa']=$data['idordenventa'];
			$dataIngreso['idcliente']=$idcliente;
			$dataIngreso['montoingresado']=$data['montofacturado'];
			$dataIngreso['saldo']=$data['montofacturado'];
			$dataIngreso['tipocobro']=10;
			$dataIngreso['idcobrador']=122;
			$dataIngreso['esvalidado']=1;
			$dataIngreso['nrodoc']=$data['numdoc'];
			$dataIngreso['fcobro']=date("Y-m-d");
			$graba=$ingresos->graba($dataIngreso);
			if ($graba) {
				
			$ruta['ruta']="/facturacion/notacredito";
			$this->view->show("ruteador.phtml",$ruta);
			}

		}
	}

	function listaOrdenVenta(){
		$id=$_REQUEST['id'];
		if (empty($_REQUEST['id'])) {
			$id=1;
		}
		session_start();
		$_SESSION['P_ListaOrden']="";
		$model=$this->AutoLoadModel('pdf');
		$ordencobro=$this->AutoLoadModel('ordencobro');

		
		
		$Factura=$model->listaOrdenVentaPaginado($id,"");
		for ($i=0; $i <count($Factura) ; $i++) { 
			$documento=$model->listaFacturaEmitidas($Factura[$i]['idordenventa']);
			$Factura[$i]['importeguia']=$ordencobro->deudatotal($Factura[$i]['idordenventa']);
			$Factura[$i]['deuda']=$ordencobro->totalPendiente($Factura[$i]['idordenventa']);
			
			if (!empty($documento) && count($documento)==1) {
				
				$Factura[$i]['serie']=$documento[0]['serie'];
				$Factura[$i]['numdoc']=$documento[0]['numdoc'];
				$Factura[$i]['montofacturado']=$documento[0]['montofacturado'];
				$Factura[$i]['nombredoc']=$documento[0]['nombredoc'];
				$Factura[$i]['iddocumento']=$documento[0]['iddocumento'];
			}
		}
		
		$data['Factura']=$Factura;
		$paginacion=$model->paginadoOrdenVenta("");
		$data['paginacion']=$paginacion;
		$data['blockpaginas']=round($paginacion/10);
		//echo '<pre>';
		//print_r($Factura);
		//exit;
		$this->view->show('/facturacion/listaordenventa.phtml',$data);

	}

	function buscaOrdenVenta(){
		$id=$_REQUEST['id'];
		if (empty($_REQUEST['id'])) {
			$id=1;
		}
		session_start();
		if (!empty($_REQUEST['txtBusqueda'])) {
			$_SESSION['P_ListaOrden']=$_REQUEST['txtBusqueda'];
		}		
		$parametro=$_SESSION['P_ListaOrden'];
		$filtro="wc_ordenventa.`codigov` like '%$parametro%'";

		$model=$this->AutoLoadModel('pdf');
		
		$Factura=$model->listaOrdenVentaPaginado($id,$filtro);
		for ($i=0; $i <count($Factura) ; $i++) { 
			$documento=$model->listaFacturaEmitidas($Factura[$i]['idordenventa']);
			
			if (!empty($documento) && count($documento)==1) {
				
				$Factura[$i]['serie']=$documento[0]['serie'];
				$Factura[$i]['numdoc']=$documento[0]['numdoc'];
				$Factura[$i]['montofacturado']=$documento[0]['montofacturado'];
				$Factura[$i]['nombredoc']=$documento[0]['nombredoc'];
				$Factura[$i]['iddocumento']=$documento[0]['iddocumento'];
			}
		}
		$data['Factura']=$Factura;
		$paginacion=$model->paginadoOrdenVenta($filtro);
		$data['retorno']=$parametro;
		$data['paginacion']=$paginacion;
		$data['blockpaginas']=round($paginacion/10);
		$data['totregistros']=$model->cuentaOrdenVenta($filtro);
		$this->view->show('/facturacion/buscaordenventa.phtml',$data);
	}
	function buscaDuracion(){
		$id=$_REQUEST['id'];
		if (empty($_REQUEST['id'])) {
			$id=1;
		}
		session_start();
		if (!empty($_REQUEST['txtBusqueda'])) {
			$_SESSION['P_ListaOrden']=$_REQUEST['txtBusqueda'];
		}		
		$parametro=$_SESSION['P_ListaOrden'];
		$filtro="wc_ordenventa.`codigov` like '%$parametro%'";

		$model=$this->AutoLoadModel('pdf');
		$ordenventaduracion=$this->AutoLoadModel('ordenventaduracion');
		$Factura=$model->listaOrdenVentaPaginado($id,$filtro);
		for ($i=0; $i <count($Factura) ; $i++) { 
			$documento=$model->listaFacturaEmitidas($Factura[$i]['idordenventa']);
			$ovd=$ordenventaduracion->listaOrdenVentaDuracionxOrdenVenta($Factura[$i]['idordenventa']);
			$cantidadDuracion=count($ovd);
			for ($y=0; $y <$cantidadDuracion ; $y++) { 
				if (strcmp($ovd[$y]['referencia'],"ventas")==0) {
					$Factura[$i]['dVentas']=$ovd[$y]['tiempo'];
				}
				elseif (strcmp($ovd[$y]['referencia'],"cobranza")==0) {
					$Factura[$i]['dCobranza']=$ovd[$y]['tiempo'];
				}
				elseif (strcmp($ovd[$y]['referencia'],"almacen")==0) {
					$Factura[$i]['dAlmacen']=$ovd[$y]['tiempo'];
				}
				elseif (strcmp($ovd[$y]['referencia'],"credito")==0) {
					$Factura[$i]['dCredito']=$ovd[$y]['tiempo'];
				}
				elseif (strcmp($ovd[$y]['referencia'],"despacho")==0) {
					$Factura[$i]['dDespacho']=$ovd[$y]['tiempo'];
				}
			}
			if (!empty($documento) && count($documento)==1) {
				
				$Factura[$i]['serie']=$documento[0]['serie'];
				$Factura[$i]['numdoc']=$documento[0]['numdoc'];
				$Factura[$i]['montofacturado']=$documento[0]['montofacturado'];
				$Factura[$i]['nombredoc']=$documento[0]['nombredoc'];
				$Factura[$i]['iddocumento']=$documento[0]['iddocumento'];
			}
		}
		$data['Factura']=$Factura;
		$paginacion=$model->paginadoOrdenVenta($filtro);
		$data['retorno']=$parametro;
		$data['paginacion']=$paginacion;
		$data['blockpaginas']=round($paginacion/10);
		$data['totregistros']=$model->cuentaOrdenVenta($filtro);
		$this->view->show('/facturacion/buscaDuracion.phtml',$data);
	}
	function listaDuracion(){
		$id=$_REQUEST['id'];
		if (empty($_REQUEST['id'])) {
			$id=1;
		}
		session_start();
		$_SESSION['P_ListaOrden']="";
		$model=$this->AutoLoadModel('pdf');
		$ordenventaduracion=$this->AutoLoadModel('ordenventaduracion');
		
		$Factura=$model->listaOrdenVentaPaginado($id,"");
		for ($i=0; $i <count($Factura) ; $i++) { 
			$documento=$model->listaFacturaEmitidas($Factura[$i]['idordenventa']);
			
			$ovd=$ordenventaduracion->listaOrdenVentaDuracionxOrdenVenta($Factura[$i]['idordenventa']);
			$cantidadDuracion=count($ovd);
			for ($y=0; $y <$cantidadDuracion ; $y++) { 
				if (strcmp($ovd[$y]['referencia'],"ventas")==0) {
					$Factura[$i]['dVentas']=$ovd[$y]['tiempo'];
				}
				elseif (strcmp($ovd[$y]['referencia'],"cobranza")==0) {
					$Factura[$i]['dCobranza']=$ovd[$y]['tiempo'];
				}
				elseif (strcmp($ovd[$y]['referencia'],"almacen")==0) {
					$Factura[$i]['dAlmacen']=$ovd[$y]['tiempo'];
				}
				elseif (strcmp($ovd[$y]['referencia'],"credito")==0) {
					$Factura[$i]['dCredito']=$ovd[$y]['tiempo'];
				}
				elseif (strcmp($ovd[$y]['referencia'],"despacho")==0) {
					$Factura[$i]['dDespacho']=$ovd[$y]['tiempo'];
				}
			}
			if (!empty($documento) && count($documento)==1) {
				
				$Factura[$i]['serie']=$documento[0]['serie'];
				$Factura[$i]['numdoc']=$documento[0]['numdoc'];
				$Factura[$i]['montofacturado']=$documento[0]['montofacturado'];
				$Factura[$i]['nombredoc']=$documento[0]['nombredoc'];
				$Factura[$i]['iddocumento']=$documento[0]['iddocumento'];
			}
		}
		
		$data['Factura']=$Factura;
		$paginacion=$model->paginadoOrdenVenta("");
		$data['paginacion']=$paginacion;
		$data['blockpaginas']=round($paginacion/10);
		//echo '<pre>';
		//print_r($Factura);
		//exit;
		$this->view->show('/facturacion/listadoDuracion.phtml',$data);

	}
}
?>
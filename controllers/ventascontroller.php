<?php

Class ventascontroller extends ApplicationGeneral{

	function creaguiaped(){
		$_SESSION['Autenticado']=true;
		$vendedor=new Actor();
		$ordenVenta=new OrdenVenta();

		$url="/".$_REQUEST['url'];

		$data['Documento']=$this->tipoDocumento();
		$data['Vendedor']=$vendedor->listadoVendedoresTodos();
		$data['FormaPago']=$this->formaPago();
		$data['CondicionLetra']=$ordenVenta->condicionesletra();
		$data['TipoLetra']=$this->tipoLetra();
		$data['ModoFacturacion']=$this->modoFacturacion();
		//$data['Codigo']=$ordenVenta->generaCodigo();
		$data['codigov']=$_REQUEST['id'];
		$this->view->show("/ventas/ordenventa.phtml",$data);
	}
	function reportstocklin(){
		$_SESSION['Autenticado']=true;
		$this->view->show("ventas/form.phtml");
	}	
	//Reporte de stock valorizado
	function reportevalorizados(){
			$linea=new Linea();
			//$this->view->template="stockvalorizado";
			$data['Linea']=$linea->listadoLineas();
			$this->view->show('reporte/stockvalorizado.phtml',$data);
		}
	function reporteStockValorizado(){
			$idLinea=$_REQUEST['linea'];
			$idSubLinea=$_REQUEST['sublinea'];
			$reporte=new Reporte();
			$data=$reporte->reporteStockValorizado($idLinea,$idSubLinea);
			$total=0;
			for($i=0;$i<count($data);$i++){
				echo '<tr>';
					echo "<td>".$data[$i]['codigo']."</td>";
					echo "<td>".$data[$i]['nompro']."</td>";
					echo "<td>".$data[$i]['idalmacen']."</td>";
					echo "<td>".$data[$i]['idlineapadre']."</td>";
					echo "<td>".$data[$i]['nomum']."</td>";
					echo "<td>".$data[$i]['stockactual']."</td>";
					echo '<td class="right">'.number_format($data[$i]['preciolista'],2).'</td>';
					echo '<td class="right">'.number_format(($data[$i]['stockactual']*$data[$i]['preciolista']),2).'</td>';
				echo '<tr>';
				$total+=($data[$i]['stockactual']*$data[$i]['preciolista']);
			}
			echo '<tr style="font-weight:bold"><td colspan="6"></td><td class="right">Total:</td><td class="right">'.number_format($total,2).'</td></tr>';
		}
		
	//mantenimiento cliente
	function mantclientes(){
		$_SESSION['Autenticado']=true;
		$cliente = new Cliente();
		$data['Cliente'] = $cliente->listadoClientes();
		$this->view->show("cliente/listar.phtml", $data);
	}
	
	/*Autocomplete Transporte*/
	function autocompleteTransporte(){
		$razonSocial=$_REQUEST['term'];
		$transporte=new Transporte();
		$data=$transporte->buscarAutocomplete($razonSocial);
		echo json_encode($data);
	}

	/**Aprobaciones*/
	function autorizarventa(){
		if(!$_REQUEST['idOrdenVenta']){
			$ordenVenta=new OrdenVenta();
			$opciones=new general();
			$url="/".$_REQUEST['url'];
			$data['Opcion']=$opciones->buscaOpcionexurl($url);
			$data['Modulo']=$opciones->buscaModulosxurl($url);
			$data['ordenVenta']=$ordenVenta->pedidoxaprobar();
			$data['FormaPago']=$this->formaPago();
			$this->view->show("ventas/aprobarpedido.phtml",$data);
		}else{
			$id=$_REQUEST['idOrdenVenta'];
			$ordenVenta=new OrdenVenta();
			
			if($id!='' && $id!=0){
				$dataBusqueda=$ordenVenta->buscarOrdenVentaxId($id);
			}
			if($dataBusqueda[0]['vbventas']!=1){	
				$estadoOrden=$_REQUEST['estadoOrden'];
				$dataOrdenVenta=$_REQUEST['Orden'];
				$dataDetalleOrdenVenta=$_REQUEST['DetalleOrdenVenta'];
				
				$detalleOrdenVenta=new DetalleOrdenVenta();
				$producto=new Producto();
				$dataOrdenVenta['vbventas']=($estadoOrden==1)?1:2;
				if ($dataOrdenVenta['vbventas']==2) {
					$dataOrdenVenta['desaprobado']=1;
				}
				$productos=$_REQUEST['Producto'];
				$exito1=$ordenVenta->actualizaOrdenVenta($dataOrdenVenta,$id);
				$cont=0;
				if($exito1){
					foreach($dataDetalleOrdenVenta as $data){

						if ($dataOrdenVenta['vbventas']==2 || $data['estado']==0) {
							//buscamos producto
							$idproducto=$productos[$cont]['idproducto'];
							$dataProducto=$producto->buscaProductoxId($idproducto);
							$stockdisponibleA=$dataProducto[0]['stockdisponible'];
							$stockdisponibleN=$stockdisponibleA+$productos[$cont]['cantsolicitada'];
							$dataNuevo['stockdisponible']=$stockdisponibleN;
							//actualizamos es stockdisponible
							$exitoP=$producto->actualizaProducto($dataNuevo,$idproducto);
						}elseif($data['estado']==1 && $dataOrdenVenta['vbventas']==1){
							
							//buscamos producto
							$idproducto=$productos[$cont]['idproducto'];
							$dataProducto=$producto->buscaProductoxId($idproducto);
							$stockdisponibleA=$dataProducto[0]['stockdisponible'];
							$stockdisponibleN=$stockdisponibleA+$productos[$cont]['cantsolicitada']-$data['cantaprobada'];
							$dataNuevo['stockdisponible']=$stockdisponibleN;
							//actualizamos es stockdisponible
							$exitoP=$producto->actualizaProducto($dataNuevo,$idproducto);
						}
	
						$exito2=$detalleOrdenVenta->actualizar($data['iddetalleordenventa'],$data);	
						$cont++;
					}
				
					if($exito2){
						$ordenVentaDuracion=new ordenventaduracion();
						$DDA=$ordenVentaDuracion->listaOrdenVentaDuracion($id,"creacion");
						$dataDuracion['idordenventa']=$id;
						$intervalo=$this->date_diff(date('Y-m-d H:i:s',strtotime($DDA[0]['fechacreacion'])),date('Y-m-d H:i:s'));
						$dataDuracion['tiempo']=$intervalo;
						$dataDuracion['referencia']='ventas';
						if (empty($DDA[0]['fechacreacion'])) {
							$dataDuracion['tiempo']='indefinido';
						}
						$exito3=$ordenVentaDuracion->grabaOrdenVentaDuracion($dataDuracion);
						$ruta['ruta']="/ventas/autorizarventa";
						$this->view->show("ruteador.phtml",$ruta);
	
						//$date3=date('Y-m-d H:i:s');
						//$intervalo=$this->date_diff($date3,'2013-01-23 15:30:00');
					}
				}
			}else{
				
				$ruta['ruta']="/ventas/autorizarventa";
				$this->view->show("ruteador.phtml",$ruta);
			}
		}
	}
	
	function listaordenes(){
			$ordenVenta=new OrdenVenta();
			$data['ordenVenta']=$ordenVenta->listaOrdenesGeneral();
			$data['FormaPago']=$this->formaPago();
			$this->view->show("/ventas/ordenesgeneral.phtml",$data);
	}

	function guiamadre(){
		$this->view->show("/ventas/guiamadre.phtml",$data);
	}

	function listaReporteVentas(){
		
		if (!empty($_REQUEST['txtFechaAprobadoInicio'])) {
			$txtFechaAprobadoInicio=date('Y-m-d',strtotime($_REQUEST['txtFechaAprobadoInicio']));
		}

		if (!empty($_REQUEST['txtFechaAprobadoFinal'])) {
			$txtFechaAprobadoFinal=date('Y-m-d',strtotime($_REQUEST['txtFechaAprobadoFinal']));
                        
		}
		if (!empty($_REQUEST['txtFechaGuiadoInicio'])) {
			$txtFechaGuiadoInicio=date('Y-m-d',strtotime($_REQUEST['txtFechaGuiadoInicio']));
		}
		
		if (!empty($_REQUEST['txtFechaGuiadoFin'])) {
                    
			$txtFechaGuiadoFin=date('Y-m-d',strtotime($_REQUEST['txtFechaGuiadoFin']));
		}
		if (!empty($_REQUEST['txtFechaDespachoInicio'])) {
			$txtFechaDespachoInicio=date('Y-m-d',strtotime($_REQUEST['txtFechaDespachoInicio']));
		}

		if (!empty($_REQUEST['txtFechaDespachoFin'])) {
			$txtFechaDespachoFin=date('Y-m-d',strtotime($_REQUEST['txtFechaDespachoFin']));
		}

		if (!empty($_REQUEST['txtFechaCanceladoInicio'])) {
			$txtFechaCanceladoInicio=date('Y-m-d',strtotime($_REQUEST['txtFechaCanceladoInicio']));
		}
		
		if (!empty($_REQUEST['txtFechaCanceladoFin'])) {
			$txtFechaCanceladoFin=date('Y-m-d',strtotime($_REQUEST['txtFechaCanceladoFin']));
		}
		
		$idOrdenVenta=$_REQUEST['idOrdenVenta'];
		$idCliente=$_REQUEST['idCliente'];
		$idVendedor=$_REQUEST['idVendedor'];
		$idpadre=$_REQUEST['idpadre'];
		$idcategoria=$_REQUEST['idcategoria'];
		$idzona=$_REQUEST['idzona'];
		$condicion=$_REQUEST['condicion'];
		$aprobados=$_REQUEST['aprobados'];
		$desaprobados=$_REQUEST['desaprobados'];
		$pendiente=$_REQUEST['pendiente'];
		$idmoneda=$_REQUEST['idmoneda'];

		$condicionVenta="";
		if ($condicion==1) {
			$condicionVenta=" and ov.es_contado='1' and ov.es_credito!='1' and ov.es_letras!='1' ";
		}elseif($condicion==2){
			$condicionVenta=" and ov.es_credito='1' and ov.es_letras!='1' ";
		}elseif($condicion==3){
			$condicionVenta="  and ov.es_letras='1' and  ov.tipo_letra=1";
		}elseif($condicion==4){
			$condicionVenta="  and ov.es_letras='1' and ov.tipo_letra=2";
		}
		
		$reporte=$this->AutoLoadModel('reporte');

		$dataReporte=$reporte->reporteVentas($txtFechaAprobadoInicio,$txtFechaAprobadoFinal,$txtFechaGuiadoInicio,$txtFechaGuiadoFin,$txtFechaDespachoInicio,$txtFechaDespachoFin,$txtFechaCanceladoInicio,$txtFechaCanceladoFin,$idOrdenVenta,$idCliente,$idVendedor,$idpadre,$idcategoria,$idzona,$condicionVenta,$aprobados,$desaprobados,$pendiente,$idmoneda);
		$cantidad=count($dataReporte);
//                var_dump($dataReporte);
//                exit();
		$totalAprobado=0;
		$totalDespachado=0;
		$fila="";
		for ($i=0; $i <$cantidad ; $i++) { 
			$situtacion="";
			if ($dataReporte[$i]['es_contado']==1 && $dataReporte[$i]['es_credito']!=1 && $dataReporte[$i]['es_letras']!=1) {
				$situtacion="Contado";
			}elseif( $dataReporte[$i]['es_credito']==1 && $dataReporte[$i]['es_letras']!=1){
				$situtacion="Credito";
			}
			elseif( $dataReporte[$i]['es_letras']==1 && $dataReporte[$i]['tipo_letra']==1){
				$situtacion="Letra Banco";
			}
			elseif( $dataReporte[$i]['es_letras']==1 && $dataReporte[$i]['tipo_letra']==2){
				$situtacion="Letra Cartera";
			}
			$estado="Pendiente";
			if ($dataReporte[$i]['desaprobado']==1) {
				$estado="Desaprobado";
			}elseif($dataReporte[$i]['vbcreditos']==1){
				$estado="Aprobado";
			}
			if ($dataReporte[$i]['vbcreditos']!=1) {
				$valorImporte=0.00;
			}else{
				$valorImporte=$dataReporte[$i]['importeov'];
			}

			$fila.="<tr>";
			$fila.="<td>".$dataReporte[$i]['fordenventa']."</td>";
			$fila.="<td>".$dataReporte[$i]['fechadespacho']."</td>";
			$fila.="<td>".$dataReporte[$i]['fechaCancelado']."</td>";
			$fila.="<td>".$dataReporte[$i]['codigov']."</td>";
			$fila.="<td>".$dataReporte[$i]['razonsocial']."</td>";
			$fila.="<td>".($dataReporte[$i]['apellidopaterno'].' '.$dataReporte[$i]['apellidomaterno'].' '.$dataReporte[$i]['nombres'])."</td>";
			$fila.="<td style='text-align:right;'>".$dataReporte[$i]['simbolo']." ".$dataReporte[$i]['importeaprobado']."</td>";
			$fila.="<td style='text-align:right;'>".$dataReporte[$i]['simbolo']." ".$valorImporte."</td>";
			$fila.="<td>".($estado)."</td>";
			$fila.="<td>".$situtacion."</td>";
			$fila.="<td>".(html_entity_decode($dataReporte[$i]['observaciones'],ENT_QUOTES,'UTF-8'))."</td>";
			$fila.="<td>".$dataReporte[$i]['estadoov']."</td>";
			$fila.="</tr>";
			$totalAprobado+=$dataReporte[$i]['importeaprobado'];
			$totalDespachado+=$valorImporte;
		}
		//$fila.="<tr><td colspan='6' style='text-align:right;'>TOTALES</td><td style='text-align:right;'>S/.".$totalAprobado."</td><td style='text-align:right;'>S/.".$totalDespachado."</td></tr>";
		echo $fila;
	}
}

?>
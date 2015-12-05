<?php
	class ReporteController extends ApplicationGeneral{
	//Lista de precios
		function ListaPrecios(){
                   	
			if(count($_REQUEST)==6){
				$linea=new Linea();
				$almacen=new Almacen();
				$opciones=new general();
				$url="/".$_REQUEST['url'];                               
				$data['Opcion']=$opciones->buscaOpcionexurl($url);
				$data['Modulo']=$opciones->buscaModulosxurl($url);
				$data['Linea']=$linea->listadoLineas('idpadre=0');
				$data['Almacen']=$almacen->listadoAlmacen();
				$this->view->show('reporte/listaprecio.phtml',$data);
                                                           
			}else{
				$idAlmacen=$_REQUEST['idAlmacen'];
				$idLinea=$_REQUEST['idLinea'];
				$idSubLinea=$_REQUEST['idSubLinea'];
				$idProducto=$_REQUEST['idProducto'];
				$reporte=new Reporte();
				$data=$reporte->reporteListaPrecio($idAlmacen,$idLinea,$idSubLinea,$idProducto);
				$rutaImagen=$this->rutaImagenesProducto();
				$unidadMedida=$this->unidadMedida();
                      
				$data2=array();
				for($i=0;$i<count($data);$i++){
					//	echo '<td><img src="'.$rutaImagen.$data[$i]['codigo'].'/'.$data[$i]['imagen'].'" width="50" height="50"></td>';
					$data2[$i]['codigo']=$data[$i]['codigopa'];
					$data2[$i]['nompro']=$data[$i]['nompro'];
					$data2[$i]['stockactual']=$data[$i]['stockactual'];
					$data2[$i]['preciolista']=$data[$i]['preciolista'];
					$data2[$i]['preciolistadolares']=$data[$i]['preciolistadolares'];
					$data2[$i]['unidadmedida']=$data[$i]['nombremedida'];
					$data2[$i]['empaque']=empty($data[$i]['idempaque'])?'Sin/Emp.':$data[$i]['codempaque'];
				}
				$objeto=$this->formatearparakui($data2);
				header("Content-type: application/json");
				//echo "{\"data\":" .json_encode($objeto). "}";
				echo json_encode($objeto);
			}
		}
		
	//Inventario
		function inventario(){
			if(count($_REQUEST)==6){
				$linea=new Linea();
				$almacen=new Almacen();
				$url="/".$_REQUEST['url'];
				$opciones=new general();
				$data['Opcion']=$opciones->buscaOpcionexurl($url);
				$data['Modulo']=$opciones->buscaModulosxurl($url);
				$data['Linea']=$linea->listadoLineas('idpadre=0');
				$data['Almacen']=$almacen->listadoAlmacen();
				$this->view->show('/reporte/inventario.phtml',$data);
			}else{
				$idAlmacen=$_REQUEST['idAlmacen'];
				$idLinea=$_REQUEST['idLinea'];
				$idSubLinea=$_REQUEST['idSubLinea'];
				$idProducto=$_REQUEST['idProducto'];
				$producto=new Producto();
				$ordenCompra=new Ordencompra();
				$ordenVenta=new OrdenVenta();
				$dataProducto=$producto->inventario($idAlmacen,$idLinea,$idSubLinea,$idProducto);
				$dataOrdenCompra=$ordenCompra->inventario($idAlmacen,$idLinea,$idSubLinea,$idProducto);
				$dataOrdenVenta=$ordenVenta->inventario($idAlmacen,$idLinea,$idSubLinea,$idProducto);
				$rutaImagen=$this->rutaImagenesProducto();
				$unidadMedida=$this->unidadMedida();
				$empaque=$this->empaque();
				$data=array();
				$indice=0;
				foreach($dataProducto as $dato){
					if(count($dataOrdenCompra)){
						foreach($dataOrdenCompra as $doc){
							if($doc['idproducto']==$dato['idproducto']){
								$dato['stockporllegar']=$doc['cantidadsolicitadaoc'];
								break;
							}
						}	
					}
					if(count($dataOrdenVenta)){
						foreach($dataOrdenVenta as $dop){
							if($dop['idproducto']==$dato['idproducto']){
								$dato['stockpordespachar']=$dop['cantaprobada'];
								break;
							}
						}
					}
					//	echo '<td><img src="'.$rutaImagen.$dato['codigo'].'/'.$dato['imagen'].'" width="50" height="50"></td>';
					$data[$indice]['codigo']=$dato['codigopa'];
					$data[$indice]['nompro']=$dato['nompro'];
					$data[$indice]['preciolista']=$dato['preciolista'];
					$data[$indice]['stockactual']=$dato['stockactual'];
					$data[$indice]['stockporllegar']=$dato['stockporllegar'];
					$data[$indice]['stockpordespachar']=$dato['stockpordespachar'];
					$data[$indice]['unidadmedida']=$dato['unidadmedida'];
					$data[$indice]['empaque']=$empaque[($dato['empaque'])];
					$indice++;
				}
				$objeto=$this->formatearparakui($data);
				header("Content-type: application/json");
				//echo "{\"data\":" .json_encode($objeto). "}";
				echo json_encode($objeto);
			}
		}
		//Kardex
		function Kardex(){
			if(count($_REQUEST)==6){
				$linea=new Linea();
				$almacen=new Almacen();
				$url="/".$_REQUEST['url'];
				$opciones=new general();
				$data['Modulo']=$opciones->buscaModulosxurl($url);
				$data['Opcion']=$opciones->buscaOpcionexurl($url);
				$data['Linea']=$linea->listadoLineas('idpadre=0');
				$data['Almacen']=$almacen->listadoAlmacen();
				$this->view->show('/reporte/kardex.phtml',$data);
			}else{
				$idAlmacen=$_REQUEST['idAlmacen'];
				$idLinea=$_REQUEST['idLinea'];
				$idSubLinea=$_REQUEST['idSubLinea'];
				$idProducto=$_REQUEST['idProducto'];
				$reporteKardex = new Reporte();
				$cliente=new Cliente();
				$orden=new Orden();
				$data=$reporteKardex->reporteKardex($idAlmacen,$idLinea,$idSubLinea,$idProducto);
				$unidadMedida=$this->unidadMedida();
				$tipoMovimiento=$this->tipoMovimiento();
				$data2=array();
				for($i=0;$i<count($data);$i++){
					$conceptoMovimiento=$this->conceptoMovimiento($data[$i]['tipomovimiento']);
					$nombreCliente="";
					if($data[$i]['idorden']!=null){
						$do=$orden->buscarxid($data[$i]['idorden']);
						if($do[0]['idcliente']){
							$dc=$cliente->buscaCliente($do[0]['idcliente']);
							$nombreCliente=($dc[0]['razonsocial']!="")?$dc[0]['razonsocial']:$dc[0]['nombres']." ".$dc[0]['apellidopaterno']." ".$dc[0]['apellidomaterno'];	
						}
					}
					$data2[$i]['ndocumento']=$data[$i]['ndocumento'];
					$data2[$i]['fechamovimiento']=date('d/m/Y',strtotime($data[$i]['fechamovimiento']));
					$data2[$i]['conceptomovimiento']=$conceptoMovimiento[($data[$i]['conceptomovimiento'])];
					$data2[$i]['tipomovimiento']=substr($tipoMovimiento[($data[$i]['tipomovimiento'])],0,1);
					$data2[$i]['cantidad']=$data[$i]['cantidad'];
					$data2[$i]['nombrecliente']=$nombreCliente;
					$data2[$i]['stockdisponible']=$data[$i]['stockdisponibledm'];
					$data2[$i]['unidadmedida']=$unidadMedida[($data[$i]['unidadmedida'])];
					$data2[$i]['pu']=number_format($data[$i]['pu'],2);
					$data2[$i]['estadopedido']=$data[$i]['estadopedido'];
				}
				$objeto=$this->formatearparakui($data2);
				header("Content-type: application/json");
				//echo "{\"data\":" .json_encode($objeto). "}";
				echo json_encode($objeto);
			}
		}
	//Agotados
		function agotados(){
			if(count($_REQUEST)==6){
				$linea=new Linea();
				$almacen=new Almacen();
				$data['Linea']=$linea->listadoLineas('idpadre=0');
				$data['Almacen']=$almacen->listadoAlmacen();
				$this->view->show('/reporte/agotados.phtml',$data);
			}else{
				
				if (!empty($_REQUEST['fecha'])) {
					$fecha = date('d-m-Y',strtotime($_REQUEST['fecha']));
				}
				if (!empty($_REQUEST['fechaInicio'])) {
					$fechaInicio = date('d-m-Y',strtotime($_REQUEST['fechaInicio']));
				}
				if (!empty($_REQUEST['fechaFinal'])) {
					$fechaFinal = date('d-m-Y',strtotime($_REQUEST['fechaFinal']));
				}
				
				$idProducto = $_REQUEST['idProducto'];
				$repote=new Reporte();
				$ordenCompra = new Ordencompra();
				$linea=new Linea();
				//$rutaImagen=$this->rutaImagenesProducto();
				$data=$repote->reporteAgotados($fecha,$fechaInicio,$fechaFinal,$idProducto);
				//$data=$repote->reporteAgotados('','','','');
				$unidadMedida=$this->unidadMedida();
				for($i=0;$i<count($data);$i++){
					$fu='';//Fecha ultima compra
					$fp='';//Fecha penultima compra
					$c1=0;//Cantidad 1
					$c2=0;//Cantidad 2
					$doc=$ordenCompra->lista2UltimasCompras($data[$i]['idproducto']);
					//Data orden compra
					$cantidadDoc=count($doc);
					if($cantidadDoc){
						if($cantidadDoc==2){
							$fu=$doc[0]['fordencompra']	;
							$fp=$doc[1]['fordencompra']	;
							$c1=$doc[0]['cantidadsolicitadaoc'];
							$c2=$doc[1]['cantidadsolicitadaoc'];
						}else{
							$fu=$doc[0]['fordencompra']	;
							$c1=$doc[0]['cantidadsolicitadaoc'];
						}
					}
					//><img src="'.$rutaImagen.$data[$i]['codigo'].'/'.$data[$i]['imagen'].'"></td>';
					$arreglo[$i]['codigo']=$data[$i]['codigop'];
					$arreglo[$i]['nompro']=$data[$i]['nompro'];
					$arreglo[$i]['fechaultima']=date("d/m/Y",strtotime($fu));
					$arreglo[$i]['cantidadultima']=$c1;
					$arreglo[$i]['fechapenultima']=date("d/m/Y",strtotime($fp));
					$arreglo[$i]['cantidadpenultima']=$c2;
					$arreglo[$i]['nomlin']=$linea->nombrexid($data[$i]['idlinea']);
                                        }
				$dataAgotados = $this->formatearparakui($arreglo);
				header("Content-type: application/json");
                echo json_encode($dataAgotados);
			}
		}
		//Stock de producto
		function StockProducto(){
			
			if(count($_REQUEST)==6){
				$linea=new Linea();
				$almacen=new Almacen();
				$data['Linea']=$linea->listadoLineas('idpadre=0');
				$data['Almacen']=$almacen->listadoAlmacen();
				$this->view->show('/reporte/stockproducto.phtml',$data);
			}else{
				$idAlmacen=$_REQUEST['idAlmacen'];
				$idLinea=$_REQUEST['idLinea'];
				$idSubLinea=$_REQUEST['idSubLinea'];
				$idProducto=$_REQUEST['idProducto'];
				$repote=new Reporte();
				$data=$repote->reporteStockProducto($idAlmacen,$idLinea,$idSubLinea,$idProducto);
				$unidadMedida=$this->unidadMedida();
				$totalStock=0;
				$data2=array();                            
				$i=0;
				for($i=0;$i<count($data);$i++){
					$data2[$i]['codigo']=$data[$i]['codigopa'];
					$data2[$i]['nompro']=$data[$i]['nompro'];
					$data2[$i]['nomalm']=$data[$i]['nomalm'];
					$data2[$i]['nomlin']=$data[$i]['nomlin'];
					$data2[$i]['preciolista']=$data[$i]['preciolista'];
					$data2[$i]['preciolistadolares']=$data[$i]['preciolistadolares'];
					$data2[$i]['unidadmedida']=$data[$i]['unidadmedida'];
					$data2[$i]['stockactual']=$data[$i]['stockactual'];
					$data2[$i]['stockdisponible']=($data[$i]['stockdisponible']);
					$totalStock+=$data[$i]['stockactual'];
				}
				$objeto = $this->formatearparakui($data2);
				header("Content-type: application/json");
				echo json_encode($objeto);
			}
		}
	//Reporte de orden de compra
		function ordenCompra(){
			if(count($_REQUEST)==6){
				$ordenCompra=new Ordencompra();
				/*$url="/".$_REQUEST['url'];
				$opciones=new general();
				$data['Opcion']=$opciones->buscaOpcionexurl($url);
				$data['Modulo']=$opciones->buscaModulosxurl($url);*/
				$data['Ordencompra']=$ordenCompra->listadoOrdenescompra();
				$this->view->show("/reporte/ordencompra.phtml",$data);	
			}else{
				$idProveedor = $_REQUEST['idProveedor'];
				$fecha = $_REQUEST['fecha'];
				$fechaInicio = $_REQUEST['fechaInicio'];
				$fechaFinal = $_REQUEST['fechaFinal'];
				$repote=new Reporte();
				$data=$repote->reporteOrdenCompra($idProveedor,$fecha,$fechaInicio,$fechaFinal);
				$data2=array();
				for($i=0;$i<count($data);$i++){
					$data2[$i]['codigooc']=$data[$i]['codigooc'];
					$data2[$i]['fordencompra']=date("d/m/Y",strtotime($data[$i]['fordencompra']));
					$data2[$i]['nomalm']=$data[$i]['nomalm'].'</td>';
					$data2[$i]['razonsocialp']=$data[$i]['razonsocialp'];
					$data2[$i]['fob']=$data[$i]['fob'];
				}
				$objeto = $this->formatearparakui($data2);
				header("Content-type: application/json"); 
				echo json_encode($objeto);
			}
		}
		
		//Reporte de stock valorizado
		function reporteStockValorizado(){
			if(count($_REQUEST)==6){
				$linea=new Linea();
				$data['Linea']=$linea->listadoLineas("idpadre=0");
				$this->view->show("/reporte/stockvalorizado.phtml",$data);
			}else{
				$idLinea=$_REQUEST['linea'];
				$idSubLinea=$_REQUEST['sublinea'];
				$reporte=new Reporte();
				$data=$reporte->reporteStockValorizado($idLinea,$idSubLinea);
				$total=0;
				$data2=array();
				for($i=0;$i<count($data);$i++){
					$data2[$i]['codigo']=$data[$i]['codigop'];
					$data2[$i]['nompro']=$data[$i]['nompro'];
					$data2[$i]['nomalm']=$data[$i]['nomalm'];
					$data2[$i]['nomlin']=$data[$i]['nomlin'];
					$data2[$i]['unidadmedida']=$data[$i]['unidadmedida'];
					$data2[$i]['stock']=$data[$i]['stockactual'];
					$data2[$i]['precio']=number_format($data[$i]['preciolista'],2);
					$data2[$i]['preciototal']=number_format(($data[$i]['stockactual']*$data[$i]['preciolista']),2);
					$total+=($data[$i]['stockactual']*$data[$i]['preciolista']);
				}
				$objeto = $this->formatearparakui($data2);
				header("Content-type: application/json"); 
				echo json_encode($objeto);
				//echo '<tr style="font-weight:bold"><td colspan="6"></td><td class="right">Total:</td><td class="right">'.number_format($total,2).'</td></tr>';
			}
		}

		function precioTotalStockValorizado(){
			$reporte=new Reporte();
			$idLinea=$_REQUEST['linea'];
			$idSubLinea=$_REQUEST['sublinea'];
			$suma=$reporte->totalesStockValorizado($idLinea,$idSubLinea);

		}

		//Reporte de ventas
		function ventas(){
			$linea = new Linea();
			$vendedor = new Actor();
			if(count($_REQUEST)==6){
				$tamanio=10;
				$ordenVenta = new OrdenVenta();
				$linea = new Linea();
				$vendedor = new Actor();
				$data['linea']=$linea->listadoLineas('idpadre=0');
				$data['vendedor']=$vendedor->listadoVendedoresTodos();
				$data['Paginacion']=$ordenVenta->Paginacion($tamanio);
				$data['Pagina']=1;
				$this->view->show('/reporte/ventas.phtml',$data);
			}else{
				$idLinea = $_REQUEST['linea'];
				$idVendedor = $_REQUEST['vendedor'];
				$fInicial = $_REQUEST['fechaInicial'];
				$fFinal = $_REQUEST['fechaFinal'];
				$ordenVenta = new OrdenVenta();
				$data = $ordenVenta->listadoReporteVentas($idLinea, $idVendedor, $fInicial, $fFinal);
				//$data = $ordenVenta->listadoReporteVentas($idLinea, $idVendedor, '2012/09/07', '2012/09/07');
				//$objeto = $this->formatearparakui($data);
				//header("Content-type: application/json"); 
				echo json_encode($data);
			}
		}
		function prueba(){
			$ordenVenta = new OrdenVenta();
			$data = $ordenVenta->listadoReporteVentas("6", "", "2012/08/01", "2012/09/05");
			$objeto = $this->formatearparakui($data);
			echo json_encode($objeto);
		}
		
		function letras(){
			if(count($_REQUEST)==6){
				$this->view->show("/reporte/letras.phtml");
			}else{
				$ordenventa=new OrdenVenta();
				$idcliente=$_REQUEST['id'];
				$dordenventa=$ordenventa->listadoOrdenVentaxidcliente2($idcliente);
				$total=count($dordenventa);
				$cuenta=array();
				$indice=0;
				for($i=0;$i<$total;$i++){
					$saldo=$ordenventa->saldoxidordenventa($dordenventa[$i]['idordenventa']);
					$fvencimiento=$ordenventa->ultimafechaxidordenventa($dordenventa[$i]['idordenventa']);
						$cuenta[$indice]['codigov'] = $dordenventa[$i]['codigov'];
						$cuenta[$indice]['importeov'] = number_format($dordenventa[$i]['importeov'],4);
						$cuenta[$indice]['idcondicionletra'] = $dordenventa[$i]['idcondicionletra'];
						$cuenta[$indice]['situacion'] = (($saldo>0)?'PENDIENTE':'CANCELADO');
						$cuenta[$indice]['fvencimiento'] = $fvencimiento;
						$cuenta[$indice]['saldo'] = number_format($saldo,4);
						$cuenta[$indice]['importedoc'] = "";
						$cuenta[$indice]['formacobro'] = "";
						$cuenta[$indice]['situacionc'] = "";
						$cuenta[$indice]['fvencimientoc'] = "";
						$indice+=1;
					$dcuenta=$ordenventa->cuentasxidordenventa($dordenventa[$i]['idordenventa']);
					$total2=count($dcuenta);
					for($j=0;$j<$total2;$j++){
						if($dcuenta[$j]['formacobro']==3){
							$cuenta[$indice]['codigov'] = $dordenventa[$i]['codigov'];
							$cuenta[$indice]['importeov'] = "";
							$cuenta[$indice]['idcondicionletra'] = "";
							$cuenta[$indice]['situacion'] = "";
							$cuenta[$indice]['fvencimiento'] = "";
							$cuenta[$indice]['saldo'] = "";
							$cuenta[$indice]['importedoc'] = number_format($dcuenta[$j]['importedoc'],4);
							$cuenta[$indice]['formacobro'] = (($dcuenta[$j]['formacobro']==1)?'CONTADO':(($dcuenta[$j]['formacobro']==4)?'LETRA':'CREDITO'));
							$cuenta[$indice]['situacionc'] = (($dcuenta[$j]['situacion']==0)?'PENDIENTE':(($dcuenta[$j]['situacion']==1)?'CANCELADO':'DESDOBLADO'));
							$cuenta[$indice]['fvencimientoc'] = $dcuenta[$j]['fvencimiento'];
							$indice+=1;	
						}
					}
				}
				$objeto = $this->formatearparakui($cuenta);
				header("Content-type: application/json");
				//echo "{\"data\":" .json_encode($objeto). "}";
				echo json_encode($objeto);
			}
		}

		function reportletras(){
			$zona=$this->AutoLoadModel('zona');
                      	$actor=$this->AutoLoadModel('actorrol');
			$tipoCobranza=$this->AutoLoadModel('tipocobranza');
			$data['padre']=$zona->listaCategoriaPrincipal();
			$data['hijo']=$zona->listacategoriaHijo();
			$data['zona']=$zona->listadoTotalZona();
			$data['tipocobranza']=$tipoCobranza->listaNueva();
			$data['vendedor']=$actor->actoresxRolxNombre(25);
			$data['cobrador']=$actor->actoresxRolxNombre(28);
			$this->view->show('/reporte/reportletras.phtml',$data);
		}
		function reporteletras(){
			$reporte=$this->AutoLoadModel('reporte');
			$tipo=$this->AutoLoadModel('tipocobranza');
			$ordenGasto=$this->AutoLoadModel('ordengasto');
			$tipoCobroIni=$this->configIniTodo('TipoCobro');
			$idzona=$_REQUEST['idzona'];
			$idcategoriaprincipal=$_REQUEST['idcategoriaprincipal'];
			$idcategoria=$_REQUEST['idcategoria'];
			$idvendedor=$_REQUEST['idvendedor'];
			$idtipocobranza=$_REQUEST['idtipocobranza'];
			$idtipocobro=$_REQUEST['idtipocobro'];
			$fechaInicio=$_REQUEST['fechaInicio'];
			$fechaFinal=$_REQUEST['fechaFinal'];
			$pendiente=$_REQUEST['pendiente'];
			$cancelado=$_REQUEST['cancelado'];
			$octava=$_REQUEST['octava'];
			$novena=$_REQUEST['novena'];
			$idcobrador=$_REQUEST['idcobrador'];
			$IdCliente=$_REQUEST['IdCliente'];
			$IdOrdenVenta=$_REQUEST['IdOrdenVenta'];

			$octavaNovena=" ";
			if (!empty($octava) && !empty($novena)) {
				$octavaNovena.=" and (wc_detalleordencobro.`fvencimiento`=DATE_SUB(CURDATE(), INTERVAL 8 DAY) or wc_detalleordencobro.`fvencimiento`=DATE_SUB(CURDATE(), INTERVAL 9 DAY)) and wc_detalleordencobro.`situacion`='' ";
			}elseif(!empty($novena)){
				
				$octavaNovena.=" and wc_detalleordencobro.`fvencimiento`=DATE_SUB(CURDATE(), INTERVAL 9 DAY) and wc_detalleordencobro.`situacion`='' ";
			}elseif (!empty($octava)) {
				
				$octavaNovena.=" and wc_detalleordencobro.`fvencimiento`=DATE_SUB(CURDATE(), INTERVAL 8 DAY) and wc_detalleordencobro.`situacion`='' ";
			}

			$situacion="";
			if (!empty($pendiente) && !empty($cancelado)) {
				$situacion.=" and (wc_detalleordencobro.`situacion`='' or wc_detalleordencobro.`situacion`='cancelado') ";
			}elseif(!empty($cancelado)){
				$situacion.=" and wc_detalleordencobro.`situacion`='cancelado' ";	
			}elseif (!empty($pendiente)) {
				$situacion.=" and wc_detalleordencobro.`situacion`='' ";
			}

			if ($_REQUEST['fechaInicio']!="") {
				$fechaInicio=date('Y-m-d',strtotime($_REQUEST['fechaInicio']));
			}
			$fechaFinal=$_REQUEST['fechaFinal'];
			if ($_REQUEST['fechaFinal']!="") {
				$fechaFinal=date('Y-m-d',strtotime($_REQUEST['fechaFinal']));
			}

			if ($_REQUEST['fechaPagoInicio']!="") {
				$fechaPagoInicio=date('Y-m-d',strtotime($_REQUEST['fechaPagoInicio']));
			}else{
				$fechaPagoInicio=$_REQUEST['fechaPagoInicio'];
			}
			
			if ($_REQUEST['fechaPagoFinal']!="") {
				$fechaPagoFinal=date('Y-m-d',strtotime($_REQUEST['fechaPagoFinal']));
			}else{
				$fechaPagoFinal=$_REQUEST['fechaPagoFinal'];
			}


			$idcategorias="";
			if (!empty($idcobrador)) {
				$cobrador=$this->AutoLoadModel('cobrador');
				$dataCobrador=$cobrador->buscaZonasxCobrador($idcobrador);
				
				$cantidadCobrador=count($dataCobrador);
				if ($cantidadCobrador!=0) {
					
					$idcategorias.=" and (";
					for ($i=0; $i <$cantidadCobrador ; $i++) { 
						if ($i==0) {
							$idcategorias.=" wc_categoria.`idcategoria`='".$dataCobrador[$i]['idzona']."' ";
						}else{
							$idcategorias.=" or wc_categoria.`idcategoria`='".$dataCobrador[$i]['idzona']."' ";
						}
						
					}
					$idcategorias.=" ) ";
				}else{
					$idcategorias.=" and  wc_categoria.`idcategoria`='0' ";
				}

			}elseif(!empty($idcategoria)){
				$idcategorias=" and wc_categoria.`idcategoria`='".$idcategoria."' ";
			}
			

			if ($idtipocobro==3) {
				$filtro="wc_detalleordencobro.`formacobro`='3' and wc_ordencobro.`tipoletra`=1";
			}elseif($idtipocobro==4){
				$filtro="wc_detalleordencobro.`formacobro`='3' and  wc_ordencobro.`tipoletra`=2";
			}elseif($idtipocobro==2){
				$filtro="wc_detalleordencobro.`formacobro`='2'";
			}elseif($idtipocobro==1){
				$filtro="wc_detalleordencobro.`formacobro`='1' ";
			}
			
			$totalPagado=0;
			$totalImporte=0;
			$importe=0;
			$totalDevolucion=0;
			$total=0;
			$TPagado=0;
			$cont=0;
			$fechaActual=date('Y-m-d');
			$datareporte=$reporte->reportletras($filtro,$idzona,$idcategoriaprincipal,$idcategorias,$idvendedor,$idtipocobranza,$fechaInicio,$fechaFinal,$octavaNovena,$situacion,$fechaPagoInicio,$fechaPagoFinal,$IdCliente,$IdOrdenVenta);

			$dataAnterior=$datareporte[-1]['idordenventa'];
			unset($percepcion);
			// echo "<pre>";
			// print_r($datareporte);
			// exit;
			echo "<thead>
					<tr>
						<th >Codigo</th>
						<th class='ocultarImpresion'>Vendedor</th>
						<th class='mostrarImpresion' style='display:none'>Ven</th>
						<th class='ocultarImpresion'>Zona Cobranza</th>
						<th class='ocultarImpresion'>Zona </th>
						<th>F. Des.</th>
						<th>F. venc.</th>
						<th>Cliente</th>
						<th>Total</th>
						<th>Pagado</th>
						<th>Devol.</th>
						<th>Deuda</th>
						<th class='ocultarImpresion'>Tipo Cobranza</th>
						<th>".date('d/m')."</th>
						<th>".date('d/m', strtotime("$fechaActual + 1 day"))."</th>
						<th>".date('d/m', strtotime("$fechaActual + 2 day"))."</th>
						<th>".date('d/m', strtotime("$fechaActual + 3 day"))."</th>
						<th>".date('d/m', strtotime("$fechaActual + 4 day"))."</th>
						<th>".date('d/m', strtotime("$fechaActual + 5 day"))."</th>
					</tr>
					<tr class='ocultarImpresion'><td colspan='10'>&nbsp;</td></tr>
				  </thead>
				  <tbody>";
				 $cantidadreporte=count($datareporte);
				 for ($i=0; $i <$cantidadreporte ; $i++) { 
				 	$simbolomoneda=$datareporte[$i]['simbolo'];

				 	if (strcasecmp($datareporte[$i]['situacion'],'')==0) {
						$color="style='color:red;text-align:right;'";	
						$total+=$datareporte[$i]['saldodoc'];	
					}else{
						$color="style='color:blue;text-align:right;'";
						$totalPagado+=$datareporte[$i]['importedoc']-$datareporte[$i]['saldodoc'];	
								
					}
					if ($dataAnterior!=$datareporte[$i]['idordenventa']) {
						$dataAnterior=$datareporte[$i]['idordenventa'];

						$dataTipoCobranza=$tipo->buscaxid($datareporte[$i]['idtipocobranza']);
						$tipocobranza=!empty($dataTipoCobranza[0]['nombre'])?$dataTipoCobranza[0]['nombre']:'Sin Asignar';
						$importe=$ordenGasto->totalGuia($datareporte[$i]['idordenventa']);
						$percepcion=$ordenGasto->ImporteGastoxIdDetalleOrdenCobro($datareporte[$i]['iddetalleordencobro']);
						// $totalImporte+=$importe;
						// $TPagado+=$datareporte[$i]['importepagado'];
						// $totalDevolucion+=$datareporte[$i]['importedevolucion'];

						$acumulaxIdMoneda[$simbolomoneda]['totalImporte']+=$importe;
						$acumulaxIdMoneda[$simbolomoneda]['TPagado']+=$datareporte[$i]['importepagado'];
						$acumulaxIdMoneda[$simbolomoneda]['totalDevolucion']+=$datareporte[$i]['importedevolucion'];
						$acumulaxIdMoneda[$simbolomoneda]['totalDeuda']=$acumulaxIdMoneda[$simbolomoneda]['totalImporte']-$acumulaxIdMoneda[$simbolomoneda]['TPagado'];

			echo 	"<tr style='border-radius:10px;background-color:rgb(124, 180, 224)'>
						 <td style='width:18mm'>".$datareporte[$i]['codigov']."</td>
						 <td class='ocultarImpresion'>".substr($datareporte[$i]['codigoa'].' '.$datareporte[$i]['apellidopaterno'].' '.$datareporte[$i]['apellidomaterno'].' '.$datareporte[$i]['nombres'],0,20 )."</td>
						 <td class='mostrarImpresion' style='display:none'>".$datareporte[$i]['codigoa']."</td>
						 <td class='ocultarImpresion'>".$datareporte[$i]['nombrec']."</td>
						 <td class='ocultarImpresion'>".$datareporte[$i]['nombrezona']."</td>
						 <td>".date('d/m/y',strtotime($datareporte[$i]['fechadespacho']))."</td>
						 <td>".date('d/m/y',strtotime($datareporte[$i]['fechavencimiento']))."</td>
						 <td style='width:36mm'>".$datareporte[$i]['razonsocial']."</td>
						 <td>".$simbolomoneda." ".number_format($importe,2)."</td>
						 <td>".$simbolomoneda." ".number_format($datareporte[$i]['importepagado'],2)."</td> 
						 <td>".$simbolomoneda." ".number_format($datareporte[$i]['importedevolucion'],2)."</td>
						 <td>".$simbolomoneda." ".number_format($importe-$datareporte[$i]['importepagado']-$datareporte[$i]['importedevolucion'],2)."</td>
						 <td class='ocultarImpresion'>".$tipocobranza."</td>
						 <td style='width:15mm;border:1px solid;'>&nbsp;</td>
						<td style='width:15mm;border:1px solid;'>&nbsp;</td>
						<td style='width:15mm;border:1px solid;'>&nbsp;</td>
						<td style='width:15mm;border:1px solid;'>&nbsp;</td>
						<td style='width:15mm;border:1px solid;'>&nbsp;</td>
						<td style='width:15mm;border:1px solid;'>&nbsp;</td>
					</tr>";
					

			echo  	"<tr  class='filaContenedor' style='padding-left:0px ;border:solid 1px;'>			
						<td colspan='18'>
							<table class='filaOculta' style='display:none;margin:0px'><tr><td colspan='15'><a class='ver' href='#'>&nbsp<img src='/imagenes/iconos/OrdenAbajo.gif'></a></td></tr></table>
							<table class='tblchildren' style='margin:0px;padding:0px;'>
								<thead>
									<tr class='ocultarImpresion'>
										<th style='width:70mm'>Direccion</th>
										
										<th style='width:30mm'>Estado</th>
										<th style='width:15mm'>Cond. Venta</th>
										<th style='width:10mm'>N° Letra</th>
										<th style='width:15mm'>F. Giro</th>
										<th style='width:15mm'>F. Ven.</th>
										<th style='width:15mm'>F. Can.</th>
										<th>N° Unico</th>
										<th>Indicador</th>
										<th>Importe</th>
										<th>Percepcion</th>
										<th>Saldo</th>
										<th>Situacion</th>
										<th style='width:25mm'>Referencia <a class='ocultar' style='margin-left:0px;' href='#'><img src='/imagenes/iconos/OrdenArriba.gif'></a></th>										
									</tr>
								</thead>
								<tbody>";
					}
			echo					"<tr style='margin-botton:none;'>";
										if ($cont==0) {
			echo						"<td >".$datareporte[$i]['direccion']."</td>";
										

										$cont++;								
										}else{
			echo						"<td >&nbsp;</td>";
										
										}

			echo						"
										<td ><h4>".strtoupper($tipo->NombreTipoCobranzaxDiasVencidos($datareporte[$i]['diffechas']))."</h4></td>
										<td style='text-align:center'>".$tipoCobroIni[$datareporte[$i]['formacobro']]."</td>
										<td >".($datareporte[$i]['numeroletra'])."</td>
										<td >".date('d/m/y',strtotime($datareporte[$i]['fechagiro']))."</td>
										<td >".date('d/m/y',strtotime($datareporte[$i]['fvencimiento']))."</td>
										<td >".$this->FechaFormatoCorto($datareporte[$i]['fechapago'])."</td>
										<td >".$datareporte[$i]['numerounico']."</td>
										<td >".$datareporte[$i]['recepcionletras']."</td>
										<td >".$simbolomoneda." ".number_format($datareporte[$i]['importedoc'],2)."</td>
										<td >".(!empty($percepcion)?($simbolomoneda." ".number_format($percepcion,2)):'')."</td>
										<td >".$simbolomoneda." ".number_format($datareporte[$i]['saldodoc'],2)."</td>
										<td >".($datareporte[$i]['situacion']==''?'Pendiente':$datareporte[$i]['situacion'])."</td>
										<td >".strtoupper($datareporte[$i]['proviene']." ".substr($datareporte[$i]['referencia'],0,11))."</td>										
										
									</tr>";
						if ($dataAnterior!=$datareporte[$i+1]['idordenventa']) {
							$cont=0;
			echo				"</tbody>
							</table>
						</td>
							
					</tr>";		
						}
				 	}
					 
			echo "</tbody>
				 <tfoot>
				 	<tr><th colspan='2' style='text-align:right;'>Total</th><td colspan='2'>S/. ".number_format($acumulaxIdMoneda['S/.']['totalImporte'],2)."</td><th colspan='2' style='text-align:right;'>Total Pagado</th><td colspan='2'>S/. ".number_format($acumulaxIdMoneda['S/.']['TPagado'],2)."</td><th  style='text-align:right;' colspan='2'>Total Devolucion</th><td style='text-align:right;' colspan='2'>S/. ".number_format($acumulaxIdMoneda['S/.']['totalDevolucion'],2)."</td ><th colspan='2'>Total Deuda</th><td colspan='3'>S/. ".number_format($acumulaxIdMoneda['S/.']['totalDeuda'],2)."</td></tr>
					<tr><th colspan='2' style='text-align:right;'>Total</th><td colspan='2'>US $. ".number_format($acumulaxIdMoneda['US $']['totalImporte'],2)."</td><th colspan='2' style='text-align:right;'>Total Pagado</th><td colspan='2'>US $ ".number_format($acumulaxIdMoneda['US $']['TPagado'],2)."</td><th  style='text-align:right;' colspan='2'>Total Devolucion</th><td style='text-align:right;' colspan='2'>US $ ".number_format($acumulaxIdMoneda['US $']['totalDevolucion'],2)."</td ><th colspan='2'>Total Deuda</th><td colspan='3'>US $ ".number_format($acumulaxIdMoneda['US $']['totalDeuda'],2)."</td></tr>
				 </tfoot>
				 ";
		}

		function reporteCreditos(){
			$reporte=$this->AutoLoadModel('reporte');
			$tipo=$this->AutoLoadModel('tipocobranza');
			$idzona=$_REQUEST['idzona'];
			$idcategoriaprincipal=$_REQUEST['idcategoriaprincipal'];
			$idcategoria=$_REQUEST['idcategoria'];
			$idvendedor=$_REQUEST['idvendedor'];
			$idtipocobro=$_REQUEST['idtipocobro'];
			$idtipocobranza=$_REQUEST['idtipocobranza'];
			$fechaInicio=$_REQUEST['fechaInicio'];
			$fechaFinal=$_REQUEST['fechaFinal'];
			$situacion=$_REQUEST['situacion'];
			$pendiente=$_REQUEST['pendiente'];
			$cancelado=$_REQUEST['cancelado'];
			$octava=$_REQUEST['octava'];
			$novena=$_REQUEST['novena'];

			$octavaNovena="";
			
			$situacion="";
			if (!empty($pendiente) && !empty($cancelado)) {
				$situacion.=" and (wc_detalleordencobro.`situacion`='' or wc_detalleordencobro.`situacion`='cancelado') ";
			}elseif(!empty($cancelado)){
				$situacion.=" and wc_detalleordencobro.`situacion`='cancelado' ";	
			}elseif (!empty($pendiente)) {
				$situacion.=" and wc_detalleordencobro.`situacion`='' ";
			}


			if ($_REQUEST['fechaInicio']!="") {
				$fechaInicio=date('Y-m-d',strtotime($fechaInicio));
			}
			
			if ($_REQUEST['fechaFinal']!="") {
				$fechaFinal=date('Y-m-d',strtotime($fechaFinal));
			}
			$titulo=$_REQUEST['titulo'];

			
			/*echo 'idzona: '.$idzona;
			echo 'idcategoriaprincipal: '.$idcategoriaprincipal;
			echo 'idcategoria: '.$idcategoria;	
			echo 'idvendedor: '.$idvendedor;
			echo 'idtipocobranza: '.$idtipocobranza;
			echo 'fechaInicio: '.$fechaInicio;
			echo 'fechaFinal: '.$fechaFinal;	
			exit;*/

			if (!empty($idtipocobranza)) {
				$dataTipoCobranza=$tipo->buscaxid($idtipocobranza);
				$titulo2=$dataTipoCobranza[0]['nombre'];
			}else{
				$titulo2='Todo';
			}
			
			

			if ($idtipocobro==1) {
				$filtro="wc_detalleordencobro.`formacobro`='1' ";
			}elseif($idtipocobro==2){
				$filtro="wc_detalleordencobro.`formacobro`='2' ";
			}


			$total=0;
			$gastosrenovacion=0;
			
			$datareporte=$reporte->reportletras($filtro,$idzona,$idcategoriaprincipal,$idcategoria,$idvendedor,$idtipocobranza,$fechaInicio,$fechaFinal,$octavaNovena,$situacion);
			
			echo "<thead>
				 <tr>
				 <tr>
				 	<th colspan=6>".$titulo."</th>
				 	<th colspan=5>".$titulo2."</th>
				 </tr>
					 <th>Vend.".$idvendedor."</th>
					 <th>Orden Venta</th>
					 <th>Z. Cobranza</th>
					 <th>Zona</th>
					 <th>F. Giro</th>
					 <th>F. Vencimiento</th>
					 <th>Cliente</th>
					 <th>Importe</th>
					 <th>Pago</th>
					 <th>Saldo</th>
					 <th>Situacion</th>
				 </tr>
				 </thead>
				 <tbody>";
				 $cantidadreporte=count($datareporte);
				 	for ($i=0; $i <$cantidadreporte ; $i++) { 
				 		if (strcasecmp($datareporte[$i]['situacion'],'')==0) {
							$color="style='color:red;text-align:right;'";
							$total+=$datareporte[$i]['saldodoc'];		
						}else{
							$color="style='color:red;text-align:right;'";
									
						}
						if ($datareporte[$i]['gastosrenovacion']==1) {
							$gastosrenovacion+=$datareporte[$i]['importedoc'];
						}
			echo 	"<tr>
					 	<td>".$datareporte[$i]['idactor']."</td>
					 	<td>".$datareporte[$i]['codigov']."</td>
					 	<td>".$datareporte[$i]['nombrec']."</td>
					 	<td>".$datareporte[$i]['nombrezona']."</td>
					 	<td>".$datareporte[$i]['fechagiro']."</td>
					 	<td>".$datareporte[$i]['fvencimiento']."</td>
					 	<td>".$datareporte[$i]['razonsocial']."</td>	
					 	<td>S/. ".number_format($datareporte[$i]['importedoc'],2)."</td>
					 	<td>S/. ".number_format(($datareporte[$i]['importedoc']-$datareporte[$i]['saldodoc']),2)."</td>
					 	<td ".$color.">S/. ".number_format($datareporte[$i]['saldodoc'],2)."</td>
					 	<td>".($datareporte[$i]['situacion']==''?'Pendiente':$datareporte[$i]['situacion'])."</td>
					 </tr>";	
				 	}
					 
			echo "</tbody>
				 <tfoot>
				 	<tr>
				 		<td colspan='3'>&nbsp</td>
				 		<th>Deuda Pendiente</th>
				 		<td>S/. ".number_format($total,2)."</td>
				 		<td>&nbsp</td>
				 	</tr>
				 </tfoot>
				 ";
			
		}
		function letraxCliente(){
			$reporte=$this->AutoLoadModel('reporte');

			$idtipocobro=$_REQUEST['tipoCobro'];
			$fechaInicio=$_REQUEST['fechaInicio'];
			$fechaFinal=$_REQUEST['fechaFinal'];
			$situacion=$_REQUEST['situacion'];
			$idcliente=$_REQUEST['idcliente'];

			if ($_REQUEST['fechaInicio']!="") {
				$fechaInicio=date('Y-m-d',strtotime($_REQUEST['fechaInicio']));
			}
			$fechaFinal=$_REQUEST['fechaFinal'];
			if ($_REQUEST['fechaFinal']!="") {
				$fechaFinal=date('Y-m-d',strtotime($_REQUEST['fechaFinal']));
			}

			if (empty($idtipocobro)) {
				$formacobro='';
				$filtro=" wc_cliente.`idcliente`='$idcliente' ";
			}

			elseif ($idtipocobro==1) {
				
				$filtro="wc_detalleordencobro.`formacobro`='1' and wc_cliente.`idcliente`='$idcliente' ";
			}elseif($idtipocobro==2){
				
				$filtro="wc_detalleordencobro.`formacobro`='2' and wc_cliente.`idcliente`='$idcliente' ";
			}elseif ($idtipocobro==3) {
				
				$filtro="wc_detalleordencobro.`formacobro`='3' and wc_ordencobro.`tipoletra`=1 and wc_cliente.`idcliente`='$idcliente' ";
			}elseif($idtipocobro==4){
				
				$filtro="wc_detalleordencobro.`formacobro`='3' and  wc_ordencobro.`tipoletra`=2 and wc_cliente.`idcliente`='$idcliente' ";
			}
			

			$total=0;
			if (strcasecmp($situacion,'pendiente')==0) {
				$situacion=" and wc_detalleordencobro.`situacion`=''";
			}elseif(strcasecmp($situacion,'cancelado')==0){
				$situacion=" and wc_detalleordencobro.`situacion`='cancelado'";
			}
			
			$datareporte=$reporte->reportclienteCobro($filtro,"","","","","",$fechaInicio,$fechaFinal,$situacion);

			// echo "<pre>";
			// print_r($datareporte);
			// exit;
			echo "<thead>
				 <tr>
					 <th>Vend.".$idvendedor."</th>
					 <th width='80px'>Orden Venta</th>
					 <th>Observaciones</th>
					 <th>Z. Cobranza</th>
					 <th>Zona</th>
					 <th width='80px'>F. Giro</th>
					 <th width='80px'>F. Vencimiento</th>
					 <th width='120px'>Tipo Cobro</th>
					 
					 <th>Numero Letra</th>
					 <th width='100px'>Importe</th>
					 <th width='100px'>Saldo</th>
					 <th>Situacion</th>
				 </tr>
				 </thead>
				 <tbody>";
				 $cantidadreporte=count($datareporte);
					for ($m=0; $m <$cantidadreporte ; $m++) { 
						$datareporteOV[]=$datareporte[$m]['codigov'];
						$contarOV=array_count_values($datareporteOV);
					}

				 $OVactual="";
			for ($i=0; $i <$cantidadreporte ; $i++) { 
				 		if ($datareporte[$i]['formacobro']==1) {
				 			$formacobro="Contado";
				 		}elseif($datareporte[$i]['formacobro']==2){
				 			$formacobro="Credito";
				 		}elseif($datareporte[$i]['formacobro']==3){
				 			$formacobro='Letra Banco';
				 		}elseif($datareporte[$i]['formacobro']==4){
				 			$formacobro='Letra Cartera';
				 		}

				 		if (strcasecmp($datareporte[$i]['situacion'],'')==0) {
							$color="style='color:red;text-align:right;'";
							$total+=$datareporte[$i]['saldodoc'];		
						}else{
							$color="style='color:blue;text-align:right;'";	
						}
						$acumula[$datareporte[$i]['simbolomoneda']]['importedoc']+=$datareporte[$i]['importedoc'];
						$acumula[$datareporte[$i]['simbolomoneda']]['saldodoc']+=$datareporte[$i]['saldodoc'];
						$OV=$datareporte[$i]['codigov'];
						if($OV!=$OVactual){
							$rowspan="rowspan=".$contarOV[$OV];
							$OVactual=$OV;
						}else{
							$rowspan="";
						}
						

			echo 	"<tr >";
			if(!empty($rowspan)){ echo "<td ".$rowspan.">".$datareporte[$i]['idactor']."</td>"; }
			if(!empty($rowspan)){ echo "<td ".$rowspan.">".$datareporte[$i]['codigov']."</td>"; }
			if(!empty($rowspan)){ echo "<td ".$rowspan.">".htmlspecialchars_decode($datareporte[$i]['observaciones'])."</td>"; }
			if(!empty($rowspan)){ echo "<td ".$rowspan.">".$datareporte[$i]['nombrec']."</td>"; }
			if(!empty($rowspan)){ echo "<td ".$rowspan.">".$datareporte[$i]['nombrezona']."</td>"; }
			echo "
					 	<td>".$datareporte[$i]['fechagiro']."</td>
					 	<td>".$datareporte[$i]['fvencimiento']."</td>
					 	<td>".$formacobro."</td>
					 	
					 	<td>".$datareporte[$i]['numeroletra']."</td>
					 	<td ".$color.">".$datareporte[$i]['simbolomoneda']." ".number_format($datareporte[$i]['importedoc'],2)."</td>
					 	<td ".$color.">".$datareporte[$i]['simbolomoneda']." ".number_format($datareporte[$i]['saldodoc'],2)."</td>
					 	<td>".($datareporte[$i]['situacion']==''?'Pendiente':$datareporte[$i]['situacion'])."</td>
					 </tr>";	
				 	}
					 
			echo "</tbody>
			<tfoot>
			 	<tr><td colspan='7'>&nbsp</td><th>Deuda Pendiente en S/.  :</th><td style='text-align:right;'> S/. ".number_format($acumula['S/.']['saldodoc'],2)."</td><td>&nbsp</td></tr>
			 	<tr><td colspan='7'>&nbsp</td><th>Deuda Pendiente en US $ :</th><td style='text-align:right;'>US $ ".number_format($acumula['US $']['saldodoc'],2)."</td><td>&nbsp</td></tr>
		 	</tfoot>
				 ";
			
		}

		function comisionVendedor(){
			
			$this->view->show("/reporte/comisionVendedor.phtml",$data);
		}

		function reporteCliente(){
			$this->view->show('/reporte/reporteCliente.phtml',$data);
		}
		function reporteingresos(){
			$data['tipoIngreso']=$this->configIniTodo('TipoIngreso');
			$this->view->show('/reporte/reporteingresos.phtml',$data);
		}

		function reporteVentas(){
			$zona=$this->AutoLoadModel('zona');
			$data['categoriaPrincipal']=$zona->listaCategoriaPrincipal();
			$data['condicionVenta']=$this->configIniTodo('TipoCobro');
			$this->view->show('/reporte/reporteventas.phtml',$data);
		}

		function reporteProductos(){
			$this->view->show('/reporte/reporteProductos.phtml',$data);
		}
		function reporteInventario(){
			$inventario=$this->AutoLoadModel('inventario');
			$bloques=$this->AutoLoadModel('bloques');
			$data['inventario']=$inventario->listado();
			$data['bloques']=$bloques->listado();
			$this->view->show('/reporte/reporteInventario.phtml',$data);
		}
		function reporteCobranza(){
			$zona=$this->AutoLoadModel('zona');
			$tipo=$this->AutoLoadModel('tipocobranza');
				
			$data['padre']=$zona->listaCategoriaPrincipal();
			$data['tipocobranza']=$tipo->lista();
			$this->view->show('/reporte/reporteCobranza.phtml',$data);
		}
		
		function kardexTotalxProducto(){
			$data['mes']=$this->meses();
			$this->view->show("/reporte/kardexTotalxProducto.phtml",$data);
		}
		
		function reporteOrdenCompra(){
			$this->view->show('/reporte/reporteOrdenCompra.phtml',$data);
			
		}
		function reporteCarteraClientes(){
			$zona=$this->AutoLoadModel('zona');
			$linea =$this->AutoLoadModel('linea');
			$data['linea']=$linea->listaLineas();
			$data['categoriaPrincipal']=$zona->listaCategoriaPrincipal();
			$data['condicionVenta']=$this->configIniTodo('TipoCobro');
			$data['situacion']=$this->configIniTodo('SituacionVenta');
			$this->view->show('/reporte/reporteCarteraClientes.phtml',$data);
				
		}
		function reporteHistorialVentasxProducto(){
			
			
			$this->view->show('/reporte/historialVentasxProducto.phtml',$data);
		}
		function reporteCobranzaxEmpresa(){
			$zona=$this->AutoLoadModel('zona');
			$tipo=$this->AutoLoadModel('tipocobranza');
			$empresa=$this->AutoLoadModel('almacen');
			$data['padre']=$zona->listaCategoriaPrincipal();
			$data['tipocobranza']=$tipo->lista();
			$data['empresa']=$empresa->listado();
			$this->view->show('/reporte/reporteCobranzaxEmpresa.phtml',$data);
		}
		function reporteUtilidadesComision(){
			$this->view->show('/reporte/reporteUtilidadesComision.phtml',$data);
		}
		function reporteFacturacion(){
			$this->view->show('/reporte/reporteFacturacion.phtml',$data);
		}
		function reporteKardexProduccion(){
			$this->view->show('/reporte/reporteKardexProduccion.phtml',$data);
		}
	}
?>

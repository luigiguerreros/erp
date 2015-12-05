<?php
Class creditoscontroller extends ApplicationGeneral{
	function AsignaPagos(){
		$_SESSION['Autenticado']=true;
		$this->view->show("creditos/form.phtml");
	}
	function retornarVentas(){
		$idordenventa=$_REQUEST['idordenventa'];
		$ordenventa=$this->AutoLoadModel('ordenventa');
		$detalleordenventa=$this->AutoLoadModel('detalleordenventa');
		$producto=$this->AutoLoadModel('producto');
		$data['mventas']='RETORNADO POR COBRANZAS';
		$data['mcobranzas']='';
		$data['malmacen']='';
		$data['vbventas']='-1';
		$data['vbalmacen']='-1';
		$data['vbcobranzas']='-1';
		$exito=$ordenventa->actualizaOrdenVenta($data,$idordenventa);
		if ($exito) {
			$dataOrdenVenta=$detalleordenventa->listaDetalleOrdenVentaYOrden($idordenventa);
			$cantidad=count($dataOrdenVenta);
			for ($i=0; $i <$cantidad ; $i++) { 
				$cantsolicitada=$dataOrdenVenta[$i]['cantsolicitada'];
				$cantidaddespachada=$dataOrdenVenta[$i]['cantdespacho'];
				$idproducto=$dataOrdenVenta[$i]['idproducto'];
				$iddetalleordenventa=$dataOrdenVenta[$i]['iddetalleordenventa'];
				$dataProducto=$producto->buscaProducto($idproducto);
				$stockdisponible=$dataProducto[0]['stockdisponible'];
				$nuevoStockDisponible=$stockdisponible+$cantidaddespachada-$cantsolicitada;
				$datos['stockdisponible']=$nuevoStockDisponible;
				$actualizar=$producto->actualizaProducto($datos,$idproducto);
			}
			if ($actualizar) {
				$datoOV['cantdespacho']=0;
				$datoOV['tipodescuento']=0;
				$datoOV['cantaprobada']=0;
				$datoOV['precioaprobado']=0;
				$datoOV['descuentoaprobado']=0;
				$datoOV['descuentoaprobadovalor']=0;
				$datoOV['descuentoaprobadotexto']='';
				$exito2=$detalleordenventa->actualizar($iddetalleordenventa,$datoOV);
				$dataRespuesta['verificacion']=true;

			}else{
				$dataRespuesta['verificacion']=false;
			}
			
		}else{
			$dataRespuesta['verificacion']=false;
		}
		echo json_encode($dataRespuesta);
	}
	/*Aprobaciones*/
	function autorizarCreditos(){
		if(!$_REQUEST['idOrdenVenta']){
			$orden=new OrdenVenta();
			$url="/".$_REQUEST['url'];
			$opciones=new general();
			$data['Opcion']=$opciones->buscaOpcionexurl($url);
			$data['Modulo']=$opciones->buscaModulosxurl($url);
			$data['ordenVenta']=$orden->pedidoxaprobar(3);
			$data['documento']=$this->tipoDocumento();
			$data['FormaPago']=$this->formaPago();
			$data['CondicionLetra']=$orden->condicionesletra();
			$data['TipoLetra']=$this->tipoLetra();
			$this->view->show("creditos/aprobarpedido.phtml",$data);

		}elseif(!empty($_REQUEST['idOrdenVenta'])){
			$id=$_REQUEST['idOrdenVenta'];
			$estadoOrden=$_REQUEST['estadoOrden'];
			$dataOrden=$_REQUEST['ordenVenta'];
		
			$dataDetalleOrdenVenta=$_REQUEST['DetalleOrdenVenta'];
			$creditoDias = $_REQUEST['creditoDias'];
			$montoContado = $_REQUEST['montoContado'];
			$montoTotal = $dataOrden['importeordencobro'];
			/*Datos para crear el movimiento*/
			$dm['idordenventa']=$id;
			$dm['conceptomovimiento']=1;
			$dm['tipomovimiento']=2;
			$dm['idtipooperacion']=1;
			$dm['essunat']=1;
			$dm['fechamovimiento']=date('Y/m/d');
			$ordenVenta=new OrdenVenta();
			$producto=new Producto();
			$movimiento=new Movimiento();
			$detalleMovimiento=new Detallemovimiento();
			$ordenCobro=new OrdenCobro();
			$detalleOrdenCobro = new detalleOrdenCobro();
			$condicionLetra = new CondicionLetra();
			$ordenGasto=$this->AutoLoadModel('ordengasto');
			$redondeo=$this->configIni('Globals','Redondeo');
			$montoPercepcion=0;
			
			if($dataOrden['idcondicionletra'] != ''){
				$dataCondicionLetra = $condicionLetra->buscaxId($dataOrden['idcondicionletra']);
			}
                        
			/*if (lstDocumento==1){
				$dataOrden['importeordencobro']=round($dataOrden['importeordencobro']*1.02,$redondeo);
				$montoPercepcion=round($dataOrden['importeordencobro']*0.02,$redondeo);
				$dataGasto['importegasto']=$montoPercepcion;
				$dataGasto['idordenventa']=$id;
				$dataGasto['idtipogasto']=6;
				$grabaGasto=$ordenGasto->graba($dataGasto);
				
				
			}*/

			/*Datos para la orden de cobro*/
			$dataOrden['saldoordencobro']=$dataOrden['importeordencobro'];
			$dataOrden['idordenventa']=$id;
			$dataOrden['femision']=date('Y/m/d');
			$dataOrden['numletras']=$dataCondicionLetra[0]['cantidadletra'];
			/*Datos para actualizar la orden de venta*/
			$dov['vbcreditos']=$estadoOrden;
			if ($estadoOrden!=1) {
				$dov['desaprobado']=1;
				$dov['fdesaprobado']=date('Y-m-d');
			}
			if ($estadoOrden==1) {
				$dov['faprobado']=date('Y-m-d');
				$dov['es_contado']=$dataOrden['escontado'];
				$dov['es_credito']=$dataOrden['escredito'];
				$dov['es_letras']=$dataOrden['esletras'];
				$dov['tipo_letra']=$dataOrden['tipoletra'];
			}
			
			$dov['importeov']=$dataOrden['importeordencobro'];
			$dov['mcreditos']=$_REQUEST['mensajeCreditos'];
			$dov['observaciones']=$_REQUEST['observacion'];

			$exito1=$ordenVenta->actualizaOrdenVenta($dov,$id);
			if ($estadoOrden==1) {
				
				$exito2=$movimiento->grabaMovimiento($dm);
				$exito3=$ordenCobro->grabaOrdencobro($dataOrden);
				
				$dataGasto['idordenventa']=$id;
				$dataGasto['importegasto']=round($dataOrden['importeordencobro']-$dataOrden['importeordencobro']/1.18,$redondeo);
				$dataGasto['idtipogasto']=7;
				$grabaGasto=$ordenGasto->graba($dataGasto);

				$dataGasto['idtipogasto']=9;
				$dataGasto['importegasto']=round($dataOrden['importeordencobro']/1.18,$redondeo);
				$grabaGasto=$ordenGasto->graba($dataGasto);
			}
			
			$productos=$_REQUEST['Producto'];
			$cantidad=count($productos);

			for ($i=0; $i <$cantidad ; $i++) { 
				if ($estadoOrden==2) {
					for ($i=0; $i <$cantidad ; $i++) { 
						//buscamos producto
						$idproducto=$productos[$i]['idproducto'];
						$dataProducto=$producto->buscaProductoxId($idproducto);
						$stockdisponibleA=$dataProducto[0]['stockdisponible'];
						$stockdisponibleN=$stockdisponibleA+$productos[$i]['cantdespacho'];
						$dataNuevo['stockdisponible']=$stockdisponibleN;
						//actualizamos es stockdisponible
						$exitoP=$producto->actualizaProducto($dataNuevo,$idproducto);
					}
				}
			}


			if($exito1 and $exito2 and $exito3 and $estadoOrden==1){
				foreach($dataDetalleOrdenVenta as $data){

					$stockNuevo=$data['stockactual']-$data['cantidad'];
					/*Datos para crear el detalle movimiento*/
					$ddm['idmovimiento']=$exito2;
					$idproducto=$ddm['idproducto']=$data['idproducto'];
					$ddm['pu']=$data['pu'];
					$pv=$producto->buscaProducto($idproducto);
					$ddm['preciovalorizado']=$pv[0]['preciocosto'];
					$ddm['cantidad']=$data['cantidad'];
					$ddm['importe']=$data['total'];
					$ddm['stockdisponibledm']=$stockNuevo;
					$ddm['stockactual']=$data['stockactual']-$data['cantidad'];
					$dataPro['stockactual']=$stockNuevo;
					if ($stockNuevo<=0) {
						$dataPro['esagotado']=1;
						$dataPro['fechaagotado']=date('Y-m-d');
					}
					
					$exito4=$producto->actualizaProducto($dataPro,$data['idproducto']);
					$exito5=$detalleMovimiento->grabaDetalleMovimieto($ddm);
				}
				if($exito4 and $exito5){
					//Disminuir el Saldo del Cliente:
					$clienteposicion=New Cliente();
					$idcliente=$clienteposicion->idclientexidordenventa($id);
					$exito_cp=$clienteposicion->restarSaldo($idcliente,$dataOrden['importeordencobro']);
					//
					$fechaActual = date('Y/m/d');
					//$ddoc = Data detalle orden cobro
					$ddoc['idordencobro']=$exito3;
					$ddoc['fvencimiento']=$fechaActual;
					$mContado = ($_REQUEST['montoContado']=='')?0:($_REQUEST['montoContado']);
					$mCredito = ($_REQUEST['montoCredito']=='')?0:($_REQUEST['montoCredito']);
					$mLetras = ($_REQUEST['montoLetras']=='')?0: ($_REQUEST['montoLetras']);
					$esContado = (!isset($dataOrden['escontado']))?0:1;
					$esCredito = (!isset($dataOrden['escredito']))?0:1;
					$esLetras = (!isset($dataOrden['esletras']))?0:1;
					
					if($esContado){
						$mContado=($esCredito+$esLetras>0)?$mContado:$dataOrden['importeordencobro'];
						$exito6 = $this->grabaContado($mContado,$exito3,$fechaActual,$id);
						$dataOrden['importeordencobro']-=$mContado;
					}
					if($esCredito){
						$mCredito=($esLetras>0)?$mCredito:$dataOrden['importeordencobro'];
						$exito7 = $this->grabaCredito($mCredito,$exito3,$fechaActual,$_REQUEST['creditoDias'],$id);
					}
					if($esLetras){
						$mLetras=($esCredito>0)?$mLetras:$dataOrden['importeordencobro'];

						$exito8=$this->grabaLetra($mLetras,$exito3,$fechaActual,$dataCondicionLetra,$id,$montoPercepcion);
					}
				}

				//graba el tiempor que demoro ser aprobado
				$ordenVentaDuracion=new ordenventaduracion();
				$DDA=$ordenVentaDuracion->listaOrdenVentaDuracion($id,"almacen");
				$dataDuracion['idordenventa']=$id;
				$intervalo=$this->date_diff(date('Y-m-d H:i:s',strtotime($DDA[0]['fechacreacion'])),date('Y-m-d H:i:s'));
				$dataDuracion['tiempo']=$intervalo;
				$dataDuracion['referencia']='credito';
				$exitoN=$ordenVentaDuracion->grabaOrdenVentaDuracion($dataDuracion);
					

				$ruta['ruta']="/creditos/autorizarcreditos";
				$this->view->show("ruteador.phtml",$ruta);
			}elseif($estadoOrden==2){
				$ruta['ruta']="/creditos/autorizarCreditos";
				$this->view->show("ruteador.phtml",$ruta);
			}
		}else{
			$ruta['ruta']="/creditos/autorizarCreditos";
			$this->view->show("ruteador.phtml",$ruta);
		}
	}
	function grabaContado($monto='',$idoc,$fechaActual,$id){
		$detalleOrdenCobro = new detalleOrdenCobro();
		$ordenVenta=$this->AutoLoadModel('ordenventa');
		if($monto!=''){
			$data['idordencobro']=$idoc;
			$data['fechagiro']=$fechaActual;
			$data['fvencimiento']=date('Y/m/d', strtotime($fechaActual));
			$data['importedoc']=$monto;
			$data['saldodoc']=$monto;
			$data['formacobro']=1;

			$od['fechavencimiento']=$data['fvencimiento'];
			$grabando=$ordenVenta->actualizaOrdenVenta($od,$id);

			return $detalleOrdenCobro->grabaDetalleOrdenVentaCobro($data);
		}
	}
	function grabaCredito($monto='',$idoc,$fechaActual,$creditoDias,$id){
		$detalleOrdenCobro = new detalleOrdenCobro();
		$ordenVenta=$this->AutoLoadModel('ordenventa');
		if($monto!=''){
			$data['idordencobro']=$idoc;
			$data['fechagiro']=$fechaActual;
			$data['importedoc']=$monto;
			$data['saldodoc']=$monto;
			$data['formacobro']=2;
			$data['fvencimiento']=date('Y/m/d', strtotime("$fechaActual + " . $creditoDias . " day"));

			$od['fechavencimiento']=$data['fvencimiento'];
			$grabando=$ordenVenta->actualizaOrdenVenta($od,$id);

			return $detalleOrdenCobro->grabaDetalleOrdenVentaCobro($data);
		}
	}
	function grabaLetra($monto='',$idoc,$fechaActual,$dataCondicionLetra,$id,$montoPercepcion){
		//GENERANDO LA LETRA:
		$documento=$this->AutoLoadModel('documento');
		$ordenVenta=$this->AutoLoadModel('ordenventa');
		$detalleOrdenCobro = new detalleOrdenCobro();
		if($monto!=''){
			$ddoc['idordencobro']=$idoc;			
			$diasLetra = split('/', $dataCondicionLetra[0]['nombreletra']);
			$numeroletra = $dataCondicionLetra[0]['cantidadletra'];
			$exito=false;
			for($i=0;$i<$numeroletra;$i++){
				$nrodias=2;//(($detalleOrdenCobro->fechagironrodias($idoc))==1?2:5);

				$ddoc['fechagiro']=date('Y/m/d', strtotime("$fechaActual + " . $nrodias . " day"));
				$ddoc['formacobro']=3;
				$monto=$monto-$montoPercepcion;
				if ($i==0) {
					$ddoc['importedoc']=round($monto/$numeroletra,2) + $montoPercepcion;
					$ddoc['saldodoc']=round($monto/$numeroletra,2) + $montoPercepcion;
				}else{
					$ddoc['importedoc']=round($monto/$numeroletra,2);
					$ddoc['saldodoc']=round($monto/$numeroletra,2);
				}
				$ddoc['numeroletra']=$detalleOrdenCobro->GeneraNumeroLetra();
				$ddoc['fvencimiento']=date('Y/m/d', strtotime("$fechaActual + " . $diasLetra[$i] . " day"));
				$exito= $detalleOrdenCobro->grabaDetalleOrdenVentaCobro($ddoc);
				//Generando los documentos de las letras
				$datadocumentos['idordenventa']=$id;
				$datadocumentos['fechadoc']=$ddoc['fechagiro'];
				$datadocumentos['numdoc']=$ddoc['numeroletra'];
				$datadocumentos['serie']=1;
				$datadocumentos['montofacturado']=$ddoc['importedoc'];
				$datadocumentos['nombredoc']=7;
				$graba=$documento->grabaDocumento($datadocumentos);
				if(!$exito || !$graba)
					break;

				if ($i==$numeroletra-1) {
					$od['fechavencimiento']=$ddoc['fvencimiento'];
					$grabando=$ordenVenta->actualizaOrdenVenta($od,$id);
				}
			}
			return $exito;
		}
	}

}

?>
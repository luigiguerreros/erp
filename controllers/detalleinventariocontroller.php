<?php
	class detalleinventariocontroller extends ApplicationGeneral{
		

		function grabarInventarioPart1(){
			$movimiento=$this->AutoLoadModel('movimiento');
			$detalleInventario=$this->AutoLoadModel('detalleinventario');

			$estado=$_REQUEST['estado'];
			$idProducto=$_REQUEST['idProducto'];
			$productoMalo=$_REQUEST['productoMalo'];
			$productoVitrina=$_REQUEST['productoVitrina'];
			$productoServicio=$_REQUEST['productoServicio'];
			$idInventario=$_REQUEST['idInventario'];
			$idBloque=$_REQUEST['idBloque'];

								
			$data['idinventario']=$idInventario;
			$data['idproducto']=$idProducto;
			$data['idbloque']=$idBloque;
			$data['malos']=$productoMalo;
			$data['servicio']=$productoServicio;
			$data['showroom']=$productoVitrina;

			$graba=$detalleInventario->graba($data);	
			if ($graba) {
				$dataRespuesta['exito']=true;
			}else{
				$dataRespuesta['exito']=false;
			}
			echo json_encode($dataRespuesta);
		}

		function verificarBloque(){
			$detalleInventario=$this->AutoLoadModel('detalleinventario');

			$idProducto=$_REQUEST['idProducto'];
			$idInventario=$_REQUEST['idInventario'];
			$idBloque=$_REQUEST['idBloque'];
			
			$filtro="idproducto='$idProducto' and  idinventario='$idInventario' and estado=1";						
			$data=$detalleInventario->buscaxfiltro($filtro);	
			if (empty($data)) {
				$dataRespuesta['exito']=true;
			}else{
				$dataRespuesta['exito']=false;
			}
			echo json_encode($dataRespuesta);
		}

		function guardaBuenos(){
			$detalleInventario=$this->AutoLoadModel('detalleinventario');
			$movimiento=$this->AutoLoadModel('movimiento');
			$detalleMovimiento=$this->AutoLoadModel('detallemovimiento');
			$producto=$this->AutoLoadModel('producto');
			$idDetalleInventario=$_REQUEST['idDetalleInventario'];
			$idProducto=$_REQUEST['idProducto'];
			$responsable=$_REQUEST['responsable'];
			$auxiliar=$_REQUEST['auxiliar'];
			$horaInicio=$_REQUEST['horaInicio'];
			$horaTermino=$_REQUEST['horaTermino'];
			$malos=$_REQUEST['malos'];
			$buenos=$_REQUEST['buenos'];
			$servicio=$_REQUEST['servicio'];
			$showroom=$_REQUEST['showroom'];

			$dataProducto=$producto->buscaProducto($idProducto);

			$data['responsable']=$responsable;
			$data['auxiliar']=$auxiliar;
			$data['horaInicio']=$horaInicio;
			$data['horaTermino']=$horaTermino;
			$data['buenos']=$buenos;
			$data['usuariograbacion']=$_SESSION['idactor'];
			$data['stockactual']=$dataProducto[0]['stockactual'];


			$exito=$detalleInventario->actualiza($data,$idDetalleInventario);

			if ($exito) {
				
				$stockActual=$dataProducto[0]['stockactual'];
				$cantidadNueva=$malos+$servicio+$showroom+$buenos-$stockActual;
				if ($cantidadNueva>0) {
					$dataI['tipomovimiento']=1;
					$dataI['idtipooperacion']=12;
					$dataI['observaciones']="Inventario diferencia a favor";
					$dataI['fechamovimiento']=date('Y-m-d');
					$dataI['essunat']="0";
					
					$graba=$movimiento->grabaMovimiento($dataI);
					if ($graba) {
						$dataDet['idmovimiento']=$graba;
						$dataDet['idproducto']=$idProducto;
						$dataDet['cantidad']=$cantidadNueva;
						$dataDet['importe']=$cantidadNueva*$dataProducto[0]['preciocosto'];
						$dataDet['stockactual']=$dataProducto[0]['stockactual']+$cantidadNueva;
						$dataDet['pu']=$dataProducto[0]['preciocosto'];
						$dataDet['preciovalorizado']=$dataProducto[0]['preciocosto'];
						$grabaDet=$detalleMovimiento->grabaDetalleMovimieto($dataDet);

						$nuevoStock=$dataProducto[0]['stockactual']+$cantidadNueva;
						$nuevoStockDis=$dataProducto[0]['stockactual']+$cantidadNueva;
						$dataP['stockactual']=$nuevoStock;
						$dataP['stockdisponible']=$nuevoStockDis;
						$exito=$producto->actualizaProducto($dataP,$idProducto);
					} 
				}elseif($cantidadNueva<0){
					$dataI['tipomovimiento']=2;
					$dataI['idtipooperacion']=8;
					$dataI['observaciones']="Inventario diferencia en contra";
					$dataI['fechamovimiento']=date('Y-m-d');
					$dataI['essunat']="0";
					
					$graba=$movimiento->grabaMovimiento($dataI);
					if ($graba) {
						$dataDet['idmovimiento']=$graba;
						$dataDet['idproducto']=$idProducto;
						$dataDet['cantidad']=abs($cantidadNueva);
						$dataDet['importe']=abs($cantidadNueva)*$dataProducto[0]['preciocosto'];
						$dataDet['stockactual']=$dataProducto[0]['stockactual']-abs($cantidadNueva);
						$dataDet['pu']=$dataProducto[0]['preciocosto'];
						$dataDet['preciovalorizado']=$dataProducto[0]['preciocosto'];
						$grabaDet=$detalleMovimiento->grabaDetalleMovimieto($dataDet);

						$nuevoStock=$dataProducto[0]['stockactual']-abs($cantidadNueva);
						$nuevoStockDis=$dataProducto[0]['stockactual']-abs($cantidadNueva);
						$dataP['stockactual']=$nuevoStock;
						$dataP['stockdisponible']=$nuevoStockDis;
						$exito=$producto->actualizaProducto($dataP,$idProducto);
					} 
				}elseif($cantidadNueva==0){
					$graba=true;
					$nuevoStock=$dataProducto[0]['stockactual'];
					$nuevoStockDis=$dataProducto[0]['stockactual'];
				}
				


				//para producto en mal estado(merma)
				if ($malos>0) {
					$dataIM['tipomovimiento']=2;
					$dataIM['idtipooperacion']=8;
					$dataIM['observaciones']="Inventario mal estado";
					$dataIM['fechamovimiento']=date('Y-m-d');
					$dataIM['essunat']="0";
					
					$grabaM=$movimiento->grabaMovimiento($dataIM);
					if ($grabaM) {
						$dataDetM['idmovimiento']=$grabaM;
						$dataDetM['idproducto']=$idProducto;
						$dataDetM['cantidad']=$malos;
						$dataDetM['importe']=$malos*$dataProducto[0]['preciocosto'];
						$dataDetM['stockactual']=$nuevoStock-$malos;
						$dataDetM['pu']=$dataProducto[0]['preciocosto'];
						$dataDetM['preciovalorizado']=$dataProducto[0]['preciocosto'];
						$grabaDetM=$detalleMovimiento->grabaDetalleMovimieto($dataDetM);

						$nuevoStockM=$nuevoStock-$malos;
						$nuevoStockDisM=$nuevoStockDis- $malos;
						$dataPM['stockactual']=$nuevoStockM;
						$dataPM['stockdisponible']=$nuevoStockDisM;
						$exitoM=$producto->actualizaProducto($dataPM,$idProducto);
					}
				}else{
					$grabaM=true;
					$nuevoStockM=$nuevoStock;
					$nuevoStockDisM=$nuevoStockDis;
				}
				

				//para los producto que estan en servicio
				if ($servicio>0) {
					$dataIS['tipomovimiento']=2;
					$dataIS['idtipooperacion']=8;
					$dataIS['observaciones']="Inventario servicio";
					$dataIS['fechamovimiento']=date('Y-m-d');
					$dataIS['essunat']="0";
					
					$grabaS=$movimiento->grabaMovimiento($dataIS);
					if ($grabaS) {
						$dataDetS['idmovimiento']=$grabaS;
						$dataDetS['idproducto']=$idProducto;
						$dataDetS['cantidad']=$servicio;
						$dataDetS['importe']=$servicio*$dataProducto[0]['preciocosto'];
						$dataDetS['stockactual']=$nuevoStockM-$servicio;
						$dataDetS['pu']=$dataProducto[0]['preciocosto'];
						$dataDetS['preciovalorizado']=$dataProducto[0]['preciocosto'];
						$grabaDetS=$detalleMovimiento->grabaDetalleMovimieto($dataDetS);

						$nuevoStockS=$nuevoStockM-$servicio;
						$nuevoStockDisS=$nuevoStockDisM-$servicio;
						$dataPS['stockactual']=$nuevoStockS;
						$dataPS['stockdisponible']=$nuevoStockDisS;
						$exitoS=$producto->actualizaProducto($dataPS,$idProducto);
					}
				}else{
					$grabaS=true;
					$nuevoStockS=$nuevoStockM;
					$nuevoStockDisS=$nuevoStockDisM;
				}
				

				//para los producto que estan designados para showroom
				if ($showroom>0) {
					$dataIR['tipomovimiento']=2;
					$dataIR['idtipooperacion']=8;
					$dataIR['observaciones']="Inventario showroom";
					$dataIR['fechamovimiento']=date('Y-m-d');
					$dataIR['essunat']="0";
					
					$grabaR=$movimiento->grabaMovimiento($dataIR);
					if ($grabaR) {
						$dataDetR['idmovimiento']=$grabaR;
						$dataDetR['idproducto']=$idProducto;
						$dataDetR['cantidad']=$showroom;
						$dataDetR['importe']=$showroom*$dataProducto[0]['preciocosto'];
						$dataDetR['stockactual']=$nuevoStockS-$showroom;
						$dataDetR['pu']=$dataProducto[0]['preciocosto'];
						$dataDetR['preciovalorizado']=$dataProducto[0]['preciocosto'];
						$grabaDetR=$detalleMovimiento->grabaDetalleMovimieto($dataDetR);

						$nuevoStockR=$nuevoStockS-$showroom;
						$nuevoStockDisR=$nuevoStockDisS-$showroom;
						$dataPR['stockactual']=$nuevoStockR;
						$dataPR['stockdisponible']=$nuevoStockDisR;
						$exitoR=$producto->actualizaProducto($dataPR,$idProducto);
					}
				}else{
					$grabaR=true;
				}
				

				if ($graba && $grabaS && $grabaM && $grabaR) {
					$dataRespuesta['verificacion']=true;
				}else{
					$dataRespuesta['verificacion']=false;
				}
			}else{
				$dataRespuesta['verificacion']=false;
			}
			echo json_encode($dataRespuesta);

		}

		function cargaDetalleInvetario(){
			$detalleInventario=$this->AutoLoadModel('reporte');
			$bloques=$this->AutoLoadModel('bloques');
			$idProducto=$_REQUEST['idProducto'];
			$idInventario=$_REQUEST['idInventario'];

			$data=$detalleInventario->reporteInventario($idInventario,"",$idProducto);
			$dataBloques=$bloques->listado();
	
			$cantidadBloques=count($dataBloques);
			$cantidadDetalle=count($data);
			$fila="";
			if ($cantidadDetalle>0) {
				if ($data[0]['usuariograbacion']==0) {
					$fila.="<tr>";
					$fila.="<th>N°</th>";
					$fila.="<th>Codigo</th>";
					$fila.="<th>Descripcion</th>";
					$fila.="<th>Inventario</th>";
					$fila.="<th>Bloque</th>";
					$fila.="<th>Malo</th>";
					$fila.="<th>Servicio</th>";
					$fila.="<th>Show Room</th>";
					$fila.="<th>Total</th>";
					$fila.="<th>Acciones</th>";
					$fila.="</tr>";	
				
					$fila.="<tr>";
					$fila.="<td>".(1)."<input type='hidden' value='".$data[0]['iddetalleinventario']."' class='id'><input type='hidden' value='".$data[0]['idproducto']."' class='idProducto'></td>";
					$fila.="<td>".$data[0]['codigopa']."</td>";
					$fila.="<td>".$data[0]['nompro']."</td>";
					$fila.="<td>".$data[0]['codigoinv']."</td>";

					$fila.="<td>";
					$fila.="<select class='lstBloque'>";
					for ($i=0; $i <$cantidadBloques ; $i++) { 
						if ($dataBloques[$i]['idbloque']==$data[0]['idbloque']) {
							$fila.="<option selected value='".$dataBloques[$i]['idbloque']."'>".$dataBloques[$i]['codigo']."</option>";
						}else{
							$fila.="<option  value='".$dataBloques[$i]['idbloque']."'>".$dataBloques[$i]['codigo']."</option>";
						}
					}
					
					
					$fila.="</select>";
					$fila.="</td>";

					$fila.="<td><input type='text' size='4px' class='malos numeric' value=".$data[0]['malos']."  ></td>";
					$fila.="<td><input type='text' size='4px' class='servicio numeric' value=".$data[0]['servicio']."  ></td>";
					$fila.="<td><input type='text' size='4px' class='showroom numeric' value=".$data[0]['showroom']."  ></td>";
					$fila.="<td><input type='text' size='4px' class='total numeric' value=".($data[0]['malos']+$data[0]['servicio']+$data[0]['showroom'])." style='border:none' readonly='readonly'></td>";
					$fila.="<td><a href='#'  class='btnGrabar' title='Grabar'><img style='display:block;margin:auto' src='/imagenes/grabar.gif' width='25' height='25' ></a></td>";
					$fila.="</tr>";	
				}else{
					$fila.="<th>El Producto ya no puede editarse</th>";
				}
				
			}else{
				$fila.="<th>El Producto no tiene bloque asignado</th>";
			}
			echo $fila;
		}

		function actualizaDetalle(){
			$detalleInventario=$this->AutoLoadModel('detalleinventario');
			$idDetalleInventario=$_REQUEST['idDetalleInventario'];
			$malos=$_REQUEST['malos'];
			$servicio=$_REQUEST['servicio'];
			$showroom=$_REQUEST['showroom'];
			$idBloque=$_REQUEST['idBloque'];


			$data['malos']=$malos;
			$data['servicio']=$servicio;
			$data['showroom']=$showroom;
			$data['idbloque']=$idBloque;

			$exito=$detalleInventario->actualiza($data,$idDetalleInventario);
			if ($exito) {
				$dataRespuesta['exito']=true;
			}else{
				$dataRespuesta['exito']=false;
			}
			echo json_encode($dataRespuesta);
		}
	}

	
?>
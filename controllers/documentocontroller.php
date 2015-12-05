<?php 
	class documentocontroller extends ApplicationGeneral{

		function listaDocumentos(){
			$model=$this->AutoLoadModel('documento');
			$pagina=$_REQUEST['id'];
			if (empty($_REQUEST['id'])) {
				$pagina=1;
			}
			session_start();
			$_SESSION['P_Documento']="";
			
			$Factura=$model->listaDocumentosPaginado("","",$pagina);

			$data['Factura']=$Factura;
			$paginacion=$model->paginadoDocumentos("","");
			$data['paginacion']=$paginacion;
			$data['blockpaginas']=round($paginacion/10);
		
			$this->view->show('/documento/listaDocumentos.phtml',$data);
		}
		function buscaDocumentos(){
			$model=$this->AutoLoadModel('documento');
			$pagina=$_REQUEST['id'];
			if (empty($_REQUEST['id'])) {
				$pagina=1;
			}
			session_start();
			if (!empty($_REQUEST['txtBusqueda'])) {
				$_SESSION['P_Documento']=$_REQUEST['txtBusqueda'];
			}		
			$parametro=$_SESSION['P_Documento'];
			
			$Factura=$model->listaDocumentosPaginado("","",$pagina,$parametro);
			
			
			$data['Factura']=$Factura;
			$paginacion=$model->paginadoDocumentos("","",$parametro);
			$data['retorno']=$parametro;
			$data['paginacion']=$paginacion;
			$data['blockpaginas']=round($paginacion/10);
			$data['totregistros']=$model->cuentaDocumentos("","",$parametro);
		
			
			$this->view->show('/documento/buscaDocumentos.phtml',$data);
		}
		function editarDocumento(){
			$id=$_REQUEST['id'];
			
			$cont=0;
			$documento=$this->AutoLoadModel('documento');
			$datadoc=$documento->buscaDocumento($id,"");

			$filtro="iddocumento !='".$datadoc[0]['iddocumento']."' and idordenventa='".$datadoc[0]['idordenventa']."'";
			$databusqueda=$documento->buscaDocumento("",$filtro);
			$datatipo=$this->tipoDocumento();
			$dataNuevo=array();

			foreach ($datatipo as $key=>$datos) { 
				$cont++;
				if (count($databusqueda)==0) {
					$dataNuevo=$datatipo;
				}else{
					for ($x=0; $x <count($databusqueda) ; $x++) { 
					
					if ($key!=$databusqueda[$x]['nombredoc']) {
						
						$dataNuevo[$cont]=$datos;
					}else{
						$dataNuevo[$cont]="";
					}
				}
				}
				
			}
			$data['ModoFacturacion']=$this->modoFacturacion();
			$data['documento']=$datadoc;
			$data['tipodocumento']=$dataNuevo;


			$this->view->show('/documento/editarDocumento.phtml',$data);
		}

		function actualizaDocumento(){
			$documento=$this->AutoLoadModel('documento');
			$orden=$this->AutoLoadModel('ordenventa');
			$data=$_REQUEST['documento'];
			
			$databusqueda=$documento->buscaDocumento($data['iddocumento'],"");
			$idordenventa=$databusqueda[0]['idordenventa'];

			if ($data['nombredoc']==1) {
			
				$dataorden['esfacturado']=1;	
				$exitoN=$orden->actualizaOrdenVenta($dataorden,$idordenventa);
			}elseif($databusqueda[0]['nombredoc']==1){
				$dataorden['esfacturado']=0;	
				$exitoN=$orden->actualizaOrdenVenta($dataorden,$idordenventa);
			}

			$data['esimpreso']=0;
			$filtro="iddocumento='".$data['iddocumento']."'";
			$exito=$documento->actualizarDocumento($data,$filtro);
			if ($exito) {
				$ruta['ruta']='/documento/listaDocumentos/';
				$this->view->show('ruteador.phtml',$ruta);
			}else{
				$ruta['ruta']='/documento/actualizaDocumento/'.$data['iddocumento'];
				$this->view->show('ruteador.phtml',$ruta);
			}
			
		}
		function imprimirDocumento(){
			$documento=$this->AutoLoadModel('documento');
			$id=$_REQUEST['id'];
			$datos=$documento->buscaDocumento($id,"");
			$tipodocumento=$datos[0]['nombredoc'];
			$data['tipodocumento']=$this->tipoDocumento();
			if ($tipodocumento==1) {
				$datos[0]['title']='Numero Guia de Remision';
				$datos[0]['action']='/reporte/generaFactura/'.$id;
				$datos[0]['tipo']=$tipodocumento;
			}elseif($tipodocumento==2){
				$datos[0]['title']='Numero Guia de Remision';
				$datos[0]['action']='/pdf/generaBoleta/';
				$datos[0]['tipo']=$tipodocumento;
			}elseif ($tipodocumento==3) {
				$datos[0]['title']='';
				$datos[0]['action']='';
				$datos[0]['tipo']=$tipodocumento;
			}elseif ($tipodocumento==4) {
				$datos[0]['title']='Numero Documento';
				$datos[0]['action']='/reporte/generaGuia/'.$id;
				$datos[0]['tipo']=$tipodocumento;
			}elseif ($tipodocumento==5) {
				$datos[0]['title']='';
				$datos[0]['action']='';
				$datos[0]['tipo']=$tipodocumento;
			}elseif ($tipodocumento==6) {
				$datos[0]['title']='';
				$datos[0]['action']='';
				$datos[0]['tipo']=$tipodocumento;
			}elseif ($tipodocumento==7) {
				$datos[0]['title']='';
				$datos[0]['action']='';
				$datos[0]['tipo']=$tipodocumento;
			}
				
			$data['documento']=$datos;
			$this->view->show('/documento/imprimir.phtml',$data);
		}
		
		function documentosxordenventa(){
			$documento=$this->AutoLoadModel('documento');
			$idordenventa=$_REQUEST['id'];

			$filtro=" nombredoc=1 ";
			$datadocumento=$documento->buscadocumentoxordenventa($idordenventa,$filtro);
			$cantidaddata=count($datadocumento);

			$filtro2=" nombredoc=2 ";
			$datadocumento2=$documento->buscadocumentoxordenventa($idordenventa,$filtro2);
			$cantidaddata2=count($datadocumento2);

			$filtro3=" nombredoc=4 ";
			$datadocumento3=$documento->buscadocumentoxordenventa($idordenventa,$filtro3);
			$cantidaddata3=count($datadocumento3);

			$filtro5=" nombredoc=5 ";
			$datadocumento5=$documento->buscadocumentoxordenventa($idordenventa,$filtro5);
			$cantidaddata5=count($datadocumento5);

			$filtro6=" nombredoc=6 ";
			$datadocumento6=$documento->buscadocumentoxordenventa($idordenventa,$filtro6);
			$cantidaddata6=count($datadocumento6);
			
			echo "<tr><th colspan=5><h3>DETALLE DE DOCUMENTOS ASOCIADOS AL PEDIDO: </h3></th></tr>";
			for ($i=0; $i <$cantidaddata ; $i++) { 

				echo "<tr>"; 
				echo "<th>N° Factura </th>";
				$serie=str_pad($datadocumento[$i]['serie'],3,'0',STR_PAD_LEFT);
				$ultimo=$datadocumento[$i]['numdoc']+$datadocumento[$i]['CantidadDocumentos']-1;
				echo "<td>".$serie.'-'.$datadocumento[$i]['numdoc'].($datadocumento[$i]['CantidadDocumentos']>0?(" al ".$serie.'-'.$ultimo):"")."</td>";
				echo "<th>Estado </th>";
				echo "<td>".($datadocumento[$i]['esAnulado']==1?'Anulado':'Activo')."</td>";
				echo "<td>".($datadocumento[$i]['esImpreso']==1?'Impreso':'No Impreso')."</td>";
				echo "</tr>";
			}

			for ($x=0; $x <$cantidaddata2 ; $x++) { 
				echo "<tr>"; 
				echo "<th>N° Boleta </th>";
				$serie=str_pad($datadocumento2[$x]['serie'],3,'0',STR_PAD_LEFT);
				$ultimo=$datadocumento2[$x]['numdoc']+$datadocumento2[$x]['CantidadDocumentos']-1;
				echo "<td>".$serie.'-'.$datadocumento2[$x]['numdoc'].($datadocumento2[$x]['CantidadDocumentos']>0?(" al ".$serie.'-'.$ultimo):"")."</td>";
				echo "<th>Estado </th>";
				echo "<td>".($datadocumento2[$x]['esAnulado']==1?'Anulado':'Activo')."</td>";
				echo "<td>".($datadocumento2[$x]['esImpreso']==1?'Impreso':'No Impreso')."</td>";
				echo "</tr>";
			}

			for ($y=0; $y <$cantidaddata3 ; $y++) { 
				echo "<tr>"; 
				echo "<th>N° Guia Remision </th>";
				$serie=str_pad($datadocumento3[$y]['serie'],3,'0',STR_PAD_LEFT);
				$ultimo=$datadocumento3[$y]['numdoc']+$datadocumento3[$y]['CantidadDocumentos']-1;
				echo "<td>".$serie.'-'.$datadocumento3[$y]['numdoc'].($datadocumento3[$y]['CantidadDocumentos']>0?(" al ".$serie.'-'.$ultimo):"")."</td>";
				echo "<th>Estado </th>";
				echo "<td>".($datadocumento3[$y]['esAnulado']==1?'Anulado':'Activo')."</td>";
				echo "<td>".($datadocumento3[$y]['esImpreso']==1?'Impreso':'No Impreso')."</td>";
				echo "</tr>";
			}

			for ($x=0; $x <$cantidaddata5 ; $x++) { 
				echo "<tr>"; 
				echo "<th>N° Nota Credito </th>";
				$serie=str_pad($datadocumento5[$x]['serie'],3,'0',STR_PAD_LEFT);
				$ultimo=$datadocumento5[$x]['numdoc']+$datadocumento5[$x]['CantidadDocumentos']-1;
				echo "<td>".$serie.'-'.$datadocumento5[$x]['numdoc'].($datadocumento5[$x]['CantidadDocumentos']>0?(" al ".$serie.'-'.$ultimo):"")."</td>";
				echo "<th>Estado </th>";
				echo "<td>".($datadocumento5[$x]['esAnulado']==1?'Anulado':'Activo')."</td>";
				echo "<td>".($datadocumento5[$x]['esImpreso']==1?'Impreso':'No Impreso')."</td>";
				echo "</tr>";
			}

			for ($y=0; $y <$cantidaddata6 ; $y++) { 
				echo "<tr>"; 
				echo "<th>N° Nota Devito </th>";
				$serie=str_pad($datadocumento6[$y]['serie'],3,'0',STR_PAD_LEFT);
				$ultimo=$datadocumento6[$y]['numdoc']+$datadocumento6[$y]['CantidadDocumentos']-1;
				echo "<td>".$serie.'-'.$datadocumento6[$y]['numdoc'].($datadocumento6[$y]['CantidadDocumentos']>0?(" al ".$serie.'-'.$ultimo):"")."</td>";
				echo "<th>Estado </th>";
				echo "<td>".($datadocumento6[$y]['esAnulado']==1?'Anulado':'Activo')."</td>";
				echo "<td>".($datadocumento6[$y]['esImpreso']==1?'Impreso':'No Impreso')."</td>";
				echo "</tr>";
			}
			
		}

		function documentosxOrden(){
			$documento=$this->AutoLoadModel('documento');
			$ordenventa=$this->AutoLoadModel('ordenventa');
			$idordenventa=$_REQUEST['idordenventa'];
			$tipodoc=$_REQUEST['tipodoc'];
			
			$filtro="nombredoc='$tipodoc'";
			$tipoDocumento=$this->tipoDocumento();
			if ($tipodoc!=7) {
				$datadocumento=$documento->buscadocumentoxordenventa($idordenventa,$filtro);
			}else{
				$datadocumento=$documento->buscaletrasxordenventa($idordenventa,$filtro);	
			}
			$dataorden=$ordenventa->buscarOrdenVentaxId($idordenventa);
			// echo "<pre>";
			// print_r($datadocumento);
			// exit;
			
			echo "<h2>".$dataorden[0]['codigov']."</h2>";
					echo 	'<table>
									<thead>
										<tr>
											<th>Tipo Documento</th>
											<td>'.$tipoDocumento[$tipodoc].'</td>
										</tr>
										<tr>
											<th>Serie</th>
											<th>Numero Doc</th>
											<th>Monto</th>
											<th>igv</th>
											<th>Fecha de Creacion</th>
											<th>Fecha de Vencimiento</th>
											<th>Numero Unico</th>
											<th>Banco</th>
											<th>Situacion</th>
											<th>% de Doc</th>
											<th>Modo de Doc</th>
											<th>Fue Impreso</th>
											<th colspan="3">Acciones</th>
										</tr>
									</thead>
									<tbody>';
				if ($tipodoc==1 || $tipodoc==2 || $tipodoc==4) {
					
					$cantDocumento=count($datadocumento);
					for ($i=0; $i <$cantDocumento ; $i++) { 
						switch ($datadocumento[$i]['modofactura']) {
							case '1':
								$datadocumento[$i]['modofactura']='precio';
								break;
							case '2':
								$datadocumento[$i]['modofactura']='Cantidad';
								break;
							
							default:
								$datadocumento[$i]['modofactura']="";
								break;
						}

						echo				'<tr>
											<td>'.(str_pad($datadocumento[$i]['serie'] ,3,'0',STR_PAD_LEFT)).'</td>
											<td>'.($datadocumento[$i]['numdoc']).'</td>
											<td>'.number_format($datadocumento[$i]['montofacturado'],2).'</td>
											<td>'.number_format($datadocumento[$i]['montigv'],2).'</td>
											<td>'.$datadocumento[$i]['fechadoc'].'</td>
											<td>'.$datadocumento[$i]['fvencimiento'].'</td>
											<td>'.$datadocumento[$i]['numerounico'].'</td>
											<td>'.$datadocumento[$i]['recepcionLetras'].'</td>
											<td>'.$datadocumento[$i]['situacion'].'</td>
											<td>'.($datadocumento[$i]['porcentajefactura']==0?'100':$datadocumento[$i]['porcentajefactura']).'</td>
											<td>'.$datadocumento[$i]['modofactura'].'<input type="hidden" class="iddocumento" value="'.$datadocumento[$i]['iddocumento'].'" ></td>
											<td>'.($datadocumento[$i]['esImpreso']==1?"<img style='margin:auto;display:block' src='/imagenes/correcto.png'>":"").'</td>';
											if($datadocumento[$i]['esAnulado']==1 && $datadocumento[$i]['esImpreso']==1){
						echo				'<td colspan="2">&nbsp</td>
											<td>Anulado'.$datadocumento[$i]['motivo'].'</td>';		
											}
											elseif ($datadocumento[$i]['esImpreso']!=1 && $datadocumento[$i]['esAnulado']!=1) {
						echo				'<td colspan="2"> <a href="#" id="'.$datadocumento[$i]['iddocumento'].'"  class="imprimir"><img style="margin:auto;display:block" src="/imagenes/imprimir.gif"></a> </td>
											<td>&nbsp</td>';						
											}elseif($datadocumento[$i]['esImpreso']==1 && $datadocumento[$i]['esAnulado']!=1){
						echo 				'<td colspan="2"></td>
											<td > <button class="anular"> Anular</button> </td>';						
											}
						echo				'</tr>';
						
					}

				}elseif($tipodoc==5 || $tipodoc==6 || $tipodoc==7){
					
					$cantDocumento=count($datadocumento);
					for ($i=0; $i <$cantDocumento ; $i++) { 
						switch ($datadocumento[$i]['modofactura']) {
							case '1':
								$datadocumento[$i]['modofactura']='precio';
								break;
							case '2':
								$datadocumento[$i]['modofactura']='Cantidad';
								break;
							
							default:
								$datadocumento[$i]['modofactura']="";
								break;
						}

						echo			'<tr>';
										if ($tipodoc==7) {
						echo				'<td>'.(str_pad($datadocumento[$i]['serie'] ,3,'0',STR_PAD_LEFT)).'</td>';
										}else{
						echo 				'<td>'.($datadocumento[$i]['serie']==0?'<input maxlength="3" type="text" class="serie numeric"> ':str_pad($datadocumento[$i]['serie'] ,3,'0',STR_PAD_LEFT)).'</td>';					
										}
											
						echo				'<td>'.($datadocumento[$i]['numdoc']==0?'<input maxlength="8" type="text" class="numdoc numeric"> ':$datadocumento[$i]['numdoc']).'</td>
											<td>'.number_format($datadocumento[$i]['montofacturado'],2).'</td>
											<td>'.number_format($datadocumento[$i]['montigv'],2).'</td>
											<td>'.$datadocumento[$i]['fechadoc'].'</td>
											<td>'.$datadocumento[$i]['fvencimiento'].'</td>
											<td>'.$datadocumento[$i]['numerounico'].'</td>
											<td>'.$datadocumento[$i]['recepcionLetras'].'</td>
											<td>'.$datadocumento[$i]['situacion'].'</td>											
											<td>'.($datadocumento[$i]['porcentajefactura']==0?'100':$datadocumento[$i]['porcentajefactura']).'</td>
											<td>'.$datadocumento[$i]['modofactura'].'<input type="hidden" class="iddocumento" value="'.$datadocumento[$i]['iddocumento'].'" ></td>
											<td>'.($datadocumento[$i]['esImpreso']==1?"<img style='margin:auto;display:block' src='/imagenes/correcto.png'>":"").'</td>';
											if($datadocumento[$i]['esAnulado']==1 && $datadocumento[$i]['esImpreso']==1){
						echo				'<td colspan="2">Anulado&nbsp</td>
											<td>'.$datadocumento[$i]['motivo'].'</td>';		
											}
											elseif ($datadocumento[$i]['esImpreso']!=1 && $datadocumento[$i]['esAnulado']!=1) {
						echo				'<td colspan="2"> <a href="#" id="'.$datadocumento[$i]['iddocumento'].'"  class="imprimir"><img style="margin:auto;display:block" src="/imagenes/imprimir.gif"></a> </td>
											<td>&nbsp</td>';						
											}elseif($datadocumento[$i]['esImpreso']==1 && $datadocumento[$i]['esAnulado']!=1){
						echo 				'<td colspan="2"></td>
											<td > <button class="anular"> Anular</button> </td>';						
											}
						echo				'</tr>';
											
									
					}
				}
				echo				'</tbody>
								</table>';
		}

		function actualizaDocumentoJson(){
			$documento=$this->AutoLoadModel('documento');
			$iddocumento=$_REQUEST['iddocumento'];


			$dataDocumento=$documento->buscaNotaCredito($iddocumento);
			if (!empty($dataDocumento)) {
				$movimiento=$this->AutoLoadModel('movimiento');

				$iddevolucion=$dataDocumento[0]['iddevolucion'];
				$idordenventa=$dataDocumento[0]['idordenventa'];
				$dataMovimiento['serie']=$_REQUEST['serie'];
				$dataMovimiento['ndocumento']=$_REQUEST['numdoc'];
				$condicion="iddevolucion='$iddevolucion' and idordenventa='$idordenventa' ";
				$exito=$movimiento->actualizaMovimiento($dataMovimiento,$condicion);

			}


			$data['iddocumento']=$iddocumento;
			$data['numdoc']=$_REQUEST['numdoc'];
			$data['serie']=$_REQUEST['serie'];
			$data['esimpreso']=1;
			$filtro="iddocumento='$iddocumento'";
			$exito=$documento->actualizarDocumento($data,$filtro);
			echo $exito;

		}

		function impresionDocumentos(){
			$data['documentos']=$this->tipoDocumento();

			$this->view->show('/documento/impresionDocumentos.phtml',$data);
		}

		function generaFactura(){
			$pdf=$this->AutoLoadModel('pdf');
			$ordenventa=$this->AutoLoadModel('documento');
			$EnLetras=New EnLetras();
        	$meses=array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
			

			$cobro=$this->AutoLoadModel('ordencobro');
			$idDoc=$_REQUEST['id'];
			$buscaFactura=$ordenventa->buscaDocumento($idDoc,"");
			if (!empty($_REQUEST['id']) && !empty($buscaFactura) && $_REQUEST['id']>0  && $buscaFactura[0]['nombredoc']==1 && $buscaFactura[0]['esAnulado']!=1) {
				
				//obtemos la condicion y tiempo de credito
				

				//obtemos los porcenajes y modo que fue facturado
				$porcentaje=$buscaFactura[0]['porcentajefactura'];

				$modo=$buscaFactura[0]['modofactura'];
				$numeroFactura=$buscaFactura[0]['numdoc'];
				$serieFactura=str_pad($buscaFactura[0]['serie'],3,'0',STR_PAD_LEFT);
				
				//buscamos la guia de remision que le pertenece en caso que lo hubiera
				$filtro="nombredoc=4";
				$dataGuia=$ordenventa->buscadocumentoxordenventaPrimero($buscaFactura[0]['idordenventa'],$filtro);
				$numeroRelacionado=$dataGuia[0]['numdoc'];
				$tipodocumentorelacionado=$dataGuia[0]['nombredoc'];

				
				
				//Grabamos en 

				//*********************//
				$dataFactura=$pdf->buscarxOrdenVenta($buscaFactura[0]['idordenventa']);
				$dataFactura[0]['numeroRelacionado']=$numeroRelacionado;
				$dataFactura[0]['numeroFactura']=$numeroFactura;
				$dataFactura[0]['serieFactura']=$serieFactura;
				$dataFactura[0]['fecha']=date('d/m/Y');
				$dataFactura[0]['referencia']='VEN: '.$dataFactura[0]['idvendedor'].' DC: '.$dataFactura[0]['codigov'];
				$data=$pdf->buscarDetalleOrdenVenta($buscaFactura[0]['idordenventa']);

				$dataCobro=$pdf->buscarOrdenCompraxId($buscaFactura[0]['idordenventa']);
				if ($dataCobro[0]['escontado']==1 && $dataCobro[0]['escredito']==0 && $dataCobro[0]['esletras']==0) {
					$dataFactura[0]['condicion']='CONTADO';
				}elseif($dataCobro[0]['escredito']==1 && $dataCobro[0]['esletras']==0){
					$dataFactura[0]['condicion']='CREDITO';
				}elseif($dataCobro[0]['esletras']==1){
					$dataFactura[0]['condicion']='LETRAS';
					//$dataFactura[0]['fechavencimiento']=$pdf->listaDetalleOrdenCompraxId($dataCobro[0]['idordencobro'],3);
				}
				$descuento=New Descuento();
				$dataDescuento=$descuento->listadoTotal();
				for ($i=0; $i < count($dataDescuento); $i++) { 
					$dscto[$dataDescuento[$i]['id']]=$dataDescuento[$i]['valor'];
				}
				
				$cantidad=count($data);
				$dataN=array();
				$total=0;
				$cont=0;
				for($i=0;$i<$cantidad;$i++){
					
						if($porcentaje!=""){
							if($modo==1){
								$precio=$data[$i]['preciofinal'];
								$data[$i]['preciofinal']=(($precio*$porcentaje)/100);
								$cantidadP=$data[$i]['cantdespacho']-$data[$i]['cantdevuelta'];

								$data[$i]['cantdespacho']=$cantidadP;
								
								
							}elseif($modo==2){
								$cantidadP=$data[$i]['cantdespacho']-$data['cantdevuelta'];

								$data[$i]['cantdespacho']=(($cantidadP*$porcentaje)/100);
								
								
							}else{
								$data[$i]['cantdespacho']=$data[$i]['cantdespacho']-$data[$i]['cantdevuelta'];
							}
							
						}else{
							$data[$i]['cantdespacho']=$data[$i]['cantdespacho']-$data[$i]['cantdevuelta'];
						}
						
						
						if($data[$i]['cantdespacho']>0){
								
							$dataN[$cont]['cantdespacho']=$data[$i]['cantdespacho'];
							$dataN[$cont]['preciofinal']=$data[$i]['preciofinal'];
							$dataN[$cont]['nompro']=$data[$i]['nompro'];
							$dataN[$cont]['codigopa']=$data[$i]['codigopa'];
							$cont++;
						}

						
				}
			echo '';
	
				$cantidadDocumentos=0;
				$maximoItem=$this->configIni("MaximoItem","Factura");
				if ($cont%$maximoItem==0) {
					$cantidadDocumentos=$cont/$maximoItem;
				}elseif($cont%$maximoItem!=0){
					$cantidadDocumentos=floor($cont/$maximoItem)+1;
				}
				//acutalizamos Documento que ya fue impreso,numero Relacionado y su tipo
				$dataV['esimpreso']=1;
				$dataV['CantidadDocumentos']=$cantidadDocumentos;
				$dataV['numerorelacionado']=$numeroRelacionado;
				$dataV['tipoDocumentoRelacionado']=$tipodocumentorelacionado;
				$filtro="iddocumento='".$idDoc."'";
				$exitoE=$ordenventa->actualizarDocumento($dataV,$filtro);

				$datos['hojas']=$cantidadDocumentos;
				$datos['maximoItem']=$maximoItem;
				$datos['Factura']=$dataFactura;
				$datos['DetalleFactura']=$dataN;
				$datos['letras']=$EnLetras;
				$datos['mes']=$meses[date('n')];
				$this->view->show('/documento/generaFactura.phtml',$datos);
			}
		}
		function generaGuia(){
			$pdf=$this->AutoLoadModel('pdf');
			$ordenventa=$this->AutoLoadModel('documento');
			$idDoc=$_REQUEST['id'];
			
			
			$tipo=$this->tipoDocumento();

			$buscaGuia=$ordenventa->buscaDocumento($idDoc,"");

			if (!empty($_REQUEST['id']) && !empty($buscaGuia) && $_REQUEST['id']>0  && $buscaGuia[0]['nombredoc']==4 && $buscaGuia[0]['esAnulado']!=1) {
				//buscamos la Factura que le pertenece en caso que lo hubiera
				$filtro="nombredoc=1";
				$dataFactura=$ordenventa->buscadocumentoxordenventaPrimero($buscaGuia[0]['idordenventa'],$filtro);
				$numeroRelacionado=$dataFactura[0]['numdoc'];
				$tipodocumentorelacionado=$dataFactura[0]['nombredoc'];

					

				session_start();
				$usuario=$_SESSION['nombres'].' '.$_SESSION['apellidopaterno'];
				
				$dataGuia=$pdf->buscarxOrdenVenta($buscaGuia[0]['idordenventa']);
				$dataGuia[0]['imprimir']=$imprimir;

				
					$dataGuia[0]['tipo']=$tipodocumentorelacionado;
					$dataGuia[0]['numeroRelacionado']=$numeroRelacionado;
				
				$dataGuia[0]['tipo']=$tipo[$dataGuia[0]['tipo']];

				$dataGuia[0]['numeroFactura']=$buscaGuia[0]['numdoc'];
				$dataGuia[0]['serieFactura']=str_pad($buscaGuia[0]['serie'],3,'0',STR_PAD_LEFT);
				$dataGuia[0]['fecha']=date('d/m/Y');
				$dataGuia[0]['referencia']=' REF: '.$dataGuia[0]['codigov'].'    VEN: '.$dataGuia[0]['idvendedor'].'     '.strtoupper($usuario).'  --  '.date('H:i:s');
				$dataGuia[0]['domiPartida']='JR. ALFZ. RICARDO HERRERA 665 - LIMA -  LIMA  -  LIMA';
				$data=$pdf->buscarDetalleOrdenVenta($buscaGuia[0]['idordenventa']);

				$cantidad=count($data);
				$cantidadDocumentos=0;
				$maximoItem=$this->configIni("MaximoItem","Guia");
				if ($cantidad%$maximoItem==0) {
					$cantidadDocumentos=$cantidad/$maximoItem;
				}elseif($cantidad%$maximoItem!=0){
					$cantidadDocumentos=floor($cantidad/$maximoItem)+1;
				}
				//acutalizamos Documento que ya fue impreso
				$data2['esimpreso']=1;

				$data2['CantidadDocumentos']=$cantidadDocumentos;
				$data2['numeroRelacionado']=$numeroRelacionado;
				$data2['tipoDocumentoRelacionado']=$tipodocumentorelacionado;
				$filtro2="iddocumento='".$idDoc."'";
				$exitoE=$ordenventa->actualizarDocumento($data2,$filtro2);

				$datos['hojas']=$cantidadDocumentos;
				$datos['Factura']=$dataGuia;
				$datos['DetalleFactura']=$data;
				$datos['maximoItem']=$maximoItem;
				
				$this->view->show('/documento/generaGuia.phtml',$datos);
			}
		}
		function generaLetra(){
			$iddocumento=$_REQUEST['id'];
			$detallecobro=$this->AutoLoadModel('detalleordencobro');
			$datadetallecobro=$detallecobro->listaConClientes($iddocumento);
			if (!empty($_REQUEST['id']) && !empty($datadetallecobro) && $_REQUEST['id']>0  && $datadetallecobro[0]['nombredoc']==7 && $datadetallecobro[0]['esAnulado']!=1) {
				$numeroletra=$datadetallecobro[0]['numdoc'];
				$databusqueda=$detallecobro->buscaDetalleOrdencobroxNumeroletra($numeroletra);
				$EnLetras=New EnLetras();
				$datadetallecobro[0]['importedoc']=$databusqueda[0]['importedoc'];
				$datadetallecobro[0]['fechagiro']=$databusqueda[0]['fechagiro'];
				$datadetallecobro[0]['fvencimiento']=$databusqueda[0]['fvencimiento'];
				$datadetallecobro[0]['numeroletra']=$databusqueda[0]['numeroletra'];
				if ($datadetallecobro[0]['tipocliente']==1) {
					$datadetallecobro[0]['ruc']=$datadetallecobro[0]['dni'];
				}
				$data['detalle']=$datadetallecobro;
				$data['letra']=$EnLetras;
				$this->view->show('/documento/generaLetra.phtml',$data);
			}
		}

		function generaBoleta(){
			$this->view->template='impresion';
			
			$pdf=$this->AutoLoadModel('pdf');
			$documento=$this->AutoLoadModel('documento');
			$EnLetras=New EnLetras();
        	$meses=array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
			

			$cobro=$this->AutoLoadModel('ordencobro');
			$idDoc=$_REQUEST['id'];
			$buscaDocumento=$documento->buscaDocumento($idDoc,"");
			//obtemos numero de boleta y serie
			if (!empty($_REQUEST['id']) && !empty($buscaDocumento) && $_REQUEST['id']>0  && $buscaDocumento[0]['nombredoc']==2 && $buscaDocumento[0]['esAnulado']!=1) {

				$numeroFactura=$buscaDocumento[0]['numdoc'];
				$serieFactura=str_pad($buscaDocumento[0]['serie'],3,'0',STR_PAD_LEFT);
				
				//buscamos la guia de remision que le pertenece en caso que lo hubiera
				$filtro="nombredoc=4";
				$dataGuia=$documento->buscadocumentoxordenventaPrimero($buscaDocumento[0]['idordenventa'],$filtro);
				$numeroRelacionado=$dataGuia[0]['numdoc'];
				$tipodocumentorelacionado=$dataGuia[0]['nombredoc'];

				

				//*********************//
				$datadocumento=$pdf->buscarxOrdenVenta($buscaDocumento[0]['idordenventa']);
				$datadocumento[0]['numeroRelacionado']=$numeroRelacionado;
				$datadocumento[0]['simbolo']=$dataGuia[0]['simbolo'];
				$datadocumento[0]['nombresimbolo']=$dataGuia[0]['nombre'];
				$datadocumento[0]['numeroFactura']=$numeroFactura;
				$datadocumento[0]['serieFactura']=$serieFactura;
				$datadocumento[0]['fecha']=date('d/m/Y');
				$datadocumento[0]['referencia']='VEN: '.$datadocumento[0]['idvendedor'].' DC: '.$datadocumento[0]['idordenventa'];
				$data=$pdf->buscarDetalleOrdenVenta($buscaDocumento[0]['idordenventa']);

				$cantidad=count($data);
				$cantidadDocumentos=0;
				$maximoItem=$this->configIni("MaximoItem","Boleta");
				if ($cantidad%$maximoItem==0) {
					$cantidadDocumentos=$cantidad/$maximoItem;
				}elseif($cantidad%$maximoItem!=0){
					$cantidadDocumentos=floor($cantidad/$maximoItem)+1;
				}
				//acutalizamos Documento que ya fue impreso,numero Relacionado y su tipo
				$dataV['esimpreso']=1;
				$dataV['CantidadDocumentos']=$cantidadDocumentos;
				$dataV['numerorelacionado']=$numeroRelacionado;
				$dataV['tipoDocumentoRelacionado']=$tipodocumentorelacionado;
				$filtro="iddocumento='".$idDoc."'";
				$exitoE=$documento->actualizarDocumento($dataV,$filtro);

				$datos['hojas']=$cantidadDocumentos;
				$datos['Boleta']=$datadocumento;
				$datos['DetalleBoleta']=$data;
				$datos['letras']=$EnLetras;
				$datos['mes']=$meses[date('n')];
				$datos['maximoItem']=$maximoItem;

				$this->view->show('/documento/generaBoleta.phtml',$datos);
			}
		}

		function generaNotaCredito(){
			$this->view->template='impresion';
			
			$pdf=$this->AutoLoadModel('pdf');
			$documento=$this->AutoLoadModel('documento');
			$devolucion=$this->AutoLoadModel('devolucion');
			$cantidadDocumentos=0;
			$maximoItem=$this->configIni("MaximoItem","NotaCredito");
			$idDoc=$_REQUEST['id'];
			//recuperamos la orden de venta
			
			$buscaDocumento=$documento->buscaDocumento($idDoc,"");
			
			if (!empty($_REQUEST['id']) && !empty($buscaDocumento) && $_REQUEST['id']>0  && $buscaDocumento[0]['nombredoc']==5 && $buscaDocumento[0]['esAnulado']!=1) {
				$iddevolucion=$buscaDocumento[0]['iddevolucion'];
				$concepto=$buscaDocumento[0]['concepto'];
				$importe=$buscaDocumento[0]['montofacturado'];
				
				
				//buscamos la Factura  que le pertenece 
				$filtro="nombredoc=1";
				$dataGuia=$documento->buscadocumentoxordenventaPrimero($buscaDocumento[0]['idordenventa'],$filtro);
				//print_r($dataGuia);
				//exit;
				//$simbolo=$dataGuia[0]['simbolo'];
				$numeroRelacionado=$dataGuia[0]['numdoc'];
				$tipodocumentorelacionado=$dataGuia[0]['nombredoc'];
				$serieFactura=$dataGuia[0]['serie'];
				$fechaD=date('d/m/Y',strtotime($dataGuia[0]['fechacreacion']));
				//Buscamos la devolucion que tiene
				

				//*********************//
				$datadocumento=$pdf->buscarxOrdenVenta($buscaDocumento[0]['idordenventa']);
				$datadocumento[0]['numeroRelacionado']=$numeroRelacionado;
				$datadocumento[0]['simbolo']=$dataGuia[0]['simbolo'];
				$datadocumento[0]['nombresimbolo']=$dataGuia[0]['nombre'];
				$datadocumento[0]['fechaFactura']=$fechaD;
				$datadocumento[0]['serieFactura']=$serieFactura;
				$datadocumento[0]['fecha']=date('d/m/Y');
				$datadocumento[0]['referencia']='VEN: '.$datadocumento[0]['idvendedor'].' DC: '.$datadocumento[0]['idordenventa'];
				//1 es devolucion y 2 para diferencia de precio
				if (!empty($iddevolucion) && $concepto==1) {
					$data=$devolucion->listaDevolucionParaImpresion($iddevolucion);
					/*echo '<pre>';
					print_r($data);
					exit;*/
					$cantidad=count($data);
					if ($cantidad%$maximoItem==0) {
						$cantidadDocumentos=$cantidad/$maximoItem;
					}elseif($cantidad%$maximoItem!=0){
						$cantidadDocumentos=floor($cantidad/$maximoItem)+1;
					}
					$motivo="POR DEVOLUCION";

				}elseif(empty($iddevolucion) && $concepto==2){
					$data[0]['nompro']="Por Diferencia de Precio";
					$data[0]['precio']=$importe;
					$data[0]['cantidad']=1;
					$cantidadDocumentos=1;
					$motivo="POR PRECIO";
				}

				//acutalizamos Documento que ya fue impreso,numero Relacionado y su tipo
				$dataV['esimpreso']=1;
				$dataV['CantidadDocumentos']=$cantidadDocumentos;
				$dataV['numerorelacionado']=$numeroRelacionado;
				$dataV['tipoDocumentoRelacionado']=$tipodocumentorelacionado;
				$filtro="iddocumento='".$idDoc."'";
				$exitoE=$documento->actualizarDocumento($dataV,$filtro);
				
				
				$datos['hojas']=$cantidadDocumentos;
				$datos['maximoItem']=$maximoItem;
				$datos['NotaCredito']=$datadocumento;
				$datos['DetalleNCredito']=$data;
				$datos['motivo']=$motivo;
				
				$this->view->show('/documento/generaNotaCredito.phtml',$datos);
			}
		}

		function generaNotaDevito(){
			$this->view->template='impresion';
			
			$pdf=$this->AutoLoadModel('pdf');
			$documento=$this->AutoLoadModel('documento');
			
			
			$idDoc=$_REQUEST['id'];
			//recuperamos la orden de venta
			$buscaDocumento=$documento->buscaDocumento($idDoc,"");

			if (!empty($_REQUEST['id']) && !empty($buscaDocumento) && $_REQUEST['id']>0  && $buscaDocumento[0]['nombredoc']==6 && $buscaDocumento[0]['esAnulado']!=1) {

				$concepto=$buscaDocumento[0]['concepto'];
				$importe=$buscaDocumento[0]['montofacturado'];
				
				
				//buscamos la Factura  que le pertenece 
				$filtro="nombredoc=1";
				$dataGuia=$documento->buscadocumentoxordenventaPrimero($buscaDocumento[0]['idordenventa'],$filtro);
				$numeroRelacionado=$dataGuia[0]['numdoc'];
				$tipodocumentorelacionado=$dataGuia[0]['nombredoc'];
				$serieFactura=$dataGuia[0]['serie'];

				//acutalizamos Documento que ya fue impreso,numero Relacionado y su tipo
				$dataV['esimpreso']=1;
				$dataV['CantidadDocumentos']=1;
				$dataV['numerorelacionado']=$numeroRelacionado;
				$dataV['tipoDocumentoRelacionado']=$tipodocumentorelacionado;
				$filtro="iddocumento='".$idDoc."'";
				$exitoE=$documento->actualizarDocumento($dataV,$filtro);

				//*********************//
				$datadocumento=$pdf->buscarxOrdenVenta($buscaDocumento[0]['idordenventa']);
				$datadocumento[0]['numeroRelacionado']=$numeroRelacionado;
				$datadocumento[0]['serieFactura']=$serieFactura;
				
				
				//1 es renovado y 2 para protesto
				if ($concepto==1) {
					$data[0]['nompro']="Por Gastos de Renovacion";
					$data[0]['precio']=$importe;
					$data[0]['cantidad']=1;
				}elseif($concepto==2){
					$data[0]['nompro']="Por Gastos de Protesto";
					$data[0]['precio']=$importe;
					$data[0]['cantidad']=1;
				}

				
				
				$datos['maximoItem']=$maximoItem;
				$datos['NotaDevito']=$datadocumento;
				$datos['DetalleNDevito']=$data;
				
				$this->view->show('/documento/generaNotaDevito.phtml',$datos);
			}
		}

		function anularDocumentos(){
			$documento=$this->AutoLoadModel('documento');
			$iddocumento=$_REQUEST['iddocumento'];
			$data=$documento->buscaDocumento($iddocumento,"");
			$tipodocumento=$data[0]['nombredoc'];
			$idordenventa=$data[0]['idordenventa'];
			$montofacturado=$data[0]['montofacturado'];
			$iddevolucion=$data[0]['iddevolucion'];
			$concepto=$data[0]['concepto'];

			if ($tipodocumento==1 || $tipodocumento==2 || $tipodocumento==4) {
				$ordenventa=$this->AutoLoadModel('ordenventa');
				$dataEnvio['esAnulado']=1;
				$filtro="iddocumento='$iddocumento'";
				$exito=$documento->actualizarDocumento($dataEnvio,$filtro);
				if ($exito) {
					if ($tipodocumento==4) {
						$data2['guiaremision']=0;
					}else{
						$data2['esfacturado']=0;
					}
					
					$exito2=$ordenventa->actualizaOrdenVenta($data2,$idordenventa);
					if (!$exito2) {
						echo 'Error 1.1';
					}
				}else{
					echo 'Error 1';
				}
				

			}elseif($tipodocumento==5 || $tipodocumento==6){
				echo 'entro';
				$dataEnvio['esAnulado']=1;
				$filtro="iddocumento='$iddocumento'";
				$exito=$documento->actualizarDocumento($dataEnvio,$filtro);
				if ($exito) {
					$dataNuevo['idordenventa']=$idordenventa;
					$dataNuevo['nombredoc']=$tipodocumento;
					$dataNuevo['montofacturado']=$montofacturado;
					$dataNuevo['iddevolucion']=$iddevolucion;
					$dataNuevo['concepto']=$concepto;
					$dataNuevo['fechadoc']=date('Y-m-d');

					$exito2=$documento->grabaDocumento($dataNuevo);
					if (!$exito2) {
						echo 'Error 2.1';
					}
				}else{
					echo 'Error 2';
				}

			}elseif($tipodocumento==7){
				$dataEnvio['esImpreso']=0;
				$filtro="iddocumento='$iddocumento'";
				$exito2=$documento->actualizarDocumento($dataEnvio,$filtro);
			}

			if ($exito2) {
				echo 'Correcto';
			}else{
				echo 'Error';
			}
		}

		function buscar(){
			$documento=$this->AutoLoadModel('documento');
			$idordenventa=$_REQUEST['id'];
			$filtro="nombredoc=1 and doc.esAnulado!=1";
			$data=$documento->buscadocumentoxordenventa($idordenventa,$filtro);
			
			$dataNotaCredito=$documento->sumaNotasCredito($idordenventa);
			$data[0]['saldo']=$dataNotaCredito;

			echo json_encode($data[0]);
		}
	}

 ?>
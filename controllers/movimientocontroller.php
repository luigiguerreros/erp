<?php
	class MovimientoController extends ApplicationGeneral{
		private $mostrar=5;
		function probando(){
			$var_config=parse_ini_file("config.ini",true);
			$tipo='Salida';
			$conceptos=$var_config[$tipo];
			print_r($conceptos);
		}
		function nuevo(){
			$rutaImagen=$this->rutaImagenesProducto();
			$documentoTipo=$this->AutoLoadModel('documentotipo');
			$movimiento=new Movimiento();
			$linea=new Linea();
			//$data['numeroMovimiento']=$movimiento->generaCodigo();
			$data['Tipomovimiento']=$this->tipoMovimiento();
			$data['RutaImagen']=$rutaImagen;
			$data['documentoTipo']=$documentoTipo->listadoDocumentoTipo();
			$this->view->show("/movimiento/nuevo.phtml",$data);
		}
		function listaConceptoMovimiento(){
			$idTipoMovimiento=$_REQUEST['id'];
			$tipomovimiento=$this->AutoLoadModel('tipooperacion');
			$conceptoMomiviento=$tipomovimiento->listadoTipoOperacion($idTipoMovimiento);
			for($i=0;$i<count($conceptoMomiviento);$i++){
				
				if($idTipoMovimiento==1){
					if($conceptoMomiviento[$i]['idtipooperacion']!=2 && $conceptoMomiviento[$i]['idtipooperacion']!=5){
						echo '<option value="'.($conceptoMomiviento[$i]['idtipooperacion']).'">'.$conceptoMomiviento[$i]['nombre'];	
					}
				}else{
					if($conceptoMomiviento[$i]['idtipooperacion']!=1 ){
						echo '<option value="'.($conceptoMomiviento[$i]['idtipooperacion']).'">'.$conceptoMomiviento[$i]['nombre'];	
					}
				}
			}
		}
		function movstock(){
			$rutaImagen=$this->rutaImagenesProducto();
			$movimiento=new Movimiento();
			$tipoMovimiento=new Tipomovimiento();
			$linea=new Linea();
			$data['numeroMovimiento']=$movimiento->contarMovimiento();
			$data['Tipomovimiento']=$tipoMovimiento->listadoTiposmovimiento();
			$data['Linea']=$linea->listadoLineas();
			$data['RutaImagen']=$rutaImagen;
			$this->view->template="movimiento";
			$this->view->show("/movimiento/nuevo.phtml",$data);
		}
		function registra(){
			$dataMovimiento=$_REQUEST['Movimiento'];
			$dataDetalleMovimiento=$_REQUEST['Detallemovimiento'];
			$producto=new Producto();
			$movimiento=new Movimiento();
			$detalleMovimiento=new Detallemovimiento();
			$dataMovimiento['idtipooperacion']=$dataMovimiento['conceptomovimiento'];
			if (!empty($dataMovimiento['iddocumentotipo'])) {
				$dataMovimiento['essunat']=1;
			}
			$exitoMovimiento=$movimiento->grabaMovimiento($dataMovimiento);
			
			if($exitoMovimiento){
				$operacion=$dataMovimiento['tipomovimiento'];
				foreach($dataDetalleMovimiento as $data){
					$idProducto=$data['idproducto'];
					$dataBusqueda=$producto->buscaProducto($idProducto);
					
					if ($operacion==2) {
						$valor=$dataBusqueda[0]['stockactual']-$data['cantidad'];
						if ($valor<=0) {
							$stockNuevo['esagotado']=1;
							$stockNuevo['fechaagotado']=date('Y-m-d');
						}
							$stockNuevo['esagotado']=0;
							$stockNuevo['fechaagotado']=null;
							$stockNuevo['stockactual']=$valor;
							$stockNuevo['stockdisponible']=$dataBusqueda[0]['stockdisponible']-$data['cantidad'];
					}elseif($operacion==1){
						$stockNuevo['esagotado']=0;
						$stockNuevo['fechaagotado']=null;
						$stockNuevo['stockactual']=$dataBusqueda[0]['stockactual']+$data['cantidad'];
						$stockNuevo['stockdisponible']=$dataBusqueda[0]['stockdisponible']+$data['cantidad'];
					}
					//$stockNuevo=($operacion=='+')?array('esagotado'=>0,'stockactual'=>($data['stockactual']+$data['cantidad']),'stockdisponible'=>($data['stockdisponibledm']+$data['cantidad'])):array('stockactual'=>($data['stockactual']-$data['cantidad']),'stockdisponible'=>($data['stockdisponibledm']-$data['cantidad']));
					
					
					$exitoProducto=$producto->actualizaProducto($stockNuevo,$data['idproducto']);

					$data2['stockactual']=$stockNuevo['stockactual'];
					$data2['idmovimiento']=$exitoMovimiento;
					$data2['preciovalorizado']=$dataBusqueda[0]['preciocosto'];
					$data2['importe']=$data['cantidad']*$dataBusqueda[0]['preciocosto'];
					$data2['stockdisponibledm']=$stockNuevo['stockdisponible'];
					$data2['idproducto']=$data['idproducto'];
					$data2['cantidad']=$data['cantidad'];
					$data2['pu']=$dataBusqueda[0]['preciocosto'];
					$exitoDetalleMovimiento=$detalleMovimiento->grabaDetalleMovimieto($data2);
					
				}
			}
			if($exitoDetalleMovimiento and $exitoProducto){
				$ruta['ruta']="/almacen/movstock";
				$this->view->show("ruteador.phtml",$ruta);
			}
		}
		
		function detalle(){
			$id=$_REQUEST['id'];
			if (empty($_REQUEST['id'])) {
				$id=$_REQUEST['idcontenedor'];
			}
			$detalleMovimiento=new Detallemovimiento();
			$data=$detalleMovimiento->buscaDetalleMovimiento($id);
			for($i=0;$i<count($data);$i++){
				echo "<tr>";
						echo "<td>".$data[$i]['codigopa']."</td>";
						echo "<td>".$data[$i]['nompro']."</td>";
						echo "<td>".$data[$i]['observaciones']."</td>";
						echo "<td>".$data[$i]['cantidad']."</td>";
				echo "</tr>";
			}
		}
        
	function KardexBuscaProducto(){
		$data['mes']=$this->meses();
		$this->view->show("/movimiento/kardex.phtml",$data);
	}

	function kardexValorizadoxProducto(){
		$idLast=(int)$_REQUEST['idproducto'];
		$mesInicial=!empty($_REQUEST['mesInicial'])?$_REQUEST['mesInicial']:1;
		$mesFinal=!empty($_REQUEST['mesFinal'])?$_REQUEST['mesFinal']:12;
		$ano=!empty($_REQUEST['ano'])?$_REQUEST['ano']:date('Y');
		$sunat=$_REQUEST['sunat'];
		$_REQUEST['fecha1oo']=$fecha1;
               
              for($x=1; $x<$idLast; $x++){
                $producto=new Producto();  
		$movimiento=new Movimiento();
                $almacen=new Almacen();
		
//		if ($_REQUEST['id']) {
                $dataBusqueda=$producto->buscarxID($x);
                $idalmacen=(int)$dataBusqueda[0]['idalmacen'];
                $dataAlmacen=$almacen->buscaAlmacen($idalmacen);
                $dataKardex=$movimiento->kardexValorizadoxProducto($x,$ano,$mesInicial,$mesFinal,$sunat);
                $total=count($dataKardex);
                if ($total>0){
//                 echo"<h2>".$dataBusqueda[0]['nompro']."</h2></br>";
                 //echo"<h2>".$dataAlmacen[0]['nomalm']."</h2></br>";
                 echo "<table style='margin-bottom:0px'>";
		 echo "<caption>";
		 echo "<h2>Formato 13.1 : Registro de Inventario Permanente Valorizado</h2>";
		 echo "</caption>";
		 echo "<tr>";
	         echo "<td style='width:30%;text-align: left;'>PERIODO : </td><td style='width:70%;text-align: left;'><label id='labelPeriodo'>".$ano."</td>";
		 echo "</tr>";
		echo "<tr>";
		echo "<td style='width:30%;text-align: left;'>RUC : </td><td style='width:70%;text-align: left;'><label id='labelRuc'>".$dataAlmacen[0]['rucalm']."</label></td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td style='width:30%;text-align: left;'>RAZON SOCIAL : </td><td style='width:70%;text-align: left;'><label id='labelRazonSocial'>".$dataAlmacen[0]['razsocalm']."</label></td>";
		echo "</tr>
		<tr>
			<td style='width:30%;text-align: left;'>ESTABLECIMIENTO : </td><td style='width:70%;text-align: left;'><label id='labelalmacen'>ALMACEN GENERAL</label></td>
		</tr>
		<tr>
			<td style='width:30%;text-align: left;'>CODIGO DE LA EXISTENCIA : </td><td style='width:70%;text-align: left;'><label id='labelCodigo'>".$dataBusqueda[0]['codigopa']."</label></td>
		</tr>
		<tr>
			<td style='width:30%;text-align: left;'>TIPO : </td><td style='width:70%;text-align: left;'><label id='labelTipo'>MERCADERIAS</label></td>
		</tr>
		<tr>
			<td style='width:30%;text-align: left;'>DESCRIPCION : </td><td style='width:70%;text-align: left;'><label id='labelProducto'>".$dataBusqueda[0]['nompro']."</label></td>
		</tr>
		<tr>
			<td style='width:30%;text-align: left;'>UNIDAD DE MEDIDA : </td><td style='width:70%;text-align: left;'><label id='labelUnidadMedida'>UNIDAD</label></td>
		</tr>
		<tr>
			<td style='width:30%;text-align: left;'>METODO DE VALUACION : </td><td style='width:70%;text-align: left;'><label id='labelMetodo'PROMEDIO MOVIL</label></td>
		</tr>";
	        echo"</table>";
	     
                echo"<table id='tblKardexValorizado'>";
                        echo "<thead>";
			echo "<tr>";
				echo "<th rowspan='2' class='text-10'>Nro</th>";
				echo "<th rowspan='2' class='text-30'>Fecha</th>";
				echo "<th rowspan='2' class='text-30'>Tipo Doc</th>";
				echo"<th rowspan='2' class='text-30'>Serie</th>";
				echo"<th rowspan='2' class='text-30'>Núm</th>";
				echo "<th rowspan='2' class='text-30'>Tipo<br>Mov.</th>";
				echo "<th colspan=3>ENTRADAS </th>";
				echo "<th colspan=3>SALIDAS</th>";
				echo "<th colspan=3>SALDO FINAL</th>";
			echo "</tr>";
			echo "<tr>";
				echo "<td >Cantidad</td>";
				echo "<td>Costo<br>Unit. (S/.)</td>";
				echo "<td class='text-100'>Costo Total (S/.)</td>";
				echo "<td>Cantidad</td>";
				echo "<td>Costo<br>Unit. (S/.)</td>";
				echo "<td class='text-100'>Costo Total (S/.)</td>";
				echo "<th>Cantidad</th>";
				echo "<th>Costo<br>Uni. (S/.)</th>";
				echo "<th class='text-100'>Costo Total (S/.)</th>								
			</tr>	
		</thead>";
                $tecant=0;
		$tecosto=0;
		$tscant=0;
		$tscosto=0;
		$cont=0;
		if ($dataKardex[0]['codigotipooperacion']!=16) {
			echo "<tr>";
			echo "<td></td>";
			echo "<td colspan='4'>Saldo Inicial</td>";
			echo "<td>16</td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td></td>";
			if ($dataKardex[0]['tipomovimiento']==1) {
				$cantidad=round($dataKardex[0]['SaldoCantidad']-round($dataKardex[0]['cantidad']));
				if ($cantidad<0) {
					$cantidad=0;
				}
			}else{
				$cantidad=round($dataKardex[0]['SaldoCantidad']+round($dataKardex[0]['cantidad']));
			}
			
			echo "<td>".$cantidad."</td>";
			echo "<td>".$dataKardex[0]['SaldoPrecio']."</td>";
			echo "<td>".round($dataKardex[0]['SaldoPrecio']*$cantidad,2)."</td>";
			echo "</tr>";
		}
                        for ($i=0; $i < $total; $i++) { 
			echo "<tr>";					
				
				if ($dataKardex[$i]['codigotipooperacion']==16) {
					echo "<td></td>";
					echo "<td colspan='4'>Saldo Inicial</td>";
				}else{
					$cont++;
					echo "<td>".($cont)."</td>";
					echo "<td>".$dataKardex[$i]['fechamovimiento']."</td>";
					echo "<td>".$dataKardex[$i]['codigotipodocumento']."</td>";
					echo "<td>".$dataKardex[$i]['serie']."</td>";
					echo "<td>".$dataKardex[$i]['ndocumento']."</td>";
				}
				$cantidad=$cantidad-$dataKardex[$i]['SalidaCantidad'];
				echo "<td style='text-align:center'>".$dataKardex[$i]['codigotipooperacion']."</td>";
				echo "<td style='text-align:center'>".$dataKardex[$i]['EntradaCantidad']."</td>";
				echo "<td style='text-align:right'>".(empty($dataKardex[$i]['EntradaPrecio'])?'':number_format($dataKardex[$i]['EntradaPrecio'],2))."</td>";
				echo "<td style='text-align:right'>".(empty($dataKardex[$i]['EntradaCosto'])?'':number_format($dataKardex[$i]['EntradaCosto'],2))."</td>";
				echo "<td style='text-align:center'>".$dataKardex[$i]['SalidaCantidad']."</td>";
				echo "<td style='text-align:right'>".(empty($dataKardex[$i]['SalidaPrecio'])?'':number_format($dataKardex[$i]['SalidaPrecio'],2))."</td>";
				echo "<td style='text-align:right'>".(empty($dataKardex[$i]['SalidaCosto'])?'':number_format($dataKardex[$i]['SalidaCosto'],2))."</td>";
				echo "<td style='text-align:center'>".$cantidad."</td>";
				echo "<td style='text-align:right'>".(empty($dataKardex[$i]['SaldoPrecio'])?'':number_format($dataKardex[$i]['SaldoPrecio'],2))."</td>";
				echo "<td style='text-align:right'>".(empty($dataKardex[$i]['SaldoCosto'])?'':number_format($dataKardex[$i]['SaldoCosto'],2))."</td>";
			echo "</tr>";
			$tecant=$cantidad+$dataKardex[$i]['EntradaCantidad'];
			$tecosto+=$dataKardex[$i]['EntradaCosto'];
			$tscant+=$dataKardex[$i]['SalidaCantidad'];
			$tscosto+=$dataKardex[$i]['SalidaCosto'];
		}
                echo "<tr>";
				echo "<td colspan=6></td>";
				echo "<th style='text-align:center'>".round($tecant)."</td>";
				echo "<td></td>";
				echo "<th style='text-align:right'>".number_format($tecosto,2)."</td>";
				echo "<th style='text-align:center'>".round($tscant)."</td>";
				echo "<td></td>";
				echo "<th style='text-align:right'>".number_format($tscosto,2)."</td>";
				echo "<td colspan=3></td>";
			echo "</tr>";
                    echo"</table>";
                        
                        }     
//		}
                }
             
                         
                        exit();
	
		$tecant=0;
		$tecosto=0;
		$tscant=0;
		$tscosto=0;
		$cont=0;
                $cantidad=0;
		if ($dataKardex[0]['codigotipooperacion']!=16) {
			echo "<tr>";
			echo "<td></td>";
			echo "<td colspan='4'>Saldo Inicial</td>";
			echo "<td>16</td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td></td>";
			if ($dataKardex[0]['tipomovimiento']==1) {
				$cantidad=round($dataKardex[0]['SaldoCantidad']-round($dataKardex[0]['cantidad']));
				if ($cantidad<0) {
					$cantidad=0;
				}
			}else{
				//$cantidad=round($dataKardex[0]['SaldoCantidad']+round($dataKardex[0]['cantidad']));
                                $cantidad=461;
			}
			
			echo "<td>".$cantidad."</td>";
			echo "<td>".$dataKardex[0]['SaldoPrecio']."</td>";
			echo "<td>".round($dataKardex[0]['SaldoPrecio']*$cantidad,2)."</td>";
			echo "</tr>";
		}
		for ($i=0; $i < $total; $i++) { 
			echo "<tr>";
				
				
				
				if ($dataKardex[$i]['codigotipooperacion']==16) {
					echo "<td></td>";
					echo "<td colspan='4'>Saldo Inicial</td>";
				}else{
					$cont++;
					echo "<td>".($cont)."</td>";
					echo "<td>".$dataKardex[$i]['fechamovimiento']."</td>";
					echo "<td>".$dataKardex[$i]['codigotipodocumento']."</td>";
					echo "<td>".$dataKardex[$i]['serie']."</td>";
					echo "<td>".$dataKardex[$i]['ndocumento']."</td>";
				}
				$cantidad=$cantidad-$dataKardex[$i]['SalidaCantidad'];
				echo "<td style='text-align:center'>".$dataKardex[$i]['codigotipooperacion']."</td>";
				echo "<td style='text-align:center'>".$dataKardex[$i]['EntradaCantidad']."</td>";
				echo "<td style='text-align:right'>".(empty($dataKardex[$i]['EntradaPrecio'])?'':number_format($dataKardex[$i]['EntradaPrecio'],2))."</td>";
				echo "<td style='text-align:right'>".(empty($dataKardex[$i]['EntradaCosto'])?'':number_format($dataKardex[$i]['EntradaCosto'],2))."</td>";
				echo "<td style='text-align:center'>".$dataKardex[$i]['SalidaCantidad']."</td>";
				echo "<td style='text-align:right'>".(empty($dataKardex[$i]['SalidaPrecio'])?'':number_format($dataKardex[$i]['SalidaPrecio'],2))."</td>";
				echo "<td style='text-align:right'>".(empty($dataKardex[$i]['SalidaCosto'])?'':number_format($dataKardex[$i]['SalidaCosto'],2))."</td>";
				echo "<td style='text-align:center'>".$cantidad."</td>";
				echo "<td style='text-align:right'>".(empty($dataKardex[$i]['SaldoPrecio'])?'':number_format($dataKardex[$i]['SaldoPrecio'],2))."</td>";
				echo "<td style='text-align:right'>".(empty($dataKardex[$i]['SaldoCosto'])?'':number_format($dataKardex[$i]['SaldoCosto'],2))."</td>";
			echo "</tr>";
			$tecant=$cantidad+$dataKardex[$i]['EntradaCantidad'];
			$tecosto+=$dataKardex[$i]['EntradaCosto'];
			$tscant+=$dataKardex[$i]['SalidaCantidad'];
			$tscosto+=$dataKardex[$i]['SalidaCosto'];
		}
			echo "<tr>";
				echo "<td colspan=6></td>";
				echo "<th style='text-align:center'>".round($tecant)."</td>";
				echo "<td></td>";
				echo "<th style='text-align:right'>".number_format($tecosto,2)."</td>";
				echo "<th style='text-align:center'>".round($tscant)."</td>";
				echo "<td></td>";
				echo "<th style='text-align:right'>".number_format($tscosto,2)."</td>";
				echo "<td colspan=3></td>";
			echo "</tr>";
	}
	
	
}
?>
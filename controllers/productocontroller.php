<?php
	class ProductoController extends ApplicationGeneral{
		private $mostrar=5;
		function lista(){
			/*$producto=new Producto();
			$totalRegistro=$producto->contarProducto();
			$pagina=($_REQUEST['id'])?$_REQUEST['id']:1;
			$inicio=($pagina-1)*$this->mostrar;
			$paginas=ceil($totalRegistro/$this->mostrar);
			$paginacion=array('Paginas'=>$paginas,'Pagina'=>$pagina);
			$data['producto']=$producto->listadoProductos($inicio,$this->mostrar);
			$data['paginacion']=$paginacion;
			$data['rutaImagen']=$this->rutaImagenesProducto();*/
			if (empty($_REQUEST['id'])) {
				$_REQUEST['id']=1;
			}
			session_start();
			$productos=$this->AutoLoadModel('producto');
			$marca=$this->AutoLoadModel('marca');
			$_SESSION['P_Producto']="";
			$dataProducto=$productos->listaProductosPaginado($_REQUEST['id']);
			
			$cantidadProducto=count($dataProducto);
			for ($i=0; $i <$cantidadProducto ; $i++) { 
				$dataMarca=$marca->listaxId($dataProducto[$i]['idmarca']);
				$dataProducto[$i]['Marca']=$dataMarca[0]['nombre'];
			}
			$data['producto']=$dataProducto;
			$paginacion=$productos->paginadoProducto();
			$data['paginacion']=$paginacion;
			$data['blockpaginas']=round($paginacion/10);
			
			$this->view->show("producto/lista.phtml",$data);
		}
		function listar2(){
			$producto=new Producto();
			$nombre = $_REQUEST['nombre'];
			$data=$producto->listadoProductos2($nombre);
			$objeto = $this->formatearparakui($data);
			header("Content-type: application/json");
			//echo "{\"data\":" .json_encode($objeto). "}";
			echo json_encode($objeto);
		}
		function buscarordencompra(){
			$idProducto=$_REQUEST['id'];
			$producto=new Producto();
			$data=$producto->buscaProductoOrdenCompra($idProducto);
			$unidadMedida=$this->unidadMedida();
			$empaque=$this->empaque();

			$dataRespuesta['codigo']=!empty($data[0]['codigopa'])?$data[0]['codigopa']:"";
			$dataRespuesta['idproducto']=!empty($data[0]['idproducto'])?$data[0]['idproducto']:"";
			$dataRespuesta['foto']=!empty($data[0]['imagen'])?$data[0]['imagen']:"";
			$dataRespuesta['nompro']=!empty($data[0]['nompro'])?$data[0]['nompro']:"";
			$dataRespuesta['nomemp']=!empty($data[0]['empaque'])?$data[0]['empaque']:"";
			$dataRespuesta['marca']=!empty($data[0]['marca'])?$data[0]['marca']:"";
			$dataRespuesta['nomum']=!empty($data[0]['unidadmedida'])?$data[0]['unidadmedida']:"";
			$dataRespuesta['fob']=!empty($data[0]['fob'])?$data[0]['fob']:"";
			$dataRespuesta['preciolista']=!empty($data[0]['preciolista'])?$data[0]['preciolista']:"";
			$dataRespuesta['descuentosolicitado']=!empty($data[0]['dunico'])?$data[0]['dunico']:"";
			$dataRespuesta['descuentovalor']=!empty($data[0]['valor'])?$data[0]['valor']:"";
			$dataRespuesta['stockactual']=!empty($data[0]['stockactual'])?$data[0]['stockactual']:"";

			echo json_encode($dataRespuesta);
		}

		function buscarxIdProducto(){
			$idProducto=$_REQUEST['idvalor'];
			$producto=new Producto();
			$marca=new Marca();
			$data=$producto->buscaProductoxId($idProducto);
			if (!empty($data[0]['idmarca'])) {
				$dataMarca=$marca->listaxId($data[0]['idmarca']);
				$marca=$dataMarca[0]['nombre'];
			}else{
				$marca="";
			}
			$dataRespuesta['valor']=$idProducto;
			$dataRespuesta['codigo']=$data[0]['codigopa'];
			$dataRespuesta['idproducto']=$data[0]['idproducto'];
			$dataRespuesta['nompro']=!empty($data[0]['nompro'])?$data[0]['nompro']:"";
			$dataRespuesta['marca']=!empty($data[0]['marca'])?$data[0]['marca']:"";
			$dataRespuesta['precio']=!empty($data[0]['preciocosto'])?$data[0]['preciocosto']:"";
			$dataRespuesta['preciolista']=!empty($data[0]['preciolista'])?$data[0]['preciolista']:"";
			$dataRespuesta['precioreferencia01']=!empty($data[0]['precioreferencia01'])?$data[0]['precioreferencia01']:0;
			$dataRespuesta['precioreferencia02']=!empty($data[0]['precioreferencia02'])?$data[0]['precioreferencia02']:0;
			$dataRespuesta['precioreferencia03']=!empty($data[0]['precioreferencia03'])?$data[0]['precioreferencia03']:0;
			$dataRespuesta['stockdisponible']=!empty($data[0]['stockdisponible'])?$data[0]['stockdisponible']:0;;
			$dataRespuesta['stockactual']=!empty($data[0]['stockactual'])?$data[0]['stockactual']:0;;

			echo json_encode($dataRespuesta);
			
		}

		function buscar(){
			if (!empty($_REQUEST['idvalor'])) {
				$idProducto=$_REQUEST['idvalor'];
			}else{
				$idProducto=$_REQUEST['id'];
				$descuento=new Descuento();
				$dscto=$descuento->buscarxid($_REQUEST['parameters'][1]);
			}
			

			$marca=$this->AutoLoadModel('marca');
			$unidad=$this->AutoLoadModel('unidadmedida');
			$almacen=$this->AutoLoadModel('almacen');
			$producto=new Producto();
			$data=$producto->buscaProductoOrdenCompra($idProducto);
			
			
			if ($data[0]['unidadmedida']) {
				$unidadMedida=$unidad->buscaUnidadMedida($data[0]['unidadmedida']);
			}
			if ($data[0]['idalmacen']) {
				$dataAlmacen=$almacen->buscaAlmacen($data[0]['idalmacen']);
			}
			
			$empaque=$this->empaque();
			$dataMarca=$marca->listado();
			for ($i=0; $i <count($dataMarca) ; $i++) { 
				if (!empty($data[0]['idmarca']) && $data[0]['idmarca']==$dataMarca[$i]['idmarca']) {
					$data[0]['idmarca']=$dataMarca[$i]['nombre'];
				}
			}
			/*echo '{
				"codigo":"'.$data[0]['codigopa'].'",
				"idproducto":"'.$data[0]['idproducto'].'",
				"foto":"'.$data[0]['imagen'].'",
				"nompro":"'.str_replace('"', '\"', $data[0]['nompro']).'",
				"nomemp":"'.$empaque[($data[0]['empaque'])].'",
				"marca":"'.$data[0]['idmarca'].'",
				"nomum":"'.$unidadMedida[($data[0]['unidadmedida'])].'",
				"fob":"'.$data[0]['fob'].'",
				"preciolista":"'.$data[0]['preciolista'].'",
				"descuentosolicitado":"'.$dscto[0]['dunico'].'",
				"descuentovalor":"'.$dscto[0]['valor'].'",
				"stockactual":"'.$data[0]['stockdisponible'].'"
			}';*/
			//$dataJson['codigo']=$data[0]['codigopa'];
			
			$dataRespuesta['codigo']=$data[0]['codigopa'];
			$dataRespuesta['nompro']=str_replace('"', '\"', $data[0]['nompro']);
			$dataRespuesta['idproducto']=$data[0]['idproducto'];
			$dataRespuesta['foto']=(empty($data[0]['imagen'])?"":$data[0]['imagen']);
			$dataRespuesta['marca']=(empty($data[0]['idmarca'])?"":$data[0]['idmarca']);
			$dataRespuesta['nomum']=(empty($unidadMedida[0]['codigo'])?"":$unidadMedida[0]['codigo']);
			$dataRespuesta['codigoalmacen']=(empty($dataAlmacen[0]['codigoalmacen'])?"":$dataAlmacen[0]['codigoalmacen']);
			$dataRespuesta['fob']=(empty($data[0]['fob'])?"":$data[0]['fob']);
			$dataRespuesta['preciolista']=(empty($data[0]['preciolista'])?"":$data[0]['preciolista']);
			$dataRespuesta['preciolistadolares']=(empty($data[0]['preciolistadolares'])?"":$data[0]['preciolistadolares']);			
			$dataRespuesta['preciocosto']=(empty($data[0]['preciocosto'])?"":$data[0]['preciocosto']);
			$dataRespuesta['descuentosolicitado']=(empty($dscto[0]['dunico'])?"0":$dscto[0]['dunico']);
			$dataRespuesta['descuentovalor']=(empty($dscto[0]['valor'])?"":$dscto[0]['valor']);
			$dataRespuesta['stockactual']=(empty($data[0]['stockdisponible'])?"":$data[0]['stockdisponible']);
			echo json_encode($dataRespuesta);
		}		
		function buscarCodigo(){
			$codigoProducto=$_REQUEST['id'];
			$codigoProducto=html_entity_decode($codigoProducto,ENT_QUOTES,'UTF-8');
			$producto=new Producto();
			$data=$producto->buscaxcodigo($codigoProducto);
			$dataR['codigo']=$data[0]['codigopa'];
			echo json_encode($dataR);
			//echo '{"codigo":"'.$data[0]['codigopa'].'"}';
		}

		function contar(){
			$cod=$_REQUEST['codPro'];
			$producto=new Producto();
			$cant=$producto->contarProducto($cod);
			echo '{"cant":"'.$cant.'"}';
		}
		function existe(){
			$codigoProducto=$_REQUEST['id'];
			$producto=new Producto();
			$existe=$producto->existeProducto($codigoProducto);
			echo '{"existe":"'.$existe.'"}';
		}
		function buscarAutocomplete(){
			$texIni=$_REQUEST['term'];
			$idLinea=$_REQUEST['idlinea'];
			$producto=new Producto();
			if($_REQUEST['idlinea']){
				$data=$producto->buscaProductoAutocomplete($texIni,$idLinea);
			}else{
				$data=$producto->buscaProductoAutocomplete($texIni);
			}
			echo json_encode($data);
		}
              	function buscarAutocompleteLimpio(){
			$texIni=$_REQUEST['term'];
			$idLinea=$_REQUEST['idlinea'];
			$producto=new Producto();
			if($_REQUEST['idlinea']){
				$data=$producto->buscaProductoAutocompleteLimpio($texIni,$idLinea);
			}else{
				$data=$producto->buscaProductoAutocompleteLimpio($texIni);
			}
			echo json_encode($data);
		}
		function buscarAutocompleteCompras(){
			$texIni=$_REQUEST['term'];
			$idLinea=$_REQUEST['idlinea'];
			$producto=new Producto();
			if($_REQUEST['idlinea']){
				$data=$producto->buscaProductoAutocompleteCompras($texIni,$idLinea);
			}else{
				$data=$producto->buscaProductoAutocompleteCompras($texIni);
			}
			echo json_encode($data);
		}

		function nuevo(){
			$almacen=new Almacen();
			
			$linea=new Linea();
			$proveedor=new Proveedor();
			$sublinea=new sublinea();
			$marca=new marca();
			$empaque=$this->AutoLoadModel('empaque');
			$unidadmedida=$this->AutoLoadModel('unidadmedida');
			$data['Almacen']=$almacen->listado();
			$data['Linea']=$linea->listadoLineas();
			$data['sublinea']=$sublinea->listaSublinea('idpadre!=0');
			$data['Empaque']=$empaque->listarEmpaque();
			$data['Unidadmedida']=$unidadmedida->listadoUnidadmedidas();
			$data['Proveedor']=$proveedor->listadoProveedores();
			$data['marca']=$marca->listado();
			$this->view->show("producto/nuevo.phtml",$data);
		}
		function graba(){
			$producto=new Producto();
			$pp=new Proveedorproducto();
			$movimiento=$this->AutoLoadModel('movimiento');
			$detallemovimiento=$this->AutoLoadModel('detallemovimiento');

			$data=$_REQUEST['Producto'];
			$data2=$_REQUEST['ProductoProveedor'];
			$data['imagen']=$_FILES['foto']['name'];
			$data['estado']=1;

			$exito=$producto->grabaProducto($data);
			$data2['idProducto']=$exito;
			if($exito){
				if (!empty($data['preciolista']) && !empty($data['preciocosto'])) {
					$dataMovimiento['conceptomovimiento']=1;
					$dataMovimiento['tipomovimiento']=3;
					$dataMovimiento['idtipooperacion']=7;
					$dataMovimiento['observaciones']='Carga de Saldos Iniciales del Producto';
					$dataMovimiento['fechamovimiento']=date('Y-m-d');

					$graba=$movimiento->grabaMovimiento($dataMovimiento);
					if ($graba) {
						$dataDetalleMovimiento['idmovimiento']=$graba;
						$dataDetalleMovimiento['idproducto']=$exito;
						$dataDetalleMovimiento['stockactual']=0;
						$dataDetalleMovimiento['stockdisponibledm']=0;
						$dataDetalleMovimiento['cantidad']=0;
						$dataDetalleMovimiento['preciovalorizado']=$data['preciocosto'];
						$dataDetalleMovimiento['pu']=$data['preciocosto'];
						$dataDetalleMovimiento['importe']=($data['preciocosto']*$data['stockactual']);
						$dataDetalleMovimiento['estado']=1;
						$graba2=$detallemovimiento->grabaDetalleMovimieto($dataDetalleMovimiento);
						}
					
				}
				
				$codigo=$producto->GeneraCodigo();
				$this->guardaImagenesFormulario($data['codigopa']);
				$pp->grabaProveedorProducto($data2);
				$ruta['ruta']="/producto/lista/";
				$this->view->show("ruteador.phtml",$ruta);
			}
			
		}
		function editar(){
			$id=$_REQUEST['id'];
			$producto=new Producto();
			$almacen=new Almacen();
			$linea=new Linea();
			$sublinea=new Sublinea();
			
			$marca=new marca();
			$empaque=$this->AutoLoadModel('empaque');
			$unidadmedida=$this->AutoLoadModel('unidadmedida');
			$dataProducto=$producto->buscaProducto($id);
			$idLinea=$linea->buscaLineaPorSublinea($dataProducto[0]['idlinea']);
			$data['Producto']=$producto->buscaProducto($id);
			//echo '<pre>';
			//print_r($data['Producto']);
			//exit;
			$data['Almacen']=$almacen->listadoAlmacen();
			$data['Linea']=$linea->listadoLineas();
			$data['Sublinea']=$sublinea->listadoSublinea($idLinea);	
			$data['Empaque']=$empaque->listarEmpaque();
			$data['Unidadmedida']=$unidadmedida->listadoTotal();
			$data['RutaImagen'] = $this->rutaImagenesProducto();
			$data['marca']=$marca->listado();
			$this->view->show("/producto/editar.phtml",$data);
		}
		function actualiza(){
			$id=$_REQUEST['idProducto'];
			$data=$_REQUEST['Producto'];
			if(count($_FILES)){
				$data['imagen']=$_FILES['foto']['name'];	
			}
			
			$producto=new Producto();
			$this->guardaImagenesFormulario($data['codigopa']);
			$exito=$producto->actualizaProducto($data,$id);

			/*print_r($_FILES);
			echo '<pre>';
			print_r($data);
			echo 'exito= '.$exito .'</br>';
			echo $id;
			echo exit; */
			
			if($exito){
				$ruta['ruta']="/producto/lista/";
				$this->view->show("ruteador.phtml",$ruta);
			}
		}
		function elimina(){
			$id=$_REQUEST['id'];
			$producto=new  Producto();
			$exito=$producto->eliminaProducto($id);
			if($exito){
				$ruta['ruta']="/producto/lista/";
				$this->view->show("ruteador.phtml",$ruta);
			}
		}
		function cantidadStock(){
			$idProducto=$_REQUEST['id'];
			$producto=new Producto();
			$data=$producto->buscaProducto($idProducto);
			echo '{"stockDisponible":"'.$data[0]['stockdisponible'].'"}';
		}
		function cantidadStockFisico(){
			$idProducto=$_REQUEST['id'];
			$producto=new Producto();
			$data=$producto->buscaProducto($idProducto);
			echo '{"stockDisponible":"'.$data[0]['stockactual'].'"}';
		}
		function busqueda(){
			$producto=New Producto();
			$Producto=$producto->listadoProductos();
			$objeto = $this->formatearparakui($Producto);
			header("Content-type: application/json");
			//echo "{\"data\":" .json_encode($objeto). "}";
			echo json_encode($objeto);
		}
		function autocomplete(){
			$producto=new Producto();
			$text=$_REQUEST['id'];
			$datos=$producto->autocomplete($text);
			echo json_encode($datos);
		}
		function busca(){
			$productos=$this->AutoLoadModel('producto');
			if (empty($_REQUEST['id'])) {
				$_REQUEST['id']=1;
			}
			session_start();
			$_SESSION['P_Producto'];
			if (!empty($_REQUEST['txtBusqueda'])) {
				$_SESSION['P_Producto']=$_REQUEST['txtBusqueda'];
			}

			$parametro=$_SESSION['P_Producto'];
			$paginacion=$productos->paginadoProductosxnombre($parametro);
			$data['retorno']=$this->limpiarString($parametro);
			$data['producto']=$productos->listaProductosPaginadoxnombre($_REQUEST['id'],$parametro);
			$data['paginacion']=$paginacion;
			$data['blockpaginas']=round($paginacion/10);
			$data['totregistros']=count($productos->BuscarRegistrosxnombre($parametro));

			$this->view->show("/producto/busca.phtml",$data);
		}

		function validarCodigo(){
			$productos=$this->AutoLoadModel('producto');
			$codigo=$_POST['Producto'];
			$valorEncontrado=$productos->buscaxcodigo($codigo['codigopa']);
			if (empty($valorEncontrado) || $valorEncontrado="") {
				$codigo['error']="Codigo Aceptado";
				$codigo['verificado']=true;
				echo json_encode($codigo);
			}else{
				$codigo['verificado']=false;
				$codigo['error']="El Codigo del Producto ya Existe";
				echo json_encode($codigo);
			}
		}

		function agregaStockdisponible(){
			$idproducto=$_REQUEST['idproducto'];
			$cantidad=$_REQUEST['cantidad'];

			$producto=$this->AutoLoadModel('producto');
			//recuperamos el stockdisponible
			$dataBusqueda=$producto->buscaProducto($idproducto);
			$stockdisponibleA=$dataBusqueda[0]['stockdisponible'];

			$nuevostockdisponible=$stockdisponibleA+$cantidad;

			$data['stockdisponible']=$nuevostockdisponible;

			$exito=$producto->actualizaProducto($data,$idproducto);
			echo $exito;
			
		}
		function disminuyeStockdisponible(){
			$idproducto=$_REQUEST['idproducto'];
			$cantidad=$_REQUEST['cantidad'];

			$producto=$this->AutoLoadModel('producto');
			//recuperamos el stockdisponible
			$dataBusqueda=$producto->buscaProducto($idproducto);
			$stockdisponibleA=$dataBusqueda[0]['stockdisponible'];

			$nuevostockdisponible=$stockdisponibleA-$cantidad;

			$data['stockdisponible']=$nuevostockdisponible;

			$exito=$producto->actualizaProducto($data,$idproducto);
			echo $exito;
		}

		/*
		Funcion:	
		*/
		function GenerarListaPrecios(){
			$compras=$this->AutoLoadModel("ordencompra");
		}

		/*
		Funcion: Permite actualizar la imagen de algun producto.
		Creado por: Fernando García Atúncar
		Fecha: 04.04.2013
		Descripcion: Se utilzia en Productos/ModificarImagen
		*/
		function actualizarImagen(){
			$this->view->show("/producto/cambiarImagen.phtml",$data);
		}

		function creaSaldoInicial(){
			$movimiento=$this->AutoLoadModel('movimiento');
			$detallemovimiento=$this->AutoLoadModel('detallemovimiento');
			$producto=$this->AutoLoadModel('producto');
			$dataProducto=$producto->listadoProductos();
			$cantidadProducto=count($dataProducto);

			$cont=0;
			$cont2=0;
			$dataMovimiento['conceptomovimiento']=1;
			$dataMovimiento['tipomovimiento']=3;
			$dataMovimiento['idtipooperacion']=7;
			$dataMovimiento['observaciones']='Carga de Saldos Iniciales del Producto';
			$dataMovimiento['fechamovimiento']=date('Y-m-d');

			$graba=$movimiento->grabaMovimiento($dataMovimiento);
			if ($graba) {
				
				for ($i=0; $i <$cantidadProducto ; $i++) { 
					$data['stockdisponible']=$dataProducto[$i]['stockactual'];
					$exito=$producto->actualizaProducto($data,$dataProducto[$i]['idproducto']);
					

					if ($exito) {
						$dataDetalleMovimiento['idmovimiento']=$graba;
						$dataDetalleMovimiento['idproducto']=$dataProducto[$i]['idproducto'];
						$dataDetalleMovimiento['stockactual']=$dataProducto[$i]['stockactual'];
						$dataDetalleMovimiento['stockdisponibledm']=$dataProducto[$i]['stockdisponible'];
						$dataDetalleMovimiento['cantidad']=$dataProducto[$i]['stockactual'];
						$dataDetalleMovimiento['preciovalorizado']=$dataProducto[$i]['preciocosto'];
						$dataDetalleMovimiento['pu']=$dataProducto[$i]['preciocosto'];
						$dataDetalleMovimiento['importe']=($dataProducto[$i]['preciocosto']*$dataProducto[$i]['stockactual']);
						$dataDetalleMovimiento['estado']=1;

						$graba2=$detallemovimiento->grabaDetalleMovimieto($dataDetalleMovimiento);
						if (!$graba2) {
							$cont2++;
						}
					}else{
						$cont++;
					}
					

				}
			}
			if (!$graba) {
				echo 'No se grabo Nada';
			}else{
				echo 'Errores en Actualizar Productos: '.$cont;
				echo '<br>';
				echo 'Errores en Grabar Detalles de Movimiento: '.$cont2;
			}
			
		}
		function precioreferencia(){

			$this->view->show('/producto/precioReferencia.phtml',$data);
		}
		function actualizaProductoJson(){
			$producto=$this->AutoLoadModel('producto');
			$id=$_REQUEST['idProducto'];
			$data['precioreferencia01']=$_REQUEST['precioreferencia01'];
			$data['precioreferencia02']=$_REQUEST['precioreferencia02'];
			$data['precioreferencia03']=$_REQUEST['precioreferencia03'];
			$exito=$producto->actualizaProducto($data,$id);
			if ($exito) {
				$dataRespuesta['respuesta']=true;
			}else{
				$dataRespuesta['respuesta']=false;
			}
			echo json_encode($dataRespuesta);
		}
		function busqueda_productos(){
			$this->view->show('/producto/busqueda_productos.phtml',$data);
		}

		function productosAgotados(){
			$reporte=$this->AutoLoadModel('reporte');
			$idLinea=$_REQUEST['lstLinea'];
			$idSubLinea=$_REQUEST['lstSubLinea'];
			$idMarca=$_REQUEST['lstMarca'];
			$idAlmacen=$_REQUEST['lstAlmacen'];
			$idProducto=$_REQUEST['idProducto'];

			if (!empty($_REQUEST['fechaInicio'])) {
				$fechaInicio=date('Y-m-d',strtotime($_REQUEST['fechaInicio']));
			}else{
				$fechaInicio=$_REQUEST['fechaInicio'];
			}

			if (!empty($_REQUEST['fechaFinal'])) {
				$fechaFinal=date('Y-m-d',strtotime($_REQUEST['fechaFinal']));
			}else{
				$fechaFinal=$_REQUEST['fechaFinal'];
			}
			$dataBusqueda=$reporte->reporteProductoAgotados($idLinea,$idSubLinea,$idMarca,$idAlmacen,$idProducto,$fechaInicio,$fechaFinal);
			$cantidad=count($dataBusqueda);

			if ($cantidad>0) {
				
			
				$fila="<tr>";
				$fila.="<th>Codigo</th>";
				$fila.="<th>Nombre Producto</th>";
				$fila.="<th>Nombre Almacen</th>";
				$fila.="<th>Nombre Marca</th>";
				$fila.="<th>Nombre SubLinea</th>";
				$fila.="<th>Precio Lista</th>";
				$fila.="<th>Fecha Agotada</th>";
				$fila.="</tr>";
				for ($i=0; $i <$cantidad ; $i++) { 
					$fila.="<tr>";
					$fila.="<td>".$dataBusqueda[$i]['codigopa']."</td>";
					$fila.="<td>".$dataBusqueda[$i]['nompro']."</td>";
					$fila.="<td>".$dataBusqueda[$i]['razsocalm']."</td>";
					$fila.="<td>".$dataBusqueda[$i]['nombre']."</td>";
					$fila.="<td>".$dataBusqueda[$i]['nomlin']."</td>";
					$fila.="<td>S/.".number_format($dataBusqueda[$i]['preciolista'],2)."</td>";
					$fila.="<td>".date("Y-m-d",strtotime($dataBusqueda[$i]['fechaagotado']))."</td>";
					$fila.="</tr>";
				}
			}
			echo $fila;

		}
		function productosVendidos(){
			$reporte=$this->AutoLoadModel('reporte');
			$idLinea=$_REQUEST['lstLinea'];
			$idSubLinea=$_REQUEST['lstSubLinea'];
			$idMarca=$_REQUEST['lstMarca'];
			$idAlmacen=$_REQUEST['lstAlmacen'];
			$fechaInicio=$_REQUEST['fechaInicio'];
			$fechaFinal=$_REQUEST['fechaFinal'];
			$idProducto=$_REQUEST['idProducto'];

			$dataBusqueda=$reporte->reporteProductoVendidos($idLinea,$idSubLinea,$idMarca,$idAlmacen,$idProducto,$fechaInicio,$fechaFinal);
			//print_r($dataBusqueda);
			//exit;
			$cantidad=count($dataBusqueda);
			if ($cantidad>0) {
				
			
				$fila="<tr>";
				$fila.="<th>Codigo</th>";
				$fila.="<th>Nombre Producto</th>";
				$fila.="<th>Nombre Almacen</th>";
				$fila.="<th>Nombre Marca</th>";
				$fila.="<th>Nombre SubLinea</th>";
				$fila.="<th>Precio Lista</th>";
				$fila.="<th title='Stock Actual'>S. A.</th>";
				$fila.="<th title='Stock Disponible'>S. Dis.</th>";+
				$fila.="<th title=''>C. Vendida</th>";
				$fila.="</tr>";
				for ($i=0; $i <$cantidad ; $i++) { 
					$fila.="<tr>";
					$fila.="<td>".$dataBusqueda[$i]['codigopa']."</td>";
					$fila.="<td>".$dataBusqueda[$i]['nompro']."</td>";
					$fila.="<td>".$dataBusqueda[$i]['razsocalm']."</td>";
					$fila.="<td>".$dataBusqueda[$i]['nombre']."</td>";
					$fila.="<td>".$dataBusqueda[$i]['nomlin']."</td>";
					$fila.="<td>S/.".number_format($dataBusqueda[$i]['preciolista'],2)."</td>";
					$fila.="<td>".$dataBusqueda[$i]['stockactual']."</td>";
					$fila.="<td>".$dataBusqueda[$i]['stockdisponible']."</td>";
					$fila.="<td>".$dataBusqueda[$i]['cantidadvendida']."</td>";
					$fila.="</tr>";
				}
			}
			echo $fila;
		}

		function valorizaxlinea(){
			$producto=$this->AutoLoadModel("Producto");
			$dataProducto=$producto->ValorizadoxLinea();
			$this->AutoLoadLib(array('GoogChart','GoogChart.class'));
			$data['datos']=$dataProducto;
			$data['grafico']=new GoogChart();
			$this->view->show("/producto/valorizadoxlinea.phtml",$data);		
		}

	}
?>
<?php 
Class PDFController extends applicationgeneral{

	function __construct(){
		parent::__construct();
		ob_end_clean();
	}

	function index(){
		$this->pdf_reportes=New pdf_reportes("L","mm","A4");
		$this->pdf_reportes->_titulo="Reporte de Vendedores";
		$this->pdf_reportes->AliasNbPages();
		$this->pdf_reportes->AddPage();
		$this->pdf_reportes->SetFont('Arial','B',10);//
		$this->pdf_reportes->Cell(0,10,'Hola, Mundo');
		$this->pdf_reportes->Output(); //'ReporteVendedores.pdf','D'
	}
	function listaLinea(){
		$this->pdf_reportes=New pdf_reportes("L","mm","A4");
		$linea=$this->AutoLoadModel('linea');
		$data=$linea->listadoLineas();
		$titulos=array('id','nombre');
		$columnas=array('idlinea','nomlin');
		$ancho=array(40, 70);
		
		$this->pdf_reportes->_titulo="Reporte de Linea";
		$this->pdf_reportes->AddPage();
		$this->pdf_reportes->SetFont('Arial','B',10);//

		$this->pdf_reportes->ln();

		$this->pdf_reportes->PintaTablaN($titulos,$data,$columnas,$ancho);
		
		$this->pdf_reportes->AliasNbPages();
		$this->pdf_reportes->Output();
	}

	function inventario(){
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
		if (empty($dataProducto)) {
			$data[$indice]['codigo']="";
			$data[$indice]['nompro']="";
			$data[$indice]['preciolista']="";
			$data[$indice]['stockactual']="";
			$data[$indice]['stockporllegar']="";
			$data[$indice]['stockpordespachar']="";
			$data[$indice]['unidadmedida']="";
			$data[$indice]['empaque']="";
		}else{
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
		}
		
		$cantidadData=count($data);
		$pdf = new PDF_Mc_Table("L","mm","A4");
		$titulos=array('Codigo','nombre','Pre Lista','S/Atc','S/Llegar','S/Desp','U. M.','Empaque');
		$columnas=array('codigo','nompro','preciolista','stockactual','stockporllegar','stockpordespachar','unidadmedida','empaque');
		$ancho=array(25,132,25,20,20,20,15,20);
		$orientacion=array('','','R','R','R','R','C','C');
		
		$pdf->_titulo="Reporte de Inventario";
		$pdf->AddPage();

		$relleno=true;
		$pdf->SetFillColor(202,232,234);
		$pdf->SetTextColor(12,78,139);
		$pdf->SetDrawColor(12,78,139);
		$pdf->SetLineWidth(.3);
		$pdf->SetFont('Helvetica','B', '8');
		$pdf->fill($relleno);
		//un arreglo con su medida  a lo ancho

		$pdf->SetWidths($ancho);
		$valor="Reporte de Ventas";
		$pdf->titlees($valor);
		//un arreglo con alineacion de cada celda

		$pdf->SetAligns($orientacion);

		for($i=0;$i<count($titulos);$i++){
		    $pdf->Cell($ancho[$i],7,$titulos[$i],1,0,'C',true);

		    }
		$pdf->Ln();
		$pdf->SetFillColor(224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetFont('');
		for ($i=0; $i <$cantidadData ; $i++) { 

			$fila=array($data[$i]['codigo'],$data[$i]['nompro'],utf8_decode($data[$i]['preciolista']),$data[$i]['stockactual'],$data[$i]['stockporllegar'],utf8_decode($data[$i]['stockpordespachar']),$data[$i]['unidadmedida'],$data[$i]['empaque']);
			$pdf->Row($fila);
			$relleno=!$relleno;
			$pdf->fill($relleno);
		}
		$pdf->AliasNbPages();
		$pdf->Output();

	}
	function StockProducto(){

		$idAlmacen=$_REQUEST['idAlmacen'];
		$idLinea=$_REQUEST['idLinea'];
		$idSubLinea=$_REQUEST['idSubLinea'];
		$idProducto=$_REQUEST['idProducto'];
		$repote=new Reporte();
		$data=$repote->reporteStockProducto($idAlmacen,$idLinea,$idSubLinea,$idProducto);
		// print_r($data[0]);
		// exit;
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

		$cantidadData=count($data2);

		/**/
		$pdf = new PDF_Mc_Table("L","mm","A4");
		$titulos=array('Codigo','Descripcion','Almacen','Linea','P. L.(S/.)','P. L.(US $)','U.M','S/Act','S/Desp');
		$columnas=array('codigo','nompro','nomalm','nomlin','preciolista','preciolistadolares','unidadmedida','stockactual','stockdisponible');
		$ancho=array(20,75,55,50,16,16,15,13,13);
		$orientacion=array('C','','','','R','R','C','R','R');
		$pdf->_titulo="Reporte de Stock Producto";
		

		$pdf->AddPage();

		$relleno=true;
		$pdf->SetFillColor(202,232,234);
		$pdf->SetTextColor(12,78,139);
		$pdf->SetDrawColor(12,78,139);
		$pdf->SetLineWidth(.3);
		$pdf->SetFont('Helvetica','B', 8);
		$pdf->fill($relleno);
		//un arreglo con su medida  a lo ancho

		$pdf->SetWidths($ancho);
		
		//un arreglo con alineacion de cada celda

		$pdf->SetAligns($orientacion);

		for($i=0;$i<count($titulos);$i++){
		    $pdf->Cell($ancho[$i],7,$titulos[$i],1,0,'C',true);

		    }
		$pdf->Ln();
		$pdf->SetFillColor(224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetFont('');
		for ($i=0; $i <$cantidadData ; $i++) { 

			$fila=array(html_entity_decode($data2[$i]['codigo'],ENT_QUOTES,'UTF-8'),html_entity_decode($data2[$i]['nompro'],ENT_QUOTES,'UTF-8'),html_entity_decode($data2[$i]['nomalm'],ENT_QUOTES,'UTF-8'),(html_entity_decode(utf8_decode($data2[$i]['nomlin']),ENT_QUOTES,'UTF-8')),$data2[$i]['preciolista'],$data2[$i]['preciolistadolares'],$data2[$i]['unidadmedida'],$data2[$i]['stockactual'],$data2[$i]['stockdisponible']);
			$pdf->Row($fila);
			$relleno=!$relleno;
			$pdf->fill($relleno);
		}
		$pdf->AliasNbPages();
		$pdf->Output();
	}
	function ventas(){		
		
		
		$idLinea = $_REQUEST['linea'];
		$idVendedor = $_REQUEST['vendedor'];
		$fInicial = $_REQUEST['fechaInicial'];
		$fFinal = $_REQUEST['fechaFinal'];
		$ordenVenta = new OrdenVenta();
		$data = $ordenVenta->listadoReporteVentas($idLinea, $idVendedor, $fInicial, $fFinal);
		$cantidadData=count($data);

		$pdf = new PDF_Mc_Table("L","mm","A4");
		$titulos=array('Fecha','Orden de Venta','Cliente','Importe','Saldo','Condicion','Vendedor','Vencimiento');
		$columnas=array('fordenventa','codigov','razonsocial','importeordencobro','importedoc','condicion','vendedor','fvencimiento');
		$ancho=array(20,28,75,20,18,21,70,27);
		$orientacion=array('C','C','','R','R','C','','C');
		$pdf->_titulo="Reporte de Ventas";
		

		$pdf->AddPage();

		$relleno=true;
		$pdf->SetFillColor(202,232,234);
		$pdf->SetTextColor(12,78,139);
		$pdf->SetDrawColor(12,78,139);
		$pdf->SetLineWidth(.3);
		$pdf->SetFont('Helvetica','B', 8);
		$pdf->fill($relleno);
		//un arreglo con su medida  a lo ancho

		$pdf->SetWidths($ancho);
		
		//un arreglo con alineacion de cada celda

		$pdf->SetAligns($orientacion);

		for($i=0;$i<count($titulos);$i++){
		    $pdf->Cell($ancho[$i],7,$titulos[$i],1,0,'C',true);

		    }
		$pdf->Ln();
		$pdf->SetFillColor(224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetFont('');
		for ($i=0; $i <$cantidadData ; $i++) { 

			$fila=array($data[$i]['fordenventa'],$data[$i]['codigov'],utf8_decode($data[$i]['razonsocial']),$data[$i]['importeordencobro'],$data[$i]['importedoc'],$data[$i]['condicion'],utf8_decode($data[$i]['vendedor']),$data[$i]['fvencimiento']);
			$pdf->Row($fila);
			$relleno=!$relleno;
			$pdf->fill($relleno);
		}
		$pdf->AliasNbPages();
		$pdf->Output();
	}
	function ListaPrecios(){
		$idAlmacen=$_REQUEST['idAlmacen'];
		$idLinea=$_REQUEST['idLinea'];
		$idSubLinea=$_REQUEST['idSubLinea'];
		$idProducto=$_REQUEST['idProducto'];
		$idmoneda=$_REQUEST['idmoneda'];


		$reporte=new Reporte();
		$linea=$this->AutoLoadModel('linea');
		$tipoCambio=$this->AutoLoadModel('tipocambio');
		$data=$reporte->reporteListaPrecio($idAlmacen,$idLinea,$idSubLinea,$idProducto);
		$rutaImagen=$this->rutaImagenesProducto();
		$unidadMedida=$this->unidadMedida();
	
		$data2=array();
		for($i=0;$i<count($data);$i++){
			$data2[$i]['codigo']=$data[$i]['codigopa'];
			$data2[$i]['nompro']=$data[$i]['nompro'];
			
			
			if($idmoneda==1){
				$data2[$i]['preciolista']=$data[$i]['preciolista'];
				$simbmadel="S/. ";
				$simbmatras=" ";
			}
			if($idmoneda==2){
				$data2[$i]['preciolista']=$data[$i]['preciolistadolares'];	
				$simbmadel=" ";
				$simbmatras="  US $";			
			}
			$data2[$i]['stockactual']=$data[$i]['stockactual'];
			$data2[$i]['unidadmedida']=$data[$i]['nombremedida'];
			$data2[$i]['empaque']=empty($data[$i]['idempaque'])?'Sin/Emp.':$data[$i]['codempaque'];
			$data2[$i]['idpadre']=$data[$i]['idpadre'];
			$data2[$i]['idlinea']=$data[$i]['idlinea'];
			$data2[$i]['nomlin']=$data[$i]['nomlin'];
		}
		$valorCambio=$this->configIni($this->configIni("Globals", "Modo"), "TipoCambio");
		
		$cantidadData=count($data2);
		$pdf = new PDF_Mc_Table("P","mm","A4");
		//$titulos=array('Codigo','Descipcion','P. L.(S/.)','P.L.($)','Stock','U/M','Empaque');
		//$columnas=array('codigo','nompro','preciolista','stockactual','unidadmedida','empaque');
		$titulos=array('Codigo','Descipcion','P. L.','Stock','U/M','Empaque');
		$columnas=array('codigo','nompro','preciolista','stockactual','unidadmedida','empaque');		
		$ancho=array(25,95,15,15,15,15);
		$orientacion=array('','','R','C','C','C');
		
		$pdf->_titulo="Lista de Precios";
		$pdf->_fecha=date("d-m-Y");
		
		
		
		$pdf->AddPage();
		
		$relleno=true;
		$pdf->SetFillColor(202,232,234);
		$pdf->SetTextColor(12,78,139);
		$pdf->SetDrawColor(12,78,139);
		$pdf->SetLineWidth(.3);
		$pdf->SetFont('Helvetica','B', 7);
		$pdf->fill($relleno);
		//un arreglo con su medida  a lo ancho
		
		$pdf->SetWidths($ancho);
		//un arreglo con alineacion de cada celda
		
		$pdf->SetAligns($orientacion);
		$cantidadTitulos=count($titulos);
		for($i=0;$i<$cantidadTitulos;$i++){
			$pdf->Cell($ancho[$i],7,$titulos[$i],1,0,'C',true);
		
		}
		$pdf->Ln();
		$pdf->SetFillColor(224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetFont('');
		$lineaA=0;
		$subLineaA=0;
		for ($i=0; $i <$cantidadData ; $i++) {

			if ($lineaA!=$data2[$i]['idpadre']) {
				$lineaA=$data2[$i]['idpadre'];
				if ($i!=0) {
					//en este espacio entraria los anexos
					$pdf->AddPage();
					for($x=0;$x<$cantidadTitulos;$x++){
						$pdf->Cell($ancho[$x],7,$titulos[$x],1,0,'C',true);
					
					}
					$pdf->Ln();
				}
				
				$dataLinea=$linea->buscaLinea($lineaA);
				$pdf->SetFillColor(202,232,234);
				
				$pdf->SetFont('Helvetica','B', 9);
				$pdf->_datoPie=$dataLinea[0]['nomlin'];
				$pdf->Cell(195,6,"LINEA <<<<<<<>>>>>>> ".$dataLinea[0]['nomlin'],'B',0,'C',0);
				$pdf->SetFont('Helvetica','B', 7);
				$pdf->Ln();
				$pdf->Cell(195,1,"",'B',0,'C',0);
				
				$pdf->Ln();
				 
			}
			if ($subLineaA!=$data2[$i]['idlinea']) {
				$subLineaA=$data2[$i]['idlinea'];
				$pdf->SetFillColor(255,200,200);
				$pdf->Ln();
				$pdf->SetFont('Helvetica','B', 9);
				$pdf->Cell(195,6,utf8_decode("Sub Linea : ".$data2[$i]['nomlin']),1,0,'C',1);
				$pdf->SetFont('Helvetica','B', 7);
				$pdf->Ln();
				 
				$pdf->SetFillColor(224,235,255);
			}
			$fila=array(utf8_decode(html_entity_decode($data2[$i]['codigo'],ENT_QUOTES,'UTF-8')),utf8_decode(html_entity_decode($data2[$i]['nompro'],ENT_QUOTES,'UTF-8')),$simbmadel.number_format($data2[$i]['preciolista'],2).$simbmatras,$data2[$i]['stockactual'],$data2[$i]['unidadmedida'],$data2[$i]['empaque']);
			$pdf->Row($fila);
			$relleno=!$relleno;
			$pdf->fill($relleno);
		}
		$pdf->AliasNbPages();
		$pdf->Output();
	}
	function kardex(){
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
					$nombreCliente=($dc[0]['razonsocial']!="")?(html_entity_decode($dc[0]['razonsocial'],ENT_QUOTES,'UTF-8')):$dc[0]['nombres']." ".$dc[0]['apellidopaterno']." ".$dc[0]['apellidomaterno'];	
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

		$this->pdf_reportes=New pdf_reportes("L","mm","A4");
		$titulos=array('N Doc.','Fecha','Tipo','Concepto','Cant.','Origen/Destino','S/Disp','Medida','Precio','Estado');
		$columnas=array('ndocumento','fechamovimiento','conceptomovimiento','tipomovimiento','cantidad','nombrecliente','stockdisponible','unidadmedida','pu','estadopedido');
		$ancho=array(15,20,35,20,15,100,20,15,20,20);
		$orientacion=array('','C','C','C','C','','C','C','R','C');
		
		$this->pdf_reportes->_titulo="Reporte de kardex";
		$this->pdf_reportes->AddPage();
		$this->pdf_reportes->SetFont('Arial','B',10);//

		$this->pdf_reportes->ln();

		$this->pdf_reportes->PintaTablaN($titulos,$data2,$columnas,$ancho,$orientacion);
		
		$this->pdf_reportes->AliasNbPages();
		$this->pdf_reportes->Output();
	}

	function agotados(){
		$fecha = $_REQUEST['fecha'];
		$fechaInicio = $_REQUEST['fechaInicio'];
		$fechaFinal = $_REQUEST['fechaFinal'];
		$idProducto = $_REQUEST['idProducto'];

		$repote=new Reporte();
		$ordenCompra = new Ordencompra();
		$linea=new Linea();
		$cantidadDoc=0;
		$rutaImagen=$this->rutaImagenesProducto();
		$data=$repote->reporteAgotados($fecha,$fechaInicio,$fechaFinal,$idProducto);

		//$data=$repote->reporteAgotados('','','','');
		$unidadMedida=$this->unidadMedida();
		$cantidadData=count($data);
		for($i=0;$i<$cantidadData;$i++){
			$fu='';//Fecha ultima compra
			$fp='';//Fecha penultima compra
			$c1=0;//Cantidad 1
			$c2=0;//Cantidad 2
			$doc=$ordenCompra->lista2UltimasCompras($data[$i]['idproducto']);
			$cantidadDoc=count($doc);
			//Data orden compra
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
			$data[$i]['codigo']=$data[$i]['codigop'];
			$data[$i]['nompro']=$data[$i]['nompro'];
			$data[$i]['fechaultima']=date("d/m/Y",strtotime($fu));
			$data[$i]['cantidadultima']=$c1;
			$data[$i]['fechapenultima']=date("d/m/Y",strtotime($fp));
			$data[$i]['cantidadpenultima']=$c2;
			$data[$i]['nomlin']=$linea->nombrexid($data[$i]['idlinea']);
		}
		
		$pdf = new PDF_Mc_Table("L","mm","A4");
		$titulos=array('Penultima','C.Penul','Ultima','C. Ultima','codigo','Descripcion','Sublinea');
		$columnas=array('fechapenultima','cantidadpenultima','fechaultima','cantidadultima','codigo','nompro','nomlin');
		$ancho=array(20,15,25,18,22,110,70);
		$orientacion=array('C','C','C','C','C','','');
		
		$pdf->_titulo="Reporte de Agotados";
		

		$pdf->AddPage();

		$relleno=true;
		$pdf->SetFillColor(202,232,234);
		$pdf->SetTextColor(12,78,139);
		$pdf->SetDrawColor(12,78,139);
		$pdf->SetLineWidth(.3);
		$pdf->SetFont('Helvetica','B', 8);
		$pdf->fill($relleno);
		//un arreglo con su medida  a lo ancho

		$pdf->SetWidths($ancho);
		$valor="Reporte de Ventas";
		$pdf->titlees($valor);
		//un arreglo con alineacion de cada celda

		$pdf->SetAligns($orientacion);

		for($i=0;$i<count($titulos);$i++){
		    $pdf->Cell($ancho[$i],7,$titulos[$i],1,0,'C',true);

		    }
		$pdf->Ln();
		$pdf->SetFillColor(224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetFont('');
		for ($i=0; $i <$cantidadData ; $i++) { 

			$fila=array($data[$i]['fechapenultima'],$data[$i]['cantidadpenultima'],utf8_decode($data[$i]['fechaultima']),$data[$i]['cantidadultima'],$data[$i]['codigo'],utf8_decode($data[$i]['nompro']),$data[$i]['nomlin']);
			$pdf->Row($fila);
			$relleno=!$relleno;
			$pdf->fill($relleno);
		}
		$pdf->AliasNbPages();
		$pdf->Output();
		
		
		
	}
	function generaFactura(){
		$pdf=$this->AutoLoadModel('pdf');
		$ordenventa=$this->AutoLoadModel('documento');
		
		$buscaFactura=$ordenventa->buscaDocumento($_REQUEST['id'],"");

			
		$idDoc=$_REQUEST['id'];
		
		$porcentaje=$buscaFactura[0]['porcentajefactura'];
		$modo=$buscaFactura[0]['modofacturado'];
		$numeroFactura=$buscaFactura[0]['numdoc'];
		$numeroRelacionado=$buscaFactura[0]['numeroRelacionado'];
		$serieFactura=str_pad($buscaFactura[0]['serie'],3,'0',STR_PAD_LEFT);
		
		
		$dataFactura=$pdf->buscarxOrdenVenta($buscaFactura[0]['idordenventa']);
		$dataFactura[0]['numeroFactura']=$numeroFactura;
		$dataFactura[0]['serieFactura']=$serieFactura;
		$dataFactura[0]['fecha']=date('d/m/Y');
		$dataFactura[0]['referencia']='VEN: '.$dataFactura[0]['idvendedor'].' DC: '.$dataFactura[0]['idordenventa'];
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

		

		$total=0;
		for($i=0;$i<count($data);$i++){
				if($porcentaje!=""){
					if($modo==1){
						$precio=$data[$i]['preciofinal'];
						$data[$i]['preciofinal']=(($precio*$porcentaje)/100);
					}elseif($modo==2){
						$cantidad=$data[$i]['cantdespacho'];
						$data[$i]['catdespacho']=ceil(($cantidad*$porcentaje)/100);
					}
				}
				
				$precioTotal=($data[$i]['preciofinal'])*($data[$i]['cantdespacho']);
				$data[$i]['subtotal']=number_format($precioTotal,2);
			$total+=$precioTotal;
		}

		$dataFactura[0]['importeov']=$total;
		$dataFactura[0]['imprimir']='no';
		$dataFactura[0]['numeroRelacionado']=$numeroRelacionado;
		$this->pdf_facturas=New pdf_facturas("P","mm","A4");
		$this->pdf_facturas->SetLeftMargin(14.5);
		$this->pdf_facturas->SetAutoPageBreak(true,0);
		$this->pdf_facturas->SetTextColor(0);
        $this->pdf_facturas->SetFont('Times','',8.5);
		$this->pdf_facturas->AddPage();
		
		$this->pdf_facturas->ln();
		$this->pdf_facturas->ImprimeFactura($dataFactura,$data);
		$this->pdf_facturas->AliasNbPages();
		$this->pdf_facturas->Output();
	}
	function imprimirFactura(){
		$pdf=$this->AutoLoadModel('pdf');
		$ordenventa=$this->AutoLoadModel('documento');
		$cobro=$this->AutoLoadModel('ordencobro');
		$idDoc=$_REQUEST['idDoc'];
		$buscaFactura=$ordenventa->buscaDocumento($idDoc,"");
		$numeroRelacionado=$_REQUEST['numeroRelacionado'];	
		$tipodocumentorelacionado=$_REQUEST['tipodocumento'];

		//obtemos la condicion y tiempo de credito
		

		//obtemos los porcenajes y modo que fue facturado
		$porcentaje=$buscaFactura[0]['porcentajefactura'];
		$modo=$buscaFactura[0]['modofacturado'];
		$numeroFactura=$buscaFactura[0]['numdoc'];
		$serieFactura=str_pad($buscaFactura[0]['serie'],3,'0',STR_PAD_LEFT);
		
		
		//acutalizamos Documento que ya fue impreso,numero Relacionado y su tipo
		$dataV['esimpreso']=1;
		$dataV['numerorelacionado']=$numeroRelacionado;
		$dataV['tipoDocumentoRelacionado']=$tipodocumentorelacionado;
		$filtro="iddocumento='".$idDoc."'";
		$exitoE=$ordenventa->actualizarDocumento($dataV,$filtro);
		
		//Grabamos en 

		//*********************//
		$dataFactura=$pdf->buscarxOrdenVenta($buscaFactura[0]['idordenventa']);
		$dataFactura[0]['numeroRelacionado']=$numeroRelacionado;
		$dataFactura[0]['numeroFactura']=$numeroFactura;
		$dataFactura[0]['serieFactura']=$serieFactura;
		$dataFactura[0]['fecha']=date('d/m/Y');
		$dataFactura[0]['referencia']='VEN: '.$dataFactura[0]['idvendedor'].' DC: '.$dataFactura[0]['idordenventa'];
		$data=$pdf->buscarDetalleOrdenVenta($buscaFactura[0]['idordenventa']);

		$dataCobro=$pdf->buscarOrdenCompraxId($buscaFactura[0]['idordenventa']);
		if ($dataCobro[0]['escontado']==1 && $dataCobro[0]['escredito']==0 && $dataCobro[0]['esletras']==0) {
			$dataFactura[0]['condicion']='CONTADO';
		}elseif($dataCobro[0]['escredito']==1){
			$dataFactura[0]['condicion']='CREDITO';
		}elseif($dataCobro[0]['escredito']==1){
			$dataFactura[0]['condicion']='LETRAS';
			//$dataFactura[0]['fechavencimiento']=$pdf->listaDetalleOrdenCompraxId($dataCobro[0]['idordencobro'],3);
		}

		

		$total=0;
		for($i=0;$i<count($data);$i++){
				if($porcentaje!=""){
					if($modo==1){
						$precio=$data[$i]['precioaprobado'];
						$data[$i]['precioaprobado']=(($precio*$porcentaje)/100);
					}elseif($modo==2){
						$cantidad=$data[$i]['cantaprobada'];
						$data[$i]['cantaprobada']=ceil(($cantidad*$porcentaje)/100);
					}
				}
				
				$precioTotal=(($data[$i]['precioaprobado'])*($data[$i]['cantaprobada']))-($data[$i]['tdescuentoaprovado']);
				$data[$i]['subtotal']=number_format($precioTotal,2);
			$total+=$precioTotal;
		}

		$dataFactura[0]['importeov']=$total;
		
		$this->pdf_facturas=New pdf_facturas("P","mm","A4");
		$this->pdf_facturas->SetLeftMargin(14.5);
		$this->pdf_facturas->SetAutoPageBreak(true,0);
		$this->pdf_facturas->SetTextColor(0);
        $this->pdf_facturas->SetFont('Times','',8.5);
		$this->pdf_facturas->AddPage();
		
		$this->pdf_facturas->ln();
		$this->pdf_facturas->ImprimeFactura($dataFactura,$data);
		$this->pdf_facturas->AliasNbPages();
		$this->pdf_facturas->Output();
	}

	function generaGuiaRemision(){
		$pdf=$this->AutoLoadModel('pdf');
		$ordenventa=$this->AutoLoadModel('documento');
		$idDoc=$_REQUEST['idDoc'];
		$numero=$_REQUEST['numeroRelacionado'];
		$numeroRelacionado=$_REQUEST['numeroRelacionado'];	
		$tipodocumentorelacionado=$_REQUEST['tipodocumento'];
		$imprimir="";
		$tipo=$this->tipoDocumento();

		if (!empty($_REQUEST['id'])) {
			$idDoc=$_REQUEST['id'];
			$imprimir='no';

		}else{
			//acutalizamos Documento que ya fue impreso
			
		}

		
		$buscaGuia=$ordenventa->buscaDocumento($idDoc,"");
		

		session_start();
		$usuario=$_SESSION['nombres'].$_SESSION['apellidopaterno'];
		
		$dataGuia=$pdf->buscarxOrdenVenta($buscaGuia[0]['idordenventa']);
		$dataGuia[0]['imprimir']=$imprimir;

		if (!empty($_REQUEST['id'])) {
			$dataGuia[0]['tipo']=$buscaGuia[0]['tipoDocumentoRelacionado'];
			$dataGuia[0]['numeroRelacionado']=$buscaGuia[0]['numeroRelacionado'];
		}else{
			$dataGuia[0]['tipo']=$tipodocumentorelacionado;
			$dataGuia[0]['numeroRelacionado']=$numero;
		}
		$dataGuia[0]['tipo']=$tipo[$dataGuia[0]['tipo']];

		$dataGuia[0]['numeroFactura']=$buscaGuia[0]['numdoc'];
		$dataGuia[0]['serieFactura']=str_pad($buscaGuia[0]['serie'],3,'0',STR_PAD_LEFT);
		$dataGuia[0]['fecha']=date('d/m/Y');
		$dataGuia[0]['referencia']=' REF: '.$dataGuia[0]['idordenventa'].'    VEN: '.$dataGuia[0]['idvendedor'].'     '.$usuario.'  --  '.date('H:i:s');
		$dataGuia[0]['domiPartida']='JR. ALFREDEZ DE FRAGATA RICARDO HERRERA 665 - LIMA';
		$data=$pdf->buscarDetalleOrdenVenta($buscaGuia[0]['idordenventa']);
		
		$pdf_guias=New pdf_facturas("P","mm","A4");
		$pdf_guias->SetLeftMargin(11.5);
		$pdf_guias->SetAutoPageBreak(true,0);
		$pdf_guias->SetTextColor(0);
        
		$pdf_guias->AddPage();
	

		$pdf_guias->ImprimeGuia($dataGuia,$data);
		$pdf_guias->AliasNbPages();
		$pdf_guias->Output();
	}

	function generaBoleta(){
		$pdf=$this->AutoLoadModel('pdf');
		$documento=$this->AutoLoadModel('documento');
	
		//$numeroRelacionado=$_REQUEST['numeroRelacionado'];	
		//$tipodocumentorelacionado=$_REQUEST['tipodocumento'];
		
		if (!empty($_REQUEST['id'])) {
			$idDoc=$_REQUEST['id'];
			$imprimir='no';

		}else{
			//acutalizamos Documento que ya fue impreso
			//$dataB['esimpreso']=1;
			//$dataB['numeroRelacionado']=$numeroRelacionado;
			//$dataB['tipoDocumentoRelacionado']=$tipodocumentorelacionado;
			//$filtro="iddocumento='".$idDoc."'";
			//$exitoE=$documento->actualizarDocumento($dataB,$filtro);
		}
		
		

		
		//*****************************//
		$buscaBoleta=$documento->buscaDocumento($idDoc,"");
		$dataBoleta=$pdf->buscarxOrdenVenta($buscaBoleta[0]['idordenventa']);
		$dataBoleta[0]['imprimir']=$imprimir;
		$dataBoleta[0]['numeroBoleta']=$buscaBoleta[0]['numdoc'];
		$dataBoleta[0]['serieBoleta']=str_pad($buscaBoleta[0]['serie'],3,'0',STR_PAD_LEFT);
		$dataBoleta[0]['fecha']=date('d/m/Y');
		$dataBoleta[0]['referencia']=' REF: '.$dataBoleta[0]['idordenventa'].'    VEN: '.$dataBoleta[0]['idvendedor'].'     '.$usuario.'  --  '.date('H:i:s');
		$data=$pdf->buscarDetalleOrdenVenta($buscaBoleta[0]['idordenventa']);
		

		$pdf_boleta=New pdf_facturas("P","mm",array(0=>210,1=>146));
		$pdf_boleta->SetLeftMargin(13);
		$pdf_boleta->SetAutoPageBreak(true,0);
		$pdf_boleta->SetTextColor(0);
		$pdf_boleta->AddPage();
		$pdf_boleta->ImprimeBoleta($dataBoleta,$data);
		$pdf_boleta->AliasNbPages();
		$pdf_boleta->Output();

	}

	function devolucion(){
		$devolucion=$this->AutoLoadModel('devolucion');
		$iddevolucion=$_REQUEST['id'];
		//obtenemos la orden de venta 
		$dataDevolucion=$devolucion->listaDevolucionxid($iddevolucion);
		$idordenventa=$dataDevolucion[0]['idordenventa'];

		
		//$data=$pdf->buscarDetalleOrdenVenta($buscaBoleta[0]['idordenventa']);
		

		$pdf_boleta=New pdf_facturas("P","mm",'A4');
		$pdf_boleta->SetLeftMargin(13);
		$pdf_boleta->SetAutoPageBreak(true,0);
		$pdf_boleta->SetTextColor(0);
		$pdf_boleta->AddPage();
		$pdf_boleta->ImprimeBoleta($dataDevolucion,$data);
		$pdf_boleta->AliasNbPages();
		$pdf_boleta->Output();
	}

	function reporteventas(){
		$fecha = $_REQUEST['fecha'];
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
		//$idmoneda=$_REQUEST['idmoneda'];

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

		$dataReporte=$reporte->reporteVentas($txtFechaAprobadoInicio,$txtFechaAprobadoFinal,$txtFechaGuiadoInicio,$txtFechaGuiadoFin,$txtFechaDespachoInicio,$txtFechaDespachoFin,$txtFechaCanceladoInicio,$txtFechaCanceladoFin,$idOrdenVenta,$idCliente,$idVendedor,$idpadre,$idcategoria,$idzona,$condicionVenta,$aprobados,$desaprobados,$pendiente,$idmoneda,$simbolomoneda);
		$cantidad=count($dataReporte);
		$totalAprobado=0;
		$totalDespachado=0;
		
		
		
		$pdf = new PDF_Mc_Table("L","mm","A4");
		$titulos=array('Fecha Guiado','Fecha Despacho','Fecha Cancelado','Orden Venta','Nombre Cliente','Nombre Vendedor','Importe Aprobado','Importe Despachado','Estado','Condicion Venta','Detalle');
		$ancho=array(15,15,15,18,40,40,18,18,17,17,60);
		$orientacionTitulos=array('C','C','C','C','C','C','C','C','C','C','C');
		$orientacion=array('C','C','C','','','','','','','','');
		$pdf->_titulo="Reporte de Ventas";
		$pdf->AddPage();

		$relleno=true;
		$pdf->SetFillColor(202,232,234);
		$pdf->SetTextColor(12,78,139);
		$pdf->SetDrawColor(12,78,139);
		$pdf->SetLineWidth(.3);
		$pdf->SetFont('Helvetica','B', 7);
		$pdf->fill($relleno);
		//un arreglo con su medida  a lo ancho

		$pdf->SetWidths($ancho);
		$valor="Reporte de Ventas";
		
		//un arreglo con alineacion de cada celda

		$pdf->SetAligns($orientacionTitulos);

		
		    $fila=$titulos;
		    $pdf->Row($fila);
			$relleno=!$relleno;
			$pdf->fill($relleno);
		$pdf->SetAligns($orientacion);
		$pdf->Ln();
		$pdf->SetFillColor(224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetFont('');
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

			$totalAprobado+=$dataReporte[$i]['importeaprobado'];
			$totalDespachado+=$valorImporte;

			$fila=array($dataReporte[$i]['fordenventa'],$dataReporte[$i]['fechadespacho'],$dataReporte[$i]['fechaCancelado'],$dataReporte[$i]['codigov'],html_entity_decode($dataReporte[$i]['razonsocial'],ENT_QUOTES,'UTF-8'),($dataReporte[$i]['apellidopaterno'].' '.$dataReporte[$i]['apellidomaterno'].' '.$dataReporte[$i]['nombres']),$dataReporte[$i]['simbolo'].' '.$dataReporte[$i]['importeaprobado'],$dataReporte[$i]['simbolo'].' '.$valorImporte,$estado,$situtacion,strip_tags(html_entity_decode($dataReporte[$i]['observaciones'],ENT_QUOTES,'UTF-8')));
			$pdf->Row($fila);
			$relleno=!$relleno;
			$pdf->fill($relleno);
		}
		
		$pdf->AliasNbPages();
		$pdf->Output();
	}

	function reporteInventario(){
		$reporte=$this->AutoLoadModel('reporte');
		$actor=$this->AutoLoadModel('actor');
		$idInventario=$_REQUEST['lstInventario'];
		$idBloque=$_REQUEST['lstBloques'];
		$idProducto=$_REQUEST['idProducto'];
		
		$data=$reporte->reporteInventario($idInventario,$idBloque,$idProducto);
		$cantidadData=count($data);
		
		$pdf = new PDF_Mc_Table("L","mm","A4");
		$titulos=array(utf8_decode('N°'),'Codigo','Descripcion','Bueno','Mala','Servicio','S.Room','Total','Stock','Dif','Precio');
		$ancho=array(10,32,113,15,15,15,15,15,15,15,15);
		$orientacion=array('C','','','C','C','C','C','C','C','C','R');
		
		$pdf->_titulo="INVENTARIO GENERAL CELESTIUM";
		
		
		$pdf->AddPage();
		
		
		//un arreglo con su medida  a lo ancho
		
		$pdf->SetWidths($ancho);
		
		//un arreglo con alineacion de cada celda
		
		$pdf->SetAligns($orientacion);
		$contB=0;
		$contS=0;
		for ($i=0; $i <$cantidadData; $i++) {
				
		
			$bloqueA=$data[$i-1]['idbloque'];
			if ($bloqueA!=$data[$i]['idbloque'] || $i==0) {
				if ($i!=0) {
					$pdf->AddPage();
				}
				$relleno=true;
				$pdf->SetFillColor(202,232,234);
				$pdf->SetTextColor(12,78,139);
				$pdf->SetDrawColor(12,78,139);
				$pdf->SetLineWidth(.3);
				$pdf->SetFont('Helvetica','B', 8);
				$pdf->fill($relleno);
		
				$pdf->Cell(20,7,'Fecha',1,0,'C',true);
				$pdf->Cell(20,7,$data[$i]['fechainv'],1,0,'C',false);
				$pdf->Cell(40,7,'Hora Inicio',1,0,'C',true);
				$pdf->Cell(30,7,$data[$i]['horainicio'],1,0,'C',false);
				$pdf->Cell(55,7,'Hora Termino',1,0,'C',true);
				$pdf->Cell(30,7,$data[$i]['horatermino'],1,0,'C',false);
				$pdf->Cell(50,7,'Bloque y/o Anaquel',1,0,'C',true);
				$pdf->Cell(30,7,$data[$i]['codigo'],1,0,'C',false);
				$pdf->ln();
		
				$pdf->Cell(30,7,'Responsable',1,0,'C',true);
				if (!empty($data[$i]['responsable'])) {
					$dataResponsable=$actor->buscarxid($data[$i]['responsable']);
				}
				$pdf->Cell(100,7,($dataResponsable[0]['nombres'].' '.$dataResponsable[0]['apellidopaterno'].' '.$dataResponsable[0]['apellidomaterno']),1,0,'',false);
				$pdf->Cell(30,7,'Auxiliar',1,0,'C',true);
				if (!empty($data[$i]['auxiliar'])) {
					$dataAuxiliar=$actor->buscarxid($data[$i]['auxiliar']);
				}
				$pdf->Cell(115,7,($dataAuxiliar[0]['nombres'].' '.$dataAuxiliar[0]['apellidopaterno'].' '.$dataAuxiliar[0]['apellidomaterno']),1,0,'',false);
				$pdf->ln();
		
				$fila=$titulos;
				$pdf->Row($fila);
		
					
			}
			$diferencia=$data[$i]['buenos']+$data[$i]['malos']+$data[$i]['servicio']+$data[$i]['showroom']-$data[$i]['stockinventario'];
			if ($diferencia>0) {
				$contB+=$diferencia*$data[$i]['precio'];
			}else if($diferencia<0){
				$contS+=$diferencia*$data[$i]['precio'];
			}
			$pdf->SetFillColor(224,235,255);
			$pdf->SetTextColor(0);
			$pdf->SetFont('');
			$fila=array($i+1,html_entity_decode($data[$i]['codigopa'],ENT_QUOTES,'UTF-8'),html_entity_decode(utf8_decode($data[$i]['nompro']),ENT_QUOTES,'UTF-8'),$data[$i]['buenos'],$data[$i]['malos'],$data[$i]['servicio'],$data[$i]['showroom'],($data[$i]['buenos']+$data[$i]['malos']+$data[$i]['servicio']+$data[$i]['showroom']),$data[$i]['stockinventario'],$diferencia,$data[$i]['precio']);
			$pdf->Row($fila);
			$relleno=!$relleno;
			$pdf->fill($relleno);
		}
		$ancho=array(26,26,26,26,26,26,26,26,26,41);
		$pdf->SetWidths($ancho);
		$orientacion=array('C','','','C','C','C','C','C','C','C');
		$pdf->ln();
		$fila2=array("Total a Favor","S/.".number_format($contB,2),"","","Total en Contra","S/.".number_format(abs($contS),2),"","","Total","S/.".number_format(($contB+$contS),2));
		$pdf->Row($fila2);
		$pdf->AliasNbPages();
		
		$pdf->Output();
	}
	function kardexTotalxProducto(){

		$mesInicial=!empty($_REQUEST['mesInicial'])?$_REQUEST['mesInicial']:1;
		$mesFinal=!empty($_REQUEST['mesFinal'])?$_REQUEST['mesFinal']:12;
		$ano=!empty($_REQUEST['periodo'])?$_REQUEST['periodo']:date('Y');
		
		
		
		//$sunat=$_REQUEST['sunat'];
		$movimiento=new Movimiento();
	
		$dataKardex=$movimiento->kardexTotalxProducto($ano,$mesInicial,$mesFinal);
		$total=count($dataKardex);
	
		$idproducto=0;
		$cont=-1;
		$totIngreso=0;
		$totSalida=0;
		$datos=array();
		for ($i=0; $i < $total; $i++){
			
			if ($idproducto!=$dataKardex[$i]['idproducto']) {
				$idproducto=$dataKardex[$i]['idproducto'];
	
				if ($dataKardex[$i]['codigotipooperacion']!=16) {
					$cont++;
					$datos[$cont]['idproducto']=$idproducto;
					$datos[$cont]['nompro']=$dataKardex[$i]['nompro'];
					$datos[$cont]['codigopa']=$dataKardex[$i]['codigopa'];
					if ($dataKardex[$i]['tipomovimiento']==1) {
	
						$cantidad=round($dataKardex[$i]['SaldoCantidad']-round($dataKardex[$i]['cantidad']));
						if ($cantidad<0) {
							$cantidad=0;
						}
	
						$datos[$cont]['saldoInicial']=$cantidad*$dataKardex[$i]['SaldoPrecio'];
	
					}elseif($dataKardex[$i]['tipomovimiento']==2){
	
						$cantidad=round($dataKardex[$i]['SaldoCantidad']+round($dataKardex[$i]['cantidad']));
						$datos[$cont]['saldoInicial']=$cantidad*$dataKardex[$i]['SaldoPrecio'];
						
							
							
						
					}
	
				}elseif($dataKardex[$i]['codigotipooperacion']==16){
					$cont++;
					$datos[$cont]['idproducto']=$dataKardex[$i]['idproducto'];
					$datos[$cont]['nompro']=$dataKardex[$i]['nompro'];
					$datos[$cont]['codigopa']=$dataKardex[$i]['codigopa'];
					$datos[$cont]['saldoInicial']=$dataKardex[$i]['SaldoPrecio']*$dataKardex[$i]['SaldoCantidad'];
				}
			}
			
			if ($idproducto==$dataKardex[$i]['idproducto']) {
				$totIngreso+=$dataKardex[$i]['EntradaCosto'];
				$totSalida+=$dataKardex[$i]['SalidaCosto'];
				if ($idproducto!=$dataKardex[$i+1]['idproducto']) {
						
					$datos[$cont]['EntradaTotal']=$totIngreso;
					$datos[$cont]['SalidaTotal']=$totSalida;
					$datos[$cont]['SaldoFinal']=$dataKardex[$i]['SaldoPrecio']*$dataKardex[$i]['SaldoCantidad'];
						
					$totIngreso=0;
					$totSalida=0;
				}
			}
		}
		
		$cantidadData=count($datos);
		$totIngresoSum=0;
		$totInicialSum=0;
		$totFinalSum=0;
		/**/
		$pdf = new PDF_Mc_Table("P","mm","A4");
		$titulos=array('Codigo','Descripcion','Saldo Inicial','Compras','Total Existencias','Existencia Final','Costo de Ventas');
		$pdf->SetFont('Helvetica','B', 6.5);
		$ancho=array(25,70,20,20,20,20,20);
		$orientacion=array('','','R','R','R','R','R');
		$pdf->_titulo="Reporte de Total de Kardex Valorizado en S/.";
		$pdf->_datoPie="Periodo: ".$ano." de ".$this->meses($mesInicial)."  a  ".$this->meses($mesFinal);
		$pdf->_imagenCabezera=ROOT.'imagenes'.DS.'POWER-ACOUSTIK.jpg';
		
		$pdf->SetWidths($ancho);
		$pdf->_titulos=$titulos;
		$pdf->AddPage();
		
		$relleno=true;
		$pdf->SetFillColor(202,232,234);
		$pdf->SetTextColor(12,78,139);
		$pdf->SetDrawColor(12,78,139);
		$pdf->SetLineWidth(.3);
		
		$pdf->fill($relleno);
		//un arreglo con su medida  a lo ancho

		
		
		//un arreglo con alineacion de cada celda
		$pdf->_orientacion=$orientacion;
		$pdf->SetAligns($orientacion);
		$pdf->SetFillColor(224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetFont('');
		for ($i=0; $i <$cantidadData ; $i++) { 
			
			$totInicialSum+=$datos[$i]['saldoInicial'];
			$totIngresoSum+=$datos[$i]['EntradaTotal'];
			$totFinalSum+=$datos[$i]['SaldoFinal'];
			$fila=array(html_entity_decode($datos[$i]['codigopa'],ENT_QUOTES,'UTF-8'),html_entity_decode($datos[$i]['nompro'],ENT_QUOTES,'UTF-8'),number_format($datos[$i]['saldoInicial'],2),number_format($datos[$i]['EntradaTotal'],2),number_format($datos[$i]['saldoInicial']+$datos[$i]['EntradaTotal'],2),number_format($datos[$i]['SaldoFinal'],2),number_format(($datos[$i]['saldoInicial']+$datos[$i]['EntradaTotal'])-$datos[$i]['SaldoFinal'],2));
			$pdf->Row($fila);
			$relleno=!$relleno;
			$pdf->fill($relleno);
		}
		$pdf->ln();
		$relleno=false;
		$pdf->fill($relleno);
		$fila=array('','TOTALES',number_format($totInicialSum,2),number_format($totIngresoSum,2),number_format($totInicialSum+$totIngresoSum,2),number_format($totFinalSum,2),number_format($totInicialSum+$totIngresoSum-$totFinalSum,2));
		$pdf->Row($fila);
		$pdf->AliasNbPages();
		$pdf->Output();
	
	}
	
	function reporteOrdenCompra(){
		$ordenCompra=$this->AutoLoadModel('reporte');
		$idOrdenCompra=$_REQUEST['idOrdenCompra'];
		$idProducto=$_REQUEST['idProducto'];
		
		$datos=$ordenCompra->reporteOrdenCompraRevision($idOrdenCompra,$idProducto);
		$cantidadData=count($datos);
		$pdf= new PDF_MC_Table("P","mm","A4");
		$titulos=array('Codigo','Descripcion','FOB($)','CIF(S/.)','P.L.(S/.)','S.A','S.D','Compra','U.M');
		$pdf->SetFont('Helvetica','B', 6.5);
		$ancho=array(25,70,15,15,15,15,12,12,12);
		$orientacion=array('','','R','R','R','C','C','C','');
		
		
		$pdf->SetWidths($ancho);
		$pdf->_titulos=$titulos;
		$pdf->_titulo="Reporte Orden Compra";
		$pdf->_fecha=$datos[0]['codigooc'];
		$pdf->_datoPie="F. Ingreso : ".$datos[0]['fordencompra']." - F. A .LLegada: ".$datos[0]['faproxllegada'];
		$pdf->AddPage();
		
		$relleno=true;
		$pdf->SetFillColor(202,232,234);
		$pdf->SetTextColor(12,78,139);
		$pdf->SetDrawColor(12,78,139);
		$pdf->SetLineWidth(.3);
		
		$pdf->fill($relleno);
		//un arreglo con su medida  a lo ancho
		
		
		
		//un arreglo con alineacion de cada celda
		$pdf->_orientacion=$orientacion;
		$pdf->SetAligns($orientacion);
		$pdf->SetFillColor(224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetFont('');
		$importe=0;
		for ($i=0; $i <$cantidadData ; $i++) {
			$fila=array(html_entity_decode($datos[$i]['codigopa'],ENT_QUOTES,'UTF-8'),html_entity_decode($datos[$i]['nompro'],ENT_QUOTES,'UTF-8'),number_format($datos[$i]['fobdoc'],2),number_format($datos[$i]['cifventas'],2),number_format($datos[$i]['precio_lista'],2),round($datos[$i]['stockactual']),$datos[$i]['stockdisponible'],$datos[$i]['cantidadrecibidaoc'],html_entity_decode($datos[$i]['codigo'],ENT_QUOTES,'UTF-8'));
			$importe+=$datos[$i]['precio_lista']*$datos[$i]['cantidadrecibidaoc'];
			$pdf->Row($fila);
			$relleno=!$relleno;
			$pdf->fill($relleno);
		}
		$pdf->ln();
		$relleno=false;
		$pdf->fill($relleno);
		$fila=array('TOTAL S/.',number_format($importe,2));
		$pdf->Row($fila);
		$pdf->AliasNbPages();
		$pdf->Output();

	}
	function historialOrdenCompra(){
		$ordenCompra=$this->AutoLoadModel('reporte');
		$idProducto=$_REQUEST['idProducto'];
	
		$datos=$ordenCompra->historialProducto($idProducto);
		$cantidadData=count($datos);
		$pdf= new PDF_MC_Table("L","mm","A4");
		$titulos=array('C. Orden','Fecha Ingreso','F. Aprox. Llegada','Codigo','Descripcion','FOB($)','CIF(S/.)','P.L.(S/.)','Compra','U.M');
		$pdf->SetFont('Helvetica','B', 6.5);
		$ancho=array(20,20,22,25,100,15,15,15,15,15);
		$orientacion=array('C','C','C','','','R','R','R','C','');
	
	
		$pdf->SetWidths($ancho);
		$pdf->_titulos=$titulos;
		$pdf->_titulo="Historial Compras del Producto";
		$pdf->AddPage();
	
		$relleno=true;
		$pdf->SetFillColor(202,232,234);
		$pdf->SetTextColor(12,78,139);
		$pdf->SetDrawColor(12,78,139);
		$pdf->SetLineWidth(.3);
	
		$pdf->fill($relleno);
		//un arreglo con su medida  a lo ancho
	
	
	
		//un arreglo con alineacion de cada celda
		$pdf->_orientacion=$orientacion;
		$pdf->SetAligns($orientacion);
		$pdf->SetFillColor(224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetFont('');
		$importe=0;
		for ($i=0; $i <$cantidadData ; $i++) {
			$fila=array($datos[$i]['codigooc'],$datos[$i]['fordencompra'],$datos[$i]['faproxllegada'],html_entity_decode($datos[$i]['codigopa'],ENT_QUOTES,'UTF-8'),html_entity_decode($datos[$i]['nompro'],ENT_QUOTES,'UTF-8'),number_format($datos[$i]['fobdoc'],2),number_format($datos[$i]['cifventas'],2),number_format($datos[$i]['precio_lista'],2),$datos[$i]['cantidadrecibidaoc'],html_entity_decode($datos[$i]['codigo'],ENT_QUOTES,'UTF-8'));
			$importe+=$datos[$i]['precio_lista']*$datos[$i]['cantidadrecibidaoc'];
			$pdf->Row($fila);
			$relleno=!$relleno;
			$pdf->fill($relleno);
		}
		$pdf->ln();
		$pdf->AliasNbPages();
		$pdf->Output();
	
	}
	
	function reporteCarteraClientes(){
		$idLinea=$_REQUEST['lstLineaProductos'];
		$idZona=$_REQUEST['lstZona'];
		$idPadre=$_REQUEST['lstRegionCobranza'];
		$idCategoria=$_REQUEST['lstCategoriaPrincipal'];
		$idVendedor=$_REQUEST['idVendedor'];
		// $idCliente=$_REQUEST['idCliente'];
		// $idOrdenVenta=$_REQUEST['idOrdenVenta'];
		$idDepartamento=$_REQUEST['lstDepartamento'];
		$idProvincia=$_REQUEST['lstProvincia'];
		$idDistrito=$_REQUEST['lstDistrito'];
		$FecIni=$_REQUEST['lstFecIni'];
		$FecFin=$_REQUEST['lstFecFin'];
		// $condicion=$_REQUEST['lstCondicion'];
		// $situacion=$_REQUEST['lstSituacion'];
		$fechaInicio=!empty($_REQUEST['txtFechaInicio'])?date('Y-m-d',strtotime($_REQUEST['txtFechaInicio'])):"";
		$fechaFin=!empty($_REQUEST['txtFechaFin'])?date('Y-m-d',strtotime($_REQUEST['txtFechaFin'])):"";
		$condiciones="";
		if (strtolower($condicion)=="contado") {
			$condiciones=" and ov.es_contado=1 and ov.es_credito!=1 and ov.es_letras!=1 ";
		}elseif(strtolower($condicion)=="credito"){
			$condiciones=" and ov.es_credito=1 and ov.es_letra!=1 ";
		}elseif(strtolower($condicion)=="letras banco"){
			$condiciones=" and ov.es_letras=1 and ov.tipo_letra=1 ";
		}elseif (strtolower($condicion)=="letras cartera"){
			$condiciones=" and ov.es_letras=1 and ov.tipo_letra=2 ";
		}
		
		
		$reporte=$this->AutoLoadModel('reporte');
		
		//$datos=$reporte->carteraClientes($idLinea,$idZona,$idPadre,$idCategoria,$idVendedor,$idCliente,$idOrdenVenta,$idDepartamento,$idProvincia,$idDistrito,$condiciones,$situacion,$fechaInicio,$fechaFin);
		$datos=$reporte->carteraClientes($idLinea,$idZona,$idPadre,$idCategoria,$idVendedor,$idDepartamento,$idProvincia,$idDistrito,$fechaInicio,$fechaFin);
		// echo $idLinea." -- ".$idZona." -- ".$idPadre." -- ".$idCategoria." -- ".$idVendedor." -- ".$idDepartamento." -- ".$idProvincia." -- ".$idDistrito;
		// exit;
		$cantidadData=count($datos);
		
		$pdf= new PDF_MC_Table("L","mm","A4");
		$titulos=array('Linea','FECHA','CLIENTE','RUC','TELEFONO','DEUDA(S/.)','DEUDA(US $)','DIRECCION','DISTRITO','DPTO');
		$pdf->SetFont('Helvetica','B', 6.5);
		$ancho=array(25,15,50,32,18,15,20,55,20,20);
		$orientacion=array('C','C','L','','','','','L','','');
		
		
		$pdf->SetWidths($ancho);
		//$pdf->_titulos=$titulos;
		$pdf->_titulo="CARTERA DE CLIENTES";
		if (!empty($idVendedor)){
		$pdf->_fecha='VEND :'.$datos[0]['nombres'].' '.$datos[0]['apellidopaterno'].' '.$datos[0]['apellidomaterno'];
		}else{
			$pdf->_fecha="";
		}
		$pdf->_datoPie=date('Y-m-d');
		$pdf->AddPage();
		
		
		
		$pdf->fill($relleno);
		$pdf->_orientacion=$orientacion;
		$pdf->SetAligns($orientacion);
	
		$importe=0;
		$zona=0;
		for ($i=0; $i <$cantidadData ; $i++) {
			if ($zona!=$datos[$i]['idzona']) {
				$zona=$datos[$i]['idzona'];
				if ($i!=0) {
					
					
				}
				$pdf->Cell(80,7,$datos[$i]['nombrezona'],0);
				$pdf->ln();
				$relleno=true;
				$pdf->fill($relleno);
				$pdf->SetFillColor(0, 0, 0);
				$pdf->SetTextColor(255,255,255);
				$pdf->SetDrawColor(12,78,139);
				$pdf->SetLineWidth(.3);
				$fila=$titulos;
				$pdf->Row($fila);
				
			}
			
				$pdf->SetFillColor(224,235,255);
				$pdf->SetTextColor(0);
				$pdf->SetFont('');
			$cliente=$this->AutoLoadModel('cliente');

			$deudas=$cliente->deudaTotalxIdCliente($datos[$i]['idcliente']);
			// echo '<pre>';
			// print_r($deudas);
			// exit;
			$deudasxmoneda=count($deudas);
			if ($deudasxmoneda==1) {
				if ($deudas[0]['idmoneda']==2) {
					$deudaSoles=0.00;
					$deudaDolares=$deudas[0]['deuda']*(-1);
				}
				if ($deudas[0]['idmoneda']==1) {
					$deudaDolares=0.00;
					$deudaSoles=$deudas[0]['deuda']*(-1);
				}
			}else{

				$deudaSoles=$deudas[0]['deuda']*(-1);
				$deudaDolares=$deudas[1]['deuda']*(-1);
			}
			
			$fila=array($datos[$i]['nomlin'],$datos[$i]['fordenventa'],utf8_decode(html_entity_decode($datos[$i]['razonsocial'],ENT_QUOTES,'UTF-8')),$datos[$i]['ruc'],$datos[$i]['telefono'],number_format($deudaSoles,2),number_format($deudaDolares,2),utf8_decode(html_entity_decode($datos[$i]['direccion'],ENT_QUOTES,'UTF-8')),html_entity_decode($datos[$i]['nombredistrito'],ENT_QUOTES,'UTF-8'),html_entity_decode($datos[$i]['nombredepartamento'],ENT_QUOTES,'UTF-8'));
			$importe+=round($datos[$i]['importeov'],2);
			$pdf->Row($fila);
			$relleno=!$relleno;
			$pdf->fill($relleno);
		}
		$pdf->ln();
		$pdf->AliasNbPages();
		$pdf->Output();
	}
	function reporteHistorialVentasxProducto(){
		$idVendedor=$_REQUEST['idVendedor'];
		$idProducto=$_REQUEST['idProducto'];
		$idCliente=$_REQUEST['idCliente'];
		$reporte=$this->AutoLoadModel('reporte');
		
		if($idProducto==0){
			$idProducto="";
		}
		$datos=$reporte->historialVentasxProducto($idProducto,$idVendedor,$idCliente);
		$cantidadData=count($datos);
		
		$pdf= new PDF_MC_Table("P","mm","A4");
		$titulos=array('Orden Venta','FECHA','CLIENTE','VENDEDOR','ORIG.','U.M.','PRECIO','CANT.','IMPORTE');
		$pdf->SetFont('Helvetica','B', 6.5);
		$ancho=array(17,14,50,50,10,11,15,13,15);
		$orientacion=array('C','C','','','C','C','R','C','R','');
		
		
		$pdf->SetWidths($ancho);
		$pdf->_titulos=$titulos;
		$pdf->_titulo="Ventas x Producto S/.";
		$pdf->_fecha=$datos[0]['codigopa'];
		$pdf->_datoPie=date('Y-m-d');
		$pdf->AddPage();

		
		$relleno=true;
		$pdf->fill($relleno);
		$pdf->SetFillColor(0224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetDrawColor(12,78,139);
		$pdf->SetLineWidth(.3);
		$pdf->_orientacion=$orientacion;
		$pdf->SetAligns($orientacion);
		$importe=0;
		$cantidadP=0;
		for ($i=0; $i <$cantidadData ; $i++) {
			$fila=array($datos[$i]['codigov'],$datos[$i]['fordenventa'],utf8_decode(html_entity_decode($datos[$i]['razonsocial'],ENT_QUOTES,'UTF-8')),utf8_decode(html_entity_decode($datos[$i]['nombres'].' '.$datos[$i]['apellidopaterno'].' '.$datos[$i]['apellidomaterno'],ENT_QUOTES,'UTF-8')),$datos[$i]['codigoalmacen'],$datos[$i]['nombremedida'],number_format($datos[$i]['preciofinal'],2),$datos[$i]['cantdespacho'],number_format($datos[$i]['importeov'],2));
			$importe+=round($datos[$i]['preciofinal'],2)*$datos[$i]['cantdespacho'];
			$cantidadP+=$datos[$i]['cantdespacho'];
			$pdf->Row($fila);
			$relleno=!$relleno;
			$pdf->fill($relleno);
		}
		
		$pdf->ln();
		$pdf->Cell(130);
		$pdf->Cell(25,5,"TOTAL x PRODUCTO",1,0,'R',false);
		$pdf->Cell(20,5,$cantidadP,1,0,'R',false);
		$pdf->Cell(20,5,number_format($importe,2),1,0,'R',false);
		$pdf->AliasNbPages();
		$pdf->Output();
	}
	function reporteCobranzaxEmpresa(){
		$reporte=$this->AutoLoadModel('reporte');
		$ordenGasto=$this->AutoLoadModel('ordengasto');
	
		$idPadre=$_REQUEST['lstCategoriaPrincipal'];
		$idCategoria=$_REQUEST['lstZonaCobranza'];
		$idZona=$_REQUEST['lstZona'];
		$idVendedor=$_REQUEST['idVendedor'];
		$idCliente=$_REQUEST['idCliente'];
		$idOrdenVenta=$_REQUEST['idOrdenVenta'];
		$situacion=$_REQUEST['lstSituacion'];
		$fechaInicio=$_REQUEST['txtFechaInicio'];
		$fechaFin=$_REQUEST['txtFechaFin'];
		$tipoCobranza=$_REQUEST['lstTipoCobranza'];
		$idAlmacen=$_REQUEST['lstEmpresa'];
		$encabezado=$_REQUEST['encabezado'];
		if ($situacion=="cancelado" or $situacion=="anulado") {
			$situ=" and wc_ordenventa.`situacion`='$situacion' and wc_detalleordencobro.`situacion`='$situacion' ";
		}elseif($situacion=="pendiente"){
			$situ=" and wc_ordenventa.`situacion`='$situacion' and wc_detalleordencobro.`situacion`='' ";
		}else{
			$situ=" and wc_detalleordencobro.`situacion`='' ";
		}
	
		if (!empty($idCliente)) {
			$filtro=" wc_cliente.`idcliente`='$idCliente' ";
		}
	
		$datos=$reporte->cobranzaxEmpresa($filtro,$idZona,$idPadre,$idCategoria,$idVendedor,$tipoCobranza,$fechaInicio,$fechaFinal,$situ,$idAlmacen);
		$cantidadData=count($datos);
		
		$pdf= new PDF_MC_Table("P","mm","A4");
		$titulos=array('Codigo','Ven','F. Des.','Cliente','Total','Pagado','Devol.','Deuda','Empresa','M.Fac.','M.Bole','%');
		$pdf->SetFont('Helvetica','B', 6.5);
		$ancho=array(17,8,15,51,14,14,14,14,11,14,14,8);
		$ancho2=array(91,12,16,14,14,11,14,22);
		$orientacion=array('C','C','C','','R','R','R','R','C','R','R','C');
		$orientacion2=array('','','C','C','C','R','R','');
		
		$pdf->SetWidths($ancho);
		$pdf->_anchoColumna=$ancho;
		$pdf->_titulos=$titulos;
		$pdf->_titulo="Cobranza x Empresa S/.";
		$pdf->_relleno=false;
		if(!empty($idAlmacen)){
			if($idAlmacen==4){
				$pdf->_imagenCabezera=ROOT.'imagenes'.DS.'DAKKARS.jpg';
			}elseif($idAlmacen==8){
				$pdf->_imagenCabezera=ROOT.'imagenes'.DS.'POWER-ACOUSTIK.jpg';
			}elseif($idAlmacen==10){
				$pdf->_imagenCabezera=ROOT.'imagenes'.DS.'KAZO.jpg';
			}
		}else{
			$pdf->_fecha='Todos';
		}
		
		$pdf->_datoPie=date('Y-m-d');
		$pdf->AddPage();

		
		$relleno=true;
		$pdf->fill($relleno);
		$pdf->SetFillColor(0224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetDrawColor(12,78,139);
		$pdf->SetLineWidth(.3);
		$pdf->_orientacion=$orientacion;
		$pdf->SetAligns($orientacion);
		$totalImporte=0;
		$totalPagado=0;
		$totalDevuelto=0;
		$totalFacturado=0;
		$totalContado=0;
		$totalCredito=0;
		$totalLetras=0;
		$totalBoleta=0;
		$idOrden=0;
		$direccion="";
	
		for ($i=0; $i <$cantidadData ; $i++) {
			if($idOrden!=$datos[$i]['idordenventa']){
				$idOrden=$datos[$i]['idordenventa'];
				$importeOrden=round($ordenGasto->totalGuia($idOrden),2);
				$totalImporte+=$importeOrden;
				$totalPagado+=round($datos[$i]['importepagado'],2);
				$totalDevuelto+=round($datos[$i]['importedevuelto'],2);
				$direccion=$datos[$i]['direccion_envio'];
				if($datos[$i]['nombredoc']==1){
					$montoFactura=round($datos[$i]['montofacturado']);
					$totalFacturado+=$montoFactura;
					$montoBoleta=0;
				}else{
					$montoBoleta=round($datos[$i]['montofacturado']);
					$totalBoleta+=$montoBoleta;
					$montoFactura=0;
				}
				if($datos[$i]['es_letras']==1){
					$totalLetras+=$importeOrden;
				}elseif($datos[$i]['es_credito']==1){
					$totalCredito+=$importeOrden;
				}elseif($datos[$i]['es_contado']==1){
					$totalContado+=$importeOrden;
				}
				$pdf->_relleno=true;
				$relleno=true;
				$pdf->fill($relleno);
				$pdf->SetWidths($ancho);
				$pdf->_original=$ancho;
				$pdf->SetAligns($orientacion);
				$pdf->_orientacion=$orientacion;
				$fila=array($datos[$i]['codigov'],$datos[$i]['codigoa'],$datos[$i]['fechadespacho'],utf8_decode(html_entity_decode($datos[$i]['razonsocial'],ENT_QUOTES,'UTF-8')),number_format($importeOrden,2),number_format($datos[$i]['importepagado'],2),number_format($datos[$i]['importedevolucion'],2),number_format($importeOrden-round($datos[$i]['importepagado'],2),2),$datos[$i]['codigoalmacen'],number_format($montoFactura,2),number_format($montoBoleta),$datos[$i]['porcentajefactura']);
				$pdf->Row($fila);
				
				
			}
			$pdf->_relleno=false;
			$pdf->SetWidths($ancho2);
			$pdf->_original=$ancho2;
			$pdf->_orientacion=$orientacion2;
			$pdf->SetAligns($orientacion2);
			$forma="";
			$relleno=false;
			$pdf->fill($relleno);
			if($datos[$i]['formacobro']==1){
				$forma='Contado';
			}elseif($datos[$i]['formacobro']==2){
				$forma="Credito";
			}elseif($datos[$i]['formacobro']==3){
				$forma="Letras";
			}
			$fila=array($direccion,$forma,$datos[$i]['numeroletra'],$datos[$i]['fechagiro'],$datos[$i]['fvencimiento'],number_format($datos[$i]['importedoc'],2),number_format($datos[$i]['saldodoc'],2),empty($datos[$i]['situacion'])?'Pendiente':$datos[$i]['situacion']);
			$direccion="";
			$pdf->Row($fila);
			
			
		}
		
		$pdf->ln();
		
		$pdf->Cell(25,5,"Total Guia",1,0,'R',true);
		$pdf->Cell(25,5,number_format($totalImporte,2),1,0,'R',false);
		
		$pdf->Cell(25,5,"Total Pagado",1,0,'R',true);
		$pdf->Cell(25,5,number_format($totalPagado,2),1,0,'R',false);
		
		$pdf->Cell(25,5,"Total Devuelto",1,0,'R',true);
		$pdf->Cell(25,5,number_format($totalDevuelto,2),1,0,'R',false);
		
		$pdf->Cell(20,5,"Total Deuda",1,0,'R',true);
		$pdf->Cell(25,5,number_format($totalImporte-$totalPagado,2),1,0,'R',false);
		
		$pdf->ln();
		
		$pdf->Cell(25,5,"Total Factura",1,0,'R',true);
		$pdf->Cell(25,5,number_format($totalFacturado,2),1,0,'R',false);
		
		$pdf->Cell(25,5,"Total Boleta",1,0,'R',true);
		$pdf->Cell(25,5,number_format($totalBoleta,2),1,0,'R',false);
		
		$pdf->Cell(25,5,"Total Contado",1,0,'R',true);
		$pdf->Cell(25,5,number_format($totalContado,2),1,0,'R',false);
		
		$pdf->Cell(20,5,"Total Credito",1,0,'R',true);
		$pdf->Cell(25,5,number_format($totalCredito,2),1,0,'R',false);
		
		$pdf->ln();
		
		$pdf->Cell(25,5,"Total Letras",1,0,'R',true);
		$pdf->Cell(25,5,number_format($totalLetras,2),1,0,'R',false);
		$pdf->ln();
		$pdf->ln();
		
		$pdf->SetAligns('','','','','','','','');
		$pdf->SetWidths(array(25,25,25,25,25,25,20,25));
		$fila=array('Zona Geografica',$encabezado['categoriaPrincipal'],'Zona Cobranza',$encabezado['zonaCobranza'],'Zona',$encabezado['zona'],'T.Cobranza',$encabezado['tipoCobranza']);
		$pdf->Row($fila);
		
		$pdf->SetAligns('','','','','','');
		$fila=array('Vendedor',$encabezado['vendedor'],'Cliente',$encabezado['cliente'],'Orden Venta',$encabezado['ordenVenta'],'Situacion',$encabezado['situacion']);
		$pdf->Row($fila);
		
		$pdf->SetAligns('','','','','','');
		$fila=array();
		$pdf->Row($fila);
		
		$pdf->Cell(25,5,"Rango Fechas",1,0,'L',false);
		$pdf->Cell(50,5,$encabezado[fecha],1,0,'R',false);
		
		$pdf->AliasNbPages();
		$pdf->Output();
		
	}
	
	function rankingVendedor(){
		$fecha = $_REQUEST['fecha'];
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
		$simbolomoneda=$idmoneda==1?"S/.":"US $";
		
		
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
		$categoria=$this->AutoLoadModel('zona');
		$data=$reporte->rankingVendedor($txtFechaAprobadoInicio,$txtFechaAprobadoFinal,$txtFechaGuiadoInicio,$txtFechaGuiadoFin,$txtFechaDespachoInicio,$txtFechaDespachoFin,$txtFechaCanceladoInicio,$txtFechaCanceladoFin,$idOrdenVenta,$idCliente,$idVendedor,$idpadre,$idcategoria,$idzona,$condicionVenta,$aprobados,$desaprobados,$pendiente,$idmoneda);
		// echo "<pre>";
		// print_r($data);
		// exit;

		if(!empty($idpadre)){
			$dataCategoria=$categoria->buscaCategoria($idpadre);
		}
		
		$cantidad=count($data);
		$dataReporte=array();
		$idVendedor=0;
		$importeContado=0;
		$totalContado=0;
		$importeCredito=0;
		$totalCredito=0;
		$importeLetraB=0;
		$totalLetraB=0;
		$importeLetraC=0;
		$totalLetraC=0;
		$importePagado=0;
		$subTotal=0;
		$importeTotal=0;
		$totalPagado=0;
		$importeLima=0;
		$importeAnulado=0;
		$importeDevolucion=0;
		$totalDevolucion=0;
		$importeProvincia=0;
		$cont=0;
		$totalGuias=0;
		$num=0;
		//$tipoCambio=$this->configIni($this->configIni('Globals','Modo'),'TipoCambio');
		
		for($i=0; $i <$cantidad+1 ; $i++){
			if($data[$i]['idvendedor']!=$idVendedor || $i==($cantidad) ){
				if($i!=$cantidad+1){
					if($i!=0){
						$dataReporte[$num]['idvendedor']=$idVendedor;
						$dataReporte[$num]['importecontado']=$importeContado;
						$dataReporte[$num]['importecredito']=$importeCredito;
						$dataReporte[$num]['importeletrab']=$importeLetraB;
						$dataReporte[$num]['importeletrac']=$importeLetraC;
						$dataReporte[$num]['importepagado']=$importePagado;
						$dataReporte[$num]['importedevolucion']=$importeDevolucion;
						$dataReporte[$num]['subtotal']=$subTotal;
						$dataReporte[$num]['cantidadguia']=$cont;
						$dataReporte[$num]['vendedor']=$nombres;
						$num++;
						
						$importeContado=0;
						$importeCredito=0;
						$importeLetraB=0;
						$importeLetraC=0;
						$importePagado=0;
						$importeDevolucion=0;
						$importePagado=0;
						$subTotal=0;
						$cont=0;
					}
					$idVendedor=$data[$i]['idvendedor'];
					$nombres=$data[$i]['nombres'].' '.$data[$i]['apellidopaterno'].' '.$data[$i]['apellidomaterno'];
				}
				
				
			}
			
				
				if($i!=$cantidad+1){
					if($data[$i]['es_letras']==1 && $data[$i]['tipo_letra']==1){
						$importeLetraB+=$data[$i]['importeov'];
						$totalLetraB+=$data[$i]['importeov'];
					}else if($data[$i]['es_letras']==1 && $data[$i]['tipo_letra']==2){
						$importeLetraC+=$data[$i]['importeov'];
						$totalLetraC+=$data[$i]['importeov'];
					}else if($data[$i]['es_credito']==1){
						$importeCredito+=$data[$i]['importeov'];
						$totalCredito+=$data[$i]['importeov'];
					}else if($data[$i]['es_contado']==1){
						$importeContado+=$data[$i]['importeov'];
						$totalContado+=$data[$i]['importeov'];
					}
					
					if($data[$i]['idpadrec']==1){
						$importeProvincia+=$data[$i]['importeov'];
					}else if($data[$i]['idpadrec']==2){
						$importeLima+=$data[$i]['importeov'];
					}
					if($data[$i]['esanulado']==1){
						$importeAnulado+=$data[$i]['importeov'];
					}
					
					$totalGuias++;
					$totalDevolucion+=$data[$i]['importedevolucion'];
					$totalPagado+=($data[$i]['importepagado']>$data[$i]['importeov'])?$data[$i]['importeov']:$data[$i]['importepagado'];
					$importeDevolucion+=$data[$i]['importedevolucion'];
					$importePagado+=($data[$i]['importepagado']>$data[$i]['importeov'])?$data[$i]['importeov']:$data[$i]['importepagado'];
					$subTotal+=$data[$i]['importeov'];
					$importeTotal+=$data[$i]['importeov'];
					$cont++;
				}
				
			
		}
		
		$cantidadReporte=count($dataReporte);
		$pdf = new PDF_Mc_Table("L","mm","A4");
		$titulos=array(utf8_decode('Nº'),'Vendedor','VTA. NETA','%','CONTADO','%','L.BANCO','%','L.CARTERA','%','CREDITO','%','Devolucion','Pagado','Pendiente','Guias');
		$ancho=array(7,55,20,9,20,9,20,9,20,9,20,9,20,20,20,10);
		$orientacionTitulos=array('C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C');
		$orientacion=array('','','R','R','R','R','R','R','R','R','R','R','R','R','R','C');
		$pdf->_titulo="Ranking de Ventas ".$simbolomoneda;
		$pdf->_fecha=$dataCategoria[0]['nombrec'].' ('.$_REQUEST['txtFechaGuiadoInicio'].' - '.$_REQUEST['txtFechaGuiadoFin'].') ';
		$pdf->AddPage();
		
		
		
		$relleno=true;
		$pdf->SetFillColor(202,232,234);
		$pdf->SetTextColor(12,78,139);
		$pdf->SetDrawColor(12,78,139);
		$pdf->SetLineWidth(.3);
		$pdf->SetFont('Helvetica','B', 7);
		$pdf->fill($relleno);
		//un arreglo con su medida  a lo ancho

		$pdf->SetWidths($ancho);
		
		//un arreglo con alineacion de cada celda

		$pdf->SetAligns($orientacionTitulos);

		
		$fila=$titulos;
		$pdf->Row($fila);
		$relleno=!$relleno;
		$pdf->fill($relleno);
		$pdf->SetAligns($orientacion);
		$pdf->SetFillColor(224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetFont('');		
		$dataReporte=$this->ordernarArray($dataReporte, "subtotal",true);
		
		for ($i=0; $i <$cantidadReporte ; $i++) { 
			

			$fila=array(($i+1),$dataReporte[$i]['vendedor'],number_format($dataReporte[$i]['subtotal'],2),$importeTotal==0?0:round($dataReporte[$i]['subtotal']*100/$importeTotal,2),number_format($dataReporte[$i]['importecontado'],2),$totalContado==0?0:round($dataReporte[$i]['importecontado']*100/$totalContado,2),number_format($dataReporte[$i]['importeletrab'],2),$totalLetraB==0?0:round($dataReporte[$i]['importeletrab']*100/$totalLetraB,2),number_format($dataReporte[$i]['importeletrac'],2),$totalLetraC==0?0:round($dataReporte[$i]['importeletrac']*100/$totalLetraC,2),number_format($dataReporte[$i]['importecredito'],2),$totalCredito==0?0:round($dataReporte[$i]['importecredito']*100/$totalCredito,2),number_format($dataReporte[$i]['importedevolucion'],2),number_format($dataReporte[$i]['importepagado'],2),number_format($dataReporte[$i]['subtotal']-$dataReporte[$i]['importepagado'],2),$dataReporte[$i]['cantidadguia']);
			$pdf->Row($fila);
			$relleno=!$relleno;
			$pdf->fill($relleno);
		}
		$pdf->ln();
		$relleno=false;
		$pdf->fill($relleno);
		$fila=array("","TOTALES EN ".$simbolomoneda." ",number_format($importeTotal,2),100,number_format($totalContado,2),100,number_format($totalLetraB,2),100,number_format($totalLetraC,2),100,number_format($totalCredito,2),100,number_format($totalDevolucion,2),number_format($totalPagado,2),number_format($importeTotal-$totalPagado,2),$totalGuias);
		$pdf->Row($fila);
		
		// $relleno=false;
		// $pdf->fill($relleno);
		// $fila=array("","TOTALES EN $.(".$tipoCambio.")",number_format($importeTotal/$tipoCambio,2),100,number_format($totalContado/$tipoCambio,2),100,number_format($totalLetraB/$tipoCambio,2),100,number_format($totalLetraC/$tipoCambio,2),100,number_format($totalCredito/$tipoCambio,2),100,number_format($totalDevolucion/$tipoCambio,2),number_format($totalPagado/$tipoCambio,2),number_format(($importeTotal-$totalPagado)/$tipoCambio,2),$totalGuias);
		// $pdf->Row($fila);
		
		$pdf->AliasNbPages();
		$pdf->Output();
	}
	function reporteUtilidadesComision(){
		$idOrdenVenta=$_REQUEST['idOrdenVenta'];
		
		$detalleOrdenVenta=$this->AutoLoadModel('detalleordenventa');
		$ordenVenta=$this->AutoLoadModel('ordenventa');
		$datos=$detalleOrdenVenta->listaDetalleProductos($idOrdenVenta);
		// echo "<pre>";
		// print_r($datos);
		// exit;
		$dataOrden=$ordenVenta->buscarOrdenVentaxId($idOrdenVenta);

		$idmoneda=$dataOrden[0]['IdMoneda'];
		$cantidadData=count($datos);
		
		$pdf= new PDF_MC_Table("P","mm","A4");
		$titulos=array('CODIGO','DESCRIPCION','CIF','P.LISTA','DESC.','P.NETO','UTILIDAD');
		$pdf->SetFont('Helvetica','B', 6.5);
		$ancho=array(25,92,15,15,15,15,15);
		
		
		
		$pdf->SetWidths($ancho);
		
		$pdf->_titulo="Reporte Utilidades Comision x Orden Venta";
		$pdf->_datoPie=date('Y-m-d');
		$pdf->AddPage();
		$pdf->_titulos=$titulos;
		
		
		$relleno=true;
		$pdf->fill($relleno);
		$pdf->SetFillColor(0224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetDrawColor(12,78,139);
		$pdf->SetLineWidth(.3);
		$pdf->_orientacion=$orientacion;
		$pdf->SetAligns($orientacion);
		$utilidad=0;
		
		$pdf->Cell(25,5,"Orden Venta",1,0,'C',true);
		$pdf->Cell(25,5,$dataOrden[0]['codigov'],1,0,'R',false);
		$pdf->Cell(25,5,"Fecha de Pedido",1,0,'C',true);
		$pdf->Cell(25,5,$dataOrden[0]['fordenventa'],1,0,'R',false);
		$pdf->Cell(25,5,"Importe OV",1,0,'C',true);
		$pdf->Cell(25,5,$dataOrden[0]['Simbolo'].' '.$dataOrden[0]['importeov'],1,0,'R',false);
		$pdf->ln();
		$pdf->ln();
		
		
		
		for ($i=0; $i <$cantidadData ; $i++) {
			if($i==0){
				$orientacion=array('C','C','C','C','C','C','C');
				$pdf->SetAligns($orientacion);
				$pdf->SetFillColor(202,232,234);
				$pdf->SetDrawColor(12,78,139);
				$pdf->SetTextColor(12,78,139);
				$fila=$titulos;
				$pdf->Row($fila);
				$pdf->SetFillColor(0224,235,255);
				$pdf->SetTextColor(0);
				$pdf->SetDrawColor(12,78,139);
				$orientacion=array('','','R','R','','R','R');
				$pdf->SetAligns($orientacion);
			}
			$preciofinal=empty($datos[$i]['preciofinal'])?0:round($datos[$i]['preciofinal'],2);

			$cif=($idmoneda==1)?($datos[$i]['cifventas']):($datos[$i]['cifventasdolares']);
			$preciolista=($idmoneda==1)?$datos[$i]['preciolista']:$datos[$i]['preciolistadolares'];
			if($cif>0){
				$utilidad=(round((($preciofinal-$cif)/$cif)*100,2));
			}else{
				$utilidad=0;
			}
			
			$fila=array(utf8_decode(html_entity_decode($datos[$i]['codigopa'],ENT_QUOTES,'UTF-8')),utf8_decode(html_entity_decode($datos[$i]['nompro'],ENT_QUOTES,'UTF-8')),number_format($cif,2),number_format($preciolista,2),$datos[$i]['descuentoaprobadotexto'],number_format($datos[$i]['preciofinal'],2),number_format($utilidad,2).'%');
			$pdf->Row($fila);
			$relleno=!$relleno;
			$pdf->fill($relleno);
		}
		
		$pdf->AliasNbPages();
		$pdf->Output();
		
	}

	function reporteFacturacion(){
		$idOrdenVenta=$_REQUEST['idOrdenVenta'];
		$txtFechaInicio=!empty($_REQUEST['txtFechaInicio'])?date('Y-m-d',strtotime($_REQUEST['txtFechaInicio'])):null;
		$txtFechaFinal=!empty($_REQUEST['txtFechaFinal'])?date('Y-m-d',strtotime($_REQUEST['txtFechaFinal'])):null;
		$idVendedor=$_REQUEST['idVendedor'];
		$idTipoDoc=$_REQUEST['idTipoDoc'];
		$idCliente=$_REQUEST['idCliente'];
		$lstSituacion=$_REQUEST['lstSituacion'];
		$orden=$_REQUEST['lstOrden'];
		$reporte=$this->AutoLoadModel('reporte');
		$datos=$reporte->reporteFacturacion($txtFechaInicio,$txtFechaFinal,$idVendedor,$idOrdenVenta,$idCliente,$idTipodoc,$lstSituacion,$orden);
		$cantidadData=count($datos);
		$pdf= new PDF_MC_Table("L","mm","A4");
		$titulos=array('#','ORDEN VENTA','S-NUM.','F. EMI.','EMP','CLIENTE','MONTO (S/)','MONTO ($)','%','T','TIPO','VENDEDOR','SITUACION');
		$pdf->SetFont('Helvetica','B', 6.5);
		$ancho=array(8,20,20,15,8,70,20,20,8,5,16,55,20);
		$orientacion=array('','','','C','C','','R','R','C','C','','','C');
		
		$tipoCambioVentas=$this->configIni($this->configIni('Globals','Modo'),'TipoCambio');
		$pdf->SetWidths($ancho);
		
		if(!empty($txtFechaFinal) || !empty($txtFechaInicio)){
			$fecha1=!empty($txtFechaInicio)?$txtFechaInicio:utf8_decode('?');
			$fecha2=!empty($txtFechaFinal)?$txtFechaFinal:utf8_decode('?');
			$pdf->_fecha='Rango Fecha: '.$fecha1.' - '.$fecha2;
		}
		
		$pdf->_titulo="REPORTE DE FACTURACION";
		$pdf->_datoPie='Impreso el :'.date('Y-m-d H:m:s');
		$pdf->AddPage();
		$pdf->_titulos=$titulos;
		
		
		$relleno=true;
		$pdf->fill($relleno);
		$pdf->SetFillColor(0224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetDrawColor(12,78,139);
		$pdf->SetLineWidth(.3);
		$pdf->_orientacion=$orientacion;
		$pdf->SetAligns($orientacion);
		$pdf->SetTitulos($titulos);
		
		$pdf->SetFillColor(0224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetDrawColor(12,78,139);
		
		$importeFactura=0;
		$importeBoleta=0;
		
		for ($i=0; $i <$cantidadData ; $i++) {
			$modoFactura="";
			if($datos[$i]['modofactura']==1){
				$modoFactura="Precio";
			}else if($datos[$i]['modofactura']==2){
				$modoFactura="Cantidad";
			}
			if($datos[$i]['nombredoc']==1){
				$importeFactura+=round($datos[$i]['montofacturado'],2);
				$valorLetra="F";
			}else{
				$importeBoleta+=round($datos[$i]['montofacturado'],2);
				$valorLetra="B";
			}
			$vendedor=$datos[$i]['nombres'].' '.$datos[$i]['apellidopaterno'].' '.$datos[$i]['apellidomaterno'];
			$fila=array(($i+1),$datos[$i]['codigov'],str_pad($datos[$i]['serie'],3,'0',STR_PAD_LEFT).'-'.$datos[$i]['numdoc'],$datos[$i]['fechadoc'],$datos[$i][codigoalmacen],html_entity_decode($datos[$i]['razonsocial'],ENT_QUOTES,'UTF-8'),number_format($datos[$i]['montofacturado'],2),number_format(round($datos[$i]['montofacturado'],2)/$tipoCambioVentas,2),$datos[$i]['porcentajefactura'],$valorLetra,$modoFactura,$vendedor,$datos[$i]['situacion']);
			$pdf->Row($fila);
			$relleno=!$relleno;
			$pdf->fill($relleno);
		}
		$pdf->ln();
		$pdf->Cell(25,5,"MONTO FACTURA S/.",1,0,'C',true);
		$pdf->Cell(25,5,number_format($importeFactura,2),1,0,'R',false);
		$pdf->Cell(25,5,"MONTO BOLETA S/.",1,0,'C',true);
		$pdf->Cell(25,5,number_format($importeBoleta,2),1,0,'R',false);
		$pdf->Cell(25,5,"TOTAL S/.",1,0,'C',true);
		$pdf->Cell(25,5,number_format($importeBoleta+$importeFactura,2),1,0,'R',false);
		$pdf->AliasNbPages();
		$pdf->Output();
	}
	function reporteKardexProduccion(){
		$txtFechaInicio=$_REQUEST['txtFechaInicio'];
		$txtFechaFinal=$_REQUEST['txtFechaFinal'];
		$idProducto=$_REQUEST['idProducto'];
		$idTipoMovimiento=$_REQUEST['idTipoMovimiento'];
		$idTipoOperacion=$_REQUEST['idTipoOperacion'];
		$txtDescripcion=$_REQUEST['txtDescripcion'];
		$reporte=$this->AutoLoadModel('reporte');
		$datos=$reporte->reporteKardexProduccion($txtFechaInicio,$txtFechaFinal,$idProducto,$idTipoMovimiento,$idTipoOperacion);
		
		$cantidadData=count($datos);
		$pdf= new PDF_MC_Table("L","mm","A4");
		$titulos=array('#','FECHA','T. MOV.','CONCEPTO','ORDEN','RAZON SOCIAL','DEVOLUCION','PRECIO','CANT. ','SALDO','IMPORTE S/.');
		$pdf->SetFont('Helvetica','B', 7.5);
		$ancho=array(8,18,20,30,18,70,20,20,15,15,20);
		$orientacion=array('','C','C','','C','','C','R','C','C','R');
		
		$tipoCambioVentas=$this->configIni($this->configIni('Globals','Modo'),'TipoCambio');
		$pdf->SetWidths($ancho);
		
		if(!empty($txtFechaFinal) || !empty($txtFechaInicio)){
			$fecha1=!empty($txtFechaInicio)?$txtFechaInicio:utf8_decode('?');
			$fecha2=!empty($txtFechaFinal)?$txtFechaFinal:utf8_decode('?');
			$pdf->_fecha='Rango Fecha: '.$fecha1.' - '.$fecha2;
		}
		
		$pdf->_titulo="REPORTE::KARDEX DE PRODUCCION";
		$pdf->_datoPie=$txtDescripcion.'     '.'Impreso el :'.date('Y-m-d H:m:s');
		$pdf->AddPage();
		$pdf->_titulos=$titulos;
		
		
		$relleno=true;
		$pdf->fill($relleno);
		$pdf->SetFillColor(0224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetDrawColor(12,78,139);
		$pdf->SetLineWidth(.3);
		$pdf->_orientacion=$orientacion;
		$pdf->SetAligns($orientacion);
		$pdf->SetTitulos($titulos);
		
		$pdf->SetFillColor(0224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetDrawColor(12,78,139);
		
		$importeFactura=0;
		$importeBoleta=0;
		for ($i=0; $i <$cantidadData ; $i++) {
			$modoFactura="";
			$fila=array(($i+1),$datos[$i]['fecha'],$datos[$i]['tipo movimiento'],$datos[$i]['concepto movimiento'],$datos[$i]['codigov'],html_entity_decode($datos[$i]['razon social'],ENT_QUOTES,'UTF-8'),$datos[$i]['devolucion'],number_format($datos[$i]['precio'],2),$datos[$i]['cantidad'],$datos[$i]['saldo'],number_format(round($datos[$i]['precio'],2)*$datos[$i]['cantidad'],2));
			$pdf->Row($fila);
			$relleno=!$relleno;
			$pdf->fill($relleno);
		}
		
		$pdf->AliasNbPages();
		$pdf->Output();
	}
}

?>

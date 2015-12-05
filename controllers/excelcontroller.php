<?php 
Class ExcelController extends ApplicationGeneral{
	
	public function  reporteCarteraClientes(){
		$idZona=$_REQUEST['lstZona'];
		$idPadre=$_REQUEST['lstRegionCobranza'];
		$idCategoria=$_REQUEST['lstCategoriaPrincipal'];
		$idVendedor=$_REQUEST['idVendedor'];
		$idCliente=$_REQUEST['idCliente'];
		$idOrdenVenta=$_REQUEST['idOrdenVenta'];
		$idDepartamento=$_REQUEST['lstDepartamento'];
		$idProvincia=$_REQUEST['lstProvincia'];
		$idDistrito=$_REQUEST['lstDistrito'];
		$condicion=$_REQUEST['lstCondicion'];
		$situacion=$_REQUEST['lstSituacion'];
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
		//traemos los datos
		$reporte=$this->AutoLoadModel('reporte');
		$datos=$reporte->carteraClientes($idZona,$idPadre,$idCategoria,$idVendedor,$idCliente,$idOrdenVenta,$idDepartamento,$idProvincia,$idDistrito,$condiciones,$situacion,$fechaInicio,$fechaFin);
		$cantidadData=count($datos);
		
		//Creamos en nombre de archivo
		$baseURL=ROOT.'descargas'.DS;
		$idActor=$_SESSION['idactor'];
		$fechaCreacion=date('Y-m-d_h.m.s');		
		$basenombre='CarteraClientes.xls';
		$filename=$baseURL.$idActor.'_'.$fechaCreacion.'_'.$basenombre;
		
		//traemos la libreria de Excel e instanciamos
		$this->AutoLoadLib('PHPExcel');
		$objPHPExcel=new PHPExcel();
		
		//llenamos los datos
		$titulos=array('Orden Venta','FECHA','COD','CLIENTE','EMAIL','RUC','TELEFONO','IMPORTE ($/.)','DIRECCION','DISTRITO','DPTO');
		
		//poniendo stylo al encabezado
		//Lineas para cuadros
		$sharedStyle1 = new PHPExcel_Style();
		
		$sharedStyle1->applyFromArray(
				array('fill' => array(
						'type' => PHPExcel_Style_Fill::FILL_SOLID,
						'color' => array('argb' => 'FFCCFFCC')
				),
						'borders' => array(
								'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
								'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
						)
				)
		);
		
		//estableciendo a automatico el ancho
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
		
		$importe=0;
		$zona=0;
		$cont=0;
		for ($i=0; $i <$cantidadData ; $i++) {
			if ($zona!=$datos[$i]['idzona']) {
				$zona=$datos[$i]['idzona'];
				if ($i!=0) {
					$cont++;
				}
				
				$cont++;
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.($cont), $datos[$i]['nombrezona']);
				$cont++;
				$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.($cont), $titulos[0])
				->setCellValue('B'.($cont), $titulos[1])
				->setCellValue('C'.($cont), $titulos[2])
				->setCellValue('D'.($cont), $titulos[3])
				->setCellValue('E'.($cont), $titulos[4])
				->setCellValue('F'.($cont), $titulos[5])
				->setCellValue('G'.($cont), $titulos[6])
				->setCellValue('H'.($cont), $titulos[7])
				->setCellValue('I'.($cont), $titulos[8])
				->setCellValue('J'.($cont), $titulos[9])
				->setCellValue('K'.($cont), $titulos[10]);
				
				//negrita
				$objPHPExcel->getActiveSheet()->setSharedStyle($sharedStyle1,"A".($cont).":K".($cont));
				
				//Negrita a los encabezados
				$objPHPExcel->getActiveSheet()->getStyle("A".($cont).":K".($cont))->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getStyle("A".($cont).":K".($cont))->getFill()->setRotation(1);
		
			}
				
			$cont++;
			$fila=array($datos[$i]['codigov'],$datos[$i]['fordenventa'],$datos[$i]['codantiguo'],html_entity_decode($datos[$i]['razonsocial'],ENT_QUOTES,'UTF-8'),utf8_decode(html_entity_decode($datos[$i]['email'],ENT_QUOTES,'UTF-8')),$datos[$i]['ruc'],$datos[$i]['telefono'],number_format($datos[$i]['importeov'],2),utf8_decode(html_entity_decode($datos[$i]['direccion'],ENT_QUOTES,'UTF-8')),html_entity_decode($datos[$i]['nombredistrito'],ENT_QUOTES,'UTF-8'),html_entity_decode($datos[$i]['nombredepartamento'],ENT_QUOTES,'UTF-8'));
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.($cont), $datos[$i]['codigov'])
			->setCellValue('B'.($cont), $datos[$i]['fordenventa'])
			->setCellValue('C'.($cont), $datos[$i]['codantiguo'])
			->setCellValue('D'.($cont), html_entity_decode($datos[$i]['razonsocial'],ENT_QUOTES,'UTF-8'))
			->setCellValue('E'.($cont), utf8_decode(html_entity_decode($datos[$i]['email'],ENT_QUOTES,'UTF-8')))
			->setCellValue('F'.($cont), $datos[$i]['ruc'])
			->setCellValue('G'.($cont), $datos[$i]['telefono'])
			->setCellValue('H'.($cont), round($datos[$i]['importeov'],2))
			->setCellValue('I'.($cont), utf8_decode(html_entity_decode($datos[$i]['direccion'],ENT_QUOTES,'UTF-8')))
			->setCellValue('J'.($cont), html_entity_decode($datos[$i]['nombredistrito'],ENT_QUOTES,'UTF-8'))
			->setCellValue('K'.($cont), html_entity_decode($datos[$i]['nombredepartamento'],ENT_QUOTES,'UTF-8'));
			
			$importe+=round($datos[$i]['importeov'],4);
			
		}	
		
		$objPHPExcel->getActiveSheet()->setTitle('Reporte_Cartera_Clientes');
		$objPHPExcel->setActiveSheetIndex(0);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');
		$objWriter->save($filename);
	
		
		header('Content-Description: File Transfer');
		header('Content-type: application/force-download');
		header('Content-Disposition: attachment; filename='.basename($filename));
		header('Content-Transfer-Encoding: binary');
		header("Content-type: application/octet-stream");
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($filename));
		ob_clean();
		flush();
		
		readfile($filename);
		unlink($filename);
			
		
	}
	public function reportecartera(){
		
		$this->AutoLoadLib(array('GoogChart','GoogChart.class'));
		$chart = new GoogChart();
		$color = array( '#95b645', '#7498e9', '#999999',);
		$dataMultiple = array( 'Año 2009' => array( XBox => 30, PS3 => 20, Wii => 45, Otros => 5, ), 'Año 2008' => array( XBox => 40, PS3 => 20, Wii => 30, Otros => 10, ), );
		echo '<h2>MERCADO DE VIDEOJUEGOS</h2>'; $chart->setChartAttrs( array( 'type' => 'pie', 'title' => 'Ventas: '.$fecha, 'data' => $dataMultiple, 'size' => array( 550, 300 ), 'color' => $color, 'labelsXY' => true, ));
		echo $chart;
	}
}

?>
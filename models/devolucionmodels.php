<?php
	class devolucion extends Applicationbase{
		private $tabla="wc_devolucion";
		private $tabla2="wc_detalledevolucion";
		private $tabla3="`wc_devolucion` wc_devolucion inner join `wc_ordenventa` wc_ordenventa on wc_devolucion.`idordenventa`=wc_ordenventa.`idordenventa`";
		function grabaDevolucion($data){
			$exito=$this->grabaRegistro($this->tabla,$data);
			return $exito;
		}
	
		function actualizarDevolucion($data,$filtro){
			$exito=$this->actualizaRegistro($this->tabla,$data,$filtro);
			return $exito;
		}
		function actualizarDetalleDevolucion($data,$filtro){
			$exito=$this->actualizaRegistro($this->tabla2,$data,$filtro);
			return $exito;
		}

		function listaDevolucion($idordenventa){
			$condicion="estado=1";
			if (!empty($idordenventa)) {
				$condicion="estado=1 and idordenventa='".$idordenventa."'";
			}

			$data=$this->leeRegistro($this->tabla,"",$condicion,"");
			return $data;
		}

		function listaDevolucionFiltro($condicion){
			$data=$this->leeRegistro($this->tabla,"",$condicion,"");
			return $data;
		}

		function nuevoId(){
			$data=$this->leeRegistro($this->tabla,"max(iddevolucion)","","");
			return $data[0]['max(iddevolucion)'];
		}
		function verificar($iddevolucion){
			$condicion=" estado=1 and aprobado=1 and iddevolucion='$iddevolucion'";	
			$data=$this->leeRegistro($this->tabla,"",$condicion,"");
			return $data;
		}
		function listaDevolucion2($idordenventa,$iddevolucion){
			$condicion="estado=1 and idordenventa='$idordenventa' and iddevolucion='$iddevolucion'";			

			$data=$this->leeRegistro($this->tabla,"",$condicion,"");
			return $data;
		}

		function actualizaDetalleDevolucion($data,$iddevolucion,$iddetalledevolucion){
			$condicion="estado=1";
			if (!empty($iddetalledevolucion) && empty($iddevolucion)) {
				$condicion="estado=1 and iddetalledevolucion='$iddetalledevolucion'";
			}elseif (!empty($iddevolucion) && empty($iddetalledevolucion)) {
				$condicion="estado=1 and iddevolucion='$iddevolucion'";
			}elseif (!empty($iddetalledevolucion) && !empty($iddevolucion)) {
				$condicion="estado=1 and iddevolucion='$iddevolucion' and iddetalledevolucion='$iddetalledevolucion'";
			}
			$exito=$this->actualizaRegistro($this->tabla2,$data,$condicion);
			return $exito;
		}
		function grabaDetalleDevolucion($data){
			$exito=$this->grabaRegistro($this->tabla2,$data);
			return $exito;
		}
		function listaDetalleDevolucion($iddevolucion,$iddetalledevolucion){
			$condicion="estado=1";
			if (!empty($iddetalledevolucion) && empty($iddevolucion)) {
				$condicion="estado=1 and iddetalledevolucion='$iddetalledevolucion'";
			}elseif (!empty($iddevolucion) && empty($iddetalledevolucion)) {
				$condicion="estado=1 and iddevolucion='$iddevolucion'";
			}elseif (!empty($iddetalledevolucion) && !empty($iddevolucion)) {
				$condicion="estado=1 and iddevolucion='$iddevolucion' and iddetalledevolucion='$iddetalledevolucion'";
			}
			$data=$this->leeRegistro($this->tabla2,"",$condicion,"");
			return $data;

		}
		function buscaDetalleDevolucion($iddetalledevolucion){
			$data=$this->leeRegistro($this->tabla2,"","iddetalledevolucion='$iddetalledevolucion' ","");
			return $data;
		}

		function listaDevolucionxid($iddevolucion){
			$condicion=" estado=1 and iddevolucion='".$iddevolucion."'";
			$data=$this->leeRegistro($this->tabla,"",$condicion,"");
			return $data;
		}

		function eliminarDevolucion($iddevolucion){
			$exito=$this->cambiaEstado($this->tabla,"iddevolucion=$iddevolucion");
			return $exito;
		}
		function eliminarDetalleDevolucion($iddevolucion){
			$exito=$this->cambiaEstado($this->tabla2,"iddevolucion=$iddevolucion");
			return $exito;
		}

		function confirmar($iddevolucion){
			$data['aprobado']=1;
			$data['fechaaprobada']=date('Y/m/d H:i:s');
			$filtro="iddevolucion='$iddevolucion'";
			$exito=$this->actualizaRegistro($this->tabla,$data,$filtro);
			return $exito;
		}

		function paginadoDevoluciones($iddevolucion,$paraBusqueda=""){
			$condicion2="";
			$condicion="wc_devolucion.`estado`=1 and wc_devolucion.`registrado`=1 ";
			if (!empty($paraBusqueda)) {
				$condicion2=" and wc_devolucion.`iddevolucion`='$paraBusqueda' or wc_ordenventa.`codigov`='$paraBusqueda' ";
			}
			if (!empty($iddevolucion)) {
				$condicion=" wc_devolucion.`estado`=1 and wc_devolucion.`registrado`=1 and wc_devolucion.`iddevolucion`='$iddevolucion' or wc_ordenventa.`codigov`='$paraBusqueda' ";
			}

			return $this->paginado(
				$this->tabla3,
				
				"",
				$condicion.$condicion2);
		}

		function listaDevolucionesPaginado($iddevolucion,$pagina,$paraBusqueda=""){
			$condicion2="";
			$condicion="wc_devolucion.`estado`=1 and wc_devolucion.`registrado`=1 ";
			if (!empty($paraBusqueda)) {
				$condicion2=" and wc_devolucion.`aprobado`=1 and (wc_devolucion.`iddevolucion`='$paraBusqueda' or wc_ordenventa.`codigov`='$paraBusqueda') ";
			}
			if (!empty($iddevolucion)) {
				$condicion=" wc_devolucion.`estado`=1 and wc_devolucion.`registrado`=1 and wc_devolucion.`iddevolucion`='$iddevolucion' or wc_ordenventa.`codigov`='$paraBusqueda' ";
			}

			$data=$this->leeRegistroPaginado(
				$this->tabla3,
				
				"",
				$condicion.$condicion2,
				"wc_devolucion.`iddevolucion` desc",$pagina);
			return $data;
		}
		function cuentaDevoluciones($iddevolucion,$paraBusqueda=""){
			$condicion2="";
			$condicion="wc_devolucion.`estado`=1 and wc_devolucion.`registrado`=1 ";
			if (!empty($paraBusqueda)) {
				$condicion2=" and wc_devolucion.`aprobado`=1 and (wc_devolucion.`iddevolucion`='$paraBusqueda' or wc_ordenventa.`codigov`='$paraBusqueda') ";
			}
			if (!empty($iddevolucion)) {
				$condicion=" wc_devolucion.`estado`=1 and wc_devolucion.`registrado`=1 and wc_devolucion.`iddevolucion`='$iddevolucion' or wc_ordenventa.`codigov`='$paraBusqueda' ";
			}
			$data=$this->leeRegistro($this->tabla3,"count(*)",$condicion.$condicion2,"");
			return $data[0]['count(*)'];
			
		}
		function listaDetalleconProductos($idordenventa){
			$condicion="wc_detalledevolucion.´estado´=1";
			if (!empty($idordenventa)) {
				$condicion="wc_devolucion.`estado`=1 and  wc_ordenventa.`idordenventa`='$idordenventa'";
			}

			$data=$this->leeRegistro(
						"`wc_ordenventa` wc_ordenventa 
						inner join `wc_devolucion` wc_devolucion on wc_ordenventa.`idordenventa`=wc_devolucion.`idordenventa`
						inner join `wc_detalledevolucion` wc_detalledevolucion on wc_detalledevolucion.`iddevolucion`=wc_devolucion.`iddevolucion`
						inner join `wc_producto` wc_producto on wc_producto.`idproducto`=wc_detalledevolucion.`idproducto`
						",

						"wc_ordenventa.`codigov`,
						wc_ordenventa.`idordenventa` as idordenventa,
						wc_producto.`codigopa`,
						wc_producto.`nompro`,
						wc_detalledevolucion.`precio` as preciodevuelto,
						wc_detalledevolucion.`cantidad` as cantidaddevuelta,
						wc_detalledevolucion.`importe` as importedevuelto,
						wc_devolucion.`iddevolucion` as iddevolucion,
						wc_devolucion.`importetotal` as importetotal
						",
						$condicion,
						"");
			return $data;
		}
		function listaDevolucionconOrden($idordenventa){
			$condicion="wc_detalledevolucion.´estado´=1";
			if (!empty($idordenventa)) {
				$condicion="wc_devolucion.`estado`=1 and  wc_ordenventa.`idordenventa`='$idordenventa'";
			}

			$data=$this->leeRegistro(
						"`wc_ordenventa` wc_ordenventa 
						inner join `wc_devolucion` wc_devolucion on wc_ordenventa.`idordenventa`=wc_devolucion.`idordenventa`
						inner join `wc_detalledevolucion` wc_detalledevolucion on wc_detalledevolucion.`iddevolucion`=wc_devolucion.`iddevolucion`
						inner join `wc_producto` wc_producto on wc_producto.`idproducto`=wc_detalledevolucion.`idproducto`
						",

						"wc_ordenventa.`codigov`,
						wc_ordenventa.`idordenventa` as idordenventa,
						wc_producto.`codigopa`,
						wc_producto.`nompro`,
						wc_detalledevolucion.`precio` as preciodevuelto,
						wc_detalledevolucion.`cantidad` as cantidaddevuelta,
						wc_detalledevolucion.`importe` as importedevuelto,
						wc_devolucion.`iddevolucion` as iddevolucion,
						wc_devolucion.`importetotal` as importetotal
						",
						$condicion,
						"");
			return $data;
		}
		function listaOrdenconCliente($idordenventa){
			$condicion="";
			if (!empty($idordenventa)) {
				$condicion="wc_ordenventa.`idordenventa`='$idordenventa'";
			}

			$data=$this->leeRegistro(
						"`wc_clientezona` wc_clientezona INNER JOIN `wc_ordenventa` wc_ordenventa ON wc_clientezona.`idclientezona` = wc_ordenventa.`idclientezona`
     					INNER JOIN `wc_cliente` wc_cliente ON wc_clientezona.`idcliente` = wc_cliente.`idcliente`
						",

						"wc_ordenventa.`codigov`,
						wc_ordenventa.`idordenventa`,
						wc_cliente.`razonsocial`,
						wc_cliente.`ruc`,
						wc_ordenventa.`situacion`,
						wc_ordenventa.`codigov`
						",
						$condicion,
						"");
			return $data;
		}

		function listaDevolucionParaImpresion($iddevolucion){
			
			$condicion="wc_devolucion.`iddevolucion`='$iddevolucion' and wc_devolucion.`registrado`=1 and wc_devolucion.`estado`=1 and wc_detalledevolucion.`cantidad`>0";
			

			$data=$this->leeRegistro(
						" `wc_devolucion` wc_devolucion 
						inner join `wc_detalledevolucion` wc_detalledevolucion on wc_detalledevolucion.`iddevolucion`=wc_devolucion.`iddevolucion`
						
						inner join `wc_producto` wc_producto on wc_producto.`idproducto`=wc_detalledevolucion.`idproducto`
						",

						"wc_producto.`codigopa`,
						wc_producto.`nompro`,
						wc_detalledevolucion.`cantidad`,
						wc_detalledevolucion.`precio`
						
						",
						$condicion,
						"");
			return $data;
		}


		public function ReporteDevoluciones($idcliente,$idordenventa,$esregistrado,$fecregini,$fecregfin,$esaprobado,$fecaprini,$fecaprfin,$devtotal)
		{
			
			$sql="	SELECT cli.idcliente,cli.razonsocial,ov.codigov,ov.idordenventa,ov.importeaprobado,dev.iddevolucion,
					CONCAT(REPEAT('0', 6-LENGTH(dev.iddevolucion)), dev.iddevolucion) as devolucion,CASE (dev.registrado) WHEN 1 THEN 'REG.' ELSE ' ' END as registrado,
					dev.fecharegistrada,CASE (dev.aprobado) WHEN 1 THEN 'APROB.' ELSE ' ' END as aprobado
					,dev.fechaaprobada,dev.importetotal,mn.simbolo,dev.observaciones FROM wc_devolucion dev
					Inner Join wc_ordenventa ov On dev.idordenventa=ov.idordenventa
					Inner Join wc_moneda mn On ov.IdMoneda=mn.idmoneda
					Inner Join wc_cliente cli On ov.idcliente=cli.idcliente
					Where dev.estado=1 ";
			if (!empty($idcliente)) {	$sql.="and cli.idcliente=".$idcliente." "; }
			if (!empty($idordenventa)) {	$sql.="and ov.idordenventa=".$idordenventa." "; }
			if (!empty($esregistrado) and $esregistrado==1) {	$sql.="and dev.registrado=1 "; }
			if(!empty($fecregini) and !empty($fecregfin)){ $sql.="and dev.fecharegistrada between '".$fecregini."' and '".$fecregfin."' ";}
			if(!empty($fecregini) and empty($fecregfin)){ $sql.="and dev.fecharegistrada >= '".$fecregini."' ";}
			if(empty($fecregini) and !empty($fecregfin)){ $sql.="and dev.fecharegistrada <= '".$fecregfin."' ";}
			if (!empty($esaprobado) and $esaprobado==1) {	$sql.="and dev.aprobado=1 "; }
			if(!empty($fecaprini) and !empty($fecaprfin)){ $sql.="and dev.fechaaprobada between '".$fecaprini."' and '".$fecaprfin."' ";}
			if(!empty($fecaprini) and empty($fecaprfin)){ $sql.="and dev.fechaaprobada >= '".$fecaprini."' ";}
			if(empty($fecaprini) and !empty($fecaprfin)){ $sql.="and dev.fechaaprobada <= '".$fecaprfin."' ";}
			if (!empty($devtotal) and $devtotal==1) {	$sql.="and ov.importeaprobado=dev.importetotal "; }

			$data=$this->EjecutaConsulta($sql);
			return $data;
		}
	}
?>


<?php
	class detalleOrdenCobro extends Applicationbase{
		private $tabla = "wc_detalleordencobro";
		function grabaDetalleOrdenVentaCobro($data){
			$exito=$this->grabaRegistro($this->tabla,$data);
			return $exito;
		}

		function listadoxidOrdenCobro($idOrdenCobro)
		{
		    	
                    return $this->leeregistro($this->tabla,"","idOrdenCobro=".$idOrdenCobro,"","");
		}
		function buscaRenovadoOrdenCobro($idOrdenCobro)
		{
			return $this->leeregistro($this->tabla,"","estado='1' and gastosrenovacion=1 and idOrdenCobro=".$idOrdenCobro,"","");
		}
		function actualizaDetalleOrdenCompraxFiltro($data,$filtro){
			$exito=$this->actualizaRegistro($this->tabla,$data,$filtro);
			return $exito;
		}

		function eliminaxIdOrdenCobro($idOrdenCobro){
				$exito=$this->cambiaEstado($this->tabla,"iddetalleordencobro=".$idOrdenCobro);
		}

		function listadoxidOrdenCobroxrenovado($idOrdenCobro)
		{
			return $this->leeregistro($this->tabla,"","idOrdenCobro='$idOrdenCobro' and renovado!=0 and situacion!='cancelado' and situacion!='anulado'","","");
		}
		function listadoxidOrdenCobrosinletras($idOrdenCobro)
		{
			return $this->leeregistro($this->tabla,"","idOrdenCobro=".$idOrdenCobro." and formacobro!=3","","");
		}
		function listadoxidOrdenCobro2($idOrdenCobro)
		{
			return $this->leeregistro($this->tabla,"","idOrdenCobro=".$idOrdenCobro." and formacobro=3","","");
		}
		function listadoxidOrdenCobroPendiente($idOrdenCobro)
		{
			return $this->leeregistro($this->tabla,"","idOrdenCobro=".$idOrdenCobro,"situacion=''","");
		}
		function actualizaDetalleOrdencobro($data,$iddetalleordencobro){
			$exito=$this->actualizaRegistro($this->tabla,$data,"iddetalleordencobro=$iddetalleordencobro");
			return $exito;
		}
		function buscaDetalleOrdencobro($iddetalleordencobro){
			$data=$this->leeRegistro($this->tabla,"","iddetalleordencobro=$iddetalleordencobro","");
			return $data;
		}
		function buscaDetalleOrdencobroxNumeroletra($numeroletra){
			$data=$this->leeRegistro($this->tabla,"","numeroletra='$numeroletra' and formacobro=3","");
			return $data;
		}
		function buscaDetalleOrdencobro2($iddetalleordencobro){
			$data=$this->leeRegistro(
				"`wc_detalleordencobro` doc inner join `wc_ordencobro` oc on doc.`idordencobro`=oc.`idordencobro`",
				"doc.`idordencobro`,
				doc.`renovado`,
				doc.`numeroletra`,
				doc.`saldodoc`,
				doc.`fvencimiento`,
				doc.`referencia`,
				doc.`fechagiro`,
				doc.`importedoc`,
				oc.`saldoordencobro`,
				oc.`tipoletra`",
				"doc.`iddetalleordencobro`=$iddetalleordencobro",
				"");
			return $data;
		}
		function listaConClientes($iddocumento){
			$data=$this->leeRegistro(
					"`wc_ordenventa` wc_ordenventa 
				     INNER JOIN `wc_clientezona` wc_clientezona ON wc_ordenventa.`idclientezona` = wc_clientezona.`idclientezona`
				     INNER JOIN `wc_moneda` wc_moneda ON wc_ordenventa.`idmoneda`=wc_moneda.`idmoneda`
				     INNER JOIN `wc_cliente` wc_cliente ON wc_clientezona.`idcliente` = wc_cliente.`idcliente`
				     INNER JOIN `wc_distrito` wc_distrito ON wc_cliente.`iddistrito` = wc_distrito.`iddistrito`
				     INNER JOIN `wc_provincia` wc_provincia ON wc_distrito.`idprovincia` = wc_provincia.`idprovincia`
				     INNER JOIN `wc_departamento` wc_departamento ON wc_provincia.`iddepartamento` = wc_departamento.`iddepartamento`
				     INNER JOIN `wc_documento` wc_documento  ON  wc_documento.`idordenventa`=wc_ordenventa.`idordenventa`",

					"wc_cliente.`razonsocial`,
					wc_cliente.`direccion`,
					wc_cliente.`ruc`,
					wc_cliente.`dni`,
					wc_cliente.`telefono`,
					wc_cliente.`tipocliente`,
					wc_provincia.`nombreprovincia`,
					wc_departamento.`nombredepartamento`,
					wc_distrito.`nombredistrito`,
					wc_documento.`iddocumento`,
					wc_documento.`numdoc`,
					wc_documento.`nombredoc`,
					wc_documento.`esImpreso`,
					wc_ordenventa.`direccion_envio`,
					wc_documento.`esAnulado`,
					wc_moneda.`simbolo`,
					wc_moneda.`nombre`					
					",
					"wc_documento.`iddocumento`='$iddocumento'",
					""
					);
			return $data;
		}

		function GeneraNumeroLetra(){
			$data=$this->leeRegistro($this->tabla,"CONCAT( DATE_FORMAT( NOW( ) ,  '%y' ) , LPAD(  (MAX(SUBSTRING(`numeroletra`,3,6))+1) , 6,  '0' ) )  as maxletra","`formacobro`=3 and year(`fechacreacion`)=year(now())","","");
			
			if ($data[0]['maxletra']!="") {
				return $data[0]['maxletra'];
			}else{
				return date('y').str_pad(1,6,'0',STR_PAD_LEFT);
			}

			
		}

		function fechagironrodias($idordencobro){
			/*$sql="SELECT c.idPADREC 
				FROM wc_categoria c
				INNER JOIN wc_zona z ON z.idcategoria = c.idcategoria
				INNER JOIN wc_clientezona cz ON cz.idzona = z.idzona
				INNER JOIN wc_ordenventa ov ON cz.idclientezona = ov.idclientezona
				INNER JOIN wc_ordencobro oc ON oc.idordenventa = ov.idordenventa
				WHERE oc.idordencobro =31";*/
			$tabla="wc_categoria c
				INNER JOIN wc_zona z ON z.idcategoria = c.idcategoria
				INNER JOIN wc_clientezona cz ON cz.idzona = z.idzona
				INNER JOIN wc_ordenventa ov ON cz.idclientezona = ov.idclientezona
				INNER JOIN wc_ordencobro oc ON oc.idordenventa = ov.idordenventa";
			$data=$this->leeRegistro($tabla,"c.idPADREC as zonacat","oc.idordencobro =".$idordencobro,"","");
			return $data[0]['zonacat'];
		}
	}
?>
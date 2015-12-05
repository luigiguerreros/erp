<?php
	class pdf extends Applicationbase{
		private $tabla="wc_detalleordenventa";
		private $tabla2="wc_documento";
		private $tabla3="wc_ordencobro";
		private $tabla4="wc_detalleordencobro";
		
		function buscarxOrdenVenta($idOrdenVenta){
			$data=$this->leeRegistro(
				"`wc_cliente` wc_cliente INNER JOIN `wc_clientezona` wc_clientezona ON wc_cliente.`idcliente` = wc_clientezona.`idcliente`
			    INNER JOIN `wc_ordenventa` wc_ordenventa ON wc_clientezona.`idclientezona` = wc_ordenventa.`idclientezona`
			    INNER JOIN `wc_moneda`  wc_moneda ON wc_ordenventa.Idmoneda=wc_moneda.IdMoneda
			    INNER JOIN `wc_distrito` wc_distrito ON wc_cliente.`iddistrito` = wc_distrito.`iddistrito`
			  
			    INNER JOIN `wc_provincia` wc_provincia ON wc_distrito.`idprovincia` = wc_provincia.`idprovincia`
			    INNER JOIN `wc_clientetransporte` wc_clientetransporte ON wc_clientetransporte.`idclientetransporte`=wc_ordenventa.`idclientetransporte`
			    INNER JOIN `wc_transporte` wc_transporte ON wc_clientetransporte.`idtransporte`=wc_transporte.`idtransporte`
			    INNER JOIN `wc_departamento` wc_departamento ON wc_provincia.`iddepartamento` = wc_departamento.`iddepartamento`",
				
				"wc_cliente.`idcliente`,
			    wc_cliente.`direccion`,
			    wc_cliente.`razonsocial`,
			    wc_cliente.`ruc`,
			    wc_cliente.`celular`,
			    wc_cliente.`dni`,
			    wc_transporte.`trazonsocial`,
			    wc_transporte.`tdireccion`,
			    wc_transporte.`truc`,
			    wc_transporte.`ttelefono`,
			    wc_distrito.`nombredistrito`,
			    wc_departamento.`nombredepartamento`,
			    wc_cliente.`telefono`,
			    wc_provincia.`nombreprovincia`,
			    wc_ordenventa.`idordenventa`,
			    wc_ordenventa.`direccion_envio`,
			    wc_ordenventa.`direccion_despacho`,
			    wc_ordenventa.`contacto`,
			   	wc_ordenventa.`codigov`,
			    wc_ordenventa.`idvendedor`,
			    wc_ordenventa.`importeov`,
			    wc_moneda.`simbolo` as simbolomoneda,			    
			    wc_moneda.`nombre` as nombremoneda",
				"wc_ordenventa.`idordenventa`=".$idOrdenVenta."",

				"",

				""
				);
			return $data;
		}

		function buscarDetalleOrdenVenta($idOrdenVenta){
			$data=$this->leeRegistro(
				"`wc_producto` wc_producto INNER JOIN `wc_detalleordenventa` wc_detalleordenventa ON wc_producto.`idproducto` = wc_detalleordenventa.`idproducto` 
				left join `wc_unidadmedida` wc_unidadmedida on wc_unidadmedida.`idunidadmedida`=wc_producto.`unidadmedida` ",

				"wc_detalleordenventa.`cantaprobada`,
				wc_detalleordenventa.`cantdespacho`,
				wc_detalleordenventa.`cantdevuelta`,
     			wc_producto.`codigopa` ,
     			wc_producto.`nompro`  ,
     			wc_unidadmedida.`codigo` as unidadmedida ,
     			wc_detalleordenventa.`tdescuentoaprovado`,
     			wc_detalleordenventa.`preciofinal`,
     			wc_detalleordenventa.`precioaprobado` ",
				"wc_detalleordenventa.`estado`=1 and wc_detalleordenventa.`idordenventa`=".$idOrdenVenta,
				"",
				""
				);
			return $data;
		}
		function buscarOrdenCompraxId($idOrdenVenta){
			$data=$this->leeRegistro(
				$this->tabla3,
				"escredito,escontado,esletras,idordencobro",
				"idordenventa=".$idOrdenVenta,
				"",
				""
				);
			return $data;
		}
		function listaDetalleOrdenCompraxId($idOrdenCobro,$formaCobro){
			$data=$this->leeRegistro($this->tabla4,"","idordencobro=$idOrdenCobro and formacobro=$formaCobro","","");
			return $data[0]['fvencimiento'];
		}
		function listaOrdenVenta($idordenventa){
			$condicion="wc_actorrol.`idrol`=25 and wc_ordenventa.`estado`=1";
			if (!empty($idordenventa)) {
				$condicion= "wc_actorrol.`idrol`=25 and wc_ordenventa.`idordenventa`=$idordenventa";
			}
			$data=$this->leeRegistro(
				"`wc_actor` wc_actor INNER JOIN `wc_ordenventa` wc_ordenventa ON wc_actor.`idactor` = wc_ordenventa.`idvendedor`
			     INNER JOIN `wc_clientezona` wc_clientezona ON wc_ordenventa.`idclientezona` = wc_clientezona.`idclientezona`
			     INNER JOIN `wc_cliente` wc_cliente ON wc_clientezona.`idcliente` = wc_cliente.`idcliente`
			     INNER JOIN `wc_actorrol` wc_actorrol ON wc_actor.`idactor` = wc_actorrol.`idactor`",

				"wc_cliente.`razonsocial`,
     			wc_actor.`nombres`,
     			wc_actor.`apellidopaterno`,
     			wc_actor.`apellidomaterno`,
     			wc_ordenventa.`vbcobranzas`,
     			wc_ordenventa.`vbalmacen`,
     			wc_ordenventa.`codigov`,
     			wc_ordenventa.`vbcreditos`,
     			wc_ordenventa.`vbventas`,
     			wc_ordenventa.`importeov`,
     			wc_ordenventa.`esfacturado`,
     			wc_ordenventa.`idordenventa`",

				$condicion,
				"",
				""
				);
			return $data;
		}

		function listaOrdenVentaPaginado($pagina,$filtro){
			$condicion="wc_actorrol.`idrol`=25 and wc_ordenventa.`estado`=1";
			if (!empty($filtro)) {
				$condicion.=" and ".$filtro;
			}
			$data=$this->leeRegistroPaginado(
				"`wc_actor` wc_actor INNER JOIN `wc_ordenventa` wc_ordenventa ON wc_actor.`idactor` = wc_ordenventa.`idvendedor`
			     INNER JOIN `wc_clientezona` wc_clientezona ON wc_ordenventa.`idclientezona` = wc_clientezona.`idclientezona`
			     INNER JOIN `wc_cliente` wc_cliente ON wc_clientezona.`idcliente` = wc_cliente.`idcliente`
			     INNER JOIN `wc_moneda` wc_moneda ON wc_ordenventa.`IdMoneda`=wc_moneda.`IdMoneda`
			     INNER JOIN `wc_actorrol` wc_actorrol ON wc_actor.`idactor` = wc_actorrol.`idactor`",
				
				"wc_cliente.`razonsocial`,
     			wc_actor.`nombres`,
     			wc_actor.`apellidopaterno`,
     			wc_actor.`apellidomaterno`,
    			wc_ordenventa.`vbcobranzas`,
     			wc_ordenventa.`vbalmacen`,
     			wc_ordenventa.`codigov`,
     			wc_ordenventa.`vbcreditos`,
     			wc_ordenventa.`vbventas`,
     			wc_ordenventa.`importeov`,
     			wc_ordenventa.`esfacturado`,
     			wc_ordenventa.`esguiado`,
     			wc_ordenventa.`esdespachado`,
     			wc_ordenventa.`importepagado`,
     			wc_ordenventa.`importedevolucion`,
     			wc_ordenventa.`tiempoduracion`,
     			wc_ordenventa.`idordenventa`,
     			wc_moneda.`simbolo` as SimboloMoneda,
     			wc_moneda.`nombre` as NombreMoneda",

				$condicion,
				"wc_ordenventa.`codigov` desc",$pagina);
			return $data;
		}

		function paginadoOrdenVenta($filtro){
			$condicion="wc_actorrol.`idrol`=25 and wc_ordenventa.`estado`=1";
			if (!empty($filtro)) {
				$condicion.=" and ".$filtro;
			}
			return $this->paginado(
				"`wc_actor` wc_actor INNER JOIN `wc_ordenventa` wc_ordenventa ON wc_actor.`idactor` = wc_ordenventa.`idvendedor`
			     INNER JOIN `wc_clientezona` wc_clientezona ON wc_ordenventa.`idclientezona` = wc_clientezona.`idclientezona`
			     INNER JOIN `wc_cliente` wc_cliente ON wc_clientezona.`idcliente` = wc_cliente.`idcliente`
			     INNER JOIN `wc_actorrol` wc_actorrol ON wc_actor.`idactor` = wc_actorrol.`idactor`",

				"wc_cliente.`razonsocial`,
     			wc_actor.`nombres`,
     			wc_actor.`apellidopaterno`,
     			wc_actor.`apellidomaterno`,
    			wc_ordenventa.`vbcobranzas`,
     			wc_ordenventa.`vbalmacen`,
     			wc_ordenventa.`codigov`,
     			wc_ordenventa.`vbcreditos`,
     			wc_ordenventa.`vbventas`,
     			wc_ordenventa.`importeov`,
     			wc_ordenventa.`esfacturado`,
     			wc_ordenventa.`idordenventa`",
				$condicion
				);
		}	


		function listaFacturaEmitidas($idordenventa){
			$condicion="estado=1";
			if (!empty($idordenventa)) {
				$condicion="idordenventa='$idordenventa' and estado=1 and nombredoc=1";
			}
			$data=$this->leeRegistro($this->tabla2,"",$condicion,"","");
			return $data;
		}
		function listaFacturaEmitidasNoAnuladas($idordenventa){
			$condicion="estado=1 and esAnulado!=1";
			if (!empty($idordenventa)) {
				$condicion="idordenventa='$idordenventa' and estado=1 and nombredoc=1 and esAnulado!=1";
			}
			$data=$this->leeRegistro($this->tabla2,"",$condicion,"","");
			return $data;
		}
		function listaGuiasEmitidasNoAnuladas($idordenventa){
			$condicion="estado=1 and esAnulado!=1";
			if (!empty($idordenventa)) {
				$condicion="idordenventa='$idordenventa' and estado=1 and nombredoc=4 and esAnulado!=1";
			}
			$data=$this->leeRegistro($this->tabla2,"",$condicion,"","");
			return $data;
		}
		
		

		function cuentaOrdenVenta($filtro){
			$condicion="wc_actorrol.`idrol`=25 and wc_ordenventa.`estado`=1";
			if (!empty($filtro)) {
				$condicion.=" and ".$filtro;
			}
			$data=$this->leeRegistro(
				"`wc_actor` wc_actor INNER JOIN `wc_ordenventa` wc_ordenventa ON wc_actor.`idactor` = wc_ordenventa.`idvendedor`
			     INNER JOIN `wc_clientezona` wc_clientezona ON wc_ordenventa.`idclientezona` = wc_clientezona.`idclientezona`
			     INNER JOIN `wc_cliente` wc_cliente ON wc_clientezona.`idcliente` = wc_cliente.`idcliente`
			     INNER JOIN `wc_actorrol` wc_actorrol ON wc_actor.`idactor` = wc_actorrol.`idactor`",
				"count(*)",
				$condicion,
				"",
				""
				);
			return $data[0]['count(*)'];
		}
		
	}
?>
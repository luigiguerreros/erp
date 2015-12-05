<?php
Class Detalleordencompra extends Applicationbase{

		private $tabla="wc_detalleordencompra";
		private $tablas="wc_detalleordencompra,wc_producto";

		function grabaDetalleOrdenCompra($data){
			$exito=$this->grabaRegistro($this->tabla,$data);
			return $exito;
		}
		function listaDetalleOrdenCompra($idOrdenCompra){
			$sql="Select doc.*,p.*,m.nombre as marca,um.nombre as unidadmedida,oc.vbimportaciones 
					From wc_detalleordencompra doc
					Inner join wc_ordencompra oc On doc.idordencompra=oc.idordencompra
					Inner Join wc_producto p On doc.idproducto=p.idProducto
					Left Join wc_marca m On p.idmarca=m.idmarca
					Left Join wc_unidadmedida um On um.idunidadmedida=p.unidadmedida
				 Where doc.estado=1 and doc.idordencompra=".$idOrdenCompra;
			$data=$this->EjecutaConsulta($sql);
			//$data=$this->leeRegistro2($this->tablas,"","idordencompra=$idOrdenCompra","");
			return $data;
		}
		function exiteProductoDetalleOrdenCompra($idProducto,$idOrdenCompra){
			$existe=$this->exiteRegistro($this->tabla,"idproducto=$idProducto AND idordencompra=$idOrdenCompra");
			return $existe;
		}
		function actualizaDetalleOrdenCompra($data,$idDetalleOrdenCompra){
			$exito=$this->actualizaRegistro($this->tabla,$data,"iddetalleordencompra=".$idDetalleOrdenCompra);
			return $exito;
		}
		//Busqueda por idordencompra y iddetalleordencompra
		function buscarDetalleOrdenCompra($idOrdenCompra,$idDetalleOrdenCompra){
			$data=$this->leeRegistro($this->tabla,"","idordencompra=$idOrdenCompra AND iddetalleordencompra=$idDetalleOrdenCompra","");
			return $data;
		}
		function buscaDetalleOrdenCompra($idOrdenCompra){
			$data=$this->leeRegistro($this->tabla,"","idordencompra=$idOrdenCompra and estado=1","");
			return $data;
		}
		function sumaCantidadProducto($filtro){
			$data=$this->leeRegistro("wc_ordencompra as oc INNER JOIN wc_detalleordencompra as doc ON oc.idordencompra=doc.idordencompra","sum(doc.cantidadsolicitadaoc)",$filtro,"");
			$respuesta=empty($data[0]['sum(doc.cantidadsolicitadaoc)'])?0:($data[0]['sum(doc.cantidadsolicitadaoc)']);
			return $respuesta;
		}
	}
?>
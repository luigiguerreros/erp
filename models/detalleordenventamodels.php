<?php
	class DetalleOrdenVenta extends Applicationbase{
		private $tabla="wc_detalleordenventa";
		private $tabla2="wc_detalleordenventa,wc_ordenventa,wc_producto";
		function graba($data){
			$exito=$this->grabaRegistro($this->tabla,$data);
			return $exito;
		}
		function actualizar($id,$data){
			$exito=$this->actualizaRegistro($this->tabla,$data,"iddetalleordenventa=$id");
			return $exito;
		}
		function actualizaxFiltro($data,$filtro){
			$exito=$this->actualizaRegistro($this->tabla,$data,$filtro);
			return $exito;
		}
		function listaDetalleOrdenVenta($idOrdenVenta){
			$data=$this->leeRegistro3($this->tabla2,"*,t1.`preciolista` as preciolista2","t1.idordenventa=$idOrdenVenta and t1.estado=1 ","",2);
			return $data;
		}
		function listaDetalleOrdenVentaGuia($idOrdenVenta){
			$data=$this->leeRegistro("wc_detalleordenventa dov 
					inner join wc_ordenventa ov on dov.idordenventa=ov.idordenventa
					inner join wc_producto p on p.idproducto=dov.idproducto
					left join wc_unidadmedida u on u.idunidadmedida=p.unidadmedida
					",
					"*,p.preciolista as preciolista2,u.codigo as unidadmedida",
					"dov.idordenventa='$idOrdenVenta' and dov.estado=1 ",
					"");
			return $data;
		}
		function listaDetalle($idOV){
			$condicion="estado=1";
			if (!empty($idOV)) {
				$condicion="estado=1 and idordenventa='".$idOV."'";
			}

			$data=$this->leeRegistro($this->tabla,"",$condicion,"");
			return $data;
		}
		function listaDetalleProductos($idOV){
			$condicion="dov.estado=1 and dov.idordenventa='$idOV'";
			$data=$this->leeRegistro("wc_detalleordenventa dov inner join wc_producto p on p.idproducto=dov.idproducto inner join wc_almacen a on a.idalmacen=p.idalmacen ","*,dov.preciolista as preciolista2",$condicion,"");
			return $data;
		}
		function listaDetalleOrdenVentaxProducto($idordenventa,$idproducto){
			$condicion="idordenventa='$idordenventa' and idproducto='$idproducto' and estado=1";
			$data=$this->leeRegistro($this->tabla,"",$condicion,"","");
			return $data;
		}
		function listaDetalleOrdenVentaYOrden($idordenventa){
			
			$data=$this->leeRegistro("wc_ordenventa ov inner join wc_detalleordenventa dov on ov.idordenventa=dov.idordenventa","","ov.idordenventa='$idordenventa' and dov.estado=1","","");
			return $data;
		}
		function sumaCantidadProducto($filtro){
			$data=$this->leeRegistro("wc_ordenventa as ov INNER JOIN wc_detalleordenventa as dov ON ov.idordenventa=dov.idordenventa","sum(dov.cantsolicitada)",$filtro,"");
			$respuesta=empty($data[0]['sum(dov.cantsolicitada)'])?0:$data[0]['sum(dov.cantsolicitada)'];
			return $respuesta;	
		}
		function listaxFiltro($filtro){
			$data=$this->leeRegistro("wc_detalleordenventa dov 
					inner join wc_ordenventa ov on dov.idordenventa=ov.idordenventa
					inner join wc_producto p on p.idproducto=dov.idproducto
					left join wc_descuentos d on dov.descuentoaprobado=d.id
					",
					"p.idproducto,p.codigopa,p.nompro,dov.preciofinal,dov.descuentoaprobado,d.dunico,dov.iddetalleordenventa,dov.precioaprobado,dov.preciolista",
					$filtro,
					"");
			return $data;
		}
	}
?>

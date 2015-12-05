<?php
	class Ordencompra extends Applicationbase{
		private $tabla="wc_ordencompra";
		private $tabla2="wc_ordencompra as t1,wc_detalleordencompra as t2,wc_producto as t3,wc_linea as t4";
		private $tabla3="wc_ordencompra,wc_proveedor,wc_almacen";
		private $tabla4 = "wc_detalleordencompra,wc_ordencompra";
		function listadoOrdenescompra(){
			$ordenCompra=$this->leeRegistro3($this->tabla3,"","t1.estado=1","t1.fechacreacion desc",2);
			return $ordenCompra;
		}
		function lista2UltimasCompras($idProducto){
			$ordenCompra=$this->leeRegistro($this->tabla4,"fordencompra,cantidadsolicitadaoc","","fordencompra desc","limit 2");
			return $ordenCompra;
		}		
		function listadoOrdenecompraNoRegistrado(){
			$ordenCompra=$this->leeRegistro3($this->tabla3,"","registrado='0'  and valorizado=1","",2);
			return $ordenCompra;
		}
		function inventario($idAlmacen,$idLinea,$idSubLinea,$idProducto){
			$condicion="";
			if(!empty($idAlmacen)){$condicion="t1.idalmacen=$idAlmacen";}
			if(!empty($idLinea)){$condicion="idpadre=$idLinea";}
			if(!empty($idSubLinea)){$condicion="t3.idlinea=$idSubLinea";}
			if(!empty($idProducto)){$condicion="t2.idproducto=$idProducto";}
			if(!empty($condicion)){$condicion.=" and";}
			$producto=$this->leeRegistro($this->tabla2,
				"t2.idproducto,sum(cantidadsolicitadaoc) as cantidadsolicitadaoc",
				"$condicion registrado=0 and t1.idordencompra=t2.idordencompra and t2.idproducto=t3.idproducto and t3.idlinea=t4.idlinea",
				"","group by t2.idproducto");
			return $producto;
		}
		function grabaOrdenCompra($data){
			$exito=$this->grabaRegistro($this->tabla,$data);
			return  $exito;
		}
		function contarOrdenCompra(){
			$cantidadOrdenCompra=$this->contarRegistro($this->tabla);
			return $cantidadOrdenCompra;
		}
		function contarOrdenCompraNoRegistrado(){
			$cantOrdCom=$this->contarRegistro($this->tabla,"registrado=0");
			return $cantOrdCom;
		}
		function editaOrdenCompra($idOrdenCompra){
			$data=$this->leeRegistro($this->tabla,"","idordencompra=$idOrdenCompra","");
			return $data;
		}
		function eliminaOrdenCompra($idOrdenCompra){
			$exito=$this->cambiaEstado($this->tabla,"idordencompra=$idOrdenCompra");
			return $exito;
		}
		function actualizaOrdenCompra($data,$idOrdenCompra){
			$exito=$this->actualizaRegistro($this->tabla,$data,"idordencompra=$idOrdenCompra");
			return $exito;
		}
		function buscaOrdenCompra($idOrdenCompra){
			$data=$this->leeRegistro($this->tabla,"","idordencompra=$idOrdenCompra","");
			return $data;
		}
		function buscaxvendedor($idProveedor,$fecha,$fechaInicio,$fechaFinal){
			$condicion="";
			if(!empty($idProveedor)){$condicion="and t3.idproveedor=$idProveedor";}
			if(!empty($fecha)){$condicion="and fordencompra='$fecha'";}
			if(!empty($fechaInicio)){$condicion="and fordencompra between '$fechaInicio' and '$fechaFinal'";}
			$data=$this->leeRegistro($this->tabla,"","idordencompra=$idOrdenCompra","");
			return $data;
		}
		function generaCodigo(){
			$data=$this->leeRegistro($this->tabla,"CONCAT( 'OC-',DATE_FORMAT( NOW( ) ,  '%y' ) , LPAD(  (MAX(SUBSTRING(`codigooc`,6,6))+1) , 6,  '0' ) )  as codigo"," year(fechacreacion)=year(now()) ","");
			$codigo="";
			if ($data[0]['codigo']!="") {
				return $data[0]['codigo'];
			}else{
				return "OC-".date('y').str_pad(1,6,'0',STR_PAD_LEFT);
		}
		}
		function OrdenCuadroUtilidad($idOrdenCompra){
			$data=$this->leeRegistro(
					"wc_ordencompra oc inner join wc_almacen a on oc.`idalmacen`=a.`idalmacen`
					inner join wc_proveedor p on p.`idproveedor`=oc.`idproveedor`
					",
					"",
					"idordencompra='$idOrdenCompra'",
					"");
			return $data;
		}
		function OrdenesValorizados(){
			$data=$this->leeRegistro($this->tabla,""," estado='1' and valorizado='1' and cuadroutilidad!=1","");
			return $data;
		}
		function TipoCambioxIdOrdenCompra($idOrdenCompra){
			return $this->leeRegistro($this->tabla,"tipocambiovigente","idordencompra=$idOrdenCompra","");
		}
		function listaOrdenCompraPaginado($pagina){
			$data=$this->leeRegistroPaginado(
				"wc_ordencompra oc inner join wc_almacen a on oc.idalmacen=a.idalmacen 
				inner join wc_proveedor p on  oc.idproveedor=p.idproveedor
				",
				"",
				"oc.estado=1",
				"oc.fechacreacion desc",$pagina);
			return $data;
		}
		function paginadoOrdenCompra(){
			return $this->paginado(
				"wc_ordencompra oc inner join wc_almacen a on oc.idalmacen=a.idalmacen 
				inner join wc_proveedor p on oc.idproveedor=p.idproveedor
				",
				"",
				"oc.estado=1");
		}
		function autoCompleteAprobados($codigoOrdenCompra){
			$data=$this->leeRegistro("wc_ordencompra","","codigooc LIKE '%$codigoOrdenCompra%' ","","");
			
			foreach($data as $valor){
				$dato[]=array("value"=>$valor['codigooc'],
						"label"=>$valor['codigooc'],
						"id"=>$valor['idordencompra'],
				);
			}
			return $dato;
		}
	}
?>
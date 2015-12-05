<?php
	class detalleordencobroingreso extends Applicationbase{
		private $tabla = "wc_detalleordencobroingreso";
		function grabadetalleordencobroingreso($data){
			$exito=$this->grabaRegistro($this->tabla,$data);
			return $exito;
		}

		function listadoxiddetalleordencobroingreso($iddetalleordencobroingreso)
		{
			return $this->leeregistro($this->tabla,"","iddetalleordencobroingreso=".$iddetalleordencobroingreso,"","");
		}
		function buscaxDetalleOrdenCobro($iddetalleordencobro)
		{
			return $this->leeregistro($this->tabla,"","estado=1 and iddetalleordencobro=".$iddetalleordencobro,"","");
		}
		function actualizadetalleordencobroingreso($data,$iddetalleordencobroingreso){
			$exito=$this->actualizaRegistro($this->tabla,$data,"iddetalleordencobroingreso=$iddetalleordencobroingreso");
			return $exito;
		}
		function eliminar($id){
			$exito=$this->inactivaRegistro($this->tabla,"iddetalleordencobroingreso=$id");
			return $exito;
		}
		function InactivaxIdIngresosxIdDetalleOrdenCobro($idingresos,$iddetalleordencobro){
			return $this->inactivaRegistro($this->tabla,"idingreso=".$idingresos." and iddetalleordencobro=".$iddetalleordencobro);
		}
	}
?>
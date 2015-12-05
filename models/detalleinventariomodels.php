<?php 

	class detalleinventario extends Applicationbase{
		private $tabla='wc_detalleinventario';

		function listadodetalleInventarioxId($iddetalleinventario){
			$movimiento=$this->leeRegistro($this->tabla,"","iddetalleinventario='$iddetalleinventario'","","");
			return $movimiento;
		}

		function listado(){
			$data=$this->leeRegistro($this->tabla,"","estado=1","");
			return $data;
		}
		function buscaxId($iddetalleinventario){
			$data=$this->leeRegistro($this->tabla,"","iddetalleinventario='$iddetalleinventario' and estado=1","");
			return $data;
		}
		function buscaxfiltro($filtro){
			$data=$this->leeRegistro($this->tabla,"",$filtro,"");
			return $data;
		}
		function actualiza($data,$iddetalleinventario){
			$exito=$this->actualizaRegistro($this->tabla,$data,"iddetalleinventario=$iddetalleinventario");
			return $exito;
		}
		function actualizaxFiltro($data,$filtro){
			$exito=$this->actualizaRegistro($this->tabla,$data,$filtro);
			return $exito;
		}
		function cambiaEstado($iddetalleinventario){
			$exito=$this->inactivaRegistro($this->tabla,"iddetalleinventario=$iddetalleinventario");
			return $exito;
		}
		function graba($data){
			$estado=$this->grabaRegistro($this->tabla,$data);

			return $estado;
		}
		
	}

 ?>
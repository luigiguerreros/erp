<?php 

	class inventario extends Applicationbase{
		private $tabla='wc_inventario';

		function listadoInventarioxId($idinventario){
			$movimiento=$this->leeRegistro($this->tabla,"","idinventario='$idinventario'","","");
			return $movimiento;
		}

		function listado(){
			$data=$this->leeRegistro($this->tabla,"","estado=1","");
			return $data;
		}
		function buscaxId($idinventario){
			$data=$this->leeRegistro($this->tabla,"","idinventario='$idinventario' and estado=1","");
			return $data;
		}
		function actualiza($data,$idinventario){
			$exito=$this->actualizaRegistro($this->tabla,$data,"idinventario=$idinventario");
			return $exito;
		}
		function cambiaEstado($idinventario){
			$exito=$this->inactivaRegistro($this->tabla,"idinventario=$idinventario");
			return $exito;
		}
		function graba($data){
			$estado=$this->grabaRegistro($this->tabla,$data);

			return $estado;
		}
	}

 ?>
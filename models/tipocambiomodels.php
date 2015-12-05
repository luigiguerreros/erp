<?php  
Class TipoCambio extends Applicationbase{
	private $_name="wc_tipocambio";
	private $_moneda="wc_moneda";
	
	function consultavigente($idmoneda){
		return $this->leeregistro($this->_name,"","idmoneda=".$idmoneda." and estado=1","","");
	}

	function consultavigentehoy(){
		$sql="
		Select m.simbolo,m.nombre,tc.compra,tc.venta From ".$this->_name." tc 
		Inner Join ".$this->_moneda." m On m.idmoneda=tc.idmoneda
		Where tc.estado=1 and tc.fechatc=date_format(Now(),'%y/%m/%d')
		Order By tc.fechatc desc,tc.estado desc,tc.idmoneda";
		return $this->EjecutaConsulta($sql);
	}	

	function consultalista(){
		$sql="
		Select tc.*,m.simbolo,m.nombre From ".$this->_name." tc 
		Inner Join ".$this->_moneda." m On m.idmoneda=tc.idmoneda
		Order By fechatc desc,estado desc,idmoneda  LIMIT 20";
		return $this->EjecutaConsulta($sql);
		//return $this->leeregistro($this->_name,"","","fechatc,estado desc,moneda","");
	}

	function grabavigente($data){
		//Desactivando los anteriores Tipos de Cambio
		$sql="Update ".$this->_name." SET estado=0 Where idmoneda=".$data['idmoneda'];
		$this->EjecutaConsulta($sql);
		//Grabando el nuevo tipo de cambio:
		$this->grabaRegistro($this->_name,$data);
		return true;
	}

	function consultaDatosTCVigente($idmoneda){
		$sql="
		Select m.simbolo,m.nombre,tc.compra,tc.venta From ".$this->_name." tc 
		Inner Join ".$this->_moneda." m On m.idmoneda=tc.idmoneda
		Where tc.estado=1 and tc.fechatc=date_format(Now(),'%y/%m/%d') and tc.idmoneda=".$idmoneda."
		Order By tc.fechatc desc,tc.estado desc,tc.idmoneda";
		return $this->EjecutaConsulta($sql);
	}		

	function consultaDatosTCVigentexTCElegido($idtipoCambio){
			$sql="Select m.simbolo,m.nombre,tc.compra,tc.venta From ".$this->_name." tc 
				Inner Join ".$this->_moneda." m On m.idmoneda=tc.idmoneda
				Where tc.idtipocambio=".$idtipoCambio."
				Order By tc.fechatc desc,tc.estado desc,tc.idmoneda";

		return $this->EjecutaConsulta($sql);
	}		
}
?>
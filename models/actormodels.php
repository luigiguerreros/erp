<?php
Class Actor extends Applicationbase{
	private $tabla="wc_actor";
	private $tabla2="wc_actorrol,wc_actor";
	
	
	public function validaActor($username,$password){
		$exito=$this->leeRegistro2(
					$this->tabla2,
					"",
					"usuario='".$username."' and contrasena='".$password."' and usuario<>''",
					"","");		
			return $exito;
	}
	public function validaAutorizacion($username,$password){
		$exito=$this->leeRegistro(
				"wc_actor as a inner join wc_actorrol as ar on a.`idactor`=ar.`idactor`",
				"",
				"a.`usuario`='".$username."' and a.`contrasena`='".$password."'  and  ar.`idrol`='33'",
				"","");		
		return $exito;
	}
	public function validaAutorizacionIngresos($username,$password){
		$exito=$this->leeRegistro(
				"wc_actor as a inner join wc_actorrol as ar on a.`idactor`=ar.`idactor`",
				"",
				"a.`usuario`='".$username."' and a.`contrasena`='".$password."'  and  ar.`idrol`='33'",
				"","");		
		return $exito;
	}
	public function validaAutorizacionVentas($username,$password){
		$exito=$this->leeRegistro(
				"wc_actor as a inner join wc_actorrol as ar on a.`idactor`=ar.`idactor`",
				"",
				"a.`usuario`='".$username."' and a.`contrasena`='".$password."'  and  ar.`idrol`='33'",
				"","");		
		return $exito;
	}
	/**
	 * Function BuscarxId
	 * Permite obtener los datos de un usuario con su Id
	 *
	 * @param Integer $id
	 * @return array $data
	 */
	public function buscarxid($id){
		$data=$this->leeRegistro($this->tabla,"","idactor=".$id,"","");
		return $data;
	}


	public function listaActorRol(){
		$data=$this->leeRegistro("wc_actorrol as t1 Inner join wc_rol as t2  on t1.idrol=t2.idrol ","t1.idactor,t1.idrol,t2.nombre","t1.estado=1 and t2.estado=1","","");
		return $data;
	}
	

	function listaRolesxIdActor($idactor){
		$data=$this->leeRegistro("wc_actorrol as t1 Inner join wc_rol as t2  on t1.idrol=t2.idrol and t1.idactor='".$idactor."' ","t1.idrol","t1.estado=1 and t2.estado=1","","");
		return $data;
	}

	function listaVendedoresPaginado($pagina){
			$data=$this->leeRegistroPaginado(
				$this->tabla,
				"",
				"estado=1",
				"",$pagina);
			return $data;
	}

	function listaSoloVendedoresPaginado($pagina){
		$data=$this->leeRegistroPaginado(
			"wc_actorrol as t1 Inner Join wc_actor as t2 on t1.idactor=t2.idactor",
			"",
			"t1.idrol=25 and t1.estado=1 and t2.estado=1",
			"",$pagina);
		return $data;
	}

	function listaVendedoresPaginadoxnombre($pagina,$condicion=""){
		$condicion=($condicion!="")?$condicion:"";
		$data=$this->leeRegistroPaginado(
			$this->tabla,
			"",
			"(nombres like '%$condicion%') or (apellidopaterno like '%$condicion%') or (apellidomaterno like '%$condicion%') or (codigoa='$condicion') and estado=1  ",
			"",$pagina);
		return $data;
	}
	function listaSoloVendedoresPaginadoxnombre($pagina,$condicion=""){
		$condicion=($condicion!="")?$condicion:"";
		$data=$this->leeRegistroPaginado(
			"wc_actorrol as t1 Inner Join wc_actor as t2 on t1.idactor=t2.idactor",
			"",
			"t1.idrol=25 and t2.estado=1 and t1.estado=1 and (t2.nombres like '%$condicion%') or (t2.apellidopaterno like '%$condicion%') or (t2.apellidomaterno like '%$condicion%') or (t2.codigoa='$condicion')  ",
			"",$pagina);
		return $data;
	}
	function paginadoVendedores(){
		return $this->paginado($this->tabla,"","estado=1");
	}
	function paginadoSoloVendedores(){
		return $this->paginado("wc_actorrol as t1 Inner Join wc_actor as t2 on t1.idactor=t2.idactor","","t1.idrol=25 and t2.estado=1 and t1.estado=1");
	}
	function paginadoVendedoresxnombre($condicion=""){
		$condicion=($condicion!="")?$condicion:"";
		return $this->paginado($this->tabla,"","nombres like '%$condicion%' or apellidopaterno like '%$condicion%' or apellidomaterno like '%$condicion%' and estado=1");
	}
	function paginadoSoloVendedoresxnombre($condicion=""){
		$condicion=($condicion!="")?$condicion:"";
		return $this->paginado("wc_actorrol as t1 Inner Join wc_actor as t2 on t1.idactor=t2.idactor","","t2.estado=1 and t1.estado=1 and t1.idrol=25 and t2.nombres like '%$condicion%' or t2.apellidopaterno like '%$condicion%' or t2.apellidomaterno like '%$condicion%' ");
	}
	public function buscaxApellido($data){
		$data=htmlentities($data,ENT_QUOTES,'UTF-8');
		$condicion="estado=1 and ((apellidopaterno like '%$data%') or 
		(apellidomaterno like '%$data%') or 
		(nombres like '%$data%')) ";
		$data=$this->leeRegistro($this->tabla,"",$condicion, "","");
		return $data;
	}
	public function SolobuscaxApellido($data){
		$data=htmlentities($data,ENT_QUOTES,'UTF-8');
		$condicion="(t1.idrol=25 and t2.estado=1 and t1.estado=1 and t2.apellidopaterno like '%$data%') or 
		(t2.apellidomaterno like '%$data%') or 
		(t2.nombres like '%$data%')  ";
		$data=$this->leeRegistro("wc_actorrol as t1 Inner Join wc_actor as t2 on t1.idactor=t2.idactor","",$condicion, "","");
		return $data;
	}
	public function grabaActor($data){
		$exito=$this->grabaRegistro($this->tabla,$data);
		return $exito;
	}
	
	public function ActualizaActor($data,$filtro){
		$exito=$this->actualizaRegistro($this->tabla,$data,$filtro);
		return $exito;
	}
	
	public function EstadoActor($idActor){
		$exito=$this->cambiaEstado($this->tabla,"idactor=".$idActor);
		return $exito;		
	}
	public function EstadoActorRol($idActor){
		$exito=$this->cambiaEstado('wc_actorrol',"idactor=".$idActor);
	}
		
	public function listadoActor($inicio=0,$tamanio=10){
		
		$inicio=($inicio-1)*$tamanio;
		if($inicio<0){
			$inicio=0;
		}
		
		$data=$this->leeRegistro($this->tabla,"","estado=1 and usuario !=''","","Limit ".$inicio.",".$tamanio);
		//$data=$this->leeRegistro($this->tabla,"idactor,codigoa,nombres,apellidopaterno,apellidomaterno,dni,email,usuario,contrasena","estado=1 and usuario !=''","","Limit ".$inicio.",".$tamanio);
		return $data;
	}
	
	public function Paginacion($tamanio,$condicion=""){
		$condicion=($condicion!="")?$condicion.=" and":"";
		$data=$this->leeRegistro($this->tabla,"idactor","$condicion estado=1 and usuario !=''","","");
		$paginas=ceil(count($data)/$tamanio);
		return $paginas;
	}
	
	public function EliminaOpcion($idActor){
		$exito=$this->eliminaRegistro($this->tabla,"idactor=".$idActor);

		if ($exito) {
			$exito=$this->eliminaRegistro("wc_actorrol","idactor=".$idActor);
		}
		
		return $exito;
	}
	
	public function ActualizaActor2($data,$id){
		$exito=$this->actualizaRegistro($this->tabla,$data,"id=".$id);
		return $exito;
	}
	
	public function listadovendedores($inicio,$tamanio,$nombre){
		$nombre=htmlentities($nombre,ENT_QUOTES,'UTF-8');
		$inicio=($inicio-1)*$tamanio;
		if($inicio<0){
			$inicio=0;
		}
		$data=$this->leeRegistro2($this->tabla2,"","t1.idrol=25 and t1.estado=1 and concat(t2.nombres,
				' ',t2.apellidopaterno,' ',t2.apellidomaterno) like '%$nombre%' ","","limit $inicio,$tamanio");
		return $data;
	}
	public function listadoVendedoresTodos(){
		$data=$this->leeRegistro2($this->tabla2,
			"concat(t2.nombres,' ',t2.apellidopaterno,' ',t2.apellidomaterno) as nombreconcat,t2.idactor,
			t2.direccion,t2.telefono,t2.celular,t2.email,t2.dni,t2.rpm,t2.codigoa","t1.idrol=25 and t1.estado=1 ","nombreconcat","");
		return $data;
	}
	public function paginacionVendedor($tamanio,$condicion=""){
		$condicion=($condicion!="")?$condicion.=" and":"";
		$data=$this->leeRegistro2($this->tabla2,"t1.idactor","$condicion t1.estado=1 and t1.idrol=25","","");
		$paginas=ceil(count($data)/$tamanio);
		return $paginas;
	}
	public function buscaautocompletev($tex){
		$tex=htmlentities($tex,ENT_QUOTES,'UTF-8');
		$datos=$this->leeRegistro2($this->tabla2,"t2.nombres,t2.apellidopaterno,t2.apellidomaterno,t2.idactor","t1.idrol=25 and t1.estado=1 and concat(t2.nombres,
				' ',t2.apellidopaterno,' ',t2.apellidomaterno,' ') like '%$tex%' ","","");
		foreach($datos as $valor){
			$dato[]=array("value"=>(html_entity_decode($valor['nombres'],ENT_QUOTES,'UTF-8')).' '.(html_entity_decode($valor['apellidopaterno'],ENT_QUOTES,'UTF-8')).' '.(html_entity_decode($valor['apellidomaterno'],ENT_QUOTES,'UTF-8')),"label"=>(html_entity_decode($valor['nombres'],ENT_QUOTES,'UTF-8')).' '.(html_entity_decode($valor['apellidopaterno'],ENT_QUOTES,'UTF-8')).' '.(html_entity_decode($valor['apellidomaterno'],ENT_QUOTES,'UTF-8')),"id"=>$valor['idactor']);
		}
		return $dato;
	}
	public function buscaautocompletevcobrador($tex){
		$tex=htmlentities($tex,ENT_QUOTES,'UTF-8');
		$datos=$this->leeRegistro2($this->tabla2,"t2.nombres,t2.apellidopaterno,t2.apellidomaterno,t2.idactor","t1.idrol=28 and t1.estado=1 and concat(t2.nombres,
				' ',t2.apellidopaterno,' ',t2.apellidomaterno,' ') like '%$tex%' ","","");
		foreach($datos as $valor){
			$dato[]=array("value"=>(html_entity_decode($valor['nombres'],ENT_QUOTES,'UTF-8')).' '.(html_entity_decode($valor['apellidopaterno'],ENT_QUOTES,'UTF-8')).' '.(html_entity_decode($valor['apellidomaterno'],ENT_QUOTES,'UTF-8')),"label"=>(html_entity_decode($valor['nombres'],ENT_QUOTES,'UTF-8')).' '.(html_entity_decode($valor['apellidopaterno'],ENT_QUOTES,'UTF-8')).' '.(html_entity_decode($valor['apellidomaterno'],ENT_QUOTES,'UTF-8')),"id"=>$valor['idactor']);
		}
		return $dato;
	}

	public function GeneraCodigo(){
		$maxcodigo=$this->leeRegistro($this->tabla,"max(idactor)","","","");
		$data['codigo'] ='GC'.str_pad($maxcodigo[0]['max(idactor)'] ,5,'0',STR_PAD_LEFT);	
		$this->actualizaRegistro($this->tabla,$data,"idactor=".$maxcodigo[0]['max(idactor)']);		
		return $data;
	}
	
	public 	function verificaCodigo($condicion=""){
		$data=$this->leeRegistro($this->tabla,"count(*)","estado=1 and codigoa='$condicion'","","");

		return $data[0]['count(*)'];
		
	}
	public function listadoCobradores(){
		$data=$this->leeRegistro2($this->tabla2,
			"t2.idactor,concat(t2.nombres,' ',t2.apellidopaterno,' ',t2.apellidomaterno) as nombre,
			t2.direccion,t2.telefono,t2.celular,t2.email,t2.dni,t2.rpm,t2.codigoa","t1.idrol=28 and t1.estado=1 ","nombre","");
		return $data;
	}
	public function listaTodosActores(){
		$data=$this->leeRegistro($this->tabla,"idactor","","","");
		return $data;
	}
}
?>
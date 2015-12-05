<?php
Class Cliente extends Applicationbase{
	private $_table;
	private $_tableSucursal;
	private $tabla1="wc_cliente";
	private $t_OrdenVenta="wc_ordenventa";
	private $t_cliente="wc_cliente";
	private $tabla2 = "wc_cliente as c,wc_clientesucursal as cs,wc_clientezona as cz,wc_distrito as d,wc_provincia as p,wc_departamento as de";
	private $tabla3 = "wc_cliente as c,wc_clientesucursal as cs,wc_clientezona as cz,wc_clientevendedor as cv,wc_distrito as d,wc_provincia as p,wc_departamento as de";
	private $tabla4 = "wc_cliente as c,wc_clientesucursal as cs,wc_clientezona as cz,wc_clientevendedor as cv,wc_distrito as d,wc_provincia as p,wc_departamento as de";
	private $tabla="wc_cliente,wc_distrito,wc_provincia,wc_departamento";
	private $departamento="wc_departamento";
	private $provincia="wc_provincia";
	private $distrito="wc_distrito";
	
	function __construct(){
		parent::__construct();
		$this->_table="wc_cliente";
		$this->_tableSucursal="wc_clientesucursal";
	}
	
	
	public function GeneraCodigoTodos(){
		$ObjCliente=$this->leeRegistro($this->_table,"idcliente","codcliente=''","idcliente asc","");
		foreach ($ObjCliente as $cliente) {
			$data['codcliente']=date('y').str_pad($cliente['idcliente'],6,'0',STR_PAD_LEFT);
			$data['bloqueado']=0;			
			$this->actualizaRegistro($this->_table,$data,"idcliente=".$cliente['idcliente']);
					
		}
	}
	public function GeneraCodigoNuevo($idCliente=""){
		$data['codcliente']=date('y').str_pad($idCliente,6,'0',STR_PAD_LEFT);
		$data['bloqueado']=0;
		$this->actualizaRegistro($this->tabla1,$data,"idcliente=".$idCliente);
	}

	public function ActualizaDirecciones(){
		$direcciones=file(ROOT."CLIENTES.txt");
		foreach ($direcciones as $direccion) {
			$fila= split(',',$direccion);			
			$ObjCliente=$this->leeRegistro($this->_table,"idcliente","codantiguo=".$fila[0],"","");	
			$data['direccion']=$fila[2];
			$this->actualizaRegistro($this->_tableSucursal,$data,"idcliente=".$ObjCliente[0]['idcliente']);
		}	
	}
	
	function buscaSucursal($idClienteSucursal){
		$data=$this->leeRegistro($this->_tableSucursal,"","idclientesucursal='$idClienteSucursal'","");
		return $data;
	}

	function listadoClientes(){
		$cliente=$this->leeRegistro($this->tabla1,"idcliente,razonsocial,ruc,codantiguo,telefono,celular,fax,email,dni,paginaweb,inicioactividades","","","");
		return $cliente;
	}
	function listadoTotalClientes(){
		$cliente=$this->leeRegistro($this->tabla1,"","","","");
		return $cliente;
	}
	function listadoxFiltro($filtro){
		$cliente=$this->leeRegistro($this->tabla1,"",$filtro,"","");
		return $cliente;
	}
	function actualizaCliente($data,$filtro){
		$exito=$this->actualizaRegistro($this->tabla1,$data,$filtro);
		return $exito;
	}
	function buscaCliente($idCliente){
		$cliente=$this->leeRegistro($this->tabla1,"","idcliente=$idCliente","");
		return $cliente;
	}
	function buscaClienteLugar($idCliente){
		$cliente=$this->leeRegistro4($this->tabla,"","idcliente=$idCliente","");
		return $cliente;
	}
	function buscaAutocompleteConSucursal($codigoCliente){
		$codigoCliente=htmlentities($codigoCliente,ENT_QUOTES,'UTF-8');
		$condicion='';
		$tabla = $this->tabla2;
		if($_SESSION['idrol']==25){
			$condicion = 'cv.idvendedor='.$_SESSION['idactor'].' and c.idcliente=cv.idcliente and';
			$tabla = $this->tabla4;
		}
		//$cliente=$this->leeRegistro4($this->tabla,"","CONCAT(nombrecli,' ',apellido1,' ',apellido2, ' ', razonsocial) LIKE '%$codigoCliente%'","");
		$cliente=$this->leeRegistro($tabla,"","c.idcliente=cz.idcliente and ".
				"c.iddistrito=d.iddistrito and d.idprovincia=p.idprovincia and ".
				"c.idcliente=cs.idcliente and ".
				"p.iddepartamento=de.iddepartamento and ($condicion razonsocial LIKE '%$codigoCliente%' or ruc like '$codigoCliente%')","","limit 0,10");
		foreach($cliente as $valor){
			$dato[]=array("value"=>($valor['razonsocial']!='')?(html_entity_decode($valor['razonsocial'],ENT_QUOTES)):(html_entity_decode($valor['nombrecli'],ENT_QUOTES))." ".(html_entity_decode($valor['apellido1'],ENT_QUOTES))." ".(html_entity_decode($valor['apellido2'],ENT_QUOTES)),
						"label"=>(html_entity_decode($valor['razonsocial'],ENT_QUOTES))." // ".$valor['direccion_fiscal']." // ".$valor['direccion_despacho_contacto'],
						"idcliente"=>$valor['idcliente'],
						"idclientezona"=>$valor['idclientezona'],
						"rucdni"=>($valor['razonsocial'])?$valor['ruc']:$valor['dni'],
						"direccion"=>$valor['direccion'],
						"distritociudad"=>$valor['nombredistrito']." - ".$valor['nombreprovincia']." - ".$valor['nombredepartamento'],
						"codigocliente"=>$valor['codcliente'],
						"codigoantiguo"=>$valor['codantiguo'],
						"telefono"=>$valor['telefono'],
						//"agenciatransporte"=>$valor['idtransporte'],
						"faxcelular"=>$valor['celular'],
						"id"=>$valor['idcliente'],
						"idclientesucursal"=>$valor['idclientesucursal'],
						"direccion_fiscal"=>$valor['direccion_fiscal'],
						"direccion_despacho_contacto"=>$valor['direccion_despacho_contacto'],
						"email"=>$valor['email']
						);
		}
		return $dato;
	}
	function buscaAutocomplete($codigoCliente){
		$codigoCliente=htmlentities($codigoCliente,ENT_QUOTES,'UTF-8');
		$condicion='';
		$tabla = "wc_cliente as c,wc_distrito as d,wc_provincia as p,wc_departamento as de";
		
		//$cliente=$this->leeRegistro4($this->tabla,"","CONCAT(nombrecli,' ',apellido1,' ',apellido2, ' ', razonsocial) LIKE '%$codigoCliente%'","");
		$cliente=$this->leeRegistro($tabla,"","".
				"c.iddistrito=d.iddistrito and d.idprovincia=p.idprovincia and c.estado=1 and ".
				"p.iddepartamento=de.iddepartamento and ($condicion razonsocial LIKE '%$codigoCliente%' or ruc like '$codigoCliente%')","","limit 0,10");
		foreach($cliente as $valor){
			$dato[]=array("value"=>($valor['razonsocial']!='')?(html_entity_decode($valor['razonsocial'],ENT_QUOTES,'UTF-8')):(html_entity_decode($valor['nombrecli'],ENT_QUOTES,'UTF-8'))." ".(html_entity_decode($valor['apellido1'],ENT_QUOTES,'UTF-8'))." ".(html_entity_decode($valor['apellido2'],ENT_QUOTES,'UTF-8')),
						"label"=>(html_entity_decode($valor['razonsocial'],ENT_QUOTES,'UTF-8')),
						"idcliente"=>$valor['idcliente'],
						
						"rucdni"=>($valor['razonsocial'])?$valor['ruc']:$valor['dni'],
						"direccion"=>$valor['direccion'],
						"distritociudad"=>$valor['nombredistrito']." - ".$valor['nombreprovincia']." - ".$valor['nombredepartamento'],
						"codigocliente"=>$valor['codcliente'],
						"codigoantiguo"=>$valor['codantiguo'],
						"telefono"=>$valor['telefono'],
						//"agenciatransporte"=>$valor['idtransporte'],
						"faxcelular"=>$valor['celular'],
						"id"=>$valor['idcliente'],
						"direccion_fiscal"=>(html_entity_decode($valor['direccion'],ENT_QUOTES,'UTF-8')),
						"direccion_despacho_contacto"=>(html_entity_decode($valor['direccion_despacho_cliente'],ENT_QUOTES,'UTF-8')),
						"nombre_contacto"=>(html_entity_decode($valor['nombre_contacto'],ENT_QUOTES,'UTF-8')),
						"email"=>$valor['email']
						);
		}
		return $dato;
	}
	function buscaAutocompleteClienteZona($codigoCliente){
		$codigoCliente=htmlentities($codigoCliente,ENT_QUOTES,'UTF-8');
		$condicion='';
		$tabla = "wc_cliente as c,wc_clientezona as cz,wc_distrito as d,wc_provincia as p,wc_departamento as de";
		
		//$cliente=$this->leeRegistro4($this->tabla,"","CONCAT(nombrecli,' ',apellido1,' ',apellido2, ' ', razonsocial) LIKE '%$codigoCliente%'","");
		$cliente=$this->leeRegistro($tabla,"","c.idcliente=cz.idcliente and c.estado=1 and ".
				"c.iddistrito=d.iddistrito and d.idprovincia=p.idprovincia and ".
				"p.iddepartamento=de.iddepartamento and cz.estado=1 and ($condicion razonsocial LIKE '%$codigoCliente%' or ruc like '$codigoCliente%')","","limit 0,10");
		foreach($cliente as $valor){
			$dato[]=array("value"=>($valor['razonsocial']!='')?(html_entity_decode($valor['razonsocial'],ENT_QUOTES,'UTF-8')):(html_entity_decode($valor['nombrecli'],ENT_QUOTES,'UTF-8'))." ".(html_entity_decode($valor['apellido1'],ENT_QUOTES,'UTF-8'))." ".(html_entity_decode($valor['apellido2'],ENT_QUOTES,'UTF-8')),
						"label"=>(html_entity_decode($valor['razonsocial'],ENT_QUOTES,'UTF-8')).' / '.(html_entity_decode($valor['direccion_fiscal'],ENT_QUOTES,'UTF-8')),
						"idcliente"=>$valor['idcliente'],
						"idclientezona"=>$valor['idclientezona'],
						"rucdni"=>($valor['razonsocial'])?$valor['ruc']:$valor['dni'],
						"direccion"=>(html_entity_decode($valor['direccion'],ENT_QUOTES,'UTF-8')),
						"distritociudad"=>$valor['nombredistrito']." - ".$valor['nombreprovincia']." - ".$valor['nombredepartamento'],
						"codigocliente"=>$valor['codcliente'],
						"codigoantiguo"=>$valor['codantiguo'],
						"telefono"=>$valor['telefono'],
						//"agenciatransporte"=>$valor['idtransporte'],
						"faxcelular"=>$valor['celular'],
						"id"=>$valor['idcliente'],
						"direccion_fiscal"=>(html_entity_decode($valor['direccion_fiscal'],ENT_QUOTES,'UTF-8')),
						"direccion_despacho_contacto"=>(html_entity_decode($valor['direccion_despacho_contacto'],ENT_QUOTES,'UTF-8')),
						"nombre_contacto"=>(html_entity_decode($valor['nomcontacto'],ENT_QUOTES,'UTF-8')),
						"email"=>$valor['email']
						);
		}
		return $dato;
	}
	function cambiaEstadoCliente($idCliente){
		$estado=$this->cambiaEstado($this->tabla1,"idcliente=".$idCliente);
		return $estado;
	}
	function grabaCliente($data){
		$exito=$this->grabaRegistro($this->tabla1,$data);
		return $exito;
	}
	function listadoDepartamento(){
		$pais=$this->leeRegistro($this->departamento,"","","");
		return $pais;
	}
	function listadoProvincia($idDepartamento){
		$pais=$this->leeRegistro($this->provincia,"","id=".$idDepartamento,"");
		return $pais;
	}
	function listadoDistrito(){
		$pais=$this->leeRegistro($this->distrito,"","","");
		return $pais;
	}
	//*****
	public function listadoCliente($inicio=0,$tamanio=10){
		$inicio=($inicio-1)*$tamanio;
		if($inicio<0){
			$inicio=0;
		}
		$data=$this->leeRegistro4($this->tabla,"","","","Limit ".$inicio.",".$tamanio);
		return $data;
	}

	public function Paginacion($tamanio,$condicion=""){
		$data=$this->leeRegistro4($this->tabla,"","$condicion","","");
				//	echo count($data);
				//	print_r($data);
				// exit;
		$paginas=intval((count($data)/$tamanio))+1;
		$paginas=$paginas>0?$paginas:1;
		return $paginas;
	}
	
	public function buscaxRazonSocial($razonsocial){
		$razonsocial=htmlentities($razonsocial,ENT_QUOTES,'UTF-8');
		$data=$this->leeRegistro($this->tabla1,"","razonsocial like '$razonsocial%' ", "","");
		return $data;
	}
	
	public function buscaxid($id){
		$data=$this->leeRegistro($this->tabla1,"","idcliente=".$id,"","");
		return $data;
	}
	function buscarxnombre($inicio,$tamanio,$nombre){
		$nombre=htmlentities($nombre,ENT_QUOTES,'UTF-8');
		$inicio=($inicio-1)*$tamanio;
		if($inicio<0){
			$inicio=0;
		}
		$data=$this->leeRegistro($this->tabla1,"","razonsocial like '%$nombre%' and estado=1","","limit $inicio,$tamanio");
		return $data;
	}
	function autocomplete($tex){
		$tex=htmlentities($tex,ENT_QUOTES,'UTF-8');
		$datos=$this->leeRegistro($this->tabla1,"razonsocial,
			idcliente,razonsocial","concat(razonsocial,' ',codantiguo) LIKE \"%$tex%\"","");
		foreach($datos as $valor){
			$dato[]=array("value"=>(html_entity_decode($valor['razonsocial'],ENT_QUOTES,'UTF-8')),"label"=>(html_entity_decode($valor['razonsocial'],ENT_QUOTES,'UTF-8')),"id"=>$valor['idcliente']);
		}
		return $dato;
	}
	function datosxnombre($nombreconcat){
		$nombreconcat=htmlentities($nombreconcat,ENT_QUOTES,'UTF-8');
		$data=$this->leeRegistro($this->tabla1,"","razonsocial=$nombreconcat","","");
		return $data;
	}
	function generaCodigo(){
		$data=$this->leeRegistro($this->tabla,"MAX(CAST(SUBSTRING(codcliente, 6, 6) AS DECIMAL)) AS codigo","","");
		$codigo="";
		if($data[0]['codigo']==0){
			$codigo="OV-".date('y')."000001";
		}else{
			$valor="0000000000".($data[0]['codigo']+1);
			$codigo="CLN".substr($valor,strlen($valor)-9,9);
		}
		return $codigo;
	}
	function nombrexid($id){
		$data=$this->leeRegistro($this->tabla1,"razonsocial","idcliente=$id","");
		return $data[0][0];
	}

	function verificaCodigo($condicion=""){
		$data=$this->leeRegistro($this->tabla1,"count(*)","estado=1 and codantiguo='$condicion'","","");

		return $data[0]['count(*)'];
		
	}
	function listaClientesPaginado($pagina){
			$data=$this->leeRegistroPaginado(
				$this->tabla1,
				"",
				"estado=1",
				"",$pagina);
			return $data;
	}
	function listaClientesPaginadoxnombre($pagina,$condicion=""){
		$condicion=($condicion!="")?(htmlentities($condicion,ENT_QUOTES,'UTF-8')):"";
		$data=$this->leeRegistroPaginado(
			$this->tabla1,
			"",
			"(razonsocial like '%$condicion%' or (nombrecli='$condicion' or apellido1='$condicion' or apellido2='$condicion' or ruc='$condicion' ) ) and estado=1  ",
			"zona",$pagina);
		return $data;
	}
	function paginadoClientes(){
		
		$data=$this->paginado($this->tabla1,"","estado=1");
		
		return $data;
	}
	function paginadoClientesxnombre($condicion=""){
		$condicion=($condicion!="")?(htmlentities($condicion,ENT_QUOTES,'UTF-8')):"";
		return $this->paginado($this->tabla1,"","(razonsocial like '%$condicion%' or (nombrecli='$condicion' or apellido1='$condicion' or apellido2='$condicion' or ruc='$condicion' ) ) and estado=1  ");
	}
	public function buscaxnombre($condicion){
		$condicion=htmlentities($condicion,ENT_QUOTES,'UTF-8');
		$filtro="(razonsocial like '%$condicion%' or (nombrecli='$condicion' or apellido1='$condicion' or apellido2='$condicion' or ruc='$condicion' ) ) and estado=1  ";
		$data=$this->leeRegistro($this->tabla1,"",$filtro, "","");
		return $data;
	}

	// Buscqueda de Clientes por Orden de Venta:
	public function buscaxOrdenVenta($IdOrdenVenta)
	{
		$sql="Select 
		c.idcliente,c.razonsocial,c.ruc,c.codcliente,c.codantiguo,c.telefono,c.celular,c.horarioatencion,
		ov.fordenventa,ov.fechadespacho,ov.fechavencimiento,ov.importeov,ov.escomisionado,ov.idordenventa,
		ov.mventas,ov.codigov,ov.direccion_envio,ov.direccion_despacho,ov.contacto,ov.observaciones as condiciones,ov.idtipocobranza,t.trazonsocial as razonsocialtransp,
		ov.nrocajas,ov.nrobultos,ov.iddespachador,ov.idverificador,ov.idverificador2,ov.idvendedor,ov.porComision,ov.situacion,ov.importedevolucion,ov.importepagado,
		concat(a.apellidopaterno,' ',a.apellidomaterno,' ',a.nombres) as vendedor,dis.nombredistrito,
		pro.nombreprovincia,dep.nombredepartamento  
		From wc_cliente c
		Inner Join wc_clientezona cz On c.idCliente=cz.idCliente
		
		Inner Join wc_clientetransporte ct On ct.idCliente=c.idCliente
		Inner Join wc_transporte t On t.idtransporte=ct.idtransporte
		Inner Join wc_ordenventa ov On ov.idClientezona=cz.idClientezona and ov.idclientetransporte=ct.idclientetransporte
		Inner Join wc_actor a On a.idactor=ov.idvendedor
		INNER JOIN wc_distrito dis ON c.`iddistrito` = dis.iddistrito
	    INNER JOIN wc_provincia pro ON dis.`idprovincia` = pro.idprovincia
	    INNER JOIN wc_departamento dep ON pro.iddepartamento = dep.iddepartamento
		Where idOrdenVenta=".$IdOrdenVenta;
		return $dataCliente=$this->EjecutaConsulta($sql);
	}
	
	public function detalleposicion($idCliente)
	{
		$sql="Select cp.* from wc_cliente c
		Inner Join wc_clienteposicion cp On c.idcliente=cp.idCliente
		Where cp.idcliente=".$idCliente;
		return $dataCliente=$this->EjecutaConsulta($sql);
	}

	public function detalleposicionactivo($idCliente)
	{
		$sql="Select cp.* from wc_cliente c
		Inner Join wc_clienteposicion cp On c.idcliente=cp.idCliente
		Where cp.estado=1 and cp.idcliente=".$idCliente;
		return $dataCliente=$this->EjecutaConsulta($sql);
	}

	public function deudaOrdenVenta($idCliente)
	{
		$sql="Select oc.idordenventa,oc.idordencobro,ov.codigov,oc.importeordencobro,SUM(saldoordencobro) as saldo from wc_ordenventa ov
		Inner Join wc_ordencobro oc On  ov.idordenventa=oc.idOrdenVenta
		Where ov.esguiado=1 and ov.idclientezona=".$idCliente." 
		Group by oc.idordenventa,oc.idordencobro,ov.codigov,ov.importeov
		Order by ov.codigov desc";
		return $dataCliente=$this->EjecutaConsulta($sql);
	}

	public function restarSaldo($idcliente,$montoordencobro)
	{
		$sql="Update wc_clienteposicion 
		set saldo=saldo - ".$montoordencobro." 
		where idcliente=".$idcliente." and estado=1";
		$dataidcliente=$this->EjecutaConsultaBoolean($sql);
		return $dataidcliente;
	}

	public function idclientexidordenventa($idorden)
	{
		$sql="Select idclientezona from wc_ordenventa where idordenventa=".$idorden;
		$dataidcliente=$this->EjecutaConsulta($sql);
		return $dataidcliente[0]['idclientezona'];
	}
	public function consultaClientes(){
		$sql="Select * From wc_cliente";
		$data=$this->EjecutaConsulta($sql);
		return $data;
	}

	public function deudaTotalxIdCliente($idcliente){
		$sql="select cli.idcliente,ov.idmoneda,ov.importepagado-sum(og.importegasto) as deuda 
			from wc_ordenventa ov
			Inner Join wc_ordengasto og On ov.idordenventa=og.idordenventa and ov.situacion='pendiente'
			Inner Join wc_cliente cli On ov.idcliente=cli.idcliente
			Group By cli.idcliente,ov.idmoneda
			Having cli.idcliente=".$idcliente;
		$data=$this->EjecutaConsulta($sql);
		return $data;
	}

}
?>
<?php
Class seguridadcontroller extends ApplicationGeneral{	
	/**
	 * Function	: listado
	 * Author	: Fernando Garcia Atuncar
	 */
	function listado(){
		$data=New Actor();
		$actor=$data->listadoActor();
		$total=count($actor);
				for($i=0;$i<$total;$i++){
					$actor[$i]['contrasena']=$this->Desencripta($actor[$i]['contrasena']);
				}
		$datos['listaactor']=$actor;
		$this->view->show("/seguridad/listado.phtml",$datos);
	}
	
	/**
	 * 	Funcion :AsignarOpciones
	 *	Author	:Fernando Garcia Atuncar
	 */
	function AsignarOpciones(){
		$Opciones=New Opciones();
		$data['Listado']=$Opciones->ListadoOpciones();
		$rol=New Rol();
		$dataRol=$rol->RolCombo();
		$data['Rol']=$dataRol;
		$this->view->show("/seguridad/AsignarOpcionRol.phtml",$data);
		
	}
	
	function AsignarxRol(){
		if(!isset($_REQUEST['idRol'])){
			$idRol=0;
		}else{
			$idRol=$_REQUEST['idRol'];	
		}
		
		$data['idRol']=$idRol;
		
		$Opciones=New Opciones();
		$data['Listado']=$Opciones->ListadoOpciones();
		
		$rol=New Rol();
		$dataRol=$rol->RolCombo();
		$data['Rol']=$dataRol;
		
		$OpcRol=New OpcionesRol();
		$data['dataRol']=$OpcRol->OpcionesListaxId($idRol);	
	
		$this->view->show("/seguridad/AsignarOpcionRol.phtml",$data);
		
	}
	
	function asignaropcionxrol(){
		$data['idrol']=$_REQUEST['Rol'];
		$data['idopciones']=$_REQUEST['Opcion'];
		$data['estado']="1";
		$OpcionRol=New OpcionesRol();
		$exito=$OpcionRol->GrabaOpcionesRol($data);
		if($exito){
			echo "Permiso asignado con &eacute;xito";
		}
		
	}
	
	function desasignaropcionxrol(){
		$data['idrol']=$_REQUEST['Rol'];
		$data['idopciones']=$_REQUEST['Opcion'];
		$data['estado']="1";
		$OpcionRol=New OpcionesRol();
		$exito=$OpcionRol->EliminaOpcionesRol($data);
		if($exito){
			echo "Permiso quitado con &eacute;xito";
		}
	}
	

	//ASIGNADO ROLES A USUARIOS.

	function AsignarRoles(){
		if(!empty($_REQUEST['id'])){
			$idActor=$_REQUEST['id'];
			$Actor=$this->AutoloadModel("actor");
			$data['Actor']=$Actor->buscarxid($idActor);
			$data['ActorRol']=$Actor->listaRolesxIdActor($idActor);
			$Rol=$this->AutoloadModel("rol");
			$data['Roles']=$Rol->RolCombo();
			$this->view->show("/seguridad/AsignarRolActor.phtml",$data);
		}else{
			$this->view->show("/seguridad/BuscarActor.phtml",$data);
		}
	}

	function asignarrolxactor(){
		$data['idrol']=$_REQUEST['Rol'];
		$data['idactor']=$_REQUEST['Actor'];
		$data['estado']="1";
		$ActorRol=New ActorRol();
		$exito=$ActorRol->grabaActorRol($data);
		if($exito){
			echo "Rol grabado correctamente";
		}
		
	}
	
	function desasignarrolxactor(){
		$idrol=$_REQUEST['Rol'];
		$idactor=$_REQUEST['Actor'];
		$ActorRol=New ActorRol();
		$exito=$ActorRol->eliminaActorRol("idactor='".$idactor."' and idrol='".$idrol."'");
		if($exito){
			echo "Rol borrado correctamente";
		}
	}
	
	
}
?>
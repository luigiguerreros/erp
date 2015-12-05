<?php
class actorcontroller extends applicationgeneral{
	
	function login(){
		$this->view->template="login";	
		//echo $this->Desencripta("39393130363032323369");
		$this->view->show("actor/login.phtml");
	}
	
	function valida(){
		$username=$_REQUEST['usuario'];
		$password=$this->Encripta($_REQUEST['contrasena']);
		$data=New Actor();
		$actor=$data->validaActor($username,$password);
						
		if($actor!=NULL){

				date_default_timezone_set("america/lima");
				$_SESSION['codigo']=$actor[0]['usuario'];
				$_SESSION['Autenticado']=true;
				$_SESSION['apellidopaterno']=$actor[0]['apellidopaterno'];
				$_SESSION['apellidomaterno']=$actor[0]['apellidomaterno'];
				$_SESSION['nombres']=$actor[0]['nombres'];
				$_SESSION['foto']=$actor[0]['foto'];
				$_SESSION['idactor']=$actor[0]['idactor'];
				$_SESSION['idrol']=$actor[0]['idrol'];
				$_SESSION['nombrecompleto']=$actor[0]['apellidopaterno']." ".$actor[0]['apellidomaterno'].",".$actor[0]['nombres'];
				$_SESSION['horaacceso']=date("H:i:s");
				$_SESSION['nivelacceso']=$actor[0]['nivelacceso'];
				
			$camino['ruta']="/index/index";
			//$camino['ruta']="/696e646578/696e646578";	
			$this->view->show("ruteador.phtml",$camino);
			//var_export($this->view);
		}else{
			session_start();
			$_SESSION['mensaje_login']='Datos incorrectos. Revise';
			header("Location: /actor/login/");
		}
	}
	function validaAutorizacion(){
		$username=$_REQUEST['usuario'];
		$password=$this->Encripta($_REQUEST['contrasena']);
		$data=New Actor();
		$actor=$data->validaAutorizacion($username,$password);
		$dataRespuesta=array();
		if (!empty($actor)) {
			$dataRespuesta['verificacion']=true;
		}else{
			$dataRespuesta['verificacion']=false;
		}
		echo json_encode($dataRespuesta);
                echo json_decode($json);
	}
	function validaAutorizacionIngresos(){
		$username=$_REQUEST['usuario'];
		$password=$this->Encripta($_REQUEST['contrasena']);
		$data=New Actor();
		$actor=$data->validaAutorizacionIngresos($username,$password);
		$dataRespuesta=array();
		if (!empty($actor)) {
			$dataRespuesta['verificacion']=true;
		}else{
			$dataRespuesta['verificacion']=false;
		}
		echo json_encode($dataRespuesta);
	}
	function validaAutorizacionVentas(){
		$username=$_REQUEST['usuario'];
		$password=$this->Encripta($_REQUEST['contrasena']);
		$data=New Actor();
		$actor=$data->validaAutorizacionVentas($username,$password);
		$dataRespuesta=array();
		if (!empty($actor)) {
			$dataRespuesta['verificacion']=true;
		}else{
			$dataRespuesta['verificacion']=false;
		}
		echo json_encode($dataRespuesta);
        }
	function cambiaclave(){
		$this->view->show("actor/nuevaclave.phtml");
	}
	
	function grabaclave(){
		//$clave=$_REQUEST['claveanterior'];
		$data['contrasena']=$this->Encripta($_REQUEST['clavenueva1']);
		//$clave2=$_REQUEST['clavenueva2'];
		$a=New actor();
		$exito=$a->actualizaactor($data,"idactor=".$_SESSION['idactor']);
		$ruta['ruta']="/";
		$this->view->show("ruteador.phtml",$ruta);
	}
	
	function salir(){
		$this->cerrar();
	}
			
	function Listado(){
		$dataActor=New Actor();
		$id=$_REQUEST['id'];
		$tamanio=10;
		$data['actorRol']=$dataActor->listaActorRol();
		$data['actor']=$dataActor->listadoActor($id,$tamanio);
		$data['paginacion']=$dataActor->Paginacion($tamanio);
		$this->view->show("actor/listado.phtml",$data);
	}
	function Busqueda(){
		$apellido=$_REQUEST['txtBusqueda'];
		$data=New Actor();
		$actor=$data->buscaxApellido($apellido);
		$datos['actorRol']=$data->listaActorRol();
		$datos['actor']=$actor;
		$this->view->show("actor/listado.phtml",$datos);
          }
	function Editar(){
		$id=$_REQUEST['id'];
		$Accion=New Actor();
		$data_actor=$Accion->buscarxid($id);
		$data_actor[0]['contrasena']=$this->Desencripta($data_actor[0]['contrasena']);
		$data['actor']=$data_actor;
		$this->view->show("actor/editar.phtml",$data);
	}
	
	function actualiza(){
		$data=$_REQUEST['Actor'];
		$data['contrasena']=$this->Encripta($data['contrasena']);
		$id=$_REQUEST['idActor'];
		$a=New Actor();
		$exito=$a->ActualizaActor($data,"idactor=".$id);
			$ruta['ruta']="/actor/listado";
			$this->view->show("ruteador.phtml",$ruta);
	}
	function cambiaEstado(){
		$idActor=$_REQUEST['id'];
		$a=New Actor();
		$exito=$a->EstadoActor($idActor);
		if ($exito){
			$ruta['ruta']="/actor/listado";
			$this->view->show("ruteador.phtml",$ruta);
		}		
	}
	
	function Eliminar(){
		$idActor=$_REQUEST['id'];
		$a=New Actor();
		$exito=$a->EstadoActor($idActor);

		if ($exito){
			$a->EstadoActorRol($idActor);
			$ruta['ruta']="/actor/listado";
			$this->view->show("ruteador.phtml",$ruta);
		}		
	}
	
	function Nuevo(){
		$this->view->show("actor/nuevo.phtml");
	}
	function grabaActor(){
		$data=$_REQUEST['Actor'];
		$data['estado']=1;
		$a=New Actor();
		$exito=$a->grabaActor($data);
		$ruta['ruta']="/actor/listado";
		$this->view->show("ruteador.phtml",$ruta);
	}

	
}
?>
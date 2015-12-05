<?php
	class ProveedorController extends ApplicationGeneral{

		function listado(){
			$proveedor=$this->AutoLoadModel("proveedor");
			$data['proveedor']=$proveedor->listaProveedoresPaginado($_REQUEST['id']);
			$data['paginacion']=$proveedor->paginadoProveedor();
			$this->view->show("proveedor/listado.phtml",$data);
		}

		function nuevo(){
			$departamento=new Departamento();
			$transporte=new Transporte();
			$data['Departamento']=$departamento->listado();
			$data['Transporte']=$transporte->listaTodo();
			$data['TipoProveedor']=$this->tipoCliente();
			$this->view->show("proveedor/nuevo.phtml",$data);
		}
		function graba(){
			$data = $_REQUEST['Proveedor'];
			$data['estado']=1;
			$proveedor = new Proveedor();
			$actorRol=new ActorRol();
			$idProveedor = $proveedor->grabaProveedor($data);
			if($idProveedor){
				$ruta['ruta']="/proveedor/listado";
				$this->view->show("ruteador.phtml", $ruta);	
			}
		}
		function editar(){
			$id = $_REQUEST['id'];
			$proveedor= new Proveedor();
			$distrito=new Distrito();
			$provincia=new Provincia();
			$departamento=new Departamento();
			$dataProveedor=$proveedor->buscaProveedor($id);
			$data['Proveedor']=$dataProveedor;
			$data['TipoProveedor']=$this->tipoCliente();
			$this->view->show("proveedor/editar.phtml", $data);
			//*/
		}

		function actualiza(){
			$data = $_REQUEST['Proveedor'];
			$id = $_REQUEST['idProveedor'];
			$proveedor = new Proveedor();
			$exito = $proveedor->actualizaProveedor($data,"idproveedor=".$id);
			$this->view->show("ruteador.phtml", $ruta);
			if($exito){
				$ruta['ruta']="/proveedor/listado";
				$this->view->show("ruteador.phtml", $ruta);
			}
		}

		function eliminar(){
			$id=$_REQUEST['id'];
			$proveedor=new Proveedor();
			$estado=$proveedor->cambiaEstadoProveedor($id);
			if($estado){
				$ruta['ruta']="/proveedor/listado";
				$this->view->show("ruteador.phtml",$ruta);
			}
		}

		function busqueda(){
			$texto=$_REQUEST['txtBusqueda'];
			$proveedor=$this->AutoLoadModel("proveedor");
			$data['proveedor']=$proveedor->buscarxnombre(0,10,$texto);
			$this->view->show("proveedor/listado.phtml",$data);
		}


		function provincia(){
			$id=$_REQUEST['lstDepartamento'];
			$provincia=new Proveedor();
			$data['Provincia']=$provincia->listadoProvincia($id);
		}
		function autocomplete(){
			$id=$_REQUEST['id'];
			$proveedor=new Proveedor();
			$data=$proveedor->autocomplete($id);
			echo json_encode($data);
		}
		function buscar(){
			$proveedor=New Proveedor();
			$datos=$proveedor->listadoProveedores();
			$objeto = $this->formatearparakui($datos);
			header("Content-type: application/json");
			//echo "{\"data\":" .json_encode($objeto). "}";
			echo json_encode($objeto);
		}

		function grabaJason(){
			$linea=$this->AutoLoadModel('proveedor');
			$data['razonsocialp']=$_REQUEST['razsocProveedor'];
			$data['rlegal']=$_REQUEST['repreProveedor'];
			$data['contacto']=$_REQUEST['percontactoProveedor'];
			$data['direccionp']=$_REQUEST['direccionProveedor'];
			$data['descripcionp']=$_REQUEST['descripcionProveedor'];
			$data['rucp']=$_REQUEST['rucProveedor'];
			$data['emailp']=$_REQUEST['emailPrincipalProveedor'];
			$data['emailp2']=$_REQUEST['emailAltenativoProveedor'];
			$data['webp']=$_REQUEST['paginaProveedor'];
			$data['telefonop']=$_REQUEST['telefonoprincipalProveedor'];
			$data['telefonop2']=$_REQUEST['telefonoalternativoProveedor'];
			$data['faxp']=$_REQUEST['faxProveedor'];
			$data['estado']=1;
			$data['idpais']=46;
			$data['tipoEmpresa']=2;
			$exito=$linea->grabaProveedor($data);
			if($exito){
				$dataResp['valid']=true;
				$dataResp['resp']='Dato Agregado';
				$dataResp['idProveedor']=$exito;
				echo json_encode($dataResp);
			}else{
				$dataResp['valid']=false;
				$dataResp['resp']='No se pudo Agregar';
				echo json_encode($dataResp);
			}

		}

	}
?>
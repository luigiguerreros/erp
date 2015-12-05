<?php 

	class letrascontroller extends ApplicationGeneral{
		private $name='letras';
		public function lista()
		{
			$pagina=$_REQUEST['id'];
			if (empty($_REQUEST['id'])) {
				$pagina=1;
			}
			$object=$this->AutoLoadModel($this->name);
			$data['data']=$object->listado();
			$data['titulo']=$this->name;
			$this->view->show('/'.$this->name.'/lista.phtml',$data);
			
		}
		public function nuevo(){
			$data['titulo']=$this->name;
			$this->view->show('/'.$this->name.'/nuevo.phtml',$data);
		}
		public function grabar(){
			$object=$this->AutoLoadModel($this->name);
			$condicion=$_REQUEST['condicion'];
			$cant=$_REQUEST['cantidad'];
			
			$diasLetra = split('/', $condicion);
			$cantidad=count($diasLetra);
			$verificacion=1;
			$dato['titulo']=$this->name;

			for ($i=0; $i <$cantidad; $i++) { 
				if ($diasLetra[$i]==0 || $diasLetra[$i]=="") {
					$verificacion=0;
					
					
				}
			}

			if ($cant!=$cantidad) {
				$dato['respuesta']="No coincide la cantidad de letra con el formato";
				$this->view->show('/'.$this->name.'/nuevo.phtml',$dato);
			}
			elseif ($verificacion==0) {
				$dato['respuesta']="No debe Ingresar Cero o Vacio";
				$this->view->show('/'.$this->name.'/nuevo.phtml',$dato);
			}
			else{

				$data['cantidadletra']=$cant;
				$data['nombreletra']=$condicion;
				
				$exito=$object->graba($data);
				
				if ($exito) {
					$ruta['ruta']="/".$this->name."/lista";
					$this->view->show("ruteador.phtml",$ruta);
				}
			}
		}
		public function eliminar(){
			$idletra=$_REQUEST['id'];
			$object=$this->AutoLoadModel($this->name);
			$exito=$object->cambiaEstado($idletra);
			if ($exito) {
				$ruta['ruta']="/".$this->name."/lista";
				$this->view->show("ruteador.phtml",$ruta);
			}
		}
	}

 ?>
<?php
	class MantenimientoController extends ApplicationGeneral {
		private $mostrar=5;
		function almacen(){
			$tamanio=10;
			$id=$_REQUEST['id'];
			$url="/".$_REQUEST['url'];
			$dataAlmacen=New Almacen();
			$opciones=new general();
			$datos['Opcion']=$opciones->buscaOpcionexurl($url);
			$datos['Modulo']=$opciones->buscaModulosxurl($url);
			$datos['almacen']=$dataAlmacen->listadoAlmacen($id,$tamanio);
			$datos['Paginacion']=1;
			$datos['Pagina']=1;
			$this->view->show("mantenimiento/almacen.phtml",$datos);
		}
		function proveedor(){
			$tamanio=10;
			$proveedor = new Proveedor();
			$opciones=new general();
			$url="/".$_REQUEST['url'];
			$data['Opcion']=$opciones->buscaOpcionexurl($url);
			$data['Modulo']=$opciones->buscaModulosxurl($url);
			$data['Proveedor']=$proveedor->listadoProveedores();
			$data['Paginacion']=$proveedor->Paginacion($tamanio);
			$data['Pagina']=1;
			$this->view->show("mantenimiento/proveedor.phtml");
		}
		function cliente(){
			$dataCliente=New Cliente();
			$opciones=new general();
			$zona=new Zona();
			$cli=new Cliente();
			$url="/".$_REQUEST['url'];
			$data['Opcion']=$opciones->buscaOpcionexurl($url);
			$data['Modulo']=$opciones->buscaModulosxurl($url);
			$id=$_REQUEST['id'];
			$tamanio=10;
			$data['cliente']=$dataCliente->listadoCliente($id,$tamanio);
			$total=count($data['cliente']);
			for($i=0;$i<$total;$i++){
				if($data['cliente'][$i]['idzona']!='' && $data['cliente'][$i]['idzona']!=0){
					$data['cliente'][$i]['nombrezona']=$zona->nombrexid($data['cliente'][$i]['idzona']);
				}
			}
			$data['Paginacion']=$cli->paginacion($tamanio,"");;
			$data['Pagina']=1;
			$this->view->show("mantenimiento/cliente.phtml");
		}
		function clientezona(){
			$dataClienteZona=New ClienteZona();
			$opciones=new general();
			$zona=new Zona();
			$cliente=new Cliente();
			$url="/".$_REQUEST['url'];
			$id=$_REQUEST['id']!=''?$_REQUEST['id']:1;
			$data['Opcion']=$opciones->buscaOpcionexurl($url);
			$data['Modulo']=$opciones->buscaModulosxurl($url);
			$tamanio=10;
			$data['ClienteZona']=$dataClienteZona->listado($id,$tamanio);
			$total=count($data['ClienteZona']);
			for($i=0;$i<$total;$i++){
				if($data['ClienteZona'][$i]['idcliente']!='' && $data['ClienteZona'][$i]['idcliente']!=0){
					$data['ClienteZona'][$i]['nombrecli']=$cliente->nombrexid($data['ClienteZona'][$i]['idcliente']);
				}
				if($data['ClienteZona'][$i]['idzona']!='' && $data['ClienteZona'][$i]['idzona']!=0){
					$data['ClienteZona'][$i]['nombrezona']=$zona->nombrexid($data['ClienteZona'][$i]['idzona']);
				}
			}
			$data['Paginacion']=1;
			$data['Pagina']=1;
			$this->view->show("/mantenimiento/clientezona.phtml");
		}

		function producto(){
			$producto=new Producto();
			$opciones=new general();
			$tamanio=10;
			$id=$_REQUEST['id'];
			$url="/".$_REQUEST['url'];
			$data['Producto']=$producto->buscarxnombre($id,$tamanio,"");
			$data['Paginacion']=$producto->paginacion($tamanio,"");
			$data['RutaImagen']=$this->rutaImagenesProducto();
			$data['Pagina']=1;
			$this->view->show("mantenimiento/producto.phtml");
		}
		function producto2(){
			$this->view->show("producto/listado.phtml");
		}
		function linea(){
			$linea=new Linea();
			$data['LineaPadre']=$linea->listadolineas("idpadre=0");
			$this->view->show("mantenimiento/linea.phtml",$data);
		}
		function zona(){
			$zona=new Zona();
			$data['Categoria']=$zona->listacategorias($id,$tamanio,"");
			$this->view->show("/mantenimiento/zona.phtml",$data);
		}
		function vendedor(){
			$this->view->show("/mantenimiento/vendedor.phtml");
		}
		function transporte(){
			$this->view->show("/mantenimiento/transporte.phtml");
		}
		function condicionletra(){
			$data['origen']=$_REQUEST['id'];
			$this->view->show("/mantenimiento/condicionletra.phtml",$data);
		}
	}

?>
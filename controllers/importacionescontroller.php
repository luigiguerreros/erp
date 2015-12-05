<?php
Class importacionescontroller extends ApplicationGeneral{
	
	function ordenCompra(){
		$ordenCompra=new Ordencompra();
		$url="/".$_REQUEST['url'];
		
			


		if (empty($_REQUEST['id'])) {
			$_REQUEST['id']=1;
		}
		$data['Ordencompra']=$ordenCompra->listaOrdenCompraPaginado($_REQUEST['id']);
		$paginacion=$ordenCompra->paginadoOrdenCompra();
		$data['paginacion']=$paginacion;
		$data['blockpaginas']=round($paginacion/10);
		
		$this->view->show("/importaciones/listadoordencompra.phtml",$data);
	}

/*Registro de nueva orden de compra*/
		function nuevaordencompra(){
			$ordCom=new Ordencompra();
			$empresa=new Almacen();
			$proveedor=new Proveedor();
			//$data['CodigoOrden']=$ordCom->generaCodigo();
			$data['Empresa']=$empresa->listadoAlmacen();
			$data['Proveedor']=$proveedor->listadoProveedores();
			$data['RutaImagen']=$this->rutaImagenesProducto();
			$this->view->show("/importaciones/nuevaordencompra.phtml",$data);
		}
	//Reporte de orden de compra
	function reportordcompra(){
		//$this->view->template="reporteordencompra";
		$this->view->show('/reporte/ordencompra.phtml');
	}
	function reporteOrdenCompra(){
		$idProducto=$_REQUEST['id'];
		$repote=new Reporte();
		$data=$repote->reporteOrdenCompra($idProducto);
		for($i=0;$i<count($data);$i++){
			echo "<tr>";
				echo '<td>'.date("d/m/Y",strtotime($data[$i]['fechacompra'])).'</td>';
				echo '<td>'.$data[$i]['idalmacen'].'</td>';
				echo '<td>'.$data[$i]['idproveedor'].'</td>';
				echo '<td>'.$data[$i]['cantidadsolicitada'].'</td>';
				echo '<td>'.$data[$i]['fob'].'</td>';
			echo "</tr>";	
		}
	}
	//Lista de precios
	function reporteListaPrecio(){
		$idLinea=$_REQUEST['linea'];
		$idSubLinea=$_REQUEST['sublinea'];
		$producto=new Producto();
		$data=$producto->listaPrecio($idLinea,$idSubLinea);
		for($i=0;$i<count($data);$i++){
			echo '<tr>';
				echo "<td>".$data[$i]['codigo']."</td>";
				echo "<td>".$data[$i]['nompro']."</td>";
				echo "<td>".$data[$i]['preciolista']."</td>";
				echo "<td>".$data[$i]['stockactual']."</td>";
				echo "<td>".$data[$i]['nomum']."</td>";
				echo "<td>".$data[$i]['nomemp']."</td>";
			echo '<tr>';
		}
	}			
}

?>
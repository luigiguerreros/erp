<?php 
	class InventarioController extends ApplicationGeneral{
		function productoInventario(){
			$inventario=$this->AutoLoadModel('inventario');
			$bloques=$this->AutoLoadModel('bloques');
			$data['inventario']=$inventario->listado();
			$data['bloques']=$bloques->listado();

			$this->view->show('/inventario/productoInventario.phtml',$data);
		}
		function productoInventarioPart2(){
			$inventario=$this->AutoLoadModel('inventario');
			$bloques=$this->AutoLoadModel('bloques');
			$actor=$this->AutoLoadModel('actorrol');

			$data['inventario']=$inventario->listado();
			$data['bloques']=$bloques->listado();
			$filtro="ar.idrol!=30 and a.estado=1";
			$data['actor']=$actor->actoresxfiltro($filtro);
			$data['auxiliar']=$actor->actoresxRol(30);

			$this->view->show('/inventario/productoInventarioPart2.phtml',$data);
		}
		function cargaBloque(){
			$detalleInventario=$this->AutoLoadModel('reporte');
			$idBloque=$_REQUEST['idBloque'];
			$idInventario=$_REQUEST['idInventario'];

			$data=$detalleInventario->reporteInventario($idInventario,$idBloque,"");
			$cantidadDetalle=count($data);
			$fila="";
			$fila.="<tr>";
			$fila.="<th>N°</th>";
			$fila.="<th>Codigo</th>";
			$fila.="<th>Descripcion</th>";
			$fila.="<th>Inventario</th>";
			$fila.="<th>Bloque</th>";
			$fila.="<th>Bueno</th>";
			$fila.="<th>Malo</th>";
			$fila.="<th>Servicio</th>";
			$fila.="<th>Show Room</th>";
			$fila.="<th>Total</th>";
			$fila.="<th>Acciones</th>";
			$fila.="</tr>";	
			for ($i=0; $i <$cantidadDetalle ; $i++) { 
				$fila.="<tr>";
				$fila.="<td>".($i+1)."<input type='hidden' value='".$data[$i]['iddetalleinventario']."' class='id'><input type='hidden' value='".$data[$i]['idproducto']."' class='idProducto'></td>";
				$fila.="<td>".$data[$i]['codigopa']."</td>";
				$fila.="<td>".$data[$i]['nompro']."</td>";
				$fila.="<td>".$data[$i]['codigoinv']."</td>";
				$fila.="<td>".$data[$i]['codigo']."</td>";
				$readonly="";
				$style="";
				$css="";
				if (!empty($data[$i]['usuariograbacion'])) {
					$readonly="readonly";
					$css="style='border:none;background:silver;'";
					$style="style='display:none'";
				}
				$fila.="<td><input ".$css." type='text' size='4px' class='buenos numeric' value=".$data[$i]['buenos']."  ".$readonly."></td>";
				$fila.="<td><input type='text' size='4px' class='malos numeric' value=".$data[$i]['malos']." style='border:none' readonly='readonly'></td>";
				$fila.="<td><input type='text' size='4px' class='servicio numeric' value=".$data[$i]['servicio']." style='border:none' readonly='readonly'></td>";
				$fila.="<td><input type='text' size='4px' class='showroom numeric' value=".$data[$i]['showroom']." style='border:none' readonly='readonly'></td>";
				$fila.="<td><input type='text' size='4px' class='total numeric' value=".($data[$i]['buenos']+$data[$i]['malos']+$data[$i]['servicio']+$data[$i]['showroom'])." style='border:none' readonly='readonly'></td>";
				$fila.="<td><a href='#' ".$style." class='btnGrabar' title='Grabar'><img style='display:block;margin:auto' src='/imagenes/grabar.gif' width='25' height='25' ></a></td>";
				$fila.="</tr>";	
			}
			echo $fila;

		}

		function editarInventario(){
			$inventario=$this->AutoLoadModel('inventario');

			$data['inventario']=$inventario->listado();

			$this->view->show('/inventario/editarInventario.phtml',$data);
		}
	}

 ?>
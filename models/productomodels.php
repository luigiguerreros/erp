<?php
	class Producto extends Applicationbase{
		private $tabla="wc_producto";
		private $tabla2="wc_producto,wc_linea";
		function listadoProductos(){
			$producto=$this->leeRegistro($this->tabla,"","estado=1","","");
			return $producto;
		}
		function listadoProductosTotal(){
			$producto=$this->leeRegistro($this->tabla,"","","","");
			return $producto;
		}

		function listaProductosPaginado($pagina){
			$data=$this->leeRegistroPaginado(
				$this->tabla,
				"",
				"estado=1",
				"",$pagina);
			return $data;
		}
		function listaProductosPaginadoxnombre($pagina,$condicion=""){
			$condicion=($condicion!="")?htmlentities($condicion,ENT_QUOTES,'UTF-8'):"";
			$data=$this->leeRegistroPaginado(
				$this->tabla,
				"",
				"(nompro like '%$condicion%') or (codigopa like '$condicion%')  and estado=1  ",
				"",$pagina);
			return $data;
		}	
		function paginadoProductosxnombre($condicion=""){
			$condicion=($condicion!="")?htmlentities($condicion,ENT_QUOTES,'UTF-8'):"";
			return $this->paginado($this->tabla,"","nompro like '$condicion%' or codigopa like '$condicion%'  and estado=1");
		}
		public function BuscarRegistrosxnombre($data){
			$data=htmlentities($data,ENT_QUOTES,'UTF-8');
			$condicion="(nompro like '%$data%') or 
			(codigopa like '%$data%')  and 
			estado=1";
			$data=$this->leeRegistro($this->tabla,"",$condicion, "","");
			return $data;
		}
		function paginadoProducto(){
			return $this->paginado($this->tabla,"","estado=1");
		}

		function listadoProductos2($nombre){
			$nombre=htmlentities($nombre,ENT_QUOTES,'UTF-8');
			$condicion = "";
			if(!empty($nombre)){$condicion=" and nompro like '%$nombre%'";}
			$producto=$this->leeRegistro($this->tabla,"nompro,stockactual,stockdisponible","estado=1 $condicion","","");
			return $producto;
		}
		function listaPrecio($idLinea,$idSubLinea){
			$condicion="";
			if(!empty($idLinea)){$condicion="idpadre=$idLinea";}
			if(!empty($idSubLinea)){$condicion="t2.idlinea=$idSubLinea";}
			$producto=$this->leeRegistro2($this->tabla2,"","$condicion","");
			return $producto;
		}
		function inventario($idAlmacen,$idLinea,$idSubLinea,$idProducto){
			$condicion="";
			if(!empty($idAlmacen)){$condicion="idalmacen=$idAlmacen";}
			if(!empty($idLinea)){$condicion="idpadre=$idLinea";}
			if(!empty($idSubLinea)){$condicion="t1.idlinea=$idSubLinea";}
			if(!empty($idProducto)){$condicion="idproducto=$idProducto";}
			$producto=$this->leeRegistro2($this->tabla2,
				"idproducto,imagen,codigopa,nompro,preciolista,stockactual,'0' as stockporllegar,'0' as stockpordespachar,unidadmedida,empaque","$condicion","");
			return $producto;
		}
		function grabaProducto($data){
			$exito=$this->grabaRegistro($this->tabla,$data);

			return  $exito;
		}
		function buscaProducto($idProducto){
			$producto=$this->leeRegistro($this->tabla,"","idproducto='$idProducto'","");
			return $producto;
		}

		function buscaProductoxId($idproducto){
			$sql="Select p.precioreferencia01,p.precioreferencia02,p.precioreferencia03,p.idproducto,p.codigopa,p.nompro,p.codigop,p.preciocosto,p.preciolista,p.stockactual,p.stockdisponible,p.idmarca
				From wc_producto p 
				
				Where  idproducto=".$idproducto;
			return $this->EjecutaConsulta($sql);
			 
		}

		function buscaxcodigo($codProducto){
			$codProducto=htmlentities($codProducto,ENT_QUOTES,'UTF-8');
			$producto=$this->leeRegistro($this->tabla,"",'codigopa="'.$codProducto.'"',"");
			return $producto;
		}
		function buscaProductoOrdenCompra($idProducto){
			$producto=$this->leeRegistro($this->tabla,"","idproducto=$idProducto","","");
			return $producto;
		}
		function buscaProductoAutocomplete($texIni,$idLinea=""){
			$texIni=htmlentities($texIni,ENT_QUOTES,'UTF-8');
			//(Stock: ".$valor['stockdisponible']." ) esto se quito al titulo de control por mientras
			$condicion="estado=1 and (codigopa LIKE '$texIni%') and preciolista!=0 and preciocosto!=0 ";
			if(!empty($idLinea)){$condicion.="AND idlineapadre=$idLinea";}
			$producto=$this->leeRegistro($this->tabla,"codigopa,nompro,idproducto,stockactual,stockdisponible,actualizado","$condicion","codigopa","limit 0,15");
			foreach($producto as $valor){
				$mensaje_SxV=" =======>>> STOCK POR VERIFICAR <========";
				$mensaje_SV="Disponible:".$valor['stockdisponible']." - Real:".$valor['stockactual'];
				$titulocontrol=(html_entity_decode($valor['nompro'],ENT_QUOTES,'UTF-8'));
				$titulolista=(html_entity_decode($valor['codigopa'],ENT_QUOTES,'UTF-8'))." ".(html_entity_decode($valor['nompro'],ENT_QUOTES,'UTF-8'))." ";
				if($valor['actualizado']==1){
					$titulolista.=$mensaje_SV;
				}else{
					$titulolista.=$mensaje_SxV;
				}
				//$dato[]=array("value"=>$valor['codigopa'],"label"=>$valor['codigopa']." ".$valor['nompro'],"id"=>$valor['idproducto']);
				$dato[]=array("value"=>(html_entity_decode($valor['codigopa'],ENT_QUOTES,'UTF-8')),"label"=>$titulolista,"id"=>$valor['idproducto'],"tituloProducto"=>$titulocontrol);
			}
			return $dato;
		}

		function buscaProductoAutocompleteLimpio($texIni,$idLinea=""){
			$texIni=htmlentities($texIni,ENT_QUOTES,'UTF-8');
			$condicion="p.estado=1 and (p.codigopa LIKE '$texIni%') and p.preciolista!=0 and p.preciocosto!=0 ";
			if(!empty($idLinea)){$condicion.="AND idlineapadre=$idLinea";}
			$producto=$this->leeRegistro("wc_producto as p left join wc_unidadmedida as u on p.unidadmedida=u.idunidadmedida","p.unidadmedida,p.codigopa,p.nompro,p.idproducto,p.stockactual,p.stockdisponible,p.imagen,p.idalmacen,u.cod_sunat","$condicion","","limit 0,15");
			foreach($producto as $valor){
				$titulocontrol=(html_entity_decode($valor['nompro'],ENT_QUOTES,'UTF-8'));
				$titulolista=(html_entity_decode($valor['codigopa'],ENT_QUOTES,'UTF-8'))." ".(html_entity_decode($valor['nompro'],ENT_QUOTES,'UTF-8'))."";
				$imagen=$valor['imagen'];
				
				$dato[]=array("value"=>(html_entity_decode($valor['codigopa'],ENT_QUOTES,'UTF-8')),"unidad"=>$valor['unidadmedida'],"almacen"=>$valor['idalmacen'],"label"=>$titulolista,"id"=>$valor['idproducto'],"tituloProducto"=>$titulocontrol,"imagen"=>$imagen,"cod_sunat"=>$valor['cod_sunat']);
			}
			return $dato;
		}
                function buscarxID($ID,$idLinea=""){
			
			$condicion="p.estado=1 and p.idproducto=$ID and p.preciolista!=0 and p.preciocosto!=0 ";
			if(!empty($idLinea)){$condicion.="AND idlineapadre=$idLinea";}
			$producto=$this->leeRegistro("wc_producto as p left join wc_unidadmedida as u on p.unidadmedida=u.idunidadmedida","p.unidadmedida,p.codigopa,p.nompro,p.idproducto,p.stockactual,p.stockdisponible,p.imagen,p.idalmacen,u.cod_sunat","$condicion","","");
			
			return $producto;
		}
		function buscaProductoAutocompleteCompras($texIni,$idLinea=""){
			$texIni=htmlentities($texIni,ENT_QUOTES,'UTF-8');
			$condicion="estado=1 and (codigopa LIKE '$texIni%')";
			if(!empty($idLinea)){$condicion.="AND idlineapadre=$idLinea";}
			$producto=$this->leeRegistro($this->tabla,"unidadmedida,codigopa,nompro,idproducto,stockactual,stockdisponible,imagen,idalmacen","$condicion","","limit 0,15");
			foreach($producto as $valor){
				$titulocontrol=(html_entity_decode($valor['nompro'],ENT_QUOTES,'UTF-8'));
				$titulolista=(html_entity_decode($valor['codigopa'],ENT_QUOTES,'UTF-8'))." ".(html_entity_decode($valor['nompro'],ENT_QUOTES,'UTF-8'))."";
				$imagen=$valor['imagen'];
				
				$dato[]=array("value"=>(html_entity_decode($valor['codigopa'],ENT_QUOTES,'UTF-8')),"unidad"=>$valor['unidadmedida'],"almacen"=>$valor['idalmacen'],"label"=>$titulolista,"id"=>$valor['idproducto'],"tituloProducto"=>$titulocontrol,"imagen"=>$imagen);
			}
			return $dato;
		}		
		function contarProducto($codProducto=""){
			$codProducto=htmlentities($codProducto,ENT_QUOTES,'UTF-8');
			$condicion="estado=1";
			if(!empty($codProducto)){$condicion=" AND codigopa='$codProducto";}
			$cantidad=$this->contarRegistro($this->tabla,"$condicion");
			return $cantidad;
		}
		function actualizaProducto($data,$idProducto){
			$exito=$this->actualizaRegistro($this->tabla,$data,"idproducto=$idProducto");
			return $exito;
		}

		function actualizaProductoxCodigo($data,$codigo){
			$exito=$this->actualizaRegistro($this->tabla,$data,"codigopa='".htmlentities($codigo,ENT_QUOTES,'UTF-8')."'");

			return $exito;
		}

		function eliminaProducto($idProducto){
			$exito=$this->cambiaEstado($this->tabla,"idproducto=$idProducto");
			return $exito;
		}
		function existeProducto($codigoProducto){
			$data=$this->leeRegistro($this->tabla,"idproducto",'idproducto="'.htmlentities($codigoProducto,ENT_QUOTES,'UTF-8').'"',"");
			if(count($data)>=1){
				return 1;
			}else{
				return 0;
			}
		}
		function paginacion($tamanio,$condicion=""){
			$condicion=($condicion!="")?$condicion.=" and":"";
			$data=$this->leeRegistro($this->tabla,"idalmacen","$condicion estado=1","","");
			$paginas=ceil(count($data)/$tamanio);
			return $paginas;
		}
		function buscarxnombre($inicio,$tamanio,$nombre){
			$nombre=htmlentities($nombre,ENT_QUOTES,'UTF-8');
			$inicio=($inicio-1)*$tamanio;
			if($inicio<0){
				$inicio=0;
			}
			$data=$this->leeRegistro($this->tabla,"","concat(codigopa,' ',nompro) like '%$nombre%' and estado=1","","limit $inicio,$tamanio");
			return $data;
		}
		function autocomplete($tex){
			$text=htmlentities($text,ENT_QUOTES,'UTF-8');
			$datos=$this->leeRegistro($this->tabla,"codigopa,idproducto","concat(codigopa,' ',nompro) LIKE '%$tex%'","","limit 0,15");
			foreach($datos as $valor){
				$dato[]=array("value"=>(html_entity_decode($valor['codigopa'],ENT_QUOTES,'UTF-8')),"label"=>(html_entity_decode($valor['codigopa'],ENT_QUOTES,'UTF-8')),"id"=>$valor['idproducto']);
			}
			return $dato;
		}

		public function GeneraCodigo($id){
			$maxcodigo=$this->leeRegistro($this->tabla,"max(idproducto)","","","");
			$data['codigop'] ='PDR'.str_pad($maxcodigo[0]['max(idproducto)'] ,5,'0',STR_PAD_LEFT);	
			$this->actualizaRegistro($this->tabla,$data,"idproducto=".$maxcodigo[0]['max(idproducto)']);		
			return $data;
		}

		public function ValorizadoxLinea()
		{
			$sql="select lin.nomlin,sum(prd.cifventasdolares*prd.stockactual) as valorizado
					from wc_producto prd
					inner join wc_linea slin On prd.idlinea=slin.idlinea
					Inner Join wc_linea lin On slin.idpadre=lin.idlinea
					group by slin.idpadre";
			$data=$this->EjecutaConsulta($sql);
			return $data;
		}
	}
?>
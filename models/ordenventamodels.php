<?php
Class OrdenVenta extends Applicationbase{
	
	private $_table="wc_ordenventa";
	private $tabla="wc_ordenventa";
	private $tablaCL="wc_condicionletra";
	private $tabla2="wc_ordenventa as t1,wc_clientetransporte as t2,wc_cliente as t3,wc_ordenCobro as t4, 
		(select idordencobro,date_format(max(fvencimiento), '%d/%m/%X')as fvencimiento,format(sum(importedoc),2) as 
		importedoc from wc_detalleordencobro where situacion=0 group by idordencobro) as t5,(select idcondicionletra,nombreletra from 
		wc_condicionletra) as t6,wc_actor as t7";
	private $tabla3="wc_clientetransporte ct,wc_ordenventa ov,wc_ordencobro oc";
	private $tabla4="wc_ordenventa as ov,wc_clientezona as cz,wc_cliente as c,wc_actor as a";
	private $tabla5="wc_ordenventa as ov,wc_clientezona as cz,wc_zona as z,wc_cliente as c";
	private $tabla6="wc_ordenventa,wc_clientezona,wc_cliente";
	private $tabla7="wc_ordenventa as t1,wc_detalleordenventa as t2,wc_producto as t3,wc_linea as t4";
	private $tablas="wc_ordencobro oc,wc_detalleordencobro doc";
	private $tabla8="wc_ordenventa ov,wc_ordencobro oc";
	private $_tableCliente="wc_cliente";
	private $_tableClienteTransporte="wc_clientetransporte";
	private $_tableTransporte="wc_transporte";
	private $_tableVendedor="wc_actor";
	private $_tableOrdenVenta="wc_ordenventa";
	private $_tableMoneda="wc_moneda";

	function listadoOrdenVenta($idclientetransporte=""){
		$filtro=($idclientetransporte!="")?"idclientetransporte=$idclientetransporte and ":"";
		$data=$this->leeRegistro($this->tabla,"","$filtro estado=1","");
		return $data;
	}
	function listado(){
		$data=$this->leeRegistro($this->tabla,"","","","");
		return $data;
	}
	function listadoAprobados(){
		$data=$this->leeRegistro($this->tabla,"","vbcreditos=1","","");
		return $data;
	}
	function listadoOrdenVentaNoRegistrado(){
		$ordenCompra=$this->leeRegistro($this->tabla,"","tipoorden=2 and registrado='0'","");
		return $ordenCompra;
	}
	function listadoReporteVentas($idLinea="", $idVendedor= "",$fechaInicio,$fechaFinal){
		$condicion="and fordenventa between '$fechaInicio' and '$fechaFinal'";
		if(!empty($idLinea)){
			$this->tabla2.=",wc_vendedorlinea as t9";
			$condicion.=" and t9.idlinea=$idLinea and t9.idvendedor=t1.idvendedor";
		}
		$this->tabla2.=",wc_actor as t8";
		$vendedor="concat(t8.nombres,' ',t8.apellidopaterno,' ',t8.apellidomaterno) as vendedor,";
		if(!empty($idVendedor)){
			$condicion.="and t1.idvendedor=$idVendedor";
		}
		$data = $this->leeRegistro($this->tabla2,"$vendedor t1.idordenventa,t4.idcondicionletra,date_format(fordenventa, '%d/%m/%X') as fordenventa,
				codigov,razonsocial,format(importeordencobro,2) as importeordencobro,
				importedoc,concat(if(escontado=1,'Contado ',''),if(escredito=1,'Credito ',''),
				if(esletras=1,concat('Letra ',(select nombreletra from wc_condicionletra as t11 where 
				t11.idcondicionletra=t4.idcondicionletra)),'')) as condicion,fvencimiento",
				"t1.idclientetransporte=t2.idclientetransporte and t2.idcliente=t3.idcliente and 
				t1.idordenventa=t4.idordenventa and t4.idordencobro=t5.idordencobro and t1.idvendedor=t8.idactor $condicion","","group by t1.idordenventa");
		return $data;

	}
	function listarxvendedor($idVendedor){
		$data=$this->leeRegistro($this->tabla5,"","ov.idclientezona=cz.idclientezona and ".
			"cz.idzona=z.idzona and cz.idcliente=c.idcliente and ov.idvendedor=".$idVendedor,"");
		return $data; 
	}
	function listarxvendedor2($filtro,$fecha,$fechaInicio,$fechaFinal){
		$condicion="";
		if($filtro==2){$condicion="and ov.vbventas=1 and ov.vbcobranzas=1 and ov.vbcreditos=1";}
		if($filtro==3){$condicion="and ov.vbventas=2 or ov.vbcobranzas=2 or ov.vbcreditos=2";}
		if(!empty($fecha)){$condicion="and ov.fordenventa='$fecha'";}
		if(!empty($fechaInicio)){$condicion="and ov.fordenventa between '$fechaInicio' and '$fechaFinal'";}
		$data=$this->leeRegistro($this->tabla5,"","ov.idclientezona=cz.idclientezona and ".
			"cz.idzona=z.idzona and cz.idcliente=c.idcliente and ov.idvendedor=".$_SESSION['idactor']." $condicion","");
		return $data; 
	}
	function listarEmisionLetras(){
		$data=$this->leeRegistro($this->tabla8,"","ov.idordenventa=oc.idordenventa and oc.esletras=1","","");
		return $data;
	}
	function pedidoxaprobar($filtro=1){
		$condicion="";
		switch($filtro){
			case 1:
				$condicion="vbventas=-1";
				break;
			case 2:
				$condicion="vbventas=1 and vbcobranzas=-1";
				break;
			case 4:
				$condicion="vbcobranzas=1 and vbalmacen=-1";
				break;			
			case 3:
				$condicion="vbalmacen=1 and vbcreditos=-1";
				break;
			case 5:
				$condicion="vbcreditos=1 and esdespachado=0 and esfacturado=0";
				break;	
		}
		$data=$this->leeregistro($this->tabla4,"","ov.idclientezona=cz.idclientezona and ".
			"cz.idcliente=c.idcliente and ov.idvendedor=a.idactor and $condicion","formapagoov,fordenventa");
		return $data;
	}
	function grabar($data){
		$exito=$this->grabaRegistro($this->tabla,$data);
		return $exito;
	}
	function inventario($idAlmacen,$idLinea,$idSubLinea,$idProducto){
		$condicion="";
		if(!empty($idAlmacen)){$condicion="t3.idalmacen=$idAlmacen";}
		if(!empty($idLinea)){$condicion="idpadre=$idLinea";}
		if(!empty($idSubLinea)){$condicion="t3.idlinea=$idSubLinea";}
		if(!empty($idProducto)){$condicion="t2.idproducto=$idProducto";}
		if(!empty($condicion)){$condicion.=" and";}
		$producto=$this->leeRegistro($this->tabla7,
			"t2.idproducto,sum(cantaprobada) as cantaprobada",
			"$condicion vbcreditos=1 and t1.idordenventa=t2.idordenventa and t2.idproducto=t3.idproducto and t3.idlinea=t4.idlinea",
			"","group by t2.idproducto");
		return $producto;
	}
	function buscarxid($id){
		$data=$this->leeRegistro3($this->tabla6,"","idordenventa=".$id." and esfacturado<>1","");
		return $data;
	}
	function buscarxidguia($id){
		$data=$this->leeRegistro3($this->tabla6,"","idordenventa=".$id." and guiaremision<>1","");
		return $data;
	}
	function buscarxidFacturado($id){
		$data=$this->leeRegistro3($this->tabla6,"","t1.`idordenventa`=".$id." and t1.`esfacturado`=1","");
		return $data;
	}
	function buscarxidCliente($idcliente){
		$data=$this->leeRegistro($this->_table,"","idcliente=".$idcliente." and esguiado=1 and situacion!='anulado' and estado=1","");
		return $data;
	}
	function buscarxidClienteFiltro($idcliente,$idordenventa){
		$data=$this->leeRegistro($this->_table,"","idcliente='$idcliente' and esguiado=1 and estado=1 and situacion!='cancelado' and situacion!='anulado' and idordenventa!='$idordenventa' ","");
		return $data;
	}
	function buscarxid2($id){
		$data=$this->leeRegistro($this->tabla,"","idorden=".$id,"","");
		return $data;
	}
	function buscarOrdenVentaxId($idordenventa){
		$sql="Select OV.*,MN.Simbolo From ".$this->tabla." OV 
				INNER JOIN ".$this->_tableMoneda." MN On OV.IdMoneda=MN.IdMoneda
				Where OV.IdOrdenVenta=".$idordenventa." AND OV.estado=1";
		return $this->EjecutaConsulta($sql);
		// $data=$this->leeRegistro($this->tabla,"","idordenventa='$idordenventa' and estado=1","","");
		// return $data;
	}

	function buscarOrdenVentaxDevoluciones($idordenventa){

		$data=$this->leeRegistro($this->tabla,"","idordenventa='$idordenventa' and estado=1","","");
		return $data;
	}	
	function buscarOrdenVentaxIdEdicion($idordenventa){
		$data=$this->leeRegistro(" wc_ordenventa ov 
						inner join wc_clientezona cz on ov.idclientezona=cz.idclientezona
						inner join wc_cliente c on c.idcliente=cz.idcliente
						inner join wc_actor a on a.idactor=ov.idvendedor
						left join wc_almacen al on ov.idalmacen=al.idalmacen
						",
						"",
						"ov.idordenventa='$idordenventa' and ov.estado=1 and vbventas!=1 and desaprobado!=1",
						"",
						"");
		return $data;
	}
	function buscarOrdenVentaxIdSinRestriccionAreas($idordenventa){
		$data=$this->leeRegistro(" wc_ordenventa ov
						inner join wc_clientezona cz on ov.idclientezona=cz.idclientezona
						inner join wc_cliente c on c.idcliente=cz.idcliente
						inner join wc_actor a on a.idactor=ov.idvendedor
						left join wc_almacen al on ov.idalmacen=al.idalmacen
						",
				"",
				"ov.idordenventa='$idordenventa' and ov.estado=1",
				"",
				"");
		return $data;
	}
	function buscarOrdenVAprobadoPorAlmacen($idordenventa){
		$data=$this->leeRegistro($this->tabla,"","idordenventa='$idordenventa' and estado=1 and vbalmacen=1","","");
		return $data;
	}
	function buscarEmisionLetra($idOrdenVenta){
		$data=$this->leeRegistro($this->tabla2,"","O.idcliente=A.idactor and idordenventa=".$idOrdenVenta,"","");
		return $data;
	}
	function buscaAutocomplete($codigoOrdenVenta){
		$cliente=$this->leeRegistro($this->tabla,"","esfacturado<>1 and esguiado=1 and codigov LIKE '%$codigoOrdenVenta%'","codigov limit 0,10");
		$modoFacturacion=$this->modoFacturacion();
		foreach($cliente as $valor){
			$dato[]=array("value"=>$valor['codigov'],
						"label"=>$valor['codigov'],
						"id"=>$valor['idordenventa'],
						);
		}
		return $dato;
	}
	function buscaAutocompleteGuiaRemision($codigoOrdenVenta){
		$cliente=$this->leeRegistro($this->tabla,"","esanulado<>1 and guiaremision<>1 and esguiado=1 and codigov LIKE '%$codigoOrdenVenta%'","codigov limit 0,10");
		$modoFacturacion=$this->modoFacturacion();
		foreach($cliente as $valor){
			$dato[]=array("value"=>$valor['codigov'],
						"label"=>$valor['codigov'],
						"id"=>$valor['idordenventa'],
						);
		}
		return $dato;
	}
	function autocompleteParaLetras($id){
		$data=$this->leeRegistro2($this->tabla3,"","ndocumento LIKE '$id%'","","limit 0,15");
		$condicionLetra=$this->condicionLetra();
		$tipoLetra=$this->tipoLetra();
		foreach($data as $valor){
			//$arrayCondicionLetra=explode("/",$condicionLetra[($valor['condicionletra'])]);
			$dato[]=array("value"=>$valor['ndocumento'],
						"label"=>$valor['ndocumento'],
						"id"=>$valor['idorden'],
						/*"condicionletra"=>$condicionLetra[($valor['condicionletra'])],
						"tipoletra"=>$tipoLetra[($valor['tipoletra'])],
						"cantidadletras"=>count($arrayCondicionLetra)*/
						);
		}
		return $dato;
	}
	function eliminaOrdenVenta($idOrdenVenta){
		$exito=$this->cambiaEstado($this->tabla,"idordenventa=$idOrdenVenta");
		return $exito;
	}
	function actualizaOrdenVenta($data,$idOrdenVenta){
		$exito=$this->actualizaRegistro($this->tabla,$data,"idordenventa=$idOrdenVenta");
		return $exito;
	}
	function buscaOrdenVenta($idOrdenVenta){
		$data=$this->leeRegistro($this->tabla,"","idordenventa=$idOrdenVenta","");
		return $data;
	}



	function listadoOrdenVentaxidcliente($idcliente="",$inicio,$tamanio){
		$inicio=($inicio-1)*$tamanio;
		if($inicio<0){
			$inicio=0;
		}
		$filtro=($idcliente!='')?" ct.idcliente=$idcliente and ":'';
		$data=$this->leeRegistro($this->tabla3,"ov.idordenventa,ov.importeov,ov.codigov,oc.idcondicionletra",
			"$filtro ct.idclientetransporte=ov.idclientetransporte and oc.idordenventa=ov.idordenventa and oc.estado=1 and ct.estado=1 and ov.estado=1","","limit $inicio,$tamanio");
		return $data;
	}
	function listadoOrdenVentaxidcliente2($idcliente=""){
		$filtro=($idcliente!='')?" ct.idcliente=$idcliente and ":'';
		$data=$this->leeRegistro($this->tabla3,"ov.idordenventa,ov.importeov,ov.codigov,oc.idcondicionletra",
			"$filtro ct.idclientetransporte=ov.idclientetransporte and oc.idordenventa=ov.idordenventa and oc.estado=1 and ct.estado=1 and ov.estado=1","","");
		return $data;
	}
	function cuentasxidordenventa($idordenventa){
		$data=$this->leeRegistro($this->tablas,"oc.idordenventa,doc.formacobro,doc.importedoc,doc.situacion,doc.fvencimiento","doc.estado=1 and oc.estado=1 
			and oc.idordenventa=$idordenventa and doc.idordencobro=oc.idordencobro","");
		return $data;
	}
	function Paginacion($tamanio,$condicion=""){
		$data=$this->leeRegistro($this->tabla,"","$condicion","","");
		$paginas=intval((count($data)/$tamanio))+1;
		return $paginas;
	}
	function generaCodigo(){
		$data=$this->leeRegistro($this->tabla,"CONCAT( 'OV-',DATE_FORMAT( NOW( ) ,  '%y' ) , LPAD(  (MAX(SUBSTRING(`codigov`,6,6))+1) , 6,  '0' ) )  as codigo","year(`fechacreacion`)=year(now())","");
		if ($data[0]['codigo']!="") {
				return $data[0]['codigo'];
			}else{
				return "OV-".date('y').str_pad(1,6,'0',STR_PAD_LEFT);
		}
	}
	function paginacionov($tamanio,$condicion=''){
		$data=$this->leeRegistro($this->tabla3,"distinct ov.idordenventa","$condicion","","");
		$paginas=intval((count($data)/$tamanio))+1;
		return $paginas;
	}

	function ultimafechaxidordenventa($idordenventa){
		$data=$this->leeRegistro($this->tablas,"doc.fvencimiento",
			"oc.idordenventa=$idordenventa and doc.idordencobro=oc.idordencobro and doc.estado=1 and oc.estado=1","doc.fvencimiento desc");
		return $data[0][0];
	}

	function generaCodigoLetra($codigov){
		$data=$this->leeRegistro("wc_detalleordencobro","MAX(iddetalleordencobro) AS codigo","","");
		$codigo=substr($codigov,3);
		if($data[0]['codigo']==0){
			$codigo.="000001";
		}else{
			$valor="00000".($data[0]['codigo']+1);
			$codigo.=substr($valor,strlen($valor)-6,6);
		}
		return $codigo;
	}
	function saldoxidordenventa($idordenventa){
		$data=$this->leeRegistro($this->tablas,"sum(doc.importedoc) as suma","oc.idordenventa=$idordenventa and doc.idordencobro=oc.idordencobro and oc.estado=1 and doc.estado=1 and doc.situacion=0","");
		return $data[0][0];
	}
	function condicionesletra(){
		$data=$this->leeRegistro($this->tablaCL,"idcondicionletra,nombreletra,cantidadletra","estado=1","nombreletra asc");
		return $data;
	}
	function nombreLetraxId($id){
		$data=$this->leeregistro($this->tablaCL,"nombreletra","idcondicionletra=$id","");
		return $data[0][0];
	}

	function buscarOrdenxParametro($filtro){
		$data=$this->leeregistro($this->tabla,"",$filtro,"");
		return $data;
	}

	function buscarUltimaOrden($idCliente){
		$data=$this->leeregistro($this->tabla,"","idcliente='$idCliente'","","order by idordenventa desc limit 0,1");
		return $data;
	}

	function buscarOrdenComision($idvendedor="",$fechaInicio="",$fechaFinal=""){
		$condicion=" wc_ordenventa.`escomisionado`=0 and wc_ordenventa.`estado`=1 and wc_ordenventa.`situacion`='cancelado' and wc_ordenventa.`esanulado`=0 ";
		$condicion.=!empty($idvendedor)?" and wc_ordenventa.`idvendedor`='$idvendedor' ":"";
		$condicion.=!empty($fechaInicio)?" and wc_ordenventa.`fechaCancelado`>='$fechaInicio' ":"";
		$condicion.=!empty($fechaFinal)?" and wc_ordenventa.`fechaCancelado`<='$fechaFinal' ":"";
		$data=$this->leeregistro(
				"`wc_ordenventa` wc_ordenventa 
				inner join `wc_cliente` wc_cliente on wc_ordenventa.`idcliente`=wc_cliente.`idcliente`
				inner join `wc_moneda` wc_moneda on wc_ordenventa.`IdMoneda`=wc_moneda.`idmoneda`
				",
				"",
				$condicion,
				""
				);
		return $data;
	}
	function buscarOrdenComisionPagada($idvendedor="",$fechacomision="",$fechaInicio="",$fechaFinal=""){
		$condicion=" wc_ordenventa.`escomisionado`=1 and wc_ordenventa.`estado`=1 and wc_ordenventa.`situacion`='cancelado' and wc_ordenventa.`esanulado`=0 ";
		$condicion.=!empty($idvendedor)?" and wc_ordenventa.`idvendedor`='$idvendedor' ":"";
		$condicion.=!empty($fechacomision)?" and wc_ordenventa.`fcomision`='$fechacomision' ":"";
		$condicion.=!empty($fechaInicio)?" and wc_ordenventa.`fechaCancelado`>='$fechaInicio' ":"";
		$condicion.=!empty($fechaFinal)?" and wc_ordenventa.`fechaCancelado`<='$fechaFinal' ":"";
		$data=$this->leeregistro(
				"`wc_ordenventa` wc_ordenventa inner join `wc_cliente` wc_cliente on wc_ordenventa.`idcliente`=wc_cliente.`idcliente`
				
				",
				"",
				$condicion,
				""
				);
		return $data;
	}

	function listaFechaComision($idvendedor=""){
		$condicion="esguiado=1 and esanulado=0 and escomisionado=1 ";
		$condicion.=" and idvendedor='$idvendedor'";
		$data=$this->leeRegistro($this->tabla,"distinct fcomision",$condicion,"fcomision desc","");
		return $data;
	}
	

	function listaOrdenesGeneral(){

		return $data=$this->leeRegistro($this->_table,"","","fordenventa","");
	}

	function buscaOrdenxPagar($codigoOrdenVenta){
		$cliente=$this->leeRegistro($this->tabla,"","vbalmacen=1 and esdespachado=1 and codigov LIKE '%$codigoOrdenVenta%'","","limit 0,10");
		$modoFacturacion=$this->modoFacturacion();
		foreach($cliente as $valor){
			$dato[]=array("value"=>$valor['codigov'],
						"label"=>$valor['codigov'],
						"id"=>$valor['idordenventa'],
						);
		}
		return $dato;
	}

	function buscaOrdenxPagarEstadoLetra($codigoOrdenVenta){
		$cliente=$this->leeRegistro($this->tabla,"","esguiado=1 and codigov LIKE '%$codigoOrdenVenta%'","","limit 0,10");
		$modoFacturacion=$this->modoFacturacion();
		foreach($cliente as $valor){
			$dato[]=array("value"=>$valor['codigov'],
						"label"=>$valor['codigov'],
						"id"=>$valor['idordenventa'],
						);
		}
		return $dato;
	}

	function buscaOrdenxPagar2($codigoOrdenVenta){
		$cliente=$this->leeRegistro("`wc_ordenventa` ov inner join `wc_ordencobro` oc on ov.`idordenventa`=oc.`idordenventa`","","ov.`esguiado`=1  and  ov.`codigov` LIKE '%$codigoOrdenVenta%' ","","group by ov.`codigov` limit 0,10");
		$modoFacturacion=$this->modoFacturacion();
		foreach($cliente as $valor){
			$dato[]=array("value"=>$valor['codigov'],
						"label"=>$valor['codigov'],
						"id"=>$valor['idordenventa'],
						);
		}
		return $dato;
	}

	function buscaOrdenxPagar3($codigoOrdenVenta){
		$cliente=$this->leeRegistro("`wc_ordenventa` ov inner join `wc_ordencobro` oc on ov.`idordenventa`=oc.`idordenventa` ","","ov.`esguiado`=1 and oc.`esletras`=1 and  ov.`codigov` LIKE '%$codigoOrdenVenta%'  ","","group by ov.`codigov` limit 0,10");
		$modoFacturacion=$this->modoFacturacion();
		foreach($cliente as $valor){
			$dato[]=array("value"=>$valor['codigov'],
						"label"=>$valor['codigov'],
						"id"=>$valor['idordenventa'],
						);
		}
		return $dato;
	}
	function buscaOrdenVentaDespacho($codigoOrdenVenta){
		$cliente=$this->leeRegistro("`wc_ordenventa` ov inner join `wc_ordencobro` oc on ov.`idordenventa`=oc.`idordenventa` ","","ov.`esdespachado`=1  and  ov.`codigov` LIKE '%$codigoOrdenVenta%'  ","","group by ov.`codigov` limit 0,10");
		$modoFacturacion=$this->modoFacturacion();
		foreach($cliente as $valor){
			$dato[]=array("value"=>$valor['codigov'],
						"label"=>$valor['codigov'],
						"id"=>$valor['idordenventa'],
						);
		}
		return $dato;
	}
	function buscaOrdenVentaCompleto($codigoOrdenVenta){
		$cliente=$this->leeRegistro("`wc_ordenventa` ov  ","","ov.`estado`=1  and  ov.`codigov` LIKE '%$codigoOrdenVenta%'  ","","group by ov.`codigov` limit 0,10");
		$modoFacturacion=$this->modoFacturacion();
		foreach($cliente as $valor){
			$dato[]=array("value"=>$valor['codigov'],
						"label"=>$valor['codigov'],
						"id"=>$valor['idordenventa'],
						);
		}
		return $dato;
	}	
	function buscaOrdenxNumeroLetra($numeroletra){
		$cliente=$this->leeRegistro("`wc_ordenventa` ov inner join `wc_ordencobro` oc on ov.`idordenventa`=oc.`idordenventa` inner join `wc_detalleordencobro` doc on doc.`idordencobro`=oc.`idordencobro`",""," ov.`esguiado`=1 and doc.`numeroletra` LIKE '%$numeroletra%'  ","","group by ov.`codigov` limit 0,10");
		$modoFacturacion=$this->modoFacturacion();
		foreach($cliente as $valor){
			$dato[]=array("value"=>$valor['numeroletra'],
						"label"=>$valor['numeroletra'],
						"id"=>$valor['idordenventa'],
						);
		}
		return $dato;
	}		
	function autocompleteCancelados($codigoOrdenVenta){
		$cliente=$this->leeRegistro("`wc_ordenventa` ov  ","","ov.`estado`=1 and situacion='cancelado' and  ov.`codigov` LIKE '%$codigoOrdenVenta%'  ","","group by ov.`codigov` limit 0,10");
		$modoFacturacion=$this->modoFacturacion();
		foreach($cliente as $valor){
			$dato[]=array("value"=>$valor['codigov'],
						"label"=>$valor['codigov'],
						"id"=>$valor['idordenventa'],
						);
		}
		return $dato;
	}

	function BuscarCampoOVxId($Id,$Campo){
		$data=$this->leeRegistro($this->_table,$Campo,"idordenventa=".$Id,"","");
		return $data[0][0];
	}

/********************************************************************************************************************************
 * Inicio de Ordenamiento del codigo.
 * Todas las funciones serán reordenadas segun su necesidad.
 * ******************************************************************************************************************************/

	/**
	 * Proceso		: Flujo básico de ventas
	 * Inicio		: Secretaria de ventas empieza el proceso, registrando la cabecera de la venta aprobada, 
	 * 				  sigue Facturación registrando los productos, luego pasa a cobranzas para su revisión , finalmente 
	 * 				  se envia a creditos para la aprobación, luego se envia a almacen para la validación y finalmente a
	 * 				  facturación para generar los documentos contables.
	 */
	
	/*
	Funcion que muestra todas las ordenes según el id del cliente
	*/
	function listaOrdenVentaxIdCliente($idcliente){
		$sql="Select 
				ov.idordenventa,
				ov.codigov,
				ov.fordenventa,
				ov.fechavencimiento,
				case ov.fechadespacho 
					when '0000-00-00' then '<b>POR DESPACHAR</b>'
				Else ov.fechadespacho 
				End as fechadespacho,
				ov.importeov,
				ov.IdTipoCambioVigente,
				ov.desaprobado,
				ov.importepagado,
				ov.importedevolucion,
				(ov.importeov-ov.importepagado-ov.importedevolucion) as saldoov,
				ov.situacion,
				CONCAT(ac.nombres,' ',ac.apellidopaterno,' ',ac.apellidomaterno) as vendedor ,fechaCancelado
			From wc_ordenventa ov
			Inner Join wc_actor ac On ov.idvendedor=ac.idactor
			Where ov.idcliente=".$idcliente." 
			Order By ov.codigov desc";
		return $this->EjecutaConsulta($sql);
		//return $this->leeRegistro($this->tabla,"","idcliente=".$idcliente." and estado=1","codigov desc","");
	}

	/**************************************************************
	Funcion que Permite extraer toda la informacion de una orden de
	venta.
	Datos del Cliente, Datos del transporte, Datos de Moneda, Monto
	Total, Vendedor, Zona del Cliente, Sucursales
	**************************************************************/
	function DataCompletaOrdenVentaxId($idordenventa){
		$sql="
		SELECT OV.idordenventa,OV.codigov as NroOrdenVenta,OV.idcliente,CLI.razonsocial,CLI.ruc,CLI.codantiguo as CodDakkar,CLI.codcliente,
		CLI.telefono,CLI.celular,
		OV.idvendedor,VND.codigoa,CONCAT(VND.nombres,' ',VND.apellidopaterno,' ',VND.apellidomaterno) as vendedor,
		OV.idclientetransporte,TRANS.trazonsocial,
		OV.idclientesucursal,OV.idalmacen,OV.fordenventa,OV.tipodoccli,OV.observaciones as SugerenciaCliente,
		CASE OV.vbventas WHEN '-1' THEN 'Pendiente' WHEN '1' THEN 'Aprobado' WHEN '2' THEN 'Desaprobado' END as VB_Ventas,
		IfNull(OV.mventas,'Sin observaciones') as msgVentas,
		CASE OV.vbcobranzas WHEN '-1' THEN 'Pendiente' WHEN '1' THEN 'Aprobado' WHEN '2' THEN 'Desaprobado' END as VB_Cobranzas,
		IfNull(OV.mcobranzas,'Sin observaciones') as msgCobranzas,
		CASE OV.vbalmacen WHEN '-1' THEN 'Pendiente' WHEN '1' THEN 'Aprobado' WHEN '2' THEN 'Desaprobado' END as VB_Almacen,
		IfNull(OV.malmacen,'Sin observaciones') as msgAlmacen,
		CASE OV.vbcreditos WHEN '-1' THEN 'Pendiente' WHEN '1' THEN 'Aprobado' WHEN '2' THEN 'Desaprobado' END as VB_Creditos,
		IfNull(OV.mcreditos,'Sin observaciones') as msgCreditos,
		OV.tiempoduracion as DuracionAcumulada,
		MN.Simbolo,MN.Nombre,OV.importeov as MontoOrdenVenta,OV.importeaprobado,OV.importepagado,OV.importedevolucion,
		OV.esguiado,OV.esdespachado,OV.fechadespacho,OV.esfacturado,OV.guiaremision,OV.esanulado,OV.fechaanulado,OV.usuarioanulacion,
		OV.situacion,OV.fechaCancelado,OV.fechavencimiento,OV.nrocajas,OV.nrobultos,OV.idtipocobranza,OV.iddespachador,OV.idverificador,
		OV.idverificador2,OV.direccion_envio,OV.direccion_despacho,OV.contacto,OV.avalorden,OV.desaprobado,
		OV.es_contado,OV.es_credito,OV.es_letras,OV.tipo_letra,OV.escomisionado,OV.porComision,OV.fcomision,OV.faprobado,OV.fdesaprobado,
		OV.estado,OV.usuariocreacion,OV.fechacreacion,OV.usuariomodificacion,OV.fechamodificacion
		FROM ".$this->_tableOrdenVenta." OV 
		INNER JOIN ".$this->_tableCliente." CLI On OV.idcliente=CLI.idcliente 
		INNER JOIN ".$this->_tableClienteTransporte." CTRNS On OV.idclientetransporte=CTRNS.idclientetransporte
		INNER JOIN ".$this->_tableTransporte." TRANS On CTRNS.idtransporte=TRANS.idtransporte
		INNER JOIN ".$this->_tableVendedor ." VND On OV.idvendedor=VND.idactor
		INNER JOIN ".$this->_tableMoneda." MN On OV.idmoneda=MN.idmoneda
		Where OV.idOrdenVenta=".$idordenventa." ";
		return $this->EjecutaConsulta($sql);
	}

}

?>

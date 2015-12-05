<?php
	class Documento extends Applicationbase{
		private $tabla="wc_documento";
		function grabaDocumento($data){
			$exito=$this->grabaRegistro($this->tabla,$data);
			return $exito;
		}
		function autocompletefactura($text){
			$text=htmlentities($text,ENT_QUOTES,'UTF-8');
			$datos=$this->leeRegistro($this->tabla,"numdoc,serie,iddocumento,idordenventa","nombredoc=1 and estado=1 and esAnulado!=1 and concat(serie,' ',numdoc) like '%".$text."%'","");
			$dato=array();
			foreach($datos as $valor){
				$dato[]=array("value"=>($valor['serie'].'-'.$valor['numdoc']),
							"label"=>$valor['serie'].'-'.$valor['numdoc'],
							"id"=>$valor['iddocumento'],
							"idorden"=>$valor['idordenventa']
							);
			}
			return $dato;
		}
		function buscaDocumento($iddocumento,$filtro){
			$condicion="estado=1";
			if(!empty($iddocumento) && !empty($filtro)){
				$condicion="$filtro and iddocumento='$iddocumento' and estado=1  ";
			}elseif(!empty($iddocumento) && empty($filtro)){
				$condicion="iddocumento='$iddocumento' and estado=1";
			}elseif (empty($iddocumento) && !empty($filtro)) {
				$condicion="$filtro  and estado=1  ";
			}
			$data=$this->leeRegistro($this->tabla,"",$condicion,"","");
			return $data;
		}
		function buscaNotaCredito($iddocumento){
			$condicion="estado=1";
			if(!empty($iddocumento)){
				$condicion="nombredoc=5 and concepto=1 and iddocumento='$iddocumento' and estado=1 and esAnulado!=1 ";
			
			}
			$data=$this->leeRegistro($this->tabla,"",$condicion,"","");
			return $data;
		}
		function actualizarDocumento($data,$filtro){
			$exito=$this->actualizaRegistro($this->tabla,$data,$filtro);
			return $exito;
		}
		function listaDocumentos($idordenventa,$nombredoc){
			$condicion="estado=1";
			if (!empty($idordenventa) && !empty($nombredoc)) {
				$condicion="idordenventa='$idordenventa' and estado=1 and nombredoc='$nombredoc'";
			}
			elseif(!empty($idordenventa)&& empty($nombredoc)){
				$condicion="idordenventa='$idordenventa' and estado=1";
			}
			elseif(empty($idordenventa)&& !empty($nombredoc)){
				$condicion="nombredoc='$nombredoc' and estado=1";
			}
			$data=$this->leeRegistro($this->tabla,"",$condicion,"","");
			return $data;
		}
		function listaDocumentosSinAnulados($idordenventa,$nombredoc){
			$condicion="estado=1 nd esAnulado!=1 ";
			if (!empty($idordenventa) && !empty($nombredoc)) {
				$condicion="idordenventa='$idordenventa' and esAnulado!=1 and estado=1 and nombredoc='$nombredoc'";
			}
			elseif(!empty($idordenventa)&& empty($nombredoc)){
				$condicion="idordenventa='$idordenventa' and estado=1 nd esAnulado!=1 ";
			}
			elseif(empty($idordenventa)&& !empty($nombredoc)){
				$condicion="nombredoc='$nombredoc' and estado=1 nd esAnulado!=1 ";
			}
			$data=$this->leeRegistro($this->tabla,"",$condicion,"","");
			return $data;
		}
		function listaDocumentos2($idordenventa,$nombredoc,$paraBusqueda=""){
			$condicion2="";
			if (!empty($paraBusqueda)) {
				$condicion2=" or wc_ordenventa.`codigov`='$paraBusqueda' or wc_documento.`numdoc`='$paraBusqueda' ";
			}

			$condicion="wc_documento.`estado`=1  and wc_ordenventa.`estado`=1";
			if (!empty($idordenventa) && !empty($nombredoc)) {
				$condicion="wc_documento.`idordenventa`='$idordenventa' and wc_documento.`estado`=1  and wc_ordenventa.`estado`=1 and wc_ordenventa.`nombredoc`='$nombredoc'";
			}elseif (!empty($idordenventa) && empty($nombredoc)) {
				$condicion="wc_documento.`idordenventa`='$idordenventa' and wc_documento.`estado`=1  and wc_ordenventa.`estado`=1";
			}
			elseif (empty($idordenventa) && !empty($nombredoc)) {
				$condicion="wc_documento.`estado`=1  and wc_ordenventa.`estado`=1 and wc_ordenventa.`nombredoc`='$nombredoc'";
			}
			$data=$this->leeRegistro(
				"`wc_ordenventa` wc_ordenventa 
     			INNER JOIN `wc_documento` wc_documento ON wc_ordenventa.`idordenventa` = wc_documento.`idordenventa`",
				"wc_documento.`serie`,
			    wc_documento.`numdoc`,
			    wc_documento.`iddocumento`,
			    wc_documento.`idordenVenta`,
			    wc_documento.`nombredoc`,
			    wc_documento.`fechadoc`,
			    wc_documento.`porcentajefactura`,
			    wc_documento.`montofacturado`,
			    wc_documento.`montoigv`,
			    wc_documento.`modofactura`,
			    wc_documento.`esImpreso`,
			    wc_ordenventa.`importeov`,
			    wc_ordenventa.`esfacturado`,
			    wc_ordenventa.`codigov`,
			    wc_ordenventa.`fordenventa`",
				$condicion+$condicion2,
				"",
				""
				);
			return $data;
		}
		function cuentaDocumentos($idordenventa,$nombredoc,$paraBusqueda=""){
			$condicion2="";
			if (!empty($paraBusqueda)) {
				$condicion2="and wc_ordenventa.`codigov`='$paraBusqueda' or wc_documento.`numdoc`='$paraBusqueda' ";
			}

			$condicion="wc_documento.`estado`=1  and wc_ordenventa.`estado`=1 ";
			if (!empty($idordenventa) && !empty($nombredoc)) {
				$condicion="wc_documento.`idordenventa`='$idordenventa' and wc_documento.`estado`=1  and wc_ordenventa.`estado`=1 and wc_ordenventa.`nombredoc`='$nombredoc' ";
			}elseif (!empty($idordenventa) && empty($nombredoc)) {
				$condicion="wc_documento.`idordenventa`='$idordenventa' and wc_documento.`estado`=1  and wc_ordenventa.`estado`=1 ";
			}
			elseif (empty($idordenventa) && !empty($nombredoc)) {
				$condicion="wc_documento.`estado`=1  and wc_ordenventa.`estado`=1 and wc_ordenventa.`nombredoc`='$nombredoc' ";
			}
			$data=$this->leeRegistro(
				"`wc_ordenventa` wc_ordenventa 
     			INNER JOIN `wc_documento` wc_documento ON wc_ordenventa.`idordenventa` = wc_documento.`idordenventa` ",
				"count(*)",
				$condicion.$condicion2,
				"",
				""
				);
			return $data[0]['count(*)'];
		}
		function listaDocumentosPaginado($idordenventa,$nombredoc,$pagina,$paraBusqueda=""){
			$condicion2="";
			if (!empty($paraBusqueda)) {
				$condicion2=" and wc_ordenventa.`codigov`='$paraBusqueda' or wc_documento.`numdoc`='$paraBusqueda' ";
			}

			$condicion="wc_documento.`estado`=1  and wc_ordenventa.`estado`=1";
			if (!empty($idordenventa) && !empty($nombredoc)) {
				$condicion="wc_documento.`idordenventa`='$idordenventa' and wc_documento.`estado`=1  and wc_ordenventa.`estado`=1 and wc_ordenventa.`nombredoc`='$nombredoc' ";
			}elseif (!empty($idordenventa) && empty($nombredoc)) {
				$condicion="wc_documento.`idordenventa`='$idordenventa' and wc_documento.`estado`=1  and wc_ordenventa.`estado`=1 ";
			}
			elseif (empty($idordenventa) && !empty($nombredoc)) {
				$condicion="wc_documento.`estado`=1  and wc_ordenventa.`estado`=1 and wc_ordenventa.`nombredoc`='$nombredoc' ";
			}
			$data=$this->leeRegistroPaginado(
				"`wc_ordenventa` wc_ordenventa 
     			INNER JOIN `wc_documento` wc_documento ON wc_ordenventa.`idordenventa` = wc_documento.`idordenventa`
     			INNER JOIN `wc_moneda` wc_moneda ON wc_ordenventa.`idmoneda`=wc_moneda.idmoneda",
				
				"wc_documento.`serie`,
			    wc_documento.`numdoc`,
			    wc_documento.`iddocumento`,
			    wc_documento.`idordenVenta`,
			    wc_documento.`nombredoc`,
			    wc_documento.`fechadoc`,
			    wc_documento.`porcentajefactura`,
			    wc_documento.`montofacturado`,
			    wc_documento.`montoigv`,
			    wc_documento.`modofactura`,
			    wc_documento.`esImpreso`,
			    wc_ordenventa.`importeov`,
			    wc_ordenventa.`esfacturado`,
			    wc_ordenventa.`codigov`,
			    wc_moneda.`simbolo`,			    
			    wc_ordenventa.`fordenventa`",
				$condicion.$condicion2,
				"wc_documento.`iddocumento` desc",$pagina);
			return $data;
		}

		function paginadoDocumentos($idordenventa,$nombredoc,$paraBusqueda=""){
			$condicion2="";
			if (!empty($paraBusqueda)) {
				$condicion2=" and wc_ordenventa.`codigov`='$paraBusqueda' or wc_documento.`numdoc`='$paraBusqueda' ";
			}

			$condicion="wc_documento.`estado`=1  and wc_ordenventa.`estado`=1";
			if (!empty($idordenventa) && !empty($nombredoc)) {
				$condicion="wc_documento.`idordenventa`='$idordenventa' and wc_documento.`estado`=1  and wc_ordenventa.`estado`=1 and wc_ordenventa.`nombredoc`='$nombredoc' ";
			}elseif (!empty($idordenventa) && empty($nombredoc)) {
				$condicion="wc_documento.`idordenventa`='$idordenventa' and wc_documento.`estado`=1  and wc_ordenventa.`estado`=1 ";
			}
			elseif (empty($idordenventa) && !empty($nombredoc)) {
				$condicion="wc_documento.`estado`=1  and wc_ordenventa.`estado`=1 and wc_ordenventa.`nombredoc`='$nombredoc' ";
			}

			return $this->paginado(
				"`wc_ordenventa` wc_ordenventa 
     			INNER JOIN `wc_documento` wc_documento ON wc_ordenventa.`idordenventa` = wc_documento.`idordenventa` ",
				
				"wc_documento.`serie`,
			    wc_documento.`numdoc`,
			    wc_documento.`iddocumento`,
			    wc_documento.`idordenVenta`,
			    wc_documento.`nombredoc`,
			    wc_documento.`fechadoc`,
			    wc_documento.`porcentajefactura`,
			    wc_documento.`montofacturado`,
			    wc_documento.`montoigv`,
			    wc_documento.`modofactura`,
			    wc_documento.`esImpreso`,
			    wc_ordenventa.`importeov`,
			    wc_ordenventa.`esfacturado`,
			    wc_ordenventa.`codigov`,
			    wc_ordenventa.`fordenventa`",
				$condicion.$condicion2);
		}
		function buscadocumentoxordenventa($idordenventa,$filtro){
			$condicion="doc.estado=1";
			if (!empty($idordenventa) && !empty($filtro)) {
				$condicion="doc.idordenventa='$idordenventa' and doc.estado=1 and ".$filtro;
			}
			elseif(!empty($idordenventa)&& empty($filtro)){
				$condicion="doc.idordenventa='$idordenventa' and doc.estado=1";
			}
			elseif(empty($idordenventa)&& !empty($filtro)){
				$condicion=" estado=1 and ".$filtro;
			}
			$sql="Select doc.*,mn.simbolo From ".$this->tabla." doc
			Inner Join wc_ordenventa ov ON ov.idordenventa=doc.idordenventa
			Inner Join wc_moneda mn ON ov.IdMoneda=mn.idmoneda 
			Where ".$condicion." ";
			$data=$this->EjecutaConsulta($sql);
			return $data;
		}

		function buscaletrasxordenventa($idordenventa,$filtro){
			$condicion="doc.estado=1";
			if (!empty($idordenventa) && !empty($filtro)) {
				$condicion="doc.idordenventa='$idordenventa' and doc.estado=1 and ".$filtro;
			}
			elseif(!empty($idordenventa)&& empty($filtro)){
				$condicion="doc.idordenventa='$idordenventa' and doc.estado=1";
			}
			elseif(empty($idordenventa)&& !empty($filtro)){
				$condicion=" estado=1 and ".$filtro;
			}
			$sql="Select doc.*,mn.simbolo,det.fvencimiento,det.numerounico,det.recepcionLetras,
			CASE det.situacion  When '' Then 'Pendiente' else det.situacion END as situacion From ".$this->tabla." doc
			Inner Join wc_ordenventa ov ON ov.idordenventa=doc.idordenventa
			Inner Join wc_moneda mn ON ov.IdMoneda=mn.idmoneda 
			Left Join wc_ordencobro oc ON ov.idordenventa=oc.idordenventa
			Inner Join wc_detalleordencobro det ON oc.idordencobro=det.idordencobro 
			and doc.numdoc=det.numeroletra and doc.nombredoc=7 and det.formacobro=3
			Where ".$condicion." ";
			// $data=$this->leeRegistro($this->tabla." doc
			// 	Inner Join wc_ordenventa ov ON ov.idordenventa=doc.idordenventa
			// 	Inner Join wc_moneda mn ON ov.IdMoneda=mn.idmoneda","",$condicion,"iddocumento ","limit 0,1 ");
			$data=$this->EjecutaConsulta($sql);
			return $data;
		}


		function buscadocumentoxordenventaPrimero($idordenventa,$filtro){
			
			$condicion="doc.estado=1 and doc.esAnulado!=1 and ";
			if (!empty($idordenventa) && !empty($filtro)) {
				$condicion="doc.idordenventa='$idordenventa' and doc.estado=1 and doc.esAnulado!=1 and ".$filtro;
			}
			elseif(!empty($idordenventa)&& empty($filtro)){
				$condicion="doc.idordenventa='$idordenventa' and doc.estado=1 and doc.esAnulado!=1 and ";
			}
			elseif(empty($idordenventa)&& !empty($filtro)){
				$condicion=" doc.estado=1 and  and doc.esAnulado!=1 and ".$filtro;
			}
			$data=$this->leeRegistro($this->tabla." doc
				Inner Join wc_ordenventa OV ON ov.idordenventa=doc.idordenVenta
				Inner Join wc_moneda MN ON ov.IdMoneda=mn.idmoneda","doc.*,mn.simbolo,mn.nombre",$condicion,"iddocumento ","limit 0,1 ");
			return $data;
		}

		function sumaNotasCredito($idordenventa){
			
			
			$data=$this->leeRegistro($this->tabla," sum(montofacturado) "," idordenventa=".$idordenventa." and nombredoc='5' ","","");

			return $data[0]['sum(montofacturado)'] ;
		}
	}
?>
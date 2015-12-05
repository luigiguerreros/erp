<?php
	class OrdenCobro extends Applicationbase{
		private $tabla="wc_ordencobro";
		private $tablas="wc_ordenventa,wc_ordencobro,";
		function listado($idordencobro=""){
			$filtro=($idordencobro!=""?("idordencobro=".$idordencobro." and "):"");
			$data=$this->leeRegistro($this->tabla,"",$filtro,"");
			return $data;
		}
		function grabaOrdencobro($data){
			$data['estado']=1;
			$exito=$this->grabaRegistro($this->tabla,$data);
			return  $exito;
		}
		function contarOrdencobro(){
			$cantidadOrdencobro=$this->contarRegistro($this->tabla,"");
			return $cantidadOrdencobro;
		}
		function editaOrdencobro($id){
			$data=$this->leeRegistro($this->tabla,"","idordencobro=$id","");
			return $data;
		}
		function eliminaOrdencobro($id){
			$exito=$this->inactivaRegistro($this->tabla,"idordencobro=$id");
			return $exito;
		}
		//eliminaxIdordenventa

		function eliminaxIdordenventa($idOrdenVenta){
			$exito=$this->inactivaRegistro($this->tabla,"idordenventa=".$idOrdenVenta);
			return $exito;
		}

		function actualizaOrdencobroxfiltro($data,$filtro){
			$exito=$this->actualizaRegistro($this->tabla,$data,$filtro);
			return $exito;
		}
		function actualizaOrdencobro($data,$idOrdencobro){
			$exito=$this->actualizaRegistro($this->tabla,$data,"idordencobro=$idOrdencobro");
			return $exito;
		}
		function buscaOrdencobro($idOrdencobro){
			$data=$this->leeRegistro($this->tabla,"","idordencobro=$idOrdencobro","");
			return $data;
		}
		function listarxguia($idguia){
			$data=$this->leeRegistro($this->tabla,"idordencobro,importeordencobro,monedaordencobro,escredito,escontado,esletras,numletras,tipoletra,femision,saldoordencobro,situacion","idordenventa=$idguia and estado=1","idordencobro desc");
			//$data=$this->leeRegistro($this->tabla,"idordencobro,importeordencobro,monedaordencobro,escredito,escontado,esletras,numletras,tipoletra,femision,saldoordencobro,situacion","idordenventa=$idguia and estado=1 and ascii(situacion)<>99","idordencobro desc");
			return $data;
		}
		function buscaxordenventa($idordenventa){
			$data=$this->leeRegistro($this->tabla,"idordencobro,importeordencobro,monedaordencobro,escredito,escontado,esletras,numletras,tipoletra,femision","idordenventa='$idordenventa'","");
			return $data;
		}
		function buscaxFiltro($filtro){
			$data=$this->leeRegistro($this->tabla,"",$filtro,"","");
			return $data;
		}
		function buscafechavencimiento($idordenventa){
			$data=$this->leeRegistro(
					"wc_ordencobro ord 
					inner join wc_detalleordencobro dt on ord.idordencobro=dt.idordencobro",
					"",
					"ord.idordenventa='$idordenventa' and dt.renovado=0 and dt.fechagiro=(SELECT min(dt2.fechagiro) FROM wc_detalleordencobro dt2 inner join wc_ordencobro ord2 on dt2.idordencobro=ord2.idordencobro where ord2.idordenventa='$idordenventa')",
					"dt.iddetalleordencobro DESC LIMIT 0,1"
					);
			return $data;
		}
 
		function tieneletras($idordencobro){
			$r=$this->leeRegistro($this->tabla,"","idordencobro=$idordencobro and tipo=3","");
			if(count($r)){
				return true;
			}else return false;
		}
		/*function buscarCobanzaultima(){
			$data=$this->leeRegistro($this->tabla,"idordencobro,importe,fvencimiento,tipo","idorden=$idguia","");
			return $data;
		}
		function buscarDConbranzaUltimo(){

		}*/
		//Es el importe total de todo lo cancelado y pendiente
		function deudatotal($idordenventa){
			$data=$this->leeRegistro(
				"`wc_ordencobro` wc_ordencobro inner join `wc_detalleordencobro` wc_detalleordencobro on  wc_ordencobro.`idordencobro`=wc_detalleordencobro.`idordencobro` ",
				"sum(wc_detalleordencobro.`importedoc`)",
				"(wc_detalleordencobro.`situacion`='cancelado' or wc_detalleordencobro.`situacion`='') and wc_ordencobro.`idordenventa`='$idordenventa' and wc_detalleordencobro.`estado`='1' ",
				"");
			$data2=$this->leeRegistro(
				"`wc_ordencobro` wc_ordencobro inner join `wc_detalleordencobro` wc_detalleordencobro on  wc_ordencobro.`idordencobro`=wc_detalleordencobro.`idordencobro` ",
				"sum(wc_detalleordencobro.`importedoc` - wc_detalleordencobro.`saldodoc`)",
				"(wc_detalleordencobro.`situacion`='reprogramado') and wc_ordencobro.`idordenventa`='$idordenventa' and wc_detalleordencobro.`estado`='1' ",
				"");
			$respuesta=$data[0]['sum(wc_detalleordencobro.`importedoc`)']+$data2[0]['sum(wc_detalleordencobro.`importedoc` - wc_detalleordencobro.`saldodoc`)'];
			return $respuesta;
		}
		function totalRenovados($idordenventa){
			$data=$this->leeRegistro(
				"`wc_ordencobro` wc_ordencobro inner join `wc_detalleordencobro` wc_detalleordencobro on  wc_ordencobro.`idordencobro`=wc_detalleordencobro.`idordencobro` ",
				"sum(wc_detalleordencobro.`importedoc`)",
				"wc_detalleordencobro.`tipogasto`=1 and wc_detalleordencobro.`situacion`!='anulado' and wc_ordencobro.`idordenventa`='$idordenventa' and wc_detalleordencobro.`estado`=1",
				"");
			return $data[0]['sum(wc_detalleordencobro.`importedoc`)'];
		}
		function totalFlete($idordenventa){
			$data=$this->leeRegistro(
				"`wc_ordencobro` wc_ordencobro inner join `wc_detalleordencobro` wc_detalleordencobro on  wc_ordencobro.`idordencobro`=wc_detalleordencobro.`idordencobro` ",
				"sum(wc_detalleordencobro.`importedoc`)",
				"wc_detalleordencobro.`tipogasto`=3 and wc_detalleordencobro.`situacion`!='anulado' and wc_detalleordencobro.`estado`=1 and wc_ordencobro.`idordenventa`='$idordenventa'",
				"");
			return $data[0]['sum(wc_detalleordencobro.`importedoc`)'];
		}
		function totalGastoBancario($idordenventa){
			$data=$this->leeRegistro(
				"`wc_ordencobro` wc_ordencobro inner join `wc_detalleordencobro` wc_detalleordencobro on  wc_ordencobro.`idordencobro`=wc_detalleordencobro.`idordencobro` ",
				"sum(wc_detalleordencobro.`importedoc`)",
				"wc_detalleordencobro.`tipogasto`=4 and wc_detalleordencobro.`situacion`!='anulado' and wc_detalleordencobro.`estado`=1 and wc_ordencobro.`idordenventa`='$idordenventa'",
				"");
			return $data[0]['sum(wc_detalleordencobro.`importedoc`)'];
		}
		function totalEnvioSobre($idordenventa){
			$data=$this->leeRegistro(
				"`wc_ordencobro` wc_ordencobro inner join `wc_detalleordencobro` wc_detalleordencobro on  wc_ordencobro.`idordencobro`=wc_detalleordencobro.`idordencobro` ",
				"sum(wc_detalleordencobro.`importedoc`)",
				"wc_detalleordencobro.`tipogasto`=5 and wc_detalleordencobro.`situacion`!='anulado' and wc_detalleordencobro.`estado`=1 and wc_ordencobro.`idordenventa`='$idordenventa'",
				"");
			return $data[0]['sum(wc_detalleordencobro.`importedoc`)'];
		}
		function totalCostoMantenimiento($idordenventa){
			$data=$this->leeRegistro(
				"`wc_ordencobro` wc_ordencobro inner join `wc_detalleordencobro` wc_detalleordencobro on  wc_ordencobro.`idordencobro`=wc_detalleordencobro.`idordencobro` ",
				"sum(wc_detalleordencobro.`importedoc`)",
				"wc_detalleordencobro.`tipogasto`=6 and wc_detalleordencobro.`situacion`!='anulado' and wc_detalleordencobro.`estado`=1 and wc_ordencobro.`idordenventa`='$idordenventa'",
				"");
			return $data[0]['sum(wc_detalleordencobro.`importedoc`)'];
		}
		function totalGastoProtesto($idordenventa){
			$data=$this->leeRegistro(
				"`wc_ordencobro` wc_ordencobro inner join `wc_detalleordencobro` wc_detalleordencobro on  wc_ordencobro.`idordencobro`=wc_detalleordencobro.`idordencobro` ",
				"sum(wc_detalleordencobro.`montoprotesto`)",
				"(wc_detalleordencobro.`tipogasto`=2 or wc_detalleordencobro.`tipogasto`=7)  and wc_detalleordencobro.`estado`=1 and wc_ordencobro.`idordenventa`='$idordenventa'",
				"");
			return $data[0]['sum(wc_detalleordencobro.`montoprotesto`)'];
		}
		//importe de los detalles de orden cobro canceladas
		function totalCancelado($idordenventa){
			$data=$this->leeRegistro(
				"`wc_ordencobro` wc_ordencobro inner join `wc_detalleordencobro` wc_detalleordencobro on  wc_ordencobro.`idordencobro`=wc_detalleordencobro.`idordencobro` ",
				"sum(wc_detalleordencobro.`importedoc`)",
				" wc_detalleordencobro.`estado`=1 and wc_ordencobro.`idordenventa`='$idordenventa' and wc_detalleordencobro.`situacion`='cancelado' ",
				"");
			return $data[0]['sum(wc_detalleordencobro.`importedoc`)'];
		}
		//importe de lo que ha pagado
		function totalPagado($idordenventa){
			$data=$this->leeRegistro(
				"`wc_ordencobro` wc_ordencobro inner join `wc_detalleordencobro` wc_detalleordencobro on  wc_ordencobro.`idordencobro`=wc_detalleordencobro.`idordencobro` ",
				"sum(wc_detalleordencobro.`importedoc`- wc_detalleordencobro.`saldodoc`)",
				" wc_detalleordencobro.`estado`=1 and wc_ordencobro.`idordenventa`='$idordenventa' and (wc_detalleordencobro.`situacion`='cancelado' or  wc_detalleordencobro.`situacion`='')",
				"");
			return $data[0]['sum(wc_detalleordencobro.`importedoc`- wc_detalleordencobro.`saldodoc`)'];
		}
		//lo que debe de la orden de venta
		function totalPendiente($idordenventa){
			$data=$this->leeRegistro(
				"`wc_ordencobro` wc_ordencobro inner join `wc_detalleordencobro` wc_detalleordencobro on  wc_ordencobro.`idordencobro`=wc_detalleordencobro.`idordencobro` ",
				"sum(wc_detalleordencobro.`saldodoc`)",
				" wc_detalleordencobro.`estado`=1 and wc_ordencobro.`idordenventa`='$idordenventa' and wc_detalleordencobro.`situacion`='' ",
				"");
			return $data[0]['sum(wc_detalleordencobro.`saldodoc`)'];
		}

		function totalAnulado($idordenventa){
			$data=$this->leeRegistro(
				"`wc_ordencobro` wc_ordencobro inner join `wc_detalleordencobro` wc_detalleordencobro on  wc_ordencobro.`idordencobro`=wc_detalleordencobro.`idordencobro` ",
				"sum(wc_detalleordencobro.`importedoc`)",
				" wc_detalleordencobro.`estado`=1 and wc_ordencobro.`idordenventa`='$idordenventa' and wc_detalleordencobro.`situacion`='anulado' ",
				"");
			return $data[0]['sum(wc_detalleordencobro.`importedoc`)'];
		}

	}
?>
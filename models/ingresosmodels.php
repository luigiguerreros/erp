<?php
	class Ingresos extends  Applicationbase{
		private $tabla="wc_ingresos";
		private $tablas='wc_actor,wc_ingresos,wc_orden';		
		function listado(){
			$cuenta=$this->leeRegistro($this->tabla,"","estado=1 and esvalidado=1","");
			return $cuenta;
		}
		function graba($data){
			$id=$this->grabaRegistro($this->tabla,$data);
			return $id;
		}
		function actualiza($data,$filtro){
			$exito=$this->actualizaRegistro($this->tabla,$data,$filtro);
			return $exito;
		}
		function actualizaxid($data,$idingresos){
			$exito=$this->actualizaRegistro($this->tabla,$data,"idingresos=$idingresos");
			return $exito;
		}
		function buscar($id){
			$data=$this->leeRegistro($this->tabla,"","idingresos=$id","");
			return $data;
		}
		function eliminar($id){
			$exito=$this->inactivaRegistro($this->tabla,"idingresos='$id'");
			return $exito;
		}
		function listarxvendedor($idvendedor){
			$data=$this->leeRegistro($this->tabla,""," esvalidado=1 and estado=1 situacionpago=0 and idvendedor=".$idvendedor,"");
			return $data; 
		}
		function buscaxid($idingresos){
			$data=$this->leeRegistro($this->tabla,"","estado=1 and idingresos='$idingresos'","");
			return $data; 
		}
		function listarxzona($idzona){
			$data=$this->leeRegistro($this->tabla,""," estado=1 and idzona=".$idzona,"");
			return $data;
		}
		function contarIngresos(){
			$cantidadMovimiento=$this->contarRegistro($this->tabla,"estado=1");
			return $cantidadMovimiento;
		}
		function listarxcliente($idcliente){
			$data=$this->leeRegistro($this->tabla,"","idcliente=".$idcliente,"");
			return $data;
		}
		function listarxordenpago($id){
			$data=$this->leeRegistro($this->tabla,"","idordenpago=".$id,"");
			return $data;
		}

		//Ingresos del día
		function listarxhoy(){
			$data=$this->leeRegistro($this->tabla,"","date(`fechacreacion`)=curdate() and estado=1 and esvalidado=1","");
			return $data;
		}

		function listarIngresosDifLetras(){
			$data=$this->leeRegistro(
				"`wc_ingresos` wc_ingresos INNER JOIN `wc_detalleordencobroingreso` wc_detalleordencobroingreso ON wc_ingresos.`idingresos` = wc_detalleordencobroingreso.`idingreso`
     			INNER JOIN `wc_detalleordencobro` wc_detalleordencobro ON wc_detalleordencobroingreso.`iddetalleordencobro` = wc_detalleordencobro.`iddetalleordencobro`",
				"wc_ingresos.`idingresos`,
			     wc_ingresos.`idOrdenVenta`,
			     wc_ingresos.`idcliente`,
			     wc_ingresos.`idtipocambio`,
			     wc_ingresos.`idcobrador`,
			     wc_ingresos.`nrorecibo`,
			     wc_ingresos.`montoingresado`,
			     wc_ingresos.`tipocobro`,
			     wc_ingresos.`nrodoc`,
			     wc_ingresos.`monedai`,
			     wc_ingresos.`fcobro`,
			     wc_detalleordencobroingreso.`iddetalleordencobroingreso`,
			 
			     wc_detalleordencobroingreso.`montop`,
			     wc_detalleordencobroingreso.`monedap`,
			     wc_detalleordencobro.`iddetalleordencobro`,
			     wc_detalleordencobro.`idordencobro`,
			     wc_detalleordencobro.`importedoc`,
			     wc_detalleordencobro.`formacobro`,
			     wc_detalleordencobro.`numeroletra`,
			     wc_detalleordencobro.`fvencimiento`,
			     wc_detalleordencobro.`protesto`,
			     wc_detalleordencobro.`situacion`,
			     wc_ingresos.`estadocomicobra`",
				"wc_ingresos.`saldo`>0 and wc_ingresos.`tipocobro`!=3 and wc_detalleordencobro.`situacion`='' and wc_ingresos.`estado`=1",
				"");
			return $data;
		}		
		

		//Registro de ingresos generales del día.
		function resumeningresoshoy()
		{
			$sql="Select 
			concat(wa.apellidopaterno,' ',wa.apellidomaterno,' ,',wa.nombres) as cobrador,
			wc.razonsocial as cliente,ing.nrorecibo,ing.idingresos,ing.montoingresado,ing.tipocobro,ing.nrodoc,ing.idOrdenVenta,ov.codigov,ing.usuariocreacion 
			From wc_ingresos ing 
			Inner Join wc_cliente wc On ing.idcliente=wc.idcliente
			Inner Join wc_actor wa On ing.idcobrador=wa.idactor
			Inner Join wc_ordenventa ov ON ing.idOrdenVenta=ov.idordenventa
			Where date(ing.`fechacreacion`)=curdate() and ing.estado=1 and esvalidado=1";
			return $this->EjecutaConsulta($sql);
		}
		

		function listarIngresos($idOrdenVenta){
			$data=$this->leeRegistro($this->tabla,"","idOrdenVenta='$idOrdenVenta' and estado=1 and esvalidado=1 ","");
			return $data;
		}

		function IngresosxIdordenVenta($idOrdenVenta){
			$data=$this->leeRegistro($this->tabla,"","idOrdenVenta='$idOrdenVenta' and estado=1","");
			return $data;
		}

		function listarIngresosConCobrador($idOrdenVenta){
			$data=$this->leeRegistro("`wc_actor` wc_actor inner join `wc_ingresos` wc_ingresos on wc_actor.`idactor`=wc_ingresos.`idcobrador`","","wc_ingresos.`idOrdenVenta`='$idOrdenVenta' and wc_ingresos.`estado`=1 and esvalidado=1","");
			return $data;
		}
		function listarIngresosConSaldo($idOrdenVenta){
			$data=$this->leeRegistro($this->tabla,"","idOrdenVenta='$idOrdenVenta' and saldo>0 and estado=1 and esvalidado=1","");
			return $data;
		}
		function sumaIngresos($idOrdenVenta){
			
			$data=$this->leeRegistro($this->tabla,"sum(saldo),sum(montoingresado),sum(montoasignado)","idOrdenVenta='$idOrdenVenta' and estado=1 and esvalidado=1","tipocobro desc");
			return $data;
		}
		function verificarrecibo($nrorecibo){
			$nrorecibo=htmlentities($nrorecibo,ENT_QUOTES,'UTF-8');
			$data=$this->leeRegistro($this->tabla,"","nrorecibo='$nrorecibo' and estado=1","");
			return count($data);
		}
		function listarIngresosNoValidados(){
			$data=$this->leeRegistro(
							"wc_ingresos i inner join wc_ordenventa ov on i.`idOrdenVenta`=ov.`idordenventa`
							inner join wc_clientezona cz on  ov.`idclientezona`=cz.`idclientezona`
							inner join wc_cliente  c  on c.`idcliente`=cz.`idcliente`
							inner join wc_moneda mn on ov.IdMoneda=mn.idmoneda
							 ",
							"",
							"i.`esvalidado`=0 and i.`estado`=1 ",
							"");
			return $data;
		}

		function liberaAsignacionxIdOrdenVenta($idOrdenVenta){
			$sql="Update ".$this->tabla." Set saldo=montoingresado,montoasignado=0 where idOrdenVenta=".$idOrdenVenta;
			return $this->EjecutaConsulta($sql);
		}
	}
?>
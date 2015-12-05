<?php
	class Movimiento extends Applicationbase{
		private $tabla='wc_movimiento';
		private $_tabla_detallemovimiento='wc_detallemovimiento';
		function listadoMovimientos(){
			$movimiento=$this->leeRegistro($this->tabla,"","","fechamovimiento desc","");
			return $movimiento;
		}
		function listadoTotal(){
			$datos=$this->leeRegistro($this->tabla,"","estado=1","");
			return $datos;
		}
		function actualizaMovimiento($data,$filtro){
			$exito=$this->actualizaRegistro($this->tabla,$data,$filtro);
			return $exito;
		}
		function buscaMovimiento($idMovimiento){
			$movimiento=$this->leeRegistro($this->tabla,"","id=".$idMovimiento." AND estado='1'","");
			return $movimiento;
		}
		function buscaMovimientoxfiltro($filtro){
			$movimiento=$this->leeRegistro($this->tabla,""," estado='1' and ".$filtro,"");
			return $movimiento;
		}
		function cambiaEstadoMovimiento($idMovimiento){
			$estado=$this->cambiaEstado($this->tabla,"id=".$idMovimiento);
			return $estado;
		}
		function registraMovimiento($data){
			$exito=$this->grabaRegistro($this->tabla,$data);
			return $exito;
		}
		function grabaMovimiento($data){
			$exito=$this->grabaRegistro($this->tabla,$data);
			return $exito;
		}
		function contarMovimiento(){
			$cantidad=$this->contarRegistro($this->tabla,"");
			return $cantidad;
		}
		function generaCodigo(){
			$data=$this->leeRegistro($this->tabla,"MAX(idmovimiento)+1 as id","","");
			$valor="00000".$data[0]['id'];
			$codigo=substr($valor,strlen($valor)-6,6);
			return $codigo;
		}

		function listaMovPaginado($pagina,$parametro=""){
			$condicion="estado=1";
			if (!empty($parametro)) {
				$condicion="estado=1 and fechamovimiento='$parametro' or ndocumento='$parametro'";
			}
			$data=$this->leeRegistroPaginado(
				$this->tabla,
				"",
				$condicion,
				"fechamovimiento desc,idmovimiento desc",$pagina);
			return $data;
		}
		function paginadoMov($parametro=""){
			$condicion="estado=1";
			if (!empty($parametro)) {
				$condicion="estado=1 and fechamovimiento='$parametro' or ndocumento='$parametro'";
			}
			return $this->paginado($this->tabla,"",$condicion);
		}
		function listadoxParametro($parametro=""){
			$condicion="estado=1";
			if (!empty($parametro)) {
				$condicion="estado=1 and fechamovimiento='$parametro' or ndocumento='$parametro'";
			}
			$datos=$this->leeRegistro($this->tabla,"",$condicion,"");
			return $datos;
		}


		function kardexValorizadoxProducto($id,$ano,$mesInicial,$mesFinal,$sunat){
			$filtro="and m.estado=1 and dm.estado=1";
			if ($sunat==1) {
				$filtro=" and m.essunat='$sunat' ";
			}
			$sql="	SELECT 
					dm.idproducto,m.fechamovimiento,m.tipomovimiento,t.codigotipooperacion,mt.codigo,mt.nombre, dt.codigotipodocumento,m.ndocumento,m.serie,dm.cantidad,dm.pu,
					CASE m.tipomovimiento WHEN 1 THEN dm.cantidad ELSE '' END AS EntradaCantidad,
					CASE m.tipomovimiento WHEN 1 THEN dm.preciovalorizado ELSE '' END AS EntradaPrecio,
					CASE m.tipomovimiento WHEN 1 THEN dm.preciovalorizado*dm.cantidad ELSE '' END AS EntradaCosto,
					CASE m.tipomovimiento WHEN 2 THEN dm.cantidad ELSE '' END AS SalidaCantidad,
					CASE m.tipomovimiento WHEN 2 THEN dm.preciovalorizado ELSE '' END AS SalidaPrecio,
					CASE m.tipomovimiento WHEN 2 THEN dm.preciovalorizado*dm.cantidad ELSE '' END AS SalidaCosto,
					dm.stockactual as SaldoCantidad, dm.preciovalorizado as SaldoPrecio, round(dm.stockactual*dm.preciovalorizado,2) AS SaldoCosto
					FROM  `wc_detallemovimiento` dm
					INNER JOIN wc_movimiento m ON m.idmovimiento = dm.idmovimiento
					left JOIN wc_tipooperacion t ON m.idtipooperacion=t.idtipooperacion
					INNER JOIN wc_movimientotipo mt ON m.tipomovimiento = mt.idmovimientotipo
					INNER JOIN wc_producto p ON dm.idproducto = p.idproducto
					
					LEFT JOIN wc_documentotipo dt ON dt.iddocumentotipo=m.iddocumentotipo
					WHERE dm.estado=1 and dm.idproducto=$id  and MONTH(m.fechamovimiento)>='$mesInicial' and MONTH(m.fechamovimiento)<='$mesFinal' and YEAR(m.fechamovimiento)='$ano' ".$filtro."
					ORDER BY dm.idproducto asc";
			return $this->EjecutaConsulta($sql);
		}
		function kardexTotalxProducto($ano,$mesInicio,$mesFinal){
			$filtro="and m.estado=1 and dm.estado=1 and p.estado=1";
			
			$sql="	SELECT
			p.codigopa,p.nompro,p.idproducto,m.fechamovimiento,m.tipomovimiento,t.codigotipooperacion,mt.codigo,mt.nombre, dt.codigotipodocumento,m.ndocumento,m.serie,dm.cantidad,dm.pu,
			CASE m.tipomovimiento WHEN 1 THEN dm.cantidad ELSE '' END AS EntradaCantidad,
			CASE m.tipomovimiento WHEN 1 THEN dm.preciovalorizado ELSE '' END AS EntradaPrecio,
			CASE m.tipomovimiento WHEN 1 THEN dm.preciovalorizado*dm.cantidad ELSE '' END AS EntradaCosto,
			CASE m.tipomovimiento WHEN 2 THEN dm.cantidad ELSE '' END AS SalidaCantidad,
			CASE m.tipomovimiento WHEN 2 THEN dm.preciovalorizado ELSE '' END AS SalidaPrecio,
			CASE m.tipomovimiento WHEN 2 THEN dm.preciovalorizado*dm.cantidad ELSE '' END AS SalidaCosto,
			dm.stockactual as SaldoCantidad, dm.preciovalorizado as SaldoPrecio, round(dm.stockactual*dm.preciovalorizado,2) AS SaldoCosto
			FROM  `wc_detallemovimiento` dm
			INNER JOIN wc_movimiento m ON m.idmovimiento = dm.idmovimiento
			left JOIN wc_tipooperacion t ON m.idtipooperacion=t.idtipooperacion
			INNER JOIN wc_movimientotipo mt ON m.tipomovimiento = mt.idmovimientotipo
			INNER JOIN wc_producto p ON dm.idproducto = p.idproducto
				
			LEFT JOIN wc_documentotipo dt ON dt.iddocumentotipo=m.iddocumentotipo
			WHERE dm.estado=1  and MONTH(m.fechamovimiento)>='$mesInicio' and MONTH(m.fechamovimiento)<='$mesFinal' and YEAR(m.fechamovimiento)='$ano' ".$filtro."
			ORDER BY dm.idproducto,m.fechamovimiento";
			return $this->EjecutaConsulta($sql);
		}



		function InactivaMovimientoxIdOrdenVenta($idOrdenVenta){
			$sql="Select idmovimiento From ".$this->tabla." Where idordenventa=".$idOrdenVenta;
			$dataMovimiento=$this->EjecutaConsulta($sql);
			$idmovimiento=$dataMovimiento[0]['idmovimiento'];

			$sql="Update  ".$this->_tabla_detallemovimiento." Set estado=0 Where idmovimiento=".$idmovimiento;
			$exito_detmov=$this->EjecutaConsulta($sql);

			$sql="Update  ".$this->tabla." Set estado=0 Where idmovimiento=".$idmovimiento;
			$exito_mov=$this->EjecutaConsulta($sql);
			return true;
		}
	}
?>
<?php
	class Reporte extends Applicationbase{
		private $tablaStockProducto="wc_producto,wc_linea,wc_almacen";
		private $tablaStockPorLinea="wc_producto,wc_linea,wc_almacen";
		//private $tablaKardex="wc_detallemovimiento,wc_producto,wc_movimiento";
		private $tablaKardex="wc_detallemovimiento as dm,wc_producto as p,wc_movimiento as m,wc_linea as l";
		private $tablaReporteOrdenCompra="wc_ordencompra,wc_almacen,wc_proveedor";
		private $tablaAgotados="wc_detallemovimiento,wc_movimiento,wc_producto";
		function reporteStockValorizado($idLinea='',$idSubLinea=''){
			$condicion='';
			if(!empty($idLinea)){$condicion="and t2.idpadre=$idLinea";}
			if(!empty($idSubLinea)){$condicion="and t1.idlinea=$idSubLinea";}
			$producto=$this->leeRegistro3($this->tablaStockPorLinea,"nompro,codigop,nomalm,nomlin,unidadmedida,stockactual,preciolista","t1.estado=1 and t2.estado=1 $condicion","",2);
			return $producto;
		}
		function totalesStockValorizado(){
			$condicion='';
			if(!empty($idLinea)){$condicion="and t2.idpadre=$idLinea";}
			if(!empty($idSubLinea)){$condicion="and t1.idlinea=$idSubLinea";}
			$suma=$this->leeRegistro3($this->tablaStockPorLinea,"sum(preciolista*stockactual) as totalpreciolista","t1.estado=1 and t2.estado=1 $condicion","",2);
			return $suma;	
		}
		function reporteStockProducto($idAlmacen,$idLinea,$idSubLinea,$idProducto){
			$condicion="t1.estado=1";
			if(!empty($idAlmacen)){$condicion="t1.idalmacen=$idAlmacen";}
			if(!empty($idLinea)){$condicion="idpadre=$idLinea";}
			if(!empty($idSubLinea)){$condicion="t2.idlinea=$idSubLinea";}
			if(!empty($idProducto)){$condicion="t1.idproducto=$idProducto";}
			$stockProducto=$this->leeRegistro("wc_producto t1 
							inner join  wc_linea t2 on t1.idlinea=t2.idlinea 
							inner join wc_almacen t3 on t1.idalmacen=t3.idalmacen
							left join wc_unidadmedida t4 on t1.unidadmedida=t4.idunidadmedida
							",
							"*,t4.codigo as unidadmedida","$condicion","","order by idpadre,trim(t1.codigopa) asc");
			return $stockProducto;
		}
		function reporteListaPrecio($idAlmacen,$idLinea,$idSubLinea,$idProducto){
			//$condicion="t1.estado=1";
			if(!empty($idAlmacen)){$condicion="t1.idalmacen=$idAlmacen";}
			if(!empty($idLinea)){$condicion="idpadre=$idLinea";}
			if(!empty($idSubLinea)){$condicion="t2.idlinea=$idSubLinea";}
			if(!empty($idProducto)){$condicion="t1.idproducto=$idProducto";}
                        
			$stockProducto=$this->leeRegistro("wc_producto t1 
							inner join  wc_linea t2 on t1.idlinea=t2.idlinea 
							inner join wc_almacen t3 on t1.idalmacen=t3.idalmacen
							left join wc_unidadmedida t4 on t1.unidadmedida=t4.idunidadmedida
							left join wc_empaque em on t1.empaque=em.idempaque
							","*,t4.nombre as nombremedida",
							"$condicion and t1.stockactual>0",
							"","order by t2.idpadre,t2.idlinea,t1.idproducto asc");
			
                        return $stockProducto;
                        
		}
		function reporteKardex($idAlmacen,$idLinea,$idSubLinea,$idProducto){
			$condicion="";
			if(!empty($idAlmacen)){$condicion="and idalmacen=$idAlmacen";}
			if(!empty($idLinea)){$condicion="and idpadre=$idLinea";}
			if(!empty($idSubLinea)){$condicion="and p.idlinea=$idSubLinea";}
			if(!empty($idProducto)){$condicion="and p.idproducto=$idProducto";}
			//$kardex=$this->leeRegistro3($this->tablaKardex,"","$condicion","",2);
			$kardex=$this->leeRegistro($this->tablaKardex,"","dm.idproducto=p.idproducto and dm.idmovimiento=m.idmovimiento and p.idlinea=l.idlinea $condicion","");
			return $kardex;
		}
		function reporteAgotados($fecha,$fechaInicio,$fechaFinal,$idProducto){
			$condicion="";
			if(!empty($fecha)){$condicion="and fechamovimiento='$fecha'";}
			if(!empty($fechaInicio)){$condicion="and fechamovimiento between '$fechaInicio' and '$fechaFinal'";}
			if(!empty($idProducto)){$condicion="and t3.idproducto=$idProducto";}
			$agotados=$this->leeRegistro3($this->tablaAgotados,"","t3.stockactual=0 $condicion","",2,"");
			return $agotados;
		}
		function reporteOrdenCompra($idProveedor,$fecha,$fechaInicio,$fechaFinal){
			$condicion="";
			if(!empty($idProveedor)){$condicion="and t3.idproveedor=$idProveedor";}
			if(!empty($fecha)){$condicion="and fordencompra='$fecha'";}
			if(!empty($fechaInicio)){$condicion="and fordencompra between '$fechaInicio' and '$fechaFinal'";}
			$data=$this->leeRegistro3($this->tablaReporteOrdenCompra,"","t1.estado=1 $condicion","",2);
			return $data;
		}
		function getRutaImagen(){

		}

		function reportletras($filtro="",$idzona="",$idcategoriaprincipal="",$idcategorias="",$idvendedor="",$idtipocobranza="",$fechainicio="",$fechafinal="",$octavaNovena="",$situacion="",$fechaPagoInicio="",$fechaPagoFinal="",$IdCliente="",$IdOrdenVenta=""){
			$condicion="wc_ordenventa.`esguiado`=1 and wc_ordenventa.`estado`=1 and wc_ordencobro.`estado`=1 and wc_detalleordencobro.`situacion`!='reprogramado'  and wc_detalleordencobro.`situacion`!='anulado'  and wc_detalleordencobro.`situacion`!='extornado' and wc_detalleordencobro.`situacion`!='refinanciado' and wc_detalleordencobro.`situacion`!='protestado' and wc_detalleordencobro.`situacion`!='renovado'  ";
			$condicion.=!empty($idzona)?" and wc_zona.`idzona`='$idzona' ":"";
			$condicion.=!empty($idcategoriaprincipal)?" and wc_categoria.`idpadrec`='$idcategoriaprincipal' ":"";
			$condicion.=!empty($idcategorias)?$idcategorias:"";
			$condicion.=!empty($idvendedor)?" and wc_actor.`idactor`='$idvendedor' ":"";
			if(!empty($idtipocobranza)){
					$sql="Select idtipocobranza,nombre,diaini,diafin 
					From wc_tipocobranza Where estado=0 and ntc='A' and idtipocobranza=".$idtipocobranza;
					$data1=$this->EjecutaConsulta($sql);

					$nomtipocobranza=$data[0]['nombre'];
					$diaini=$data1[0]['diaini'];
					$diafin=$data1[0]['diafin'];
					$condicion.="AND DATEDIFF(NOW(),wc_detalleordencobro.`fvencimiento`) BETWEEN ".$diaini." and ".$diafin." ";
					$situacion.=" and wc_detalleordencobro.`situacion`='' ";
			}
			$condicion.=!empty($IdCliente)?" and wc_cliente.`idcliente`='$IdCliente' ":"";
			$condicion.=!empty($IdOrdenVenta)?" and wc_ordenventa.`idordenventa`='$IdOrdenVenta' ":"";
			//$condicion.=!empty($idtipocobranza)?" and wc_ordenventa.`idtipocobranza`='$idtipocobranza' ":"";
			//$condicion.=!empty($idtipocobranza)?" and DATEDIFF(NOW(),wc_detalleordencobro.`fvencimiento`) BETWEEN  ":"";
			$condicion.=!empty($fechainicio)?" and wc_detalleordencobro.`fvencimiento`>='$fechainicio' ":"";
			$condicion.=!empty($fechafinal)?" and wc_detalleordencobro.`fvencimiento`<='$fechafinal' ":"";
			$condicion.=!empty($fechaPagoInicio)?" and wc_detalleordencobro.`fechapago`>='$fechaPagoInicio' ":"";
			$condicion.=!empty($fechaPagoFinal)?" and wc_detalleordencobro.`fechapago`<='$fechaPagoFinal' ":"";
			$condicion.=!empty($octavaNovena)?$octavaNovena:"";
			$condicion.=!empty($situacion)?$situacion:"";
			$condicion.=!empty($filtro)?" and  ".$filtro." ":"";

			$data=$this->leeRegistro(
					"`wc_ordenventa` wc_ordenventa 
					INNER JOIN `wc_moneda` wc_moneda ON wc_ordenventa.IdMoneda=wc_moneda.idmoneda
					INNER JOIN `wc_clientezona` wc_clientezona ON wc_ordenventa.`idclientezona` = wc_clientezona.`idclientezona`
				    INNER JOIN `wc_actor` wc_actor ON wc_ordenventa.`idvendedor` = wc_actor.`idactor`
				    INNER JOIN `wc_cliente` wc_cliente ON wc_clientezona.`idcliente` = wc_cliente.`idcliente`
				    INNER JOIN `wc_zona` wc_zona ON wc_clientezona.`idzona` = wc_zona.`idzona`
				    INNER JOIN `wc_categoria` wc_categoria ON wc_zona.`idcategoria` = wc_categoria.`idcategoria`
				    inner join `wc_ordencobro` wc_ordencobro on wc_ordencobro.`idordenventa`=wc_ordenventa.`idordenventa`
				    inner join `wc_detalleordencobro` wc_detalleordencobro on wc_detalleordencobro.`idordencobro`=wc_ordencobro.`idordencobro`",

					"wc_actor.`apellidomaterno`,
				     wc_ordenventa.`codigov`,
				     (wc_ordenventa.`importepagado`-wc_ordenventa.`importedevolucion`) as `importepagado`,
				     wc_ordenventa.`idordenventa`,
				     wc_ordenventa.`idtipocobranza`,
				     wc_ordenventa.`fechadespacho`,
				     wc_ordenventa.`fechavencimiento`,
				     wc_ordenventa.`importedevolucion`,
				     wc_moneda.`simbolo`,
				     wc_cliente.`idcliente`,
				     wc_cliente.`iddistrito`,
				     wc_cliente.`apellido1`,
				     wc_cliente.`apellido2`,
				     wc_cliente.`direccion`,
				     wc_categoria.`idcategoria`,
				     wc_categoria.`idpadrec`,
				     wc_categoria.`codigoc`,
				     wc_categoria.`nombrec`,
				     wc_actor.`idactor`,
				     wc_actor.`codigoa`,
				     wc_cliente.`nombrecli`,
				     wc_cliente.`razonsocial`,
				     wc_zona.`idzona`,
				     wc_zona.`nombrezona`,
				     wc_actor.`nombres`,
				     wc_actor.`apellidopaterno`,
				     wc_actor.`apellidomaterno`,
				     wc_ordencobro.`saldoordencobro`,
				     wc_detalleordencobro.`iddetalleordencobro`,
				     wc_detalleordencobro.`situacion`,
				     wc_detalleordencobro.`saldodoc`, 
				     wc_detalleordencobro.`importedoc`,
				     wc_detalleordencobro.`numeroletra`,
				     wc_detalleordencobro.`referencia`,
				     wc_detalleordencobro.`numerounico`,
				     wc_detalleordencobro.`recepcionLetras`,
				     wc_detalleordencobro.`fechagiro`,
				     wc_detalleordencobro.`fvencimiento`,
				     wc_detalleordencobro.`gastosrenovacion`,
				     wc_detalleordencobro.`recepcionLetras`,
				     wc_detalleordencobro.`fechapago`,
				     wc_detalleordencobro.`formacobro`,
				     CASE  WHEN (SUBSTRING( wc_detalleordencobro.`numeroletra`,9,1)='R' OR wc_detalleordencobro.`renovado`>0) and wc_detalleordencobro.`protesto`='' THEN CONCAT('RENOV.',SUBSTRING( wc_detalleordencobro.`referencia`,10,1)) WHEN SUBSTRING( wc_detalleordencobro.`referencia`,9,1)='P' THEN 'PROTE.' WHEN SUBSTRING(wc_detalleordencobro.`numeroletra`,9,1)='' AND length(wc_detalleordencobro.`numeroletra`)>1 THEN 'REPRO.' WHEN wc_detalleordencobro.`tipogasto`=1 THEN 'GAST. RENOV.'  WHEN wc_detalleordencobro.`tipogasto`=2 THEN 'GAST. PROTE.' ELSE ' ' END as Proviene,
				     CASE wc_detalleordencobro.`situacion` when '' then DATEDIFF(NOW(),wc_detalleordencobro.`fvencimiento`) else 0 end  as DifFechas",
					$condicion,
					"trim(wc_cliente.`razonsocial`) asc ,wc_cliente.`idCliente`,wc_ordenventa.`idordenventa`,wc_detalleordencobro.`fvencimiento`"
					);
			return $data;
		}

		function reportclienteCobro($filtro="",$idzona="",$idcategoriaprincipal="",$idcategoria="",$idvendedor="",$idtipocobranza="",$fechainicio="",$fechafinal="",$situacion=""){
			$condicion="wc_ordenventa.`estado`=1 ";
			
			$condicion.=!empty($idzona)?" and wc_zona.`idzona`='$idzona' ":"";
			$condicion.=!empty($idcategoriaprincipal)?" and wc_categoria.`idpadrec`='$idcategoriaprincipal' ":"";
			$condicion.=!empty($idcategoria)?" and wc_categoria.`idcategoria`='$idcategoria' ":"";
			$condicion.=!empty($idvendedor)?" and wc_actor.`idactor`='$idvendedor' ":"";
			$condicion.=!empty($idtipocobranza)?" and wc_ordenventa.`idtipocobranza`='$idtipocobranza' ":"";
			$condicion.=!empty($fechainicio)?" and wc_detalleordencobro.`fvencimiento`>='$fechainicio' ":"";
			$condicion.=!empty($fechafinal)?" and wc_detalleordencobro.`fvencimiento`<='$fechafinal' ":"";
			$condicion.=!empty($situacion)?$situacion:"";
			$condicion.=!empty($filtro)?" and  ".$filtro." ":"";
			
			$data=$this->leeRegistro(
					"`wc_ordenventa` wc_ordenventa INNER JOIN `wc_clientezona` wc_clientezona ON wc_ordenventa.`idclientezona` = wc_clientezona.`idclientezona`
				     INNER JOIN `wc_actor` wc_actor ON wc_ordenventa.`idvendedor` = wc_actor.`idactor`
					 INNER JOIN `wc_moneda` wc_moneda ON wc_ordenventa.`idmoneda`=wc_moneda.`idmoneda`
				     INNER JOIN `wc_cliente` wc_cliente ON wc_clientezona.`idcliente` = wc_cliente.`idcliente`
				     INNER JOIN `wc_zona` wc_zona ON wc_clientezona.`idzona` = wc_zona.`idzona`
				     INNER JOIN `wc_categoria` wc_categoria ON wc_zona.`idcategoria` = wc_categoria.`idcategoria`
				     inner join `wc_ordencobro` wc_ordencobro on wc_ordencobro.`idordenventa`=wc_ordenventa.`idordenventa`
				     inner join `wc_detalleordencobro` wc_detalleordencobro on wc_detalleordencobro.`idordencobro`=wc_ordencobro.`idordencobro`",

					"wc_actor.`apellidomaterno`,
				     wc_ordenventa.`codigov`,
				     wc_ordenventa.`idordenventa`,
				     wc_ordenventa.`idtipocobranza`,
				     wc_ordenventa.`fechadespacho`,
				     wc_ordenventa.`fechavencimiento`,
				     wc_ordenventa.`importepagado`,
				     wc_ordenventa.`importedevolucion`,
				     wc_ordenventa.`observaciones`,
				     wc_moneda.`simbolo` as simbolomoneda,
				     wc_cliente.`idcliente`,
				     wc_cliente.`iddistrito`,
				     wc_cliente.`apellido1`,
				     wc_cliente.`apellido2`,
				     wc_cliente.`direccion`,
				     wc_categoria.`idcategoria`,
				     wc_categoria.`idpadrec`,
				     wc_categoria.`codigoc`,
				     wc_categoria.`nombrec`,
				     wc_actor.`idactor`,
				     wc_actor.`codigoa`,
				     wc_cliente.`nombrecli`,
				     wc_cliente.`razonsocial`,
				     wc_zona.`idzona`,
				     wc_zona.`nombrezona`,
				     wc_actor.`nombres`,
				     wc_actor.`apellidopaterno`,
				     wc_ordencobro.`saldoordencobro`,
				     wc_detalleordencobro.`situacion`,
				     wc_detalleordencobro.`formacobro`,
				     wc_detalleordencobro.`saldodoc`, 
				     wc_detalleordencobro.`importedoc`,
				     wc_detalleordencobro.`numeroletra`,
				     wc_detalleordencobro.`fechagiro`,
				     wc_detalleordencobro.`fvencimiento`,
				     wc_detalleordencobro.`gastosrenovacion`,
				     wc_detalleordencobro.`recepcionLetras`",
					$condicion,
					"wc_ordenventa.`codigov`, wc_detalleordencobro.`fechagiro` desc"
					);
			return $data;
		}
		function reportingresos($idordenventa="",$idcliente="",$idcobrador="",$nrorecibo="",$fechaInicio="",$fechaFinal="",$monto="",$idtipocobro=""){
			$condicion="i.`estado`=1 and i.`esvalidado`=1 ";
			$condicion.=!empty($idordenventa)?" and i.`idordenventa`='$idordenventa' ":" ";
			$condicion.=!empty($idcliente)?" and i.`idcliente`='$idcliente' ":" ";
			$condicion.=!empty($idcobrador)?" and i.`idcobrador`='$idcobrador' ":" ";
			$condicion.=!empty($nrorecibo)?" and i.`nrorecibo`='$nrorecibo' ":" ";
			$condicion.=!empty($fechaInicio)?" and i.`fcobro`>='$fechaInicio' ":" ";
			$condicion.=!empty($fechaFinal)?" and i.`fcobro`<='$fechaFinal' ":" ";
			$condicion.=!empty($idtipocobro)?" and i.`tipocobro`='$idtipocobro' ":" ";
			$condicion.=!empty($monto)?$monto:"";
			
			$data=$this->leeRegistro(
					"wc_ingresos i 
					inner join wc_cliente c on i.`idcliente`=c.`idcliente`
					inner join wc_actor a on a.`idactor`=i.`idcobrador`

					",
					"",
					$condicion,
					""
					);
			return $data;
		}

		function reporteVentas($txtFechaAprobadoInicio,$txtFechaAprobadoFinal,$txtFechaGuiadoInicio,$txtFechaGuiadoFin,$txtFechaDespachoInicio,$txtFechaDespachoFin,$txtFechaCanceladoInicio,$txtFechaCanceladoFin,$idOrdenVenta,$idCliente,$idVendedor,$idpadre,$idcategoria,$idzona,$condicion,$aprobados,$desaprobados,$pendiente,$idmoneda){
			$filtro=" ov.estado=1 ";
			$filtro.=!empty($txtFechaAprobadoInicio)?" and ov.faprobado>='$txtFechaAprobadoInicio' ":"";
			$filtro.=!empty($txtFechaAprobadoFinal)?" and ov.faprobado<='$txtFechaAprobadoFinal' ":"";
			$filtro.=!empty($txtFechaGuiadoInicio)?" and ov.fordenventa>='$txtFechaGuiadoInicio' ":"";
			$filtro.=!empty($txtFechaGuiadoFin)?" and ov.fordenventa<='$txtFechaGuiadoFin' ":"";
			$filtro.=!empty($txtFechaDespachoInicio)?" and ov.fechadespacho>='$txtFechaDespachoInicio' ":"";
			$filtro.=!empty($txtFechaDespachoFin)?" and ov.fechadespacho<='$txtFechaDespachoFin' ":"";
			$filtro.=!empty($txtFechaCanceladoInicio)?" and ov.fechaCancelado>='$txtFechaCanceladoInicio' ":"";
			$filtro.=!empty($txtFechaCanceladoFin)?" and ov.fechaCancelado<='$txtFechaCanceladoFin' ":"";
			$filtro.=!empty($idOrdenVenta)?" and ov.idordenventa='$idOrdenVenta' ":"";
			$filtro.=!empty($idCliente)?" and c.idcliente='$idCliente' ":"";
			$filtro.=!empty($idVendedor)?" and ov.idvendedor='$idVendedor' ":"";
			$filtro.=!empty($idpadre)?" and ct.idpadrec='$idpadre' ":"";
			$filtro.=!empty($idcategoria)?" and ct.idcategoria='$idcategoria' ":"";
			$filtro.=!empty($idzona)?" and z.idzona='$idzona' ":"";
			$filtro.=!empty($condicion)?$condicion:"";
			$filtro.=!empty($aprobados)?" and ov.vbcreditos=1 ":"";
			$filtro.=!empty($desaprobados)?" and ov.desaprobado='1' ":"";
			$filtro.=!empty($pendiente)?" and ov.situacion='Pendiente' ":"";

			$data=$this->leeRegistro(
					"wc_ordenventa ov 
					
					inner join wc_clientezona cz ON cz.idclientezona=ov.idclientezona
					inner join wc_cliente c ON c.idcliente=cz.idcliente
					inner join wc_zona z ON z.idzona=cz.idzona
					inner join wc_categoria ct ON ct.idcategoria=z.idcategoria
					inner join wc_actor a ON a.idactor=ov.idvendedor
					inner join wc_tipocambio tc On ov.fordenventa=tc.fechatc
					inner join wc_moneda mn ON ov.idmoneda=mn.idmoneda					
					",
					"*,ov.situacion as estadoov,mn.simbolo,tc.venta,tc.compra,ov.MontoTipoCambioVigente,
					case ".$idmoneda." when 1 then ov.importeov*MontoTipoCambioVigente when 2 then ov.importeov*tc.compra end as importeov1",
					$filtro,
					"",
					"group by ov.idordenventa"
					);
                        
			return $data;
		}

		function reporteProductoAgotados($idLinea,$idSubLinea,$idMarca,$idAlmacen,$idProducto,$fechaInicio,$fechaFinal){
			$filtro=" p.estado=1 and p.stockactual<=0 ";
			$filtro.=!empty($idLinea)?" and li.idpadre='$idLinea' ":"";
			$filtro.=!empty($idSubLinea)?" and p.idlinea='$idSubLinea' ":"";
			$filtro.=!empty($idMarca)?" and p.idmarca='$idMarca' ":"";
			$filtro.=!empty($idAlmacen)?" and p.idalmacen='$idAlmacen' ":"";
			$filtro.=!empty($idProducto)?" and p.idproducto='$idProducto' ":"";
			$filtro.=!empty($fechaInicio)?" and DATE(p.fechaagotado)>='$fechaInicio' ":"";
			$filtro.=!empty($fechaFinal)?" and DATE(p.fechaagotado)<='$fechaFinal' ":"";
			
			$data=$this->leeRegistro(
					"wc_producto p 
					left join wc_almacen a on p.idalmacen=a.idalmacen
					left join wc_linea li on li.idlinea=p.idlinea
					left join wc_marca m on m.idmarca=p.idmarca
					",
					"",
					$filtro,
					"",
					""
					);
			return $data;

		}
		function reporteProductoVendidos($idLinea,$idSubLinea,$idMarca,$idAlmacen,$idProducto,$fechaInicio,$fechaFinal){
			$filtro=" p.estado=1 and ov.esguiado=1 ";
			$filtro.=!empty($idLinea)?" and li.idpadre='$idLinea' ":"";
			$filtro.=!empty($idSubLinea)?" and p.idlinea='$idSubLinea' ":"";
			$filtro.=!empty($idMarca)?" and p.idmarca='$idMarca' ":"";
			$filtro.=!empty($idAlmacen)?" and p.idalmacen='$idAlmacen' ":"";
			$filtro.=!empty($idProducto)?" and p.idproducto='$idProducto' ":"";
			$filtro.=!empty($fechaInicio)?" and ov.fordenventa>='$fechaInicio' ":"";
			$filtro.=!empty($fechaFinal)?" and ov.fordenventa<='$fechaFinal' ":"";
			
			$data=$this->leeRegistro(
					"wc_producto p 
					left join wc_almacen a on p.idalmacen=a.idalmacen
					left join wc_linea li on li.idlinea=p.idlinea
					left join wc_marca m on m.idmarca=p.idmarca
					inner join wc_detalleordenventa dov on dov.idproducto=p.idproducto
					inner join wc_ordenventa ov on ov.idordenventa=dov.idordenventa
					",
					"*,sum(dov.cantdespacho) as cantidadvendida",
					$filtro,
					"",
					"group by dov.idproducto order by cantidadvendida desc"
					);
			return $data;
		}

		function reporteInventario($idInventario="",$idBloque="",$idProducto=""){
			$filtro=" di.estado=1";
			$filtro.=!empty($idInventario)?" and di.idinventario='$idInventario' ":"";
			$filtro.=!empty($idBloque)?" and di.idbloque='$idBloque' ":"";
			$filtro.=!empty($idProducto)?" and di.idproducto='$idProducto' ":"";
			
			$data=$this->leeRegistro(
					"wc_detalleinventario di
					inner join wc_producto p on di.idproducto=p.idproducto
					inner join wc_inventario i on i.idinventario=di.idinventario
					inner join wc_bloques b on b.idbloque=di.idbloque
					",
					"*,di.horainicio,di.horatermino,di.stockactual as stockinventario",
					$filtro,
					"",
					"order by di.idbloque,di.iddetalleinventario"
					);
			return $data;
		}
		
		function reporteOrdenCompraRevision($idOrdenCompra,$idProducto=''){
			$condicion="oc.estado=1 and doc.estado=1 and oc.registrado=1  ";
			if (!empty($idProducto) && empty($idOrdenCompra) ) {
				$condicion.=" and  oc.idordencompra=(select max(oc.idordencompra) from wc_ordencompra oc inner join wc_detalleordencompra doc on oc.idordencompra=doc.idordencompra where doc.idproducto='$idProducto')";
			}else if(!empty($idProducto) && !empty($idOrdenCompra)){
				$condicion.=" and doc.idproducto='$idProducto' and oc.idordencompra='$idOrdenCompra' ";
			}else if(empty($idProducto) && !empty($idOrdenCompra)){
			
				$condicion.=!empty($idOrdenCompra)?" and oc.idordencompra='$idOrdenCompra' ":"";
			}
			
			
			$data=$this->leeRegistro("wc_ordencompra as oc 
									 inner join wc_detalleordencompra as doc on oc.idordencompra=doc.idordencompra 
									 inner join wc_producto as p on p.idproducto=doc.idproducto 
									 left join wc_unidadmedida as u on u.idunidadmedida=p.unidadmedida ",
									 "",
									 $condicion,
									 "",
									 "order by oc.idordencompra,doc.iddetalleordencompra");
			return $data;
		}
		function historialProducto($idProducto){
			$condicion="oc.estado=1 and doc.estado=1 and oc.registrado=1  ";
			$condicion.=!empty($idProducto)?" and doc.idproducto='$idProducto' ":"";
			$data=$this->leeRegistro("wc_ordencompra as oc
									 inner join wc_detalleordencompra as doc on oc.idordencompra=doc.idordencompra
									 inner join wc_producto as p on p.idproducto=doc.idproducto
									 left join wc_unidadmedida as u on u.idunidadmedida=p.unidadmedida ",
					"",
					$condicion,
					"",
					"order by oc.idordencompra");
			return $data;
		}
		function carteraClientes($idLinea,$idZona,$idPadre,$idCategoria,$idVendedor,$idDepartamento,$idProvincia,$idDistrito,$fechaInicio,$fechaFin) {
			$sql="Select razonsocial from wc_cliente c
			inner join wc_clientelinea cl On c.idcliente=cl.idcliente
			inner join wc_linea lin On cl.idlinea=lin.idlinea
			Where cl.idlinea=191
			";
			$condicion="c.estado=1  ";
			$condicion.=!empty($idLinea)?" and cl.idlinea='$idLinea' ":"";
			$condicion.=!empty($idZona)?" and c.zona='$idZona' ":"";
			$condicion.=!empty($idPadre)?" and ct.idcategoria='$idPadre' ":"";
			$condicion.=!empty($idCategoria)?" and ct.idpadrec='$idCategoria' ":"";
			$condicion.=!empty($idVendedor)?" and cv.idvendedor='$idVendedor' ":"";
			$condicion.=!empty($idDepartamento)?" and dp.iddepartamento='$idDepartamento' ":"";
			$condicion.=!empty($idProvincia)?" and pv.idprovincia='$idProvincia' ":"";
			$condicion.=!empty($idDistrito)?" and d.iddistrito='$idDistrito' ":"";
			$condicion.=(!empty($fechaInicio) && !empty($fechaFin))?" and ov.fordenventa between '$fechaInicio' and  '$fechaFin'":"";

			
			$data=$this->leeRegistro("wc_cliente as c 
					inner join wc_clientevendedor as cv on cv.idcliente=c.idcliente
					inner join wc_actor as a  on a.idactor=cv.idvendedor
					inner join wc_clientelinea cl On c.idcliente=cl.idcliente
					inner join wc_linea lin On cl.idlinea=lin.idlinea					
					inner join wc_distrito as d on d.iddistrito=c.iddistrito
					inner join wc_provincia as pv on pv.idprovincia=d.idprovincia
					inner join wc_departamento as dp on dp.iddepartamento=pv.iddepartamento
					left join wc_zona as z on z.idzona=c.zona
					left join wc_ordenventa as ov on ov.idordenventa=c.idultimaorden 
					inner join wc_categoria as ct on ct.idcategoria=z.idcategoria",
					"lin.nomlin,z.nombrezona,z.idzona,c.idcliente,concat(c.telefono,' ',c.celular) as telefono,c.email,c.ruc,c.razonsocial,c.direccion,d.nombredistrito,
					dp.nombredepartamento,a.nombres,a.apellidomaterno,a.apellidopaterno,ov.codigov,ov.fordenventa,ov.importeov ",
					
					$condicion,
					"",
					"order by lin.nomlin,ct.idpadrec,ct.idcategoria,z.idzona,TRIM(c.razonsocial) asc");
			return $data;
		}
		function historialVentasxProducto($idProducto,$idVendedor,$idCliente){
			$condicion="ov.estado=1 and dov.estado=1 and vbcreditos=1 ";
			$condicion.=!empty($idProducto)?" and dov.idproducto='$idProducto' ":"";
			$condicion.=!empty($idVendedor)?" and ov.idvendedor='$idVendedor' ":"";
			$condicion.=!empty($idCliente)?" and ov.idcliente='$idCliente' ":"";
			
			$data=$this->leeRegistro("wc_ordenventa ov
					INNER JOIN wc_detalleordenventa dov ON ov.idordenventa=dov.idordenventa
					INNER JOIN wc_producto p  ON p.idproducto=dov.idproducto
					INNER JOIN wc_cliente c ON ov.idcliente=c.idcliente
					INNER JOIN wc_actor a ON ov.idvendedor=a.idactor
					LEFT JOIN wc_unidadmedida um ON p.unidadmedida=um.idunidadmedida
					LEFT JOIN wc_almacen al ON ov.idalmacen=al.idalmacen",
					"p.codigopa,a.nombres,a.apellidopaterno,a.apellidomaterno,codigoa,c.razonsocial,ov.fordenventa,dov.cantdespacho,um.nombre as nombremedida ,ov.codigov,dov.idproducto,al.codigoalmacen,ov.importeov,dov.preciolista,dov.descuentoaprobadovalor,dov.descuentoaprobadotexto,dov.preciofinal",
					$condicion,
					"",
					"order by ov.fordenventa,ov.idordenventa ");
			return $data;
		}
		function cobranzaxEmpresa($filtro="",$idzona="",$idcategoriaprincipal="",$idcategoria="",$idvendedor="",$idtipocobranza="",$fechainicio="",$fechafinal="",$situacion="",$idAlmacen=""){
			$condicion="wc_ordenventa.`estado`=1 and wc_detalleordencobro.`estado`=1 and wc_documento.`esAnulado`=0 and (wc_documento.`nombredoc`=2 or wc_documento.`nombredoc`=1) ";
			
			$condicion.=!empty($idzona)?" and wc_zona.`idzona`='$idzona' ":"";
			$condicion.=!empty($idcategoriaprincipal)?" and wc_categoria.`idpadrec`='$idcategoriaprincipal' ":"";
			$condicion.=!empty($idcategoria)?" and wc_categoria.`idcategoria`='$idcategoria' ":"";
			$condicion.=!empty($idvendedor)?" and wc_actor.`idactor`='$idvendedor' ":"";
			$condicion.=!empty($idtipocobranza)?" and wc_ordenventa.`idtipocobranza`='$idtipocobranza' ":"";
			$condicion.=!empty($fechainicio)?" and wc_detalleordencobro.`fvencimiento`>='$fechainicio' ":"";
			$condicion.=!empty($fechafinal)?" and wc_detalleordencobro.`fvencimiento`<='$fechafinal' ":"";
			$condicion.=!empty($situacion)?$situacion:"";
			$condicion.=!empty($idAlmacen)?" and wc_ordenventa.`idalmacen`='$idAlmacen' ":"";
			$condicion.=!empty($filtro)?" and  ".$filtro." ":"";
			
			$data=$this->leeRegistro(
					"`wc_ordenventa` wc_ordenventa INNER JOIN `wc_clientezona` wc_clientezona ON wc_ordenventa.`idclientezona` = wc_clientezona.`idclientezona`
				     INNER JOIN `wc_actor` wc_actor ON wc_ordenventa.`idvendedor` = wc_actor.`idactor`
				     LEFT JOIN  `wc_documento` wc_documento ON wc_ordenventa.`idordenventa`=wc_documento.`idordenventa`
				     INNER JOIN `wc_cliente` wc_cliente ON wc_clientezona.`idcliente` = wc_cliente.`idcliente`
				     INNER JOIN `wc_zona` wc_zona ON wc_clientezona.`idzona` = wc_zona.`idzona`
				     LEFT JOIN `wc_almacen` wc_almacen ON wc_ordenventa.`idalmacen` = wc_almacen.`idalmacen`
				     INNER JOIN `wc_categoria` wc_categoria ON wc_zona.`idcategoria` = wc_categoria.`idcategoria`
				     inner join `wc_ordencobro` wc_ordencobro on wc_ordencobro.`idordenventa`=wc_ordenventa.`idordenventa`
				     inner join `wc_detalleordencobro` wc_detalleordencobro on wc_detalleordencobro.`idordencobro`=wc_ordencobro.`idordencobro`",

					"wc_actor.`apellidomaterno`,
				     wc_ordenventa.`codigov`,
				     wc_ordenventa.`idordenventa`,
				     wc_ordenventa.`idtipocobranza`,
				     wc_ordenventa.`fechadespacho`,
				     wc_ordenventa.`fechavencimiento`,
				     wc_ordenventa.`importepagado`,
				     wc_ordenventa.`importedevolucion`,
				     wc_ordenventa.`direccion_envio`,
				     wc_ordenventa.`es_contado`,
				     wc_ordenventa.`es_credito`,
				     wc_ordenventa.`es_letras`,
				     wc_cliente.`idcliente`,
				     wc_cliente.`iddistrito`,
				     wc_cliente.`apellido1`,
				     wc_cliente.`apellido2`,
				     wc_cliente.`direccion`,
				     wc_categoria.`idcategoria`,
				     wc_categoria.`idpadrec`,
				     wc_categoria.`codigoc`,
				     wc_categoria.`nombrec`,
				     wc_actor.`idactor`,
				     wc_documento.`porcentajefactura`,
				     wc_documento.`montofacturado`,
				     wc_documento.`nombredoc`,
				     wc_almacen.`codigoalmacen`,
				     wc_almacen.`nomalm`,
				     wc_cliente.`nombrecli`,
				     wc_cliente.`razonsocial`,
				     wc_zona.`idzona`,
				     wc_zona.`nombrezona`,
				     wc_actor.`codigoa`,
				     wc_actor.`nombres`,
				     wc_actor.`apellidopaterno`,
				     wc_ordencobro.`saldoordencobro`,
				     wc_detalleordencobro.`situacion`,
				     wc_detalleordencobro.`formacobro`,
				     wc_detalleordencobro.`saldodoc`, 
				     wc_detalleordencobro.`importedoc`,
				     wc_detalleordencobro.`numeroletra`,
				     wc_detalleordencobro.`fechagiro`,
				     wc_detalleordencobro.`fvencimiento`,
				     wc_detalleordencobro.`gastosrenovacion`,
				     wc_detalleordencobro.`recepcionLetras`",
					$condicion,
					"wc_ordenventa.`fechavencimiento`,wc_ordenventa.`idordenventa` desc"
					);
			return $data;
		}
		function rankingVendedor($txtFechaAprobadoInicio,$txtFechaAprobadoFinal,$txtFechaGuiadoInicio,$txtFechaGuiadoFin,$txtFechaDespachoInicio,$txtFechaDespachoFin,$txtFechaCanceladoInicio,$txtFechaCanceladoFin,$idOrdenVenta,$idCliente,$idVendedor,$idpadre,$idcategoria,$idzona,$condicion,$aprobados,$desaprobados,$pendiente,$idmoneda){
			$filtro=" ov.estado=1 ";
			$filtro.=!empty($txtFechaAprobadoInicio)?" and ov.faprobado>='$txtFechaAprobadoInicio' ":"";
			$filtro.=!empty($txtFechaAprobadoFinal)?" and ov.faprobado<='$txtFechaAprobadoFinal' ":"";
			$filtro.=!empty($txtFechaGuiadoInicio)?" and ov.fordenventa>='$txtFechaGuiadoInicio' ":"";
			$filtro.=!empty($txtFechaGuiadoFin)?" and ov.fordenventa<='$txtFechaGuiadoFin' ":"";
			$filtro.=!empty($txtFechaDespachoInicio)?" and ov.fechadespacho>='$txtFechaDespachoInicio' ":"";
			$filtro.=!empty($txtFechaDespachoFin)?" and ov.fechadespacho<='$txtFechaDespachoFin' ":"";
			$filtro.=!empty($txtFechaCanceladoInicio)?" and ov.fechaCancelado>='$txtFechaCanceladoInicio' ":"";
			$filtro.=!empty($txtFechaCanceladoFin)?" and ov.fechaCancelado<='$txtFechaCanceladoFin' ":"";
			$filtro.=!empty($idOrdenVenta)?" and ov.idordenventa='$idOrdenVenta' ":"";
			$filtro.=!empty($idCliente)?" and c.idcliente='$idCliente' ":"";
			$filtro.=!empty($idVendedor)?" and ov.idvendedor='$idVendedor' ":"";
			$filtro.=!empty($idpadre)?" and ct.idpadrec='$idpadre' ":"";
			$filtro.=!empty($idcategoria)?" and ct.idcategoria='$idcategoria' ":"";
			$filtro.=!empty($idzona)?" and z.idzona='$idzona' ":"";
			$filtro.=!empty($condicion)?$condicion:"";
			$filtro.=!empty($aprobados)?" and ov.vbcreditos=1 ":"";
			$filtro.=!empty($desaprobados)?" and ov.desaprobado='1' ":"";
			$filtro.=!empty($pendiente)?" and ov.situacion='Pendiente' ":"";

			$data=$this->leeRegistro(
					"wc_ordenventa ov 
					inner join wc_clientezona cz ON cz.idclientezona=ov.idclientezona
					inner join wc_cliente c ON c.idcliente=cz.idcliente
					inner join wc_zona z ON z.idzona=cz.idzona
					inner join wc_categoria ct ON ct.idcategoria=z.idcategoria
					inner join wc_actor a ON a.idactor=ov.idvendedor
					inner join wc_tipocambio tc ON ov.fordenventa=tc.fechatc
					inner join wc_moneda mn ON ov.IdMoneda=mn.idmoneda
					",
					"ov.codigov,ov.idvendedor,a.nombres,a.apellidopaterno,a.apellidomaterno,ct.idpadrec,ov.situacion as estadoov,ov.es_contado,ov.es_credito,ov.es_letras,
					ov.tipo_letra,
					tc.venta,mn.simbolo,tc.compra,
					case concat(".$idmoneda.",ov.IdMoneda) when 11 then ov.importepagado when 12 then ov.importepagado*tc.venta when 21 then ov.importepagado/tc.venta when 22 then ov.importepagado end as importepagado,
					case concat(".$idmoneda.",ov.IdMoneda) when 11 then ov.importedevolucion when 12 then ov.importedevolucion*tc.venta when 21 then ov.importedevolucion/tc.venta when 22 then ov.importedevolucion end as importedevolucion,
					case concat(".$idmoneda.",ov.IdMoneda) when 11 then ov.importeov when 12 then ov.importeov*tc.venta when 21 then ov.importeov/tc.venta when 22 then ov.importeov end as importeov
					",
					$filtro,
					"",
					"order by ov.idvendedor"
					);
			return $data;
		}
		
		function reporteFacturacion($txtFechaInicio,$txtFechaFinal,$idVendedor,$idOrdenVenta,$idCliente,$idTipodoc,$lstSituacion,$orden){
			$filtro=" ov.estado=1 and d.estado=1 and d.esanulado!=1 and (nombredoc=1 or nombredoc=2) ";
			$filtro.=!empty($txtFechaInicio)?" and d.fechadoc>='$txtFechaInicio' ":"";
			$filtro.=!empty($txtFechaFinal)?" and d.fechadoc<='$txtFechaFinal' ":"";
			$filtro.=!empty($idVendedor)?" and ov.idvendedor='$idVendedor' ":"";
			$filtro.=!empty($idOrdenVenta)?" and ov.idordenventa='$idOrdenVenta' ":"";
			$filtro.=!empty($idCliente)?" and ov.idcliente='$idCliente' ":"";
			$filtro.=!empty($idTipodoc)?" and d.nombredoc='$idTipodoc' ":"";
			$filtro.=!empty($lstSituacion)?" and ov.situacion='$lstSituacion' ":"";
			$order=!empty($orden)?(" order by ".$orden.""):"";

			$data=$this->leeRegistro(
					"wc_ordenventa ov 
					inner join wc_cliente c ON c.idcliente=ov.idcliente
					inner join wc_actor a ON a.idactor=ov.idvendedor
					inner join wc_almacen al ON al.idalmacen=ov.idalmacen
					left join wc_documento d ON ov.idordenventa=d.idordenventa
					",
					"ov.codigov,d.serie,d.numdoc,d.fechadoc,al.codigoalmacen,d.nombredoc,d.montofacturado,d.porcentajefactura,d.modofactura,c.razonsocial,a.nombres,a.apellidopaterno,a.apellidomaterno,ov.situacion",
					$filtro,
					"",
					$order
					);
			return $data;
		}
		function reporteKardexProduccion($txtFechaInicio,$txtFechaFinal,$idProducto,$idTipoMovimiento,$idTipoOperacion){
			$filtro="  dm.estado=1 and m.estado=1 ";
			$filtro.=!empty($txtFechaInicio)?" and m.fechamovimiento>='$txtFechaInicio' ":"";
			$filtro.=!empty($txtFechaFinal)?" and m.fechamovimiento<='$txtFechaFinal' ":"";
			$filtro.=!empty($idProducto)?" and dm.idproducto='$idProducto' ":"";
			$filtro.=!empty($idTipoOperacion)?" and m.conceptomovimiento='$idTipoOperacion' ":"";
			$filtro.=!empty($idTipoMovimiento)?" and m.tipomovimiento='$idTipoMovimiento' ":"";
			
			$order="Order By m.fechamovimiento,m.idmovimiento asc";

			$data=$this->leeRegistro(
					"wc_detallemovimiento dm
					Inner Join wc_movimiento m On dm.idmovimiento=m.idmovimiento
					Left Join wc_ordenventa ov On ov.idordenventa=m.idordenventa
					Left Join wc_ordencompra oc On oc.idordencompra=m.idordencompra
					Inner Join wc_movimientotipo mt On m.tipomovimiento=mt.idmovimientotipo
					left Join wc_cliente c On ov.idcliente=c.idcliente
					left Join wc_proveedor p On oc.idproveedor=p.idproveedor
					left Join wc_devolucion d On m.iddevolucion=d.iddevolucion
					left join wc_tipooperacion tio On tio.idtipooperacion=m.conceptomovimiento
					",
					"ov.codigov,m.fechamovimiento as Fecha,mt.nombre as 'Tipo Movimiento',tio.nombre as 'Concepto Movimiento',
					CASE WHEN ov.codigov<>'Null' Then c.razonsocial WHEN oc.codigooc<>'Null' Then p.razonsocialp Else 'Mov. Interno' END as 'Razon Social',
					CASE WHEN m.iddevolucion<>0 THEN 'Devolucion' ELSE ' ' END as Devolucion,
					dm.pu as 'Precio',dm.cantidad,ROUND(dm.stockactual,0) as Saldo,dm.importe as 'Monto'",
					$filtro,
					"",
					$order
					);
			return $data;
			
		}
	}
	
?>

<?php  
Class IngresosController extends ApplicationGeneral{

	function NuevoRegistro(){
		$Actor=New Actor;
		$data['tipoIngreso']=$this->configIniTodo('TipoIngreso');
		$data['cobrador']=$Actor->listadoCobradores();
		$Ingresos=New Ingresos();
		$banco=$this->AutoLoadModel('banco');
		$data['dataBanco']=$banco->listado();
		$data['ingresos']=$Ingresos->listarxhoy();
		$this->view->show("/ingresos/nuevo.phtml",$data);
	}

	function NuevoDietario(){
		$Actor=New Actor;
		$letras=New Letras();
		$data['cobrador']=$Actor->listadoCobradores();
		$Ingresos=New Ingresos();
		$data['letras']=$letras->listado();
		$data['ingresos']=$Ingresos->listarxhoy();
		$this->view->show("/ingresos/nuevodietario.phtml",$data);
	}

	function Registrar(){
		$ingreso=$_REQUEST['Ingreso'];
		$ingreso['saldo']=round($ingreso['montoingresado'],2);
		$ingreso['saldo']=$ingreso['montoingresado'];
		$objIngreso=New Ingresos();
		$graba=$objIngreso->graba($ingreso);
		$ruta['ruta'] = "/ingresos/nuevoregistro/";
		$this->view->show("ruteador.phtml", $ruta);

	}
	//zona de dietario
	function pago(){
		$idordenventa=$_REQUEST['idordenventa'];
		$idcliente=$_REQUEST['idcliente'];
		$idcobrador=$_REQUEST['idcobrador'];
		$monto=round($_REQUEST['monto'],2);
		$formacobro=$_REQUEST['formacobro'];
		$iddetalleordencobro=$_REQUEST['iddetalleordencobro'];
		$numeroletra=$_REQUEST['numeroletra'];
		$numerorecibo=$_REQUEST['numerorecibo'];
		$fechapago=$_REQUEST['fechapago'];
		$ordencobro=$this->AutoLoadModel('ordencobro');
		$detOrdenCobro=$this->AutoLoadModel('detalleordencobro');
		$doci=$this->AutoLoadModel('detalleordencobroingreso');
		$objIngreso=New Ingresos();
		//recuperamos los datos del detalleordencobro como el idordencobro
		$datadetordencobro=$detOrdenCobro->buscaDetalleOrdencobro($iddetalleordencobro);
		$idordencobro=$datadetordencobro[0]['idordencobro'];
		$monto=round($datadetordencobro[0]['importedoc'],2);

		//recuperamos algunos datos del antiguo ordencobro
		$ordencobroantiguo=$ordencobro->buscaOrdencobro($idordencobro);
		$saldoordencobroA=round($ordencobroantiguo[0]['saldoordencobro'],2);
		//registra un nuevo ingreso
		$ingreso['idordenventa']=$idordenventa;
		$ingreso['idcliente']=$idcliente;
		$ingreso['idcobrador']=$idcobrador;
		$ingreso['montoingresado']=$monto;
		$ingreso['montoasignado']=$monto;
		$ingreso['saldo']=0;
		$ingreso['esvalidado']=1;
		$ingreso['tipocobro']=9;
		$ingreso['nrorecibo']=$numerorecibo;
		$ingreso['nrodoc']=$numeroletra;
		$ingreso['fcobro']=date('Y-m-d',strtotime($fechapago));
		$graba=$objIngreso->graba($ingreso);

		if ($graba) {
			//actualiza detalleOrdenCobro
			$data['situacion']='cancelado';
			$data['fechapago']=date('Y-m-d',strtotime($fechapago));
			$data['saldodoc']=0;
			$exito=$detOrdenCobro->actualizaDetalleOrdencobro($data,$iddetalleordencobro);
			//Registramos un detalleordencobroingreso
			$data2['iddetalleordencobro']=$iddetalleordencobro;
			$data2['idingreso']=$graba;
			$data2['montop']=$monto;
			$exito2=$doci->grabadetalleordencobroingreso($data2);
			if ($exito2 && $exito) {
				//actualizamos el orden de cobro
				$dataoc['saldoordencobro']=$saldoordencobroA-$monto;
				if ($dataoc['saldoordencobro']<0.1) {
					$dataoc['situacion']="cancelado";
				}

				$exito2=$ordencobro->actualizaOrdencobro($dataoc,$idordencobro);
				echo $exito2;
			}else{
				echo 'segundo error';
			}
		}else{
			echo 'primer error';
		}
	}
	function protestar(){
		$idordenventa=$_REQUEST['idordenventa'];
		$idcliente=$_REQUEST['idcliente'];
		$idcobrador=$_REQUEST['idcobrador'];
		$monto=$_REQUEST['monto'];
		$formacobro=$_REQUEST['formacobro'];
		$iddetalleordencobro=$_REQUEST['iddetalleordencobro'];
		$numeroletra=$_REQUEST['numeroletra'];
		$fecha=$_REQUEST['fecha'];
		$dias=$_REQUEST['dias'];
		$montoadicional=round($_REQUEST['montoadicional'],2);
		$pagoAcuenta=round($_REQUEST['pagoAcuenta'],2);
		if (empty($pagoAcuenta)) {
			$pagoAcuenta=0;
		}
		//gasto adicionales
		$flete=$_REQUEST['flete'];
		if (empty($flete)) {
			$flete=0;
		}else{
			$flete=$this->trucarNumeros($flete,2);
		}
		$envioSobre=$_REQUEST['envioSobre'];
		if (empty($envioSobre)) {
			$envioSobre=0;
		}else{
			$envioSobre=$this->trucarNumeros($envioSobre,2);
		}
		$gastoBancario=$_REQUEST['gastoBancario'];
		if (empty($gastoBancario)) {
			$gastoBancario=0;
		}else{
			$gastoBancario=$this->trucarNumeros($gastoBancario,2);
		}
		$costoMantenimiento=$_REQUEST['costoMantenimiento'];
		if (empty($costoMantenimiento)) {
			$costoMantenimiento=0;
		}else{
			$costoMantenimiento=$this->trucarNumeros($costoMantenimiento,2);
		}
		/********/

		$ordencobro=$this->AutoLoadModel('ordencobro');
		$detOrdenCobro=$this->AutoLoadModel('detalleordencobro');
		$documento=$this->AutoLoadModel('documento');
		$ordenGasto=$this->AutoLoadModel('ordengasto');

		//verificamos que la orden de venta tenga factura y si tiene generamos una nota debito
		$busquedaDocumento=$documento->listaDocumentosSinAnulados($idordenventa,1);
		if (!empty($busquedaDocumento)) {
			$datadocumento['idordenventa']=$idordenventa;
			$datadocumento['nombredoc']=6;
			$datadocumento['fechadoc']=date('Y-m-d');
			$datadocumento['montofacturado']=$montoadicional;
			$datadocumento['concepto']=2;//2 es cuando es protesto y 1 cuando es renovacion
			$exitoD=$documento->grabaDocumento($datadocumento);
			if (!$exitoD) {
				echo 'Error al grabar el debito';
				exit;
			}
		}

		//recuperamos los datos del detalleordencobro como el idordencobro
		$datadetordencobro=$detOrdenCobro->buscaDetalleOrdencobro($iddetalleordencobro);
		$idordencobro=$datadetordencobro[0]['idordencobro'];
		$fvencimientoA=$datadetordencobro[0]['fvencimiento'];
		$renovadoA=$datadetordencobro[0]['renovado'];
		$monto=round($datadetordencobro[0]['importedoc'],2);
		$fechaActual=date('Y-m-d');
		$montorenovado=0;
		//verificamos si la letra viene de una renovacion
		if ($renovadoA!=0) {
			//buscamos en su orden de cobro todos sus item que tenga renovado!=0 y recuperamos el importe antes que se renueve
			$databusqueda=$detOrdenCobro->listadoxidOrdenCobroxrenovado($idordencobro);
			$cantidadbusqueda=count($databusqueda);
			if ($databusqueda>1) {
				for ($i=0; $i <$cantidadbusqueda ; $i++) { 
					$idoc=$databusqueda[$i]['iddetalleordencobro'];
					$montorenovado+=$databusqueda[$i]['importedoc'];
					//actualizamos los detalles de ordencobro
					$informacion['saldodoc']=0;
					$informacion['situacion']='protestado';
					$informacion['protesto']='1';
					$exito=$detOrdenCobro->actualizaDetalleOrdencobro($informacion,$idoc);
				}
				$monto=round($montorenovado,2);
			}
		}
		if (!empty($pagoAcuenta) && $pagoAcuenta!=0) {
			$monto=$monto-$pagoAcuenta;
		}
		//recuperamos algunos datos del antiguo ordencobro
		$ordencobroantiguo=$ordencobro->buscaOrdencobro($idordencobro);
		$saldoordencobroA=$ordencobroantiguo[0]['saldoordencobro'];
		//creamos una nueva orden de cobro

		$dataNuevo['situacion']="Pendiente";
		$dataNuevo['importeordencobro']=$monto+$pagoAcuenta+$montoadicional+$flete+$envioSobre+$gastoBancario+$costoMantenimiento;
		$dataNuevo['idordenventa']=$idordenventa;
		$dataNuevo['femision']=date('Y-m-d');
		$dataNuevo['escredito']="1";
		$dataNuevo['saldoordencobro']=$monto+$pagoAcuenta+$montoadicional+$flete+$envioSobre+$gastoBancario+$costoMantenimiento;
		
		$Nuevoidcobro=$ordencobro->grabaOrdencobro($dataNuevo);

		//creamos su detalle de orden de cobro
		if ($Nuevoidcobro) {
			
			if (!empty($flete) && $flete!=0) {
				$datosFlete['idordencobro']=$Nuevoidcobro;
				$datosFlete['importedoc']=$flete;
				$datosFlete['saldodoc']=$flete;
				$datosFlete['tipogasto']=3;
				$datosFlete['formacobro']=2;
				$datosFlete['referencia']=$numeroletra.'P';
				$datosFlete['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $dias . " day"));
				$datosFlete['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datosFlete['idpadre']=$iddetalleordencobro;
				$grabaFlete=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datosFlete);
				
				$filtro="idordenventa='$idordenventa' and idtipogasto=3";
				$dataOrdenGastoFlete=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoFlete)){
					$dataF['importegasto']=$flete+$dataOrdenGastoFlete[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataF,$dataOrdenGastoFlete[0]['idordengasto']);
				}else{
					$dataF['importegasto']=$flete;
					$dataF['idordenventa']=$idordenventa;
					$dataF['idtipogasto']=3;
					$exitoF=$ordenGasto->graba($dataF);
				}
				
			}

			if (!empty($envioSobre) && $envioSobre!=0) {
				$datosEnvioSobre['idordencobro']=$Nuevoidcobro;
				$datosEnvioSobre['importedoc']=$envioSobre;
				$datosEnvioSobre['saldodoc']=$envioSobre;
				$datosEnvioSobre['tipogasto']=5;
				$datosEnvioSobre['formacobro']=2;
				$datosEnvioSobre['referencia']=$numeroletra.'P';
				$datosEnvioSobre['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $dias . " day"));
				$datosEnvioSobre['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datosEnvioSobre['idpadre']=$iddetalleordencobro;
				$grabaEnvioSobre=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datosEnvioSobre);
				
				$filtro="idordenventa='$idordenventa' and idtipogasto=5";
				$dataOrdenGastoEnvio=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoEnvio)){
					$dataE['importegasto']=$envioSobre+$dataOrdenGastoEnvio[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataE,$dataOrdenGastoEnvio[0]['idordengasto']);
				}else{
					$dataE['importegasto']=$envioSobre;
					$dataE['idordenventa']=$idordenventa;
					$dataE['idtipogasto']=5;
					$exitoF=$ordenGasto->graba($dataE);
				}
			}
			if (!empty($gastoBancario) && $gastoBancario!=0) {
				$datosGastoBancario['idordencobro']=$Nuevoidcobro;
				$datosGastoBancario['importedoc']=$gastoBancario;
				$datosGastoBancario['saldodoc']=$gastoBancario;
				$datosGastoBancario['tipogasto']=4;
				$datosGastoBancario['formacobro']=2;
				$datosGastoBancario['referencia']=$numeroletra.'P';
				$datosGastoBancario['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $dias . " day"));
				$datosGastoBancario['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datosGastoBancario['idpadre']=$iddetalleordencobro;
				$grabaGastoBancario=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datosGastoBancario);
				
				$filtro="idordenventa='$idordenventa' and idtipogasto=4";
				$dataOrdenGastoBancario=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoBancario)){
					$dataB['importegasto']=$gastoBancario+$dataOrdenGastoBancario[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataB,$dataOrdenGastoBancario[0]['idordengasto']);
				}else{
					$dataB['importegasto']=$gastoBancario;
					$dataB['idordenventa']=$idordenventa;
					$dataB['idtipogasto']=4;
					$exitoF=$ordenGasto->graba($dataB);
				}
			}
			if (!empty($costoMantenimiento) && $costoMantenimiento!=0) {
				$datosCostoMantenimiento['idordencobro']=$Nuevoidcobro;
				$datosCostoMantenimiento['importedoc']=$costoMantenimiento;
				$datosCostoMantenimiento['saldodoc']=$costoMantenimiento;
				$datosCostoMantenimiento['tipogasto']=8;
				$datosCostoMantenimiento['formacobro']=2;
				$datosCostoMantenimiento['referencia']=$numeroletra.'P';
				$datosCostoMantenimiento['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $dias . " day"));
				$datosCostoMantenimiento['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datosCostoMantenimiento['idpadre']=$iddetalleordencobro;
				$grabaCostoMantenimiento=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datosCostoMantenimiento);
				
				$filtro="idordenventa='$idordenventa' and idtipogasto=8";
				$dataOrdenGastoMantenimiento=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoMantenimiento)){
					$dataM['importegasto']=$costoMantenimiento+$dataOrdenGastoMantenimiento[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataM,$dataOrdenGastoMantenimiento[0]['idordengasto']);
				}else{
					$dataM['importegasto']=$costoMantenimiento;
					$dataM['idordenventa']=$idordenventa;
					$dataM['idtipogasto']=8;
					$exitoF=$ordenGasto->graba($dataM);
				}
			}
			if (!empty($pagoAcuenta) && $pagoAcuenta!=0) {
				$datosPagoAcuenta['idordencobro']=$Nuevoidcobro;
				$datosPagoAcuenta['importedoc']=$pagoAcuenta;
				$datosPagoAcuenta['saldodoc']=$pagoAcuenta;
				$datosPagoAcuenta['formacobro']=1;
				$datosPagoAcuenta['referencia']=$numeroletra.'P';
				$datosPagoAcuenta['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $dias . " day"));
				$datosPagoAcuenta['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datosPagoAcuenta['idpadre']=$iddetalleordencobro;
				$grabaPagoAcuenta=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datosPagoAcuenta);
			}


			$datos['idordencobro']=$Nuevoidcobro;
			$datos['saldodoc']=$monto+$montoadicional;
			$datos['importedoc']=$monto+$montoadicional;
			$datos['formacobro']='2';
			$datos['protesto']='2';//0 -no ha sido protestado , 1 - ha sido protestado ,2 viene de un protesto
			$datos['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
			$datos['situacion']='';
			$datos['referencia']=$numeroletra.'P';
			$datos['tipogasto']=2;
			$datos['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $dias . " day"));
			$datos['montoprotesto']=$montoadicional;
			$datos['idpadre']=$iddetalleordencobro;
			
			$graba=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datos);
			
			$filtro="idordenventa='$idordenventa' and idtipogasto=2";
			$dataOrdenGastoProtesto=$ordenGasto->buscaxFiltro($filtro);
			if (!empty($dataOrdenGastoProtesto)){
				$dataP['importegasto']=$montoadicional+$dataOrdenGastoProtesto[0]['importegasto'];
				$exitoF=$ordenGasto->actualiza($dataP,$dataOrdenGastoProtesto[0]['idordengasto']);
			}else{
				$dataP['importegasto']=$montoadicional;
				$dataP['idordenventa']=$idordenventa;
				$dataP['idtipogasto']=2;
				$exitoF=$ordenGasto->graba($dataP);
			}

			if ($graba) {
				if ($renovadoA!=0) {
					
				}else{
					$data['protesto']='1';
					$data['situacion']='protestado';
					$data['saldodoc']=0;
					
					$exito=$detOrdenCobro->actualizaDetalleOrdencobro($data,$iddetalleordencobro);
				}
				
				if ($exito) {
					//Actualizamos la orden de cobro
					$dataoc['saldoordencobro']=$saldoordencobroA-$monto;
					if ($dataoc['saldoordencobro']<0.1) {
						$dataoc['situacion']="cancelado";
					}
					
					$exito2=$ordencobro->actualizaOrdencobro($dataoc,$idordencobro);
					echo $exito2;
				}else{
					echo 'segundo error';
				}
			}else{
				echo 'primer error';
			}
		}else{
			echo 'tercer error';
		}	
	}

	function refinanciar(){
		$idordenventa=$_REQUEST['idordenventa'];
		$idcliente=$_REQUEST['idcliente'];
		$idcobrador=$_REQUEST['idcobrador'];
		$monto=round($_REQUEST['monto'],2);
		$formacobro=$_REQUEST['formacobro'];
		$iddetalleordencobro=$_REQUEST['iddetalleordencobro'];
		$numeroletra=$_REQUEST['numeroletra'];
		$fecha=$_REQUEST['fecha'];
		$dias=$_REQUEST['dias'];
		$montoadicional=round($_REQUEST['montoadicional'],2);
		$pagoAcuenta=round($_REQUEST['pagoAcuenta'],2);
		if (empty($pagoAcuenta)) {
			$pagoAcuenta=0;
		}
		//gasto adicionales
		$flete=$_REQUEST['flete'];
		if (empty($flete)) {
			$flete=0;
		}else{
			$flete=$this->trucarNumeros($flete,2);
		}
		$envioSobre=$_REQUEST['envioSobre'];
		if (empty($envioSobre)) {
			$envioSobre=0;
		}else{
			$envioSobre=$this->trucarNumeros($envioSobre,2);
		}
		$gastoBancario=$_REQUEST['gastoBancario'];
		if (empty($gastoBancario)) {
			$gastoBancario=0;
		}else{
			$gastoBancario=$this->trucarNumeros($gastoBancario,2);
		}
		$costoMantenimiento=$_REQUEST['costoMantenimiento'];
		if (empty($costoMantenimiento)) {
			$costoMantenimiento=0;
		}else{
			$costoMantenimiento=$this->trucarNumeros($costoMantenimiento,2);
		}
		/********/

		$ordencobro=$this->AutoLoadModel('ordencobro');
		$detOrdenCobro=$this->AutoLoadModel('detalleordencobro');
		$documento=$this->AutoLoadModel('documento');
		$ordenGasto=$this->AutoLoadModel('ordengasto');

		//verificamos que la orden de venta tenga factura y si tiene generamos una nota debito
		$busquedaDocumento=$documento->listaDocumentosSinAnulados($idordenventa,1);
		if (!empty($busquedaDocumento)) {
			$datadocumento['idordenventa']=$idordenventa;
			$datadocumento['nombredoc']=6;
			$datadocumento['fechadoc']=date('Y-m-d');
			$datadocumento['montofacturado']=$montoadicional;
			$datadocumento['concepto']=2;
			$exitoD=$documento->grabaDocumento($datadocumento);
			if (!$exitoD) {
				echo 'Error al grabar el debito';
				exit;
			}
		}

		//recuperamos los datos del detalleordencobro como el idordencobro
		$datadetordencobro=$detOrdenCobro->buscaDetalleOrdencobro($iddetalleordencobro);
		$idordencobro=$datadetordencobro[0]['idordencobro'];
		$fvencimientoA=$datadetordencobro[0]['fvencimiento'];
		$renovadoA=$datadetordencobro[0]['renovado'];
		$monto=round($datadetordencobro[0]['importedoc'],2);
		$fechaActual=date('Y-m-d');
		$montorenovado=0;
		//verificamos si la letra viene de una renovacion
		if ($renovadoA!=0) {
			//buscamos en su orden de cobro todos sus item que tenga renovado!=0 y recuperamos el importe antes que se renueve
			$databusqueda=$detOrdenCobro->listadoxidOrdenCobroxrenovado($idordencobro);
			$cantidadbusqueda=count($databusqueda);
			if ($databusqueda>1) {
			
				for ($i=0; $i <$cantidadbusqueda ; $i++) { 
					$idoc=$databusqueda[$i]['iddetalleordencobro'];
					$montorenovado+=$databusqueda[$i]['importedoc'];
					//actualizamos los detalles de ordencobro
					$informacion['saldodoc']=0;
					$informacion['situacion']='refinanciado';
					$informacion['protesto']='1';
					$exito=$detOrdenCobro->actualizaDetalleOrdencobro($informacion,$idoc);
				}
				$monto=round($montorenovado,2);
			}
		}
		if (!empty($pagoAcuenta) && $pagoAcuenta!=0) {
			$monto=$monto-$pagoAcuenta;
		}
		//recuperamos algunos datos del antiguo ordencobro
		$ordencobroantiguo=$ordencobro->buscaOrdencobro($idordencobro);
		$saldoordencobroA=$ordencobroantiguo[0]['saldoordencobro'];

		//creamos una nueva orden de cobro
		//$dataNuevo['idcondicionletra']=$ordencobroantiguo[0]['idcondicionletra'];
		$dataNuevo['situacion']="Pendiente";
		$dataNuevo['tipoletra']=1;
		$dataNuevo['idordenventa']=$idordenventa;
		$dataNuevo['femision']=date('Y-m-d');
		$dataNuevo['esletras']="1";
		$dataNuevo['numletras']="1";
		$dataNuevo['importeordencobro']=$monto+$montoadicional+$pagoAcuenta+$flete+$envioSobre+$gastoBancario+$costoMantenimiento;
		$dataNuevo['saldoordencobro']=$monto+$montoadicional+$pagoAcuenta+$flete+$envioSobre+$gastoBancario+$costoMantenimiento;
		$Nuevoidcobro=$ordencobro->grabaOrdencobro($dataNuevo);

		



		if ($Nuevoidcobro) {
			if (!empty($flete) && $flete!=0) {
				$datosFlete['idordencobro']=$Nuevoidcobro;
				$datosFlete['importedoc']=$flete;
				$datosFlete['saldodoc']=$flete;
				$datosFlete['tipogasto']=3;
				$datosFlete['formacobro']=2;
				$datosFlete['referencia']=$numeroletra.'F';
				$datosFlete['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $dias . " day"));
				$datosFlete['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datosFlete['idpadre']=$iddetalleordencobro;
				$grabaFlete=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datosFlete);
				
				$filtro="idordenventa='$idordenventa' and idtipogasto=3";
				$dataOrdenGastoFlete=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoFlete)){
					$dataF['importegasto']=$flete+$dataOrdenGastoFlete[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataF,$dataOrdenGastoFlete[0]['idordengasto']);
				}else{
					$dataF['importegasto']=$flete;
					$dataF['idordenventa']=$idordenventa;
					$dataF['idtipogasto']=3;
					$exitoF=$ordenGasto->graba($dataF);
				}
			}

			if (!empty($envioSobre) && $envioSobre!=0) {
				$datosEnvioSobre['idordencobro']=$Nuevoidcobro;
				$datosEnvioSobre['importedoc']=$envioSobre;
				$datosEnvioSobre['saldodoc']=$envioSobre;
				$datosEnvioSobre['tipogasto']=5;
				$datosEnvioSobre['formacobro']=2;
				$datosEnvioSobre['referencia']=$numeroletra.'F';
				$datosEnvioSobre['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $dias . " day"));
				$datosEnvioSobre['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datosEnvioSobre['idpadre']=$iddetalleordencobro;
				$grabaEnvioSobre=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datosEnvioSobre);
			
				$filtro="idordenventa='$idordenventa' and idtipogasto=5";
				$dataOrdenGastoEnvio=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoEnvio)){
					$dataE['importegasto']=$envioSobre+$dataOrdenGastoEnvio[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataE,$dataOrdenGastoEnvio[0]['idordengasto']);
				}else{
					$dataE['importegasto']=$envioSobre;
					$dataE['idordenventa']=$idordenventa;
					$dataE['idtipogasto']=5;
					$exitoF=$ordenGasto->graba($dataE);
				}
			}
			if (!empty($gastoBancario) && $gastoBancario!=0) {
				$datosGastoBancario['idordencobro']=$Nuevoidcobro;
				$datosGastoBancario['importedoc']=$gastoBancario;
				$datosGastoBancario['saldodoc']=$gastoBancario;
				$datosGastoBancario['tipogasto']=4;
				$datosGastoBancario['formacobro']=2;
				$datosGastoBancario['referencia']=$numeroletra.'F';
				$datosGastoBancario['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $dias . " day"));
				$datosGastoBancario['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datosGastoBancario['idpadre']=$iddetalleordencobro;
				$grabaGastoBancario=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datosGastoBancario);
			
				$filtro="idordenventa='$idordenventa' and idtipogasto=4";
				$dataOrdenGastoBancario=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoBancario)){
					$dataB['importegasto']=$gastoBancario+$dataOrdenGastoBancario[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataB,$dataOrdenGastoBancario[0]['idordengasto']);
				}else{
					$dataB['importegasto']=$gastoBancario;
					$dataB['idordenventa']=$idordenventa;
					$dataB['idtipogasto']=4;
					$exitoF=$ordenGasto->graba($dataB);
				}
			}
			if (!empty($costoMantenimiento) && $costoMantenimiento!=0) {
				$datosCostoMantenimiento['idordencobro']=$Nuevoidcobro;
				$datosCostoMantenimiento['importedoc']=$costoMantenimiento;
				$datosCostoMantenimiento['saldodoc']=$costoMantenimiento;
				$datosCostoMantenimiento['tipogasto']=8;
				$datosCostoMantenimiento['formacobro']=2;
				$datosCostoMantenimiento['referencia']=$numeroletra.'F';
				$datosCostoMantenimiento['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $dias . " day"));
				$datosCostoMantenimiento['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datosCostoMantenimiento['idpadre']=$iddetalleordencobro;
				$grabaCostoMantenimiento=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datosCostoMantenimiento);
			
				$filtro="idordenventa='$idordenventa' and idtipogasto=8";
				$dataOrdenGastoMantenimiento=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoMantenimiento)){
					$dataM['importegasto']=$costoMantenimiento+$dataOrdenGastoMantenimiento[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataM,$dataOrdenGastoMantenimiento[0]['idordengasto']);
				}else{
					$dataM['importegasto']=$costoMantenimiento;
					$dataM['idordenventa']=$idordenventa;
					$dataM['idtipogasto']=8;
					$exitoF=$ordenGasto->graba($dataM);
				}
			}
			if (!empty($pagoAcuenta) && $pagoAcuenta!=0) {
				$datosPagoAcuenta['idordencobro']=$Nuevoidcobro;
				$datosPagoAcuenta['importedoc']=$pagoAcuenta;
				$datosPagoAcuenta['saldodoc']=$pagoAcuenta;
				$datosPagoAcuenta['formacobro']=1;
				$datosPagoAcuenta['referencia']=$numeroletra.'F';
				$datosPagoAcuenta['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $dias . " day"));
				$datosPagoAcuenta['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datosPagoAcuenta['idpadre']=$iddetalleordencobro;
				$grabaPagoAcuenta=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datosPagoAcuenta);
			}

			//creamos su detalleordencobro
			$datos['idordencobro']=$Nuevoidcobro;
			$datos['importedoc']=$monto+$montoadicional;
			$datos['formacobro']='3';
			$datos['saldodoc']=$monto+$montoadicional;
			$datos['numeroletra']=$detOrdenCobro->GeneraNumeroLetra();
			$datos['situacion']='';
			$datos['referencia']=$numeroletra.'F';
			$datos['tipogasto']=2;
			$datos['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $dias . " day"));
			$datos['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
			$datos['idpadre']=$iddetalleordencobro;
			$datos['montoprotesto']=$montoadicional;
			echo $fecha;
			$graba=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datos);
			//creamos un documento tipo letra
			$datadocumentos['idordenventa']=$idordenventa;
			$datadocumentos['fechadoc']=$datos['fechagiro'];
			$datadocumentos['numdoc']=$datos['numeroletra'];
			$datadocumentos['serie']=1;
			$datadocumentos['montofacturado']=$datos['importedoc'];
			$datadocumentos['nombredoc']=7;
			$grabaDoc=$documento->grabaDocumento($datadocumentos);
			
			$filtro="idordenventa='$idordenventa' and idtipogasto=2";
			$dataOrdenGastoProtesto=$ordenGasto->buscaxFiltro($filtro);
			if (!empty($dataOrdenGastoProtesto)){
				$dataP['importegasto']=$montoadicional+$dataOrdenGastoProtesto[0]['importegasto'];
				$exitoF=$ordenGasto->actualiza($dataP,$dataOrdenGastoProtesto[0]['idordengasto']);
			}else{
				$dataP['importegasto']=$montoadicional;
				$dataP['idordenventa']=$idordenventa;
				$dataP['idtipogasto']=2;
				$exitoF=$ordenGasto->graba($dataP);
			}

			if ($graba && $grabaDoc) {
				if ($renovadoA!=0) {
					
				}else{
					$data['situacion']='refinanciado';
					$data['saldodoc']=0;
					$data['protesto']=1;
					$exito=$detOrdenCobro->actualizaDetalleOrdencobro($data,$iddetalleordencobro);
				}
				
				if ($exito) {
					$dataoc['saldoordencobro']=$saldoordencobroA-$monto;
					if ($dataoc['saldoordencobro']<0.1) {
						$dataoc['situacion']="cancelado";
					}
					
					$exito2=$ordencobro->actualizaOrdencobro($dataoc,$idordencobro);
					echo $exito2;
				}
			}else{
				echo 'segundo error';
			}
		}else{
			echo 'primer error';
		}
	}

	function variasLetras(){
		$idordenventa=$_REQUEST['idordenventa'];
		$idcliente=$_REQUEST['idcliente'];
		$idcobrador=$_REQUEST['idcobrador'];
		$monto=round($_REQUEST['monto'],2);
		$formacobro=$_REQUEST['formacobro'];
		$iddetalleordencobro=$_REQUEST['iddetalleordencobro'];
		$numeroletra=$_REQUEST['numeroletra'];
		$fecha=$_REQUEST['fecha'];
		$idletras=$_REQUEST['idletras'];
		$montoadicional=round($_REQUEST['montoadicional'],2);
		$pagoAcuenta=round($_REQUEST['pagoAcuenta'],2);
		if (empty($pagoAcuenta)) {
			$pagoAcuenta=0;
		}
		//gasto adicionales
		$flete=$_REQUEST['flete'];
		if (empty($flete)) {
			$flete=0;
		}else{
			$flete=$this->trucarNumeros($flete,2);
		}
		$envioSobre=$_REQUEST['envioSobre'];
		if (empty($envioSobre)) {
			$envioSobre=0;
		}else{
			$envioSobre=$this->trucarNumeros($envioSobre,2);
		}
		$gastoBancario=$_REQUEST['gastoBancario'];
		if (empty($gastoBancario)) {
			$gastoBancario=0;
		}else{
			$gastoBancario=$this->trucarNumeros($gastoBancario,2);
		}
		$costoMantenimiento=$_REQUEST['costoMantenimiento'];
		if (empty($costoMantenimiento)) {
			$costoMantenimiento=0;
		}else{
			$costoMantenimiento=$this->trucarNumeros($costoMantenimiento,2);
		}
		/********/

		$ordencobro=$this->AutoLoadModel('ordencobro');
		$detOrdenCobro=$this->AutoLoadModel('detalleordencobro');
		$documento=$this->AutoLoadModel('documento');
		$letras=$this->AutoLoadModel('condicionletra');
		$ordenGasto=$this->AutoLoadModel('ordengasto');

		//verificamos que la orden de venta tenga factura y si tiene generamos una nota debito
		$busquedaDocumento=$documento->listaDocumentosSinAnulados($idordenventa,1);
		if (!empty($busquedaDocumento)) {
			$datadocumento['idordenventa']=$idordenventa;
			$datadocumento['nombredoc']=6;
			$datadocumento['fechadoc']=date('Y-m-d');
			$datadocumento['montofacturado']=$montoadicional;
			$datadocumento['concepto']=2;
			$exitoD=$documento->grabaDocumento($datadocumento);
			if (!$exitoD) {
				echo 'Error al grabar el devito';
				exit;
			}
		}

		//recuperamos los datos del detalleordencobro como el idordencobro
		$datadetordencobro=$detOrdenCobro->buscaDetalleOrdencobro($iddetalleordencobro);
		$idordencobro=$datadetordencobro[0]['idordencobro'];
		$fvencimientoA=$datadetordencobro[0]['fvencimiento'];
		$renovadoA=$datadetordencobro[0]['renovado'];
		$monto=round($datadetordencobro[0]['importedoc'],2);
		$fechaActual=date('Y-m-d');
		$montorenovado=0;
		//verificamos si la letra viene de una renovacion
		if ($renovadoA!=0) {
			//buscamos en su orden de cobro todos sus item que tenga renovado!=0 y recuperamos el importe antes que se renueve
			$databusqueda=$detOrdenCobro->listadoxidOrdenCobroxrenovado($idordencobro);
			$cantidadbusqueda=count($databusqueda);
			for ($i=0; $i <$cantidadbusqueda ; $i++) { 
				$idoc=$databusqueda[$i]['iddetalleordencobro'];
				$montorenovado+=$databusqueda[$i]['importedoc'];
				//actualizamos los detalles de ordencobro
				$informacion['saldodoc']=0;
				$informacion['situacion']='refinanciado';
				$informacion['protesto']='1';
				$exito=$detOrdenCobro->actualizaDetalleOrdencobro($informacion,$idoc);
			}
			$monto=round($montorenovado,2);
		}
		if (!empty($pagoAcuenta) && $pagoAcuenta!=0) {
			$monto=$monto-$pagoAcuenta;
		}
		//recuperamos algunos datos del antiguo ordencobro
		$ordencobroantiguo=$ordencobro->buscaOrdencobro($idordencobro);
		$saldoordencobroA=$ordencobroantiguo[0]['saldoordencobro'];

		//creamos una nueva orden de cobro
		//$dataNuevo['idcondicionletra']=$ordencobroantiguo[0]['idcondicionletra'];
		$dataNuevo['situacion']="Pendiente";
		$dataNuevo['tipoletra']=1;
		$dataNuevo['idordenventa']=$idordenventa;
		$dataNuevo['femision']=date('Y-m-d');
		$dataNuevo['esletras']="1";
		$dataNuevo['numletras']="1";
		$dataNuevo['importeordencobro']=$monto+$montoadicional+$pagoAcuenta+$flete+$envioSobre+$gastoBancario+$costoMantenimiento;
		$dataNuevo['saldoordencobro']=$monto+$montoadicional+$pagoAcuenta+$flete+$envioSobre+$gastoBancario+$costoMantenimiento;
		$Nuevoidcobro=$ordencobro->grabaOrdencobro($dataNuevo);

		



		if ($Nuevoidcobro) {
			//recuperamos la condicion de la letra y numero de letras
			$dataletras=$letras->buscaxId($idletras);
			$cantidadletras=$dataletras[0]['cantidadletra'];
			$diasletra=split('/', $dataletras[0]['nombreletra']);
			
			//creamos su detalleordencobro
			for ($i=0; $i <$cantidadletras ; $i++) { 
			
				$datos['idordencobro']=$Nuevoidcobro;
				$datos['importedoc']=round((($monto+$montoadicional)/$cantidadletras),2);
				$datos['formacobro']='3';
				$datos['saldodoc']=round((($monto+$montoadicional)/$cantidadletras),2);
				$datos['numeroletra']=$detOrdenCobro->GeneraNumeroLetra();
				$datos['situacion']='';
				$datos['referencia']=$numeroletra.'F';
				$datos['montoprotesto']=$montoadicional/$cantidadletras;
				$datos['tipogasto']=2;
				$datos['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $diasletra[$i] . " day"));
				$datos['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datos['idpadre']=$iddetalleordencobro;
				echo $fvencimientoA;
				$graba=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datos);
				//creamos un documento tipo letra
				$datadocumentos['idordenventa']=$idordenventa;
				$datadocumentos['fechadoc']=$datos['fechagiro'];
				$datadocumentos['numdoc']=$datos['numeroletra'];
				$datadocumentos['serie']=1;
				$datadocumentos['montofacturado']=$datos['importedoc'];
				$datadocumentos['nombredoc']=7;
				$grabaDoc=$documento->grabaDocumento($datadocumentos);
			}
			
			$filtro="idordenventa='$idordenventa' and idtipogasto=2";
			$dataOrdenGastoProtesto=$ordenGasto->buscaxFiltro($filtro);
			if (!empty($dataOrdenGastoProtesto)){
				$dataP['importegasto']=$montoadicional+$dataOrdenGastoProtesto[0]['importegasto'];
				$exitoF=$ordenGasto->actualiza($dataP,$dataOrdenGastoProtesto[0]['idordengasto']);
			}else{
				$dataP['importegasto']=$montoadicional;
				$dataP['idordenventa']=$idordenventa;
				$dataP['idtipogasto']=2;
				$exitoF=$ordenGasto->graba($dataP);
			}

			if (!empty($flete) && $flete!=0) {
				$datosFlete['idordencobro']=$Nuevoidcobro;
				$datosFlete['importedoc']=$flete;
				$datosFlete['saldodoc']=$flete;
				$datosFlete['tipogasto']=3;
				$datosFlete['formacobro']=2;
				$datosFlete['referencia']=$numeroletra.'F';
				$datosFlete['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $diasletra[$cantidadletras-1] . " day"));
				$datosFlete['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datosFlete['idpadre']=$iddetalleordencobro;
				$grabaFlete=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datosFlete);
				
				$filtro="idordenventa='$idordenventa' and idtipogasto=3";
				$dataOrdenGastoFlete=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoFlete)){
					$dataF['importegasto']=$flete+$dataOrdenGastoFlete[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataF,$dataOrdenGastoFlete[0]['idordengasto']);
				}else{
					$dataF['importegasto']=$flete;
					$dataF['idordenventa']=$idordenventa;
					$dataF['idtipogasto']=3;
					$exitoF=$ordenGasto->graba($dataF);
				}
			}

			if (!empty($envioSobre) && $envioSobre!=0) {
				$datosEnvioSobre['idordencobro']=$Nuevoidcobro;
				$datosEnvioSobre['importedoc']=$envioSobre;
				$datosEnvioSobre['saldodoc']=$envioSobre;
				$datosEnvioSobre['tipogasto']=5;
				$datosEnvioSobre['formacobro']=2;
				$datosEnvioSobre['referencia']=$numeroletra.'F';
				$datosEnvioSobre['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $diasletra[$cantidadletras-1]  . " day"));
				$datosEnvioSobre['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datosEnvioSobre['idpadre']=$iddetalleordencobro;
				$grabaEnvioSobre=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datosEnvioSobre);
			
				$filtro="idordenventa='$idordenventa' and idtipogasto=5";
				$dataOrdenGastoEnvio=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoEnvio)){
					$dataE['importegasto']=$envioSobre+$dataOrdenGastoEnvio[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataE,$dataOrdenGastoEnvio[0]['idordengasto']);
				}else{
					$dataE['importegasto']=$envioSobre;
					$dataE['idordenventa']=$idordenventa;
					$dataE['idtipogasto']=5;
					$exitoF=$ordenGasto->graba($dataE);
				}
			}
			if (!empty($gastoBancario) && $gastoBancario!=0) {
				$datosGastoBancario['idordencobro']=$Nuevoidcobro;
				$datosGastoBancario['importedoc']=$gastoBancario;
				$datosGastoBancario['saldodoc']=$gastoBancario;
				$datosGastoBancario['tipogasto']=4;
				$datosGastoBancario['formacobro']=2;
				$datosGastoBancario['referencia']=$numeroletra.'F';
				$datosGastoBancario['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $diasletra[$cantidadletras-1]  . " day"));
				$datosGastoBancario['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datosGastoBancario['idpadre']=$iddetalleordencobro;
				$grabaGastoBancario=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datosGastoBancario);
			
				$filtro="idordenventa='$idordenventa' and idtipogasto=4";
				$dataOrdenGastoBancario=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoBancario)){
					$dataB['importegasto']=$gastoBancario+$dataOrdenGastoBancario[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataB,$dataOrdenGastoBancario[0]['idordengasto']);
				}else{
					$dataB['importegasto']=$gastoBancario;
					$dataB['idordenventa']=$idordenventa;
					$dataB['idtipogasto']=4;
					$exitoF=$ordenGasto->graba($dataB);
				}
			}
			if (!empty($costoMantenimiento) && $costoMantenimiento!=0) {
				$datosCostoMantenimiento['idordencobro']=$Nuevoidcobro;
				$datosCostoMantenimiento['importedoc']=$costoMantenimiento;
				$datosCostoMantenimiento['saldodoc']=$costoMantenimiento;
				$datosCostoMantenimiento['tipogasto']=8;
				$datosCostoMantenimiento['formacobro']=2;
				$datosCostoMantenimiento['referencia']=$numeroletra.'F';
				$datosCostoMantenimiento['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $diasletra[$cantidadletras-1]  . " day"));
				$datosCostoMantenimiento['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datosCostoMantenimiento['idpadre']=$iddetalleordencobro;
				$grabaCostoMantenimiento=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datosCostoMantenimiento);
			
				$filtro="idordenventa='$idordenventa' and idtipogasto=8";
				$dataOrdenGastoMantenimiento=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoMantenimiento)){
					$dataM['importegasto']=$costoMantenimiento+$dataOrdenGastoMantenimiento[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataM,$dataOrdenGastoMantenimiento[0]['idordengasto']);
				}else{
					$dataM['importegasto']=$costoMantenimiento;
					$dataM['idordenventa']=$idordenventa;
					$dataM['idtipogasto']=8;
					$exitoF=$ordenGasto->graba($dataM);
				}
			}
			if (!empty($pagoAcuenta) && $pagoAcuenta!=0) {
				$datosPagoAcuenta['idordencobro']=$Nuevoidcobro;
				$datosPagoAcuenta['importedoc']=$pagoAcuenta;
				$datosPagoAcuenta['saldodoc']=$pagoAcuenta;
				$datosPagoAcuenta['formacobro']=1;
				$datosPagoAcuenta['referencia']=$numeroletra.'F';
				$datosPagoAcuenta['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $diasletra[$cantidadletras-1]  . " day"));
				$datosPagoAcuenta['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datosPagoAcuenta['idpadre']=$iddetalleordencobro;
				$grabaPagoAcuenta=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datosPagoAcuenta);
			}


			echo $graba.'/'.$grabaDoc;
			if ($graba && $grabaDoc) {
				if ($renovadoA!=0) {
					
				}else{
					$data['situacion']='refinanciado';
					$data['saldodoc']=0;
					$data['protesto']=1;
					$exito=$detOrdenCobro->actualizaDetalleOrdencobro($data,$iddetalleordencobro);
				}
				
				if ($exito) {
					$dataoc['saldoordencobro']=$saldoordencobroA-$monto;
					if ($dataoc['saldoordencobro']<0.1) {
						$dataoc['situacion']="cancelado";
					}
					
					$exito2=$ordencobro->actualizaOrdencobro($dataoc,$idordencobro);
					echo $exito2;
				}
			}else{
				echo 'segundo error';
			}
		}else{
			echo 'primer error';
		}
	}

	function renovar(){
		$idordenventa=$_REQUEST['idordenventa'];
		$idcliente=$_REQUEST['idcliente'];
		$idcobrador=$_REQUEST['idcobrador'];
		$monto=round($_REQUEST['monto'],2);
		$formacobro=$_REQUEST['formacobro'];
		$iddetalleordencobro=$_REQUEST['iddetalleordencobro'];
		$numeroletra=$_REQUEST['numeroletra'];
		$fecha=$_REQUEST['fecha'];
		$dias=$_REQUEST['dias'];
		$montoadicional=round($_REQUEST['montoadicional'],2);
		$montoporcentaje=round($_REQUEST['montoporcentaje'],2);
		$tipoPago=$_REQUEST['tipoPago'];
		//gasto adicionales
		$flete=$_REQUEST['flete'];
		if (empty($flete)) {
			$flete=0;
		}else{
			$flete=$this->trucarNumeros($flete,2);
		}
		$envioSobre=$_REQUEST['envioSobre'];
		if (empty($envioSobre)) {
			$envioSobre=0;
		}else{
			$envioSobre=$this->trucarNumeros($envioSobre,2);
		}
		$gastoBancario=$_REQUEST['gastoBancario'];
		if (empty($gastoBancario)) {
			$gastoBancario=0;
		}else{
			$gastoBancario=$this->trucarNumeros($gastoBancario,2);
		}
		$costoMantenimiento=$_REQUEST['costoMantenimiento'];
		if (empty($costoMantenimiento)) {
			$costoMantenimiento=0;
		}else{
			$costoMantenimiento=$this->trucarNumeros($costoMantenimiento,2);
		}
		/********************/
		$diasadicionales=8;
		$ordencobro=$this->AutoLoadModel('ordencobro');
		$detOrdenCobro=$this->AutoLoadModel('detalleordencobro');
		$documento=$this->AutoLoadModel('documento');
		$ordenGasto=$this->AutoLoadModel('ordengasto');

		//verificamos que la orden de venta tenga factura y si tiene generamos una nota debito
		$busquedaDocumento=$documento->listaDocumentosSinAnulados($idordenventa,1);
		if (!empty($busquedaDocumento)) {
			$datadocumento['idordenventa']=$idordenventa;
			$datadocumento['nombredoc']=6;
			$datadocumento['fechadoc']=date('Y-m-d');
			$datadocumento['montofacturado']=$montoadicional;
			$datadocumento['concepto']=1;
			$exitoD=$documento->grabaDocumento($datadocumento);
			if (!$exitoD) {
				echo 'Error al grabar el devito';
				exit;
			}
		}
		

		//recuperamos los datos del detalleordencobro como el idordencobro
		$datadetordencobro=$detOrdenCobro->buscaDetalleOrdencobro2($iddetalleordencobro);
		$idordencobro=$datadetordencobro[0]['idordencobro'];
		$fvencimientoA=$datadetordencobro[0]['fvencimiento'];
		$renovado=$datadetordencobro[0]['renovado'];
		$monto=round($datadetordencobro[0]['importedoc'],2);
		$fechaActual=date('Y-m-d');
		echo $fvencimientoA;

		//calculamos el montoaPagar y nuevomontoletra
		if ($tipoPago==1) {
			$MontoPagar=round(($monto*$montoporcentaje)/100,2);
			$nuevoMontoLetra=round(($monto*(100-$montoporcentaje))/100,2);
		}elseif ($tipoPago==2) {
			$MontoPagar=$montoporcentaje;
			$nuevoMontoLetra=$monto-$montoporcentaje;
		}
		//recuperamos algunos datos del antiguo ordencobro
		$ordencobroantiguo=$ordencobro->buscaOrdencobro($idordencobro);
		$saldoordencobroA=$ordencobroantiguo[0]['saldoordencobro'];
		
		//creamos una nueva orden cobro 
		//$dataNuevo['idcondicionletra']=$ordencobroantiguo[0]['idcondicionletra'];
		$dataNuevo['situacion']="Pendiente";
		$dataNuevo['idordenventa']=$idordenventa;
		$dataNuevo['femision']=date('Y-m-d');
		$dataNuevo['esletras']="1";
		$dataNuevo['escredito']="1";
		$dataNuevo['numletras']="1";
		$dataNuevo['tipoletra']="1";
		$dataNuevo['importeordencobro']=$monto+$montoadicional+$flete+$envioSobre+$gastoBancario+$costoMantenimiento;
		$dataNuevo['saldoordencobro']=$monto+$montoadicional+$flete+$envioSobre+$gastoBancario+$costoMantenimiento;
		$Nuevoidcobro=$ordencobro->grabaOrdencobro($dataNuevo);

		if ($Nuevoidcobro) {
			//creamos cobro para flete si no esta vacio
			if (!empty($flete) && $flete!=0) {
				$datosFlete['idordencobro']=$Nuevoidcobro;
				$datosFlete['importedoc']=$flete;
				$datosFlete['saldodoc']=$flete;
				$datosFlete['tipogasto']=3;
				$datosFlete['formacobro']=2;
				$datosFlete['referencia']=$numeroletra;
				$datosFlete['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $dias . " day"));
				$datosFlete['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datosFlete['idpadre']=$iddetalleordencobro;
				$grabaFlete=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datosFlete);
			
				$filtro="idordenventa='$idordenventa' and idtipogasto=3";
				$dataOrdenGastoFlete=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoFlete)){
					$dataF['importegasto']=$flete+$dataOrdenGastoFlete[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataF,$dataOrdenGastoFlete[0]['idordengasto']);
				}else{
					$dataF['importegasto']=$flete;
					$dataF['idordenventa']=$idordenventa;
					$dataF['idtipogasto']=3;
					$exitoF=$ordenGasto->graba($dataF);
				}
			}

			if (!empty($envioSobre) && $envioSobre!=0) {
				$datosEnvioSobre['idordencobro']=$Nuevoidcobro;
				$datosEnvioSobre['importedoc']=$envioSobre;
				$datosEnvioSobre['saldodoc']=$envioSobre;
				$datosEnvioSobre['tipogasto']=5;
				$datosEnvioSobre['formacobro']=2;
				$datosEnvioSobre['referencia']=$numeroletra;
				$datosEnvioSobre['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $dias . " day"));
				$datosEnvioSobre['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datosEnvioSobre['idpadre']=$iddetalleordencobro;
				$grabaEnvioSobre=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datosEnvioSobre);
			
				$filtro="idordenventa='$idordenventa' and idtipogasto=5";
				$dataOrdenGastoEnvio=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoEnvio)){
					$dataE['importegasto']=$envioSobre+$dataOrdenGastoEnvio[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataE,$dataOrdenGastoEnvio[0]['idordengasto']);
				}else{
					$dataE['importegasto']=$envioSobre;
					$dataE['idordenventa']=$idordenventa;
					$dataE['idtipogasto']=5;
					$exitoF=$ordenGasto->graba($dataE);
				}
			}
			if (!empty($gastoBancario) && $gastoBancario!=0) {
				$datosGastoBancario['idordencobro']=$Nuevoidcobro;
				$datosGastoBancario['importedoc']=$gastoBancario;
				$datosGastoBancario['saldodoc']=$gastoBancario;
				$datosGastoBancario['tipogasto']=4;
				$datosGastoBancario['formacobro']=2;
				$datosGastoBancario['referencia']=$numeroletra;
				$datosGastoBancario['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $dias . " day"));
				$datosGastoBancario['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datosGastoBancario['idpadre']=$iddetalleordencobro;
				$gabraGastoBancario=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datosGastoBancario);
			
				$filtro="idordenventa='$idordenventa' and idtipogasto=4";
				$dataOrdenGastoBancario=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoBancario)){
					$dataB['importegasto']=$gastoBancario+$dataOrdenGastoBancario[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataB,$dataOrdenGastoBancario[0]['idordengasto']);
				}else{
					$dataB['importegasto']=$gastoBancario;
					$dataB['idordenventa']=$idordenventa;
					$dataB['idtipogasto']=4;
					$exitoF=$ordenGasto->graba($dataB);
				}
			}
			if (!empty($costoMantenimiento) && $costoMantenimiento!=0) {
				$datosCostoMantenimiento['idordencobro']=$Nuevoidcobro;
				$datosCostoMantenimiento['importedoc']=$costoMantenimiento;
				$datosCostoMantenimiento['saldodoc']=$costoMantenimiento;
				$datosCostoMantenimiento['tipogasto']=8;
				$datosCostoMantenimiento['formacobro']=2;
				$datosCostoMantenimiento['referencia']=$numeroletra;
				$datosCostoMantenimiento['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $dias . " day"));
				$datosCostoMantenimiento['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
				$datosCostoMantenimiento['idpadre']=$iddetalleordencobro;
				$grabaCostoMantenimiento=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datosCostoMantenimiento);
			
				$filtro="idordenventa='$idordenventa' and idtipogasto=8";
				$dataOrdenGastoMantenimiento=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoMantenimiento)){
					$dataM['importegasto']=$costoMantenimiento+$dataOrdenGastoMantenimiento[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataM,$dataOrdenGastoMantenimiento[0]['idordengasto']);
				}else{
					$dataM['importegasto']=$costoMantenimiento;
					$dataM['idordenventa']=$idordenventa;
					$dataM['idtipogasto']=8;
					$exitoF=$ordenGasto->graba($dataM);
				}
			}

			//creamos un nuevo detalleordencobro para gastos renovacion
			$datos['idordencobro']=$Nuevoidcobro;
			$datos['importedoc']=$montoadicional;
			$datos['formacobro']='2';
			$datos['tipogasto']=1;
			$datos['gastosrenovacion']='1';//1 pertenece a un gasto de renovacion y no esta cancelado/
			//2 pertenecia a un gasto de renovacion y fue anulado
			$datos['saldodoc']=$montoadicional;
			$datos['situacion']='';
			$datos['referencia']=$numeroletra;
			$datos['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $dias . " day"));
			$datos['fechagiro']=date('Y-m-d',strtotime($fvencimientoA));
			$datos['idpadre']=$iddetalleordencobro;
			$graba=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datos);

			//creamos un nuevo detalleordencobro para monto al contado
			$renovado=$renovado+1;
			$datos2['idordencobro']=$Nuevoidcobro;
			$datos2['importedoc']=$MontoPagar;
			$datos2['formacobro']='1';
			$datos2['saldodoc']=$MontoPagar;
			$datos2['situacion']='';
			$datos2['referencia']=$numeroletra;
			$datos2['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $diasadicionales . " day"));
			$datos2['fechagiro']=date('Y/m/d', strtotime("$fvencimientoA + " . $diasadicionales . " day"));
			$datos2['renovado']=$renovado;
			$datos2['idpadre']=$iddetalleordencobro;
			$graba2=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datos2);

			//creamos un nuevo detalleordencobro para la letra renovada
			$numeroletraN=substr($numeroletra,0,8);
			$datos3['idordencobro']=$Nuevoidcobro;
			$datos3['importedoc']=$nuevoMontoLetra;
			$datos3['formacobro']='3';
			$datos3['numeroletra']=$numeroletraN.'R'.$renovado;
			$datos3['saldodoc']=$nuevoMontoLetra;
			$datos3['situacion']='';
			$datos3['referencia']=$numeroletra;
			$datos3['tipopago']=$tipoPago;
			$datos3['porcentajeMonto']=$montoporcentaje;
			$datos3['fvencimiento']=date('Y/m/d', strtotime("$fvencimientoA + " . $dias . " day"));
			$datos3['fechagiro']=date('Y/m/d', strtotime($fvencimientoA));
			$datos3['renovado']=$renovado;
			$datos3['idpadre']=$iddetalleordencobro;
			$graba3=$detOrdenCobro->grabaDetalleOrdenVentaCobro($datos3);

			//creamos un documento tipo letra
			$datadocumentos['idordenventa']=$idordenventa;
			$datadocumentos['fechadoc']=$datos3['fechagiro'];
			$datadocumentos['numdoc']=$datos3['numeroletra'];
			$datadocumentos['serie']=1;
			$datadocumentos['montofacturado']=$datos3['importedoc'];
			$datadocumentos['nombredoc']=7;
			$grabaDoc=$documento->grabaDocumento($datadocumentos);
			
			//creamos a actualizamos el gasto por renovacion
			$filtro="idordenventa='$idordenventa' and idtipogasto=1";
			$dataOrdenGastoRenovado=$ordenGasto->buscaxFiltro($filtro);
			if (!empty($dataOrdenGastoRenovado)){
				$dataR['importegasto']=$montoadicional+$dataOrdenGastoRenovado[0]['importegasto'];
				$exitoF=$ordenGasto->actualiza($dataR,$dataOrdenGastoRenovado[0]['idordengasto']);
			}else{
				$dataR['importegasto']=$montoadicional;
				$dataR['idordenventa']=$idordenventa;
				$dataR['idtipogasto']=1;
				$exitoF=$ordenGasto->graba($dataR);
			}

			if ($graba && $graba2 && $graba3 && $grabaDoc) {
				$data['situacion']='renovado';
				$data['saldodoc']=0;
				$exito=$detOrdenCobro->actualizaDetalleOrdencobro($data,$iddetalleordencobro);

				if ($exito) {
					//Actualizamos la orden de cobro
					$dataoc['saldoordencobro']=$saldoordencobroA-$monto;
					if ($dataoc['saldoordencobro']<0.1) {
						$dataoc['situacion']="cancelado";
					}
					
					$exito2=$ordencobro->actualizaOrdencobro($dataoc,$idordencobro);
					echo $exito2;
				}else{
					echo 'tercer error';
				}
			}else{
				echo 'segundo error';
			}
		}else{
			echo 'primer error';
		}
	}

	function extornar(){
		$detalleordencobro=$this->AutoLoadModel('detalleordencobro');
		$ordencobro=$this->AutoLoadModel('ordencobro');
		$documento=$this->AutoLoadModel('documento');

		$tipocobro=$_REQUEST['tipocobro'];
		$montoporcentaje=$this->trucarNumeros($_REQUEST['montoporcentaje'],2);
		$iddetalleordencobro=$_REQUEST['iddetalleordencobro'];
		$idordenventa=$_REQUEST['idordenventa'];
		//obtenemos los datos del detalleordencobro
		$datadetordencobro=$detalleordencobro->buscaDetalleOrdencobro2($iddetalleordencobro);
		$idordencobro=$datadetordencobro[0]['idordencobro'];
		$tipoletra=$datadetordencobro[0]['tipoletra'];
		$renovado=$datadetordencobro[0]['renovado']+1;
		$fechagiro=$datadetordencobro[0]['fechagiro'];
		$fvencimiento=$datadetordencobro[0]['fvencimiento'];
		$numeroletraA=$datadetordencobro[0]['numeroletra'];
		$nLA=$datadetordencobro[0]['referencia'];
		$numeroletra=substr($numeroletraA,0,8);
		$diasadicionales=8;
		
		

		$databusqueda=$detalleordencobro->listadoxidOrdenCobroxrenovado($idordencobro);
		$cantidad=count($databusqueda);

		
		//creamos la nueva orden de cobro
		for ($i=0; $i <$cantidad ; $i++) { 
			$importe+=$databusqueda[$i]['importedoc'];
		}

		if (empty($montoporcentaje)) {
			
		$dataOrdenCobro['esletras']=1;
		$dataOrdenCobro['escontado']=1;
		$dataOrdenCobro['importeordencobro']=round($importe,2);
		$dataOrdenCobro['saldoordencobro']=round($importe,2);
		$dataOrdenCobro['tipoletra']=1;
		$dataOrdenCobro['numletras']=1;
		$dataOrdenCobro['idordenventa']=$idordenventa;
		$dataOrdenCobro['femision']=date('Y-m-d');

		$graba=$ordencobro->grabaOrdencobro($dataOrdenCobro);
		}

		if (!empty($montoporcentaje)) {
			//aca entra las extornaciones por porcentaje o monto
			//sacamos el nuevo contado y letra sus montos
			if ($tipocobro==1) {
				$MontoPagar=round((($importe*$montoporcentaje)/100),2);
				$nuevoMontoLetra=round((($importe*(100-$montoporcentaje))/100),2);
			}elseif ($tipocobro==2) {
				$MontoPagar=$montoporcentaje;
				$nuevoMontoLetra=$importe-$montoporcentaje;
			}


			
			for ($i=0; $i <$cantidad ; $i++) { 
				if ($databusqueda[$i]['formacobro']==1) {
					$iddet=$databusqueda[$i]['iddetalleordencobro'];
					$dataI['importedoc']=$MontoPagar;
					$dataI['saldodoc']=$MontoPagar;
					$exitoNI=$detalleordencobro->actualizaDetalleOrdencobro($dataI,$iddet);
					if(!$exitoNI){
						echo 'pr1mer error';
					}
				}
				elseif ($databusqueda[$i]['formacobro']==3) {
					$iddet=$databusqueda[$i]['iddetalleordencobro'];
					$data['importedoc']=$nuevoMontoLetra;
					$data['saldodoc']=$nuevoMontoLetra;
					$data['tipopago']=$tipocobro;
					$data['porcentajeMonto']=$montoporcentaje;
					$exitoN1=$detalleordencobro->actualizaDetalleOrdencobro($data,$iddet);
					if(!$exitoN1){
						echo 'segundo error';
					}
				}
				

				
			}
			$dataDoc['montofacturado']=$nuevoMontoLetra;
			$filtro="idordenventa='$idordenventa' and numdoc='$numeroletraA' and nombredoc='7' ";
			$exito=$documento->actualizarDocumento($dataDoc,$filtro);
			

		}elseif($graba && empty($montoporcentaje)){
			//recuperamos la fechaGiro y la fechavencimiento de su referencia
			$dataF=$detalleordencobro->buscaDetalleOrdencobroxNumeroletra($nLA);
			$FGA=$dataF[0]['fechagiro'];
			$FVA=$dataF[0]['fvencimiento'];
			//aca entra las extornaciones por pago
			$dataDetalle['idordencobro']=$graba;
			$dataDetalle['renovado']=$renovado;
			$dataDetalle['formacobro']=3;
			$dataDetalle['importedoc']=round($importe,2);
			$dataDetalle['saldodoc']=round($importe,2);
			$dataDetalle['situacion']='';
			$dataDetalle['referencia']=$numeroletraA;
			$dataDetalle['fechagiro']=$FGA;
			$dataDetalle['fvencimiento']=$FVA;
			$dataDetalle['numeroletra']=$numeroletra.'R'.$renovado;
			$dataDetalle['idpadre']=$iddetalleordencobro;
			$grabaC=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalle);

			//creamos un documento tipo letra
			$datadocumentos['idordenventa']=$idordenventa;
			$datadocumentos['fechadoc']=$dataDetalle['fechagiro'];
			$datadocumentos['numdoc']=$dataDetalle['numeroletra'];
			$datadocumentos['serie']=1;
			$datadocumentos['montofacturado']=$dataDetalle['importedoc'];
			$datadocumentos['nombredoc']=7;
			$grabaDoc=$documento->grabaDocumento($datadocumentos);
			
			if ($grabaC) {
				for ($i=0; $i <$cantidad ; $i++) { 
					$iddet=$databusqueda[$i]['iddetalleordencobro'];
					$data['situacion']='extornado';
					$data['saldodoc']=0;

					$exitoN2=$detalleordencobro->actualizaDetalleOrdencobro($data,$iddet);
				}
				if ($exitoN2) 
				{
					//recuperamos su importe de orden de cobro
					$databusquedaOrden=$ordencobro->buscaOrdencobro($idordencobro);
					$saldoordencobro=round($databusquedaOrden[0]['saldoordencobro'],2);
					$nuevosaldo=$saldoordencobro-round($importe,2);
					if ($nuevosaldo<0.1) {
						$dataOrden['situacion']='cancelado';
					}
					$dataOrden['saldoordencobro']=$nuevosaldo;
					$graba3=$ordencobro->actualizaOrdencobro($dataOrden,$idordencobro);		
		}else{
			echo 'tercer error';
		}
			}else{
				echo 'segundo error2';
			}

		}else{
			echo 'primer error';
		}


		

		echo $tipocobro.'<br>';
		echo $montoporcentaje.'<br>';
		echo $iddetalleordencobro;
	}

	function deshacerPago(){
		$detalleordencobro=$this->AutoLoadModel('detalleordencobro');
		$ordencobro=$this->AutoLoadModel('ordencobro');
		$detalleordencobroingreso=$this->AutoLoadModel('detalleordencobroingreso');
		$ingresos=$this->AutoLoadModel('ingresos');
		$iddetalleordencobro=$_REQUEST['iddetalleordencobro'];
		$motivo=$_REQUEST['motivo'];
		
		//recuperamos los datos del detalleordencobro
		$datadetordencobro=$detalleordencobro->buscaDetalleOrdencobro2($iddetalleordencobro);
		$idordencobro=$datadetordencobro[0]['idordencobro'];
		$importedoc=round($datadetordencobro[0]['importedoc'],2);

		//recuperamos el importe de la ordenCobro
		$dataOrdenCobro=$ordencobro->buscaOrdencobro($idordencobro);
		$saldoordencobro=round($dataOrdenCobro[0]['saldoordencobro'],2);

		$nuevoImporte=$saldoordencobro+$importedoc;

		if (!empty($datadetordencobro) && !empty($dataOrdenCobro)) {
			//actualizamos el detalledeordencobro
			$datadetalle['saldodoc']=$importedoc;
			$datadetalle['situacion']='';
			$datadetalle['fechapago']='';
			$datadetalle['motivo']=$motivo;
			$exito=$detalleordencobro->actualizaDetalleOrdencobro($datadetalle,$iddetalleordencobro);
			if ($exito) {
				$data['saldoordencobro']=$nuevoImporte;
				$data['situacion']='Pendiente';
				$exito2=$ordencobro->actualizaOrdencobro($data,$idordencobro);

				if ($exito2) {
					$databusqueda=$detalleordencobroingreso->buscaxDetalleOrdenCobro($iddetalleordencobro);
					$cantidadIngre=count($databusqueda);
					for ($i=0; $i <$cantidadIngre ; $i++) { 
						$idingreso=$databusqueda[$i]['idingreso'];
						$iddetalleordencobroingreso=$databusqueda[$i]['iddetalleordencobroingreso'];
						$exito3=$detalleordencobroingreso->eliminar($iddetalleordencobroingreso);
						if ($exito3) {

							$exito4=$ingresos->eliminar($idingreso);
							if ($exito4) {
								echo 'correcto '.$idingreso;
							}else{
								echo 'quinto error';
							}
						}else{
							echo 'cuarto error';
						}
					}
					
					

				}else{
					echo 'tercer error';
				}
			}else{
				echo 'segundo error';
			}
			
		}else{
			echo 'primer error';
		}
	}
	function deshacerPagoAsignacion(){
		$detalleordencobro=$this->AutoLoadModel('detalleordencobro');
		$ordencobro=$this->AutoLoadModel('ordencobro');
		$detalleordencobroingreso=$this->AutoLoadModel('detalleordencobroingreso');
		$ingresos=$this->AutoLoadModel('ingresos');
		$iddetalleordencobro=$_REQUEST['iddetalleordencobro'];
		$motivo=$_REQUEST['motivo'];
		//recuperamos los datos del detalleordencobro
		$datadetordencobro=$detalleordencobro->buscaDetalleOrdencobro2($iddetalleordencobro);
		$idordencobro=$datadetordencobro[0]['idordencobro'];
		$importedoc=round($datadetordencobro[0]['importedoc'],2);
		$saldodoc=round($datadetordencobro[0]['saldodoc'],2);

		//recuperamos el saldo de la ordenCobro
		$dataOrdenCobro=$ordencobro->buscaOrdencobro($idordencobro);
		$saldoordencobro=round($dataOrdenCobro[0]['saldoordencobro'],2);

		$nuevoImporte=$saldoordencobro+$importedoc-$saldodoc;

		if (!empty($datadetordencobro) && !empty($dataOrdenCobro)) {
			//actualizamos el detalledeordencobro
			$datadetalle['saldodoc']=$importedoc;
			$datadetalle['situacion']='';
			$datadetalle['fechapago']='';
			$datadetalle['motivo']=$motivo;
			$exito=$detalleordencobro->actualizaDetalleOrdencobro($datadetalle,$iddetalleordencobro);
			if ($exito) {
				$data['saldoordencobro']=$nuevoImporte;
				$data['situacion']='Pendiente';
				$exito2=$ordencobro->actualizaOrdencobro($data,$idordencobro);

				if ($exito2) {
					
					$databusqueda=$detalleordencobroingreso->buscaxDetalleOrdenCobro($iddetalleordencobro);
					$cantidadIngre=count($databusqueda);
					for ($i=0; $i <$cantidadIngre ; $i++) { 
						
						$idingresos=$databusqueda[$i]['idingreso'];
						$iddetalleordencobroingreso=$databusqueda[$i]['iddetalleordencobroingreso'];
						$importe=round($databusqueda[$i]['montop'],2);
						$exito3=$detalleordencobroingreso->eliminar($iddetalleordencobroingreso);
						if ($exito3) {
							
							//recuperamos el saldo del ingreso
							$datosIngreso=$ingresos->buscaxid($idingresos);        
							echo 'cantidad'.count($dataIngreso);
							$saldoIngreso=round($datosIngreso[0]['saldo'],2);
							$montoasignado=round($datosIngreso[0]['montoasignado'],2);
							$nuevoSaldoIngreso=$saldoIngreso+$importe;
							$nuevoMontoAsignado=$montoasignado-$importe;
							//actualizamos el ingreso
							$dataIngreso['saldo']=$nuevoSaldoIngreso;
							$dataIngreso['montoasignado']=$nuevoMontoAsignado;
							$exitoI=$ingresos->actualizaxid($dataIngreso,$idingresos);
							if ($exitoI) {
								echo 'correcto';
							}else{
								echo 'quinto error';
							}
						}else{
							echo 'cuarto error';
						}
					}
					
					

				}else{
					echo 'tercer error';
				}
			}else{
				echo 'segundo error';
			}
			
		}else{
			echo 'primer error';
		}
	}

	function anular(){
		$detalleordencobro=$this->AutoLoadModel('detalleordencobro');
		$ordencobro=$this->AutoLoadModel('ordencobro');
		$ordenGasto=$this->AutoLoadModel('ordengasto');
		$iddetalleordencobro=$_REQUEST['iddetalleordencobro'];
		$idOrdenGasto=$_REQUEST['idOrdenGasto'];
		$nuevoImporteG=$_REQUEST['nuevoImporte'];
		$redondeo=$this->configIni('Globals','Redondear');

		//recuperamos los datos del detalleordencobro
		$datadetordencobro=$detalleordencobro->buscaDetalleOrdencobro2($iddetalleordencobro);
		$idordencobro=$datadetordencobro[0]['idordencobro'];
		$importedoc=round($datadetordencobro[0]['importedoc'],$redondeo);

		//recuperamos el importe de la ordenCobro
		$dataOrdenCobro=$ordencobro->buscaOrdencobro($idordencobro);
		$saldoordencobro=$dataOrdenCobro[0]['saldoordencobro'];

		$nuevoImporte=$saldoordencobro-$importedoc;

		if (!empty($datadetordencobro) && !empty($dataOrdenCobro)) {
			$datadetalle['saldodoc']=0;
			$datadetalle['situacion']='anulado';
			$exito=$detalleordencobro->actualizaDetalleOrdencobro($datadetalle,$iddetalleordencobro);
			if ($exito) {
				$data['saldoordencobro']=$nuevoImporte;
				if ($nuevoImporte<0.1) {
					$data['situacion']='cancelado';
				}
				
				$exito2=$ordencobro->actualizaOrdencobro($data,$idordencobro);
				if ($exito2) {
					echo '<br>';
					echo $nuevoImporteG;
					$dataOG['importegasto']=round($nuevoImporteG,$redondeo);
					$exito3=$ordenGasto->actualiza($dataOG,$idOrdenGasto);
					echo 'correcto';
				}else{
					echo 'tercer error';
				}
			}else{
				echo 'segundo error';
			}
		}else{
			echo 'primer error';
		}		
	}

	function modificar(){
		$iddetalleordencobro=$_REQUEST['idModificar'];
		$flete=$_REQUEST['flete'];

		$diasFlete=$_REQUEST['diasFlete'];
		$envioSobre=$_REQUEST['envioSobre'];
		$diasEnvioSobre=$_REQUEST['diasEnvioSobre'];
		$gastoBancario=$_REQUEST['gastoBancario'];
		$diasGastoBancario=$_REQUEST['diasGastoBancario'];
		$costoMantenimiento=$_REQUEST['costoMantenimiento'];
		$diasCostoMantenimiento=$_REQUEST['diasCostoMantenimiento'];
		$montoContado=$_REQUEST['montoContado'];
		$montoCredito0=$_REQUEST['montoCredito0'];
		$montoCredito1=$_REQUEST['montoCredito1'];
		$montoCredito2=$_REQUEST['montoCredito2'];
		$montoCredito3=$_REQUEST['montoCredito3'];
		$montoCredito4=$_REQUEST['montoCredito4'];
		$diasCredito0=$_REQUEST['diasCredito0'];
		$diasCredito1=$_REQUEST['diasCredito1'];
		$diasCredito2=$_REQUEST['diasCredito2'];
		$diasCredito3=$_REQUEST['diasCredito3'];
		$diasCredito4=$_REQUEST['diasCredito4'];
		$montoLetra=$_REQUEST['montoLetra'];
		$diasMontoLetra=$_REQUEST['diasMontoLetra'];
		$cantidadLetras=$_REQUEST['cantidadLetras'];
		$montoLetraNueva=$_REQUEST['montoLetraNueva'];
		$nuevaLetra=$_REQUEST['nuevaLetra'];
		$nuevaFecha=$_REQUEST['nuevaFecha'];
		//
		$flete=$_REQUEST['flete'];
		if (empty($flete)) {
			$flete=0;
		}else{
			$flete=$this->trucarNumeros($flete,2);
		}
		$envioSobre=$_REQUEST['envioSobre'];
		if (empty($envioSobre)) {
			$envioSobre=0;
		}else{
			$envioSobre=$this->trucarNumeros($envioSobre,2);
		}
		$gastoBancario=$_REQUEST['gastoBancario'];
		if (empty($gastoBancario)) {
			$gastoBancario=0;
		}else{
			$gastoBancario=$this->trucarNumeros($gastoBancario,2);
		}
		$costoMantenimiento=$_REQUEST['costoMantenimiento'];
		if (empty($costoMantenimiento)) {
			$costoMantenimiento=0;
		}else{
			$costoMantenimiento=$this->trucarNumeros($costoMantenimiento,2);
		}
		

		$detalleordencobro=$this->AutoLoadModel('detalleordencobro');
		$ordencobro=$this->AutoLoadModel('ordencobro');
		$letras=$this->AutoLoadModel('condicionletra');
		$documento=$this->AutoLoadModel('documento');
		$ordenGasto=$this->AutoLoadModel('ordengasto');

		//recuperamos los datos del detalleordencobro
		$datadetordencobro=$detalleordencobro->buscaDetalleOrdencobro2($iddetalleordencobro);
		$idordencobro=$datadetordencobro[0]['idordencobro'];
		$numeroletra=$datadetordencobro[0]['numeroletra'];
		$importedoc=round($datadetordencobro[0]['saldodoc'],2);

		//recuperamos la orden de venta a la que pertenece y saldoanterior
		$dataOrdenCobro=$ordencobro->buscaOrdencobro($idordencobro);
		$idordenventa=$dataOrdenCobro[0]['idordenventa'];
		$tipoletra=$dataOrdenCobro[0]['tipoletra'];
		$saldoordencobro=round($dataOrdenCobro[0]['saldoordencobro'],2);

		if (!empty($montoContado)) {
			$dataNOrdenCobro['escontado']=1;
		}

		if (!empty($montoCredito0) && !empty($diasCredito0)) {
			$dataNOrdenCobro['escredito']=1;
		}elseif(!empty($montoCredito1) && !empty($diasCredito1)){
			$dataNOrdenCobro['escredito']=1;
		}elseif(!empty($montoCredito2) && !empty($diasCredito2)){
			$dataNOrdenCobro['escredito']=1;
		}elseif(!empty($montoCredito3) && !empty($diasCredito3)){
			$dataNOrdenCobro['escredito']=1;
		}elseif(!empty($montoCredito4) && !empty($diasCredito4)){
			$dataNOrdenCobro['escredito']=1;
		}

		if (!empty($montoLetra) && !empty($diasMontoLetra)) {
			$dataNOrdenCobro['esletras']=1;
			$dataNOrdenCobro['tipoletra']=1;
		}elseif(!empty($nuevaLetra) && !empty($montoLetraNueva)){
			$dataNOrdenCobro['esletras']=1;
			$dataNOrdenCobro['tipoletra']=1;
		}

		$dataNOrdenCobro['importeordencobro']=round($datadetordencobro[0]['saldodoc'],2)+$flete+$envioSobre+$gastoBancario+$costoMantenimiento;
		$dataNOrdenCobro['saldoordencobro']=round($datadetordencobro[0]['saldodoc'],2)+$flete+$envioSobre+$gastoBancario+$costoMantenimiento;
		$dataNOrdenCobro['femision']=date('Y-m-d');
		$dataNOrdenCobro['idordenventa']=$idordenventa;
		$dataNOrdenCobro['idcondicionletra']=$cantidadLetras;
		
		$nuevoIdCobro=$ordencobro->grabaOrdencobro($dataNOrdenCobro);

		if ($nuevoIdCobro) {
			if (!empty($flete) && !empty($diasFlete) && $flete!=0) {
				$dataDetalle1['idordencobro']=$nuevoIdCobro;
				$dataDetalle1['importedoc']=$flete;
				$dataDetalle1['saldodoc']=$flete;
				$dataDetalle1['formacobro']=2;
				$dataDetalle1['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalle1['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasFlete . " day"));
				$dataDetalle1['referencia']=$numeroletra;
				$dataDetalle1['tipogasto']=3;
				$dataDetalle1['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalle1);
			
				$filtro="idordenventa='$idordenventa' and idtipogasto=3";
				$dataOrdenGastoFlete=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoFlete)){
					$dataF['importegasto']=$flete+$dataOrdenGastoFlete[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataF,$dataOrdenGastoFlete[0]['idordengasto']);
				}else{
					$dataF['importegasto']=$flete;
					$dataF['idordenventa']=$idordenventa;
					$dataF['idtipogasto']=3;
					$exitoF=$ordenGasto->graba($dataF);
				}
			}
			if (!empty($envioSobre) && !empty($diasEnvioSobre) && $envioSobre!=0) {
				$dataDetalle2['idordencobro']=$nuevoIdCobro;
				$dataDetalle2['importedoc']=$envioSobre;
				$dataDetalle2['saldodoc']=$envioSobre;
				$dataDetalle2['formacobro']=2;
				$dataDetalle2['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalle2['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasEnvioSobre . " day"));
				$dataDetalle2['referencia']=$numeroletra;
				$dataDetalle2['tipogasto']=5;
				$dataDetalle2['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalle2);
			
				$filtro="idordenventa='$idordenventa' and idtipogasto=5";
				$dataOrdenGastoEnvio=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoEnvio)){
					$dataE['importegasto']=$envioSobre+$dataOrdenGastoEnvio[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataE,$dataOrdenGastoEnvio[0]['idordengasto']);
				}else{
					$dataE['importegasto']=$envioSobre;
					$dataE['idordenventa']=$idordenventa;
					$dataE['idtipogasto']=5;
					$exitoF=$ordenGasto->graba($dataE);
				}
			}
			if (!empty($gastoBancario) && !empty($diasGastoBancario) && $gastoBancario!=0) {
				$dataDetalle3['idordencobro']=$nuevoIdCobro;
				$dataDetalle3['importedoc']=$gastoBancario;
				$dataDetalle3['saldodoc']=$gastoBancario;
				$dataDetalle3['formacobro']=2;
				$dataDetalle3['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalle3['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasGastoBancario . " day"));
				$dataDetalle3['referencia']=$numeroletra;
				$dataDetalle3['tipogasto']=4;
				$dataDetalle3['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalle3);
			
				$filtro="idordenventa='$idordenventa' and idtipogasto=4";
				$dataOrdenGastoBancario=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoBancario)){
					$dataB['importegasto']=$gastoBancario+$dataOrdenGastoBancario[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataB,$dataOrdenGastoBancario[0]['idordengasto']);
				}else{
					$dataB['importegasto']=$gastoBancario;
					$dataB['idordenventa']=$idordenventa;
					$dataB['idtipogasto']=4;
					$exitoF=$ordenGasto->graba($dataB);
				}
			}
			if (!empty($costoMantenimiento) && !empty($diasCostoMantenimiento) && $costoMantenimiento!=0) {
				$dataDetalle4['idordencobro']=$nuevoIdCobro;
				$dataDetalle4['importedoc']=$costoMantenimiento;
				$dataDetalle4['saldodoc']=$costoMantenimiento;
				$dataDetalle4['formacobro']=2;
				$dataDetalle4['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalle4['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasCostoMantenimiento . " day"));
				$dataDetalle4['referencia']=$numeroletra;
				$dataDetalle4['tipogasto']=8;
				$dataDetalle4['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalle4);
			
				$filtro="idordenventa='$idordenventa' and idtipogasto=8";
				$dataOrdenGastoMantenimiento=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoMantenimiento)){
					$dataM['importegasto']=$costoMantenimiento+$dataOrdenGastoMantenimiento[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataM,$dataOrdenGastoMantenimiento[0]['idordengasto']);
				}else{
					$dataM['importegasto']=$costoMantenimiento;
					$dataM['idordenventa']=$idordenventa;
					$dataM['idtipogasto']=8;
					$exitoF=$ordenGasto->graba($dataM);
				}
			}
			if (!empty($montoContado)) {
				$dataDetalleC1['idordencobro']=$nuevoIdCobro;
				$dataDetalleC1['importedoc']=round($montoContado,2);
				$dataDetalleC1['saldodoc']=round($montoContado,2);
				$dataDetalleC1['formacobro']=1;
				$dataDetalleC1['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalleC1['fvencimiento']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalleC1['referencia']=$numeroletra;
				$dataDetalleC1['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalleC1);
			}
			if (!empty($montoCredito0) && !empty($diasCredito0)) {
				$dataDetalle5['idordencobro']=$nuevoIdCobro;
				$dataDetalle5['importedoc']=round($montoCredito0,2);
				$dataDetalle5['saldodoc']=round($montoCredito0,2);
				$dataDetalle5['formacobro']=2;
				$dataDetalle5['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalle5['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasCredito0 . " day"));
				$dataDetalle5['referencia']=$numeroletra;
				$dataDetalle5['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalle5);
			}
			if (!empty($montoCredito1) && !empty($diasCredito1)) {
				$dataDetalle6['idordencobro']=$nuevoIdCobro;
				$dataDetalle6['importedoc']=round($montoCredito1,2);
				$dataDetalle6['saldodoc']=round($montoCredito1,2);
				$dataDetalle6['formacobro']=2;
				$dataDetalle6['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalle6['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasCredito1 . " day"));
				$dataDetalle6['referencia']=$numeroletra;
				$dataDetalle6['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalle6);
			}
			if (!empty($montoCredito2) && !empty($diasCredito2)) {
				$dataDetalle7['idordencobro']=$nuevoIdCobro;
				$dataDetalle7['importedoc']=round($montoCredito2,2);
				$dataDetalle7['saldodoc']=round($montoCredito2,2);
				$dataDetalle7['formacobro']=2;
				$dataDetalle7['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalle7['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasCredito2 . " day"));
				$dataDetalle7['referencia']=$numeroletra;
				$dataDetalle7['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalle7);
			}
			if (!empty($montoCredito3) && !empty($diasCredito3)) {
				$dataDetalle8['idordencobro']=$nuevoIdCobro;
				$dataDetalle8['importedoc']=round($montoCredito3,2);
				$dataDetalle8['saldodoc']=round($montoCredito3,2);
				$dataDetalle8['formacobro']=2;
				$dataDetalle8['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalle8['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasCredito3 . " day"));
				$dataDetalle8['referencia']=$numeroletra;
				$dataDetalle8['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalle8);
			}
			if (!empty($montoCredito4) && !empty($diasCredito4)) {
				$dataDetalle9['idordencobro']=$nuevoIdCobro;
				$dataDetalle9['importedoc']=round($montoCredito4,2);
				$dataDetalle9['saldodoc']=round($montoCredito4,2);
				$dataDetalle9['formacobro']=2;
				$dataDetalle9['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalle9['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasCredito4 . " day"));
				$dataDetalle9['referencia']=$numeroletra;
				$dataDetalle9['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalle9);
			}
			if (!empty($montoLetra) && !empty($diasMontoLetra)) {
				$dataDetalleL1['idordencobro']=$nuevoIdCobro;
				$dataDetalleL1['importedoc']=round($montoLetra,2);
				$dataDetalleL1['saldodoc']=round($montoLetra,2);
				$dataDetalleL1['formacobro']=3;
				$dataDetalleL1['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalleL1['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasMontoLetra . " day"));
				$dataDetalleL1['referencia']=$numeroletra;
				$dataDetalleL1['numeroletra']=$detalleordencobro->GeneraNumeroLetra();
				$dataDetalleL1['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalleL1);

				$datadocumentos['idordenventa']=$idordenventa;
				$datadocumentos['fechadoc']=date('Y-m-d',strtotime($nuevaFecha));
				$datadocumentos['numdoc']=$dataDetalleL1['numeroletra'];
				$datadocumentos['serie']=1;
				$datadocumentos['montofacturado']=round($montoLetra,2);
				$datadocumentos['nombredoc']=7;
				$grabaDoc=$documento->grabaDocumento($datadocumentos);
			}


			if (!empty($nuevaLetra) && !empty($montoLetraNueva)) {
				//recuperamos la condicion de la letra y numero de letras
				$dataletras=$letras->buscaxId($nuevaLetra);
				$cantidadletras=$dataletras[0]['cantidadletra'];
				$diasletra=split('/', $dataletras[0]['nombreletra']);

				for ($i=0; $i <$cantidadletras ; $i++) { 
					//creamos su detalleordencobro
					$dataDetalleL2['idordencobro']=$nuevoIdCobro;
					$dataDetalleL2['importedoc']=round(($montoLetraNueva/$cantidadletras),2);
					$dataDetalleL2['saldodoc']=round(($montoLetraNueva/$cantidadletras),2);
					$dataDetalleL2['formacobro']=3;
					$dataDetalleL2['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
					$dataDetalleL2['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasletra[$i] . " day"));
					$dataDetalleL2['referencia']=$numeroletra;
					$dataDetalleL2['numeroletra']=$detalleordencobro->GeneraNumeroLetra();
					$dataDetalleL2['idpadre']=$iddetalleordencobro;
					$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalleL2);

					$datadocumentos['idordenventa']=$idordenventa;
					$datadocumentos['fechadoc']=date('Y-m-d',strtotime($nuevaFecha));
					$datadocumentos['numdoc']=$dataDetalleL2['numeroletra'];
					$datadocumentos['serie']=1;
					$datadocumentos['montofacturado']=$dataDetalleL2['importedoc'];
					$datadocumentos['nombredoc']=7;
					$grabaDoc=$documento->grabaDocumento($datadocumentos);
				}
			}
			
			if ($graba) {

				$data['situacion']='reprogramado';
				
				$exito=$detalleordencobro->actualizaDetalleOrdencobro($data,$iddetalleordencobro);
				
				echo 'iddetalleordencobro: '.$iddetalleordencobro;
				if ($exito) {
					$dataoc['saldoordencobro']=round($saldoordencobro-$datadetordencobro[0]['saldodoc'],2);
					if ($dataoc['saldoordencobro']<0.01) {
						$dataoc['situacion']="cancelado";

					}
					
					$exito2=$ordencobro->actualizaOrdencobro($dataoc,$idordencobro);
					if ($exito2) {
						echo 'correcto';
					}else{
						echo 'tercer error';
					}
					
				}
			}else{
				echo 'segundo error';
			}
		}else{echo 'segundo error';}
	}
	function reprogramacionTotalDeuda(){
		$valorEnvio=$_REQUEST['valorEnvio'];
		$idordenventa=$_REQUEST['idOrdenVenta'];

		$flete=$_REQUEST['flete'];
		$diasFlete=$_REQUEST['diasFlete'];
		$envioSobre=$_REQUEST['envioSobre'];
		$diasEnvioSobre=$_REQUEST['diasEnvioSobre'];
		$gastoBancario=$_REQUEST['gastoBancario'];
		$diasGastoBancario=$_REQUEST['diasGastoBancario'];
		$costoMantenimiento=$_REQUEST['costoMantenimiento'];
		$diasCostoMantenimiento=$_REQUEST['diasCostoMantenimiento'];
		$montoContado=$_REQUEST['montoContado'];
		$montoCredito0=$_REQUEST['montoCredito0'];
		$montoCredito1=$_REQUEST['montoCredito1'];
		$montoCredito2=$_REQUEST['montoCredito2'];
		$montoCredito3=$_REQUEST['montoCredito3'];
		$montoCredito4=$_REQUEST['montoCredito4'];
		$diasCredito0=$_REQUEST['diasCredito0'];
		$diasCredito1=$_REQUEST['diasCredito1'];
		$diasCredito2=$_REQUEST['diasCredito2'];
		$diasCredito3=$_REQUEST['diasCredito3'];
		$diasCredito4=$_REQUEST['diasCredito4'];
		$montoLetra=$_REQUEST['montoLetra'];
		$diasMontoLetra=$_REQUEST['diasMontoLetra'];
		$cantidadLetras=$_REQUEST['cantidadLetras'];
		$montoLetraNueva=$_REQUEST['montoLetraNueva'];
		$nuevaLetra=$_REQUEST['nuevaLetra'];
		$nuevaFecha=$_REQUEST['nuevaFecha'];
		
		//
	$flete=$_REQUEST['flete'];
		if (empty($flete)) {
			$flete=0;
		}else{
			$flete=$this->trucarNumeros($flete,2);
		}
		$envioSobre=$_REQUEST['envioSobre'];
		if (empty($envioSobre)) {
			$envioSobre=0;
		}else{
			$envioSobre=$this->trucarNumeros($envioSobre,2);
		}
		$gastoBancario=$_REQUEST['gastoBancario'];
		if (empty($gastoBancario)) {
			$gastoBancario=0;
		}else{
			$gastoBancario=$this->trucarNumeros($gastoBancario,2);
		}
		$costoMantenimiento=$_REQUEST['costoMantenimiento'];
		if (empty($costoMantenimiento)) {
			$costoMantenimiento=0;
		}else{
			$costoMantenimiento=$this->trucarNumeros($costoMantenimiento,2);
		}
		

		$detalleordencobro=$this->AutoLoadModel('detalleordencobro');
		$ordencobro=$this->AutoLoadModel('ordencobro');
		$letras=$this->AutoLoadModel('condicionletra');
		$documento=$this->AutoLoadModel('documento');
		$ordenGasto=$this->AutoLoadModel('ordengasto');


		if (!empty($montoContado)) {
			$dataNOrdenCobro['escontado']=1;
		}

		if (!empty($montoCredito0) && !empty($diasCredito0)) {
			$dataNOrdenCobro['escredito']=1;
		}elseif(!empty($montoCredito1) && !empty($diasCredito1)){
			$dataNOrdenCobro['escredito']=1;
		}elseif(!empty($montoCredito2) && !empty($diasCredito2)){
			$dataNOrdenCobro['escredito']=1;
		}elseif(!empty($montoCredito3) && !empty($diasCredito3)){
			$dataNOrdenCobro['escredito']=1;
		}elseif(!empty($montoCredito4) && !empty($diasCredito4)){
			$dataNOrdenCobro['escredito']=1;
		}

		if (!empty($montoLetra) && !empty($diasMontoLetra)) {
			$dataNOrdenCobro['esletras']=1;
			$dataNOrdenCobro['tipoletra']=1;
		}elseif(!empty($nuevaLetra) && !empty($montoLetraNueva)){
			$dataNOrdenCobro['esletras']=1;
			$dataNOrdenCobro['tipoletra']=1;
		}

		$dataNOrdenCobro['importeordencobro']=$valorEnvio+$flete+$envioSobre+$gastoBancario+$costoMantenimiento;
		$dataNOrdenCobro['saldoordencobro']=$valorEnvio+$flete+$envioSobre+$gastoBancario+$costoMantenimiento;
		$dataNOrdenCobro['femision']=date('Y-m-d');
		$dataNOrdenCobro['idordenventa']=$idordenventa;
		$dataNOrdenCobro['idcondicionletra']=$cantidadLetras;
		
		$nuevoIdCobro=$ordencobro->grabaOrdencobro($dataNOrdenCobro);

		if ($nuevoIdCobro) {
			if (!empty($flete) && !empty($diasFlete) && $flete!=0) {
				$dataDetalle1['idordencobro']=$nuevoIdCobro;
				$dataDetalle1['importedoc']=$flete;
				$dataDetalle1['saldodoc']=$flete;
				$dataDetalle1['formacobro']=2;
				$dataDetalle1['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalle1['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasFlete . " day"));
				$dataDetalle1['referencia']=$numeroletra;
				$dataDetalle1['tipogasto']=3;
				$dataDetalle1['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalle1);
			
				$filtro="idordenventa='$idordenventa' and idtipogasto=3";
				$dataOrdenGastoFlete=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoFlete)){
					$dataF['importegasto']=$flete+$dataOrdenGastoFlete[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataF,$dataOrdenGastoFlete[0]['idordengasto']);
				}else{
					$dataF['importegasto']=$flete;
					$dataF['idordenventa']=$idordenventa;
					$dataF['idtipogasto']=3;
					$exitoF=$ordenGasto->graba($dataF);
				}
			}
			if (!empty($envioSobre) && !empty($diasEnvioSobre) && $envioSobre!=0) {
				$dataDetalle2['idordencobro']=$nuevoIdCobro;
				$dataDetalle2['importedoc']=$envioSobre;
				$dataDetalle2['saldodoc']=$envioSobre;
				$dataDetalle2['formacobro']=2;
				$dataDetalle2['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalle2['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasEnvioSobre . " day"));
				$dataDetalle2['referencia']=$numeroletra;
				$dataDetalle2['tipogasto']=5;
				$dataDetalle2['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalle2);
			
				$filtro="idordenventa='$idordenventa' and idtipogasto=5";
				$dataOrdenGastoEnvio=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoEnvio)){
					$dataE['importegasto']=$envioSobre+$dataOrdenGastoEnvio[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataE,$dataOrdenGastoEnvio[0]['idordengasto']);
				}else{
					$dataE['importegasto']=$envioSobre;
					$dataE['idordenventa']=$idordenventa;
					$dataE['idtipogasto']=5;
					$exitoF=$ordenGasto->graba($dataE);
				}
			}
			if (!empty($gastoBancario) && !empty($diasGastoBancario) && $gastoBancario!=0) {
				$dataDetalle3['idordencobro']=$nuevoIdCobro;
				$dataDetalle3['importedoc']=$gastoBancario;
				$dataDetalle3['saldodoc']=$gastoBancario;
				$dataDetalle3['formacobro']=2;
				$dataDetalle3['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalle3['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasGastoBancario . " day"));
				$dataDetalle3['referencia']=$numeroletra;
				$dataDetalle3['tipogasto']=4;
				$dataDetalle3['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalle3);
			
				$filtro="idordenventa='$idordenventa' and idtipogasto=4";
				$dataOrdenGastoBancario=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoBancario)){
					$dataB['importegasto']=$gastoBancario+$dataOrdenGastoBancario[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataB,$dataOrdenGastoBancario[0]['idordengasto']);
				}else{
					$dataB['importegasto']=$gastoBancario;
					$dataB['idordenventa']=$idordenventa;
					$dataB['idtipogasto']=4;
					$exitoF=$ordenGasto->graba($dataB);
				}
			}
			if (!empty($costoMantenimiento) && !empty($diasCostoMantenimiento) && $costoMantenimiento!=0) {
				$dataDetalle4['idordencobro']=$nuevoIdCobro;
				$dataDetalle4['importedoc']=$costoMantenimiento;
				$dataDetalle4['saldodoc']=$costoMantenimiento;
				$dataDetalle4['formacobro']=2;
				$dataDetalle4['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalle4['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasCostoMantenimiento . " day"));
				$dataDetalle4['referencia']=$numeroletra;
				$dataDetalle4['tipogasto']=8;
				$dataDetalle4['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalle4);
			
				$filtro="idordenventa='$idordenventa' and idtipogasto=8";
				$dataOrdenGastoMantenimiento=$ordenGasto->buscaxFiltro($filtro);
				if (!empty($dataOrdenGastoMantenimiento)){
					$dataM['importegasto']=$costoMantenimiento+$dataOrdenGastoMantenimiento[0]['importegasto'];
					$exitoF=$ordenGasto->actualiza($dataM,$dataOrdenGastoMantenimiento[0]['idordengasto']);
				}else{
					$dataM['importegasto']=$costoMantenimiento;
					$dataM['idordenventa']=$idordenventa;
					$dataM['idtipogasto']=8;
					$exitoF=$ordenGasto->graba($dataM);
				}
			}
			if (!empty($montoContado)) {
				$dataDetalleC1['idordencobro']=$nuevoIdCobro;
				$dataDetalleC1['importedoc']=round($montoContado,2);
				$dataDetalleC1['saldodoc']=round($montoContado,2);
				$dataDetalleC1['formacobro']=1;
				$dataDetalleC1['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalleC1['fvencimiento']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalleC1['referencia']=$numeroletra;
				$dataDetalleC1['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalleC1);
			}
			if (!empty($montoCredito0) && !empty($diasCredito0)) {
				$dataDetalle5['idordencobro']=$nuevoIdCobro;
				$dataDetalle5['importedoc']=round($montoCredito0,2);
				$dataDetalle5['saldodoc']=round($montoCredito0,2);
				$dataDetalle5['formacobro']=2;
				$dataDetalle5['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalle5['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasCredito0 . " day"));
				$dataDetalle5['referencia']=$numeroletra;
				$dataDetalle5['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalle5);
			}
			if (!empty($montoCredito1) && !empty($diasCredito1)) {
				$dataDetalle6['idordencobro']=$nuevoIdCobro;
				$dataDetalle6['importedoc']=round($montoCredito1,2);
				$dataDetalle6['saldodoc']=round($montoCredito1,2);
				$dataDetalle6['formacobro']=2;
				$dataDetalle6['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalle6['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasCredito1 . " day"));
				$dataDetalle6['referencia']=$numeroletra;
				$dataDetalle6['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalle6);
			}
			if (!empty($montoCredito2) && !empty($diasCredito2)) {
				$dataDetalle7['idordencobro']=$nuevoIdCobro;
				$dataDetalle7['importedoc']=round($montoCredito2,2);
				$dataDetalle7['saldodoc']=round($montoCredito2,2);
				$dataDetalle7['formacobro']=2;
				$dataDetalle7['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalle7['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasCredito2 . " day"));
				$dataDetalle7['referencia']=$numeroletra;
				$dataDetalle7['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalle7);
			}
			if (!empty($montoCredito3) && !empty($diasCredito3)) {
				$dataDetalle8['idordencobro']=$nuevoIdCobro;
				$dataDetalle8['importedoc']=round($montoCredito3,2);
				$dataDetalle8['saldodoc']=round($montoCredito3,2);
				$dataDetalle8['formacobro']=2;
				$dataDetalle8['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalle8['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasCredito3 . " day"));
				$dataDetalle8['referencia']=$numeroletra;
				$dataDetalle8['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalle8);
			}
			if (!empty($montoCredito4) && !empty($diasCredito4)) {
				$dataDetalle9['idordencobro']=$nuevoIdCobro;
				$dataDetalle9['importedoc']=round($montoCredito4,2);
				$dataDetalle9['saldodoc']=round($montoCredito4,2);
				$dataDetalle9['formacobro']=2;
				$dataDetalle9['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalle9['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasCredito4 . " day"));
				$dataDetalle9['referencia']=$numeroletra;
				$dataDetalle9['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalle9);
			}
			if (!empty($montoLetra) && !empty($diasMontoLetra)) {
				$dataDetalleL1['idordencobro']=$nuevoIdCobro;
				$dataDetalleL1['importedoc']=round($montoLetra,2);
				$dataDetalleL1['saldodoc']=round($montoLetra,2);
				$dataDetalleL1['formacobro']=3;
				$dataDetalleL1['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
				$dataDetalleL1['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasMontoLetra . " day"));
				$dataDetalleL1['referencia']=$numeroletra;
				$dataDetalleL1['numeroletra']=$detalleordencobro->GeneraNumeroLetra();
				$dataDetalleL1['idpadre']=$iddetalleordencobro;
				$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalleL1);

				$datadocumentos['idordenventa']=$idordenventa;
				$datadocumentos['fechadoc']=date('Y-m-d',strtotime($nuevaFecha));
				$datadocumentos['numdoc']=$dataDetalleL1['numeroletra'];
				$datadocumentos['serie']=1;
				$datadocumentos['montofacturado']=$montoLetra;
				$datadocumentos['nombredoc']=7;
				$grabaDoc=$documento->grabaDocumento($datadocumentos);
			}


			if (!empty($nuevaLetra) && !empty($montoLetraNueva)) {
				//recuperamos la condicion de la letra y numero de letras
				$dataletras=$letras->buscaxId($nuevaLetra);
				$cantidadletras=$dataletras[0]['cantidadletra'];
				$diasletra=split('/', $dataletras[0]['nombreletra']);

				for ($i=0; $i <$cantidadletras ; $i++) { 
					//creamos su detalleordencobro
					$dataDetalleL2['idordencobro']=$nuevoIdCobro;
					$dataDetalleL2['importedoc']=round(($montoLetraNueva/$cantidadletras),2);
					$dataDetalleL2['saldodoc']=round(($montoLetraNueva/$cantidadletras),2);
					$dataDetalleL2['formacobro']=3;
					$dataDetalleL2['fechagiro']=date('Y-m-d',strtotime($nuevaFecha));
					$dataDetalleL2['fvencimiento']=date('Y-m-d',strtotime("$nuevaFecha + " . $diasletra[$i] . " day"));
					$dataDetalleL2['referencia']=$numeroletra;
					$dataDetalleL2['numeroletra']=$detalleordencobro->GeneraNumeroLetra();
					$dataDetalleL2['idpadre']=$iddetalleordencobro;
					$graba=$detalleordencobro->grabaDetalleOrdenVentaCobro($dataDetalleL2);

					$datadocumentos['idordenventa']=$idordenventa;
					$datadocumentos['fechadoc']=date('Y-m-d',strtotime($nuevaFecha));
					$datadocumentos['numdoc']=$dataDetalleL2['numeroletra'];
					$datadocumentos['serie']=1;
					$datadocumentos['montofacturado']=$dataDetalleL2['importedoc'];
					$datadocumentos['nombredoc']=7;
					$grabaDoc=$documento->grabaDocumento($datadocumentos);
				}
			}
			
			if ($graba) {
				
				$dataOrdenesCobro=$ordencobro->buscaxordenventa($idordenventa);
				$cantidadOrdenes=count($dataOrdenesCobro);
				echo 'entro'.'cantidad '.$cantidadOrdenes.'<br>';
				for ($j=0; $j <$cantidadOrdenes ; $j++) { 
					$idordencobro=$dataOrdenesCobro[$j]['idordencobro'];
					echo $idordencobro.' es';
					$filtro="situacion='' and idordencobro='$idordencobro' and idordencobro!='$nuevoIdCobro' ";
					$data['situacion']='reprogramado';
					
					$exito=$detalleordencobro->actualizaDetalleOrdenCompraxFiltro($data,$filtro);
					echo 'exito: '.$exito;
				}
				
				
				
				if ($exito) {
					$dataoc['saldoordencobro']=0;
					$dataoc['situacion']="cancelado";
					$filtro2="idordenventa='$idordenventa' and idordencobro!='$nuevoIdCobro'";
					$exito2=$ordencobro->actualizaOrdencobroxfiltro($dataoc,$filtro2);
					if ($exito2) {
						echo 'correcto';
					}else{
						echo 'tercer error';
					}
					
				}
			}else{
				echo 'segundo error';
			}
		}else{echo 'segundo error';}
	}
	/***************************/
	function asignacionpagos(){
		
		$letras=New Letras();
		$data['letras']=$letras->listado();
		
		$this->view->show("/ingresos/asignacionpagos.phtml",$data);
	}
	function vistadiaria()
	{
		$objIngreso=New Ingresos();
		$actor=$this->AutoLoadModel('actor');
		
		$dataIngresos=$objIngreso->resumeningresoshoy();
		$cantidad=count($dataIngresos);
		for ($i=0; $i <$cantidad ; $i++) { 
			$dataIngresos[$i]['nombreIngreso']=$this->configIni('TipoIngreso',$dataIngresos[$i]['tipocobro']);
			$dataActor=$actor->buscarxid($dataIngresos[$i]['usuariocreacion']);
			$dataIngresos[$i]['creador']=$dataActor[0]['nombres'].' '.$dataActor[0]['apellidopaterno'].' '.$dataActor[0]['apellidomaterno'];
		}
		$data['ingresos']=$dataIngresos;
		$this->view->show("/ingresos/listadohoy.phtml",$data);
	}
	// function BuscaOrden(){
	// 	$numeroOrden=$_REQUEST['txtBusqueda'];
	// 	$objOrden=$this->AutoLoadModel("Orden");
	// 	$dataOrden=$objOrden->
	// }

	function IngresosxOrdenventa(){
		$ingresos=$this->AutoLoadModel('ingresos');
		$idordenventa=$_REQUEST['id'];
		$dataGuia=$this->AutoLoadModel("OrdenVenta");
		$idMoneda=$dataGuia->BuscarCampoOVxId($idordenventa,"idmoneda");//PREGUNTAR SI ACTUAL O AL ELEGIDO EN LA COMPRA
		$simbolo=($idMoneda==1)?"S/.":"US $";
		$dataSuma=$ingresos->sumaIngresos($idordenventa);
		$dataIngresos=$ingresos->listarIngresosConCobrador($idordenventa);
		$cantidad=count($dataIngresos);
		if (count($dataSuma)==0) {
			$dataSuma[0]['sum(montoingresado)']=0;
			$dataSuma[0]['sum(montoasignado)']=0;
			$dataSuma[0]['sum(saldo)']=0;
		}
		echo 	"<thead>
					<tr>
						<th colspan='3'>Ingreso Total</th>
						<th colspan='3'>Monto Asignado</th>
						<th colspan='3'>Saldo </th>
					 </tr>
				</thead>
			 	<tbody>
			 		<tr>
					 	<td colspan='3'>&nbsp ".$simbolo." ".number_format($dataSuma[0]['sum(montoingresado)'],2)."<input type='hidden' value='".($dataSuma[0]['sum(montoingresado)'])."' id='montoingresado'></td>
						<td colspan='3'>&nbsp ".$simbolo." ".number_format($dataSuma[0]['sum(montoasignado)'],2)."<input type='hidden' value='".$dataSuma[0]['sum(montoasignado)']."' id='montoasignado'></td>
						<td colspan='3'>&nbsp ".$simbolo." ".number_format($dataSuma[0]['sum(saldo)'],2)."<input type='hidden' value='".$dataSuma[0]['sum(saldo)']."' id='saldo'></td>
			 		</tr>";
			echo 	"<tr><td colspan='12'>&nbsp;</td></tr>";
			$fila.="<tr>";
			$fila.="<th>N°</th>";
			$fila.="<th>Cobrador</th>";
			$fila.="<th>Tipo Ingreso</th>";
			$fila.="<th>M. Ingresado</th>";
			$fila.="<th>M. Asignado</th>";
			$fila.="<th>Saldo</th>";
			$fila.="<th>M. Liberado</th>";
			$fila.="<th>M. Anulado</th>";
			$fila.="<th>F. Cobroooooo</th>";
			$fila.="<th>N° recibo</th>";
			$fila.="<th>N° Operacion</th>";
			$fila.="<th>Observaciones</th>";
			$fila.="</tr>";		
		for ($i=0; $i <$cantidad ; $i++) { 
			$fila.="<tr>";
			$fila.="<td>".($i+1)."<input type='hidden' class='idingresos' value='".$dataIngresos[$i]['idingresos']."'></td>";
			$fila.="<td>".($dataIngresos[$i]['nombres'].' '.$dataIngresos[$i]['apellidopaterno'].' '.$dataIngresos[$i]['apellidomaterno'])."</td>";
			$fila.="<td>".$this->configIni("TipoIngreso",$dataIngresos[$i]['tipocobro'])."</td>";
			$fila.="<td> ".$simbolo." ".number_format($dataIngresos[$i]['montoingresado'],2)."</td>";
			$fila.="<td> ".$simbolo." ".number_format($dataIngresos[$i]['montoasignado'],2)."</td>";
			$fila.="<td> ".$simbolo." ".number_format($dataIngresos[$i]['saldo'],2)."<input type='hidden' class='saldo' value='".$dataIngresos[$i]['saldo']."'></td>";
			$fila.="<td> ".$simbolo." ".number_format($dataIngresos[$i]['montoliberado'],2)."</td>";
			$fila.="<td> ".$simbolo." ".number_format($dataIngresos[$i]['montoanulado'],2)."</td>";
			$fila.="<td>".($dataIngresos[$i]['fcobro'])."</td>";
			$fila.="<td>".($dataIngresos[$i]['nrorecibo'])."</td>";
			$fila.="<td>".($dataIngresos[$i]['nrooperacion'])."</td>";
			$fila.="<td>".($dataIngresos[$i]['observaciones'])."</td>";
			$fila.="</tr>";
		}
		echo $fila;
		echo 	"<tr><td colspan='12'>&nbsp;</td></tr>";
		echo "</tbody>";
	}


	//zona de asignacion pagos
	function cancelar(){
		$ingresos=$this->AutoLoadModel('ingresos');
		$cobroingreso=$this->AutoLoadModel('detalleordencobroingreso');
		$ordencobro=$this->AutoLoadModel('ordencobro');
		$detOrdenCobro=$this->AutoLoadModel('detalleordencobro');

		$idordenventa=$_REQUEST['idordenventa'];
		$fechapago=$_REQUEST['fechapago'];
		if (!empty($fechapago)) {
			$fechapago=date('Y-m-d',strtotime($fechapago));
		}else{
			$fechapago=date('Y-m-d');
		}
		$iddetalleordencobro=$_REQUEST['iddetalleordencobro'];
		$saldogeneral=round($_REQUEST['saldogeneral'],2);
		$cantidadgastada=0;

		$datalistaingresos=$ingresos->listarIngresosConSaldo($idordenventa);


		$cantidadingresos=count($datalistaingresos);
		
		if ($cantidadingresos!=0) {
			for ($i=0; $i <$cantidadingresos ; $i++) { 
				$datalistaingresos[$i]['saldo']=round($datalistaingresos[$i]['saldo'],2);
				$datalistaingresos[$i]['montoasignado']=round($datalistaingresos[$i]['montoasignado'],2);
				if ($saldogeneral<=0) {
					echo 'no tiene saldo';
				}elseif ($saldogeneral>=$datalistaingresos[$i]['saldo'] && $saldogeneral>0) {
					//echo $datalistaingresos[$i]['saldo'];
					$saldogeneral=$saldogeneral-$datalistaingresos[$i]['saldo'];
					$cantidadgastada+=$datalistaingresos[$i]['saldo'];
					$saldoprovicional=$datalistaingresos[$i]['saldo'];
					$dataingreso['saldo']=0;
					$dataingreso['montoasignado']=$datalistaingresos[$i]['montoasignado']+$saldoprovicional;
					$exito_y=$ingresos->actualizaxid($dataingreso,$datalistaingresos[$i]['idingresos']);
					if ($exito_y) {
						$dataingresoorden['montop']=$saldoprovicional;
						$dataingresoorden['iddetalleordencobro']=$iddetalleordencobro;
						$dataingresoorden['idingreso']=$datalistaingresos[$i]['idingresos'];
						$graba_n=$cobroingreso->grabadetalleordencobroingreso($dataingresoorden);
						if (!$graba_n) {
							echo 'segundo error';
						}
					}else{echo 'primer error';}
					
				}elseif($saldogeneral<$datalistaingresos[$i]['saldo'] && $saldogeneral>0){
					//echo $datalistaingresos[$i]['saldo'];
					$dataingreso['saldo']=$datalistaingresos[$i]['saldo']-$saldogeneral;
					$dataingreso['montoasignado']=$datalistaingresos[$i]['montoasignado']+$saldogeneral;
					
					$exito_y=$ingresos->actualizaxid($dataingreso,$datalistaingresos[$i]['idingresos']);
					if ($exito_y) {
						$dataingresoorden['montop']=$saldogeneral;
						$dataingresoorden['iddetalleordencobro']=$iddetalleordencobro;
						$dataingresoorden['idingreso']=$datalistaingresos[$i]['idingresos'];
						$graba_n=$cobroingreso->grabadetalleordencobroingreso($dataingresoorden);
						$cantidadgastada+=$saldogeneral;
						$saldogeneral=0;
						if (!$graba_n) {
							echo 'cuarto error';
						}
					}else{echo 'tercer error';}

				}
				
			}

		}else{echo 'No hay Ingresos suficientes para esta Orden';}
		if ($exito_y && $graba_n) {
			//recuperamos los datos del detalleordencobro como el idordencobro
			$datadetordencobro=$detOrdenCobro->buscaDetalleOrdencobro($iddetalleordencobro);
			$idordencobro=$datadetordencobro[0]['idordencobro'];
			$saldodoc=$datadetordencobro[0]['saldodoc'];
			$montoprotesto=$datadetordencobro[0]['montoprotesto'];
			$gastosrenovacion=$datadetordencobro[0]['gastosrenovacion'];
			if ($montoprotesto==1) {
				$data['montoprotesto']=2;
			}
			if ($gastosrenovacion==1) {
				$data['gastosrenovacion']=2;
			}
			$saldodetalleordencobro=$saldodoc-$cantidadgastada;
			$data['saldodoc']=$saldodetalleordencobro;
			$data['fechapago']=$fechapago;
			if ($saldodetalleordencobro<0.1) {
				$data['situacion']="cancelado";
				$data['saldodoc']=0;
				//asignar el valor del saldo de la detalleordecobro para que no acumule decimales la orden cobro
				$cantidadgastada=$saldodoc;
			}

			$exito2=$detOrdenCobro->actualizaDetalleOrdencobro($data,$iddetalleordencobro);
			if ($exito2) {
				//recuperamos algunos datos del antiguo ordencobro
				$ordencobroantiguo=$ordencobro->buscaOrdencobro($idordencobro);
				$saldoordencobroA=$ordencobroantiguo[0]['saldoordencobro'];

				$saldoordencobro=$saldoordencobroA-$cantidadgastada;
				$dataoc['saldoordencobro']=$saldoordencobro;

				if ($saldoordencobro<0.1) {
					$dataoc['situacion']='CANCELADO';
					$dataoc['saldoordencobro']=0;
				}
				$exito3=$ordencobro->actualizaOrdencobro($dataoc,$idordencobro);
				if ($exito3) {
					echo 'La transaccion se realizo correctamente';
				}else{
					echo 'sexto error';
				}
			}else{echo 'quinto error';}

			
		}
	}
	/***************************/
	function verificarrecibo(){
		$ingresos=$this->AutoLoadModel('ingresos');
		$nrorecibo=$_REQUEST['nrorecibo'];
		$cantidad=$ingresos->verificarrecibo($nrorecibo);
		if ($cantidad!=0) {
			$respuesta['verificacion']=false;
		}else{
			$respuesta['verificacion']=true;
		}
		echo json_encode($respuesta);
	}

	function asignarIngresosFavor(){

		$this->view->Show('/ingresos/asignarIngresosFavor.phtml',$data);
	}

	function ingresosxOrdenventaLista(){
		$idordenventa=$_REQUEST['idordenventa'];
		$ingresos=$this->AutoLoadModel('ingresos');
		$dataIngresos=$ingresos->listarIngresosConCobrador($idordenventa);
		$actor=$this->AutoLoadModel('actor');
		$cantidad=count($dataIngresos);

		$fila.="<table>";	
		$fila.="<thead>";	
		$fila.="<tr>";
		$fila.="<th>N°</th>";
		$fila.="<th>Cobrador</th>";
		$fila.="<th>Tipo Ingreso</th>";
		$fila.="<th>M. Ingresado</th>";
		$fila.="<th>M. Usado</th>";
		$fila.="<th>Saldo</th>";
		$fila.="<th>M. Liberado</th>";
		$fila.="<th>M. Anulado</th>";
		$fila.="<th>Fecha Cobro</th>";
		$fila.="<th>N° recibo</th>";
		$fila.="<th>N° Operacion</th>";
		$fila.="<th>U. Ingreso</th>";
		$fila.="<th>U. Modifico</th>";
		$fila.="<th>Observaciones</th>";
		$fila.="<th colspan='2'>Accion</th>";
		$fila.="</tr>";
		$fila.="</thead>";
		$fila.="<tbody>";
		for ($i=0; $i <$cantidad ; $i++) { 
			$dataUsuarioC=$actor->buscarxid($dataIngresos[$i]['usuariocreacion']);
			$dataUsuarioM=$actor->buscarxid($dataIngresos[$i]['usuariomodificacion']);
			$fila.="<tr>";
			$fila.="<td>".($i+1)."<input type='hidden' class='idingresos' value='".$dataIngresos[$i]['idingresos']."'></td>";
			$fila.="<td>".($dataIngresos[$i]['nombres'].' '.$dataIngresos[$i]['apellidopaterno'].' '.$dataIngresos[$i]['apellidomaterno'])."</td>";
			$fila.="<td>".$this->configIni("TipoIngreso",$dataIngresos[$i]['tipocobro'])."</td>";
			$fila.="<td>S/.".number_format($dataIngresos[$i]['montoingresado'],2)."</td>";
			$fila.="<td>S/.".number_format($dataIngresos[$i]['montoasignado'],2)."</td>";
			$fila.="<td>S/.".number_format($dataIngresos[$i]['saldo'],2)."<input type='hidden' class='saldo' value='".$dataIngresos[$i]['saldo']."'></td>";
			$fila.="<td>S/.".number_format($dataIngresos[$i]['montoliberado'],2)."</td>";
			$fila.="<td>S/.".number_format($dataIngresos[$i]['montoanulado'],2)."</td>";
			$fila.="<td>".($dataIngresos[$i]['fcobro'])."</td>";
			$fila.="<td>".($dataIngresos[$i]['nrorecibo'])."</td>";
			$fila.="<td>".($dataIngresos[$i]['nrooperacion'])."</td>";
			$fila.="<td>".($dataUsuarioC[$i]['nombres'].' '.$dataUsuarioC[$i]['apellidopaterno'].' '.$dataUsuarioC[$i]['apellidomaterno'])."</td>";
			$fila.="<td>".($dataUsuarioM[$i]['nombres'].' '.$dataUsuarioM[$i]['apellidopaterno'].' '.$dataUsuarioM[$i]['apellidomaterno'])."</td>";
			$fila.="<td>".($dataIngresos[$i]['observaciones'])."</td>";
			if ($dataIngresos[$i]['saldo']>0) {
				$fila.="<td><a href='#' class='lista' title='Liberar Saldo'><img height='30' width='30' src='/imagenes/dinero2.jpg'></a></td>";
				$fila.="<td><a href='#' class='remover' title='Remover dinero'><img height='30' width='30' src='/imagenes/dinero_5.jpg'></a></td>";
			}
			$fila.="</tr>";
		}
		$fila.="</tbody></table>";
		echo $fila;
	}

	function reprogramar(){
		$Actor=New Actor;
		$letras=New Letras();
		$tipoGasto=$this->AutoLoadModel('tipogasto');
		$data['cobrador']=$Actor->listadoCobradores();
		$Ingresos=New Ingresos();
		$data['letras']=$letras->listado();
		$data['ingresos']=$Ingresos->listarxhoy();
		$data['tipoGasto']=$tipoGasto->listaxCriterio(3);
		$this->view->show('/ingresos/reprogramar.phtml',$data);
	}
	function validacheque(){
		$ingresos=$this->AutoLoadModel('ingresos');
		$banco=$this->AutoLoadModel('banco');
		$dataIngresos=$ingresos->listarIngresosNoValidados();
		$cantidad=count($dataIngresos);
		for ($i=0; $i <$cantidad ; $i++) { 
			$dataBanco=$banco->buscaxId($dataIngresos[$i]['idbancocheque']);
			$dataIngresos[$i]['banco']=$dataBanco[0]['codigo'];
		}
		$data['Ingresos']=$dataIngresos;
		$data['banco']=$banco->listado();
		$this->view->show('/ingresos/validarcheque.phtml',$data);
	}
	function eliminarCheque(){
		$ingreso=$this->AutoLoadModel('ingresos');

		$idingresos=$_REQUEST['id'];

		$exito=$ingreso->eliminar($idingresos);
		if ($exito) {
			$ruta['ruta']="/ingresos/validacheque";
			$this->view->show("ruteador.phtml",$ruta);
		}
	}
	function aceptarCheque(){
		$ingreso=$this->AutoLoadModel('ingresos');

		//$idingresos=$_REQUEST['id'];
		$data['ctacorriente']=$_REQUEST['nroCuenta'];
		$data['fcobro']=$_REQUEST['fcobro'];
		$data['nrooperacion']=$_REQUEST['nrooperacion'];
		$idingresos=$_REQUEST['idingresos'];
		$data['esvalidado']=1;
		$exito=$ingreso->actualizaxid($data,$idingresos);
		
		echo $exito;

	}
	function cargaIngresos(){
		$idcliente=$_REQUEST['idCliente'];
		$idcobrador=$_REQUEST['idCobrador'];
		$idordenventa=$_REQUEST['idOrdenVenta'];
		$idtipocobro=$_REQUEST['idTipoCobro'];
		$fechaInicio=$_REQUEST['fechaInicio'];
		if (!empty($fechaInicio)) {
			$fechaInicio=date('Y-m-d',strtotime($fechaInicio));
		}
		$fechaFinal=$_REQUEST['fechaFinal'];
		if (!empty($fechaFinal)) {
			$fechaFinal=date('Y-m-d',strtotime($fechaFinal));
		}
		$monto=$_REQUEST['monto'];
		$nrorecibo=$_REQUEST['nroRecibo'];
		$simbolo=$_REQUEST['simbolo'];

		if (!empty($monto)) {
			$monto=' and montoingresado'.$simbolo.$monto;
		}

		//echo $columna.="<tr><td>".($monto)."</td></tr>";;
		//exit;
		$ingresos=$this->AutoLoadModel('reporte');
		$ordenventa=$this->AutoLoadModel('ordenventa');
		$tipoIngreso=$this->tipoIngreso();
		$dataIngresos=$ingresos->reportingresos($idordenventa,$idcliente,$idcobrador,$nrorecibo,$fechaInicio,$fechaFinal,$monto,$idtipocobro);

		$cantidad=count($dataIngresos);
		$columna="";
		$total=0;
		$saldo=0;
		$totalAnulado=0;
		$totalLiberado=0;
		for ($i=0; $i <$cantidad ; $i++) {

			$dataOrden=$ordenventa->buscarOrdenVentaxId($dataIngresos[$i]['idOrdenVenta']);
			$simboloMoneda	=$dataOrden[0]['Simbolo'];

			// $totalAnulado+=$dataIngresos[$i]['montoanulado'];
			// $totalLiberado+=$dataIngresos[$i]['montoliberado'];
			// $total+=$dataIngresos[$i]['montoasignado']+$dataIngresos[$i]['saldo'];
			// $saldo+=$dataIngresos[$i]['saldo'];

			$acumulaxIdMoneda[$simboloMoneda]['totalAnulado']+=$dataIngresos[$i]['montoanulado'];
			$acumulaxIdMoneda[$simboloMoneda]['totalLiberado']+=$dataIngresos[$i]['montoliberado'];
			$acumulaxIdMoneda[$simboloMoneda]['total']+=$dataIngresos[$i]['montoasignado']+$dataIngresos[$i]['saldo'];
			$acumulaxIdMoneda[$simboloMoneda]['saldo']+=$dataIngresos[$i]['saldo'];

			//
			$columna.="<tr>";
			$columna.="<td>".($i+1)."</td>";
			$columna.="<td>".($dataIngresos[$i]['fcobro'])."</td>";
			$columna.="<td>".($tipoIngreso[$dataIngresos[$i]['tipocobro']])."</td>";
			$columna.="<td>".($dataIngresos[$i]['razonsocial'])."</td>";
			$columna.="<td>".($dataOrden[0]['codigov'])."</td>";
			$columna.="<td>".($dataIngresos[$i]['nombres'].' '.$dataIngresos[$i]['apellidopaterno'].' '.$dataIngresos[$i]['apellidomaterno'])."</td>";
			$columna.="<td>".($dataIngresos[$i]['nrorecibo'])."</td>";
			$columna.="<td>".($dataIngresos[$i]['nrooperacion'])."</td>";
			$columna.="<td>".$simboloMoneda." ".(number_format($dataIngresos[$i]['montoingresado'],2))."</td>";
			$columna.="<td>".$simboloMoneda." ".(number_format($dataIngresos[$i]['montoasignado'],2))."</td>";
			$columna.="<td>".$simboloMoneda." ".(number_format($dataIngresos[$i]['saldo'],2))."</td>";
			$columna.="<td>".$simboloMoneda." ".(number_format($dataIngresos[$i]['montoanulado'],2))."</td>";
			$columna.="<td>".$simboloMoneda." ".(number_format($dataIngresos[$i]['montoliberado'],2))."</td>";
			$columna.="<td>".$dataIngresos[$i]['observaciones']."</td>";
			$columna.="</tr>";
			
		}

		if ($cantidad==0) {
			echo "<tr><td>No se Encontraron Datos</td></tr>";
		}else{
			$columna.="<tr><td style='background:white;' colspan='13'>&nbsp</td></tr>";
			$columna.="<tr><td colspan='7'>&nbsp</td><th >Total Ingresos  S/.</th><td>".number_format($acumulaxIdMoneda['S/.']['total'],2)."</td><th>Total Saldo S/. </th><td>".number_format($acumulaxIdMoneda['S/.']['saldo'],2)."</td><td></td><td></td><td></td></tr>";
			$columna.="<tr><td colspan='7'>&nbsp</td><th >Total Ingresos US $</th><td>".number_format($acumulaxIdMoneda['US $']['total'],2)."</td><th>Total Saldo US $</th><td>".number_format($acumulaxIdMoneda['US $']['saldo'],2)."</td><td></td><td></td><td></td></tr>";
			echo $columna;
		}
		

	}

	function cambiaIngresos(){
		$ingresos=$this->AutoLoadModel('ingresos');
		$idingresos=$_REQUEST['idingresos'];
		$nuevaidordenventa=$_REQUEST['nuevaidordenventa'];
		$nroRecibo=$_REQUEST['nroRecibo'];
		$idcliente=$_REQUEST['idcliente'];
		$monto=$_REQUEST['monto'];
		$observaciones=$_REQUEST['observaciones'];
		$observacionesingresonuevo=$_REQUEST['observacionesingresonuevo'];
		
		$dataIngreso=$ingresos->buscaxid($idingresos);
		$saldoAnterior=$dataIngreso[0]['saldo'];
		$asignadoAnterior=$dataIngreso[0]['montoasignado'];
		$liberadoAnterior=$dataIngreso[0]['montoliberado'];
		$observacionesA=$dataIngreso[0]['observaciones'];


		$nuevosaldo=$saldoAnterior-$monto;
		$nuevaData['saldo']=$nuevosaldo;
		$nuevaData['observaciones']=$observacionesA."::".$observaciones;
		$nuevaData['montoliberado']=$monto+$liberadoAnterior;
		$exito=$ingresos->actualizaxid($nuevaData,$idingresos);


		if ($exito) {
			
			//registra un nuevo ingreso
			$ingreso['idordenventa']=$nuevaidordenventa;
			$ingreso['idcliente']=$idcliente;
			$ingreso['idcobrador']=122;
			$ingreso['montoingresado']=$monto;
			$ingreso['montoasignado']=0;
			$ingreso['saldo']=$monto;
			$ingreso['esvalidado']=1;
			$ingreso['tipocobro']=8;
			$ingreso['nrorecibo']=$nroRecibo;
			$ingreso['observaciones']=$observacionesingresonuevo;
			
			$ingreso['fcobro']=date("Y-m-d");
			$graba=$ingresos->graba($ingreso);
			if ($graba) {
				echo 'Se grabo correctamente';
			}else{
				echo 'error 2';
			}
			
		}else{
			echo 'error 1';
		}
	}

	function removerIngresos(){
		$ingresos=$this->AutoLoadModel('ingresos');

		$idingresos=$_REQUEST['idingresos'];
		$monto=$_REQUEST['monto'];
		$observaciones=$_REQUEST['observaciones'];

		$dataIngreso=$ingresos->buscaxid($idingresos);
		$saldoAnterior=$dataIngreso[0]['saldo'];
		$observacionesAnterior=$dataIngreso[0]['observaciones'];
		$montoanulado=$dataIngreso[0]['montoanulado'];


		$nuevosaldo=$saldoAnterior-$monto;
		$nuevaData['saldo']=$nuevosaldo;
		$nuevaData['montoanulado']=$monto+$montoanulado;
		$nuevaData['observaciones']=$observacionesAnterior.' :: '.$observaciones."(S/.".$monto.")";
		$exito=$ingresos->actualizaxid($nuevaData,$idingresos);

		if ($exito) {
			echo 'Se grabo correctamente';
		}else{
			echo 'error';
		}

	}
	function verificarIngresosxOrden(){
		
	}
}
?>

<?PHP
$fechaFFase='2014-11-04';
//$fechaFFase='2014-11-21';
//$fechaFFase='2014-12-06';
//$dateUS = \DateTime::createFromFormat("d.m.Y", $dateDE)->format("m/d/Y");
//var_dump($fechaFFase);
//$fechaPago = new DateTime($fechaFFase);
//$fechaPago->modify('+120 day');
//echo $fechaPago->format('Y-m-d');
$hoy=date('Y-m-d');
//if($hoy<=$fechaPago)
//{
	//echo "<button class=pagar>Pagar</button>";
//	echo "<button class=renovar>Renovar</button>";
//}
//else
	//echo "no hay botones";



$fecha = date('Y-m-d', strtotime($fechaFFase.'120 day'));// suma 1 día
 
echo $fecha;
if($hoy<=$fecha)
{
	echo "<button class=pagar>Pagar</button>";
echo "<button class=renovar>Renovar</button>";
}
else
	echo "no hay botones";


?>
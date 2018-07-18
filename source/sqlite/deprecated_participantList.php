<?php
include_once('class.mysqli.php');	
?>
<!DOCTYPE html>
<html>
<body>

<p>
	<a href="../"> << Regresar </a>
</p>

<h1>Lista de participantes registrados</h1>

<?php
//Open database
$db = new MySQL("matest");
	
if($_GET["remove"]){
	$db->consulta("UPDATE participants SET trash = 1 WHERE id LIKE ".$_GET["patient_id"]);	
}	
//Reading data
$consulta = $db->consulta("SELECT * FROM participants WHERE trash LIKE 2");

$files = array();
while($row=$db->fetch_array($consulta)){
	echo "<p>";
		//echo "<a href='?remove=true&patient_id=".$row['id']."'>Borrar</a>";
		echo "<a href='./patientTests.php?patient_id=".$row['id']."'>".utf8_encode($row['name'])." ".utf8_encode($row['surname'])."</a>";
	echo "</p>";
}
$db->close();
?>

</body>
</html>
<?php
include_once('class.mysqli.php');	
?>
<!DOCTYPE html>
<html>
<body>
 
<p>
	<a href="../"> << Regresar </a>
</p>

<?php

//Open database
$db = new MySQL("matest");
		
//Reading data
$consulta = $db->consulta("SELECT * FROM tests");

//echo "</br>Process started...";

while($row = $db->fetch_array($consulta)){
	$queryAcc = "SELECT  `x`, `y`, `z`, UNIX_TIMESTAMP(`created`) AS `created` FROM `sensor_linear_acceleration` WHERE `patient_id` LIKE '".$row['patient_id']."' AND `test_id` LIKE '".$row['id']."' INTO OUTFILE '/var/www/html/sqlite/parsed_files/acc/".$row['patient_id']."@T".$row['id']."Acc.txt'; ";
	$db->consulta($queryAcc);

	$queryOri = "SELECT  `azimuth`, `pitch`, `roll`, UNIX_TIMESTAMP(`created`) AS `created` FROM `sensor_orientation` WHERE `patient_id` LIKE '".$row['patient_id']."' AND `test_id` LIKE '".$row['id']."' INTO OUTFILE '/var/www/html/sqlite/parsed_files/orient/".$row['patient_id']."@T".$row['id']."Ori.txt'; ";
	$db->consulta($queryOri);
}

$db->close();

echo "</br>Process finished.";
echo "</br></br>Continue pressing button 4.";
?>

</body>
</html>
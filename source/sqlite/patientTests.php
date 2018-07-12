<?php
include_once('class.mysqli.php');	
?>
<!DOCTYPE html>
<html>
<body style="width:100%">

<p>
	<a href="./participantList.php"> << Regresar </a>
</p>

<?php
//Open database
$db = new MySQL("matest");
		
//Reading data
$consulta = $db->consulta("SELECT * FROM participants WHERE id LIKE ".$_GET["patient_id"]."");

$files = array();
while($row=$db->fetch_array($consulta)){
	$patient_name = utf8_encode($row['name'])." ".utf8_encode($row['surname']);
}

$db->close();
?>

<h1>Pruebas del paciente: <b><?php echo $patient_name; ?></b></h1>

<p>Para consultar las variables de interés, presione con un clic sobre la prueba correspondiente.</p>

<?php
//Open database
$db = new MySQL("matest");
		
//Reading data
$consulta = $db->consulta("SELECT * FROM tests WHERE patient_id LIKE ".$_GET["patient_id"]."");

$files = array();
echo "<table style='width:100%; font-size:0.8em;'>
  <tr>
    <th align='center' bgcolor='#5D7B9D'><font color='#fff'>ID</font></th>
    <th align='center' bgcolor='#5D7B9D'><font color='#fff'>TIPO DE TEST</font></th>
    <th align='center' bgcolor='#5D7B9D'><font color='#fff'>FECHA</font></th>
    <th align='center' bgcolor='#5D7B9D'><font color='#fff'>Q1</font></br><span style='font-size:0.5em; line-height:10px; display:none;'>(No alteración de marcha)</span></th>
    <th align='center' bgcolor='#5D7B9D'><font color='#fff'>Q2</font></br><span style='font-size:0.5em; line-height:10px; display:none;'>(Ritmo lento)</span></th>
    <th align='center' bgcolor='#5D7B9D'><font color='#fff'>Q3</font></br><span style='font-size:0.5em; line-height:10px; display:none;'>(Pérdida balance)</span></th>
    <th align='center' bgcolor='#5D7B9D'><font color='#fff'>Q4</font></br><span style='font-size:0.5em; line-height:10px; display:none;'>(Gira en su punto)</span></th>
    <th align='center' bgcolor='#5D7B9D'><font color='#fff'>Q5</font></br><span style='font-size:0.5em; line-height:10px; display:none;'>(Balance de brazos)</span></th>
    <th align='center' bgcolor='#5D7B9D'><font color='#fff'>Q6</font></br><span style='font-size:0.5em; line-height:10px; display:none;'>(Estabilización con paredes)</span></th>
    <th align='center' bgcolor='#5D7B9D'><font color='#fff'>Q7</font></br><span style='font-size:0.5em; line-height:10px; display:none;'>(Arrastra pies)</span></th>
    <th align='center' bgcolor='#5D7B9D'><font color='#fff'>Q8</font></br><span style='font-size:0.5em; line-height:10px; display:none;'>(Pasos cortos)</span></th>
    <th align='center' bgcolor='#5D7B9D'><font color='#fff'>Q9</font></br><span style='font-size:0.5em; line-height:10px; display:none;'>(No usa dispositivo correctamente)</span></th>
    <th align='center' bgcolor='#5D7B9D'><font color='#fff'>EVALUACIÓN</br></font></th>
    <th align='center' bgcolor='#5D7B9D'><font color='#fff'>DESCRIPCIÓN</br></font></th>
    <th align='center' bgcolor='#5D7B9D'><font color='#fff'>ESTÁTUS</br></font></th>
    <th align='center' bgcolor='#5D7B9D'><font color='#fff'>VARIABLES</font></th>
    <th align='center' bgcolor='#5D7B9D'><font color='#fff'>SEÑAL ACC</font></th>
  </tr>";

$i = 0;
while($row=$db->fetch_array($consulta)){
	$testData = getTestType($row["type_test"], $row["test_option"]);

    if($i%2) $bgcolor = 'dee4eb'; else $bgcolor = 'fff';
    if($row["status"]!="testCompleted") $fontcolor = 'c7cdd3'; else $fontcolor = '000';

	echo "
    <tr bgcolor='#".$bgcolor."'>
        <td align='left'><font color='#".$fontcolor."'>P".$row["patient_id"]."T".$row["id"]."</font></td>
        <td align='left'><font color='#".$fontcolor."'>".$testData[0]." (".$testData[1].")</font></td>
        <td align='center'><font color='#".$fontcolor."'>".date('d-m-Y H:i:s', strtotime($row["beginning_sensor_collection_timestamp"]))."</font></td>
        <td align='center'><font color='#".$fontcolor."'>".$row["q1"]."</font></td>
        <td align='center'><font color='#".$fontcolor."'>".$row["q2"]."</font></td>
        <td align='center'><font color='#".$fontcolor."'>".$row["q3"]."</font></td>
        <td align='center'><font color='#".$fontcolor."'>".$row["q4"]."</font></td>
        <td align='center'><font color='#".$fontcolor."'>".$row["q5"]."</font></td>
        <td align='center'><font color='#".$fontcolor."'>".$row["q6"]."</font></td>
        <td align='center'><font color='#".$fontcolor."'>".$row["q7"]."</font></td>
        <td align='center'><font color='#".$fontcolor."'>".$row["q8"]."</font></td>
        <td align='center'><font color='#".$fontcolor."'>".$row["q9"]."</font></td>
        <td align='center'><font color='#".$fontcolor."'>".$row["data_evaluation_score"]."</font></td>
        <td align='center'><font color='#".$fontcolor."'>".$row["data_evaluation_description"]."</font></td>
        <td align='center'><font color='#".$fontcolor."'>".$row["status"]."</font></td>
    ";

    //********************************************************************
    //********************************************************************
    $sensibility_tug = 1.9;
    $sensibility_strength = 2.0;
    $sensibility = array($sensibility_tug, $sensibility_strength);
    //********************************************************************
    //********************************************************************
    
    if($row["status"]=="testCompleted"){

        if($row["type_test"] == 1)
            $duration = "&duracion=".date('s', (strtotime($row["finishing_sensor_collection_timestamp"]) - strtotime($row["beginning_sensor_collection_timestamp"])) );
        else
            $duration = "";

        echo "<td align='center'><font color='#".$fontcolor."'><a target='_blank' href='./eventsDetector.php?patient_id=".$row["patient_id"]."&test_id=".$row["id"]."&type_test=".$row["type_test"]."&test_option=".$row["test_option"].$duration."'>Ver</a></font></td>";
        
        if($row["type_test"]=="1" || $row["type_test"]=="2"){
            echo "<td align='center'><font color='#".$fontcolor."'><a target='_blank' href='./print_data.htm?fname=pre_cleaning_".$row["patient_id"]."@T".$row["id"]."Acc&type=".getTestTypeId($row["type_test"])."&dir=acc'>pre</a>&nbsp;&nbsp;";
            echo "<a target='_blank' href='./print_data.htm?fname=post_cleaning_".$row["patient_id"]."@T".$row["id"]."Acc&type=".getTestTypeId($row["type_test"])."&dir=acc&sensibility=".getSensibility($row["type_test"],$sensibility)."'>pos</a></font></td>";
        }else{
            echo "<td align='center'><font color='#".$fontcolor."'>---</font></td>";
        }

        echo "</tr>";
    }else{
        echo "<td align='center'><font color='#".$fontcolor."'>---</font></td>";
        echo "<td align='center'><font color='#".$fontcolor."'>---</font></td>";
        echo "</tr>";
    }

    $i++;
}

echo "</table>";

$db->close();
?>

<?php

function getTestType($type_test, $test_option){
	$outcome = "";
	$outcome2 = "N/A";

	if($type_test==1)	$outcome = "Prueba de marcha (TUG)";
	if($type_test==2)	$outcome = "Prueba de fuerza";
	if($type_test==3)	{
		$outcome = "Prueba de balance";

		if($test_option==1)	$outcome2 = "Tandem";
		if($test_option==2)	$outcome2 = "Semi-tandem";
		if($test_option==3)	$outcome2 = "Pies juntos";
		if($test_option==4)	$outcome2 = "Una pierna";
	}

	return [$outcome, $outcome2];
}

function getTestTypeId($type_test){
    $outcome = "";

    if($type_test==1)   $outcome = "tug";
    if($type_test==2)   $outcome = "strenght";

    return $outcome;
}

function getSensibility($type_test, $sensibility){
    $outcome = 0;

    if($type_test==1) $outcome = $sensibility[0];
    if($type_test==2) $outcome = $sensibility[1];

    return $outcome;
}

?>

</body>
</html>
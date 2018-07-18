<?php 
	//error_reporting(0);
	ini_set('memory_limit', '-1');
	ini_set('max_execution_time', 300);
?>

<!DOCTYPE html>
<html>
<body style="width:100%">

<p>
	<a href="./patientTests.php?patient_id=<?php echo $_GET["patient_id"]; ?>"> << Regresar </a>
</p>

<?php
	/**
	* This section handles all files and basic control information to perform an appropriated extraction of variables of interest. 
	* Please, take into account follow information:
	*	-Sensor data is limited based on the test interval of time; defined on respective protocol, for instance strength test consists on a 30 s interval, balance test consists on a 10 s interval, so forth. 
	*	-"sensibility" variable is a paramount value. It determinates the distance on the y-axis to determinate a measurable threshold, so a number of event can be appropriated identified.
	*	-"ground_truth_event" variable help to define a sensibility variable, when it has not been defined. 
	* Thus, actually only one of previous two variables are mandatory. It depends very much on that information is available at the time of extracting the variable of interest.
	*/

	/**
	* It is quite important to note that the goal with an homogeneous populations is to set a single "sensibility" variable value. At this stage of the project, I have enable previous two mentioned
	* variables since pilot populations is quite heterogeneous. 
	*/

	/**
	* Note: please, take into account that (as elaborated previously) $sensibility variables must be settled manually until reach a stable value to generalize.
	*/

	$FILE_PATH 		= "./parsed_files/";
	$ACC_DIRECTORY 	= $FILE_PATH."acc";
	$ORI_DIRECTORY 	= $FILE_PATH."orient";

	//********************************************************************
    //********************************************************************
	$sensibility_tug = 1.7;
	$sensibility_strength = 1.4;
	//********************************************************************
    //********************************************************************

	if($_GET["duracion"]) 
			$duracion = $_GET["duracion"]; 
		else 
			$duracion = false;

	// TUG
	if( $_GET["type_test"] == 1 ){
		$directoryname			= 	$ACC_DIRECTORY;	
		$filename 				= 	$_GET["patient_id"]."@T".$_GET["test_id"]."Acc";
		$type 					= 	"tug"; 			
		$sensibility 			= 	$sensibility_tug;
		$ground_truth_events	= 	false;
		$segmentTimeSeconds 	= 	$duracion;
	}

	// Strenght
	if( $_GET["type_test"] == 2 ){
		$directoryname			= 	$ACC_DIRECTORY;	
		$filename 				= 	$_GET["patient_id"]."@T".$_GET["test_id"]."Acc";	
		$type 					= 	"strenght";				
		$sensibility 			= 	$sensibility_strength;
		$ground_truth_events	= 	false;
		$segmentTimeSeconds 	= 	30;
	}

	// Balance
	if( $_GET["type_test"] == 3 ){
		if( $_GET["test_option"] == 1 ){
			$directoryname		=	$ORI_DIRECTORY;	
			$filename 			= 	$_GET["patient_id"]."@T".$_GET["test_id"]."Ori";	
			$type 				= 	"balance_tamdem";		
			$segmentTimeSeconds = 	10;
		}
		if( $_GET["test_option"] == 2 ){
			$directoryname		=	$ORI_DIRECTORY;	
			$filename 			= 	$_GET["patient_id"]."@T".$_GET["test_id"]."Ori";	
			$type 				= 	"balance_semiTandem";	
			$segmentTimeSeconds	= 	10;
		}
		if( $_GET["test_option"] == 3 ){
			$directoryname		=	$ORI_DIRECTORY;	
			$filename 			= 	$_GET["patient_id"]."@T".$_GET["test_id"]."Ori";	
			$type 				= 	"balance_twoFeet";		
			$segmentTimeSeconds = 	10;
		}
		if( $_GET["test_option"] == 4 ){
			$directoryname		=	$ORI_DIRECTORY;	
			$filename 			= 	$_GET["patient_id"]."@T".$_GET["test_id"]."Ori";	
			$type 				= 	"balance_oneLeg";		
			$segmentTimeSeconds = 	10;
		}
	}

	/**
	* This section looks for a previously defined file with the goal of preparing them based on a formatted shape,
	* so far, a basic structure consists on a tsv format style, but if necessary any adjustment should be 
	* conducted on this phase of the process
	*/

	$_filepath = "./".$directoryname."/".$filename.'.txt';

	if( file_exists($_filepath) ){
		$filecontent= file_get_contents(__DIR__."/".$directoryname."/".$filename.'.txt');
		$words 		= preg_split('/[\s]+/', $filecontent, -1, PREG_SPLIT_NO_EMPTY);
		$dir 		= __DIR__."/".$directoryname;

		if( is_dir($dir) === false ){
			mkdir($dir);
		}

		$filename_out = $dir."/".$filename.'.tsv';

		$c = 0; // Simple counter
		$i = 4; // Define the number of columns on a file
		$n = 0; // Another simple counter control
		$flagTimeSeconds 	= true;
		$veryFirstDatePoint = "";
		$veryLastDatePoint 	= "";
		$stopParsingData 	= false;
		// TODO $frequency should automatically found by reading txt files
		//$frequency 			= 50; //hz per second

		foreach( $words as $value ){
			if( !$stopParsingData ){
				if( $c < $i ){
					
					// This section allows to clean frequency cycles
					/*if( !$flagTimeSeconds ){
						if( $c == ($i-1) )
							$value = (double)$veryFirstDatePoint + (double)( ( ( 1000 / $frequency) / 1000 ) * $n );
					}*/

					$salida .= $value."\t";
					$c++;
				}
				
				// At the time we are reading the fourth column (time)
				if( $c == $i ){
					$salida .= "\n";	
					$c 		 = 0;

					/** 
					* This condition allows to get the very first datapoint, so I can start the segmentation of provided data
					*/
					if( $flagTimeSeconds ){
						$veryFirstDatePoint = $value;
						$flagTimeSeconds 	= false;
					}

					/** 
					* This condition detects when the current time has arrived :)
					* Thus, if a limit segment is marked, the signal is cut respectively, otherwise all signal is taken into account.
					*/
					if( $segmentTimeSeconds != false ){
						if( ( (double)$value ) >= ( (double)$veryFirstDatePoint + (double)$segmentTimeSeconds + 6 ) ){ // 4 second of threshold to cover the delay for the user to respord to the audio alert
							$stopParsingData = true;	
						}
					}

					/** 
					* This condition gets the last datapoint, so I can cut respective segment
					*/
					$veryLastDatePoint = $value;
					$n++;
				}
			}
		}

		/** 
		* This section create a new file with a format ready to be grafically plotted using html & js functions.
		*/
		$fileSize = $n;
		$salida  .= "\t";
		file_put_contents( $filename_out, substr_replace($salida, "", -2) );

		$nFile = fopen( $filename_out, "r" );
		$filename_out_events_pre_cleaning 	= $dir."/pre_cleaning_".$filename.'_'.$type.'.json';
		$filename_out_events_post_cleaning 	= $dir."/post_cleaning_".$filename.'_'.$type.'.json';

		$salida_events_pre_cleaning;
		$salida_events_post_cleaning;

		$azimuth= array();
		$pitch 	= array();
		$roll 	= array();

		$_axisX_pre	 =	array();
		$_axisY_pre	 =	array();
		$_axisZ_pre	 =	array();

		$_axisX_post =	array();
		$_axisY_post =	array();
		$_axisZ_post =	array();

		$f_timestamp = array();


		if( $nFile !== FALSE ) {
			$i	= 0;
			
			$finalSignal= 0;

			$_nParsed0	= 0;
			$_nParsed1	= 0;
			$_nParsed2  = 0;

			/**
			* This section extract data from file and prepare variables so data is more likely to be treated.
			*/
			while ( !feof($nFile) ) {
				$nLineData 		= fgets($nFile);
				$nParsed 		= explode("\t", $nLineData, -1);

				$_nParsed0 		= $nParsed[0];
				$_nParsed1 		= $nParsed[1];
				$_nParsed2 		= $nParsed[2];
				$recordingDate 	= $nParsed[3];

				if($type == "balance_twoFeet" || $type == "balance_semiTandem" || $type == "balance_tamdem" || $type == "balance_oneLeg"){
					$azimuth[$i]= $_nParsed0;
					$pitch[$i] 	= $_nParsed1;
					$roll[$i]	= $_nParsed2;
				}else{
					$_axisX_pre[$i]	= $_nParsed0;
					$_axisY_pre[$i]	= $_nParsed1;
					$_axisZ_pre[$i]	= $_nParsed2;
				}

				$f_timestamp[$i] 	 = $recordingDate;
				//$finalSignal 		+= $_nParsed1;
				$i++;
			}
			
			/**
			* In this section the DC component is removed from collected data, so, outcome is cleaner.
			*/
			/*$DC_COM = $finalSignal / $i;

			// DELETE DC component from input data
			for ($i = 0; $i < count($_axisY_pre); $i++){
				$_axisY_pre[$i] = ( subtracting($_axisY_pre[$i], $DC_COM) );
			}*/

			// Filtering data
			$command = escapeshellcmd('python /var/www/html/sqlite/filtering.py '.join(',', $_axisX_pre));
			$output = shell_exec($command);
			$_axisX_post = explode( ' ', substr(preg_replace('/\s+/', ' ', $output), 1, -2) );

			$command = escapeshellcmd('python /var/www/html/sqlite/filtering.py '.join(',', $_axisY_pre));
			$output = shell_exec($command);
			$_axisY_post = explode( ' ', substr(preg_replace('/\s+/', ' ', $output), 1, -2) );

			$command = escapeshellcmd('python /var/www/html/sqlite/filtering.py '.join(',', $_axisZ_pre));
			$output = shell_exec($command);
			$_axisZ_post = explode( ' ', substr(preg_replace('/\s+/', ' ', $output), 1, -2) );

			//*****************************************************************************************
			// FINAL SIGNAL TO TAKE INTO ACCOUNT
			//*****************************************************************************************
			for ($i = 0; $i < count($_axisX_post); $i++){
				$f_LA_pre_cleaning[$i] 	=	sqrt( pow($_axisY_pre[$i], 2) + pow($_axisZ_pre[$i], 2) );
				$f_LA_post_cleaning[$i]	=	sqrt( pow($_axisY_post[$i], 2) + pow($_axisZ_post[$i], 2) );
			}
			//*****************************************************************************************

			// Exclusive for TUG and STRENGTH test
			if($type == "tug" || $type == "strenght"){
				// I will take into account only Y axis, since it is a controlled study in which we know for granted that Y axis could be considerated the core of the data analysis.
				findSensibility($f_LA_post_cleaning, $f_timestamp);
				eventsAutomata($f_LA_post_cleaning, $f_timestamp);
			}

			// EXCLUSIVE for BALANCE test
			if($type == "balance_twoFeet" || $type == "balance_semiTandem" || $type == "balance_tamdem" || $type == "balance_oneLeg"){
				$sway_index 		= calculateBalanceVariables( $azimuth, $pitch, $roll, "sway_index" );
				$stability_index 	= calculateBalanceVariables( $azimuth, $pitch, $roll, "stability_index" );
				$anterior_posterior = calculateBalanceVariables( $azimuth, $pitch, $roll, "anterior_posterior" );
				$medio_lateral 		= calculateBalanceVariables( $azimuth, $pitch, $roll, "medio_lateral" );
			}

			/**
			* Below three variables are used only for graphical visualization
			*/
			for ($i = 0; $i < count($f_LA_pre_cleaning); $i++){
				// PRE CLEANING SIGNAL
				if( $f_LA_pre_cleaning[$i]!="" && $f_timestamp[$i]!="" )
					$salida_events_pre_cleaning  .=	"[".cleanUnixValue($f_timestamp[$i]).",".( $f_LA_pre_cleaning[$i] )."],";
			
				// POST CLEANING SIGNAL
				if( $f_LA_post_cleaning[$i]!="" && $f_timestamp[$i]!="" )
					$salida_events_post_cleaning .=	"[".cleanUnixValue($f_timestamp[$i]).",".( $f_LA_post_cleaning[$i] )."],";
			}

			fclose($nFile);
		}

		/**
		* Data concentratino for graphical purposes
		*/
		$salida_events_pre_cleaning =
		'[
			{	"name": "Signal",
				"data": [
					'.substr_replace( $salida_events_pre_cleaning, "", -1 ).'
				]
			}
		]';

		$salida_events_post_cleaning =
		'[
			{	"name": "Signal",
				"data": [
					'.substr_replace( $salida_events_post_cleaning, "", -1 ).'
				]
			}
		]';


		// Below file is created as a JSON file, and have nothing to do with sensibility. It is a simple representation to be plotted and graphically visualized.
		file_put_contents( $filename_out_events_pre_cleaning, $salida_events_pre_cleaning );
		file_put_contents( $filename_out_events_post_cleaning, $salida_events_post_cleaning );


		if($type == "tug" || $type == "strenght"){
			echo "<table style='font-size:12px; text-align: left;'><tbody>";
			if($type == "tug" ) 
				echo "<tr><th colspan='3' style='text-align: left;'>
				================================================</br>
				<b>PRUEBA TUG (".$filename.")</b>
				</br>================================================</br>
				</th></tr>";
			if($type == "strenght" ) 
				echo "<tr><th colspan='3' style='text-align: left;'>
				================================================</br>
				<b>PRUEBA DE FUERZA (".$filename.")</b>
				</br>================================================</br>
				</th></tr>";
			if($sensibility==false){
				echo "Utilizar el valor de la DS como entrada para la variable sensibility: ".$sensibility_found;
			}else{
				echo "<td valign='top'>";
					echo "<b>A) DETALLES DE LOS EVENTOS</b> </br>";
					echo $event_properties;
				echo "</td>";
				echo "<td>&nbsp;&nbsp;</td>";

				//$total_delay_ 	= $total_delay;
				//$total_length_ 	= $total_length;
				/*$firstDataPointTime = (int)$firstDataPointTime.split(".")[0];
				$fdt = new DateTime("@$firstDataPointTime");
				echo $fdt->format('Y-m-d H:i:s');
				echo "</br>";

				$lastDataPointTime = (int)$lastDataPointTime.split(".")[0];
				$ldt = new DateTime("@$lastDataPointTime");
				echo $ldt->format('Y-m-d H:i:s'); 
				echo "</br>";

				$interval = date_diff($fdt, $ldt);
				echo $interval->format('%s');*/

				$interval_length= round( ($lastDataPointTime - $firstDataPointTime) * 1000 );
				if( $interval_length < 0 ) $interval_length = 0;  
				$events 		= eventsAutomata($f_LA_post_cleaning, $f_timestamp);


				if($type == "tug"){
					$eventName = "paso";
				}else{
					$eventName = "evento";
				}

				//echo $interval_length;
				//echo "</br>";
				//echo $delay_delete;
				$interval_length -= $delay_delete;

				echo "<td valign='top'>";
					echo "<b>B) INFORMACIÓN GENERAL DE LOS EVENTOS</b> </br>";
					echo "Número de ".$eventName."s detectados: " . $events . "</br>";
					echo "Duración promedio por ".$eventName." (s) : ". round( ( $total_length / $events ) / 1000, 4 ) . "</br>";
					//echo "AVG latencia (s) : ". round( ( $total_delay / $events ) / 1000, 4 ) . "</br>";
					echo "Intervalo de tiempo entre el primer y último ".$eventName." detectado (s) : ". $interval_length/1000 . "</br></br>";
				
					if($type == "tug"){
						echo "<b>C) DETALLES DE LA MARCHA</b> </br>";
						echo "Velocidad de marcha (".$eventName."/min): ". round( ($events/(($interval_length/1000)/60)), 4 ) . "</br>";
						echo "Distancia entre ".$eventName."s (cm) : ". round( (1400/$events),4) . "</br>";
						echo "Tiempo entre ".$eventName." (s): " . round( ( $total_delay / $events )/1000, 4 ) . "</br>";
					}

					if($type == "strenght"){
						echo "<b>C) DETALLES DE LA PRUEBA DE FUERZA</b> </br>";
						echo "Tiempo entre ".$eventName." (s) : " . round( ( $total_delay / $events )/1000, 4 ) . "</br>";
					}

				echo "</td>";
				echo "</tbody></table>";
			}
		}

		if($type == "balance_twoFeet" || $type == "balance_semiTandem" || $type == "balance_tamdem" || $type == "balance_oneLeg"){
			echo "<table style='font-size:12px; text-align: left;'><tbody>";
			if($type == "balance_twoFeet" ) 
				echo "<tr><th colspan='3' style='text-align: left;'>
				================================================</br>
				<b>PRUEBA DE BALANCE -PIERNAS JUNTAS- (".$filename.")</b>
				</br>================================================</br>
				</th></tr>";
			if($type == "balance_semiTandem" ) 
				echo "<tr><th colspan='3' style='text-align: left;'>
				================================================</br>
				<b>PRUEBA DE BALANCE -SEMI TANDEM- (".$filename.")</b>
				</br>================================================</br>
				</th></tr>";
			if($type == "balance_tamdem" ) 
				echo "<tr><th colspan='3' style='text-align: left;'>
				================================================</br>
				<b>PRUEBA DE BALANCE -TANDEM- (".$filename.")</b>
				</br>================================================</br>
				</th></tr>";
			if($type == "balance_oneLeg" ) 
				echo "<tr><th colspan='3' style='text-align: left;'>
				================================================</br>
				<b>PRUEBA DE BALANCE -UNA PIERNA- (".$filename.")</b>
				</br>================================================</br>
				</th></tr>";
			 		
			echo "<td valign='top'>";
				if($type == "balance_twoFeet" || $type == "balance_semiTandem" || $type == "balance_tamdem"){
					echo "<b>A) DETALLES</b> </br></br>";
					/**
					* Pag: 8-18, from operation manual
					* The Sway Index is the Standard deviation of the Stability index. The higher the Sway Indes. The more 
					* unsteady the person was during the test. The Sway Index is an objective quantification of what commonly 
					* is done with a time-based pass/fail for completing the CTSIB (Clinical Test od Sensory Integration and Balance) 
					* stage in 30 seconds without falling, or assigning a value of 1 to 4 to characterize the sway. 1 = minimal sway, 4 = a fall.
					*/
					echo "<b>Indice de oscilación (Sway Index):</b> ".$sway_index." </br>";
					/**
					* The Stability Index is the average position from center. It does not indicate how much the patien swayed
					* only their position. Consider the following example:
					* 
					* If a patient is positioned in a manner that biases thier placement from the center, the stability
					* index will be large value. However if they swayed very little the standard deviation would be low.
					* A patient could have a score of 6.5, yet their standard deviation would only be .8. The printout tracing will
					* show they did not sway very much. However, if they were positioned off-center, or even on center- and they swayed
					* a lot the standard deviation would be higher. Thus the standard deviation is indicative of sway.
					*/
					echo "<b>Indice de estabilidad general (Overal Stability Index -SI):</b> ".$stability_index."</br>";
				}
				if($type == "balance_oneLeg"){
					echo "<b>A) DETALLES</b> </br></br>";
					echo "<b>Indice de estabilidad general (Overal Stability Index -SI):</b> ".$stability_index."</br>";
					echo "<b>Indice de estabilidad Anterior/Posterior (AP):</b> ".$anterior_posterior."</br>";
					echo "<b>Indice de estabilidad Medio/Lateral (M/L):</b> ".$medio_lateral."</br>";
				}
			echo "</td>";

			echo "</tbody></table>";
		}

	}else{ // if(file_exists($filecontent))
		// if($sensibility!=false){
			echo "<table style='font-size:12px; text-align: left;'><tbody>";
			if($type == "tug" ) 
				echo "<tr><th style='text-align: left;'>
				================================================</br>
				<b>PRUEBA TUG (".$filename.")</b>
				</br>================================================</br>
				</th></tr>";
			if($type == "strenght" ) 
				echo "<tr><th style='text-align: left;'>
				================================================</br>
				<b>PRUEBA DE FUERZA (".$filename.")</b>
				</br>================================================</br>
				</th></tr>";
			if($type == "balance_twoFeet" ) 
				echo "<tr><th colspan='3' style='text-align: left;'>
				================================================</br>
				<b>PRUEBA DE BALANCE -PIERNAS JUNTAS- (".$filename.")</b>
				</br>================================================</br>
				</th></tr>";
			if($type == "balance_semiTandem" ) 
				echo "<tr><th colspan='3' style='text-align: left;'>
				================================================</br>
				<b>PRUEBA DE BALANCE -SEMI TANDEM- (".$filename.")</b>
				</br>================================================</br>
				</th></tr>";
			if($type == "balance_tamdem" ) 
				echo "<tr><th colspan='3' style='text-align: left;'>
				================================================</br>
				<b>PRUEBA DE BALANCE -TANDEM- (".$filename.")</b>
				</br>================================================</br>
				</th></tr>";
			if($type == "balance_oneLeg" ) 
				echo "<tr><th colspan='3' style='text-align: left;'>
				================================================</br>
				<b>PRUEBA DE BALANCE -UNA PIERNA- (".$filename.")</b>
				</br>================================================</br>
				</th></tr>";
			echo "<tr><td>Archivo no encontrado:</br>".$_filepath."</td></tr>";
			echo "</tbody></table>";
		// }
	}

	////////////////////////////////////////////////////////////////////////////////
	////////////////				FUNCTION SECTION 				////////////////
	////////////////////////////////////////////////////////////////////////////////
	/**
	* This function handle standarized date format from ISO 8601 to unixvalue
	*/
	function cleanUnixValue($epochFormat){
		$iso8601 = "";

		$millisecondsArray 	= explode(".",strval($epochFormat));
		$iso8601 = $millisecondsArray[0]."".substr($millisecondsArray[1],0,3);

		return $iso8601;
	}

	/**
	* This function perform a simple subtraction :P
	*/
	function subtracting($a, $b){
		return $a - $b;
	}

	/**
	* This function return the number of events detected
	*/
	function eventsAutomata($input,$timestamp){
		global $sensibility;
		
		$eventDetected = 0;
		//$eventDetected = flatpoint($input,$sensibility,$timestamp);
		$eventDetected = peakpoint($input,$sensibility,$timestamp);

		return $eventDetected;
	}

	/**
	* This function return a calculated sensibility based on the number of stepts used as ground truth
	*/
	function findSensibility($input,$timestamp){
		$calculated_sensibility = 0;
		global $ground_truth_events;
		global $sensibility_found;

		$eventsx=0;

		$max = max($input);
		$dsG = sd($input);

		while($eventsx <= $ground_truth_events){
			$max -= ($dsG/10);
			$eventsx = peakpoint($input,$max,$timestamp);
		}

		if($eventsx > $ground_truth_events){
			$calculated_sensibility = $max+($dsG/10);
			$sensibility_found = $calculated_sensibility;
		}else{
			$calculated_sensibility = $max;
			$sensibility_found = $calculated_sensibility;
		}

		return $calculated_sensibility;
	}

	/**
	* This function search for a number of events based on the peakpoint mechanism
	*/
	function peakpoint($input,$max,$timestamp){
		global $event_properties;
		global $delay_delete;
		global $total_length;
		global $total_delay;
		global $firstDataPointTime;
		global $lastDataPointTime;
		global $type;

		$delay 			= 0;
		$delay_delete	= 0;
		$total_length 	= 0;
		$total_delay 	= 0;
		$splits 		= "";
		
		// Simple to control sensibility when it was not defined a-priori
		if($max == false)	$max = 0; 

		$length = 0;
		$flag = false;
		//$previous_length		= 0;
		//$previous_delay		= 0;
		$eventDetected 		= 0;
		$eventStepDetected	= 0;
		$eventSancadaDetected=0;
		$event_properties 	= "";
		$middle_of_peakpoint= 0;
		$timestamp_OF_first_peakpointDetected	= 0;
		$timestamp_OF_second_peakpointDetected	= 0;

		/**
		* Relevante interpretation:
		* 	Duración = tiempo que duró en participante en completar un evento. Lo calculo obteniendo los tiempos en que se identifican un peakpoint, después uns simple resta.
		* 	Latencia = tiempo que duró el participante en iniciar un evento después de haber completado un evento
		* 	NOTA: el primer evento toma el tiempo 
		*/

		// Note that a full peakpoint consist on a two detected datapoint
		for( $i = 0; $i < count($input); $i++ ){

			// First peakpoint detected
			if( ( floatval($input[$i]) >= floatval($max) ) ){
				$flag = true;
				$timestamp_OF_first_peakpointDetected = $timestamp[$i];
			}

			// Second peakpoint detected
			if( ($flag == true) && ( floatval($input[$i]) <= floatval($max) ) ){
				++$eventDetected;
				
				$timestamp_OF_second_peakpointDetected = $timestamp[$i];
				$middle_of_peakpoint = $timestamp_OF_first_peakpointDetected + ($timestamp_OF_second_peakpointDetected - $timestamp_OF_first_peakpointDetected);
				
				$length = round( ($timestamp_OF_second_peakpointDetected - $timestamp_OF_first_peakpointDetected) * 1000 );

				if( $eventDetected == 1 ){
					$delay = 0;
					$firstDataPointTime = $middle_of_peakpoint;
					$_timestamp_OF_first_peakpointDetected = $middle_of_peakpoint;
				}else{
					$delay = round( ($middle_of_peakpoint - $_timestamp_OF_first_peakpointDetected) * 1000 );
					$lastDataPointTime = $middle_of_peakpoint;
					$_timestamp_OF_first_peakpointDetected = $middle_of_peakpoint;
				}

				// Preserving last peakpoint detected
				$timestamp_OF_second_peakpointDetected = $timestamp_OF_first_peakpointDetected;

				if($type == "strenght"){
					if( ($delay >= 1000 || $delay == 0) && ($delay <= 4000) ){
						$splits .= "<tr>
										<td>(".$eventDetected.")</td>
										<!--<td> ".$length." </td>-->
										<td> ".$delay." </td>
									</tr>";

						$total_length += $length;
						$total_delay  += $delay;
					}

					if( ($delay > 0 && $delay < 1000) || ($delay > 4000) ){
						//$delay_delete += $delay;
						--$eventDetected;
					}
				}
				if($type == "tug"){
					// Steps
					if( ($delay >= 500 || $delay == 0) && ($delay <= 3000) ){
						//if( $delay >= 500 ){
							$splits .= "<tr>
											<td>(".$eventDetected.")</td>
											<!--<td> ".$length." </td>-->
											<td> ".$delay." </td>
										</tr>";

							$total_length += $length;
							$total_delay  += $delay;
						//}
					}

					if( ($delay > 0 && $delay < 500) || ($delay > 3000) ){
						$delay_delete += $delay;
						--$eventDetected;
					}


					// Zancada
					/*
					if( $eventDetected % 2 == 0 && ($delay > 450 && $delay < 5000) ){
						++$eventStepDetected;
						
						//$_delay = $delay;
						$length = round( ($length + $previous_length) / 2 );
						$delay = round( ($delay + $previous_delay) / 2 );

						//if( $delay >= 500 ){
							$splits .= "<tr>
											<td>(".$eventStepDetected.")</td>
											<!--<td> ".$length." </td>-->
											<td> ".$delay." </td>
										</tr>";
							

							$total_length += $length;
							$total_delay  += $delay;

							$previous_length = $length;
							$previous_delay = $delay;
						//}
						
						//if( $delay < 500 ){
						//	--$eventStepDetected;
						//}	
					}
					*/
				}

				$flag = false;
			}
		}

		// Control snippet to avoid false interpretation. I considere two possible False Positives (i.e., at the begining and end of signal)
		if( $eventDetected <= 2 ){
			$splits 		= "";
			$total_length 	= 0;
			$total_delay 	= 0;
			$delay_delete	= 0;
			$eventDetected 	= 0;
			$lastDataPointTime = 0;
			$firstDataPointTime = 0;
		}

		if($type == "tug"){
			$eventName = "paso";
		}else{
			$eventName = "evento";
		}

		$event_properties ="
		<table style='font-size:12px'>
			<tr>
				<td>#</td>
				<!--<td>Duración (ms)</td>-->
				<td>Latencia de ".$eventName." (ms)</td>
			</tr>
		".$splits."
		</table>";

		/*if($type == "tug" && $eventDetected > 2){
			$eventDetected = $eventStepDetected;
			$event_properties .= "
				</br></br>
				<table style='font-size:12px'>
					<tr>
						<td>#</td>
						<!--<td>Duración (ms)</td>-->
						<td>Latencia de pasos (ms)</td>
					</tr>
				".$_splits."
				</table>";
		}*/

		return $eventDetected;
	}

	/**
	* This function search for a number of events based on the flatpoint mechanism
	*/
	function flatpoint($input,$dsx,$timestamp){
		global $event_properties;
		global $total_length;
		global $total_delay;
		global $firstDataPointTime;
		global $lastDataPointTime;
		global $type;

		$total_length 	= 0;
		$total_delay 	= 0;
		$splits 		= "";
		
		// Simple to control sensibility when it was not defined a-priori
		if($dsx == false)	$dsx = 0; 

		$flag = false;
		$eventDetected 		= 0;
		$event_properties 	= "";

		/**
		* Relevante interpretation:
		* 	Duración = tiempo que duró en participante en completar un paso. Lo calculo obteniendo los tiempos en que se identifican los dos flatpoints, después uns simple resta.
		* 	Latencia = tiempo que duró el participante en iniciar un paso después de haber completado un paso
		* 	NOTA: el primer paso toma el tiempo 
		*/

		// Note that a full flatpoint consist on a two detected events
		for( $i = 0; $i < count($input); $i++ ){
			// First flatpoint detected
			if( floatval($input[$i]) >= floatval($dsx) ){
				$flag = true;
				$timestamp_OF_first_flatpointDetected = $timestamp[$i];
			}

			// Second flatpoint detected
			if( ($flag == true) && ( floatval($input[$i]) <= -floatval($dsx) ) ){
				++$eventDetected;
				
				$timestamp_OF_second_flatpointDetected = $timestamp[$i];

				$length = round( ($timestamp_OF_second_flatpointDetected - $timestamp_OF_first_flatpointDetected) * 1000 );
				
				if( $eventDetected == 1 ){
					$delay = 0;
					$firstDataPointTime = $timestamp_OF_second_flatpointDetected;
					$_timestamp_OF_first_flatpointDetected = $timestamp_OF_first_flatpointDetected;
				}else{
					$delay = round( ($timestamp_OF_second_flatpointDetected - $_timestamp_OF_first_flatpointDetected) * 1000 );
					$_timestamp_OF_first_flatpointDetected = $timestamp_OF_first_flatpointDetected;
					$lastDataPointTime = $timestamp_OF_second_flatpointDetected;
				}

				//if($type == "strenght"){
					//if( $length >= 100 && ($delay >= 500 || $delay == 0) ){
						$splits .= "<tr>
										<td>(".$eventDetected.")</td>
										<td> ".$length." </td>
										<td> ".$delay." </td>
									</tr>";

						$total_length += $length;
						$total_delay  += $delay;
					//}

					//if( ($length > 0 && $length < 100) || ($delay > 0 && $delay < 500) ){
					//	--$eventDetected;
					//}
				//}
				/*if($type == "tug"){
					if( $length >= 50 && ($delay >= 200 || $delay == 0) ){
						$splits .= "<tr>
										<td>(".$eventDetected.")</td>
										<td> ".$length." </td>
										<td> ".$delay." </td>
									</tr>";

						$total_length += $length;
						$total_delay  += $delay;
					}

					if( ($length > 0 && $length < 50) || ($delay > 0 && $delay < 200) ){
						--$eventDetected;
					}	
				}*/

				$flag = false;
			}
		}

		// Control snippet to avoid false interpretation. I considere two possible False Positives (i.e., at the begining and end of signal)
		if( $eventDetected <= 2 ){
			$splits 		= "";
			$total_length 	= 0;
			$total_delay 	= 0;
			$eventDetected 	= 0;
		}

		$event_properties ="
		<table style='font-size:12px'>
			<tr>
				<td>#</td>
				<td>Duración (ms)</td>
				<td>Latencia (ms)</td>
			</tr>
		".$splits."
		</table>";

		return $eventDetected;
	}

	// Function to calculate square of value - mean
	function sd_square($x, $mean) { 
		return pow($x - $mean,2); 
	}

	// Function to calculate standard deviation (uses sd_square)
	function sd($array){
		// square root of sum of squares devided by N-1
		return sqrt(array_sum(array_map("sd_square", $array, array_fill(0,count($array), (array_sum($array) / count($array)) ) ) ) / (count($array)-1) );
	}

	function mean($array){
		return ( array_sum($array) / count($array) );
	}

	function calculateBalanceVariables($azimuth, $pitch, $roll, $option){
		$DI = 0;
		/**
		* Is the standard deviation of the Stability Index
		*/
		if($option == "sway_index"){
			// Total number of samples
			$n = sizeof($azimuth);
			$sample = $n*.10;

			// I will use a starting 10% of position data as the COB constant; as a first approximation of calibration mechanism.
			$COB_x = array_sum(array_slice($azimuth, 0, $sample))/$sample;
			$COB_y = array_sum(array_slice($pitch, 0, $sample))/$sample;

			// Addition of difference between settled center of balance and participant sway over the X axis of the platform
			foreach ($azimuth as $X) {
	    		$SUM_X_balance += pow( ($COB_x - $X), 2 );
			}

			// Addition of difference between settled center of balance and participant sway over the Y axis of the platform
			foreach ($pitch as $Y) {
	    		$SUM_Y_balance += pow( ($COB_y - $Y), 2 );
			}

			$DI = sd( [sqrt( ($SUM_X_balance) / $n), sqrt( ($SUM_Y_balance) / $n)] );
		}

		/**
		* Is the average position from center. Represents the variance of foot platform displacement in degrees, from level
		* in all motions during a test. A high number is indicative of a lot of movement during a test with static
		* measures; it is the angular excursion of the patient's center of gravity.
		* Use as a starting point for a perfect balance state. COB x = 0, COB y = 0, where COB stands for "Center of Balance"
		*/
		if($option == "stability_index"){
			// Total number of samples
			$n = sizeof($azimuth);
			$sample = $n*.10;

			// I will use a starting 10% of position data as the COB constant; as a first approximation of calibration mechanism.
			$COB_x = array_sum(array_slice($azimuth, 0, $sample))/$sample;
			$COB_y = array_sum(array_slice($pitch, 0, $sample))/$sample;

			// Addition of difference between settled center of balance and participant sway over the X axis of the platform
			foreach ($azimuth as $X) {
	    		$SUM_X_balance += pow( ($COB_x - $X), 2 );
			}

			// Addition of difference between settled center of balance and participant sway over the Y axis of the platform
			foreach ($pitch as $Y) {
	    		$SUM_Y_balance += pow( ($COB_y - $Y), 2 );
			}

			$DI = sqrt( ($SUM_X_balance + $SUM_Y_balance) / $n);
		}

		/**
		* Represents the variance of foot platform displacement in deggrees, from level, for motion in the sagittal plane.
		*/
		if($option == "anterior_posterior"){
			// Total number of samples
			$n = sizeof($azimuth);
			$sample = $n*.10;

			// I will use a starting 10% of position data as the COB constant; as a first approximation of calibration mechanism.
			$COB_y = array_sum(array_slice($pitch, 0, $sample))/$sample;

			// Addition of difference between settled center of balance and participant sway over the Y axis of the platform
			foreach ($pitch as $Y) {
	    		$SUM_Y_balance += pow( ($COB_y - $Y), 2 );
			}

			$DI = sqrt( $SUM_Y_balance / $n);
		}

		/**
		* Represents the variance of foot platform displacement in deggrees, from level, for motion in the frontal plane.
		*/
		if($option == "medio_lateral"){
			// Total number of samples
			$n = sizeof($azimuth);
			$sample = $n*.10;

			// I will use a starting 10% of position data as the COB constant; as a first approximation of calibration mechanism.
			$COB_x = array_sum(array_slice($azimuth, 0, $sample))/$sample;

			// Addition of difference between settled center of balance and participant sway over the X axis of the platform
			foreach ($azimuth as $X) {
	    		$SUM_X_balance += pow( ($COB_x - $X), 2 );
			}

			$DI = sqrt( $SUM_X_balance / $n);
		}

		return round($DI,4);
	}
	////////////////////////////////////////////////////////////////////////////////
	////////////////				FUNCTION SECTION 				////////////////
	////////////////////////////////////////////////////////////////////////////////
?>
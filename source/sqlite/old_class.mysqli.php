<?php
/** Archivo que almacena los procedimientos para la gesti�n de la base de datos del Sistema en general */
//include_once('class.config.php');
class MySQL{  
   	private $conexion;  
   	private $total_consultas;  
	
		public function MySQL($dbName){  
		
		/** Datos de contros para la base de datos */
		$host = "localhost";
		$usuario = "root";
		$password = "password_fiware_server";
	
   			if(!isset($this->conexion)){ 
			global $config;
   			$this->conexion = (mysql_connect($host,$usuario,$password)) or die(mysql_error());
   			mysql_select_db($dbName,$this->conexion) or die(mysql_error());  
   			}  
   		}
		
		/** Gesti�n de consultas del Sistema */
  		public function consulta($consulta){  
   			$this->total_consultas++;  
   			$resultado = mysql_query($consulta,$this->conexion);  
   			if(!$resultado){
				echo 'MySQL Error: ' . mysql_error();
   				exit;  
   			}  
   		return $resultado;   
   		}  
  
  		/** Enlistado en un arreglo los resultados de la consulta previamente realizada a la funci�n consulta() de este archivo */
  		public function fetch_array($consulta){   
   			return mysql_fetch_array($consulta);  
   		}
		
		/** Liberaci�n de memoria */
		public function free_result($consulta){   
   			return mysql_free_result($consulta);  
   		}
  
  		/** Contador de registros obtenido en la consulta previamente realizada a la funci�n consulta() de este archivo */
  		public function num_rows($consulta){   
   			return mysql_num_rows($consulta);  
   		}  
  		
		/** Funci�n regresa el n�mero de consultas generadas por la funci�n consulta() de este archivo  */
		public function getTotalConsultas(){  
   			return $this->total_consultas;  
   		}  
		
		/** Funci�n que cierra la consulta a la base de datos y cierra la conexi�n a la base de datos */
		public function close(){ 
			if ($this->conexion){ 
				return mysql_close($this->conexion); 
			} 
		} 
}
?>
<?php
/** Archivo que almacena los procedimientos para la gestión de la base de datos del Sistema en general */
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
   			$this->conexion = (mysqli_connect($host,$usuario,$password,$dbName)) or die(mysqli_error($this->conexion));
   			mysqli_select_db($this->conexion,$dbName) or die(mysqli_error($this->conexion));  
   			}  
   		}
		
		/** Gestión de consultas del Sistema */
  		public function consulta($consulta){  
   			$this->total_consultas++;  
   			$resultado = mysqli_query($this->conexion,$consulta);  
   			if(!$resultado){
				echo 'MySQLI Error: ' . mysqli_error($this->conexion);
   				exit;  
   			}  
   		return $resultado;   
   		}  
  
  		/** Enlistado en un arreglo los resultados de la consulta previamente realizada a la función consulta() de este archivo */
  		public function fetch_array($consulta){   
   			return mysqli_fetch_array($consulta);  
   		}
		
		/** Liberación de memoria */
		public function free_result($consulta){   
   			return mysqli_free_result($consulta);  
   		}
  
  		/** Contador de registros obtenido en la consulta previamente realizada a la función consulta() de este archivo */
  		public function num_rows($consulta){   
   			return mysqli_num_rows($consulta);  
   		}  
  		
		/** Función regresa el número de consultas generadas por la función consulta() de este archivo  */
		public function getTotalConsultas(){  
   			return $this->total_consultas;  
   		}  
		
		/** Función que cierra la consulta a la base de datos y cierra la conexión a la base de datos */
		public function close(){ 
			if ($this->conexion){ 
				return mysqli_close($this->conexion); 
			} 
		} 
}
?>
<?php
// ---------------------------------------------------------------------------
/**
* Paquete .cloud
* @package dotcloud
*/
// ---------------------------------------------------------------------------
/**
* Clase para manejo de la conexión a BD
* @author Antonio Juan Sánchez Martín
* @version 0.9
*/
class ConexionMySQL
{
	private 	$miconexion 	= null;
	private 	$bdNombre 		= '', $bdUsuario='',	$bdPassword='',		$bdServidor='',	$mySqlDirectorio ='';
                
	//---------------------------------------------------------------------------
	/**
	* Constructor
	* @param string $servidor: Nombre del servidor de bases de datos
	* @param string $usuario: Usuario de la base de datos
	* @param string $password: Password de acceso a la base de datos
	* @param string $basedatos: Nombre de la base de datos MySQL
	*/
	function __construct ($servidor = null , $usuario = null , $password = null , $basedatos = null) 
	{
		$this->bdNombre = $basedatos;
		$this->bdPassword = $password;
		$this->bdUsuario = $usuario;
		$this->bdServidor = $servidor; 

		$this->miconexion = new mysqli ($servidor, $usuario, $password, $basedatos);
		
		// Fallo en la conexión
		if ($this->miconexion->connect_errno) $this->miconexion = null;	
		else $this->consulta ("SET CHARACTER SET 'utf8'");
	}	


	//---------------------------------------------------------------------------
	/** 
	* Comprueba que la conexión esté activa
	* @return boolean Verdadero si la conexión está activa
	*/
	function comprobar ()
	{
		if ($this->miconexion != null) return 1;	
		else return 0;
	}

                
    //---------------------------------------------------------------------------
	/**
	* Ejecuta una consulta en la base de datos y devuelve el resultado. En el caso de fallar devuelve null. En caso de éxito devuelve un array
	* @param string $consulta: ConsultaS SQL
	* @return array Resultado de la consulta
	*/
	function consulta ($consulta)
	{	
			// Si no hay conexión devuelve nulo
			if ($this->miconexion == null) return null;
			
			// Ejecución de la consulta		
			$resultado = $this->miconexion->query($consulta);
			
			return $resultado ;
	}

	//---------------------------------------------------------------------------
	/**
	* Ejecuta una consulta básica en el que todos los datos pertenecen a una única tabla. Útil para listados sencillos
	* @param array $campos Array de campos del tipo: "nombre_tabla.campo1, nombre_tabla.campo2"
	* @return int $id Id del objeto buscado (opcional)
	*/
	function consultaBasica ($tabla, $campos, $id = null, $valores=null)
	{	
		if (count($campos))
		{
			// Creamos el listado SQL de los campos
			$camposSQL = " "; 
			foreach ($campos as $campo) $camposSQL .= $campo." ,";
			$camposSQL = substr ($camposSQL, 0, -1); 

			// Realizamos la consulta
			$consulta = "SELECT ". $camposSQL ." FROM ". $tabla;
			
			if ($id != null) 
			{
				
 				if (($valores != null) && (count($campos) != 0) && (count($valores) == count ($campos)))
 				{
 					// Se crea la consulta de inserción
 					$consulta = 'UPDATE '.$tabla.' SET ';
 					for ($i=0; $i<count($campos); $i++) 
 						{
 							$consulta .= $campos[$i]."='".$valores[$i]."',";
 						}
 					$consulta = substr ($consulta, 0, -1); 
 				}
 				$consulta .= " WHERE id=".$id;
			}
 			return $this->consulta ($consulta);
		}
		// Error campo nulo
		return null;
	}

	//---------------------------------------------------------------------------
	/**
	* Ejecuta una consulta en la base de datos y devuelve el resultado. En el caso de éxito devuelve 0. En caso de fallo devuelve el número de la consulta que dio el fallo
	* @param array $consulta Array de consultas a ejecutar
	* @return int Error (0 si todo correcto)
	*/
	function transaccion ($consultas)
	{	
			// Inicia transacción
			$this->inicioTransaccion ();

			// Ejecuta las consultas
			if ($consultas != null)
				foreach ($consultas as $num_consulta => $consulta)
				{		
					if ($this->consulta ($consulta) == null)
					{
						$this->abortarTransaccion ();
						// Terminación anómala
						return $num_consulta ;
					}
				}

			// Finaliza trasacción normalmente
			$this->finTransaccion ();
			return 0;	
	}

	//---------------------------------------------------------------------------
	/** Marca el inicio de una transacción en la Base de Datos
	*/
	function inicioTransaccion ()
	{	
		if ($this->miconexion != null) $this->miconexion->query("BEGIN"); 
	}


	//---------------------------------------------------------------------------
	/** Marca el final exitoso de una transacción en la Base de Datos
	*/
	function finTransaccion ()
	{	
		if ($this->miconexion != null) $this->miconexion->query("COMMIT");
	}

	//---------------------------------------------------------------------------
	/** Marca el final fallido de una transacción en la Base de Datos
	*/
	function abortarTransaccion ()
	{	
		if ($this->miconexion != null) $this->miconexion->query("ROLLBACK");
	}

	//---------------------------------------------------------------------------
	/** Cierre de sesión	
	*/
	function __destruct ()
	{
		//Cierra la conexión
		if ($this->miconexion != null) $this->miconexion->close();		
	}
}

?>
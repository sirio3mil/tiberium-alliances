<?php
class Database extends mysqli {

	public function __construct() {
    	parent::__construct ("localhost","root","Anteojo08","db_ta");
    	if ($this->connect_error){
    		throw new Exception("Estamos realizando labores de mantenimiento");
    	}
    }
	
	public function query($query){
		if(!empty($query)){
			$result = parent::query($query);
			if($result){
				return $result;
			}
			$trace = debug_backtrace();
			echo "<strong>".date("H:i")."</strong> Error SQL {$this->errno} {$this->error}<br />";
			echo "<strong>".date("H:i")."</strong> $query<br />";
			echo "<strong>".date("H:i")."</strong> ".__FILE__." en la linea ".__LINE__."<br />";
			foreach($trace as $indice=>$datos){
				echo "<strong>".date("H:i")."</strong> #$indice {$datos['function']} en {$datos['file']} ({$datos['line']})<br/>";
			}
			throw new Exception("Abortada conexion");
		}
		return false;
	}
	
	public function fetch_assoc($query){
		$result = $this->query($query);
		if($result){
			$row = $result->fetch_assoc();
			$result->close();
			return $row;
		}
		return false;
	}
	
	public function fetch_object($query){
		$result = $this->query($query);
		if($result){
			$row = $result->fetch_object();
			$result->close();
			return $row;
		}
		return false;
	}
	
	public function fetch_array($query){
		$result = $this->query($query);
		if($result){
			$retorno = array();
			while($row = $result->fetch_row()){
				$retorno[] = $row[0];
			}
			$result->close();
		}
		return $retorno;
	}
	
	public function fetch_value($query){
		$retorno = false;
		$result = $this->query($query);
		if($result){
			$row = $result->fetch_row();
			$retorno = (!empty($row[0]))?$row[0]:false;
			$result->close();
		}
		return $retorno;
	}
}
?>
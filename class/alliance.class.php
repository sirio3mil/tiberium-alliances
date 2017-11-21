<?php
class Alliance {
	
	protected $alliance_id;
	protected $name;
	
	function __construct(stdClass $data) {
		$this->alliance_id = $data->a;
		$this->name = $data->an;
		if(!$this->exist()){
			$this->create();
		}
		else{
			$this->update();
		}
		$this->check();
	}
	
	public function exist(){
		global $mysqli;
		$query = sprintf("SELECT COUNT(*) FROM alliances WHERE alliance_id = '%u'",
				$this->alliance_id
		);
		return (!empty($mysqli->fetch_value($query)))?TRUE:FALSE;
	}
	
	protected function check(){
		global $mysqli;
		$query = sprintf("UPDATE alliances SET check_time = CURRENT_TIMESTAMP WHERE alliance_id = '%u'",
				$this->alliance_id
		);
		return $mysqli->query($query);
	}
	
	public function create(){
		global $mysqli;
		$query = sprintf("INSERT INTO alliances
		        (
					alliance_id,
		            name
				)
				VALUES (
					'%u',
			        '%s'
				)",
				$this->alliance_id,
				$mysqli->real_escape_string($this->name)
		);
		return $mysqli->query($query);
	}
	
	public function update(){
		global $mysqli;
		if($this->modified()){
			if($this->save()){
				$query = sprintf("UPDATE alliances SET
						name = '%s',
						updated = CURRENT_TIMESTAMP 
						WHERE alliance_id = '%u'",
						$mysqli->real_escape_string($this->name),
						$this->alliance_id
				);
				return $mysqli->query($query);
			}
			return FALSE;
		}
		return TRUE;
	}
	
	protected function save(){
		global $mysqli;
		$query = sprintf("INSERT INTO alliances_historical
				(
					alliance,
					name
				)
				SELECT alliance_id,
				name 
				FROM alliances
				WHERE alliance_id = '%u'",
				$this->alliance_id
		);
		return $mysqli->query($query);
	}
	
	protected function modified(){
		global $mysqli;
		$query = sprintf("SELECT name 
				FROM alliances 
				WHERE alliance_id = '%u'",
				$this->alliance_id
		);
		$current = $mysqli->fetch_object($query);
		$current_md5 = md5($current->name);
		$new_md5 = md5($this->name);
		return ($current_md5 != $new_md5)?TRUE:FALSE;
	}
}

?>
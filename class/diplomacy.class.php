<?php
class Diplomacy {
	
	protected $alliance;
	protected $diplomacy_id;
	
	function __construct($alliance) {
		$this->alliance = $alliance;
	}
	
	protected function exist($alliance){
		global $mysqli;
		$query = sprintf("SELECT diplomacy_id 
				FROM diplomacy 
				WHERE alliance_one = '%u' 
				AND alliance_two = '%u'",
				$this->alliance,
				$alliance
		);
		$this->diplomacy_id = $mysqli->fetch_value($query);
		if(empty($this->diplomacy_id)){
			$query = sprintf("SELECT diplomacy_id
					FROM diplomacy
					WHERE alliance_two = '%u'
					AND alliance_one = '%u'",
					$this->alliance,
					$alliance
			);
			$this->diplomacy_id = $mysqli->fetch_value($query);
		}
		return (empty($this->diplomacy_id))?FALSE:TRUE;
	}
	
	public function set($alliance, $type){
		global $mysqli;
		if(!$this->exist($alliance)){
			return $this->create($alliance, $type);
		}
		if($this->save()){
			return $this->update($type);
		}
		return FALSE;
	}
	
	public function remove($alliance){
		global $mysqli;
		if($this->exist($alliance) && $this->save()){
			$query = sprintf("UPDATE diplomacy SET
					active = '0',
					updated = CURRENT_TIMESTAMP
					WHERE diplomacy_id = '%u'",
					$this->diplomacy_id
			);
			return $mysqli->query($query);
		}
		return FALSE;
	}
	
	protected function update($type){
		global $mysqli;
		$query = sprintf("UPDATE diplomacy SET
				active = '1',
				type = '%s',
				updated = CURRENT_TIMESTAMP
				WHERE diplomacy_id = '%u'",
				$mysqli->real_escape_string($type),
				$this->diplomacy_id
		);
		return $mysqli->query($query);
	}
	
	protected function save(){
		global $mysqli;
		$query = sprintf("INSERT INTO diplomacy_historical
				(
					diplomacy,
					active,
					type
				)
				SELECT diplomacy_id,
				active,
				type 
				FROM diplomacy
				WHERE diplomacy_id = '%u'",
				$this->diplomacy_id
		);
		return $mysqli->query($query);
	}
	
	protected function create($alliance, $type){
		global $mysqli;
		$query = sprintf("INSERT INTO diplomacy
				(
					alliance_one,
					alliance_two,
					type
				)
				VALUES
				(
					'%u',
					'%u',
					'%s'
				)",
				$this->alliance,
				$alliance,
				$mysqli->real_escape_string($type)
		);
		return $mysqli->query($query);
	}
}
?>
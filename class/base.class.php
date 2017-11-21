<?php
class Base {
	
	protected $base_id;
	protected $name;
	protected $player;
	protected $score;
	protected $x_coordinate;
	protected $y_coordinate;
	
	function __construct(stdClass $data) {
		$this->base_id = $data->i;
		$this->name = $data->n;
		$this->player = $data->player;
		$this->score = $data->p;
		$this->x_coordinate = $data->x;
		$this->y_coordinate = $data->y;
		if(!$this->exist()){
			$this->create();
		}
		else{
			$this->update();
		}
	}
	
	public function exist(){
		global $mysqli;
		$query = sprintf("SELECT COUNT(*) FROM bases WHERE base_id = '%u'",
				$this->base_id
		);
		return (!empty($mysqli->fetch_value($query)))?TRUE:FALSE;
	}
	
	public function create(){
		global $mysqli;
		$query = sprintf("INSERT INTO bases
		        (
					base_id,
		            name,
		            player,
		            score,
		            x_coordinate,
		            y_coordinate
				)
				VALUES (
					'%u',
			        '%s',
			        '%u',
			        '%u',
			        '%u',
			        '%u'
				)",
				$this->base_id,
				$mysqli->real_escape_string($this->name),
				$this->player,
				$this->score,
				$this->x_coordinate,
				$this->y_coordinate
		);
		return $mysqli->query($query);
	}
	
	public function update(){
		global $mysqli;
		if($this->modified()){
			if($this->save()){
				$query = sprintf("UPDATE bases SET
						name = '%s',
						score = '%u',
						x_coordinate = '%u',
						y_coordinate = '%u',
						updated = CURRENT_TIMESTAMP 
						WHERE base_id = '%u'",
						$mysqli->real_escape_string($this->name),
						$this->score,
						$this->x_coordinate,
						$this->y_coordinate,
						$this->base_id
				);
				return $mysqli->query($query);
			}
			return FALSE;
		}
		return TRUE;
	}
	
	protected function save(){
		global $mysqli;
		$query = sprintf("INSERT INTO bases_historical
				(
					base,
					name,
					score,
					x_coordinate,
					y_coordinate
				)
				SELECT base_id,
				name,
				score,
				x_coordinate,
				y_coordinate 
				FROM bases
				WHERE base_id = '%u'",
				$this->base_id
		);
		return $mysqli->query($query);
	}
	
	protected function modified(){
		global $mysqli;
		$query = sprintf("SELECT name,
		        score,
		        x_coordinate,
		        y_coordinate 
				FROM bases 
				WHERE base_id = '%u'",
				$this->base_id
		);
		$current = $mysqli->fetch_object($query);
		$current_md5 = md5($current->name.$current->score.$current->x_coordinate.$current->y_coordinate);
		$new_md5 = md5($this->name.$this->score.$this->x_coordinate.$this->y_coordinate);
		return ($current_md5 != $new_md5)?TRUE:FALSE;
	}
}

?>
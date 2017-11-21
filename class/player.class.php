<?php
class Player {
	
	protected $player_id;
	protected $name;
	protected $faction;
	protected $alliance;
	protected $rank;
	protected $score;
	protected $forgotten_bases_destroyed;
	protected $player_bases_destroyed;
	
	function __construct(stdClass $data) {
		$this->player_id = $data->i;
		$this->name = $data->n;
		$this->faction = $data->faction;
		$this->alliance = $data->a;
		$this->rank = $data->r;
		$this->score = $data->p;
		$this->forgotten_bases_destroyed = $data->bde;
		$this->player_bases_destroyed = $data->d;
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
		$query = sprintf("SELECT COUNT(*) FROM players WHERE player_id = '%u'",
				$this->player_id
		);
		return (!empty($mysqli->fetch_value($query)))?TRUE:FALSE;
	}
	
	protected function check(){
		global $mysqli;
		$query = sprintf("UPDATE players SET check_time = CURRENT_TIMESTAMP WHERE player_id = '%u'",
				$this->player_id
		);
		return $mysqli->query($query);
	}
	
	public function create(){
		global $mysqli;
		if(empty($this->alliance)){
			$query = sprintf("INSERT INTO players
		            (
						player_id,
			            name,
			            faction,
			            alliance,
			            rank,
			            score,
			            forgotten_bases_destroyed,
			            player_bases_destroyed
					)
					VALUES (
						'%u',
				        '%s',
				        '%u',
				        NULL,
				        '%u',
				        '%u',
				        '%u',
				        '%u'
					)",
					$this->player_id,
					$mysqli->real_escape_string($this->name),
					$this->faction,
					$this->rank,
					$this->score,
					$this->forgotten_bases_destroyed,
					$this->player_bases_destroyed
			);
		}
		else{
			$query = sprintf("INSERT INTO players
		            (
						player_id,
			            name,
			            faction,
			            alliance,
			            rank,
			            score,
			            forgotten_bases_destroyed,
			            player_bases_destroyed
					)
					VALUES (
						'%u',
				        '%s',
				        '%u',
				        '%u',
				        '%u',
				        '%u',
				        '%u',
				        '%u'
					)",
					$this->player_id,
					$mysqli->real_escape_string($this->name),
					$this->faction,
					$this->alliance,
					$this->rank,
					$this->score,
					$this->forgotten_bases_destroyed,
					$this->player_bases_destroyed
			);
		}
		return $mysqli->query($query);
	}
	
	public function update(){
		global $mysqli;
		if($this->modified()){
			if($this->save()){
				if(empty($this->alliance)){
					$query = sprintf("UPDATE players SET 
							alliance = NULL,
							rank = '%u',
							score = '%u',
							forgotten_bases_destroyed = '%u',
							player_bases_destroyed = '%u',
							updated = CURRENT_TIMESTAMP 
							WHERE player_id = '%u'",
							$this->rank,
							$this->score,
							$this->forgotten_bases_destroyed,
							$this->player_bases_destroyed,
							$this->player_id
					);
				}
				else{
					$query = sprintf("UPDATE players SET
							alliance = '%u',
							rank = '%u',
							score = '%u',
							forgotten_bases_destroyed = '%u',
							player_bases_destroyed = '%u',
							updated = CURRENT_TIMESTAMP 
							WHERE player_id = '%u'",
							$this->alliance,
							$this->rank,
							$this->score,
							$this->forgotten_bases_destroyed,
							$this->player_bases_destroyed,
							$this->player_id
					);
				}
				return $mysqli->query($query);
			}
			return FALSE;
		}
		return TRUE;
	}
	
	protected function save(){
		global $mysqli;
		$query = sprintf("INSERT INTO players_historical
				(
					player,
					alliance,
					rank,
					score,
					forgotten_bases_destroyed,
					player_bases_destroyed
				)
				SELECT player_id,
				alliance,
				rank,
				score,
				forgotten_bases_destroyed,
				player_bases_destroyed 
				FROM players
				WHERE player_id = '%u'",
				$this->player_id
		);
		return $mysqli->query($query);
	}
	
	protected function modified(){
		global $mysqli;
		$query = sprintf("SELECT alliance,
		        rank,
		        score,
		        forgotten_bases_destroyed,
		        player_bases_destroyed 
				FROM players 
				WHERE player_id = '%u'",
				$this->player_id
		);
		$current = $mysqli->fetch_object($query);
		$current_md5 = md5($current->alliance.$current->rank.$current->score.$current->forgotten_bases_destroyed.$current->player_bases_destroyed);
		$new_md5 = md5($this->alliance.$this->rank.$this->score.$this->forgotten_bases_destroyed.$this->player_bases_destroyed);
		return ($current_md5 != $new_md5)?TRUE:FALSE;
	}
}

?>
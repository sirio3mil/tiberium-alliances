<?php
include_once '../class/db.class.php';
include_once '../class/cncta.class.php';
include_once '../class/base.class.php';
include_once '../class/alliance.class.php';
include_once '../class/player.class.php';
set_time_limit(0);
$start_time = $_SERVER['REQUEST_TIME_FLOAT'];
$mysqli = new Database();
try{
	$cncta = TiberiumAlliances::getInstance();
	// login into Game and get a sessionId
	$cncta->login("sirio3mil@gmail.com", "Anteojo08");
	// start a game session
	$cncta->openGameSession();
	// get start point
	$query = "SELECT start_point, start_sum FROM processes_data_import WHERE DATE > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 HOUR) ORDER BY DATE DESC LIMIT 1";
	$point = $mysqli->fetch_object($query);
	if(!$point){
		$point = new stdClass();
		$point->start_point = 0;
		$point->start_sum = 0;
	}
	elseif($point->start_point > 24){
		$point->start_point = 0;
		$point->start_sum = 0;
	}
	// set variables
	$alliances = array();
	$fallidos_jugador = 0;
	$fallidos_info = 0;
	$last_ranking = ($point->start_point * 1000) + $point->start_sum;
	$jugadores = [];
	for ($i=$point->start_point; $i<25; $i++){
		$start = ($i * 1000) + $point->start_sum;
		$end = $start + 1000;
		$data = array(
				"firstIndex"	=> $start,
				"lastIndex"		=> $end,
				"view"			=> 0,
				"rankingType"	=> 0,
				"sortColumn"	=> 2,
				"ascending"		=> true
		);
		/*
		echo "<pre>";
		print_r($data);
		echo "</pre><br />";
		*/
		$ranking = $cncta->get('RankingGetData', $data);
		/*
		echo "<pre>";
		print_r($ranking);
		echo "</pre><br />";
		*/
		if(!empty($ranking->p)){
			foreach ($ranking->p as $player){
				if(!empty($player) && !empty($player->p) && !in_array($player->p, $jugadores)){
					$jugadores[] = $player->p;
					$info = $cncta->get('GetPublicPlayerInfo', array("id"=>$player->p));
					if(!empty($info) && !empty($info->i)){
						$info->faction = $player->f;
						$last_ranking++;
						if(!empty($player->a) && !in_array($player->a, $alliances)){
							$cAlliance = new Alliance($player);
							$alliances[] = $player->a;
						}
						$cPlayer = new Player($info);
						echo sprintf("%s Jugador %u %s ranking %s<br />",
								date("H:i:s"),
								$player->p,
								$player->pn,
								str_pad($info->r, 5, 0, STR_PAD_LEFT)
						);
						foreach ($info->c as $base){
							$base->player = $player->p;
							$cBase = new Base($base);
						}
					}
					else{
						throw new Exception("No hay informacion del jugador $player->p en la linea $i");
					}
				}
				else{
					throw new Exception("No hay jugador en la linea $i");
				}
			}
		}
		else{
			throw new Exception("Se ha perdido la sesion");
		}
		sleep(5);
	}
}
catch(Exception $e){
	echo date("H:i:s"), " ", $e->getMessage(), "<br />";
}
$elapsed_time = microtime(true) - $start_time;
$elapsed_seconds = intval($elapsed_time);
$elapsed_precision = $elapsed_time - $elapsed_seconds;
echo date("H:i:s"), " Tiempo de ejecucion ", date("H:i:s", $elapsed_time - 60*60 - 1), ".", substr($elapsed_precision, 2), "<br />";
if(!empty($last_ranking)){
	$start_point = intval($last_ranking / 1000);
	$start_sum = $last_ranking - ($start_point*1000);
	$query = sprintf("INSERT INTO processes_data_import
	        (
				elapsed,
	            start_point,
	            start_sum
			)
			VALUES 
			(
				'%s',
		        '%u',
		        '%u'
			)",
			$elapsed_time,
			$start_point,
			$start_sum
	);
	if($mysqli->query($query)){
		echo date("H:i:s"), " Start point ", $start_point, "<br />";
		echo date("H:i:s"), " Start sum ", $start_sum, "<br />";
	}
}
$mysqli->close();
?>
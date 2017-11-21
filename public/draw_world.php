<?php
include_once '../class/db.class.php';
include_once '../class/diplomacy.class.php';
include_once '../defines/defines.php';
include_once '../include/diplomacy.inc';
define("SELECTED_PLAYER", 11770);
$filtros = "";
$mysqli = new Database();
$query = "SELECT players.name,
		alliances.name as alliance,
		alliances.alliance_id 
		FROM players 
		LEFT JOIN alliances ON players.alliance = alliances.alliance_id 
		WHERE player_id = '%u'";
$query = sprintf($query,
		SELECTED_PLAYER
);
$player = $mysqli->fetch_object($query);
if (!empty($_POST)){
	if(!empty($_POST['aliados'])){
		$cDiplomacy = new Diplomacy($player->alliance_id);
		$cDiplomacy->set($_POST['aliados'], TIPO_ALIADOS);
	}
	if(!empty($_POST['pacto_no_agresion'])){
		$cDiplomacy = new Diplomacy($player->alliance_id);
		$cDiplomacy->set($_POST['pacto_no_agresion'], TIPO_PNA);
	}
	if(!empty($_POST['enemigos'])){
		$cDiplomacy = new Diplomacy($player->alliance_id);
		$cDiplomacy->set($_POST['enemigos'], TIPO_ENEMIGOS);
	}
}
$aliados 	= GetAlliances($player->alliance_id, TIPO_ALIADOS);
$pna 		= GetAlliances($player->alliance_id, TIPO_PNA);
$enemigos 	= GetAlliances($player->alliance_id, TIPO_ENEMIGOS);
$idaliados	= array_keys($aliados);
$idpna		= array_keys($pna);
$idenemigos	= array_keys($enemigos);
$alianzas 	= array_unique(array_merge($idaliados, $idpna, $idenemigos));
if(isset($_POST['search-bases'])){
	if(!empty($_POST['players'])){
		$filtros .= sprintf("bases.player IN (%s) AND ",
				implode(",", $_POST['players'])
		);
	}
	if(!empty($_POST['alliances'])){
		$filtros .= sprintf("players.alliance IN (%s) AND ",
				implode(",", $_POST['alliances'])
		);
	}
	if(!empty($_POST['relations'])){
		$relaciones = "";
		foreach ($_POST['relations'] as $relation){
			switch ($relation){
				case 1:
					if(!empty($aliados)){
						$relaciones .= sprintf("players.alliance IN (%s) OR ",
								implode(",", $idaliados)
						);
					}
					break;
				case 2:
					if(!empty($pna)){
						$relaciones .= sprintf("players.alliance IN (%s) OR ",
								implode(",", $idpna)
						);
					}
					break;
				case 3:
					if(!empty($enemigos)){
						$relaciones .= sprintf("players.alliance IN (%s) OR ",
								implode(",", $idenemigos)
						);
					}
					break;
				case 4:
					if(!empty($alianzas)){
						$relaciones .= sprintf("(players.alliance NOT IN (%s) OR players.alliance IS NULL) OR ",
								implode(",", $alianzas)
						);
					}
					break;
			}
		}
		if(!empty($relaciones)){
			$filtros .= "(" . substr($relaciones, 0, -4) . ") AND ";
		}
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Tiberium Alliances World 15</title>
		<link rel="stylesheet" href="../font-awesome/css/font-awesome.css">
		<link rel="stylesheet" href="../css/jquery-ui.css">
		<style>
		ul{padding-left:15px}
		ul li{list-style-type:none}
		.texto-listado{margin-left:10px}
		.coords{width:50px}
		.current-coords{font-weight:bold;text-align:center}
		button, select, .full-width{width:100%;text-align:center}
		.fa-times{cursor: pointer}
		.leyend-container{height:50px}
		.leyend-color-container{float: left;width:50px;height:100%;padding:0}
		.leyend-color-image{width:100%;height:100%}
		.leyend-text-container{float: left;margin-left:15px;padding-top:19px;font-size:11px;font-weight:bold;text-transform:uppercase}
		.clear-float{clear:both}
		</style>
	</head>
	<body>
		<div style="width:250px;position:absolute;left:10px;top:0">
			<form method="post" id="buscar-coordenadas">
				<h3><i class="fa fa-codepen"></i> Coordenadas</h3>
				<input type="number" id="coords_axis_x_current" class="coords current-coords" value="0" readonly="readonly" />
				<span>:</span>
				<input type="number" id="coords_axis_y_current" class="coords current-coords" value="0" readonly="readonly" />
				<br />
				<input type="number" max="1096" min="0" id="coords_axis_x_one" class="coords" />
				<span>:</span>
				<input type="number" max="1096" min="0" id="coords_axis_y_one" class="coords" />
				<br />
				<br />
				<button id="search-coords">Localizar coordenadas</button>
				<br />
				<button id="show-range-coords">Mostrar alcance de la base</button>
				<br />
				<br />
				<input type="number" max="1096" min="0" id="coords_axis_x_two" class="coords" />
				<span>:</span>
				<input type="number" max="1096" min="0" id="coords_axis_y_two" class="coords" />
				<br />
				<br />
				<button id="calculate-coords">Calcular distancia</button>
				<h3><i class="fa fa-search"></i> Búsquedas</h3>
				<h4><i class="fa fa-users"></i> Jugadores</h4>
				<input type="text" id="search-commanders" class="full-width" />
				<ul id="search-selected-players">
				<?php 
				if(!empty($_POST['players'])){
					$query = "SELECT player_id, name FROM players WHERE player_id IN (%s)";
					$query = sprintf($query,
						implode(",", $_POST['players'])
					);
					$result = $mysqli->query($query);
					while($row = $result->fetch_row()){
						echo "<li><i class='fa fa-times remove-searched' data-target='player-{$row[0]}'></i><span class='texto-listado'>{$row[1]}</span></li>";
					}
					$result->close();
				}
				?>
				</ul>
				<h4><i class="fa fa-chain"></i> Alianzas</h4>
				<input type="text" id="search-alliances" class="full-width" />
				<ul id="search-selected-alliances">
				<?php 
				if(!empty($_POST['alliances'])){
					$query = "SELECT alliance_id, name FROM alliances WHERE alliance_id IN (%s)";
					$query = sprintf($query,
						implode(",", $_POST['alliances'])
					);
					$result = $mysqli->query($query);
					while($row = $result->fetch_row()){
						echo "<li><i class='fa fa-times remove-searched' data-target='alliance-{$row[0]}'></i><span class='texto-listado'>{$row[1]}</span></li>";
					}
					$result->close();
				}
				?>
				</ul>
				<h4><i class="fa fa-graduation-cap"></i> Diplomacia</h4>
				<?php 
				$relations = (!empty($_POST['relations']))?$_POST['relations']:array();
				?>
				<input type="checkbox" name="relations[]" class="search-groups" value="1" <?=(in_array(1, $relations))?"checked":""?> />
				<label>Aliados</label>
				<br />
				<input type="checkbox" name="relations[]" class="search-groups" value="2" <?=(in_array(2, $relations))?"checked":""?> />
				<label>PNA</label>
				<br />
				<input type="checkbox" name="relations[]" class="search-groups" value="3" <?=(in_array(3, $relations))?"checked":""?> />
				<label>Enemigos</label>
				<br />
				<input type="checkbox" name="relations[]" class="search-groups" value="4" <?=(in_array(4, $relations))?"checked":""?> />
				<label>Otros</label>
				<br />
				<br />
				<button name="search-bases">Mostrar</button>
				<?php 
				if(!empty($_POST['players'])){
					foreach ($_POST['players'] as $id){
						echo "<input type='hidden' value='{$id}' id='player-{$id}' name='players[]' />";
					}
				}
				if(!empty($_POST['alliances'])){
					foreach ($_POST['alliances'] as $id){
						echo "<input type='hidden' value='{$id}' id='alliance-{$id}' name='alliances[]' />";
					}
				}
				?>
			</form>
			<div>
				<h3><i class="fa fa-puzzle-piece"></i> Leyenda</h3>
				<div class="leyend-container">
					<div class="leyend-color-container" style="background-color:#040f81">
						<img src="../images/mancha.png" class="leyend-color-image" />
					</div>
					<div class="leyend-text-container">Mis bases</div>
					<br class="clear-float" />
				</div>
				<div class="leyend-container">
					<div class="leyend-color-container" style="background-color:#3a8104">
						<img src="../images/mancha.png" class="leyend-color-image" />
					</div>
					<div class="leyend-text-container">Mi orden</div>
					<br class="clear-float" />
				</div>
				<div class="leyend-container">
					<div class="leyend-color-container" style="background-color:#0073ea">
						<img src="../images/mancha.png" class="leyend-color-image" />
					</div>
					<div class="leyend-text-container">Aliados</div>
					<br class="clear-float" />
				</div>
				<div class="leyend-container">
					<div class="leyend-color-container" style="background-color:#000000">
						<img src="../images/mancha.png" class="leyend-color-image" />
					</div>
					<div class="leyend-text-container">PNA</div>
					<br class="clear-float" />
				</div>
				<div class="leyend-container">
					<div class="leyend-color-container" style="background-color:#cc0000">
						<img src="../images/mancha.png" class="leyend-color-image" />
					</div>
					<div class="leyend-text-container">Enemigos</div>
					<br class="clear-float" />
				</div>
				<div class="leyend-container">
					<div class="leyend-color-container" style="background-color:#cb842e">
						<img src="../images/mancha.png" class="leyend-color-image" />
					</div>
					<div class="leyend-text-container">Otros</div>
					<br class="clear-float" />
				</div>
			</div>
		</div>
		<div style="height:1096px;width:1096px;margin:0 auto">
			<canvas></canvas>
		</div>
		<div style="width:250px;position:absolute;right:10px;top:0">
			<form method="post" id="editar-diplomacia">
				<h3><i class="fa fa-user"></i> Jugador</h3>
				<ul>
					<li><i class="fa fa-crosshairs"></i><span class="texto-listado"><?=$player->alliance?></span></li>
					<li><i class="fa fa-user"></i><span class="texto-listado"><?=$player->name?></span></li>
				</ul>
				<h3><i class="fa fa-check"></i> Aliados</h3>
				<select name="aliados" class="combo-diplomacia">
					<option value="0">selecciona</option>
					<?php 
					$query = "SELECT alliance_id, name FROM alliances ORDER BY name";
					$result = $mysqli->query($query);
					while($row = $result->fetch_row()){
						if(!in_array($row[0], $alianzas)){
							echo "<option value='{$row[0]}'>{$row[1]}</option>";
						}
					}
					$result->close();
					?>
				</select>
				<?php 
				if(!empty($aliados)){
					echo "<ul>";
					foreach ($aliados as $alliance => $value){
						echo "<li><i class='fa fa-times remove-diplomacy' data-alliance='{$alliance}'></i><span class='texto-listado'>{$value}</span></li>";
					}
					echo "</ul>";
				}
				?>
				<h3><i class="fa fa-exclamation-triangle"></i> PNA</h3>
				<select name="pacto_no_agresion" class="combo-diplomacia">
					<option value="0">selecciona</option>
					<?php 
					$query = "SELECT alliance_id, name FROM alliances ORDER BY name";
					$result = $mysqli->query($query);
					while($row = $result->fetch_row()){
						if(!in_array($row[0], $alianzas)){
							echo "<option value='{$row[0]}'>{$row[1]}</option>";
						}
					}
					$result->close();
					?>
				</select>
				<?php 
				if(!empty($pna)){
					echo "<ul>";
					foreach ($pna as $value){
						echo "<li><i class='fa fa-times remove-diplomacy' data-alliance='{$alliance}'></i><span class='texto-listado'>{$value}</span></li>";
					}
					echo "</ul>";
				}
				?>
				<h3><i class="fa fa-bomb"></i> Enemigos</h3>
				<select name="enemigos" class="combo-diplomacia">
					<option value="0">selecciona</option>
					<?php 
					$query = "SELECT alliance_id, name FROM alliances ORDER BY name";
					$result = $mysqli->query($query);
					while($row = $result->fetch_row()){
						if(!in_array($row[0], $alianzas)){
							echo "<option value='{$row[0]}'>{$row[1]}</option>";
						}
					}
					$result->close();
					?>
				</select>
				<?php 
				if(!empty($enemigos)){
					echo "<ul>";
					foreach ($enemigos as $value){
						echo "<li><i class='fa fa-times remove-diplomacy' data-alliance='{$alliance}'></i><span class='texto-listado'>{$value}</span></li>";
					}
					echo "</ul>";
				}
				?>
				<br />
				<br />
				<button>Actualizar</button>
			</form>
		</div>
	</body>
	<script src="../js/jquery-2.1.1.js"></script>
	<script src="../js/jquery-ui.js"></script>
	<script src="../js/jcanvas.min.js"></script>
	<script src="../js/draw_world.js"></script>
	<script type="text/javascript">
	$(function(){
		<?php 
		if(!empty($filtros)){
			$filtros = "WHERE " . substr($filtros, 0, -5);
		}
		$query = "SELECT bases.name,
				bases.x_coordinate, 
				bases.y_coordinate,
				players.alliance,
				players.player_id   
				FROM bases 
				INNER JOIN players ON players.player_id = bases.player 
				$filtros 
				LIMIT 10";
		$result = $mysqli->query($query);
		while($row = $result->fetch_object()){
			if(SELECTED_PLAYER == intval($row->player_id)){
				$color = "#040f81";
			}
			elseif($player->alliance_id == $row->alliance){
				$color = "#3a8104";
			}
			elseif(array_key_exists($row->alliance, $aliados)){
				$color = "#0073ea";
			}
			elseif(array_key_exists($row->alliance, $pna)){
				$color = "#000000";
			}
			elseif(array_key_exists($row->alliance, $enemigos)){
				$color = "#cc0000";
			}
			else{
				$color = "#cb842e";
			}
			echo sprintf("$('canvas').drawRect({layer: true,groups: ['bases'],name: '%s',fillStyle: '%s',x: %u, y: %u,width: 1,height: 1});\n\r",
				$row->name,
				$color,
				$row->x_coordinate,
				$row->y_coordinate
			);
		}
		$result->close();
		?>
	});
	</script>
</html>
<?php 
$mysqli->close();
?>
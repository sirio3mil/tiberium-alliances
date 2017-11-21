<?php
include_once '../class/db.class.php';
include_once '../class/location.class.php';
include_once '../defines/defines.php';
$mysqli = new Database();
// busco el punto primero en los sectores grandes
$sectores = array(
		1 => "N",
		2 => "NE",
		3 => "E",
		4 => "SE",
		5 => "S",
		6 => "SO",
		7 => "O",
		8 => "NO"
);
$pointLocation = new Location();
$sector = 0;
$vertices = array(
		"extremos" 	=> array(),
		"total" 	=> 0
);
$lados = 32;
$angulo_lado_poligono = 360/(2*$lados);
foreach ($sectores as $sector_id => $acronym){
	$query = "SELECT real_angle 
			FROM sectors 
			WHERE sectors.sector_id = '%u'";
	$query = sprintf($query,
			$sector_id
	);
	$poligono = array();
	$angulo_inicial = $mysqli->fetch_value($query);
	if($angulo_inicial){
		$angulo_final = $angulo_inicial + 45;
		$poligono[] = "548 -548";
		for ($i = $angulo_inicial;$i <= $angulo_final; $i += $angulo_lado_poligono){
			$radianes = deg2rad($i);
			$x = 548 + round(cos($radianes) * 548);
			$y = 548 - round(sin($radianes) * 548);
			$poligono[] = "{$x} -{$y}";
		}
		$poligono[] = $poligono[0];
	}
	if($pointLocation->pointInPolygon("{$_POST['x']} -{$_POST['y']}", $poligono) !== "outside"){
		$sector = $sector_id;
		break;
	}
}
if(!empty($sector)){
	$query = "SELECT sector_id, acronym, angle, real_angle FROM sectors WHERE sector_id = '%u'";
	$query = sprintf($query,
			$sector
	);
	$sector = $mysqli->fetch_object($query);
	// busco en los sub sectores
	for($j = 1; $j <= 5; $j++){
		$query = "SELECT inner_radius,
				outer_radius 
				FROM subsectors 
				WHERE subsector_id = '%u'";
		$query = sprintf($query,
				$j
		);
		$poligono = array();
		$vertex = array();
		$subsector = $mysqli->fetch_object($query);
		if($subsector){
			$angulo_final = $sector->real_angle + 45;
			$radios = array($subsector->inner_radius, $subsector->outer_radius);
			foreach ($radios as $radio){
				for ($i = $sector->real_angle;$i <= $angulo_final; $i += $angulo_lado_poligono){
					$radianes = deg2rad($i);
					$x = 548 + round(cos($radianes) * $radio);
					$y = 548 - round(sin($radianes) * $radio);
					$coordenadas = "{$x} -{$y}";
					if(!in_array($coordenadas, $poligono)){
						$poligono[] = $coordenadas;
						if($i == $sector->real_angle || $i == $angulo_final){
							$vertex[] = array(
									"x" => $x,
									"y" => $y
							);
						}
					}
				}
			}
			$poligono[] = $poligono[0];
		}
		if($pointLocation->pointInPolygon("{$_POST['x']} -{$_POST['y']}", $poligono) !== "outside"){
			$vertices["extremos"] = $vertex;
			$vertices["total"] = count($vertex);
			$vertices["sector"] = $sector->acronym;
			$vertices["angulo"] = $sector->angle;
			$vertices["radio_interior"] = $subsector->inner_radius;
			$vertices["radio_exterior"] = $subsector->outer_radius;
			break;
		}
	}
}
$mysqli->close();
echo json_encode($vertices);
?>
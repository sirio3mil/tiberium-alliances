<?php
include_once '../class/db.class.php';
include_once '../defines/defines.php';
$mysqli = new Database();
if(!empty($_GET['term'])) {
	$query = "SELECT alliance_id,
			name 
			FROM alliances 
			WHERE name LIKE '%s%s%s'
			LIMIT 10";
	$query = sprintf($query,
			"%",
			$mysqli->real_escape_string($_GET['term']),
			"%"
	);
	$data = array();
	$result = $mysqli->query($query);
	if($result){
		while($row = $result->fetch_object()){
			$data[] =  array(
					"value" => $row->name,
					"id"	=> $row->alliance_id
			);
		}
		$result->close();
	}
	echo json_encode($data);
}
$mysqli->close();
?>
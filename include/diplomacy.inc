<?php
function GetAlliances($alianza, $tipo){
	global $mysqli;
	$query = sprintf("SELECT alliance_id,
			name
			FROM alliances
			INNER JOIN diplomacy ON diplomacy.alliance_one = alliances.alliance_id
			WHERE alliance_two = '%u'
			AND active = '1'
			AND type = '%s'
			UNION DISTINCT
			SELECT alliance_id,
			name
			FROM alliances
			INNER JOIN diplomacy ON diplomacy.alliance_two = alliances.alliance_id
			WHERE alliance_one = '%u'
			AND active = '1'
			AND type = '%s'",
			$alianza,
			$tipo,
			$alianza,
			$tipo
	);
	$result = $mysqli->query($query);
	$alianzas = array();
	if(!empty($result->num_rows)){
		while($row = $result->fetch_row()){
			$alianzas[$row[0]] = $row[1];
		}
	}
	$result->close();
	return $alianzas;
}
?>
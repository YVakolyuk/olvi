<?php
	require 'connection.php';
	$id_track = $_POST["id_track"];
	$query=mysql_query("SELECT id_marker, latitude, longitude FROM markers where id_track='$id_track'");
	$row=mysql_num_rows($query);
	for($i=0;$i<$row;$i++) {
		$row_mas=mysql_fetch_array($query);
		$object[$i]=array('id_marker' => $row_mas['id_marker'], 'latitude' => $row_mas['latitude'],  'longitude' => $row_mas['longitude']);
	}
	echo json_encode($object);
?>
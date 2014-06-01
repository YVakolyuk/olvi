<?php
	require 'connection.php';
	$query=mysql_query("SELECT * FROM tracks");
	$row=mysql_num_rows($query);
	for($i=0;$i<$row;$i++) {
		$row_mas=mysql_fetch_array($query);
		$object[$i]=array('id_track' => $row_mas['id_track'], 'track_name' => $row_mas['track_name']);
	}
	echo json_encode($object);
?>
<?php
	require 'connection.php';
	$query=mysql_query("SELECT latitude,longitude,altitude,time FROM gps_location WHERE time=(select max(time) from gps_location)");
	$row=mysql_num_rows($query);
	$row_mas=mysql_fetch_array($query);
	$object=array('time' => $row_mas['time'], 'latitude' => $row_mas['latitude'], 'longitude' => $row_mas['longitude'], 'altitude' => $row_mas['altitude']);
	echo json_encode($object);
?>
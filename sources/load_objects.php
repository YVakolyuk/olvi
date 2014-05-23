<?php
	require 'connection.php';
	$query=mysql_query("SELECT o.id_object, obj_name, id_trace, status, latitude, longitude, altitude FROM 
									objects as o join gps_location as gl on o.id_object=gl.id_object");
	$row=mysql_num_rows($query);
	for($i=0;$i<$row;$i++) {
		$row_mas=mysql_fetch_array($query);
		$object[$i]=array('id_object' => $row_mas['id_object'], 'obj_name' => $row_mas['obj_name'],
									'status' => $row_mas['status'], 'id_trace' => $row_mas['id_trace'], 
									'lat' => $row_mas['latitude'], 'lng' => $row_mas['longitude'], 'alt' => $row_mas['altitude']);
	}
	echo json_encode($object);
?>
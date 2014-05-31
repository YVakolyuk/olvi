<?php
	require 'connection.php';
	$temp = $_GET['p'];
//	if($temp=="load") {
		$query=mysql_query("select gl.id_object, o.obj_name, o.id_track, o.status, gl.latitude, gl.longitude, gl.altitude
										from gps_location as gl
										join objects as o
										on gl.id_object=o.id_object
										where gl.time=(select max(gl2.time)
																from gps_location as gl2
																where gl2.id_object=gl.id_object)
																order by id_object");
		$row=mysql_num_rows($query);
		for($i=0;$i<$row;$i++) {
			$row_mas=mysql_fetch_array($query);
			$object[$i]=array('id_object' => $row_mas['id_object'], 'obj_name' => $row_mas['obj_name'],
										'status' => $row_mas['status'], 'id_track' => $row_mas['id_track'], 
										'lat' => $row_mas['latitude'], 'lng' => $row_mas['longitude'], 'alt' => $row_mas['altitude']);
		}
		echo json_encode($object);
/*	}
	elseif($temp=="obj") {
		$find_id = $_GET['id'];
		$query=mysql_query("select gl.id_object, o.obj_name, o.id_trace, o.status, gl.latitude, gl.longitude, gl.altitude
										from gps_location as gl
										join objects as o
										on gl.id_object=o.id_object
										where gl.time=(select max(gl2.time)
																from gps_location as gl2
																where gl2.id_object=gl.id_object) and
																			o.id_object='$find_id'
																order by id_object");
		$row_mas=mysql_fetch_array($query);
		$object=array('id_object' => $row_mas['id_object'], 'obj_name' => $row_mas['obj_name'],
										'status' => $row_mas['status'], 'id_trace' => $row_mas['id_trace'], 
										'lat' => $row_mas['latitude'], 'lng' => $row_mas['longitude'], 'alt' => $row_mas['altitude']);
		
		echo json_encode($object);
	}*/
?>
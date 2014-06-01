<?php
	require 'connection.php';
	$id_track = $_POST["id_track"];
	$id_object = $_POST["id"];
	if(mysql_query("UPDATE objects SET id_track='$id_track' WHERE id_object='$id_object'")) 
		echo "updated";
	else
		echo "Something wrong!";
?>
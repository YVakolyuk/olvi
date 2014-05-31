<?php
	require 'connection.php';
	$id=$_POST["id_object"];
	$stat=$_POST["stat"];
	if(mysql_query("UPDATE objects SET status='$stat' WHERE id_object='$id'")){
		echo "database updated";
	}
?>
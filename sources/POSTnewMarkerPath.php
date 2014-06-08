<?php
	require 'connection.php';
	$latitude=$_POST["lat"];
	$longitude=$_POST["lng"];
	$path_id=$_POST["path"];
	if(mysql_query("INSERT INTO markers VALUES (NULL,'$path_id', '$latitude', '$longitude')")){
		echo "database updated";
	} else echo "Something wrong!";
?>
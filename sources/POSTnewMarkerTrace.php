<?php
	require 'connection.php';
	$latitude=$_POST["lat"];
	$longitude=$_POST["lng"];
	$altitude=$_POST["alt"];
	if(mysql_query("INSERT INTO `gps_location`(`ilocation_id`, `time`, `latitude`, `longitude`, `altitude`) 
												VALUES (NULL,NULL,$latitude,$longitude,$altitude)")){
		echo "database updated";
	}
?>
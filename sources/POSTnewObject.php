<?php
	require 'connection.php';
	$name=$_POST["name"];
	if(mysql_query("INSERT INTO `objects` VALUES (NULL,'$name',null,'off')")){
		echo mysql_insert_id();
	}
	else echo "not set. Bad request.";
?>
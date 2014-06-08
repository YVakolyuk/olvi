<?php
	$path_name = $_POST["path_name"];
	require 'connection.php';
		if(mysql_query("INSERT INTO tracks VALUES (NULL,'$path_name')")){
			echo mysql_insert_id();
		} else {
			echo "Something wrong!";
		}
?>
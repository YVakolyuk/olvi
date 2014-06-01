<?php 
	require 'connection.php';
		$id_track = $_POST['track'];
		$query=mysql_query("SELECT count(*) as num_rows FROM objects WHERE id_track='$id_track'") or die(mysql_error());
		$res=mysql_fetch_array($query);
		if($res['num_rows'] == 0) {
			if(mysql_query("DELETE FROM tracks WHERE id_track='$id_track'") && 
				mysql_query("DELETE FROM markers WHERE id_track='$id_track'")) {
				echo "deleted";
			} else {
				echo "Something wrong!";
			}
		} else {
			die("On this path assigned object. Removal is not possible");
		}
?>
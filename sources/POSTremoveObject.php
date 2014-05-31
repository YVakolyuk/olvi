<?php 
	require 'connection.php';
		$login = $_POST['login'];
		$password = $_POST['admPass'];
		$id = $_POST['id'];
		$query=mysql_query("select user_password from users where user_login='$login'") or die(mysql_error());
		$res=mysql_fetch_array($query);
			if($res['user_password'] == md5($password)) {
				if(mysql_query("DELETE FROM objects WHERE id_object='$id'")) {
				//	$obj = array('code' => 0, 'answ' => "Password for $login successfully changed.");
				//	echo json_encode($obj);
					echo "deleted";
				}
				else {
				//	$obj = array('code' => 1, 'answ' => "Something wrong!");
				//	echo json_encode($obj);
					echo "Something wrong!";
				}
			}
			else {
			//	$obj = array('code' => 1, 'answ' => "Wrong current password");
			//	echo json_encode($obj);
				die("Wrong current password");
			}
?>
<?php 
	require 'connection.php';
		$login = $_POST['login'];
		$password = $_POST['password'];
		$newPass = $_POST['newPassword'];
		$query=mysql_query("select user_password from users where user_login='$login'") or die(mysql_error());
		$res=mysql_fetch_array($query);
			if($res['user_password'] == md5($password)) {
				$newPass = md5($newPass);
				if(mysql_query("update users set user_password='$newPass' where user_login='$login'")) {
					$obj = array('code' => 0, 'answ' => "Password for $login successfully changed.");
					echo json_encode($obj);
					//echo "Password for $login successfully changed.";
				}
				else {
					$obj = array('code' => 1, 'answ' => "Something wrong!");
					echo json_encode($obj);
					//echo "Something wrong!";
				}
			}
			else {
				$obj = array('code' => 1, 'answ' => "Wrong current password");
				echo json_encode($obj);
				//die("Wrong current password");
			}
?>
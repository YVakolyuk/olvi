<?php
	require 'connection.php';
	session_start();
//	if(isset($_POST['submit'])) {
//	echo "1";
		$login = $_POST['login'];
		$password = $_POST['password'];
		
		$query = mysql_query("select * from users where user_login='$login'") or die(mysql_error());
		$user_data = mysql_fetch_array($query);
		if($user_data['user_password'] == md5($password)) {
			$_SESSION['olvi'] = $login;
			header("Location: index.php"); exit();
		}
		else {
			$_SESSION['log']=true;
			header("Location: index.php"); exit();
		}
//	}
?>
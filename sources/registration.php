<?php 
	require 'connection.php';
		$login = $_POST['login'];
		$name = $_POST['userName'];
		$password = $_POST['userPassword'];
		$admin = $_POST['admLogin'];
		$admPass = $_POST['admPass'];
		$query=mysql_query("select count(*) as num_rows from users where user_login='$login'") or die(mysql_error());
		$res=mysql_fetch_array($query);
		if($res['num_rows'] == 0) {
			$query=mysql_query("select user_password from users where user_login='$admin'") or die(mysql_error());
			$res=mysql_fetch_array($query);
			if($res['user_password'] == md5($admPass)) {
				$password = md5($password);
				if(mysql_query("insert into users values(null, '$name', '$login', '$password')")) {
					$obj = array('code' => 0, 'answ' => "User $name successfully registered.");
					echo json_encode($obj);
					//echo "User $name successfully registered.";
				}
				else {
					$obj = array('code' => 1, 'answ' => "Something wrong!");
					echo json_encode($obj);
					//echo "Something wrong!";
				}
			}
			else die("Wrong admin password");
		}
		else {
			$obj = array('code' => 1, 'answ' => "This login already exists.");
			echo json_encode($obj);
			//die("This login already exists");
		}
?>
<?php 
	require 'connection.php';
	
	if(isset($_POST['submit'])) {
		$login = $_POST['login'];
		$query=mysql_query("select count(*) as num_rows from users where user_login='$login'") or die(mysql_error());
		$res=mysql_fetch_array($query);
		if($res['num_rows'] == 0) {
			$password = $_POST['password'];
			$repassword = $_POST['repassword'];
			if($password == $repassword) {
				$password = md5($password);
				if(mysql_query("insert into users values(null, '$login', '$password')") or die(mysql_error()))
					echo "Registration complete";
			}
			else die("Password must much");
		}
		else die("This login already exists");
	}
?>

<form method="post" action="registration.php">
	<input type="text" name="login" placeholder=" | Enter your login" required /><br>
	<input type="password" name="password" placeholder=" | Enter your password" required /><br>
	<input type="password" name="repassword" placeholder=" | Retype password" required /><br>
	<input type="submit" name="submit" value="Register" /><br>
</form>
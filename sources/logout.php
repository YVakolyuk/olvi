<?php
	session_start();
	unset($_SESSION['olvi']);
	session_destroy();
	header("Location: index.php"); exit();
?>
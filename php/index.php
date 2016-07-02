<?php

 require_once('config.php');
 require_once('functions.php');

 session_start();

 if(empty($_SESSION['me'])){ //sent to login.php if there's no contents. stay on this page if there's contents.
 	header('Location: login.php');
 	exit;
 }



 $me = $_SESSION['me'];

 if($_SERVER['REQUEST_METHOD'] != "POST"){
 	setToken();
 }
 else{
 	checkToken();
 	empty($_SESSION['me']);
 	header("LOCATION: login.php");
 	exit;
 }
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset = "utf-8">
	<title>Home</title>
</head>
<body>
	<div>
		Logged in as <?php echo "hi".h($me['member_firstname']); ?>
	</div>	
	<h1>Member's List</h1>
	<form action="login.php" method="POST">
		<input type="submit" value="logout">
	</form>
</body>

</html>
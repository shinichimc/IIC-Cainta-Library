<?php 

error_reporting(E_ALL & ~E_NOTICE);

if(isset($_POST['submit'])) {

	if(!empty($_POST['name']) && !empty($_POST['contact'])) {

		$name = $_POST['name'];
		$contact = $_POST['contact'];

		$dbc = mysqli_connect('localhost','root','','events_tracking_system');

		$sql = "insert into contact_list (name, contact) values ('".$name."', '".$contact."')";
		echo $sql;
		mysqli_query($dbc, $sql);

		if(mysqli_affected_rows($dbc) == 1) {
			echo "successfully added.";
		} else {
			echo "the item could not be added.";
		}

		mysqli_close($dbc);

	} else {
			echo "you forgot to enter your items.";
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>My Phonebook</title>
	<link href="main.css" type="text/css" rel="stylesheet">
</head>
<body>
<div id = "wrapper">
<h1>New Contact</h1>
<ul>
<form action="" method="post">
	<p>NAME : <input name="name" type="text" placeholder="Enter your name"></p>
	<p>CONTACT : <input name="contact" type="text" placeholder="Enter your contact"></p>
	<p><input name="submit" type="submit" value="Save"><button><a href="exam_home.php">Back</a></button></p>
</form>
</div>
</body>
</html>
<?php 

error_reporting(E_ALL & ~E_NOTICE);


if(!empty($_POST['id'])) {
	
	$dbc = mysqli_connect('localhost','root','','events_tracking_system');

	$sql = "delete from contact_list where id = ".$_POST['id'];
	mysqli_query($dbc, $sql);

	mysqli_close($dbc);

} 

?>
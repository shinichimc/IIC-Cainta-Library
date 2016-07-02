<?php

require_once('php/config.php');
require_once('php/functions.php');

$dbh = connectDB();

if(isset($_POST['id'])){
	$sql = "update notification set check_seen = 1 where member_id = :id";
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array(":id" => (string)$_POST['id']));
}

echo "member_id is:".$_POST['id'];

$dbh = null;



?>
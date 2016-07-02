<?php 
require_once('../php/config.php');
require_once('../php/functions.php');

$dbh = connectDB();

$sql = "update tasks set type = 'deleted', modified = now() where id = :id";

$stmt = $dbh->prepare($sql);
$stmt->execute(array(":id" => (int)$_POST['id']));

?>
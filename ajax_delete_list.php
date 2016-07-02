<?php 
require_once('php/config.php');
require_once('php/functions.php');

session_start();
$me = $_SESSION['me'];

echo $me['member_id'];

$dbh = connectDB();

$sql = "delete from rec_list where ISBN = :isbn and member_id = :id";
$stmt = $dbh->prepare($sql);
$stmt->execute(array(
	":isbn" => $_POST['id'],
	":id" => $me['member_id']
	 ));

$dbh = null;

?>
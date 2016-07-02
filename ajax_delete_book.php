<?php 

require_once('php/config.php');
require_once('php/functions.php');

$dbh = connectDB();

if(isset($_POST['id'])){
	$sql = "delete from book_basic where ISBN = :isbn";
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array(":isbn" => $_POST['id']));
}

if(isset($_POST['checkboxes'])){
	$checkboxes = $_POST['checkboxes'];
	foreach($checkboxes as $checkbox){
		$sql = "delete from book_basic where ISBN = :isbn";
		$stmt = $dbh->prepare($sql);
		$stmt->execute(array(":isbn" => $checkbox));
	}
}

$dbh = null;

?>
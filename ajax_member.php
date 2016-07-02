<?php 

require_once('php/config.php');
require_once('php/functions.php');

$dbh = connectDB();

// id1 = disable, id2 = enable, id3 = edit

if(isset($_POST['id'])){
	$sql = "update member_basic set disabled = 1 where member_id = :id";
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array(":id" => $_POST['id']));
}
if(isset($_POST['id2'])){
	$sql = "update member_basic set disabled = 0 where member_id = :id";
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array(":id" => $_POST['id2']));
}

if(isset($_POST['id3'])){
	$id = (string)$_POST['id3'];
	$sql = "select * from member_basic where member_id =".$id." limit 1";
	$stmt = $dbh->query($sql);
	$member = $stmt->fetch();
	$rs = array(
		"firstname" => $member['member_firstname'],
		"lastname" => $member['member_lastname'],
		"type" => $member['member_type'],
		"birthdate" => $member['birthdate']
	);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($rs);
}

if(isset($_POST['checkboxes'])){
	$checkboxes = $_POST['checkboxes'];
	foreach($checkboxes as $checkbox){
		$sql = "delete from member_basic where member_id = :id";
		$stmt = $dbh->prepare($sql);
		$stmt->execute(array(":id" => $checkbox));
	}
}

$dbh = null;

?>
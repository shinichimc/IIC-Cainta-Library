<?php 

require_once('php/config.php');
require_once('php/functions.php');


	$id = $_POST['id3'];

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

?>
<?php

require_once('php/config.php');
require_once('php/functions.php');

$dbh = connectDB();

if(isset($_GET['borrowed_id'])){
	///////////////////////////////////EXTEND
	$ctr = $dbh->query("select count(*) from book_borrowed where borrowed_id = ".$_GET['borrowed_id']." and datediff(date_due,now()) >= 3")->fetchColumn();
	if($ctr == 0){
		$dbh->query("update book_borrowed set date_due = date_add(date_due, INTERVAL ".getExpire()." day) where borrowed_id = ".$_GET['borrowed_id']);
	}
	
}

header("LOCATION: ".$_SERVER['HTTP_REFERER']);
exit;







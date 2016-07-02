<?php
  
  require_once('php/config.php');
  require_once('php/functions.php');

  session_start();

  $me = $_SESSION['me'];
  
  $dbh = connectDB();

  if(isset($_POST['isbn'])){

  	$ISBN = $_POST['isbn'];

  	$check_already_reserve = $dbh->query("select count(*) from book_reserved where member_id = '".$me['member_id']."' and ISBN = '".$ISBN."' and (status = 'waiting' or status = 'available')")->fetchColumn();
  	echo $check_already_reserve;
  	if($check_already_reserve == 0)	{

	  
	  $check_availability = $dbh->query("select count(*) from book_each where ISBN = ".$ISBN." and availability = 'available'")->fetchColumn();
	  $waiting_status = ($check_availability >= 1) ? 0 : 1;

	  if($waiting_status == 0) { // if you dont have to wait to pick up the book
	  	$accession_id = $dbh->query("select accession_id from book_each where ISBN = ".$ISBN." and availability = 'available' order by rand() limit 1")->fetchColumn();
	  	if($me['member_type'] == 'Faculty' || $me['member_type'] == 'Staff'){
	  		$stmt = $dbh->prepare("insert into book_reserved (member_id, ISBN, accession_id, date_reserved, date_available, status) values(:id , :isbn, :accession, now(), now(), 'available')");
	  	} else {                                                                                                                                                                                                                                                                                                                                                                                                                                                               

			$stmt = $dbh->prepare("insert into book_reserved (member_id, ISBN, accession_id, date_reserved, date_available, date_expire, status) values(:id , :isbn, :accession, now(), now(), date_add(now(), INTERVAL ".getExpire()." day), 'available')");
		}

		$stmt->execute(array(":id" => $me['member_id'], ":accession" => $accession_id, ":isbn" => $ISBN));
		
		$dbh->query("update book_each set availability = 'unavailable' where accession_id = $accession_id");
		$book_title = $dbh->query("select title from book_basic where ISBN = '".$ISBN."'")->fetchColumn();
		$dbh->query("insert into notification (member_id, message, date_notif, href) values('".$me['member_id']."', '".$book_title." is now available!', now(), 'member_book_details.php?isbn=".$ISBN."' )");

	  } else { // if you have to wait
	  	$dbh->query("insert into book_reserved (member_id, ISBN, date_reserved, status) values('".$me['member_id']."','".$ISBN."', now(), 'waiting')"); 
	  }

	  $dbh->query("insert into notification (member_id, message, date_notif, href) values('0123456789', '".$me['member_firstname']." ".$me['member_lastname']." reserved ".$book_title."', now(), 'admin_book_details.php?ISBN=".$ISBN."' )");

	 
    }

 }
  header("LOCATION: ".$_SERVER['HTTP_REFERER']);
  exit;
  
?>
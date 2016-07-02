<?php
  
require_once('php/config.php');
require_once('php/functions.php');

session_start();

$me = $_SESSION['me'];

$dbh = connectDB();

if(isset($_GET['ISBN']) && isset($_GET['id'])){

	$ISBN = $_GET['ISBN'];
	$BRWD_ID = $_GET['id'];

	$ctr = $dbh->query("select count(*) from book_borrowed where borrowed_id = ".$BRWD_ID." and not status = 'returned'")->fetchColumn();
	if($ctr != 0) {

		//fetching the book record
		$sql = "select * from book_basic where ISBN = :ISBN";
		$stmt = $dbh->prepare($sql);
		$stmt->execute(array(":ISBN" => $ISBN));
		$book = $stmt->fetch();
		
		//fetching borrowed record of the person who returne
		$sql = "select * from book_borrowed where borrowed_id = :id";
		$stmt = $dbh->prepare($sql);
		$stmt->execute(array(":id" =>$BRWD_ID));
		$borrowed_record_who_returned = $stmt->fetch();


		//changing status ->returned
		$dbh->query("update book_borrowed set status = 'returned', date_returned = now() where borrowed_id = ".$BRWD_ID);

		//fetching reservation record of the person who is the first in Line
		$sql = "select * from book_reserved r, member_basic m where m.member_id = r.member_id and ISBN = :ISBN and status = 'waiting' ORDER BY case member_type when 'Faculty' then 1 when 'Staff' then 1 else 2 end, date_reserved limit 1";
		$stmt = $dbh->prepare($sql);
		$stmt->execute(array(":ISBN" => $ISBN));
		$reservation_record_who_firstinLine = $stmt->fetch();

		$inLine_rowCount = $dbh->query("select count(*) from (select r.* from book_reserved r, member_basic m where m.member_id = r.member_id and ISBN = '".$ISBN."' and status = 'waiting' ORDER BY case member_type when 'Faculty' then 1 when 'Staff' then 1 else 2 end, date_reserved) as count")->fetchColumn();


		//if there are people in Line
		if($inLine_rowCount >= 1){

		  	//assigning accession_id, new status, date available to the person who's first in Line
		  	if($reservation_record_who_firstinLine['member_type'] == 'Faculty' || $reservation_record_who_firstinLine['member_type'] == 'Staff'){
		  		$dbh->query("update book_reserved set accession_id = ".$borrowed_record_who_returned['accession_id'].", status = 'available', date_available = now() where reservation_id = ".$reservation_record_who_firstinLine['reservation_id']); 
		  	} else {
		  		$dbh->query("update book_reserved set accession_id = ".$borrowed_record_who_returned['accession_id'].", status = 'available', date_available = now(), date_expire = date_add(now(), interval ".getExpire()." day) where reservation_id = ".$reservation_record_who_firstinLine['reservation_id']); 
		  	}

		  	$dbh->query("insert into notification (member_id, message, date_notif, href) values('".$reservation_record_who_firstinLine['member_id']."', '".$book['title']." is now available!', now(), 'member_book_details.php?isbn=".$ISBN."' )");

	    } else {

	    		$dbh->query("update book_each set availability = 'available' where accession_id = ".$borrowed_record_who_returned['accession_id']);
	    }
	}

 }

  header("LOCATION: ".$_SERVER['HTTP_REFERER']);
  exit;
  
?>
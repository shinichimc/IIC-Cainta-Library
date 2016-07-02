<?php
  
  require_once('php/config.php');
  require_once('php/functions.php');

  session_start();

  $me = $_SESSION['me'];
  
  $dbh = connectDB();

  if(isset($_GET['accession']) && isset($_GET['member_id']) && isset($_GET['res_id']) &&isset($_GET['isbn'])){

  	$ctr = $dbh->query("select count(*) from book_reserved where reservation_id = ".$_GET['res_id']." and not status = 'picked'")->fetchColumn();

  	if($ctr != 0){
  		
		  $member_id = $_GET['member_id'];
		  $accession_id = $_GET['accession'];
		  $reservation_id = $_GET['res_id'];
		  $isbn = $_GET['isbn'];
		  $member_type = $dbh->query("select member_type from member_basic where member_id = '".$member_id."'")->fetchColumn();

		  $book_title = $dbh->query("select title from book_basic where ISBN = '".$isbn."'")->fetchColumn();
		  echo $member_id;

		  //inserting records into book_borrowed table
		  if($member_type == 'Faculty' || $member_type == 'Staff'){

		 	 $sql = "insert into book_borrowed (member_id, accession_id, ISBN, date_borrowed, status) values(:member_id, :accession_id, :isbn, now(), 'borrowed')";
		 	 echo $sql;
		 	 echo $member_type;
		  } else {
		  	
		  	$sql = "insert into book_borrowed (member_id, accession_id, ISBN, date_borrowed, date_due, status) values(:member_id, :accession_id, :isbn, now(), date_add(now(), INTERVAL ".getExpire()." day), 'borrowed')";
		  	echo $sql;
		  	echo $member_type;
		  	$duedate = Date('M j(D)', strtotime("+".getExpire()." days"));
		  	$dbh->query("insert into notification (member_id, message, date_notif, href) values('".$member_id."', 'You now borrowed ".$book_title." . Date due is ".$duedate."', now(), 'member_book_details.php?isbn=".$isbn."' )");

		  }
		  $stmt = $dbh->prepare($sql);
		  $stmt->execute(array(":member_id" => $member_id, ":accession_id" => $accession_id, ":isbn" => $isbn));

		  //updating status for book_reserved table
		  $dbh->query("update book_reserved set status = 'picked' where reservation_id = ".$reservation_id);

		  $dbh->query("update book_each set availability = 'unavailable' where accession_id = ".$accession_id);
	}
 }

///////////////////////////////////////////FROM THE RIGHT TABLE IN ADMIN_BOOK_DETAILS.php
 if(isset($_GET['form_accession']) && isset($_GET['form_membername']) && isset($_GET['form_isbn'])){

 	$ctr = $dbh->query("select count(*) from book_borrowed where accession_id = ".$_GET['form_accession']." and status = 'borrowed'")->fetchColumn();
 	if($ctr == 0) {

	 	$name = $_GET['form_membername'];
	 	$accession_id = $_GET['form_accession'];
	 	$ISBN = $_GET['form_isbn'];

	 	//fetching records from MEMBER_BASIC table
	 	$sql = "select *, concat(member_firstname,' ',member_lastname) as complete_name from member_basic having complete_name = :complete_name";
	 	$stmt = $dbh->prepare($sql);
	 	$stmt->execute(array(":complete_name" => $name));
	 	$member_info = $stmt->fetch();

	 	//fetching person record from book_reserved who's AVAILABLE && LAST IN ROW
	 	$sql = "select r.*, m.member_type, date_format(date_reserved, '%b %d / %h:%i %p') as time from book_reserved r, member_basic m where m.member_id = r.member_id and ISBN = :ISBN and status = 'available' ORDER BY case member_type when 'Student' then 1 else 2 end, date_reserved desc limit 1";
	 	$stmt = $dbh->prepare($sql);
	 	$stmt->execute(array(":ISBN" => $ISBN));
	 	$member_last_in_row_and_available = $stmt->fetch();

	 	
	 	//inserting records into BOOK_BORROWERD table
	 	if($member_info['member_type'] == 'Faculty' || $member_info['member_type'] == 'Staff'){
		    	$sql = "insert into book_borrowed (member_id, accession_id, ISBN, date_borrowed, status) values(:member_id, :accession_id, :isbn, now(), 'borrowed')";
		  		echo $sql;
		} else {
		   		$sql = "insert into book_borrowed (member_id, accession_id, ISBN, date_borrowed, date_due, status) values(:member_id, :accession_id, :isbn, now(), date_add(now(), INTERVAL ".getExpire()." day), 'borrowed')";
		 		echo $sql;
	    }
			    $stmt = $dbh->prepare($sql);
			    $stmt->execute(array(":member_id" => $member_info['member_id'], ":accession_id" => $accession_id, ":isbn" => $ISBN));

		//available => unavailable
	    $dbh->query("update book_each set availability = 'unavailable' where accession_id = ".$accession_id);

	    //counting availability of the book
	 	$count_availability = $dbh->query("select count(*) from book_each where ISBN = ".$ISBN." and availability = 'available'")->fetchColumn();

	 	if($count_availability == 0){

	 		$accession_sources = array();
	 		$sql = "select * from book_reserved where ISBN = ".$ISBN." and status = 'available'";
	 		foreach($dbh->query($sql) as $row){
	 			array_push($accession_sources, $row);
	 		}

	 		$accession_shuffle = array();
	 		foreach($accession_sources as $source){
	 			if($source['accession_id'] != $accession_id){
	 				array_push($accession_shuffle,$source['accession_id']);
	 			}
	 		}
	 	
	 		//changing status of the last person in line who was available
	 		$dbh->query("update book_reserved set status = 'waiting', date_expire = null, date_available = null, accession_id = null where reservation_id = ".$member_last_in_row_and_available['reservation_id']);

	 		$remained_members = array();
	 		$sql = "select * from book_reserved where ISBN = ".$ISBN." and status = 'available'";
	 		foreach($dbh->query($sql) as $row){
	 			array_push($remained_members,$row);
	 		}

	 		$c = 0;
	 		foreach($remained_members as $remained){

	 			$dbh->query("update book_reserved set accession_id = ".$accession_shuffle[$c]." where reservation_id = ".$remained['reservation_id']);
	 			$dbh->query("update book_each set availability = 'unavailable' where accession_id = ".$accession_shuffle[$c]);
	 			$c++;

	 		}

	 	}
	 }

 }
  header("LOCATION: ".$_SERVER['HTTP_REFERER']);
  exit;
  
?>
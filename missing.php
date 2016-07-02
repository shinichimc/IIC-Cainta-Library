<?php 

require_once('php/config.php');
require_once('php/functions.php');

$dbh = connectDB();

$ISBN = $_GET['ISBN'];

//fetching the book record
$sql = "select * from book_basic where ISBN = :ISBN";
$stmt = $dbh->prepare($sql);
$stmt->execute(array(":ISBN" => $ISBN));
$book = $stmt->fetch();

//fetching reservation record of the person who is the first in Line
$sql = "select * from book_reserved r, member_basic m where m.member_id = r.member_id and ISBN = :ISBN and status = 'waiting' ORDER BY case member_type when 'Faculty' then 1 when 'Staff' then 1 else 2 end, date_reserved limit 1";
$stmt = $dbh->prepare($sql);
$stmt->execute(array(":ISBN" => $ISBN));
$reservation_record_who_firstinLine = $stmt->fetch();

//fetching reservation record of  the person who is last in line & available
$sql = "select r.*, m.member_type, date_format(date_reserved, '%b %d / %h:%i %p') as time from book_reserved r, member_basic m where m.member_id = r.member_id and ISBN = '".$ISBN."' and status = 'available' ORDER BY case member_type when 'Student' then 1 else 2 end, date_reserved desc limit 1";
$stmt = $dbh->prepare($sql);
$stmt->execute(array(":ISBN" => $ISBN));
$reservation_record_who_last_and_available = $stmt->fetch();

//counting people who are 'waiting'
$inLine_rowCount = $dbh->query("select count(*) from (select r.* from book_reserved r, member_basic m where m.member_id = r.member_id and ISBN = '".$ISBN."' and status = 'waiting' ORDER BY case member_type when 'Faculty' then 1 when 'Staff' then 1 else 2 end, date_reserved) as count")->fetchColumn();

///// MAX COUNT, MISSING COUNT, AVAILABLE COUNT///
$max_count = $dbh->query("select count(*) from book_each where ISBN = '".$ISBN."'")->fetchColumn();
$missing_count = $dbh->query("select count(*) from book_each where missing = 1 and ISBN = '".$ISBN."'")->fetchColumn();
$available_count = $dbh->query("select count(*) from book_reserved where ISBN = '".$ISBN."' and status = 'available'")->fetchColumn();
$missing_plus_available_count = $missing_count + $available_count;




///////////////////////WHEN MISSING
if(isset($_GET['accession_id_missing']) && isset($_GET['ISBN'])){
	$borrow_check = $dbh->query("select count(*) from book_borrowed where accession_id = ".$_GET['accession_id_missing']." and status = 'borrowed'")->fetchColumn();

	if($borrow_check == 0){


		$dbh->query("update book_each set availability = 'unavailable' where accession_id = ".$_GET['accession_id_missing']);

	   //counting availability of the book
		$count_availability = $dbh->query("select count(*) from book_each where ISBN = '".$ISBN."' and availability = 'available'")->fetchColumn();

		if($count_availability == 0){

			$accession_sources = array();
			$sql = "select * from book_reserved where ISBN = '".$ISBN."' and status = 'available'";
			foreach($dbh->query($sql) as $row){
				array_push($accession_sources, $row);
			}

			$accession_shuffle = array();
			foreach($accession_sources as $source){
				if($source['accession_id'] != $_GET['accession_id_missing']){
					array_push($accession_shuffle,$source['accession_id']);
				}
			}
		
			//changing status of the last person in line who was available
			$dbh->query("update book_reserved set status = 'waiting', date_expire = null, date_available = null, accession_id = null where reservation_id = ".$reservation_record_who_last_and_available['reservation_id']);

			$remained_members = array();
			$sql = "select * from book_reserved where ISBN = '".$ISBN."' and status = 'available'";
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

	$dbh->query("update book_each set missing = 1, date_lost = now() where accession_id = ".$_GET['accession_id_missing']);
	
}

///////////////////////WHEN FOUND
if(isset($_GET['accession_id_found']) && isset($_GET['ISBN'])){
	$borrow_check = $dbh->query("select count(*) from book_borrowed where accession_id = ".$_GET['accession_id_found']." and status = 'borrowed'")->fetchColumn();

	if($borrow_check == 0){


		$dbh->query("update book_each set missing = 0 where accession_id = ".$_GET['accession_id_found']);

		
		if($missing_plus_available_count <= $max_count){

		  	//assigning accession_id, new status, date available to the person who's first in Line
		  	if($reservation_record_who_firstinLine['member_type'] == 'Faculty' || $reservation_record_who_firstinLine['member_type'] == 'Staff'){
		  		$dbh->query("update book_reserved set accession_id = ".$_GET['accession_id_found'].", status = 'available', date_available = now() where reservation_id = ".$reservation_record_who_firstinLine['reservation_id']); 
		  	} else {
		  		$dbh->query("update book_reserved set accession_id = ".$_GET['accession_id_found'].", status = 'available', date_available = now(), date_expire = date_add(now(), interval ".getExpire()." day) where reservation_id = ".$reservation_record_who_firstinLine['reservation_id']); 
		  	}

		  	$dbh->query("insert into notification (member_id, message, date_notif, href) values('".$reservation_record_who_firstinLine['member_id']."', '".$book['title']." is now available!', now(), 'member_book_details.php?isbn='".$ISBN."')");

		} else {

				$dbh->query("update book_each set availability = 'available' where accession_id = ".$_GET['accession_id_found']);
		}
	} else {
		$dbh->query("update book_each set missing = 0 where accession_id = ".$_GET['accession_id_found']);
	}

}


header("LOCATION: ".$_SERVER['HTTP_REFERER']);
exit;

?>
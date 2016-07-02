<?php


function connectDB() {
	try{
		return new PDO(DSN, DB_USER,DB_PASSWORD);

	}
    catch(PDOException $e){
		echo $e->getMessage();
		exit;
	}
}

function h($s) {
	return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}

function usernameExists($username, $dbh){
	$sql = "select * from member_basic where member_id = :id limit 1";
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array(":id" => $username));
	$user = $stmt->fetch();
	return $user ? $user : false;
}

function getUser($username, $password, $dbh){
	$sql = "select * from member_basic where member_id = :id and password = :pw limit 1";
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array(
		":id" => $username,
		// ":pw" => getSha1Password($password)
			":pw" => $password

		));
	$user = $stmt->fetch();
	return $user ? $user : false;
}

function setToken() {
 	$token = sha1(uniqid(mt_rand(), true));
 	$_SESSION['token'] = $token;
 }
function checkToken() {
 	if(empty($_SESSION['token']) || ($_SESSION['token'] != $_POST['token'])) {
 		echo "invalid post";
 		exit;
 	}
 }

function getSha1Password($s) {
 	return (sha1(PASSWORD_KEY.$s));
 }

function getNotification($s) {
	$dbh = connectDB();
  $notifications = array();
  $sql = "select *, date_format(date_notif, '%h:%i %p  %b %d') as time from notification where member_id = ".$s." order by date_notif desc";
  foreach($dbh->query($sql) as $row){
  array_push($notifications, $row);
	}
	return $notifications;
}

function priorityUpdate($isbn, $dbh) {

  $book = $dbh->query("select title from book_basic where ISBN = '".$isbn."'")->fetchColumn();

  $display_reservation = array();

  $sql = "select r.*, m.member_type, date_format(date_reserved, '%b %d / %h:%i %p') as time from book_reserved r, member_basic m where m.member_id = r.member_id and ISBN = '".$isbn."' and not status = 'picked' and not status = 'cancelled'
          ORDER BY
           case member_type
              when 'Faculty' then 1
              when 'Staff' then 1
              else 2
          end, date_reserved";

  foreach($dbh->query($sql) as $row){
  array_push($display_reservation, $row);
  }

  $ctr_student_available = 0;
  $ctr_faculty_staff_waiting = 0;

  foreach($display_reservation as $reservation){

    if($reservation['member_type'] == 'Student' && $reservation['status'] == 'available') {
      ${"student_rs_" . $ctr_student_available} = $reservation['reservation_id'];
      ${"student_ac_" . $ctr_student_available} = $reservation['accession_id'];
      ${"student_ma_" . $ctr_student_available} = $reservation['member_id'];
      $ctr_student_available++;

    }

    if(($reservation['member_type'] == 'Faculty' && $reservation['status'] == 'waiting') || ($reservation['member_type'] == 'Staff' && $reservation['status'] == 'waiting')){
      ${"facStaff_" . $ctr_faculty_staff_waiting} = $reservation['reservation_id'];
      $ctr_faculty_staff_waiting++;
    }
  }

   //when there are students and faculty
  if($ctr_student_available >= 1 && $ctr_faculty_staff_waiting >= 1){
      //when student is more than faculty -> loop the number of faculty
      if($ctr_student_available>$ctr_faculty_staff_waiting) {

        $c = 0;
        for($i = 0 ; $i < $ctr_faculty_staff_waiting ; $i++){


          $dbh->query("update book_reserved set accession_id = null, status = 'waiting', date_available = null, date_expire = null where reservation_id = ".${"student_rs_".(($ctr_student_available-1) - $c)});
          $dbh->query("update book_reserved set accession_id = ".${"student_ac_".(($ctr_student_available-1) - $c)}." , date_expire = date_add(now(), interval ".getExpire()." day), status = 'available', date_available = now() where reservation_id = ".${"facStaff_".$i});
          $dbh->query("insert into notification (member_id, message, date_notif, href) values('".${"student_ma_".(($ctr_student_available-1) - $c)}."', '".$book." that was available is now reserved by one of the faculty members/staff. be a bit more patient.', now(), 'member_book_details.php?isbn=".$isbn."' )");

          $c--;
        }

      }
      else {

        $c = 0;
        for($i = 0 ; $i < $ctr_student_available ; $i++){
          $dbh->query("update book_reserved set accession_id = null, status = 'waiting', date_available = null, date_expire = null where reservation_id = ".${"student_rs_".(($ctr_student_available-1) - $c)});
          $dbh->query("update book_reserved set accession_id = ".${"student_ac_".(($ctr_student_available-1) - $c)}." , date_expire = date_add(now(), interval ".getExpire()." day), status = 'available', date_available = now() where reservation_id = ".${"facStaff_".$i});
          $dbh->query("insert into notification (member_id, message, date_notif, href) values('".${"student_ma_".(($ctr_student_available-1) - $c)}."', '".$book." that was available is reserved by one of the faculty members/staff. be a bit more patient.', now(), 'member_book_details.php?isbn=".$isbn."' )");
          $c--;
        }

      }
  }

  $display_reservation = array();
  $sql = "select r.*, m.member_type, date_format(date_reserved, '%b %d / %h:%i %p') as time from book_reserved r, member_basic m where m.member_id = r.member_id and ISBN = '".$isbn."' and not status = 'picked' and not status = 'cancelled'
          ORDER BY
           case member_type
              when 'Faculty' then 1
              when 'Staff' then 1
              else 2
          end, date_reserved";
  foreach($dbh->query($sql) as $row){
    array_push($display_reservation, $row);
  }


  return $display_reservation;

}

function getExpire() {

  switch (date('w')) {

    //Sunday : 0 ~ Saturday 6
    case 0 :
    return 3;
    break;

    case 1 :
    return 3;
    break;

    case 2 :
    return 3;
    break;

    case 3 :
    return 5;
    break;

    case 4 :
    return 4;
    break;

    case 5 :
    return 4;
    break;

    case 6 :
    return 3;
    break;

    default:

  }
}

function handleExpire ($dbh) {

  //fetching all the book_reserved table records that are available and expire
     $records_with_available_and_expire = array();
     $sql = "select * from book_reserved where status = 'available' and date_format(date_expire,'%Y%m%d') <= date_format(curdate(),'%Y%m%d')";
     foreach($dbh->query($sql) as $row){
       array_push($records_with_available_and_expire, $row);
       // fields : reservation_id, member_id, accession_id, ISBN, date_reserved, date_available, date_expire, status
     }

  //counting number of records that satisfy the above query
     $count =  $dbh->query("select count(*) from book_reserved where status = 'available' and date_format(date_expire,'%Y%m%d') <= date_format(curdate(),'%Y%m%d')")->fetchColumn();


    if($count >= 1){
      foreach($records_with_available_and_expire as $record_with_available_and_expire){

        //fetching the book record
        $sql = "select * from book_basic where ISBN = :ISBN";
        $stmt = $dbh->prepare($sql);
        $stmt->execute(array(":ISBN" => $record_with_available_and_expire['ISBN']));
        $book = $stmt->fetch();

        //changing status available -> cancelled
        $dbh->query("update book_reserved set accession_id = null, status = 'cancelled' where reservation_id = ".$record_with_available_and_expire['reservation_id']);

        //and send notification for it
        $dbh->query("insert into notification (member_id, message, date_notif, href) values('".$record_with_available_and_expire['member_id']."', 'I am sorry. ".$book['title']." you have reserved already expired.', now(), 'member_book_details.php?isbn=".$record_with_available_and_expire['ISBN']."' )");

        //fetching reservation record of the person who is the first in Line
        $sql = "select * from book_reserved r, member_basic m where m.member_id = r.member_id and ISBN = :ISBN and status = 'waiting' ORDER BY case member_type when 'Faculty' then 1 when 'Staff' then 1 else 2 end, date_reserved limit 1";
        $stmt = $dbh->prepare($sql);
        $stmt->execute(array(":ISBN" => $record_with_available_and_expire['ISBN']));
        $reservation_record_who_firstinLine = $stmt->fetch();

        $inLine_rowCount = $dbh->query("select count(*) from (select r.* from book_reserved r, member_basic m where m.member_id = r.member_id and ISBN = '".$record_with_available_and_expire['ISBN']."' and status = 'waiting' ORDER BY case member_type when 'Faculty' then 1 when 'Staff' then 1 else 2 end, date_reserved) as count")->fetchColumn();



        //if there are people in Line
        if($inLine_rowCount >= 1){

            //assigning accession_id, new status, date available to the person who's first in Line
            $dbh->query("update book_reserved set accession_id = ".$record_with_available_and_expire['accession_id'].", status = 'available', date_available = now(), date_expire = date_add(now(), interval ".getExpire()." day) where reservation_id = ".$reservation_record_who_firstinLine['reservation_id']);

            $dbh->query("insert into notification (member_id, message, date_notif, href) values('".$reservation_record_who_firstinLine['member_id']."', '".$book['title']." is now available!', now(), 'member_book_details.php?isbn=".$record_with_available_and_expire['ISBN']."' )");

        } else /*if there's no people in line, just change the availability status */ {

            $dbh->query("update book_each set availability = 'available' where accession_id = ".$reservation_record_who_cancelled['accession_id']);

        }
      }
    }
}

function handleOverdue ($dbh) {

  //the condition "where overdue_sent = 0" is for only sending notification once,
  $overdue_members = array();
  $sql = "select book_borrowed.*, datediff(date_due, now()) as due, title from book_borrowed left join book_basic using (ISBN) where overdue_sent is null having due <= 0";
  foreach($dbh->query($sql) as $row){
    array_push($overdue_members,$row);
  }

  //updating status to 'overdue' for overdue records, happens only once
  $dbh->query("update book_borrowed set status = 'overdue', overdue_sent = 1 where datediff(date_due, now()) = 0 and status = 'borrowed'");

  $dbh->query("update book_borrowed set status = 'borrowed', overdue_sent = null where datediff(date_due, now()) > 0 and status = 'overdue'");


  foreach($overdue_members as $i){
  $dbh->query("insert into notification (member_id, message, date_notif, href) values('".$i['member_id']."', 'I am sorry. ".$i['title']." is overdue today. Please return it to the library immediately.', now(), 'member_book_details.php?isbn=".$i['ISBN']."' )");
  }


}

function destroySearchSession () {
  unset($_SESSION['sel']);
  unset($_SESSION['primary_search']);
  unset($_SESSION['page']);
  unset($_SESSION['totalPages']);
  unset($_SESSION['class_id']);
  unset($_SESSION['category_name']);
  unset($_SESSION['subject_name']);
}

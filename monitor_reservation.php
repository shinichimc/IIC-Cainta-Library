<?php

  /////////////////////////////////UNIVERSAL
  require_once('php/config.php');
  require_once('php/functions.php');

  session_start();

  if(empty($_SESSION['me'])){
    header("LOCATION: index.php");
    exit;
  }

  $me = $_SESSION['me'];

  if($me['member_type'] != "Admin"){
    header('LOCATION: index.php');
    exit;
  }

   $dbh = connectDB();

////////////////////////////////////WHEN YOU CLICK THE CLEAR SEARCH BUTTON, EMPTY THE SESSION VALUES
  if(isset($_POST['clear_search'])){ 
    unset($_SESSION['primary_search']);
    unset($_SESSION['sel']);
  }

  ///////////////////////////////////HANDLE EXPIRE
  handleExpire($dbh);

  //////////////////////////////////HANDLE DUEDATE
  handleOverdue($dbh);

 ////////////////////////////////////RECORDS FROM MEMBER_BASIC TABLE
  $results = array();
  $sql = "select m.member_id, concat(member_firstname, ' ',member_lastname) as complete_name,member_type, reservation_id, b.ISBN, title, accession_id, date_format(date_reserved, '%b %d / %h:%i %p') as d_reserve, date_format(date_available, '%b %d / %h:%i %p') as d_available, date_format(date_expire, '%b %d (%a)') as d_expire, status from member_basic m, book_reserved r, book_basic b where m.member_id = r.member_id and r.ISBN = b.ISBN and (status = 'waiting' or status = 'available')";
  foreach($dbh->query($sql) as $row){
    array_push($results, $row);
  }

////////////////////////////////////GETTING TOTAL NUMBER OF RESULTS
  $_SESSION['total'] = $dbh->query("select count(*) from book_reserved where status = 'waiting' or status = 'available'")->fetchColumn();
  

////////////////////////////////////WHEN YOU CLICK THE "GO!"" BUTTON
  if(isset($_POST['searchsubmit'])){

    $_SESSION['sel'] = $_POST['searchby'];
    $_SESSION['primary_search'] = $_POST['primary_search'];


    $results = array();

    if($_SESSION['sel'] == 'title'){
      $sql = "select m.member_id, concat(member_firstname, ' ',member_lastname) as complete_name,member_type, reservation_id, b.ISBN, title, accession_id, date_format(date_reserved, '%b %d / %h:%i %p') as d_reserve, date_format(date_available, '%b %d / %h:%i %p') as d_available, date_format(date_expire, '%b %d (%a)') as d_expire, status from member_basic m, book_reserved r, book_basic b where m.member_id = r.member_id and r.ISBN = b.ISBN and title like '%".$_SESSION['primary_search']."%' and (status = 'waiting' or status = 'available')";
      $_SESSION['total'] = $dbh->query("select count(*) from member_basic m, book_reserved r, book_basic b where m.member_id = r.member_id and r.ISBN = b.ISBN and title like '%".$_SESSION['primary_search']."%' and (status = 'waiting' or status = 'available')")->fetchColumn();
    } 
    elseif($_SESSION['sel'] == 'membername') {
       $sql = "select m.member_id, concat(member_firstname, ' ',member_lastname) as complete_name,member_type, reservation_id, b.ISBN, title, accession_id, date_format(date_reserved, '%b %d / %h:%i %p') as d_reserve, date_format(date_available, '%b %d / %h:%i %p') as d_available, date_format(date_expire, '%b %d (%a)') as d_expire, status from member_basic m, book_reserved r, book_basic b where m.member_id = r.member_id and r.ISBN = b.ISBN and (status = 'waiting' or status = 'available') having complete_name like '%".$_SESSION['primary_search']."%'";
      $_SESSION['total'] = $dbh->query("select count(*) from(select concat(member_firstname,' ',member_lastname) as complete_name from member_basic m, book_reserved r, book_basic b where m.member_id = r.member_id and r.ISBN = b.ISBN and (status = 'waiting' or status = 'available') having complete_name like '%".$_SESSION['primary_search']."%') as count")->fetchColumn();
    } 
    elseif($_SESSION['sel'] == 'accession') {
      $sql = "select m.member_id, concat(member_firstname, ' ',member_lastname) as complete_name,member_type, reservation_id, b.ISBN, title, accession_id, date_format(date_reserved, '%b %d / %h:%i %p') as d_reserve, date_format(date_available, '%b %d / %h:%i %p') as d_available, date_format(date_expire, '%b %d (%a)') as d_expire, status from member_basic m, book_reserved r, book_basic b where m.member_id = r.member_id and r.ISBN = b.ISBN and accession_id like '%".$_SESSION['primary_search']."%' and (status = 'waiting' or status = 'available')";
      $_SESSION['total'] = $dbh->query("select count(*) from member_basic m, book_reserved r, book_basic b where m.member_id = r.member_id and r.ISBN = b.ISBN and accession_id like '%".$_SESSION['primary_search']."%' and (status = 'waiting' or status = 'available')")->fetchColumn();
    } 
    else {
      $sql = "select m.member_id, concat(member_firstname, ' ',member_lastname) as complete_name,member_type, reservation_id, b.ISBN, title, accession_id, date_format(date_reserved, '%b %d / %h:%i %p') as d_reserve, date_format(date_available, '%b %d / %h:%i %p') as d_available, date_format(date_expire, '%b %d (%a)') as d_expire, status from member_basic m, book_reserved r, book_basic b where m.member_id = r.member_id and r.ISBN = b.ISBN and b.ISBN like '%".$_SESSION['primary_search']."%' and (status = 'waiting' or status = 'available')";
      $_SESSION['total'] = $dbh->query("select count(*) from member_basic m, book_reserved r, book_basic b where m.member_id = r.member_id and r.ISBN = b.ISBN and b.ISBN like '%".$_SESSION['primary_search']."%' and (status = 'waiting' or status = 'available')")->fetchColumn();
    } 

    foreach($dbh->query($sql) as $row){
    array_push($results, $row);
    }

  }
 
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Monitor Reservation</title>
<link rel = "short icon" href="images/favicon7.ico"/>
<link href="styles/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="styles/reset.css"/>
<link rel="stylesheet" href="styles/fonts.css"/>
<link rel="stylesheet" href="styles/fontello.css"/>
<link rel="stylesheet" href="styles/backtotop.css"/>
<link rel="stylesheet" href="styles/global.css"/>
<link rel="stylesheet" href="styles/manage_memberbook.css"/>

<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/backtotop.js"></script>
<script src="js/bootstrap.min.js"></script>
</head>
<body>
  
  <header>
    <a href="home_admin.php" id="logo"><img src="images/height80.png"/></a>
    <p class="hi_admin">Admin Page</p>
    <nav id="mainnav">  
      <div class="nav-container">
        <div class="nav-left"><p class="icon-user-big"></p></div>
        <div class="nav-right two-line"><li><a href="manage_member.php">Manage Member</a></li></div>
      </div> 
      <div class="nav-container">
        <div class="nav-left"><p class="icon-book-big"></p></div>
        <div class="nav-right two-line"><li><a href="manage_book.php">Manage Book</a></li></div>
      </div>
      <div class="nav-container active">
        <div class="nav-left"><p class="icon-edit-big"></p></div>
        <div class="nav-right two-line"><li><a href="monitor_reservation.php">Monitor Reservation</a></li></div>
      </div>
      <div class="nav-container">
        <div class="nav-left"><p class="icon-book-open-big"></p></div>
        <div class="nav-right two-line"><li><a href="monitor_borrowed.php">Monitor Borrowed</a></li></div>
      </div>
      <div class="nav-container">
        <div class="nav-left"><p class="icon-newspaper-big"></p></div>
        <div class="nav-right" style="padding-top:15px;"><li><a href="reports.php">Reports</a></li></div>
      </div>
      <div class="nav-container">
        <div class="nav-left"><p class="icon-logout-big"></p></div>
        <div class="nav-right" style="padding-top:15px;"><li><a href="logout.php">Log out</a></li></div>        
      </div>         
    </nav>
  </header>
  <div id="wrapper">
    <form action="monitor_reservation.php" method="post" id="clear"></form>
    <p class="general icon-edit-big">  Currently Reserved Books <input form = "clear" type="submit" name="clear_search" class="btn btn-default btn-sm clear-search" value="Clear Search"></p>
    <hr>

    <div id="top-container" class="margin20">
      <form id = "form_monitor_reservation" class="form-inline" action="monitor_reservation.php" method="POST">
        <select class="form-control select-admin" name="searchby">
          <option value="membername" <?php if($_SESSION['sel']=='membername') echo 'selected';?>>Member Name</option>
          <option value="title" <?php if($_SESSION['sel']=='title') echo 'selected';?>>Title</option>
          <option value="accession" <?php if($_SESSION['sel']=='accession') echo 'selected';?>>Accession No.</option>
          <option value="isbn" <?php if($_SESSION['sel']=='isbn') echo 'selected';?>>ISBN</option>
        </select>
        <div class="input-group search-box-admin">
          <input class="form-control" type="text" name="primary_search" placeholder=" Search Reservation" value="<?php echo $_SESSION['primary_search'];?>" required>
          <span class="input-group-btn">
            <input class="btn btn-default" name="searchsubmit" type="submit" value="Go!">
          </span>
        </div>
        </form>
      <div class="btn-group manipulate2">
        <input form="form_monitor_reservation" type="button" name="btn_all" class="btn-all btn btn-manipulate2 btn-default btn-xs" value="All" disabled>
        <input form="form_monitor_reservation" type="button" name="btn_student" class="btn-student btn btn-manipulate2 btn-default btn-xs" value="Student">
        <input form="form_monitor_reservation" type="button" name="btn_faculty" class="btn-faculty btn btn-manipulate2 btn-default btn-xs" value="Faculty">
        <input form="form_monitor_reservation" type="button" name="btn_staff" class="btn-staff btn btn-manipulate2 btn-default btn-xs" value="Staff">
      </div>
      
    </div>

    <?php if(count($results) >= 1) : ?>
    <div class="result-info-container-normal">
      <p class = "result-info-text"><?php echo $_SESSION['total']." result(s) found!";?></p>
      
    </div>

    <table class="table table-bordered table-condensed">
      <thead>
        <tr>
          <th>Name</th>
          <th>Title</th>
          <th>ISBN</th>
          <th>Accession No.</th>
          <th>Date Reserved</th>
          <th>Date Available</th>
          <th>Date Expire</th>
          <th>Status</th>
          <th style="text-align:center;">Add</th>
        </tr>
      </thead>
      <tbody>
        <?php $strip = 0; 
              $red_plus = "strip-background";
         ?>
        <?php foreach($results as $result) : ?>
          <tr id = "row_<?php echo $result['member_id'];?>" class="sort <?php echo $result['member_type'];?>  <?php if($result['status'] == 'available') echo "downy";?>" data-id="<?php echo $result['member_id'];?>">
            <td><a class="ISBN-link" href="admin_book_details.php?ISBN=<?php echo $result['ISBN'];?>"><?php echo $result['complete_name'];?></a></td>
            <td><?php echo $result['title'];?></td>
            <td><?php echo $result['ISBN'];?></td>
            <td><?php if($result['status'] != 'available') echo '---'; else echo $result['accession_id']; ?></td>
            <td><?php echo $result['d_reserve']; ?></td>
            <td><?php if($result['status'] != 'available') echo '---'; else echo $result['d_available']; ?></td>
            <td><?php if($result['status'] != 'available'|| $result['d_expire'] == '') echo '---'; else echo $result['d_expire']; ?></td>
            <td><?php echo $result['status']; ?></td>
             <?php if($result['status'] == 'available') :?>
            <td style="text-align:center;"><a id="btn-transfer" class="btn btn-default btn-xs icon-plus" href="borrow.php?accession=<?php echo $result['accession_id'];?>&member_id=<?php echo $result['member_id'];?>&res_id=<?php echo $result['reservation_id'];?>&isbn=<?php echo $result['ISBN'];?>"></a></td>
            <?php else : ?> <td style="text-align:center;"><a class="btn btn-default btn-xs icon-plus" disabled></a></td> <?php endif ; ?>
          </tr>
        <?php $strip++ ; ?>
        <?php endforeach ; ?>
      </tbody>
    </table>
    <?php else : ?>
      <table class="table table-bordered table-condensed">
      <thead>
        <tr>
          <th>Name</th>
          <th>Title</th>
          <th>ISBN</th>
          <th>Accession No.</th>
          <th>Date Reserved</th>
          <th>Date Available</th>
          <th>Date Expire</th>
          <th>Status</th>
          <th style="text-align:center;">Add</th>
        </tr>
      </thead>
      <tbody>
          <tr>
            <td colspan="9" class="middle no-report-yet" style="font-size:650%; padding:50px;"><span class="icon-attention-alt"></span>No Records Yet.</td>
          </tr>
        </tbody>
      </table>
    </table>
    <?php endif ; ?>
  </div><!-- # wrapper -->

  
   <footer><p>Informatics International College - Cainta Library<br><br>&copy; 2014 all rights reserved</p></footer>

  

  <a href="#0" class="cd-top">Top</a> 
  <script>
   $(document).ready(function(){
    $(".nav-container").click(function(){
             window.location = $(this).find("a").attr("href");
             return false;
    });

     $('#btn-transfer').click(function(){
      if(confirm("are you sure you want to add this member to the borrowers list?")){

      } else return false;

    });

    $("tr").hover(function(){
      $(this).find(".each-delete").css("opacity","1");
      $(this).find(".each-edit").css("opacity","1");
      $(this).find(".each-enable").css("opacity","1");
    }, function(){
       $(this).find(".each-delete").css("opacity","0");
       $(this).find(".each-edit").css("opacity","0");
       $(this).find(".each-enable").css("opacity","0");
    });

    $(document).on('click','.btn-all', function(){
      $('.sort').show(600);
      $(this).prop('disabled',true);
      $('div.manipulate2 input').not(this).prop('disabled',false);
    });
    $(document).on('click','.btn-student', function(){
      $('.sort').not('.Student').hide(600);
      $('.Student').show(600);
      $(this).prop('disabled',true);
      $('div.manipulate2 input').not(this).prop('disabled',false);
    });
    $(document).on('click','.btn-faculty', function(){
      $('.sort').not('.Faculty').hide(600);
      $('.Faculty').show(600);
      $(this).prop('disabled',true);
      $('div.manipulate2 input').not(this).prop('disabled',false);
    });
    $(document).on('click','.btn-staff', function(){
      $('.sort').not('.Staff').hide(600);
      $('.Staff').show(600);
      $(this).prop('disabled',true);
      $('div.manipulate2 input').not(this).prop('disabled',false);
    });
    
  });
  </script>
  </body>
</html>
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
  $sql = "select borrowed_id, m.member_id, concat(member_firstname, ' ',member_lastname) as complete_name,member_type, b.ISBN, accession_id, title, date_format(date_borrowed, '%b %d / %h:%i %p') as d_borrow,date_format(date_due, '%b %d (%a)') as d_due, status, datediff(date_due,now()) as diff from member_basic m, book_borrowed b, book_basic ba where m.member_id = b.member_id and ba.ISBN = b.ISBN and not status = 'returned'";
  foreach($dbh->query($sql) as $row){
    array_push($results, $row);
  }

////////////////////////////////////GETTING TOTAL NUMBER OF RESULTS
  $_SESSION['total'] = $dbh->query("select count(*) from book_borrowed where not status = 'returned'")->fetchColumn();
  

////////////////////////////////////WHEN YOU CLICK THE "GO!"" BUTTON
  if(isset($_POST['searchsubmit'])){

    $_SESSION['sel'] = $_POST['searchby'];
    $_SESSION['primary_search'] = $_POST['primary_search'];


    $results = array();

    if($_SESSION['sel'] == 'title'){
      $sql = "select borrowed_id, m.member_id, concat(member_firstname, ' ',member_lastname) as complete_name,member_type, b.ISBN, accession_id, title, date_format(date_borrowed, '%b %d / %h:%i %p') as d_borrow,date_format(date_due, '%b %d / %h:%i %p') as d_due, status,datediff(date_due,now()) as diff from member_basic m, book_borrowed b, book_basic ba where m.member_id = b.member_id and ba.ISBN = b.ISBN and not status = 'returned' and title like '%".$_SESSION['primary_search']."%'";

      $_SESSION['total'] = $dbh->query("select count(*) from(select borrowed_id, m.member_id, concat(member_firstname, ' ',member_lastname) as complete_name,member_type, b.ISBN, accession_id, title, date_format(date_borrowed, '%b %d / %h:%i %p') as d_borrow,date_format(date_due, '%b %d / %h:%i %p') as d_due, status from member_basic m, book_borrowed b, book_basic ba where m.member_id = b.member_id and ba.ISBN = b.ISBN and not status = 'returned' and title like '%".$_SESSION['primary_search']."%') as count")->fetchColumn();
    } 
    elseif($_SESSION['sel'] == 'membername') {
       $sql = "select borrowed_id, m.member_id, concat(member_firstname, ' ',member_lastname) as complete_name,member_type, b.ISBN, accession_id, title, date_format(date_borrowed, '%b %d / %h:%i %p') as d_borrow,date_format(date_due, '%b %d / %h:%i %p') as d_due, status,datediff(date_due,now()) as diff from member_basic m, book_borrowed b, book_basic ba where m.member_id = b.member_id and ba.ISBN = b.ISBN and not status = 'returned' having complete_name like '%".$_SESSION['primary_search']."%'";

      $_SESSION['total'] = $dbh->query("select count(*) from(select borrowed_id, m.member_id, concat(member_firstname, ' ',member_lastname) as complete_name,member_type, b.ISBN, accession_id, title, date_format(date_borrowed, '%b %d / %h:%i %p') as d_borrow,date_format(date_due, '%b %d / %h:%i %p') as d_due, status from member_basic m, book_borrowed b, book_basic ba where m.member_id = b.member_id and ba.ISBN = b.ISBN and not status = 'returned' having complete_name like '%".$_SESSION['primary_search']."%') as count")->fetchColumn();
    } 
    elseif($_SESSION['sel'] == 'accession') {
      $sql = "select borrowed_id, m.member_id, concat(member_firstname, ' ',member_lastname) as complete_name,member_type, b.ISBN, accession_id, title, date_format(date_borrowed, '%b %d / %h:%i %p') as d_borrow,date_format(date_due, '%b %d / %h:%i %p') as d_due, status, datediff(date_due,now()) as diff from member_basic m, book_borrowed b, book_basic ba where m.member_id = b.member_id and ba.ISBN = b.ISBN and not status = 'returned' and accession_id like '%".$_SESSION['primary_search']."%'";

      $_SESSION['total'] = $dbh->query("select count(*) from book_borrowed where not status = 'returned' and accession_id like '%".$_SESSION['primary_search']."%'")->fetchColumn();
    } 
    else {
       $sql = "select borrowed_id, m.member_id, concat(member_firstname, ' ',member_lastname) as complete_name,member_type, b.ISBN, accession_id, title, date_format(date_borrowed, '%b %d / %h:%i %p') as d_borrow,date_format(date_due, '%b %d / %h:%i %p') as d_due, status, datediff(date_due,now()) as diff from member_basic m, book_borrowed b, book_basic ba where m.member_id = b.member_id and ba.ISBN = b.ISBN and not status = 'returned' and b.ISBN like '%".$_SESSION['primary_search']."%'";

      $_SESSION['total'] = $dbh->query("select count(*) from book_borrowed where not status = 'returned' and ISBN like '%".$_SESSION['primary_search']."%'")->fetchColumn();
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
<title>Monitor Borrowed Books</title>
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
      <div class="nav-container">
        <div class="nav-left"><p class="icon-edit-big"></p></div>
        <div class="nav-right two-line"><li><a href="monitor_reservation.php">Monitor Reservation</a></li></div>
      </div>
      <div class="nav-container active">
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
    <form action="monitor_borrowed.php" method="post" id="clear"></form>
    <p class="general icon-book-open-big">  Currently Borrowed Books <input form = "clear" type="submit" name="clear_search" class="btn btn-default btn-sm clear-search" value="Clear Search"></p>
    <hr>

    <div id="top-container" class="margin20">
      <form id = "form_monitor_borrowed" class="form-inline" action="monitor_borrowed.php" method="POST">
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
        <input form="form_monitor_borrowed" type="button" name="btn_all" class="btn-all btn btn-manipulate2 btn-default btn-xs" value="All" disabled>
        <input form="form_monitor_borrowed" type="button" name="btn_overdue" class="btn-overdue btn btn-manipulate2 btn-default btn-xs" value="Overdue">
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
          <th>Date Borrowed</th>
          <th>Date Due</th>
          <th>Status</th>
          <th style="text-align:center;">return</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php $strip = 0; 
              $red_plus = "strip-background";
         ?>
        <?php foreach($results as $result) : ?>
          <tr id = "row_<?php echo $result['member_id'];?>" class="sort <?php echo $result['status'];?> <?php if($result['diff'] <= 0) echo "red";?> <?php if($strip % 2 == 0) echo $red_plus;?>" data-id="<?php echo $result['member_id'];?>">
            <td><a class="ISBN-link" <?php if($result['diff'] <= 0) echo "style='color:#fff;'";?> href="admin_book_details.php?ISBN=<?php echo $result['ISBN'];?>"><?php echo $result['complete_name'];?></a></td>
            <td><?php echo $result['title'];?></td>
            <td><?php echo $result['ISBN']; ?></td>
            <td><?php echo $result['accession_id']; ?></td>
            <td><?php echo $result['d_borrow']; ?></td>
            <td><?php if(empty($result['d_due'])) echo '---'; else echo $result['d_due']; ?></td>
            <td><?php echo $result['status']; ?></td>
            <td style="text-align:center;"><a id="btn-return" class="btn btn-default btn-xs icon-ok" href="return.php?id=<?php echo $result['borrowed_id']?>&ISBN=<?php echo $result['ISBN']; ?>"></a></td>
            <td style="text-align:center;">
            <?php if($result['member_type'] == 'Staff' || $result['member_type'] == 'Faculty') :?>
             <span>---</span>
            <?php else : ?>
              <?php 
              $count_pending = $dbh->query("select count(*) from book_reserved where ISBN = '".$result['ISBN']."' and status = 'waiting'")->fetchColumn();
              $which_icon = $count_pending >= 1 ? "attention-circled" : "circle";

              if($result['diff'] >= 3) $color = "three";
              elseif($result['diff'] == 2) $color = "two";
              elseif($result['diff'] == 1) $color = "one";
              else $color = "black";
              ?>
              <span class="signal icon-<?php echo $which_icon;?> <?php echo $color;?>">
                <div id="legend-container">
                  <table id="legend">
                    <tr style="border-bottom:1px #ccc solid;">
                      <td class="first-column"></td>
                      <td>date due in</td>
                      <td>with pending request</td>
                    </tr>
                    <tr>
                      <td class="first-column">3 days</td>
                      <td><span class="size three icon-circle"></span></td>
                      <td><span class="size three icon-attention-circled"></span></td>
                    </tr>
                    <tr>
                      <td class="first-column">2 days</td>
                      <td><span class="size two icon-circle"></span></td>
                      <td><span class="size two icon-attention-circled"></td>
                    </tr>
                    <tr>
                      <td class="first-column">1 day</td>
                      <td><span class="size one icon-circle"></span></td>
                      <td><span class="size one icon-attention-circled"></td>
                    </tr>
                    <tr>
                      <td class="first-column">overdue</td>
                      <td><span class="size black icon-circle"></span></td>
                      <td><span class="size black icon-attention-circled"></td>
                    </tr>
                  </table>
               </div>
              </span>
            <?php endif ; ?>
            </td>
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
          <th>Date Borrowed</th>
          <th>Date Due</th>
          <th>Status</th>
          <th style="text-align:center;">return</th>
          <th></th>
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

     $('#btn-return').click(function(){
      if(confirm("are you sure?")){

      } else return false;
    });

    $(document).on('click','.btn-all', function(){
      $('.sort').show(600);
      $(this).prop('disabled',true);
      $('div.manipulate2 input').not(this).prop('disabled',false);
    });
    $(document).on('click','.btn-overdue', function(){
      $('.sort').not('.overdue').hide(600);
      $('.overdue').show(600);
      $(this).prop('disabled',true);
      $('div.manipulate2 input').not(this).prop('disabled',false);
    });
    
  });
  </script>
  </body>
</html>
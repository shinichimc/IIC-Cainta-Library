<?php

  /////////////////////////////////UNIVERSAL
  require_once('php/config.php');
  require_once('php/functions.php');
  

  session_start();
  $dbh = connectDB();


  $me = $_SESSION['me'];

  if($me['member_type'] != "Admin"){
    header('LOCATION: '.SITE_URL.'index.php');
    exit;
  }

////////////////////////////////////WHEN YOU CLICK THE CLEAR SEARCH BUTTON, EMPTY THE SESSION VALUES
  if(isset($_POST['clear_search'])){ 
    unset($_SESSION['sel']);
    unset($_SESSION['period']);
    unset($_SESSION['status']);
  }

  ///////////////////////////////////HANDLE EXPIRE
  handleExpire($dbh);

  //////////////////////////////////HANDLE DUEDATE
  handleOverdue($dbh);

  /////////////////////////////////NOTIFICATION
  $notifications = getNotification($me['member_id']);

////////////////////////////////////WHEN YOU CLICK THE "OK!"" BUTTON
  if(isset($_POST['report_submit'])){

    $dbh->query("insert into audit_log (date, event, description,member_id,result) values (DATE_ADD(NOW(), INTERVAL 15 HOUR),'Report', 'Admin generated a report','".ADMIN_USERNAME."' , 'Success')");
    
    $_SESSION['sel'] = $_POST['searchby'];
    $_SESSION['period'] = $_POST['period'];

    $results = array();

     if(isset($_POST['status'])){

        $_SESSION['status'] = $_POST['status'];

        $status_condition = " and (";
        foreach($_POST['status'] as $status){
          switch ($status) {
              case "all":
                $status_condition .= " status is not null";
                break;
              case "available":
                $status_condition .= " status = 'available'";
                break;
              case "waiting":
                $status_condition .= " status = 'waiting'";
                break;
              case "picked":
                $status_condition .= " status = 'picked'";
                break;
              case "cancelled":
                $status_condition .= " status = 'cancelled'";
                break;
              default:
          }
          $status_condition .= " OR";
        }
        $status_condition = mb_substr($status_condition, 0, -3);
        $status_condition .= ")";
     } 

    if($_SESSION['sel'] == 'current_reserve'){
      $sql = "select m.member_id, concat(member_firstname, ' ',member_lastname) as complete_name,member_type, reservation_id, b.ISBN, title, accession_id, date_format(date_reserved, '%b %d / %h:%i %p') as d_reserve, date_format(date_available, '%b %d / %h:%i %p') as d_available, date_format(date_expire, '%b %d (%a)') as d_expire, status from member_basic m, book_reserved r, book_basic b where m.member_id = r.member_id and r.ISBN = b.ISBN and (status = 'waiting' or status = 'available')";
      $total = $dbh->query("select count(*) from book_reserved where status ='waiting' or status = 'available'")->fetchColumn();
      $message = "Currently Reserved Books as of ".date('Y/m/d');
    }
    

    //  IF DATE PICKER IS SET
    elseif($_POST['date_from'] != '' && $_POST['date_to'] != ''){ 
      
      if($_SESSION['sel'] == 'reserved_books') {

       
         $sql = "select reservation_id, m.member_id, concat(member_firstname, ' ',member_lastname) as complete_name, b.ISBN, accession_id, title, date_format(date_reserved, '%b %d / %h:%i %p') as d_reserved, status from member_basic m, book_reserved b, book_basic ba where m.member_id = b.member_id and ba.ISBN = b.ISBN and date_reserved between '".$_POST['date_from']."' and '".$_POST['date_to']."' ".$status_condition." order by date_reserved desc";

         $total = $dbh->query("select count(*) from(select reservation_id, m.member_id, concat(member_firstname, ' ',member_lastname) as complete_name, b.ISBN, accession_id, title, date_format(date_reserved, '%b %d / %h:%i %p') as d_reserved, status from member_basic m, book_reserved b, book_basic ba where m.member_id = b.member_id and ba.ISBN = b.ISBN and date_reserved between '".$_POST['date_from']."' and '".$_POST['date_to']."' ".$status_condition.") as count")->fetchColumn();
          
          $message = "Reserved Books from ".$_POST['date_from']." to ".$_POST['date_to'];

      } elseif($_SESSION['sel'] == 'most_reserved') {

        $sql = "select count(ISBN) amount_reserved, ISBN, title FROM book_reserved left join book_basic using (ISBN) where date_reserved between '".$_POST['date_from']."' and '".$_POST['date_to']."' ".$status_condition." group by ISBN order by count(ISBN) desc";
        $total = $dbh->query("select count(*) from(select count(ISBN), ISBN, title FROM book_reserved left join book_basic using (ISBN) where date_reserved between '".$_POST['date_from']."' and '".$_POST['date_to']."' ".$status_condition." group by ISBN) as count")->fetchColumn();
        $message = "Most Reserved Books from ".$_POST['date_from']." to ".$_POST['date_to'];
      } elseif($_SESSION['sel'] == 'most_members'){
        $sql = "select count(member_id) as highest, concat(member_firstname,' ',member_lastname) as complete_name ,ISBN, title FROM `book_reserved` left join member_basic using (member_id) left join book_basic using(ISBN) where date_reserved between '".$_POST['date_from']."' and '".$_POST['date_to']."' group by member_id order by highest desc";
        $total = $dbh->query("select count(*) from(select count(member_id) as highest, concat(member_firstname,' ',member_lastname) as complete_name ,ISBN, title FROM `book_reserved` left join member_basic using (member_id) left join book_basic using(ISBN) where date_reserved between '".$_POST['date_from']."' and '".$_POST['date_to']."' group by member_id) as count")->fetchColumn();
        $message = "Members with the heighest number of book reservations from ".$_POST['date_from']." to ".$_POST['date_to'];
      }

    // IF PERIOD PICKER IS USED
    } else {

      switch ($_POST['period']) {
        case "all":
          $condition = "is not null";
          $message = "for all dates";
          break;
        case "this_month":
          $condition = "between date_format(DATE_ADD(NOW(), INTERVAL 15 HOUR) ,'%Y-%m-01') and DATE_ADD(NOW(), INTERVAL 15 HOUR)";
          $message = "for this month";
          break;
        case "three_months":
          $condition = "between date_add(date_format(DATE_ADD(NOW(), INTERVAL 15 HOUR) ,'%Y-%m-01'), interval -2 month) and DATE_ADD(NOW(), INTERVAL 15 HOUR)";
          $message = "for the past 3 months";
          break;
        case "six_months":
          $condition = "between date_add(date_format(DATE_ADD(NOW(), INTERVAL 15 HOUR) ,'%Y-%m-01'), interval -5 month) and DATE_ADD(NOW(), INTERVAL 15 HOUR)";
          $message = "for the past 6 months";
          break;
        case "this_year":
          $condition = "between date_format(DATE_ADD(NOW(), INTERVAL 15 HOUR) ,'%Y-01-01') and DATE_ADD(NOW(), INTERVAL 15 HOUR)";
          $message = "for this year";
          break;
        default:
         
      }

      if($_SESSION['sel'] == 'reserved_books') {
       
         $sql = "select reservation_id, m.member_id, concat(member_firstname, ' ',member_lastname) as complete_name, b.ISBN, accession_id, title, date_format(date_reserved, '%b %d / %h:%i %p') as d_reserved, status from member_basic m, book_reserved b, book_basic ba where m.member_id = b.member_id and ba.ISBN = b.ISBN and date_reserved ".$condition." ".$status_condition." order by date_reserved desc";

         $total = $dbh->query("select count(*) from(select reservation_id, m.member_id, concat(member_firstname, ' ',member_lastname) as complete_name, b.ISBN, accession_id, title, date_format(date_reserved, '%b %d / %h:%i %p') as d_reserved, status from member_basic m, book_reserved b, book_basic ba where m.member_id = b.member_id and ba.ISBN = b.ISBN and date_reserved ".$condition." ".$status_condition.") as count")->fetchColumn();
          
          $message = "'Reserved books'"." ".$message;

      }  elseif($_SESSION['sel'] == 'most_reserved') {

        $sql = "select count(ISBN) amount_reserved, ISBN, title FROM book_reserved left join book_basic using (ISBN) where date_reserved ".$condition." ".$status_condition." group by ISBN order by count(ISBN) desc";
        $total = $dbh->query("select count(*) from(select count(ISBN), ISBN, title FROM book_reserved left join book_basic using (ISBN) where date_reserved ".$condition." ".$status_condition." group by ISBN) as count")->fetchColumn();
        $message = "'Most Reserved books'"." ".$message;

      } elseif($_SESSION['sel'] == 'most_members'){
        $sql = "select count(member_id) as highest, concat(member_firstname,' ',member_lastname) as complete_name ,ISBN, title FROM `book_reserved` left join member_basic using (member_id) left join book_basic using(ISBN) where date_reserved ".$condition." group by member_id order by highest desc";

        $total = $dbh->query("select count(*) from(select count(member_id) as highest, concat(member_firstname,' ',member_lastname) as complete_name ,ISBN, title FROM `book_reserved` left join member_basic using (member_id) left join book_basic using(ISBN) where date_reserved ".$condition." group by member_id) as count")->fetchColumn();

        $message = "Members with the heighest number of book reservations "." ".$message;
      }

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
<title>Reports</title>
<link rel = "short icon" href="images/favicon7.ico"/>
<link href="styles/bootstrap.min.css" rel="stylesheet">
<link href="styles/datepicker3.css" rel="stylesheet">
<link rel="stylesheet" href="styles/reset.css"/>
<link rel="stylesheet" href="styles/fonts.css"/>
<link rel="stylesheet" href="styles/fontello.css"/>
<link rel="stylesheet" href="styles/backtotop.css"/>
<link rel="stylesheet" href="styles/global.css"/>
<link rel="stylesheet" href="styles/manage_memberbook.css"/>

<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/backtotop.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="js/tableExport.js"></script>
<script type="text/javascript" src="js/jquery.base64.js"></script>
</head>
<body>
  <header>
    <a href="<?php echo SITE_URL_ADMIN;?>home_admin.php?kesu" id="logo"><img src="../images/height80.png"/></a>
    <p class="hi_admin">Administrator Page</p>
    <nav id="mainnav">  
      <div class="nav-container">
        <div class="nav-left"><p class="icon-user-big"></p></div>
        <div class="nav-right two-line"><li><a href="<?php echo SITE_URL_ADMIN;?>manage_member.php?kesu">Manage Member</a></li></div>
      </div> 
      <div class="nav-container">
        <div class="nav-left"><p class="icon-book-big"></p></div>
        <div class="nav-right two-line"><li><a href="<?php echo SITE_URL_ADMIN;?>manage_book.php?kesu">Manage Book</a></li></div>
      </div>
      <div class="nav-container">
        <div class="nav-left"><p class="icon-edit-big"></p></div>
        <div class="nav-right two-line"><li><a href="<?php echo SITE_URL_ADMIN;?>monitor_reservation.php?kesu">Monitor Reservation</a></li></div>
      </div>
      <div class="nav-container ">
        <div class="nav-left"><p class="icon-book-open-big"></p></div>
        <div class="nav-right two-line"><li><a href="<?php echo SITE_URL_ADMIN;?>monitor_borrowed.php?kesu">Borrowed Books</a></li></div>
      </div>
      <div class="nav-container active">
        <div class="nav-left"><p class="icon-newspaper-big"></p></div>
        <div class="nav-right" style="padding-top:15px;"><li><a href="<?php echo SITE_URL_ADMIN;?>reports.php?kesu">Reports</a></li></div>
      </div>
      <div class="nav-container">
        <div class="nav-left"><p class="icon-logout-big"></p></div>
        <div class="nav-right" style="padding-top:15px;"><li><a href="<?php echo SITE_URL_ADMIN;?>logout.php">Log out</a></li></div>        
      </div>         
    </nav>
    <div class="notification"> <!-- as panel wrap -->
      <?php $notif_count = $dbh->query("select count(*) from notification where member_id = '".$me['member_id']."' and check_seen = 0")->fetchColumn(); ?>
      <?php $notif_check_exists = $dbh->query("select count(*) from notification where member_id = '".$me['member_id']."'")->fetchColumn(); ?>
      <?php if($notif_count >= 1) : ?>
      <span class="notif-badge"><?php echo $notif_count;?></span>
      <?php endif ; ?>
      <span class="icon-bell"></span>
      <div class="panel-btn"></div> 
      
        <div class="panel-notif">
          <div class="panel-label">notifications</div>
          <div class="panel-contents">
            <ul>
              <?php if($notif_check_exists >= 1) : ?>
              <?php foreach($notifications as $notification) :?>
              <li class="notif-list"><a href="<?php echo $notification['href'];?>"><?php echo $notification['message'];?></a><br><span class="notif-time"><?php echo $notification['time']; ?></span></li>
              <?php endforeach ; ?>
              <?php else : ?>
              <li style="text-align:center; padding:30px;">No Notification</li>
              <?php endif ; ?>
            </ul>
          </div>
       </div>
    </div>
  </header>
  <div id="wrapper">
    <form action="<?php echo SITE_URL_ADMIN;?>reports.php" method="post" id="clear"></form>
    <p class="general icon-newspaper-big">  Reports <input form = "clear" type="submit" name="clear_search" class="btn btn-default btn-sm clear-search" value="Clear Report"></p>
    <hr>

    <form action="" method="post" id = "form_report"></form>
    <div id="top-report" class="margin20">
      <table id="menu-table" class="table" style="margin-bottom:0;"> 
        <tr>
          <td>
             <label for="selectReport" class="col-sm-2 control-label">Select report :</label>
             <div class="col-sm-10">
            <select  form="form_report" class="form-control select-report" id="selectReport" name="searchby">
              <option value="reserved_books" <?php if($_SESSION['sel']=='reserved_books') echo 'selected'; else echo '';?>>Reserved Books</option>
              <option value="current_reserve" <?php if($_SESSION['sel']=='current_reserve') echo 'selected'; else echo '';?>>Currently Reserved Books</option>
              <option value="most_reserved" <?php if($_SESSION['sel']=='most_reserved') echo 'selected'; else echo '';?>>Most Reserved Books</option>
              <option value="most_members" <?php if($_SESSION['sel']=='most_members') echo 'selected'; else echo '';?>>Members with the highest number of book reservations</option>
            </select>
          </div>
          </td>
        </tr>
        <tr id="row_datepicker">
          <td>
            <label for="selectReport" class="col-sm-2 control-label"> From :</label>
            <div class="col-sm-3">
              <div class="input-group date" >
                <input form="form_report" type="text" name = "date_from" class="form-control datepicker" datepickerdata-date-format="yyyy/mm/dd" placeholder="From"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
              </div>
            </div>
            <label for="selectReport" class="col-sm-2 control-label"> To :</label>
            <div class="col-sm-3">
              <div class="input-group date">
                <input form="form_report" type="text" name="date_to" class="form-control datepicker" datepickerdata-date-format="yyyy/mm/dd" placeholder="To"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
              </div>
           </div>
          </td>
        </tr>
        <tr id="row_periodpicker">
          <td>
            <label for="selectPeriod" class="col-sm-2 control-label"> Period :</label>
            <div class="col-sm-2">
              <div class="input-group">
                <input form="form_report" type="radio" name="period" class="period" value="all" <?php if($_SESSION['period']=='all') echo 'checked';?>> All
              </div>
           </div>
           <div class="col-sm-2">
              <div class="input-group">
                <input form="form_report" type="radio" name="period" class="period" value="this_month" <?php if($_SESSION['period']=='this_month') echo 'checked';?>> This month
              </div>
           </div>
            <div class="col-sm-2">
              <div class="input-group">
                <input form="form_report" type="radio" name="period" class="period" value="three_months" <?php if($_SESSION['period']=='three_months') echo 'checked';?>> Last 3 months
              </div>
           </div>
            <div class="col-sm-2">
              <div class="input-group">
                <input form="form_report" type="radio" name="period" class="period" value="six_months" <?php if($_SESSION['period']=='six_months') echo 'checked';?>> Last 6 months
              </div>
           </div>
            <div class="col-sm-2">
              <div class="input-group">
                <input form="form_report" type="radio" name="period" class="period" value="this_year" <?php if($_SESSION['period']=='this_year') echo 'checked';?>> This year
              </div>
           </div>
          </td>
        </tr>
        <!-- new row start -->
        <tr id = "row_status">
          <td>
            <label for="selectStatus" class="col-sm-2 control-label"> Status :</label>
            <div class="col-sm-2">
              <div class="input-group">
                <input form="form_report" type="checkbox" name="status[]" class="status" value="all" <?php if(in_array("all", $_SESSION['status']) || empty($_POST['status'])) echo 'checked';?>> All
              </div>
           </div>
           <div class="col-sm-2">
              <div class="input-group">
                <input form="form_report" type="checkbox" name="status[]" class="status" value="available" <?php if(in_array("available", $_SESSION['status'])) echo 'checked';?>> Available
              </div>
           </div>
            <div class="col-sm-2">
              <div class="input-group">
                <input form="form_report" type="checkbox" name="status[]" class="status" value="waiting" <?php if(in_array("waiting", $_SESSION['status'])) echo 'checked';?>> Waiting
              </div>
           </div>
            <div class="col-sm-2">
              <div class="input-group">
                <input form="form_report" type="checkbox" name="status[]" class="status" value="picked" <?php if(in_array("picked", $_SESSION['status'])) echo 'checked';?>> Picked
              </div>
           </div>
            <div class="col-sm-2">
              <div class="input-group">
                <input form="form_report" type="checkbox" name="status[]" class="status" value="cancelled" <?php if(in_array("cancelled", $_SESSION['status'])) echo 'checked';?>> Cancelled
              </div>
           </div>
          </td>
        </tr>
        <!-- new row end -->
        <tr>
          <td><input form="form_report" type="submit" name="report_submit" value="OK" class="btn btn-info" style="width:35%;"></td>
        </tr>
      </table>
    </div>
        
    <?php if(isset($_POST['report_submit'])) : ?>
    <div class="result-info-container-normal">
      <?php if(count($results) >= 1) : ?>
      <p class = "result-info-text"><?php echo $total." result(s) found";?></p>
      <?php else : ?>
      <p class = "result-info-text">No result found.</p>
      <?php endif ; ?>
    </div>   
      <?php if($_POST['searchby'] == 'reserved_books') : ?>
     
        <table class="table table-bordered table-condensed rpt">
          <thead>
            <tr><td colspan="8" class="middle info"><?php echo "Report for ".$message;?></td></tr>
            <tr>
              <th>Reservation ID</th>
              <th>Member Name</th>
              <th>Title</th>
              <th>Author(s)</th>
              <th>ISBN</th>
              <th>Accession No.</th>
              <th>Date Reserved</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php $strip = 0; 
                  $red_plus = "strip-background";
             ?>
            <?php foreach($results as $result) : ?>
              <tr>
                <td><?php echo $result['reservation_id'];?></td>
                <td><?php echo $result['complete_name'];?></td>
                <td><a class="ISBN-link" href="<?php echo SITE_URL_ADMIN;?>admin_book_details.php?ISBN=<?php echo $result['ISBN'];?>" data-toggle="tooltip" data-placement="top" title="Go to Book Details Page for '<?php echo $result['title']; ?>'"><?php echo $result['title']; ?></a></td>

                <?php $authors = $dbh->query("select group_concat(author order by author desc) as authors from book_author where ISBN = '".$result['ISBN']."' group by ISBN")->fetchColumn(); ?>
                <td><?php echo $authors; ?></td>
                <td>'<?php echo $result['ISBN']; ?>'</td>
                <td><?php if(empty($result['accession_id'])) echo "---"; else echo "'".$result['accession_id']."'"; ?></td>
                <td><?php echo $result['d_reserved']; ?></td>
                <td><?php echo $result['status']; ?></td>
               
              </tr>
            <?php $strip++ ; ?>
            <?php endforeach ; ?>
          </tbody>
        </table>
 
      <?php elseif($_POST['searchby'] == 'current_reserve') : ?>
        <table class="table table-bordered table-condensed rpt">
          <thead>
            <tr><td colspan="9" class="middle info"><?php echo "Report for ".$message;?></td></tr>
            <tr>
              <th>Member Name</th>
              <th>Member Type</th>
              <th>Title</th>
              <th>ISBN</th>
              <th>Accession No.</th>
              <th>Date Reserved</th>
              <th>Date Available</th>
              <th>Date Expire</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php $strip = 0; 
                  $red_plus = "strip-background";
             ?>
            <?php foreach($results as $result) : ?>
              <tr>
                <td><?php echo $result['complete_name']; ?></td>
                <td><?php echo $result['member_type']; ?></td>
                <td><a class="ISBN-link" href="admin_book_details.php?ISBN=<?php echo $result['ISBN'];?>" data-toggle="tooltip" data-placement="top" title="Go to Book Details Page for '<?php echo $result['title']; ?>'"><?php echo $result['title']; ?></a></td>
                <td>'<?php echo $result['ISBN']; ?>'</td>
                <td><?php if(empty($result['accession_id'])) echo "---"; else echo "'".$result['accession_id']."'"; ?></td>
                <td><?php echo $result['d_reserve']; ?></td>
                <td><?php if(empty($result['d_available'])) echo "---"; else echo $result['d_available']; ?></td>
                <td><?php if(empty($result['d_expire'])) echo "---"; else echo $result['d_expire']; ?></td>
                <td><?php echo $result['status']; ?></td>
              </tr>
            <?php $strip++ ; ?>
            <?php endforeach ; ?>
          </tbody>
        </table>

      <?php elseif($_POST['searchby'] == 'most_reserved') : ?>
        <table class="table table-bordered table-condensed rpt">
          <thead>
            <tr><td colspan="3" class="middle info"><?php echo "Report for ".$message;?></td></tr>
            <tr>
              <th>Amount Reserved</th>
              <th>ISBN</th>
              <th>Title</th>
            </tr>
          </thead>
          <tbody>
            <?php $strip = 0; 
                  $red_plus = "strip-background";
             ?>
            <?php foreach($results as $result) : ?>
              <tr>
                <td><?php echo $result['amount_reserved']; ?></td>
                <td>'<?php echo $result['ISBN']; ?>'</td>
                <td><a class="ISBN-link" href="admin_book_details.php?ISBN=<?php echo $result['ISBN'];?>" data-toggle="tooltip" data-placement="top" title="Go to Book Details Page for '<?php echo $result['title']; ?>'"><?php echo $result['title']; ?></a></td>
              </tr>
            <?php $strip++ ; ?>
            <?php endforeach ; ?>
          </tbody>
        </table>
      <?php elseif($_POST['searchby'] == 'most_members') : ?>
        <table class="table table-bordered table-condensed rpt">
          <thead>
            <tr><td colspan="2" class="middle info"><?php echo "Report for ".$message;?></td></tr>
            <tr>
              <th>Amount Reserved</th>
              <th>Member Name</th>
              
            </tr>
          </thead>
          <tbody>
            <?php $strip = 0; 
                  $red_plus = "strip-background";
             ?>
            <?php foreach($results as $result) : ?>
              <tr>
                <td><?php echo $result['highest']; ?></td>
                <td><?php echo $result['complete_name']; ?></td>
              </tr>
            <?php $strip++ ; ?>
            <?php endforeach ; ?>
          </tbody>
        </table>
      
      <?php endif ; ?>

      <?php if(count($result) >= 1) : ?>
      <p style="text-align:center; margin-top:50px;"><a class="btn-export btn btn-success" onClick ="$('.rpt').tableExport({type:'excel',tableName:'Informatics International College - Cainta', escape:'false'});"><span class="icon-download"></span>   Export to MS Excel</a></p>

      <?php endif ; ?>


    <?php else : ?>
      <table class="table table-bordered table-condensed">
        <thead>
          <tr>
            <th class="middle">Report</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="padding:50px; color:rgba(180,180,180,1);">
              <p class="middle" style="font-size:650%; color:rgba(200,200,200,0.5); margin-bottom:50px;"><span class="icon-attention-alt"></span>No Report Selected Yet.</p>
            </td>
          </tr>
        </tbody>
      </table>
    <?php endif ; ?>
   
  </div><!-- # wrapper -->

  
   <footer><p>Informatics International College - Cainta Library<br><br>&copy; 2014 all rights reserved</p></footer>

  

  <a href="#0" class="cd-top">Top</a> 
  <script>
   $(document).ready(function(){
    $(window).load(function(){
       $(".panel-contents").mCustomScrollbar({
           theme: "minimal-dark"
       });
    });
    
    $(".nav-container").click(function(){
             window.location = $(this).find("a").attr("href");
             return false;
    });

    // onClick ="$('#tableID').tableExport({type:'pdf',escape:'false'});"

    $('.input-group.date').datepicker({
      format: 'yyyy-mm-dd',
    });

    $('.datepicker').on('change',function(){
      $('.period').prop('checked',false);
    });

    $('.period').on('change',function(){
      $('.datepicker').val('');
    });

    $('.status[value="all"]').click(function(){
      if($(this).prop('checked')){
        $('.status[value="available"]').prop('checked', false);
        $('.status[value="waiting"]').prop('checked', false);
        $('.status[value="picked"]').prop('checked', false);
        $('.status[value="cancelled"]').prop('checked', false);
      }
      
    });

    $('.status[value="available"]').click(function(){
      if($(this).prop('checked')){
        $('.status[value="all"]').prop('checked', false);
      }
    });
    $('.status[value="waiting"]').click(function(){
      if($(this).prop('checked')){
        $('.status[value="all"]').prop('checked', false);
      }
    });
    $('.status[value="picked"]').click(function(){
      if($(this).prop('checked')){
        $('.status[value="all"]').prop('checked', false);
      }
    });
    $('.status[value="cancelled"]').click(function(){
      if($(this).prop('checked')){
        $('.status[value="all"]').prop('checked', false);
      }
    });

   $(".panel-btn").click(function(){
         var clickPanel = $('.panel-notif');
         clickPanel.toggle();
         $('.notif-badge').fadeOut(700);
         // $(".panel").not(clickPanel).slideUp(0);
         
         var member_id = "<?php echo $me['member_id'];?>";
         $.post('ajax_notification.php', {
            id : member_id
          }, function(){
            
          });
         return false;
    });

   $('.select-report').change(function(){
      if($(this).find("option:selected").val() == 'current_reserve'){
        $('.datepicker').prop('disabled',true);
        $('.period').prop('disabled',true);
        $('#row_datepicker').fadeOut(100);
        $('#row_periodpicker').fadeOut(100);
        
      } else {
        $('.datepicker').prop('disabled',false);
        $('.period').prop('disabled',false);
        $('#row_datepicker').fadeIn(100);
        $('#row_periodpicker').fadeIn(100);

      }

      if($(this).find("option:selected").val() == 'current_reserve' || $(this).find("option:selected").val() == 'most_members'){
        $('.status').prop('disabled',true);
        $('#row_status').fadeOut(100);
      } else {
        $('.status').prop('disabled',false);
        $('#row_status').fadeIn(100);
      }

      if($(this).find("option:selected").val() == 'most_reserved'){
        $('.status[value="all"]').prop('checked', false);
        $('.status[value="available"]').prop('checked', true);
        $('.status[value="waiting"]').prop('checked', true);
        $('.status[value="picked"]').prop('checked', true);
        $('.status[value="cancelled"]').prop('checked', false);
      } else {
        $('.status[value="all"]').prop('checked', true);
        $('.status[value="available"]').prop('checked', false);
        $('.status[value="waiting"]').prop('checked', false);
        $('.status[value="picked"]').prop('checked', false);
      }
    });

   if($('.select-report').val() == 'current_reserve'){
      $('.datepicker').prop('disabled',true);
      $('.period').prop('disabled',true);
      $('#row_datepicker').fadeOut(100);
      $('#row_periodpicker').fadeOut(100);
    } else {
      $('.datepicker').prop('disabled',false);
      $('.period').prop('disabled',false);
      $('#row_datepicker').fadeIn(100);
      $('#row_periodpicker').fadeIn(100);
    }

    if($('.select-report').val() == 'most_members' || $('.select-report').val() == 'current_reserve'){
      $('.status').prop('disabled',true);
      $('#row_status').fadeOut(100);
    } else {
      $('.status').prop('disabled',false);
      $('#row_status').fadeIn(100);
    }
    // $('.rpt').DataTable({
    //        dom: 'T<"clear">lfrtip',
    //        "filter":   false,
    //        "tableTools": {
    //            "sSwfPath": "http://iiccaintalibrary.com/admin/swf/copy_csv_xls_pdf.swf",
    //            "aButtons": [
    //               "xls",
    //               {
    //                   "sExtends": "pdf",
    //                   "sPdfOrientation": "landscape",
    //                   "sPdfMessage": "Informatics International College"
    //               },
    //               "print"
    //           ]
    //         }
    //    });

    $('a').tooltip()
    
  });
  </script>
  </body>
</html> 
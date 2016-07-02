<?php
  
  require_once('php/config.php');
  require_once('php/functions.php');

  session_start();

  if(empty($_SESSION['me'])){
    header("LOCATION: index.php");
    exit;
  }

  $dbh = connectDB();
  $me = $_SESSION['me'];

  //////////////////////////////////HANDLE DUEDATE
  handleOverdue($dbh);
 
  $notifications = getNotification($me['member_id']);

  $pw_updated_date = $dbh->query("select date_format(password_updated, '%M %e %Y') as updated_date from member_basic where member_id = '".$me['member_id']."'")->fetchColumn();

  $total_reservation_count = $dbh->query("select count(*) from book_reserved where member_id = '".$me['member_id']."' and not status = 'cancelled'")->fetchColumn();
  
  $total_borrow_count = $dbh->query("select count(*) from book_borrowed where member_id = '".$me['member_id']."' and (status = 'borrowed' or status = 'overdue')")->fetchColumn();

  $total_overdue_count = $dbh->query("select count(*) from book_borrowed where member_id = '".$me['member_id']."' and status = 'overdue'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Personal Page</title>
<link rel = "short icon" href="images/favicon5.ico"/>
<link rel="stylesheet" href="styles/bootstrap.min.css">
<link rel="stylesheet" href="styles/reset.css"/>
<link rel="stylesheet" href="styles/global.css"/>
<link rel="stylesheet" href="styles/fonts.css"/>
<link rel="stylesheet" href="styles/fontello.css"/>
<link rel="stylesheet" href="styles/backtotop.css"/> 

<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/backtotop.js"></script>
</head>
<body>
  <header>
    <a href="home_login.php" id="logo"><img src="images/height80.png"/></a>
    <span class="hi_member"><?php echo $me['member_firstname']." ".$me['member_lastname'];?></span>
    <nav id="mainnav">  
      <div class="nav-container ">
        <div class="nav-left"><p class="icon-home-big"></p></div>
        <div class="nav-right"><li><a href="home_login.php">HOME</a></li></div>
      </div> 
      <div class="nav-container ">
        <div class="nav-left "><p class="icon-book-big"></p></div>
        <div class="nav-right two-line"><li><a href="subject_search.php">Subject Search</a></li></div>
      </div>
      <div class="nav-container ">
        <div class="nav-left "><p class="icon-book-big"></p></div>
        <div class="nav-right two-line"><li><a href="category_search.php">Category Search</a></li></div>
      </div>
      <div class="nav-container">
        <div class="nav-left"><p class="icon-heart-big"></p></div>
        <div class="nav-right"><li><a href="my_list.php">MY LIST</a></li></div>
      </div>
      <div class="nav-container active">
        <div class="nav-left"><p class="icon-user-big"></p></div>
        <div class="nav-right"><li><a href="personal_page_reservation.php">Personal</a></li></div>
      </div>
      <div class="nav-container">
        <div class="nav-left"><p class="icon-logout-big"></p></div>
        <div class="nav-right"><li><a href="logout.php">Log Out</a></li></div>     
      </div>                 
    </nav>
  <div class="notification"> <!-- as panel wrap -->
        <?php $notif_count = $dbh->query("select count(*) from notification where member_id = ".$me['member_id']." and check_seen = 0")->fetchColumn(); ?>
        <?php $notif_check_exists = $dbh->query("select count(*) from notification where member_id = ".$me['member_id'])->fetchColumn(); ?>
        <?php if($notif_count >= 1) : ?>
        <span class="notif-badge"><?php echo $notif_count;?></span>
        <?php endif ; ?>
        <span class="icon-bell"></span>
        <div class="panel-btn"></div> 

        <div class="panel-notif">
          <div class="panel-label">notifications</div>
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
  </header>
    
  <div id="wrapper">
    <?php if($_SESSION['tempo_message'] == 1) : ?>
        <div class="alert-pw-update-success alert alert-success alert-dismissible" role="alert">
          <strong>Password changed!</strong> 
        </div>
      <?php endif ; ?>
      <?php unset($_SESSION['tempo_message']); ?>
    <div id="crumbs_container" class="margin20">
      <div id="crumbs">
        <ul>
          <li><a href="home_login.php"><p class="icon-home"></p></a></li>
         <!--  <li><a href="#">Welcome Page</a></li> -->
          <li><a href="">Personal Page (Profile)</a></li>
        </ul>
      </div> <!-- ** breadcrumbs ** -->
    </div>

    <h3 class="icon-user">  My Personal Page</h3>
    
    <ul class="my-tabs nav nav-tabs nav-justified" role="tablist">
      <li><a href="personal_page_reservation.php">Reservation</a></li>
      <li><a href="personal_page_borrowed.php">Borrowed Books</a></li>
      <li class="active"><a href="">Profile</a></li>
    </ul>

    <div class="tab-contents">
      <table class="table" style="background:rgba(240,240,240,0.5);">
        <tr>
          <th>Name : </th>
          <td><?php echo $me['member_firstname']." ".$me['member_lastname'];?></td>
        </tr>
        <tr>
          <th>Account No. : </th>
          <td><?php echo $me['member_id'];?></td>
        </tr>
        <tr>
          <th>Member Type : </th>
          <td><?php echo $me['member_type'];?></td>
        </tr>
         <tr>
          <th>Total Books Reserved : </th>
          <td><?php echo $total_reservation_count; ?></td>
        </tr>
        <tr>
          <th>Total Books Borrowed : </th>
          <td><?php echo $total_borrow_count; ?></td>
        </tr>
         <tr>
          <th>Overdue Books : </th>
          <td><?php echo $total_overdue_count; ?></td>
        </tr>
         <tr>
          <th>Password : </th>
          <td><?php if(isset($pw_updated_date)) : ?>
                 <?php echo "Updated on ".$pw_updated_date;?>
              <?php else : ?>
                  <?php echo "Default (Your Birthdate)"; ?>
              <?php endif ; ?>
              <br><a href="change_password.php" class="change-pw">Change Password</a>
          </td>
        </tr>
      </table>
    </div>
  </div>
  
  <footer><p>Informatics International College - Cainta Library<br><br>&copy; 2014 all rights reserved</p></footer>

  <a href="#0" class="cd-top">Top</a>
  
  <script>
    $(document).ready(function(){

      $(".nav-container").click(function(){
             window.location=$(this).find("a").attr("href");
             return false;
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

      $('.alert-pw-update-success').fadeOut(4000);
      
    });
  </script>
  </body>
</html>
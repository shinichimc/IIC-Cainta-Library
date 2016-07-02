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

  $reservations = array();
  $sql = "select *, date_format(date_expire, '%b %d (%a)') as expires , date_format(date_reserved, '%b %d / %h:%i %p') as time_reserved, date_format(date_available, '%b %d / %h:%i %p') as time_available from book_reserved where not status = 'picked' and not status = 'cancelled' and member_id = ".$me['member_id']." order by date_available desc, status";
  foreach($dbh->query($sql) as $row){ 
    array_push($reservations, $row);
  }

  $reservation_count = $dbh->query("select count(*) from book_reserved where not status = 'picked' and not status = 'cancelled' and member_id = ".$me['member_id'])->fetchColumn();
  // var_dump($all_lists);
  
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
    <div id="crumbs_container" class="margin20">
      <div id="crumbs">
        <ul>
          <li><a href="home_login.php"><p class="icon-home"></p></a></li>
         <!--  <li><a href="#">Welcome Page</a></li> -->
          <li><a href="">Personal Page (Reservation)</a></li>
        </ul>
      </div> <!-- ** breadcrumbs ** -->
    </div>

    <h3 class="icon-user">  My Personal Page</h3>
    
    <ul class="my-tabs nav nav-tabs nav-justified" role="tablist">
      <li class="active"><a href="">Reservation</a></li>
      <li><a href="personal_page_borrowed.php">Borrowed Books</a></li>
      <li><a href="personal_page_profile.php">Profile</a></li>
    </ul>

    <div class="tab-contents">
      <div class="table-responsive">
      <table id="table-personal-reservation" class="table table-bordered">
        <thead>
          <tr>
            <th>Title</th>
            <th>Date Requested</th>
            <th>Status</th>
            <th>Date Available</th>
            <th>Expires</th>
            <th>Option</th>
          </tr>
        </thead>
        <tbody>
          <?php if($reservation_count == 0) : ?>
          <tr>
            <td colspan="6" style="text-align:center; color:rgb(200,200,200); font-size:95%;">There's no book you have reserved recently.</td>
          </tr>
          <?php else : ?>
    
            <?php foreach($reservations as $reservation) :?>
              <?php 
                $isbn = $reservation['ISBN'];
                $sql = "select * from book_basic where ISBN = :isbn";
                $stmt = $dbh->prepare($sql);
                $stmt->execute(array(":isbn" => $isbn));
                $book = $stmt->fetch();
                ?>
                <?php if($reservation['status'] == "available") : ?>
                <tr class="downy">
                <?php else : ?>
                <tr> 
                <?php endif ; ?>
                  <td><a class="ISBN-link2" href="member_book_details.php?isbn=<?php echo $reservation['ISBN'];?>"><?php echo $book['title'];?></a></td>
                  <td><?php echo $reservation['time_reserved'];?></td>
                  <td><?php echo $reservation['status'];?></td>

                  <?php if($reservation['status'] == "available") : ?>
                  <td><?php echo $reservation['time_available'];?></td>       
                  <td><?php echo $reservation['expires']; ?></td>
                  <?php else : ?>
                  <td style="text-align:center;">---</td>       
                  <td style="text-align:center;">---</td>
                  <?php endif ; ?>
                  <td><a class="btn-cancel-reservation btn btn-default btn-sm" href="cancel_reservation.php?isbn=<?php echo $reservation['ISBN'];?>&id=<?php echo $reservation['reservation_id'];?>">Cancel</a></td>
                </tr>
                </tr>
            <?php endforeach ; ?>
          <?php endif ; ?>
        </tbody>
      </table>
    </div>
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

      $('.btn-cancel-reservation').click(function(){
        if(confirm("are you sure you want to cancel this item?")){
        } else {
          return false;
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
      
    });
  </script>
  </body>
</html>
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

  $borrowed_info = array();
  $sql = "select book_borrowed.*, title, date_format(date_borrowed, '%b %d (%a)') as d_borrow , date_format(date_due, '%b %d (%a)') as d_due, datediff(date_due,now()) as diff from book_borrowed left join book_basic using (ISBN) where not status = 'returned' and member_id = ".$me['member_id']." order by date_borrowed desc";
  foreach($dbh->query($sql) as $row){ 
    array_push($borrowed_info, $row);
  }
  
  $borrow_count = $dbh->query("select count(*) from book_borrowed where not status = 'returned' and member_id = ".$me['member_id'])->fetchColumn();

  
 
  
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
    <!-- <div class="success-reserved alert alert-success alert-dismissable">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      <strong>1 book successfully Updated!</strong>
    </div> -->

    <div id="crumbs_container" class="margin20">
      <div id="crumbs">
        <ul>
          <li><a href="home_login.php"><p class="icon-home"></p></a></li>
         <!--  <li><a href="#">Welcome Page</a></li> -->
          <li><a href="">Personal Page (Borrowed Books)</a></li>
        </ul>
      </div> <!-- ** breadcrumbs ** -->
    </div>


    <h3 class="icon-user">  My Personal Page</h3>
    
    <ul class="my-tabs nav nav-tabs nav-justified" role="tablist">
      <li><a href="personal_page_reservation.php">Reservation</a></li>
      <li class="active"><a href="">Borrowed Books</a></li>
      <li><a href="personal_page_profile.php">Profile</a></li>
    </ul>

    <div class="tab-contents">
      <div class="table-responsive">
        <table id="table-mylist" class="table table-bordered">
          <thead>
            <tr>
              <th style="text-align:center;">Status</th>
              <th>Title</th>
              <th>Date Borrowed</th>
              <th>Date Due</th>
              <th>Option</th>
            </tr>
          </thead>
          <tbody>
             <?php if($borrow_count == 0) : ?>
              <tr>
                <td colspan="5" style="text-align:center; color:rgb(200,200,200); font-size:95%;">There's no book you're currently borrowing</td>
              </tr>
              <?php else : ?>
                
                <?php foreach($borrowed_info as $borrow) :?>
                <tr <?php if($borrow['diff'] <= 0) echo "class='red'";?>> 

                  <td style="text-align:center;">
                  <?php if($me['member_type'] == 'Staff' || $me['member_type'] == 'Faculty') :?>
                   <span>---</span>
                  <?php else : ?>
                    <?php 
                    $count_pending = $dbh->query("select count(*) from book_reserved where ISBN = '".$borrow['ISBN']."' and status = 'waiting'")->fetchColumn();
                    $which_icon = $count_pending >= 1 ? "attention-circled" : "circle";

                    if($borrow['diff'] >= 3) $color = "three";
                    elseif($borrow['diff'] == 2) $color = "two";
                    elseif($borrow['diff'] == 1) $color = "one";
                    else $color="black";
                    ?>
                    <span class="signal icon-<?php echo $which_icon;?> <?php echo $color;?>">
                      <div id="legend-container2">
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

                  <td><a <?php if($borrow['diff'] <= 0) echo "style='color:#fff;'";?> class="ISBN-link" href="member_book_details.php?isbn=<?php echo $borrow['ISBN'];?>"><?php echo $borrow['title'];?></a></td>
                  <td><?php echo $borrow['d_borrow'];?></td>
                  <td><?php echo $borrow['d_due'];?></td>
                  <td style="text-align:center;"><a id="btn-extend" class="btn btn-default btn-sm" href="extend.php?borrowed_id=<?php echo $borrow['borrowed_id'];?>"<?php if($which_icon == 'attention-circled' || $borrow['diff'] <= 0 || $borrow['diff'] >= 3) echo 'disabled'; ?>>Extend</a></td>
                
                </tr>

                <?php endforeach ; ?>
              <?php endif ; ?>
          </tbody>
        </table>
      </div>
      <p style="text-align:center; margin-top:80px; margin-bottom:-3px; font-size:78%;">*Extend option is available only within 2 days of the date due and when there's no pending reservation.</p>
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

      $('.success-reserved').click(function () {
        $(this).fadeOut('slow');
      });

      $('#btn-extend').click(function(){
        if(confirm("are you sure you want to extend?")){

        } else {
          return false;
        }

      });

      $(".signal").hover(function(){
        $(this).find('#legend-container2').fadeIn(200).css('left','300px');
      }, function(){
         $(this).find('#legend-container2').fadeOut(200);
      });
    });
    </script>
  </body>
</html>
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

  $three_check_count = $dbh->query("select count(*) from book_reserved where not status = 'picked' and not status = 'cancelled' and member_id = ".$me['member_id'])->fetchColumn();
 

  $notifications = getNotification($me['member_id']);

  $all_lists = array();
  $sql = "select ISBN, member_id, GROUP_CONCAT(book_author.author ORDER BY book_author.author) as Authors from rec_list LEFT JOIN book_author USING (ISBN) where member_id = ".$me['member_id']." group by ISBN";
  foreach($dbh->query($sql) as $row){
    array_push($all_lists, $row);
  }
  // var_dump($all_lists);
  
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My List</title>
<link rel = "short icon" href="images/favicon5.ico"/>
<link rel="stylesheet" href="styles/bootstrap.min.css">
<link rel="stylesheet" href="styles/bootflat.min.css"/>
<link rel="stylesheet" href="styles/reset.css"/>
<link rel="stylesheet" href="styles/global.css"/>
<link rel="stylesheet" href="styles/fonts.css"/>
<link rel="stylesheet" href="styles/fontello.css"/>
<link rel="stylesheet" href="styles/backtotop.css"/>
<link rel="stylesheet" href="styles/accordion2.css"/>
 

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
      <div class="nav-container active">
        <div class="nav-left"><p class="icon-heart-big"></p></div>
        <div class="nav-right"><li><a href="my_list.php">MY LIST</a></li></div>
      </div>
      <div class="nav-container">
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
    <div class="success-reserved alert alert-success alert-dismissable">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      <strong>1 book successfully reserved!</strong>
    </div>
    <div id="crumbs_container" class="margin20">
      <div id="crumbs">
        <ul>
          <li><a href="home_login.php"><p class="icon-home"></p></a></li>
         <!--  <li><a href="#">Welcome Page</a></li> -->
          <li><a href="">My List</a></li>
        </ul>
      </div> <!-- ** breadcrumbs ** -->
    </div>
    <h3 class="icon-heart">   My List</h3>
   
    <hr>
    <div class="table-responsive">
      <table id="table-mylist" class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>Title</th>
            <th>Author(s)</th>
            <th>Availability</th>
            <th>Option</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($all_lists as $each_list) :?>
          <?php 
            $isbn = $each_list['ISBN'];
            $sql = "select count(*) as c1, (select count(*) from book_each where availability = 'Available' and ISBN = :isbn1) as c2 from book_each where ISBN = :isbn2";
            $stmt = $dbh->prepare($sql);
            $stmt->execute(array(":isbn1" => $isbn, ":isbn2" => $isbn));
            $available = $stmt->fetch();

            $sql = "select title from book_basic where ISBN = :isbn";
            $stmt = $dbh->prepare($sql);
            $stmt->execute(array(":isbn" => $isbn));
            $title = $stmt->fetch();

            $missing_count = $dbh->query("select count(*) from book_each where missing = 1 and ISBN = '".$isbn."'")->fetchColumn();
            $max_count = $dbh->query("select count(*) from book_each where ISBN = '".$isbn."'")->fetchColumn(); 
            ?>
          <tr id = "row_<?php echo $each_list['ISBN'];?>" data-id="<?php echo $each_list['ISBN'];?>">

            <td><a class="ISBN-link2" href="member_book_details.php?isbn=<?php echo $each_list['ISBN'];?>"><?php echo $title['title'];?></a></td>
            <td><?php echo $each_list['Authors'];?></td>
            <td><?php echo $available['c2']." / ".$available['c1']?></td>

            <?php $one_check_count = $dbh->query("select count(*) from book_reserved where not status = 'picked' and not status = 'cancelled' and member_id = ".$me['member_id']." and ISBN = '".$each_list['ISBN']."'")->fetchColumn(); ?>

            <?php if($me['disabled'] != 0 || $one_check_count >= 1 || $three_check_count >= 3 || $missing_count == $max_count)  : ?>
            <td><button class="delete-btn btn btn-danger btn-sm" >Clear</button>&nbsp;&nbsp;<button class="reserve-btn btn btn-primary btn-sm disabled">Reserve</button></td>
            <?php else : ?>
            <td><button class="delete-btn btn btn-danger btn-sm" >Clear</button>&nbsp;&nbsp;<button class="reserve-btn btn btn-primary btn-sm">Reserve</button></td>
            <?php endif ; ?>

          </tr>
          <?php endforeach ; ?>
        </tbody>
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

      $(document).on('click','.delete-btn', function(){
        var test = $(this).closest('tr').data('id');
        console.log(test);
        if(confirm('Are you sure you want to delete this item?')){
          var id = $(this).closest('tr').data('id');
          console.log(id);
          $.post('ajax_delete_list.php', {
            id : id
          }, function(){
            $('#row_'+id).fadeOut(500);
          });
        }
      });

      $(document).on('click','.reserve-btn', function(){
        if(confirm('Are you sure you want to reserve this item?')){
          var id = $(this).closest('tr').data('id');
          console.log(id);
          $.post('reserve.php', {
            isbn : id
          }, function(){
              $(".success-reserved").show();   
              $('#row_'+id).find('.reserve-btn').prop('disabled',true);    
          });
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

      $('.success-reserved').click(function () {
        $(this).fadeOut('slow');
      });
    });
    </script>
  </body>
</html>
<?php
  
  require_once('php/config.php');
  require_once('php/functions.php');

  session_start();

  if(empty($_SESSION['me'])){
    header("LOCATION: index.php");
    exit;
  }

  $me = $_SESSION['me'];
  $dbh = connectDB();
  
  ///////////////////////////////////HANDLE EXPIRE
  handleExpire($dbh);

  //////////////////////////////////HANDLE DUEDATE
  handleOverdue($dbh);

  $notifications = getNotification($me['member_id']);
  
  if($me['member_type'] != "Admin"){
    header('LOCATION: index.php');
    exit;
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Home</title>
<link rel = "short icon" href="images/favicon7.ico"/>
<link rel="stylesheet" href="styles/jquery.mCustomScrollbar.css">
<link href="styles/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="styles/reset.css"/>
<link rel="stylesheet" href="styles/fonts.css"/>
<link rel="stylesheet" href="styles/fontello.css"/>
<link rel="stylesheet" href="styles/backtotop.css"/>
<link rel="stylesheet" href="styles/global.css"/>
<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/backtotop.js"></script>
<script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
<script src="js/dropdown.min.js"></script>   
</head>
<body>
    <header>
      <a href="#" id="logo"><img src="images/height80.png"/></a>
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
      <h2 class="general">General Information</h2><hr>
      
      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed a euismod lacus. Sed fermentum non turpis vitae auctor. Pellentesque vitae eros a nunc tincidunt venenatis. Donec mattis vulputate massa, et iaculis enim sodales malesuada. Phasellus ac porta nisi, et adipiscing massa. Nulla facilisi. Nunc id mauris quis quam auctor gravida. Praesent facilisis vulputate turpis ac lobortis. In ultrices semper libero, at commodo magna rhoncus vitae. Nam dictum diam eget lorem posuere, et malesuada diam consequat. Quisque eget ipsum urna.</p>
      <br><p>Sed fringilla tempus rutrum. Suspendisse sit amet ligula purus. In hendrerit dolor sit amet sapien facilisis, tempor fringilla nisi dictum. Integer lobortis elementum erat vel consequat. Integer at dolor quis diam bibendum posuere a sit amet quam. Nullam id ligula quis nisl congue iaculis in ut est. Vestibulum volutpat dictum risus, interdum sollicitudin elit blandit sed. Donec aliquam quam lorem. Fusce malesuada lacus sem, et rutrum ligula feugiat a. Donec ut enim vel ante vulputate posuere sit amet ac sapien. In sed tristique eros. Sed eget leo mauris. Praesent sed scelerisque quam, a hendrerit eros. Praesent suscipit euismod pulvinar.</p>
      <br><p>Proin vitae adipiscing diam. Nam eu leo euismod, sollicitudin quam non, sagittis sem. Aliquam erat volutpat. Nam id dolor diam. Ut non tellus sed massa accumsan vestibulum. Etiam sed tincidunt nisi. Ut ut eros mattis, luctus leo sed, laoreet orci. In a elit tellus. Suspendisse laoreet, eros id feugiat interdum, ipsum nisi facilisis libero, feugiat semper lectus nunc eget magna. Sed rhoncus diam dolor, non iaculis dui egestas non. Aenean pellentesque pulvinar bibendum.</p>
      <br><p>Quisque tempor risus augue, varius aliquet tortor consequat in. Mauris ligula dolor, laoreet non tellus id, fringilla aliquet odio. Fusce imperdiet magna ut libero rhoncus, at rhoncus erat consectetur. Quisque fermentum tellus risus, vitae dignissim nunc tempus id. Proin eget libero nec leo facilisis dapibus. Nulla congue massa id egestas dapibus. Vivamus cursus a mi vel elementum. Donec accumsan vulputate ultricies. Donec sed risus dignissim, fringilla augue sed, dignissim lectus. Vivamus eu dictum leo. Duis ac suscipit risus. Aliquam dui mi, luctus in vestibulum non, lobortis lobortis mi. Donec eget lorem velit. Praesent elit nibh, interdum ut nunc quis, auctor imperdiet nisl. Curabitur vel posuere ipsum.</p>
      <br><p>Proin vitae adipiscing diam. Nam eu leo euismod, sollicitudin quam non, sagittis sem. Aliquam erat volutpat. Nam id dolor diam. Ut non tellus sed massa accumsan vestibulum. Etiam sed tincidunt nisi. Ut ut eros mattis, luctus leo sed, laoreet orci. In a elit tellus. Suspendisse laoreet, eros id feugiat interdum, ipsum nisi facilisis libero, feugiat semper lectus nunc eget magna. Sed rhoncus diam dolor, non iaculis dui egestas non. Aenean pellentesque pulvinar bibendum.</p>
    </div><!-- # wrapper -->
    <footer><p>Informatics International College - Cainta Library<br><br>&copy; 2014 all rights reserved</p></footer>
    <a href="#0" class="cd-top">Top</a>
  
   <script>
    $(document).ready(function(){   
      $(".nav-container").click(function(){
        window.location = $(this).find("a").attr("href");
        return false;
      });

      $(".nav-container").click(function(){
        $(this).find("a").attr("href");
        $(this).find("a").attr("href");       
      });
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
  </script>
  </body>
</html>
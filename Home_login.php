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

//////////////////////////////////HANDLE EXPIRE
handleExpire($dbh);

//////////////////////////////////HANDLE DUEDATE
handleOverdue($dbh);

$notifications = getNotification($me['member_id']);

$hearts = array();
$sql = "select * from rec_list where member_id = ".$me['member_id'];
foreach($dbh->query($sql) as $row){
  array_push($hearts, $row);
}

define('ITEMS_PER_PAGE',10);

if(isset($_GET['clear'])){
  destroySearchSession ();
} 
  


if(isset($_POST['submit'])){// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
// checkToken();

  $_SESSION['sel'] = $_POST['searchby'];
  $_SESSION['primary_search'] = $_POST['primary_search'];

  $_SESSION['page'] = 1;

  $offset = ITEMS_PER_PAGE * ($_SESSION['page'] - 1);

  $results = array();
  if($_SESSION['sel'] == 'title'){
    $sql = "select ISBN, title,  GROUP_CONCAT(book_author.author ORDER BY book_author.author) as Authors, year from book_basic LEFT JOIN book_author USING (ISBN) where title like '%".$_SESSION['primary_search']."%' group by ISBN limit ".$offset.",".ITEMS_PER_PAGE;
    $_SESSION['total'] = $dbh->query("select count(*) from book_basic where title like '%".$_SESSION['primary_search']."%'")->fetchColumn();
  } elseif($_SESSION['sel'] == 'year') {
    $sql = "select ISBN, title,  GROUP_CONCAT(book_author.author ORDER BY book_author.author) as Authors, year from book_basic LEFT JOIN book_author USING (ISBN) where year like '%".$_SESSION['primary_search']."%' group by ISBN limit ".$offset.",".ITEMS_PER_PAGE;
    $_SESSION['total'] = $dbh->query("select count(*) from (select ISBN, title, GROUP_CONCAT(book_author.author ORDER BY book_author.author) as Authors, year from book_basic LEFT JOIN book_author USING (ISBN) where year like '%".$_SESSION['primary_search']."%' group by ISBN) as count")->fetchColumn();
  } else {
   $sql = "select ISBN, title,  GROUP_CONCAT(book_author.author ORDER BY book_author.author) as Authors, year from book_basic LEFT JOIN book_author USING (ISBN) group by ISBN having Authors like '%".$_SESSION['primary_search']."%' limit ".$offset.",".ITEMS_PER_PAGE;
   $_SESSION['total'] = $dbh->query("select count(*) from (select ISBN, title, GROUP_CONCAT(book_author.author ORDER BY book_author.author) from book_basic LEFT JOIN book_author USING (ISBN) group by ISBN having GROUP_CONCAT(book_author.author ORDER BY book_author.author) like '%".$_SESSION['primary_search']."%') as count")->fetchColumn();
   
  }
  foreach($dbh->query($sql) as $row){
  array_push($results, $row);
  }

  $_SESSION['sql'] = $sql;  //tempo

  $_SESSION['results'] = $results;

  
  $_SESSION['totalPages'] = ceil($_SESSION['total'] / ITEMS_PER_PAGE);
  
  } //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

if(isset($_GET['page'])){//###################################

  if(preg_match('/^[1-9][0-9]*$/',$_GET['page'])){ //
    $_SESSION['page'] = (int)$_GET['page'];                    // GETTING CURRENT PAGE, GET THE VALUE FROM URL FROM $_GET['page']
  }else{                                           //
    $_SESSION['page'] = 1;                                     //
  }

  $offset = ITEMS_PER_PAGE * ($_SESSION['page'] - 1);

  $results = array();
  if($_SESSION['sel'] == 'title'){
  $sql = "select ISBN, title,  GROUP_CONCAT(book_author.author ORDER BY book_author.author) as Authors, year from book_basic LEFT JOIN book_author USING (ISBN) where title like '%".$_SESSION['primary_search']."%' group by ISBN limit ".$offset.",".ITEMS_PER_PAGE;
  } elseif($_SESSION['sel'] == 'year') {
  $sql = "select ISBN, title,  GROUP_CONCAT(book_author.author ORDER BY book_author.author) as Authors, year from book_basic LEFT JOIN book_author USING (ISBN) where year like '%".$_SESSION['primary_search']."%' group by ISBN limit ".$offset.",".ITEMS_PER_PAGE;
  } else {
  $sql = "select ISBN, title,  GROUP_CONCAT(book_author.author ORDER BY book_author.author) as Authors, year from book_basic LEFT JOIN book_author USING (ISBN) group by ISBN having Authors like '%".$_SESSION['primary_search']."%' limit ".$offset.",".ITEMS_PER_PAGE;
  }
  foreach($dbh->query($sql) as $row){
  array_push($results, $row);
  }

  $_SESSION['sql'] = $sql;  //tempo

  $_SESSION['results'] = $results;
  
}// ################################################

$results = $_SESSION['results'];
// echo "page : ".$_SESSION['page'].",";
// echo "total = ".$_SESSION['total'].",";
// echo "totalPages = ".$_SESSION['totalPages'].",";
// echo "<br>";
// echo $_SESSION['sql'];
// echo "<br>";
// echo $_POST['searchby'];
// echo $_SESSION['sel'];
// var_dump($results);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Home (<?php echo "Home | Basic search"; ?>)</title>

<!-- Bootstrap -->
<!-- <link href="styles/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="styles/normalize.css" />
<link rel="stylesheet" href="styles/flatui.min.css" />
<link rel="stylesheet" href="styles/magnific-popup.css"> -->
<link rel='stylesheet' type='text/css' href='http://fonts.googleapis.com/css?family=PT+Sans:400,700' >
<link rel="stylesheet" href="styles/jquery.mCustomScrollbar.css">
<link rel="stylesheet" href="styles/bootstrap.min.css">
<link rel="stylesheet" href="styles/reset.css"/>
<link rel='stylesheet' id='camera-css' href='styles/camera.css' type='text/css'>
<link rel="stylesheet" href="styles/global.css"/>
<link rel="stylesheet" href="styles/fonts.css"/>
<link rel="stylesheet" href="styles/fontello.css"/>
<link rel="stylesheet" href="styles/backtotop.css"/>
<link rel="stylesheet" href="styles/accordion2.css"/>
<link rel="short icon" href="images/favicon5.ico"/>

<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/backtotop.js"></script>
<script type='text/javascript' src='js/jquery.min.js'></script>
<script type='text/javascript' src='js/jquery.mobile.customized.min.js'></script>
<script type='text/javascript' src='js/jquery.easing.1.3.js'></script> 
<script type='text/javascript' src='js/camera.min.js'></script>

</head>
<body>
  <?php if($me['disabled'] == 1) : ?>
  <div class="alert alert-danger alert-dismissable">
    <strong>I'm sorry, your account is currently disabled! Your account needs to be enabled by the admin in order to be able to reserve books.</strong>
  </div>
  <?php endif ; ?>
  <header>
    <div id="toggle"><a class="icon-menu" href="#"></a><span>IIC - Cainta Libary</span></div>
    <a href="#" id="logo"><img src="images/height80.png"/></a>
    <span class="hi_member"><?php echo $me['member_firstname']." ".$me['member_lastname'];?></span>
    <nav id="mainnav">  
      <div class="nav-container active">
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
    <div style="position:relative">
      <div id="crumbs_container" class="margin20">
        <div id="crumbs">
          <ul>
            <li><a href="#"><p class="icon-home"></p></a></li>
           <!--  <li><a href="#">Welcome Page</a></li> -->
            <li><a href="home_login.php">Basic Search   <span class="icon-search"></span></a></li>
            <?php if(isset($_SESSION['primary_search'])) : ?>
            <li><a href=""><?php echo $_SESSION['primary_search'];?></a></li>
            <?php endif; ?>
          </ul>
        </div> <!-- ** breadcrumbs ** -->
      </div>
      <a href="home_login.php?clear=0" class="btn-refresh btn btn-default btn-sm" data-toggle="tooltip" data-placement="left" title="Clear Search"><span class="icon-arrows-cw" style="color:rgba(100,100,100,0.8);"></span></a>
    </div>
    <hr>
    <!-- form ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
    <form class="search-box-form form-inline" action="home_login.php" method="POST">
      <select class="form-control" name="searchby">
        <option value="title" <?php if($_SESSION['sel']=='title') echo 'selected';?>>Title</option>
        <option value="author" <?php if($_SESSION['sel']=='author') echo 'selected';?>>Author</option>
        <option value="year" <?php if($_SESSION['sel']=='year') echo 'selected';?>>Year</option>
      </select>
      <div class="input-group search-box">
        <input class="form-control search-text" type="text" name="primary_search" placeholder=" Basic Search" value="<?php echo $_SESSION['primary_search'];?>" required autofocus >
        <span class="input-group-btn">
          <input class="btn btn-default search-text" name="submit" type="submit" value="Go!">
        </span>
      </div>
      <!-- <input type="hidden" name="token" value = "<?php echo h($_SESSION['token']); ?>"> -->
    </form>
    <hr>

    <?php if($_SERVER['REQUEST_METHOD'] == 'POST'|| isset($_GET['page']) ||isset($_SESSION['secret'])) : ?>

    <!-- result info container ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
    <div class="result-info-container">
      <div class="result-info-box">
        <?php $to = ($offset + ITEMS_PER_PAGE) < $_SESSION['total'] ? ($offset + ITEMS_PER_PAGE) : $_SESSION['total']; ?>
        <?php if($_SESSION['total'] >= ITEMS_PER_PAGE) :?>
          <p><?php echo $offset + 1,"-".$to." of ".$_SESSION['total']." results found for '".$_SESSION['primary_search']."'";?></p>
        <?php elseif($_SESSION['total'] < ITEMS_PER_PAGE && $_SESSION['total'] != 0) : ?>
         <p><?php echo $offset + 1,"-". ($_SESSION['total'])." of ".$_SESSION['total']." results found for '".$_SESSION['primary_search']."'";?></p>
        <?php else :?>
        <p><?php echo "No results found.";?></p>
       <?php endif ; ?>
      </div>
    </div>
     
    <!--  result container~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
    <div id="result-container">
      <?php $ctr = 1; ?>
      <?php foreach($results as $result) : ?>
      <?php 
      $isbn = $result['ISBN'];
      $sql = "select count(*) as c1, (select count(*) from book_each where availability = 'Available' and ISBN = :isbn1) as c2 from book_each where ISBN = :isbn2";
      $stmt = $dbh->prepare($sql);
      $stmt->execute(array(":isbn1" => $isbn, ":isbn2" => $isbn));
      $available = $stmt->fetch();
      ?>
      <div class="result-semi-container">
        <a class="title" href="member_book_details.php?isbn=<?php echo $result['ISBN']; ?>"><?php echo $offset + $ctr ?>. <?php echo $result['title'];?></a>
        <ul>
          <li>ISBN: <?php echo $result['ISBN']; ?></li>
          <li>Author: <?php echo $result['Authors']; ?></li>
          <li>Publication Year: <?php echo $result['year']; ?></li>
          <li>Availability: <?php echo $available['c2']." / ".$available['c1']; ?></li>
        </ul>
        <?php
        $heart_ctr = 0;
        foreach($hearts as $heart){
          if(strcmp($result['ISBN'] , $heart['ISBN']) == 0){
          $heart_ctr++;
          }
        }
        ?>
        <?php
        if($heart_ctr >= 1){
         echo "<p class='result-add-disabled icon-heart'>This is on your List!</p>";   
        }else{
          echo "<a href='add_mylist.php?isbn=".$result['ISBN']."' class='result-add icon-heart'>Add to My List</a>";
        }
        ?>            
      </div>
      <?php $ctr++;?>
      <?php endforeach; ?>
    </div> 
    <?php else : ?>
    <div class="camera_wrap camera_beige_skin" id="camera_wrap_1" style="margin-top:35px;">
      <div data-src="images/poster1.jpg">
          <div class="camera_caption fadeFromBottom">
              Informatics International College - Cainta Library
          </div>
      </div>
      <div data-src="images/library1.jpg">
          <div class="camera_caption fadeFromBottom">
              Informatics International College - Cainta Library
          </div>
      </div>
      <div data-src="images/library2.jpg">
          <div class="camera_caption fadeFromBottom">
              Informatics International College - Cainta Library
          </div>
      </div>
      <div data-src="images/library3.jpg">
          <div class="camera_caption fadeFromBottom">
              Informatics International College - Cainta Library
          </div>
      </div>
  </div><!-- #camera_wrap_1 -->
    <h2 class="general icon-search"> Basic Search</h2><br>
    <div class="introduction">
      
        <h5 class="icon-search intro">  Basic Search</h5>
        <p class="intro-indent">You can search books by Title, Author and Year.</p>
        <h5 class="icon-search intro">  Subject Search</h5>
        <p class="intro-indent">When you want to search books related to certain subjects , the Subject Search function is most useful. </p>
        <h5 class="icon-search intro">  Category</h5>
        <p class="intro-indent">You can also search books by selecting from a variety of categories. </p>
        <h5 class="icon-heart intro">  My List</h5>
        <p class="intro-indent">If you find any books of interest and  want to keep track of them, you can add them to your list by simply clicking the "Add this to my list" which appears either on the search results or the book details page for each book. Then from the My List page you can later reserve the book or remove it from your list. </p>
        <h5 class="icon-user intro">  Personal Page</h5>
        <p class="intro-indent">This page consists of 3 sections:</p><br>
        <h6 class="intro-indent">-Reservation</h6>
        <p class="intro-indent">You can check the statuses of the books that you have currently reserved. An option for cancelling a reservation is also available.</p><br>
        <h6 class="intro-indent">-Borrowed Books</h6>
        <p class="intro-indent">On this page you can see the books that you are currently borrowing and  monitor the status of each book. an option for extending a due date is also available.</p><br>
        <h6 class="intro-indent">-Profile</h6>
        <p class="intro-indent">General information of your account is listed here including an option to change your password. </p>
        
      </div>
    <?php endif ; ?>
    <?php unset($_SESSION['secret']);?>
  </div>

  <!-- pagination~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
  <div id="pagination-container">
    <?php if ($_SESSION['page'] > 1) : ?>
    <a href="?page=<?php echo ($_SESSION['page'] - 1);?>" class="page-numbers">&laquo; Prev</a>
    <?php endif; ?>

    <?php for($a = 1; $a <= $_SESSION['totalPages'] ; $a++) : ?>
      <?php if($a == $_SESSION['page']) : ?>
      <a href="?page=<?php echo $a;?>" class="page-numbers isActive"><?php echo $a;?></a>
      <?php else : ?>
      <a href="?page=<?php echo $a;?>" class="page-numbers"><?php echo $a;?></a>
      <?php endif ; ?>
    <?php endfor ; ?>

    <?php if ($_SESSION['page'] < $_SESSION['totalPages']) : ?>
    <a href="?page=<?php echo ($_SESSION['page'] + 1);?>" class="page-numbers">Next &raquo;</a>
    <?php endif; ?>
  </div>

  <!-- footer ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
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
             window.location=$(this).find("a").attr("href");
             return false;
        });

      $(".result-semi-container").hover(function(){
        $(this).find(".result-add").show();
      }, function(){
        $(".result-add").hide();
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
      $('a').tooltip()

      $("#toggle a").click(function(){
        $("#mainnav").slideToggle(300);
        return false;
      });
      $(window).resize(function(){
        var win = $(window).width();
        var p = 480;
        if(win > p){
          $("#mainnav").show();
        } else {
          $("#mainnav").hide();
        }
      });
      
    });
  </script>
  <script>
    jQuery(function(){
      
      jQuery('#camera_wrap_1').camera({
                loader : 'bar',
                height: '30%',
                portrait: false,
                alignment: 'Topcenter',
                pagination: false
      });
    });
  </script>
</body>
</html>
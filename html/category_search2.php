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

  $subjects_bsit = array();
  $sql = "select * from class_list where class_group = 'Subject' and BSIT = 1 order by class_name asc";
  foreach($dbh->query($sql) as $row){
    array_push($subjects_bsit,$row);
  }

  $subjects_bscs = array();
  $sql = "select * from class_list where class_group = 'Subject' and BSCS = 1 order by class_name asc";
  foreach($dbh->query($sql) as $row){
    array_push($subjects_bscs,$row);
  }

  $subjects_bsba = array();
  $sql = "select * from class_list where class_group = 'Subject' and BSBA = 1 order by class_name asc";
  foreach($dbh->query($sql) as $row){
    array_push($subjects_bsba,$row);
  }

  $subjects_bsis = array();
  $sql = "select * from class_list where class_group = 'Subject' and BSIS = 1 order by class_name asc";
  foreach($dbh->query($sql) as $row){
    array_push($subjects_bsis,$row);
  }

  $hearts = array();
  $sql = "select * from rec_list where member_id = ".$me['member_id'];
  foreach($dbh->query($sql) as $row){
    array_push($hearts, $row);
  }

  if(preg_match('/^[1-9][0-9]*$/',$_GET['page'])){
    $page = (int)$_GET['page'];
  }else{
    $page = 1;
  }

  
  define('ITEMS_PER_PAGE',15);
  $offset = ITEMS_PER_PAGE * ($page - 1);
  $results = array();
  $sql = "select ISBN, title,  GROUP_CONCAT(book_author.author ORDER BY book_author.author) as Authors, year from book_basic LEFT JOIN book_author USING (ISBN) group by ISBN limit ".$offset.",".ITEMS_PER_PAGE;
  foreach($dbh->query($sql) as $row){
    array_push($results, $row);
  }

  $total = $dbh->query("select count(*) from book_basic")->fetchColumn();
        $totalPages = ceil($total / ITEMS_PER_PAGE);
  
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Category Search</title>

    <!-- Bootstrap -->
    <!-- <link href="styles/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/normalize.css" />
    <link rel="stylesheet" href="styles/flatui.min.css" />
    <link rel="stylesheet" href="styles/magnific-popup.css"> -->
    <link rel = "short icon" href="images/favicon5.ico"/>
    <link href="styles/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/reset.css"/>
    <link rel="stylesheet" href="styles/accordion2.css"/>
    <link rel="stylesheet" href="styles/global.css"/>
    <link rel="stylesheet" href="styles/fonts.css"/>
    <link rel="stylesheet" href="styles/fontello.css"/>
    <link rel="stylesheet" href="styles/backtotop.css"/>
   

    
    <script src="js/jquery-1.11.1.min.js"></script>
    <script src="js/backtotop.js"></script>
    
     
     <!-- <script src="js/bootstrap.min.js"></script>
    // <script src="js/flatui_modernizer.js"></script>
    // <script src="js/flatui_jquery.js"></script>
    // <script src="js/jquery.cookie.js"></script>
    // <script src="js/flatui_foundation.min.js"></script>
    // <script src="js/magnific-popup.min.js"></script> 
    // <script src="bower_components/modernizr/modernizr.js"></script> -->

   
  </head>
  <body>
    
    <header>
      <a href="#" id="logo"><img src="images/height80.png"/></a>
      <span class="hi_member"><?php echo $me['member_firstname']." ".$me['member_lastname'];?></span>
      <nav id="mainnav">  
        <div class="nav-container ">
          <div class="nav-left"><p class="icon-home-big"></p></div>
          <div class="nav-right"><li><a href="Home_login.php">HOME</a></li></div>
        </div> 
        <div class="nav-container ">
          <div class="nav-left "><p class="icon-book-big"></p></div>
          <div class="nav-right two-line"><li><a href="subject_search.php">Subject Search</a></li></div>
        </div>
        <div class="nav-container active">
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

        <div class="panel">
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
            <li><a href="Home_logout.php"><p class="icon-home"></p></a></li>
           <!--  <li><a href="#">Welcome Page</a></li> -->
            <li><a href="category_search.php">Category Search</a></li>
           
          </ul>

        </div> <!-- ** breadcrumbs ** -->
      </div>
      <hr>
      <div class="result-info-container">
        <div class="result-info-box">
          <p><?php echo $offset + 1,"-". ($offset + ITEMS_PER_PAGE)." of ".$total." results found!";?></p>
        </div>
      </div>
      <div style="overflow:hidden">
        <div id="side1">
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
                <div class="result-semi-container2">
                  <a class="title" href="member_book_details.php?isbn=<?php echo $result['ISBN']; ?>"><?php echo $offset + $ctr ?>. <?php echo $result['title'];?></a>
                  <ul>
                    
                    <li>ISBN: <?php echo $result['ISBN']; ?></li>
                    
                    <li>Author: <?php echo $result['Authors']; ?></li>
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
        </div>

        <div id="side2">
         
          <div id="accordion">
            <form> 
              <label>
                <input type="radio" name="btn" />
                <div>
                  <div>BSIT</div>
                  <ul>
                    <?php foreach($subjects_bsit as $subject) : ?>
                    <li><a href="#"><?php echo h($subject['class_name']); ?></a></li>
                    <?php endforeach ; ?>
                  </ul>
                </div>
              </label>
              <label>
                <input type="radio" name="btn" />
                <div>
                  <div>BSCS</div>
                  <ul>
                   <?php foreach($subjects_bscs as $subject) : ?>
                    <li><a href="#"><?php echo h($subject['class_name']); ?></a></li>
                    <?php endforeach ; ?>
                  </ul>
                </div>
              </label>
              <label>
                <input type="radio" name="btn" />
                <div>
                  <div>BSIS</div>
                  <ul>
                    <?php foreach($subjects_bsis as $subject) : ?>
                    <li><a href="#"><?php echo h($subject['class_name']); ?></a></li>
                    <?php endforeach ; ?>
                  </ul>
                </div>
              </label>
              <label>
                <input type="radio" name="btn" />
                <div>
                  <div>BSBA</div>
                  <ul>
                    <?php foreach($subjects_bsba as $subject) : ?>
                    <li><a href="#"><?php echo h($subject['class_name']); ?></a></li>
                    <?php endforeach ; ?>
                  </ul>
                </div>
              </label>
            </form> 
          </div>
        </div>
      </div>

    </div>
    <div id="pagination-container">
      <?php if ($page > 1) : ?>
      <a href="?page=<?php echo $page - 1;?>" class="page-numbers">&laquo; Prev</a>
      <?php endif; ?>
      <?php for($a = 1; $a <= $totalPages ; $a++) : ?>
        <?php if($a == $page) : ?>
        <a href="?page=<?php echo $a;?>" class="page-numbers isActive"><?php echo $a;?></a>
        <?php else : ?>
        <a href="?page=<?php echo $a;?>" class="page-numbers"><?php echo $a;?></a>
        <?php endif ; ?>
      <?php endfor ; ?>
      <?php if ($page < $totalPages) : ?>
      <a href="?page=<?php echo $page + 1;?>" class="page-numbers">Next &raquo;</a>
      <?php endif; ?>
    </div>
    <footer><p>Informatics International College - Cainta Library<br><br>&copy; 2014 all rights reserved</p></footer>

    


  <a href="#0" class="cd-top">Top</a>
  
   <script>
    $(document).ready(function(){

      // var newCrumb = $("<li><a href='#'>welcome page</a></li>");
      // newCrumb.addClass("a b c").insertAfter($("#crumbs ul:last-child"));
  

      // $(".sq").mouseover(function (){
      //     $(this).children().css("color","white");

      //   });
      // $(".sq").mouseleave(function(){
      //   $(this).css("color","rgba(41,41,41,0.85)");
      // });

      //accordion
    $('.accordion dt').click(function() {
        $(this).next('dd').slideToggle();
        $(this).next('dd').siblings('dd').slideUp();
        $(this).toggleClass('open');
        $(this).siblings('dt').removeClass('open');
      });
      //---accordion end

      $(".nav-container").click(function(){
             window.location=$(this).find("a").attr("href");
             return false;
        });

      $(".result-semi-container2").hover(function(){
        $(this).find(".result-add").show();
      }, function(){
        $(".result-add").hide();
      });

      $(".panel-btn").click(function(){
         var clickPanel = $('.panel');
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
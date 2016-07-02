<?php
  
  require_once('php/config.php');
  require_once('php/functions.php');

  session_start();


  if(empty($_SESSION['me'])){
    header("LOCATION: index.php");
    exit;
  }

  $me = $_SESSION['me'];

  $isbn = (string)$_GET['isbn'];
  $dbh = connectDB();

  ///////////////////////////////////HANDLE EXPIRE
  handleExpire($dbh);

  //////////////////////////////////HANDLE DUEDATE
  handleOverdue($dbh);

  $heart_check_count = $dbh->query("select count(*) from rec_list where ISBN = '".$isbn."' and member_id = ".$me['member_id'])->fetchColumn();

  $three_check_count = $dbh->query("select count(*) from book_reserved where not status = 'picked' and not status = 'cancelled' and member_id = ".$me['member_id'])->fetchColumn();

  $one_check_count = $dbh->query("select count(*) from book_reserved where not status = 'picked' and not status = 'cancelled' and member_id = ".$me['member_id']." and ISBN = '".$isbn."'")->fetchColumn();
 

  ////////////////////////////////////////////////////////////////////////////
  $display_reservation = priorityUpdate($isbn, $dbh);
  $count_reservation = $dbh->query("select count(*) from book_reserved where ISBN = '".$isbn."' and not status = 'picked' and not status = 'cancelled'")->fetchColumn();
  ///////////////////////////////////////////////////////////////////////////////
  

  $notifications = getNotification($me['member_id']);

  /////////////////////////////////RECORDS FROM BOOK_BORROWED TABLE
   $sql = "select *, date_format(date_borrowed, '%b %d / %h:%i %p') as borrowdate, date_format(date_due, '%b %d') as duedate from book_borrowed where ISBN = '".$isbn."' and not status = 'returned' order by accession_id";
   $borrowed_info = array();
   foreach($dbh->query($sql) as $row){
      array_push($borrowed_info, $row);
   }

  //for displaying book_basic details
  $sql = "select * from book_basic where ISBN ='".$isbn."' limit 1";
  $stmt = $dbh->query($sql);
  $book = $stmt->fetch();

  //for author
  $sql = "select author from book_author where ISBN = '".$isbn."'";
  $authors = array();
  foreach($dbh->query($sql) as $row){
    array_push($authors, $row);
  }
  //for accession ID
  $sql = "select * from book_each where ISBN ='".$isbn."'";
  $accession_ids = array();
  foreach($dbh->query($sql) as $row){
    array_push($accession_ids, $row);
  }

  $ctr_available = 0;
  $ctr = 0;
  foreach($accession_ids as $accession){
    if(strcmp($accession['availability'],"available") == 0 ){
      $ctr_available++;
    } 
      $ctr++;
  }

  $missing_count = $dbh->query("select count(*) from book_each where missing = 1 and ISBN = '".$isbn."'")->fetchColumn();

  /////////////////////////////////GET CATEGORIES
   $categories = array();
   $sql ="select book_class.*,class_name from book_class left join class_list using (class_id) where class_group = 'Category' and ISBN = '".$isbn."'"; 
    foreach($dbh->query($sql) as $row){
      array_push($categories,$row);
   }

   $subjects = array();
   $sql ="select book_class.*,class_name from book_class left join class_list using (class_id) where class_group = 'Subject' and ISBN = '".$isbn."'"; 
    foreach($dbh->query($sql) as $row){
      array_push($subjects,$row);
   }
  


?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $book['title']; ?></title>

    <!-- Bootstrap -->
    <!-- <link href="styles/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/normalize.css" />
    <link rel="stylesheet" href="styles/flatui.min.css" />
    <link rel="stylesheet" href="styles/magnific-popup.css"> -->
    <link rel = "short icon" href="images/favicon5.ico"/>
    <link rel="stylesheet" href="styles/bootstrap.min.css">
    <link rel="stylesheet" href="styles/reset.css"/>
    <link rel="stylesheet" href="styles/global.css"/>
    <link rel="stylesheet" href="styles/fonts.css"/>
    <link rel="stylesheet" href="styles/fontello.css"/>
    <link rel="stylesheet" href="styles/backtotop.css"/>
    <link rel="stylesheet" href="styles/accordion2.css"/>
   

    <script src="js/jquery-1.11.1.min.js"></script>
    <script src="js/backtotop.js"></script>
     <script src="js/bootstrap.min.js"></script>

     
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
        <div class="nav-container">
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
            <li><a href="<?php echo $_SERVER['HTTP_REFERER']; ?>"><span class="icon-level-up" style="font-size:120%;"></span></a></li>
           <!--  <li><a href="#">Welcome Page</a></li> -->
            <li><a href=""><?php echo $book['title']; ?></a></li>
          </ul>
        </div> <!-- ** breadcrumbs ** -->
      </div>
      <h2 class="general">Book Details</h2><hr>
      <div class="table-responsive">
        <table id="table-details" class="table table-striped table-condensed">

           <tr>
             <th>TITLE</th>
             <td><?php echo $book['title'];?></td>
           </tr>
           <tr>
             <th>ISBN</th>
             <td><?php echo $book['ISBN'];?></td>
           </tr>
           <tr>
             <th>Accession No.</th>
             <td><?php foreach($accession_ids as $accession){
                  echo $accession['accession_id']."&nbsp;&nbsp;&nbsp;";}?>  
             </td>
           </tr>
           <tr>
             <th>Author(s)</th>
             <td> <?php foreach($authors as $author){
                  echo $author['author']."&nbsp;&nbsp;&nbsp;";}?>
             </td>
           </tr>
           <tr>
              <th>Edition</th>
              <td><?php echo $book['edition'];?></td>
            </tr>
           <tr>
             <th>Page</th>
             <td><?php echo $book['pages']." pp.";?></td>
           </tr>
           <tr>
             <th>Publication Date</th>
             <td><?php echo $book['year'];?></td>
           </tr>
           
            <tr>
              <th>Format</th>
              <td><?php echo $book['format'];?></td>
            </tr>
            <tr>
             <th>Description</th>
             <td><?php echo $book['description'];?></td>
           </tr>
           <tr>
              <th>Category</th>
              <td>
                <?php foreach($categories as $category) : ?>
                 <a class="tag" href="category_search.php?class_id=<?php echo $category['class_id'];?>" style="text-decoration:none;"><span class="lbl-cat label label-info"><?php echo $category['class_name']; ?></span></a>
                <?php endforeach ; ?>
              </td>
            </tr>
            <tr>
              <th>Subject</th>
              <td>
                <?php foreach($subjects as $subject) : ?>
                 <a href="subject_search.php?class_id=<?php echo $subject['class_id'];?>" style="text-decoration:none;"><span class="lbl-sub label label-default"><?php echo $subject['class_name']; ?></span></a>
                <?php endforeach ; ?>
              </td>
            </tr>
            <tr>
             <th>Availability</th>
             <td>
                  <?php if ($ctr_available == 0) :?>
                  <p style="color:#D2322D;"><?php echo $ctr_available." / ".$ctr."    (Not available)";?></p>
                  <?php elseif($ctr_available == 1) : ?>
                  <p style="color:#3276B1;"><?php echo $ctr_available." / ".$ctr."    (".$ctr_available." copy is available)";?></p>
                  <?php else : ?>
                  <p style="color:#3276B1;"><?php echo $ctr_available." / ".$ctr."    (".$ctr_available." copies are available)";?></p>
                  <?php endif ; ?>
             </td>
           </tr> 
        </table>
      </div>
      <form action = "reserve.php" method="POST" id="reserve" onsubmit="return confirm('Are you sure you want to reserve this book?')"></form>
      <?php if($me['disabled'] != 0) : ?>
      <input form = "reserve" type = "button" class="details-reserve-btn btn btn-danger" value="Your account is currently disabled" disabled>
      <?php elseif($one_check_count >= 1) : ?>
      <button class="details-reserve-btn btn btn-success" disabled><span class="icon-ok"></span>  You already reserved this book</button>
      <?php elseif($three_check_count >= 3) :?>
      <input form = "reserve" type = "button" class="details-reserve-btn btn btn-danger" value="You cannot reserve more than 3 books at once" disabled>
      <?php elseif($missing_count == $ctr) :?>
      <input form = "reserve" type = "button" class="details-reserve-btn btn btn-default" value="This book is currently unavailable" disabled>
      <?php else : ?>
      <input form = "reserve" type = "submit" name = "submit" class="details-reserve-btn btn btn-primary" value="Reserve this book">
      <?php endif ; ?>
      <input form = "reserve" type ="hidden" name ="isbn" value="<?php echo $book['ISBN'];?>">
      
     <!-- <h3>Status</h3>
     <hr> -->
     <div style="overflow:hidden;">
       <div id="details-left-container">
         <div class="panel panel-default">
          <div class="panel-heading middle" style="color:rgba(100,100,100,.8);">Reservation Status</div>
          <div class="table-responsive">
             <table id="table-details-right" class="table table-bordered table-condensed">
                 <thead>
                   <tr>
                     <th>No.</th>
                     <th>Member Name</th>
                     <th>Member Type</th>
                     <th>Date Reserved</th>
                   </tr>
                 </thead>
                 <tbody>
                  <?php if ($count_reservation >= 1) : ?> 
                    <?php $counter = 1; ?>
                    <?php foreach($display_reservation as $reservation) :?>
                    <?php $stmt = $dbh->prepare("select * from member_basic where member_id = :id");
                          $stmt->execute(array(":id" => $reservation['member_id']));
                          $user = $stmt->fetch();
                    ?>
                    <tr <?php if($reservation['status'] == 'available') echo "class='bg-success' style='color:#5cb85c;'"; ?>>
                      <td><?php echo $counter; ?></td>
                      <td><?php echo $user['member_firstname']." ".$user['member_lastname'];?></td>
                      <td><?php echo $user['member_type'];?></td>
                      <td><?php echo $reservation['time'];?></td>
                    </tr>
                    <?php $counter++; ?>
                    <?php endforeach ; ?>
                  <?php else : ?>
                    <tr><td colspan="4" style="text-align:center; color:rgb(200,200,200); font-size:95%;">No person has currently reserved the book</td></tr>
                  <?php endif ; ?>
                 </tbody>
             </table>
           </div>
         </div>
       </div>

       <div id="details-right-container">
        <div class="panel panel-default">
          <div class="panel-heading middle" style="color:rgba(100,100,100,.8);">Borrower Status</div>
          <div class="table-responsive">
             <table id="table-details-left" class="table table-bordered table-condensed">
               <thead>
                 <tr>
                   <th>Accession No.</th>
                   <th>Member Name</th>
                   <th>Date Borrowed</th>
                   <th>Due Date</th>
                   <th>Status</th>
                 </tr>
               </thead>
               <tbody>
                 <?php foreach($accession_ids as $accession) :?>
                  <?php $counter = 0; ?>

                    <?php foreach($borrowed_info as $borrowed) : ?>


                      <?php if($borrowed['accession_id'] == $accession['accession_id']) : ?>

                        <?php $member_name = $dbh->query("select member_firstname from member_basic where member_id = '".$borrowed['member_id']."'")->fetchColumn(); ?>
                        <tr <?php if($borrowed['status'] == 'overdue') echo "class='red'";?>>
                          <td><?php echo $accession['accession_id']; ?></td>
                          <td><?php echo $member_name; ?></td>
                          <td><?php echo $borrowed['borrowdate']; ?></td>
                          <td><?php echo $borrowed['duedate']; ?></td>
                          <td><?php echo $borrowed['status']; ?></td>
                        </tr>
                        <?php $counter++; ?>

                      <?php endif ; ?>

                    <?php endforeach ; ?>

                    <?php if($counter == 0) : ?>

                      <tr>
                        <td><?php echo $accession['accession_id']; ?></td>
                        <?php if($accession['missing'] == 0) : ?>
                        <td colspan="5" style="text-align:center;  color:rgb(200,200,200); font-size:95%;">No borrower</td>
                        <?php else : ?>
                        <td colspan="5" style="text-align:center;  color:rgb(200,200,200); font-size:95%;">This book is missing</td>
                        <?php endif ; ?>
                        
                      </tr>
                    <?php endif ; ?>


                  <?php endforeach ; ?>
               </tbody>     
             </table>
           </div>
          </div>
        </div>
     </div><!-- #overflow hidden -->
     <hr>
     <?php if($heart_check_count == 0) : ?>
     <a href="add_mylist.php?isbn=<?php echo $isbn;?>" class="icon-heart add-mylist"><span class="add-this-on-your-list">Add this on your list</span></a>
     <?php else : ?>
     <a href="my_list.php" class="icon-heart add-mylist-disabled" data-toggle="tooltip" data-placement="top" title="Go to My List page"><span class="add-this-on-your-list">This book is on your list</span></a>
     <?php endif ; ?>
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

  });

    $('a').tooltip()

  </script>
  </body>
</html>
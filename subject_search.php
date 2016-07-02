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

if(isset($_GET['clear'])){
  destroySearchSession ();
} 

/////////////////////////////////GET SUBJECTS
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
////////////////////////////////
define('ITEMS_PER_PAGE',10);

if(isset($_GET['class_id'])){
	// checkToken();

  $_SESSION['class_id'] = $_GET['class_id'];
  $_SESSION['subject_name'] = $dbh->query("select class_name from class_list where class_id = ".$_GET['class_id'])->fetchColumn();

  $_SESSION['page'] = 1;

  $offset = ITEMS_PER_PAGE * ($_SESSION['page'] - 1);

  $results = array();
  $sql = "select book_class_id, class_id, a.ISBN, title, year from book_class a, book_basic b where a.ISBN = b.ISBN and class_id = ".$_SESSION['class_id']." limit ".$offset.",".ITEMS_PER_PAGE;
 
  foreach($dbh->query($sql) as $row){
  array_push($results, $row);
  }
  $_SESSION['total'] = $dbh->query("select count(*) from book_class a, book_basic b where a.ISBN = b.ISBN and class_id = ".$_SESSION['class_id'])->fetchColumn();
   
  $_SESSION['results'] = $results;

  $_SESSION['totalPages'] = ceil($_SESSION['total'] / ITEMS_PER_PAGE);
  
} 

if(isset($_GET['page'])){

  if(preg_match('/^[1-9][0-9]*$/',$_GET['page'])){ //
    $_SESSION['page'] = (int)$_GET['page'];                    // GETTING CURRENT PAGE, GET THE VALUE FROM URL FROM $_GET['page']
  }else{                                           //
    $_SESSION['page'] = 1;                                     //
  }

  $offset = ITEMS_PER_PAGE * ($_SESSION['page'] - 1);

   $results = array();
  $sql = "select book_class_id, class_id, a.ISBN, title, year from book_class a, book_basic b where a.ISBN = b.ISBN and class_id = ".$_SESSION['class_id']." limit ".$offset.",".ITEMS_PER_PAGE;

  foreach($dbh->query($sql) as $row){
  array_push($results, $row);
  }

  $_SESSION['sql'] = $sql;  //tempo

  $_SESSION['results'] = $results;
  
}

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
<title>Subject Search</title>

<!-- Bootstrap -->
<!-- <link href="styles/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="styles/normalize.css" />
<link rel="stylesheet" href="styles/flatui.min.css" />
<link rel="stylesheet" href="styles/magnific-popup.css"> -->
<link rel='stylesheet' type='text/css' href='http://fonts.googleapis.com/css?family=PT+Sans:400,700' >
<link rel="stylesheet" href="styles/bootstrap.min.css">
<link rel="stylesheet" href="styles/reset.css"/>
<link rel="stylesheet" href="styles/global.css"/>
<link rel="stylesheet" href="styles/fonts.css"/>
<link rel="stylesheet" href="styles/fontello.css"/>
<link rel="stylesheet" href="styles/backtotop.css"/>
<link rel="short icon" href="images/favicon5.ico"/>

<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/backtotop.js"></script>

</head>
<body>
<?php if($me['disabled'] == 1) : ?>
<div class="alert alert-danger alert-dismissable">
<strong>I'm sorry, your account is currently disabled! Your account needs to be enabled by the admin in order to be able to reserve books.</strong>
</div>
<?php endif ; ?>
<header>
	<a href="home_login.php" id="logo"><img src="images/height80.png"/></a>
	<span class="hi_member"><?php echo $me['member_firstname']." ".$me['member_lastname'];?></span>
	<nav id="mainnav">  
		<div class="nav-container ">
			<div class="nav-left"><p class="icon-home-big"></p></div>
			<div class="nav-right"><li><a href="home_login.php">HOME</a></li></div>
		</div> 
		<div class="nav-container active">
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
<div id="superwrapper">
  <div id="upper-wrapper">
    <div style="position:relative;">
      <div id="crumbs_container" class="margin20">
        <div id="crumbs">
          <ul>
            <li><a href="home_login.php"><p class="icon-home"></p></a></li>
           <!--  <li><a href="#">Welcome Page</a></li> -->
            <li><a href="subject_search.php">Subject Search   <span class="icon-search"></span></a></li>
            <?php if(isset($_SESSION['subject_name'])) : ?>
            <li><a href=""><?php echo $_SESSION['subject_name'];?></a></li>
            <?php endif; ?>
          </ul>
        </div> <!-- ** breadcrumbs ** -->
      </div>
      <a href="subject_search.php?clear=0" class="btn-refresh btn btn-default btn-sm" data-toggle="tooltip" data-placement="left" title="Clear Search"><span class="icon-arrows-cw" style="color:rgba(100,100,100,0.8);"></span></a>
    </div>
  </div>

  <div id="sidebar">
    <h4 class="icon-pencil" style="margin-left:-10px;">Subject</h4>
    <hr>
    
    <div class="panel-group" id="accordion">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
              BSIT
            </a>
          </h4>
        </div>
        <div id="collapseOne" class="panel-collapse collapse">
          <div class="panel-body">
            <ul>
              <?php foreach($subjects_bsit as $bsit) : ?>
              <?php $count_subject = $dbh->query("select count(*) from book_class where class_id = ".$bsit['class_id'])->fetchColumn(); ?>
              <li class="category-list"><a class="category-link" href="subject_search.php?class_id=<?php echo $bsit['class_id'];?>"><?php echo $bsit['class_name'];?>&nbsp;&nbsp;&nbsp;<span class="category-badge"><?php echo $count_subject; ?></span>
              </a></li>
              <?php endforeach ; ?>
            </ul>
          </div>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
              BSCS
            </a>
          </h4>
        </div>
        <div id="collapseTwo" class="panel-collapse collapse">
          <div class="panel-body">
            <ul>
              <?php foreach($subjects_bscs as $bscs) : ?>
              <?php $count_subject = $dbh->query("select count(*) from book_class where class_id = ".$bscs['class_id'])->fetchColumn(); ?>
              <li class="category-list"><a class="category-link" href="subject_search.php?class_id=<?php echo $bscs['class_id'];?>"><?php echo $bscs['class_name'];?>&nbsp;&nbsp;&nbsp;<span class="category-badge"><?php echo $count_subject; ?></span>
              </a></li>
              <?php endforeach ; ?>
            </ul>
          </div>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">
              BSIS
            </a>
          </h4>
        </div>
        <div id="collapseThree" class="panel-collapse collapse">
          <div class="panel-body">
             <ul>
              <?php foreach($subjects_bsis as $bsis) : ?>
              <?php $count_subject = $dbh->query("select count(*) from book_class where class_id = ".$bsis['class_id'])->fetchColumn(); ?>
              <li class="category-list"><a class="category-link" href="subject_search.php?class_id=<?php echo $bsis['class_id'];?>"><?php echo $bsis['class_name'];?>&nbsp;&nbsp;&nbsp;<span class="category-badge"><?php echo $count_subject; ?></span>
              </a></li>
              <?php endforeach ; ?>
            </ul>
          </div>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapseFour">
              BSBA
            </a>
          </h4>
        </div>
        <div id="collapseFour" class="panel-collapse collapse">
          <div class="panel-body">
             <ul>
              <?php foreach($subjects_bsba as $bsba) : ?>
              <?php $count_subject = $dbh->query("select count(*) from book_class where class_id = ".$bsba['class_id'])->fetchColumn(); ?>
              <li class="category-list"><a class="category-link" href="subject_search.php?class_id=<?php echo $bsba['class_id'];?>"><?php echo $bsba['class_name'];?>&nbsp;&nbsp;&nbsp;<span class="category-badge"><?php echo $count_subject; ?></span>
              </a></li>
              <?php endforeach ; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="main">

    <?php if(isset($_GET['class_id'])|| isset($_GET['page']) ||isset($_SESSION['secret'])) : ?>


      <!-- result info container ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
      <div class="result-info-container-cs">
        <div class="result-info-box-cs">
          <?php $to = ($offset + ITEMS_PER_PAGE) < $_SESSION['total'] ? ($offset + ITEMS_PER_PAGE) : $_SESSION['total']; ?>
          <?php if($_SESSION['total'] >= ITEMS_PER_PAGE) :?>
            <p><?php echo $offset + 1,"-".$to." of ".$_SESSION['total']." results found for the subject '".$_SESSION['subject_name']."'";?></p>
          <?php elseif($_SESSION['total'] < ITEMS_PER_PAGE && $_SESSION['total'] != 0) : ?>
           <p><?php echo $offset + 1,"-". ($_SESSION['total'])." of ".$_SESSION['total']." results found for the subject '".$_SESSION['subject_name']."'";?></p>
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

        $authors = $dbh->query("select group_concat(author) from book_author where ISBN = '".$isbn."' group by ISBN ORDER BY `ISBN` ASC  ")->fetchColumn();
        ?>
        <div class="result-semi-container2">
          <a class="title" href="member_book_details.php?isbn=<?php echo $result['ISBN']; ?>"><?php echo $offset + $ctr ?>. <?php echo $result['title'];?></a>
          <ul>
            <li>ISBN: <?php echo $result['ISBN']; ?></li>
            <li>Author: <?php echo $authors; ?></li>
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
      <h2 class="general icon-search"> Subject Search</h2><hr>
      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean ac lacus in odio pretium venenatis a in diam. Aliquam eget vehicula mauris, sed condimentum orci. Sed commodo et justo eu aliquet. Integer et pulvinar odio, ac pulvinar nisi. Donec luctus interdum purus, at pretium diam porttitor non. Aliquam non risus sed nunc vestibulum elementum nec accumsan orci. Morbi ultrices tortor quam, sit amet blandit neque eleifend ac. Maecenas pretium bibendum dolor, a adipiscing eros sollicitudin sit amet. Nullam pretium gravida aliquet. Aenean nulla felis, faucibus quis iaculis et, rhoncus faucibus ligula. Sed tristique placerat velit, non auctor lorem hendrerit ut. In ornare turpis ac diam dictum facilisis.</p>

      <br> <p>Cras bibendum, dolor lacinia accumsan faucibus, est felis facilisis diam, faucibus auctor eros mauris sed erat. Nullam tempor lectus ut gravida consequat. Etiam facilisis dolor sed risus sagittis porta. Pellentesque consectetur varius porttitor. Nam diam urna, scelerisque non condimentum eget, ultrices nec dui. Morbi ornare ac est eget ultricies. Nulla non eleifend purus, vitae venenatis lorem. Phasellus tincidunt a est tempor pellentesque. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Suspendisse sapien enim, feugiat id nisl vel, viverra posuere justo. Fusce eros turpis, vestibulum ut lorem vel, suscipit hendrerit risus. Nullam tempus massa ac neque auctor, eu imperdiet sapien bibendum. Proin a lectus ac orci tincidunt vestibulum non eget ipsum. Nullam volutpat vehicula suscipit. Vivamus sem mauris, ullamcorper rhoncus est in, malesuada malesuada sem. Donec id fringilla turpis.</p>

      <br><p>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Duis vehicula semper viverra. Curabitur malesuada faucibus tincidunt. Curabitur fringilla justo id quam facilisis luctus. Maecenas felis neque, suscipit at aliquet nec, congue ut nunc. Vestibulum tincidunt massa sed mi sagittis, vitae ultricies lorem molestie. Maecenas malesuada faucibus libero, eget venenatis nisl varius a. Donec suscipit ligula quis pretium egestas. Ut nec sollicitudin mi, nec lacinia lacus. Nulla sed arcu ultrices, eleifend magna ac, adipiscing diam. Maecenas a varius odio, non volutpat nisl. Etiam volutpat mi urna, sed ullamcorper nunc pellentesque sit amet. Maecenas ac magna neque.</p>
      
      <?php endif ; ?>
      <?php unset($_SESSION['secret']);?>
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

  </div>
 
</div>



  <!-- footer ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
  <footer><p>Informatics International College - Cainta Library<br><br>&copy; 2014 all rights reserved</p></footer>

  <a href="#0" class="cd-top">Top</a>
  
  <script>
    $(document).ready(function(){
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

    });
  </script>
</body>
</html>
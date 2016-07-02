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
 
  $notifications = getNotification($me['member_id']);

  function passwordExists($current_pw, $dbh){
  $sql = "select * from member_basic where password = :pw limit 1";
  $stmt = $dbh->prepare($sql);
  $stmt->execute(array(":pw" => $current_pw));
  $passcheck = $stmt->fetch();
  return $passcheck ? $passcheck : false;
 }

 if($_SERVER['REQUEST_METHOD'] != 'POST'){
  //For CSRF
  setToken();
 } else {

  checkToken();

  $current_pw = $_POST['current'];
  $new_pw = $_POST['new'];
  $retype_new_pw = $_POST['retype'];
  $secret_question = $_POST['secret_question'];
  $secret_answer = mysql_real_escape_string($_POST['secret_answer']);

  $error = array();


  if(!passwordExists($current_pw, $dbh)){
    $error['password'] = "wrong password!";
  }

  if($new_pw != $retype_new_pw){
    $error['wrong_pw'] = "new password didn't match!";
  }
  
 
  if(empty($error)){

    $stmt = $dbh->prepare("update member_basic set password = :pw, password_updated = now(), secret_question = :secret, secret_answer = :answer
                            where member_id = :id");
    $stmt->execute(array(":pw" => $new_pw, ":secret" => $secret_question, ":answer" => $secret_answer, ":id" => $me['member_id']));

    $rowCount = $stmt->rowCount();
    $_SESSION['tempo_message'] = $rowCount == 1 ? 1 : 3;
    
    $_SESSION['me']['password'] = $new_pw; // updating password to the Session
    $_SESSION['me']['secret_question'] = $_POST['secret_question'];
    $_SESSION['me']['secret_answer'] = $_POST['secret_answer'];


    if($_SESSION['tempo_message'] == 1){

      header('LOCATION: personal_page_profile.php');
      exit;

    }
    
  }

 }

  
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
    <?php if(isset($_SESSION['tempo_message'])) : ?>
      <div class="alert-pw-update-danger alert alert-danger alert-dismissible" role="alert">
        <strong>Process failed! please make sure your information is valid</strong> 
      </div>
    <?php endif ; ?>
    <?php unset($_SESSION['tempo_message']); ?>
    <div id="crumbs_container" class="margin20">
      <div id="crumbs">
        <ul>
          <li><a href="home_login.php"><p class="icon-home"></p></a></li>
         <!--  <li><a href="#">Welcome Page</a></li> -->
          <li><a href="personal_page_profile.php">Personal Page (Profile)</a></li>
           <li><a href="">Change Password</a></li>
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
      <h4 class="icon-lock">  Change Password</h4>
      <form action="" method="post" name="form_change_password" class="form-horizontal form-change-pw">
        <table class="table" >
          <tr>
            <th>Current</th>
            <td><input type="password" name="current" class="form-control" placeholder="Enter your current password" value="<?php echo $me['password'];?>" required><span style="color:#D2322D"><?php echo $error['password'];?></span>
            </td>
          </tr>
          <tr>
            <th>New</th>
            <td><input type="password" name="new" class="form-control" placeholder="Enter your new password" value="" required></td>
          </tr>
          <tr>
            <th>Re-type New</th>
            <td><input type="password" name="retype" class="form-control" placeholder="Re-enter your new password" value="" required>
              <span style="color:#D2322D"><?php echo $error['wrong_pw'];?></td>
          </tr>
          <tr>
            <th>Secret Question</th>
            <td><input type="text" name="secret_question" class="form-control" placeholder="Enter your secret question" value="<?php echo h($me['secret_question']);?>" required></td>
          </tr>
          <tr>
            <th>Secret Answer</th>
            <td><input type="text" name="secret_answer" class="form-control" placeholder="Enter your secret answer" value="<?php echo $me['secret_answer'];?>" required></td>
          </tr>
          <tr>
            <th></th>
            <td><input type="submit" name="submit_change_pw" class="form-control btn btn-success" value="Save Changes" style="width:70%">
                <a type="button" href="<?php echo $_SERVER['HTTP_REFERER'];?>" class="btn btn-default" style="width:25%">Cancel</a>
            </td>
          </tr>
        </table>
        <input type="hidden" name="token" value = "<?php echo h($_SESSION['token']); ?>">
      </form>
      <p class="change-pw-caution">* Secret question and secret answers will be used for retriving your new password in case you forget.</p>
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

      $('.alert-pw-update-danger').fadeOut(4000);
      
    });
  </script>
  </body>
</html>
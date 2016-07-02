<?php
  
  require_once('php/config.php');
  require_once('php/functions.php');

  session_start();

  
  $isbn = $_GET['isbn'];
  $dbh = connectDB();


  $sql = "select * from book_basic where ISBN =".$isbn." limit 1";
  $stmt = $dbh->query($sql);
  $book = $stmt->fetch();

  $sql = "select author from book_author where ISBN =".$isbn;
  $authors = array();
  foreach($dbh->query($sql) as $row){
    array_push($authors, $row);
  }

  $sql = "select * from book_each where ISBN =".$isbn;
  $accession_ids = array();
  foreach($dbh->query($sql) as $row){
    array_push($accession_ids, $row);
  }

  function usernameExists($username, $dbh){
    $sql = "select * from member_basic where member_id = :id limit 1";
    $stmt = $dbh->prepare($sql);
    $stmt->execute(array(":id" => $username));
    $user = $stmt->fetch();
    return $user ? $user : false;
  }

  function getUser($username, $password, $dbh){
    $sql = "select * from member_basic where member_id = :id and password = :pw limit 1";
    $stmt = $dbh->prepare($sql);
    $stmt->execute(array(
      ":id" => $username,
      // ":pw" => getSha1Password($password)
        ":pw" => $password
      
      ));
    $user = $stmt->fetch();
    return $user ? $user : false;

  }

  if(isset($_POST['loginsubmit'])){
    //For CSRF
    // setToken();
  }

  else{
    // checkToken();

    $username = $_POST['username'];
    $password = $_POST['password'];

    $dbh = connectDB();
    $error = array();

    if($username == ''){
      $error['username'] = "Please enter your username";
    }
    elseif(!usernameExists($username, $dbh)){
      $error['username'] = "Username didn't match";
      header("LOCATION: wrong_password.php");
      exit;
    }

    if($password == ''){
      $error['password'] = "Please enter your password";
    }
    elseif(!$me = getUser($username, $password, $dbh)){
      $error['password'] = "your username and password is wrong";
      header("LOCATION: wrong_password.php");
      exit;
    }
  
    if(empty($error)){
      session_regenerate_id(true);
      $_SESSION['me'] = $me;

      if(isset($_POST['rememberme'])){
        setcookie('username',$username,time()+60*60*24*14);
        setcookie('password',$password, time()+60*60*24*14); 
      }else{
        setcookie('username','',time()-10);
        setcookie('password','',time()-10);
      }

      if($me['member_type'] == "Admin"){
        header('LOCATION: home_admin.php');
        exit;
      }
      else{
      header('LOCATION: home_login.php');
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
    <title>Book Details</title>

    <!-- Bootstrap -->
    <!-- <link href="styles/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/normalize.css" />
    <link rel="stylesheet" href="styles/flatui.min.css" />
    <link rel="stylesheet" href="styles/magnific-popup.css"> -->
    <link rel = "short icon" href="images/favicon5.ico"/>
    <link rel="stylesheet" href="styles/bootstrap.min.css">
    <link rel="stylesheet" href="styles/reset.css"/>
    <link rel="stylesheet" href="styles/fonts.css"/>
    <link rel="stylesheet" href="styles/global.css"/>
    <link rel="stylesheet" href="styles/fontello.css"/>
    <link rel="stylesheet" href="styles/backtotop.css"/>
    <link rel="stylesheet" href="styles/login_form.css"/>
   
    <script src="js/jquery-1.11.1.min.js"></script>
    <script src="js/backtotop.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/login_form.js"></script>

   
  </head>
  <body>
   <header role="banner">
     <a href="index.php" id="logo"><img src="images/height80.png"/></a>
     <nav class="main-nav">
       <ul>          
         <li><a class="cd-signin" href="#0">Sign in</a></li>
       </ul>
     </nav>
   </header>
    

    <div id="wrapper">
      <h2 class="general">Book Details</h2><hr>
      <table id="table-details" class="table table-striped table-bordered table-condensed">
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
           <th>author(s)</th>
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
           <th>Description</th>
           <td><?php echo $book['description'];?></td>
         </tr>
         <tr>
           <th>Availability</th>
           <td><?php 
                $ctr_available = 0;
                $ctr = 0;
                foreach($accession_ids as $accession){
                  if(strcmp($accession['availability'],"available") == 0 ){
                    $ctr_available++;
                  } 
                    $ctr++;
                }?>
                <p style="color:#3276B1;"><?php echo $ctr_available." / ".$ctr."    (".$ctr_available." copies are available)";?></p>
           </td>
         </tr>
      </table>
      
     <!-- <h3>Status</h3>
     <hr> -->
     <div style="overflow:hidden;">
       <div id="details-left-container">
         <table id="table-details-left" class="table table-striped table-bordered table-condensed">
           <thead>
             <tr><th colspan = "5" style="text-align:center;">Borrowed Status</th></tr>
             <tr>
               <th>Accession No.</th>
               <th>Member Name</th>
               <th>Date Borrowed</th>
               <th>Due Date</th>
               <th>Over Due</th>
             </tr>
           </thead>
           <tbody>
             <?php foreach($accession_ids as $accession) :?>
             <tr>
               <td><?php echo $accession['accession_id']; ?></td>
               <td colspan="4" style="text-align:center;  color:#D2322D;">Not borrowed</td>
               <!-- <td></td>
               <td></td>
               <td></td> -->
             </tr>
             <?php endforeach ; ?>
           </tbody>     
         </table>
       </div>

       <div id="details-right-container">
         <table id="table-details-right" class="table table-striped table-bordered table-condensed">
             <thead>
               <tr><th colspan = "5" style="text-align:center;">Reservation Status</th></tr>
               <tr>
                 <th>Member Name</th>
                 <th>Member Type</th>
                 <th>Date Reserved</th>
               </tr>
             </thead>
             <tbody>
                 <tr><td colspan="3" style="text-align:center; color:#D2322D;">No person has currently reserved the book</td></tr>
             </tbody>
         </table>
       </div>
     </div><!-- #overflow hidden -->
    </div>

    <footer><p>Informatics International College - Cainta Library<br><br>&copy; 2014 all rights reserved</p></footer>

    <div class="cd-user-modal"> <!-- this is the entire modal form, including the background -->
      <div class="cd-user-modal-container"> <!-- this is the container wrapper -->
        <ul class="cd-switcher">
          <li><a href="#0">Sign in</a></li>
        </ul>
        <div id="cd-login"> <!-- log in form -->
          <form action="" method="POST" class="cd-form">
            <p class="fieldset">
              <input name="username" value="<?php echo h($_COOKIE['username']); ?>"class="full-width has-padding has-border"  type="text" placeholder="Username">
            </p>
            <p class="fieldset">
              <input name="password" value="<?php echo h($_COOKIE['password']); ?>" class="full-width has-padding has-border"  type="password"  placeholder="Password">
            </p>
            <p class="fieldset">
              <input type="checkbox" name = "rememberme" id="remember-me" checked>
              <label for="remember-me">remember me</label>
            </p>
            <input type="hidden" name="token" value = "<?php echo h($_SESSION['token']); ?>">
            <p class="fieldset">
              <input class="full-width" type="submit" value="Login">
            </p>
          </form>
          <p class="cd-form-bottom-message"><a href="forgotten_password.php">Forgotten password?</a></p>
          <a href="#0" class="cd-close-form">Close</a>
        </div> <!-- cd-login -->
      </div> <!-- cd-user-modal-container -->
    </div>    


  <a href="#0" class="cd-top">Top</a>
  
   <script>
    $(document).ready(function(){

     $form_login.find('input[type="submit"]').on('click', function(event){
       event.preventDefault();
       $form_login.find('input[type="email"]').toggleClass('has-error').next('span').toggleClass('is-visible');
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

      
    });
    </script>
  </body>
</html>
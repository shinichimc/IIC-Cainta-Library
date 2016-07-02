<?php

  require_once('php/config.php');
  require_once('php/functions.php');
  require_once('php/sqlFunctions.php');

  if (isset($_GET['kesu'])) {
    require_once('clearsearch.php');
  }

  session_start();

  if (isset($_SESSION['me'])) {
    header('Location: Home_login.php');
    exit;
  }

  $dbh = connectDB();

  define('ITEMS_PER_PAGE',10);

  handleExpire($dbh);

  handleOverdue($dbh);

  if (isset($_POST['searchsubmit'])) {

    $_SESSION['sel'] = $_POST['searchby'];
    $_SESSION['primary_search'] = $_POST['primary_search'];

    $_SESSION['page'] = 1;

    $offset = ITEMS_PER_PAGE * ($_SESSION['page'] - 1);

    $results = array();

    if ($_SESSION['sel'] == 'title') {

      $sql = sqlSearchByTitle($_SESSION['primary_search'], $offset, ITEMS_PER_PAGE);
      $_SESSION['total'] = $dbh->query("select count(*) from book_basic where title like '%".$_SESSION['primary_search']."%'")->fetchColumn();
    }
    elseif ($_SESSION['sel'] == 'year') {
      $sql = sqlSearchByYear($_SESSION['primary_search'], $offset, ITEMS_PER_PAGE);
      $_SESSION['total'] = $dbh->query("select count(*) from (select ISBN, title, GROUP_CONCAT(book_author.author ORDER BY book_author.author) as Authors, year from book_basic LEFT JOIN book_author USING (ISBN) where year like '%".$_SESSION['primary_search']."%' group by ISBN) as count")->fetchColumn();
    }
    else {
     $sql = sqlSearchByAuthor($_SESSION['primary_search'], $offset, ITEMS_PER_PAGE);
     $_SESSION['total'] = $dbh->query("select count(*) from (select ISBN, title, GROUP_CONCAT(book_author.author ORDER BY book_author.author) from book_basic LEFT JOIN book_author USING (ISBN) group by ISBN having GROUP_CONCAT(book_author.author ORDER BY book_author.author) like '%".$_SESSION['primary_search']."%') as count")->fetchColumn();
    }
    foreach ($dbh->query($sql) as $row) {
      array_push($results, $row);
    }

    $_SESSION['sql'] = $sql;

    $_SESSION['results'] = $results;


    $_SESSION['totalPages'] = ceil($_SESSION['total'] / ITEMS_PER_PAGE);

  }

  if (isset($_GET['page'])) {

    if (preg_match('/^[1-9][0-9]*$/',$_GET['page'])) {
      $_SESSION['page'] = (int) $_GET['page'];
    }
    else {
      $_SESSION['page'] = 1;
    }

    $offset = ITEMS_PER_PAGE * ($_SESSION['page'] - 1);

    $results = array();

    if ($_SESSION['sel'] == 'title') {
      $sql = "select ISBN, title,  GROUP_CONCAT(book_author.author ORDER BY book_author.author) as Authors, year from book_basic LEFT JOIN book_author USING (ISBN) where title like '%".$_SESSION['primary_search']."%' group by ISBN limit ".$offset.",".ITEMS_PER_PAGE;
    }
    elseif ($_SESSION['sel'] == 'year') {
      $sql = "select ISBN, title,  GROUP_CONCAT(book_author.author ORDER BY book_author.author) as Authors, year from book_basic LEFT JOIN book_author USING (ISBN) where year like '%".$_SESSION['primary_search']."%' group by ISBN limit ".$offset.",".ITEMS_PER_PAGE;
    }
    else {
      $sql = "select ISBN, title,  GROUP_CONCAT(book_author.author ORDER BY book_author.author) as Authors, year from book_basic LEFT JOIN book_author USING (ISBN) group by ISBN having Authors like '%".$_SESSION['primary_search']."%' limit ".$offset.",".ITEMS_PER_PAGE;
    }

    foreach($dbh->query($sql) as $row){
      array_push($results, $row);
    }

    $_SESSION['sql'] = $sql;  //tempo

    $_SESSION['results'] = $results;

  }

  if(!isset($_POST['loginsubmit'])){

    //For CSRF
    setToken();

  }

  else{

    checkToken();

    $username = $_POST['username'];
    $password = $_POST['password'];

    $dbh = connectDB();
    $error = array();

    if ($username == '') {
      $error['username'] = "Please enter your username";
    }
    elseif (!usernameExists($username, $dbh)) {
      $error['username'] = "Username didn't match";
      header("LOCATION: wrong_password.php");
      exit;
    }

    if ($password == '') {
      $error['password'] = "Please enter your password";
    }
    elseif (!$me = getUser($username, $password, $dbh)) {
      $error['password'] = "your username and password is wrong";
      header("LOCATION: wrong_password.php");
      exit;
    }

    if (empty($error)) {

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
<title>Welcome Page</title>
<!-- Bootstrap -->
<!-- <link href="styles/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="styles/normalize.css" />
<link rel="stylesheet" href="styles/flatui.min.css" />
<link rel="stylesheet" href="styles/magnific-popup.css"> -->
<link rel = "short icon" href="images/favicon5.ico"/>
<link rel="stylesheet" href="styles/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="styles/reset.css"/>
<link rel="stylesheet" href="styles/fonts.css"/>
<link rel='stylesheet' id='camera-css' href='styles/camera.css' type='text/css' media='all'>
<link rel="stylesheet" href="styles/fontello.css"/>
<link rel="stylesheet" href="styles/backtotop.css"/>
<link rel="stylesheet" href="styles/login_form.css"/>
<link rel="stylesheet" href="styles/global.css"/>


</head>
<body>

  <header role="banner">
    <a href="index.php?kesu" id="logo"><img src="images/height80.png"/></a>
    <nav class="main-nav">
      <ul>
        <li><a class="cd-signin" href="#0" style="text-decoration:none;">Sign in  <span class="icon-login"></span></a></li>
      </ul>
    </nav>

  </header>

  <div id="wrapper">
      <div id="crumbs_container" class="margin20">
        <div id="crumbs">
          <ul>
            <li><a href="index.php?kesu"><p class="icon-home"></p></a></li>
           <!--  <li><a href="#">Welcome Page</a></li> -->
            <li><a href="home_login.php?kesu">Basic Search <span class="icon-search"></span></a></li>
            <?php if(isset($_SESSION['primary_search'])) : ?>
            <li><a href=""><?php echo $_SESSION['primary_search'];?></a></li>
            <?php endif; ?>
          </ul>
        </div> <!-- ** breadcrumbs ** -->
      </div>
      <hr>
      <!-- form ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
      <form class="search-box-form form-inline" action="index.php" method="POST">
        <select class="form-control" name="searchby">
          <option value="title" <?php if($_SESSION['sel']=='title') echo 'selected';?>>Title</option>
          <option value="author" <?php if($_SESSION['sel']=='author') echo 'selected';?>>Author</option>
          <option value="year" <?php if($_SESSION['sel']=='year') echo 'selected';?>>Year</option>
        </select>
        <div class="input-group search-box">
          <input class="form-control input-one" type="text" name="primary_search" placeholder=" Basic Search" value="<?php echo $_SESSION['primary_search'];?>" required autofocus>
          <span class="input-group-btn">
            <input class="btn btn-default" name="searchsubmit" type="submit" value="Go!">
          </span>
        </div>
        <!-- <input type="hidden" name="token" value = "<?php echo h($_SESSION['token']); ?>"> -->
      </form>
      <hr>

      <?php if(isset($_POST['searchsubmit'])|| isset($_GET['page']) ||isset($_SESSION['secret'])) : ?>

      <!-- result info container ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
      <div class="result-info-container">
        <div class="result-info-box">
          <?php $to = ($offset + ITEMS_PER_PAGE) < $_SESSION['total'] ? ($offset + ITEMS_PER_PAGE) : $_SESSION['total']; ?>
          <?php if($_SESSION['total'] >= ITEMS_PER_PAGE) :?>
            <p><?php echo $offset + 1,"-".$to." of ".$_SESSION['total']." results found!";?></p>
          <?php elseif($_SESSION['total'] < ITEMS_PER_PAGE && $_SESSION['total'] != 0) : ?>
           <p><?php echo $offset + 1,"-". ($_SESSION['total'])." of ".$_SESSION['total']." results found!";?></p>
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
          <a class="title" href="nonmember_book_details.php?isbn=<?php echo $result['ISBN']; ?>"><?php echo $offset + $ctr ?>. <?php echo $result['title'];?></a>
          <ul>
            <li>ISBN: <?php echo $result['ISBN']; ?></li>
            <li>Author: <?php echo $result['Authors']; ?></li>
            <li>Publication Year: <?php echo $result['year']; ?></li>
            <li>Availability: <?php echo $available['c2']." / ".$available['c1']; ?></li>
          </ul>
        </div>
        <?php $ctr++;?>
        <?php endforeach; ?>
      </div>
      <?php else : ?>

      <h2 class="general">This is a top page </h2><br>
      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean ac lacus in odio pretium venenatis a in diam. Aliquam eget vehicula mauris, sed condimentum orci. Sed commodo et justo eu aliquet. Integer et pulvinar odio, ac pulvinar nisi. Donec luctus interdum purus, at pretium diam porttitor non. Aliquam non risus sed nunc vestibulum elementum nec accumsan orci. Morbi ultrices tortor quam, sit amet blandit neque eleifend ac. Maecenas pretium bibendum dolor, a adipiscing eros sollicitudin sit amet. Nullam pretium gravida aliquet. Aenean nulla felis, faucibus quis iaculis et, rhoncus faucibus ligula. Sed tristique placerat velit, non auctor lorem hendrerit ut. In ornare turpis ac diam dictum facilisis.</p>

      <br> <p>Cras bibendum, dolor lacinia accumsan faucibus, est felis facilisis diam, faucibus auctor eros mauris sed erat. Nullam tempor lectus ut gravida consequat. Etiam facilisis dolor sed risus sagittis porta. Pellentesque consectetur varius porttitor. Nam diam urna, scelerisque non condimentum eget, ultrices nec dui. Morbi ornare ac est eget ultricies. Nulla non eleifend purus, vitae venenatis lorem. Phasellus tincidunt a est tempor pellentesque. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Suspendisse sapien enim, feugiat id nisl vel, viverra posuere justo. Fusce eros turpis, vestibulum ut lorem vel, suscipit hendrerit risus. Nullam tempus massa ac neque auctor, eu imperdiet sapien bibendum. Proin a lectus ac orci tincidunt vestibulum non eget ipsum. Nullam volutpat vehicula suscipit. Vivamus sem mauris, ullamcorper rhoncus est in, malesuada malesuada sem. Donec id fringilla turpis.</p>

      <br><p>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Duis vehicula semper viverra. Curabitur malesuada faucibus tincidunt. Curabitur fringilla justo id quam facilisis luctus. Maecenas felis neque, suscipit at aliquet nec, congue ut nunc. Vestibulum tincidunt massa sed mi sagittis, vitae ultricies lorem molestie. Maecenas malesuada faucibus libero, eget venenatis nisl varius a. Donec suscipit ligula quis pretium egestas. Ut nec sollicitudin mi, nec lacinia lacus. Nulla sed arcu ultrices, eleifend magna ac, adipiscing diam. Maecenas a varius odio, non volutpat nisl. Etiam volutpat mi urna, sed ullamcorper nunc pellentesque sit amet. Maecenas ac magna neque.</p>
      <?php endif ; ?>
      <?php unset($_SESSION['secret']);?>
    </div>

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

  <footer><p>Informatics International College - Cainta Library<br><br>&copy; 2014 all rights reserved</p></footer>

  <div class="cd-user-modal"> <!-- this is the entire modal form, including the background -->
    <div class="cd-user-modal-container"> <!-- this is the container wrapper -->
      <ul class="cd-switcher">
        <li><a href="#0" style="text-decoration:none;">Sign in  <span class="icon-login"></span></a></li>
      </ul>
      <div id="cd-login"> <!-- log in form -->
        <form action="" method="POST" class="cd-form">
          <p class="fieldset">
            <input name="username" value="<?php echo h($_COOKIE['username']); ?>"class="full-width has-padding has-border"  type="text" placeholder="Username" required autofocus>
          </p>
          <p class="fieldset">
            <input name="password" value="<?php echo h($_COOKIE['password']); ?>" class="full-width has-padding has-border"  type="password"  placeholder="Password" required>
          </p>
          <p class="fieldset">
            <input type="checkbox" name = "rememberme" id="remember-me" checked>
            <label for="remember-me">remember me</label>
          </p>
          <input type="hidden" name="token" value = "<?php echo h($_SESSION['token']); ?>">
          <p class="fieldset">
            <input class="full-width" type="submit" name="loginsubmit" value="Login">
          </p>
        </form>
        <p class="cd-form-bottom-message"><a href="forgotten_password.php">Forgotten password?</a></p>
        <a href="#0" class="cd-close-form">Close</a>
      </div> <!-- cd-login -->
    </div> <!-- cd-user-modal-container -->
  </div>
  <a href="#0" class="cd-top">Top</a>


<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/backtotop.js"></script>
<script src="js/accordion1.js"></script>
<script src="js/login_form.js"></script>
<script type='text/javascript' src='js/jquery.min.js'></script>
<script type='text/javascript' src='js/jquery.mobile.customized.min.js'></script>
<script type='text/javascript' src='js/jquery.easing.1.3.js'></script>
<script type='text/javascript' src='js/camera.min.js'></script>

<script>
    $(document).ready(function(){
      $form_login.find('input[type="submit"]').on('click', function(event){
        event.preventDefault();
        $form_login.find('input[type="email"]').toggleClass('has-error').next('span').toggleClass('is-visible');
      });


      $(".result-semi-container").hover(function(){
        $(this).find(".result-add").show();
      }, function(){
        $(".result-add").hide();
      });

    });
</script>
 <script>
    jQuery(function(){
      jQuery('#camera_wrap_1').camera({
                loader : 'bar',
                height: '330px',
                portrait: false,
                alignment: 'center'
      });
    });
  </script>

  </body>
</html>

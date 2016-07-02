<?php
  
  require_once('php/config.php');
  require_once('php/functions.php');
  
  session_start();

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

 if($_SERVER['REQUEST_METHOD'] != 'POST'){
    //For CSRF
    setToken();
  }

  else{
    checkToken();

    $username = $_POST['username'];
    $password = $_POST['password'];

    $dbh = connectDB();
    $error = array();

    if($username == ''){
      $error['username'] = "Please enter your username";
    }
    elseif(!usernameExists($username, $dbh)){
      $error['username'] = "Username didn't match";
    }

    if($password == ''){
      $error['password'] = "Please enter your password";
    }
    elseif(!$me = getUser($username, $password, $dbh)){
      $error['password'] = "your username and password is wrong";
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
    <title>Wrong Password</title>
    <link rel = "short icon" href="images/favicon5.ico"/>
    <link href="styles/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/reset.css"/>
    <link rel="stylesheet" href="styles/wrong_password.css"/>
    <link rel="stylesheet" href="styles/fontello.css"/>

  </head>
  <body>
    <header>
      <a href="index.php" id="logo"><img src="images/height80.png" width="200px"></a>
    </header>
    <div id="wrapper">
      <div class="alert alert-danger error-message">
        <strong>It looks like you put wrong username or password. Renter your information to confirm login.</strong>
      </div>

      <form action="" method="POST" class="cd-form">
        <p class="fieldset">
          <input name="username" value=""class="full-width has-padding has-border"  type="text" placeholder="Username">
         <br> <span class="cd-error-message"><?php echo h($error['username']); ?></span>
        </p>

        <p class="fieldset">
          <input name="password" value="" class="full-width has-padding has-border"  type="password"  placeholder="Password">
          <br><span class="cd-error-message"><?php echo h($error['password']); ?></span>
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
      <footer><p>Informatics International College - Cainta Liblary &copy; 2014</p></footer>
    </div>
     <div class="forgot-link"><a class="link_forgotten_password" href="forgotten_password.php">Forgotten password?</a></div>
  </body>

  
</html>
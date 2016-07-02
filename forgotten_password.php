<?php

  require_once('php/config.php');
  require_once('php/functions.php');
  
  session_start();
  $tempo = $_SESSION['tempo'];
  
 
  function usernameExists($post_username, $dbh){ //fetching row
    $sql = "select * from member_basic where member_id = :id limit 1";
    $stmt = $dbh->prepare($sql);
    $stmt->execute(array(":id" => $_POST['username']));
    $user = $stmt->fetch();
    return $user ? $user : false;
  }

  if($_SERVER['REQUEST_METHOD'] != 'POST'){
    //For CSRF
    setToken();

  }else{   // --> execute when pressing button
    checkToken();

    $post_username = $_POST['username'];

    $dbh = connectDB();
    $error = array();

    if(isset($_POST['username'])){ // if $_POST['username'] has a value
    echo "secret quetion ---";
      if($_POST['username'] == ''){
        $error['username'] = "Please enter your username";
      }
      elseif(!$tempo = usernameExists($_POST['username'], $dbh)){
        $error['username'] = "Username didn't match";
      }
    }
    if(isset($_POST['secretanswer'])){ // if $_POST['secretanswet'] has a value
    echo "secret answer--------";
      if($_POST['secretanswer'] == ''){
        $error['username'] = "Please enter your answer";
      }
      elseif(strcmp(strtoupper($_POST['secretanswer']),strtoupper($tempo['secret_answer'])) != 0){
        $error['username'] = "Answer didn't match";
      }
    }    
     
    if(empty($error)){
      session_regenerate_id(true);
        $_SESSION['counter']++;
        $_SESSION['tempo'] = $tempo;
        // $tempo = $_SESSION['tempo'];
    }
    
 
  }
  echo "counter : ".$_SESSION['counter'].", secret answer(post) : ".$_POST['secretanswer'].", secret answer(db) :".$tempo['secret_answer'];
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Retrieve Password</title>

    <link rel = "short icon" href="images/favicon5.ico"/>
    <link href="styles/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/reset.css"/>
    <link rel="stylesheet" href="styles/fontello.css"/>
    <link rel="stylesheet" href="styles/forgotten_password.css"/>

  </head>
  <body>
    <header>
      <a href="index.php" id="logo"><img src="images/height80.png" width="200px"></a>
    </header>
    <div id="wrapper">
      <p class="top-box">
        <?php if(empty($_SESSION['counter'])){  
          echo "Enter your username";
        }elseif($_SESSION['counter'] == 1){
           echo $tempo['secret_question'];        }else{
           echo "Here's your passwrod<br><br>"."'".$tempo['password']."'";  
        }
        ?>
      </p>
          <form action="" method="POST" class="cd-form" >
            
            <?php if(empty($_SESSION['counter'])) :?>
            <p class="fieldset">
              <input name="username" value=""class="full-width has-padding has-border"  type="text" placeholder="Username">
             <br> <span class="cd-error-message" style="color:rgb(216,5,5);"><?php echo h($error['username']); ?></span>
            </p>
            <?php elseif($_SESSION['counter'] < 2) : ?>
             <p class="fieldset">
              <input name="secretanswer" value="" class="full-width has-padding has-border"  type="text"  placeholder="Secret Answer"> 
              <br> <span class="cd-error-message" style="color:rgb(216,5,5);"><?php echo h($error['username']); ?></span>
            </p> 
            <?php else : ?>
            <?php endif ; ?>
           
            

           <!--  <p class="fieldset">
              <input type="checkbox" name = "rememberme" id="remember-me" checked>
              <label for="remember-me">remember me</label>
            </p> -->

            <input type="hidden" name="token" value = "<?php echo h($_SESSION['token']); ?>">
            <?php if($_SESSION['counter'] == 2) : ?>
            <p class="fieldset">
              <a href="index.php"><input class="full-width submitbutton" type="button" value="Back To Login Page"></a>
            </p>
           <?php unset($_SESSION['counter']);?>
           <?php unset($_SESSION['tempo']); ?>
           <?php else : ?>
            <p class="fieldset">
              <input class="full-width submitbutton" type="submit" value="OK">
            </p>
            <?php endif; ?>
          </form>
      

      <footer><p>Informatics International College - Cainta Liblary &copy; 2014</p></footer>
    </div>

    <script src="js/jquery-1.11.1.min.js"></script>


  </body>

  
</html>
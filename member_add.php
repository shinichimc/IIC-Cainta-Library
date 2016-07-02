<?php
  
  require_once('php/config.php');
  require_once('php/functions.php');

  session_start();


  $dbh = connectDB();

  if($_SERVER['REQUEST_METHOD'] == "POST"){
    //For CSRF
    // setToken();
    // checkToken();
    if(isset($_POST['submit_add'])){
    $form_member_id = $_POST['form_id'];
    $form_first = $_POST['form_first'];
    $form_last = $_POST['form_last'];
    $form_type = $_POST['form_type'];
    $form_birthdate = $_POST['form_birthdate'];
    $password = str_replace("-","",$form_birthdate);
    

      $sql = "insert into member_basic (member_id, member_firstname, member_lastname, member_type, birthdate, password)
          values(:member_id, :member_firstname, :member_lastname, :member_type, :birthdate, :password)";
      $stmt = $dbh->prepare($sql);
      $params =  array(
          ":member_id" => $form_member_id,
          ":member_firstname" => $form_first,
          ":member_lastname" => $form_last,
          ":member_type" => $form_type,
          ":birthdate" => $form_birthdate,
          ":password" => $password
          // ":pw" => getSha1Password($password)
        );

      $stmt->execute($params);
     
     $rowCount = $stmt->rowCount();
     echo $rowCount;

     $_SESSION['success'] = $stmt->rowCount() == 1 ? 1 : 3;
      header('LOCATION: manage_member.php');
      exit;

    }
      
    
  }
  
  
?>
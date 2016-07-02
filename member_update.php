<?php
  
  require_once('php/config.php');
  require_once('php/functions.php');

  session_start();

  $dbh = connectDB();

  if($_SERVER['REQUEST_METHOD']=="POST"){
    //For CSRF
    // setToken();

    // checkToken();

    if(isset($_POST['submit_edit'])){
      $id = $_POST['form_id'];
      $firstname = $_POST['form_first'];
      $lastname = $_POST['form_last'];
      $type = $_POST['form_type'];
      $birthdate = $_POST['form_birthdate'];

      $sql = "update member_basic set member_firstname = :first, member_lastname = :last, member_type = :type, birthdate = :birthdate where member_id = :id";
      $stmt = $dbh->prepare($sql);
      $params =  array(
          ":first" => $firstname,
          ":last" => $lastname,
          ":type" => $type,
          ":birthdate" => $birthdate,
          ":id" => $id
        );
      

      $stmt->execute($params);
      $_SESSION['success'] = $stmt->rowCount() == 1 ? 2 : 3;
      header('LOCATION: manage_member.php');
      exit;

    }  
  }
?>
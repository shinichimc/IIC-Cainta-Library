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
  
  $fetch_isbn = $_GET['isbn'];

  $stmt = $dbh->prepare("insert into rec_list (member_id,ISBN) values(:id, :isbn)");
  $stmt->bindParam(":id",$me['member_id']);
  $stmt->bindParam(":isbn",$fetch_isbn);
  $stmt->execute();

  $_SESSION['secret'] = 1;
  header("LOCATION: ".$_SERVER['HTTP_REFERER']);
  exit;
  
?>
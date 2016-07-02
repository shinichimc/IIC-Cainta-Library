<?php
  
  require_once('php/config.php');
  require_once('php/functions.php');

  session_start();

  $dbh = connectDB();

  if($_SERVER['REQUEST_METHOD']=="POST"){
    //For CSRF
    // setToken();

  // }else{
    // checkToken();
    if(isset($_POST['submit_add'])){
    $form_isbn = $_POST['form_isbn'];
    $form_title = $_POST['form_title'];
    $form_accno = $_POST['form_accno'];
    $form_edition = $_POST['form_edition'];
    $form_year = $_POST['form_year'];
    $form_pages = $_POST['form_pages'];
    $form_format = $_POST['form_format'];
    $form_category = $_POST['form_category'];
    $form_subject = $_POST['form_subject'];
    $form_description = $_POST['form_description'];
    $form_author = $_POST['form_author'];
    $authors = explode(",",$form_author);
    $accessions = explode(",",$form_accno);
    $form_price = $_POST['from_price'];

      $sql = "insert into book_basic (ISBN, title, edition, year, pages, format, description,price)
          values(:isbn, :title, :edition, :year, :pages, :format, :description, :price)";
      $stmt = $dbh->prepare($sql);
      $params =  array(
          ":isbn" => $form_isbn,
          ":title" => $form_title,
          ":edition" => $form_edition,
          ":year" => $form_year,
          ":pages" => $form_pages,
          ":format" => $form_format,
          ":description" => $form_description,
          ":price" => $form_price
          // ":pw" => getSha1Password($password)
        );

      $stmt->execute($params);
      $rowCount1 = $stmt->rowCount();

      foreach($authors as $author){ // repete
      $sql = "insert into book_author (ISBN, author)
          values(:isbn, :author)";
      $stmt = $dbh->prepare($sql);
      $params =  array(
          ":isbn" => $form_isbn,
          ":author" => $author
        );

      $stmt->execute($params);
      $rowCount2 = $stmt->rowCount();
    }

      foreach($accessions as $accession){ // repete
      $sql = "insert into book_each (accession_id, ISBN)
          values(:accession_id, :isbn)";
      $stmt = $dbh->prepare($sql);
      $params =  array(
          ":accession_id" => $accession,
          ":isbn" => $form_isbn
        );

      $stmt->execute($params);
      $rowCount3 = $stmt->rowCount();
    }

      foreach($form_category as $category){
        $dbh->query("insert into book_class (class_id, ISBN) values(".$category.", '".$form_isbn."')");
      }

      foreach($form_subject as $subject){
        $dbh->query("insert into book_class (class_id, ISBN) values(".$subject.", '".$form_isbn."')");
      }

      if($rowCount1 >= 1 && $rowCount2 >= 1 && $rowCount3 >= 1) {
          $_SESSION['success'] =  1;
       } else {
          $_SESSION['success'] =  3;
       }
      header('LOCATION: manage_book.php');
      exit;

    }
      
    
  }
  
  
?>
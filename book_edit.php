<?php
  
  require_once('php/config.php');
  require_once('php/functions.php');

  session_start();

  $dbh = connectDB();

  if($_SERVER['REQUEST_METHOD'] =="POST"){
    //For CSRF
    // setToken();

    // checkToken();
    
    if(isset($_POST['submit_edit'])){
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
      $form_price = $_POST['form_price'];
      $form_author = $_POST['form_author'];
      $authors = explode(",",$form_author);
      $accessions = explode(",",$form_accno);

      setcookie('form_isbn','',time()-10);
      setcookie('form_title','',time()-10);
      setcookie('form_accno','',time()-10);
      setcookie('form_edition','',time()-10);
      setcookie('form_year','',time()-10);
      setcookie('form_pages','',time()-10);
      setcookie('form_format','',time()-10);
      setcookie('form_authors','',time()-10);

      $sql = "update book_basic set title = :title, edition = :edition, year = :year, pages = :pages, format = :format,
       description = :description, price = :price where ISBN = :isbn";
      $stmt = $dbh->prepare($sql);
      $params =  array(
          ":title" => $form_title,
          ":edition" => $form_edition,
          ":year" => $form_year,
          ":pages" => $form_pages,
          ":format" => $form_format,
          ":isbn" => $form_isbn,
          ":description" => $form_description,
          ":price" => $form_price
          // ":pw" => getSha1Password($password)
        );
      

      $stmt->execute($params);
      $rowCount1 = $stmt->rowCount();

      $sql = "delete from book_author where ISBN = :isbn";
      $stmt = $dbh->prepare($sql);
      $stmt->execute(array(":isbn" => $form_isbn));
      $rowCount2 = $stmt->rowCount();

      foreach($authors as $author){ // repete
      $sql = "insert into book_author (ISBN, author)
          values(:isbn, :author)";
      $stmt = $dbh->prepare($sql);
      $params =  array(
          ":isbn" => $form_isbn,
          ":author" => $author
        );

      $stmt->execute($params);
      $rowCount3 = $stmt->rowCount();

      }
     
      $sql = "delete from book_each where ISBN = :isbn";
      $stmt = $dbh->prepare($sql);
      $stmt->execute(array(":isbn" => $form_isbn)); 
      $rowCount4 = $stmt->rowCount();

      foreach($accessions as $accession){ // repete
      $sql = "insert into book_each (accession_id, ISBN)
          values(:accession_id, :isbn)";
      $stmt = $dbh->prepare($sql);
      $params =  array(
          ":accession_id" => $accession,
          ":isbn" => $form_isbn
        );

      $stmt->execute($params);
      $rowCount5 = $stmt->rowCount();

      }

      $dbh->query("delete book_class from book_class left join class_list using (class_id) where ISBN = '".$form_isbn."' and class_group = 'Category'");
      foreach($form_category as $category){
        $dbh->query("insert into book_class (class_id, ISBN) values(".$category.", '".$form_isbn."')");
      }

      $dbh->query("delete book_class from book_class left join class_list using (class_id) where ISBN = '".$form_isbn."' and class_group = 'Subject'");
      foreach($form_subject as $subject){
        $dbh->query("insert into book_class (class_id, ISBN) values(".$subject.", '".$form_isbn."')");
      }
       

       if($rowCount1 >= 1 || $rowCount2 >= 1 || $rowCount3 >= 1 || $rowCount4 >= 1 || $rowCount5 >= 1) {
          $_SESSION['success'] =  2;
       } else {
          $_SESSION['success'] =  3;
       }

        header('LOCATION: manage_book.php');
        exit;

    }
      
    
  }
  
  
?>
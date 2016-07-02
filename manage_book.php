<?php
  
  require_once('php/config.php');
  require_once('php/functions.php');

  session_start();

  if(empty($_SESSION['me'])){
    header("LOCATION: index.php");
    exit;
  }

  $me = $_SESSION['me'];

  if($me['member_type'] != "Admin"){
    header('LOCATION: index.php');
    exit;
  }

  if(isset($_POST['clear_search'])){ // unsetting Sessions when you click clear button, 
    unset($_SESSION['primary_search']);
    unset($_SESSION['sel']);
  }

  $dbh = connectDB();

  ///////////////////////////////////HANDLE EXPIRE
  handleExpire($dbh);

  //////////////////////////////////HANDLE DUEDATE
  handleOverdue($dbh);

  //////////////////////////////////GET STORED  CATEGORIES
  $categories = array();
  $sql = "select * from class_list where class_group = 'Category' order by class_name";
  foreach($dbh->query($sql) as $row){
    array_push($categories, $row);
  }
  /////////////////////////////////GET SUBJECTS
  $subjects = array();
  $sql = "select * from class_list where class_group = 'Subject' order by class_name asc";
  foreach($dbh->query($sql) as $row){
    array_push($subjects,$row);
  }


  define('ITEMS_PER_PAGE',15);

  if(preg_match('/^[1-9][0-9]*$/',$_GET['page'])){ 
    $_SESSION['page'] = (int)$_GET['page'];                  
  }else{                                           
    $_SESSION['page'] = 1;                                     
  }

  $offset = ITEMS_PER_PAGE * ($_SESSION['page'] - 1);
  $results = array();
  $sql = "select * from book_basic limit ".$offset.",".ITEMS_PER_PAGE;
  foreach($dbh->query($sql) as $row){
    array_push($results, $row);
  }
  $_SESSION['sql'] = $sql;

  $_SESSION['total'] = $dbh->query("select count(*) from book_basic")->fetchColumn();
  $_SESSION['totalPages'] = ceil($_SESSION['total'] / ITEMS_PER_PAGE);

  if(isset($_POST['searchsubmit'])){ //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

    $_SESSION['sel'] = $_POST['searchby'];
    $_SESSION['primary_search'] = $_POST['primary_search'];

    $_SESSION['page'] = 1;

    $offset = ITEMS_PER_PAGE * ($_SESSION['page'] - 1);

    $results = array();
    if($_SESSION['sel'] == 'title'){
      $sql = "select * from book_basic where title like '%".$_SESSION['primary_search']."%' group by ISBN limit ".$offset.",".ITEMS_PER_PAGE;
      $_SESSION['total'] = $dbh->query("select count(*) from book_basic where title like '%".$_SESSION['primary_search']."%'")->fetchColumn();
    } 
    elseif($_SESSION['sel'] == 'year') {
      $sql = "select * from book_basic where year like '%".$_SESSION['primary_search']."%' group by ISBN limit ".$offset.",".ITEMS_PER_PAGE;
      $_SESSION['total'] = $dbh->query("select count(*) from book_basic where title like '%".$_SESSION['primary_search']."%'")->fetchColumn();
    } 
    elseif($_SESSION['sel'] == 'author')  {
      $sql = "select book_basic.*, GROUP_CONCAT(book_author.author ORDER BY book_author.author) as Authors from book_basic LEFT JOIN book_author USING (ISBN) group by ISBN having Authors like '%".$_SESSION['primary_search']."%' limit ".$offset.",".ITEMS_PER_PAGE;
      $_SESSION['total'] = $dbh->query("select count(*) from (select ISBN, title, GROUP_CONCAT(book_author.author ORDER BY book_author.author) from book_basic LEFT JOIN book_author USING (ISBN) group by ISBN having GROUP_CONCAT(book_author.author ORDER BY book_author.author) like '%".$_SESSION['primary_search']."%') as count")->fetchColumn();
    } 
    else {
      $sql = "select book_basic.*, GROUP_CONCAT(book_each.accession_id ORDER BY book_each.accession_id asc) as accessions from book_basic LEFT JOIN book_each USING (ISBN) group by ISBN having accessions like '%".$_SESSION['primary_search']."%' limit ".$offset.",".ITEMS_PER_PAGE;
      $_SESSION['total'] = $dbh->query("select count(*) from (select book_basic.*, GROUP_CONCAT(book_each.accession_id ORDER BY book_each.accession_id asc) as accessions from book_basic LEFT JOIN book_each USING (ISBN) group by ISBN having accessions like '%".$_SESSION['primary_search']."%') as count")->fetchColumn();
    }

    foreach($dbh->query($sql) as $row){
    array_push($results, $row);
    }

    $_SESSION['sql'] = $sql;  //tempo

    $_SESSION['totalPages'] = ceil($_SESSION['total'] / ITEMS_PER_PAGE);

  }//^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

  if(isset($_GET['page']) && isset($_SESSION['primary_search'])){//###################################

    if(preg_match('/^[1-9][0-9]*$/',$_GET['page'])){ //
      $_SESSION['page'] = (int)$_GET['page'];                    // GETTING CURRENT PAGE, GET THE VALUE FROM URL FROM $_GET['page']
    }else{                                           //
      $_SESSION['page'] = 1;                                     //
    }

    $offset = ITEMS_PER_PAGE * ($_SESSION['page'] - 1);

    $results = array();

    if($_SESSION['sel'] == 'title'){
      $sql = "select * from book_basic where title like '%".$_SESSION['primary_search']."%' group by ISBN limit ".$offset.",".ITEMS_PER_PAGE;
      $_SESSION['total'] = $dbh->query("select count(*) from book_basic where title like '%".$_SESSION['primary_search']."%'")->fetchColumn();
    } 
    elseif($_SESSION['sel'] == 'year') {
      $sql = "select * from book_basic where year like '%".$_SESSION['primary_search']."%' group by ISBN limit ".$offset.",".ITEMS_PER_PAGE;
      $_SESSION['total'] = $dbh->query("select count(*) from book_basic where title like '%".$_SESSION['primary_search']."%'")->fetchColumn();
    } 
    elseif($_SESSION['sel'] == 'author')  {
      $sql = "select book_basic.*, GROUP_CONCAT(book_author.author ORDER BY book_author.author) as Authors from book_basic LEFT JOIN book_author USING (ISBN) group by ISBN having Authors like '%".$_SESSION['primary_search']."%' limit ".$offset.",".ITEMS_PER_PAGE;
      $_SESSION['total'] = $dbh->query("select count(*) from (select ISBN, title, GROUP_CONCAT(book_author.author ORDER BY book_author.author) from book_basic LEFT JOIN book_author USING (ISBN) group by ISBN having GROUP_CONCAT(book_author.author ORDER BY book_author.author) like '%".$_SESSION['primary_search']."%') as count")->fetchColumn();
    } 
    else {
      $sql = "select book_basic.*, GROUP_CONCAT(book_each.accession_id ORDER BY book_each.accession_id asc) as accessions from book_basic LEFT JOIN book_each USING (ISBN) group by ISBN having accessions like '%".$_SESSION['primary_search']."%' limit ".$offset.",".ITEMS_PER_PAGE;
      $_SESSION['total'] = $dbh->query("select count(*) from(select book_basic.*, GROUP_CONCAT(book_each.accession_id ORDER BY book_each.accession_id asc) as accessions from book_basic LEFT JOIN book_each USING (ISBN) group by ISBN having accessions like '%".$_SESSION['primary_search']."%') as count")->fetchColumn();
    }

    foreach($dbh->query($sql) as $row){
    array_push($results, $row);
    }


    $_SESSION['sql'] = $sql;  //tempo
    
  }// ################################################

  // echo $_SESSION['sql'];
  // echo "<br>";
  // echo $_SESSION['primary_search'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manage Book</title>
<link rel = "short icon" href="images/favicon7.ico"/>
<link href="styles/bootstrap.min.css" rel="stylesheet">
<link href="styles/bootstrap-multiselect.css" rel="stylesheet">
<link rel="stylesheet" href="styles/reset.css"/>
<link rel="stylesheet" href="styles/fonts.css"/>
<link rel="stylesheet" href="styles/fontello.css"/>
<link rel="stylesheet" href="styles/backtotop.css"/>
<link rel="stylesheet" href="styles/manage_memberbook.css"/>
<link rel="stylesheet" href="styles/global.css"/>

<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/backtotop.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-multiselect.js"></script>



</head>
<body>
  <?php if(isset($_SESSION['success']) && $_SESSION['success'] == 1) :?>
  <div class="alert alert-success alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>1 book successfully added!</strong>
  </div>
  <?php elseif(isset($_SESSION['success']) && $_SESSION['success'] == 2) : ?>
  <div class="alert alert-success alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>1 book successfully Updated!</strong>
  </div>
  <?php elseif(isset($_SESSION['success']) && $_SESSION['success'] == 3) : ?>
  <div class="alert alert-danger alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>Process failed! Please make sure your information is correct.</strong>
  </div>
  <?php else : ?>
  <?php endif ; ?>
  <?php unset($_SESSION['success']);?>
    <header>
      <a href="home_admin.php" id="logo"><img src="images/height80.png"/></a>
      <p class="hi_admin">Admin Page</p>
      <nav id="mainnav">  
        <div class="nav-container ">
          <div class="nav-left"><p class="icon-user-big"></p></div>
          <div class="nav-right two-line"><li><a href="manage_member.php">Manage Member</a></li></div>
        </div> 
        <div class="nav-container active">
          <div class="nav-left"><p class="icon-book-big"></p></div>
          <div class="nav-right two-line"><li><a href="manage_book.php">Manage Book</a></li></div>
        </div>
        <div class="nav-container">
          <div class="nav-left"><p class="icon-edit-big"></p></div>
          <div class="nav-right two-line"><li><a href="monitor_reservation.php">Monitor Reservation</a></li></div>
        </div>
        <div class="nav-container">
          <div class="nav-left"><p class="icon-book-open-big"></p></div>
          <div class="nav-right two-line"><li><a href="monitor_borrowed.php">Monitor Borrowed</a></li></div>
        </div>
        <div class="nav-container">
          <div class="nav-left"><p class="icon-newspaper-big"></p></div>
          <div class="nav-right" style="padding-top:15px;"><li><a href="reports.php">Reports</a></li></div>
        </div>
        <div class="nav-container">
          <div class="nav-left"><p class="icon-logout-big"></p></div>
          <div class="nav-right" style="padding-top:15px;"><li><a href="logout.php">Log out</a></li></div>        
        </div>         
      </nav>
    </header>
    <div id="wrapper">
      <form action="manage_book.php" method="post" id="clear"></form>
      <p class="general icon-book-big">  Manage Book <input form = "clear" type="submit" name="clear_search" class="btn btn-default btn-sm clear-search" value="Clear Search"></p>
      <hr>
        <div id="top-container" class="margin20">
          <form class="form-inline" action="manage_book.php" method="POST">
            <select class="form-control select-admin" name="searchby">
              <option value="title" <?php if($_SESSION['sel']=='title') echo 'selected';?>>Title</option>
              <option value="author" <?php if($_SESSION['sel']=='author') echo 'selected';?>>Author</option>
              <option value="accession" <?php if($_SESSION['sel']=='accession') echo 'selected';?>>Accession No.</option>
              <option value="year" <?php if($_SESSION['sel']=='year') echo 'selected';?>>Year</option>
            </select>
            <div class="input-group search-box-admin">
              <input class="form-control" type="text" name="primary_search" placeholder=" Search Book" value="<?php echo $_SESSION['primary_search'];?>" required>
              <span class="input-group-btn">
                <input class="btn btn-default" name="searchsubmit" type="submit" value="Go!">
              </span>
            </div>
          </form>
            <!-- <input type="hidden" name="token" value = "<?php echo h($_SESSION['token']); ?>"> -->
          <div class="btn-group manipulate">
            <button id="addbtn" class="btn btn-manipulate btn-primary icon-plus" data-toggle="modal" data-target="#AddBook"> Add Book</button>
            <button id="editbtn" class="btn btn-manipulate btn-success icon-cog" data-toggle="modal" data-target="#EditBook" disabled> Edit Book</button>
            <button id="deletebtn" class="btn btn-manipulate btn-danger icon-cup" value="Delete Book" disabled> Delete Book</button>
          </div>
        </div>

        
      <div class="result-info-container-normal">
        <?php $to = ($offset + ITEMS_PER_PAGE) < $_SESSION['total'] ? ($offset + ITEMS_PER_PAGE) : $_SESSION['total']; ?>
        <?php if($_SESSION['total'] >= ITEMS_PER_PAGE) :?>
          <p><?php echo $offset + 1,"-".$to." of ".$_SESSION['total']." results found!";?></p>
        <?php elseif($_SESSION['total'] < ITEMS_PER_PAGE && $_SESSION['total'] != 0) : ?>
         <p><?php echo $offset + 1,"-". ($_SESSION['total'])." of ".$_SESSION['total']." results found!";?></p>
        <?php else :?>
        <p><?php echo "No results found.";?></p>
       <?php endif ; ?>
      </div>

      <table class="table table-striped table-bordered table-condensed table-hover">
        <thead>
          <tr>
            <th><input type="checkbox" id ="check-all"></th>
            <th>ISBN</th>
            <th>Tite</th>
            <th>Accession No.</th>
            <th>Authors</th>
            <th>Year</th>
            <th>Availability</th>
            <th colspan="3" style="text-align:center;">option</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($results as $result) : ?>
          <?php 
            $isbn = $result['ISBN'];

            //fetching availability 
            $sql = "select count(*) as c1, (select count(*) from book_each where availability = 'Available' and ISBN = :isbn1) as c2 from book_each where ISBN = :isbn2";
            $stmt = $dbh->prepare($sql);
            $stmt->execute(array(":isbn1" => $isbn, ":isbn2" => $isbn));
            $available = $stmt->fetch();

            //fetching accession NO
            $sql = "select group_concat(accession_id) as accessions from book_each where ISBN = :isbn group by ISBN";
            $stmt = $dbh->prepare($sql);
            $stmt->execute(array(":isbn" => $isbn));
            $access = $stmt->fetch();

            //fetching author
            $sql = "select group_concat(author) as authors from book_author where ISBN = :isbn group by ISBN";
            $stmt = $dbh->prepare($sql);
            $stmt->execute(array(":isbn" => $isbn));
            $authors = $stmt->fetch();


          ?>
          <tr id = "row_<?php echo $result['ISBN'];?>" data-id="<?php echo $result['ISBN'];?>">
            <td><input type="checkbox" name="check[]" id="check_<?php echo $result['ISBN'];?>" value="<?php echo $result['ISBN']; ?>" class="check-each"></td>
            <td><a class="ISBN-link" href="admin_book_details.php?ISBN=<?php echo $result['ISBN'];?>"><?php echo $result['ISBN'];?></a></td>
            <td><?php echo $result['title'];?></td>
            <td><?php echo $access['accessions'];?></td>
            <td><?php echo $authors['authors']; ?></td>
            <td><?php echo $result['year']; ?></td>
            <td><?php echo $available['c2']." / ".$available['c1']; ?></td>
            <td><button class="each-edit btn btn-xs btn-success icon-cog" data-toggle="modal" data-target="#EditBook"></button></td>
            <td><button class="each-delete btn btn-xs btn-danger icon-cup"></button></td>
          </tr>
          <?php endforeach ; ?>
        </tbody>
      </table>

    </div>

      <!-- MODAL WINDOW (ADD) -->
      <div class="modal fade" id="AddBook" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title" id="myModalLabel" style="text-align:center;">Add Book</h4><br>
              <div class="alert alert-warning" style="text-align:center;">put "," for multiple records.
                <br>i.e.) "Author 1, Author 2, Author 3"</div>
            </div>
            <div class="modal-body">
              <form action="book_add.php" method="POST" class="form-horizontal" role="form">
                <div class="form-group">
                  <label for="inputISBN" class="col-sm-2 control-label">ISBN</label>
                  <div class="col-sm-10">
                    <input name="form_isbn" type="text" class="form-control" id="inputISBN" placeholder="ISBN" required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputTitle" class="col-sm-2 control-label">Title</label>
                  <div class="col-sm-10">
                    <input name="form_title" type="text" class="form-control" id="inputTitle" placeholder="Title" required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputAccession" class="col-sm-2 control-label">Access. No</label>
                  <div class="col-sm-10">
                    <input name="form_accno" type="text" class="form-control" id="inputAccession" placeholder="Accession Number" required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputAuthor" class="col-sm-2 control-label">Author</label>
                  <div class="col-sm-10">
                    <input name="form_author" type="text" class="form-control" id="inputAuthor" placeholder="Author" required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEdition" class="col-sm-2 control-label">Edition</label>
                  <div class="col-sm-10">
                    <input name="form_edition" type="text" class="form-control" id="inputEdition" placeholder="Edition">
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputYear" class="col-sm-2 control-label">Year</label>
                  <div class="col-sm-10">
                    <input name="form_year" type="text" class="form-control" id="inputYear" placeholder="Year" required>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputPages" class="col-sm-2 control-label">Pages</label>
                  <div class="col-sm-10">
                    <input name="form_pages" type="text" class="form-control" id="inputPages" placeholder="Page">
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputFormat" class="col-sm-2 control-label">Format</label>
                  <div class="col-sm-10">
                  <select name="form_format" class="form-control" id="inputFormat">
                    <option selected>Book</option>
                    <option>CD</option>          
                  </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputCategory" class="col-sm-2 control-label">Category</label>
                  <div class="col-sm-10">
                    <select class="multiselect form-control" multiple="multiple" name="form_category[]" id="inputCategory">
                     <?php foreach($categories as $category): ?>
                      <option value="<?php echo $category['class_id'];?>"><?php echo $category['class_name']; ?></option>
                     <?php endforeach ; ?>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputSubject" class="col-sm-2 control-label">Subject</label>
                  <div class="col-sm-10">
                    <select class="multiselect form-control" multiple="multiple" name="form_subject[]" id="inputSubject">
                     <?php foreach($subjects as $subject): ?>
                      <option value="<?php echo $subject['class_id'];?>"><?php echo $subject['class_name']; ?></option>
                     <?php endforeach ; ?>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputPrice" class="col-sm-2 control-label">Price</label>
                  <div class="col-sm-10">
                    <input name="form_price" type="text" class="form-control" id="inputPrice" placeholder="Price">
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputDescription" class="col-sm-2 control-label">Description</label>
                  <div class="col-sm-10">
                    <textarea name="form_description" class="form-control" id="inputDescription" placeholder="Description" rows="5"></textarea>
                  </div>
                </div>
            </div>
            <div class="modal-footer">
              <input type="hidden" name="token" value = "<?php echo h($_SESSION['token']); ?>">
              <input name="submit_add" type="submit" class="btn btn-success" value="Save">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </form>
              
            </div>
          </div>
        </div>
      </div>
      <!-- MODAL WINDOW (ADD) END -->

      <!-- MODAL WINDOW (EDIT) -->
      <div class="modal fade" id="EditBook" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title" id="myModalLabel" style="text-align:center;">Edit Book</h4><br>
            </div>
            <div class="modal-body">
              <form action="book_edit.php" method="POST" class="form-horizontal" role="form">
                <div class="form-group">
                  <label for="inputISBN" class="col-sm-2 control-label">ISBN</label>
                  <div class="col-sm-10">
                    <input name="form_isbn" type="text" class="form-control input-isbn" id="inputISBN" placeholder="ISBN" readonly>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputTitle" class="col-sm-2 control-label">Title</label>
                  <div class="col-sm-10">
                    <input name="form_title" type="text" class="form-control input-title" id="inputTitle" placeholder="Title">
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputAccession" class="col-sm-2 control-label">Access. No</label>
                  <div class="col-sm-10">
                    <input name="form_accno" type="text" class="form-control input-accession" id="inputAccession" placeholder="Accession Number">
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputAuthor" class="col-sm-2 control-label">Author</label>
                  <div class="col-sm-10">
                    <input name="form_author" type="text" class="form-control input-author" id="inputAuthor" placeholder="Author">
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputEdition" class="col-sm-2 control-label">Edition</label>
                  <div class="col-sm-10">
                    <input name="form_edition" type="text" class="form-control input-edition" id="inputEdition" placeholder="Edition">
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputYear" class="col-sm-2 control-label">Year</label>
                  <div class="col-sm-10">
                    <input name="form_year" type="text" class="form-control input-year" id="inputYear" placeholder="Year">
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputPages" class="col-sm-2 control-label">Pages</label>
                  <div class="col-sm-10">
                    <input name="form_pages" type="text" class="form-control input-pages" id="inputPages" placeholder="Page">
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputFormat" class="col-sm-2 control-label">Format</label>
                  <div class="col-sm-10">
                  <select name="form_format" class="form-control input-format" id="inputFormat" selected>
                    <option>Book</option>
                    <option>CD</option>          
                  </select>
                </div>
                </div>
                <div class="form-group">
                  <label for="inputCategory" class="col-sm-2 control-label">Category</label>
                  <div class="col-sm-10">
                    <select class="multiselect form-control" multiple="multiple" name="form_category[]" id="inputCategory">
                     <?php foreach($categories as $category): ?>
                      <option class="category-option" value="<?php echo $category['class_id'];?>"><?php echo $category['class_name']; ?></option>
                     <?php endforeach ; ?>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputSubject" class="col-sm-2 control-label">Subject</label>
                  <div class="col-sm-10">
                    <select class="multiselect form-control" multiple="multiple" name="form_subject[]" id="inputSubject">
                     <?php foreach($subjects as $subject): ?>
                      <option value="<?php echo $subject['class_id'];?>"><?php echo $subject['class_name']; ?></option>
                     <?php endforeach ; ?>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputPrice" class="col-sm-2 control-label">Price</label>
                  <div class="col-sm-10">
                    <input name="form_price" type="text" class="form-control input-price" id="inputPrice" placeholder="Price">
                  </div>
                </div>
                <div class="form-group">
                  <label for="inputDescription" class="col-sm-2 control-label">Description</label>
                  <div class="col-sm-10">
                    <textarea name="form_description" class="form-control input-description" id="inputDescription" placeholder="Description" rows="5"></textarea>
                  </div>
                </div>
                
            </div>
            <div class="modal-footer">
              <input type="hidden" name="token" value = "<?php echo h($_SESSION['token']); ?>">
              <input name="submit_edit" type="submit" class="btn btn-success" value="Update">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </form>
              
            </div>
          </div>
        </div>
      </div>
      <!-- MODAL WINDOW (EDIT) END-->

      <div id="pagination-container">
        <?php if ($_SESSION['page'] > 1) : ?>
        <a href="?page=<?php echo $_SESSION['page'] - 1;?>" class="page-numbers">&laquo; Prev</a>
        <?php endif; ?>
        <?php for($a = 1; $a <= $_SESSION['totalPages'] ; $a++) : ?>
          <?php if($a == $_SESSION['page']) : ?>
          <a href="?page=<?php echo $a;?>" class="page-numbers isActive"><?php echo $a;?></a>
          <?php else : ?>
          <a href="?page=<?php echo $a;?>" class="page-numbers"><?php echo $a;?></a>
          <?php endif ; ?>
        <?php endfor ; ?>
        <?php if ($_SESSION['page'] < $_SESSION['totalPages']) : ?>
        <a href="?page=<?php echo $_SESSION['page'] + 1;?>" class="page-numbers">Next &raquo;</a>
        <?php endif; ?>
      </div>


  
  <a href="#0" class="cd-top">Top</a>

  <footer><p>Informatics International College - Cainta Library<br><br>&copy; 2014 all rights reserved</p></footer>

<script>
  $(document).ready(function(){
    $(".nav-container").click(function(){
             window.location = $(this).find("a").attr("href");
             return false;
    });

     $('.multiselect').multiselect({  
        enableFiltering: true,
        maxHeight: 220,
        buttonWidth: '460px'
     });

    $("tbody input[type=checkbox]").click(function(){
      var count_check = $('tbody').find('input[type=checkbox]:checked').length;
      console.log(count_check);
      if(count_check >= 2 || count_check == 0){
         $("#editbtn").prop("disabled", true);
      } else {
         $("#editbtn").prop("disabled",false);
      }

      if(count_check >= 1){
         $("#deletebtn").prop("disabled",false);
      } else {
         $("#deletebtn").prop("disabled",true);
      }

    });

    $("#check-all").click(function(){
      var count_check = $('tbody').find('input[type=checkbox]:checked').length;
      if($(this).prop('checked')){
        $('input[type=checkbox]').prop('checked',true);
        $("#editbtn").prop("disabled", true);
        $("#deletebtn").prop("disabled", false);
      } else {
        $('input[type=checkbox]').prop('checked',false);
        $("#editbtn").prop("disabled", true);
        $("#deletebtn").prop("disabled", true);
      }
    });

    $('#editbtn').click(function(){
        var isbn = $('table').find('input[type=checkbox]:checked').val();
        $(".input-isbn").val(isbn);
        console.log(isbn);
       
        $.post('ajax_edit_book.php', {
          isbn :isbn
        }, function(data){
          $(".input-title").val(data.title);
          $(".input-accession").val(data.accession);
          $(".input-author").val(data.author);
          $(".input-edition").val(data.edition);
          $(".input-year").val(data.year);
          $(".input-pages").val(data.pages);
          $(".input-format").val(data.format);
          $(".input-description").val(data.description);
          $(".input-price").val(data.price);
          
        });

    });

    $("tr").hover(function(){
      $(this).find(".each-delete").css("opacity","1");
      $(this).find(".each-edit").css("opacity","1");
    }, function(){
       $(this).find(".each-delete").css("opacity","0");
       $(this).find(".each-edit").css("opacity","0");
    });

    $(document).on('click','.each-delete', function(){
      if(confirm('Are you sure you want to delete this item?')){
        var id = $(this).closest('tr').data('id');
        console.log(id);
        $.post('ajax_delete_book.php', {
          id : id
        }, function(){
          $('#row_'+id).fadeOut(500);
        });
      }
    });

    $(document).on('click','#deletebtn', function(){
      // var count_check = $('tbody').find('input[type=checkbox]:checked').length;
      // console.log(count_check);
      if(confirm('Are you sure you want to delete the item(s)?')){
        var checkboxes = $('tbody').find('input[type=checkbox]:checked').map(function(){
            return $(this).val();
          }).get(); // <----
          console.log(checkboxes);
        $.post('ajax_delete_book.php', {
          checkboxes : checkboxes
        }, function(){
         $('tbody').find('input[type=checkbox]:checked').parents('tr').fadeOut(800);
        });
      }
      
    });

    $(document).on('click','.each-edit',function(){
        var id = $(this).closest('tr').data('id');
        $(".input-isbn").val(id);
        console.log(id);
        $.post('ajax_edit_book.php', {
          isbn : id
        }, function(data){
          $(".input-title").val(data.title);
          $(".input-accession").val(data.accession);
          $(".input-author").val(data.author);
          $(".input-edition").val(data.edition);
          $(".input-year").val(data.year);
          $(".input-pages").val(data.pages);
          $(".input-format").val(data.format);
          $(".input-description").val(data.description);
          $(".input-price").val(data.price);

        });
    });
  });
</script>
</body>
</html>


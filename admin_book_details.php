<?php
  
  ////////////////////////////////////UNIVERSAL
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

  $isbn = $_GET['ISBN'];

  $dbh = connectDB();

///////////////////////////////////HANDLE EXPIRE
  handleExpire($dbh);

///////////////////////////////////PRIORITY UPDATE 
  $display_reservation = priorityUpdate($isbn, $dbh);
  $count_reservation = $dbh->query("select count(*) from book_reserved where ISBN = '".$isbn."' and not status = 'picked' and not status = 'cancelled'")->fetchColumn();

///////////////////////////////////RECORDS FROM BOOK_BASIC TABLE
  $sql = "select * from book_basic where ISBN ='".$isbn."' limit 1";
  $stmt = $dbh->query($sql);
  $book = $stmt->fetch();

///////////////////////////////////RECORDS FROM BOOK_AUTHOR TABLE
  $sql = "select author from book_author where ISBN ='".$isbn."'";
  $authors = array();
  foreach($dbh->query($sql) as $row){
    array_push($authors, $row);
  }

  /////////////////////////////////RECORDS FROM BOOK_EACH TABLE  & CHECK AVAILABILITY OF THE BOOK
  $sql = "select * from book_each where ISBN ='".$isbn."'";
  $accession_ids = array();
  foreach($dbh->query($sql) as $row){
    array_push($accession_ids, $row);
  }

   $ctr_available = 0;
   $ctr = 0;
   foreach($accession_ids as $accession){
     if(strcmp($accession['availability'],"available") == 0 ){
       $ctr_available++;
     } 
       $ctr++;
   }

   /////////////////////////////////RECORDS FROM BOOK_BORROWED TABLE
   $sql = "select *, date_format(date_borrowed, '%b %d / %h:%i %p') as borrowdate, date_format(date_due, '%b %d') as duedate from book_borrowed where ISBN = '".$isbn."' and not status = 'returned' order by accession_id";
   $borrowed_info = array();
   foreach($dbh->query($sql) as $row){
      array_push($borrowed_info, $row);
   }
   /////////////////////////////////RECORDS FROM MEMBER_BASIC
   $members = array();
   $sql = "select *, concat(member_firstname,' ',member_lastname) as complete_name from member_basic where disabled = 0";
   foreach($dbh->query($sql) as $row) {
    array_push($members, $row);
   }

   /////////////////////////////////GET CATEGORIES
   $categories = array();
   $sql ="select book_class.*,class_name from book_class left join class_list using (class_id) where class_group = 'Category' and ISBN = '".$isbn."'"; 
    foreach($dbh->query($sql) as $row){
      array_push($categories,$row);
   }

   $subjects = array();
   $sql ="select book_class.*,class_name from book_class left join class_list using (class_id) where class_group = 'Subject' and ISBN = '".$isbn."'"; 
    foreach($dbh->query($sql) as $row){
      array_push($subjects,$row);
   }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Book Details</title>
<link rel = "short icon" href="images/favicon7.ico"/>
<link href="styles/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="styles/reset.css"/>
<link rel="stylesheet" href="styles/fonts.css"/>
<link rel="stylesheet" href="styles/fontello.css"/>
<link rel="stylesheet" href="styles/backtotop.css"/>
<link rel="stylesheet" href="styles/global.css"/>
<link rel="stylesheet" href="styles/manage_memberbook.css"/>
</head>
<body>
  <?php if(isset($_SESSION['success'])) :?>
  <div class="alert alert-success alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>1 book successfully added!</strong>
  </div>
  <?php unset($_SESSION['success']);?>
  <?php endif ; ?>
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
          <div class="nav-right two-line"><li><a href="manage_book.php">Manege Book</a></li></div>
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
      <h2 class="general">Book Details</h2><hr>
      <table id="table-details" class="table table-striped table-condensed">
          <tr>
            <th>TITLE</th>
            <td><?php echo $book['title']?></td>
          </tr>
          <tr>
            <th>ISBN</th>
            <td><?php echo $book['ISBN']?></td>
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
            <td><?php echo $book['pages']." pp."?></td>
          </tr>
          <tr>
            <th>Publication Date</th>
            <td><?php echo $book['year']?></td>
          </tr>
          <tr>
            <th>Format</th>
            <td><?php echo $book['format'];?></td>
          </tr>
          <tr>
            <th>Description</th>
            <td><?php echo $book['description']?></td>
          </tr>
          <tr>
            <th>Availability</th>
            <td>
                 <?php if ($ctr_available == 0) :?>
                 <p style="color:#D2322D;"><?php echo $ctr_available." / ".$ctr."    (Not available)";?></p>
                 <?php elseif($ctr_available == 1) : ?>
                 <p style="color:#3276B1;"><?php echo $ctr_available." / ".$ctr."    (".$ctr_available." copy is available)";?></p>
                 <?php else : ?>
                 <p style="color:#3276B1;"><?php echo $ctr_available." / ".$ctr."    (".$ctr_available." copies are available)";?></p>
                 <?php endif ; ?>
            </td>
          </tr>
          <tr>
            <th>Category</th>
            <td>
              <?php foreach($categories as $category) : ?>
               <a href="category_search.php?class_id=<?php echo $category['class_id'];?>" style="text-decoration:none;"><span class="lbl-cat label label-info"><?php echo $category['class_name']; ?></span></a>
              <?php endforeach ; ?>
            </td>
          </tr>
          <tr>
            <th>Subject</th>
            <td>
              <?php foreach($subjects as $subject) : ?>
               <a href="subject_search.php?class_id=<?php echo $subject['class_id'];?>" style="text-decoration:none;"><span class="lbl-sub label label-default"><?php echo $subject['class_name']; ?></span></a>
              <?php endforeach ; ?>
            </td>
          </tr>
      </table>
      <!-- <h3>Status</h3>
      <hr> -->
      <div style="overflow:hidden;">
        <div id="details-left-container">
          <table id="table-details-right" class="table table-bordered table-condensed">
             <thead>
               <tr><th colspan = "5" style="text-align:center;">Reservation Status</th></tr>
               <tr>
                 <th>No.</th>
                 <th>Member Name</th>
                 <th>Member Type</th>
                 <th>Date Reserved</th>
                 <th>Add</th>
               </tr>
             </thead>
             <tbody>
              <?php if ($count_reservation >= 1) : ?> 
                <?php $counter = 1; ?>
                <?php foreach($display_reservation as $reservation) :?>
                <?php $stmt = $dbh->prepare("select * from member_basic where member_id = :id");
                      $stmt->execute(array(":id" => $reservation['member_id']));
                      $user = $stmt->fetch();
                ?>
                <tr <?php if($reservation['status'] == 'available') echo "class='downy'"; ?>>
                  <td><?php echo $counter; ?></td>
                  <td><?php echo $user['member_firstname']." ".$user['member_lastname'];?></td>
                  <td><?php echo $user['member_type'];?></td>
                  <td><?php echo $reservation['time'];?></td>
                  <?php if($reservation['status'] == 'available') :?>
                  <td style="text-align:center;"><a id="btn-transfer" class="btn btn-default btn-xs icon-plus" href="borrow.php?accession=<?php echo $reservation['accession_id'];?>&member_id=<?php echo $user['member_id'];?>&res_id=<?php echo $reservation['reservation_id'];?>&isbn=<?php echo $isbn; ?>"></a></td>
                  <?php else : ?>
                   <td style="text-align:center;"><a class="btn btn-default btn-xs icon-plus" disabled></a></td>
                  <?php endif ; ?>
                </tr>
                <?php $counter++; ?>
                <?php endforeach ; ?>
              <?php else : ?>
                <tr><td colspan="5" style="text-align:center; color:rgb(200,200,200); font-size:95%;">No person has currently reserved the book</td></tr>
              <?php endif ; ?>
             </tbody>
         </table>
        </div>

        <div id="details-right-container">
          <table id="table-details-left" class="table table-bordered table-condensed">
            <thead>
              <tr><th colspan = "6" style="text-align:center;">Borrowed Status</th></tr>
              <tr>
                <th>Accession No.</th>
                <th>Member Name</th>
                <th>Date Borrowed</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>return</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($accession_ids as $accession) :?>
              <?php $counter = 0; ?>

                <?php foreach($borrowed_info as $borrowed) : ?>


                  <?php if($borrowed['accession_id'] == $accession['accession_id']) : ?>

                    <?php $member_name = $dbh->query("select member_firstname from member_basic where member_id = '".$borrowed['member_id']."'")->fetchColumn(); ?>
                    <tr class='borrow-row <?php if($borrowed['status'] == 'overdue') echo "red";?>'>

                      <?php if($accession['missing'] == 0) : ?>
                        <td><?php echo $accession['accession_id']; ?>&nbsp;&nbsp;<a href="missing.php?accession_id_missing=<?php echo $accession['accession_id'];?>&ISBN=<?php echo $isbn;?>" class="btn-missing btn btn-xs btn-default">missing</a></td>
                        <td><?php echo $member_name; ?></td>
                        <td><?php echo $borrowed['borrowdate']; ?></td>
                        <td><?php echo $borrowed['duedate']; ?></td>
                        <td><?php echo $borrowed['status']; ?></td>
                        <td style="text-align:center;"><a id="btn-return" class="btn btn-default btn-xs icon-ok" href="return.php?id=<?php echo $borrowed['borrowed_id']?>&ISBN=<?php echo $isbn; ?>"></a></td>
                      <?php else : ?>
                        <td><?php echo $accession['accession_id']; ?>&nbsp;&nbsp;<a href="missing.php?accession_id_found=<?php echo $accession['accession_id'];?>&ISBN=<?php echo $isbn;?>" class="btn-found btn btn-xs btn-primary">found</a></td>
                        <td colspan="5" style="text-align:center;  color:rgb(200,200,200); font-size:95%;">This book is missing</td>
                      <?php endif ; ?>
                       </tr>
                    <?php $counter++; ?>

                  <?php endif ; ?>

                <?php endforeach ; ?>

                <?php if($counter == 0) : ?>

                  <tr class="borrow-row row_<?php echo $accession['accession_id'];?>" data-id="<?php echo $accession['accession_id'];?>">

                    <?php if($accession['missing'] == 0) : ?>
                    <td><?php echo $accession['accession_id']; ?>&nbsp;&nbsp;<a href="missing.php?accession_id_missing=<?php echo $accession['accession_id'];?>&ISBN=<?php echo $isbn;?>" class="btn-missing btn btn-xs btn-default">missing</a></td>
                    <td id="some-column" colspan="5" style="text-align:center;  color:rgb(200,200,200); font-size:95%;"><button id="btn-add-borrow" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#AddBorrower">Add borrower</button></td>
                    <?php else : ?>
                    <td><?php echo $accession['accession_id']; ?>&nbsp;&nbsp;<a href="missing.php?accession_id_found=<?php echo $accession['accession_id'];?>&ISBN=<?php echo $isbn;?>" class="btn-found btn btn-xs btn-primary">found</a></td>
                    <td colspan="5" style="text-align:center;  color:rgb(200,200,200); font-size:95%;">This book is missing</td>
                    <?php endif ; ?>
                    
                  </tr>
                <?php endif ; ?>


              <?php endforeach ; ?>
            </tbody>     
          </table>
        </div>
      </div><!-- #overflow hidden -->
    </div><!-- # wrapper -->

 <!-- MODAL WINDOW (ADD) -->
   <div class="modal fade" id="AddBorrower" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
     <div class="modal-dialog">
       <div class="modal-content">
         <div class="modal-header">
           <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
           <h4 class="modal-title" id="myModalLabel" style="text-align:center;">Add Borrower</h4><br>
         </div>
         <div class="modal-body">
           <form action="borrow.php" method="GET" class="form-horizontal" role="form">
             <div class="form-group">
               <label for="inputName" class="col-sm-2 control-label">Member Name</label>
               <div class="col-sm-10">
                 <input name="form_membername" type="text" list= "combobox" class="form-control" id="inputName" placeholder="Enter member name" required>
                 <datalist id="combobox">
                   <?php foreach($members as $member) : ?>
                   <option value="<?php echo $member['complete_name']; ?>"><?php echo $member['member_id'];?></option>
                   <?php endforeach ; ?>
                 </datalist>
               </div>
             </div>
             <input name="form_accession" type="hidden" class="form-control" id="inputAccession">
             <input name="form_isbn" type="hidden" class="form-control" id="inputISBN">
         </div>
         <div class="modal-footer">
           <input type="hidden" name="token" value = "<?php echo h($_SESSION['token']); ?>">
           <input type="submit" class="btn btn-success" value="Add">
           <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
           </form>
           
         </div>
       </div>
     </div>
   </div>
   <!-- MODAL WINDOW (ADD) END -->
  
  <footer><p>Informatics International College - Cainta Library<br><br>&copy; 2014 all rights reserved</p></footer>
  <a href="#0" class="cd-top">Top</a>

<script src="js/jquery-1.11.1.min.js"></script>
<script src="bower_components/modernizr/modernizr.js"></script>
<script src="js/backtotop.js"></script>
<!-- // <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script> -->
<script src="js/flatui_modernizer.js"></script>
<script src="js/flatui_jquery.js"></script>
<script src="js/jquery.cookie.js"></script>
<script src="js/flatui_foundation.min.js"></script>
<script src="js/bootstrap.min.js"></script> 
<script>
  $(document).ready(function(){
   
    $('.modal').on('shown.bs.modal', function () {
        $('#inputName').focus();
    })
    
    $(document).on('click','#btn-add-borrow',function(){
      var accession_id = $(this).closest('tr').data('id');
      $('#inputAccession').val(accession_id);
      $('#inputISBN').val('<?php echo $isbn;?>');

    });

    $('#btn-transfer').click(function(){
      if(confirm("are you sure you want to add this member to the borrowers list?")){

      } else return false;

    });

    $('#btn-return').click(function(){
      if(confirm("are you sure?")){

      } else return false;

    });

    $(".borrow-row").hover(function(){
      $(this).find(".btn-missing").css("opacity","1");
      $(this).find(".btn-found").css("opacity","1");
      
    }, function(){
       $(this).find(".btn-missing").css("opacity","0");
       $(this).find(".btn-found").css("opacity","0");
    });

    $('.btn-missing').click(function(){
      if(confirm("Is this book missing?")){    } else return false;
    });
    
    $('.btn-found').click(function(){
      if(confirm("Is this book found?")){    } else return false;
    });

  });
</script>
</body>
</html>
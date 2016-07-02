<?php

  /////////////////////////////////UNIVERSAL
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

   $dbh = connectDB();

////////////////////////////////////WHEN YOU CLICK THE CLEAR SEARCH BUTTON, EMPTY THE SESSION VALUES
  if(isset($_POST['clear_search'])){ 
    unset($_SESSION['primary_search']);
    unset($_SESSION['sel']);
  }

  ///////////////////////////////////HANDLE EXPIRE
  handleExpire($dbh);

  //////////////////////////////////HANDLE DUEDATE
  handleOverdue($dbh);

 ////////////////////////////////////RECORDS FROM MEMBER_BASIC TABLE
  $results = array();
  $sql = "select * , concat(member_firstname,' ',member_lastname) as name from member_basic";
  foreach($dbh->query($sql) as $row){
    array_push($results, $row);
  }

////////////////////////////////////GETTING TOTAL NUMBER OF RESULTS
  $_SESSION['total'] = $dbh->query("select count(*) from member_basic")->fetchColumn();
  

////////////////////////////////////WHEN YOU CLICK THE "GO!"" BUTTON
  if(isset($_POST['searchsubmit'])){

    $_SESSION['sel'] = $_POST['searchby'];
    $_SESSION['primary_search'] = $_POST['primary_search'];


    $results = array();

    if($_SESSION['sel'] == 'membername'){
      $sql = "select *, concat(member_firstname,' ',member_lastname) as name from member_basic having name like '%".$_SESSION['primary_search']."%'";
      $_SESSION['total'] = $dbh->query("select count(*) from(select concat(member_firstname,' ',member_lastname) as haha from member_basic having haha like '%".$_SESSION['primary_search']."%') as count")->fetchColumn();
    } 
    else {
      $sql = "select *, concat(member_firstname,' ',member_lastname) as name from member_basic where member_id like '%".$_SESSION['primary_search']."%'";
      $_SESSION['total'] = $dbh->query("select count(*) from member_basic where member_id like '%".$_SESSION['primary_search']."%'")->fetchColumn();
    } 

    foreach($dbh->query($sql) as $row){
    array_push($results, $row);
    }

  }

///////////////////////////////////
  // if(isset($_SESSION['primary_search'])){

  //   $results = array();

  //   if($_SESSION['sel'] == 'membername'){
  //     $sql = "select *, concat(member_firstname,' ',member_lastname) as name from member_basic having name like '%".$_SESSION['primary_search']."%'";
  //     $_SESSION['total'] = $dbh->query("select count(*) from(select concat(member_firstname,' ',member_lastname) as haha from member_basic having haha like '%".$_SESSION['primary_search']."%') as count")->fetchColumn();
  //   } 
  //   else {
  //     $sql = "select *, concat(member_firstname,' ',member_lastname) as name from member_basic where member_id like '%".$_SESSION['primary_search']."%'";
  //     $_SESSION['total'] = $dbh->query("select count(*) from member_basic where member_id like '%".$_SESSION['primary_search']."%'")->fetchColumn();
  //   } 

  //   foreach($dbh->query($sql) as $row){
  //   array_push($results, $row);
  //   }
    
  // }

 
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manage Member</title>
<link rel = "short icon" href="images/favicon7.ico"/>
<!-- <link href="styles/skins/all.css" rel="stylesheet"> -->
<link href="styles/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="styles/manage_memberbook.css"/>
<link rel="stylesheet" href="styles/reset.css"/>
<link rel="stylesheet" href="styles/fonts.css"/>
<link rel="stylesheet" href="styles/fontello.css"/>
<link rel="stylesheet" href="styles/backtotop.css"/>
<link rel="stylesheet" href="styles/global.css"/>
<link rel="stylesheet" href="styles/manage_memberbook.css"/>


<script src="js/jquery-1.11.1.min.js"></script>
<!-- <script src="js/icheck.min.js"></script> -->
<script src="js/backtotop.js"></script>
<script src="js/bootstrap.min.js"></script>


</head>
<body>
  <?php if(isset($_SESSION['success']) && $_SESSION['success'] == 1) :?>
  <div class="alert alert-success alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>1 member successfully added!</strong>
  </div>
  <?php elseif(isset($_SESSION['success']) && $_SESSION['success'] == 2) : ?>
  <div class="alert alert-success alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>1 member successfully Updated!</strong>
  </div>
  <?php elseif(isset($_SESSION['success']) && $_SESSION['success'] == 3) : ?>
  <div class="alert alert-danger alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>Process failed! Please make sure your information is correct.</strong>
  </div>
  <?php else : ?>
  <?php endif ; ?>
  <?php unset($_SESSION['success']);
  ?>
  
  <header>
    <a href="home_admin.php" id="logo"><img src="images/height80.png"/></a>
    <p class="hi_admin">Admin Page</p>
    <nav id="mainnav">  
      <div class="nav-container active">
        <div class="nav-left"><p class="icon-user-big"></p></div>
        <div class="nav-right two-line"><li><a href="manage_member.php">Manage Member</a></li></div>
      </div> 
      <div class="nav-container">
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
    <form action="manage_member.php" method="post" id="clear"></form>
    <p class="general icon-user-big">  Manage Member <input form = "clear" type="submit" name="clear_search" class="btn btn-default btn-sm clear-search" value="Clear Search"></p>
    <hr>

    <div id="top-container" class="margin20">
      <form id = "form_manage_member" class="form-inline" action="manage_member.php" method="POST">
        <select class="form-control select-admin" name="searchby">
          <option value="membername" <?php if($_SESSION['sel']=='name') echo 'selected';?>>Name</option>
          <option value="accountno" <?php if($_SESSION['sel']=='accountno') echo 'selected';?>>Account No.</option>
        </select>
        <div class="input-group search-box-admin">
          <input class="form-control" type="text" name="primary_search" placeholder=" Search Member" value="<?php echo $_SESSION['primary_search'];?>" required>
          <span class="input-group-btn">
            <input class="btn btn-default" name="searchsubmit" type="submit" value="Go!">
          </span>
        </div>
        </form>
       
      <div class="btn-group manipulate">
        <button id="addbtn" class="btn btn-manipulate btn-primary icon-plus" data-toggle="modal" data-target="#AddMember"> Add Member</button>
        <button id="editbtn" class="btn btn-manipulate btn-success icon-cog" data-toggle="modal" data-target="#EditMember" disabled> Edit Member</button>
        <button id="deletebtn" class="btn btn-manipulate btn-danger icon-cup" value="Delete Book" disabled> Delete Member</button>
      </div>
    </div>

    <div class="result-info-container-normal">
      <p class = "result-info-text"><?php echo $_SESSION['total']." member(s) found!";?></p>
      <div class="btn-group manipulate2">
        <input form="form_manage_member" type="button" name="btn_all" class="btn-all btn btn-manipulate2 btn-default btn-xs" value="All" disabled>
        <input form="form_manage_member" type="button" name="btn_student" class="btn-student btn btn-manipulate2 btn-default btn-xs" value="Student">
        <input form="form_manage_member" type="button" name="btn_faculty" class="btn-faculty btn btn-manipulate2 btn-default btn-xs" value="Faculty">
        <input form="form_manage_member" type="button" name="btn_staff" class="btn-staff btn btn-manipulate2 btn-default btn-xs" value="Staff">
      </div>
    </div>

    <table class="table table-bordered table-condensed">
      <thead>
        <tr>
          <th><input type="checkbox" id ="check-all"></th>
          <th>Name</th>
          <th>Account Number</th>
          <th>Member Type</th>
          <th>Reservation</th>
          <th>Borrowed Books</th>
          <th>Overdue Books</th>
          <th colspan="2" style="text-align:center;">option</th>
        </tr>
      </thead>
      <tbody>
        <?php $strip = 0; 
              $red_plus = "strip-background";
         ?>
        <?php foreach($results as $result) : ?>

          <?php 
            $r = $dbh->query("select count(*) from book_reserved where member_id = '".$result['member_id']."' and (status = 'waiting' or status = 'available')")->fetchColumn();

            $b = $dbh->query("select count(*) from book_borrowed where member_id = '".$result['member_id']."' and (status = 'borrowed' or status = 'overdue')")->fetchColumn();

            $o = $dbh->query("select count(*) from book_borrowed where member_id = '".$result['member_id']."' and status = 'overdue'")->fetchColumn();

          ?>

          <?php if($result['disabled'] == 1) : ?>
          <tr id = "row_<?php echo $result['member_id'];?>" class="sort <?php echo $result['member_type'];?> tr-red" data-id="<?php echo $result['member_id'];?>">
          <?php else : ?>
          <tr id = "row_<?php echo $result['member_id'];?>" class="sort <?php echo $result['member_type'];?> <?php if($strip % 2 == 0) echo $red_plus;?>" data-id="<?php echo $result['member_id'];?>">
          <?php endif ; ?>
            <td><input type="checkbox" name="check[]" id="check_<?php echo $result['member_id'];?>" value="<?php echo $result['member_id']; ?>" class="check-each"></td>
            <td><?php echo $result['name'];?></td>
            <td><?php echo $result['member_id'];?></td>
            <td><?php echo $result['member_type'];?></td>
            <td><?php if($r>=1) echo $r; else echo "---" ;?></td>
            <td><?php if($b>=1) echo $b; else echo "---" ;?></td>
            <td><?php if($o>=1) echo $o; else echo "---" ;?></td>
            <td style="text-align:center;"><button class="each-edit btn btn-xs btn-success icon-cog" data-toggle="modal" data-target="#EditMember"></button></td>
            <?php if($result['disabled'] != 0) : ?>
            <td style="text-align:center;"><button class="each-enable btn btn-xs btn-info">enable</button></td>
            <?php else : ?>
            <td style="text-align:center;"><button class="each-delete btn btn-xs btn-warning">disable</button></td>
            <?php endif ; ?>  
          </tr>
        <?php $strip++ ; ?>
        <?php endforeach ; ?>
      </tbody>
    </table>
  </div><!-- # wrapper -->

  <!-- MODAL WINDOW (ADD) -->
  <div class="modal fade" id="AddMember" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="myModalLabel" style="text-align:center;">Add Member</h4><br>
        </div>
        <div class="modal-body">
          <form action="member_add.php" method="POST" class="form-horizontal" role="form">
            <div class="form-group">
              <label for="inputaccount" class="col-sm-2 control-label">Account No.</label>
              <div class="col-sm-10">
                <input name="form_id" type="text" class="form-control" id="inputaccount" placeholder="Account Number" required>
              </div>
            </div>
            <div class="form-group">
              <label for="inputfirst" class="col-sm-2 control-label">First Name</label>
              <div class="col-sm-10">
                <input name="form_first" type="text" class="form-control" id="inputfirst" placeholder="First Name" required>
              </div>
            </div>
            <div class="form-group">
              <label for="inputlast" class="col-sm-2 control-label">Last Name</label>
              <div class="col-sm-10">
                <input name="form_last" type="text" class="form-control" id="inputlast" placeholder="Last Name" required>
              </div>
            </div>
            <div class="form-group">
              <label for="inputtype" class="col-sm-2 control-label">Member Type</label>
              <div class="col-sm-10">
              <select name="form_type" class="form-control" id="inputtype" required>
                <option>Student</option>
                <option>Faculty</option>
                <option>Staff</option>            
              </select>
              </div>
            </div>
            <div class="form-group">
              <label for="inputbirth" class="col-sm-2 control-label" required>Birthdate</label>
              <div class="col-sm-10">
                <input name="form_birthdate" type="date" class="form-control" id="inputbirth" placeholder="Birthdate">
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
  <div class="modal fade" id="EditMember" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="myModalLabel" style="text-align:center;">Edit Member</h4><br>
        </div>
        <div class="modal-body">
          <form action="member_update.php" method="POST" class="form-horizontal" role="form">
            <div class="form-group">
              <label for="inputaccount" class="col-sm-2 control-label">Account No.</label>
              <div class="col-sm-10">
                <input name="form_id" type="text" class="form-control input-id" id="inputaccount" placeholder="Account Number" readonly>
              </div>
            </div>
            <div class="form-group">
              <label for="inputfirst" class="col-sm-2 control-label">First Name</label>
              <div class="col-sm-10">
                <input name="form_first" type="text" class="form-control input-firstname" id="inputfirst" placeholder="First Name" required>
              </div>
            </div>
            <div class="form-group">
              <label for="inputlast" class="col-sm-2 control-label">Last Name</label>
              <div class="col-sm-10">
                <input name="form_last" type="text" class="form-control input-lastname" id="inputlast" placeholder="Last Name" required>
              </div>
            </div>
            <div class="form-group">
              <label for="inputtype" class="col-sm-2 control-label">Member Type</label>
              <div class="col-sm-10">
              <select name="form_type" class="form-control input-type" id="inputtype" required>
                <option>Student</option>
                <option>Faculty</option>
                <option>Staff</option>            
              </select>
              </div>
            </div>
            <div class="form-group">
              <label for="inputbirth" class="col-sm-2 control-label">Birthdate</label>
              <div class="col-sm-10">
                <input name="form_birthdate" type="date" class="form-control input-date" id="inputbirth" placeholder="Birthdate" required>
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

  <!-- <div id="pagination-container">
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
  </div> -->
  
   <footer><p>Informatics International College - Cainta Library<br><br>&copy; 2014 all rights reserved</p></footer>

  <a href="#0" class="cd-top">Top</a>
  <script>
   $(document).ready(function(){
    $(".nav-container").click(function(){
             window.location = $(this).find("a").attr("href");
             return false;
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
        var id = $('table').find('input[type=checkbox]:checked').val();
        $(".input-id").val(id);
        console.log(id);
        $.post('ajax_member.php', {
          id3 :id
        }, function(data){
          $(".input-firstname").val(data.firstname);
          $(".input-lastname").val(data.lastname);
          $(".input-type").val(data.type);
          $(".input-date").val(data.birthdate);
          
        });

    });

    $("tr").hover(function(){
      $(this).find(".each-delete").css("opacity","1");
      $(this).find(".each-edit").css("opacity","1");
      $(this).find(".each-enable").css("opacity","1");
    }, function(){
       $(this).find(".each-delete").css("opacity","0");
       $(this).find(".each-edit").css("opacity","0");
       $(this).find(".each-enable").css("opacity","0");
    });

    $(document).on('click','.each-delete', function(){
        if(confirm("do you want to disable this member?")){
        var id = $(this).closest('tr').data('id');
        console.log(id);
        $.post('ajax_member.php', {
          id : id
        }, function(){
          $('#row_'+id).addClass("tr-red");
          $('#row_'+id).find('.each-delete').removeClass("each-delete btn btn-xs btn-warning")
                           .addClass("each-enable btn btn-xs btn-info")
                           .text("enable");
                           
        });
      }
    });

    $(document).on('click','.each-enable', function(){
        if(confirm("do you want to enable this member?")){
        var id = $(this).closest('tr').data('id');
        console.log(id);
        $.post('ajax_member.php', {
          id2 : id
        }, function(){
          $('#row_'+id).removeClass("tr-red");
          $('#row_'+id).find('.each-enable')
              .removeClass("each-enable btn btn-xs btn-info")
              .addClass("each-delete btn btn-xs btn-warning")
              .text("disable");
              
        });
      }
    });

    $(document).on('click','.each-edit',function(){
        var id = $(this).closest('tr').data('id');
        $(".input-id").val(id);
        console.log(id);
        $.post('ajax_member.php', {
          id3 : id
        }, function(data){
          $(".input-firstname").val(data.firstname);
          $(".input-lastname").val(data.lastname);
          $(".input-type").val(data.type);
          $(".input-date").val(data.birthdate);
          console.log(data.firstname);
          console.log(data.lastname);
          console.log(data.type);
          console.log(data.birthdate);
        });
    });

    $(document).on('click','#deletebtn', function(){
      // var count_check = $('tbody').find('input[type=checkbox]:checked').length;
      // console.log(count_check);
      if(confirm('Are you sure you want to delete the member(s)?')){
        var checkboxes = $('tbody').find('input[type=checkbox]:checked').map(function(){
            return $(this).val();
          }).get(); // <----
          console.log(checkboxes);
        $.post('ajax_member.php', {
          checkboxes : checkboxes
        }, function(){
         $('tbody').find('input[type=checkbox]:checked').parents('tr').fadeOut(800);
        });
      }
    });

    $(document).on('click','.btn-all', function(){
      $('.sort').show(600);
      $(this).prop('disabled',true);
      $('div.manipulate2 input').not(this).prop('disabled',false);
    });
    $(document).on('click','.btn-student', function(){
      $('.sort').not('.Student').hide(600);
      $('.Student').show(600);
      $(this).prop('disabled',true);
      $('div.manipulate2 input').not(this).prop('disabled',false);
    });
    $(document).on('click','.btn-faculty', function(){
      $('.sort').not('.Faculty').hide(600);
      $('.Faculty').show(600);
      $(this).prop('disabled',true);
      $('div.manipulate2 input').not(this).prop('disabled',false);
    });
    $(document).on('click','.btn-staff', function(){
      $('.sort').not('.Staff').hide(600);
      $('.Staff').show(600);
      $(this).prop('disabled',true);
      $('div.manipulate2 input').not(this).prop('disabled',false);
    });

    // $('input').iCheck({
    //    checkboxClass: 'icheckbox_flat-blue',
    //    radioClass: 'iradio_flat-blue',
    //    increaseArea: '20%' // optional
    //  });
    
  });
  </script>
  </body>
</html>
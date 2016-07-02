<?php
  
  require_once('php_class_config.php');
  require_once('php_class_functions.php');

  $dbh = connectDB();

  $sql = "select * from event";
  $stmt = $dbh->query($sql);
  $events = array();
  foreach($dbh->query($sql) as $row){
    array_push($events,$row);
  }

  //when "EDIT" is clicked, display selected row in the form
  $rowid = $_GET['rowid'];
  $sql = "select * from event where id = :id limit 1";
  $stmt = $dbh->prepare($sql);
  $stmt->execute(array(
    ":id" => $rowid ));
  $user = $stmt->fetch(); // all the data is in this $user variable, you just have to specify which field you want, 

  //these are from form
  $date = $_POST['date'];
  $unit = $_POST['unit'];
  $e= $_POST['event'];
  $actiontaken = $_POST['actiontaken'];
  $remarks = $_POST['remarks'];

  echo $date;

  
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Events Tracking System</title>

    <!-- Bootstrap -->
    <!-- <link href="styles/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/normalize.css" />
    <link rel="stylesheet" href="styles/flatui.min.css" />
    <link rel="stylesheet" href="styles/magnific-popup.css"> -->
    <link href='http://fonts.googleapis.com/css?family=PT+Sans:400,700' rel='stylesheet' type='text/css'>
    <link href="styles/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/reset.css"/>
    <!-- <link rel="stylesheet" href="styles/wrong_password.css"/>
    <link rel="stylesheet" href="styles/header_prototype_font.css"/>
    <link rel="stylesheet" href="styles/fontello.css"/> -->
    
    <link rel="stylesheet" href="styles/php_class.css"/>
    <script src="js/login_form.js"></script>
    <script src="js/jquery-1.11.1.min.js"></script>
   
  
  </head>
  <body>
    

    <div id="wrapper">
      <table id="table1">
        <tr>
          <th>#</th>
          <th>Date</th>
          <th>Event</th>
          <th>Unit</th>
          <th>Actions</th>
        </tr>
         
        <?php $ctr = 1 ?>
        <?php foreach($events as $event) : ?>
        
        <tr>
          <th><?php echo $ctr;?> </th>
          <th><?php echo $event['date']; ?></th>
          <th><?php echo $event['unit']; ?></th>
          <th><?php echo $event['event']; ?></th>
          <th><a href = "?rowid=<?php echo $ctr; ?>">Edit</a></th>
        </tr>
        <?php $ctr++; ?>
        <?php endforeach ; ?>
      </table>    

      <form action="" method="POST">
      <table id="table2">
        <tr>
          <th>Date</th>
          <td><input type="text" name="date" value="<?php echo $user['date']; ?>"></td>
        </tr>
        <tr>
          <th>Unit</th>
          <td><input type="text" name="unit" value="<?php echo $user['unit']; ?>"></td>
        </tr>
        <tr>
          <th>Event</th>
          <td><input type="text" name="event" value="<?php echo $user['event']; ?>"></td>
        </tr>
        <tr>
          <th>Action Taken</th>
          <td><input type="text" name="actiontaken" value="<?php echo $user['actiontaken']; ?>"></td>
        </tr>
        <tr>
          <th>Remarks</th>
          <td><textarea name="remarks"><?php echo $user['remarks']; ?></textarea></td>
        </tr>
        <tr>
          <td colspan=2 class="button-row"><input type="submit" name ="button1" class="button1 btn btn-success" value="Update"></td>
        </tr>
         
       
      </table>  
    </form>

   </div>
   
   <script type="text/javascript">
    $(".button-row").click(function(){
      var r = confirm("are you sure you want to update the records?");
      if(r==true){
        $stmt = $dbh->prepare("")
        }
    }); 
  </script>   
  </body>
</html>
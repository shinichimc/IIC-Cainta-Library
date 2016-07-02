<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Category Search</title>

<!-- Bootstrap -->
<!-- <link href="styles/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="styles/normalize.css" />
<link rel="stylesheet" href="styles/flatui.min.css" />
<link rel="stylesheet" href="styles/magnific-popup.css"> -->
<link rel='stylesheet' type='text/css' href='http://fonts.googleapis.com/css?family=PT+Sans:400,700' >
<link rel="stylesheet" href="styles/bootstrap.min.css">
<link rel="stylesheet" href="styles/reset.css"/>
<link rel="stylesheet" href="styles/global.css"/>
<link rel="stylesheet" href="styles/fonts.css"/>
<link rel="stylesheet" href="styles/fontello.css"/>
<link rel="stylesheet" href="styles/backtotop.css"/>
<link rel="short icon" href="images/favicon5.ico"/>

<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/backtotop.js"></script>
<script src="js/bootstrap.min.js"></script>


</head>
<body>

<header>
	<a href="#" id="logo"><img src="images/height80.png"/></a>
	<span class="hi_member"></span>
	<nav id="mainnav">  
		<div class="nav-container ">
			<div class="nav-left"><p class="icon-home-big"></p></div>
			<div class="nav-right"><li><a href="#">HOME</a></li></div>
		</div> 
		<div class="nav-container active">
			<div class="nav-left "><p class="icon-book-big"></p></div>
			<div class="nav-right two-line"><li><a href="subject_search.php">Subject Search</a></li></div>
		</div>
		<div class="nav-container ">
			<div class="nav-left "><p class="icon-book-big"></p></div>
			<div class="nav-right two-line"><li><a href="category_search.php">Category Search</a></li></div>
		</div>
		<div class="nav-container">
			<div class="nav-left"><p class="icon-heart-big"></p></div>
			<div class="nav-right"><li><a href="my_list.php">MY LIST</a></li></div>
		</div>
		<div class="nav-container">
			<div class="nav-left"><p class="icon-user-big"></p></div>
			<div class="nav-right"><li><a href="personal_page_reservation.php">Personal</a></li></div>
		</div>
		<div class="nav-container">
			<div class="nav-left"><p class="icon-logout-big"></p></div>
			<div class="nav-right"><li><a href="logout.php">Log Out</a></li></div>     
		</div>                 
	</nav>

</header>
<?php 
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=hoge.xls");
$excel = "<table class='table table-bordered'>
  <tr>
    <td>hihihih</td>
    <td>hihihih</td>
    <td>hihihih</td>
    <td>hihihih</td>
    <td>hihihih</td>
  </tr>
   <tr>
    <td>hihihih</td>
    <td>hihihih</td>
    <td>hihihih</td>
    <td>hihihih</td>
    <td>hihihih</td>
  </tr>
   <tr>
    <td>hihihih</td>
    <td>hihihih</td>
    <td>hihihih</td>
    <td>hihihih</td>
    <td>hihihih</td>
  </tr>
   <tr>
    <td>hihihih</td>
    <td>hihihih</td>
    <td>hihihih</td>
    <td>hihihih</td>
    <td>hihihih</td>
  </tr>
</table>";
print $excel;
exit();

?>
  <!-- <button type="button" class="btn3d btn btn-default btn-lg"><span class="glyphicon glyphicon-download-alt"></span> Download</button>
<button type="button" class="btn btn-primary btn-lg btn3d"><span class="glyphicon glyphicon-cloud"></span> Upload</button>
<button type="button" class="btn btn-success btn-lg btn3d"><span class="glyphicon glyphicon-ok"></span> Success</button>
<button type="button" class="btn btn-info btn-lg btn3d"><span class="glyphicon glyphicon-question-sign"></span> Help</button>
<button type="button" class="btn btn-warning btn-lg btn3d"><span class="glyphicon glyphicon-warning-sign"></span> Alert</button>
<button type="button" class="btn btn-danger btn-lg btn3d"><span class="glyphicon glyphicon-remove"></span> Delete</button> -->


  <!-- footer ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
  <footer><p>Informatics International College - Cainta Library<br><br>&copy; 2014 all rights reserved</p></footer>

  <a href="#0" class="cd-top">Top</a>
  
  <script>
    $(document).ready(function(){
      $(".nav-container").click(function(){
             window.location=$(this).find("a").attr("href");
             return false;
        });

      $(".result-semi-container2").hover(function(){
        $(this).find(".result-add").show();
      }, function(){
        $(".result-add").hide();
      });

      $(".panel-btn").click(function(){
         var clickPanel = $('.panel');
         clickPanel.toggle();
         $('.notif-badge').fadeOut(700);
         // $(".panel").not(clickPanel).slideUp(0);
         
         var member_id = "<?php echo $me['member_id'];?>";
         $.post('ajax_notification.php', {
            id : member_id
          }, function(){
            
          });
         return false;
       });

    });
  </script>
</body>
</html>
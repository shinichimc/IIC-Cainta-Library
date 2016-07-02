<?php

require_once('php/config.php');
require_once('php/functions.php');

session_start();

$me = $_SESSION['me'];

$dbh = connectDB();


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

<style type="text/css">
	.tag {
		background:#D2322D;
		padding:3px;
	}
	
</style>
</head>
<body>

	<?php 
	$categories = array();
	$sql ="select book_class.*,class_name from book_class left join class_list using (class_id)"; 
	foreach($dbh->query($sql) as $row){
		array_push($categories,$row);
	}


	?>
	<?php foreach($categories as $category) : ?>
	<a href="category_search.php"><span class="label label-danger"><?php echo $category['class_name']; ?></span></a>
	<?php endforeach ; ?>

	
</body>
</html>
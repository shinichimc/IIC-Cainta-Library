<?php 

error_reporting(E_ALL & ~E_NOTICE);

$dbc = mysqli_connect('localhost','root','','events_tracking_system');

$sql = "select * from contact_list";
$rs = mysqli_query($dbc,$sql);
$id = $_GET['id'];

if(isset($_GET['id'])){
	
	$sql = "select * from contact_list where id = ".$id." limit 1";
	$rs_1 = mysqli_query($dbc, $sql);

	while($row = mysqli_fetch_array($rs_1)){
		$display_name = $row['name'];
		$display_contact = $row['contact'];
	}
}


if($_SERVER['REQUEST_METHOD'] == "POST") {

	if(!empty($_POST['name']) && !empty($_POST['contact'])) {

		$name = $_POST['name'];
		$contact = $_POST['contact'];

		$sql = "update contact_list set name = '".$name."', contact = '".$contact."' where id = ".$id;
		echo $sql;
		mysqli_query($dbc, $sql);

		if(mysqli_affected_rows($dbc) == 1) {
			echo "successfully updated.";
		} else {
			echo "the item could not be updated.";
		}

		mysqli_close($dbc);

		header("LOCATION: ".$_SERVER["REQUEST_URI"]);

	} else {
			echo "you forgot to enter your items.";
	}
}



?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>My Phonebook</title>
	<link href="main.css" type="text/css" rel="stylesheet">
</head>
<body>
<div id = "wrapper">
<h1>View / Edit</h1>
<table>
	<thead>
		<tr>
			<th>#</th>
			<th>Name</th>
			<th>Contact</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
		<?php while($row = mysqli_fetch_array($rs)){ ?>
		<tr>
			<td><?php echo $row['id'];?></td>
			<td><?php echo $row['name'];?></td>
			<td><?php echo $row['contact'];?></td>
			<td><a href="?id=<?php echo $row['id'];?>">Edit</a></td>
		</tr>
		<?php }; ?>
	</tbody>
</table>
<form method="post" action="">
	<table>
		<tr>
			<th>Name</th>
			<td><input type="text" name="name" value="<?php echo $display_name; ?>"></td>
		</tr>
		<tr>
			<th>Contact</th>
			<td><input type="text" name="contact" value="<?php echo $display_contact; ?>"></td>
		</tr>
		<tr><td colspan="2"><input type="submit" value="update"><button><a href="exam_home.php">Back</a></button></td></tr>	
	</table>
</form>
</div>
</body>

</html>
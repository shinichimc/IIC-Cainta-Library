<?php 

error_reporting(E_ALL & ~E_NOTICE);

$dbc = mysqli_connect('localhost','root','','events_tracking_system');

$sql = "select * from contact_list";
$rs = mysqli_query($dbc,$sql);

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>My Phonebook</title>
	<link href="main.css" type="text/css" rel="stylesheet">
	<script src="jquery-1.11.1.min.js"></script>
</head>
<body>
<div id = "wrapper">
<h1>View / Delete</h1>
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
		<tr id='row_<?php echo $row['id'];?>' data-id='<?php echo $row['id'];?>'>
			<td><?php echo $row['id'];?></td>
			<td><?php echo $row['name'];?></td>
			<td><?php echo $row['contact'];?></td>
			<td><button class='button-delete'>Delete</button></td>
		</tr>
		<?php }; ?>
	</tbody>
</table>
<button><a href="exam_home.php">back</a></button>
<script language='javascript'>
	$(function(){
		$(document).on('click','.button-delete', function(){
			if(confirm('do you really want to delete this item?')){
				var id = $(this).closest('tr').data('id');
				console.log(id);
				$.post('ajax_delete.php', {
					id : id
				}, function(rs){
					$('#row_'+id).fadeOut(500);
				});
			}
		});
	});
</script>
</div>
</body>

</html>
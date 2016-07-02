<?php 

require_once('../php/config.php');
require_once('../php/functions.php');

$dbh = connectDB();

$tasks = array();

$sql = "select * from tasks where type != 'deleted' order by seq";

foreach($dbh->query($sql) as $row){
	array_push($tasks,$row);
}


?>
<!DOCTYPE html>
<html language="en">
<head>
<title>Tasks</title>
<meta charset="utf-8">
<script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
<style type="text/css">
.deleteTask{
cursor:pointer;
color:blue;
}
</style>

</head>
<body>
<h1>TODO application</h1>
<ul>
<?php foreach($tasks as $task) : ?>
	<li id="task_<?php echo h($task['id']);?>" data-id="<?php echo h($task['id']);?>">
		<?php echo h($task['title']); ?>
		<span class="deleteTask">[delete]</span>
	</li>
<?php endforeach ; ?>
</ul>
<script>
	$(function(){
		$(document).on('click','.deleteTask', function(){
			if(confirm('do you really want to delete this item?')){
				var id = $(this).parent().data('id');
				console.log(id);
				$.post('_ajax_delete_task.php', {
					id : id
				}, function(rs){
					$('#task_'+id).fadeOut(650);
				});
			}
		});
	});
</script>
</body>
</html>
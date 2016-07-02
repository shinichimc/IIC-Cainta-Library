<?php

$dbh = new PDO('mysql:host=localhost; dbname=a', 'root','');


	$results = array();
	$sql = "SELECT * FROM b left join d using(bd)";
	foreach($dbh->query($sql) as $row){
		array_push($results, $row);
    }
	$results2 = array();
	$sql = "SELECT * FROM e 
			left join c using (ce) 
			left join f using (ef)";
	foreach($dbh->query($sql) as $row){
		array_push($results2, $row);
    }

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Finals</title>
<link href="bootstrap.min.css" rel="stylesheet">
<style>
	.container {
		margin-top:20px;
	}
	body {
		background : rgba(50,50,50,1);
		color : #fff;
		}
	.t {
		background : rgba(100,100,100,1);
	}
</style>
</head>
<body>
<div class="container">
	<table class="table">
		<tr><th colspan="10" class="t">Table B + D__SELECT * FROM  b left join d using(bd)</th></tr>
		<tr>
			<th>B1</th>
			<th>B2</th>
			<th>B3</th>
			<th>B4</th>
			<th>B5</th>
			<th>D1</th>
			<th>D2</th>
			<th>D3</th>
			<th>D4</th>
			<th>D5</th>
		</tr>
			<?php foreach($results as $result) : ?>
			<tr>
				<td><?php echo $result['b1']; ?></td>
				<td><?php echo $result['b2']; ?></td>
				<td><?php echo $result['b3']; ?></td>
				<td><?php echo $result['b4']; ?></td>
				<td><?php echo $result['b5']; ?></td>
				<td><?php echo $result['d1']; ?></td>
				<td><?php echo $result['d1']; ?></td>
				<td><?php echo $result['d1']; ?></td>
				<td><?php echo $result['d1']; ?></td>
				<td><?php echo $result['d1']; ?></td>
			</tr>
			<?php endforeach ; ?>
		
	</table>
	<br>
	<table class="table">
		<tr><th colspan="15" class="t">Table C + E + F__SELECT * FROM e left join c using (ce) left join f using (ef)</th></tr>
		<tr>
			<th>C1</th>
			<th>C2</th>
			<th>C3</th>
			<th>C4</th>
			<th>C5</th>
			<th>E1</th>
			<th>E2</th>
			<th>E3</th>
			<th>E4</th>
			<th>E5</th>
			<th>F1</th>
			<th>F2</th>
			<th>F3</th>
			<th>F4</th>
			<th>F5</th>
		</tr>
		
		
			<?php foreach($results2 as $result) : ?>
			<tr>
				<td><?php echo $result['c1']; ?></td>
				<td><?php echo $result['c2']; ?></td>
				<td><?php echo $result['c3']; ?></td>
				<td><?php echo $result['c4']; ?></td>
				<td><?php echo $result['c5']; ?></td>
				<td><?php echo $result['e1']; ?></td>
				<td><?php echo $result['e2']; ?></td>
				<td><?php echo $result['e3']; ?></td>
				<td><?php echo $result['e4']; ?></td>
				<td><?php echo $result['e5']; ?></td>
				<td><?php echo $result['f1']; ?></td>
				<td><?php echo $result['f2']; ?></td>
				<td><?php echo $result['f3']; ?></td>
				<td><?php echo $result['f4']; ?></td>
				<td><?php echo $result['f5']; ?></td>
			</tr>
			<?php endforeach ; ?>
		
	</table>
</div>
</body>
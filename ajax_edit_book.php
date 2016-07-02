<?php 

require_once('php/config.php');
require_once('php/functions.php');


	$isbn = $_POST['isbn'];

	$dbh = connectDB();

	$sql = "select * from book_basic where ISBN ='".$isbn."' limit 1";
	$stmt = $dbh->query($sql);
	$book = $stmt->fetch();

	$sql = "select author from book_author where ISBN ='".$isbn."'";
	$authors = array();
	foreach($dbh->query($sql) as $row){
	  array_push($authors, $row);
	}

	 $ctr_author = 0;
	 foreach($authors as $author){
	 	$ctr_author++;
	 	$author_append = $author_append.$author['author'].",";
	 }
	 $author_append = rtrim($author_append,',');



	$sql = "select * from book_each where ISBN ='".$isbn."'";
	$accession_ids = array();
	foreach($dbh->query($sql) as $row){
	  array_push($accession_ids, $row);
	}

	$ctr_accession = 0;
	foreach($accession_ids as $accession){
		$ctr_accession++;
		$accession_append = $accession_append.$accession['accession_id'].",";
	}
	 $accession_append = rtrim($accession_append,',');


	 

	$rs = array(
		"title" => $book['title'],
		"edition" => $book['edition'],
		"year" => $book['year'],
		"pages" => $book['pages'],
		"format" => $book['format'],
		"description" => $book['description'],
		"author" => $author_append,
		"accession" => $accession_append,
		"ctr_accession" => $ctr_accession,
		"ctr_author" => $ctr_author,
		"price" => $book['price']
	);

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($rs);

?>
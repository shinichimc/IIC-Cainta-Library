<?php

function connectDB() {
	try{
		return new PDO('mysql:host=localhost; dbname=events_tracking_system','root','');

	}catch(PDOException $e){
		echo $e->getMessage();
		exit;
	}
}

function h($s) {
	return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}

function setToken() {
 	$token = sha1(uniqid(mt_rand(), true));
 	$_SESSION['token'] = $token;
 }
 function checkToken() {
 	if(empty($_SESSION['token']) || ($_SESSION['token'] != $_POST['token'])) {
 		echo "invalid post";
 		exit;
 	}
 }

 function getSha1Password($s) {
 	return (sha1(PASSWORD_KEY.$s));
 }
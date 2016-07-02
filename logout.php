<?php

 require_once('php/config.php');
 require_once('php/functions.php');

 session_start();

 $_SESSION = array();

 if(isset($_COOKIE[session_name()])){
 	setcookie(session_name(), '', time()-85400,'/test/');
 }

 session_destroy();

 header("LOCATION: index.php");
<?php 

require_once('php/config.php');
require_once('php/functions.php');

session_start();

if(isset($_SESSION['sel'])) unset($_SESSION['sel']);
if(isset($_SESSION['primary_search'])) unset($_SESSION['primary_search']);
if(isset($_SESSION['page'])) unset($_SESSION['page']);
if(isset($_SESSION['totalPages'])) unset($_SESSION['totalPages']);
if(isset($_SESSION['total'])) unset($_SESSION['total']);
if(isset($_SESSION['class_id'])) unset($_SESSION['class_id']);
if(isset($_SESSION['category_name'])) unset($_SESSION['category_name']);
if(isset($_SESSION['subject_name'])) unset($_SESSION['subject_name']);
if(isset($_SESSION['results'])) unset($_SESSION['results']);


?>
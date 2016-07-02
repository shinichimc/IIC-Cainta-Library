<?php

 require_once('config.php');
 require_once('functions.php');

 session_start();


 if(!empty($_SESSION['me'])){ // go back to index.php if there's contents. stay on this page if there's no contents.
 	header('Location: index.php');
 	exit;
 }

 function usernameExists($username, $dbh){
 	$sql = "select * from member_basic where member_id = :id limit 1";
 	$stmt = $dbh->prepare($sql);
 	$stmt->execute(array(":id" => $username));
 	$user = $stmt->fetch();
 	return $user ? $user : false;
 }

 function getUser($username, $password, $dbh){
 	$sql = "select * from member_basic where member_id = :id and password = :pw limit 1";
 	$stmt = $dbh->prepare($sql);
 	$stmt->execute(array(
 		":id" => $username,
 		// ":pw" => getSha1Password($password)
 	    ":pw" => $password
 		
 		));
 	$user = $stmt->fetch();
 	return $user ? $user : false;

 }

if($_SERVER['REQUEST_METHOD'] != 'POST'){
 	//For CSRF
 	setToken();
 }

 else{
 	checkToken();

 	$username = $_POST['username'];
 	$password = $_POST['password'];

 	$dbh = connectDB();
 	$error = array();

 	if($username == ''){
 		$error['username'] = "Please enter your username";
 	}
 	elseif(!usernameExists($username, $dbh)){
 		$error['username'] = "Username didn't match";
 	}

 	if($password == ''){
 		$error['password'] = "Please enter your password";
 	}
 	elseif(!$me = getUser($username, $password, $dbh)){
 		$error['password'] = "your username and password is wrong";
 	}
 
 	if(empty($error)){
 		session_regenerate_id(true);
 		$_SESSION['me'] = $me;
 		header('LOCATION: index.php');
 		exit;
 	}
 }

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset = "utf-8">
	<title>Login Page</title>
</head>
<body>
	
	<h1>Login</h1>
	<form action="" method="POST">
		<p>Username : <input type="text" name="username" value="<?php echo h($username); ?>"><p style="color:red;"><?php echo h($error['username']); ?></p></p>
		<p>Password : <input type="password" name="password"><p style="color:red;"><?php echo h($error['password']); ?></p></p>
		<input type="hidden" name="token" value = "<?php echo h($_SESSION['token']); ?>">
		<p><input type="submit" value="login"><a href="signup.php">Sign Up</a></p>



	</form>
</body>

</html>
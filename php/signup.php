<?php

 require_once('config.php');
 require_once('functions.php');

 session_start();


 if($_SERVER['REQUEST_METHOD'] != 'POST'){
 	//For CSRF
 	setToken();
 }

 else{
 	checkToken();

 	$username = $_POST['username'];
 	$firstname = $_POST['first'];
 	$lastname = $_POST['last'];
 	$type = $_POST['type'];
 	$birthdate = $_POST['birthdate'];
 	$password = $_POST['password'];

 	$dbh = connectDB();
 	$error = array();

 	if($username == ''){
 		$error['username'] = "Please enter your username";
 	}
 	if($firstname == ''){
 		$error['firstname'] = "Please enter your firstname";
 	}
 	if($lastname == ''){
 		$error['lastname'] = "Please enter your lastname";
 	}
 	if($type == ''){
 		$error['type'] = "Please enter your type";
 	}
 	if($birthdate == ''){
 		$error['birthdate'] = "Please enter your birthdate";
 	}
 	if($password == ''){
 		$error['password'] = "Please enter your password";
 	}
 	// if(filter_var('$email',FILTER_VALIDATE_EMAIL)){
 	// 	$error['password'] = "Please enter your password";
 	// }
 		// if(emailExists($email, $dbh)){
 		// 	$error['email'] = "already used";
 		// }

 	if(empty($error)){
 		$sql = "insert into member_basic (member_id, member_firstname, member_lastname, member_type, birthdate, password) 
 				values(:id, :first, :last, :type, :birth, :pw)";
 		$stmt = $dbh->prepare($sql);
 		$params =  array(
 				":id" => $username,
 				":first" => $firstname,
 				":last" => $lastname,
 				":type" => $type,
 				":birth" => $birthdate,
 				":pw" => $password
 				// ":pw" => getSha1Password($password)
 			);

 		$stmt->execute($params);
 		header('LOCATION: login.php');
 		exit;
 	}
 }

 
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset = "utf-8">
	<title>Sign Up</title>
	<link href="styles/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="styles/normalize.css" />
    <link rel="stylesheet" href="styles/flatui.min.css" />

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	 <script src="js/flatui_modernizer.js"></script>
    <script src="js/flatui_jquery.js"></script>
    <script src="js/jquery.cookie.js"></script>
    <script src="js/flatui_foundation.min.js"></script>
    <script src="js/magnific-popup.min.js"></script> 
    <script src="bower_components/modernizr/modernizr.js"></script>
</head>
<body>
	
		<div class="row" id="doc-forms">
		  <div class="large-12 columns">
		    <h3 class="text-center">Sign Up</h3>
		    <hr>
		    <form action = "" method="POST" class="custom">
		    

		        <div class="row">
		          <div class="large-6 columns">
		            <label>Username</label>
		            <input type="text" name="username" placeholder="Username" value="<?php echo h($username)?>"><p style="color:red;"><?php echo h($error['username']); ?></p>		            
		          </div>
		        </div>

		        <div class="row">
		          <div class="large-4 columns">
		            <label>First Name</label>
		            <input type="text" name="first" placeholder="First Name" value="<?php echo h($firstname)?>"><p style="color:red;"><?php echo h($error['firstname']); ?></p>
		          </div>
		      	</div>

		      	<div class="row">
		          <div class="large-4 columns">
		            <label>Last Name</label>
		            <input type="text" name="last" placeholder="Last Name" value="<?php echo h($lastname)?>"><p style="color:red;"><?php echo h($error['lastname']); ?></p>
		          </div>
		      	</div>

		      	<div class="row">
		          <div class="large-4 columns">
		            <label>Member Type</label>
		            <input type="text" name="type" placeholder="Member Type" list="membertype" value="<?php echo h($type)?>">
		            <datalist id="membertype">
		            	<option value="Student">
		            	<option value="Faculty Member">
		            	<option value="Staff">
		            </datalist><p style="color:red;"><?php echo h($error['type']); ?></p>
		          </div>
		      	</div>
		      	<div class="row">
		          <div class="large-4 columns">
		            <label>Birth Date</label>
		            <input type="date" name="birthdate" placeholder="Birth Date" value="<?php echo h($birthdate)?>"><p style="color:red;"><?php echo h($error['birthdate']); ?></p>
		          </div>
		      	</div>
		      	<div class="row">
		          <div class="large-4 columns">
		            <label>Password</label>
		            <input type="password" name="password" placeholder="Password"><p style="color:red;"><?php echo h($error['password']); ?></p>
		          </div>
		      	</div>

		      	<div class="row">
		          <div class="large-4 columns">
		            
		            <input type="hidden" name="token" value = "<?php echo h($_SESSION['token']); ?>">
		          </div>
		      	</div>

		      	<div class="row">
		          <div class="large-4 columns">
		           
		            <input class="btn btn-success" type="submit" value="Sign Up!">
		            <a href="login.php" style="margin-left:50px;">Back To Home</a>
		          </div>
		      	</div>

	      
		    </form>
		    <hr>
		  </div>
		</div>

</body>

</html>
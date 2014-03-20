<?php
session_start();
include("config.php");

if (isset($_SESSION['uid']))
{
	header("Location:home.php");
	die();
}

if (isset($_POST['login']))
{
	if (strlen($_POST['username'])==0) //Didn't type a username
		$login_error = "Please enter a username.";
	elseif (strlen($_POST['password'])==0) // Didn't type a password
		$login_error = "Please enter a password.";
	else //Username and password typed
	{
		$username = $_POST['username'];
		$password = hash('sha256', $_POST['password'].$password_salt);
		$sql_login_user = $db -> prepare("SELECT uid, username, admin FROM users WHERE username=:username AND password=:password");
		$sql_login_user -> bindParam(':username', $username, PDO::PARAM_STR);
		$sql_login_user -> bindParam(':password', $password, PDO::PARAM_STR);
		$sql_login_user -> execute();
		$login_user_rows = $sql_login_user -> fetchall(PDO::FETCH_ASSOC);
		
		if (count($login_user_rows)==0) //No user-password match
			$login_error = "Username or password was incorrect. Please try again.";
		else //User exists. Logged in.
		{
			$sql_register_session = $db -> prepare("INSERT INTO sessions (uid, php_session, ip_address, timestamp, expires) VALUES (:uid, :php_session, INET_ATON(:ip_address), UNIX_TIMESTAMP(NOW()), UNIX_TIMESTAMP(NOW() + INTERVAL ".$session_expiration." SECOND))");
			$sql_register_session -> bindParam(':uid', $login_user_rows[0]['uid'], PDO::PARAM_INT);
			$sql_register_session -> bindValue(':php_session', session_id(), PDO::PARAM_STR);
			$sql_register_session -> bindParam(':ip_address', $_SERVER['REMOTE_ADDR'], PDO::PARAM_INT);
			$sql_register_session -> execute();
			
			$sql_insert_login = $db -> prepare("INSERT INTO logins (uid, timestamp, php_session, ip_address) VALUES (:uid, NOW(), :php_session, :ip_address)");
			$sql_insert_login -> bindParam(':uid', $login_user_rows[0]['uid'], PDO::PARAM_INT);
			$sql_insert_login -> bindValue(':php_session', session_id(), PDO::PARAM_STR);
			$sql_insert_login -> bindValue(':ip_address', ip2long($_SERVER['REMOTE_ADDR']), PDO::PARAM_INT);
			$sql_insert_login -> execute();
			
			$_SESSION['uid'] = $login_user_rows[0]['uid'];
			$_SESSION['username'] = $login_user_rows[0]['username'];
			$_SESSION['admin'] = $login_user_rows[0]['admin'];
			$_SESSION['expires'] = time() + $session_expiration;
			header("Location:home.php");
			die("You have successfully logged in. If you are not automatically redirected, click <a href=\"home.php\">here</a>.");
		}
	}
}
?>

<!DOCTYPE HTML>
<html>
<head>
	<title>Scoring Engine Login</title>
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body style="background:#CCC">
	<?php include_once("navbar.php"); ?>
	<div class="container" style="margin-top:30px">
		<?php if (isset($error)) { ?>
		<div class="alert alert-error" style="height:40px">
			<div style="margin:5px;float:left">
				<img src="img/red_x.png">
			</div>
			<div>
				<b><?php echo $error; ?></b>
			</div>
        </div>
        <?php } elseif (isset($_GET['expired'])) { ?>
            <div class="alert alert-error" style="height:40px">
                <div style="margin:5px;float:left">
                    <img src="img/red_x.png">
                </div>
                <div style="margin-top:10px">
                    <b>Your session has expired or you have been logged out by an administrator. Please log in again.</b>
                </div>
            </div>
        <?php } elseif (isset($_GET['blocked'])) { ?>
            <div class="alert alert-error" style="height:40px">
                <div style="margin:5px;float:left">
                    <img src="img/red_x.png">
                </div>
                <div>
                    <b>You are blocked for the following reason:<br><?php echo $_GET['reason'] ?></b>
                </div>
            </div>
		<?php } elseif (isset($_GET['badip'])) { ?>
        <div class="alert alert-error" style="height:40px">
			<div style="margin:5px;float:left">
				<img src="img/red_x.png">
			</div>
			<div style="margin-top:10px">
				<b>Your IP address appears to have changed. Security violation. Please log in again.</b>
			</div>
		</div>
		<?php } elseif (isset($_GET['logout'])) { ?>
		<div class="alert alert-success" style="height:40px">
			<div style="margin:5px;float:left">
				<img src="img/green_check.png">
			</div>
			<div style="margin-top:10px">
				<b>You have successfully logged out.</b>
			</div>
		</div>
		<?php } ?>
		<div class="well" style="max-width:300px; margin:0 auto">
			<h2>Please log in</h2>
			<hr>
			<?php if (isset($login_error)) { ?>
				<div class="alert alert-error">
					<?php echo $login_error; ?>
				</div>
			<?php } ?>
			<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
				<input id="username" name="username" type="text" class="input-block-level" placeholder="Username" autofocus>
				<input id="password" name="password" type="password" class="input-block-level" placeholder="Password">
				<center>
					<button name="login" class="btn btn-large btn-primary" type="submit">Login</button>
				</center>
			</form>
		</div>
	</div>
	
	<script src="https://code.jquery.com/jquery.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
</body>
<?php
if ($debug)
{
	echo "Session ID: ".session_id()."<br>";
	var_dump($GLOBALS);
}	
?>

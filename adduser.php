<?php
session_start();
include("config.php");
include("sessioncheck.php");

if ($_SESSION['admin'] != 1)
{
	header("Location:home.php");
	die();
}

if (count($_POST)>0)
{
	if (strlen($_POST['username'])==0)
	{
		$_SESSION['error_message'] = "You must enter a username.";
		header("Location:users.php");
		die();
	}
	elseif ($_POST['password'] != $_POST['password2'])
	{
		$_SESSION['error_message'] = "Passwords must match.";
		header("Location:users.php");
		die();
	}
	elseif (strlen($_POST['password'])==0)
	{
		$_SESSION['error_message'] = "You must enter a password.";
		header("Location:users.php");
		die();
	}
	elseif (!preg_match("/^[a-zA-Z0-9-_]+$/", $_POST['username']))
	{
		$_SESSION['error_message'] = "Username may only contain alphanumeric characters, dashes, and underscores.";
		header("Location:users.php");
		die();
	}
	else
	{
		$sql_check_exists = $db -> prepare("SELECT COUNT(username) AS numusers FROM users WHERE username=:username");
		$sql_check_exists -> bindParam(':username', $_POST['username'], PDO::PARAM_STR);
		$sql_check_exists -> execute();
		$check_exists_rows = $sql_check_exists -> fetchall(PDO::FETCH_ASSOC);
		if ($check_exists_rows[0]['numusers']>0)
		{
			$_SESSION['error_message'] = "That user already exists.";
			header("Location:users.php");
			die();
		}
		else
		{
			$sql_register_user = $db -> prepare("INSERT INTO users (username, password, admin) VALUES (:username, :password, :admin)");
			$sql_register_user -> bindParam(':username', $_POST['username'], PDO::PARAM_STR);
			$sql_register_user -> bindValue(':password', hash('sha256', $_POST['password'].$password_salt));
			$sql_register_user -> bindValue(':admin', ($_POST['admin']=="on"), PDO::PARAM_BOOL);
			$sql_register_user -> execute();

			$_SESSION['success_message'] = "User successfully added.";
			header("Location:users.php");
			die();
		}
	}
}
?>

<form id="add-form" class="form-horizontal" action="adduser.php" method="POST">
	<div class="control-group">
		<label class="control-label" for="username">Username</label>
		<div class="controls">
			<input name="username" type="text" id="username">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="password">Password</label>
		<div class="controls">
			<input name="password" type="password" id="password">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="password2">Verify Password</label>
		<div class="controls">
			<input name="password2" type="password" id="password2">
		</div>
	</div>
	<div class="control-group">
		<div class="controls">
			<label class="checkbox">
				Admin
				<input name="admin" type="checkbox">
			</label>
		</div>
	</div>
</form>
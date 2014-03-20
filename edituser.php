<?php
session_start();
include("config.php");
include("sessioncheck.php");

if ($_SESSION['admin'] != 1)
{
	header("Location:home.php");
	die();
}

if (!isset($_REQUEST['uid']))
	die();

$sql_get_user_details = $db -> prepare("SELECT uid, username, password, admin FROM users WHERE uid=:uid");
$sql_get_user_details -> bindParam(':uid', $_REQUEST['uid'], PDO::PARAM_INT);
$sql_get_user_details -> execute();
$user_details = $sql_get_user_details -> fetchall(PDO::FETCH_ASSOC);
$user = $user_details[0];

$sql_user_blocked = $db -> prepare("SELECT unblock, expiration FROM block_log WHERE blocked_user=:uid ORDER BY timestamp DESC LIMIT 1");
$sql_user_blocked -> bindParam(':uid', $_REQUEST['uid'], PDO::PARAM_INT);
$sql_user_blocked -> execute();
$user_blocked = $sql_user_blocked -> fetchall(PDO::FETCH_ASSOC);
$user['blocked'] = count($user_blocked)>0 && $user_blocked[0]['unblock'] == 0;

if (isset($_POST['uid']))
{
	$sql_update_user_details = $db -> prepare("UPDATE users SET username=:username, password=:password, admin=:admin WHERE uid=:uid");
	$sql_update_user_details -> bindParam(':uid', $_POST['uid'], PDO::PARAM_INT);
	$sql_update_user_details -> bindParam(':username', $username, PDO::PARAM_STR);
	$sql_update_user_details -> bindParam(':password', $password, PDO::PARAM_STR);
	$sql_update_user_details -> bindParam(':admin', $admin, PDO::PARAM_BOOL);

	if (strlen($_POST['username'])==0)
	{
		$_SESSION['error_message'] = "You must enter a username.";
		header("Location:users.php");
		die();
	}
	else
	{
		$username = $_POST['username'];
	}

	if ($_POST['password'] != $_POST['password2'])
	{
		$_SESSION['error_message'] = "Passwords must match.";
		header("Location:users.php");
		die();
	}
	elseif (strlen($_POST['password'])>0 && $_POST['password']==$_POST['password2'])
	{
		$password = hash('sha256', $_POST['password'].$password_salt);
	}
	elseif (!preg_match("/^[a-zA-Z0-9-_]+$/", $_POST['username']))
	{
		$_SESSION['error_message'] = "Username may only contain alphanumeric characters, dashes, and underscores.";
		header("Location:users.php");
		die();
	}
	else
	{
		$password = $user['password'];
	}

	$admin = ($_POST['admin']=="on");

	$sql_update_user_details -> execute();
	$_SESSION['success_message'] = "User successfully updated.";
	header("Location:users.php");
	die();
}

?>

<form id="edit-form" class="form-horizontal" action="edituser.php" method="POST">
	<input type="hidden" name="uid" value="<?php echo $user_details[0]['uid'] ?>">
	<div class="control-group">
		<label class="control-label" for="uid">User ID</label>
		<div class="controls">
			<input name="uid" type="text" id="uid" value="<?php echo $user['uid'] ?>" disabled>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="username">Username</label>
		<div class="controls">
			<input name="username" type="text" id="username" value="<?php echo $user['username'] ?>">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="password">New Password</label>
		<div class="controls">
			<input name="password" type="password" id="password"><span class="help-block">Leave blank to keep unchanged.</span>
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
				<input name="admin" type="checkbox"<?php echo ($user['admin']) ? " checked" : "" ?>>
			</label>
		</div>
	</div>
</form>

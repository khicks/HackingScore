<?php
session_start();
include("config.php");
include("sessioncheck.php");

if ($_SESSION['admin'] != 1)
{
	header("Location:home.php");
	die();
}

$sql_get_message = $db -> prepare("SELECT id, from_uid, timestamp, subject, body, resolved FROM help WHERE id=:id");
$sql_get_message -> bindParam(':id', $_GET['id'], PDO::PARAM_INT);
$sql_get_message -> execute();
$message = $sql_get_message -> fetchAll(PDO::FETCH_ASSOC);

if (count($message)==0)
{
	$_SESSION['error_message'] = "That message does not exist.";
	header("Location:messages.php");
	die();
}

$message = $message[0];

$sql_get_username = $db -> prepare("SELECT username, admin FROM users WHERE uid=:uid");
$sql_get_username -> bindParam(':uid', $uid, PDO::PARAM_INT);

$uid = $message['from_uid'];
$sql_get_username -> execute();
$from = $sql_get_username -> fetchAll(PDO::FETCH_ASSOC);
$message['from'] = (count($from)>0) ? $from[0]['username'] : "[deleted]";

if ($message['resolved']>0)
{
	$uid = $message['resolved'];
	$sql_get_username -> execute();
	$resolved = $sql_get_username -> fetchAll(PDO::FETCH_ASSOC);
	$message['resolved'] = (count($resolved)>0) ? $resolved[0]['username'] : "[deleted]";
}
else
{
	$message['resolved']="";
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Read help request</title>
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body style="background:#CCC">
<?php include_once("navbar.php"); ?>
<div class="container" style="margin-top:30px">
	<?php include_once("statusmessage.php"); ?>
	<div class="well">
		<h2>Read help request</h2>
		<form class="form-horizontal">
			<div class="control-group">
				<label class="control-label" for="expires">From</label>
				<div class="controls">
					<input type="text" value="<?php echo $message['from'] ?>" readonly>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="expires">Resolved by</label>
				<div class="controls">
					<input type="text" value="<?php echo $message['resolved'] ?>" readonly>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="timestamp">Time</label>
				<div class="controls">
					<input type="text" value="<?php echo $message['timestamp'] ?>" readonly>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="subject">Subject</label>
				<div class="controls">
					<input class="span7" type="text" value="<?php echo $message['subject'] ?>" readonly>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="body">Body</label>
				<div class="controls">
					<textarea class="span7" rows="10" readonly><?php echo $message['body'] ?></textarea>
				</div>
			</div>
			<a href="replyhelp.php?replyto=<?php echo $message['id'] ?>" class="btn btn-primary btn-block btn-large" style="width:540px; margin:0 auto">Reply</a>
		</form>
	</div>
</div>

<script src="https://code.jquery.com/jquery.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="custom.js"></script>
</body>
</html>
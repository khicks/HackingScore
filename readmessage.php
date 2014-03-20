<?php
session_start();
include("config.php");
include("sessioncheck.php");

if (!isset($_SESSION['uid']))
{
	header("Location:login.php");
}

$sql_get_message = $db -> prepare("SELECT id, from_uid, to_uid, timestamp, subject, body FROM messages WHERE id=:id");
$sql_get_message -> bindParam(':id', $_GET['id'], PDO::PARAM_INT);
$sql_get_message -> execute();
$message = $sql_get_message -> fetchAll(PDO::FETCH_ASSOC);

if (count($message)==0 || ($_SESSION['admin']!=1 && $message[0]['from_uid']!=$_SESSION['uid'] && $message[0]['to_uid']!=$_SESSION['uid']))
{
	$_SESSION['error_message'] = "That message does not exist or you do not have access to it.";
	header("Location:messages.php");
	die();
}

$message = $message[0];

if($message['to_uid']==$_SESSION['uid'])
{
	$sql_mark_read = $db -> prepare("UPDATE messages SET opened=1 WHERE id=:id");
	$sql_mark_read -> bindParam(':id', $_GET['id'], PDO::PARAM_INT);
	$sql_mark_read -> execute();
}

$sql_get_username = $db -> prepare("SELECT username, admin FROM users WHERE uid=:uid");
$sql_get_username -> bindParam(':uid', $uid, PDO::PARAM_INT);

$uid = $message['from_uid'];
$sql_get_username -> execute();
$from = $sql_get_username -> fetchAll(PDO::FETCH_ASSOC);
$message['from'] = (count($from)>0) ? $from[0]['username'] : "[deleted]";

$uid = $message['to_uid'];
$sql_get_username -> execute();
$to = $sql_get_username -> fetchAll(PDO::FETCH_ASSOC);
$message['to'] = (count($to)>0) ? $to[0]['username'] : "[deleted]";

?>

<!DOCTYPE html>
<html>
<head>
	<title>Read message</title>
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body style="background:#CCC">
<?php include_once("navbar.php"); ?>
<div class="container" style="margin-top:30px">
	<?php include_once("statusmessage.php"); ?>
	<div class="well">
		<h2>Read message</h2>
		<form class="form-horizontal">
			<div class="control-group">
				<label class="control-label" for="expires">From</label>
				<div class="controls">
					<input type="text" value="<?php echo $message['from'] ?>" readonly>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="expires">To</label>
				<div class="controls">
					<input type="text" value="<?php echo $message['to'] ?>" readonly>
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
			<?php if($message['to_uid']==$_SESSION['uid']) { ?>
			<a href="newmessage.php?replyto=<?php echo $message['id'] ?>" class="btn btn-primary btn-block btn-large" style="width:540px; margin:0 auto">Reply</a>
			<?php } ?>
		</form>
	</div>
</div>

<script src="https://code.jquery.com/jquery.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="custom.js"></script>
</body>
</html>
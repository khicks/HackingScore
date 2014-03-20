<?php
session_start();
include("config.php");
include("sessioncheck.php");

if (!isset($_SESSION['uid']))
{
    header("Location:login.php");
}

if (isset($_GET['replyto']) && is_numeric($_GET['replyto']))
{
	$sql_get_message = $db -> prepare("SELECT from_uid, subject, timestamp, body FROM messages WHERE id=:id AND to_uid=:to_uid");
	$sql_get_message -> bindParam(':id', $_GET['replyto'], PDO::PARAM_INT);
	$sql_get_message -> bindParam(':to_uid', $_SESSION['uid'], PDO::PARAM_INT);
	$sql_get_message -> execute();
	$original_message = $sql_get_message -> fetchAll(PDO::FETCH_ASSOC);

	$sql_get_username = $db -> prepare("SELECT username FROM users WHERE uid=:uid");
	$sql_get_username -> bindParam(':uid', $original_message[0]['from_uid'], PDO::PARAM_INT);
	$sql_get_username -> execute();
	$from = $sql_get_username -> fetchAll(PDO::FETCH_ASSOC);
	$original_message[0]['from'] = (count($from)>0) ? $from[0]['username'] : "[deleted]";

	if (count($original_message)>0)
	{
		$_POST['subject'] = $original_message[0]['subject'];
		if (substr($original_message[0]['subject'], 0, 4)!="RE: ")
			$_POST['subject'] = "RE: ".$_POST['subject'];
		$_POST['body'] = "\n\n\n\n----- Previous message -----\nFrom: ".$original_message[0]['from']."\nTime: ".$original_message[0]['timestamp']."\n\n".$original_message[0]['body'];
	}
}

$sql_get_users = $db -> prepare("SELECT uid, username FROM users WHERE admin=?");
$sql_get_users -> execute(array(1));
$admins = $sql_get_users -> fetchAll(PDO::FETCH_ASSOC);
$sql_get_users -> execute(array(0));
$users = $sql_get_users -> fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['send']))
{
	$subject = str_replace("<", "&lt", str_replace(">", "&gt", $_POST['subject']));
	$body = str_replace("<", "&lt", str_replace(">", "&gt", $_POST['body']));

	$sql_check_user_exists = $db -> prepare("SELECT count(uid) AS count FROM users WHERE uid=:uid");
	$sql_check_user_exists -> bindParam(':uid', $_POST['to'], PDO::PARAM_INT);
	$sql_check_user_exists -> execute();
	$count = $sql_check_user_exists -> fetchAll(PDO::FETCH_ASSOC);
	$count = $count[0]['count'];

	if (strlen($subject)==0)
	{
		$_SESSION['error_message'] = "Please enter a subject.";
	}
	elseif (strlen($body)==0)
	{
		$_SESSION['error_message'] = "Please enter a message body.";
	}

	elseif ($count==0 && $_POST['to']!="help" && $_POST['to']!="all")
	{
		$_SESSION['error_message'] = "Please select a recipient.";
	}

	elseif (strlen($_POST['subject'])>128)
	{
		$_SESSION['error_message'] = "Subject is too long. Please shorten the subject line.";
	}

	elseif ($_POST['to']=="help")
	{
		$sql_insert_help = $db -> prepare("INSERT INTO help (from_uid, timestamp, subject, body, resolved) VALUES (:uid, NOW(), :subject, :body, 0)");
		$sql_insert_help -> bindParam(':uid', $_SESSION['uid'], PDO::PARAM_INT);
		$sql_insert_help -> bindParam(':subject', $subject, PDO::PARAM_STR);
		$sql_insert_help -> bindParam(':body', $body, PDO::PARAM_STR);
		$sql_insert_help -> execute();

		$_SESSION['success_message'] = "Your request has been delivered.";
		header("Location:messages.php");
		die();
	}
    elseif ($_POST['to']=="all" && $_SESSION['admin']==1)
    {
        $sql_broadcast_message = $db -> prepare("INSERT INTO messages (from_uid, to_uid, timestamp, subject, body, opened) SELECT :from_uid, uid, NOW(), :subject, :body, 0 FROM users");
        $sql_broadcast_message -> bindParam(':from_uid', $_SESSION['uid'], PDO::PARAM_INT);
        $sql_broadcast_message -> bindParam(':subject', $_POST['subject'], PDO::PARAM_STR);
        $sql_broadcast_message -> bindParam(':body', $_POST['body'], PDO::PARAM_STR);
        $sql_broadcast_message -> execute();

        $_SESSION['success_message'] = "Your message has been delivered.";
        header("Location:messages.php");
        die();
    }
	else
	{
		$sql_insert_message = $db -> prepare("INSERT INTO messages (from_uid, to_uid, timestamp, subject, body, opened) VALUES (:from_uid, :to_uid, NOW(), :subject, :body, 0)");
		$sql_insert_message -> bindParam(':from_uid', $_SESSION['uid'], PDO::PARAM_INT);
		$sql_insert_message -> bindParam(':to_uid', $_POST['to'], PDO::PARAM_INT);
		$sql_insert_message -> bindParam(':subject', $_POST['subject'], PDO::PARAM_STR);
		$sql_insert_message -> bindParam(':body', $_POST['body'], PDO::PARAM_STR);
		$sql_insert_message -> execute();

		$_SESSION['success_message'] = "Your message has been delivered.";
		header("Location:messages.php");
		die();
	}

}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Write message</title>
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body style="background:#CCC">
	<?php include_once("navbar.php"); ?>
<div class="container" style="margin-top:30px">
    <?php include_once("statusmessage.php"); ?>
    <div class="well">
        <h2>Write message</h2>
        <form class="form-horizontal" action="newmessage.php" method="POST">
            <div class="control-group">
                <label class="control-label" for="expires">To</label>
                <div class="controls">
                    <select name="to" id="to">
						<option value="help">QUESTION / HELP</option>
                        <?php if ($_SESSION['admin']==1) { ?>
                        <option value="all">ALL USERS</option>
                        <?php } ?>
						<option value="invalid" disabled="disabled">--Administrators--</option>
						<?php foreach ($admins as $admin) { ?>
						<option value="<?php echo $admin['uid'] ?>"<?php if ($admin['uid']==$original_message[0]['from_uid']) echo " selected" ?>><?php echo $admin['username'] ?></option>
						<?php } ?>
						<option value="invalid" disabled="disabled">--Contestants--</option>
						<?php foreach ($users as $user) { if ($user['uid'] != $_SESSION['uid']) { ?>
							<option value="<?php echo $user['uid'] ?>"<?php if ($user['uid']==$original_message[0]['from_uid']) echo " selected" ?>><?php echo $user['username'] ?></option>
						<?php } } ?>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="subject">Subject</label>
                <div class="controls">
                    <input class="span7" name="subject" type="text" maxlength="128" id="subject" value="<?php echo $_POST['subject'] ?>">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="body">Body</label>
                <div class="controls">
                    <textarea class="span7" name="body" rows="10" id="body"><?php echo $_POST['body'] ?></textarea>
                </div>
            </div>
            <button name="send" type="submit" class="btn btn-primary btn-block btn-large" style="width:540px; margin:0 auto">Send</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="custom.js"></script>
</body>
</html>
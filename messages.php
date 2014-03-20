<?php
session_start();
include("config.php");
include("sessioncheck.php");

if (!isset($_SESSION['uid']))
{
	header("Location:login.php");
}

if (isset($_GET['sent']) && !isset($_GET['unread']) && !isset($_GET['inbox']))
	$from_or_to = "from";
else
	$from_or_to = "to";

if (isset($_GET['unread']))
    $where_unread = " AND opened=0";

$sql_count_messages = $db -> prepare("SELECT count(id) AS count FROM messages WHERE ".$from_or_to."_uid=:uid".$where_unread);
$sql_count_messages -> bindParam(':uid', $_SESSION['uid'], PDO::PARAM_INT);
$sql_count_messages -> execute();
$rows = $sql_count_messages -> fetchAll(PDO::FETCH_ASSOC);
$rows = $rows[0]['count'];

$last_page = ceil($rows/10);
$current_page = (isset($_GET['page']) && $_GET['page']>0 && is_numeric($_GET['page'])) ? floor($_GET['page']) : 1;


$sql_messages = $db -> prepare("SELECT id, from_uid, to_uid, timestamp, subject, body, opened FROM messages WHERE ".$from_or_to."_uid=:uid".$where_unread." ORDER BY timestamp DESC LIMIT ".(($current_page-1)*10).",10");
$sql_messages -> bindParam(':uid', $_SESSION['uid'], PDO::PARAM_INT);
$sql_messages -> execute();
$messages = $sql_messages -> fetchAll(PDO::FETCH_ASSOC);

$sql_get_username = $db -> prepare("SELECT username, admin FROM users WHERE uid=:uid");
$sql_get_username -> bindParam(':uid', $uid, PDO::PARAM_INT);



?>

<!DOCTYPE html>
<html>
<head>
	<title>Message Center</title>
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body style="background:#CCC">
	<?php include_once("navbar.php"); ?>
	<div class="container" style="margin-top:30px">
		<?php include_once("statusmessage.php"); ?>
		<div class="well">
			<h2>Message Center</h2>
			<ul class="nav nav-pills">
				<li<?php if ($from_or_to=="to" && !isset($_GET['unread'])) echo " class=\"active\"" ?>><a href="messages.php?inbox">Inbox</a></li>
				<li<?php if ($from_or_to=="to" && isset($_GET['unread'])) echo " class=\"active\"" ?>><a href="messages.php?unread">Unread</a></li>
				<li<?php if ($from_or_to=="from") echo " class=\"active\"" ?>><a href="messages.php?sent">Sent</a></li>
			</ul>
			<table class="table table-hover">
				<thead>
					<th width="100px">From</th>
					<th width="100px">To</th>
					<th width="180px">Time</th>
					<th>Subject</th>
                    <th width="125px" style="text-align: right"><a href="newmessage.php" class="btn btn-success"><b>New message</b></a></th>
				</thead>
				<tbody>
                    <?php if (count($messages)==0) { ?>
                    <tr>
                        <td colspan="5" style="text-align: center">(No messages to display.)</td>
                    </tr>
                    <?php } ?>
					<?php foreach($messages as $message) {
						$uid = $message['from_uid'];
						$sql_get_username -> execute();
						$from = $sql_get_username -> fetchAll(PDO::FETCH_ASSOC);
						$message['from'] = (count($from)>0) ? $from[0]['username'] : "[deleted]";
						if ($from[0]['admin']==1) $message['from'] = "<span class=\"label label-success\">".$message['from']."</span>";
						$uid = $message['to_uid'];
						$sql_get_username -> execute();
						$to = $sql_get_username -> fetchAll(PDO::FETCH_ASSOC);
						$message['to'] = (count($to)>0) ? $to[0]['username'] : "[deleted]";
						if ($to[0]['admin']==1) $message['to'] = "<span class=\"label label-success\">".$message['to']."</span>";

					?>
					<tr<?php echo ($from_or_to=="to" && $message['opened']==0) ? " class=\"info\"" : "" ?>>
						<td><?php echo $message['from'] ?></td>
						<td><?php echo $message['to'] ?></td>
                        <td><?php echo $message['timestamp'] ?></td>
                        <td><?php echo $message['subject'] ?></td>
                        <td style="text-align:center"><a href="readmessage.php?id=<?php echo $message['id'] ?>" class="btn btn-primary">Read</a>
					</tr>
					<?php } ?>
				</tbody>
			</table>
            <?php if ($last_page>1) { ?>
            <div>
                <ul class="pager">
                    <li<?php echo ($current_page==1) ? " class=\"disabled\"" : "" ?>><a href="messages.php?page=<?php echo $current_page-1 ?>">Newer</a></li>
                    <li<?php echo ($current_page>=$last_page) ? " class=\"disabled\"" : "" ?>><a href="messages.php?page=<?php echo $current_page+1 ?>">Older</a></li>
                </ul>
            </div>
            <?php } ?>
		</div>
	</div>

	<script src="https://code.jquery.com/jquery.js"></script>
	<script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="custom.js"></script>
</body>
</html>
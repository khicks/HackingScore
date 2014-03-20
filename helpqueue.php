<?php
session_start();
include("config.php");
include("sessioncheck.php");

if ($_SESSION['admin'] != 1)
{
	header("Location:home.php");
	die();
}

$sql_count_messages = $db -> prepare("SELECT count(id) AS count FROM help");
$sql_count_messages -> execute();
$rows = $sql_count_messages -> fetchAll(PDO::FETCH_ASSOC);
$rows = $rows[0]['count'];

$last_page = ceil($rows/10);
$current_page = (isset($_GET['page']) && $_GET['page']>0 && is_numeric($_GET['page'])) ? floor($_GET['page']) : 1;


$sql_messages = $db -> prepare("SELECT id, from_uid, timestamp, subject, body, resolved FROM help ORDER BY timestamp DESC LIMIT ".(($current_page-1)*10).",10");
$sql_messages -> execute();
$messages = $sql_messages -> fetchAll(PDO::FETCH_ASSOC);

$sql_get_username = $db -> prepare("SELECT username, admin FROM users WHERE uid=:uid");
$sql_get_username -> bindParam(':uid', $uid, PDO::PARAM_INT);

?>

<!DOCTYPE html>
<html>
<head>
	<title>Help Center</title>
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body style="background:#CCC">
<?php include_once("navbar.php"); ?>
<div class="container" style="margin-top:30px">
	<?php include_once("statusmessage.php"); ?>
	<div class="well">
		<h2>Help Center</h2>
		<ul class="nav nav-pills">
			<li<?php if ($from_or_to=="to" && !isset($_GET['unread'])) echo " class=\"active\"" ?>><a href="messages.php?inbox">Inbox</a></li>
			<li<?php if ($from_or_to=="to" && isset($_GET['unread'])) echo " class=\"active\"" ?>><a href="messages.php?unread">Unread</a></li>
			<li<?php if ($from_or_to=="from") echo " class=\"active\"" ?>><a href="messages.php?sent">Sent</a></li>
		</ul>
		<table class="table table-hover">
			<thead>
			<th width="100px">From</th>
			<th width="180px">Time</th>
			<th>Subject</th>
			<th width="100px">Resolved by</th>
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
				<tr<?php echo ($message['resolved']=="") ? " class=\"error\"" : "" ?>>
					<td><?php echo $message['from'] ?></td>
					<td><?php echo $message['timestamp'] ?></td>
					<td><?php echo $message['subject'] ?></td>
					<td><?php echo $message['resolved'] ?></td>
					<td style="text-align:center"><a href="readhelp.php?id=<?php echo $message['id'] ?>" class="btn btn-primary">Read</a>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<?php if ($last_page>1) { ?>
			<div>
				<ul class="pager">
					<li<?php echo ($current_page==1) ? " class=\"disabled\"" : "" ?>><a href="helpqueue.php?page=<?php echo $current_page-1 ?>">Newer</a></li>
					<li<?php echo ($current_page>=$last_page) ? " class=\"disabled\"" : "" ?>><a href="helpqueue.php?page=<?php echo $current_page+1 ?>">Older</a></li>
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
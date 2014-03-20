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

$sql_get_user_details = $db -> prepare("SELECT uid, username FROM users WHERE uid=:uid");
$sql_get_user_details -> bindParam(':uid', $_REQUEST['uid'], PDO::PARAM_INT);
$sql_get_user_details -> execute();
$user_details = $sql_get_user_details -> fetchall(PDO::FETCH_ASSOC);
$user = $user_details[0];

$sql_block_history = $db -> prepare("SELECT blocked_by, timestamp, expiration, unblock, reason FROM block_log WHERE blocked_user=:uid ORDER BY timestamp DESC");
$sql_block_history -> bindParam(':uid', $_REQUEST['uid'], PDO::PARAM_INT);
$sql_block_history -> execute();
$block_history = $sql_block_history -> fetchall(PDO::FETCH_ASSOC);
$user['blocked'] = count($block_history)>0 && $block_history[0]['unblock'] == 0;

$sql_blocking_admin = $db -> prepare("SELECT username FROM users WHERE uid=:uid");

if (isset($_POST['uid']))
{
	$expires = "NOW() + INTERVAL ";
	if($_POST['expires']=="10min")
		$expires .= "10 MINUTE";
	elseif($_POST['expires']=="30min")
		$expires .= "30 MINUTE";
	elseif($_POST['expires']=="30min")
		$expires .= "30 MINUTE";
	elseif($_POST['expires']=="1hr")
		$expires .= "1 HOUR";
	elseif($_POST['expires']=="6hr")
		$expires .= "6 HOUR";
	elseif($_POST['expires']=="1day")
		$expires .= "1 DAY";
	elseif($_POST['expires']=="1wk")
		$expires .= "1 WEEK";
	elseif($_POST['expires']=="2wk")
		$expires .= "2 WEEK";
	elseif($_POST['expires']=="1month")
		$expires .= "1 MONTH";
	elseif($_POST['expires']=="6month")
		$expires .= "6 MONTH";
	elseif($_POST['expires']=="1yr")
		$expires .= "1 YEAR";
	elseif($_POST['expires']=="10yr")
		$expires .= "10 YEAR";
	else
		$expires = "NULL";

	$reason = str_replace("<", "&lt", $_POST['reason']);
	$reason = str_replace(">", "&gt", $reason);
	//var_dump($user);
	//die($_SESSION['uid'].", ".$_POST['uid'].", ".$expires.", ".($user['blocked']+0).", ".$_POST['reason']);
	$sql_block_user = $db -> prepare("INSERT INTO block_log (blocked_by, blocked_user, timestamp, expiration, unblock, reason) VALUES (:admin, :user, NOW(), ".$expires.", :unblock, :reason)");
	$sql_block_user -> bindParam(':admin', $_SESSION['uid'], PDO::PARAM_INT);
	$sql_block_user -> bindParam(':user', $_POST['uid'], PDO::PARAM_INT);
	$sql_block_user -> bindValue(':unblock', $user['blocked']+0, PDO::PARAM_INT);
	$sql_block_user -> bindParam(':reason', $reason, PDO::PARAM_STR);
	$sql_block_user -> execute();

	header("Location:users.php");
	die();

}

?>
<h3><?php echo ($user['blocked']) ? "Unblocking " : "Blocking "; echo $user['username']; ?></h3>
<form id="block-form" class="form-horizontal" action="blockuser.php" method="POST">
	<input type="hidden" name="uid" value="<?php echo $user_details[0]['uid'] ?>">
	<?php if (!$user['blocked']) { ?>
	<div class="control-group">
		<label class="control-label" for="expires">Expiration</label>
		<div class="controls">
			<select name="expires" id="expires">
				<option value="indefinite">Indefinite</option>
				<option value="10min">10 minutes</option>
				<option value="30min">30 minutes</option>
				<option value="1hr">1 hour</option>
				<option value="6hr">6 hours</option>
				<option value="1day">1 day</option>
				<option value="1wk">1 week</option>
				<option value="2wk">2 weeks</option>
				<option value="1month">1 month</option>
				<option value="6month">6 months</option>
				<option value="1yr">1 year</option>
				<option value="10yr">10 years</option>
			</select>
		</div>
	</div>
	<?php } ?>
	<div class="control-group">
		<label class="control-label" for="reason">Reason</label>
		<div class="controls">
			<textarea name="reason" rows="3" id="reason"></textarea>
		</div>
	</div>
</form>

<h4>Block history</h4>
<table class="table table-hover">
	<thead>
		<th>Admin</th>
		<th>Action</th>
		<th>Time</th>
		<th>Expires</th>
		<th>reason</th>
	</thead>
	<tbody>
	<?php foreach($block_history as $history) { 
		$sql_blocking_admin -> bindValue(':uid', $history['blocked_by'], PDO::PARAM_INT);
		$sql_blocking_admin -> execute();
		$blocked_by = $sql_blocking_admin -> fetchall(PDO::FETCH_ASSOC);
		?>
		<tr<?php echo ($history['unblock']) ? " class=\"success\"" : " class=\"error\""; ?>>
			<td><?php echo $blocked_by[0]['username'] ?></td>
			<td><?php echo ($history['unblock']) ? "unblock" : "block" ?></td>
			<td><?php echo $history['timestamp'] ?></td>
			<td><?php echo ($history['expiration']) ? $history['expiration'] : "Indefinite" ?></td>
			<td><?php echo $history['reason'] ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>

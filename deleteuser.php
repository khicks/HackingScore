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

if (isset($_POST['uid']))
{
	$sql_delete_user = $db -> prepare("DELETE FROM users WHERE uid=:uid");
	$sql_delete_user -> bindParam(':uid', $_POST['uid'], PDO::PARAM_INT);
	$sql_delete_user -> execute();

    $sql_purge_sessions = $db -> prepare("DELETE FROM sessions WHERE uid=:uid");
    $sql_purge_sessions -> bindParam(':uid', $_POST['uid'], PDO::PARAM_INT);
    $sql_purge_sessions -> execute();

    $sql_purge_progress = $db -> prepare("DELETE FROM progress WHERE uid=:uid");
    $sql_purge_progress -> bindParam(':uid', $_POST['uid'], PDO::PARAM_INT);
    $sql_purge_progress -> execute();

    $sql_purge_points = $db -> prepare("DELETE FROM points WHERE uid=:uid");
    $sql_purge_points -> bindParam(':uid', $_POST['uid'], PDO::PARAM_INT);
    $sql_purge_points -> execute();

    $sql_purge_block = $db -> prepare("DELETE FROM block_log WHERE blocked_user=:uid");
    $sql_purge_block -> bindParam(':uid', $_POST['uid'], PDO::PARAM_INT);
    $sql_purge_block -> execute();

	$_SESSION['success_message'] = "User successfully deleted.";
	header("Location:users.php");
	die();
}

?>

<h3>Are you sure you want to delete <?php echo $user['username'] ?>?</h3>
<form id="delete-form" class="form-horizontal" action="deleteuser.php" method="POST">
	<input type="hidden" name="uid" value="<?php echo $user['uid'] ?>">
</form>
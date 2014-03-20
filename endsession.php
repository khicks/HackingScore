<?php
session_start();
include("config.php");
include("sessioncheck.php");

if ($_SESSION['admin'] != 1)
{
    header("Location:home.php");
    die();
}

//if (!isset($_REQUEST['uid']) && !isset($_REQUEST['sid']))
//    die();

if (isset($_POST['sid']))
{
    $sql_end_session = $db -> prepare("DELETE FROM sessions WHERE sid=:sid");
    $sql_end_session -> bindParam(':sid', $_POST['sid'], PDO::PARAM_INT);
    $sql_end_session -> execute();

    $_SESSION['success_message'] = "Session successfully ended.";
    header("Location:sessions.php");
    die();
}

elseif (isset($_POST['uid']))
{
    $sql_end_user_sessions = $db -> prepare("DELETE FROM sessions WHERE uid=:uid");
    $sql_end_user_sessions -> bindParam(':uid', $_POST['uid'], PDO::PARAM_INT);
    $sql_end_user_sessions -> execute();

    $_SESSION['success_message'] = "User sessions successfully ended.";
    header("Location:sessions.php");
    die();
}

$sql_get_user_details = $db -> prepare("SELECT uid, username FROM users WHERE uid=:uid");
$sql_get_user_details -> bindParam(':uid', $_REQUEST['uid'], PDO::PARAM_INT);
$sql_get_user_details -> execute();
$user_details = $sql_get_user_details -> fetchall(PDO::FETCH_ASSOC);
$user = $user_details[0];


?>

<h3>Are you sure you want to end all of <?php echo $user['username'] ?>'s sessions?</h3>
<form id="end-form" class="form-horizontal" action="endsession.php" method="POST">
    <input type="hidden" name="uid" value="<?php echo $user['uid'] ?>">
</form>
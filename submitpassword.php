<?php
session_start();
include("config.php");
include("sessioncheck.php");

if (!isset($_SESSION['uid']))
{
	header("Location:login.php");
}

if ($_SESSION['admin']==1)
{
	die("Administrators cannot participate in the contest.");
}

$uid = $_SESSION['uid'];

$sql_last_level_completed = $db -> prepare("SELECT uid, lid, levels.level AS level, timestamp FROM progress JOIN levels ON levels.id = progress.lid WHERE uid=:uid ORDER BY level DESC LIMIT 1");
$sql_last_level_completed -> bindParam(':uid', $uid, PDO::PARAM_INT);
$sql_last_level_completed -> execute();
$last_level_completed = $sql_last_level_completed -> fetchAll(PDO::FETCH_ASSOC);

if (count($last_level_completed)==0)
	$next_level = 1;
else
	$next_level = $last_level_completed[0]['level']+1;

if (isset($_POST['password']))
{
    if (!$contest_active)
    {
        $_SESSION['error_message'] = "The contest is not currently accepting submissions.";
        header("Location:home.php");
        die();
    }

	$sql_check_level_password = $db -> prepare("SELECT id, level FROM levels WHERE level=:level AND password=:password");
	$sql_check_level_password -> bindParam(':level', $next_level, PDO::PARAM_INT);
	$sql_check_level_password -> bindParam(':password', $_POST['password'], PDO::PARAM_STR);
	$sql_check_level_password -> execute();
	$completed_level = $sql_check_level_password -> fetchAll(PDO::FETCH_ASSOC);

	if (count($completed_level)==0)
	{
		$_SESSION['error_message'] = "Sorry, but that password is incorrect.";
		header("Location:home.php");
		die();
	}

	$completed_level = $completed_level[0];

	$sql_credit_level = $db -> prepare("INSERT INTO progress (uid, lid, timestamp) VALUES (:uid, :lid, NOW())");
	$sql_credit_level -> bindParam(':uid', $uid, PDO::PARAM_INT);
	$sql_credit_level -> bindParam(':lid', $completed_level['id'], PDO::PARAM_INT);
	$sql_credit_level -> execute();

	$_SESSION['success_message'] = "Congratulations, you've finished level ".$completed_level['level']."!";
	header("Location:home.php");
	die();
}


?>

<form id="code-form" class="form-horizontal" action="submitpassword.php" method="POST">
	<div style="text-align: center">Found the password for level <?php echo $next_level ?>? Enter it here to proceed.<br/>
		<input name="password"  style="text-align:center" type="text" id="password">
	</div>
</form>

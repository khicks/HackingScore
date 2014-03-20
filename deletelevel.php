<?php
session_start();
include("config.php");
include("sessioncheck.php");

if ($_SESSION['admin'] != 1)
{
	header("Location:home.php");
	die();
}

if (!isset($_REQUEST['id']))
	die();

$sql_get_level_details = $db -> prepare("SELECT id, name, level FROM levels WHERE id=:id");
$sql_get_level_details -> bindParam(':id', $_REQUEST['id'], PDO::PARAM_INT);
$sql_get_level_details -> execute();
$level_details = $sql_get_level_details -> fetchAll(PDO::FETCH_ASSOC);
$level = $level_details[0];

if (isset($_POST['id']))
{
	$sql_delete_level = $db -> prepare("DELETE FROM levels WHERE id=:id");
	$sql_delete_level -> bindParam(':id', $_POST['id'], PDO::PARAM_INT);
	$sql_delete_level -> execute();

	$sql_move_up_levels = $db -> prepare("UPDATE levels SET level=level-1 WHERE level>:level");
	$sql_move_up_levels -> bindParam(':level', $level['level'], PDO::PARAM_INT);
	$sql_move_up_levels -> execute();

	$_SESSION['success_message'] = "Level successfully deleted.";
	header("Location:levels.php");
	die();
}

?>

<h3>Are you sure you want to delete level: "<?php echo $level['name'] ?>"?</h3>
<form id="delete-form" class="form-horizontal" action="deletelevel.php" method="POST">
	<input type="hidden" name="id" value="<?php echo $level['id'] ?>">
</form>
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

$sql_get_level_details = $db -> prepare("SELECT id, name, description, password, points, multiplier, minutes FROM levels WHERE id=:id");
$sql_get_level_details -> bindParam(':id', $_REQUEST['id'], PDO::PARAM_INT);
$sql_get_level_details -> execute();
$level_details = $sql_get_level_details -> fetchAll(PDO::FETCH_ASSOC);
$level = $level_details[0];

echo "Hai!";
if (isset($_POST['id']))
{
	$sql_update_level_details = $db -> prepare("UPDATE levels SET name=:name, description=:description, password=:password, points=:points, multiplier=:multiplier, minutes=:minutes WHERE id=:id");
	$sql_update_level_details -> bindParam(':id', $_POST['id'], PDO::PARAM_INT);
	$sql_update_level_details -> bindParam(':name', $name, PDO::PARAM_STR);
	$sql_update_level_details -> bindParam(':description', $description, PDO::PARAM_STR);
	$sql_update_level_details -> bindParam(':password', $password, PDO::PARAM_STR);
	$sql_update_level_details -> bindParam(':points', $points, PDO::PARAM_INT);
	$sql_update_level_details -> bindParam(':multiplier', $multiplier, PDO::PARAM_INT);
	$sql_update_level_details -> bindParam(':minutes', $minutes, PDO::PARAM_INT);

	if (strlen($_POST['levelname'])==0)
	{
		$_SESSION['error_message'] = "You must enter a level name.";
		header("Location:levels.php");
		die();
	}
	else
	{
		$name = str_replace("<", "&lt;", str_replace(">", "&gt", $_POST['levelname']));
	}

	if (strlen($_POST['password'])==0)
	{
		$_SESSION['error_message'] = "You must enter a password.";
		header("Location:levels.php");
		die();
	}
	else
	{
		$password = $_POST['password'];
	}

	$description = str_replace("<script", "&lt;script", $_POST['description']);

	if (!is_numeric($_POST['points']))
	{
		$_SESSION['error_message'] = "Point value must be a positive or negative integer.";
		header("Location:levels.php");
		die();
	}
	else
	{
		$points = $_POST['points'];
	}

    if (!is_numeric($_POST['multiplier']))
    {
        $_SESSION['error_message'] = "Multiplier must be a positive or negative integer.";
        header("Location:levels.php");
        die();
    }
    else
    {
        $multiplier = $_POST['multiplier'];
    }

    if (!is_numeric($_POST['minutes']))
    {
        $_SESSION['error_message'] = "Time expected must be a positive or negative integer.";
        header("Location:levels.php");
        die();
    }
    else
    {
        $minutes = $_POST['minutes'];
    }

	$sql_update_level_details -> execute();
	$_SESSION['success_message'] = "Level successfully updated.";
	header("Location:levels.php");
	die();
}

?>

<form id="edit-form" class="form-horizontal" action="editlevel.php" method="POST">
	<input type="hidden" name="id" value="<?php echo $level['id'] ?>">
	<div class="control-group">
		<label class="control-label" for="levelname">Name</label>
		<div class="controls">
			<input name="levelname" type="text" id="levelname" value="<?php echo $level['name'] ?>">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="description">Description</label>
		<div class="controls">
			<textarea name="description" rows="9" id="description"><?php echo $level['description'] ?></textarea>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="password">Password</label>
		<div class="controls">
			<input name="password" type="text" id="password" value="<?php echo $level['password'] ?>">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="points">Points awarded</label>
		<div class="controls">
			<input name="points" type="text" id="points" value="<?php echo $level['points'] ?>">
		</div>
	</div>
    <div class="control-group">
        <label class="control-label" for="multiplier">Multiplier</label>
        <div class="controls">
            <input name="multiplier" type="text" id="multiplier" value="<?php echo $level['multiplier'] ?>">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="minutes">Time expected (minutes)</label>
        <div class="controls">
            <input name="minutes" type="text" id="minutes" value="<?php echo $level['minutes'] ?>">
        </div>
    </div>
</form>
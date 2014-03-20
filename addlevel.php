<?php
session_start();
include("config.php");
include("sessioncheck.php");

if ($_SESSION['admin'] != 1)
{
	header("Location:home.php");
	die();
}

if (count($_POST)>0)
{
	if (strlen($_POST['levelname'])==0)
	{
		$_SESSION['error_message'] = "You must enter a level name.";
		header("Location:levels.php");
		die();
	}
	elseif (strlen($_POST['password'])==0)
	{
		$_SESSION['error_message'] = "You must enter a level password.";
		header("Location:levels.php");
		die();
	}
	elseif (!is_numeric($_POST['points']))
	{
		$_SESSION['error_message'] = "Point value must be a positive or negative integer.";
		header("Location:levels.php");
		die();
	}
    elseif (!is_numeric($_POST['multiplier']))
    {
        $_SESSION['error_message'] = "Multiplier must be a positive or negative integer.";
        header("Location:levels.php");
        die();
    }
    elseif (!is_numeric($_POST['minutes']))
    {
        $_SESSION['error_message'] = "Time expected must be a positive or negative integer.";
        header("Location:levels.php");
        die();
    }
	else
	{
        $name = str_replace("<script", "&lt;script", $_POST['levelname']);
		$description = str_replace("<script", "&lt;script", $_POST['description']);
		$sql_check_highest_level = $db -> prepare("SELECT level FROM levels ORDER BY level DESC LIMIT 1");
		$sql_check_highest_level -> execute();
		$highest_level = $sql_check_highest_level -> fetchAll(PDO::FETCH_ASSOC);
		if (count($highest_level)==0)
			$level = 1;
		else
			$level = $highest_level[0]['level']+1;

		$sql_add_level = $db -> prepare("INSERT INTO levels (level, name, description, password, points, multiplier, minutes) VALUES (:level, :name, :description, :password, :points, :multiplier, :minutes)");
		$sql_add_level -> bindParam(':level', $level, PDO::PARAM_INT);
		$sql_add_level -> bindParam(':name', $name, PDO::PARAM_STR);
		$sql_add_level -> bindParam(':description', $description, PDO::PARAM_STR);
		$sql_add_level -> bindParam(':password', $_POST['password'], PDO::PARAM_STR);
		$sql_add_level -> bindParam(':points', $_POST['points'], PDO::PARAM_INT);
		$sql_add_level -> bindParam(':multiplier', $_POST['multiplier'], PDO::PARAM_INT);
		$sql_add_level -> bindParam(':minutes', $_POST['minutes'], PDO::PARAM_INT);
		$sql_add_level -> execute();

		$_SESSION['success_message'] = "Level successfully added.";
		header("Location:levels.php");
		die();
	}
}
?>

<form id="add-form" class="form-horizontal" action="addlevel.php" method="POST">
	<div class="control-group">
		<label class="control-label" for="levelname">Name</label>
		<div class="controls">
			<input name="levelname" type="text" id="levelname">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="description">Description</label>
		<div class="controls">
			<textarea name="description" rows="9" id="description"></textarea>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="password">Password</label>
		<div class="controls">
			<input name="password" type="text" id="password">
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="points">Points awarded</label>
		<div class="controls">
			<input name="points" type="text" id="points">
		</div>
	</div>
    <div class="control-group">
        <label class="control-label" for="multiplier">Multiplier</label>
        <div class="controls">
            <input name="multiplier" type="text" id="multiplier">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="minutes">Time expected (minutes)</label>
        <div class="controls">
            <input name="minutes" type="text" id="minutes">
        </div>
    </div>
</form>
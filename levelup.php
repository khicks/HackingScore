<?php
session_start();
include("config.php");
include("sessioncheck.php");

if ($_SESSION['admin'] != 1)
{
	header("Location:home.php");
	die();
}

// "Up" and "down" refer to their position on the page, not their level number.

$sql_level_id = $db -> prepare("SELECT id FROM levels WHERE level=:level LIMIT 1");
$sql_level_id -> bindParam(':level', $_GET['level'], PDO::PARAM_INT);
$sql_level_id -> execute();
$level_id = $sql_level_id -> fetchAll(PDO::FETCH_ASSOC);

if (count($level_id)==0)
    die("<html><img src=\"http://i.imgur.com/R5JJxV6.png?2\"></html>");

$level_id = $level_id[0]['id'];

$sql_previous_level_down = $db -> prepare("UPDATE levels SET level = level+1 WHERE level=:level-1");
$sql_previous_level_down -> bindParam(':level', $_GET['level'], PDO::PARAM_INT);
$sql_previous_level_down -> execute();

$sql_current_level_up = $db -> prepare("UPDATE levels SET level = level-1 WHERE id=:id");
$sql_current_level_up -> bindParam(':id', $level_id, PDO::PARAM_INT);
$sql_current_level_up -> execute();

header("Location:levels.php");
die();
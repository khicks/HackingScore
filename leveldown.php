<?php
session_start();
include("config.php");
include("sessioncheck.php");

if ($_SESSION['admin'] != 1)
{
	header("Location:home.php");
	die();
}

$sql_check_highest_level = $db -> prepare("SELECT level FROM levels ORDER BY level DESC LIMIT 1");
$sql_check_highest_level -> execute();
$highest_level = $sql_check_highest_level -> fetchAll(PDO::FETCH_ASSOC);
if (count($highest_level)==0)
	$highest_level = 1;
else
	$highest_level = $highest_level[0]['level'];

if ($_GET['level']>=$highest_level || $_GET['level']<1)
	die("<html><img src=\"http://i.imgur.com/R5JJxV6.png?2\"></html>");
// "Up" and "down" refer to their position on the page, not their level number.

$sql_level_id = $db -> prepare("SELECT id FROM levels WHERE level=:level LIMIT 1");
$sql_level_id -> bindParam(':level', $_GET['level'], PDO::PARAM_INT);
$sql_level_id -> execute();
$level_id = $sql_level_id -> fetchAll(PDO::FETCH_ASSOC);
$level_id = $level_id[0]['id'];

$sql_previous_level_up = $db -> prepare("UPDATE levels SET level = level-1 WHERE level=:level+1");
$sql_previous_level_up -> bindParam(':level', $_GET['level'], PDO::PARAM_INT);
$sql_previous_level_up -> execute();

$sql_current_level_down = $db -> prepare("UPDATE levels SET level = level+1 WHERE id=:id");
$sql_current_level_down -> bindParam(':id', $level_id, PDO::PARAM_INT);
$sql_current_level_down -> execute();

header("Location:levels.php");
die();
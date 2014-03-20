<?php
session_start();
include("config.php");

$sql_destroy_session = $db -> prepare("DELETE FROM sessions WHERE php_session=:php_session");
$sql_destroy_session -> bindValue(':php_session', session_id(), PDO::PARAM_STR);
$sql_destroy_session -> execute();

session_destroy();
setcookie("PHPSESSID", "", -1);
header("Location:login.php?logout=true");

?>

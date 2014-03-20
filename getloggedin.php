<?php
session_start();
include("config.php");

$sql_check_session = $db -> prepare("SELECT uid, php_session, ip_address FROM sessions WHERE php_session=:php_session");
$sql_check_session -> bindValue(':php_session', session_id(), PDO::PARAM_STR);
$sql_check_session -> execute();
$check_session = $sql_check_session -> fetchall(PDO::FETCH_ASSOC);

if ($_SESSION['expires'] < time() || count($check_session)==0)
{
    $sql_destroy_session = $db -> prepare("DELETE FROM sessions WHERE php_session=:php_session");
    $sql_destroy_session -> bindValue(':php_session', session_id(), PDO::PARAM_STR);
    $sql_destroy_session -> execute();

    session_destroy();
    echo json_encode(array("loggedIn"=>false));
}
else echo json_encode(array("loggedIn"=>true));

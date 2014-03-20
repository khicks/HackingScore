<?php
session_start();
include("config.php");

if (isset($_SESSION['uid']))
{
	$sql_get_new_messages = $db -> prepare("SELECT count(id) AS count FROM messages WHERE to_uid=:uid AND opened=0");
	$sql_get_new_messages -> bindParam(':uid', $_SESSION['uid'], PDO::PARAM_INT);
	$sql_get_new_messages -> execute();
	$new_message_count = $sql_get_new_messages -> fetchAll(PDO::FETCH_ASSOC);
	$new_message_count = $new_message_count[0]['count'];

	echo json_encode(array("newMessages"=>$new_message_count));
}

?>
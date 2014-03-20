<?php

$debug = false;

$db_host = "yourhost";
$db_user = "youruser";
$db_pass = "yourpass";
$db_name = "yourdb";
$db = new PDO('mysql:host='.$db_host.';dbname='.$db_name.';charset=utf8', $db_user, $db_pass);

$password_salt = "YOURSALT";
$session_expiration = 30*60; //30 minutes

$sql_contest_variable = $db -> prepare("SELECT value_int, value_string, value_timestamp FROM contest_variables WHERE name=:name");
$sql_contest_variable -> bindParam(':name', $contest_variable_name, PDO::PARAM_STR);

$contest_variable_name = "contest_active";
$sql_contest_variable -> execute();
$contest_active = $sql_contest_variable -> fetchAll(PDO::FETCH_ASSOC);
$contest_active = $contest_active[0]['value_int'];

$contest_variable_name = "start_time";
$sql_contest_variable -> execute();
$contest_start = $sql_contest_variable -> fetchAll(PDO::FETCH_ASSOC);
$contest_start = $contest_start[0]['value_timestamp'];

?>

<?php

$sql_clean_sessions = $db -> prepare("DELETE FROM sessions WHERE expires<UNIX_TIMESTAMP(NOW())");
$sql_clean_sessions -> execute();

$sql_check_session = $db -> prepare("SELECT uid, php_session, ip_address FROM sessions WHERE php_session=:php_session");
$sql_check_session -> bindValue(':php_session', session_id(), PDO::PARAM_STR);
$sql_check_session -> execute();
$check_session = $sql_check_session -> fetchall(PDO::FETCH_ASSOC);

$sql_user_blocked = $db -> prepare("SELECT unblock, expiration, reason FROM block_log WHERE blocked_user=? ORDER BY timestamp DESC LIMIT 1");
$sql_user_blocked -> execute(array($_SESSION['uid']));
$user_blocked = $sql_user_blocked -> fetchall(PDO::FETCH_ASSOC);
$blocked = count($user_blocked)>0 && $user_blocked[0]['unblock'] == 0;

if($_SESSION['uid']!=1)
{
    // Universal Rickroll
	//header("Location:http://bit.ly/r8BnSp");
	//die();
}

if ($blocked)
{
    $sql_destroy_session = $db -> prepare("DELETE FROM sessions WHERE php_session=:php_session");
    $sql_destroy_session -> bindValue(':php_session', session_id(), PDO::PARAM_STR);
    $sql_destroy_session -> execute();

    session_destroy();
    setcookie("PHPSESSID", "", -1);
    header("Location:login.php?blocked=true&reason=".$user_blocked[0]['reason']);
}
elseif ($_SESSION['expires'] < time())
{
	$sql_destroy_session = $db -> prepare("DELETE FROM sessions WHERE php_session=:php_session");
	$sql_destroy_session -> bindValue(':php_session', session_id(), PDO::PARAM_STR);
	$sql_destroy_session -> execute();
	
	session_destroy();
	setcookie("PHPSESSID", "", -1);
	header("Location:login.php?expired=true");
}
elseif(count($check_session)==0)
{
	session_destroy();
	setcookie("PHPSESSID", "", -1);
	header("Location:login.php?expired=true");
}
elseif(long2ip($check_session[0]['ip_address']) != $_SERVER['REMOTE_ADDR'])
{
	$sql_destroy_session = $db -> prepare("DELETE FROM sessions WHERE php_session=:php_session");
	$sql_destroy_session -> bindValue(':php_session', session_id(), PDO::PARAM_STR);
	$sql_destroy_session -> execute();
	
	session_destroy();
	setcookie("PHPSESSID", "", -1);
	header("Location:login.php?badip=true");
}
else
{
	$_SESSION['expires'] = time() + $session_expiration;

    $sql_update_session_expiration = $db -> prepare("UPDATE sessions SET expires=:expires WHERE php_session=:php_session");
    $sql_update_session_expiration -> bindParam(':expires', $_SESSION['expires'], PDO::PARAM_INT);
    $sql_update_session_expiration -> bindValue(':php_session', session_id(), PDO::PARAM_STR);
    $sql_update_session_expiration -> execute();
}



//Update user details from db to session
$sql_user_data = $db -> prepare("SELECT username, admin FROM users WHERE uid=:uid");
$sql_user_data -> bindParam(':uid', $_SESSION['uid'], PDO::PARAM_INT);
$sql_user_data -> execute();
$user_data = $sql_user_data -> fetchall(PDO::FETCH_ASSOC);

$_SESSION['username'] = $user_data[0]['username'];
$_SESSION['admin'] = $user_data[0]['admin'];
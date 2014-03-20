<?php
if (isset($_SESSION['uid']))
{
	header("Location:home.php");
	die();
}
else
{
	header("Location:login.php");
	die();
}
?>

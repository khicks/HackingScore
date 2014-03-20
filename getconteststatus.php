<?php
session_start();
include("config.php");

if (isset($_SESSION['uid']))
{
    echo json_encode(array("active"=>($contest_active) ? true : false));
}

?>
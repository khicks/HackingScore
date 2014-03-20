<?php
session_start();
include("config.php");
include("sessioncheck.php");

if ($_SESSION['admin'] != 1)
{
    header("Location:home.php");
    die();
}

?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Contest management</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="bootstrap/css/custom.css" rel="stylesheet" media="screen">
</head>
<body style="background:#CCC">
<?php include_once("navbar.php"); ?>

<div class="container" style="margin-top:30px">
    <?php include_once("statusmessage.php"); ?>
    <div class="well">
        <h2>Contest management</h2>
        <a href="conteststate.php" data-target="#changeContest" role="button" class="btn btn-xlarge btn-block btn-<?php echo ($contest_active) ? "danger" : "success" ?>" data-toggle="modal"><?php echo ($contest_active) ? "Stop" : "Start" ?> contest</a>
    </div>
</div>

<div class="modal fade hide" id="changeContest" tabindex="-1" role="dialog" aria-labelledby="startContestLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3 id="startContestLabel">Start contest</h3>
    </div>
    <div class="modal-body">
        <p>Loading...</p>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <button id="contest-submit" name="submit" type="submit" class="btn btn-<?php echo ($contest_active) ? "danger" : "success" ?> href="#"><?php echo ($contest_active) ? "Stop" : "Start" ?> contest</button>
    </div>
</div>

<script src="https://code.jquery.com/jquery.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="custom.js"></script>
<script type="text/javascript">
    $('#changeContest').on('hidden', function () {
        $(this).removeData('modal');
    });
    $('#contest-submit').on('click', function(e){
        e.preventDefault();
        $('#contest-form').submit();
    });
</script>
</body>
</html>
<?php
session_start();
include("config.php");
include("sessioncheck.php");

if ($_SESSION['admin'] != 1)
{
    header("Location:home.php");
    die();
}

if (isset($_POST['posted']))
{
    $sql_toggle_contest_state = $db -> prepare("UPDATE contest_variables SET value_int=1-value_int WHERE name='contest_active'");
    $sql_toggle_contest_state -> execute();

    if ($_POST['reset']=="on")
    {
        $sql_reset_contest_start_time = $db -> prepare("UPDATE contest_variables SET value_timestamp=NOW() WHERE name='start_time'");
        $sql_reset_contest_start_time -> execute();
    }

    if ($contest_active)
        $_SESSION['success_message'] = "Contest successfully stopped.";
    else
        $_SESSION['success_message'] = "Contest successfully started.";
    header("Location:contest.php");
    die();
}

?>

<h4>Are you sure you want to <?php echo ($contest_active) ? "stop" : "start" ?> the contest?</h4>
<form id="contest-form" class="form-horizontal" action="conteststate.php" method="POST">
    <input type="hidden" name="posted" value="true">
    <?php if (!$contest_active) { ?>
    <div class="control-group">
        <div class="controls">
            <label class="checkbox">
                <input name="reset" type="checkbox"<?php echo ($user['admin']) ? " checked" : "" ?>>
                First start of contest
            </label>
        </div>
    </div>
    <?php } ?>
</form>
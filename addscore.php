<?php
session_start();
include("config.php");
include("sessioncheck.php");

if ($_SESSION['admin'] != 1)
{
    header("Location:home.php");
    die();
}

$sql_get_username = $db -> prepare("SELECT username FROM users WHERE uid=:uid");
$sql_get_username -> bindParam(':uid', $_GET['uid'], PDO::PARAM_INT);
$sql_get_username -> execute();
$username = $sql_get_username -> fetchAll(PDO::FETCH_ASSOC);
$username = $username[0]['username'];

if (count($_POST)>0)
{
    if (strlen($_POST['description'])==0)
    {
        $_SESSION['error_message'] = "You must enter a description.";
        header("Location:score.php");
        die();
    }
    elseif(!is_numeric($_POST['points']))
    {
        $_SESSION['error_message'] = "Point value must be a positive or negative integer.";
        header("Location:score.php");
        die();
    }
    else
    {
        $sql_add_score = $db -> prepare("INSERT INTO points (from_uid, to_uid, timestamp, description, points) VALUES (:from_uid, :to_uid, NOW(), :description, :points)");
        $sql_add_score -> bindParam(':from_uid', $_SESSION['uid'], PDO::PARAM_INT);
        $sql_add_score -> bindParam(':to_uid', $_POST['uid'], PDO::PARAM_INT);
        $sql_add_score -> bindParam(':description', $_POST['description'], PDO::PARAM_STR);
        $sql_add_score -> bindParam(':points', $_POST['points'], PDO::PARAM_INT);
        $sql_add_score -> execute();

        $_SESSION['success_message'] = "Score entry successfully added.";
        header("Location:score.php");
        die();
    }
}
?>

<form id="add-form" class="form-horizontal" action="addscore.php" method="POST">
    <input type="hidden" name="uid" value="<?php echo $_GET['uid'] ?>">
    <div class="control-group">
        <label class="control-label" for="username">Contestant</label>
        <div class="controls">
            <input type="text" id="contestant" value="<?php echo $username ?>"readonly>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="password">Description</label>
        <div class="controls">
            <input name="description" type="text" id="description">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="password2">Points</label>
        <div class="controls">
            <input name="points" type="text" id="points">
        </div>
    </div>
</form>
<?php
session_start();
include("config.php");
include("sessioncheck.php");

if (!isset($_SESSION['uid']))
{
    header("Location:login.php");
}

if ($_SESSION['admin']==1)
{
    $sql_get_contestants = $db -> prepare("SELECT uid FROM users WHERE admin=0");
    $sql_get_contestants -> execute();
    $contestants = $sql_get_contestants -> fetchAll(PDO::FETCH_ASSOC);
    foreach($contestants as $contestant)
    {
        $uid_list[] = $contestant['uid'];
    }
}
else
    $uid_list = array($_SESSION['uid']);

$sql_get_username = $db -> prepare("SELECT username FROM users WHERE uid=?");

$sql_level_points = $db -> prepare("SELECT level, name, points, multiplier, minutes, timestamp FROM progress JOIN levels ON progress.lid = levels.id WHERE uid=:uid ORDER BY timestamp");
$sql_level_points -> bindParam(':uid', $contestant_uid, PDO::PARAM_INT);

$sql_aux_points = $db -> prepare("SELECT from_uid, timestamp, description, points FROM points WHERE to_uid=:uid ORDER BY timestamp");
$sql_aux_points -> bindParam(':uid', $contestant_uid, PDO::PARAM_INT);

foreach ($uid_list as $contestant_uid)
{
    unset($previous_time);
    $score_data = array();
    $aux_data = array();

    $sql_get_username -> execute(array($contestant_uid));
    $username = $sql_get_username -> fetchAll(PDO::FETCH_ASSOC);
    $username = $username[0]['username'];

    $sql_level_points -> execute();
    $level_points = $sql_level_points -> fetchAll(PDO::FETCH_ASSOC);

    $sql_aux_points -> execute();
    $aux_points = $sql_aux_points -> fetchAll(PDO::FETCH_ASSOC);

    $total_levels = 0; $total_base = 0; $total_bonus = 0; $total_aux = 0;

    foreach ($level_points as $level)
    {
        $previous_time = isset($previous_time) ? $previous_time : $contest_start;
        $time_taken = (int)((strtotime($level['timestamp']) - strtotime($previous_time))/60);
        $time_bonus = $level['minutes'] - $time_taken;
        if($time_bonus<0) $time_bonus = 0;
        $points_awarded = (int)$level['multiplier']*($time_bonus)+$level['points'];
        $score_data[] = array("level"=>$level['level'], "name"=>$level['name'], "base_points"=>$level['points'], "multiplier"=>$level['multiplier'], "time_expected"=>$level['minutes'], "time_actual"=>$time_taken, "time_bonus"=>$time_bonus, "points_awarded"=>$points_awarded);
        $previous_time = $level['timestamp'];
        $total_base += $level['points'];
        $total_bonus += $time_bonus;
        $total_levels += $points_awarded;
    }

    foreach ($aux_points as $aux)
    {
        $sql_get_username -> execute(array($aux['from_uid']));
        $from_username = $sql_get_username -> fetchAll(PDO::FETCH_ASSOC);
        $from_username = $from_username[0]['username'];

        $aux_data[] = array("from"=>$from_username, "timestamp"=>$aux['timestamp'], "description"=>$aux['description'], "points"=>$aux['points']);
        $total_aux += $aux['points'];
    }

    $contestant_progress[] = array(
        "uid"=>$contestant_uid,
        "username"=>$username,
        "score_data"=>$score_data,
        "aux_data"=>$aux_data,
        "total_base"=>$total_base,
        "total_bonus"=>$total_bonus,
        "total_points"=>$total_levels,
        "total_aux"=>$total_aux
    );
   }

?>

<!DOCTYPE html>
<html>
<head>
    <title>Score Breakdown</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body style="background:#CCC">
    <?php include_once("navbar.php"); ?>
    <div class="container" style="margin-top:30px">
        <?php include_once("statusmessage.php"); ?>
        <div class="well">
            <h2>Score breakdown</h2>
            <?php foreach($contestant_progress as $contestant) { ?>
                <hr>
                <h3><?php echo $contestant['username'] ?></h3>
                <h4>Level progression</h4>
                <table class="table">
                    <thead>
                    <th>Level</th>
                    <th>Name</th>
                    <th style="text-align: right">Expected time</th>
                    <th style="text-align: right">Actual time</th>
                    <th style="text-align: right">Time bonus</th>
                    <th style="text-align: right">Multiplier</th>
                    <th style="text-align: right">Base points</th>
                    <th  width="50px" style="text-align: right">Total</th>
                    </thead>
                    <tbody>
                    <?php if (count($contestant['score_data'])==0) { ?>
                        <tr>
                            <td colspan="8" style="text-align: center">(You have not completed any levels.)</td>
                        </tr>
                    <?php } ?>
                    <?php foreach($contestant['score_data'] as $score) { ?>
                        <tr>
                            <td><?php echo $score['level'] ?></td>
                            <td><?php echo $score['name'] ?></td>
                            <td style="text-align: right"><?php echo $score['time_expected'] ?> minutes</td>
                            <td style="text-align: right"><?php echo $score['time_actual'] ?> minutes</td>
                            <td style="text-align: right"><?php echo $score['time_bonus'] ?></td>
                            <td style="text-align: right"><?php echo $score['multiplier'] ?></td>
                            <td style="text-align: right"><?php echo $score['base_points'] ?></td>
                            <td style="text-align: right"><?php echo $score['points_awarded'] ?></td>
                        </tr>
                    <?php } ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align: right"><b>Total</b></td>
                            <td style="text-align: right"><b><?php echo $contestant['total_points'] ?></b></td>
                        </tr>
                    </tbody>
                </table>
                <h4>Static points</h4>
                <table class="table">
                    <thead>
                    <th width="100px">From</th>
                    <th width="150px">Time</th>
                    <th>Description</th>
                    <th width="50px" style="text-align: right">Points</th>
                    </thead>
                    <tbody>
                    <?php if (count($contestant['aux_data'])==0) { ?>
                        <tr>
                            <td colspan="4" style="text-align: center">(No static points to display.)</td>
                        </tr>
                    <?php } ?>
                    <?php foreach($contestant['aux_data'] as $score) { ?>
                        <tr>
                            <td><span class="label label-success"><?php echo $score['from'] ?></span></td>
                            <td><?php echo $score['timestamp'] ?></td>
                            <td><?php echo $score['description'] ?></td>
                            <td style="text-align: right"><?php echo $score['points'] ?></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td style="text-align: right"><b>Total</b></td>
                        <td style="text-align: right"><b><?php echo $contestant['total_aux'] ?></b></td>
                    </tr>
                    </tbody>
                </table>
                <?php if ($_SESSION['admin']==1) { ?>
                <div style="text-align: right">
                    <a href="addscore.php?uid=<?php echo $contestant['uid'] ?>" data-target="#addScore" role="button" class="btn btn-primary" data-toggle="modal">Add/remove points</a>
                </div>
                <?php } ?>
                <div style="text-align: center"><h3>Grand total: <?php echo $contestant['total_points'] + $contestant['total_aux'] ?></h3></div>
            <?php } ?>
        </div>
</div>
<?php if ($_SESSION['admin']==1) { ?>
    <div class="modal fade hide" id="addScore" tabindex="-1" role="dialog" aria-labelledby="addScoreLabel" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3 id="addScoreLabel">Add/remove points</h3>
        </div>
        <div class="modal-body">
            <p>Loading...</p>
        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            <button id="add-form-submit" name="submit" type="submit" class="btn btn-primary" href="#">Submit</button>
        </div>
    </div>


<?php } ?>

<script src="https://code.jquery.com/jquery.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<?php if ($_SESSION['admin']!=1) { ?>
<script type="text/javascript">
    setTimeout('window.location.href=window.location.href;', 5000);
</script>
<?php } ?>
    <script type="text/javascript">
        $('#addScore').on('hidden', function () {
            $(this).removeData('modal');
        });
        $('#add-form-submit').on('click', function(e){
            e.preventDefault();
            $('#add-form').submit();
        });
    </script>
<script src="custom.js"></script>
</body>
</html>

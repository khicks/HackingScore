<?php
session_start();
include("config.php");
include("sessioncheck.php");

if ($_SESSION['admin'] != 1)
{
    header("Location:home.php");
    die();
}

$sql_get_contestants = $db -> prepare("SELECT uid FROM users WHERE admin=0");
$sql_get_contestants -> execute();
$contestants = $sql_get_contestants -> fetchAll(PDO::FETCH_ASSOC);
foreach($contestants as $contestant)
{
    $uid_list[] = $contestant['uid'];
}

$sql_get_username = $db -> prepare("SELECT username FROM users WHERE uid=?");

$sql_get_sessions = $db -> prepare("SELECT sid, php_session, INET_NTOA(ip_address) AS ip_address, FROM_UNIXTIME(timestamp) AS timestamp, FROM_UNIXTIME(expires) AS expires FROM sessions WHERE uid=:uid");
$sql_get_sessions -> bindParam(':uid', $contestant_uid, PDO::PARAM_INT);

foreach ($uid_list as $contestant_uid)
{
    $sql_get_username -> execute(array($contestant_uid));
    $username = $sql_get_username -> fetchAll(PDO::FETCH_ASSOC);
    $username = $username[0]['username'];

    $sql_get_sessions -> execute();
    $user_sessions = $sql_get_sessions -> fetchAll(PDO::FETCH_ASSOC);

    $all_sessions[] = array("uid"=>$contestant_uid, "username"=>$username, "sessions"=>$user_sessions);
}

?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Session Management</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body style="background:#CCC">
<?php include_once("navbar.php"); ?>
<div class="container" style="margin-top:30px">
    <?php include_once("statusmessage.php"); ?>
    <div class="well">
        <h2>Session management</h2>
        <?php foreach ($all_sessions as $session) { ?>
            <hr>
            <h3><?php echo $session['username'] ?></h3>
            <table class="table table-condensed">
                <thead>
                <th width="150px">IP address</th>
                <th>PHP session ID</th>
                <th width="150px">Login time</th>
                <th width="150px">Expires</th>
                <th width="100px" style="text-align: right">
                    <a href="endsession.php?uid=<?php echo $session['uid'] ?>" data-target="#endAll" role="button" class="btn btn-danger" data-toggle="modal">End all</a>
                </th>
                </thead>
                <tbody>
                <?php if (count($session['sessions'])==0) { ?>
                    <tr>
                        <td colspan="5" style="text-align: center">(User has no active sessions.)</td>
                    </tr>
                <?php } ?>
                <?php foreach ($session['sessions'] as $sessions) { ?>
                    <tr>
                        <td><?php echo $sessions['ip_address'] ?></td>
                        <td><?php echo $sessions['php_session'] ?></td>
                        <td><?php echo $sessions['timestamp'] ?></td>
                        <td><?php echo $sessions['expires'] ?></td>
                        <td style="text-align: right">
                            <form action="endsession.php" method="POST">
                                <input type="hidden" name="sid" value="<?php echo $sessions['sid'] ?>">
                                <input type="submit" class="btn btn-danger" value="End">
                            </form>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</div>
<div class="modal fade hide" id="endAll" style="width:800px;margin-left:-400px" tabindex="-1" role="dialog" aria-labelledby="endAllLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3 id="endAllLabel">End user sessions</h3>
    </div>
    <div class="modal-body">
        <p>Loading...</p>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <button id="end-all-submit" name="submit" type="submit" class="btn btn-danger" href="#">End all</button>
    </div>
</div>

<script src="https://code.jquery.com/jquery.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript">
    $('#endAll').on('hidden', function () {
        $(this).removeData('modal');
    });
    $('#end-all-submit').on('click', function(e){
        e.preventDefault();
        $('#end-form').submit();
    });
</script>
</body>
</html>
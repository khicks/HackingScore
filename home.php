<?php
session_start();
include("config.php");
include("sessioncheck.php");

if (!isset($_SESSION['uid']))
{
	header("Location:login.php");
}

if ($_SESSION['admin']==1 && !$contest_active)
{
    $_SESSION['warning_message'] = "Contest is inactive.";
}

$sql_contestant_list = $db -> prepare("SELECT uid, username FROM users WHERE admin=0");
$sql_contestant_list -> execute();
$contestants = $sql_contestant_list -> fetchAll(PDO::FETCH_ASSOC);

$sql_last_level = $db -> prepare("SELECT level FROM levels ORDER BY level DESC LIMIT 1");
$sql_last_level -> execute();
$last_level = $sql_last_level -> fetchAll(PDO::FETCH_ASSOC);
$last_level = $last_level[0]['level'];

$sql_current_level = $db -> prepare("SELECT levels.level FROM progress JOIN levels ON progress.lid = levels.id WHERE uid=? ORDER BY level DESC LIMIT 1");

$sql_current_level -> execute(array($_SESSION['uid']));
$my_current_level = $sql_current_level -> fetchAll(PDO::FETCH_ASSOC);
if (count($my_current_level)==0)
    $next_level_number = 1;
else
    $next_level_number = $my_current_level[0]['level']+1;

if ($next_level_number>$last_level)
    $contest_complete = true;
else
{
    $sql_next_level = $db -> prepare("SELECT level, name, description FROM levels WHERE level=:level");
    $sql_next_level -> bindParam(':level', $next_level_number, PDO::PARAM_INT);
    $sql_next_level -> execute();
    $next_level = $sql_next_level -> fetchAll(PDO::FETCH_ASSOC);
    $next_level = $next_level[0];
}

foreach($contestants as $contestant)
{
    $sql_current_level -> execute(array($contestant['uid']));
    $current_level = $sql_current_level -> fetchAll(PDO::FETCH_ASSOC);
    if (count($current_level)==0)
    {
        $percentage = 0;
        $on_level = 1;
    }
    else
    {
        $percentage = $current_level[0]['level']/$last_level * 100;
        if ($percentage==100)
            $on_level = "Complete!";
        else
            $on_level = $current_level[0]['level'] + 1;
    }
    $progress_bars[$contestant['uid']] = array("username" => $contestant['username'], "percentage" => $percentage, "on_level"=>$on_level);
}

$sql_admin_messages = $db -> prepare("SELECT id, username, subject, opened FROM messages JOIN users ON users.uid=messages.from_uid WHERE to_uid=:uid AND admin=1 ORDER BY timestamp DESC LIMIT 3");
$sql_admin_messages -> bindParam('uid', $_SESSION['uid']);
$sql_admin_messages -> execute();
$admin_messages = $sql_admin_messages -> fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE HTML>
<html>
<head>
	<title>Scoring Engine</title>
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
	<link href="bootstrap/css/custom.css" rel="stylesheet" media="screen">
</head>
<body style="background:#CCC">
	<?php include_once("navbar.php"); ?>
	<div class="container" style="margin-top:30px">
		<div id="newMessageAlert" class="alert alert-info" style="display: none">
			<img src="img/mail.png"><b>You have new messages!</b>
		</div>
		<?php include_once("statusmessage.php"); ?>
        <?php if (!$_SESSION['admin']) { ?>
        <?php if ($contest_complete) { ?>
        <div class="hero-unit">
            <h1>Congratulations!</h1>
            You have completed the contest! Sit back, relax, and help your fellow contestants if you wish.
        </div>
        <?php } elseif ($contest_active) { ?>
        <div class="well">
            <h1>Level <?php echo convert_number_to_words($next_level['level']) ?></h1>
            <h2><?php echo $next_level['name'] ?></h2>
            <?php echo $next_level['description'] ?>
        </div>
		<div class="well">
			<a href="submitpassword.php" data-target="#submitPassword" role="button" class="btn btn-xlarge btn-block btn-primary"  data-toggle="modal" onclick="checkLogin()">Submit level password</a>
		</div>
        <?php } else { ?>
        <div class="well">
            <h1>Contest inactive</h1>
            The contest has not active. If you believe there is a mistake, please tell a contest administrator.
        </div>
        <?php } } ?>
        <div class="row-fluid">
            <div class="span7">
                <div class="well">
                    <h2>Level progress</h2>
                    <table class="table">
                        <thead>
                        <th width="80px">Team</th>
                        <th>Progress</th>
                        <th width="40px" style="text-align: center">Level</th>
                        </thead>
                        <tbody>
                        <?php foreach ($progress_bars as $progress_bar) { ?>
                            <tr>
                                <td width="80px"><?php echo $progress_bar['username'] ?></td>
                                <td>
                                    <div class="progress progress-striped active">
                                        <div class="bar" style="width: <?php echo $progress_bar['percentage'] ?>%;"></div>
                                    </div>
                                </td>
                                <td width="40px" style="text-align: center"><?php echo $progress_bar['on_level'] ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="span5">
                <div class="well" style="text-align: center">
                    <div id="IframeWrapper" style="position: relative;">
                        <div id="iframeBlocker" style="position: absolute; top: 0; left: 0; width: 350px; height: 60px"></div>
                        <iframe src="https://freesecure.timeanddate.com/clock/i3vw98g7/n24/fs48/tct/pct/ftb/th2" frameborder="0" width="285" height="60" allowTransparency="true"></iframe>
                    </div>

                </div>
            </div>

            <div class="span5">
                <div class="well">
                    <h3>Admin messages</h3>
                    <table class="table table-hover">
                        <thead>
                        <th width="50px">From</th>
                        <th>Subject</th>
                        <th></th>
                        </thead>
                        <tbody>
                        <?php foreach ($admin_messages as $message) { ?>
                            <tr<?php echo ($message['opened']==0) ? " class=\"info\"" : "" ?>>
                                <td><span class="label label-success"><?php echo $message['username'] ?></span></td>
                                <td><?php echo $message['subject'] ?></td>
                                <td style="text-align:center" width="70px"><a href="readmessage.php?id=<?php echo $message['id'] ?>" class="btn btn-primary">Read</a>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
	</div>

	<div class="modal fade hide" id="submitPassword" tabindex="-1" role="dialog" aria-labelledby="submitPasswordLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 id="submitPasswordLabel">Submit</h3>
		</div>
		<div class="modal-body">
			<p>Loading...</p>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			<button id="code-form-submit" name="submit" type="submit" class="btn btn-primary" href="#">Submit</button>
		</div>
	</div>

	<script src="https://code.jquery.com/jquery.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="custom.js"></script>
    <script type="text/javascript">
		$('#submitPassword').on('hidden', function () {
		  $(this).removeData('modal');
		});
		$('#code-form-submit').on('click', function(e){
			e.preventDefault();
			$('#code-form').submit();
		  });
        function checkLogin()
        {
            var checkLoginRequest = new XMLHttpRequest();
            var url = "getloggedin.php";
			checkLoginRequest.open('GET', url, false);
			checkLoginRequest.send();
            var loggedIn = JSON.parse(checkLoginRequest.response).loggedIn;
            if (!loggedIn)
                window.location.replace("login.php?expired=true");
        }

        setInterval(checkContestStatus, 3000);
        function checkContestStatus()
        {
            var checkContestStatus = new XMLHttpRequest();
            var url = "getconteststatus.php";
            checkContestStatus.open('GET', url, false);
            checkContestStatus.send();
            var active = JSON.parse(checkContestStatus.response).active;
            if (active!=<?php echo ($contest_active) ? "true" : "false" ?>)
                window.location.replace("home.php");
        }
        <?php if ($_SESSION['username']=="damon") { ?>
        setTimeout('window.location.href=window.location.href;', 5000);
        <?php } ?>


	</script>
</body>

<?php
if ($debug)
{
	echo "Session ID: ".session_id()."<br>";
	var_dump($_SESSION);
	echo "Current time: ".time();
}

function convert_number_to_words($number) {

    $hyphen      = '-';
    $conjunction = ' and ';
    $separator   = ', ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = array(
        0                   => 'zero',
        1                   => 'one',
        2                   => 'two',
        3                   => 'three',
        4                   => 'four',
        5                   => 'five',
        6                   => 'six',
        7                   => 'seven',
        8                   => 'eight',
        9                   => 'nine',
        10                  => 'ten',
        11                  => 'eleven',
        12                  => 'twelve',
        13                  => 'thirteen',
        14                  => 'fourteen',
        15                  => 'fifteen',
        16                  => 'sixteen',
        17                  => 'seventeen',
        18                  => 'eighteen',
        19                  => 'nineteen',
        20                  => 'twenty',
        30                  => 'thirty',
        40                  => 'fourty',
        50                  => 'fifty',
        60                  => 'sixty',
        70                  => 'seventy',
        80                  => 'eighty',
        90                  => 'ninety',
        100                 => 'hundred',
        1000                => 'thousand',
        1000000             => 'million',
        1000000000          => 'billion',
        1000000000000       => 'trillion',
        1000000000000000    => 'quadrillion',
        1000000000000000000 => 'quintillion'
    );

    if (!is_numeric($number)) {
        return false;
    }

    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . convert_number_to_words(abs($number));
    }

    $string = $fraction = null;

    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = $number / 100;
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . convert_number_to_words($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= convert_number_to_words($remainder);
            }
            break;
    }

    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = array();
        foreach (str_split((string) $fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);
    }

    return $string;
}

?>

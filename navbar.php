	<div class="navbar navbar-inverse navbar-static-top">
		<div class="navbar-inner">
			<div class="container">
				<a class="brand" href="https://<?php echo $_SERVER['HTTP_HOST'] ?>/">Competition Scoring Engine</a>
				<?php if (isset($_SESSION['uid'])) { ?>
				<ul class="nav">
					<li<?php echo ($_SERVER['SCRIPT_NAME']=="/home.php") ? " class=\"active\"" : "" ?>><a href="home.php">Home</a></li>
					<li<?php echo ($_SERVER['SCRIPT_NAME']=="/messages.php") ? " class=\"active\"" : "" ?>><a href="messages.php">Messages <span id="messageBadge" class="badge badge-important" style="display: none">0</span></a></li>
					<?php if ($_SESSION['admin']==1) { ?>
						<li<?php echo ($_SERVER['SCRIPT_NAME']=="/helpqueue.php") ? " class=\"active\"" : "" ?>><a href="helpqueue.php">Help System</a></li>
					<?php } ?>
					<li<?php echo ($_SERVER['SCRIPT_NAME']=="/score.php") ? " class=\"active\"" : "" ?>><a href="score.php">Score</a></li>
					<?php if ($_SESSION['admin']==1) { ?>
					<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin<b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li<?php echo ($_SERVER['SCRIPT_NAME']=="/contest.php") ? " class=\"active\"" : "" ?>><a href="contest.php">Contest</a></li>
							<li<?php echo ($_SERVER['SCRIPT_NAME']=="/levels.php") ? " class=\"active\"" : "" ?>><a href="levels.php">Levels</a></li>
							<li<?php echo ($_SERVER['SCRIPT_NAME']=="/users.php") ? " class=\"active\"" : "" ?>><a href="users.php">Users</a></li>
							<li<?php echo ($_SERVER['SCRIPT_NAME']=="/sessions.php") ? " class=\"active\"" : "" ?>><a href="sessions.php">Sessions</a></li>
						</ul>
					</li>
					<?php } ?>
				</ul>
				<div class="pull-right">
					<div style="position:relative; top:3px; right:5px; display:inline-block; color:#999; z-index:20">Logged in as <b><?php echo $_SESSION['username'] ?></b></div>
					<a class="btn" href="logout.php">Logout</a>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>

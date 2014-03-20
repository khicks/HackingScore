<?php
session_start();
include("config.php");
include("sessioncheck.php");

if ($_SESSION['admin'] != 1)
{
	header("Location:home.php");
	die();
}

$sql_list_users = $db -> prepare("SELECT uid, username, admin FROM users ORDER BY uid");
$sql_last_login = $db -> prepare("SELECT timestamp FROM logins WHERE uid=? ORDER BY timestamp DESC LIMIT 1");
$sql_user_blocked = $db -> prepare("SELECT unblock, expiration FROM block_log WHERE blocked_user=? ORDER BY timestamp DESC LIMIT 1");

$sql_list_users -> execute();
$user_rows = $sql_list_users -> fetchall(PDO::FETCH_ASSOC);
?>

<!DOCTYPE HTML>
<html>
<head>
	<title>User Management</title>
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body style="background:#CCC">
	<?php include_once("navbar.php"); ?>
	
	<div class="container" style="margin-top:30px">
        <?php include_once("statusmessage.php"); ?>
		<div class="well">
			<h2>User management</h2>
			<table class="table table-hover" data-provides="rowlink">
				<thead>
					<th>ID</th>
					<th>Username</th>
					<th>Last login</th>
					<th>Admin</th>
					<th>Blocked</th>
					<th><a href="adduser.php" data-target="#addUser" role="button" class="btn btn-primary" data-toggle="modal" style="width:88px">Add User</a></th>
				</thead>
				<tbody>
				<?php foreach ($user_rows as $user) {
					$sql_last_login -> execute(array($user['uid']));
					$last_login = $sql_last_login -> fetchall(PDO::FETCH_ASSOC);
					$user['lastlogin'] = (count($last_login)>0) ? $last_login[0]['timestamp'] : "Never";
					
					$sql_user_blocked -> execute(array($user['uid']));
					$user_blocked = $sql_user_blocked -> fetchall(PDO::FETCH_ASSOC);
					$user['blocked'] = count($user_blocked)>0 && $user_blocked[0]['unblock'] == 0; //User is blocked if a log entry exists and unblocked is 0.
					?>
					<tr <?php if($user['blocked']) echo " class=\"error\""; elseif ($user['admin']) echo " class=\"success\""; ?>>
						<td><?php echo $user['uid'] ?></td>
						<td><?php echo $user['username'] ?></td>
						<td><?php echo $user['lastlogin'] ?></td>
						<td><?php echo ($user['admin']) ? "Yes" : "No"; ?></td>
						<td><?php echo ($user['blocked']) ? "Yes" : "No"; ?></td>
						<td><a href="edituser.php?uid=<?php echo $user['uid'] ?>" data-target="#editUser" role="button" class="btn btn-success" data-toggle="modal">Edit</a>
						<a href="blockuser.php?uid=<?php echo $user['uid'] ?>" data-target="#blockUser" role="button" class="btn btn-warning" data-toggle="modal"><?php echo ($user['blocked']) ? "Unblock" : "Block"; ?></a>
						<a href="deleteuser.php?uid=<?php echo $user['uid'] ?>" data-target="#deleteUser" role="button" class="btn btn-danger" data-toggle="modal">Delete</a></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="modal fade hide" id="addUser" tabindex="-1" role="dialog" aria-labelledby="addUserLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 id="addUserLabel">Add user</h3>
		</div>
		<div class="modal-body">
			<p>Loading...</p>
		</div>
	<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			<button id="add-form-submit" name="submit" type="submit" class="btn btn-primary" href="#">Add user</button>
		</div>
	</div>
	<div class="modal fade hide" id="editUser" tabindex="-1" role="dialog" aria-labelledby="editUserLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 id="editUserLabel">Edit user</h3>
		</div>
		<div class="modal-body">
			<p>Loading...</p>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			<button id="edit-form-submit" name="submit" type="submit" class="btn btn-success" href="#">Save changes</button>
		</div>
	</div>
	<div class="modal fade hide" id="blockUser" style="width:800px;margin-left:-400px" tabindex="-1" role="dialog" aria-labelledby="blockUserLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 id="blockUserLabel">Block/Unblock user</h3>
		</div>
		<div class="modal-body">
			<p>Loading...</p>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			<button id="block-form-submit" name="submit" type="submit" class="btn btn-warning" href="#">Apply rule</button>
		</div>
	</div>
	<div class="modal fade hide" id="deleteUser" style="width:800px;margin-left:-400px" tabindex="-1" role="dialog" aria-labelledby="deleteUserLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 id="deleteUserLabel">Delete user</h3>
		</div>
		<div class="modal-body">
			<p>Loading...</p>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			<button id="delete-form-submit" name="submit" type="submit" class="btn btn-danger" href="#">Delete</button>
		</div>
	</div>



	<script src="https://code.jquery.com/jquery.js"></script>
	<script src="bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript">
		$('#addUser').on('hidden', function () {
		  $(this).removeData('modal');
		});
		$('#add-form-submit').on('click', function(e){
			e.preventDefault();
			$('#add-form').submit();
		  });

		$('#editUser').on('hidden', function () {
		  $(this).removeData('modal');
		});
		$('#edit-form-submit').on('click', function(e){
			e.preventDefault();
			$('#edit-form').submit();
		  });

		$('#blockUser').on('hidden', function () {
		  $(this).removeData('modal');
		});
		$('#block-form-submit').on('click', function(e){
			e.preventDefault();
			$('#block-form').submit();
		});

		$('#deleteUser').on('hidden', function () {
		  $(this).removeData('modal');
		});
		$('#delete-form-submit').on('click', function(e){
			e.preventDefault();
			$('#delete-form').submit();
		});
	</script>
    <script src="custom.js"></script>
</body>
</html>
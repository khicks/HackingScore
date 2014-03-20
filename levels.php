<?php
session_start();
include("config.php");
include("sessioncheck.php");

if ($_SESSION['admin'] != 1)
{
	header("Location:home.php");
	die();
}

$sql_check_highest_level = $db -> prepare("SELECT level FROM levels ORDER BY level DESC LIMIT 1");
$sql_check_highest_level -> execute();
$highest_level = $sql_check_highest_level -> fetchAll(PDO::FETCH_ASSOC);
if (count($highest_level)==0)
	$highest_level = 1;
else
	$highest_level = $highest_level[0]['level'];

$sql_list_levels = $db -> prepare("SELECT id, level, name, description, password, points FROM levels WHERE level>0 ORDER BY level");
$sql_list_levels -> execute();
$list_levels = $sql_list_levels -> fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE HTML>
<html>
<head>
	<title>Level Management</title>
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
</head>
<body style="background:#CCC">
	<?php include_once("navbar.php"); ?>
	<div class="container" style="margin-top:30px">
        <?php include_once("statusmessage.php"); ?>
		<div class="well">
			<h2>Level management</h2>
			<table class="table table-hover" data-provides="rowlink">
				<thead>
					<th>Level</th>
					<th width="120px">Name</th>
					<th>Description</th>
					<th>Password</th>
					<th>Points</th>
					<th width="150px"><a href="addlevel.php" data-target="#addLevel" role="button" class="btn btn-primary" style="width:95px" data-toggle="modal" style="width:88px">Add Level</a></th>
				</thead>
				<tbody>
				<?php if (count($list_levels)==0) { ?>
					<tr><td colspan=6 style="text-align:center">(No levels to display.)</td></tr>
				<?php }
				foreach ($list_levels as $level) { ?>
					<tr>
						<td><?php echo $level['level'] ?></td>
						<td><?php echo $level['name'] ?></td>
						<td><?php echo $level['description'] ?></td>
						<td><?php echo $level['password'] ?></td>
						<td><?php echo $level['points'] ?></td>
						<td><a href="editlevel.php?id=<?php echo $level['id'] ?>" data-target="#editLevel" role="button" class="btn" data-toggle="modal">Edit</a>
						<a href="deletelevel.php?id=<?php echo $level['id'] ?>" data-target="#deleteLevel" role="button" class="btn btn-danger" data-toggle="modal">Delete</a></td>
						<td><?php if ($level['level']>1) { ?>
							<a href="levelup.php?level=<?php echo $level['level'] ?>"><i class="icon-arrow-up"></i></a>
							<?php } else { ?><i class="icon-"></i><?php }
							if ($level['level']<$highest_level) { ?>
							<a href="leveldown.php?level=<?php echo $level['level'] ?>"><i class="icon-arrow-down"></i></a>
							<?php } else { ?><i class="icon-"></i><?php } ?>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="modal fade hide" id="addLevel" tabindex="-1" role="dialog" aria-labelledby="addLevelLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 id="addLevelLabel">Add level</h3>
		</div>
		<div class="modal-body" style="max-height:500px">
			<p>Loading...</p>
		</div>
	<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			<button id="add-form-submit" name="submit" type="submit" class="btn btn-primary" href="#">Add level</button>
		</div>
	</div>
	<div class="modal fade hide" id="editLevel" tabindex="-1" role="dialog" aria-labelledby="editLevelLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 id="editLevelLabel">Edit level</h3>
		</div>
		<div class="modal-body" style="max-height:500px">
			<p>Loading...</p>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			<button id="edit-form-submit" name="submit" type="submit" class="btn btn-primary" href="#">Save changes</button>
		</div>
	</div>
	<div class="modal fade hide" id="deleteLevel" style="width:800px;margin-left:-400px" tabindex="-1" role="dialog" aria-labelledby="deleteLevelLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 id="deleteLevelLabel">Delete level</h3>
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
		$('#addLevel').on('hidden', function () {
		  $(this).removeData('modal');
		});
		$('#add-form-submit').on('click', function(e){
			e.preventDefault();
			$('#add-form').submit();
		  });

		$('#editLevel').on('hidden', function () {
		  $(this).removeData('modal');
		});
		$('#edit-form-submit').on('click', function(e){
			e.preventDefault();
			$('#edit-form').submit();
		  });

		$('#deleteLevel').on('hidden', function () {
		  $(this).removeData('modal');
		});
		$('#delete-form-submit').on('click', function(e){
			e.preventDefault();
			$('#delete-form').submit();
		});
	</script>
</body>
</html>

<?php
if ($debug)
{
	echo "Session ID: ".session_id()."<br>";
	var_dump($_SESSION);
	echo "Current time: ".time();
}
?>
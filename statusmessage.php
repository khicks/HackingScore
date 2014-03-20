<?php if (isset($_SESSION['success_message']))
{ ?>
	<div class="alert alert-success">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<img src="img/green_check.png"><b><?php echo $_SESSION['success_message'] ?></b>
	</div>
	<?php unset($_SESSION['success_message']);
}
elseif (isset($_SESSION['error_message']))
{ ?>
	<div class="alert alert-error">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<img src="img/red_x.png"><b><?php echo $_SESSION['error_message'] ?></b>
	</div>
<?php unset($_SESSION['error_message']);
}
elseif (isset($_SESSION['warning_message']))
{ ?>
    <div class="alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <b><?php echo $_SESSION['warning_message'] ?></b>
    </div>
    <?php unset($_SESSION['warning_message']);
}
?>
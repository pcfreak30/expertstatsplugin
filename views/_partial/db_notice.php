<?php /** @var string $db_label */ ?>
<?php /** @var string $db_table */ ?>
<?php /** @var string $type */ ?>
<div class="<?php echo $type ?> notice">
	<p>
		<?php echo $db_label . ' (' . $db_table . ') ' . $message; ?></p>
</div>

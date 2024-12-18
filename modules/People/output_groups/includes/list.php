<?php

$page = 1;
require_once __DIR__ . '/list_btn.php';
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<?php include(__DIR__."/list_filter.php"); ?>
			<div class="p_pageContent">
                <?php require __DIR__ . '/ajax.list.php'; ?>
			</div>
		</div>
	</div>
</div>

<?php $list_filter = isset($_GET['list_filter']) ? ($_GET['list_filter']) : 'all'; ?>
<script type="text/javascript">
</script>

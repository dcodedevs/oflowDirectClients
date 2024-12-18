<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	// Ownercompany list
	$sql = "SELECT * FROM ownercompany";
	$o_query = $o_main->db->query($sql);
	$ownercompany_list = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();

	if(isset($_POST['migrateData'])) {
		if($_POST['ownercompany_id'] > 0){
	        $hook_file = __DIR__ . '/../../../hooks/sync_customer_from_external.php';
	        if (file_exists($hook_file)) {
	            require_once $hook_file;
	            if (is_callable($run_hook)) {
					$hook_params = array(
						'ownercompany_id' => $_POST['ownercompany_id'],
						'update' => $_POST['update']
					);
					$hook_result = $run_hook($hook_params);
					var_dump($hook_result);
	            }
				unset($run_hook);
	        }
		} else {
			echo $formText_MissingOwnercompany_output;
		}
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >
		<?php echo $formText_Ownercompany_output;?>
		<select name="ownercompany_id" required autocomplete="off">
			<option value=""><?php echo $formText_Select_output;?></option>
			<?php
			foreach($ownercompany_list as $ownercompany) {
				?>
				<option value="<?php echo $ownercompany['id'];?>" <?php if(count($ownercompany_list) == 1) echo 'selected';?>><?php echo $ownercompany['name'];?></option>
				<?php
			}
			?>
		</select>
		<div class="formRow submitRow">
			<label for="non_existing"><?php echo $formText_ImportOnlyNonExisting_output;?></label>
			<input id="non_existing" type="radio" value="0" name="update" autocomplete="off" checked/>

			<label for="update_existing"><?php echo $formText_ImportAndSyncExisting_output;?></label>
			<input id="update_existing" type="radio" value="1" name="update" autocomplete="off"/>
			<br/>
			<br/>
		</div>
		<input type="submit" name="migrateData" value="Sync customers from external system">
	</form>
</div>

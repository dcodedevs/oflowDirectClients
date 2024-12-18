<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	// Ownercompany list
	$sql = "SELECT * FROM ownercompany";
	$o_query = $o_main->db->query($sql);
	$ownercompany_list = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();

	if(isset($_POST['migrateData'])) {
        $hook_file = __DIR__ . '/../../../hooks/send_print_file.php';
        if (file_exists($hook_file)) {
            require_once $hook_file;
            if (is_callable($run_hook)) {
				$hook_params = array(
                    'ownercompany_id' => 1,
					'zip_file_path' => ACCOUNT_PATH."/uploads/Claimletter_9614_140.zip",
					'zip_file_name' => "Claimletter_9614_140.zip"
                );
                $hook_result = $run_hook($hook_params);
				var_dump($hook_result);
            }
			unset($run_hook);
        }
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >
		<div class="formRow submitRow">

			<input type="submit" name="migrateData" value="Send test zip">

		</div>
	</form>
</div>

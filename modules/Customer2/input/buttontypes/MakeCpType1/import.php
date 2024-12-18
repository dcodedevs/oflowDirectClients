<?php

	// error_reporting(E_ALL);
	// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);
	if(isset($_POST['fixOrders'])) {
        $s_sql = "UPDATE contactperson SET type = 1 WHERE customerId > 0";
        $o_query = $o_main->db->query($s_sql);
		if($o_query){
			echo $formText_UpdatedSuccessfully_output;
		} else {
			echo $formText_ErrorOccured_Output;
		}
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">

			<input type="submit" name="fixOrders" value="Make contactpersons with customerId type 1 ">

		</div>
	</form>
</div>

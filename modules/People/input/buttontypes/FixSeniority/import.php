<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	if(isset($_POST['fixSeniority'])) {

		$o_main->db->query("UPDATE people SET
			seniority_salary = 1
			WHERE seniorityStartDate is not null && seniorityStartDate <> '' && seniorityStartDate <> '0000-00-00'");

	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<input type="submit" name="fixSeniority" value="Fix seniority">

		</div>
	</form>
</div>

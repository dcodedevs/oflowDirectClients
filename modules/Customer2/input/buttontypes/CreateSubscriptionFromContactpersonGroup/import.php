<?php

	// error_reporting(E_ALL);
	// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);
	if(isset($_POST['fixOrders'])) {
		$subscription_type = $_POST['subscription_type'];
		$subscription_subtype = $_POST['subscription_subtype'][$subscription_type];
		$contactperson_group = $_POST['contactperson_group'];
		$start_date = $_POST['start_date'];
		$stopped_date = $_POST['stopped_date'];
		if($contactperson_group > 0 && $subscription_type > 0 && $_POST['ownercompany_id'] > 0){
			$start_date_formatted = "0000-00-00";
			$stopped_date_formatted = "0000-00-00";
			if($start_date != ""){
				$start_date_formatted = date("Y-m-d", strtotime($start_date));
			}
			if($stopped_date != ""){
				$stopped_date_formatted = date("Y-m-d", strtotime($stopped_date));
			}
			$sql = "SELECT cp.* FROM contactperson_group_user p
			JOIN contactperson_group g ON g.id = p.contactperson_group_id
			JOIN contactperson cp ON cp.id = p.contactperson_id
			WHERE p.type = 1 AND (p.status = 0 OR p.status is null) AND (p.hidden = 0 OR p.hidden is null) AND p.contactperson_group_id = ?";
			$o_query = $o_main->db->query($sql, array($contactperson_group));
			$contactpersons = $o_query ? $o_query->result_array(): array();
			$subscriptionsCreated = 0;
			foreach($contactpersons as $contactperson) {
				$subscriptionName = $contactperson['name'];
				if($contactperson['middlename'] != ""){
					$subscriptionName .= " ".$contactperson['middlename'];
				}
				if($contactperson['lastname'] != ""){
					$subscriptionName .= " ".$contactperson['lastname'];
				}
				$contactpersonId = $contactperson['id'];

				$sql = "INSERT INTO subscriptionmulti SET created = NOW(),
				createdBy='".$o_main->db->escape_str($variables->loggID)."',
				subscriptiontype_id = '".$o_main->db->escape_str($subscription_type)."',
				subscriptionsubtypeId = '".$o_main->db->escape_str($subscription_subtype)."',
				startDate = '".$o_main->db->escape_str($start_date_formatted)."',
				nextRenewalDate = '".$o_main->db->escape_str($stopped_date_formatted)."',
				ownercompany_id = '".$o_main->db->escape_str($_POST['ownercompany_id'])."',
				subscriptionName = '".$o_main->db->escape_str($subscriptionName)."',
				customerId =  '".$o_main->db->escape_str($contactperson['customerId'])."',
				periodNumberOfMonths = 1";
				$o_query = $o_main->db->query($sql);
				if($o_query){
					$subscriptionmultiId = $o_main->db->insert_id();
					$subscriptionsCreated++;

					$s_sql = "INSERT INTO contactperson_role_conn SET
					created = now(),
					createdBy= ?,
					subscriptionmulti_id = ?,
					contactperson_id = ?,
					role = 0";
					$o_query = $o_main->db->query($s_sql, array($variables->loggID, $subscriptionmultiId, $contactpersonId));
					$contactperson_conn_id = $o_main->db->insert_id();
				}
			}
			echo $subscriptionsCreated." subscriptions created";
		} else {
			echo 'Missing fields';
		}
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<div style="padding: 3px 0px;">
				<label><?php echo $formText_OwnerCompany_output;?></label>
				<select name="ownercompany_id" autocomplete="off" required>
					<option value=""><?php echo $formText_Select_output;?></option>
					<?php
					$sql = "SELECT g.* FROM ownercompany g ORDER BY name";
					$o_query = $o_main->db->query($sql);
					$groups = $o_query ? $o_query->result_array(): array();
					foreach($groups as $group){
						?>
						<option value="<?php echo $group['id'];?>"><?php echo $group['name'];?></option>
						<?php
					}
					?>
				</select>
			</div>
			<div style="padding: 3px 0px;">
				<label><?php echo $formText_Group_output;?></label>
				<select name="contactperson_group" autocomplete="off" required>
					<option value=""><?php echo $formText_Select_output;?></option>
					<?php
					$sql = "SELECT g.* FROM contactperson_group g WHERE group_type = 1 ORDER BY name";
					$o_query = $o_main->db->query($sql);
					$groups = $o_query ? $o_query->result_array(): array();
					foreach($groups as $group){
						?>
						<option value="<?php echo $group['id'];?>"><?php echo $group['name'];?></option>
						<?php
					}
					?>
				</select>
			</div>
			<div style="padding: 3px 0px;">
				<label><?php echo $formText_SubscriptionType_output;?></label>
				<select class="subscriptiontypeSelect" autocomplete="off" name="subscription_type" required>
					<option value=""><?php echo $formText_Select_output;?></option>
					<?php
					$subscriptiontypes = array();

					$s_sql = "SELECT * FROM subscriptiontype WHERE activatePersonalSubscriptionConnection = 1";
					$o_query = $o_main->db->query($s_sql);
					if($o_query && $o_query->num_rows()>0) {
					    $subscriptiontypes = $o_query->result_array();
					}
					foreach($subscriptiontypes as $subscriptiontype) {
						?>
						<option value="<?php echo $subscriptiontype['id'];?>"><?php echo $subscriptiontype['name'];?></option>
						<?php
					}
					?>
				</select>
			</div>
			<?php
			foreach($subscriptiontypes as $subscriptiontype) {
				$s_sql = "SELECT * FROM subscriptiontype_subtype WHERE subscriptiontype_id = ?";
				$o_query = $o_main->db->query($s_sql, array($subscriptiontype['id']));
				$subscriptionSubTypes = $o_query ? $o_query->result_array() : array();
				if(count($subscriptionSubTypes) > 0) {
				?>
					<div  style="padding: 3px 0px;" class="subscriptionSubtype subscriptionSubtype<?php echo $subscriptiontype['id'];?>">
						<label><?php echo $formText_SubscriptionSubtype_output;?></label>
						<select autocomplete="off"  name="subscription_subtype[<?php echo $subscriptiontype['id'];?>]">
							<option value=""><?php echo $formText_Select_output;?></option>
							<?php foreach($subscriptionSubTypes as $subscriptionSubType) { ?>
								<option value="<?php echo $subscriptionSubType['id'];?>"><?php echo $subscriptionSubType['name'];?></option>
							<?php } ?>
						</select>
					</div>
				<?php } ?>
			<?php } ?>
			<div style="padding: 3px 0px;">
				<label><?php echo $formText_StartDate_output;?></label>
				<input type="text" name="start_date" class="datepicker" autocomplete="off"/>
			</div>
			<div style="padding: 3px 0px;">
				<label><?php echo $formText_NextRenewalDate_output;?></label>
				<input type="text" name="stopped_date" class="datepicker" autocomplete="off"/>
			</div>
			<input type="submit" name="fixOrders" value="Create subscriptions ">
		</div>
	</form>
</div>
<style>
.subscriptionSubtype {
	display: none;
}
</style>
<script type="text/javascript">
$(function(){
	$(".datepicker").datepicker({
		firstDay: 1,
        dateFormat: 'dd.mm.yy',
	})
	$(".subscriptiontypeSelect").change(function(){
		$(".subscriptionSubtype").hide();
		$(".subscriptionSubtype"+$(this).val()).show();
	})
})
</script>

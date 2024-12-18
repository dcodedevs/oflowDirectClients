<?php
	$people_contactperson_type = 2;
	$sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
	$o_query = $o_main->db->query($sql);
	$accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
	if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
		$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
	}
	$o_query = $o_main->db->get('accountinfo');
	$accountinfo = $o_query ? $o_query->row_array() : array();
	if(intval($accountinfo['contactperson_type_to_use_in_people']) > 0)
	{
		$people_contactperson_type = $accountinfo['contactperson_type_to_use_in_people'];
	}
	// error_reporting(E_ALL);
	// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);
	if(isset($_POST['Migrate'])) {

		$s_sql = "SELECT id,name,status,department,enable_page,enable_channel, activate_memberlist_page,activate_infopages_page,activate_filearchive_page,activate_picturegallery_page,activate_activitycalendar_page,activate_workboard_page,show_group_to_all_in_group_page,show_members_in_group_page,show_group_to_all_in_group_list,show_only_admins_in_group_list,page_module, 	display_posts_to_members, 	activate_article_page, members   FROM cache_grouplist ORDER BY id ASC";
		$o_query = $o_main->db->query($s_sql);
		$getynetGroups = $o_query ? $o_query->result_array() : array();

		foreach($getynetGroups as $getynetGroup) {
			$members = json_decode($getynetGroup['members'], true);
			unset($getynetGroup['members']);
			$getynetGroup['created'] = date("Y-m-d H:i:s");
			$getynetGroup['createdBy'] = $variables->loggID;
			$getynetGroup['group_type'] = $people_contactperson_type;

			$s_sql = "SELECT * FROM contactperson_group WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($getynetGroup['id']));
			$local_group = $o_query ? $o_query->row_array() : array();
			if($local_group){

			} else {
				$o_query = $o_main->db->insert("contactperson_group", $getynetGroup);
				$error = $o_main->db->error();
				if($error['code']!=0){
					echo $error['message']."<br/>";
				}
			}
			foreach($members as $member){
				$username = $member['username'];
				$type = $member['type'];

				$s_sql = "SELECT * FROM contactperson WHERE email = ? AND type = ?";
				$o_query = $o_main->db->query($s_sql, array($username, $people_contactperson_type));
				$contactperson = $o_query ? $o_query->row_array() : array();

				if($contactperson){
					$s_sql = "SELECT * FROM contactperson_group_user WHERE contactperson_group_id = ? AND contactperson_id = ?";
					$o_query = $o_main->db->query($s_sql, array($getynetGroup['id'], $contactperson['id']));
					$local_member = $o_query ? $o_query->row_array() : array();
					if(!$local_member){
						$s_sql = "INSERT INTO contactperson_group_user SET contactperson_group_id = ?, contactperson_id = ?, type = ?, created = NOW(), createdBy = ?, hidden = ?";
						$o_query = $o_main->db->query($s_sql, array($getynetGroup['id'], $contactperson['id'], $type, $variables->loggID, $member['hidden']));
					}
				} else {
					echo $formText_CanNotFindContactPersonFor." ".$username."</br>";
				}
			}


		}

	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">

			<input type="submit" name="Migrate" value="migrate getynet groups to local">

		</div>
	</form>
</div>

<?php
$people_contactperson_type = 2;
$sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
$o_query = $o_main->db->query($sql);
$accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
	$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
}
if(intval($variables->accountinfo['contactperson_type_to_use_in_people']) > 0)
{
	$people_contactperson_type = $variables->accountinfo['contactperson_type_to_use_in_people'];
}
$registered_group_list = array();
$v_membersystem = array();
$v_membersystem_membership = array();
$v_registered_usernames = array();
$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
$v_cache_userlist = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist as $v_user_cached_info) {
	$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
    if($v_user_cached_info['user_id'] > 0) $v_registered_usernames[] = $v_user_cached_info['username'];
	$registered_group_list[$v_user_cached_info['username']] = json_decode($v_user_cached_info['groups'], true);
}

$o_query = $o_main->db->query("SELECT * FROM cache_userlist_membershipaccess");
$v_cache_userlist_membership = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist_membership as $v_user_cached_info) {
	$v_membersystem_membership[$v_user_cached_info['username']] = $v_user_cached_info;
    if($v_user_cached_info['user_id'] > 0) $v_registered_usernames[] = $v_user_cached_info['username'];
	$registered_group_list[$v_user_cached_info['username']] = json_decode($v_user_cached_info['groups'], true);
}
$list_filter = isset($_POST['list_filter']) ? $_POST['list_filter'] : "";
$department_filter = isset($_POST['department']) ? $_POST['department'] : 0;
$employees = array();
$resultOnly = false;
$search = str_replace(" ","%",trim($o_main->db->escape_like_str($_POST['search'])));

$deleted_sql = " contactperson.content_status < 2";
if($list_filter == "deleted") {
	$deleted_sql = " contactperson.content_status = 2";
}

$s_sql = "SELECT * FROM contactperson
WHERE ".$deleted_sql." AND (CONCAT_WS(' ', contactperson.name, contactperson.middlename, contactperson.lastname) LIKE '%".$search."%' OR contactperson.external_employee_id LIKE '%".$search."%') AND contactperson.type = ? ORDER BY contactperson.name ASC";
$o_query = $o_main->db->query($s_sql, array($people_contactperson_type));
$employees = ($o_query ? $o_query->result_array() : array());

if(count($employees) == 0){
	$s_sql = "SELECT * FROM contactperson
	WHERE ".$deleted_sql." AND contactperson.title LIKE '%".$search."%' AND contactperson.type = ? GROUP BY contactperson.title";
	$o_query = $o_main->db->query($s_sql, array($people_contactperson_type));
	$employees = ($o_query ? $o_query->result_array() : array());

	?>
	<table class="table table-striped table-condensed">
		<tbody>
		<?php foreach($employees as $employee) {
			?>
			<tr>
				<td>
					<a href="#" class="tablescript" data-employeeid="<?php echo $employee['id']?>" data-employeename="<?php echo $employee['title'];?>"><?php echo $employee['title'];?></a>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<script type="text/javascript">
		$(".employeeSearchSuggestions .tablescript").unbind("click").bind("click", function(){
			var employeeName = $(this).data("employeename");
			if(employeeName != ""){
				fw_load_ajax("<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output";?>&inc_obj=list&department_filter=<?php echo $department;?>&list_filter=<?php echo $list_filter?>&search_filter="+employeeName, '', true);
			}
		})
	</script>
	<?php
} else {
	?>
	<table class="table table-striped table-condensed">
		<tbody>
		<?php foreach($employees as $employee) {
			$employee['email'] = mb_strtolower($employee['email']);

			$nameToDisplay = $employee['name'] . " ". $employee['middlename']." ".$employee['lastname'];

			$groups = array();
			$departments = array();
			$b_registered_user = false;

			if(isset($v_membersystem[$employee['email']]) || isset($v_membersystem_membership[$employee['email']]))
			{
				$member = array();

				if(isset($v_membersystem[$employee['email']])){
					$member = $v_membersystem[$employee['email']];
				} else if(isset($v_membersystem_membership[$employee['email']])){
					$member = $v_membersystem_membership[$employee['email']];
				}
				if($member['user_id'] > 0)
				{
					$b_registered_user = TRUE;
					if($member['image'] != "" && $member['image'] != null){
						$imgToDisplay = json_decode($member['image'], TRUE);
					}
					if($member['first_name'] != "") {
						$nameToDisplay = $member['first_name'] . " ". $member['middle_name']." ".$member['last_name'];
					}
					if($member['mobile'] != "") {
						$phoneToDisplay = $member['mobile'];
					}
				}
				if(isset($registered_group_list[$employee['email']]))
				{
					$allGroupsForNotRegistered = $registered_group_list[$employee['email']];
					foreach($allGroupsForNotRegistered as $groupSingleItem){
						if($groupSingleItem['department']){
							array_push($departments, $groupSingleItem);
						} else {
							array_push($groups, $groupSingleItem);
						}
					}
				}
				$employee['groups'] = $groups;
				$employee['departments'] = $departments;
			}
			$departmentIds = array();
			foreach($employee['departments'] as $dep){
				if(is_object($dep)){
					array_push($departmentIds, $dep->id);
				} else {
					array_push($departmentIds, $dep['id']);
				}
			}
			if(($department_filter && in_array($department_filter, $departmentIds)) || intval($department_filter) == 0){
				?>
				<tr>
					<td>
						<a href="#" class="tablescript" data-employeeid="<?php echo $employee['id']?>" data-employeename="<?php echo $nameToDisplay;?>"><?php echo $nameToDisplay;?></a>
					</td>
				</tr>
			<?php } ?>
		<?php } ?>
		</tbody>
	</table>
	<?php
	if($l_total_pages > 1)
	{
		?><ul class="pagination pagination-sm" style="margin:0;"><?php
		for($l_x = 0; $l_x < $l_total_pages; $l_x++)
		{
			if($l_x < 1 || ($l_x > ($l_page - 7) && $l_x < ($l_page + 7)) || $l_x >= ($l_total_pages - 1))
			{
				$b_print_space = true;
				?><li<?php echo ($l_page==$l_x ? ' class="active"' : '');?>><a href="#"><?php echo ($l_x+1);?></a></li><?php
			} else if($b_print_space) {
				$b_print_space = false;
				?><li><a onClick="javascript:return false;">...</a></li><?php
			}
		}
		?></ul><?php
	}?>
	<script type="text/javascript">
		$(".employeeSearchSuggestions .tablescript").unbind("click").bind("click", function(){
			var employeeID = $(this).data("employeeid");
			var employeeName = $(this).data("employeename");
			if(employeeID > 0){
				<?php if($_POST['detailpage']) { ?>
					fw_load_ajax("<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output";?>&inc_obj=details&cid="+employeeID, '', true);
				<?php } else { ?>
					fw_load_ajax("<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output";?>&inc_obj=list&list_filter=<?php echo $list_filter?>&search_id="+employeeID+"&search_filter="+employeeName, '', true);
				<?php } ?>
			}
		})
	</script>
<?php } ?>

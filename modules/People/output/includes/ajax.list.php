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
require_once __DIR__ . '/functions.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");
if(!function_exists("filter_email_by_domain")) include_once(__DIR__."/fnc_filter_email_by_domain.php");

$filtersList = array("search_filter","search_by", "department_filter", "projecttype_filter", "tag_view_filter");

if (isset($_POST['list_filter'])) $_GET['list_filter'] = $_POST['list_filter'];
if (isset($_POST['list_view'])) $_GET['list_view'] = $_POST['list_view'];
foreach($filtersList as $filterName){
	if (isset($_POST[$filterName])) $_GET[$filterName] = $_POST[$filterName];
}

$list_filter = isset($_GET['list_filter']) ? ($_GET['list_filter']) : $default_list;

$department_filter = isset($_GET['department_filter']) ? ($_GET['department_filter']) : 0;
$search_id = isset($_GET['search_id']) ? ($_GET['search_id']) : 0;

$list_view = isset($_GET['list_view']) ? ($_GET['list_view']) : 0;

foreach($filtersList as $filterName) {
	${$filterName} = $_GET[$filterName] ? ($_GET[$filterName]) : '';
}
if($search_filter != ""){
	$search_filter = preg_replace('!\s+!', ' ', $search_filter);
}
if($responsibleperson_filter == ''){
    $responsibleperson_filter = $currentEmployee['id'];
}

$_SESSION['list_filter'] = $list_filter;
foreach($filtersList as $filterName){
	$_SESSION[$filterName] = ${$filterName};
}

$filters = array();
foreach($filtersList as $filterName){
	if($filterName == "search_filter"){
		$filters[$filterName] = array($search_by, ${$filterName});
	} else if($filterName != "search_by") {
		$filters[$filterName] = ${$filterName};
	}
}

if(isset($_GET['page'])) {
	$page = $_GET['page'];
}
if(isset($_POST['page'])) {
	$page = $_POST['page'];
}
if(intval($page) == 0){
	$page = 1;
}
if(isset($_POST['rowOnly'])){ $rowOnly = $_POST['rowOnly']; } else { $rowOnly = NULL; }
$perPage = 500;
$showing = $page * $perPage;
$showMore = false;

$itemCount = get_customer_list_count($o_main, $list_filter, $filters);

$itemCount2 = get_customer_list_count2($o_main, $list_filter, $filters);

//get all list to filter by getynet values
$customerList = get_customer_list($o_main, $list_filter, $filters, $page, $perPage);

$currentCount = $itemCount;

$showStart = ($page-1)*$perPage;
$showEnd = $showStart+$perPage;

if($showing < $currentCount){
	$showMore = true;
}
$totalPages = ceil($currentCount/$perPage);

$personList = false;
if($s_inc_obj == "person_list" || $_POST['person_list']) {
	$personList = true;
}
include_once(__DIR__."/person_init.php");
if($v_employee_accountconfig['activateFilterByTags']){
	$customerList2 = $customerList;
	$customerList = array();
	$addedToListIds = array();
	foreach($customerList2 as $v_row) {
		if(/*in_array($v_row['crm_contactperson_id'], $filtered_contactperson_ids) &&*/ !$v_row['notVisibleInMemberOverview']) {
			if(!in_array($v_row['id'], $addedToListIds)){
				array_push($customerList, $v_row);
				array_push($addedToListIds, $v_row['id']);
			}
		}
	}
}

$_SESSION['listpagePerPage'] = $perPage;
$_SESSION['listpagePage'] = $page;

$o_query = $o_main->db->query("SELECT * FROM people_basisconfig");
$v_people_config = $o_query ? $o_query->row_array() : array();
$o_query = $o_main->db->query("SELECT * FROM people_accountconfig");
if($o_query && $o_query->num_rows()>0)
{
	$v_people_config = $o_query->row_array();
}
?>
<?php if (!$rowOnly) { ?>
<table class="gtable" id="gtable_search">
	<tr class="gtable_row table_head">
		<td class="gtable_cell gtable_cell_head"></td>
		<td class="gtable_cell gtable_cell_head"><?php echo $formText_Name_output;?></td>
		<?php if($list_view == 0){?>
			<td class="gtable_cell gtable_cell_head"></td>
			<?php if($v_employee_basisconfig['activateWorkIdCardSection']) { ?>
				<td class="gtable_cell gtable_cell_head"><?php echo $formText_WorkIdCard_output;?></td>
			<?php } ?>
			<?php if($v_employee_basisconfig['activateEmployers'] && $accessElementAllow_AddEditDeletePeople) { ?>
				<td class="gtable_cell gtable_cell_head"><?php echo $formText_Employers_output;?></td>
			<?php } else { ?>
				<?php if($people_accountconfig['activateEmployeeCode'] == 2 && $accessElementAllow_AddEditDeletePeople) { ?>
					<td class="gtable_cell gtable_cell_head"><?php echo $formText_EmployeeCode_output;?></td>
				<?php } ?>
			<?php } ?>
			<?php if($personList) { ?>
				<td class="gtable_cell gtable_cell_head"><?php echo $formText_Company_output;?></td>
			<?php } ?>
			<?php if(!$people_accountconfig['hide_departments_in_people']) { ?>
				<td class="gtable_cell gtable_cell_head"><?php echo $formText_Departments_output;?></td>
			<?php } ?>
			<?php if(!$people_accountconfig['hide_groups_in_people']) { ?>
				<td class="gtable_cell gtable_cell_head"><?php echo $formText_Groups_output;?></td>
			<?php } ?>
			<td class="gtable_cell gtable_cell_head" ><?php echo $formText_AccessLevel_output;?></td>
		<?php } else if($list_view == 1) { ?>
			<td class="gtable_cell gtable_cell_head break_none"><?php echo $formText_Seniority_output;?></td>
			<td class="gtable_cell gtable_cell_head break_none"><?php echo $formText_StartDate_output;?></td>
			<td class="gtable_cell gtable_cell_head break_none"><?php echo $formText_SeniorityYears_output;?></td>
			<td class="gtable_cell gtable_cell_head break_none"><?php echo $formText_NextAdjustmentMonth_output;?></td>
			<td class="gtable_cell gtable_cell_head break_none"><?php echo $formText_Salary_output;?></td>
		<?php } ?>
		<td class="gtable_cell gtable_cell_head lastColumn">&nbsp;</td>
	</tr>
<?php } ?>
<?php
$o_query = $o_main->db->query("SELECT * FROM accountinfo");
$v_accountinfo = $o_query ? $o_query->row_array() : array();

$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access LIMIT 1");
$v_cache_userlist_access = $o_query ? $o_query->row_array() : array();

$v_param = array(
	'COMPANY_ID'=>$companyID,
	'CACHE_TIMESTAMP'=>$v_cache_userlist_access['cache_timestamp'],
	'CACHE_RECREATE'=>strtotime($variables->accountinfo['force_cache_refresh']) > strtotime($v_cache_userlist_access['cache_timestamp']),
	'GET_MEMBERSHIPS' => 1
);
$s_response = APIconnectorUser("companyaccessbycompanyidget_v2", $variables->loggID, $variables->sessionID, $v_param);
$v_response = json_decode($s_response, TRUE);
if(isset($v_response['status']) && $v_response['status'] == 1 && $v_response['cache_status'] != 2)
{
	$o_main->db->query("TRUNCATE cache_userlist_access");
	foreach($v_response['data'] as $v_item)
	{
		$o_main->db->query("INSERT INTO cache_userlist_access SET cache_timestamp = '".$o_main->db->escape_str($v_response['cache_timestamp'])."', companyaccess_id = '".$o_main->db->escape_str($v_item['companyaccess_id'])."', user_id = '".$o_main->db->escape_str($v_item['user_id'])."', username = '".$o_main->db->escape_str($v_item['username'])."', first_name = '".$o_main->db->escape_str($v_item['first_name'])."', middle_name = '".$o_main->db->escape_str($v_item['middle_name'])."', last_name = '".$o_main->db->escape_str($v_item['last_name'])."', deactivated = '".$o_main->db->escape_str($v_item['deactivated'])."', admin = '".$o_main->db->escape_str($v_item['admin'])."', system_admin = '".$o_main->db->escape_str($v_item['system_admin'])."', groupID = '".$o_main->db->escape_str($v_item['groupID'])."', groupname = '".$o_main->db->escape_str($v_item['groupname'])."', accesslevel = '".$o_main->db->escape_str($v_item['accesslevel'])."', image = '".$o_main->db->escape_str($v_item['image'])."', mobile = '".$o_main->db->escape_str($v_item['mobile'])."', mobile_prefix = '".$o_main->db->escape_str($v_item['mobile_prefix'])."', mobile_verified = '".$o_main->db->escape_str($v_item['mobile_verified'])."', firstlogin = '".$o_main->db->escape_str($v_item['firstlogin'])."', lastlogin = '".$o_main->db->escape_str($v_item['lastlogin'])."', last_activity = '".$o_main->db->escape_str($v_item['last_activity'])."', invitationsent = '".$o_main->db->escape_str($v_item['invitationsent'])."', invitationsentnr = '".$o_main->db->escape_str($v_item['invitationsentnr'])."', `groups` = '".$o_main->db->escape_str(json_encode($v_item['groups']))."'");
	}
	$o_main->db->query("TRUNCATE cache_userlist_membershipaccess");
	foreach($v_response['data_memberships'] as $v_item)
	{
		$o_main->db->query("INSERT INTO cache_userlist_membershipaccess SET cache_timestamp = '".$o_main->db->escape_str($v_response['cache_timestamp'])."', companyaccess_id = '".$o_main->db->escape_str($v_item['companyaccess_id'])."', user_id = '".$o_main->db->escape_str($v_item['user_id'])."', username = '".$o_main->db->escape_str($v_item['username'])."', first_name = '".$o_main->db->escape_str($v_item['first_name'])."', middle_name = '".$o_main->db->escape_str($v_item['middle_name'])."', last_name = '".$o_main->db->escape_str($v_item['last_name'])."', deactivated = '".$o_main->db->escape_str($v_item['deactivated'])."', admin = '".$o_main->db->escape_str($v_item['admin'])."', system_admin = '".$o_main->db->escape_str($v_item['system_admin'])."', groupID = '".$o_main->db->escape_str($v_item['groupID'])."', groupname = '".$o_main->db->escape_str($v_item['groupname'])."', accesslevel = '".$o_main->db->escape_str($v_item['accesslevel'])."', image = '".$o_main->db->escape_str($v_item['image'])."', mobile = '".$o_main->db->escape_str($v_item['mobile'])."', mobile_prefix = '".$o_main->db->escape_str($v_item['mobile_prefix'])."', mobile_verified = '".$o_main->db->escape_str($v_item['mobile_verified'])."', firstlogin = '".$o_main->db->escape_str($v_item['firstlogin'])."', lastlogin = '".$o_main->db->escape_str($v_item['lastlogin'])."', last_activity = '".$o_main->db->escape_str($v_item['last_activity'])."', invitationsent = '".$o_main->db->escape_str($v_item['invitationsent'])."', invitationsentnr = '".$o_main->db->escape_str($v_item['invitationsentnr'])."', `groups` = '".$o_main->db->escape_str(json_encode($v_item['groups']))."'");
	}
}

$v_membersystem = array();
$v_membersystem_membership = array();
$v_registered_usernames = array();
$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
$v_cache_userlist = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist as $v_user_cached_info) {
	$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
    if($v_user_cached_info['user_id'] > 0) $v_registered_usernames[] = $v_user_cached_info['username'];
}

$o_query = $o_main->db->query("SELECT * FROM cache_userlist_membershipaccess");
$v_cache_userlist_membership = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist_membership as $v_user_cached_info) {
	$v_membersystem_membership[$v_user_cached_info['username']] = $v_user_cached_info;
    if($v_user_cached_info['user_id'] > 0) $v_registered_usernames[] = $v_user_cached_info['username'];
}
// $response = json_decode(APIconnectorUser("companyaccessbycompanyidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$companyID, 'GET_GROUPS'=>1)));
// foreach($response->data as $writeContent)
// {
// 	$v_membersystem[$writeContent->username] = $writeContent;
// 	if($writeContent->registeredID > 0) $v_registered_usernames[] = $writeContent->username;
// }

$v_not_registered_usernames = array();
foreach($customerList as $v_row)
{
	if(!in_array($v_row['email'], $v_registered_usernames) && $v_row['email'] != "") $v_not_registered_usernames[] = $v_row['email'];
}
$v_not_registered_images = array();
if(count($v_not_registered_usernames)>0)
{
	$v_response = json_decode(APIconnectorAccount("user_image_upload_get", $v_accountinfo['accountname'], $v_accountinfo['password'], array('username'=>$v_not_registered_usernames)), TRUE);
	if(isset($v_response['status']) && $v_response['status'] == 1)
	{
		$v_not_registered_images = $v_response['items'];
	}
}

function cmp($a, $b)
{
    return strcmp(mb_strtolower($a["nameToDisplay"]), mb_strtolower($b["nameToDisplay"]));
}

$customerListFiltered = array();
// filter
foreach($customerList as $v_row)
{
	$nameToDisplay = $v_row['name']." ".$v_row['middlename']." ".$v_row['lastname'];
	$phoneToDisplay = $v_row['mobile'];
	$imgToDisplay = "../elementsGlobal/avatar_placeholder.jpg";
	$currentMember = "";

	$groups = array();
	$departments = array();
	$b_registered_user = FALSE;
	//re set information for getynet registered
	if(isset($v_membersystem[$v_row['email']]) || isset($v_membersystem_membership[$v_row['email']]))
	{
		if(isset($v_membersystem[$v_row['email']])){
			$currentMember = $member = $v_membersystem[$v_row['email']];
		} else if(isset($v_membersystem_membership[$v_row['email']])){
			$currentMember = $member = $v_membersystem_membership[$v_row['email']];
		}
		if($member['user_id'] > 0)
		{
			$b_registered_user = TRUE;
			if($member['image'] != "" && $member['image'] != null){
				$imgToDisplay = json_decode($member['image'], TRUE);
			}
			if($member['first_name'] != "") {
				$nameToDisplay = $member['first_name'] . " ". $member['middle_name']." ".$member['last_name'];
				
				//sync data
				if(strtotime($v_row['updated']) <= strtotime($member['cache_timestamp'])) {
					$o_query = $o_main->db->query("UPDATE contactperson SET updated = NOW(), name = ?, middlename = ?, lastname = ? WHERE id = ?", array($member['first_name'], $member['middle_name'], $member['last_name'], $v_row['id']));
				}
			}
			if($member['mobile'] != "") {
				$phoneToDisplay = $member['mobile'];
			}
		}
	}
	if(!$b_registered_user)
	{
		if(isset($v_not_registered_images[$v_row['email']]) && $v_row['email'] != "")
		{
			if($v_not_registered_images[$v_row['email']]['image'] != '')
			{
				$imgToDisplay = json_decode($v_not_registered_images[$v_row['email']]['image'], TRUE);
			}
		}
	}

	$sql = "SELECT p.* FROM contactperson_group p
	JOIN contactperson_group_user pu ON pu.contactperson_group_id = p.id
	JOIN contactperson ON contactperson.id = pu.contactperson_id
	WHERE p.status = 1 AND p.department = 1 AND contactperson.id = ? ORDER BY p.name";
	$o_query = $o_main->db->query($sql, array($v_row['id']));
	$departments = $o_query ? $o_query->result_array(): array();

	$sql = "SELECT p.* FROM contactperson_group p
	JOIN contactperson_group_user pu ON pu.contactperson_group_id = p.id
	JOIN contactperson ON contactperson.id = pu.contactperson_id
	WHERE p.status = 1 AND (p.department = 0 OR p.department is null) AND contactperson.id = ? ORDER BY p.name";
	$o_query = $o_main->db->query($sql, array($v_row['id']));
	$groups = $o_query ? $o_query->result_array(): array();

	$v_row['groups'] = $groups;
	$v_row['departments'] = $departments;

	$nameToDisplay = trim(preg_replace('!\s+!', ' ', $nameToDisplay));
	$v_row['nameToDisplay'] = $nameToDisplay;
	$v_row['phoneToDisplay'] = $phoneToDisplay;
	$v_row['imgToDisplay'] = $imgToDisplay;

	$departmentIds = array();
	foreach($v_row['departments'] as $dep){
		if(is_object($dep)){
			array_push($departmentIds, $dep->id);
		} else {
			array_push($departmentIds, $dep['id']);
		}
	}


	// filter by departments
	if(intval($department_filter) >0) {
		if(in_array($department_filter, $departmentIds)) {
			array_push($customerListFiltered, $v_row);
		}
	} else {
		array_push($customerListFiltered, $v_row);
	}
}
//change currentShowing count if filtered
if($department_filter!="" || $search_filter !="") {
	$currentCount = count($customerListFiltered);

	if($showing < $currentCount){
		$showMore = true;
	}
	$totalPages = ceil($currentCount/$perPage);
}
$showed = 1;
$canEditAdmin = $accessElementAllow_AddEditDeletePeople;
$seniorityTypes = array(0=>"", 1=>$formText_AdjustAutomaticallyFromSeniorityDate_output, 2=> $formText_AdjustManually_output);

$salaryTypes = array(0=>$formText_Standard_output, 1=>$formText_Individual_output, 2=> $formText_SendingInvoice_output);
//sorting by name the arrays after getting local names
usort($customerListFiltered, "cmp");
foreach($customerListFiltered as $v_row)
{
	$nameToDisplay = $v_row['nameToDisplay'];
	$phoneToDisplay = $v_row['phoneToDisplay'];
	$imgToDisplay = $v_row['imgToDisplay'];
	if($imgToDisplay != "../elementsGlobal/avatar_placeholder.jpg") {
		$imgToDisplay = "https://pics.getynet.com/profileimages/".$imgToDisplay[0];
	}

	$isPersonAdmin = false;
	$isRegistered = false;
	$v_access = null;
	foreach($v_membersystem as $writeContent) {
		if(mb_strtolower($writeContent['username']) == mb_strtolower($v_row['email'])) {
			$isRegistered = true;
			if($writeContent->admin) {
				$isPersonAdmin = true;
			}
			$v_access = $writeContent;
		}
	}
	if($v_access == null){
		if($v_row['email']!="")
		{
			foreach($v_membersystem_membership as $writeContent) {
				if(mb_strtolower($writeContent['username']) == mb_strtolower($v_row['email'])) {
					$isRegistered = true;
					if($writeContent->admin) {
						$isPersonAdmin = true;
					}
					$v_access = $writeContent;
					break;
				}
			}
		}
	}
	$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id'];
	?>
	<tr class="gtable_row output-click-helper" data-href="<?php echo $s_edit_link;?>">
		<td class="gtable_cell middleAligned imageItem">
			<?php if($imgToDisplay != "") { ?>
			<div class="employeeImage">
				<img src="<?php echo $imgToDisplay; ?>" alt="<?php echo $nameToDisplay;?>" title="<?php echo $nameToDisplay;?>"/>
			</div>
			<?php } ?>
		</td>
		<td class="gtable_cell border_bottom middleAligned">
			<?php echo $nameToDisplay;?>
			<?php if($people_accountconfig['activateJobTitle'] == 2) { ?>
				<div class="jobTitle fw_icon_color"><?php echo $v_row['title']?></div>
			<?php } ?>
		</td>
		<?php if($list_view == 0){?>
			<td class="gtable_cell border_bottom middleAligned ">
				<?php if(filter_email_by_domain($v_row['email']) !="") { ?>
					<a class="link fw_text_link_color people-action email-action" href="mailto:<?php echo filter_email_by_domain($v_row['email']);?>"><span class="fas fa-at  fw_icon_color" title="<?php echo filter_email_by_domain($v_row['email']);?>"></span></a>
				<?php } ?>
				<?php if($phoneToDisplay !="") { ?>
					<span class="fas fa-phone fw_icon_color people-action" title="<?php echo $phoneToDisplay;?>"></span>
				<?php } ?>
				<?php if($v_access['user_id'] > 0) { ?>
					<span class="icon icon-chat fw_icon_color openChat" title="<?php echo $formText_OpenChat_output;?>" data-userid="<?php echo $v_access['user_id']?>"></span>
				<?php } ?><?php
				if($canEditAdmin && $v_employee_basisconfig['activateSalarySection']) {
	                $salary_list = get_salary_list($v_row, $v_employee_accountconfig, false, false, true);

					if($salary_list['salary_count'] > 0){
						?>
						<span class="salary_wrapper">
							<?php echo $salary_list['salary_count']?>
							<div class="salary_hover">
								<?php echo $salary_list['output'];?>

								<?php
								echo "<b>".$formText_Seniority_Output."</b><br/>";
								if($v_row['seniority_salary'] == 0) {
		                            echo $formText_NotActivated_output;
		                        } else if($v_row['seniority_salary'] == 1) {
		                            echo $formText_AdjustAutomaticallyFromSeniorityDate_output." ";
		                            if($v_row['seniorityStartDate'] != "0000-00-00" && $v_row['seniorityStartDate'] != null) echo date("d.m.Y", strtotime($v_row['seniorityStartDate']));
		                        } else if($v_row['seniority_salary'] == 2) {
		                            echo $formText_AdjustManually_output." ";
		                            echo $v_row['seniority_years']." ".$formText_Years_Output." ";
		                            if($v_row['seniority_reminder_consider_new_adjustment_from_date'] != "0000-00-00" && $v_row['seniority_reminder_consider_new_adjustment_from_date'] != null)
		                            echo " - ".$formText_NextAdjustmentMonth_output." ".date("m.Y", strtotime($v_row['seniority_reminder_consider_new_adjustment_from_date']));
		                            echo "<br/>".nl2br($v_row['seniority_note']);
		                        }
								?>
							</div>
						</span>
					<?php } ?>
				<?php } ?>
			</td>
			<?php if($v_employee_basisconfig['activateWorkIdCardSection']) { ?>
				<td class="gtable_cell border_bottom middleAligned">
					<?php if($v_row['workIdCardExpireDate'] != "" && $v_row['workIdCardExpireDate'] != "0000-00-00"){
					if(time() > strtotime($v_row['workIdCardExpireDate'])) { echo '<span class="red">';}
					echo date("d.m.Y", strtotime($v_row['workIdCardExpireDate']));
					if(time() > strtotime($v_row['workIdCardExpireDate'])) { echo '</span>';}
					}?>
				</td>
			<?php } ?>

			<?php if($v_employee_basisconfig['activateEmployers'] && $accessElementAllow_AddEditDeletePeople) { ?>
				<td class="gtable_cell border_bottom middleAligned">
					<?php
					$s_sql = "SELECT * FROM people_employerconnection WHERE peopleId = ?";
	                $o_result = $o_main->db->query($s_sql, array($v_row['id']));
	                $employersConnections = $o_result ? $o_result->result_array() : array();
	                foreach($employersConnections as $employersConnection) {
	                    $s_sql = "SELECT * FROM repeatingorder_employers WHERE content_status < 2 AND id = ? ORDER BY name ASC";
	                    $o_query = $o_main->db->query($s_sql, array($employersConnection['employerId']));
	                    $employer = ($o_query ? $o_query->row_array() : array());
						?>
                        <div class="employer-name"><?php echo $employer['name'];?> - <?php echo $employersConnection['accountingEmployeeId'];?></div>
						<?php
					}
					?>
				</td>
			<?php } else { ?>
				<?php if($people_accountconfig['activateEmployeeCode'] == 2 && $accessElementAllow_AddEditDeletePeople) { ?>
					<td class="gtable_cell border_bottom middleAligned"><?php echo $v_row['external_employee_id'];?></td>
				<?php } ?>
			<?php } ?>

			<?php if($personList) { ?>
				<td class="gtable_cell border_bottom middleAligned">
					<?php
					$sql = "SELECT c.* FROM customer c
					JOIN contactperson ON contactperson.customerId = c.id
					WHERE contactperson.id = ? AND (notVisibleInMemberOverview = 0 OR notVisibleInMemberOverview is null) ORDER BY c.name";
					$o_query = $o_main->db->query($sql, array($v_row['id']));
					$customers = $o_query ? $o_query->result_array(): array();

					// $s_sql = "SELECT * FROM people_crm_contactperson_connection WHERE people_id = ? AND (notVisibleInMemberOverview = 0 OR notVisibleInMemberOverview is null)";
	                // $o_result = $o_main->db->query($s_sql, array($v_row['id']));
	                // $crm_connections = $o_result ? $o_result->result_array() : array();
	                foreach($customers as $customer) {
						?>
                        <div class="employer-name"><?php
						echo $customer['name']." ".$customer['middlename']." ".$customer['lastname'];
						// if(isset($crmCustomersShowOnPersonTab[$crm_connection['crm_customer_id']])) echo $crmCustomersShowOnPersonTab[$crm_connection['crm_customer_id']]['subscriptionTypeName'];
						// if(isset($crmCustomers[$crm_connection['crm_customer_id']])) echo $crmCustomers[$crm_connection['crm_customer_id']]['name'];
						?></div>
						<?php
					}

					?>
				</td>
			<?php } ?>
			<?php if(!$people_accountconfig['hide_departments_in_people']) { ?>
			<td class="gtable_cell border_bottom middleAligned">
				<?php
				$departmentShown = 1;
				foreach($v_row['departments'] as $group) {
					?>
					<div class="<?php if($departmentShown > 3) echo 'extraRow';?>"><?php echo is_object($group) ? $group->name : $group['name'];?></div>
					<?php
					$departmentShown++;
				}?>
				<?php if(count($v_row['departments']) > 3) { ?>
					<div class="seeAllDepartments seeElements fw_text_link_color view-changer"><?php echo $formText_SeeAll_output;?>(<?php echo count($v_row['departments']);?>)</div>
					<div class="hideAllDepartments hideElements fw_text_link_color view-changer"><?php echo $formText_Hide_output;?></div>
				<?php } ?>
			</td>
			<?php } ?>
			<?php if(!$people_accountconfig['hide_groups_in_people']) { ?>
			<td class="gtable_cell border_bottom middleAligned">
				<?php
				$groupShown = 1;
				foreach($v_row['groups'] as $group) {
					?>
					<div class="<?php if($groupShown > 3) echo 'extraRow';?>"><?php echo is_object($group) ? $group->name : $group['name'];?></div>
					<?php
					$groupShown++;
				}?>
				<?php if(count($v_row['groups']) > 3) { ?>
					<div class="seeAllDepartments seeElements fw_text_link_color view-changer"><?php echo $formText_SeeAll_output;?>(<?php echo count($v_row['groups']);?>)</div>
					<div class="hideAllDepartments hideElements fw_text_link_color view-changer"><?php echo $formText_Hide_output;?></div>
				<?php } ?>
			</td>
			<?php } ?>
			<td class="gtable_cell border_bottom middleAligned rightAlignedCell" width="15%">
				<?php
				if($list_filter != "deleted" && $list_filter != "inactive"){
					if($accessElementAllow_GiveRemoveAccessPeople) {
						?>
						<div class="output-access-loader" data-id="<?php echo $v_row['id']?>" data-email="<?php echo $v_row['email'];?>" data-membersystem-id="<?php echo $v_row['id'];?>">
							<div class="output-access-changer"><?php
							if($isRegistered)
							{
								$v_invitations = explode(",", $v_access['invitationsent']);
								$v_access['invitationsent'] = '';
								foreach($v_invitations as $s_invitation)
								{
									$v_access['invitationsent'] .= ($v_access['invitationsent']!=''?', ':'').date("d.m.Y", strtotime($s_invitation));
								}
								$s_icon = "green";
								if($v_access['user_id'] == 0) $s_icon = "green_grey";
								?><img src="<?php echo $extradir."/output/elementsOutput/access_key_".$s_icon;?>.png" /><?php
								?>
					            <?php if(!$v_employee_accountconfig['duplicate_module']) {
									if(!$personList || ($personList && !$v_employee_accountconfig['personTabHideGivingAccess'])) {
										?>
										<div class="output-access-dropdown">
											<div class="script fw_text_link_color" onClick="javascript:output_access_remove(this,'<?php echo $v_row['id'];?>');" data-delete-msg="<?php echo $formText_RemoveAccess_Output.": ".$v_row['email'];?>?">
												<?php echo $formText_RemoveAccess_Output;?>
											</div>
											<?php /*?><div>
												<?php
												if($v_access['last_activity'] != "0000-00-00 00:00:00" && $v_access['last_activity'] != null)
													echo $formText_LastActivity_Output.": ".date("d.m.Y H:i", strtotime($v_access['last_activity']));
												if($v_access['firstlogin'] == "0000-00-00 00:00:00")
													echo $formText_NeverLoggedIn_Output;
												?>
											</div><?php */?>
											<!-- <div><?php echo $formText_InvitationSent_Output.': '.$v_access['invitationsent'];?></div> -->
											<div class="script fw_text_link_color" onClick="javascript:output_access_grant(this,'<?php echo $v_row['id'];?>');"><?php echo $formText_ResendInvitation_Output;?></div>

											<div class="script fw_text_link_color" onClick="javascript:output_access_grant_no_sending(this,'<?php echo $v_row['id'];?>');"><?php echo $formText_EditAccess_Output;?></div>
										</div>
									<?php } ?>
								<?php } ?>
								<?php
								if($v_access['accesslevel'] == 1){
									?>
									<div class="accesslevel"><?php echo $formText_AccessAll_output;?></div>
									<?php
								} else if($v_access['accesslevel'] == 2) {
									?>
									<div class="accesslevel"><?php echo $formText_SpecificAccess_output;?></div>
									<?php
								} else if($v_access['accesslevel'] == 0) {
									?>
									<div class="accesslevel"><?php echo $formText_NoAccess_output;?></div>
									<?php
								} else if($v_access['accesslevel'] == 3){
									?>
									<div class="accesslevel"><?php echo $formText_GroupAccess_output;?> - <?php echo $v_access['groupname'];?></div>
									<?php
								} else if($v_access['accesslevel'] == 4){
									?>
									<div class="accesslevel"><?php echo $formText_MembershipAccess_output;?></div>
									<?php
								}
							} else {
								?>
					            <?php if(!$v_employee_accountconfig['duplicate_module']) {
									?>
								   	<img src="<?php echo $extradir;?>/output/elementsOutput/access_key_grey.png" />
									<?php
								   if(!$personList || ($personList && !$v_employee_accountconfig['personTabHideGivingAccess'])) {?>
									   <div class="output-access-dropdown"><div class="script fw_text_link_color" onClick="javascript:output_access_grant(this,'<?php echo $v_row['id'];?>');"><?php echo $formText_GiveAccess_Output;?></div></div>
								<?php }
								}
							}
							?>

							</div>
						</div>
						<?php
						if($isPersonAdmin) {
							?>
							<span class="administratorLabel" title="<?php echo $formText_Administrator_output;?>">A</span>
							<?php
						}
						?>
					<?php } else if($isPersonAdmin){ ?>
						<?php echo $formText_Administrator_output;?>
					<?php } ?>
				<?php } ?>
			</td>
		<?php } else if($list_view == 1) { ?>
			<td class="gtable_cell border_bottom break_none">
				<?php echo $seniorityTypes[intval($v_row['seniority_salary'])];?>
				<?php if(intval($v_row['seniority_salary']) == 2 && $v_row['seniority_note'] != "") { ?>
					<div class="seniorityNoteWrapper">
						<span class="fas fa-info-circle note_hover"></span>
						<div class="noteHoverToDisplay">
							<?php echo nl2br($v_row['seniority_note']);?>
						</div>
					</div>
				<?php } ?>
			</td>
			<td class="gtable_cell border_bottom break_none"><?php if(intval($v_row['seniority_salary']) == 1) if($v_row['seniorityStartDate'] != "" && $v_row['seniorityStartDate'] != "0000-00-00") echo date("d.m.Y", strtotime($v_row['seniorityStartDate']));?></td>
			<td class="gtable_cell border_bottom break_none"><?php if(intval($v_row['seniority_salary']) == 2) echo $v_row['seniority_years'];?></td>
			<td class="gtable_cell border_bottom break_none"><?php if(intval($v_row['seniority_salary']) == 2) if($v_row['seniority_reminder_consider_new_adjustment_from_date'] != "" && $v_row['seniority_reminder_consider_new_adjustment_from_date'] != "0000-00-00") echo date("d.m.Y", strtotime($v_row['seniority_reminder_consider_new_adjustment_from_date']));;?></td>
			<td class="gtable_cell border_bottom break_none ">
				<?php
				$workLeaderSql = "SELECT peoplesalary.* FROM peoplesalary
		            WHERE peoplesalary.peopleId = ?";
		        $findWorkLeaders = $o_main->db->query($workLeaderSql, array($v_row['id']));
				$salaries = $findWorkLeaders ? $findWorkLeaders->result_array() : array();
				if(count($salaries) == 0){
					echo $salaryTypes[intval($salarySingle['stdOrIndividualRate'])];
				} else {
					foreach($salaries as $salarySingle) {
						?>
						<div class="salaryInfoWrapper">
							<?php
							if($salarySingle['stdOrIndividualRate'] == 0){
								if($salarySingle['standardwagerate_group_id'] > 0){
									$wageData_sql = $o_main->db->query("SELECT * FROM standardwagerate_group WHERE id = ? ORDER BY name", array($salarySingle['standardwagerate_group_id']));
									$rateGroup = $wageData_sql ? $wageData_sql->row_array() : array();
									echo $rateGroup['name'];
								} else {
									echo $salaryTypes[intval($salarySingle['stdOrIndividualRate'])];
								}
							} else {
								echo $salaryTypes[intval($salarySingle['stdOrIndividualRate'])];
							}
							if($salarySingle['stdOrIndividualRate'] == 0 && $salarySingle['standardwagerate_group_id'] == 0){

							} else {
							?>
								<div class="salaryHoverToDisplay">
									<?php
									switch(intval($salarySingle['stdOrIndividualRate'])){
										case 0:
											$wageData_sql = $o_main->db->query("SELECT * FROM standardwagerate WHERE id = ?", array($salarySingle['standardWageRateId']));
											$wageData = $wageData_sql ? $wageData_sql->row_array() : array();
											if(!$wageData){
												if($salarySingle['standardwagerate_group_id'] == 0){
													$s_sql = "SELECT * FROM standardwagerate_group WHERE default_group =  1 ORDER BY id DESC";
													$o_query = $o_main->db->query($s_sql);
													$defaultStandardWageRateGroup = ($o_query ? $o_query->row_array() : array());
													if(!$defaultStandardWageRateGroup){
														$s_sql = "SELECT * FROM standardwagerate_group ORDER BY id ASC";
														$o_query = $o_main->db->query($s_sql);
														$defaultStandardWageRateGroup = ($o_query ? $o_query->row_array() : array());
													}
												} else {
													$s_sql = "SELECT * FROM standardwagerate_group WHERE id = ? ORDER BY id DESC";
													$o_query = $o_main->db->query($s_sql, array($salarySingle['standardwagerate_group_id']));
													$defaultStandardWageRateGroup = ($o_query ? $o_query->row_array() : array());
												}
												$s_sql = "SELECT * FROM standardwagerate WHERE default_salary_repeatingorder =  1 AND standartwagerate_group_id = ? ORDER BY id DESC";
												$o_query = $o_main->db->query($s_sql, array($defaultStandardWageRateGroup['id']));
												$wageData = ($o_query ? $o_query->row_array() : array());
												if(!$wageData){
													$s_sql = "SELECT * FROM standardwagerate WHERE standartwagerate_group_id = ? ORDER BY id DESC";
													$o_query = $o_main->db->query($s_sql, array($defaultStandardWageRateGroup['id']));
													$wageData = ($o_query ? $o_query->row_array() : array());
												}
											}
											if($wageData) {
		                                        $seniorityYears = 0;
		                                        if($v_row['seniority_salary'] == 1){
		                                        	$seniorityStartDate = $v_row['seniorityStartDate'];
		                                        	if($seniorityStartDate != "" && $seniorityStartDate != "0000-00-00") {
		                                        		$d1 = new DateTime($date);
		                                        		$d2 = new DateTime($seniorityStartDate);
		                                        		$diff = $d2->diff($d1);

		                                        		$seniorityYears = $diff->y;
		                                        	}
		                                        } else if($v_row['seniority_salary'] == 2){
		                                            $seniorityYears = $v_row['seniority_years'];
		                                        }


			                                    $s_sql = "SELECT * FROM standardwagerateinperiod_seniority
			                                        WHERE standardwagerateinperiod_seniority.standardwagerate_id = ? AND standardwagerateinperiod_seniority.seniority_years <= ?
			                                        ORDER BY standardwagerateinperiod_seniority.seniority_years DESC";
			                                    $o_query = $o_main->db->query($s_sql, array($wageData['id'], $seniorityYears));
			                                    $wageDataPeriodSeniority = $o_query ? $o_query->row_array() : array();
		                                        echo $wageData['name']." ".number_format($wageDataPeriodSeniority['amount'], 2, ",", "");;
											}
										break;
										case 1:
											echo $formText_HourlyRate_Output." ".number_format($salarySingle['rate'], 2, ",", "");
										break;
										case 2:
											echo $formText_HourlyRate_Output." ".number_format($salarySingle['hourlyRate'], 2, ",", "");
										break;
									}
									?>
								</div>
							<?php } ?>
						</div>
						<?php
					}
				}
				?>
			</td>
		<?php } ?>
		<td class="gtable_cell middleAligned lastColumn">&nbsp;</td>
	</tr>
	<?php
} ?>
<script type="text/javascript">
	$(document).off('click', '.output-click-helper');
	$(".output-click-helper").on("click", function(e){
		if((!$(e.target).hasClass("output-access-loader") && $(e.target).parents(".output-access-loader").length == 0)
		&& (!$(e.target).hasClass("view-changer") && $(e.target).parents(".view-changer").length == 0)
		&& (!$(e.target).hasClass("openChat") && $(e.target).parents(".openChat").length == 0)
		&& (!$(e.target).hasClass("salary_wrapper") && $(e.target).parents(".salary_wrapper").length == 0)
		&& (!$(e.target).hasClass("link") && $(e.target).parents(".link").length == 0)){
			fw_load_ajax($(this).data('href'),'',true);
		}
	})
	$(".seeAllDepartments").on("click", function(){
		$(this).parents(".gtable_cell").toggleClass("active");
	})
	$(".hideAllDepartments").on("click", function(){
		$(this).parents(".gtable_cell").toggleClass("active");
	})
	$(".openChat").on('click', function(){
		var userId = $(this).data("userid");
	    if(fwchat != undefined && userId > 0){
	        fwchat.showChat(userId);
	    }
	})
	<?php if($department_filter!="" || $search_filter !="") {?>
		$(".filteredCountRow .selectionCount").html(<?php echo $currentCount?>);
		$(".filteredCountRow").show();
	<?php } else { ?>
		$(".filteredCountRow").hide();
	<?php } ?>
</script>
<?php if (!$rowOnly) { ?>
</table>
<?php if($totalPages > 1) {
	$currentPage = $page;
	$pages = array();
	array_push($pages, 1);
	if(!in_array($currentPage, $pages)){
		array_push($pages, $currentPage);
	}
	if(!in_array($totalPages, $pages)){
		array_push($pages, $totalPages);
	}
	for ($y = 10; $y <= $totalPages; $y+=10){
		if(!in_array($y, $pages)){
			array_push($pages, $y);
		}
	}
	for($x = 1; $x <= 3;$x++){
		$prevPage = $page - $x;
		$nextPage = $page + $x;
		if($prevPage > 0){
			if(!in_array($prevPage, $pages)){
				array_push($pages, $prevPage);
			}
		}
		if($nextPage <= $totalPages){
			if(!in_array($nextPage, $pages)){
				array_push($pages, $nextPage);
			}
		}
	}
	asort($pages);
	?>
    <div class="paginationWrapper showMoreCustomers">
		<?php echo $formText_Showing_Output;?> <span class="showing"><?php echo $showing;?></span> <?php echo $formText_Of_output." ".$currentCount;?>
		<a href="#" class="showMoreCustomersBtn fw_text_link_color"><?php echo $formText_ShowMore_output;?></a>
	</div>
<?php } ?>
<script type="text/javascript">
var page = 1;
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed: 0,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
        if($(this).hasClass("close-reload")){
            loadView("list", {list_filter: '<?php echo $list_filter?>', list_view: '<?php echo $list_view;?>'});
        }
		$(this).removeClass('opened');
	}
};
$(function() {
	$(document).off('mouseenter mouseleave', '.output-access-changer')
	.on('mouseenter', '.output-access-changer', function(){
		$(this).find(".output-access-dropdown").show();
	}).on('mouseleave', '.output-access-changer', function(){
		$(this).find(".output-access-dropdown").hide();
	});


    $('.page-link').on('click', function(e) {
		var page = $(this).data("page");
        e.preventDefault();
        var data = {
            department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
			page: page,
            tag_view_filter: $('.tagViewFilter').val(),
			list_view: '<?php echo $list_view;?>'
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent').html(json.html);
        });
    });

	$(".editInvitationButton").on('click', function(e){
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_invitation";?>',
			data: { fwajax: 1, fw_nocss: 1, cid: $(this).data('id') },
			success: function(obj){
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(obj.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			}
		});
	});

	$('.showMoreCustomersBtn').on('click', function(e) {
		var currentCount = '<?php echo $currentCount?>';
        page = parseInt(page)+1;
        e.preventDefault();
        var data = {
			department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
            page: page,
            rowOnly: 1,
			person_list: '<?php echo $personList?>',
            tag_view_filter: $('.tagViewFilter').val(),
			list_view: '<?php echo $list_view;?>'
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent .gtable').append(json.html);
            $(".showMoreCustomers .showing").html($(".gtable .gtable_row.output-click-helper").length);
            if(json.html.replace(" ", "") == "" || $(".gtable .gtable_row.output-click-helper").length == currentCount){
                $(".showMoreCustomersBtn").hide();
            }
        });
    });

	$('.exportPeople').on('click', function(e) {
        e.preventDefault();
		var data = {
			fwajax: 1,
			fw_nocss: 1
		};
		submit_post_via_hidden_form('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=exportPeople"; ?>', data);
    });
});
function output_access_load()
{
	var _items = $('.output-access-loader.load');
	if(_items.length > 0)
	{
		var _this = _items.get(0);
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=access_status";?>',
			data: { fwajax: 1, fw_nocss: 1, contactperson_id: $(_this).data('id'), membersystem_id: $(_this).data('membersystem-id'), email: $(_this).data('email') },
			success: function(obj){
				if(obj.html != ""){
					$(_this).removeClass("load").html(obj.html);
					setTimeout(output_access_load,1);
				}
			}
		});
	}
}
function output_access_grant(_this, id)
{
	fw_loading_start();
	$(_this).closest(".output-access-loader").addClass("load");
	$.ajax({
		cache: false,
		type: 'POST',
		dataType: 'json',
		url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=send_invitation_preview";?>',
		data: { fwajax: 1, fw_nocss: 1, cid: id },
		success: function(obj){
			fw_loading_end();
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(obj.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
			$(window).resize();
		}
	});
}
function output_access_grant_no_sending(_this, id)
{
	fw_loading_start();
	$(_this).closest(".output-access-loader").addClass("load");
	$.ajax({
		cache: false,
		type: 'POST',
		dataType: 'json',
		url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=send_invitation_preview";?>',
		data: { fwajax: 1, fw_nocss: 1, cid: id, noinvitiation:1 },
		success: function(obj){
			fw_loading_end();
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(obj.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
			$(window).resize();
		}
	});
}
function output_access_remove(_this, id)
{
	if(!fw_click_instance)
	{
		fw_click_instance = true;
		bootbox.confirm({
			message:$(_this).attr("data-delete-msg"),
			buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
			callback: function(result){
				if(result)
				{
					fw_loading_start();
					$(_this).closest(".output-access-loader").addClass("load");
					$.ajax({
						cache: false,
						type: 'POST',
						dataType: 'json',
						url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=remove_access";?>',
						data: { fwajax: 1, fw_nocss: 1, cid: id },
						success: function(obj){
							fw_loading_end();
							$('#popupeditboxcontent').html('');
							$('#popupeditboxcontent').html(obj.html);
							out_popup = $('#popupeditbox').bPopup(out_popup_options);
							$("#popupeditbox:not(.opened)").remove();
							output_access_load();
							$(window).resize();
						}
					});
				}
				fw_click_instance = false;
			}
		});
	}
}
function submit_post_via_hidden_form(url, params) {
    var f = $("<form method='POST' target='_blank' style='display:none;'></form>").attr({
        action: url
    }).appendTo(document.body);
    for (var i in params) {
        if (params.hasOwnProperty(i)) {
            $('<input type="hidden" />').attr({
                name: i,
                value: params[i]
            }).appendTo(f);
        }
    }
    f.submit();
    f.remove();
}

</script>
<?php
}

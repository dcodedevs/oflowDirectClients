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

    $sql = "SELECT p.* FROM contactperson p WHERE p.email = ? AND p.content_status < 2 AND p.type = ?";
    $o_query = $o_main->db->query($sql, array($variables->loggID, $people_contactperson_type));
    $currentContactPerson = $o_query ? $o_query->row_array(): array();

    $groupId = isset($_POST['groupId']) ? $_POST['groupId'] : 0;
    $v_membersystem_un = array();

	$o_query = $o_main->db->query("SELECT * FROM cache_userlist_membershipaccess");
	$v_cache_userlist_membership = $o_query ? $o_query->result_array() : array();
	foreach($v_cache_userlist_membership as $v_user_cached_info) {
		$v_membersystem_un[$v_user_cached_info['username']] = $v_user_cached_info;
    	$registered_group_list[$v_user_cached_info['username']] = json_decode($v_user_cached_info['groups']);
	}
    $o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
    $v_cache_userlist = $o_query ? $o_query->result_array() : array();
    foreach($v_cache_userlist as $v_user_cached_info) {
    	$v_membersystem_un[$v_user_cached_info['username']] = $v_user_cached_info;
    	$registered_group_list[$v_user_cached_info['username']] = json_decode($v_user_cached_info['groups']);
    }

    if(!function_exists("filter_email_by_domain")) include_once(__DIR__."/fnc_filter_email_by_domain.php");
    function cmp($a, $b)
    {
        return strcmp(mb_strtolower($a["nameToDisplay"]), mb_strtolower($b["nameToDisplay"]));
    }
?>
<table class="gtable" id="gtable_search">
    <?php
	$isMember = false;
    $isAdmin = false;
    $members = array();

	$sql = "SELECT p.* FROM contactperson_group p WHERE p.id = ?";
	$o_query = $o_main->db->query($sql, array($groupId));
	$v_row = $o_query ? $o_query->row_array(): array();

	$sql = "SELECT p.* FROM contactperson_group_user p WHERE p.contactperson_id = ?";
	$o_query = $o_main->db->query($sql, array($currentContactPerson['id']));
	$currentContactPersonMember = $o_query ? $o_query->row_array(): array();
	if($currentContactPersonMember){
		$isMember = true;
		if($member['type'] == 2){
			$isAdmin = true;
		}
	}

	$s_sql = "select * from people_accountconfig";
	$o_query = $o_main->db->query($s_sql);
	$v_employee_accountconfig = ($o_query ? $o_query->row_array() : array());

	$sql = "SELECT * FROM people_basisconfig ORDER BY id";
	$o_query = $o_main->db->query($sql);
	$v_employee_basisconfig = $o_query ? $o_query->row_array() : array();

	foreach($v_employee_accountconfig as $key=>$value){
	    if($value > 0){
	        $v_employee_basisconfig[$key] = ($value - 1);
	    }
	}
	$cp_sql_where = "";
	if($v_employee_basisconfig['show_only_persons_marked_to_show_in_intranet'] == 1){
		$cp_sql_where .= " AND c.show_in_intranet = 1";
	}

    $show_hidden = intval($_POST['hidden']);
    $show_admin = intval($_POST['show_admin']);
    $canAdd = true;
    if($show_hidden) {
        if(!$isAdmin){
            $canAdd = false;
        }
    }
	if($show_hidden){
		$cp_sql_where .= " AND p.hidden = 1";
	} else {
		$cp_sql_where .= " AND (p.hidden = 0 OR p.hidden is null)";
	}
	if($show_admin){
		$cp_sql_where .= " AND p.type = 2";
	}

	if($v_row['show_only_admins_in_group_list']){
		$cp_sql_where .= " AND p.type = 2";
	}
	$search_filter = isset($_POST['search']) ? trim($_POST['search']) : '';
	if($search_filter != "") {
        $search_filter_reg = str_replace(" ", "|",$search_filter);
		$cp_sql_where .= " AND (c.name REGEXP '".$search_filter_reg."' OR c.middlename REGEXP '".$search_filter_reg."' OR c.lastname REGEXP '".$search_filter_reg."' OR c.mobile LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR c.email LIKE '%".$o_main->db->escape_like_str($search_filter)."%')";
	}
	$perPage = 50;
	$page = isset($_POST['page']) ? $_POST['page'] : 1;
	$offset = ($page-1)*$perPage;

	$pager = " LIMIT ".$perPage." OFFSET ".$offset;

	$notvisible_sql = "";
	if($people_contactperson_type != 2){
		$notvisible_sql = " AND (c.notVisibleInMemberOverview = 0 OR c.notVisibleInMemberOverview is null)";
	}

	$sql = "SELECT p.* FROM contactperson_group_user p JOIN contactperson c ON c.id = p.contactperson_id
	WHERE p.contactperson_group_id = ? AND (p.status = 0 OR p.status is null) AND c.content_status < 2
	".$notvisible_sql.$cp_sql_where."
	ORDER BY c.name ASC";

	$o_query = $o_main->db->query($sql, array($groupId));
	$totalCount = $o_query ? $o_query->num_rows(): 0;
	$totalPages = ceil($totalCount/$perPage);
	$sql = $sql.$pager;
	$o_query = $o_main->db->query($sql, array($groupId));
	$members = $o_query ? $o_query->result_array(): array();


    $v_registered_usernames = array();
    $finalMembersToShow = array();
    if($canAdd){
        foreach($members as $member) {
	        $sql = "SELECT p.* FROM contactperson p WHERE p.id = ?";
	        $o_query = $o_main->db->query($sql, array($member['contactperson_id']));
	        $peopleData = $o_query ? $o_query->row_array() : array();
            if($peopleData['content_status'] >= 2) continue;
            foreach($v_membersystem_un as $writeContent) {
                if(mb_strtolower($writeContent['username']) == mb_strtolower($peopleData['email'])) {
                    if($writeContent['user_id'] > 0) $v_registered_usernames[] = mb_strtolower($writeContent['username']);
                }
            }
            if($member['name'] != ""){
                $nameToDisplay = $member['name'] . " ". $member['middle_name']." ".$member["last_name"];
            } else {
                $nameToDisplay = $peopleData['name']." ".$peopleData['middlename']." ".$peopleData['lastname'];
            }
            $member['nameToDisplay'] = $nameToDisplay;
            $member['peopleData'] = $peopleData;
            array_push($finalMembersToShow, $member);
        }
    }
    //sorting by name the arrays after getting local names
    usort($finalMembersToShow, "cmp");

    $v_not_registered_usernames = array();
    foreach($finalMembersToShow as $member)
    {
		$sql = "SELECT p.* FROM contactperson p WHERE p.id = ?";
		$o_query = $o_main->db->query($sql, array($member['contactperson_id']));
		$peopleData = $o_query ? $o_query->row_array() : array();
        if(!in_array($peopleData['email'], $v_registered_usernames) && $peopleData['email'] != "") $v_not_registered_usernames[] = mb_strtolower($peopleData['email']);
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
    foreach($finalMembersToShow as $member) {
		$sql = "SELECT p.* FROM contactperson p WHERE p.id = ?";
		$o_query = $o_main->db->query($sql, array($member['contactperson_id']));
		$peopleData = $o_query ? $o_query->row_array() : array();

        $phoneToDisplay = $peopleData['mobile'];

        $imgToDisplay = "../elementsGlobal/avatar_placeholder.jpg";
        $v_access = null;

        foreach($v_membersystem_un as $writeContent) {
            if(mb_strtolower($writeContent['username']) == mb_strtolower($peopleData['email'])) {
                $v_access = $writeContent;
                break;
            }
        }
        $nameToDisplay = $peopleData['name']." ".$peopleData['middlename']." ".$peopleData['lastname'];

        $currentMember = "";
        $people_getynet_id = "";
        if($v_access['image'] != "" && $v_access['image'] != null){
            $imgToDisplay = json_decode($v_access['image'],true);
            $imgToDisplay = "https://pics.getynet.com/profileimages/".$imgToDisplay[0];
        }
        if(!$v_access){
            if(isset($v_not_registered_images[$peopleData['email']])  && $peopleData['email'] != "")
            {
                if($v_not_registered_images[$peopleData['email']]['image'] != '')
                {
                    $imgToDisplay = json_decode($v_not_registered_images[$peopleData['email']]['image'], TRUE);
                    $imgToDisplay = "https://pics.getynet.com/profileimages/".$imgToDisplay[0];
                }
            }
        }
        if($member['name'] != ""){
            $nameToDisplay = $member['name']." ".$member['middle_name']." ".$member['last_name'];
        }
        if($member['mobile'] != "") {
            $phoneToDisplay = $member['mobile'];
        }
        $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$peopleData['id'];

        ?>
        <tr class="gtable_row <?php if($member['type'] == 2) {?> administrator<?php } ?> output-click-helper"  data-href="<?php echo $s_edit_link;?>">
            <td class="gtable_cell middleAligned imagetd imageItem">
                <?php if($imgToDisplay != "") { ?>
                <div class="employeeImage">
                    <img src="<?php echo $imgToDisplay; ?>" alt="<?php echo $nameToDisplay;?>" title="<?php echo $nameToDisplay;?>"/>
                </div>
                <?php } ?>
            </td>
            <td class="gtable_cell border_bottom middleAligned">
                <?php if($member['type'] == 2) {?>
                    (<?php echo $formText_Administrator?>)<br/>
                <?php } ?>
                <?php echo $nameToDisplay;?>
            </td>
            <td class="gtable_cell border_bottom middleAligned ">
                <?php if($v_access['user_id'] > 0) { ?>
                    <span class="icon icon-chat fw_icon_color openChat" title="<?php echo $formText_OpenChat_output;?>" data-userid="<?php echo $v_access['user_id']?>"></span>
                <?php } ?>
            </td>
            <td class="gtable_cell border_bottom middleAligned"><a class="link fw_text_link_color" href="mailto:<?php echo filter_email_by_domain($member['username']);?>"><?php echo filter_email_by_domain($member['username']);?></a></td>
            <td class="gtable_cell border_bottom middleAligned"><?php echo $phoneToDisplay;?></td>
            <td class="gtable_cell border_bottom middleAligned">
            </td>
            <?php if($isAdmin) {?>
                <td class="gtable_cell border_bottom middleAligned rightAligned noRightPadding actionColumn">
                    <span class="glyphicon glyphicon-pencil editMember fw_delete_edit_icon_color" data-groupid="<?php echo $groupId?>"  data-groupuser_connection_id="<?php echo $member['id']?>"></span>
                    <span class="glyphicon glyphicon-trash deleteMember fw_delete_edit_icon_color" data-groupid="<?php echo $groupId?>"  data-contactperson_id="<?php echo $member['contactperson_id']?>"></span>
                </td>
            <?php } ?>
        </tr>
    <?php } ?>
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
	<?php foreach($pages as $pageItem) {?>
		<a href="#" data-page="<?php echo $pageItem?>" data-groupid="<?php echo $groupId?>" data-hidden="<?php echo $show_hidden;?>" data-admin="<?php echo $show_admin;?>" class="page-link"><?php echo $pageItem;?></a>
	<?php } ?>
	<?php /*
	<div class="showMoreCustomers"><?php echo $formText_Showing_Output;?> <span class="showing"><?php echo $showing;?></span> <?php echo $formText_Of_output." ".$currentCount;?> <a href="#" class="showMoreCustomersBtn"><?php echo $formText_ShowMore_output;?></a> </div>*/?>
<?php } ?>

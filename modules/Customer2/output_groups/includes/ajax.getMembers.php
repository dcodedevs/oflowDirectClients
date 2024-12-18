<?php
    $groupId = $_POST['groupId'] ? $_POST['groupId'] : 0;

    if(!function_exists("filter_email_by_domain")) include_once(__DIR__."/fnc_filter_email_by_domain.php");

    $sql = "SELECT p.* FROM contactperson p WHERE p.email = ? AND p.content_status < 2";
    $o_query = $o_main->db->query($sql, array($variables->loggID));
    $currentContactPerson = $o_query ? $o_query->row_array(): array();

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

    $show_hidden = intval($_POST['hidden']);
    $show_admin = intval($_POST['show_admin']);
    if($show_hidden){
        $cp_sql_where .= " AND p.hidden = 1";
    } else {
        $cp_sql_where .= " AND (p.hidden = 0 OR p.hidden is null)";
    }
    if($show_admin){
        $cp_sql_where .= " AND p.type = 2";
    }
    $cp_sql_where = "";
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

    $sql = "SELECT p.* FROM contactperson_group_user p LEFT OUTER JOIN contactperson c ON c.id = p.contactperson_id
	LEFT OUTER JOIN customer cus ON cus.id = c.customerId
    WHERE p.contactperson_group_id = ? AND (p.status = 0 OR p.status is null)
    AND c.content_status < 2 ".$cp_sql_where." AND (cus.id is null OR cus.content_status < 2)
    ORDER BY c.name ASC";

    $o_query = $o_main->db->query($sql, array($groupId));
    $totalCount = $o_query ? $o_query->num_rows(): 0;
    $totalPages = ceil($totalCount/$perPage);
    $sql = $sql.$pager;
    $o_query = $o_main->db->query($sql, array($groupId));
    $members = $o_query ? $o_query->result_array(): array();

    $v_registered_usernames = array();
    $finalMembersToShow = array();
    foreach($members as $member) {
        $sql = "SELECT p.* FROM contactperson p WHERE p.id = ?";
        $o_query = $o_main->db->query($sql, array($member['contactperson_id']));
        $peopleData = $o_query ? $o_query->row_array() : array();
        foreach($v_membersystem_un as $writeContent) {
            if(mb_strtolower($writeContent['username']) == mb_strtolower($peopleData['email'])) {
                if($writeContent['registeredID'] > 0) $v_registered_usernames[] = mb_strtolower($writeContent['username']);
            }
        }
        if($member['name'] != ""){
            $nameToDisplay = $member['name'] . " ". $member['middle_name']." ".$member["last_name"];
        } else {
            $nameToDisplay = $peopleData['name']." ".$peopleData['middlename']." ".$peopleData['lastname'];
        }
        $member['nameToDisplay'] = $nameToDisplay;
        $member['peopleData'] = $peopleData;
        $member['username'] = $peopleData['email'];
        array_push($finalMembersToShow, $member);
    }
    //sorting by name the arrays after getting local names
    usort($finalMembersToShow, "cmp");

    $v_not_registered_usernames = array();
    foreach($finalMembersToShow as $member)
    {
        if(!in_array($member['username'], $v_registered_usernames)) $v_not_registered_usernames[] = mb_strtolower($member['username']);
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

        $imgToDisplay = "../elementsGlobal/avatar_placeholder.jpg";
        $v_access = null;
        //$v_membersystem_un defined in readAccessElements;
        foreach($v_membersystem_un as $writeContent) {
            if(mb_strtolower($writeContent['username']) == mb_strtolower($member['username'])) {
                $v_access = $writeContent;
                break;
            }
        }
        $nameToDisplay = $peopleData['name']." ".$peopleData['middlename']." ".$peopleData['lastname'];

        $currentMember = "";
        $people_getynet_id = "";

        if($member['image'] != "" && $member['image'] != null){
            $imgToDisplay = json_decode($member['image'],true);
            $imgToDisplay = "https://pics.getynet.com/profileimages/".$imgToDisplay[0];
        }
        if(!$v_access){
            if(isset($v_not_registered_images[$member['username']]))
            {
                if($v_not_registered_images[$member['username']]['image'] != '')
                {
                    $imgToDisplay = json_decode($v_not_registered_images[$member['username']]['image'], TRUE);
                    $imgToDisplay = "https://pics.getynet.com/profileimages/".$imgToDisplay[0];
                }
            }
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
                <?php if($v_access['registeredID'] > 0) { ?>
                    <span class="icon icon-chat fw_icon_color openChat" title="<?php echo $formText_OpenChat_output;?>" data-userid="<?php echo $v_access['registeredID']?>"></span>
                <?php } ?>
            </td>
            <td class="gtable_cell border_bottom middleAligned"><a class="link fw_text_link_color" href="mailto:<?php echo filter_email_by_domain($member['username']);?>"><?php echo filter_email_by_domain($member['username']);?></a></td>
            <td class="gtable_cell border_bottom middleAligned"><?php echo $member['mobile'];?></td>
            <td class="gtable_cell border_bottom middleAligned">
            </td>
            <?php if($isAdmin) {?>
                <td class="gtable_cell border_bottom middleAligned rightAligned noRightPadding actionColumn">
                    <span class="glyphicon glyphicon-pencil editMember fw_delete_edit_icon_color" data-groupid="<?php echo $groupId?>" data-contactperson_id="<?php echo $member['contactperson_id']?>"></span>
                    <span class="glyphicon glyphicon-trash deleteMember fw_delete_edit_icon_color" data-groupid="<?php echo $groupId?>" data-contactperson_id="<?php echo $member['contactperson_id']?>"></span>
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

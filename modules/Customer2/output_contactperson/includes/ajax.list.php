<?php

require_once __DIR__ . '/functions.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");
if(!function_exists("filter_email_by_domain")) include_once(__DIR__."/fnc_filter_email_by_domain.php");

$filtersList = array("search_filter","search_by", "department_filter", "projecttype_filter", "group_filter");

if ($_POST['list_filter']) $_GET['list_filter'] = $_POST['list_filter'];
foreach($filtersList as $filterName){
	if ($_POST[$filterName]) $_GET[$filterName] = $_POST[$filterName];
}

$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : $default_list;

$department_filter = $_GET['department_filter'] ? ($_GET['department_filter']) : 0;

foreach($filtersList as $filterName){
	${$filterName} = $_GET[$filterName] ? ($_GET[$filterName]) : '';
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


$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $customer_basisconfig = $o_query->row_array();
}

$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}
if(!function_exists("rewriteCustomerBasisconfig")) include_once(__DIR__."/../../output/includes/fnc_rewritebasisconfig.php");
rewriteCustomerBasisconfig();

// $itemCount = get_customer_list_count($o_main, $list_filter, $filters);
//
// $itemCount2 = get_customer_list_count2($o_main, $list_filter, $filters);

//get all list to filter by getynet values
$customerList = get_customer_list($o_main, $list_filter, $filters);

if(isset($_GET['page'])) {
	$page = $_GET['page'];
}
if(isset($_POST['page'])) {
	$page = $_POST['page'];
}
if(intval($page) == 0){
	$page = 1;
}
$rowOnly = $_POST['rowOnly'];
$perPage = 100;
$showing = $page * $perPage;
$showMore = false;
$currentCount = count($customerList);

$showStart = ($page-1)*$perPage;
$showEnd = $showStart+$perPage;

if($showing < $currentCount){
	$showMore = true;
}
$totalPages = ceil($currentCount/$perPage);

$_SESSION['listpagePerPage'] = $perPage;
$_SESSION['listpagePage'] = $page;
?>
<?php if (!$rowOnly) { ?>
<table class="gtable " id="gtable_search">
	<tr class="gtable_row table_head">
		<td class="gtable_cell gtable_cell_head"></td>
		<td class="gtable_cell gtable_cell_head"><?php echo $formText_Name_output;?></td>
		<td class="gtable_cell gtable_cell_head"></td>
		<td class="gtable_cell gtable_cell_head"><?php echo $formText_Email_output;?></td>
		<td class="gtable_cell gtable_cell_head"><?php echo $formText_Phone_output;?></td>
		<?php if($people_accountconfig['activateEmployeeCode'] == 2) { ?>
			<td class="gtable_cell gtable_cell_head"><?php echo $formText_EmployeeCode_output;?></td>
		<?php } ?>
		<td class="gtable_cell gtable_cell_head"><?php echo $formText_Customers_output;?></td>
		<?php if(1 == $v_customer_accountconfig['activatePersonalMembership']) { ?>
			<td class="gtable_cell gtable_cell_head"><?php echo $formText_PersonalMembership_output;?></td>
		<?php } ?>
		<td class="gtable_cell gtable_cell_head"><?php echo $formText_Groups_output;?></td>
		<td class="gtable_cell gtable_cell_head" width="20%"><?php echo $formText_AccessLevel_output;?></td>
		<td class="gtable_cell gtable_cell_head lastColumn">&nbsp;</td>
	</tr>
<?php } ?>
<?php
$o_query = $o_main->db->query("SELECT * FROM accountinfo");
$v_accountinfo = $o_query ? $o_query->row_array() : array();
$response = json_decode(APIconnectorUser("companyaccessbycompanyidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$companyID, 'GET_GROUPS'=>1)));
$v_membersystem = array();
$v_registered_usernames = array();
foreach($response->data as $writeContent)
{
	$v_membersystem[$writeContent->username] = $writeContent;
	if($writeContent->registeredID > 0) $v_registered_usernames[] = $writeContent->username;
}
$v_not_registered_usernames = array();
foreach($customerList as $v_row)
{
	if(!in_array($v_row['email'], $v_registered_usernames)) $v_not_registered_usernames[] = $v_row['email'];
}
$v_not_registered_images = array();
$not_registered_group_list = array();
if(count($v_not_registered_usernames)>0)
{
	$v_response = json_decode(APIconnectorAccount("user_image_upload_get", $v_accountinfo['accountname'], $v_accountinfo['password'], array('username'=>$v_not_registered_usernames)), TRUE);
	if(isset($v_response['status']) && $v_response['status'] == 1)
	{
		$v_not_registered_images = $v_response['items'];
	}
	$v_response = json_decode(APIconnectorUser("group_get_list_by_filter", $variables->loggID, $variables->sessionID, array('company_id'=>$_GET['companyID'], 'usernames'=>$v_not_registered_usernames)),true);
	if(isset($v_response['status']) && $v_response['status'] == 1)
	{
		$not_registered_group_list = $v_response['items'];
	}

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
	if(isset($v_membersystem[$v_row['email']]))
	{
		$currentMember = $member = $v_membersystem[$v_row['email']];
		if($member->registeredID > 0)
		{
			$b_registered_user = TRUE;
			if($member->image != "" && $member->image != null){
				$imgToDisplay = json_decode($member->image, TRUE);
			}
			if($member->first_name != ""){
				$nameToDisplay = $member->first_name . " ". $member->middle_name." ".$member->last_name;
			}
			if($member->mobile != "") {
				$phoneToDisplay = $member->mobile;
			}
		}
		$groups = array();
		$departments = array();
		foreach($member->groups as $groupSingle){
			if(intval($groupSingle->department) == 0) {
				array_push($groups, $groupSingle);
			} else {
				array_push($departments, $groupSingle);
			}
		}
		$v_row['groups'] = $groups;
		$v_row['departments'] = $departments;
	}
	if(!$b_registered_user)
	{
		if(isset($v_not_registered_images[$v_row['email']]))
		{
			if($v_not_registered_images[$v_row['email']]['image'] != '')
			{
				$imgToDisplay = json_decode($v_not_registered_images[$v_row['email']]['image'], TRUE);
			}
		}
		if(isset($not_registered_group_list[$v_row['email']]))
		{
			$groups = array();
			$departments = array();
			$allGroupsForNotRegistered = $not_registered_group_list[$v_row['email']];
			foreach($allGroupsForNotRegistered as $groupSingleItem){
				if($groupSingleItem['department']){
					array_push($departments, $groupSingleItem);
				} else {
					array_push($groups, $groupSingleItem);
				}
			}
			$v_row['groups'] = $groups;
			$v_row['departments'] = $departments;
		}
	}

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
	if(($department_filter && in_array($department_filter, $departmentIds)) || intval($department_filter) == 0) {
		if($search_filter == ""){
			array_push($customerListFiltered, $v_row);
		} else {
			if(strpos(strtolower($v_row['nameToDisplay']), strtolower($search_filter)) !== false || strpos($v_row['phoneToDisplay'], $search_filter) !== false) {
				array_push($customerListFiltered, $v_row);
			}
		}
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
foreach($customerListFiltered as $v_row)
{
	if($showed > $showStart && $showed <= $showEnd) {
		$nameToDisplay = $v_row['nameToDisplay'];
		$phoneToDisplay = $v_row['phoneToDisplay'];
		$imgToDisplay = $v_row['imgToDisplay'];
		if($imgToDisplay != "../elementsGlobal/avatar_placeholder.jpg") {
			$imgToDisplay = "https://pics.getynet.com/profileimages/".$imgToDisplay[0];
		}
		$customers = array();
		$o_query = $o_main->db->query("SELECT * FROM customer WHERE customer.id = ".$v_row['customerId']);
		$customers = $o_query ? $o_query->result_array() : array();
		$isRegistered = false;
		$v_access = null;
		foreach($v_membersystem as $writeContent) {
			if(mb_strtolower($writeContent->username) == mb_strtolower($v_row['email'])) {
				$isRegistered = true;
			}
		}
		$v_access_multiple = $response->data;
		foreach($v_access_multiple as $v_access_single) {
			if(mb_strtolower($v_row['email']) == mb_strtolower($v_access_single->username)) {
				$v_access = $v_access_single;
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
			<td class="gtable_cell border_bottom middleAligned ">
				<?php if($v_access->registeredID > 0) { ?>
					<span class="icon icon-chat fw_icon_color openChat" title="<?php echo $formText_OpenChat_output;?>" data-userid="<?php echo $v_access->registeredID?>"></span>
				<?php } ?>
			</td>
			<td class="gtable_cell border_bottom middleAligned"><a class="link fw_text_link_color" href="mailto:<?php echo filter_email_by_domain($v_row['email']);?>"><?php echo filter_email_by_domain($v_row['email']);?></a></td>
			<td class="gtable_cell border_bottom middleAligned"><?php echo $phoneToDisplay;?></td>
			<?php if($people_accountconfig['activateEmployeeCode'] == 2) { ?>
				<td class="gtable_cell border_bottom middleAligned"><?php echo $v_row['external_employee_id'];?></td>
			<?php } ?>
			<td class="gtable_cell border_bottom middleAligned">
				<?php
				$link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=";
				foreach($customers as $customer) {
					echo '<a class="link fw_text_link_color" href="'.$link.$customer['id'].'">' . $customer['name']."</a></br>";
				}?>
			</td>

			<?php if(1 == $v_customer_accountconfig['activatePersonalMembership']) {
				$sql = "SELECT subscriptiontype.name as subscriptionTypeName, subscriptiontype_subtype.name as subscriptionSubtypeName FROM subscriptionmulti
				LEFT OUTER JOIN subscriptiontype ON subscriptiontype.id = subscriptionmulti.subscriptiontype_id
				LEFT OUTER JOIN subscriptiontype_subtype ON subscriptiontype_subtype.id = subscriptionmulti.subscriptionsubtypeId
				LEFT OUTER JOIN contactperson_role_conn ON contactperson_role_conn.subscriptionmulti_id = subscriptionmulti.id
				WHERE subscriptiontype.activatePersonalSubscriptionConnection = 1 AND contactperson_role_conn.contactperson_id = '".$o_main->db->escape_str($v_row['id'])."'";
				$o_query = $o_main->db->query($sql);
				$personalMemberships = $o_query ? $o_query->result_array(): array();
				?>
			<td  class="gtable_cell border_bottom middleAligned"><?php
			foreach($personalMemberships as $personalMembership) {
				echo $personalMembership['subscriptionTypeName']."</br>".$personalMembership['subscriptionSubtypeName'];
			}
			?></td>
			<?php } ?>
			<?php if(!$people_accountconfig['hide_groups_in_people']) { ?>
			<td class="gtable_cell border_bottom middleAligned">
				<?php
				$sql = "SELECT g.* FROM contactperson_group_user p
				JOIN contactperson_group g ON g.id = p.contactperson_group_id
				WHERE p.type = 1 AND g.group_type = 1 AND (p.status = 0 OR p.status is null) AND (p.hidden = 0 OR p.hidden is null) AND p.contactperson_id = ?";
				$o_query = $o_main->db->query($sql, array($v_row['id']));
				$groups = $o_query ? $o_query->result_array(): array();

				echo '<span class="editContactpersonGroups" data-contactperson-id="'.$v_row['id'].'" data-customer-id="'.$v_row['customerId'].'">'.count($groups).'</span>';
				?>

				<?php
				/*
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
				<?php }*/ ?>
			</td>
			<?php } ?>
			<td class="gtable_cell border_bottom middleAligned rightAlignedCell" width="20%">
				<div class="output-access-loader" data-id="<?php echo $v_row['id']?>" data-email="<?php echo $v_row['email'];?>" data-membersystem-id="<?php echo $v_row['id'];?>">
					<div class="output-access-changer"><?php
					if($isRegistered)
					{
						$v_invitations = explode(",", $v_access->invitationsent);
						$v_access->invitationsent = '';
						foreach($v_invitations as $s_invitation)
						{
							$v_access->invitationsent .= ($v_access->invitationsent!=''?', ':'').date("d.m.Y", strtotime($s_invitation));
						}
						$s_icon = "green";
						if($v_access->registeredID == 0) $s_icon = "green_grey";
						?><img src="<?php echo $extradir."/output/elementsOutput/access_key_".$s_icon;?>.png" /><?php
						?>
						<?php
						if($v_access->accesslevel == 1){
							?>
							<div class="accesslevel"><?php echo $formText_AccessAll_output;?></div>
							<?php
						} else if($v_access->accesslevel == 2) {
							?>
							<div class="accesslevel"><?php echo $formText_SpecificAccess_output;?></div>
							<?php
						} else if($v_access->accesslevel == 0) {
							?>
							<div class="accesslevel"><?php echo $formText_NoAccess_output;?></div>
							<?php
						} else {
							?>
							<div class="accesslevel"><?php echo $formText_GroupAccess_output;?> - <?php echo $v_access->groupname;?></div>
							<?php
						}
					} else {
						?><img src="<?php echo $extradir;?>/output/elementsOutput/access_key_grey.png" /><?php
						?>
						<div class="accesslevel"><?php echo $formText_NoAccess_output;?></div>
						<?php
					}
					?>

					</div>
				</div>
			</td>
			<td class="gtable_cell middleAligned lastColumn">&nbsp;</td>
		</tr>
		<?php
	}
	$showed++;
} ?>
<style>
.editContactpersonGroups {
    cursor: pointer;
    color: #46b2e2;
}
</style>
<script type="text/javascript">
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
	$(".editContactpersonGroups").off("click").on("click", function(e){
        e.preventDefault();
		var data = {
			contactpersonId: $(this).data('contactperson-id'),
			customerId:  $(this).data('customer-id')
		};
		ajaxCall('edit_contactperson_group', data, function(json) {
			 $('#popupeditboxcontent').html('').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
			fw_click_instance = false;
			fw_loading_end();
		});
    });
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

		<?php foreach($pages as $page) {?>
			<a href="#" data-page="<?php echo $page?>" class="page-link"><?php echo $page;?></a>
		<?php } ?>

		<!-- <?php echo $formText_Showing_Output;?> <span class="showing"><?php echo $showing;?></span> <?php echo $formText_Of_output." ".$currentCount;?>
		<a href="#" class="showMoreCustomersBtn fw_text_link_color"><?php echo $formText_ShowMore_output;?></a> -->
	</div>
<?php } ?>
<script type="text/javascript">
var page = 1;
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed: 300,
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
            loadView("list", {list_filter: '<?php echo $list_filter?>'});
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
			page: page
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

    $(".page-link").on('click', function(e) {
	    page = $(this).data("page");
	    e.preventDefault();
	    var data = {
			department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
            page: page,
	    };
	    ajaxCall('list', data, function(json) {
	        $('.p_pageContent').html(json.html);
	        if(json.html.replace(" ", "") == ""){
	            $(".showMoreCustomersBtn").hide();
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
            rowOnly: 1
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent .gtable').append(json.html);
            $(".showMoreCustomers .showing").html($(".gtable .gtable_row.output-click-helper").length);
            if(json.html.replace(" ", "") == "" || $(".gtable .gtable_row.output-click-helper").length == currentCount){
                $(".showMoreCustomersBtn").hide();
            }
        });
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
						}
					});
				}
				fw_click_instance = false;
			}
		});
	}
}

</script>
<?php
}

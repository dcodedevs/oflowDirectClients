<?php
$sql_join = '';
$sql_where = '';
if((!isset($variables->useradmin) || 0 == $variables->useradmin) && $v_employee_accountconfig['activateFilterByTags'])
{
	$v_property_ids = $v_property_group_ids = array();
	$s_sql = "SELECT cp.* FROM contactperson AS cp
	LEFT OUTER JOIN subscriptionmulti AS sm ON sm.customerId = cp.customerId AND sm.startDate <= CURDATE() AND (sm.stoppedDate = '0000-00-00' OR sm.stoppedDate is null OR sm.stoppedDate >= CURDATE())
	
	LEFT OUTER JOIN contactperson_subscription_connection AS csc ON csc.contactperson_id = cp.id
	LEFT OUTER JOIN subscriptionmulti AS sm2 ON csc.subscriptionmulti_id = sm2.id AND sm2.startDate <= CURDATE()
	AND (sm2.stoppedDate = '0000-00-00' OR sm2.stoppedDate is null OR sm2.stoppedDate >= CURDATE())
	
	WHERE cp.email = '".$o_main->db->escape_str($variables->loggID)."' AND (
	(IFNULL(cp.intranet_membership_subscription_type, 0) = 0 AND sm.id IS NOT NULL) OR 
	(cp.intranet_membership_subscription_type = 1 AND sm2.id IS NOT NULL AND csc.id IS NOT NULL) OR
	cp.intranet_membership_subscription_type = 2
	)";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_contactperson)
	{
		$v_properties = array();
		if(intval($v_contactperson['intranet_membership_type']) == 0)
		{
			$s_sql = "SELECT imao.object_id, pgc.property_id FROM intranet_membership AS im
			JOIN intranet_membership_customer_connection AS im_cus ON im_cus.membership_id = im.id
			LEFT OUTER JOIN intranet_membership_attached_object AS imao ON imao.membership_id = im_cus.membership_id
			LEFT OUTER JOIN property_group_connection AS pgc ON pgc.property_group_id = imao.objectgroup_id AND imao.object_id = 0
			WHERE im_cus.customer_id = '".$o_main->db->escape_str($v_contactperson['customerId'])."'";
			$o_find = $o_main->db->query($s_sql);
			$v_properties = $o_find ? $o_find->result_array() : array();

		} else if($v_contactperson['intranet_membership_type'] == 1)
		{
			$s_sql = "SELECT imao.object_id, pgc.property_id FROM intranet_membership AS im
			JOIN intranet_membership_contactperson_connection AS im_cp ON im_cp.membership_id = im.id
			LEFT OUTER JOIN intranet_membership_attached_object AS imao ON imao.membership_id = im_cp.membership_id
			LEFT OUTER JOIN property_group_connection AS pgc ON pgc.property_group_id = imao.objectgroup_id AND imao.object_id = 0
			WHERE im_cp.contactperson_id = '".$o_main->db->escape_str($v_contactperson['id'])."'";
			$o_find = $o_main->db->query($s_sql);
			$v_properties = $o_find ? $o_find->result_array() : array();

		}
		foreach($v_properties as $v_item)
		{
			if(0 < $v_item['object_id'] && !in_array($v_item['object_id'], $v_property_ids))
			{
				array_push($v_property_ids, $v_item['object_id']);
			}
			if(0 < $v_item['property_id'] && !in_array($v_item['property_id'], $v_property_group_ids))
			{
				array_push($v_property_group_ids, $v_item['property_id']);
			}
		}
	}
	
	//echo 'PROP: '.implode(', ', $v_property_ids).'<br>';
	//echo 'GROUP_PROP: '.implode(', ', $v_property_group_ids).'<br>';
	$s_sql_a = '';
	$s_sql_b = '';
	if(0<count($v_property_ids))
	{
		$s_sql_a = "imao.object_id IN (".implode(', ', $v_property_ids).")";
		$s_sql_b = "imao2.object_id IN (".implode(', ', $v_property_ids).")";
	}
	if(0<count($v_property_group_ids))
	{
		$s_sql_a .= (''!=$s_sql_a?" OR ":'')."pgc.property_id IN (".implode(', ', $v_property_group_ids).")";
		$s_sql_b .= (''!=$s_sql_b?" OR ":'')."pgc2.property_id IN (".implode(', ', $v_property_group_ids).")";
	}
	
	$sql_join .= " JOIN contactperson AS cp ON cp.id = p.id
	LEFT OUTER JOIN subscriptionmulti AS sm ON sm.customerId = cp.customerId AND sm.startDate <= CURDATE() AND (sm.stoppedDate = '0000-00-00' OR sm.stoppedDate is null OR sm.stoppedDate >= CURDATE())
	
	LEFT OUTER JOIN contactperson_subscription_connection AS csc ON csc.contactperson_id = cp.id
	LEFT OUTER JOIN subscriptionmulti AS sm2 ON csc.subscriptionmulti_id = sm2.id AND sm2.startDate <= CURDATE()
	AND (sm2.stoppedDate = '0000-00-00' OR sm2.stoppedDate is null OR sm2.stoppedDate >= CURDATE())
	
	LEFT OUTER JOIN intranet_membership_customer_connection AS im_cus ON im_cus.customer_id = cp.customerId
	LEFT OUTER JOIN intranet_membership_attached_object AS imao ON imao.membership_id = im_cus.membership_id
	LEFT OUTER JOIN property_group_connection AS pgc ON pgc.property_group_id = imao.objectgroup_id AND imao.object_id = 0
	
	LEFT OUTER JOIN intranet_membership_contactperson_connection AS im_cp ON im_cp.contactperson_id = cp.id
	LEFT OUTER JOIN intranet_membership_attached_object AS imao2 ON imao2.membership_id = im_cp.membership_id
	LEFT OUTER JOIN property_group_connection AS pgc2 ON pgc2.property_group_id = imao2.objectgroup_id AND imao2.object_id = 0";
	
	$sql_where .= " AND (
	(IFNULL(cp.intranet_membership_type, 0) = 0 AND (".$s_sql_a.") AND im_cus.id IS NOT NULL) OR
	(cp.intranet_membership_type = 1 AND (".$s_sql_b.") AND im_cp.id IS NOT NULL)
	)
	AND (
	(IFNULL(cp.intranet_membership_subscription_type, 0) = 0 AND sm.id IS NOT NULL) OR 
	(cp.intranet_membership_subscription_type = 1 AND sm2.id IS NOT NULL AND csc.id IS NOT NULL) OR
	cp.intranet_membership_subscription_type = 2
	)
	GROUP BY cp.id";
}

require_once __DIR__ . '/list_btn.php';
$v_membersystem = array();
$v_registered_usernames = array();
$o_query = $o_main->db->query("SELECT * FROM cache_userlist_membershipaccess");
$v_cache_userlist_membership = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist_membership as $v_user_cached_info) {
	$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
}
$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
$v_cache_userlist = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist as $v_user_cached_info) {
	$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
    if($v_user_cached_info['user_id'] > 0) $v_registered_usernames[] = $v_user_cached_info['username'];
}
if(isset($_GET['search'])){ $search_filter = $_GET['search']; } else { $search_filter = null; }
if(isset($_GET['subscriptionType'])){ $subscriptiontype_filter = $_GET['subscriptionType']; } else { $subscriptiontype_filter = null; }
$subscriptionTypes = array();
if($v_employee_accountconfig['show_companies_subscription_type']) {
	foreach($crmCustomers as $crmCustomer) {
		if(intval($crmCustomer['subscriptionTypeId']) > 0) {
			if(!$subscriptionTypes[$crmCustomer['subscriptionTypeId']]){
				$subscriptionTypes[$crmCustomer['subscriptionTypeId']] = $crmCustomer['subscriptionTypeName'];
			}
		}
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
	$cp_sql_where .= " AND p.show_in_intranet = 1";
}

?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content" style="background: none;">
            <div class="p_tableFilter" style="margin:0px 0px 15px; background: #fff; padding: 0px 15px">
                <div class="p_tableFilter_left">
                    <span class="fas fa-users fw_icon_title_color"></span>
                    <?php echo $formText_Companies_Output;?>
                </div>
                <div class="p_tableFilter_right">
					<?php if($v_employee_accountconfig['show_companies_subscription_type']) {?>
						<div class="fw_filter_color selectDiv personCompanyWrapper">
                            <div class="selectDivWrapper">
								<select class="subscriptionTypeFilter" autocomplete="off">
									<option value=""><?php echo $formText_All_output;?></option>
									<?php foreach($subscriptionTypes as $subscriptionTypeId => $subscriptionTypeName) { ?>
										<option value="<?php echo $subscriptionTypeId;?>" <?php if($subscriptiontype_filter == $subscriptionTypeId) echo 'selected';?>><?php echo $subscriptionTypeName;?></option>
									<?php } ?>
								</select>
                            </div>
                            <div class="arrowDown"></div>
                        </div>
					<?php }?>
                    <form class="searchFilterForm" id="searchFilterForm">
                        <input type="text" class="searchFilter" autocomplete="off" placeholder="<?php echo $formText_SearchAfterCompany_output; ?>" value="<?php echo $search_filter;?>">
                        <button id="p_tableFilterSearchBtn" class="fw_button_color "><?php echo $formText_Search_output; ?></button>
                    </form>
                    <div class="clear"></div>
                </div>
            </div>
            <script type="text/javascript">
            $(document).ready(function(){

                // Filter by customer name
                $('.searchFilterForm').on('submit', function(e) {
                    e.preventDefault();
                    loadView("companies", {search: $(".searchFilter").val(), subscriptionType: $(".subscriptionTypeFilter").val()});
                });
				$(".subscriptionTypeFilter").change(function(e){
					e.preventDefault();
                    loadView("companies", {search: $(".searchFilter").val(), subscriptionType: $(".subscriptionTypeFilter").val()});
				})
            })
            </script>

            <div class="p_pageContent">
                <?php
				foreach($crmCustomers as $crmCustomer)
				{
                    $contactPersons = array();
                    $s_sql = "SELECT p.* FROM contactperson p ".$sql_join." WHERE p.customerId = ? AND (p.notVisibleInMemberOverview = 0 OR p.notVisibleInMemberOverview is null)".$cp_sql_where.$sql_where;
                    $o_result = $o_main->db->query($s_sql, array($crmCustomer['id']));
                    $contactPersons = $o_result ? $o_result->result_array() : array();
$l_cnt += count($contactPersons);
                    ?>
                    <div class="peopleGroupWrapper">
            			<div class="peopleGroupTitle">
            				<?php echo $crmCustomer['name']?>
							<?php if($v_employee_accountconfig['show_companies_subscription_type']) {?>
								<span class="subscriptionType"><?php echo $crmCustomer['subscriptionTypeName']." ".$formText_Membership_output;?></span>
							<?php } ?>
            				<div class="clear"></div>
            			</div>
            			<div class="peopleGroupContent <?php if(in_array($crmCustomer['id'], $preloadedBlocks)) echo 'active';?>" data-groupid="<?php echo $crmCustomer['id']?>">
            				<div class="peopleGroupContentTopRow">
								<?php if(!$v_employee_accountconfig['hide_contactinfo']) { ?>
	                                <div class="peopleGroupShowAll peopleGroupShowInfo fw_text_link_color"><?php echo $formText_ContactInformation_output;?></div>
								<?php } ?>

            					<div class="peopleGroupShowAll peopleGroupShowContacts fw_text_link_color"><?php echo $formText_ContactPersons_output;?> (<?php echo count($contactPersons)?>)</div>

            					<div class="clear"></div>
            				</div>
            				<div class="peopleGroupContentBottom">
                                <table class="gtable" id="gtable_search_info">
                                    <tr>
                                        <td width="200px"><b><?php echo $formText_VisitingAddress_output?></b></td>
                                        <td><?php echo $crmCustomer['vaStreet']." ".$crmCustomer['vaCity']." ".$crmCustomer['vaPostalNumber']?></td>
                                    </tr>
                                    <tr>
                                        <td><b><?php echo $formText_PostalAddress_output?></b></td>
                                        <td><?php echo $crmCustomer['paStreet']." ".$crmCustomer['paCity']." ".$crmCustomer['paPostalNumber']?></td>
                                    </tr>
                                    <tr>
                                        <td><b><?php echo $formText_Phone_output?></b></td>
                                        <td><?php echo $crmCustomer['mobile']?></td>
                                    </tr>
								</table>
                                <table class="gtable" id="gtable_search">
                                    <?php
                                    foreach($contactPersons as $contactperson){
                                        $phoneToDisplay = $contactperson['mobile'];

                                        $imgToDisplay = "../elementsGlobal/avatar_placeholder.jpg";
                                        $v_access = null;
                                        foreach($v_membersystem as $writeContent) {
                                            if(mb_strtolower($writeContent['username']) == mb_strtolower($contactperson['email'])) {
                                                $v_access = $writeContent;
                                                break;
                                            }
                                        }
                                        $nameToDisplay = $contactperson['name']." ".$contactperson['middlename']." ".$contactperson['lastname'];

                                        $currentMember = "";
                                        $people_getynet_id = "";

                                        if($v_access['image'] != "" && $v_access['image'] != null){
                                            $imgToDisplay = json_decode($v_access['image'],true);
                                            $imgToDisplay = "https://pics.getynet.com/profileimages/".$imgToDisplay[0];
                                        }
                                        if(!$v_access){
                                            if(isset($v_not_registered_images[$contactperson['email']]))
                                            {
                                                if($v_not_registered_images[$contactperson['email']]['image'] != '')
                                                {
                                                    $imgToDisplay = json_decode($v_not_registered_images[$contactperson['email']]['image'], TRUE);
                                                    $imgToDisplay = "https://pics.getynet.com/profileimages/".$imgToDisplay[0];
                                                }
                                            }
                                        }

                                        if($v_access['name'] != ""){
                                            $nameToDisplay = $v_access['name']." ".$v_access['middle_name']." ".$v_access['last_name'];
                                        }
                                        if($v_access['mobile'] != "") {
                                            $phoneToDisplay = $v_access['mobile'];
                                        }
                                        $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$contactperson['id'];
                                        ?>
                                        <tr class="gtable_row output-click-helper"  data-href="<?php echo $s_edit_link;?>">
                                            <td class="gtable_cell middleAligned imagetd imageItem">
                                                <?php if($imgToDisplay != "") { ?>
                                                <div class="employeeImage">
                                                    <img src="<?php echo $imgToDisplay; ?>" alt="<?php echo $nameToDisplay;?>" title="<?php echo $nameToDisplay;?>"/>
                                                </div>
                                                <?php } ?>
                                            </td>
                                            <td class="gtable_cell border_bottom middleAligned">
                                                <?php if($contactperson['admin'] == 1) {?>
                                                    (<?php echo $formText_Administrator?>)<br/>
                                                <?php } ?>
                                                <?php echo $nameToDisplay;?>
                                            </td>
                                            <td class="gtable_cell border_bottom middleAligned ">
                                                <?php if($v_access['user_id'] > 0) { ?>
                                                    <span class="icon icon-chat fw_icon_color openChat" title="<?php echo $formText_OpenChat_output;?>" data-userid="<?php echo $v_access['user_id']?>"></span>
                                                <?php } ?>
                                            </td>
                                            <td class="gtable_cell border_bottom middleAligned"><a class="link fw_text_link_color" href="mailto:<?php echo ($v_access['username']);?>"><?php echo ($v_access['username']);?></a></td>
                                            <td class="gtable_cell border_bottom middleAligned"><?php echo $phoneToDisplay;?></td>
                                            <td class="gtable_cell border_bottom middleAligned">
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </table>
            				</div>
            			</div>
            		</div>
                    <?php
                }
                ?>
			</div>
			<?php if($totalPages > 1) {
				$currentPage = $companyPage;
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
					$prevPage = $companyPage - $x;
					$nextPage = $companyPage + $x;
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
				<?php foreach($pages as $page) {?>
					<a href="#" data-page="<?php echo $page?>" class="page-link <?php if($companyPage == $page) echo 'active';?>"><?php echo $page;?></a>
				<?php } ?>
				<?php /*
			    <div class="showMoreCustomers"><?php echo $formText_Showing_Output;?> <span class="showing"><?php echo $showing;?></span> <?php echo $formText_Of_output." ".$currentCount;?> <a href="#" class="showMoreCustomersBtn"><?php echo $formText_ShowMore_output;?></a> </div>*/?>
		<?php } ?>
        </div>
    </div>
</div>
<script type="text/javascript">
var page = '<?php echo $companyPage;?>';

bindMembersActions();
function bindMembersActions(){

    $(".output-click-helper").off("click").on("click", function(e){
        if((!$(e.target).hasClass("output-access-loader") && $(e.target).parents(".output-access-loader").length == 0)
        && (!$(e.target).hasClass("view-changer") && $(e.target).parents(".view-changer").length == 0)
        && (!$(e.target).hasClass("openChat") && $(e.target).parents(".openChat").length == 0)
        && (!$(e.target).hasClass("actionColumn") && $(e.target).parents(".actionColumn").length == 0)
        && (!$(e.target).hasClass("link") && $(e.target).parents(".link").length == 0)){
            fw_load_ajax($(this).data('href'),'',true);
        }
    })
    $(".openChat").off("click").on('click', function(){
        var userId = $(this).data("userid");
        if(fwchat != undefined && userId > 0){
            fwchat.showChat(userId);
        }
    })

    $(".peopleGroupShowInfo").unbind("click").bind("click", function(){
        var parent = $(this).parents(".peopleGroupContent");
        var groupId = parent.data("groupid");

        parent.find(".peopleGroupShowContacts").removeClass("active");
        $(this).addClass("active");
        parent.addClass("active");
        parent.find(".peopleGroupContentBottom #gtable_search").hide();
        parent.find(".peopleGroupContentBottom #gtable_search_info").show();

    })
    $(".peopleGroupShowContacts").unbind("click").bind("click", function(){
        var parent = $(this).parents(".peopleGroupContent");
        var groupId = parent.data("groupid");

        parent.find(".peopleGroupShowInfo").removeClass("active");
        $(this).addClass("active");
        parent.addClass("active");
        parent.find(".peopleGroupContentBottom #gtable_search_info").hide();
        parent.find(".peopleGroupContentBottom #gtable_search").show();


        // parent.find(".peopleGroupContentBottom .gtable_row").show();

    })
    $(".peopleGroupMemberCount").unbind("click").bind("click", function(){
        var parent = $(this).parents(".peopleGroupContent");
        parent.toggleClass("active");
        var groupId = parent.data("groupid");
        if(!parent.hasClass("loaded") && !parent.hasClass("loadedHidden")){
            if(parent.hasClass("active")){
                parent.find(".peopleGroupContentBottom #gtable_search").hide();
                parent.find(".peopleGroupContentBottom #gtable_search_info").show();
            }
        }
    })
}

$(".page-link").on('click', function(e) {
	page = $(this).data("page");

	e.preventDefault();
	var data = {
		search: $('.searchFilter').val(),
		page: page
	};
	loadView('companies', data);
});
</script>
<style>
.peopleGroupTitle .subscriptionType {
	float: right;
	color: #919191;
	font-size: 16px;
}
.page-link.active {
	text-decoration: underline;
}
</style>

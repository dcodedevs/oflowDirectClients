<?php
require_once __DIR__ . '/list_btn.php';
$v_membersystem = array();
$v_registered_usernames = array();

$o_query = $o_main->db->query("SELECT * FROM accountinfo");
$v_accountinfo = $o_query ? $o_query->row_array() : array();

$o_query = $o_main->db->query("SELECT * FROM people_basisconfig");
$v_people_config = $o_query ? $o_query->row_array() : array();
$o_query = $o_main->db->query("SELECT * FROM people_accountconfig");
if($o_query && $o_query->num_rows()>0)
{
	$v_people_config = $o_query->row_array();
}

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

$o_query = $o_main->db->query("SELECT * FROM cache_userlist_membershipaccess");
$v_cache_userlist = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist as $v_user_cached_info) {
	$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
    if($v_user_cached_info['user_id'] > 0) $v_registered_usernames[] = $v_user_cached_info['username'];
}
$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
$v_cache_userlist = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist as $v_user_cached_info) {
	$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
    if($v_user_cached_info['user_id'] > 0) $v_registered_usernames[] = $v_user_cached_info['username'];
}
$_GET['personcompany_filter'] = isset($_POST['personcompany_filter']) ? $_POST['personcompany_filter'] : intval($_GET['personcompany_filter']);

if($_GET['personcompany_filter'] != ""){
	$_SESSION['personcompany_filter'] = $_GET['personcompany_filter'];
}
if($_SESSION['personcompany_filter'] != "") {
	$_GET['personcompany_filter'] = $_SESSION['personcompany_filter'];
}

$personcompany_filter = $_GET['personcompany_filter'];

foreach($personCompanies as $personCompany) {
	if($personcompany_filter > 0){
		if($personcompany_filter == $personCompany['id']){
			$crmCustomer = $personCompany;
		    break;
		}
	} else {
	    $crmCustomer = $personCompany;
	    break;
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

$isCrmAdmin = false;
$s_sql = "SELECT * FROM contactperson WHERE customerId = ? AND email = ?";
$o_result = $o_main->db->query($s_sql, array($crmCustomer['id'], $variables->loggID));
$currentContactPerson = $o_result ? $o_result->row_array() : array();
if($currentContactPerson['admin']) {
	$isCrmAdmin = true;
}
$cp_sql_where = " AND (contactperson.notVisibleInMemberOverview = 0 OR contactperson.notVisibleInMemberOverview is null)";
if($v_employee_basisconfig['filter_in_owncompany_tab'] == 0){
	if($isCrmAdmin) {
		$cp_sql_where = "";
	}
}

$contactPersons = array();
$s_sql = "SELECT * FROM contactperson WHERE customerId = ? AND (inactive IS NULL OR inactive = 0)".$cp_sql_where;
$o_result = $o_main->db->query($s_sql, array($crmCustomer['id']));
$contactPersons = $o_result ? $o_result->result_array() : array();
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
            <div class="p_tableFilter">
                <div class="p_tableFilter_left">
                    <div class="module_name">
                        <span class="fas fa-address-book fw_icon_title_color wrappedIcon"></span>
                         <?php echo $formText_PersonOwnCompany_Output; ?>
						 <?php if($crmCustomer && $isCrmAdmin){?>
							<span class="personAddMember fw_text_link_color item" data-customer-id="<?php echo $crmCustomer['id'];?>">+ <?php echo $formText_AddMember_output;?></span>
						<?php } ?>
                     </div>
                    <div class="clear"></div>
                </div>
                <div class="p_tableFilter_right">
                    <?php if(count($personCompanies) > 0) { ?>
                        <div class="fw_filter_color selectDiv personCompanyWrapper">
                            <div class="selectDivWrapper">
                                <select class="personCompany">
                                    <?php foreach($personCompanies as $personCompany) { ?>
                                        <option value="<?php echo $personCompany['id']?>" <?php if($crmCustomer['id'] == $personCompany['id']) echo 'selected';?>><?php echo $personCompany['name'];?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="arrowDown"></div>
                        </div>
                    <?php } else {
						echo $formText_NoAccessToCompanies_output;
					} ?>
                    <div class="clear"></div>
                </div>
            </div>
            <script type="text/javascript">
            $(document).ready(function(){
                // Filter by building
                $('.personCompany').on('change', function(e) {
                    var data = {
                        personcompany_filter: $(this).val(),
                    };
                    loadView("people_owncompany", data);
                });
            })
            </script>

            <div class="p_pageContent">
                <?php
                ?>

                <table class="gtable" id="gtable_search">
                    <?php
                    foreach($contactPersons as $peopleData){

                        $phoneToDisplay = $contactperson['mobile'];

                        $imgToDisplay = "../elementsGlobal/avatar_placeholder.jpg";
                        $v_access = null;
						$isPersonAdmin = false;
						$isRegistered = false;
                        foreach($v_membersystem as $writeContent) {
                            if(mb_strtolower($writeContent['username']) == mb_strtolower($peopleData['email'])) {
								$isRegistered = true;
                                $v_access = $writeContent;
								if($writeContent['admin']) {
									$isPersonAdmin = true;
								}
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
                            if(isset($v_not_registered_images[$peopleData['email']]))
                            {
                                if($v_not_registered_images[$peopleData['email']]['image'] != '')
                                {
                                    $imgToDisplay = json_decode($v_not_registered_images[$peopleData['email']]['image'], TRUE);
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
                        $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$peopleData['id'];

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
                                <?php if($peopleData['admin'] == 1) {?>
                                    (<?php echo $formText_Administrator?>)<br/>
                                <?php } ?>
                                <?php echo $nameToDisplay;?>
                                <?php if($peopleData['notVisibleInMemberOverview'] == 1) {?>
                                    <div class="notVisibleInMemberOverview"><?php echo $formText_NotVisibleInMemberOverview_Output;?></div>
                                <?php }?>
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
							<td class="gtable_cell border_bottom middleAligned rightAlignedCell" width="15%">
								<div class="output-access-loader" data-id="<?php echo $peopleData['id']?>" data-email="<?php echo $peopleData['email'];?>" data-membersystem-id="<?php echo $peopleData['id'];?>">
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
							            <?php if(!$v_employee_accountconfig['duplicate_module'] && $isCrmAdmin) { ?>
											<div class="output-access-dropdown">
												<?php /*?><div class="script fw_text_link_color" onClick="javascript:output_access_remove(this,'<?php echo $peopleData['id'];?>');" data-delete-msg="<?php echo $formText_RemoveAccess_Output.": ".$peopleData['email'];?>?">
													<?php echo $formText_RemoveAccess_Output;?>
												</div><?php */?>
												<?php /*?><div>
													<?php
													if($v_access['last_activity'] != "0000-00-00 00:00:00" && $v_access['last_activity'] != null)
														echo $formText_LastActivity_Output.": ".date("d.m.Y H:i", strtotime($v_access['last_activity']));
													if($v_access['firstlogin'] == "0000-00-00 00:00:00")
														echo $formText_NeverLoggedIn_Output;
													?>
												</div><?php */?>
												<!-- <div><?php echo $formText_InvitationSent_Output.': '.$v_access['invitationsent'];?></div> -->
												<div class="script fw_text_link_color" onClick="javascript:output_access_grant(this,'<?php echo $peopleData['id'];?>');"><?php echo $formText_ResendInvitation_Output;?></div>

												<?php /*?><div class="script fw_text_link_color" onClick="javascript:output_access_grant_no_sending(this,'<?php echo $peopleData['id'];?>');"><?php echo $formText_EditAccess_Output;?></div><?php */?>
											</div>
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
							            <?php if(!$v_employee_accountconfig['duplicate_module'] && $isCrmAdmin) { ?>
										<img src="<?php echo $extradir;?>/output/elementsOutput/access_key_grey.png" />
										<div class="output-access-dropdown"><div class="script fw_text_link_color" onClick="javascript:output_access_grant(this,'<?php echo $peopleData['id'];?>');"><?php echo $formText_GiveAccess_Output;?></div></div>
										<?php }
									}
									?>

									</div>
								</div>
							</td>
                            <td class="gtable_cell border_bottom middleAligned rightAligned noRightPadding actionColumn">
								<?php if($isCrmAdmin) { ?>
	                                <span class="glyphicon glyphicon-pencil editMember fw_delete_edit_icon_color" data-customer-id="<?php echo $peopleData['customerId']?>" data-contactperson-id="<?php echo $peopleData['id']?>"></span>
	                                <span class="glyphicon glyphicon-trash deleteMember fw_delete_edit_icon_color" data-customer-id="<?php echo $peopleData['customerId']?>" data-contactperson-id="<?php echo $peopleData['id']?>" data-delete-msg="<?php echo $formText_AreYouSureYouWantToDelete_Output.': '.$nameToDisplay;?>"></span>
								<?php } ?>
							</td>
                        </tr>
                    <?php } ?>
                </table>
			</div>
        </div>
    </div>
</div>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	fadeSpeed: 0,
	followSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
		if($(this).hasClass("close-reload")){
			reloadListView();
        }
		if($(this).hasClass("close-page-reload")){
			reloadListView(true);
        }
		$(this).removeClass('opened');
	}
};
function reloadListView(reloadPage){
	if(reloadPage){
		fw_loading_start();
		window.location.reload();
	} else {
		var data = {};
		loadView({module_file:'people_owncompany', module_name: 'People', module_folder: 'output'}, data, true);
	}
}
bindMembersActions();
function bindMembersActions(){
	$(document).off('mouseenter mouseleave', '.output-access-changer')
	.on('mouseenter', '.output-access-changer', function(){
		$(this).find(".output-access-dropdown").show();
	}).on('mouseleave', '.output-access-changer', function(){
		$(this).find(".output-access-dropdown").hide();
	});
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
    $(".personAddMember").off("click").on('click', function(e){
		e.preventDefault();
        var data = {
			from_owncompany: 1,
            customer_id: $(this).data("customer-id"),
        };
        ajaxCall({module_file:'editPeople', module_name: 'People', module_folder: 'output'}, data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
	});
    $(".deleteMember").off("click").on('click', function(e){
        e.preventDefault();
		var _this = this;
		if(!fw_click_instance)
		{
			fw_click_instance = true;
			var contactpersonId = $(this).data("contactperson-id");
			var data = {
				action: "delete",
				customer_id: $(_this).data("customer-id"),
				contactpersonId: contactpersonId
			};
			bootbox.confirm({
				message:$(_this).data('delete-msg'),
				buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
				callback: function(result){
					fw_click_instance = false;
					if(result)
					{
						ajaxCall({module_file:'editPersonMembers', module_name: 'People', module_folder: 'output'}, data, function(json) {
							if(json.error !== undefined)
							{
								$.each(json.error, function(index, value){
									var _type = Array("error");
									if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
									fw_info_message_add(_type[0], value);
								});
								fw_info_message_show();
								fw_loading_end();
								fw_click_instance = fw_changes_made = false;
							} else {
								reloadListView();
							}
						});
					}
				}
			});
		}
    });
    $(".editMember").off("click").on('click', function(e){
        e.preventDefault();
        var contactpersonId = $(this).data("contactperson-id");
        var data = {
            contactpersonId: contactpersonId
        };
        ajaxCall({module_file:'editPersonMemberInfo', module_name: 'People', module_folder: 'output'}, data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

}
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
			data: { fwajax: 1, fw_nocss: 1, from_owncompany:1, contactperson_id: $(_this).data('id'), membersystem_id: $(_this).data('membersystem-id'), email: $(_this).data('email') },
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
		data: { fwajax: 1, fw_nocss: 1,from_owncompany:1, cid: id, crm_customer_id: '<?php echo $crmCustomer['id']?>' },
		success: function(obj){
			fw_loading_end();
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(obj.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
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
		data: { fwajax: 1, fw_nocss: 1,from_owncompany:1, cid: id, noinvitiation:1 },
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
						data: { fwajax: 1, fw_nocss: 1, from_owncompany:1, cid: id },
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
<style>
.notVisibleInMemberOverview {
    color: #bbb;
}
.personAddMember  {
	margin-left: 10px;
	cursor: pointer;
	font-size: 13px;
}
</style>

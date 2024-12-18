<?php
$s_email_template = "sendemail_standard";
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$s_sql = "select * from accountinfo";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_accountinfo = $o_query->row_array();
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

// if($v_customer_accountconfig['intranet_membership_object_table'] != "")
// {
// 	$object_table = $v_customer_accountconfig['intranet_membership_object_table'];
// } else {
// 	$object_table =  $customer_basisconfig['intranet_membership_object_table'];
// }
// if($v_customer_accountconfig['intranet_membership_objectgroup_table'] != "")
// {
// 	$objectgroup_table = $v_customer_accountconfig['intranet_membership_objectgroup_table'];
// } else {
// 	$objectgroup_table =  $customer_basisconfig['intranet_membership_objectgroup_table'];
// }

$v_data = json_decode(APIconnectAccount("accountcompanyinfoget", $v_accountinfo['accountname'], $v_accountinfo['password'], array()),true);
$companyinfo = $v_data['data'];

$s_sql = "select * from contactperson where id = ?";
$o_query = $o_main->db->query($s_sql, array($_POST['cid']));
$v_contactperson = $o_query ? $o_query->row_array() : array();

if(!$v_contactperson){
    $s_sql = "select * from contactperson where customerId = ?";
    $o_query = $o_main->db->query($s_sql, array($_POST['customerId']));
    $v_contactperson = $o_query ? $o_query->row_array() : array();
}

$l_contactperson_id = $v_contactperson['id'];
$s_receiver_name = $v_contactperson['name'];
$s_receiver_email = $v_contactperson['email'];

$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($v_contactperson['customerId'])."'");
$v_customer = $o_query ? $o_query->row_array() : array();
$l_selfdefined_company_id = $v_customer['selfdefined_company_id'];

$v_selfdefined_companies = array();
$b_activate_selfdefined_company = $b_check_selfdefined_company = FALSE;
$s_response = APIconnectAccount('companyname_selfdefined_getlist', $v_accountinfo['accountname'], $v_accountinfo['password']);
$v_response = json_decode($s_response, TRUE);
if(isset($v_response['status']) && 1 == $v_response['status'])
{
	$b_activate_selfdefined_company = TRUE;
	$v_selfdefined_companies = $v_response['items'];
}
if($s_receiver_email)
{
	$s_sql = "select * from customer_stdmembersystem_basisconfig";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0){
		$v_membersystem_config = $o_query->row_array();
	}
	$o_membersystem = json_decode(APIconnectAccount("membersystemcompanyaccessusernameget", $v_accountinfo['accountname'], $v_accountinfo['password'], array("COMPANY_ID"=>$companyID, "USER"=>$s_receiver_email, "MEMBERSYSTEMID"=>$v_customer['id'], "ACCESSLEVEL"=>$v_membersystem_config['access_level'], "MODULE"=>$module)));
	if(isset($o_membersystem->data) && $o_membersystem->data->companyname_selfdefined_id != '')
	{
		$l_selfdefined_company_id = $o_membersystem->data->companyname_selfdefined_id;
	}
}
if(isset($_POST['selfdefined_company_id'])) $l_selfdefined_company_id = $_POST['selfdefined_company_id'];
foreach($v_selfdefined_companies as $v_item)
{
	if($l_selfdefined_company_id == $v_item['id'] && $v_item['invitation_config'] != '')
	{
		$variables->invitation_config = $v_item['invitation_config'];
	}
}

include(__DIR__."/../../".$s_email_template."/template.php");
$_POST["folder"] = "sendemail_standard";

include(__DIR__."/readOutputLanguage.php");

$currentModule=explode('/modules/', __DIR__);
?>
<link href="../modules/<?php echo $currentModule[1];?>/../output.css" rel="stylesheet" type="text/css" >
<div class="popupform">
	<div class="popupfromTitle"><?php echo $formText_ApproveInvitation_Output;?></div>
    <div class="popupError"></div>
	<?php
	if($b_activate_selfdefined_company)
	{
		if(sizeof($v_selfdefined_companies) > 1)
		{
			$b_check_selfdefined_company = TRUE;
			?>
			<div style="border-bottom:1px solid #e9e9e9; padding-bottom:10px; margin-bottom:20px;">
				<label><?php echo $formText_SelfdefinedCompany_output;?>&nbsp;&nbsp;&nbsp;</label>
				<select class="selfdefined_company_id" autocomplete="off">
					<option value=""><?php echo $formText_NoneSelected_output;?></option>
					<?php
					foreach($v_selfdefined_companies as $v_item)
					{
						?><option value="<?php echo $v_item['id'];?>"<?php echo ($l_selfdefined_company_id == $v_item['id'] ? ' selected':'');?>><?php echo $v_item['name'];?></option><?php
					}
					?>
				</select>
			</div>
			<?php
		} else {
			?><input type="hidden" class="selfdefined_company_id" value="<?php echo $v_response['items'][0]['id'];?>"><?php
		}
	}
    if($v_customer_accountconfig['activate_intranet_membership']) {
        $contactPersonMembershipConnections = array();
        $s_sql = "select * from intranet_membership WHERE content_status < 2 ORDER BY name ASC";
        $o_query = $o_main->db->query($s_sql, array());
        $intranet_memberships = $o_query ? $o_query->result_array() : array();

        $s_sql = "select * from subscriptionmulti WHERE customerId = ? AND content_status < 2 ORDER BY subscriptionName ASC";
        $o_query = $o_main->db->query($s_sql, array($v_contactperson['customerId']));
        $intranet_membership_subscriptions = $o_query ? $o_query->result_array() : array();

        if($v_contactperson['intranet_membership_type'] == 1) {
            $s_sql = "select intranet_membership_contactperson_connection.* from intranet_membership_contactperson_connection
            where contactperson_id = ?";
            $o_query = $o_main->db->query($s_sql, array($v_contactperson['id']));
            $contactPersonMembershipConnections = $o_query ? $o_query->result_array() : array();


            $s_sql = "select contactperson_subscription_connection.* from contactperson_subscription_connection
            where contactperson_id = ?";
            $o_query = $o_main->db->query($s_sql, array($v_contactperson['id']));
            $contactPersonMembershipSubscriptionConnections = $o_query ? $o_query->result_array() : array();

        }
        ?>
        <div style="border-bottom:1px solid #e9e9e9; padding-bottom:10px; margin-bottom:20px;">
            <label><?php echo $formText_MembershipType_output;?>&nbsp;&nbsp;&nbsp;</label>
            <select class="intranet_membership_type" autocomplete="off" required>
                <option value=""><?php echo $formText_SelectMembershipType_output;?></option>
                <option value="0" <?php if($v_contactperson['intranet_membership_type'] == 0) echo 'selected';?>><?php echo $formText_CustomerDefinedMembership_output;?></option>
                <option value="1" <?php if($v_contactperson['intranet_membership_type'] == 1) echo 'selected';?>><?php echo $formText_SpecifiedMembership_output;?></option>
            </select>
            <div class="membershipList" style="display: none;">
                <div class="listWrapper">
                    <?php
                    foreach($contactPersonMembershipConnections as $contactPersonMembershipConnection) {
                        ?>
                        <div class="membershipConnectionRow">
                            <select class="membershipConnectionSelect">
                                <option value=""><?php echo $formText_Select_output;?></option>
                                <?php
                                foreach($intranet_memberships as $intranet_membership) {
                                    ?>
                                    <option value="<?php echo $intranet_membership['id']?>" <?php if($contactPersonMembershipConnection['membership_id'] == $intranet_membership['id']) echo 'selected';?>><?php echo $intranet_membership['name'];?></option>
                                    <?php
                                }
                                ?>
                            </select>
                            <span class="glyphicon glyphicon-trash removeMembershipConnectionSelect"></span>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <div class="addMembershipConnection"><?php echo $formText_AddMembershipConnection_output;?></div>
                <div class="emptyMembershipConnection" style="display:none;">
                    <select class="membershipConnectionSelect">
                        <option value=""><?php echo $formText_Select_output;?></option>
                        <?php
                        foreach($intranet_memberships as $intranet_membership) {
                            ?>
                            <option value="<?php echo $intranet_membership['id']?>"><?php echo $intranet_membership['name'];?></option>
                            <?php
                        }
                        ?>
                    </select>
                    <span class="glyphicon glyphicon-trash removeMembershipConnectionSelect"></span>
                </div>
            </div>
            <br/>
            <label><?php echo $formText_MembershipSubscriptionType_output;?>&nbsp;&nbsp;&nbsp;</label>
            <select class="intranet_membership_subscription_type" autocomplete="off" required>
                <option value=""><?php echo $formText_SelectSubscriptionType_output;?></option>
                <option value="0" <?php if($v_contactperson['intranet_membership_subscription_type'] == 0) echo 'selected';?>><?php echo $formText_AnyCustomerSubscription_output;?></option>
                <option value="1" <?php if($v_contactperson['intranet_membership_subscription_type'] == 1) echo 'selected';?>><?php echo $formText_SpecifiedSubscriptions_output;?></option>
                <option value="2" <?php if($v_contactperson['intranet_membership_subscription_type'] == 2) echo 'selected';?>><?php echo $formText_NoSubscriptionNeeded_output;?></option>
            </select>
            <div class="membershipSubscriptionList" style="display: none;">
                <div class="listWrapper">
                    <?php
                    foreach($contactPersonMembershipSubscriptionConnections as $contactPersonMembershipSubscriptionConnection) {
                        ?>
                        <div class="membershipSubscriptionConnectionRow">
                            <select class="membershipSubscriptionConnectionSelect">
                                <option value=""><?php echo $formText_Select_output;?></option>
                                <?php
                                foreach($intranet_membership_subscriptions as $intranet_membership_subscription) {
                                    ?>
                                    <option value="<?php echo $intranet_membership_subscription['id']?>" <?php if($contactPersonMembershipSubscriptionConnection['subscriptionmulti_id'] == $intranet_membership_subscription['id']) echo 'selected';?>><?php echo $intranet_membership_subscription['subscriptionName'];?></option>
                                    <?php
                                }
                                ?>
                            </select>
                            <span class="glyphicon glyphicon-trash removeMembershipSubscriptionConnectionSelect"></span>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <div class="addMembershipSubscriptionConnection"><?php echo $formText_AddMembershipSubscriptionConnection_output;?></div>
                <div class="emptyMembershipSubscriptionConnection" style="display:none;">
                    <select class="membershipSubscriptionConnectionSelect">
                        <option value=""><?php echo $formText_Select_output;?></option>
                        <?php
                        foreach($intranet_membership_subscriptions as $intranet_membership_subscription) {
                            ?>
                            <option value="<?php echo $intranet_membership_subscription['id']?>"><?php echo $intranet_membership_subscription['subscriptionName'];?></option>
                            <?php
                        }
                        ?>
                    </select>
                    <span class="glyphicon glyphicon-trash removeMembershipSubscriptionConnectionSelect"></span>
                </div>
            </div>
        </div>
        <?php
    }
	if(!isset($_POST['change_access'])) {
	?>
	<div style="border-bottom:1px solid #e9e9e9; padding-bottom:10px; margin-bottom:20px;"><b><?php echo $formText_invitationSender_Output;?>: <?Php print htmlspecialchars($s_email_from); ?></b></div>
    <div style="border-bottom:1px solid #e9e9e9; padding-bottom:10px; margin-bottom:20px;"><b><?php echo $formText_invitationSubject_Output;?>: <?Php print $s_email_subject; ?></b></div>
	<div style="background:#fff;"><?php echo $s_email_body;?></div>
	<?php } ?>
</div>

<?php
	if($_POST['inBatch'] && !isset($_POST['change_access'])) {
        $response = json_decode(APIconnectorAccount("membersystemcompanyaccesslistallget", $accountname, $v_accountinfo['password'],array("COMPANY_ID"=>$companyID,"MEMBERSYSTEMID"=>"")));
		// $response = json_decode(APIconnectorUser("companyaccessbycompanyidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$companyID)));
		$v_membersystem = array();
		foreach($response->data as $writeContent)
		{
		    array_push($v_membersystem, $writeContent);
		}

		$sql = "SELECT p.*
	             FROM contactperson p
	            WHERE p.customerId = ? ORDER BY p.name ASC";
		$o_query = $o_main->db->query($sql, array($_POST['customerId']));
		$customerList = $o_query ? $o_query->result_array() : array();
		?>
		<div class="batchUserTop"><input type="checkbox" class="selectAll" id="selectAll"/> <label for="selectAll"><?php echo $formText_SelectAll_output?></label></div>
		<div class="batchUserList">
		<?php
		$showingToGiveAccess = 0;
		foreach($customerList as $v_row)
		{
			$hasAccess = false;
			foreach($v_membersystem as $member){
			    if($member->username == $v_row['email']){
                    if($member->membersystemmodule == "Customer2" && $member->membersystemID == $v_row['customerId']){
    					$hasAccess = true;
    				}
                }
			}
			if(!$hasAccess) {
				$showingToGiveAccess++;
				?>
				<div class="peopleRow">
					<input type="checkbox" class="peopleItemCheckbox" id="peopleItem<?php echo $v_row['id']?>" value="<?php echo $v_row['id']?>" name="peopleIds[]"/><label for="peopleItem<?php echo $v_row['id']?>">
                        <?php echo $v_row['name']." ".$v_row['middlename']." ".$v_row['lastname'];?> (<?php echo $v_row['email']?>)
                    </label>
				</div>
				<?php
			}
		}
		?>
		</div>
		<div class="batchUserBottom"><?php echo $formText_Selected_output?> <span class="selected">0</span> <?php echo $formText_Of_output?> <span class="total"><?php echo $showingToGiveAccess;?></span></div>
		<?php
	}
?>
<div class="popupformbtn">
	<button type="button" class="output-btn b-large b-close" onClick="javascript:$('.output-access-loader.load').removeClass('load');"><?php echo $formText_Cancel_Output;?></button>
	<button type="button" class="submitbtn" onClick="<?php if($_POST['inBatch'] && !isset($_POST['change_access'])) { ?>output_send_invitation_in_batch();<?php } else {?>output_send_invitation(<?php echo $l_contactperson_id;?>);<?php } ?>"><?php echo (isset($_POST['resend']) ? $formText_ResendInvitation_Output : (isset($_POST['change_access']) ? $formText_ChangeAccess_Output : $formText_SendInvitation_Output)); ?></button>
</div>
<script type="text/javascript">
<?php if(!isset($_POST['change_access'])) { ?>
$('.selfdefined_company_id').change(function(e){
	var _data = { fwajax: 1, fw_nocss: 1, cid: '<?php echo $l_contactperson_id;?>'<?php echo ($_POST['inBatch'] ? ', inBatch: 1' : '');?>, customerId: '<?php echo $_POST['customerId']?>' };
	_data.selfdefined_company_id = $(this).val();
    fw_loading_start();
	$.ajax({
		cache: false,
		type: 'POST',
		dataType: 'json',
		url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=send_invitation_preview";?>',
		data: _data,
		success: function(obj){
            fw_loading_end();
			$('#popupeditboxcontent').html(obj.html);
		}
	});
});
<?php } ?>
$(".intranet_membership_type").change(function(){
    if($(this).val() == 0){
        $(".membershipList").hide();
    } else if($(this).val() == 1) {
        $(".membershipList").show();
    }
}).change();
$(".intranet_membership_subscription_type").change(function(){
    if($(this).val() == 0){
        $(".membershipSubscriptionList").hide();
    } else if($(this).val() == 1) {
        $(".membershipSubscriptionList").show();
    } else {
        $(".membershipSubscriptionList").hide();
    }
}).change();
$(".popupform .addMembershipConnection").off("click").on("click", function(){
    $(".popupform .membershipList .listWrapper").append($(".popupform .emptyMembershipConnection").clone().removeClass("emptyMembershipConnection").addClass("membershipConnectionRow").show());
    rebindButtons();
})
$(".popupform .addMembershipSubscriptionConnection").off("click").on("click", function(){
    $(".popupform .membershipSubscriptionList .listWrapper").append($(".popupform .emptyMembershipSubscriptionConnection").clone().removeClass("emptyMembershipSubscriptionConnection").addClass("membershipSubscriptionConnectionRow").show());
    rebindButtons();
})
rebindButtons();
function rebindButtons(){
    $(".popupform .removeMembershipConnectionSelect").off("click").on("click", function(){
        $(this).parents(".membershipConnectionRow").remove();
    })
    $(".popupform .removeMembershipSubscriptionConnectionSelect").off("click").on("click", function(){
        $(this).parents(".membershipSubscriptionConnectionRow").remove();
    })
}


function output_send_invitation(cid)
{
	if(cid === undefined) cid = 0;
	var _data = { fwajax: 1, fw_nocss: 1, cid: cid };
	<?php if($b_activate_selfdefined_company) { ?>
	_data.selfdefined_company_id = $('.selfdefined_company_id').val();
	<?php } ?>
	<?php if($b_check_selfdefined_company) { ?>
	if($('.selfdefined_company_id').val().trim() == '')
	{
		$(".popupform .popupError").html("<?php echo $formText_SelfdefinedCompanyIsMandatory_Output;?>");
		return;
	}
	<?php } ?>
    <?php
    if($v_customer_accountconfig['activate_intranet_membership']) { ?>
        var membership_connection_values = [];
        if($('.intranet_membership_type').val().trim() == '')
    	{
    		$(".popupform .popupError").html("<?php echo $formText_MembershipTypeIsMandatory_Output;?>");
    		return;
    	} else if($('.intranet_membership_type').val().trim() == 1){
            var membership_connections = $(".listWrapper .membershipConnectionRow select");

            $(membership_connections).each(function(index, el){
                if($(el).val() > 0){
                    membership_connection_values.push($(el).val());
                }
            })
            if(membership_connection_values.length == 0){
                $(".popupform .popupError").html("<?php echo $formText_SelectAtLeastOneSpecifiedMembership_Output;?>");
        		return;
            }
        }

        var membership_subscription_connection_values = [];
        if($('.intranet_membership_subscription_type').val().trim() == '')
    	{
    		$(".popupform .popupError").html("<?php echo $formText_MemberShipSubscriptionTypeIsMandatory_Output;?>");
    		return;
    	} else if($('.intranet_membership_subscription_type').val().trim() == 1){
            var membership_subscription_connections = $(".listWrapper .membershipSubscriptionConnectionRow select");

            $(membership_subscription_connections).each(function(index, el){
                if($(el).val() > 0){
                    membership_subscription_connection_values.push($(el).val());
                }
            })
            if(membership_subscription_connection_values.length == 0){
                $(".popupform .popupError").html("<?php echo $formText_SelectAtLeastOneSpecifiedSubscription_Output;?>");
        		return;
            }
        }
    	_data.intranet_membership_type = $('.intranet_membership_type').val();
    	_data.intranet_membership_subscription_type = $('.intranet_membership_subscription_type').val();

        _data.intranet_membership_connections = membership_connection_values;
        _data.intranet_membership_subscription_connections = membership_subscription_connection_values;
    <?php } ?>
	<?php if(isset($_POST['change_access'])) { ?>
		_data.change_access = 1;
	<?php } ?>
    fw_loading_start();
	$.ajax({
		cache: false,
		type: 'POST',
		dataType: 'json',
		url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=".(isset($_POST['resend']) ? "re" : "")."send_invitation";?>',
		data: _data,
		success: function(obj){
            fw_loading_end();
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(obj.html);
			out_popup = $('#popupeditbox').addClass('close-reload').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
			if(typeof output_access_load == 'function')
			{
				output_access_load();
			}
		}
	});
}
function output_send_invitation_in_batch()
{
    var accesslevel = $(".popupform .selectAccess select option:selected").val();
    if(accesslevel != "") {
        var peopleIds = $(".peopleItemCheckbox").serialize();
        if(peopleIds != ""){
            $(".popupError").html("");
            var _data = { fwajax: 1, fw_nocss: 1, peopleIds: peopleIds, inbatch: 1, accesslevel: accesslevel };
			if($('.selfdefined_company_add').is('.active'))
			{
				if($('.selfdefined_company_name').val().trim() == '')
				{
					$(".popupform .popupError").html("<?php echo $formText_SelfdefinedCompanyNameIsEmpty_Output;?>");
					return;
				}
				_data.selfdefined_company_name = $('.selfdefined_company_name').val();
				_data.selfdefined_company_style_set = $('.selfdefined_company_style_set').val();
			} else {
				_data.selfdefined_company_id = $('.selfdefined_company_id').val();
			}
            <?php
            if($v_customer_accountconfig['activate_intranet_membership']) { ?>
                var membership_connection_values = [];
                if($('.intranet_membership_type').val().trim() == '')
            	{
            		$(".popupform .popupError").html("<?php echo $formText_MembershipTypeIsMandatory_Output;?>");
            		return;
            	} else if($('.intranet_membership_type').val().trim() == 1){
                    var membership_connections = $(".listWrapper .membershipConnectionRow select");

                    $(membership_connections).each(function(index, el){
                        if($(el).val() > 0){
                            membership_connection_values.push($(el).val());
                        }
                    })
                    if(membership_connection_values.length == 0){
                        $(".popupform .popupError").html("<?php echo $formText_SelectAtLeastOneSpecifiedMembership_Output;?>");
                		return;
                    }
                }

                var membership_subscription_connection_values = [];
                if($('.intranet_membership_subscription_type').val().trim() == '')
            	{
            		$(".popupform .popupError").html("<?php echo $formText_MemberShipSubscriptionTypeIsMandatory_Output;?>");
            		return;
            	} else if($('.intranet_membership_subscription_type').val().trim() == 1){
                    var membership_subscription_connections = $(".listWrapper .membershipSubscriptionConnectionRow select");

                    $(membership_connections).each(function(index, el){
                        if($(el).val() > 0){
                            membership_subscription_connection_values.push($(el).val());
                        }
                    })
                    if(membership_subscription_connection_values.length == 0){
                        $(".popupform .popupError").html("<?php echo $formText_SelectAtLeastOneSpecifiedSubscription_Output;?>");
                		return;
                    }
                }
            	_data.intranet_membership_type = $('.intranet_membership_type').val();
            	_data.intranet_membership_subscription_type = $('.intranet_membership_subscription_type').val();

                _data.intranet_membership_connections = membership_connection_values;
                _data.intranet_membership_subscription_connections = membership_subscription_connection_values;
            <?php } ?>
            fw_loading_start();
			$.ajax({
                cache: false,
                type: 'POST',
                dataType: 'json',
                url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=".(isset($_POST['resend']) ? "re" : "")."send_invitation";?>',
                data: _data,
                success: function(obj){
                    fw_loading_end();
                    $('#popupeditboxcontent').html('');
                    $('#popupeditboxcontent').html(obj.html);
                    out_popup = $('#popupeditbox').addClass('close-reload').bPopup(out_popup_options);
                    $("#popupeditbox:not(.opened)").remove();
                    if(typeof output_access_load == 'function')
                    {
                        output_access_load();
                    }
                }
            });
        } else {
            $(".popupform .popupError").html("<?php echo $formText_ChoosePeopleToInvite_output;?>");
        }
    } else {
        $(".popupform .popupError").html("<?php echo $formText_ChooseAccessLevel_output;?>");
    }
}
$(".selectAll").click(function(){
    var checked = $(this).is(":checked");
    if(checked) {
        $(".peopleItemCheckbox").prop("checked", true);
    } else {
        $(".peopleItemCheckbox").prop("checked", false);
    }
    updateSelectedCount();
})
$(".peopleItemCheckbox").click(function(){

    updateSelectedCount();
})
function updateSelectedCount(){
    $(".batchUserBottom .selected").html($(".peopleItemCheckbox:checked").length);
}
</script>
<style>
.popupError {
    margin-top: -10px;
    padding-bottom: 10px;
    color: red;
}
#popupeditbox {
	background-color:#FFFFFF;
	border-radius:4px;
	color:#111111;
	display:none;
	padding:25px;
	width:600px;
}
#popupeditbox .button {
	background-color:#0393ff;
	color:#fff;
	cursor:pointer;
	display:inline-block;
	padding:10px 20px;
	text-align:center;
	text-decoration:none;
}
#popupeditbox .button:hover {
	background-color:#1e1e1e;
}
#popupeditbox .button.b-close,
#popupeditbox .button.bClose {
	position:absolute;
	border: 3px solid #fff;
	-webkit-border-radius: 100px;
	-moz-border-radius: 100px;
	border-radius: 100px;
	padding: 0px 9px;
	font: bold 100% sans-serif;
	line-height: 25px;
	right:-10px;
	top:-10px
}
#popupeditbox .button > span {
	font: bold 100% sans-serif;
	font-size: 12px;
	line-height: 12px;
}
#popupeditbox .popupform {
    border: 0;
}
.popupfromTitle {
	font-size: 24px;
	padding: 0px 0px 10px;
	border-bottom: 1px solid #ededed;
	color: #5d5d5d;
	margin-bottom: 15px;
}
.popupformbtn {
	text-align:right;
	margin-top:20px;
	position:relative;
}
.popupformbtn .submitbtn {
	border-radius:4px;
	border:1px solid #0393ff;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
	margin-left:10px;
}
.batchUserList {
    max-height: 200px;
    overflow: auto;
}
.batchUserList input {
    vertical-align: middle;
    margin: 0;
    margin-right: 5px;
}
.batchUserList label {
    vertical-align: middle;
    margin-bottom: 0px;
}
.batchUserTop {
    padding: 5px 0px;
}
.batchUserTop input {
    vertical-align: middle;
    margin: 0;
}
.batchUserTop label {
    vertical-align: middle;
    margin-bottom: 0px;
}
.batchUserBottom {
    padding: 10px 0px;
}
.selfdefined_company_add {
	display:none;
	margin:10px 0;
	padding:10px;
	border:1px solid #999999;
	border-radius:3px;
}
.selfdefined_company_add label {
	width:20%;
	display:inline-block !important;
}
.selfdefined_company_add input {
	width:70%;
}
.addMembershipConnection {
    color: #46b2e2;
    cursor: pointer;
}
.membershipConnectionRow {
    padding: 3px 0px;
}
.membershipConnectionRow .removeMembershipConnectionSelect {
    cursor: pointer;
    margin-left: 20px;
}
.addMembershipSubscriptionConnection {
    color: #46b2e2;
    cursor: pointer;
}
.membershipSubscriptionConnectionRow {
    padding: 3px 0px;
}
.membershipSubscriptionConnectionRow .removeMembershipSubscriptionConnectionSelect {
    cursor: pointer;
    margin-left: 20px;
}
</style>

<?php
$list_filter = isset($_GET['list_filter']) ? ($_GET['list_filter']) : 'active';
$department_filter = isset($_GET['department_filter']) ? ($_GET['department_filter']) : 0;
$linkedToCrmAccount = false;
if($v_employee_accountconfig['linked_crm_account'] != "" && $v_employee_accountconfig['linked_crm_account_token'] != ""){
    $linkedToCrmAccount = true;
}

$b_activate_signant = FALSE;
if(1 == $accessElementAllow_SendFilesToSignant)
{
	$s_signant_file = BASEPATH.'modules/IntegrationSignant/output/includes/class_IntegrationSignant.php';
	if(is_file($s_signant_file)) $b_activate_signant = TRUE;
}

if(isset($_GET['list_filter'])) { $filter = $_GET['list_filter']; } else { $filter = "active"; }
?>
<div class="p_tableFilter">
    <div class="p_tableFilter_left">
        <div class="module_name">
            <span class="fas fa-address-book fw_icon_title_color wrappedIcon"></span>
             <?php if($s_module_local_name && $variables->developeraccess <= 5){ echo $s_module_local_name;} else { echo $formText_People_Output; } ?></div>
        <?php if($list_filter != "inactive" && $list_filter != "deleted") {?>
            <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                <?php if($accessElementAllow_AddEditDeletePeople) {?><div class="addBtn fw_text_link_color addPeopleBtn">+ <?php echo $formText_AddPeople_output;?></div><?php } ?>
                <?php if($accessElementAllow_GiveRemoveAccessPeople) {?><div class="giveAccessInBatch fw_text_link_color addBtn"><?php echo $formText_GiveAccessInBatch_output;?></div><?php } ?>
                <?php if($accessElementAllow_GiveRemoveAccessPeople && !$linkedToCrmAccount) {?><div class="resendAccessInBatch fw_text_link_color addBtn"><?php echo $formText_ResendAccessInBatch_output;?></div><?php } ?>
                <?php if($variables->developeraccess > 5) { ?>
                    <div class="addBtn fw_text_link_color addEditSelfDefinedFieldsBtn">+ <?php echo $formText_AddEditSelfdefinedFields_output;?></div>
                <?php } ?>
                <?php
                include('ajax.import_data.php');
                ?>
                <?php if($accessElementAllow_ExportPeople) {?><div class="exportPeople fw_text_link_color addBtn"><?php echo $formText_Export_output;?></div><?php } ?>
                <?php if($accessElementAllow_AddEditDeletePeople) { ?><div class="edit_contract_types fw_text_link_color"><?php echo $formText_EditContractTypes_output;?></div><?php } ?>
                <?php if($accessElementAllow_AddEditDeletePeople) { ?><div class="edit_competence_register fw_text_link_color"><?php echo $formText_EditCompetenceRegister_output;?></div><?php } ?>
                <?php if($accessElementAllow_AddEditDeletePeople && $people_accountconfig['activatePosition'] > 0) { ?><div class="edit_position fw_text_link_color"><?php echo $formText_EditPositionRegister_output;?></div><?php } ?>
            <?php } ?>
        <?php } ?>
        <div class="clear"></div>
    </div>
    <div class="p_tableFilter_right">
        <?php if($list_filter != "inactive" && $list_filter != "deleted") {?>
            <?php if(count($departments) > 0) { ?>
                <div class="fw_filter_color selectDiv filterDepartmentWrapper">
                    <div class="selectDivWrapper">
                        <select class="filterDepartment" autocomplete="off">
                            <option value=""><?php echo $formText_All_output;?></option>
                            <?php foreach($departments as $department) { ?>
                                <option value="<?php echo $department['id']?>" <?php if($department_filter == $department['id']) echo 'selected';?>><?php echo $department['name'];?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="arrowDown"></div>
                </div>
            <?php } ?>
        <?php } ?>
        <form class="searchFilterForm" id="searchFilterForm">
            <div class="employeeSearch">
                <input type="text" placeholder="<?php echo $formText_SearchForPeople_output;?>" class="employeeSearchInput searchFilter"  value="<?php echo $search_filter;?>" autocomplete="off"/>
                <div class="employeeSearchSuggestions allowScroll"></div>
            </div>
            <button id="p_tableFilterSearchBtn" class="fw_button_color "><?php echo $formText_Search_output; ?></button>
        </form>
        <div class="clear"></div>
        <div class="filteredCountRow">
            <span class="selectionCount">0</span> <?php echo $formText_InSelection_output;?>
            <div class="resetSelection fw_text_link_color"><?php echo $formText_Reset_output;?></div>
        </div>

        <?php if($v_employee_basisconfig['activate_seniority_and_salary_listview']) {
            if($list_filter == "active"){
         ?>
                <div class="listViewWrapper">
                    <span class=""><?php echo $formText_ListView_output;?>: </span>
                    <div class="fw_filter_color selectDiv ">
                        <div class="selectDivWrapper">
                            <select class="listView" autocomplete="off">
                                <option value="0"><?php echo $formText_Normal_output;?></option>
                                <option value="1" <?php if($list_view == 1) echo 'selected';?>><?php echo $formText_SeniorityAndSalary_output;?></option>
                            </select>
                        </div>
                        <div class="arrowDown"></div>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
        <div class="clear"></div>
    </div>

	<div class="clear"></div>
	<?php
	if(1 == $accessElementAllow_SendFilesToSignant && $b_activate_signant)
	{
		$s_sql = "SELECT * FROM people_files AS pf JOIN integration_signant AS s ON s.id = pf.signant_id WHERE s.sign_status < 2 AND s.posting_id <> ''";
		$o_query = $o_main->db->query($s_sql);
		$l_documents_for_sign_count = $o_query ? $o_query->num_rows() : 0;
		if(0 < $l_documents_for_sign_count)
		{
			?><div class="alert alert-info">
				<?php echo $formText_PendingDocumentsForSigning_Output.': '.$l_documents_for_sign_count; ?>. <a href="#" class="output-signant-show-unsigned"><?php echo $formText_ShowDetails_Output;?></a>
			</div><?php
		}
	}
	?>
</div>
<style>
#p_container  .employeeSearch {
    display: inline-block;
    position: relative;
    margin-bottom: 0;
}
#p_container  .employeeSearch .employeeSearchSuggestions {
    display: none;
    background: #fff;
    position: absolute;
    width: 100%;
    max-height: 200px;
    overflow: auto;
    z-index: 2;
    border: 1px solid #dedede;
    border-top: 0;
    text-align: left;
}
#p_container  .employeeSearch .employeeSearchSuggestions table {
    margin-bottom: 0;
}
#p_container .employeeSearch .employeeSearchSuggestions td {
    padding: 5px 10px;
}

#p_container .employeeSearch .glyphicon-triangle-right {
    position: absolute;
    top: 7px;
    right: 4px;
    color: #048fcf;
}
#p_container .employeeSearch .glyphicon-search {
    position: absolute;
    top: 7px;
    left: 6px;
    color: #048fcf;
}
#p_container .employeeSearchInput {
    width: auto;
    border: 1px solid #dedede;
}
#p_container .employeeSearchInputBefore {
    width: 150px;
    border: 1px solid #dedede;
    padding: 3px 10px 3px 10px;
}

.article-loading.lds-ring {
  display: inline-block;
  position: relative;
  width: 24px;
  height: 24px;
  margin: 10px 20px;
}
.article-loading.lds-ring div {
  box-sizing: border-box;
  display: block;
  position: absolute;
  width: 22px;
  height: 22px;
  margin: 3px;
  border: 3px solid #46b2e2;
  border-radius: 50%;
  animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
  border-color: #46b2e2 transparent transparent transparent;
}
.article-loading.lds-ring div:nth-child(1) {
  animation-delay: -0.45s;
}
.article-loading.lds-ring div:nth-child(2) {
  animation-delay: -0.3s;
}
.article-loading.lds-ring div:nth-child(3) {
  animation-delay: -0.15s;
}
@keyframes lds-ring {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
.edit_contract_types {
    cursor: pointer;
    color: #46b2e2;
    float: left;
    padding: 7px 10px;
    font-size: 13px;
}
.edit_competence_register {
    cursor: pointer;
    color: #46b2e2;
    float: left;
    padding: 7px 10px;
    font-size: 13px;
}
.edit_position {
    cursor: pointer;
    color: #46b2e2;
    float: left;
    padding: 7px 10px;
    font-size: 13px;
}
</style>
<script type="text/javascript">
$(document).ready(function(){
    var loadingCustomer = false;
    var $input = $('.employeeSearchInput');
    var customer_search_value;
    $input.on('focusin', function () {
    	searchCustomerSuggestions();
    	$("#p_container").unbind("click").bind("click", function (ev) {
    		if($(ev.target).parents(".employeeSearch").length == 0){
    			$(".employeeSearchSuggestions").hide();
    		}
    	});
    })
    //on keyup, start the countdown
    $input.on('keyup', function () {
    	searchCustomerSuggestions();
    });
    //on keydown, clear the countdown
    $input.on('keydown', function () {
    	searchCustomerSuggestions();
    });
    function searchCustomerSuggestions (){
    	if(!loadingCustomer) {
    		if(customer_search_value != $(".employeeSearchInput").val()) {
    			loadingCustomer = true;
    			customer_search_value = $(".employeeSearchInput").val();
    			$('.employeeSearch .employeeSearchSuggestions').html('<div class="article-loading lds-ring"><div></div><div></div><div></div><div></div></div>').show();
    			var _data = { fwajax: 1, fw_nocss: 1, search: customer_search_value, department: '<?php echo $department_filter?>', list_filter: '<?php echo $list_filter;?>'};
    			$.ajax({
    				cache: false,
    				type: 'POST',
    				dataType: 'json',
    				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_people_suggestions";?>',
    				data: _data,
    				success: function(obj){
    					loadingCustomer = false;
    					$('.employeeSearch .employeeSearchSuggestions').html('');
    					$('.employeeSearch .employeeSearchSuggestions').html(obj.html).show();
    					searchCustomerSuggestions();
    				}
    			}).fail(function(){
    				loadingCustomer = false;
    			})
    		}
    	}
    }
    // Filter by building
    $('.filterDepartment').on('change', function(e) {
        var data = {
            list_filter: '<?php echo $list_filter;?>',
            department_filter: $(this).val(),
            search_filter: $('.searchFilter').val(),
            list_view:$(".listView").val()
        };
        loadView("list", data);
    });

    // Filter by customer name
    $('.searchFilterForm').on('submit', function(e) {
        e.preventDefault();
        var data = {
            list_filter: '<?php echo $list_filter;?>',
            department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
            list_view:$(".listView").val()
        };
        loadView("list", data);
    });
    $('.listView').on('change', function(e) {
        e.preventDefault();
        var data = {
            list_filter: '<?php echo $list_filter;?>',
            department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
            list_view:$(".listView").val()
        };
        loadView("list", data);
    });
    $(".addPeopleBtn").on('click', function(e){
        e.preventDefault();
        var data = {
            supportId: 0
        };
        ajaxCall('editPeople', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".giveAccessInBatch").on('click', function(e){
        e.preventDefault();
        var data = {
            inBatch: 1
        };
        ajaxCall('send_invitation_preview', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".resendAccessInBatch").on('click', function(e){
        e.preventDefault();
        var data = {
            inBatch: 1,
            resend: 1
        };
        ajaxCall('send_invitation_preview', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".addEditSelfDefinedFieldsBtn").on('click', function(e){
        e.preventDefault();
        var data = {
        };
        ajaxCall('editSelfdefinedFields', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".edit_contract_types").on('click', function(e){
        e.preventDefault();
        var data = {
        };
        ajaxCall('editContractTypes', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".edit_competence_register").on('click', function(e){
        e.preventDefault();
        var data = {
        };
        ajaxCall('editCompetenceRegister', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".edit_position").on('click', function(e){
        e.preventDefault();
        var data = {
        };
        ajaxCall('edit_position', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".resetSelection").on('click', function(e) {
        e.preventDefault();
        var data = {
            list_filter: '<?php echo $list_filter;?>',
            list_view:$(".listView").val()
        };
        loadView("list", data);
    });

	<?php if($b_activate_signant) { ?>
	$('.output-signant-show-unsigned').off('click').on('click', function(e){
        e.preventDefault();
        var data = {
        };
        ajaxCall('signant_show_unsigned', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
	<?php } ?>
})
</script>

<?php

require_once __DIR__ . '/functions.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");
if(!function_exists("APIconnectorUser")) include_once(__DIR__."/../../../../fw/account_fw/includes/APIconnector.php");
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
$o_query = $o_main->db->query($sql, array($variables->loggID, array($people_contactperson_type)));
$currentContactPerson = $o_query ? $o_query->row_array(): array();


$searchText = "";
if(isset($_POST['search_filter']) && $_POST['search_filter'] != ""){
	$searchText = $_POST['search_filter'];
}
if(isset($_GET['department'])){ $isDepartment = $_GET['department']; }
if(isset($_POST['department'])){
	$isDepartment = $_POST['department'];
}
$customerList = array();
$departments = array();
$groups = array();

$search_sql = "";
if($searchText != ""){
	$search_sql = " AND p.name LIKE '%".$searchText."%'";
}
$sql = "SELECT p.* FROM contactperson_group p WHERE p.status = 1 AND p.show_in_people = 1 ".$search_sql." ORDER BY p.name";
$o_query = $o_main->db->query($sql, array($people_contactperson_type));
$allGroups = $o_query ? $o_query->result_array(): array();

foreach($allGroups as $v_cache_group) {
	if(intval($v_cache_group['department']) == 1){
		array_push($departments, $v_cache_group);
	} else {
		array_push($groups, $v_cache_group);
	}
}

if($isDepartment) {
	$customerList = $departments;
} else {
	$customerList = $groups;
}

$preloadedBlocks = array();
if(isset($_GET['preloadedGroups'])){
	$preloadedBlocks = explode(",", $_GET['preloadedGroups']);
}
?>
<?php

foreach($customerList as $v_row)
{
	$isMember = false;
	$isAdmin = false;
	$notvisible_sql = "";
	if($people_contactperson_type != 2){
		$notvisible_sql = " AND (c.notVisibleInMemberOverview = 0 OR c.notVisibleInMemberOverview is null)";
	}
	$administrate_members = false;
	$sql = "SELECT p.* FROM contactperson_group_user p
	JOIN contactperson c ON c.id = p.contactperson_id
	WHERE p.contactperson_group_id = ? AND (p.status = 0 OR p.status is null) AND (p.hidden = 0 OR p.hidden is null) AND c.content_status < 2".$notvisible_sql;
	$o_query = $o_main->db->query($sql, array($v_row['id']));
	$memberCount = $o_query ? $o_query->num_rows(): 0;

	$sql = "SELECT p.* FROM contactperson_group_user p
	JOIN contactperson c ON c.id = p.contactperson_id
	WHERE p.contactperson_group_id = ? AND (p.status = 0 OR p.status is null) AND (p.hidden = 1) AND c.content_status < 2".$notvisible_sql;
	$o_query = $o_main->db->query($sql, array($v_row['id']));
	$hiddenMemberCount = $o_query ? $o_query->num_rows(): 0;

	$sql = "SELECT p.* FROM contactperson_group_user p
	JOIN contactperson c ON c.id = p.contactperson_id
	WHERE p.contactperson_group_id = ? AND (p.status = 0 OR p.status is null) AND p.type = 2 AND (p.hidden = 0 OR p.hidden is null) AND c.content_status < 2".$notvisible_sql;
	$o_query = $o_main->db->query($sql, array($v_row['id']));
	$adminMemberCount = $o_query ? $o_query->num_rows(): 0;

	$sql = "SELECT p.* FROM contactperson_group_user p WHERE p.contactperson_id = ? AND p.contactperson_group_id = ?";
	$o_query = $o_main->db->query($sql, array($currentContactPerson['id'], $v_row['id']));
	$userGroupConnection = $o_query ? $o_query->row_array(): array();
	if($userGroupConnection) {
		$isMember = true;
		if($userGroupConnection['type'] == 2){
			$isAdmin = true;
			if($userGroupConnection['administrate_settings']) {
				$administrate_members = true;
			}
		}
	}
	if($v_row['all_admins_are_group_admins'] && $variables->useradmin){
		$isAdmin = true;
		$administrate_members = true;
	}

	if(($isMember) || $v_row['show_group_to_all_in_group_list'] || $accessElementAllow_SeeAllGroupsAndDepartmentsInList){
		?>
		<div class="peopleGroupWrapper">
			<div class="peopleGroupTitle">
				<?php echo $v_row['name']?>
				<?php if($isAdmin || $accessElementAllow_AddOneselfAsGroupadmin) {?>
					<div class="peopleGroupActions">
						<?php if($accessElementAllow_AddOneselfAsGroupadmin && !$isAdmin) { ?>
							<span class="groupAddAdmin fw_text_link_color item" data-groupid="<?php echo $v_row['id']?>">+ <?php echo $formText_AddYourselfAsGroupadmin_output;?></span>
						<?php } ?>
						<?php if($isAdmin) {?>
							<?php if($administrate_members) {?>
								<span class="groupAddMember fw_text_link_color item" data-groupid="<?php echo $v_row['id']?>">+ <?php echo $formText_AddMember_output;?></span>
							<?php } ?>
							<span class="editGroup item" data-groupid="<?php echo $v_row['id']?>"><span class="glyphicon glyphicon-pencil fw_delete_edit_icon_color"></span></span>
							<span class="deleteGroup item" data-groupid="<?php echo $v_row['id']?>"><span class="glyphicon glyphicon-trash fw_delete_edit_icon_color"></span></span>
						<?php } ?>
					</div>
				<?php } ?>
				<div class="clear"></div>
			</div>
			<div class="peopleGroupContent <?php if(in_array($v_row['id'], $preloadedBlocks)) echo 'active';?>" data-groupid="<?php echo $v_row['id']?>">
				<div class="peopleGroupContentTopRow">
					<div class="peopleGroupMemberCount">
						<?php
						echo $formText_Members_output;
						?>
						<span class="glyphicon glyphicon-triangle-right fw_icon_color"></span>
						<span class="glyphicon glyphicon-triangle-bottom fw_icon_color"></span>
					</div>
					<div class="peopleGroupShowAll fw_text_link_color"><?php echo $formText_ShowAll_output;?> (<?php if(!$v_row['show_only_admins_in_group_list']) { echo $memberCount;} else { echo $adminMemberCount; }?>)</div>

					<?php if(!$v_row['show_only_admins_in_group_list'] && $adminMemberCount > 0) { ?>
						<div class="peopleGroupShowAdmins fw_text_link_color"><?php echo $formText_ShowAdmins_output;?>  (<?php echo $adminMemberCount;?>)</div>
					<?php } ?>
					<?php if($hiddenMemberCount > 0 && $isAdmin) {?>
						<div class="peopleGroupShowHidden fw_text_link_color"><?php echo $formText_Hidden_output;?>  (<?php echo $hiddenMemberCount;?>)</div>
					<?php } ?>
					<?php
					if($v_row['show_only_admins_in_group_list']) { ?>
						<div class="peopleGroupShowOnly"><?php echo $formText_ShowingOnlyAdmins_output;?></div>
					<?php } ?>
					<div class="peopleGroupSearch"><input type="text" class="searchPeople" autocomplete="off" placeholder="<?php echo $formText_Search_output;?>"/></div>
					<div class="exportContactPersons fw_text_link_color"><?php echo $formText_Export_output;?></div>
					<?php ?>
					<div class="clear"></div>
				</div>
				<div class="peopleGroupContentBottom">

				</div>
			</div>
		</div>
		<?php

	}

} ?>
<style>
	.exportContactPersons {
		float: right;
		cursor: pointer;
	}
	.peopleGroupSearch {
		float: left;
		margin-left: 20px;
		display: none;
	}
	.peopleGroupSearch input {
		border: 1px solid #cecece;
		border-radius: 5px;
		padding: 1px 5px;
	}
	.peopleGroupContent.active .peopleGroupSearch {
		display: block;
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
</style>
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
		var data = {department: '<?php echo $isDepartment?>'};
		var __data = {};
		var activeContent = $(".peopleGroupContent.active");
		if(activeContent.length > 0){
			var preloadedGroupString = "";
			activeContent.each(function(){
				var groupid = $(this).data("groupid");
				preloadedGroupString += groupid+",";
			})
			__data = {
				preloadedGroups: preloadedGroupString
			}
		}
		var finalData = $.extend({}, __data, data);
		loadView({module_file:'list', module_name: 'People', module_folder: 'output_groups'}, finalData, true);
	}
}
$(function() {
	var loadingCustomer = false;
	$(document).off('mouseenter mouseleave', '.output-access-changer')
	.on('mouseenter', '.output-access-changer', function(){
		$(this).find(".output-access-dropdown").show();
	}).on('mouseleave', '.output-access-changer', function(){
		$(this).find(".output-access-dropdown").hide();
	});
	$(".exportContactPersons").off("click").on("click", function(e){
		var parent = $(this).parents(".peopleGroupContent");
		var group_id = parent.data("groupid");

		var generateIframeDownload = function(){
			fetch("<?php echo $extradir;?>/output_groups/includes/export_contactperson.php?group_id="+group_id+"&time=<?php echo time();?>")
			  .then(resp => resp.blob())
			  .then(blob => {
				const url = window.URL.createObjectURL(blob);
				const a = document.createElement('a');
				a.style.display = 'none';
				a.href = url;
				// the filename you want
				a.download = 'export.xls';
				document.body.appendChild(a);
				a.click();
				window.URL.revokeObjectURL(url);
				out_popup.close();
			  })
			  .catch(() => fw_loading_end());
		  }

		  generateIframeDownload();
	})
	$(".editGroup").off("click").on('click', function(e){
		e.preventDefault();
        var data = {
            groupId: $(this).data("groupid"),
			department: '<?php echo $isDepartment?>'
        };
        ajaxCall({module_file:'editGroup', module_name: 'People', module_folder: 'output_groups'}, data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
	});
	$(".deleteGroup").off("click").on('click', function(e){
		e.preventDefault();
        var self = $(this);

		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
            if (result) {
		        var data = {
		            groupId: self.data("groupid"),
					department: '<?php echo $isDepartment?>',
					action: 'delete'
		        };
		        ajaxCall({module_file:'editGroup', module_name: 'People', module_folder: 'output_groups'}, data, function(json) {
					if(json.error == undefined){
						reloadListView();
					} else {
						$.each(json.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							fw_info_message_add(_type[0], value);
						});
						fw_info_message_show();
						fw_loading_end();
						fw_click_instance = fw_changes_made = false;
					}
		        });
			}
	     });
	});
	$(".groupAddMember").off("click").on('click', function(e){
		e.preventDefault();
        var data = {
            groupId: $(this).data("groupid"),
			department: '<?php echo $isDepartment?>'
        };
        ajaxCall({module_file:'editMembers', module_name: 'People', module_folder: 'output_groups'}, data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
	});
	$(".groupAddAdmin").off("click").on('click', function(e){
		e.preventDefault();
		var groupid = $(this).data("groupid");
		var data = {
			username: '<?php echo $variables->loggID?>',
			department: '<?php echo $isDepartment?>',
			groupId: groupid,
			action: "addYourselfAsAdmin"
		};
		ajaxCall({module_file:'editMemberInfo', module_name: 'People', module_folder: 'output_groups'}, data, function(json) {
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
	});
	$(".peopleGroupMemberCount").unbind("click").bind("click", function(){
		var parent = $(this).parents(".peopleGroupContent");
		parent.toggleClass("active");
		var groupId = parent.data("groupid");
		if(!parent.hasClass("loaded") && !parent.hasClass("loadedHidden")){
			loadMembers(groupId);
			if(parent.hasClass("active")){
				parent.find(".peopleGroupShowAll").addClass("active");
			}
		}
	})
	setTimeout(function(){
		$(".peopleGroupContent.active").each(function(){
			if(!$(this).hasClass("loaded")){
				var groupId = $(this).data("groupid");
				loadMembers(groupId);
			}
		})
	}, 10);

	$('.showMoreCustomersBtn').off("click").on('click', function(e) {
        page = parseInt(page)+1;
        e.preventDefault();
        var data = {
            building_filter: $(".buildingFilter").val(),
            customergroup_filter: $(".customerGroupFilter").val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
			department: '<?php echo $isDepartment?>',
            page: page,
            rowOnly: 1
        };
        ajaxCall({module_file:'list', module_name: 'People', module_folder: 'output_groups'}, data, function(json) {
            $('.p_pageContent .gtable').append(json.html);
            $(".showMoreCustomers .showing").html($(".gtable .gtable_row.output-click-helper").length);
            if(json.html.replace(" ", "") == ""){
                $(".showMoreCustomersBtn").hide();
            }
        });
    });
	bindMembersActions();
	function loadMembers(groupId, showAdmin, hidden, page, search = '') {
		var data = {
			groupId: groupId,
			page: page,
			hidden: hidden,
			show_admin: showAdmin,
			search: search
        };
        ajaxCall({module_file:'getMembers', module_name: 'People', module_folder: 'output_groups'}, data, function(json) {
			loadingCustomer = false;
			var parent = $('.peopleGroupContent[data-groupid="'+groupId+'"]');
			parent.removeClass("loadedHidden");
			parent.addClass("loaded");
			parent.find('.peopleGroupContentBottom').html(json.html);
			if(showAdmin){
				parent.find(".peopleGroupContentBottom .gtable_row").hide();
				parent.find(".peopleGroupContentBottom .gtable_row.administrator").show();
			}
			bindMembersActions();
        });
	}
    var $input = $('.searchPeople');
    var customer_search_value;
    $input.on('focusin', function () {
        searchCustomerSuggestions(this);
    })
    //on keyup, start the countdown
    $input.on('keyup', function () {
        searchCustomerSuggestions(this);
    });
    //on keydown, clear the countdown
    $input.on('keydown', function () {
        searchCustomerSuggestions(this);
    });
    function searchCustomerSuggestions (el){
        if(!loadingCustomer) {
            if(customer_search_value != $(el).val()) {
				var parent = $(el).parents(".peopleGroupContent");
				var page = 1;
				var hidden = 0;
				var admin = 0;
				var groupId = parent.data("groupid");

                loadingCustomer = true;
                customer_search_value = $(el).val();
                parent.find(".peopleGroupContentBottom").html('<div class="article-loading lds-ring"><div></div><div></div><div></div><div></div></div>').show();

				loadMembers(groupId, admin, hidden, page, $(el).val());
            }
        }
    }

	function loadHiddenMembers(groupId) {
		var data = {
			groupId: groupId,
			hidden: 1
        };
        ajaxCall({module_file:'getMembers', module_name: 'People', module_folder: 'output_groups'}, data, function(json) {
			var parent = $('.peopleGroupContent[data-groupid="'+groupId+'"]');
			parent.removeClass("loaded");
			parent.addClass("loadedHidden");
			parent.find('.peopleGroupContentBottom').html(json.html);
			bindMembersActions();
        });
	}
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

		$(".peopleGroupShowAdmins").unbind("click").bind("click", function(){
			var parent = $(this).parents(".peopleGroupContent");
			var groupId = parent.data("groupid");

			parent.find(".peopleGroupShowHidden").removeClass("active");
			parent.find(".peopleGroupShowAll").removeClass("active");
			$(this).addClass("active");
			parent.addClass("active");

			loadMembers(groupId, 1);
		})
		$(".peopleGroupShowAll").unbind("click").bind("click", function(){
			var parent = $(this).parents(".peopleGroupContent");
			var groupId = parent.data("groupid");

			parent.find(".peopleGroupShowHidden").removeClass("active");
			parent.find(".peopleGroupShowAdmins").removeClass("active");
			$(this).addClass("active");
			parent.addClass("active");

			loadMembers(groupId);
		})
		$(".peopleGroupShowHidden").unbind("click").bind("click", function(){
			var parent = $(this).parents(".peopleGroupContent");
			var groupId = parent.data("groupid");

			parent.find(".peopleGroupShowAll").removeClass("active");
			parent.find(".peopleGroupShowAdmins").removeClass("active");
			$(this).addClass("active");

			parent.addClass("active");
			loadMembers(groupId, false, true);
		})


		$(".deleteMember").on('click', function(e){
			e.preventDefault();
			var contactperson_id = $(this).data("contactperson_id");
			var groupid = $(this).data("groupid");
			var data = {
				action: "delete",
				contactperson_id: contactperson_id,
	            list_filter: 'group_tab',
				department: '<?php echo $isDepartment?>',
				groupId: groupid
			};
			ajaxCall({module_file:'editMembers', module_name: 'People', module_folder: 'output_groups'}, data, function(json) {
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
		});
		$(".editMember").on('click', function(e){
			e.preventDefault();
			var groupuser_connection_id = $(this).data("groupuser_connection_id");
			var groupid = $(this).data("groupid");
			var data = {
				groupuser_connection_id: groupuser_connection_id,
	            list_filter: 'group_tab',
				department: '<?php echo $isDepartment?>',
				groupId: groupid
			};
			ajaxCall({module_file:'editMemberInfo', module_name: 'People', module_folder: 'output_groups'}, data, function(json) {
	            $('#popupeditboxcontent').html('');
	            $('#popupeditboxcontent').html(json.html);
	            out_popup = $('#popupeditbox').bPopup(out_popup_options);
	            $("#popupeditbox:not(.opened)").remove();
			});
		});
		$(".peopleGroupContentBottom .page-link").off("click").on("click", function(e){
			e.preventDefault();
			var page = $(this).data("page");
			var hidden = $(this).data("hidden");
			var admin = $(this).data("admin");
			var groupId = $(this).data("groupid");
			loadMembers(groupId, admin, hidden, page);
		})
	}
});
</script>

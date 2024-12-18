<?php

require_once __DIR__ . '/functions.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");
if(!function_exists("APIconnectorUser")) include_once(__DIR__."/../../../../fw/account_fw/includes/APIconnector.php");

$sql = "SELECT p.* FROM contactperson p WHERE p.email = ? AND p.content_status < 2";
$o_query = $o_main->db->query($sql, array($variables->loggID));
$currentContactPerson = $o_query ? $o_query->row_array(): array();

$searchText = "";
if($_POST['search_filter'] != ""){
	$searchText = $_POST['search_filter'];
}
$isDepartment = $_GET['department'];
if($_POST['department']){
	$isDepartment = $_POST['department'];
}
$customerList = array();
$departments = array();
$groups = array();
if($groupList && $searchText == ""){
	$data = $groupList;
} else {

	$sql = "SELECT p.* FROM contactperson_group p WHERE p.status = 1 AND p.group_type = 1 AND p.name LIKE '%".$searchText."%' ORDER BY p.name";
	$o_query = $o_main->db->query($sql);
	$allGroups = $o_query ? $o_query->result_array(): array();

}
foreach($allGroups as $allGroup){
	if(intval($allGroup['department']) == 1){
		array_push($departments, $allGroup);
	} else {
		array_push($groups, $allGroup);
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

function cmp($a, $b)
{
    return strcmp(mb_strtolower($a["nameToDisplay"]), mb_strtolower($b["nameToDisplay"]));
}
foreach($customerList as $v_row)
{
	$isMember = false;
	$isAdmin = false;
	$memberCount = 0;

	$sql = "SELECT p.* FROM contactperson_group_user p
	LEFT OUTER JOIN contactperson c ON c.id = p.contactperson_id
	LEFT OUTER JOIN customer cus ON cus.id = c.customerId
	WHERE p.contactperson_group_id = ? AND (p.status = 0 OR p.status is null) AND (p.hidden = 0 OR p.hidden is null)  AND (cus.id is null OR cus.content_status < 2)";
	$o_query = $o_main->db->query($sql, array($v_row['id']));
	$memberCount = $o_query ? $o_query->num_rows(): 0;

	$sql = "SELECT p.* FROM contactperson_group_user p
	LEFT OUTER JOIN contactperson c ON c.id = p.contactperson_id
	LEFT OUTER JOIN customer cus ON cus.id = c.customerId
	WHERE p.contactperson_id = ? AND p.contactperson_group_id = ? AND (cus.id is null OR cus.content_status < 2)";
	$o_query = $o_main->db->query($sql, array($currentContactPerson['id'], $v_row['id']));
	$userGroupConnection = $o_query ? $o_query->row_array(): array();
	$userGroupConnection = $o_query ? $o_query->row_array(): array();
	if($userGroupConnection) {
		$isMember = true;
		if($userGroupConnection['type'] == 2){
			$isAdmin = true;
		}
	}


	if(($isMember) || $v_row['show_group_to_all_in_group_list'] || $v_row['editableForAllUserInCrm'] || $accessElementAllow_SeeAllGroupsAndDepartmentsInList){
		?>
		<div class="peopleGroupWrapper">
			<div class="peopleGroupTitle">
				<?php echo $v_row['name']?>
				<?php if($isAdmin || $accessElementAllow_AddOneselfAsGroupadmin || $v_row['editableForAllUserInCrm']) {?>
					<div class="peopleGroupActions">
						<?php if($accessElementAllow_AddOneselfAsGroupadmin && !$isAdmin && !$v_row['editableForAllUserInCrm']) { ?>
							<span class="groupAddAdmin fw_text_link_color item" data-groupid="<?php echo $v_row['id']?>">+ <?php echo $formText_AddYourselfAsGroupadmin_output;?></span>
						<?php } ?>
						<?php if($isAdmin || $v_row['editableForAllUserInCrm']) {?>
							<span class="groupAddMember fw_text_link_color item" data-groupid="<?php echo $v_row['id']?>">+ <?php echo $formText_AddMember_output;?></span>
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
						?> (<?php echo $memberCount;?>)
						<span class="glyphicon glyphicon-triangle-right fw_icon_color"></span>
						<span class="glyphicon glyphicon-triangle-bottom fw_icon_color"></span>
					</div>
					<?php if(!$v_row['show_only_admins_in_group_list']) { ?>
						<div class="peopleGroupShowAll active fw_text_link_color"><?php echo $formText_ShowAll_output;?></div>
						<div class="peopleGroupShowAdmins fw_text_link_color"><?php echo $formText_ShowAdmins_output;?></div>
					<?php } ?>
					<div class="peopleGroupSearch"><input type="text" class="searchPeople" autocomplete="off" placeholder="<?php echo $formText_Search_output;?>"/></div>
					<div class="exportContactPersons fw_text_link_color"><?php echo $formText_Export_output;?></div>
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
			reloadListView();
        }
		if($(this).hasClass("close-page-reload")){
			reloadListView(true);
        }
		$(this).removeClass('opened');
	}
};
function reloadListView(reloadPage = false){
	if(reloadPage){
		fw_loading_start();
		window.location.reload();
	} else {
		var data = {
			department: '<?php echo $isDepartment?>',
			list_filter: 'group_tab'
		};
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
		loadView({module_file:'list', module_name: 'Customer2', module_folder: 'output_groups'}, finalData, true);
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
	$(".editGroup").on('click', function(e){
		e.preventDefault();
        var data = {
            groupId: $(this).data("groupid"),
            list_filter: 'group_tab',
			department: '<?php echo $isDepartment?>'
        };
        ajaxCall({module_file:'editGroup', module_name: 'Customer2', module_folder: 'output_groups'}, data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
	});
	$(".deleteGroup").on('click', function(e){
		e.preventDefault();
        var self = $(this);

		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
            if (result) {
		        var data = {
		            groupId: self.data("groupid"),
		            list_filter: 'group_tab',
					department: '<?php echo $isDepartment?>',
					action: 'delete'
		        };
		        ajaxCall({module_file:'editGroup', module_name: 'Customer2', module_folder: 'output_groups'}, data, function(json) {
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
	$(".groupAddMember").on('click', function(e){
		e.preventDefault();
        var data = {
            groupId: $(this).data("groupid"),
            list_filter: 'group_tab',
			department: '<?php echo $isDepartment?>'
        };
        ajaxCall({module_file:'editMembers', module_name: 'Customer2', module_folder: 'output_groups'}, data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
	});
	$(".groupAddAdmin").on('click', function(e){
		e.preventDefault();
		var groupid = $(this).data("groupid");
		var data = {
			username: '<?php echo $variables->loggID?>',
			department: '<?php echo $isDepartment?>',
            list_filter: 'group_tab',
			groupId: groupid,
			action: "addYourselfAsAdmin"
		};
		ajaxCall({module_file:'editMemberInfo', module_name: 'Customer2', module_folder: 'output_groups'}, data, function(json) {
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
		if(!parent.hasClass("loaded")){
			loadMembers(groupId);
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

	$('.showMoreCustomersBtn').on('click', function(e) {
        page = parseInt(page)+1;
        e.preventDefault();
        var data = {
            building_filter: $(".buildingFilter").val(),
            customergroup_filter: $(".customerGroupFilter").val(),
            list_filter: 'group_tab',
            search_filter: $('.searchFilter').val(),
			department: '<?php echo $isDepartment?>',
            page: page,
            rowOnly: 1
        };
        ajaxCall({module_file:'list', module_name: 'Customer2', module_folder: 'output_groups'}, data, function(json) {
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
        ajaxCall({module_file:'getMembers', module_name: 'Customer2', module_folder: 'output_groups'}, data, function(json) {
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
	function bindMembersActions(){

		// $(".output-click-helper").on("click", function(e){
		// 	if((!$(e.target).hasClass("output-access-loader") && $(e.target).parents(".output-access-loader").length == 0)
		// 	&& (!$(e.target).hasClass("view-changer") && $(e.target).parents(".view-changer").length == 0)
		// 	&& (!$(e.target).hasClass("openChat") && $(e.target).parents(".openChat").length == 0)
		// 	&& (!$(e.target).hasClass("actionColumn") && $(e.target).parents(".actionColumn").length == 0)
		// 	&& (!$(e.target).hasClass("link") && $(e.target).parents(".link").length == 0)){
		// 		fw_load_ajax($(this).data('href'),'',true);
		// 	}
		// })
		$(".openChat").on('click', function(){
			var userId = $(this).data("userid");
		    if(fwchat != undefined && userId > 0){
		        fwchat.showChat(userId);
		    }
		})

		$(".peopleGroupShowAdmins").unbind("click").bind("click", function(){
			var parent = $(this).parents(".peopleGroupContent");
			var groupId = parent.data("groupid");

			var parent = $(this).parents(".peopleGroupContent");
			parent.find(".peopleGroupContentBottom .gtable_row").hide();
			parent.find(".peopleGroupContentBottom .gtable_row.administrator").show();
			$(this).addClass("active");
			parent.find(".peopleGroupShowAll").removeClass("active");
			loadMembers(groupId, 1);
		})
		$(".peopleGroupShowAll").unbind("click").bind("click", function(){
			var parent = $(this).parents(".peopleGroupContent");
			var groupId = parent.data("groupid");

			var parent = $(this).parents(".peopleGroupContent");
			parent.find(".peopleGroupContentBottom .gtable_row").show();
			$(this).addClass("active");
			parent.find(".peopleGroupShowAdmins").removeClass("active");
			loadMembers(groupId);
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
			ajaxCall({module_file:'editMembers', module_name: 'Customer2', module_folder: 'output_groups'}, data, function(json) {
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
			var contactperson_id = $(this).data("contactperson_id");
			var groupid = $(this).data("groupid");
			var data = {
				contactperson_id: contactperson_id,
	            list_filter: 'group_tab',
				department: '<?php echo $isDepartment?>',
				groupId: groupid
			};
			ajaxCall({module_file:'editMemberInfo', module_name: 'Customer2', module_folder: 'output_groups'}, data, function(json) {
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

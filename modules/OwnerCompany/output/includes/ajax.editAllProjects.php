<?php

$ownerCompanyAccountConfig_sql = $o_main->db->query("SELECT * FROM ownercompany_accountconfig");
if($ownerCompanyAccountConfig_sql && $ownerCompanyAccountConfig_sql->num_rows() > 0) $ownerCompanyAccountConfig = $ownerCompanyAccountConfig_sql->row();

$ownerCompanyBasisConfig_sql = $o_main->db->query("SELECT * FROM ownercompany_basisconfig");
if($ownerCompanyBasisConfig_sql && $ownerCompanyBasisConfig_sql->num_rows() > 0) $ownerCompanyBasisConfig = $ownerCompanyBasisConfig_sql->row();

// Basis config overrides from accountconfig
if($ownerCompanyAccountConfig->activateEditAllProjects > 0) {
	$ownerCompanyBasisConfig->activateEditAllProjects = $ownerCompanyAccountConfig->activateEditAllProjects - 1;
}
if($ownerCompanyAccountConfig->activateEditAllDepartment > 0) {
	$ownerCompanyBasisConfig->activateEditAllDepartment = $ownerCompanyAccountConfig->activateEditAllDepartment - 1;
}

if (isset($_POST['action']) && $_POST['action'] === 'openWithSync') {
    $integration = $ownerCompanyAccountConfig->global_integration ? $ownerCompanyAccountConfig->global_integration : 'IntegrationXledger';
    $integration_file = __DIR__ . '/../../../'. $integration .'/internal_api/load.php';
    if (file_exists($integration_file)) {
        require_once $integration_file;
        if (class_exists($integration)) {
            if ($api) unset($api);
            $api = new $integration(array(
                'o_main' => $o_main
            ));
        }
    }

    $projects_list = $api->get_projects_list();

    // Empty table
    $o_main->db->truncate('projectforaccounting');

    // Insert projects
    foreach($projects_list as $project) {
        $o_main->db->insert('projectforaccounting', array(
            'created' => date('Y-m-d H:i:s'),
            'createdBy' => $variables->loggID,
            'projectnumber' => $project['code'],
            'parentNumber' => $project['parentCode'],
            'name' => $project['description']
        ));
    }

    // Update last sync date
    $o_main->db->update('ownercompany_accountconfig', array(
        'lastProjectSyncDate' => date('Y-m-d H:i:s')
    ));
}

function getProjects($o_main, $parentNumber = 0,  $search = '') {
    $projects = array();

    if($search != ""){
      $o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE (projectnumber LIKE '%".$search."%' OR name LIKE '%".$search."%') ORDER BY projectnumber");
    } else {
        if ($parentNumber) {
            $o_main->db->order_by('projectnumber', 'ASC');
            $o_query = $o_main->db->get_where('projectforaccounting', array('parentNumber' => $parentNumber));
        } else {
            $o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE parentNumber IS NULL OR parentNumber = 0 ORDER BY projectnumber");
        }
    }

    if ($o_query && $o_query->num_rows()) {
        foreach ($o_query->result_array() as $row) {

            if($search != ""){
                array_push($projects, array(
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'number' => $row['projectnumber']
                ));
            } else {
                array_push($projects, array(
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'number' => $row['projectnumber'],
                    'parentId' => $row['parentId'] ? $row['parentId'] : 0,
                    'children' => getProjects($o_main, $row['id'])
                ));
            }
        }
    }

    return $projects;
}

function getProjectsListHtml($projects) {
    global $formText_Delete_output;
    global $formText_AddSubproject_output;
    global $formText_Edit_output;
    global $formText_ConfirmDelete_output;
    global $formText_Cancel_output;
    global $ownerCompanyBasisConfig;
    global $formText_AddNewProject_output;
    ob_start(); ?>


	<?php if($ownerCompanyBasisConfig->activateEditAllProjects == 2) { ?>
		<span class="addDepartment"><?php echo $formText_AddNewProject_output;?></span>
	<?php }?>
    <ul class="ep_project_list">
        <?php foreach ($projects as $project): ?>
            <li class="ep_project_list_item">
                <div class="ep_project_list_item_content">
                    <div class="ep_project_list_item_text">
                        <b><?php echo $project['number']; ?></b>
                        <?php echo $project['name']; ?>
						<?php if($ownerCompanyBasisConfig->activateEditAllProjects == 2) { ?>
							<span class="deleteDepartmentBtn" data-departmentid="<?php echo $project['id']?>" data-name="<?php echo $project['name']; ?>">
								<span class="glyphicon glyphicon-trash"></span>
							</span>
							<span class="editDepartmentBtn" data-departmentid="<?php echo $project['id']?>">
								<span class="glyphicon glyphicon-pencil"></span>
							</span>
                        <?php } ?>
                    </div>
                </div>

                <?php if (count($project['children'])): ?>
                <div class="ep_project_list_item_children">
                    <?php echo getProjectsListHtml($project['children']); ?>
                </div>
                <?php endif; ?>
            </li>

        <?php endforeach; ?>
    </ul>

    <?php return ob_get_clean();
}
$o_query = $o_main->db->get('ownercompany_accountconfig');
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();
$lastSyncDate = $formText_Na_output;

if ($ownercompany_accountconfig['lastProjectSyncDate']) {
    $lastSyncDate = date('H:i / d.m.Y', strtotime($ownercompany_accountconfig['lastProjectSyncDate']));
}
$projects = getProjects($o_main, 0, $_POST['search']);
$totalprojects = getProjects($o_main);
?>

<div class="ep_sync_data">
    <a href="#" class="ep_sync_projects">
        <?php echo $formText_SyncProjects_output; ?>
    </a>
    (<?php echo $formText_LastSync_output; ?>: <?php echo $lastSyncDate; ?>)
</div>


<div class="contactPersonSearch">
    <span class="glyphicon glyphicon-search"></span>
    <input type="text" placeholder="<?php echo $formText_ProjectName_output;?>" class="contactPersonSearchInput" value="<?php echo $_POST['search']?>"/>
    <span class="glyphicon glyphicon-triangle-right"></span>
</div>
<?php if(isset($_POST['search']) && trim($_POST['search']) != "") { ?>
    <div class="searchResult">
        <?php echo $formText_Searched_output." ". count($projects)." / ".count($totalprojects) ?> <span class="resetSearch"><?php echo $formText_ResetSearch_output;?></span>
    </div>
<?php } ?>
<div class="clear"></div>
<?php
echo getProjectsListHtml($projects);
?>

<script type="text/javascript">
var input = $(".contactPersonSearchInput");
input.focus();
var tmpStr = input.val();
input.val('');
input.val(tmpStr);

$(document).ready(function() {
    $('.ep_sync_projects').on('click', function(e) {
        e.preventDefault();
        var data = {
            action: 'openWithSync'
        };

        ajaxCall('editAllProjects', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".searchResult .resetSearch").unbind("click").bind("click", function(e){
        $(".contactPersonSearchInput").val("").keyup();
    })

    var typingTimer;                //timer identifier
    var doneTypingInterval = 400;  //time in ms, 5 second for example
    var $input = $('.contactPersonSearchInput');

    // $input.on('focusin', function () {
    //     typingTimer = setTimeout(doneTypingSearchContact, doneTypingInterval);
    // })
    //on keyup, start the countdown
    $input.on('keyup', function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(doneTypingSearchContact, doneTypingInterval);
    });

    //on keydown, clear the countdown
    $input.on('keydown', function () {
        clearTimeout(typingTimer);
    });

    //user is "finished typing," do something
    function doneTypingSearchContact () {
        if($(".contactPersonSearchInput").val().length > 1 || $(".contactPersonSearchInput").val().length == 0){
            var data = { fwajax: 1, fw_nocss: 1, search: $(".contactPersonSearchInput").val() };

            ajaxCall('editAllProjects', data, function(json) {
                $("#popupeditboxcontent").html(json.html);
            });
        }
    }
	$(".editDepartmentBtn").unbind("click").bind("click", function(){
		var departmentId = $(this).data("departmentid");
		var data = { fwajax: 1, fw_nocss: 1, departmentId: departmentId };

		ajaxCall('editAccountingProject', data, function(json) {
            $('#popupeditboxcontent2').html('');
            $('#popupeditboxcontent2').html(json.html);
            out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
            $("#popupeditbox2:not(.opened)").remove();
		});
	})
	$(".addDepartment").unbind("click").bind("click", function(){
		var data = { fwajax: 1, fw_nocss: 1 };

		ajaxCall('editAccountingProject', data, function(json) {
            $('#popupeditboxcontent2').html('');
            $('#popupeditboxcontent2').html(json.html);
            out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
            $("#popupeditbox2:not(.opened)").remove();
		});
	})
	$(".deleteDepartmentBtn").unbind("click").bind("click", function(){
		var departmentId = $(this).data("departmentid");
		var name = $(this).data("name");
		bootbox.confirm("<?php echo $formText_ConfirmDeleteDepartment; ?>: " + name, function(result) {
		  	if(result) {
				var data = { fwajax: 1, fw_nocss: 1, deleteDepartment: 1, departmentId: departmentId  };

				ajaxCall('editAccountingProject', data, function(json) {
					var data = { fwajax: 1, fw_nocss: 1, search: $(".contactPersonSearchInput").val() };

		            ajaxCall('editAllProjects', data, function(json) {
		                $("#popupeditboxcontent").html(json.html);
		            });
				});
			}
		}).css({"z-index": 1000000})
	})
});
</script>

<style>
.searchResult {
    margin-left: 20px;
    float: left;
}
.searchResult .resetSearch {
    margin: 0;
    margin-left: 15px;
    color: #0284C9;
    cursor: pointer;
    vertical-align: middle;
}
.contactPersonSearch {
    position: relative;
    float: left;
    margin-bottom: 10px;
}
.contactPersonSearch .contactPersonSearchSuggestions {
    display: none;
    background: #fff;
    position: absolute;
    width: 100%;
    max-height: 200px;
    overflow: auto;
    z-index: 2;
    border: 1px solid #dedede;
    border-top: 0;
}
.contactPersonSearch .contactPersonSearchSuggestions table {
    margin-bottom: 0;
}
.contactPersonSearch .contactPersonSearchSuggestions td {
    padding: 5px 10px;
}

.contactPersonSearch .glyphicon-triangle-right {
    position: absolute;
    top: 7px;
    right: 4px;
    color: #048fcf;
}
.contactPersonSearch .glyphicon-search {
    position: absolute;
    top: 7px;
    left: 6px;
    color: #048fcf;
}
.contactPersonSearchInput {
    width: 250px;
    border: 1px solid #dedede;
    padding: 3px 15px 3px 25px;
}
.contactPersonSearchInputBefore {
    width: 150px;
    border: 1px solid #dedede;
    padding: 3px 10px 3px 10px;
}
.contactPersonSearchBtn {
    background: #0093e7;
    border-radius: 5px;
    margin-left: 3px;
    color: #fff;
    padding: 5px 15px;
    cursor: pointer;
    border: 0;
}

.ep_sync_data {
    margin-bottom:20px;
}
.ep_project_list {
    margin:0;
    padding:0;
	max-height: 500px;
	overflow: auto;
}

.ep_project_list_item {
    border-top:1px solid #efecec;
    padding:7px 0;
}

.ep_project_list_item:first-child {
    border-top:none;
}

.ep_project_list_item_content::after {
    clear:both;
    display:block;
    content:" ";
}

.ep_project_list_item_text {
    float:left;
    width:60%;
}

.ep_project_list_item_btns {
    float:left;
    font-size:11px;
    width:40%;
    text-align:right;
}

.ep_delete_project_btn {
    color:red;
}

.ep_project_list_item_btns_confirm_delete {
    display:none;
}

.ep_project_list_item_btns a {
    display:inline-block;
    margin-left:10px;
}

.ep_project_list_item_btns a .glyphicon {
    font-size:0.85em;
}

.ep_project_list_item_btns a:hover {
    text-decoration:none;
}

.ep_project_list_item_add_btn {
    margin-right:10px;
}

.ep_project_list_item_children {
    margin-top:7px;
    margin-left:20px;
    border-top:1px solid #efecec;
}
.editDepartmentBtn {
	color: #0095E4;
	float: right;
	margin-right: 10px;
	cursor: pointer;
}
.deleteDepartmentBtn {
	color: #0095E4;
	float: right;
	cursor: pointer;
}
.addDepartment {
	display: inline-block;
	color: #0095E4;
	cursor: pointer;
	margin-top: 10px;
	margin-bottom: 10px;
}
</style>

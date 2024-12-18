<?php
$differentFilter = true;
include(__DIR__."/../../output/includes/list_filter.php");
?>
<?php
$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : 'group_tab';

?>
<?php
if(isset($_GET['list_filter'])) { $filter = $_GET['list_filter']; } else { $filter = "group_tab"; }
?>

<div class="p_tableFilter">
    <div class="p_tableFilter_left">
        <span class="fas fa-users fw_icon_color"></span>
        <?php  if($_GET['department']){ echo $formText_Departments_output; } else { echo $formText_Groups_Output;}?>
        <div class="addGroupBtn fw_text_link_color">+ <?php if($_GET['department']){ echo $formText_CreateNewDepartment_output; } else { echo $formText_CreateNewGroup_output; }?></div>
    </div>
    <div class="p_tableFilter_right">
        <form class="searchFilterForm2" id="searchFilterForm">
            <input type="text" class="searchFilter3" autocomplete="off" placeholder="<?php if($_GET['department']){ echo $formText_SearchAfterDepartment_output; } else { echo $formText_SearchAfterGroup_output; }?>">
            <button id="p_tableFilterSearchBtn" class="fw_button_color "><?php echo $formText_Search_output; ?></button>
        </form>
        <div class="clear"></div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
     // Filter by building
    $('.customerGroupFilter').on('change', function(e) {
        var data = {
            building_filter: $('.buildingFilter').val(),
            customergroup_filter: $(this).val(),
            list_filter: 'group_tab',
            search_filter: $('.searchFilter3').val(),
            department: '<?php echo $_GET['department']?>'
        };
        ajaxCall({module_file:'list', module_name: 'Customer2', module_folder: 'output_groups'}, data, function(json) {
            $('.p_pageContent').html(json.html);
        });
    });

    // Filter by customer name
    $('.searchFilterForm2').on('submit', function(e) {
        e.preventDefault();
        var data = {
            building_filter: $('.buildingFilter').val(),
            list_filter: 'group_tab',
            search_filter: $('.searchFilter3').val(),
            customergroup_filter: $(".customerGroupFilter").val(),
            department: '<?php echo $_GET['department']?>'
        };
        ajaxCall({module_file:'list', module_name: 'Customer2', module_folder: 'output_groups'}, data, function(json) {
            $('.p_pageContent').html(json.html);
        });
    });
    $(".addGroupBtn").on('click', function(e){
        e.preventDefault();
        var data = {
            supportId: 0,
            department: '<?php echo $_GET['department']?>'
        };
        ajaxCall({module_file:'editGroup', module_name: 'Customer2', module_folder: 'output_groups'}, data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
})
</script>

<?php
$list_filter = isset($_GET['list_filter']) ? ($_GET['list_filter']) : 'active';

?>


<?php
if(isset($_GET['list_filter'])) { $filter = $_GET['list_filter']; } else { $filter = "active"; }
?>

<div class="p_tableFilter">
    <div class="p_tableFilter_left">
        <div class="module_name">
            <span class="fas fa-address-book fw_icon_title_color wrappedIcon"></span>
             <?php if($s_module_local_name && $variables->developeraccess <= 5){ echo $s_module_local_name;} else { echo $formText_Persons_Output; } ?>
         </div>
        <div class="clear"></div>
    </div>
    <div class="p_tableFilter_right">
        <?php if($list_filter != "deleted") {?>
            <?php if(count($departments) > 0) { ?>
                <div class="fw_filter_color selectDiv filterDepartmentWrapper">
                    <div class="selectDivWrapper">
                        <select class="filterDepartment">
                            <option value=""><?php echo $formText_All_output;?></option>
                            <?php foreach($departments as $department) { ?>
                                <option value="<?php echo $department['id']?>"><?php echo $department['name'];?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="arrowDown"></div>
                </div>
            <?php } ?>
        <?php } ?>
        <form class="searchFilterForm" id="searchFilterForm">
            <input type="text" class="searchFilter" autocomplete="off" placeholder="<?php echo $formText_SearchForPeople_output;?>">
            <button id="p_tableFilterSearchBtn" class="fw_button_color "><?php echo $formText_Search_output; ?></button>
        </form>
        <div class="clear"></div>
        <div class="filteredCountRow">
            <span class="selectionCount">0</span> <?php echo $formText_InSelection_output;?>
            <div class="resetSelection fw_text_link_color"><?php echo $formText_Reset_output;?></div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
    // Filter by building
    $('.filterDepartment').on('change', function(e) {
        var data = {
            list_filter: '<?php echo $list_filter;?>',
            department_filter: $(this).val(),
            search_filter: $('.searchFilter').val(),
            person_list: 1,
            tag_view_filter: $('.tagViewFilter').val()
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent').html(json.html);
        });
    });

    // Filter by customer name
    $('.searchFilterForm').on('submit', function(e) {
        e.preventDefault();
        var data = {
            list_filter: '<?php echo $list_filter;?>',
            department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
            person_list: 1,
            tag_view_filter: $('.tagViewFilter').val()
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent').html(json.html);
        });
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
    $(".resetSelection").on('click', function(e) {
        e.preventDefault();
        var data = {
            list_filter: '<?php echo $list_filter;?>',
            person_list: 1,
            tag_view_filter: $('.tagViewFilter').val()
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent').html(json.html);
            $('.searchFilterForm .searchFilter').val("");
            $(".filterDepartment").val("");
        });
    });
})
</script>

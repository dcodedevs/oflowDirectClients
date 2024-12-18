<?php
$differentFilter = true;
include(__DIR__."/../../output/includes/list_filter.php");
?><?php
if(isset($_GET['list_filter'])) { $filter = $_GET['list_filter']; } else { $filter = "active"; }


$sql = "SELECT g.* FROM contactperson_group g
WHERE  g.group_type = 1 AND g.content_status < 2";
$o_query = $o_main->db->query($sql);
$groups = $o_query ? $o_query->result_array(): array();

?>

<div class="p_tableFilter">
    <div class="p_tableFilter_left">
        <label class=""><?php echo $formText_FilterByGroup_output;?></label>
        <select class="group_filter" autocomplete="off">
            <option value=""><?php echo $formText_Select_output;?></option>
            <option value="-1" <?php if(-1 == $_GET['group_filter']) echo 'selected';?>><?php echo $formText_NotConnectedToAnyGroups_output;?></option>
            <?php foreach($groups as $group) {
                ?>
                <option value="<?php echo $group['id']?>" <?php if($group['id'] == $_GET['group_filter']) echo 'selected';?>><?php echo $group['name'];?></option>
                <?php
            }?>
        </select>
    </div>
    <div class="p_tableFilter_right">
        <form class="searchFilterForm" id="searchFilterForm">
            <input type="text" class="searchFilter" autocomplete="off" placeholder="">
            <button id="p_tableFilterSearchBtn" class="fw_button_color "><?php echo $formText_Search_output; ?></button>
        </form>
        <div class="clear"></div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function(){

    $(".group_filter").change(function(){
        var data = {
            city_filter: '<?php echo $city_filter;?>',
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
            selfdefinedfield_filter: '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>',
            updateOnlyList: true,
            activecontract_filter: '<?php echo $activecontract_filter;?>',
            group_filter: $(".group_filter").val()
        };
        loadView('list', data);
    });
    $(".searchFilter").keyup(function(){
        delay(function(){
            var data = {
                city_filter: '<?php echo $city_filter;?>',
                list_filter: '<?php echo $list_filter; ?>',
                search_filter: $('.searchFilter').val(),
                search_by: $(".searchBy").val(),
                selfdefinedfield_filter: '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>',
                updateOnlyList: true,
                activecontract_filter: '<?php echo $activecontract_filter;?>',
                group_filter: $(".group_filter").val()
            };
            ajaxCall('list', data, function(json) {
                if($(".resultTableWrapper").length > 0){
                    $('.resultTableWrapper').html(json.html);
                } else {
                    $('.p_pageContent').html(json.html);
                }
            });
        }, 500 );
    });
    // Filter by customer name
    $('.searchFilterForm').on('submit', function(e) {
        e.preventDefault();
        var data = {
            city_filter: '<?php echo $city_filter;?>',
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
            selfdefinedfield_filter: '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>',
            activecontract_filter: '<?php echo $activecontract_filter;?>',
            group_filter: $(".group_filter").val()
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent').html(json.html);
        });
    });
})
</script>

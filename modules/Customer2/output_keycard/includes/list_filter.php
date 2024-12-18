<?php
$s_sql = "SELECT * FROM customer_listtabs_basisconfig ORDER BY sortnr";
$o_query = $o_main->db->query($s_sql);
$customer_listtabs_basisconfig = ($o_query ? $o_query->result_array() : array());

$s_sql = "select * from customer_basisconfig";
$o_query = $o_main->db->query($s_sql);
$customer_basisconfig = $o_query ? $o_query->row_array() : array();

$default_list = "all";
// if(count($customer_listtabs_basisconfig) > 0) {
// 	$default_list = $customer_listtabs_basisconfig[0]['id'];
// }

$list_filter = $_GET['list_filter'] ? $_GET['list_filter'] : $default_list;

$all_count = $with_orders_count = '<img src="'.$extradir.'/output/elementsOutput/ajax-loader.gif"/>';
if($list_filter == "all"){    
    $all_count = $itemCount;
}

$filteredCount = -1;
if($city_filter != "" || $search_filter != "" || $activecontract_filter != "" || $selfdefinedfield_filter != ""){
    $filteredCount = get_customer_list_count2($o_main, $list_filter, $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter);
}
?>

<div class="output-filter">
    <ul>        
        <li class="item<?php echo ($list_filter == 'all' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="all" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_keycard&list_filter=all"; ?>">
                <span class="link_wrapper">
                    <?php echo $formText_All_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'active' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="all" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_keycard&list_filter=active"; ?>">
                <span class="link_wrapper">
                    <?php echo $formText_ActiveKeycards_output;?>
                </span>
            </a>
        </li>
    </ul>
</div>

<?php
if(isset($_GET['list_filter'])) { $filter = $_GET['list_filter']; } else { $filter = "total"; }
?>

<div class="p_tableFilter">
    <div class="p_tableFilter_left">
        
    </div>
    <div class="p_tableFilter_right">
        <form class="searchFilterForm" id="searchFilterForm">
            <select name="searchBy" class="searchBy">
                <option value="1" <?php if($search_by == 1) echo 'selected';?>><?php echo $formText_KeyCardNumber_output_keycard;?></option>
                <option value="2" <?php if($search_by == 2) echo 'selected';?>><?php echo $formText_Customer_output_keycard;?></option>
                <option value="3" <?php if($search_by == 3) echo 'selected';?>><?php echo $formText_ContactPerson_output_keycard;?></option>
            </select>
            <input type="text" class="searchFilter" value="<?php echo $search_filter;?>">
            <button id="p_tableFilterSearchBtn"><?php echo $formText_Search_output; ?></button>
        </form>
    </div>
</div>
<style>
    .filteredWrapper {
        margin-top: 10px;
    }
    .filterLine {
        display: inline-block;
        vertical-align: middle;
        margin-right: 15px;
    }
    .p_tableFilter_left {
        max-width: 60%;
        float: left;
    }
    .p_tableFilter_right {
        float: right;
    }
    .filteredRow {
        margin-top: 5px;
        margin-right: 5px;
        float: left;
        border: 1px solid #23527c;
        padding: 2px 5px;
        border-radius: 3px;
    }
    .filteredRow .filteredLabel{
        float: left;
    }
    .filteredRow .filteredValue{
        float: left;
        margin-left: 3px;
    }
    .filteredRow .filterRemove {
        float: right;
        font-size: 10px;
        line-height: 14px;
        margin-left: 10px;
        padding: 0px 3px 1px;
        cursor: pointer;
        color: #23527c;
    }
</style>
<script type="text/javascript">
    var delay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();
    // Filter by building
    // $('.customerGroupFilter').on('change', function(e) {
    //     var data = {
    //         building_filter: $('.buildingFilter').val(),
    //         customergroup_filter: $(this).val(),
    //         list_filter: '<?php echo $list_filter; ?>',
    //         search_filter: $('.searchFilter').val()
    //     };
    //     ajaxCall('list', data, function(json) {
    //         $('.p_pageContent').html(json.html);
    //     });
    // });
    $(".searchFilter").keyup(function(){
        delay(function(){   
            var data = {                
                list_filter: '<?php echo $list_filter; ?>',
                search_filter: $('.searchFilter').val(),
                search_by: $(".searchBy").val(),
                updateOnlyList: true,            
            };
            ajaxCall('list', data, function(json) {
                $('.resultTableWrapper').html(json.html);
            });
        }, 500 );
    });
    // Filter by customer name
    $('.searchFilterForm').on('submit', function(e) {
        e.preventDefault();
        var data = {
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent').html(json.html);
        });
    });
    <?php /*
    $(function(){
        function loadTabNumber(tab_id){
            var data = {
                city_filter: '<?php echo $city_filter;?>',
                list_filter: '<?php echo $list_filter; ?>',
                search_filter: $('.searchFilter').val(),
                search_by: $(".searchBy").val(),
                selfdefinedfield_filter: '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>',
                tab_id: tab_id,            
                activecontract_filter: '<?php echo $activecontract_filter;?>'
            };
            ajaxCall('getTabNumbers', data, function(json) {
                $('.topFilterlink[data-listfilter="'+tab_id+'"] .count').html(json.html);
            }, false);
        }
        <?php if($list_filter != "all") { ?>
            loadTabNumber("all");
        <?php } ?>
        <?php if(count($customer_listtabs_basisconfig) > 0) { 
            foreach($customer_listtabs_basisconfig as $customer_listtab) {

                if($list_filter != $customer_listtab["id"]) {
                ?>  
                    loadTabNumber(<?php echo $customer_listtab["id"]?>);
                <?php 
                }
            }
        } else {
            if($list_filter != "with_orders") {
                ?>            
                loadTabNumber("with_orders");   
                <?php
            }
        }?>
    })*/?>
</script>

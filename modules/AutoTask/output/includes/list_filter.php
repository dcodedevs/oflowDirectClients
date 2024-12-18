<?php
$list_filter = $_GET['list_filter'] ? $_GET['list_filter'] : 'active';
$s_sql = "SELECT * FROM employee WHERE email = ?";
$o_query = $o_main->db->query($s_sql, array($variables->loggID));
if($o_query && $o_query->num_rows()>0){
    $currentEmployee = $o_query->row_array();
}
$responsibleperson_filter = $_POST['responsibleperson_filter'] ? $_POST['responsibleperson_filter'] : $currentEmployee['id'];

require_once __DIR__ . '/functions.php';

$all_count = get_support_list_count($o_main, 'active', $search_filter, $responsibleperson_filter);
$onhold_count = get_support_list_count($o_main, 'onhold', $search_filter, $responsibleperson_filter);
$delivered_count = get_support_list_count($o_main, 'delivered', $search_filter, $responsibleperson_filter);
$finished_count = get_support_list_count($o_main, 'finished', $search_filter, $responsibleperson_filter);
$deleted_count = get_support_list_count($o_main, 'deleted', $search_filter, $responsibleperson_filter);
?>

<div class="output-filter">
    <ul>
        <li class="item<?php echo ($list_filter == 'active' ? ' active':'');?>">
            <a class="filterlist" data-listtype="active" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=active"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $all_count; ?></span>
                    <?php echo $formText_ActiveOrders_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'onhold' ? ' active':'');?>">
            <a class="filterlist" data-listtype="onhold" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=onhold"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $onhold_count; ?></span>
                    <?php echo $formText_OnHoldOrders_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'delivered' ? ' active':'');?>">
            <a class="filterlist" data-listtype="delivered" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=delivered"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $delivered_count; ?></span>
                    <?php echo $formText_DeliveredOrders_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'finished' ? ' active':'');?>">
            <a class="filterlist" data-listtype="finished" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=finished"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $finished_count; ?></span>
                    <?php echo $formText_FinishedOrders_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'deleted' ? ' active':'');?>">
            <a class="filterlist" data-listtype="deleted" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=deleted"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $deleted_count; ?></span>
                    <?php echo $formText_DeletedProjectOrders_output;?>
                </span>
            </a>
        </li>
    </ul>
</div>

<?php
if(isset($_GET['list_filter'])) { $filter = $_GET['list_filter']; } else { $filter = "active"; }
?>

<div class="p_tableFilter">
    <div class="p_tableFilter_left">
        <?php echo $formText_ResponsiblePerson_output; ?>
        <span class="selectDiv selected">
            <span class="selectDivWrapper">
                <select name="responsiblePerson" class="responsiblePersonFilter">
                    <option value="-1"><?php echo $formText_All_output;?></option>
                    <?php
                    $s_sql = "SELECT * FROM employee ORDER BY employee.name";
                    $o_query = $o_main->db->query($s_sql);
                    if($o_query && $o_query->num_rows()>0){
                        $rows = $o_query->result_array();
                        foreach($rows as $row){ ?>
                            <option value="<?php echo $row['id']; ?>" <?php if($responsibleperson_filter == $row['id']) { echo 'selected';}?>><?php echo $row['name']; ?></option>
                            <?php 
                        }
                    }
                    ?>

                </select>
            </span>
            <span class="arrowDown"></span>
        </span>
    </div>
    <div class="p_tableFilter_right">
        <form class="searchFilterForm" id="searchFilterForm">
            <input type="text" class="searchFilter" value="<?php echo $search_filter;?>">
            <button id="p_tableFilterSearchBtn"><?php echo $formText_Search_output; ?></button>
        </form>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
     // Filter by building
    
     $('.responsiblePersonFilter').on('change', function(e) {
        var data = {
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            responsibleperson_filter: $(".responsiblePersonFilter").val(),
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent').html(json.html);
        });
    });

    // Filter by customer name
    $('.searchFilterForm').on('submit', function(e) {
        e.preventDefault();
        var data = {
            building_filter: $('.buildingFilter').val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            customergroup_filter: $(".customerGroupFilter").val(),
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent').html(json.html);
        });
    });
    // Filter by customer name
    $('.filterlist').unbind("click").on('click', function(e) {
        e.preventDefault();
        var data = {
            list_filter: $(this).data("listtype"),
            search_filter: $('.searchFilter').val(),
            responsibleperson_filter: $(".responsiblePersonFilter").val(),
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent').html(json.html);
        });
    });
})
</script>

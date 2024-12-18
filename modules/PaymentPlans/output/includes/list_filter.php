<?php
$s_sql = "select * from customer_basisconfig";
$o_query = $o_main->db->query($s_sql);
$customer_basisconfig = $o_query ? $o_query->row_array() : array();

$unhandled_count=$finished_count=$under_work_count = '<img src="'.$extradir.'/output/elementsOutput/ajax-loader.gif"/>';

$filteredCount = -1;
// if(count($filters) > 0){
//     $filteredCount = get_customer_list_count2($o_main, $list_filter, $list_filter_main, $filters);
// }

$filteredCount = get_customer_list_count2($o_main, "active", $list_filter_main, $filters);
$filteredCount2 = get_customer_list_count2($o_main, "interrupted", $list_filter_main, $filters);
$filteredCount3 = get_customer_list_count2($o_main, "completed", $list_filter_main, $filters);

$itemCount = get_customer_list_count($o_main, "active", $list_filter_main, $filters);
$itemCount2 = get_customer_list_count($o_main, "interrupted", $list_filter_main, $filters);
$itemCount3 = get_customer_list_count($o_main, "completed", $list_filter_main, $filters);

$active_count = $itemCount;
$interrupted_count =$itemCount2;
$completed_count = $itemCount3;


?>
<div class="output-filter">
    <ul>
        <li class="item<?php echo ($list_filter == 'active' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="active" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=active"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $active_count; ?></span>
                    <?php echo $formText_Active_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'interrupted' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="interrupted" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=interrupted"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $interrupted_count; ?></span>
                    <?php echo $formText_Interrupted_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'completed' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="completed" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=completed"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $completed_count; ?></span>
                    <?php echo $formText_Completed_output;?>
                </span>
            </a>
        </li>
    </ul>


     <div class="clear"></div>
</div>

<?php

$s_sql = "SELECT * FROM project_internal_repeating_tasks ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql);
$internalRepeatingTasks = ($o_query ? $o_query->result_array() : array());
?>

<div class="p_tableFilter">
    <div class="p_tableFilter_left">
        <div class="selectFilterWrapper">

        </div>
        <?php if($search_filter != "") { ?>
            <div class="filterResultRow"><?php echo $filteredCount." ".$formText_RecordsInSelection_output; ?></div>
            <div class="resetSearch"><?php echo $formText_ResetSearch_output;?></div>
        <?php } ?>
    </div>
    <div class="p_tableFilter_right">
        <form class="searchFilterForm" id="searchFilterForm">
            <input type="text" class="searchFilter" value="<?php echo $search_filter;?>">
            <button id="p_tableFilterSearchBtn"><?php echo $formText_Search_output; ?></button>
        </form>
    </div>
</div>
<style>
    .selectFilterWrapper {
        margin-bottom: 10px;
    }
    .topFilterlink img {
        width: 20px;
    }
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
    .top_filter_wrapper {
        background: #fff;
        padding: 10px 15px;
        margin-bottom: 10px;
    }
    .top_filter_wrapper .top_filter_column {
        float: left;
        margin-right: 10px;
    }
    .resetSearch {
        cursor: pointer;
        color: #46b2e2;
    }
    .output-filter .caseTypeFilterWrapper {
        float: right;
        margin-top: 10px;
        margin-right: 5px;
    }
    .output-filter ul {
        float: left;
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
    $(".openFilterPopup").unbind("click").bind("click", function(e){
    	e.preventDefault();
        var data = {
			main_filter: 'case',
            list_filter: '<?php echo $list_filter; ?>'
        };
        ajaxCall('filter', data, function(json) {
           	$('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    })
    $(".topFilterlink").unbind("click").bind("click", function(e){
        e.preventDefault();
        var data = {
			main_filter: 'case',
            list_filter: $(this).data("listfilter"),
            list_filter_main: '<?php echo $list_filter_main?>',
            responsibleperson_filter: $(".responsiblePersonFilter").val(),
            projecttype_filter: $(".projectTypeFilter").val(),
            projectcategory_filter: $(".projectCategoryFilter").val(),
            invoiceperson_filter: $(".invoiceResponsibleFilter").val(),
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
        };
        loadView("list", data);
    })
    $(".topFilterlinkMain").unbind("click").bind("click", function(e){
        e.preventDefault();
        var data = {
			main_filter: 'case',
            list_filter: '<?php echo $list_filter?>',
            list_filter_main: $(this).data("listfiltermain"),
            responsibleperson_filter: $(".responsiblePersonFilter").val(),
            projecttype_filter: $(".projectTypeFilter").val(),
            projectcategory_filter: $(".projectCategoryFilter").val(),
            invoiceperson_filter: $(".invoiceResponsibleFilter").val(),
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
        };
        loadView("list", data);
    })
    $(".activecontract_filter").change(function(){
        var data = {
			main_filter: 'case',
            list_filter: '<?php echo $list_filter;?>',
            list_filter_main: '<?php echo $list_filter_main?>',
            responsibleperson_filter: $(".responsiblePersonFilter").val(),
            projecttype_filter: $(".projectTypeFilter").val(),
            projectcategory_filter: $(".projectCategoryFilter").val(),
            invoiceperson_filter: $(".invoiceResponsibleFilter").val(),
            search_filter: '<?php echo $search_filter;?>',
            search_by: $(".searchBy").val(),
        };
        loadView("list", data);
        // ajaxCall('list', data, function(json) {
        //     $('.p_pageContent').html(json.html);
        // });
    })
    $(".filterRemove").unbind("click").bind("click", function(e){
        var removeFilter = $(this).data("removefilter");
        e.preventDefault();

        var data = {};
        data.main_filter = 'case';
        if(removeFilter != "city"){
            data.city_filter= '<?php echo $city_filter;?>';
        }
        if(removeFilter != "list"){
            data.list_filter= '<?php echo $list_filter;?>';
        }
        if(removeFilter != "search"){
            data.search_filter= '<?php echo $search_filter;?>';
        }
        if(removeFilter != "activecontract"){
            data.activecontract_filter= '<?php echo $activecontract_filter;?>';
        }
        if(removeFilter == "selfdefinedfield"){
            var removeSelfdefinedFieldId = $(this).data("selfdefinedfieldid");
            var removeSelfdefinedFieldValue = $(this).data("selfdefinedfieldvalue");
            var selfdefinedfield_filter_old =  <?php echo json_encode($selfdefinedfield_filter);?>;
            var selfdefinedfield_filter_new = {};
            $.each(selfdefinedfield_filter_old, function(index, value){
                if(index != removeSelfdefinedFieldId) {
                    selfdefinedfield_filter_new[index] = value;
                } else {
                    var myarr = value.split(",");
                    var newArray = new Array();
                    $.each(myarr, function(index2, value2){
                        if(value2 != removeSelfdefinedFieldValue) {
                            newArray.push(value2);
                        }
                    });
                    var newString = newArray.join(",");
                    selfdefinedfield_filter_new[index] = newString;
                }
            })

            data.selfdefinedfield_filter = btoa(JSON.stringify(selfdefinedfield_filter_new));
        } else {
            data.selfdefinedfield_filter = '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>';
        }

        loadView("list", data);
        // ajaxCall('list', data, function(json) {
        //     $('.p_pageContent').html(json.html);
        // });
    })
     $('.responsiblePersonFilter').on('change', function(e) {
        var data = {
			main_filter: 'case',
            list_filter: '<?php echo $list_filter; ?>',
            responsibleperson_filter: $(".responsiblePersonFilter").val(),
            projecttype_filter: $(".projectTypeFilter").val(),
            projectcategory_filter: $(".projectCategoryFilter").val(),
            invoiceperson_filter: $(".invoiceResponsibleFilter").val(),
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
        };
        loadView("list", data);
    });
     $('.caseTypeFilter').on('change', function(e) {
        var data = {
			main_filter: 'case',
            list_filter: '<?php echo $list_filter; ?>',
            list_filter_main: '<?php echo $list_filter_main?>',
            responsibleperson_filter: $(".responsiblePersonFilter").val(),
            casetype_filter: $(".caseTypeFilter").val(),
            projectcategory_filter: $(".projectCategoryFilter").val(),
            invoiceperson_filter: $(".invoiceResponsibleFilter").val(),
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
        };
        loadView("list", data);
    });
     $('.projectCategoryFilter').on('change', function(e) {
        var data = {
			main_filter: 'case',
            list_filter: '<?php echo $list_filter; ?>',
            list_filter_main: '<?php echo $list_filter_main?>',
            responsibleperson_filter: $(".responsiblePersonFilter").val(),
            projecttype_filter: $(".projectTypeFilter").val(),
            projectcategory_filter: $(".projectCategoryFilter").val(),
            invoiceperson_filter: $(".invoiceResponsibleFilter").val(),
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
        };
        loadView("list", data);
    });

    $(".searchFilter").keyup(function(){
        delay(function(){
            var data = {
    			main_filter: 'case',
                list_filter: '<?php echo $list_filter; ?>',
                list_filter_main: '<?php echo $list_filter_main?>',
                responsibleperson_filter: $(".responsiblePersonFilter").val(),
                projecttype_filter: $(".projectTypeFilter").val(),
                projectcategory_filter: $(".projectCategoryFilter").val(),
                invoiceperson_filter: $(".invoiceResponsibleFilter").val(),
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
			main_filter: 'case',
            list_filter: '<?php echo $list_filter; ?>',
            list_filter_main: '<?php echo $list_filter_main?>',
            responsibleperson_filter: $(".responsiblePersonFilter").val(),
            projecttype_filter: $(".projectTypeFilter").val(),
            projectcategory_filter: $(".projectCategoryFilter").val(),
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val()
        };

        loadView("list", data);
        // ajaxCall('list', data, function(json) {
        //     $('.p_pageContent').html(json.html);
        // });
    });
    $(".resetSearch").off("click").on("click", function(e){
        e.preventDefault();
        var data = {
			main_filter: 'case',
            list_filter: '<?php echo $list_filter; ?>',
            list_filter_main: '<?php echo $list_filter_main?>',
            responsibleperson_filter: $(".responsiblePersonFilter").val(),
            projecttype_filter: $(".projectTypeFilter").val(),
            projectcategory_filter: $(".projectCategoryFilter").val(),
            search_filter: "",
            search_by: ""
        };

        loadView("list", data);
    })
    <?php /*
    $(function(){
        function loadTabNumber(tab_id){
            var data = {
                list_filter: '<?php echo $list_filter; ?>',
                list_filter_main: '<?php echo $list_filter_main?>',
                responsibleperson_filter: $(".responsiblePersonFilter").val(),
                projecttype_filter: $(".projectTypeFilter").val(),
                search_filter: $('.searchFilter').val(),
                search_by: $(".searchBy").val(),
                tab_id: tab_id,
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
            if($list_filter != "active") {
                ?>
                loadTabNumber("active");
                <?php
            }
            if($list_filter != "inactive") {
                ?>
                loadTabNumber("inactive");
                <?php
            }
            if($list_filter != "with_uninvoiced_orders") {
                ?>
                loadTabNumber("with_uninvoiced_orders");
                <?php
            }
            if($list_filter != "not_released") {
                ?>
                loadTabNumber("not_released");
                <?php
            }
            if($list_filter != "idea") {
                ?>
                loadTabNumber("idea");
                <?php
            }
            if($list_filter != "canceled") {
                ?>
                loadTabNumber("canceled");
                <?php
            }
            // if($list_filter != "finished_not_invoiced") {
            //     ?>
            //     loadTabNumber("finished_not_invoiced");
            //     <?php
            // }
            if($list_filter != "completed") {
                ?>
                loadTabNumber("completed");
                <?php
            }
        }?>
    })*/?>
</script>

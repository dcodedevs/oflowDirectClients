<?php
$active_count = $inactive_count = '';
$active_count = count(get_ownercompany_list($o_main, 'active', $search_filter));
$inactive_count = count(get_ownercompany_list($o_main, 'inactive', $search_filter));

?>
<?php /*
<div class="output-filter">
    <ul>
        <li class="item<?php echo ($list_filter == 'active' ? ' active':'');?>">
            <a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=active&building_filter=".$building_filter; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $active_count; ?></span>
                    <?php echo $formText_Active_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'inactive' ? ' active':'');?>">
            <a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=inactive&building_filter=".$building_filter; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $inactive_count; ?></span>
                    <?php echo $formText_Inactive_output;?>
                </span>
            </a>
        </li>
    </ul>
</div>
*/?>
<div class="p_tableFilter">
    <div class="p_tableFilter_left">
       
    </div>
    <div class="p_tableFilter_right">
        <form class="searchFilterForm" id="searchFilterForm">
            <input type="text" class="searchFilter" value="<?php echo $search_filter; ?>">
            <button id="p_tableFilterSearchBtn"><?php echo $formText_Search_output; ?></button>
        </form>
    </div>
</div>

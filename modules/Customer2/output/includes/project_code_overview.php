
<?php
if($_GET['cid'] > 0) {
    $s_sql = "SELECT customer.* FROM customer WHERE customer.id = ?";
    $o_query = $o_main->db->query($s_sql, array($_GET['cid']));
    $v_data = ($o_query ? $o_query->row_array() : array());
}
?>
<div class="p_headerLine"><?php
if($moduleAccesslevel > 10)
{	?>
	<div class="backToCustomer btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_Back_Output; ?></div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="clear"></div>
	<?php
}
?>
</div>
<script type="text/javascript">
    $(".backToCustomer").on('click', function(e){
        e.preventDefault();
        fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list"; ?>', false, true);
    });
</script>

<div id="p_container" class="p_container">
	<div class="p_containerInner">
        <div class="p_content">
			<div class="p_pageContent">
                <div class="p_tableFilter">
                    <div class="p_tableFilter_left">
                        <div class="employeeSearch">
                            <span class="glyphicon glyphicon-search"></span>
                            <input type="text" placeholder="<?php echo $formText_Customer_output;?>" class="employeeSearchInput" autocomplete="off"/>
                            <span class="glyphicon glyphicon-triangle-right"></span>
                            <div class="employeeSearchSuggestions allowScroll"></div>
                        </div>
                    </div>
                    <?php if($v_data){?>
                        <div class="customerNameWrapper">
                            <?php
                            echo $formText_Customer_output.": ". $v_data['name']." ".$v_data['middlename']." ".$v_data['lastname'];
                            ?>
                        </div>
                    <?php } ?>
                </div>
                <?php
                if($v_data){
                    ?>
                    <?php
                    $s_sql = "SELECT subscriptionmulti.* FROM subscriptionmulti WHERE subscriptionmulti.customerId = ? AND subscriptionmulti.content_status < 2 ORDER BY created DESC";
                    $o_query = $o_main->db->query($s_sql, array($v_data['id']));
                    $repeatingOrders = ($o_query ? $o_query->result_array() : array());
                    if(count($repeatingOrders) > 0) {
                        ?>
                        <div class="nameWrapper"><?php echo $formText_RepeatingOrders_output;?></div>
                        <table class="gtable" id="gtable_search" style="table-layout: fixed;">
                            <tr>
                                <th class="gtable_cell gtable_cell_head"><?php echo $formText_ProjectCode_output;?></th>
                                <th class="gtable_cell gtable_cell_head"><?php echo $formText_RepeatingOrderName_output;?></th>
                                <th class="gtable_cell gtable_cell_head"><?php echo $formText_Created_output;?></th>
                            </tr>
                            <?php
                            foreach($repeatingOrders as $repeatingOrder) {
                                ?>
                                <tr>
                                    <td class="gtable_cell"><?php echo $repeatingOrder['projectId'];?></td>
                                    <td class="gtable_cell"><?php echo $repeatingOrder['subscriptionName'];?></td>
                                    <td class="gtable_cell"><?php echo date("d.m.Y", strtotime($repeatingOrder['created']));?></td>
                                </tr>
                            <?php }
                            ?>
                        </table><br/>
                        <?php
                    }
                    $s_sql = "SELECT project2.* FROM project2 WHERE project2.customerId = ? AND project2.content_status < 2 ORDER BY created DESC";
                    $o_query = $o_main->db->query($s_sql, array($v_data['id']));
                    $projects = ($o_query ? $o_query->result_array() : array());
                    if(count($projects) > 0) {
                        ?>
                        <div class="nameWrapper"><?php echo $formText_Projects_output;?></div>
                        <table class="gtable" id="gtable_search" style="table-layout: fixed;">
                            <tr>
                                <th class="gtable_cell gtable_cell_head"><?php echo $formText_ProjectCode_output;?></th>
                                <th class="gtable_cell gtable_cell_head"><?php echo $formText_ProjectName_output;?></th>
                                <th class="gtable_cell gtable_cell_head"><?php echo $formText_Created_output;?></th>
                            </tr>
                            <?php
                            foreach($projects as $project) {
                                ?>
                                <tr>
                                    <td class="gtable_cell"><?php echo $project['projectCode'];?></td>
                                    <td class="gtable_cell"><?php echo $project['name'];?></td>
                                    <td class="gtable_cell"><?php echo date("d.m.Y", strtotime($project['created']));?></td>
                                </tr>
                            <?php }
                            ?>
                        </table>
                        <?php
                    } ?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<style>

.p_pageContent .employeeSearch {
    float: right;
    position: relative;
    margin-bottom: 0;
}
.p_pageContent .employeeSearch .employeeSearchSuggestions {
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
.p_pageContent .employeeSearch .employeeSearchSuggestions table {
    margin-bottom: 0;
}
#p_container .p_pageContent .employeeSearch .employeeSearchSuggestions td {
    padding: 5px 10px;
}

.p_pageContent .employeeSearch .glyphicon-triangle-right {
    position: absolute;
    top: 10px;
    right: 4px;
    color: #048fcf;
}
.p_pageContent .employeeSearch .glyphicon-search {
    position: absolute;
    top: 10px;
    left: 6px;
    color: #048fcf;
}
.p_pageContent .employeeSearchInput {
    width: 250px;
    border: 1px solid #dedede;
    padding: 3px 15px 3px 25px;
}
.p_pageContent .employeeSearchInputBefore {
    width: 150px;
    border: 1px solid #dedede;
    padding: 3px 10px 3px 10px;
}
.p_pageContent .employeeSearchBtn {
    background: #0093e7;
    border-radius: 5px;
    margin-left: 3px;
    color: #fff;
    padding: 5px 15px;
    cursor: pointer;
    border: 0;
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
.customerNameWrapper {
    padding: 10px 15px;
    background: #fff;
    display: inline-block;
    vertical-align: middle;
    margin-top: 10px;
}
.nameWrapper {
    padding: 10px 15px;
    background: #fff;
    font-weight: bold;
}
</style>
<script type="text/javascript">

var loadingCustomer = false;
var $input = $('.employeeSearchInput');
var customer_search_value;
$input.on('focusin', function () {
    searchCustomerSuggestions();
    $("#p_container").unbind("click").bind("click", function (ev) {
        if($(ev.target).parents(".employeeSearch").length == 0){
            $(".employeeSearchSuggestions").hide();
        }
    });
})
//on keyup, start the countdown
$input.on('keyup', function () {
    searchCustomerSuggestions();
});
//on keydown, clear the countdown
$input.on('keydown', function () {
    searchCustomerSuggestions();
});
function searchCustomerSuggestions (){
    if(!loadingCustomer) {
        if(customer_search_value != $(".employeeSearchInput").val()) {
            loadingCustomer = true;
            customer_search_value = $(".employeeSearchInput").val();
            $('.employeeSearch .employeeSearchSuggestions').html('<div class="article-loading lds-ring"><div></div><div></div><div></div><div></div></div>').show();
            var _data = { fwajax: 1, fw_nocss: 1, search: customer_search_value, from_project_code_overview: 1};
            $.ajax({
                cache: false,
                type: 'POST',
                dataType: 'json',
                url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers_suggestions";?>',
                data: _data,
                success: function(obj){
                    loadingCustomer = false;
                    $('.employeeSearch .employeeSearchSuggestions').html('');
                    $('.employeeSearch .employeeSearchSuggestions').html(obj.html).show();
                    searchCustomerSuggestions();
                }
            }).fail(function(){
                loadingCustomer = false;
            })
        }
    }
}
</script>

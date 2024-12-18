<?php
$customerId = $_POST['customerId'];
$order_field = isset($_POST['order_field']) ?  $_POST['order_field'] : 'created';
$order_direction = isset($_POST['order_direction']) ?  $_POST['order_direction'] : 0;


?>
<?php if($moduleAccesslevel > 10) { ?><button class="addEntryBtn edit-task-btn"><?php echo $formText_Add_output;?></button><?php } ?>
<br/><br/>
<table class="table">
    <tr>
        <th class="orderBy"  data-orderfield="created" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>"><?php echo $formText_Created_output;?>
            <div class="ordering">
                <div class="fas fa-caret-up" <?php if($order_field == "created" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
                <div class="fas fa-caret-down" <?php if($order_field == "created" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
            </div>
        </th>
        <th><?php echo $formText_Performer_output;?></th>
        <th><?php echo $formText_TaskName_output;?></th>
        <th class="orderBy" data-orderfield="tododate" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>"><?php echo $formText_ToDoDate_output;?>
            <div class="ordering">
                <div class="fas fa-caret-up" <?php if($order_field == "tododate" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
                <div class="fas fa-caret-down" <?php if($order_field == "tododate" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
            </div>
        </th>
    </tr>
<?php
switch($order_direction) {
    case "0";
        $sql_order_direction = " DESC";
    break;
    case "1";
        $sql_order_direction = " ASC";
    break;
}
switch($order_field) {
    case "created";
        $sql_order_by = " ORDER BY p.created ".$sql_order_direction;
    break;
    case "tododate";
        $sql_order_by = " ORDER BY tododate ".$sql_order_direction;
    break;
    default:
        $sql_order_by = " ORDER BY p.created ".$sql_order_direction;
    break;
}

$s_sql = "SELECT p.*, COALESCE(IF(deadline = '0000-00-00',null,deadline), IF(task_date = '0000-00-00', null, task_date)) AS tododate FROM task_crm p WHERE p.customerId = ? ".$sql_order_by;
$o_query = $o_main->db->query($s_sql, array($customerId));
$customer_tasks = $o_query ? $o_query->result_array() : array();
foreach($customer_tasks as $customer_task) {
    $s_sql = "SELECT * FROM contactperson WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, $customer_task['responsible_person_id']);
    $performer = $o_query ? $o_query->row_array() : array();
    ?>
    <tr class="edit-task-btn" data-task-id="<?php echo $customer_task['id'];?>" data-customer-id="<?php echo $customerId; ?>">
        <td><?php echo date("d.m.Y", strtotime($customer_task['created']));?></td>
        <td><?php echo $performer['name']." ".$performer['middlename']." ".$performer['lastname'];?></td>
        <td><?php echo $customer_task['name'];?></td>
        <td><?php
        if($customer_task['deadline'] != "0000-00-00" && $customer_task['deadline'] != null){ echo date("d.m.Y", strtotime($customer_task['deadline']))." (".$formText_Deadline_output.")"; }
        if($customer_task['task_date'] != "0000-00-00" && $customer_task['task_date'] != null){
            echo date("d.m.Y", strtotime($customer_task['task_date']));
            if(!$customer_task['no_time']){
                echo " ".date("H:i", strtotime($customer_task['task_time']));
            }
            echo " (".$formText_SpecificDate_output.")"; }

        ?></td>
    </tr>
    <?php
}
?>

</table>
<style>
.task_column {
    width: 20%;
}
.orderBy {
	cursor: pointer;
}
.gtable_cell_head .orderBy {
	padding: 3px 0px;
}
.ordering {
	display: inline-block;
	vertical-align: middle;
}
.ordering div {
	display: block;
	line-height: 8px;
	color: #46b2e2;
}
.orderActive .ordering div {
	color: #FF4500;
}
</style>
<script type="text/javascript">
    $(function(){
        $(".edit-task-btn").off("click").on('click', function(e){
    		e.preventDefault();
            var data = {
                taskId: $(this).data("task-id"),
                customerIdFromCustomer: '<?php echo $customerId;?>'
            };
            ajaxCall({module_file:'editTask', module_name: 'CaseCrm', module_folder: 'output'}, data, function(json) {
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(json.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
            });
    	});
        $(".delete-task-btn").off("click").on('click', function(e){
    		e.preventDefault();
            var self = $(this);
            var data = {
                taskId: $(this).data("task-id"),
                action: "deleteProject"
            };
    		bootbox.confirm('<?php echo $formText_ConfirmDeleteTask_output; ?>', function(result) {
    			if (result) {
    				ajaxCall({module_file:'editTask', module_name: 'CaseCrm', module_folder: 'output'}, data, function(json) {
    					output_reload_page();
    				});
    			}
    		});
    	});

        $(".orderBy").off("click").on("click", function(){
    		var order_field = $(this).data("orderfield");
    		var order_direction = $(this).data("orderdirection");

            var data = {
    			customerId: '<?php echo $customerId;?>',
    			typeId: 'tasks',
    			order_field: order_field,
    			order_direction: order_direction
    		}
    		ajaxCall("customer_activity_content", data, function(json) {
    			$(".customer_activity_content").html(json.html);
    			rebind_prospect_button();
    		});
    	})

    })
</script>

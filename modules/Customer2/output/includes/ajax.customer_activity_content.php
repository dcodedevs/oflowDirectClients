<?php
$typeId = $_POST['typeId'] ? $_POST['typeId'] : '';
$customerId = $_POST['customerId'] ? $_POST['customerId'] : '';
if($typeId == "pipelineProspects"){
    include("ajax.prospect_list.php");
} else if($typeId == "eventParticipants") {
    include("ajax.eventparticipant_list.php");
} else if($typeId == "tasks"){
    include("ajax.crmtask_list.php");
} else if($typeId == "comments"){
    include("ajax.comments_list.php");
}  else {
    $s_sql = "SELECT * FROM customer_activity_types WHERE  id = ?";
    $o_query = $o_main->db->query($s_sql, array($typeId));
    $customer_activity_type = ($o_query ? $o_query->row_array():array());
    if($customer_activity_type){
        $s_sql = "SELECT * FROM customer_activity WHERE activity_type_id = ? AND customer_id = ? ORDER BY id ASC";
        $o_query = $o_main->db->query($s_sql, array($typeId, $customerId));
        $v_comments = ($o_query ? $o_query->result_array():array());
        ?>
        <button class="addEntryBtn edit_customer_activity" data-type-id="<?php echo $typeId;?>" data-customer-id="<?php echo $customerId?>"><?php echo $formText_AddActivity_output;?></button>
        <?php
        foreach($v_comments as $v_comment)
        {
            ?><div class="output-comment">
                <div>
                    <span class="createdBy">
                        <?php echo (!empty($v_comment['name']) ? $v_comment['name'] : $v_comment['createdBy']);  ?>
                    </span>
                    <span class="createdTime">
                        <?php echo date('d.m.Y H:i', strtotime($v_comment['created']));?>
                    </span>
                    <?php if($moduleAccesslevel > 10) { ?>
                    <button class="editBtnIcon small edit_customer_activity editBtnBlank" data-cid="<?php echo $v_comment['id'];?>"><span class="glyphicon glyphicon-pencil"></span></button>
                    <?php } ?>
                    <?php if($moduleAccesslevel > 110) { ?>
                    <button class="editBtnIcon small delete_customer_activity editBtnBlank"  data-cid="<?php echo $v_comment['id'];?>" data-delete-msg="<?php echo $formText_DeleteActivity_Output;?>?"><span class="glyphicon glyphicon-trash"></span></button>
                    <?php } ?>
                </div>
                <div class="commentTitle"><?php echo $v_comment['title'];?></div>
                <div class="commentText"><?php echo nl2br($v_comment['description']);?></div>
            </div><?php
        }
        ?>
        <script>
            $(function(){
                $(".edit_customer_activity").off("click").on("click", function(){
                    var typeId = $(this).data("type-id");
                    var customerId = $(this).data("customer-id");
                    var cid = $(this).data("cid");

                    var data = {
                        customerId: customerId,
                        typeId: typeId,
                        cid: cid
                    }
                    ajaxCall("edit_customer_activity", data, function(json) {
                        $('#popupeditboxcontent').html('');
                        $('#popupeditboxcontent').html(json.html);
                        out_popup = $('#popupeditbox').bPopup(out_popup_options);
                        $("#popupeditbox:not(.opened)").remove();
                    });
                })
                $(".delete_customer_activity").off("click").on("click", function(){
                    if(!fw_click_instance)
                    {
                        var _this = this;
                        var cid = $(this).data("cid");
                        fw_click_instance = true;
                        bootbox.confirm({
                            message:$(_this).attr("data-delete-msg"),
                            buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
                            callback: function(result){
                                if(result)
                                {
                                    var data = {
                                        output_delete: 1,
                                        cid: cid
                                    }
                                    ajaxCall("edit_customer_activity", data, function(json) {
                                        $(_this).parents(".output-comment").remove();
                                    });
                                }
                                fw_click_instance = false;
                            }
                        });
                    }
                })
            })
        </script>
        <?php
    }
}
?>

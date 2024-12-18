<?php
$customerId = $_POST['customerId'] ? $_POST['customerId'] : '';
if(!$accessElementRestrict_Comments) {
    $v_comments = array();
    $s_sql = "SELECT * FROM customer_comments WHERE customer_id = ?";
    $o_query = $o_main->db->query($s_sql, array($customerId));
    if($o_query && $o_query->num_rows()>0){
        $v_comments = $o_query->result_array();
    }
     ?>
     <?php if($moduleAccesslevel > 10) { ?><button id="output-add-comment" class="addEntryBtn"><?php echo $formText_Add_output;?></button><?php } ?>
     <br/><br/>
     <table class="table">
         <tr>
             <th><?php echo $formText_Created_output;?></th>
             <th><?php echo $formText_CreatedBy_output;?></th>
             <th><?php echo $formText_Comment_output;?></th>
             <th></th>
         </tr>
        <?php
        foreach($v_comments as $v_comment)
        {
        ?><tr>
            <td><?php echo date('d.m.Y H:i', strtotime($v_comment['created']));?></td>
            <td><?php echo (!empty($v_comment['name']) ? $v_comment['name'] : $v_comment['createdBy']);  ?></td>
            <td><?php echo nl2br($v_comment['comment']);?></td>
            <td>
                <?php if($moduleAccesslevel > 10) { ?>
                <button class="editBtnIcon small output-edit-comment editBtnBlank" data-cid="<?php echo $v_comment['id'];?>"><span class="glyphicon glyphicon-pencil"></span></button>
                <?php } ?>
                <?php if($moduleAccesslevel > 110) { ?>
                <button class="editBtnIcon small output-delete-comment editBtnBlank" data-url="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_comment&cid=".$v_comment['id'];?>" data-delete-msg="<?php echo $formText_DeleteComment_Output;?>?"><span class="glyphicon glyphicon-trash"></span></button>
                <?php } ?>
            </td>
        </tr><?php
        }
        ?>
    </table>
    <script type="text/javascript">
        $(function(){
        	$(".output-edit-comment").on('click', function(e){
        		output_edit_comment($(this).data('cid'));
        	});
        	$("#output-add-comment").on('click', function(e){
        		output_edit_comment();
        	});
            $('.output-delete-comment').on('click',function(e){
        		e.preventDefault();
        		if(!fw_click_instance)
        		{
        			fw_click_instance = true;
        			var $_this = $(this);
        			bootbox.confirm({
        				message:$_this.attr("data-delete-msg"),
        				buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
        				callback: function(result){
        					if(result)
        					{
        						$.ajax({
        							cache: false,
        							type: 'POST',
        							dataType: 'json',
        							data: {fwajax: 1, fw_nocss: 1, customerId: '<?php echo $customerId;?>', output_delete: 1},
        							url: $_this.data('url'),
        							success: function(data){
        								if(data.error !== undefined)
        								{
        									fw_info_message_empty();
        									$.each(data.error, function(index, value){
        										var _type = Array("error");
        										if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
        										fw_info_message_add(_type[0], value);
        									});
        									fw_info_message_show();
        									fw_loading_end();
        								} else {
                                            output_reload_page();
        								}
        							}
        						});
        					}
        					fw_click_instance = false;
        				}
        			});
        		}
        	});
        })
    </script>
<?php }

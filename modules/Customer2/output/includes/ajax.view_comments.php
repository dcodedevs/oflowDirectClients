<?php
$subscriptionmulti_id = $_POST['subscriptionmulti_id'];
$project2_period_id = $_POST['project2_period_id'];

if($subscriptionmulti_id == 0 && $project2_period_id == 0) {
    echo $formText_MissingId_output;
} else {
    if($_POST['action'] == "delete_comment"){
        $sql = "DELETE FROM total_result_comments WHERE id = '".$o_main->db->escape_str($_POST['comment_id'])."'";
    	$o_query = $o_main->db->query($sql);
    } else {


        if($subscriptionmulti_id > 0){
    		$sql = "SELECT * FROM total_result_comments WHERE subscriptionmulti_id = '".$o_main->db->escape_str($subscriptionmulti_id)."'";
    		$o_query = $o_main->db->query($sql);
    		$comments = $o_query ? $o_query->result_array() : array();
        } else if($project2_period_id > 0) {
    		$sql = "SELECT * FROM total_result_comments WHERE project2_period_id = '".$o_main->db->escape_str($project2_period_id)."'";
    		$o_query = $o_main->db->query($sql);
    		$comments = $o_query ? $o_query->result_array() : array();
        }
    	$current_comment = $comments[0];
    }

    if($moduleAccesslevel > 10) {
    	if(isset($_POST['output_form_submit'])) {
            if($current_comment){
                $s_sql = "UPDATE total_result_comments SET
                updated = now(),
                updatedBy= '".$o_main->db->escape_str($variables->loggID)."',
                project2_period_id = '".$o_main->db->escape_str($_POST['project2_period_id'])."',
                subscriptionmulti_id = '".$o_main->db->escape_str($_POST['subscriptionmulti_id'])."',
                comment = '".$o_main->db->escape_str($_POST['comment'])."'
                WHERE id = '".$o_main->db->escape_str($current_comment['id'])."'";
                $o_query = $o_main->db->query($s_sql);
                if($o_query){
                    $fw_return_data = $s_sql;
                    $fw_redirect_url = $_POST['redirect_url'];
                }
            } else {
                $s_sql = "INSERT INTO total_result_comments SET
                created = now(),
                createdBy= '".$o_main->db->escape_str($variables->loggID)."',
                project2_period_id = '".$o_main->db->escape_str($_POST['project2_period_id'])."',
                subscriptionmulti_id = '".$o_main->db->escape_str($_POST['subscriptionmulti_id'])."',
                comment = '".$o_main->db->escape_str($_POST['comment'])."'";
                $o_query = $o_main->db->query($s_sql);
                if($o_query){
                    $fw_return_data = $s_sql;
                    $fw_redirect_url = $_POST['redirect_url'];
                }
            }
    	}
    }

    ?>
    <div class="popupform">
        <form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=view_comments";?>" method="post">
        	<input type="hidden" name="fwajax" value="1">
        	<input type="hidden" name="fw_nocss" value="1">
        	<input type="hidden" name="output_form_submit" value="1">
        	<input type="hidden" name="subscriptionmulti_id" value="<?php print $subscriptionmulti_id;?>">
        	<input type="hidden" name="project2_period_id" value="<?php print $project2_period_id;?>">
        	<input type="hidden" name="comment_id" value="<?php print $current_comment['id'];?>">
            <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId; ?>">
            <div class="inner">
                <div class="popupformTitle"><?php if($current_comment){ echo $formText_EditComment_output; } else { echo $formText_AddNewComment_output;}?></div>
        		<div class="line">
                    <div class="lineTitle"><?php echo $formText_Comments_Output; ?></div>
                    <div class="lineInput">
                        <textarea name="comment" class="popupforminput botspace" required><?php echo $current_comment['comment']?></textarea>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>

        	<div id="popup-validate-message" style="display:none;"></div>
        	<div class="popupformbtn">
                <?php
                if($current_comment){
                ?>
                    <button type="button" class="output-btn b-large delete_result_comment" data-comment-id="<?php echo $current_comment['id']?>"><?php echo $formText_DeleteComment_Output;?></button>
                <?php } ?>
                <button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
                <input type="submit" name="sbmbtn" value="<?php if($current_comment){ echo $formText_EditComment_output; } else {echo $formText_AddComment_Output; } ?>">
        	</div>
        </form>
    </div>
    <style>
    .edit_result_comment {
        color: #46b2e2;
        cursor: pointer;
    }
    .delete_result_comment {
        color: #46b2e2;
        cursor: pointer;
    }

    </style>
    <script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
    <script type="text/javascript">

    $(document).ready(function() {
        $("form.output-form").validate({
            submitHandler: function(form) {
                fw_loading_start();
                $.ajax({
                    url: $(form).attr("action"),
                    cache: false,
                    type: "POST",
                    dataType: "json",
                    data: $(form).serialize(),
                    success: function (data) {
                        fw_loading_end();
                        if(data.redirect_url !== undefined)
                        {
                            var data = {
                				subscriptionmulti_id: '<?php echo $subscriptionmulti_id;?>',
                				project2_period_id: '<?php echo $project2_period_id;?>'
                			};
                			ajaxCall('view_comments', data, function(json) {
                                out_popup.addClass("close-reload");
                                out_popup.close();
                			});
                        }
                    }
                }).fail(function() {
                    $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                    $("#popup-validate-message").show();
                    $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
                    fw_loading_end();
                });
            },
            invalidHandler: function(event, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var message = errors == 1
                    ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                    : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                    $("#popup-validate-message").html(message);
                    $("#popup-validate-message").show();
                    $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
                } else {
                    $("#popup-validate-message").hide();
                }
                setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
            }
        });

		$(".edit_result_comment").off("click").on("click", function(){
			var data = {
                subscriptionmulti_id: '<?php echo $subscriptionmulti_id;?>',
                project2_period_id: '<?php echo $project2_period_id;?>',
                comment_id: $(this).data("comment-id")
			};
			ajaxCall('view_comments', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			});
		})

		$(".delete_result_comment").off("click").on("click", function(){
            var _this = this;
			bootbox.confirm({
				message: '<?php echo $formText_DeleteComment_output;?>',
				buttons: {confirm:{label:'<?php echo $formText_Yes_Output;?>'},cancel:{label:'<?php echo $formText_No_Output;?>'}},
				callback: function(result){
					fw_click_instance = false;
					if(result)
					{
            			var data = {
                            subscriptionmulti_id: '<?php echo $subscriptionmulti_id;?>',
                            project2_period_id: '<?php echo $project2_period_id;?>',
                            action:"delete_comment",
                            comment_id: $(_this).data("comment-id")
            			};

            			ajaxCall('view_comments', data, function(json) {
                            out_popup.addClass("close-reload");
                            out_popup.close();
            			});
                    }
                }
            }).css({"z-index": 10000})
		})
    });

    </script>
    <?php
}

?>

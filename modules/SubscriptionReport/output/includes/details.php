<?php
// Get support module id
$orders_module_id = "";
$o_query = $o_main->db->query("SELECT * FROM moduledata WHERE name = 'Support'");
if($o_query && $o_row = $o_query->row()) $orders_module_id = $o_row->uniqueID;

require_once __DIR__ . '/list_btn.php';

$cid = $_GET['cid'];

$ordersData = array();
$o_query = $o_main->db->query("SELECT * FROM orders WHERE id = ?", array($cid));
if($o_query && $o_query->num_rows()>0) $ordersData = $o_query->row_array();

$s_page_reload_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$ordersData['id'];

?>
<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<div class="p_pageDetails">
					<div class="p_pageDetailsTitle">
                    	<?php echo $formText_OrderDetails_Output;?>
                    </div>
                    <div class="p_contentBlock no-vertical-padding">
                        <div class="customerDetails">
                            <table class="mainTable fullTable" width="100%"  border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="txt-label border-left"><?php echo $formText_AddOnInvoice_output;?></td>
                                    <td class="txt-value">
                                    	<input type="checkbox" class="defaultCheckbox addOnInvoiceCheckbox" data-order-id="<?php echo $row['id']?>" data-customer-id="<?php echo $cid; ?>" <?php if($ordersData['addOnInvoice']) { echo 'checked';}?> disabled/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="txt-label border-left"><?php echo $formText_Id_output;?></td>
                                    <td class="txt-value"><?php echo $ordersData['id'];?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label border-left"><?php echo $formText_DateCreated_output;?></td>
                                    <td class="txt-value"><?php echo $ordersData['created'];?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label border-left"><?php echo $formText_Name_output;?></td>
                                    <td class="txt-value"><?php echo $ordersData['articleName'];?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label border-left"><?php echo $formText_Description_output;?></td>
                                    <td class="txt-value"><?php echo nl2br($ordersData['describtion']);?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label border-left"><?php echo $formText_Amount_output;?></td>
                                    <td class="txt-value"><?php echo number_format($ordersData['amount'], 2, ",", "");?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label border-left"><?php echo $formText_PricePerPiece_output;?></td>
                                    <td class="txt-value"><?php echo number_format($ordersData['pricePerPiece'], 2, ",", "");?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label border-left"><?php echo $formText_Discount_output;?></td>
                                    <td class="txt-value"><?php echo number_format($ordersData['discountPercent'], 2, ",", "");?></td>
                                </tr>
                                <tr>
                                    <td class="txt-label border-left"><?php echo $formText_PriceTotal_output;?></td>
                                    <td class="txt-value"><?php echo number_format($ordersData['priceTotal'], 2, ",", "");?></td>
                                </tr>
                                <tr>
                                    <td class="btn-edit" colspan="2"><?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-order editBtnBlank" data-order-id="<?php echo $cid; ?>"><?php echo $formText_Edit_Output;?></button><?php } ?></td>
                                </tr>
                            </table>
                            <div class="clear"></div>
                        </div>
					</div> 
					<div class="p_contentBlock">
						<?php
						$o_query = $o_main->db->query("SELECT * FROM activity WHERE content_status < 2 AND orderId = ?", array($ordersData['id']));
						if($o_query) $activities_count = $o_query->num_rows()>0;                               
						?>
                        <div class="p_contentBlockTitle activitiesTitle show_activities show_dropdown" data-order-id="<?php echo $ordersData['id'];?>">
                            <b><?php echo $formText_Activities_Output;?></b>
                            <span class="badge">
                                <?php echo $activities_count; ?>
                            </span>
                            <?php if($moduleAccesslevel > 10) { ?>
                                <a href="#" class="output-edit-activity addEntryBtn small" data-order-id="<?php echo $ordersData['id']?>" data-activity-id="" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_AddActivity_output;?></a>
                            <?php } ?>
                            <div class="showArrow"><span class="glyphicon glyphicon-triangle-right"></span></div>
                        </div>
                        <div class="activityList activities_content">
                           <!--  <div class="activityRow activityRowTitle">
                                <div class="activityName">
                                    <?php echo $formText_ActivityName_output;?>
                                </div>
                                <div class="activityResponsiblePerson">
                                    <?php echo $formText_ResponsiblePerson_output;?>
                                </div>
                                <div class="activityAction">
                                </div>
                            </div> -->
                        
                        </div>
                    </div>    
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, false],
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
		if($(this).is('.close-reload')) {
			var redirectUrl = $(this).data("redirect");
			if(redirectUrl !== undefined && redirectUrl != ""){
				document.location.href = redirectUrl;
			} else {
            	loadView("details", {cid: "<?php echo $cid;?>"});
            }
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};

$(function(){
	<?php if($moduleAccesslevel > 10) { ?>
		$(".output-edit-order").unbind("click").on('click', function(e){
		    e.preventDefault();
		    var data = {
		        orderId: $(this).data('order-id'),
		    };
		    ajaxCall('editOrder', data, function(json) {
		        $('#popupeditboxcontent').html('');
		        $('#popupeditboxcontent').html(json.html);
		        out_popup = $('#popupeditbox').bPopup(out_popup_options);
		        $("#popupeditbox:not(.opened)").remove();
		    });
		});
		rebindActivities();
	    function rebindActivities(){
	        $(".output-edit-activity").unbind("click").on('click', function(e){
	            e.preventDefault();
	            var data = {
	                orderId: $(this).data('order-id'),
	                activityId: $(this).data('activity-id'),
	                customerId: $(this).data('customer-id'),
	            }
	            ajaxCall('editActivity', data, function(json) {
	                $('#popupeditboxcontent').html('');
	                $('#popupeditboxcontent').html(json.html);
	                out_popup = $('#popupeditbox').bPopup(out_popup_options);
	                $("#popupeditbox:not(.opened)").remove();
	            });
	        });    

	        $(".output-delete-activity").unbind("click").on('click', function(e){
	            e.preventDefault();
	            var self = $(this);

	            bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
	                if (result) {
	                    var data = {
	                        activityId: self.data('activity-id'),
	                        action: 'deleteActivity'
	                    };
	                    ajaxCall('editActivity', data, function(json) {
	                        output_reload_page();
	                    });
	                }
	            });
	        });
	        $(".output-edit-partactivity").unbind("click").on('click', function(e){
	            e.preventDefault();
	            var data = {
	                partactivityId: $(this).data('partactivity-id'),
	                activityId: $(this).data('activity-id'),
	                orderId: $(this).data('order-id'),
	            }
	            ajaxCall('editPartactivity', data, function(json) {
	                $('#popupeditboxcontent').html('');
	                $('#popupeditboxcontent').html(json.html);
	                out_popup = $('#popupeditbox').bPopup(out_popup_options);
	                $("#popupeditbox:not(.opened)").remove();
	            });
	        });    

	        $(".output-delete-partactivity").unbind("click").on('click', function(e){
	            e.preventDefault();
	            var self = $(this);

	            bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
	                if (result) {
	                    var data = {
	                        partactivityId: self.data('partactivity-id'),
	                        action: 'deletePartactivity'
	                    };
	                    ajaxCall('editPartactivity', data, function(json) {
	                        output_reload_page();
	                    });
	                }
	            });
	        });
	    }
	<?php } ?>
	$(".show_activities").unbind("click").bind("click", function(e){
	    if(!$(e.target).hasClass("output-edit-activity")){
	        var titleBlock = $(this);
	        var parent = $(this).parents(".p_contentBlock");
	        e.preventDefault();
	        if(parent.find(".activities_content .activityRow").length > 0 ){
	            if(parent.find(".activities_content").is(":visible")) {
	                parent.find(".activities_content").slideUp();
	                titleBlock.find(".showArrow .glyphicon").addClass("glyphicon-triangle-right");
	                titleBlock.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-bottom");
	            } else {
	                parent.find(".activities_content").slideDown();
	                titleBlock.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
	                titleBlock.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");
	            }
	        } else {
	            var data = {
	                orderId: $(this).data('order-id')
	            };
	            ajaxCall('activity_list', data, function(json) {
	                titleBlock.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
	                titleBlock.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");
	                parent.find(".activities_content").html(json.html).slideDown();
	                rebindActivities();
	            });
	        }
	    }
	})
	<?php if(intval($_GET['activity']) > 0) { ?>
		$(".show_activities").click();
	<?php } ?>

})
</script>
<style>
	.fullTable td {
		padding: 5px 15px;
	}
	.output-edit-order {
		float: right;
	}
	.activityList {
		display: none;
		border: 1px solid #cecece;
		border-bottom: 0;
		margin-top: 10px;
	}
	.activityRow {
		padding: 10px 15px;
		border-bottom: 1px solid #cecece;
	} 
	.activityRow .activityName {
		display: inline-block;
		vertical-align: middle;
		width: 30%;
	}
	.activityRow .activityResponsiblePerson {
		display: inline-block;
		vertical-align: middle;
		width: 30%;
	}
	.activityRow .activityStatus {
		display: inline-block;
		vertical-align: middle;
		width: 20%;
	}
	.activityRow .activityAction {
		display: inline-block;
		vertical-align: middle;
		width: 15%;
	}
	.activityRow .partActivityList {
		padding: 10px 20px 0px;
	}
	.activityRow .output-edit-partactivity {
		margin-top: 10px;
	}
	.activityRow .partActivityList .partactivityRow {
		border-bottom: 1px solid #cecece;
		padding: 3px 5px;
	}
	.activityRow .partActivityList .partactivityName {
		display: inline-block;
		vertical-align: middle;
		width: 30%;
	}
	.activityRow .partActivityList .partactivityDescription {
		display: inline-block;
		vertical-align: middle;
		width: 30%;
	}
	.activityRow .partActivityList .partactivityStatus {
		display: inline-block;
		vertical-align: middle;
		width: 20%;
	}
	.activityRow .partActivityList .partactivityAction {
		display: inline-block;
		vertical-align: middle;
		width: 15%;
	}
	.activityRowTitle {
		background: #f9f9f9;
		font-weight: bold;
	}
</style>
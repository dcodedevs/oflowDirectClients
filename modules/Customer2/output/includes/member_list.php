<?php
$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $customer_basisconfig = $o_query->row_array();
}
$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}
require_once("fnc_rewritebasisconfig.php");
rewriteCustomerBasisconfig();
?>
<div class="p_headerLine">
    <div class="backToCustomer btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_BackToCustomer_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
</div>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <div class="p_tableFilter">
                    <div class="p_tableFilter_left">
                        <span class="send_link_to_many"><?php echo $formText_SendLinkToMany_output;?></span>
						<br>
						<span class="output_edit_config"><?php echo $formText_EditTextInEmail_Output;?></span>
                    </div>
                    <div class="p_tableFilter_right">
                        <form class="searchFilterForm" id="searchFilterForm">
                            <input type="text" class="searchFilter" value="<?php echo $search_filter;?>" autocomplete="off">
                            <button id="p_tableFilterSearchBtn"><?php echo $formText_Search_output; ?></button>
                        </form>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="resultTableWrapper">
                    <table class="table table-fixed">
                        <tr>
                            <th><?php echo $formText_Member_output;?></th>
                            <th width="200"><?php echo $formText_MainContact_output;?></th>
                            <th width="150"><?php echo $formText_LinkLastOpen_output;?></th>
                            <th width="150"><?php echo $formText_LinkLastSent_output;?></th>
                            <th width="80"></th>
                        </tr>
                        <?php
                        $sql_where = "";
                        if($search_filter != ""){
                            $search_filter_reg = str_replace(" ", "|",$search_filter);
                            $sql_where = " AND ((customer.name REGEXP '".$search_filter."' AND (customer.customerType is null OR customer.customerType = 0)) OR ((customer.name REGEXP '".$search_filter_reg."' OR customer.middlename REGEXP '".$search_filter_reg."' OR customer.lastname REGEXP '".$search_filter_reg."') AND customer.customerType = 1))";
                        }
                        $left_join_sql = "";
                        if($v_customer_accountconfig['member_profile_addextra_member_by_selfdefined'] > 0) {
                            $left_join_sql = " LEFT OUTER JOIN customer_selfdefined_values csv ON csv.selfdefined_fields_id = '".$v_customer_accountconfig['member_profile_addextra_member_by_selfdefined']."' AND csv.customer_id = customer.id AND csv.active = 1";
                        }

                        $getComp = $o_main->db->query("SELECT customer.* FROM customer
                        LEFT JOIN
                        	(SELECT subscriptionmulti.startDate, subscriptionmulti.id, subscriptionmulti.customerId, MIN(subscriptionmulti.stoppedDate) AS stoppedDate, subscriptionmulti.extraCheckbox FROM subscriptionmulti
                        		WHERE subscriptionmulti.customerId <> 0 GROUP by subscriptionmulti.customerId) subscriptionmulti
                        	ON subscriptionmulti.customerId = customer.id
                            ".$left_join_sql."
                        WHERE customer.content_status <> '2'
                        AND (subscriptionmulti.extraCheckbox = 0 OR subscriptionmulti.extraCheckbox is null)
                        AND (
                            (
                                (subscriptionmulti.startDate is not null AND subscriptionmulti.startDate <> '0000-00-00')
                                AND (subscriptionmulti.stoppedDate is null OR subscriptionmulti.stoppedDate = '0000-00-00' OR subscriptionmulti.stoppedDate > NOW())
                            ) OR (csv.id is not null)
                        )
                        ".$sql_where."
                        ORDER BY name");
                        $members = $getComp ? $getComp->result_array() : array();

                        foreach($members as $member) {
                            $getComp = $o_main->db->query("SELECT * FROM contactperson WHERE customerId = ?", array($member['id']));
                            $contacts = $getComp ? $getComp->result_array() : array();
                            $main_contact = array();
                            $contactNumber = 0;
                            foreach($contacts as $contact) {
                                if($contact['mainContact']) {
                                    $main_contact = $contact;
                                } else {
                                    $contactNumber++;
                                }
                            }

                            $linkLastOpen = "";
                            $getComp = $o_main->db->query("SELECT * FROM customer_member_link_tracking WHERE code = ? ORDER BY created DESC", array($member['member_profile_link_code']));
                            $tracking = $getComp ? $getComp->row_array() : array();
                            if($tracking){
                                $linkLastOpen = $tracking['created'];
                            }
                            $getComp = $o_main->db->query("SELECT sys_emailsend.*, sys_emailsendto.* FROM sys_emailsend
                                LEFT OUTER JOIN sys_emailsendto ON sys_emailsendto.emailsend_id = sys_emailsend.id
                                WHERE sys_emailsend.content_table = 'customer_member_link' AND sys_emailsend.content_id = ?
                                ORDER BY sys_emailsendto.perform_time DESC", array($member['id']));
                            $emails = $getComp ? $getComp->result_array() : array();
                            $linkLastSent = "";
                            foreach($emails as $email) {
                                if($linkLastSent == ""){
                                    $linkLastSent = $email['perform_time'];
                                }
                            }
                        ?>
                            <tr>
                                <td><?php echo $member['name']." ".$member['middlename']." ".$member['lastname'];?></td>
                                <td>
                                    <div class="editMainContact" data-customer-id="<?php echo $member['id'];?>">
                                        <?php
                                        if($main_contact){
                                            echo $main_contact['name']." ".$main_contact['middle_name']." ".$main_contact['last_name'];
                                        } else {
                                            echo $formText_NoMainContact_output;
                                        }

                                        ?> (+<?php echo $contactNumber;?>)
                                    </div>
                                </td>
                                <td><?php if($linkLastOpen != "") echo date("d.m.Y", strtotime($linkLastOpen));?></td>
                                <td><?php if($linkLastSent != ""){
                                    echo date("d.m.Y", strtotime($linkLastSent));
                                    ?>
                                    <span class="glyphicon glyphicon-info-sign hoverEye"><div class="hoverInfo">
                        				<div class="container-fluid">
                        				<div class="row">
                        					<div class="col-xs-5"><strong><?php echo $formText_Date_Output;?></strong></div>
                        					<div class="col-xs-5"><strong><?php echo $formText_SentTo_Output;?></strong></div>
                        				</div>
                        				<?php
                    					foreach($emails as $v_log)
                    					{
                    						?>
                    						<div class="row">
                    							<div class="col-xs-5"><?php echo date("d.m.Y H:i", strtotime($v_log['perform_time']));?></div>
                    							<div class="col-xs-5"><?php echo $v_log['receiver_email'];?></div>
                    						</div>
                    						<?php
                    					}
                        				?>
                        			</div>
                        			</div></span>
                                    <?php
                                }?></td>
                                <td><span class="send_link" data-customer-id="<?php echo $member['id'];?>"><?php echo $formText_SendLink_output;?></span></td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .table-fixed {
        table-layout: fixed;
    }
    .editMainContact {
        cursor: pointer;
    }
    .resultTableWrapper {
        background: #fff;
    }
    .resultTableWrapper th {
        background: #f8f8f8;
    }
    .send_link {
        cursor: pointer;
        color: #46b2e2;
    }
    .send_link_to_many, .output_edit_config {
        cursor: pointer;
        color: #46b2e2;
    }

    .hoverEye {
    	position: relative;
    	color: #0284C9;
    	float: right;
    	margin-top: 2px;
    }
    .hoverEye .hoverInfo {
    	font-family: 'PT Sans', sans-serif;
    	width:450px;
    	display: none;
    	color: #000;
    	position: absolute;
    	right: 0%;
    	top: 100%;
    	padding: 5px 10px;
    	background: #fff;
    	border: 1px solid #ccc;
    	z-index: 1;
    }
    .hoverEye:hover .hoverInfo {
    	display: block;
    }
</style>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed: 300,
	fadeSpeed: 0,
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
            	loadView("member_list");
            }
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};
    $(function(){
        $(".editMainContact").off("click").on("click", function(e){
            var data = { customerId: $(this).data("customer-id")};
            ajaxCall('editMemberMainContact', data, function(obj) {
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(obj.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
            });
        })
        $(".backToCustomer").off("click").on("click", function(e){
            e.preventDefault();
            var data = {
            };
            loadView("list", data);
        })
        $(".send_link").off("click").on("click", function(){
            var data = { customerId: $(this).data("customer-id"), send_link: 1, output_form_submit: 1};
            ajaxCall('sendMemberLink', data, function(obj) {
                if(obj.error != undefined) {
                    $('#popupeditboxcontent').html('');
                    $('#popupeditboxcontent').html(obj.error);
                    out_popup = $('#popupeditbox').bPopup(out_popup_options);
                    $("#popupeditbox:not(.opened)").remove();
                } else {
                    loadView("member_list");
                }
            });
        })
        $(".send_link_to_many").off("click").on("click", function(){
            var data = { };
            ajaxCall('sendMemberLink', data, function(obj) {
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(obj.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
            });
        });
		$(".output_edit_config").off("click").on("click", function(){
            var data = { };
            ajaxCall('edit_member_link_settings', data, function(obj) {
                $('#popupeditboxcontent').html('').html(obj.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
            });
        });
        $("#p_tableFilterSearchBtn").off("click").on("click", function(e){
            e.preventDefault();
            var data = { search_filter: $(".searchFilter").val() };
            loadView("member_list", data);
        })
    })
</script>

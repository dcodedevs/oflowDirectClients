<?php
require_once __DIR__ . '/functions.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

?>
<?php if (!$rowOnly) { ?>
	<?php if(!$_POST['updateOnlyList']){?>
		<?php include(__DIR__."/list_filter.php"); ?>
	<?php } ?>
	<?php if(!$_POST['updateOnlyList']){?>
	<div class="resultTableWrapper">
	<?php } ?>
	<div class="gtable" id="gtable_search">
	    <div class="gtable_row">
			<?php if($filter != "not_sent"){ ?>
		        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Date_output;?></div>
		        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Total_output;?></div>
		        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Delivered_output;?></div>
		        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Failed_output;?></div>
		        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Queued_output;?></div>
		        <div class="gtable_cell gtable_cell_head"></div>
			<?php } else { ?>
				<div class="gtable_cell gtable_cell_head"><input type="checkbox" class="selectAll" autocomplete="off"/></div>
				<div class="gtable_cell gtable_cell_head"><?php echo $formText_Id_Output;?></div>
				<div class="gtable_cell gtable_cell_head"><?php echo $formText_Debitor_Output;?></div>
				<div class="gtable_cell gtable_cell_head"><?php echo $formText_Creditor_Output;?></div>
				<div class="gtable_cell gtable_cell_head"><?php echo $formText_Email_Output;?></div>
		        <div class="gtable_cell gtable_cell_head"></div>
			<?php } ?>
	    </div>
<?php } ?>

<?php

if($filter == "not_sent"){

	$s_sql = "SELECT c.*, a.id AS action_id, cust.invoiceEmail FROM collecting_cases_handling_action a JOIN collecting_cases_handling h ON h.id = a.handling_id
	JOIN collecting_cases c ON c.id = h.collecting_case_id
	LEFT OUTER JOIN customer cust ON cust.id = c.debitor_id
	WHERE (a.performed_date IS NULL OR a.performed_date = '0000-00-00')
	AND (a.action_type = 2 OR (a.action_type = 4 AND (cust.invoiceEmail <> '' AND cust.invoiceEmail is not null))) AND a.collecting_cases_process_steps_action_id is not null
	ORDER BY c.id";
	$o_query = $o_main->db->query($s_sql);
	$notProcessedEmails = $o_query ? $o_query->result_array() : array();

	foreach($notProcessedEmails as $notProcessedEmail) {
		$sql = "SELECT p.*, c2.name as debitorName, c.name as creditorName FROM collecting_cases p
		LEFT JOIN customer c2 ON c2.id = p.debitor_id
		LEFT JOIN customer c ON c.id = p.creditor_id
		WHERE p.id = ? ORDER BY p.sortnr ASC";
		$o_query = $o_main->db->query($sql, array($notProcessedEmail['id']));
		$case = $o_query ? $o_query->row_array() : array();

	    $s_sql = "SELECT * FROM collecting_cases_handling_action WHERE id = ?";
	    $o_query = $o_main->db->query($s_sql, array($notProcessedEmail['action_id']));
	    $handling_action = ($o_query ? $o_query->row_array() : array());

		$s_sql = "SELECT * FROM collecting_cases_process_steps_action WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($handling_action['collecting_cases_process_steps_action_id']));
		$collecting_cases_process_steps_action = ($o_query ? $o_query->row_array() : array());
		if($collecting_cases_process_steps_action){
			$s_sql = "SELECT * FROM collecting_cases_emailtext WHERE collecting_cases_emailtext.id = ?";
			$o_query = $o_main->db->query($s_sql, array($collecting_cases_process_steps_action['collecting_cases_emailtext_id']));
			$emailText = ($o_query ? $o_query->row_array() : array());
			if($emailText){
				$s_email_subject = $emailText['subject'];
				$s_email_body = nl2br($emailText['text']);
			}
		}
		?>
		<div class="gtable_row">
			 <div class="gtable_cell"><input type="checkbox" class="checkboxesGenerate" name="casesToGenerate" autocomplete="off" value="<?php echo $case['id'];?>" /></div>
			 <div class="gtable_cell"><?php echo $notProcessedEmail['id'];?></div>
			 <div class="gtable_cell"><?php echo $case['debitorName'];?></div>
			 <div class="gtable_cell"><?php echo $case['creditorName'];?></div>
			 <div class="gtable_cell"><?php echo $notProcessedEmail['invoiceEmail'];?></div>
			 <div class="gtable_cell">
				 <?php
				 if($emailText){
					 echo (strlen($s_email_subject)>17?substr(substr($s_email_subject,0,17),0,strrpos(substr($s_email_subject,0,17)," "))."...":$s_email_subject);?>
					 <a href="#" class="output-show-email-message" data-subject="<?php echo $s_email_subject;?>" data-message="<?php echo $s_email_body;?>"><span class="glyphicon glyphicon-info-sign"></span></a>
				 <?php } else {
					 echo $formText_NoEmailTextSet_output;
				 }
				 ?>
			 </div>
		</div>
		<?php
	}
	?>
	<script type="text/javascript">
	 	$(".output-show-email-message").unbind("click").on('click', function(e){
		 	e.preventDefault();
		 	$('#popupeditboxcontent').html('<h3><?php echo $formText_EmailMessage_Output;?></h3><div><b><?php echo $formText_Subject_Output;?>:</b></div><div>' + $(this).data('subject') + '</div><div style="margin-top:10px;"><b><?php echo $formText_Message_Output;?>:</b></div><div>' + $(this).data('message') + '</div>');
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
	 	});
	</script>
	<?php
} else if($filter == "sent_today"){
	$s_sql = "SELECT es.batch_id, es.send_on, COUNT(est.id) AS cnt, es.id FROM sys_emailsend es LEFT OUTER JOIN sys_emailsendto est ON est.emailsend_id = es.id
	WHERE es.content_table = 'collecting_cases' AND DATE(es.send_on) = CURDATE() GROUP BY es.batch_id ORDER BY es.batch_id";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_row)
	{
		$o_query = $o_main->db->query("select est.id from sys_emailsendto est JOIN sys_emailsend es ON es.id = est.emailsend_id  where es.batch_id = ? and est.status = 1", array($v_row['batch_id']));
		$l_success = $o_query ? $o_query->num_rows() : 0;
		$o_query = $o_main->db->query("select est.id from sys_emailsendto est JOIN sys_emailsend es ON es.id = est.emailsend_id  where es.batch_id = ? and est.status = 2", array($v_row['batch_id']));
		$l_failed = $o_query ? $o_query->num_rows() : 0;

		$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['batch_id'];
		?>
		<div class="gtable_row output-click-helper" data-href="<?php echo $s_edit_link;?>">
		<?php
		// Show default columns
		 ?>
			<div class="gtable_cell"><?php echo date("d.m.Y", strtotime($v_row['send_on']));?></div>
			<div class="gtable_cell"><?php echo $v_row['cnt'];?></div>
			<div class="gtable_cell"><?php echo $l_success;?></div>
			<div class="gtable_cell"><?php echo $l_failed;?></div>
			<div class="gtable_cell"><?php echo $v_row['cnt'] - $l_success - $l_failed;?></div>
			<div class="gtable_cell"><a class="optimize" href="<?php echo $s_edit_link;?>"><?php echo $formText_ShowReport_Output;?></a></div>
		</div><?php
	}
} else if($filter == "sent_earlier"){
	$s_sql = "SELECT es.batch_id, es.send_on, COUNT(est.id) AS cnt, es.id FROM sys_emailsend es LEFT OUTER JOIN sys_emailsendto est ON est.emailsend_id = es.id
	WHERE es.content_table = 'collecting_cases' AND DATE(es.send_on) <> CURDATE() GROUP BY es.batch_id ORDER BY es.batch_id";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_row)
	{
		$o_query = $o_main->db->query("select est.id from sys_emailsendto est JOIN sys_emailsend es ON es.id = est.emailsend_id  where es.batch_id = ? and est.status = 1", array($v_row['batch_id']));
		$l_success = $o_query ? $o_query->num_rows() : 0;
		$o_query = $o_main->db->query("select est.id from sys_emailsendto est JOIN sys_emailsend es ON es.id = est.emailsend_id  where es.batch_id = ? and est.status = 2", array($v_row['batch_id']));
		$l_failed = $o_query ? $o_query->num_rows() : 0;

		$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['batch_id'];
		?>
		<div class="gtable_row output-click-helper" data-href="<?php echo $s_edit_link;?>">
		<?php
		// Show default columns
		 ?>
			<div class="gtable_cell"><?php echo date("d.m.Y", strtotime($v_row['send_on']));?></div>
			<div class="gtable_cell"><?php echo $v_row['cnt'];?></div>
			<div class="gtable_cell"><?php echo $l_success;?></div>
			<div class="gtable_cell"><?php echo $l_failed;?></div>
			<div class="gtable_cell"><?php echo $v_row['cnt'] - $l_success - $l_failed;?></div>
			<div class="gtable_cell"><a class="optimize" href="<?php echo $s_edit_link;?>"><?php echo $formText_ShowReport_Output;?></a></div>
		</div><?php
	}
}
?>

<?php if (!$rowOnly) { ?>
	</div>
	<?php if($list_filter == "not_sent") { ?>
        <div class="launchEmailSending"><?php echo $formText_LaunchEmailSendingScript_output; ?></div>
        <script type="text/javascript">
            $(".selectAll").on("click", function(){
                if($(this).is(":checked")){
                    $(".checkboxesGenerate").prop("checked", true);
                } else {
                    $(".checkboxesGenerate").prop("checked", false);
                }
            });
			$(".launchEmailSending").on("click", function(e){
		    	e.preventDefault();
		        bootbox.confirm('<?php echo $formText_ProcessActions_output; ?>', function(result) {
					if (result) {
						var casesToGenerate = [];
                        var data = {
                            casesToGenerate: casesToGenerate
                        }
                        $(".checkboxesGenerate").each(function(index, el){
                            if($(el).is(":checked")){
                                casesToGenerate.push($(el).val());
                            }
                        })
		                ajaxCall('send_emails', data, function(json) {
		                    var data = {
		                        list_filter: '<?php echo $list_filter;?>',
		                        customer_filter:$(".customerId").val(),
		                        search_filter: $('.searchFilter').val()
		                    };
		                    loadView('list', data);
		            	});
					}
				});
		    })
        </script>
    <?php } ?>
	<?php if(!$_POST['updateOnlyList']){ ?>
	</div>
	<?php } ?>
<script type="text/javascript">
	var out_popup;
	var out_popup_options={
    	follow: [true, true],
    	followSpeed: 0,
    	fadeSpeed: 0,
		modalClose: false,
		escClose: false,
		closeClass:'b-close',
		onOpen: function(){
			$(this).addClass('opened');
			//$(this).find('.b-close').on('click', function(){out_popup.close();});
		},
		onClose: function(){
			$(this).removeClass('opened');
		}
	};
	$(function() {
		$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
			if(e.target.nodeName == 'DIV') fw_load_ajax($(this).data('href'),'',true);
		});
	});

    $(".page-link").on('click', function(e) {
	    page = $(this).data("page");
	    e.preventDefault();
	    var data = {
	        building_filter:$(".buildingFilter").val(),
	        customergroup_filter: $(".customerGroupFilter").val(),
	        list_filter: '<?php echo $list_filter; ?>',
	        search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
	        page: page
	    };
	    ajaxCall('list', data, function(json) {
	        $('.p_pageContent').html(json.html);
	        if(json.html.replace(" ", "") == ""){
	            $(".showMoreCustomersBtn").hide();
	        }

	    });
    });
    $('.showMoreCustomersBtn').on('click', function(e) {
        page = parseInt(page)+1;
        e.preventDefault();
        var data = {
            building_filter: $(".buildingFilter").val(),
            customergroup_filter: $(".customerGroupFilter").val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
            page: page,
            rowOnly: 1
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent .gtable').append(json.html);
            $(".showMoreCustomers .showing").html($(".gtable .gtable_row.output-click-helper").length);
            if(json.html.replace(" ", "") == ""){
                $(".showMoreCustomersBtn").hide();
            }
        });
    });
</script>
<?php } ?>

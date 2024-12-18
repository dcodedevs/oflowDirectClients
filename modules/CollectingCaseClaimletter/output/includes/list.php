<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require_once("Exception.php");
require_once("PHPMailer.php");
require_once("SMTP.php");

if($variables->loggID == "byamba@dcode.no"){
	// $s_sql = "SELECT ccl.*, c.invoiceEmail FROM collecting_cases_claim_letter ccl
	// LEFT OUTER JOIN collecting_cases cs ON cs.id = ccl.case_id
	// LEFT OUTER JOIN customer c ON c.id = cs.debitor_id
	// WHERE ccl.sending_status = -1";
	// $o_query = $o_main->db->query($s_sql);
	// $claim_letters = $o_query ? $o_query->result_array() : array();
	// foreach($claim_letters as $claim_letter){
	// 	if(preg_replace('/\xc2\xa0/', '', trim($claim_letter['invoiceEmail']))== "") {
	// 		$s_sql = "UPDATE collecting_cases_claim_letter SET sending_action = 1,sending_status=0  WHERE id = ?";
	// 		$o_query = $o_main->db->query($s_sql, $claim_letter['id']);
	// 	}
	// }
}
//
// 	$s_sql = "SELECT * FROM collecting_system_settings";
// 	$o_query = $o_main->db->query($s_sql);
// 	$collecting_system_settings = $o_query ? $o_query->row_array() : array();
//
// 	$s_email_subject = $formText_ReminderFrom_output."  Skagerak Trål og Notbøteri AS";
//
// 	$s_email_body = $formText_Hi_pdf."<br/><br/>".$formText_SeeAttachedPdfFileWithSetupOfOurClaimAndDueDate_pdf."<br/><br/>".$formText_BestRegards_pdf."<br/>".$creditor['name']." ".$creditor['middlename']." ".$creditor['lastname']."<br/>";
// 	if($creditor['phone'] != "") {
// 		$s_email_body .= $formText_Phone_pdf." ".$creditor['phone'];
// 	}
// 	$s_email_body.="<br/><br/>".$formText_ThisEmailSentFromReminderSystemOflow_output." (<a href='".$formText_realWebAddressAtBottomOfEmail_output."'>".$formText_realWebAddressAtBottomOfEmail_output."</a>)";
//
// 	$s_sql = "select * from sys_emailserverconfig order by default_server desc";
// 	$o_query = $o_main->db->query($s_sql);
// 	$v_email_server_config = $o_query ? $o_query->row_array() : array();
//
// 	$mail = new PHPMailer;
// 	$mail->CharSet	= 'UTF-8';
// 	$mail->IsSMTP(true);
// 	$mail->isHTML(true);
// 	if($v_email_server_config['host'] != "")
// 	{
// 		$mail->Host	= $v_email_server_config['host'];
// 		if($v_email_server_config['port'] != "") $mail->Port = $v_email_server_config['port'];
//
// 		if($v_email_server_config['username'] != "" and $v_email_server_config['password'] != "")
// 		{
// 			$mail->SMTPAuth	= true;
// 			$mail->Username	= $v_email_server_config['username'];
// 			$mail->Password	= $v_email_server_config['password'];
//
// 		}
// 	} else {
// 		$mail->Host = "mail.dcode.no";
// 	}
// 	$mail->From		= $collecting_system_settings['reminder_sender_email'];
// 	$mail->FromName	= "test";
// 	$mail->Subject	= $s_email_subject;
// 	$mail->Body		= $s_email_body;
// 	$mail->AddAddress("byamba@dcode.no");
// 	// $mail->AddBCC("david@dcode.no");
//
// 	if($mail->Send())
// 	{
//
// 	}
// }
$page = 1;
require_once __DIR__ . '/list_btn.php';

$sql = "SELECT * FROM accountinfo";
$result = $o_main->db->query($sql);
$v_accountinfo = $result ? $result->row_array(): array();

?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <?php require __DIR__ . '/ajax.list.php'; ?>
			</div>
		</div>
	</div>
</div>

<?php $list_filter = $_GET['list_filter'] ? $_GET['list_filter'] : 'all'; ?>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [false, false],
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
				var data = {
	            };
            	loadView("list", data);
            }
        }
		$(this).removeClass('opened');
	}
};


$(document).ready(function() {

    var page = '<?php echo $page?>';
    // On customer row click
	$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
		if(e.target.nodeName == 'DIV'){
		 	fw_load_ajax($(this).data('href'),'',true);
			if($("body.alternative").length == 0) {
			 	if($(this).parents(".tinyScrollbar.col1")){
				 	var $scrollbar6 = $('.tinyScrollbar.col1');
				    $scrollbar6.tinyscrollbar();

				    var scrollbar6 = $scrollbar6.data("plugin_tinyscrollbar");
			        scrollbar6.update(0);
			    }
			}
		}
	});
});
</script>

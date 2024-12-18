<?php
$l_invoice_id = $_POST['invoiceId'] ? $_POST['invoiceId'] : 0;

if($l_invoice_id > 0)
{
	$o_query = $o_main->db->query("SELECT * FROM invoice WHERE id = '".$o_main->db->escape_str($l_invoice_id)."'");
	$v_invoice = $o_query ? $o_query->row_array() : array();
}
$s_ehf_file_path = __DIR__."/../../../../".$v_invoice['ehf_invoice_file'];

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
        if($v_invoice['id'] > 0)
		{
			$s_error_msg_extra = '';
			$b_handle = TRUE;
			if(strpos($v_invoice['ehf_reference'], '[REFERENCE]') !== FALSE)
			{
				$b_handle = FALSE;
				$fw_error_msg[] = $formText_InvoiceIsAlreadySentToEhfService_Output;
			}
			if(!is_file($s_ehf_file_path))
			{
				$b_handle = FALSE;
				$fw_error_msg[] = $formText_EhfInvoiceIsNotAvailable_Output;
			}
			
            $o_query = $o_main->db->query("SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($v_invoice['customerId'])."'");
			$v_customer = $o_query ? $o_query->row_array() : array();
			$o_query = $o_main->db->query("SELECT * FROM ownercompany WHERE id = '".$o_main->db->escape_str($v_invoice['ownercompany_id'])."'");
			$v_settings = $o_query ? $o_query->row_array() : array();
			
			$b_is_sent = FALSE;
			$s_ehf_reference = '';
            $v_ehf_data = array();
			$v_ehf_data['supplier_org_nr'] = preg_replace('#[^0-9]+#', '', $v_settings['companyorgnr']);
			$v_ehf_data['customer_org_nr'] = preg_replace('#[^0-9]+#', '', $v_customer['publicRegisterId']);
            
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			//curl_setopt($ch, CURLOPT_URL, 'https://hotell.difi.no/api/jsonp/difi/elma/capabilities?ehf_invoice=true&query='.$v_ehf_data['customer_org_nr'].'*&callback=callback123');
			// BIS 3.0
			//curl_setopt($ch, CURLOPT_URL, 'https://hotell.difi.no/api/jsonp/difi/elma/participants?PEPPOLBIS_3_0_BILLING_01_UBL=Ja&query='.$v_ehf_data['customer_org_nr'].'*&callback=callback123');
			// BIS 3.0 identification
			$s_url = 'https://hotell.difi.no/api/jsonp/difi/elma/participants?identifier='.$v_ehf_data['customer_org_nr'].'&callback=callback123';
			curl_setopt($ch, CURLOPT_URL, $s_url);
			$s_response = curl_exec($ch);
			$b_found_receiver = FALSE;
			if(FALSE !== $s_response)
			{
				$v_response = json_decode(substr($s_response, 12, -2), TRUE);
				if(isset($v_response['entries']))
				foreach($v_response['entries'] as $v_entry)
				{
					$b_is_icd = $b_is_bis3 = FALSE;
					foreach($v_entry as $s_key => $s_value)
					{
						if($s_key == 'Icd' && $s_value == '0192') $b_is_icd = TRUE;
						if($s_key == 'PEPPOLBIS_3_0_BILLING_01_UBL' && $s_value == 'Ja') $b_is_bis3 = TRUE;
					}
					if($b_is_icd && $b_is_bis3) $b_found_receiver = TRUE;
				}
			}
			if(!$b_found_receiver) mail("agris@dcode.no", "ELMA FAIL", wordwrap($s_url."\n".$s_response,70));
			if($b_handle && $b_found_receiver)
			{
				$s_ehf_reference .= '[ELMA]:OK';
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://ap.getynet.com/validator/');
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_VERBOSE, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
				curl_setopt($ch, CURLOPT_COOKIEJAR, '/var/www/tmp/cookie-'.basename($s_ehf_file_path));
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
				$s_response = curl_exec($ch);
				$dom = new DomDocument();
				$dom->loadHTML($s_response);
				$tokens = $dom->getElementsByTagName("input");
				for ($i = 0; $i < $tokens->length; $i++)
				{
					$meta = $tokens->item($i);
					if($meta->getAttribute('name') == '_csrf')
					$s_token = $meta->getAttribute('value');
				}
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_VERBOSE, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
				curl_setopt($ch, CURLOPT_COOKIEFILE, '/var/www/tmp/cookie-'.basename($s_ehf_file_path));
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
				curl_setopt($ch, CURLOPT_URL, 'https://ap.getynet.com/validator/?_csrf='.$s_token);
				$v_post = array(
					'file' => new CurlFile($s_ehf_file_path, mime_content_type($s_ehf_file_path), basename($s_ehf_file_path)),
					'_csrf' => $s_token,
				);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $v_post);
				$s_response = curl_exec($ch);
				
				$s_status = '';
				include_once __DIR__ . '/../../../BatchInvoicing/procedure_create_invoices/scripts/CREATE_INVOICE/html5/Parser.php';
				$dom = HTML5_Parser::parse($s_response);
				$divs = $dom->getElementsByTagName('div');
				$b_check = FALSE;
				foreach($divs as $div)
				{
					if($b_check && strpos($div->getAttribute('class'), 'status status-') !== FALSE)
					{
						$s_status = strtolower($div->getAttribute('class'));
						break;
					}
					if($div->getAttribute('class') == 'report')
					{
						$b_check = TRUE;
					}
				}
				$b_valid_file = (strpos($s_status, 'status-warning') !== FALSE || strpos($s_status, 'status-ok') !== FALSE);
				
				if($b_valid_file)
				{
					$s_ehf_reference .= '[FILE_VALID]:OK';
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_VERBOSE, 0);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
					curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
					curl_setopt($ch, CURLOPT_POST, TRUE);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
					curl_setopt($ch, CURLOPT_URL, 'https://ap_api.getynet.com/index.php');
					$v_post = array(
						'file' => new CurlFile($s_ehf_file_path, mime_content_type($s_ehf_file_path), basename($s_ehf_file_path)),
						'receiver' => '0192:'.$v_ehf_data['customer_org_nr'],
						'sender' => '0192:'.$v_ehf_data['supplier_org_nr'],
						'token' => 'RySBvCv3i9u6tP4mEd177X2gspGP6Rw0E512874043zDfUyHDsNF70gZvJ2R9s6idmGxk29amaRRR1R4Hbvqy93LJzPCz9oy',
						'password' => '_9^JAj|7_atz~-Y.BndXBguO9_jr0^z~~09m-*:4SXlj|!KZ6Xd.hnJe3WM75H9Vy=;ZIcrab-84WLKN+4Kdz~+xM5U%ePIY'
					);
					
					curl_setopt($ch, CURLOPT_POSTFIELDS, $v_post);
					$s_response = curl_exec($ch);
					
					$v_response = json_decode($s_response, TRUE);
					if(isset($v_response['status']) && $v_response['status'] == 1)
					{
						$b_is_sent = TRUE;
						$s_ehf_reference = '[REFERENCE]:'.$v_response['reference'].'[AP_RESPONSE]:'.$s_response;
					} else {
						$s_ehf_reference .= '[AP_ERROR]:'.$s_response;
					}
				} else {
					$s_ehf_reference .= '[FILE_VALID]:FAIL '.' ( '.curl_getinfo($ch, CURLINFO_EFFECTIVE_URL).' )';
					$s_error_msg_extra .= ' (<a href="'.curl_getinfo($ch, CURLINFO_EFFECTIVE_URL).'" target="_blank">'.$formText_MoreInfo_Output.'</a>)';
				}
			} else {
				if($b_handle)
				{
					$s_ehf_reference .= '[ELMA]:FAIL';
				}
			}
			if($s_ehf_reference!='') $o_main->db->query("UPDATE invoice SET ehf_reference = '".$o_main->db->escape_str($s_ehf_reference)."' WHERE id = '".$o_main->db->escape_str($v_invoice['id'])."'");
			
			if($b_is_sent)
			{
            	$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&company_filter=".$v_settings['id'];
			} else {
				$fw_error_msg[] = $formText_ErrorOccurredHandlingRequest_Output.$s_error_msg_extra;
			}
			
			$o_main->db->query("INSERT INTO invoice_send_log SET created = NOW(), invoice_id = '".$o_main->db->escape_str($v_invoice['id'])."', send_type = 3, send_status = '".$o_main->db->escape_str($b_is_sent ? 1 : 2)."'");
        }
	}
}

?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=send_ehf";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="invoiceId" value="<?php echo $l_invoice_id;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list"?>">
		<h4><?php echo $formText_SendInvoice_Output;?></h4>
		<div class="inner">
			<?php
			$b_show_send_btn = FALSE;
			if(strpos($v_invoice['ehf_reference'], '[REFERENCE]') !== FALSE)
			{
				echo $formText_InvoiceIsAlreadySentToEhfService_Output;
			} else {
				if(is_file($s_ehf_file_path))
				{
					$b_show_send_btn = TRUE;
					echo $formText_SendInvoiceToEhfService_Output;
				} else {
					echo $formText_EhfInvoiceIsNotAvailable_Output;
				}
			}
			?>
		</div>

		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<?php if($b_show_send_btn) { ?><input type="submit" name="sbmbtn" value="<?php echo $formText_Send_Output; ?>"><?php } ?>
		</div>
	</form>
</div>
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
                success: function (data){
					if(data.error !== undefined)
					{
						/*$.each(data.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							fw_info_message_add(_type[0], value);
						});
						fw_info_message_show();*/
						var message = '';
						$.each(data.error, function(index, value){
							message = message + '<div>' + value + '</div>';
						});
						$("#popup-validate-message").html(message, true).show();
					} else {
						out_popup.addClass("close-reload");
						out_popup.close();
					}
					fw_loading_end();
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
	
    function h(e) {
        $(e).css({'height':'auto','overflow-y':'hidden'}).height(e.scrollHeight);
    }
    $('.autoheight').each(function () {
        h(this);
    }).on('input', function () {
        h(this);
    });
});

</script>
<style>

.selectDivModified {
    display:block;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
}
label.error { display: none !important; }
.popupform .popupforminput.error { border-color:#c11 !important;}
#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
/* css for timepicker */
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
.clear {
	clear:both;
}
.inner {
	padding:10px;
}
.pplineV {
	position:absolute;
	top:0;bottom:0;left:70%;
	border-left:1px solid #e8e8e8;
}
.popupform input.popupforminput, .popupform textarea.popupforminput, .popupform select.popupforminput, .col-md-8z input {
	width:100%;
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
	color:#3c3c3f;
	background-color:transparent;
	-webkit-box-sizing: border-box;
	   -moz-box-sizing: border-box;
		 -o-box-sizing: border-box;
			box-sizing: border-box;
	font-weight:400;
	border: 1px solid #cccccc;
}
.popupformname {
	font-size:12px;
	font-weight:bold;
	padding:5px 0px;
}
.popupforminput.botspace {
	margin-bottom:10px;
}
textarea {
	min-height:50px;
	max-width:100%;
	min-width:100%;
	width:100%;
}
.popupformname {
	font-weight: 700;
	font-size: 13px;
}
.popupformbtn {
	text-align:right;
	margin:10px;
}
.popupformbtn input {
	border-radius:4px;
	border:1px solid #0393ff;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
	margin-left:10px;
}
.error {
	border: 1px solid #c11;
}
.popupform .lineTitle {
	font-weight:700;
}
.popupform .line .lineTitle {
	width:30%;
	float:left;
	font-weight:700;
	padding:5px 0;
}

.popupform .line .lineTitleWithSeperator {
    width:100%;
    margin: 20px 0;
    padding:0 0 10px;
    border-bottom:1px solid #EEE;
}

.popupform .line .lineInput {
	width:70%;
	float:left;
}
.priceTotalLine .popupforminput {
    border: none !important;
}
.popupform input.popupforminput.checkbox {
    width: auto;
}
</style>

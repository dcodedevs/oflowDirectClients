<?php
$o_query = $o_main->db->query("SELECT * FROM invoice WHERE id = '".$o_main->db->escape_str($_POST['invoice_id'])."'");
$v_invoice = $o_query ? $o_query->row_array() : array();

if(0 == intval($v_invoice['id']))
{
	echo $formText_InvoiceHasNotBeenFound_Output;
	return;
}

$o_query = $o_main->db->query("SELECT id FROM invoice_send_log WHERE invoice_id = '".$o_main->db->escape_str($v_row->id)."' LIMIT 1");
$b_is_sending_log = $o_query && $o_query->num_rows > 0;
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<h4><?php echo $formText_SendingLogForInvoice_Output.': '.$v_invoice['external_invoice_nr'];?></h4>
	<div class="container-fluid">
		<div class="row">
			<div class="col-xs-4"><strong><?php echo $formText_InvoicingTime_Output;?></strong></div>
			<div class="col-xs-4"><strong><?php echo $formText_InvoicedBy_Output;?></strong></div>
			<div class="col-xs-4"><strong><?php echo $formText_Status_Output;?></strong></div>
		</div>
		<?php
		$v_types = array(
			1 => $formText_Paper_Output,
			2 => $formText_Email_Output,
			3 => $formText_Ehf_Output,
		);
		$v_status = array(
			1 => $formText_Success_Output,
			2 => $formText_Fail_Output,
		);
		$s_sql = "SELECT * FROM invoice_send_log WHERE invoice_id = '".$o_main->db->escape_str($v_invoice['id'])."' ORDER BY id DESC";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && $o_query->num_rows()>0)
		{
			foreach($o_query->result_array() as $v_row)
			{
				?>
				<div class="row">
					<div class="col-xs-4"><?php echo date("d.m.Y H:i", strtotime($v_row['created']));?></div>
					<div class="col-xs-4"><?php echo $v_types[$v_row['send_type']];?></div>
					<div class="col-xs-4"><?php echo $v_status[$v_row['send_status']];?></div>
				</div>
				<?php
			}
		} else {
			?><div class="row">
				<div class="col-xs-12"><?php echo $formText_NoRecords_Output;?></div>
			</div><?php
		}
		?>
	</div>
	<div class="popupformbtn">
		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
	</div>
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
                $("#popup-validate-message").html("<?php echo $formText_ErrorOccurredSavingContent_Output;?>", true);
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
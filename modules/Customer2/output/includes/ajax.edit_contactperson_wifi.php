<?php
$o_query = $o_main->db->get('customer_accountconfig');
$v_customer_accountconfig = $o_query ? $o_query->row_array() : array();

$contactpersonId = $_POST['contactpersonId'] ? ($_POST['contactpersonId']) : 0;
$customerId = $_POST['customerId'] ? ($_POST['customerId']) : 0;

// Get contactperson data
$o_query = $o_main->db->get_where('contactperson', array('id' => $contactpersonId));
$contactperson_data = $o_query ? $o_query->row_array() : array();

// On form submit
if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        // Split name. Use the "last name" as last name and all other ones as first
        $name_parts = explode(" ", $contactperson_data['name']);
		if($contactperson_data['lastname'] != '')
        {
            $first_name = $contactperson_data['name'];
             $last_name = $contactperson_data['lastname'];
        }
        else if (count($name_parts) <= 1) {
            $first_name = $last_name = $name_parts[0];
        } else {
            $last_name = array_pop($name_parts);
            $first_name = implode(" ", $name_parts);
        }

        // Email / username
        $email = $contactperson_data['email'];
        $phone = $contactperson_data['mobile'];

        // Get company data
        $o_query = $o_main->db->get_where('customer', array('id' => $contactperson_data['customerId']));
        $customer_data = $o_query ? $o_query->row_array() : array();
        $company_name = $customer_data['name'];

        // Access & password
        $network_group_access = $_POST['network_group_access'];
        $network_password = $_POST['network_password'];
        $network_status = $_POST['network_status'] ? 'true' : 'false';

        // Csv content
        $csv_content = "GivenName;SurName;Mail;SamAccount;Description;Office;Phone;Password;Enable\n";
        $csv_content .= "$first_name;$last_name;$email;$phone;$company_name;$network_group_access;$phone;$network_password;$network_status\n";

        // Log
        $o_main->db->insert('contactperson_wifi_access_log', array(
            'moduleID' => $moduleID,
            'created' => date('Y-m-d H:i:s'),
            'createdBy' => $variables->loggID,
            'contactpersonId' => $contactperson_data['id'],
            'csvContent' => $csv_content
        ));
        $log_id = $o_main->db->insert_id();

        // CSV file
        $csv_remote_file_name = 'getynet-wifi-sync-' .$log_id . '.csv';
        $csv_local_tmp_file = __DIR__ . '/../../../../uploads/getyent-wifi-sync-tmp.csv';
        $csv_fp = fopen($csv_local_tmp_file, 'w');
        fwrite($csv_fp, $csv_content);
        fclose($csv_fp);

        $csv_local_tmp_realpath_file = realpath($csv_local_tmp_file);

        // Put file on FTP
        $ftpAddress = $v_customer_accountconfig['contactperson_wifi_access_ftp_address'];
        $ftpPort = $v_customer_accountconfig['contactperson_wifi_access_ftp_port'];
        $ftpLogin = $v_customer_accountconfig['contactperson_wifi_access_ftp_username'];
        $ftpPassword = $v_customer_accountconfig['contactperson_wifi_access_ftp_password'];
        $ftpConn = ftp_connect($ftpAddress, $ftpPort);

        if (ftp_login($ftpConn, $ftpLogin, $ftpPassword)) {
            ftp_pasv($ftpConn, true);
            $ftp_put_result = ftp_put($ftpConn, $csv_remote_file_name, $csv_local_tmp_realpath_file, FTP_ASCII);

			if ($ftp_put_result) {
				$o_main->db->where('id', $contactperson_data['id']);
				$o_query = $o_main->db->update('contactperson', array(
					'network_password' => $_POST['network_password'],
					'network_group_access' => $_POST['network_group_access'],
					'network_status' => $_POST['network_status'] ? 1 : 0,
				));

			}
        }

		$return['ftp_put_result'] = $ftp_put_result;

        // Redirect
        $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId;
	}
}
?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_contactperson_wifi";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="contactpersonId" value="<?php echo $contactpersonId;?>">
		<input type="hidden" name="customerId" value="<?php echo $customerId;?>">
		<div class="inner">
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Username_Output; ?></div>
				<div class="lineInput">
					<?php echo $contactperson_data['mobile']; ?>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle">
                    <?php echo $formText_NetworkPassword_Output; ?>
                </div>
				<div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="network_password" value="<?php echo $contactperson_data['network_password']; ?>" autocomplete="off">
				</div>
				<div class="clear"></div>
			</div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_AccessGroup_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="network_group_access" value="<?php echo $contactperson_data['network_group_access']; ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Active_Output; ?></div>
                <div class="lineInput">
                    <input type="checkbox" class="popupforminput botspace" name="network_status" value="1" <?php echo $contactperson_data['network_status'] ? 'checked="checked"' : ''; ?>>
                </div>
                <div class="clear"></div>
            </div>
            <!-- <div class="line">
                <div class="lineTitle"><?php echo $formText_DownloadCsv_Output; ?></div>
                <div class="lineInput">
					<a href="<?php echo $_SERVER['PHP_SELF']."/../../modules/$module/output/includes/download_wifi_csv.php?contactpersonId=$contactpersonId";?>"><?php echo $formText_DownloadCsv_output; ?></a>

                </div>
                <div class="clear"></div>
            </div> -->
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
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
                success: function (data) {
					fw_loading_end();

					if (!data.ftp_put_result) {
						$("#popup-validate-message").html("<?php echo $formText_FtpUploadError_Output;?>", true);
		                $("#popup-validate-message").show();
		                // $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
					}
					else {
						if(data.redirect_url !== undefined)
						{
							out_popup.addClass("close-reload");
							out_popup.close();
						}
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

    $("#invoiceByPaper").unbind("click").bind("click", function(){
        $(".invoiceEmail").hide();
    })
    $("#invoiceByEmail").unbind("click").bind("click", function(){
        $(".invoiceEmail").show();
    })
    $("input[name='invoiceBy']:checked").click();
});

</script>
<style>
.popupform input.popupforminput.checkbox {
    width: auto;
}
.popupform .inlineInput input.popupforminput {
    display: inline-block;
    width: auto;
    vertical-align: middle;
    margin-right: 20px;
}
.popupform .inlineInput label {
    display: inline-block !important;
    vertical-align: middle;
}
.selectDivModified {
    display:block;
}
.popupform, .popupeditform {
    width:100%;
    margin:0 auto;
    border:1px solid #e8e8e8;
    position:relative;
}
.invoiceEmail {
    display: none;
}
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
</style>

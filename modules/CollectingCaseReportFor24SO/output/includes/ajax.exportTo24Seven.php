<?php
require_once __DIR__ . '/ApiCallDriver.php';
class Integration24Report {
	function __construct($config) {

		// Get API urls based on mode (demo/production)
		if ($config['live_mode']) {
			$oauth_url = 'https://login.24sevenoffice.com/';
			$api_url = 'https://usagelog.api.24sevenoffice.com';
		} else {
			$oauth_url = 'https://login.24sevenoffice.com/';
			$api_url = 'https://usagelog-beta.api.24sevenoffice.com';
		}

		// Initialize API drivers with correct urls
		$this->api = new ApiCallDriver($api_url, array('bearer' => true));
		$this->oauth_api = new ApiCallDriver($oauth_url, array('bearer' => true));

		$this->auth_token = $this->create_auth_token($config);
		$this->api->set_auth_token($this->auth_token);
	}
	function create_auth_token($config) {
		$request_data = array('grant_type' => 'client_credentials',
		"audience"=>"https://usagelog.24sevenoffice.com",
		"client_id"=>"rEh3XBbLMcyKnfS3MGwVljsXFKnCaCic",
		"client_secret"=>"dqodINA5VrUHjiKRiZd1bh7FgEmb-Mbcwua4MIohRD-33IFv7e34mcj9vrDtrOBT");
		$session_data = $this->oauth_api->post('oauth/token', $request_data);
		$access_token = "";

		if(isset($session_data['access_token'])) {
			$access_token = $session_data['access_token'];
		} else {
			$access_token = $session_data[0]['access_token'];
		}
		return $access_token;
	}
	function get_entries($params) {
	    $response = $this->api->get('/entries', $params);
	    return $response;
	}
	function insert_entry($params) {
		if($this->auth_token != ""){
			$response = $this->api->post('/entries', $params);
			return $response;
		} else {
			return array(array("message"=>"missing accesstoken"), 0);
		}
	}

}

if($_POST['action'] == "export") {
	$suggested_date = $_POST['date'];
	if($suggested_date != ""){
		$date = $_POST['confirmed_date'];
		if($date != "") {
			$date = date("Y-m-d", strtotime($date));
			$s_sql = "SELECT * FROM collecting_cases_report_24so WHERE date = ?";
			$o_query = $o_main->db->query($s_sql, array(date("Y-m-d", strtotime($suggested_date))));
			$reports = $o_query ? $o_query->result_array() : array();
			if(count($reports) > 0) {
				$api = new Integration24Report(array(
					'o_main' => $o_main,
					'bearer' => 1,
					'live_mode' => 1
				));

				foreach($reports as $report) {
					$s_sql = "SELECT * FROM creditor WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($report['creditor_id']));
					$creditor = $o_query ? $o_query->row_array() : array();
					if($creditor){
						$client_id = $creditor['24sevenoffice_client_id'];
						if($client_id != "") {
							if(!$creditor['is_demo']){
								$lineReported = 0;
								$error_msg = "";

								if($report['printed_amount_reported'] == "0000-00-00" || $report['printed_amount_reported'] == ""){
									if(intval($report['printed_amount']) > 0){
										$type_name = "oflow_printed_count";
										$data = array();
										$data['subjectId'] = $client_id."";
										$data['type'] = $type_name;
										$data['value'] = intval($report['printed_amount']);
										$data['id'] = $report['id']."_".$type_name;
										$data['isUniqueId'] = true;
										$data['timestamp'] = date("Y-m-d", strtotime($date))."T12:00:00Z";

										// var_dump($api->get_entries(array("type"=>"oflow_test")));
										list($result, $httpcode) = $api->insert_entry($data);
										if($httpcode == 202){
											$s_sql = "UPDATE collecting_cases_report_24so SET printed_amount_reported = NOW() WHERE id = ?";
											$o_query = $o_main->db->query($s_sql, array($report['id']));
											$lineReported++;
										} else {
											if($result['message'] != "") {
												$error_msg .= $type_name.": ".$result['message']."<br/>";
											} else {
												$error_msg .= $type_name.": error occured httpcode:".$httpcode."<br/>";
											}
										}
									} else {
										$s_sql = "UPDATE collecting_cases_report_24so SET printed_amount_reported = NOW() WHERE id = ?";
										$o_query = $o_main->db->query($s_sql, array($report['id']));
									}
								}
								if($report['ehf_amount_reported'] == "0000-00-00" || $report['ehf_amount_reported'] == ""){
									if(intval($report['ehf_amount']) > 0){
										$type_name = "oflow_ehf_count";
										$data = array();
										$data['subjectId'] = $client_id."";
										$data['type'] = $type_name;
										$data['value'] = intval($report['ehf_amount']);
										$data['id'] = $report['id']."_".$type_name;
										$data['isUniqueId'] = true;
										$data['timestamp'] = date("Y-m-d", strtotime($date))."T12:00:00Z";

										// var_dump($api->get_entries(array("type"=>"oflow_test")));
										list($result, $httpcode) = $api->insert_entry($data);
										if($httpcode == 202){
											$s_sql = "UPDATE collecting_cases_report_24so SET ehf_amount_reported = NOW() WHERE id = ?";
											$o_query = $o_main->db->query($s_sql, array($report['id']));
											$lineReported++;
										} else {
											if($result['message'] != "") {
												$error_msg .= $type_name.": ".$result['message']."<br/>";
											} else {
												$error_msg .= $type_name.": error occured httpcode:".$httpcode."<br/>";
											}
										}
									} else {
										$s_sql = "UPDATE collecting_cases_report_24so SET ehf_amount_reported = NOW() WHERE id = ?";
										$o_query = $o_main->db->query($s_sql, array($report['id']));
									}
								}

								if($report['total_fees_payed_reported'] == "0000-00-00" || $report['total_fees_payed_reported'] == ""){
									
									if($creditor['billing_type'] == 2){
										$modifier_fees = $creditor['billing_percent_fees']/100;
										$modifier_interest = $creditor['billing_percent_interest']/100;
										
										$tobeBilled = round(($report['fee_payed_amount']*$modifier_fees + $report['interest_payed_amount']*$modifier_interest));
									} else {
										$modifier = 0.5;
										if($creditor['billing_type'] == 1){
											$modifier = $creditor['billing_percent']/100;
										}
										$tobeBilled = round(($report['fee_payed_amount'] + $report['interest_payed_amount']) * $modifier);
									}
									if($tobeBilled > 0) {
										$type_name = "oflow_fees_payed_amount";
										$data = array();
										$data['subjectId'] = $client_id."";
										$data['type'] = $type_name;
										$data['value'] = $tobeBilled;
										$data['id'] = $report['id']."_".$type_name;
										$data['isUniqueId'] = true;
										$data['timestamp'] = date("Y-m-d", strtotime($date))."T12:00:00Z";

										// var_dump($api->get_entries(array("type"=>"oflow_test")));
										list($result, $httpcode) = $api->insert_entry($data);
										if($httpcode == 202){
											$s_sql = "UPDATE collecting_cases_report_24so SET total_fees_payed_reported = NOW(), total_fee_and_interest_billed = ? WHERE id = ?";
											$o_query = $o_main->db->query($s_sql, array($tobeBilled, $report['id']));
											$lineReported++;
										} else {
											if($result['message'] != "") {
												$error_msg .= $type_name.": ".$result['message']."<br/>";
											} else {
												$error_msg .= $type_name.": error occured httpcode:".$httpcode."<br/>";
											}
										}
									} else {
										$s_sql = "UPDATE collecting_cases_report_24so SET total_fees_payed_reported = NOW() WHERE id = ?";
										$o_query = $o_main->db->query($s_sql, array($report['id']));
									}
								}

								if($report['sent_without_fees_reported'] == "0000-00-00" || $report['sent_without_fees_reported'] == ""){
									if(intval($report['sent_without_fees_amount']) > 0){
										$type_name = "oflow_sent_without_fees_amount";
										$data = array();
										$data['subjectId'] = $client_id."";
										$data['type'] = $type_name;
										$data['value'] = intval($report['sent_without_fees_amount']);
										$data['id'] = $report['id']."_".$type_name;
										$data['isUniqueId'] = true;
										$data['timestamp'] = date("Y-m-d", strtotime($date))."T12:00:00Z";

										// var_dump($api->get_entries(array("type"=>"oflow_test")));
										list($result, $httpcode) = $api->insert_entry($data);
										if($httpcode == 202){
											$s_sql = "UPDATE collecting_cases_report_24so SET sent_without_fees_reported = NOW() WHERE id = ?";
											$o_query = $o_main->db->query($s_sql, array($report['id']));
											$lineReported++;
										} else {
											if($result['message'] != "") {
												$error_msg .= $type_name.": ".$result['message']."<br/>";
											} else {
												$error_msg .= $type_name.": error occured httpcode:".$httpcode."<br/>";
											}
										}
									} else {
										$s_sql = "UPDATE collecting_cases_report_24so SET sent_without_fees_reported = NOW() WHERE id = ?";
										$o_query = $o_main->db->query($s_sql, array($report['id']));
									}
								}

								if($report['fees_forgiven_amount_reported'] == "0000-00-00" || $report['fees_forgiven_amount_reported'] == ""){
									if(intval($report['fees_forgiven_amount']) > 0){
										$type_name = "oflow_fees_forgiven_amount";
										$data = array();
										$data['subjectId'] = $client_id."";
										$data['type'] = $type_name;
										$data['value'] = intval($report['fees_forgiven_amount']);
										$data['id'] = $report['id']."_".$type_name;
										$data['isUniqueId'] = true;
										$data['timestamp'] = date("Y-m-d", strtotime($date))."T12:00:00Z";

										// var_dump($api->get_entries(array("type"=>"oflow_test")));
										list($result, $httpcode) = $api->insert_entry($data);
										if($httpcode == 202){
											$s_sql = "UPDATE collecting_cases_report_24so SET fees_forgiven_amount_reported = NOW() WHERE id = ?";
											$o_query = $o_main->db->query($s_sql, array($report['id']));
											$lineReported++;
										} else {
											if($result['message'] != "") {
												$error_msg .= $type_name.": ".$result['message']."<br/>";
											} else {
												$error_msg .= $type_name.": error occured httpcode:".$httpcode."<br/>";
											}
										}
									} else {
										$s_sql = "UPDATE collecting_cases_report_24so SET fees_forgiven_amount_reported = NOW() WHERE id = ?";
										$o_query = $o_main->db->query($s_sql, array($report['id']));
									}
								}

								if($error_msg != "") {
									$fw_error_msg[] = $error_msg;
									$s_sql = "UPDATE collecting_cases_report_24so SET report_error_msg = ? WHERE id = ?";
									$o_query = $o_main->db->query($s_sql, array($error_msg, $report['id']));
								} else {
									$s_sql = "UPDATE collecting_cases_report_24so SET report_error_msg = '' WHERE id = ?";
									$o_query = $o_main->db->query($s_sql, array($report['id']));
								}
							} else {
								$s_sql = "UPDATE collecting_cases_report_24so SET is_demo = 1, report_error_msg = '',
								fees_forgiven_amount_reported = NOW(), sent_without_fees_reported = NOW(), total_fees_payed_reported = NOW(),
								printed_amount_reported = NOW()	WHERE id = ?";
								$o_query = $o_main->db->query($s_sql, array($report['id']));
							}
						} else {
							$fw_error_msg[] = $creditor['id']." ".$formText_CreditorMissing24SevenOfficeClientId_output."<br/>";
						}
					} else {
						$fw_error_msg[] = $report['id']." ".$formText_ReportMissingCreditor_output."<br/>";
					}
				}

			} else {
				$fw_error_msg[] =  $formText_NoReportsFound_output."<br/>";
			}
		} else {
			?>
			<div class="popupform">
				<div id="popup-validate-message" style="display:none;"></div>
			<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=exportTo24Seven";?>" method="post">
				<input type="hidden" name="fwajax" value="1">
				<input type="hidden" name="fw_nocss" value="1">
				<input type="hidden" name="output_form_submit" value="1">
				<input type="hidden" name="action" value="export">
				<input type="hidden" name="date" value="<?php echo $suggested_date; ?>">

				<div class="inner">
					<div class="line">
						<div class="lineTitle"><?php echo $formText_Date_output; ?></div>
						<div class="lineInput">
							<input type="text" class="popupforminput botspace datefield" autocomplete="off" name="confirmed_date" value="<?php echo date("d.m.Y", strtotime($suggested_date)); ?>" required>
						</div>
						<div class="clear"></div>
					</div>
				</div>
				<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>"></div>
			</form>
			</div>
			<style>

			.popupform .lineTitle {
				font-weight:700;
				margin-bottom: 10px;
			}
			.popupform textarea.popupforminput {
				border-radius: 4px;
				padding:5px 10px;
				font-size:12px;
				line-height:17px;
			}
			.project-file {
				margin-bottom: 4px;
			}
			.project-file .deleteImage {
				float: right;
			}
			.popupeditbox label.error {
			    color: #c11;
			    margin-left: 10px;
			    border: 0;
			    display: none !important;
			}
			.popupform .popupforminput.error { border-color:#c11 !important;}
			#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
			</style>
			<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
			<script type="text/javascript">


			$(function() {
				$(".datefield").datepicker({
					dateFormat: "d.m.yy",
					firstDay: 1
				})
				$("form.output-form").validate({
					submitHandler: function(form) {
						fw_loading_start();
						var formdata = $(form).serializeArray();
						var data = {};
						$(formdata).each(function(index, obj){
							data[obj.name] = obj.value;
						});
						// data.imagesToProcess = imagesToProcess;
						// data.imagesHandle = imagesHandle;
						// data.images = images;

						$.ajax({
							url: $(form).attr("action"),
							cache: false,
							type: "POST",
							dataType: "json",
							data: data,
							success: function (data) {
								fw_loading_end();
								if(data.error !== undefined)
								{
									$.each(data.error, function(index, value){
										var _type = Array("error");
										if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");

									});
									fw_loading_end();
									fw_click_instance = fw_changes_made = false;
								} else {
									out_popup.addClass("close-reload");
									out_popup.close();
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
			});
			</script>

			<?php
		}
	} else {
		echo $formText_DateNotProvided_output;
	}
} else if($_POST['action'] == "check_data"){
	$api = new Integration24Report(array(
		'o_main' => $o_main,
		'bearer' => 1
	));
	var_dump($api->get_entries(array("type"=>"oflow_sent_without_fees_amount")));
}

?>

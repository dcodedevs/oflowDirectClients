<?php
$subscribtionId = $_POST['subscribtionId'] ? ($_POST['subscribtionId']) : 0;
$customerId = $_POST['customerId'] ? ($_POST['customerId']) : 0;
$action = $_POST['action'] ? ($_POST['action']) : '';

// Save
if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        if ($subscribtionId) {
			if($_POST['property_turnover_rent'] == 0){
				$_POST['percentageOfTurnover'] = "";
				$_POST['turnoverMinimumAmount'] = "";
				$_POST['turnoverMaximumAmount'] = "";
			}

            $s_sql = "UPDATE subscriptionmulti SET
            updated = now(),
            updatedBy= ?,
			property_turnover_rent = ?,
			percentageOfTurnover = ?,
			turnoverMinimumAmount = ?
            WHERE id = ?";
            $o_main->db->query($s_sql, array($variables->loggID,
			$_POST['property_turnover_rent'],
			$_POST['percentageOfTurnover'],  $_POST['turnoverMinimumAmount'],
			 $subscribtionId));
            $fw_return_data = $s_sql;
            $fw_redirect_url = $_POST['redirect_url'];
        }
	}
}
if($subscribtionId) {
    $s_sql = "SELECT * FROM subscriptionmulti WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($subscribtionId));
    if($o_query && $o_query->num_rows()>0) {
        $subscribtionData = $o_query->row_array();
    }
}

$s_sql = "SELECT * FROM customer WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($customerId));
if($o_query && $o_query->num_rows()>0) {
    $customer = $o_query->row_array();
}

function formatDate($date) {
    global $formText_NotSet_output;
    if ($date == '0000-00-00' || !$date || empty($date)) return '';
    return date('d.m.Y', strtotime($date));
}

function unformatDate($date) {
    $d = explode('.', $date);
    return $d[2].'-'.$d[1].'-'.$d[0];
}
$ownercompanies = array();

$s_sql = "SELECT * FROM ownercompany";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
    $ownercompanies = $o_query->result_array();
}

$subscriptiontypes = array();

$s_sql = "SELECT * FROM subscriptiontype";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
    $subscriptiontypes = $o_query->result_array();
}
?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editSubscriptionTurnover";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="subscribtionId" value="<?php echo $subscribtionId;?>">
		<input type="hidden" name="customerId" value="<?php echo $customerId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId; ?>">
		<div class="inner">
            <div class="line">
                <div class="lineTitle"><?php echo $formText_TurnoverBasedRent_output; ?></div>
                <div class="lineInput">
					<label for="optionyes"><?php echo $formText_Yes_output;?></label>
					<input class="propertyOption" id="optionyes" type="radio" name="property_turnover_rent" value="1" <?php if($subscribtionData['property_turnover_rent'] == 1) echo 'checked'; ?>/>
					<label for="optionno"><?php echo $formText_No_output;?></label>
					<input class="propertyOption" id="optionno" type="radio" name="property_turnover_rent" value="0" <?php if($subscribtionData['property_turnover_rent'] == 0) echo 'checked'; ?>/>
                </div>
				<div class="clear"></div>
            </div>
			<div class="propertyOptionTextWrapper" <?php if($subscribtionData['property_turnover_rent'] == 0) echo 'style="display:none;"'?>>
	            <div class="line">
	                <div class="lineTitle"><?php echo $formText_PercentageOfTurnover_Output; ?></div>
	                <div class="lineInput">
						<input type="text" class="popupforminput botspace" name="percentageOfTurnover" value="<?php echo $subscribtionData['percentageOfTurnover']; ?>" autocomplete="off">
	                </div>
					<div class="clear"></div>
	            </div>
	            <div class="line">
	                <div class="lineTitle"><?php echo $formText_MinimumAmount_Output; ?></div>
	                <div class="lineInput">
						<input type="text" class="popupforminput botspace" name="turnoverMinimumAmount" value="<?php echo $subscribtionData['turnoverMinimumAmount']; ?>" autocomplete="off">
	                </div>
					<div class="clear"></div>
	            </div>
			</div>

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
                    if(data.redirect_url !== undefined)
                    {
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

    $(".propertyOption").change(function(){
		var value = $(this).val();
		if(value == 1){
			$(".propertyOptionTextWrapper").show();
		} else if(value == 0){
			$(".propertyOptionTextWrapper").hide();
		} else {

		}
    })

	$(".datepicker").datepicker({
		firstDay: 1,
		beforeShow: function(dateText, inst) {
			$(inst.dpDiv).removeClass('monthcalendar');
		},
		dateFormat: "dd.mm.yy"
	})
})

</script>
<style>
.popupform input.popupforminput.checkbox {
    width: auto;
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

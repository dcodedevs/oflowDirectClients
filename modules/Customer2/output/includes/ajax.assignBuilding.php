<?php
if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
		$s_sql = "INSERT INTO customer2buildingconnection SET
		id=NULL,
		moduleID = ?,
		created = now(),
		createdBy= ?,
		customerId= ?,
		buildingId= ?";

		$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['customerId'], $_POST['buildingId']));
		$fw_return_data = $o_main->db->insert_id();
		$fw_redirect_url = $_POST['redirect_url'];
		return;
	}
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=assignBuilding";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="customerId" value="<?php echo $_POST['customerId'];?>">

        <?php if ($_POST['page'] == 'detailPage'): ?>
            <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId']; ?>">
        <?php else: ?>
            <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&list_filter=all"; ?>">
        <?php endif; ?>

		<div class="inner">
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_Building_Output; ?></div>
        		<div class="lineInput">
                    <div class="selectDiv selectDivModified">
                        <div class="selectDivWrapper">
                            <select name="buildingId" id="">
                                <option value="0"><?php echo $formText_ChooseBuilding_output; ?></option>
                                <?php
                                $buildings = array();
                                $s_sql = "SELECT * FROM building";
                                $o_query = $o_main->db->query($s_sql);
								if($o_query && $o_query->num_rows()>0) {
								    $buildings = $o_query->result_array();
								}
								foreach($buildings as $building){ ?>
                                    <option value="<?php echo $building['id']; ?>"><?php echo $building['name']; ?></option>
                                <?php } ?>
                            </select>
                            <div class="arrowDown"></div>
                        </div>
                    </div>
                </div>
        		<div class="clear"></div>
    		</div>
		</div>

		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>
<script type="text/javascript">

$(document).ready(function() {
    $('.output-form').on('submit', function(e) {
        e.preventDefault();
        var data = {};
        $(this).serializeArray().forEach(function(item, index) {
            data[item.name] = item.value;
        });
        ajaxCall('assignBuilding', data, function (json) {
            if (json.redirect_url) document.location.href = json.redirect_url;
            else out_popup.close();
        });
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
.popupform .line .lineInput {
	width:70%;
	float:left;
}
</style>

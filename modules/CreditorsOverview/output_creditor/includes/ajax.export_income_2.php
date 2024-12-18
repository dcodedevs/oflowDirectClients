<?php
if($_POST['output_form_submit']) {
    if(isset($_POST['dateFrom'])){ $filter_date_from = date("Y-m-d", strtotime($_POST['dateFrom'])); } else { $filter_date_from = ''; }
    if(isset($_POST['dateTo'])){ $filter_date_to = date("Y-m-d", strtotime($_POST['dateTo'])); } else { $filter_date_to = ''; }
    if($filter_date_from != "" && $filter_date_to != "") {
               
    }
} else {
    ?>
    <div class="popupform">
    <div class="popupformTitle"><?php echo $formText_ChooseWhichColumnsToExport_LID6214;?></div>
    <div id="popup-validate-message" style="display:none;"></div>
    <form class="output-form-contactperson" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=".$inc_obj."&inc_act=export_income_2";?>" method="post" target="_blank">
        <input type="hidden" name="fwajax" value="1">
        <input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">

        <div class="inner">
            <div class="line">
				<div class="lineTitle"><?php echo $formText_DateFrom_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace datepicker dateFrom" autocomplete="off" name="dateFrom" value="<?php echo $bookaccount['bookaccount']; ?>" required/>
				</div>
				<div class="clear"></div>
			</div>
            
            <div class="line">
				<div class="lineTitle"><?php echo $formText_DateTo_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace datepicker dateTo" autocomplete="off" name="dateTo" value="<?php echo $bookaccount['bookaccount']; ?>" required/>
				</div>
				<div class="clear"></div>
			</div>
        </div>
        <div class="popupformbtn">
            <button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_LID4382;?></button>
            <input type="submit" name="sbmbtn" value="<?php echo $formText_Export_LID6219; ?>">
        </div>
    </form>
    </div>
    <style>
    input[type="checkbox"][readonly] {
    pointer-events: none;
    }
    h3 select {
        font-size: 16px;
        margin-left: 15px;
    }
    </style>
    <script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
    <script type="text/javascript">
    $(function() {
        $(".datepicker").datepicker({
            "dateFormat": "dd.mm.yy"
        });
        $("form.output-form-contactperson").validate({
            submitHandler: function(form) {
                form.submit();
                out_popup.close();
            }
        });
    });
    </script>

    <?php
}
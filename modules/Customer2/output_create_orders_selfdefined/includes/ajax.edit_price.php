<?php
$pricePerPiece = $_POST['pricePerPiece'] ? $o_main->db->escape_str($_POST['pricePerPiece']) : 0;


?>

<div class="popupform popupform-<?php echo $article_id;?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=".$s_inc_act;?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$projectId; ?>">
        <?php if($error != "") {
            ?>
            <div class="inner">
                <?php
                echo $error;
                ?>
            </div>
        <?php
        } else { ?>
            <div class="inner">
        		<div class="line">
            		<div class="lineTitle"><?php echo $formText_PricePerPiece_Output; ?></div>
            		<div class="lineInput">
                        <input type="text" class="popupforminput botspace" id="pricePerPiece" autocomplete="off" name="pricePerPiece" required value="<?php echo htmlentities($pricePerPiece);?>">
                    </div>
            		<div class="clear"></div>
        		</div>
    		</div>

    		<div class="popupformbtn">
    			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
    			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
    		</div>
        <?php } ?>
	</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$(".popupform-<?php echo $article_id;?> form.output-form").validate({
        ignore: [],
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
                    $(".price_per_piece").data("price", $("#pricePerPiece").val());
                    $(".price_per_piece").html($("#pricePerPiece").val());
                    out_popup.addClass("close-reload");
                    out_popup.close();
                }
            }).fail(function() {
                $(".popupform-<?php echo $article_id;?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $(".popupform-<?php echo $article_id;?> #popup-validate-message").show();
                $('.popupform-<?php echo $article_id;?> #popupeditbox').css('height', $('.popupform-<?php echo $article_id;?> #popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $(".popupform-<?php echo $article_id;?> #popup-validate-message").html(message);
                $(".popupform-<?php echo $article_id;?> #popup-validate-message").show();
                $('.popupform-<?php echo $article_id;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $(".popupform-<?php echo $article_id;?> #popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "external_sys_customer_id") {
                error.insertAfter(".popupform-<?php echo $article_id;?> .selectCustomer");
            }
            if(element.attr("name") == "projectLeader") {
                error.insertAfter(".popupform-<?php echo $article_id;?> .selectEmployee");
            }
            if(element.attr("name") == "projectOwner") {
                error.insertAfter(".popupform-<?php echo $article_id;?> .selectOwner");
            }
        },
        messages: {
            external_sys_customer_id: "<?php echo $formText_SelectTheCustomer_output;?>",
            projectLeader: "<?php echo $formText_SelectProjectLeader_output;?>",
            projectOwner: "<?php echo $formText_SelectProjectOwner_output;?>"
        }
    });

    $(".popupform-<?php echo $article_id;?> .selectArticle").unbind("click").bind("click", function(){
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_create_orders_selfdefined&inc_obj=ajax&inc_act=get_articles";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })
});

</script>
<style>
.lineInput .otherInput {
    margin-top: 10px;
}
.lineInput input[type="radio"]{
    margin-right: 10px;
    vertical-align: middle;
}
.lineInput input[type="radio"] + label {
    margin-right: 10px;
    vertical-align: middle;
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
.popupform .lineInput.lineWhole {
	font-size: 14px;
}
.popupform .lineInput.lineWhole label {
	font-weight: normal !important;
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
label.error {
    color: #c11;
    margin-left: 10px;
    border: 0;
    display: inline !important;
}
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
.addSubProject {
    margin-bottom: 10px;
}
</style>
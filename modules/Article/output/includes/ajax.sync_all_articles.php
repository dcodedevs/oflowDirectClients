<?php

$s_sql = "SELECT * FROM article_basisconfig";
$o_query = $o_main->db->query($s_sql);
$article_basisconfig = $o_query ? $o_query->row_array() : array();
if($article_basisconfig == null){
	$article_basisconfig = array();
}

$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
$article_accountconfig = $o_query ? $o_query->row_array() : array();

foreach($article_accountconfig as $key=>$value) {
	if($value > 0){
		$article_basisconfig[$key] = intval($value) - 1;
	}
}

$sql = "SELECT * FROM article WHERE external_sys_id = 0 OR external_sys_id is null";
$o_query = $o_main->db->query($sql);
$articles = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();

if($moduleAccesslevel > 10) {
    if(isset($_POST['output_form_submit'])) {

        if ($article_accountconfig['activate_check_article_in_external_sys_before_save']) {
            foreach($articles as $article) {
				$sql = "SELECT * FROM ownercompany WHERE company_product_set_id = ?";
		        $o_query = $o_main->db->query($sql, array($article['company_product_set_id']));
		        $ownercompany = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

                $external_sys_id = 0;
                $hook_params = array(
					'ownercompany_id'=$ownercompany['id'],
                    'id' => $article['id'],
                    'account' => $article['SalesAccountWithVat'],
                    'vat' => $article['VatCodeWithVat'],
                    'articleCode' => $article['articleCode'] ? $article['articleCode'] : 0,
                    'name' => $article['name'],
                    'costWithoutVat' => $article['costPrice'],
                    'priceWithoutVat' => $article['price']
                );

                $hook_file = __DIR__ . '/../../../../' . $article_accountconfig['path_check_article_in_external_sys_before_save'];
                if (file_exists($hook_file)) {
                    include $hook_file;
                    if (is_callable($run_hook)) {
                        $hook_result = $run_hook($hook_params);
                        unset($run_hook);
                    }
                }

                // Error message
                if ($hook_result['error']) {
                    $noError = false;
                    $integrationHookError = true;
                    $integrationHookErrorMessage = $hook_result['message'];
                } else {
                    $external_sys_id = $hook_result['product_data']['id'];
                }
                if ($article['id']) {
                    $s_sql = "UPDATE article SET
                    updated = now(),
                    updatedBy=?,
                    name= ?,
                    costPrice = ?,
                    price = ?,
                    SalesAccountWithVat = ?,
                    VatCodeWithVat = ?,
                    articleCode = ?,
                    accounting_reference_for_invoice_receiver = ?,
                    external_sys_id = ?,
                    comment = ?,
                    articlegroup_id = ?,
                    system_article_type = ?
                    WHERE id = ?";
                    $o_main->db->query($s_sql, array($variables->loggID, $article['name'], $article['costPrice'], $article['price'], $article['SalesAccountWithVat'],  $article['VatCodeWithVat'], $article['articleCode'], $article['accounting_reference_for_invoice_receiver'], $external_sys_id, $article['comment'], $article['article_group'], $article['system_article_type'], $article['id']));

                }
            }

            $fw_redirect_url = $_POST['redirect_url'];
        }
    }
}
?>

<div class="popupform">
    <div id="popup-validate-message" style="display:none;"></div>
    <form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=sync_all_articles";?>" method="post">
        <input type="hidden" name="fwajax" value="1">
        <input type="hidden" name="fw_nocss" value="1">
        <input type="hidden" name="output_form_submit" value="1">
        <input type="hidden" name="articleId" value="<?php echo $articleId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list"; ?>">
        <div class="inner">
            <?php
            if ($article_accountconfig['activate_check_article_in_external_sys_before_save'] && count($articles) > 0) {
                echo count($articles)." ".$formText_ArticlesNotSynced_output;
            }
            ?>

        </div>
        <?php if(count($articles) > 0) { ?>
            <div class="popupformbtn">
                <button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
                <input type="submit" name="sbmbtn" value="<?php echo $formText_PerformSync_Output; ?>">
            </div>
        <?php } ?>
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
                    if(data.error !== undefined){
                        $("#popup-validate-message").html(data.error);
                        $("#popup-validate-message").show();
                    } else {
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

    $('.datefield').datepicker({
        dateFormat: 'dd.mm.yy',
        firstDay: 1
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
.popupform input.popupforminput.checkbox {
    width: auto;
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

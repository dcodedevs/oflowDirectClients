<?php

?>
<div class="popupform">
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_adjustprices&inc_obj=ajax&inc_act=edit_specific_article";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">

	<div class="inner">
        <div class="line">
            <div class="lineTitle"><?php echo $formText_Action_Output; ?></div>
            <div class="lineInput">
                <input type="radio" required name="action" value="1" id="uncheck_article"/> <label for="uncheck_article"><?php echo $formText_Uncheck_output?></label>
                <input type="radio" required name="action" value="2" id="check_article"/> <label for="check_article"><?php echo $formText_Check_output?></label>
            </div>
            <div class="clear"></div>
        </div>

		<div class="line">
    		<div class="lineTitle"><?php echo $formText_Article_Output; ?></div>
    		<div class="lineInput">
                <select name="article" class="articleSelector" required>
                    <option value=""><?php echo $formText_Select_output;?><option>
                    <?php

                    $s_sql = "SELECT * FROM article
                    WHERE article.content_status < 2 AND (article.article_supplier_id is null OR article.article_supplier_id = 0) ORDER BY article.name ASC";

                    $o_query = $o_main->db->query($s_sql);
                    $articles = ($o_query ? $o_query->result_array() : array());
                    foreach($articles as $article) {
                        ?>
                        <option value="<?php echo $article['id']?>"><?php echo $article['name'];?></option>
                        <?php
                    }
                    ?>
                </select>
    		</div>
    		<div class="clear"></div>
		</div>

	</div>
	<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>"></div>
</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
	$("form.output-form").validate({
		submitHandler: function(form) {
            var action = 0;
            if($("#uncheck_article").is(":checked")) {
                action = 1;
            } else if($("#check_article").is(":checked")) {
                action = 2;
            }
            var articleId = 0;
            if($(".articleSelector").val() > 0){
                articleId = $(".articleSelector").val();
            }
            if(articleId > 0 && action > 0){
                if(action == 2){
                    var checked = true;
                } else if(action == 1){
                    var checked = false;
                }
                $(".article_orderline_"+articleId).prop("checked", checked);
            }
			out_popup.close();
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

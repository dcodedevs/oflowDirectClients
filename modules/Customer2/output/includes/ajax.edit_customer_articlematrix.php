<?php
$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
$article_accountconfig = $o_query ? $o_query->row_array() : array();

if($article_accountconfig['activateArticlePriceMatrix'] || $article_accountconfig['activateArticleDiscountMatrix']) {

	if($moduleAccesslevel > 10)
	{
		if(isset($_POST['output_form_submit']))
		{
			if($article_accountconfig['activateArticlePriceMatrix'] && isset($_POST['articlePriceMatrixId'])){
				$s_sql = "UPDATE customer SET
				updated = now(),
                updatedBy= ?,
				articlePriceMatrixId = ?
                WHERE id = ?";
				$o_main->db->query($s_sql, array($variables->loggID, $_POST['articlePriceMatrixId'], $_POST['customerId']));
			}
			if($article_accountconfig['activateArticleDiscountMatrix'] && isset($_POST['articleDiscountMatrixId'])){
				$s_sql = "UPDATE customer SET
				updated = now(),
                updatedBy= ?,
				articleDiscountMatrixId = ?
                WHERE id = ?";
				$o_main->db->query($s_sql, array($variables->loggID, $_POST['articleDiscountMatrixId'], $_POST['customerId']));
			}
			$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId'];
			return;

		} 
	}

	if(isset($_POST['customerId']) && $_POST['customerId'] > 0)
	{	
		$s_sql = "SELECT * FROM customer WHERE id = ?";
	    $o_query = $o_main->db->query($s_sql, array($_POST['customerId']));
	    if($o_query && $o_query->num_rows()>0) {
	        $v_data = $o_query->row_array();
	    }
	}
	?>
	<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_customer_articlematrix";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="customerId" value="<?php print $_POST['customerId'];?>">
		<div class="inner">
			<?php if($article_accountconfig['activateArticlePriceMatrix']) {
            $s_sql = "SELECT * FROM articlepricematrix ORDER BY name ASC";
            $o_query = $o_main->db->query($s_sql);
            $articlePriceMatrixes = $o_query ? $o_query->result_array() : array();
            ?>
			<div class="line articleLine">
                <div class="lineTitle"><?php echo $formText_ArticlePriceMatrix_Output; ?></div>
                <div class="lineInput">
                    <select name="articlePriceMatrixId">                        
			            <option value=""><?php echo $formText_None_output;?></option>
			            <?php foreach($articlePriceMatrixes as $articlePriceMatrix) { ?>
			            <option value="<?php echo $articlePriceMatrix['id']?>" <?php if($articlePriceMatrix['id'] == $v_data['articlePriceMatrixId']) echo 'selected';?>><?php echo $articlePriceMatrix['name'];?></option>
			            <?php }?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
            <?php } ?>

            <?php if($article_accountconfig['activateArticleDiscountMatrix']) {
            $s_sql = "SELECT * FROM articlediscountmatrix ORDER BY name ASC";
            $o_query = $o_main->db->query($s_sql);
            $articleDiscountMatrixes = $o_query ? $o_query->result_array() : array();
            ?>
			<div class="line articleLine">
                <div class="lineTitle"><?php echo $formText_ArticleDiscountMatrix_Output; ?></div>
                <div class="lineInput">
                    <select name="articleDiscountMatrixId">                        
			            <option value=""><?php echo $formText_None_output;?></option>
			            <?php foreach($articleDiscountMatrixes as $articleDiscountMatrix) { ?>
			            <option value="<?php echo $articleDiscountMatrix['id']?>" <?php if($articleDiscountMatrix['id'] == $v_data['articleDiscountMatrixId']) echo 'selected';?>><?php echo $articleDiscountMatrix['name'];?></option>
			            <?php }?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
            <?php } ?>
        </div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
	</div>
	<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
	<script type="text/javascript">
	$(function() {
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
	                            out_popup.addClass("close-reload").data("redirect", data.redirect_url);
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
	});
	</script>
<?php } ?>

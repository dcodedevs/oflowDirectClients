<?php
// Customer accountconfig
$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
$v_customer_accountconfig = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

// Ownercompany list
$sql = "SELECT * FROM ownercompany";
$o_query = $o_main->db->query($sql);
$ownercompany_list = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();

$multiple_ownercompanies = count($ownercompany_list) ? true : false;
$sync_completed = false;
$sync_error = false;

if($moduleAccesslevel > 10) {
    // If there is just one ownercompany select that and sync immediately
    if (!$multiple_ownercompanies || isset($_POST['output_form_submit'])) {
        $hook_params = array(
            'customer_id' => $_POST['cid'],
            'ownercompany_id' => $_POST['ownercompany_id'] ? $_POST['ownercompany_id'] : $ownercompany_list[0]['id']
        );

        $hook_file = __DIR__ . '/../../../../' . $v_customer_accountconfig['path_sync_customer_hook'];
        if (file_exists($hook_file)) {
            require_once $hook_file;
            if (is_callable($run_hook)) {
                $hook_result = $run_hook($hook_params);
                unset($run_hook);
            }
        }

        $sync_completed = true;

        if ($hook_result['customer_sync_result']['status'] == 422) {
            $sync_error = $hook_result;
        } 
    }
}
?>

<?php if ($sync_completed): ?>

    <?php if (!$sync_error): ?>
        <div>Customer synced</div>

    <?php else: ?>
        <div>Sync error</div>
        <pre><?php print_r($sync_error); ?></pre>
    <?php endif; ?>

<?php else: ?>

    <div class="popupform">
        <form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=syncCustomerHook";?>" method="post">
            <input type="hidden" name="fwajax" value="1">
            <input type="hidden" name="fw_nocss" value="1">
            <input type="hidden" name="output_form_submit" value="1">
            <input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">

            <div class="inner">
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_SelectOwnerCompany_Output; ?></div>
                    <div class="lineInput">
                        <select name="ownercompany_id">
                            <?php foreach ($ownercompany_list as $ownercompany): ?>
                                <option value="<?php echo $ownercompany['id']; ?>"><?php echo $ownercompany['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>

            <div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_Sync_Output; ?>"></div>
        </form>
    </div>

<?php endif; ?>

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
                    $('.popupform').html(data.html);
                    out_popup.addClass("close-reload");
					// out_popup.close();
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

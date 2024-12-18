<?php
$table_id = $_POST['table_id'] ? $o_main->db->escape_str($_POST['table_id']) : 0;
$subtable_id = $_POST['subtable_id'] ? $o_main->db->escape_str($_POST['subtable_id']) : 0;
$action = $_POST['action'] ? $_POST['action'] : '';
$status = $_POST['status'] ? $_POST['status'] : 0;

$sql = "SELECT * FROM table_viewer  WHERE id = ?";
$o_query = $o_main->db->query($sql, array($table_id));
$table_viewer = $o_query ? $o_query->row_array() : array();

$sql = "SELECT * FROM table_viewer_sub  WHERE id = ?";
$o_query = $o_main->db->query($sql, array($subtable_id));
$sub_table_viewer = $o_query ? $o_query->row_array() : array();

if($moduleAccesslevel > 10) {
    if($_POST['action'] == "deleteTable"){
        $sql = "DELETE FROM table_viewer_sub WHERE id = ?";
        $o_query = $o_main->db->query($sql, array($subtable_id));
        return;
    }

	if(isset($_POST['output_form_submit'])) {
		if($_POST['table_name'] != "" && $_POST['parent_field'] != "" && $_POST['table_field'] != ""){
	        if ($sub_table_viewer) {
	            $sql = "UPDATE table_viewer_sub SET
	            updated = now(),
	            updatedBy='".$variables->loggID."',
	            table_name='".$o_main->db->escape_str($_POST['table_name'])."',
	            table_field='".$o_main->db->escape_str($_POST['table_field'])."',
	            parent_field='".$o_main->db->escape_str($_POST['parent_field'])."',
	            subtable_field='".$o_main->db->escape_str($_POST['subtable_field'])."',
                table_viewer_id='".$o_main->db->escape_str($table_id)."',
				moduleID='".$o_main->db->escape_str($moduleID)."'
	            WHERE id = ".$table_viewer['id'];

				$o_query = $o_main->db->query($sql);
				$insert_id = $processId;
	            $fw_redirect_url = $_POST['redirect_url'];
	        } else {
	            $sql = "INSERT INTO table_viewer_sub SET
	            created = now(),
	            createdBy='".$variables->loggID."',
	            table_name='".$o_main->db->escape_str($_POST['table_name'])."',
	            table_field='".$o_main->db->escape_str($_POST['table_field'])."',
	            parent_field='".$o_main->db->escape_str($_POST['parent_field'])."',
	            subtable_field='".$o_main->db->escape_str($_POST['subtable_field'])."',
                table_viewer_id='".$o_main->db->escape_str($table_id)."',
				moduleID='".$o_main->db->escape_str($moduleID)."'";
				$o_query = $o_main->db->query($sql);
	            $insert_id = $o_main->db->insert_id();
	            $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_view_table&inc_obj=details&cid=".$insert_id;
	        }
		} else {
			$fw_error_msg[] = $formText_MissingName_output;
		}
	}
}

$sql = "SHOW COLUMNS FROM ".$table_viewer['table_name'];
$o_query = $o_main->db->query($sql);
$columns = $o_query ? $o_query->result_array() : array();
?>

<div class="popupform popupform-<?php echo $processId;?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_view_table&inc_obj=ajax&inc_act=add_subtable";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="table_id" value="<?php echo $table_id;?>">
		<input type="hidden" name="subtable_id" value="<?php echo $subtable_id;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_view_table&inc_obj=list&viewer_id=".$table_id; ?>">
		<div class="inner">
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_ParentColumn_Output; ?></div>
        		<div class="lineInput">
                    <select name="parent_field" required autocomplete="off">
                        <option value=""><?php echo $formText_Select_output;?></option>
                        <?php
                        foreach($columns as $column) {
                            ?>
                            <option value="<?php echo $column['Field']?>" <?php if($sub_table_viewer['parent_field'] == $column['Field']) echo 'selected';?>><?php echo $column['Field']?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
        		<div class="clear"></div>
    		</div>
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_SubTableName_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" autocomplete="off" name="table_name" required value="<?php echo $sub_table_viewer['table_name']; ?>">
                </div>
        		<div class="clear"></div>
    		</div>
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_SubTableFieldForParentConnection_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" autocomplete="off" name="subtable_field" required value="<?php echo $sub_table_viewer['subtable_field']; ?>">
                </div>
        		<div class="clear"></div>
    		</div>
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_SubTableFieldNameToDisplayInMainTable_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" autocomplete="off" name="table_field" required value="<?php echo $sub_table_viewer['table_field']; ?>">
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
<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
    $(".popupform-<?php echo $processId;?> form.output-form").validate({
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
                    if(data.redirect_url !== undefined)
                    {
                        out_popup.addClass("close-reload");
                        out_popup.close();
                    }
                }
            }).fail(function() {
                $(".popupform-<?php echo $processId;?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $(".popupform-<?php echo $processId;?> #popup-validate-message").show();
                $('.popupform-<?php echo $processId;?> #popupeditbox').css('height', $('.popupform-<?php echo $processId;?> #popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $(".popupform-<?php echo $processId;?> #popup-validate-message").html(message);
                $(".popupform-<?php echo $processId;?> #popup-validate-message").show();
                $('.popupform-<?php echo $processId;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $(".popupform-<?php echo $processId;?> #popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "creditor_id") {
                error.insertAfter(".popupform-<?php echo $processId;?> .selectCreditor");
            }
            if(element.attr("name") == "debitor_id") {
                error.insertAfter(".popupform-<?php echo $processId;?> .selectDebitor");
            }
        },
        messages: {
            creditor_id: "<?php echo $formText_SelectTheCreditor_output;?>",
            debitor_id: "<?php echo $formText_SelectTheDebitor_output;?>",
        }
    });
	$(".datefield").datepicker({
		dateFormat: "d.m.yy",
		firstDay: 1
	})
});

</script>

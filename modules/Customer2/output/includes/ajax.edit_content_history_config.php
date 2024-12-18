<?php
$v_fields_basis = array();
$o_query = $o_main->db->query("SELECT * FROM sys_content_history_basisconfig WHERE name = '".$o_main->db->escape_str($_POST['table'])."'");
$v_content_history_basisconfig = $o_query ? $o_query->row_array() : array();
if(isset($v_content_history_basisconfig['id']))
{
	$v_json = json_decode($v_content_history_basisconfig['field_config'], TRUE);
	if(FALSE !== $v_json)
	{
		$v_fields_basis = $v_json;
	}
}
$v_fields_account = array();
$o_query = $o_main->db->query("SELECT * FROM sys_content_history_accountconfig WHERE name = '".$o_main->db->escape_str($_POST['table'])."'");
$v_content_history_accountconfig= $o_query ? $o_query->row_array() : array();
if(isset($v_content_history_accountconfig['id']))
{
	$v_json = json_decode($v_content_history_accountconfig['field_config'], TRUE);
	if(FALSE !== $v_json)
	{
		$v_fields_account = $v_json;
	}
}

$v_fields_all_basis = $v_fields_basis;
$v_fields_all_account = $v_fields_account;

$o_query = $o_main->db->query("SHOW COLUMNS FROM ".$o_main->db_escape_name($_POST['table']));
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $v_row)
{
	if(!in_array($v_row['Field'], $v_fields_all_basis))
	{
		$v_fields_all_basis[] = $v_row['Field'];
	}
	if(!in_array($v_row['Field'], $v_fields_all_account))
	{
		$v_fields_all_account[] = $v_row['Field'];
	}
}
if($o_main->db->table_exists($o_main->db_escape_name($_POST['table'].'content')))
{
	$o_query = $o_main->db->query("SHOW COLUMNS FROM ".$o_main->db_escape_name($_POST['table'].'content'));
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_row)
	{
		if(in_array($v_row['Field'], array('id', 'languageID', $_POST['table'].'ID'))) continue;
		
		if(!in_array($v_row['Field'], $v_fields_all_basis))
		{
			$v_fields_all_basis[] = $v_row['Field'];
		}
		if(!in_array($v_row['Field'], $v_fields_all_account))
		{
			$v_fields_all_account[] = $v_row['Field'];
		}
	}
}

// On form submit
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		$v_fields = array();
		foreach($_POST['output_account_field'] as $l_key => $s_field)
		{
			if(1 == $_POST['output_account_selected'][$l_key])
			{
				$v_fields[] = $s_field;
			}
		}
		$o_query = $o_main->db->query("SELECT * FROM sys_content_history_accountconfig WHERE name = '".$o_main->db->escape_str($_POST['table'])."'");
		if($o_query && $o_query->num_rows()>0)
		{
			$o_query = $o_main->db->query("UPDATE sys_content_history_accountconfig SET updated = NOW(), updatedBy = '".$o_main->db->escape_str($variables->loggID)."', field_config = '".$o_main->db->escape_str(json_encode($v_fields))."' WHERE name = '".$o_main->db->escape_str($_POST['table'])."'");
		} else {
			if(0 < sizeof($v_fields))
			{
				$o_query = $o_main->db->query("INSERT INTO sys_content_history_accountconfig SET created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."', field_config = '".$o_main->db->escape_str(json_encode($v_fields))."', name = '".$o_main->db->escape_str($_POST['table'])."'");
			}
		}
		
		$v_fields = array();
		foreach($_POST['output_basis_field'] as $l_key => $s_field)
		{
			if(1 == $_POST['output_basis_selected'][$l_key])
			{
				$v_fields[] = $s_field;
			}
		}
		$o_query = $o_main->db->query("SELECT * FROM sys_content_history_basisconfig WHERE name = '".$o_main->db->escape_str($_POST['table'])."'");
		if($o_query && $o_query->num_rows()>0)
		{
			$o_query = $o_main->db->query("UPDATE sys_content_history_basisconfig SET updated = NOW(), updatedBy = '".$o_main->db->escape_str($variables->loggID)."', field_config = '".$o_main->db->escape_str(json_encode($v_fields))."' WHERE name = '".$o_main->db->escape_str($_POST['table'])."'");
		} else {
			if(0 < sizeof($v_fields))
			{
				$o_query = $o_main->db->query("INSERT INTO sys_content_history_basisconfig SET created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."', field_config = '".$o_main->db->escape_str(json_encode($v_fields))."', name = '".$o_main->db->escape_str($_POST['table'])."'");
			}
		}
		return;
	}
}
?>

<div class="popupform">
	<div class="popupformTitle"><?php echo $formText_SelectFieldsToShowAndDragAndDropToChangeOrder_Output;?></div>
	<div id="popup-validate-message-2" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=".$s_inc_obj."&inc_act=".$s_inc_act;?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="table" value="<?php echo $_POST['table'];?>">
		<div class="inner">
			<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
				<div class="panel panel-default">
					<div class="panel-heading" role="tab" id="headingOne">
						<h4 class="panel-title">
							<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
								<?php echo $formText_AccountConfig_Output;?>
							</a>
						</h4>
					</div>
					<div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
						<div class="panel-body">
							<table class="table table-condensed table-hover output-sortable"><?php
							foreach($v_fields_all_account as $s_field)
							{
								?>
								<tr>
									<td><?php echo $s_field; ?></td>
									<td>
										<input type="checkbox" value="1"<?php echo (in_array($s_field, $v_fields_account) ? ' checked' : '');?> onChange="$(this).parent().find('.real').val($(this).is(':checked')?1:0);">
										<input type="hidden" name="output_account_field[]" value="<?php echo $s_field;?>">
										<input type="hidden" name="output_account_selected[]" value="<?php echo (in_array($s_field, $v_fields_account) ? 1 : 0);?>" class="real">
									</td>
								</tr>
								<?php
							}
							?>
							</table>
						</div>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading" role="tab" id="headingTwo">
						<h4 class="panel-title">
							<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
								<?php echo $formText_BasisConfig_Output;?>
							</a>
						</h4>
					</div>
					<div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
						<div class="panel-body">
							<table class="table table-condensed table-hover output-sortable"><?php
							foreach($v_fields_all_basis as $s_field)
							{
								?>
								<tr>
									<td><?php echo $s_field; ?></td>
									<td>
										<input type="checkbox" value="1"<?php echo (in_array($s_field, $v_fields_basis) ? ' checked' : '');?> onChange="$(this).parent().find('.real').val($(this).is(':checked')?1:0);">
										<input type="hidden" name="output_basis_field[]" value="<?php echo $s_field;?>">
										<input type="hidden" name="output_basis_selected[]" value="<?php echo (in_array($s_field, $v_fields_basis) ? 1 : 0);?>" class="real">
									</td>
								</tr>
								<?php
							}
							?>
							</table>
						</div>
					</div>
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
$('.output-sortable').sortable({items:'tr'});
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
                success: function (json) {
					fw_loading_end();

					if(json.error !== undefined)
					{
						var _msg = '';
						$.each(json.error, function(index, value){
							var _type = Array('error');
							if(index.length > 0 && index.indexOf('_') > 0) _type = index.split('_');
							fw_info_message_add(_type[0], value);
							
							_msg = _msg + '<div class="msg-' + _type[0] + '">' + value + '</div>';
						});
						fw_info_message_show();
						
						
						$('#popup-validate-message-2').html(_msg, true);
						$('#popup-validate-message-2').html('<?php echo $formText_ErrorOccuredSavingContent_Output;?>', true);
						$('#popup-validate-message-2').show();
					} else {
						$("#popupeditbox2 .b-close").click();
						$(window).resize();
						var data = {
							id: '<?php echo $_POST['id'];?>',
							table: '<?php echo $_POST['table'];?>',
						};
						ajaxCall('show_content_history', data, function(json) {
							 $('#popupeditboxcontent').html('').html(json.html);
						});
					}
                }
            }).fail(function() {
                $("#popup-validate-message-2").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $("#popup-validate-message-2").show();
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

                $("#popup-validate-message-2").html(message);
                $("#popup-validate-message-2").show();
                $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $("#popup-validate-message-2").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        }
    });
});

</script>
<style>
.popupform input.popupforminput.checkbox {
    width: auto;
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
.selectDivModified {
    display:block;
}
.popupform, .popupeditform {
    width:100%;
    margin:0 auto;
    position:relative;
}
.invoiceEmail {
    display: none;
}
.selectDivModified {
    display:block;
}
label.error { display: none !important; }
.popupform .popupforminput.error { border-color:#c11 !important;}
#popup-validate-message-2, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
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

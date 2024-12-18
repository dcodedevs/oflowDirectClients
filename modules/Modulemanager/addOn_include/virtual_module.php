<?php
ob_start();
//$s_module = (isset($_POST['sys_module']) ? $_POST['sys_module'] : $_GET['sys_module']);
//$o_query = $o_main->db->query("SELECT * FROM moduledata WHERE name = '".$o_main->db->escape_str($s_module)."'");
//$v_data = $o_query ? $o_query->row_array() : array();
//if(empty($v_data['uniqueID']))
//{
//	echo $formText_ModuleDoesNotExists_Modulemanager;
//	return;
//}

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		$s_module_name = preg_replace('#[^A-za-z0-9]+#', '', isset($_POST['module_name']) ? trim($_POST['module_name']) : '');
		$s_module_source = preg_replace('#[^A-za-z0-9]+#', '', isset($_POST['module_source']) ? trim($_POST['module_source']) : '');
		if('' == $s_module_name)
		{
			$fw_error_msg['error_'.count($fw_error_msg)] = $formText_ModuleNameCannotBeEmptyOrWithSpecialCharacters_Modulemanager;
		} else if('' == $s_module_source)
		{
			$fw_error_msg['error_'.count($fw_error_msg)] = $formText_ModuleSourceIsMissing_Modulemanager;
		} else {
			$o_query = $o_main->db->query("SELECT * FROM moduledata WHERE name = '".$o_main->db->escape_str($s_module_name)."' OR local_name = '".$o_main->db->escape_str($s_module_name)."'");
			if($o_query && $o_query->num_rows()>0)
			{
				$fw_error_msg['error_'.count($fw_error_msg)] = $formText_ModuleNameAlreadyInUse_Modulemanager;
			}
		}
		if(0 == count($fw_error_msg))
		{
			$o_query = $o_main->db->query("SELECT MAX(ordernr) AS ordernr FROM moduledata");
			$v_moduledata = $o_query ? $o_query->row_array() : array();
			$l_max = intval($v_moduledata['ordernr']) + 1;
			$o_main->db->query("INSERT INTO moduledata SET
				name = '".$o_main->db->escape_str($s_module_name)."',
				`type`=0,
				ordernr='".$o_main->db->escape_str($l_max)."',
				modulemode='C',
				frontpage=0,
				local_name='".$o_main->db->escape_str($s_module_name)."',
				virtual_module_source = '".$o_main->db->escape_str($s_module_source)."'
			");
			$l_id = $o_main->db->insert_id();
			$o_main->db->query("UPDATE moduledata SET id = uniqueID WHERE uniqueID = '".$o_main->db->escape_str($l_id)."'");
			$o_main->db->query("UPDATE accountinfo SET force_cache_refresh = NOW()");
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module;
		return;
	}
}
?>
<div class="popupform">
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&includefile=virtual_module";?>" method="POST">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<div class="inner">
		<div class="popupformTitle"><?php echo $formText_AddVirtualModule_Modulemanager;?></div>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_ModuleName_Output; ?></div>
			<div class="lineInput"><input type="text" class="popupforminput" name="module_name" value=""></div>
			<div class="clear"></div>
		</div>
		<br>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_VirtualModuleSource_Output; ?></div>
			<div class="lineInput">
				<select name="module_source" class="popupforminput"><?php
				$o_query = $o_main->db->query("SELECT * FROM moduledata WHERE type = 0 AND (virtual_module_source IS NULL OR virtual_module_source = '') ORDER BY name");
				if($o_query && $o_query->num_rows()>0)
				foreach($o_query->result_array() as $v_row)
				{
					?><option value="<?php echo $v_row['name'];?>"><?php echo $v_row['name'];?></option><?php
				}
				?></select>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<div id="popup-validate-message"></div>
	<div class="popupformbtn">
		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
		<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output;?>">
	</div>
</form>
</div>
<style>
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	position:relative;
}
label.error { display: none !important; }
.popupform .popupforminput.error { border-color:#c11 !important;}
#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }

.clear {
	clear:both;
}
.inner {
	padding:10px;
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
<script type="text/javascript">
$(function() {
	$("form.output-form").on('submit', function(e){
		e.preventDefault();
		$('#popup-validate-message').html('');
		fw_loading_start();
		var form = $(this).closest('form');
		$.ajax({
				url: $(form).attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
				data: $(form).serialize(),
				success: function (data) {
					if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							$('#popup-validate-message').append('<div class="error-msg">' + value + '</div>');
						});
						$("#popup-validate-message").show();
						fw_loading_end();
					} else {
						if(data.redirect_url !== undefined)
						{
							out_popup.close();
							fw_load_ajax(data.redirect_url, '', false);//window.location = data.redirect_url;
						}
					}
				}
			}).fail(function() {
				$("#popup-validate-message").html("<div class=\"error-msg\"><?php echo $formText_ErrorOccurredProcessingRequest_Output;?></div>", true);
				$("#popup-validate-message").show();
				fw_loading_end();
			});
	});
});
</script>
<?php
$fw_return_data['html'] = ob_get_clean();
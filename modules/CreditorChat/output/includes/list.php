<?php
//error_reporting(E_ALL | E_STRICT); ini_set("display_errors", 1);
$page = 1;
require_once __DIR__ . '/list_btn.php';

$creditor_id = $_GET['creditor_id'] ?? 0;
$collecting_company_case_id = $_GET['collecting_company_case_id'] ?? 0;
$s_sql = "SELECT creditor.id, creditor.companyname, cccc.collecting_company_case_id FROM creditor
JOIN creditor_collecting_company_chat cccc ON cccc.creditor_id = creditor.id
WHERE creditor.id = ? AND cccc.collecting_company_case_id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id, $collecting_company_case_id));
$selected_creditor = ($o_query ? $o_query->row_array() : array());

$fwaFileuploadConfigs = array(
	array (
	  'module_folder' => 'CreditorChat', // module id in which this block is used
	  'id' => 'articleimageeuploadpopup',
	  'upload_type'=>'file',
	  'content_table' => 'creditor_collecting_company_chat',
	  'content_field' => 'files',
	  'content_id' => $cid,
	  'content_module_id' => $moduleID, // id of module
	  'dropZone' => 'block',
	  'callbackAll' => 'callBackOnUploadAll',
	  'callbackStart' => 'callbackOnStart',
	  'callbackDelete' => 'callbackOnDelete'
	),
	array (
	  'module_folder' => 'CreditorChat', // module id in which this block is used
	  'id' => 'articleinsfileupload',
	  'upload_type' => 'image',
	  'content_table' => 'creditor_collecting_company_chat',
	  'content_field' => 'screenshot',
	  'content_id' => $cid,
	  'content_module_id' => $moduleID, // id of module
	  'dropZone' => 'block',
	  'callbackAll' => 'callBackOnUploadAll',
	  'callbackStart' => 'callbackOnStart',
	  'callbackDelete' => 'callbackOnDelete'
	)
);

?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<div class="creditor_selected_block">
					<?php echo $formText_SelectedCreditor_output;?>: 
					<?php echo $selected_creditor['companyname']; ?>
				</div>
				<div class="creditor_selecting_wrapper">
					<div class="creditor_selecting_block">
						<?php 
						$s_sql = "SELECT creditor.id, creditor.companyname, ccc.id as collecting_company_case_id, 
						IF(SUM(IF(IFNULL(cccc.message_from_oflow, 0) = 0, IF(IFNULL(cccc.read_check, 0) = 0, 1, 0), 0)) > 0, 1,0) as unread_exists FROM creditor
						JOIN creditor_collecting_company_chat cccc ON cccc.creditor_id = creditor.id
						JOIN collecting_company_cases ccc ON cccc.collecting_company_case_id = ccc.id
						GROUP BY ccc.id
						ORDER BY unread_exists DESC, cccc.created DESC";
						$o_query = $o_main->db->query($s_sql);
						$creditors_with_cases = ($o_query ? $o_query->result_array() : array());
						foreach($creditors_with_cases as $creditor_with_cases){
							?>
							<div class="creditor_selection<?php if($creditor_with_cases['id'] == $selected_creditor['id'] && $creditor_with_cases['collecting_company_case_id'] == $selected_creditor['collecting_company_case_id']) echo ' active';?>" data-creditor-id="<?php echo $creditor_with_cases['id'];?>" data-collecting_company_case_id-id="<?php echo $creditor_with_cases['collecting_company_case_id']?>">
								<?php if($creditor_with_cases['unread_exists']) echo '<span class="unread_indicator"></span>';?><?php echo $creditor_with_cases['companyname'];?> <?php echo $creditor_with_cases['collecting_company_case_id'];?>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<?php  if($selected_creditor){ ?>
				<div class="creditor_message_wrapper">
					<div class="creditor_chat_wrapper">
						<?php /*?>
					<form class="messageForm" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=add_message";?>" method="post">
						<input type="hidden" name="fwajax" value="1">
						<input type="hidden" name="fw_nocss" value="1">
						<input type="hidden" name="creditor_id" value="<?php echo $selected_creditor['id'];?>">
						<input type="hidden" name="collecting_company_case_id" value="<?php echo $selected_creditor['collecting_company_case_id'];?>">
							<textarea class="creditor_chat" name="message"></textarea>
							
							<div class="line">
								<div class="lineTitle"><?php echo $formText_Files_Output; ?></div>
								<div class="lineInput" style="margin-bottom: 10px">
									<?php
									$fwaFileuploadConfig = $fwaFileuploadConfigs[0];
									include __DIR__ . '/fileupload_popup/output.php';
									?>
								</div>
								<div class="clear"></div>
							</div>
							<div class="line">
								<div class="lineTitle"><?php echo $formText_Images_Output; ?></div>
								<div class="lineInput">
									<?php
									$fwaFileuploadConfig = $fwaFileuploadConfigs[1];
									include __DIR__ . '/fileupload_popup/output.php';
									?>
								</div>
								<div class="clear"></div>
							</div>
					</form><div class="send_message"><?php echo $formText_Send_output;?></div>*/?>
						
						<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$collecting_company_case_id;?>" class="show_case" target="_blank"><?php echo $formText_ShowCase_output;?></a>
					</div>
					<div class="creditor_chat_messages">
						<?php 
						$s_sql = "SELECT cccc.id,
						cccc.created,
						cccc.message,
						cccc.message_from_oflow,
						cccc.creditor_id,
						cccc.collecting_company_case_id,
						cccc.screenshot,
						cccc.files,
						cccc.createdBy
						FROM creditor_collecting_company_chat cccc
						WHERE cccc.creditor_id = ? AND cccc.collecting_company_case_id = ?
						ORDER BY cccc.created DESC";
						$o_query = $o_main->db->query($s_sql, array($selected_creditor['id'], $collecting_company_case_id));
						$creditor_messages = ($o_query ? $o_query->result_array() : array());
						foreach($creditor_messages as $creditor_message) {
							?>
							<div class="chat_message<?php if($creditor_message['message_from_oflow']) echo ' from_oflow'?>">
								<div class="message_info"><?php echo $formText_Created_output;?> <?php echo date("d.m.Y H:i", strtotime($creditor_message['created']))?> <?php if($creditor_message['message_from_oflow']) { echo $formText_Oflow_output." ".$creditor_message['createdBy'];} else { echo $creditor_message['createdBy'];}?></div>
								<div class="chat_message_info">
									<div><?php echo nl2br($creditor_message['message']);?></div>
									<?php
									$ordersScreenshots = json_decode($creditor_message['screenshot'], true);
									foreach($ordersScreenshots as $file) {
										$fileParts = explode('/',$file[1][0]);
										$fileName = array_pop($fileParts);
										$fileParts[] = rawurlencode($fileName);
										$filePath = implode('/',$fileParts);
										$fileUrl = $extradomaindirroot."/../".$file[1][0];
										$fileName = $file[0];
										if(strpos($file[1][0],'uploads/protected/')!==false)
										{
											$fileUrl = $extradomaindirroot."/../".$filePath.'?caID='.$_GET['caID'].'&table=creditor_collecting_company_chat&field=screenshot&ID='.$creditor_message['id'];
										}
									?>
									
									<span class="screenshot-view" >
										<a href="<?php echo $fileUrl;?>" class="fancybox" rel="message<?php echo $creditor_message['id'];?>">
											<img src="<?php echo $fileUrl;?>" class="screenshotImage"/></a>
										</span>
									<?php } ?>
									<?php
									$files = json_decode($creditor_message['files'], true);
									foreach($files as $file) {
										$fileParts = explode('/',$file[1][0]);
										$fileName = array_pop($fileParts);
										$fileParts[] = rawurlencode($fileName);
										$filePath = implode('/',$fileParts);
										$fileUrl = $extradomaindirroot."/../".$file[1][0];
										$fileName = $file[0];
										if(strpos($file[1][0],'uploads/protected/')!==false)
										{
											$fileUrl = $extradomaindirroot."/../".$filePath.'?caID='.$_GET['caID'].'&table=creditor_collecting_company_chat&field=files&ID='.$creditor_message['id'];
										}
									?>
										<div class="project-file">
											<div class="project-file-file">
												<a href="<?php echo $fileUrl;?>" download><?php echo $fileName;?></a>
											</div>
										</div>
										<?php
									}
									?>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<?php } ?>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>
<style>
	.creditor_selected_block {
		padding: 5px;
	}
	.creditor_selecting_wrapper {
		width: 25%;
		float: left;
	}
	.creditor_selecting_block {
		height: 600px;
		overflow-y: scroll;
	}
	.creditor_message_wrapper {
		width: 75%;
		float: right;
	}
	.creditor_chat_wrapper {
		margin-left: 15px;
		margin-right: 15px;
	}
	.creditor_chat_wrapper .creditor_chat {
		width: 100%;
	}
	.creditor_selection {
		padding: 5px;
		background: #fff;
		border: 1px solid #cecece;
		cursor: pointer;
	}
	.creditor_selection.active {
		background: #46b2e2;
		color: #fff;
	}
	.creditor_message_wrapper .send_message {
		display: inline-block;
		border: none;
		border-radius: 4px;
		padding: 5px 10px;
		color: #FFF;
		background: #124171;
		outline: none;
		margin-top: 10px;
		cursor:pointer;
	}
	.creditor_chat_messages {
		margin-top: 10px;
		padding: 5px 15px;
	}
	.creditor_chat_messages .chat_message {
		display: block;
	    margin-bottom: 10px;	
		margin-top: 5px;
		float: left;
		width: 65%;
		text-align: left;		
	}
	.creditor_chat_messages .chat_message_info {
		border: 1px solid #ddd;
	    border-radius: 5px;
		word-break: break-all;
	    padding: 5px 7px;
		background: #6edaed;
	}
	.creditor_chat_messages .chat_message.from_oflow {
	    float: right;
		text-align: right;
	}
	.creditor_chat_messages .chat_message.from_oflow .chat_message_info{
	    background: #f0f0f0;
	}
	.creditor_chat_messages .message_info {
		color: #bbbbbb;
	}
	.show_case {
		margin-left: 10px;
	}
	.screenshot-view {
	    display: inline-block;
	    vertical-align: top;
	    width: 50px;
	    margin-right: 10px;
	    border: 1px solid #cecece;
	    cursor: pointer;
	}
	.screenshot-view img {
	    width: 100%;
	}
	.unread_indicator {
		background-color: red;
		width: 6px;
		height: 6px;
		display: inline-block;
		margin-right: 5px;
		border-radius: 10px;
		vertical-align: middle;;
	}
</style>

<?php if(isset($_GET['list_filter'])){ $list_filter = $_GET['list_filter']; } else { $list_filter = 'all'; } ?>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed: 300,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
		if($(this).is('.close-reload')) {
			var redirectUrl = $(this).data("redirect");
			if(redirectUrl !== undefined && redirectUrl != ""){
				document.location.href = redirectUrl;
			} else {
                var data = {
                    creditor_id: '<?php echo $selected_creditor['id'];?>',
                };
				loadView("list", data);
            }
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};

function callBackOnUploadAll(data) {
	// updatePreview();
    $('.creditor_message_wrapper .send_message').val('<?php echo $formText_Send; ?>').prop('disabled',false);

};
function callbackOnStart(data) {
    $('.creditor_message_wrapper .send_message').val('<?php echo $formText_UploadInProgress_output;?>...').prop('disabled',true);
};
function callbackOnDelete(data){
	// updatePreview();
}
$(function(){
	$(".creditor_selection").off("click").on("click", function(){
		var data = {
			creditor_id: $(this).data("creditor-id"),
			collecting_company_case_id: $(this).data('collecting_company_case_id-id')
		}
		loadView("list", data);
	})
	$(".fancybox").fancybox();
	$(".send_message").off("click").on("click", function(){
        var data = {
			creditor_id: '<?php echo $selected_creditor['id']?>',
			collecting_company_case_id: '<?php echo $selected_creditor['collecting_company_case_id']?>',
			message: $(".creditor_chat").val()
        };
		var formdata = $(".messageForm").serializeArray();
		var data = {};
		$(formdata ).each(function(index, obj){
			if(data[obj.name] != undefined) {
				if(Array.isArray(data[obj.name])){
					data[obj.name].push(obj.value);
				} else {
					data[obj.name] = [data[obj.name], obj.value];
				}
			} else {
				data[obj.name] = obj.value;
			}
		});

		$.ajax({
			url: $(".messageForm").attr("action"),
			cache: false,
			type: "POST",
			dataType: "json",
			data: data,
			success: function (data) {
				fw_loading_end();					
				var data = {
					creditor_id: '<?php echo $selected_creditor['id'];?>',
					collecting_company_case_id: '<?php echo $selected_creditor['collecting_company_case_id'];?>',
				};
				loadView("list", data);
			}
		}).fail(function() {
			$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
			$("#popup-validate-message").show();
			$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
			fw_loading_end();
		});

	})
})
</script>
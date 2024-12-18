<?php
$customerId = isset($_POST['customerId']) ? $_POST['customerId'] : "";
$selectedBox = isset($_POST['box']) ? $_POST['box'] : "";
$search_mailbox = isset($_POST['search']) ? $_POST['search'] : "";

$fromResult = array();
$toResult = array();

$emailInfos = array();
$s_sql = "SELECT * FROM sys_emailintegration ORDER BY emailName ASC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
	$emailInfos = $o_query->result_array();
}
function upperListEncode() { //convert mb_list_encodings() to uppercase
    $encodes=mb_list_encodings();
    foreach ($encodes as $encode) $tencode[]=strtoupper($encode);
    return $tencode;
}
$s_sql = "SELECT * FROM customer WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($customerId));
$customer = $o_query ? $o_query->row_array() :array();
if($customer){
	$s_sql = "SELECT * FROM contactperson WHERE email is not null AND email <> '' AND customerId = ? ORDER BY name ASC";
	$o_query = $o_main->db->query($s_sql, array($customer['id']));
	$contactPersons = $o_query ? $o_query->result_array() : array();
	?>
	<div class="email_browser">
		<div class="email_browser_left">
			<select class="email_browser_select_wrapper">
				<?php
				foreach($emailInfos as $emailInfo) {
					?>
					<option value="<?php echo $emailInfo['emailAddress']?>"><?php echo $emailInfo['emailAddress'];?></option>
					<?php
				}
				?>
			</select>
			<?php

			$mbox = imap_open("{".$emailInfo['emailServerIn']."/imap/ssl}INBOX", $emailInfo['emailAddress'], $emailInfo['emailPassword'], OP_READONLY);
		    $mboxSent = imap_open("{".$emailInfo['emailServerOut']."/imap/ssl}SENT ITEMS", $emailInfo['emailAddress'], $emailInfo['emailPassword'], OP_READONLY);
			$searchString = "";
			$emails = array();
			foreach($contactPersons as $contactPerson){
				$searchString = ' FROM "'.$contactPerson['email'].'"';
				$emails_single = imap_sort($mbox, SORTDATE, 1, SE_NOPREFETCH, $searchString);
				$emails = array_merge($emails, $emails_single);
			}
			$inbox_count = count($emails);

			$searchString = "";
			$emails = array();
			foreach($contactPersons as $contactPerson){
				$searchString =  ' TO "'.$contactPerson['email'].'"';
				$emails_single = imap_sort($mbox, SORTDATE, 1, SE_NOPREFETCH, $searchString);
				$emails = array_merge($emails, $emails_single);
			}

			$outbox_count = count($emails);

			imap_close($mbox);
			imap_close($mboxSent);

			?>
			<div class="mailbox_title"><?php echo $formText_Mailboxes_output;?></div>
			<div class="mailbox_item <?php if($selectedBox == "inbox") echo 'active';?>" data-box="inbox" data-email="<?php echo $emailInfo['emailAddress'];?>"><?php echo $formText_Inbox_output;?><span class="mailbox_count"><?php echo $inbox_count;?></span></div>
			<div class="mailbox_item <?php if($selectedBox == "outbox") echo 'active';?>" data-box="outbox" data-email="<?php echo $emailInfo['emailAddress'];?>"><?php echo $formText_Outbox_output;?><span class="mailbox_count"><?php echo $outbox_count;?></span></div>

		</div>
		<div class="email_browser_middle">
			<div class="email_browser_box_search">
				<input type="text" name="" value="<?php echo $search_mailbox;?>" />
			</div>
			<div class="email_browser_middle_wrapper"></div>
		</div>
		<div class="email_browser_right">
			<div class="email_browser_single_wrapper">

			</div>
		</div>
		<div class="clear"></div>
	</div>
	<style>
	.email_browser {
		width: 100%;
		height: 400px;
		border: 1px solid #cecece;
	}
	.email_browser_select_wrapper {
		width: calc(100% - 20px);
		margin: 5px 10px;
		background: #fff;
		padding: 2px 5px;
		border-radius: 4px;
		border: 1px solid #cecece;
		box-sizing: border-box;
		position: relative;
	}
	.email_browser_left {
		float: left;
		width: 200px;
		height: 100%;
		background: #f4f4f4;
		overflow-x: hidden;
		overflow-y: auto;
		border-right: 1px solid #cecece;
	}
	.email_browser_left .mailbox_title {
		font-size: 11px;
		padding: 3px 10px;
	}
	.email_browser_left .mailbox_item {
		padding: 3px 10px;
		cursor: pointer;
	}
	.email_browser_left .mailbox_item .mailbox_count {
		float: right;
	}
	.email_browser_left .mailbox_item.active,
	.email_browser_left .mailbox_item:hover {
		background: #e8e8e8;
		font-weight: bold;
	}
	.email_browser_middle {
		float: left;
		width: 200px;
		height: 100%;
		background: #fff;
		overflow-x: hidden;
		overflow-y: auto;
		border-right: 1px solid #cecece;
	}
	.email_browser_no_box {
		padding: 5px 15px;
	}
	.email_browser_row {
		padding: 3px 10px;
		border-bottom: 1px solid #cecece;
		cursor: pointer;
	}
	.email_browser_row.active {
		background: #e8e8e8;
	}
	.email_browser_row_left {
		float: left;
		font-weight: bold;
	}
	.email_browser_row_right {
		float: right;
	}
	.email_browser_right {
		float: left;
		width: calc(100% - 400px);
		height: 100%;
		background: #fff;
		overflow-x: hidden;
		overflow-y: auto;
	}
	.email_browser_single_wrapper {
		padding: 10px 15px;
		overflow: auto;
		height: 100%;
	}
	.email_browser_box_search {
		padding: 10px 10px;
	}
	.email_browser_box_search input {
		width: 100%;
		padding: 2px 5px;
		border-radius: 4px;
		border: 1px solid #cecece;
	}
	.showMoreEmails {
		padding: 5px 15px;
		margin: 5px 10px;
		background: #52b2e4;
		border-radius: 4px;
		border: 1px solid #52b2e4;
		color: #fff;
		cursor: pointer;
		text-align: center;
	}
	</style>
	<script type="text/javascript">
		$(function(){
			$(".mailbox_item").off("click").on("click", function(){
				$(".mailbox_item").removeClass("active");
				$(this).addClass("active");
				var data = {
					box: $(this).data("box"),
					email: $(this).data("email"),
					customerId: '<?php echo $customerId;?>'
				}
				ajaxCall("getEmailsIMAPmore", data, function(data){
					$(".email_browser_box_search input").val("");
	                $('.email_browser_middle_wrapper').html(data.html);
				});
			})

			$(".email_browser_box_search input").change(function() {
				var data = {
					box: $(".mailbox_item.active").data("box"),
					search: $(this).val(),
					email: $(".mailbox_item.active").data("email"),
					customerId: '<?php echo $customerId;?>'
				}
				ajaxCall("getEmailsIMAPmore", data, function(data){
	                $('.email_browser_middle_wrapper').html('');
	                $('.email_browser_middle_wrapper').html(data.html);
				});
			})
		})
	</script>
<?php } ?>

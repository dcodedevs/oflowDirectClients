<?php
$customerId = isset($_POST['customerId']) ? $_POST['customerId'] : "";
$selectedBox = isset($_POST['box']) ? $_POST['box'] : "inbox";
$lastEmailnumber = isset($_POST['lastEmailnumber']) ? $_POST['lastEmailnumber'] : "";
$email = isset($_POST['email']) ? $_POST['email'] : "";
$search_mailbox = isset($_POST['search']) ? $_POST['search'] : "";



$s_sql = "SELECT * FROM customer WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($customerId));
$customer = $o_query ? $o_query->row_array() :array();
if($customer){
	$s_sql = "SELECT * FROM contactperson WHERE email is not null AND email <> '' AND customerId = ? ORDER BY name ASC";
	$o_query = $o_main->db->query($s_sql, array($customer['id']));
	$contactPersons = $o_query ? $o_query->result_array() : array();

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

	$s_sql = "SELECT * FROM sys_emailintegration WHERE emailAddress = ?";
	$o_query = $o_main->db->query($s_sql, array($email));
	if($o_query && $o_query->num_rows()>0) $emailInfo = $o_query->row_array();


	?>
	<?php
	if($selectedBox != "") {
		if($selectedBox == "inbox"){
			$mbox = imap_open("{".$emailInfo['emailServerIn']."/imap/ssl}INBOX", $emailInfo['emailAddress'], $emailInfo['emailPassword'], OP_READONLY);
			$mboxCheck = imap_check($mbox);
			$totalMessages = $lastEmailnumber != "" ? $lastEmailnumber - 1 : $mboxCheck->Nmsgs;
		} else {
			$mboxSent = imap_open("{".$emailInfo['emailServerOut']."/imap/ssl}SENT ITEMS", $emailInfo['emailAddress'], $emailInfo['emailPassword'], OP_READONLY);
			$mboxSentCheck = imap_check($mboxSent);
			$totalMessages = $lastEmailnumber != "" ? $lastEmailnumber - 1 : $mboxSentCheck->Nmsgs;
			$mbox = $mboxSent;
		}
		$emails = array();
		$searchString = "";
		$searchStringArray = array();
		if(strlen($search_mailbox) > 3){
			$searchString .= ' SUBJECT "'.$search_mailbox.'"';
		}
		if($selectedBox == "inbox"){
			foreach($contactPersons as $contactPerson){
				$searchStringEmail = ' FROM '.$contactPerson['email'].'';
				$emails_single = imap_sort($mbox, SORTDATE, 1, SE_NOPREFETCH, $searchString.$searchStringEmail);
				$emails = array_merge($emails, $emails_single);
			}
		} else if($selectedBox == "outbox"){
			foreach($contactPersons as $contactPerson){
				$searchStringEmail = ' TO "'.$contactPerson['email'].'"';
				$emails_single = imap_sort($mbox, SORTDATE, 1, SE_NOPREFETCH, $searchString.$searchStringEmail);
				$emails = array_merge($emails, $emails_single);
			}
		}

		if($emails){
			rsort($emails);
		}

		foreach($emails as $fromEmailItem) {
			$headerinfo = imap_headerinfo($mbox, $fromEmailItem);
			if(is_object($headerinfo)){
				$from = $headerinfo->from;
				$to = $headerinfo->to;

				if($selectedBox == "inbox"){
					$fromSingle = $from[0];
					$emailType = 'from';
				} else if($selectedBox == "outbox"){
					$fromSingle = $to[0];
					$emailType = 'to';
				}
				$elements = iconv_mime_decode($headerinfo->subject);
				?>
				<div class='email_browser_row' data-email='<?php echo $emailInfo["emailAddress"]?>' data-contactemail='<?php echo $selectedContactPerson; ?>' data-emailtype='<?php echo $emailType;?>'
				data-emailnumber='<?php echo $fromEmailItem; ?>'>
					<div class="email_browser_row_left"><?php echo $fromSingle->personal != "" ? iconv_mime_decode($fromSingle->personal) : iconv_mime_decode($fromSingle->mailbox);  ?></div>
					<div class="email_browser_row_right"><?php echo date("d.m.Y", strtotime($headerinfo->date));?></div>
					<div class="clear"></div>
					<?php
					echo $elements;

					?>
				</div>
				<?php
				$showFrom++;
			}
		}
		/*
		if(strlen($search_mailbox) < 3 && count($emails) < $totalMessages){
			?>
			<div class="showMoreEmails" data-email='<?php echo $emailInfo["emailAddress"]?>' data-emailtype='<?php echo $emailType;?>' data-emailnumber='<?php echo $fromEmailItem; ?>'><?php echo $formText_ShowMoreEmails_output;?></div>
			<?php
		}*/
	} else {
		?>
		<div class="email_browser_no_box">
			<?php
			echo $formText_SelectBox_output;
			?>
		</div>
		<?php
	}
	?>

	<?php

	imap_close($mbox);
	imap_close($mboxSent);
	?>
	<script type="text/javascript">
		$(function(){
			$(".showMoreEmails").off("click").on("click", function(){
				var item = $(this);
				var data = {
					box: $(this).data("box"),
					lastEmailnumber: $(this).data("emailnumber"),
					email: $(this).data("email")
				}
				ajaxCall("getEmailsIMAPmore", data, function(data){
	                $('.email_browser_middle_wrapper').append(data.html);
					item.remove();
				});
			})

			$(".email_browser_row").unbind("click").bind("click", function(){
				var item = $(this);
				var email = $(this).data("email");
				var emailType = $(this).data("emailtype");
				var emailNumber = $(this).data("emailnumber");
				if(email != "" && emailNumber != ""){
					$('#fw_loading').show();
					var data = {
						emailNumber: emailNumber,
						email: email,
						emailType: emailType
					}
					ajaxCall("getSingleEmailInfo", data, function(data){
		                $('.email_browser_single_wrapper').html('');
		                $('.email_browser_single_wrapper').html(data.html);
						$(".email_browser_row").removeClass("active");
						item.addClass("active");
					});
				}
			})
		})
	</script>

	<?php
	/*
	foreach($emailInfos as $emailInfo) {
	    $mbox = imap_open("{".$emailInfo['emailServerIn']."/imap/ssl}INBOX", $emailInfo['emailAddress'], $emailInfo['emailPassword'], OP_READONLY);
	    $mboxSent = imap_open("{".$emailInfo['emailServerOut']."/imap/ssl}SENT ITEMS", $emailInfo['emailAddress'], $emailInfo['emailPassword'], OP_READONLY);

	    echo '<div class="emailSubTitle">'.$formText_Inbox_output.' - '.$emailInfo['emailAddress'].'</div>';
	    foreach($contactPersonEmails as $contactPersonEmail){
	        $fromEmailArray = imap_search($mbox, 'FROM "'.$contactPersonEmail.'"', SE_FREE);
	        if($fromEmailArray){
	        	$showFrom = 0;
	        	$fromEmailArray = array_reverse($fromEmailArray);
	        	?>
	        	<div class="emailContentWrapper">
	        		<div class="contactEmail"><?php echo $contactPersonEmail;?></div>
	        		<div class="mailList">
			        	<?php
			        	foreach($fromEmailArray as $fromEmailItem) {
			        		if($showFrom < $perPage){
				        		$headerinfo = imap_headerinfo($mbox, $fromEmailItem);
				        		if(is_object($headerinfo)){
				        			$from = $headerinfo->from;
				        			$to = $headerinfo->to;

				        			$elements = iconv_mime_decode($headerinfo->subject);
									// $subject = "";
	                                // if (property_exists($headerinfo, 'subject')) $subject = $headerinfo->subject;
				        			// $elements = imap_mime_header_decode($subject);
									//
									// $texte='';
									// for ($i=0; $i<count($elements); $i++) {
									// 	switch (strtoupper($elements[$i]->charset)) { //convert charset to uppercase
								    //         case 'UTF-8': $texte.= $elements[$i]->text; //utf8 is ok
								    //             break;
								    //         case 'DEFAULT': $texte.= $elements[$i]->text; //no convert
								    //             break;
								    //         default:
									// 			if (in_array(strtoupper($elements[$i]->charset),upperListEncode())) //found in mb_list_encodings()
						            //             {
									// 				$texte.= mb_convert_encoding($elements[$i]->text,'UTF-8',$elements[$i]->charset);
									// 			}
							        //             else { //try to convert with iconv()
							        //                   $ret = iconv($elements[$i]->charset, "UTF-8", $elements[$i]->text);
							        //                   if (!$ret) $texte.=$elements[$i]->text;  //an error occurs (unknown charset)
							        //                   else $texte.=$ret;
						            //             }
							        //         break;
							        //     }
							        // }

					        		echo "<div class='mailListRow' data-email='".$emailInfo["emailAddress"]."' data-contactemail='".$contactPersonEmail."' data-emailtype='from' data-emailnumber='".$fromEmailItem."'><b>".
					        		$elements."</b></br>".$headerinfo->date. "</br>"."From: ";

					        		foreach($from as $fromSingle) {
					        			echo $fromSingle->mailbox."@".$fromSingle->host;
					        		}
					        		echo " To: ";
					        		foreach($to as $toSingle) {
					        			echo $toSingle->mailbox."@".$toSingle->host." ";
					        		}
					        		echo "</div>";
					        		$showFrom++;
					        	}
				        	} else {
				        		break;
				        	}
			        	}
			        	?>
			        </div>
		        	<?php
		        	$totalEmails = count($fromEmailArray);

		        ?>
		        	<div class="totalEmails">
		        		<?php echo '<span class="shown">'.$showFrom ."</span> ".$formText_Of_output." ".$totalEmails;?>
		        		<?php
		        		if($showFrom < $totalEmails) {
		        		?>
		        			<div class="showMore" data-total="<?php echo $totalEmails;?>" data-email="<?php echo $emailInfo['emailAddress'];?>" data-contactemail="<?php echo $contactPersonEmail;?>" data-emailshown="<?php echo $showFrom?>" data-emailtype="from"><?php echo $formText_ShowMore_output?></div>
		        		<?php } ?>
		        	</div>

		    	</div>
		        <?php
	        }
	    }
	    echo '<div class="emailSubTitle">'.$formText_Outbox_output.' - '.$emailInfo['emailAddress'].'</div>';
	    foreach($contactPersonEmails as $contactPersonEmail){
	        $toEmailArray = imap_search($mboxSent, 'TO "'.$contactPersonEmail.'"', SE_FREE);
	        if($toEmailArray){
	        	$shownTo = 0;
	        	$c = 0;
	        	$toEmailArray = array_reverse($toEmailArray);
	        	?>
	        	<div class="emailContentWrapper">
	        		<div class="contactEmail"><?php echo $contactPersonEmail;?></div>
	        		<div class="mailList">
			        	<?php
			        	foreach($toEmailArray as $toEmailItem) {
			        		if($shownTo < $perPage){
				        		$headerinfo = imap_headerinfo($mboxSent, $toEmailItem);
				        		if(is_object($headerinfo)){
				        			$from = $headerinfo->from;
				        			$to = $headerinfo->to;

									$elements = iconv_mime_decode($headerinfo->subject);

				        			// $elements = iconv_mime_decode($headerinfo->subject);
									// $subject = "";
									// if (property_exists($headerinfo, 'subject')) $subject = $headerinfo->subject;
									// $elements = imap_mime_header_decode($subject);
									//
									// $texte='';
									// for ($i=0; $i<count($elements); $i++) {
									// 	switch (strtoupper($elements[$i]->charset)) { //convert charset to uppercase
									// 		case 'UTF-8': $texte.= $elements[$i]->text; //utf8 is ok
									// 			break;
									// 		case 'DEFAULT': $texte.= $elements[$i]->text; //no convert
									// 			break;
									// 		default:
									// 			if (in_array(strtoupper($elements[$i]->charset),upperListEncode())) //found in mb_list_encodings()
									// 			{
									// 				$texte.= mb_convert_encoding($elements[$i]->text,'UTF-8',$elements[$i]->charset);
									// 			}
									// 			else { //try to convert with iconv()
									// 				  $ret = iconv($elements[$i]->charset, "UTF-8", $elements[$i]->text);
									// 				  if (!$ret) $texte.=$elements[$i]->text;  //an error occurs (unknown charset)
									// 				  else $texte.=$ret;
									// 			}
									// 		break;
									// 	}
									// }

					        		echo "<div class='mailListRow' data-email='".$emailInfo["emailAddress"]."' data-contactemail='".$contactPersonEmail."' data-emailtype='to' data-emailnumber='".$toEmailItem."'><b>".
									$elements."</b></br>".$headerinfo->date. "</br>"."From: ";
					        		foreach($from as $fromSingle) {
					        			echo $fromSingle->mailbox."@".$fromSingle->host;
					        		}
					        		echo " To: ";
					        		foreach($to as $toSingle) {
					        			echo $toSingle->mailbox."@".$toSingle->host." ";
					        		}
					        		echo "</div>";
					        		$shownTo++;
				        		}

				        	} else {
				        		break;
				        	}
			        	}
			        	?>
			        </div>
		        	<?php
		        	$totalEmails = count($toEmailArray);
		        ?>
		        	<div class="totalEmails">
		        		<?php echo '<span class="shown">'.$shownTo ."</span> ".$formText_Of_output." ".$totalEmails;?>
		        		<?php
	        			if($shownTo < $totalEmails) {
		        		?>
		        			<div class="showMore" data-total="<?php echo $totalEmails;?>" data-email="<?php echo $emailInfo['emailAddress'];?>" data-contactemail="<?php echo $contactPersonEmail;?>" data-emailshown="<?php echo $shownTo?>" data-emailtype="to"><?php echo $formText_ShowMore_output;?></div>
		        		<?php } ?>
		        	</div>
	    	    </div>
	        	<?php
	        }
	    }

	    imap_close($mbox);
	    imap_close($mboxSent);
	}
	?>
	<script>

	rebind();
	function rebind(){
		$(".totalEmails .showMore").unbind("click").bind("click", function(){
			var contactEmail = $(this).data("contactemail");
			var emailShown = $(this).data("emailshown");
			var emailType = $(this).data("emailtype");
			var email = $(this).data("email");
			var box = $(this);
			var total = $(this).data("total");
			var perPage = '<?php echo $perPage?>';
			if(email != "" && emailType != "" && emailShown != "" && contactEmail != ""){
				$(this).unbind("click");
				$('#fw_loading').show();
				$.ajax({
			        cache: false,
			        type: 'POST',
			        dataType: 'json',
			        data: {fwajax: 1, fw_nocss: 1, contactEmail: contactEmail, emailShown: emailShown, emailType: emailType, email: email, perPage: perPage},
			        url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=getMoreEmails"; ?>',
			        success: function(data){
			        	$('#fw_loading').hide();
			        	var boxParent = box.parent();
			        	boxParent.prev(".mailList").append($(data.html));
			        	var newEmailShown = parseInt(emailShown)+parseInt(perPage);
			        	if(newEmailShown >= total) {
			        		box.unbind("click").hide();
				        	box.data("emailshown", total);
				        	boxParent.find(".shown").html(total);
			        	} else {
				        	box.data("emailshown", newEmailShown);
				        	boxParent.find(".shown").html(newEmailShown);
			        	}
			        	rebind();
			        }
			    });
			}
		})
	}
	</script>*/
} ?>

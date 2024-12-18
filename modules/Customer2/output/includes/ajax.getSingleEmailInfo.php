<?php
$email = ($_POST['email']);
$s_sql = "SELECT * FROM sys_emailintegration WHERE emailAddress = ?";
$o_query = $o_main->db->query($s_sql, array($email));
if($o_query && $o_query->num_rows()>0) $emailInfo = $o_query->row_array();

if($emailInfo){
	$contactEmail = ($_POST['contactEmail']);
	$emailNumber = ($_POST['emailNumber']);
	$emailType = ($_POST['emailType']);
	if($emailType == "from"){
 		$mbox = imap_open("{".$emailInfo['emailServerIn']."/imap/ssl}INBOX", $emailInfo['emailAddress'], $emailInfo['emailPassword'], OP_READONLY);

	} else if($emailType == "to"){
		$mbox = imap_open("{".$emailInfo['emailServerOut']."/imap/ssl}SENT ITEMS", $emailInfo['emailAddress'], $emailInfo['emailPassword'], OP_READONLY);

	}
    // $emailBody = imap_fetchbody($mbox, $emailNumber, 1.2);
    $emailHeader = imap_headerinfo($mbox, $emailNumber);
    $structure = imap_fetchstructure($mbox,$emailNumber);

  	// if($emailBody == "") {
  	// 	$emailBody = imap_fetchbody($mbox, $emailNumber, 2);
  	// 	if($emailBody == "") {
	  // 		$emailBody = imap_body($mbox, $emailNumber);
	  // 	}
  	// }
  	$attachments = array();
	if(isset($structure->parts) && count($structure->parts)) {
		for($i = 0; $i < count($structure->parts); $i++) {
			// var_dump($structure->parts[$i]);
			$attachments[$i] = array(
				'is_attachment' => false,
				'filename' => '',
				'name' => '',
				'attachment' => '');

			if($structure->parts[$i]->ifdparameters) {
				foreach($structure->parts[$i]->dparameters as $object) {
					if(strtolower($object->attribute) == 'filename') {
						$attachments[$i]['is_attachment'] = true;
						$attachments[$i]['filename'] = $object->value;
					}
				}
			}

			if($structure->parts[$i]->ifparameters) {
				foreach($structure->parts[$i]->parameters as $object) {
					if(strtolower($object->attribute) == 'name') {
						$attachments[$i]['is_attachment'] = true;
						$attachments[$i]['name'] = $object->value;
					}
				}
			}

			if($attachments[$i]['is_attachment']) {
				$attachments[$i]['attachment'] = imap_fetchbody($mbox, $emailNumber, $i+1);
				if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
					$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
				}
				elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
					$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
				}
			}
		} // for($i = 0; $i < count($structure->parts); $i++)
	} // if(isset($structure->parts) && count($structure->parts))

	$obj_section = $structure;
	$section = "1";
	for ($i = 0 ; $i < 10 ; $i++) {
	    if ($obj_section->type == 0) {
	        break;
	    } else {
	        $obj_section = $obj_section->parts[0];
	        $section.= ($i > 0 ? ".1" : "");
	    }
	}

	$emailBody = imap_fetchbody($mbox, $emailNumber, $section);
	if ($obj_section->encoding == 3) {
	    $emailBody = imap_base64($emailBody);
	} else if ($obj_section->encoding == 4) {
	    $emailBody = imap_qprint($emailBody);
	}
	foreach ($obj_section->parameters as $obj_param) {
	    if (($obj_param->attribute == "charset") && (mb_strtoupper($obj_param->value) != "UTF-8")) {
	        $emailBody = utf8_encode($emailBody);
	        break;
	    }
	}
	function format_html($str) {
	    // $str = htmlentities($str, ENT_COMPAT, "UTF-8");
	    $str = str_replace(chr(10), "<br>", $str);
	    return $str;
	}

    $from = $emailHeader->from;
	$to = $emailHeader->to;
	$elements = iconv_mime_decode($emailHeader->subject);
	echo "<div class='mailHeader'><b>".$elements."</b>
		</br>".$emailHeader->date. "</br>"."From: ";

		foreach($from as $fromSingle) {
			echo $fromSingle->mailbox."@".$fromSingle->host;
		}
		echo " To: ";
		foreach($to as $toSingle) {
			echo $toSingle->mailbox."@".$toSingle->host." ";
		}
	echo "</div>";
	?>
	<div class="mailBody">
		<?php echo format_html($emailBody);?>
	</div>
	<div class="mailAttachements">
		<?php
		foreach($attachments as $attachment) {
			if($attachment['is_attachment']) {
				$filename = iconv_mime_decode($attachment['filename']);
				$attachment = $attachment['attachment'];
				// var_dump($attachment)
				?>
				<a target="_blank" href='<?php echo $_SERVER['PHP_SELF']."/../../modules/$module/output/includes/downloadAttachment.php?emailNumber=$emailNumber&email=$email&contactEmail=$contactEmail&emailType=$emailType&filename=$filename";?>'><?php echo $filename;?></a>
				</br>
				<?php
			}
		}
		?>
	</div>
	<?php
}
?>

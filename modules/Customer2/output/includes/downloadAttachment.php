<?php
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);

include('../../../../elementsGlobal/cMain.php');
$filenameToDownload = $_GET['filename'];
if($filenameToDownload != ""){
	header('Content-Disposition: attachment; filename="'.$filenameToDownload.'"');


	$email = $_GET['email'];
	$s_sql = "SELECT * FROM sys_emailintegration WHERE emailAddress = ?";

	$o_query = $o_main->db->query($s_sql, array($email));
    if($o_query && $o_query->num_rows()>0){
        $emailInfo = $o_query->row_array();
    }
	if($emailInfo){
		$contactEmail = $_GET['contactEmail'];
		$emailNumber = $_GET['emailNumber'];
		$emailType = $_GET['emailType'];

		if($emailType == "from"){
	 		$mbox = imap_open("{".$emailInfo['emailServerIn']."/imap/ssl}INBOX", $emailInfo['emailAddress'], $emailInfo['emailPassword'], OP_READONLY);

		} else if($emailType == "to"){
			$mbox = imap_open("{".$emailInfo['emailServerOut']."/imap/ssl}SENT ITEMS", $emailInfo['emailAddress'], $emailInfo['emailPassword'], OP_READONLY);

		}

	    $structure = imap_fetchstructure($mbox,$emailNumber);

	  	$attachments = array();
		if(isset($structure->parts) && count($structure->parts)) {
			for($i = 0; $i < count($structure->parts); $i++) {
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
	}
	foreach($attachments as $attachment) {
		if($attachment['is_attachment']) {
			$filename = iconv_mime_decode($attachment['filename']);
			$attachment = $attachment['attachment'];
			if($filename == $filenameToDownload){
				echo $attachment;
			}
		}
	}
}
?>

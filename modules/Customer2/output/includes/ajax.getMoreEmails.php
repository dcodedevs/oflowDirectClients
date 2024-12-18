<?php 

$email = ($_POST['email']);

$s_sql = "SELECT * FROM sys_emailintegration WHERE emailAddress = ?";
$o_query = $o_main->db->query($s_sql, array($email));
if($o_query && $o_query->num_rows()>0) $emailInfo = $o_query->row_array(); 


if($emailInfo){
	$contactEmail = $_POST['contactEmail'];
	$emailShown = $_POST['emailShown'];
	$emailType = $_POST['emailType'];
	$perPage = $_POST['perPage'];
	if($emailType == "from"){
 		$mbox = imap_open("{".$emailInfo['emailServerIn']."/imap/ssl}INBOX", $emailInfo['emailAddress'], $emailInfo['emailPassword'], OP_READONLY);
	 	$fromEmailArray = imap_search($mbox, 'FROM "'.$contactEmail.'"');
	} else if($emailType == "to"){
		$mbox = imap_open("{".$emailInfo['emailServerOut']."/imap/ssl}SENT ITEMS", $emailInfo['emailAddress'], $emailInfo['emailPassword'], OP_READONLY);
   	 	$fromEmailArray = imap_search($mbox, 'TO "'.$contactEmail.'"');
	}
    if($fromEmailArray){
    	$showFrom = 0;
    	$fromEmailArray = array_reverse($fromEmailArray);
    	foreach($fromEmailArray as $fromEmailItem) {
    		if($showFrom >= $emailShown ){
    			if($showFrom < ($emailShown+$perPage)){
	        		$headerinfo = imap_headerinfo($mbox, $fromEmailItem);
	        		if(is_object($headerinfo)){
	        			$from = $headerinfo->from;
	        			$to = $headerinfo->to;
	        			$elements = iconv_mime_decode($headerinfo->subject);
	        			
		        		echo "<div class='mailListRow' data-email='".$emailInfo["emailAddress"]."' data-contactemail='".$contactEmail."' data-emailtype='".$emailType."' data-emailnumber='".$fromEmailItem."'><b>".$elements."</b></br>".$headerinfo->date. "</br>"."From: ";
		        		foreach($from as $fromSingle) {
		        			echo $fromSingle->mailbox."@".$fromSingle->host;
		        		}
		        		echo " To: ";
		        		foreach($to as $toSingle) {
		        			echo $toSingle->mailbox."@".$toSingle->host." ";
		        		}
		        		echo "</div>";
		        	}
		        } else {
		        	break;
		        }
        	}
    		$showFrom++;
    	}
    }
}
?>
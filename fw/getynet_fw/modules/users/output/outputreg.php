<?php
header('Content-Type: text/html; charset=utf-8');

if(!function_exists('APIconnectorUser')) require_once(__DIR__."/../../../includes/APIconnector.php");
$includeFile = __DIR__."/../../../languages/default.php";
if(is_file($includeFile)) include($includeFile);
$includeFile = __DIR__."/../../../languages/".$_POST['languageID'].".php";
if(is_file($includeFile)) include($includeFile);

$error = "";
if((isset($_POST['deletetest']) && $_POST['deletetest'] == 1 ) || (isset($_POST['edituser']) && $_POST['formsendtype'] == 2))
{
	$resultcompanyaccess = json_decode(APIconnectorUser("companyaccessdelete", $_COOKIE['username'], $_COOKIE['sessionID'], array('COMPANY_ID'=>$_POST['companyID'], 'USER_ID'=>$_POST['userID'])),true);
	if(!array_key_exists('error',$resultcompanyaccess))
	{
		$data = json_decode(APIconnectorUser("departmentaccessdelete", $_COOKIE['username'], $_COOKIE['sessionID'], array('COMPANYACCESS_ID'=>$_POST['userID'],'COMPANYDEPARTMENT_ID'=>'')),true);
	} else {
		$error = "&error=".urlencode($resultcompanyaccess['error']);
	}

	if(isset($_POST['deletetest']))
		$returnString =$_SERVER['HTTP_REFERER'].$error;
	else
		$returnString = $_POST['fw_domain_url'].($error=="" ? "&folderfile=output" : "&folderfile=outputedit&username=".$_POST['username']."&accessID=".$_POST['accessID'].$error);

	header("Location: ".$returnString);
	exit;
}
if((isset($_POST['deletetestgroup']) && $_POST['deletetestgroup'] == 1))
{
	$resultcompanyaccess = json_decode(APIconnectorUser("groupcompanyaccessdelete", $_COOKIE['username'], $_COOKIE['sessionID'], array('COMPANY_ID'=>$_POST['companyID'], 'GROUP_ID'=>$_POST['groupID'])));

	if(isset($_POST['deletetestgroup']))
		$returnString =$_SERVER['HTTP_REFERER'];
	else
		$returnString =$_POST['returnurl'];

	header("Location: ".$returnString);
	exit;
}
if(isset($_POST['editgroup']))
{
	$v_specific_access = array();
	foreach ($_POST as $key => $value) 
	{
		//module access
		if(stristr($key,"account_"))
		{
			list($r,$accountID,$r,$moduleID) = explode("_",$key);
			//$v_specific_access[$accountID][$moduleID] = $value;
			$tmp = 0;
			foreach($_POST[$key] as $val) $tmp += intval($val);
			$v_specific_access[$accountID][$moduleID] = $tmp;
		}
		if(stristr($key,"accountaccess_"))
		{
			list($r,$accountID) = explode("_",$key);
			$v_specific_access[$accountID]['current'] = $value;
		}
		//dashboard access
		if(stristr($key,"accountdashboard_"))
		{
			list($rest, $accountID, $moduleID) = explode("_",$key);
			$v_specific_access[$accountID]['dashboard'][$moduleID] = intval($_POST[$key]);
		}
		//content access
		if(stristr($key,"content_"))
		{
			list($rest, $l_access_id, $moduleID, $accountID) = explode("_",$key);
			$v_specific_access[$accountID]['content'][$moduleID][$l_access_id] = intval($_POST[$key]);
		}
		//extended content access
		if(stristr($key,"extended_"))
		{
			list($rest, $l_access_id, $moduleID, $accountID) = explode("_",$key);
			$v_extended_access[] = array('account_id'=>$accountID, 'module_id'=>$moduleID, 'access_id'=>$l_access_id, 'accesslevel'=>intval($_POST[$key]));
		}
		//accesselements
		if(stristr($key,"accesselement_"))
		{
			list($r,$accountID,$r,$moduleID) = explode("_",$key);
			$v_specific_access[$accountID]['accesselement'][$moduleID]['allow'] = $_POST[$key];
		}
		if(stristr($key,"accesselementrestrict_"))
		{
			list($r,$accountID,$r,$moduleID) = explode("_",$key);
			$v_specific_access[$accountID]['accesselement'][$moduleID]['restrict'] = $_POST[$key];
		}
	}
	$v_param = array(
		'company_id'=>$_POST['companyID'],
		'group_id'=>$_POST['groupID'],
		'access'=>$v_extended_access
	);
	$v_response_ext = json_decode(APIconnectorUser("contentaccess_extended_set", $_COOKIE['username'], $_COOKIE['sessionID'], $v_param), TRUE);

	$v_param = array(
		'COMPANY_ID'=>$_POST['companyID'],
		'GROUP_ID'=>$_POST['groupID'],
		'GROUPNAME'=>$_POST['groupname'],
		'license_rate_group_id'=>$_POST['license_rate_group_id'],
		'ACCESSLEVEL'=>$_POST['companyaccess'],
		'DEACTIVATED'=>$_POST['deactivated'],
		'SPECIFICACCESS'=>$v_specific_access
	);
	$v_response = json_decode(APIconnectorUser("groupcompanyaccessset", $_COOKIE['username'], $_COOKIE['sessionID'], $v_param), TRUE);

	if($v_response['status'] == 0 || isset($v_response['error']))
	{
		header("Location: ".$_POST['fw_domain_url']."".($_POST['groupID']>0 ? "&groupID=".$_POST['groupID']:"")."&error=".urlencode($v_response_ext['error'].$v_response['error']));
		exit;
	}

	header("Location: ".$_POST['fw_domain_url']."&groupID=".$v_response['groupID']);
	exit;
}

if(isset($_POST['edituser']))
{
	$start = microtime(true);
	$v_specific_access = $v_extended_access = array();
	foreach ($_POST as $key => $value) 
	{
		//module access
		if(stristr($key,"account_"))
		{
			list($r,$accountID,$r,$moduleID) = explode("_",$key);
			//$v_specific_access[$accountID][$moduleID] = $value;
			$tmp = 0;
			foreach($_POST[$key] as $val) $tmp += intval($val);
			$v_specific_access[$accountID][$moduleID] = $tmp;
		}
		if(stristr($key,"accountaccess_"))
		{
			list($r,$accountID) = explode("_",$key);
			$v_specific_access[$accountID]['current'] = $value;
		}
		//dashboard access
		if(stristr($key,"accountdashboard_"))
		{
			list($rest, $accountID, $moduleID) = explode("_",$key);
			$v_specific_access[$accountID]['dashboard'][$moduleID] = intval($_POST[$key]);
		}
		//content access
		if(stristr($key,"content_"))
		{
			list($rest, $l_access_id, $moduleID, $accountID) = explode("_",$key);
			$v_specific_access[$accountID]['content'][$moduleID][$l_access_id] = intval($_POST[$key]);
		}
		//extended content access
		if(stristr($key,"extended_"))
		{
			list($rest, $l_access_id, $moduleID, $accountID) = explode("_",$key);
			$v_extended_access[] = array('account_id'=>$accountID, 'module_id'=>$moduleID, 'access_id'=>$l_access_id, 'accesslevel'=>intval($_POST[$key]));
		}
		//accesselements
		if(stristr($key,"accesselement_"))
		{
			list($r,$accountID,$r,$moduleID) = explode("_",$key);
			$v_specific_access[$accountID]['accesselement'][$moduleID]['allow'] = $_POST[$key];
		}
		if(stristr($key,"accesselementrestrict_"))
		{
			list($r,$accountID,$r,$moduleID) = explode("_",$key);
			$v_specific_access[$accountID]['accesselement'][$moduleID]['restrict'] = $_POST[$key];
		}
	}
	$_POST['username'] = trim($_POST['username']);
	define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');
	$o_main->db->query("DELETE FROM sys_modulemenuusers WHERE username = '".$o_main->db->escape_str($_POST['username'])."'");
	$o_main->db->query("INSERT INTO sys_modulemenuusers(set_id, username) VALUES('".$o_main->db->escape_str($_POST['modulemenuset'])."', '".$o_main->db->escape_str($_POST['username'])."')");

	$v_param = array(
		'companyaccess_id'=>$_POST['userID'],
		'access'=>$v_extended_access
	);
	$v_response_ext = json_decode(APIconnectorUser("contentaccess_extended_set", $_COOKIE['username'], $_COOKIE['sessionID'], $v_param), TRUE);

	$v_param = array(
		'COMPANY_ID'=>$_POST['companyID'],
		'COMPANYACCESS_ID'=>$_POST['userID'],
		'NAME'=>$_POST['first_name'].' '.$_POST['middle_name'].' '.$_POST['last_name'],
		'USERNAME'=>$_POST['username'],
		'ADMIN'=>$_POST['admin'],
		'SYSTEM_ADMIN'=>$_POST['system_admin'],
		'DEVELOPERACCESS'=>$_POST['developeraccess'],
		'ACCESSLEVEL'=>$_POST['companyaccess'],
		'DEACTIVATED'=>$_POST['deactivated'],
		'SPECIFICACCESS'=>$v_specific_access,
		'ACCESSID'=>$_POST['accessID'],
		'MOBILE'=>$_POST['mobile']
	);
	$v_response = json_decode(APIconnectorUser("companyaccessset", $_COOKIE['username'], $_COOKIE['sessionID'], $v_param), TRUE);

	if(isset($v_response['error']))
	{
		header("Location: ".$_POST['fw_domain_url']."&folderfile=outputedit".($v_response['data']>0 ? "&username=".$_POST['username']."&accessID=".$v_response['data'] : "")."&error=".urlencode($v_response['error'].$v_response_ext['error']));
		exit;
	}
	$companyAccessID = $v_response['data'];
	$data = json_decode(APIconnectorUser("departmentaccessdelete", $_COOKIE['username'], $_COOKIE['sessionID'], array('COMPANYACCESS_ID'=>$companyAccessID,'COMPANYDEPARTMENT_ID'=>'')),true);
	reset($_POST);
	foreach ($_POST as $key => $value) 
	{//echo "key =$key<br />";
		if(stristr($key,"departmentcheck_"))
		{
			list($r,$departmentID) = explode("_",$key);
		 	//echo "departmentID = $departmentID<br />";
			$data = json_decode(APIconnectorUser("departmentaccessadd", $_COOKIE['username'], $_COOKIE['sessionID'], array('COMPANYACCESS_ID'=>$companyAccessID,'COMPANYDEPARTMENT_ID'=>$departmentID)),true);
		}
		if(stristr($key,"departmentsetALL_"))
		{
			list($r,$departmentsetID) = explode("_",$key);
		 	//echo "departmentID = $departmentID<br />";
			$data = json_decode(APIconnectorUser("departmentaccessaddset", $_COOKIE['username'], $_COOKIE['sessionID'], array('COMPANYACCESS_ID'=>$companyAccessID,'COMPANYDEPARTMENTSETIDALL'=>$departmentsetID)),true);
		}
	}

	if($_POST['formsendtype'] == 1)
	{
		/*$invitationresponse = json_decode(APIconnectorUser("sendinvitation", $_COOKIE['username'], $_COOKIE['sessionID'],array("USER"=>$_POST['username'], "USERID"=>$companyAccessID,"COMPANY_ID"=>$_POST['companyID'],"FULLNAME"=> $_POST['fullname'],"HELLOTEXT"=>$formText_emailInvitationHello_usersOutputLink,"INVITATIONMESSAGE1"=>urlencode($formText_emailInvitationMessage1_usersOutputLink),"INVITATIONMESSAGE2"=>urlencode($formText_emailInvitationMessage2_usersOutputLink),"SPECIALTEXT"=>'0',"DONTSHOWENDTEXT"=>0,"INVITATIONMESSAGE3"=>urlencode($formText_emailInvitationMessage3_usersOutputLink),"INVITATIONMESSAGE4"=>urlencode($formText_emailInvitationMessage4_usersOutputLink),"INVITATIONMESSAGE5"=>urlencode($formText_emailInvitationMessage5_usersOutputLink),"INVITATIONMESSAGE6"=>urlencode($formText_emailInvitationMessage6_usersOutputLink),"INVITATIONMESSAGEREGISTERED1"=>urlencode($formText_emailInvitationMessageRegistered1_usersOutputLink),"INVITATIONMESSAGEREGISTERED2"=>urlencode($formText_emailInvitationMessageRegistered2_usersOutputLink),"MESSAGE_GETYNET_SLOGAN"=>$formText_emailInvitationSlogan_usersOutputLink,"SENDEREMAIL"=>$_POST['editedBy'])));*/

		$o_query = $o_main->db->query("SELECT * FROM accountinfo_invitation_basisconfig WHERE name = 'user_administration'");
		$v_invitation_config = $o_query ? $o_query->row_array() : array();
		$o_query = $o_main->db->query("SELECT * FROM accountinfo_invitation_accountconfig WHERE name = 'user_administration'");
		if($o_query && $o_query->num_rows()>0)
		{
			$v_row = $o_query ? $o_query->row_array() : array();
			if($v_row['activate_accountconfig'] == 1)
			{
				$v_invitation_config = $v_row;
			}
		}


		$v_param = array('companyaccessID' => (isset($_POST['caID'])?$_POST['caID']:$_COOKIE[$_GET['accountname'].'_caID']), 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
		$o_query = $o_main->db->get_where('session_framework', $v_param);
		$fw_session = $o_query ? $o_query->row_array() : array();

		$v_logo = json_decode($v_invitation_config['getynet_logo'], TRUE);
		$v_partner_logo = json_decode($v_invitation_config['partner_logo'], TRUE);

		$s_logo = $s_partner_logo = '';
		$s_file = $v_logo[0][1][0];
		if(is_file(__DIR__.'/../../../../../'.$s_file))
		{
			$s_type = pathinfo(__DIR__.'/../../../../../'.$s_file, PATHINFO_EXTENSION);
			$s_data = file_get_contents(__DIR__.'/../../../../../'.$s_file);
			$s_logo = 'image/' . $s_type . ';base64,' . base64_encode($s_data);
		}
		$s_file = $v_partner_logo[0][1][0];
		if(is_file(__DIR__.'/../../../../../'.$s_file))
		{
			$s_type = pathinfo(__DIR__.'/../../../../../'.$s_file, PATHINFO_EXTENSION);
			$s_data = file_get_contents(__DIR__.'/../../../../../'.$s_file);
			$s_partner_logo = 'image/' . $s_type . ';base64,' . base64_encode($s_data);
		}

		$v_param = array(
			'COMPANYACCESS_ID' => $companyAccessID,
			'EMAIL' => $_POST['username'],
			'FIRST_NAME' => $_POST['first_name'],
			'MIDDLE_NAME' => $_POST['middle_name'],
			'LAST_NAME' => $_POST['last_name'],
			'MOBILE_PREFIX' => $_POST['mobile_prefix'],
			'MOBILE' => $_POST['mobile'],
			'INVITATION_TEXT' => $v_invitation_config['text'],
			'SENDER_FROM_NAME' => $v_invitation_config['sender_from_name'],
			'SENDER_FROM_EMAIL' => $v_invitation_config['sender_from_email'],
			'SHOW_SENDER_PERSON_IN_FOOTER' => $v_invitation_config['show_sender_person_in_footer'],
			'COMPANY_NAME' => $v_invitation_config['company_name'],
			'PARTNER_LOGO' => $s_partner_logo,
			'GETYNET_LOGO' => $s_logo,
			'VERIFY_MOBILE' => $v_invitation_config['ask_for_mobile_verification'],
			'LANGUAGE_ID' => $fw_session['accountlanguageID']
		);
		$invitationresponse = json_decode(APIconnectorUser('send_invitation_v2', $_COOKIE['username'], $_COOKIE['sessionID'], $v_param));
	}

	header("Location: ".$_POST['fw_domain_url']."&folderfile=output");
	exit;
}
if(isset($_POST['profileedit']))
{
	if($_FILES['profileimage']['name'] != "" && $_FILES['profileimage']['error'] == 0 )
	{
		$img_src = $_FILES['profileimage']['tmp_name'];
		$newImage = uploadingfile('profileimage');
		$fp = fopen($img_src, 'r');
		$data = fread($fp, filesize($img_src));
		//$data = addslashes($data);
		fclose($fp);
		//APIconnectorServer("profileimageadd",'pics.getynet.com','!xPe6yTti!09@P5tivFS', array('IMAGEDATA'=>$data));
		//echo "data = $data";
		//print_r($_POST);
		$local_directory=__DIR__.'/local_files/';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_URL, 'https://pics.getynet.com/serverapi/commands/profileimageadd.php' );
		//most importent curl assues @filed as file field
		$foldername =  ereg_replace("[^[a-zA-Z0-9._\-]","",$_POST['name']);
		$post_array = array(
			"profileimage"=>"@".$img_src,
			"upload"=>"Upload",
			"foldername"=>strtolower($foldername[0]),
			"oldprofileimage"=>urldecode($_POST['oldprofileimage']),
			"filetype"=>substr($_FILES['profileimage']['name'],strrpos($_FILES['profileimage']['name'],"."))
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_array);
		$response = curl_exec($ch);
		//json_decode($response,true);
		// echo "response = $response";exit;
		//file_put_contents( "../../../../tmp/testtest.jpg",$data);
		//exit;
	} else {
		$response=urldecode($_POST['oldprofileimage']);
	}

	//TODO: update session_framework cache
	$resultcompanyaccess = json_decode(APIconnectorUser("userprofileset", $_COOKIE['username'], $_COOKIE['sessionID'],
		array(
		'NAME'=>$_POST['name'],
		'TEXT'=>$_POST['text'],
		'IMAGE'=>$response,
		'IMAGEEXT'=>$newImageExt,
		'PHONE'=>$_POST['phone'],
		'MOBILE'=>$_POST['mobile'],
		'DATEOFBORN'=>$_POST['dateofborn'],
		'EMPLOYERS'=>$_POST['employers'],
		'TITLE'=>$_POST['title'],
		'PHONEWORK'=>$_POST['phonework'],
		'MOBILEWORK'=>$_POST['mobilework'],
		'EMAILWORK'=>$_POST['emailwork'],
		'EMAILPRIVATE'=>$_POST['emailprivate'],
		'COUNTRY'=>$_POST['country'],
		'LANGUAGE'=>$_POST['language'],
		'IPADDRESSCHECK'=>(isset($_POST['IPAddressCheck']) ? '1' : '0')
		)
	));
}

$returnString = ($_POST['returnurl'] != "" ? $_POST['returnurl'] : $_SERVER['HTTP_REFERER']);
header("Location: ".$returnString);
exit;




function uploadingFile($filefieldname)
{
	if (in_array($extension, $allowedExts))
	{
		if ($_FILES[$filefieldname]["error"] > 0)
		{
			echo "Return Code: " . $_FILES[$filefieldname]["error"] . "<br>";
		} else {
			$newname =  "../../../../tmp/".$randomnumber."". substr($_FILES[$filefieldname]["name"],strrpos($_FILES[$filefieldname]["name"],"."));

			move_uploaded_file($_FILES[$filefieldname]["tmp_name"],$newname);
			umask(0);
			chmod($newname,0777);
		}
		return($newname);
	} else {
		// echo "Invalid file";
		return("ERROR: INVALID FILE");
	}
}
?>

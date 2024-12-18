<?php 
/*
** Content status:
	0 - active
	1 - inactive
	2 - deleted
	3 - history
*/
$v_return = array();
session_start();
define('BASEPATH', realpath(__DIR__.'/../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
require_once(__DIR__."/../../../ftpConnect.php");
if(!function_exists("ftp_file_put_content")) include(__DIR__."/includes/ftp_commands.php");
if(!function_exists("log_action")) include(__DIR__."/includes/fn_log_action.php");
include(__DIR__."/includes/readInputLanguage.php");

$s_secure = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : ""; 
list($s_protocol,$s_rest) = explode("/", strtolower($_SERVER["SERVER_PROTOCOL"]),2); 
$l_port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
$s_account_url = $s_protocol.$s_secure."://".$_SERVER['SERVER_NAME'].$l_port."/accounts/".$_GET['accountname']."/";

$error_msg = array();
$b_return_url = false;
$o_query = $o_main->db->query('SELECT * FROM session_framework WHERE companyaccessID = ? AND session = ? AND username = ?', array($_GET['caID'], $_COOKIE['sessionID'], $_COOKIE['username']));
if($o_query && $fw_session = $o_query->row_array())
{
	$menuaccess = json_decode($fw_session['cache_menu'], true);
	$access = $menuaccess[$_POST['module']][2];
	$b_owner_access = ($menuaccess[$_POST['module']][6] == 1 ? true : false);
	if($fw_session['developeraccess'] >= 20)
	{
		$access = 111;
		$b_owner_access = false;
	}
} else {
	$access = 0;
	$b_owner_access = false;
}
$username = $_COOKIE['username'];

$s_default_output_language = '';
$o_query = $o_main->db->query('SELECT languageID FROM language ORDER BY defaultOutputlanguage DESC, outputlanguage DESC, sortnr ASC');
if($o_query && $o_row = $o_query->row()) $s_default_output_language = $o_row->languageID;
$module = $_POST['module'];
$submodule = $_POST['submodule'];
$languageID = $_POST['languageID'];
$ID = intval($_POST['ID']);
$parentID = $_POST['parentID'];

if($o_main->db->table_exists($submodule))
{
	// check owner access
	if($b_owner_access && $ID > 0)
	{
		$o_query = $o_main->db->query('SELECT createdBy FROM '.$submodule.' WHERE id = ?', array($ID));
		if($o_query && $o_row = $o_query->row())
		{
			if($o_row->createdBy != $username) $access = $access % 10;
		}
	}
	
	if($access >= 10)
	{
		$thisFile = "contentreg";
		$insertStatus = 0;
		if($_POST['parentdir'] != '')
			$extradir ="..";
				
		include(__DIR__."/includes/fieldloader.php");
		
		$b_do_history = false;
		$v_history = array();
		foreach($databases as $basetable)
		{
			if($basetable->name == $submodule)
			{
				$basetable->ID = $ID;
			}
			
			$fields = $basetable->load($fields,$languageID,$error_msg);
			for($z = 0; $z < sizeof($basetable->langfields); $z++)
			{
				if($basetable->langfields[$z] != "all" && $ID > 0 && $basetable->name != $submodule)
				{
					$v_content_check = array($submodule.'ID' => $ID, 'languageID' => $basetable->langfields[$z]);
				} else if($ID == 0) {
					$v_content_check = array('1' => 2);
				} else {
					$v_content_check = array('id' => $ID);
				}
				$o_query = $o_main->db->get_where($basetable->name, $v_content_check);
				if(!$o_query || ($o_query && $o_query->num_rows() == 0))
				{
					if(is_file(__DIR__."/includes/insertquery.php"))
					{
						include(__DIR__."/includes/insertquery.php");
					}
				} else {
					$v_content = $o_query->row_array();
					if(is_file(__DIR__."/includes/updatequery.php"))
					{
						include(__DIR__."/includes/updatequery.php");
					}
				}
			}
		}
		
		if($b_do_history)
		{
			foreach($databases as $basetable)
			{ 
				foreach($v_history[$basetable->name] as $v_data)
				{
					if($basetable->multilanguage == 1)
					{
						$o_main->db->insert($basetable->name, $v_data);
						$l_content_id = $o_main->db->insert_id();
						$v_data = array(substr($basetable->name, 0, strrpos($basetable->name, 'content')).'ID' => $l_history_content_id);
						$o_main->db->update($basetable->name, $v_data, array('id' => $l_content_id));
					} else {
						$o_main->db->insert($basetable->name, $v_data);
						$l_history_content_id = $o_main->db->insert_id();
					}
				}
			}
		}
		
		foreach($databases as $basetable)
		{
			foreach($basetable->fieldNums as $nums)
			{
				if(is_file(__DIR__."/fieldtypes/".$fields[$nums][4]."/post.php"))
				{
					include(__DIR__."/fieldtypes/".$fields[$nums][4]."/post.php");
				}
			}
		}
		
		if(is_file(__DIR__."/../addOn_Contentreg/addOn_Contentreg.php"))
		{
			include(__DIR__."/../addOn_Contentreg/addOn_Contentreg.php");
		}
		if($_POST['firedbutton'] == 'save2' && is_file(__DIR__."/../addOn_Contentreg/addOn_Contentreg_save2.php"))
		{
			include(__DIR__."/../addOn_Contentreg/addOn_Contentreg_save2.php");
		}
		$s_return_url = substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=$module&ID=".$databases[$submodule]->ID."&includefile=edit&submodule=$submodule";
		
		if($_POST['firedbutton'] == "duplicate")
		{
			foreach($databases as $basetable)
			{
				for($z=0; $z < sizeof($basetable->langfields); $z++)
				{
					if(is_file(__DIR__."/includes/insertquery.php"))
					{
						include(__DIR__."/includes/insertquery.php");
					}
				}
			}
			foreach($databases as $basetable)
			{ 
				foreach($basetable->fieldNums as $nums)
				{
					if(is_file(__DIR__."/fieldtypes/".$fields[$nums][4]."/post.php"))
					{
						include(__DIR__."/fieldtypes/".$fields[$nums][4]."/post.php");
					}
				}
			}
			$s_return_url .= "&includefile=edit&ID=".$databases[$submodule]->ID."";
			$b_return_url = true;
		}
		else if($parentmodule != "")
		{
			foreach($fields as $field)
			{
				if($field[4] == 20 && $field[6]['all'] != 0 && $field[6]['all'] != "")
				{
					$parentID = $field[6]['all'];
					$parentmodule = $field[5];
				}
			}
			$s_return_url = substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=$module&submodule=$parentmodule&includefile=edit&ID=$parentID";
		}
		else
		{
			$b_return_url = true;
		}
		
		if(isset($_POST['menulevellevel']) and intval($_POST['menulevellevel'])>0)
		{
			$s_return_url .= "&level=".intval($_POST['menulevellevel']);
		}
		if(isset($_POST["content_status_filter"]))
		{
			$s_return_url .= "&content_status=".$_POST["content_status_filter"];
		}
		
		if(isset($_POST['return_url']))
		{
			if($b_return_url)
			{
				$s_return_url .= "&return_url=".urlencode($_POST['return_url']);
			} else {
				$redirect_link = base64_decode($_POST['return_url']);
			}
		}
		
		if(isset($redirect_link))
		{
			$s_return_url = $redirect_link.'&returl='.urlencode($s_return_url);
		}
		
		if($_POST['form_type'] == 'main')// || ($_POST['form_type'] == 'subcontent' && ($_POST['firedbutton'] == "duplicate" || $_POST['firedbutton'] == "save2")))
		{
			$v_return["url"] = $s_return_url;
		}
		
	} else {
		$error_msg["error_".count($error_msg)] = $formText_YouHaveNoAccessToSaveThisContent_input;
	}
} else {
	$error_msg["error_".count($error_msg)] = $formText_IncorrectOrMissingParameters_input;
}
// return error messages
if(count($error_msg)>0)
{
	$v_return["error"] = $error_msg;
	log_action("content_save_fail");
} else {
	if(!isset($_POST['no_success_msg']))
	{
		//single array error messages reported by JS alert() - do not use single and double quote;
		$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
		$o_main->db->update('session_framework', array('error_msg' => json_encode(array("info_1"=>$formText_ContentIsSaved_input))), $v_param);
	}
	log_action("content_save");
}

print json_encode($v_return);
exit;
?>
<?php
session_start();
define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');

include(__DIR__."/readInputLanguage.php");

$s_sql = "SELECT * FROM session_framework WHERE companyaccessID = '".$o_main->db->escape_str($_GET['caID'])."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."'".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")." LIMIT 1";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0)
{
	$currentMenulevel = intval($_POST['choosenMenulevel']);
	
	$output = array();
	if($currentMenulevel > 0)
	{
		$output['html'] = '';
		$s_sql = "SELECT *, m.id FROM menulevel AS m 
		LEFT OUTER JOIN menulevelcontent AS mc ON mc.menulevelID = m.id AND mc.languageID = '".$o_main->db->escape_str($choosenListInputLang)."'
		WHERE m.parentlevelID = '".$o_main->db->escape_str($currentMenulevel)."'".($o_main->multi_acc?" AND m.account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")." AND m.content_status < 2
		ORDER BY m.sortnr";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && $menulevelChilds = $o_query->result_array())
		{
			if(count($menulevelChilds) > 0)
			{
				$output['html'] = '<div class="selectDiv">
					<div class="selectDivWrapper">
						<select name="filter" class="filterBy">
							<option value="0">'.$formText_All_inputFilter.'</option>';
				foreach($menulevelChilds as $menulevelChild)
				{
					$selected = "";	
					if($menulevelChild['id'] == $_SESSION['filter2'] || $menulevelChild['id'] == $_SESSION['filter3']) $selected = 'selected';			
					$output['html'] .=  '<option value="'.$menulevelChild['id'].'"'.$selected.'>'.$menulevelChild['levelname'].'</option>';
				} 
				$output['html'] .='</select>
						<div class="arrowDown"></div>
					</div>
				</div>';
			}
		}
	} else {	
		$output['html'] = '';
	}
} else {
	$output['html'] = $formText_YouHaveNoAccessToThisModule_input;
}

echo json_encode($output);

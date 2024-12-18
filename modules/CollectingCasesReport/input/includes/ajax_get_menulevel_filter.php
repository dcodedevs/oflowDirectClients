<?php
session_start();
define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
include(__DIR__."/readInputLanguage.php");

$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
$o_query = $o_main->db->get_where('session_framework', $v_param);
if($o_query && $o_query->num_rows()>0)
{
	$currentMenulevel = intval($_POST['choosenMenulevel']);
	
	$output = array();
	if($currentMenulevel > 0)
	{
		$output['html'] = '';
		$getMenuChilds = "SELECT *, menulevel.id FROM menulevel 
		LEFT OUTER JOIN menulevelcontent ON menulevelcontent.menulevelID = menulevel.id AND menulevelcontent.languageID =".$o_main->db->escape($choosenListInputLang)."
		WHERE content_status < 2 AND menulevel.parentlevelID = ".$currentMenulevel." ORDER BY menulevel.sortnr";
		$o_query = $o_main->db->query($getMenuChilds);
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
?>
<?php
function get_menulevel_parrents($levelID, $langID)
{
	$o_main = get_instance();
	if($levelID > 0)
	{
		$row = array();
		$o_query = $o_main->db->query("select ml.id, mlc.levelname from menulevel ml join menulevelcontent mlc on mlc.menulevelID = ml.id and mlc.languageID = ? join menulevel ml2 on ml.id = ml2.parentlevelID where ml2.id = ?", array($langID, $levelID));
		if($o_query && $o_query->num_rows()>0) $row = $o_query->row_array();
		return get_menulevel_parrents($row['id'], $langID).$row['levelname']." ";
	}
	return '';
}
?>
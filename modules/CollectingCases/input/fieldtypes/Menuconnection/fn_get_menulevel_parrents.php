<?php
function get_menulevel_parrents($levelID, $langID, $splitter = ' ')
{
	$o_main = get_instance();
	$splitter = trim($splitter);
	if('' == $splitter) $splitter = ' ';
	if($levelID > 0)
	{
		$row = array();
		$o_query = $o_main->db->query('select ml.id, mlc.levelname from menulevel ml join menulevelcontent mlc on mlc.menulevelID = ml.id and mlc.languageID = ? join menulevel ml2 on ml.id = ml2.parentlevelID where ml2.id = ? and ml.content_status < 2', array($langID, $levelID));
		if($o_query && $o_query->num_rows()>0) $row = $o_query->row_array();
		return get_menulevel_parrents($row['id'], $langID, $splitter).$row['levelname'].$splitter;
	}
	return '';
}
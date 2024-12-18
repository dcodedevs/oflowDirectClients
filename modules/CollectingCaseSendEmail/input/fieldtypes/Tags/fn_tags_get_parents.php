<?php
function tags_get_parents($id, $s_default_output_language)
{
	$ret="";
	$o_main = get_instance();
	$o_query = $o_main->db->query("SELECT t.parentID, IF(tc.tagname<>'',tc.tagname,t.name) name FROM sys_tag t JOIN sys_tagcontent tc ON tc.sys_tagID = t.id AND tc.languageID = ? WHERE t.id = ? ORDER BY t.sortnr;", array($s_default_output_language, $id));
	if($o_query && $o_query->num_rows()>0)
	{
		$row = $o_query->row_array();
		$ret = tags_get_parents($row['parentID'], $s_default_output_language);
		if($ret!="") $ret .= " - ";
		$ret .= $row['name'];
	}
	return $ret;
}
?>
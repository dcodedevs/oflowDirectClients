<?php
function get_menulevel_parrents($levelID, $langID)
{
	if($levelID > 0)
	{
		$row = mysql_fetch_assoc(mysql_query("select ml.id, mlc.levelname from menulevel ml join menulevelcontent mlc on mlc.menulevelID = ml.id and mlc.languageID = '$langID' join menulevel ml2 on ml.id = ml2.parentlevelID where ml2.id = '$levelID'"));
		return get_menulevel_parrents($row['id'], $langID).$row['levelname']." ";
	}
	return '';
}
?>
<?php
//allow to delte if content are not assigned
$o_query = $o_main->db->query('SELECT id FROM pageID WHERE menulevelID = ? AND deleted != 1', array($deleteFieldID));
if($o_query && $o_query->num_rows()>0)
{
	$error_msg[] = $formText_FoundContentAssignedToThisMenupointUnasignOrDeleteIt_fieldtype;
}
$o_query = $o_main->db->query('SELECT id FROM menulevel WHERE parentlevelID = ?', array($deleteFieldID));
if($o_query && $o_query->num_rows()>0)
{
	$error_msg[] = $formText_FoundChildLevelsForThisMenupointDeleteThemFirst_fieldtype;
}
?>
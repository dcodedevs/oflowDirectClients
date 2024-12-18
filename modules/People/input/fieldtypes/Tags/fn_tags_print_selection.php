<?php
function tags_print_selection($parentID,$deep,$className,$function,$selectedtagid,$s_default_output_language)
{
	$o_main = get_instance();
	$o_query = $o_main->db->query("SELECT t.id, IF(tc.tagname<>'',tc.tagname,t.name) name FROM sys_tag t JOIN sys_tagcontent tc ON tc.sys_tagID = t.id AND tc.languageID = ? WHERE t.parentID = ? ORDER BY t.sortnr", array($s_default_output_language, $parentID));
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $row)
	{
		if($row['id']==$selectedtagid) continue;
		print '<div style="padding-left:'.($deep*20).'px;" class="tag_popup_item_'.$className.'"><a href="javascript:;" onClick="do_action_'.$className.'(this,\''.$function.'\');">'.$row['name'].'<input type="hidden" class="tagname" value="'.$row['name'].'" /><input type="hidden" class="tagid" value="'.$row['id'].'" /><input type="hidden" class="selectedtagid" value="'.$selectedtagid.'" /></a></div>';
		tags_print_selection($row['id'],$deep+1,$className,$function,$selectedtagid,$s_default_output_language);
	}
}
?>
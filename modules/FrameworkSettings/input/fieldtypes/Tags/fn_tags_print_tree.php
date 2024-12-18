<?php
function tags_print_tree($parentID,$deep,$className,$addButton,$renameButton,$moveButton,$mergeButton,$deleteButton,$s_default_output_language)
{
	$o_main = get_instance();
	$o_query = $o_main->db->query("SELECT t.id, IF(tc.tagname<>'',tc.tagname,t.name) name FROM sys_tag t JOIN sys_tagcontent tc ON tc.sys_tagID = t.id AND tc.languageID = ? WHERE t.parentID = ? ORDER BY t.sortnr;", array($s_default_output_language, $parentID));
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $row)
	{
		print '<div style="padding-left:'.($deep*20).'px;" class="tag_popup_item_'.$className.'"><a href="javascript:;" class="tag_popup_item_pick_'.$className.'">'.$row['name'].'<input type="hidden" class="tagname" value="'.$row['name'].'" /><input type="hidden" class="tagid" value="'.$row['id'].'" /><input type="hidden" class="tagparent" value="'.$parentID.'" /><input type="hidden" class="taglangID" value="no" /></a> <a class="'.$className.'_btn tag_popup_item_add_'.$className.'">'.$addButton.'</a> <a class="'.$className.'_btn tag_popup_item_rename_'.$className.'">'.$renameButton.'</a> <a class="'.$className.'_btn tag_popup_item_move_'.$className.'">'.$moveButton.'</a> <a class="'.$className.'_btn tag_popup_item_merge_'.$className.'">'.$mergeButton.'</a> <a class="'.$className.'_btn tag_popup_item_delete_'.$className.'">'.$deleteButton.'</a></div>';
		tags_print_tree($row['id'],$deep+1,$className,$addButton,$renameButton,$moveButton,$mergeButton,$deleteButton,$s_default_output_language);
	}
}
?>
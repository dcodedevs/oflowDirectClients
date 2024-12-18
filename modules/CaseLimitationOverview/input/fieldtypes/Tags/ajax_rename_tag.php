<?php
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
$v_output_languages = $values = array();
$o_query = $o_main->db->query('SELECT languageID, name FROM language WHERE outputlanguage = 1 ORDER BY defaultOutputlanguage DESC, sortnr ASC');
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $row)
{
	$v_output_languages[$row['languageID']] = $row['name'];
}
if(isset($_POST['action']) and $_POST['action'] == 'rename')
{
	$o_main->db->query("update sys_tag set name = ? where id = ?", array($_POST['tagname_'.$_POST['s_default_output_language']], $_POST['selectedtagid']));
	foreach($v_output_languages as $key => $name)
	{
		$o_main->db->query("update sys_tagcontent set tagname = ? where sys_tagID = ? AND languageID = ?", array($_POST['tagname_'.$key], $_POST['selectedtagid'], $key));
	}
} else {
	$o_query = $o_main->db->query('SELECT tc.tagname, tc.languageID FROM sys_tag t JOIN sys_tagcontent tc ON tc.sys_tagID = t.id WHERE t.id = ?', array($_GET['selectedtagid']));
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $row)
	{
		$value[$row['languageID']] = $row['tagname'];
	}
	?>
	<div class="tag_popup_title_<?php echo $_GET['className'];?>"><?php echo $_GET['label_RenameTitle'];?></div>
	<div class="tag_popup_item_<?php echo $_GET['className'];?>"><?php
		foreach($v_output_languages as $key => $name)
		{
			?><div class="<?php echo $_GET['className'];?>_row"><span><?php echo $_GET['label_Tagname']." ({$name}): ";?></span><input type="text" class="<?php echo $_GET['className'].'_tagname_'.$key;?>" name="<?php echo $_GET['className'].'_tagname_'.$key;?>" value="<?php echo htmlspecialchars($value[$key]);?>" /></div><?php
		}
		?>
		<div class="<?php echo $_GET['className'];?>_btn">
			<a class="<?php echo $_GET['className'];?>_btn bold script" href="javascript:;" onClick="do_action_<?php echo $_GET['className'];?>(this,'rename');">
				<?php echo $_GET['label_RenameButton'];?>
			<input type="hidden" class="tagid" value="0">
			<input type="hidden" class="selectedtagid" value="<?php echo $_GET['selectedtagid'];?>">
			</a>
			&nbsp;&nbsp;&nbsp;&nbsp;<a class="<?php echo $_GET['className'];?>_btn bold script" href="javascript:;" onClick="open_tag_popup_<?php echo $_GET['className'];?>();"><?php echo $_GET['label_CancelButton'];?></a>
		</div>
	</div><?php
}

<?php
$formText_TestLanguageVariable_Output="";
$protectFilter = "";
$s_table = $variables->contentTable;
//if($variables->loggID=="") $s_protect = ' AND '.$s_table.'.protected <> 1';
if($o_main->db->table_exists($s_table.'content'))
{
	$s_sql = 'SELECT c.id cid, c.*, cc.* FROM '.$s_table.' c JOIN '.$s_table.'content cc ON cc.'.$s_table.'ID = c.id AND cc.languageID = '.$o_main->db->escape($variables->languageID).' WHERE c.id = '.$o_main->db->escape($variables->contentID).$s_protect;
} else {
	$s_sql = 'SELECT c.id cid, c.* FROM '.$s_table.' c WHERE c.id = '.$o_main->db->escape($variables->contentID).$s_protect;
}
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) $o_content = $o_query->row();
?>
<div class="textarea">
	<div id="textarea-wrap" class="textarea-wrap">
		<h1 class="header"><?php echo $o_content->header;?></h1>
		<div class="text">
			<?php if($o_content->ingress!="") {?><div class="ingress"><?php echo $o_content->ingress;?></div><?php } ?>
			<?php if($o_content->text!="") {?><div><?php echo $o_content->text;?></div><?php } ?>
		</div>
	</div>
</div>
<?php																																			
// ali - image processing template
/*
if($o_content->image != "")
{
	?><td valign="top" style="padding-left:20px; width:250px;"><?php
	$i = 0;
	$data = json_decode($o_content->image);
	foreach($data as $obj)
	{
		//$name = (string)$obj[0];
		$img = (array)$obj[1];
		//$labels = (array)$obj[2];
		$tmp = array();
		foreach($labels as $label)
		{
			$tmp[] = html_entities_decode($label); 
		}
		$labels = $tmp;
		?>
		<div class="image_item" style=" <?php if($i>0) print 'margin-top:25px;';?>">
			<img border="0" src="<?php print $img[0];?>" />
		</div>
		<?php
		$i++;
	}
	?></td><?php
} */
?>
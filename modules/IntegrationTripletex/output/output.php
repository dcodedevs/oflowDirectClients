<?php
$formText_TestLanguageVariable_output="";
$protectFilter = "";
$contentTable = $variables->contentTable;
//if($variables->loggID=="") $protectFilter = "AND $contentTable.protected <> 1";
$contentSQL = "SELECT $contentTable.id cid, $contentTable.*, {$contentTable}content.* FROM $contentTable, {$contentTable}content WHERE $contentTable.id = '{$variables->contentID}' AND {$contentTable}content.{$contentTable}ID = $contentTable.id AND {$contentTable}content.languageID = '{$variables->languageID}' $protectFilter;";
if(!$findContent = mysql_query($contentSQL))
{
	$contentSQL = "SELECT $contentTable.id cid, $contentTable.* FROM $contentTable WHERE $contentTable.id = '{$variables->contentID}' $protectFilter;";
	$findContent = mysql_query($contentSQL);
}
$content = mysql_fetch_array($findContent);
?>
<div class="textarea">
	<div id="textarea-wrap" class="textarea-wrap">
		<h1 class="header"><?php echo $content['header'];?></h1>
		<div class="text">
			<?php if($content['ingress']!="") {?><div class="ingress"><?php echo $content['ingress'];?></div><?php } ?>
			<?php if($content['text']!="") {?><div><?php echo $content['text'];?></div><?php } ?>
		</div>
	</div>
</div>
<?php																																			
// ali - image processing template
/*
if($content['image'] != "")
{
	?><td valign="top" style="padding-left:20px; width:250px;"><?php
	$i = 0;
	$data = json_decode($content['image']);
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
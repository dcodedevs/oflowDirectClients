<?php
$options = explode("::",$field[11]);
$doc = new DOMDocument();
if($field[6][$langID] != '') @$doc->loadHTML($field[6][$langID]);
else @$doc->loadHTML('');
foreach($doc->getElementsByTagName('img') as $tag)
{
	$s_r = '/accounts/'.$_GET['accountname'];
	$s_src = $s_src_new = $tag->getAttribute('src');
	if(substr($s_src_new, 0, 8) == "uploads/") $s_src_new = "/".$s_src_new;
	if(substr($s_src_new, 0, strlen($s_r)) != $s_r) $s_src_new = $s_r.$s_src_new;
	$field[6][$langID] = str_replace('src="'.$s_src.'"', 'src="'.$s_src_new.'"', $field[6][$langID]);
}
foreach($doc->getElementsByTagName('a') as $tag)
{
	$s_href = $tag->getAttribute('href');
	if(substr($s_href, 0, 8) == "uploads/")
	{
		$field[6][$langID] = str_replace('href="'.$s_href.'"', 'href="'.'/accounts/'.$_GET['accountname'].'/'.$s_href.'"', $field[6][$langID]);
	}
}
if($access>=10)
{
	?><textarea <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" name="<?php echo $field[1].$ending;?>"><?php
	echo $field[6][$langID];
	?></textarea>
	<script type="text/javascript">
	<?php if(isset($ob_javascript)) { ob_start(); } ?>
	CKEDITOR.replace(
		'<?php echo $field_ui_id;?>',
		{
			//baseHref : '<?php print $accountdir; ?>/'
			baseHref : ''
			<?php
			foreach($options as $option)
			{
				if($option == 'enter_br') print ',enterMode : CKEDITOR.ENTER_BR'.PHP_EOL;
			}
			?>,
			removePlugins : 'floating-tools',
			extraAllowedContent: '*[*]{*}(*)'
		}
	);
	<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
	</script><?php
} else {
	?><input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="hidden" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>"/><?php
	print $field[6][$langID];
}
?>
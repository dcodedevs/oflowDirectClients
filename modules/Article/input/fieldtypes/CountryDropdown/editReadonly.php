<?php 
if( intval($_GET['relationID']) && $_GET['relationfield'] )
{
	$parentStatus = array_shift(mysql_fetch_assoc(mysql_query("SELECT prospectStatus FROM customers WHERE id = '".intval($_GET['relationID'])."';")));
}

$options = explode(":::",$field[11]);
$options = explode("::",$options[0]);

//$options = explode("::",$field[11]);
foreach($options as $option)
{
	$val = explode(":",$option);
	$txt[$val[0]] = $val[1];
}
?>
<input value="<?=(intval($_GET['ID']))?$field[6][$langID]:$parentStatus;?>" name="<?=$field[1].$ending;?>" type="hidden" />
<?php if($showStatus != 2){ ?><div class="<?php if($textBeforeOrAbove == 1){ echo "oneinput"; }else{ echo "twoinput"; }  ?>"><span class="fieldname" style="font-size:11px;"><strong><?=$field[2];?></strong> </span><?=$languageName[$langID];?></div><?php } ?>
<div <?php if($showStatus != 0){ ?> style="width:30%;"<?php } ?> class="<?php if($textBeforeOrAbove == 1){ echo "onefield"; }else{ echo "twofield"; }  ?>"><?=(intval($_GET['ID']))?$txt[$field[6][$langID]]:$txt[$parentStatus];?></div>
<div style="clear:both;"></div>
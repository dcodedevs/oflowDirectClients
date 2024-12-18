<?php
$varName = $finList."ListArray";
$values = explode('¤',$writeContent[$finList]);
foreach($values as $key=>$value)
{
	$values[$key] = (isset(${$varName}) ? ${$varName}[$value] : $value);
}
if($nameDirectLink == 1)
{
	?><a href="<?php echo $linkText;?>" class="optimize"><?php echo implode('<br/>',$values);?></a><?php
} else {
	print implode('<br/>',$values);
}

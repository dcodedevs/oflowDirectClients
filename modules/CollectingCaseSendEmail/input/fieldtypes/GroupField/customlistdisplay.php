<?php
if($nameDirectLink == 1)
{
	?><a href="<?php echo $linkText;?>" class="optimize"><?php echo implode('<br/>',explode('¤',$writeContent[$finList]));?></a><?php
} else {
	print implode('<br/>',explode('¤',$writeContent[$finList]));
}
?>
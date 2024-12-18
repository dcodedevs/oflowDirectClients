<?php
function proc_rem_style($str)
{
	$str = strip_tags($str,'<p><ol><ul><li><b><i><strong>');
	$str = str_replace('<p>',"",$str);
	$str = str_replace('</p>',"<br />",$str);
	$str = str_replace('&rdquo;',"\"",$str);
	return $str;
}
function proc_mod10( $kid_u ){

        $siffer = str_split(strrev($kid_u));
        $sum = 0;

        for($i=0; $i<count($siffer); ++$i) $sum += proc_tverrsum(( $i & 1 ) ? $siffer[$i] * 1 : $siffer[$i] * 2);


		$controlnumber = ($sum==0) ? 0 : 10 - substr($sum, -1);
		if ($controlnumber == 10) $controlnumber = 0;

        return $controlnumber;

}

function proc_tverrsum($tall){
        return array_sum(str_split($tall));
}
?>

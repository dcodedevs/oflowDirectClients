<?php
function devide_by_uppercase($str)
{
	$tmp_return="";
	for($i=0;$i< strlen($str); $i++)
	{
		$letter=$str[$i];
		if ($letter==strtoupper($letter))
		{
			$tmp_return=$tmp_return." ".strtolower($letter);
		} else {
			$tmp_return=$tmp_return.$letter;
		}
	}
	$tmp_return=strtolower($tmp_return);
	$tmp_return=trim($tmp_return);
	$tmp_return=ucfirst($tmp_return);
	
	return $tmp_return; 
}
?>
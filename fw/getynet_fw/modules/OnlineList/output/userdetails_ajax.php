<?php
if(isset($_COOKIE['username'],$_COOKIE['sessionID']))
{
	if(!function_exists("APIconnectorUser")) if(is_file(__DIR__."/../../../includes/APIconnector.php")) include_once(__DIR__."/../../../includes/APIconnector.php");
	$s_file = __DIR__."/../../../outputLanguages/".$_GET['dlang'].".php";
	if(is_file($s_file)) include($s_file);
	$s_file = __DIR__."/../../../outputLanguages/".$_GET['lang'].".php";
	if(is_file($s_file)) include($s_file);

	$info = json_decode(APIconnectorUser("userdetailsget", $_COOKIE['username'], $_COOKIE['sessionID'], array('USER_ID'=>(isset($_GET['userID'])?$_GET['userID']:NULL))),true);
	$profileimage = json_decode(urldecode($_GET['profileimage']),true);
	?>
	<div style="width:180px; padding:5px;">
	<?php if(is_array($profileimage)){?><img src="https://pics.getynet.com/profileimages/<?php print $profileimage[1]; ?>" alt="" border="0" align="right" /><?php } ?> <b><?php print $info['name']; ?></b><br />
	<?php if($info['emailwork']!=""){ ?><a href="mailto:<?php print $info['emailwork']; ?>"><?php print $info['emailwork']; ?></a><br /><?php } ?>
	<?php if(1==1 or $info['status'] > 0) {?><a href="javascript:start_chat('<?php echo $info['id'];?>','<?php echo $info['name'];?>');"><?php print $formText_StartChat_RightSide; ?></a><br /><?php } ?>
	<br />
	<?php
	if($info['employers']!="") print $info['employers'].'<br />';
	if($info['title']!="") print $info['title'].'<br />';
	if($info['phonework']!="") print $info['phonework'].'<br />';
	?></div><?php
}
?>
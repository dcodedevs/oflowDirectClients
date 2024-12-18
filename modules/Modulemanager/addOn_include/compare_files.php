<?php
if(!class_exists("Diff")) include(__DIR__."/class_Diff.php");
if(!function_exists("ftp_ext_file_put_content")) include(__DIR__."/ftp_commandsExternal.php");

$v_error_msg = array();
if(isset($_GET['comparefiles']))
{
	list($s_lib_file, $s_acc_file) = explode('[:]', base64_decode($_GET['comparefiles']));
	$s_acc_root = realpath(__DIR__.'/../../../');
	$s_cmp_file = '/uploads/installtmp/cmp_'.rand(10000,9999999).'.cmp';
	mkdir(dirname($s_acc_root.$s_cmp_file),octdec(2777),true);
	$o_result = ftp_ext_get($s_acc_root.$s_cmp_file, $s_lib_file);
	if(!$o_result)
	{
		$v_error_msg[] = $formText_ErrorOccuredCommunicatingWithLibrary_Input;
	}
} else {
	$v_error_msg[] = $formText_FilesNotSpecified_Input;
}
?>

<div class="module-manager" style="overflow-y:scroll !important;">
	<h1><?php echo $formText_CompareResult_Input;?></h1>
	<?php
	if(sizeof($v_error_msg)==0)
	{
		echo Diff::toTable(Diff::compareFiles($s_acc_root.$s_cmp_file, __DIR__.'/../../../'.$s_acc_file), '', '', $formText_FileInLibrary_Input, $formText_FileInAccount_Input);
	} else {
		foreach($v_error_msg as $s_msg)
		{
			?>
		<div class="alert alert-danger"><?php echo $s_msg;?></div>
		<?php
		}
	}
	ftp_delete_file($s_cmp_file);
	?>
	<div> <a href="<?php echo base64_decode($_GET['returl']);?>" class="btn btn-default optimize"><?php echo $formText_GoBack_input;?></a> </div>
</div>
<style type="text/css">
.modulecontent {
	width:100% !important;
}
.module-manager > div {
	padding-top:20px;
}
.diff {
	background-color:#ffffff;
	width:100%;
}
.diff td {
	padding:0 0.667em;
	vertical-align:top;
	white-space:pre;
	white-space:pre-wrap;
	font-family:Consolas, 'Courier New', Courier, monospace;
	font-size:0.65em;
	line-height:1.333;
	border-left:1px solid #999999;
}
.diff td:last-child {
	border-right:1px solid #999999;
}
.diff tr:first-child {
	border-top:1px solid #999999;
}
.diff tr:last-child {
	border-bottom:1px solid #999999;
}
.diff span {
	display:block;
	min-height:1.333em;
	margin-top:-1px;
	padding:0 3px;
}
* html .diff span {
	height:1.333em;
}
.diff span:first-child {
	margin-top:0;
}
.diffDeleted span {
	border:1px solid rgb(255,192,192);
	background:rgb(255,224,224);
}
.diffInserted span {
	border:1px solid rgb(192,255,192);
	background:rgb(224,255,224);
}
#toStringOutput {
	margin:0 2em 2em;
}
</style>
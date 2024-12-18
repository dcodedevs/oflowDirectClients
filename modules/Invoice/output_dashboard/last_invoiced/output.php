<?php
include_once(__DIR__."/output_functions.php");
if(!function_exists("include_local")) include(__DIR__."/../../input/includes/fn_include_local.php");

$s_sql = '"SELECT languageID FROM language ORDER BY defaultOutputlanguage DESC, outputlanguage DESC, sortnr ASC"';
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
	$v_row = $o_query->result();
	$s_default_output_language = $v_row['languageID'];
}
$s_original_value = $choosenListInputLang;
$choosenListInputLang = $s_default_output_language;
include(__DIR__."/../../input/includes/readInputLanguage.php");
$choosenListInputLang = $s_original_value;

$headmodule = "";
$submods = $v_module_main_tables = array();
if($findBase = opendir(__DIR__."/../../input/settings/tables"))
{
	while($writeBase = readdir($findBase))
	{
		$fieldParts = explode(".",$writeBase);
		if($fieldParts[1] != "LCK" && $fieldParts[1] == "php" && $fieldParts[0] != "")
		{
			$submods[] = $fieldParts[0];
			$vars = include_local(__DIR__."/../../input/settings/tables/".$fieldParts[0].".php", $v_language_variables);

			if($vars['tableordernr'] == "1")
			{
				$headmodule = $fieldParts[0];
				$v_module_main_tables[1] = array($fieldParts[0], $vars['preinputformName'], $vars['moduletype']);
			}
			else if($vars['moduleMainTable'] == "1" && intval($vars['moduleTableAccesslevel'])<=$fw_session['developeraccess'])
			{
				$l_id = intval($vars['tableordernr']);
				if(array_key_exists($vars['tableordernr'], $v_module_main_tables)) $l_id += 20;
				$v_module_main_tables[$l_id] = array($fieldParts[0], $vars['preinputformName'], $vars['moduletype']);
			}
		}
	}
	if($headmodule == "")
	{
		$headmodule = $submods[0];
	}
	if(count($v_module_main_tables)==0)
	{
		$vars = include_local(__DIR__."/../../input/settings/tables/".$submods[0].".php", $v_language_variables);
		$v_module_main_tables[1] = array($submods[0], $vars['preinputformName'], $vars['moduletype']);
	}
	if(is_file(__DIR__."/../../input/settings/tables/".$headmodule.".php")) include(__DIR__."/../../input/settings/tables/".$headmodule.".php");
	closedir($findBase);
}
$submodule = $headmodule;
$fields = array();
include(__DIR__."/../../input/settings/fields/".$submodule."fields.php");
foreach($prefields as $s_field)
{
	$v_field = explode("¤",$s_field);
	$fields[$v_field[0]] = $v_field;
}

$s_page_reload_url = "";
$error_msg = json_decode($fw_session['error_msg'],true);
if(!isset($_POST['output_form_submit']) && sizeof($error_msg)>0)
{
	$print = "";
	foreach($error_msg as $key => $message)
	{
		list($class,$rest) = explode("_",$key);
		$print .= ' fw_info_message_add("'.$class.'", "'.$message.'"); ';
	}
	$print .= ' fw_info_message_show(); ';
	if(isset($ob_javascript))
	{
		$ob_javascript .= ' $(function(){'.$print.'});';
	} else {
		?><script type="text/javascript" language="javascript"><?php print '$(function(){'.$print.'});';?></script><?php
	}
	$fw_session['error_msg'] = array();
	$o_main->db->query("update session_framework set error_msg = '' where companyaccessID = ? and session = ? and username = ?", array($_GET['caID'], $variables->sessionID, $variables->loggID));
}

ob_start();
include_once(__DIR__."/includes/readOutputLanguage.php");
$v_include = array("list", "details", "details_approve", "ajax");
if(isset($_GET['inc_obj']) && in_array($_GET['inc_obj'], $v_include)) $s_inc_obj = $_GET['inc_obj']; else $s_inc_obj = "list";
if($s_inc_obj != "ajax")
{
    if(!function_exists("include_local")) include(__DIR__."/../../input/includes/fn_include_local.php");

    include(__DIR__."/includes/readOutputLanguage.php");

    $currentYear = date("Y");
    ?>
    <div class="output-category-block" id="last_invoices_block">
        <div class="output-title-different"><?php echo $formText_LastInvoiced_output;?></div>
        <div class="rs_box extra">
            <div class="output-content" id="last_invoices_info">
                <div class="loading"><div class="lds-ring"><div></div><div></div><div></div><div></div></div></div>
            </div>
        </div>
    </div>
    <style>

	#last_invoices_block .output-title-different {
        font-size: 16px;
        font-weight: bold;
        padding: 7px 15px 0px;
    }
    #last_invoices_block .output-content .invoice_month_row {
        padding: 0px 0px 5px;
        overflow: hidden;
    }
    #last_invoices_block .output-content .invoice_month_row .number {
        color: #57b4e4;
        font-size: 16px;
        margin-left: 10px;
        float: right;
        font-weight: bold;
        vertical-align: middle;
    }
    #last_invoices_block .output-content .invoice_month_row .number.columntype {
		width: 70px;
		text-align: right;
	}
    #last_invoices_block .output-content .invoice_month_row span.textLabel {
        vertical-align: middle;
        min-width: 80px;
        display: inline-block;
        font-weight: bold;
    }
    #last_invoices_block .output-content .invoice_month_row .invoice_month_row_department {
        color: #7c7c7c;
    }

    #last_invoices_block .output-content .invoice_month_row .invoice_month_row_department span.textLabel {
        font-weight: normal;
    }
    #last_invoices_block .output-content .invoice_month_row .invoice_month_row_department .number {
        color: #7c7c7c;
        font-weight: normal
    }
	#last_invoices_block .project-table-fixed {
		table-layout: fixed;
	}
	#last_invoices_block .table.project-table-fixed > tbody > tr > td,
	#last_invoices_block .table.project-table-fixed > tbody > tr > th,
	#last_invoices_block .table.project-table-fixed > tfoot > tr > td,
	#last_invoices_block .table.project-table-fixed > tfoot > tr > th,
	#last_invoices_block .table.project-table-fixed > thead > tr > td,
	#last_invoices_block .table.project-table-fixed > thead > tr > th {
		padding: 4px;
		font-size: 12px;
	}
	#last_invoices_block .table.project-table-fixed .numberedTd {
		text-align: right;
	}
    </style>
    <script type="text/javascript">

        //load latesupdates
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&abortable=1&module=Invoice&folderfile=output_redirect&subfolder=".basename(__DIR__)."&folder=output_dashboard&inc_obj=ajax&inc_act=get_info"; ?>',
            data:  "fwajax=1&fw_nocss=1",
            success: function(json){
                setTimeout(function() {
                    $('#last_invoices_info').html(json.html);
                }, 500);
            }
        }).fail(function() {
        });
    </script>
	<?php
} else {
	$s_inc_act = "";
	if(is_string($_GET['inc_act'])) $s_inc_act = $_GET['inc_act'];
	if(is_file(__DIR__."/includes/".$s_inc_obj.".".$s_inc_act.".php")) include(__DIR__."/includes/".$s_inc_obj.".".$s_inc_act.".php");
}

if(!isset($_POST['output_form_submit'])) print ob_get_clean();
?>

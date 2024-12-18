<?php
$b_webapp = FALSE;
$v_fields = array(
	'name' => $formText_Language_Output,
	'inputlanguage' => $formText_InputLanguage_Output,
	'defaultInputlanguage' => $formText_DefaultInputLanguage_Output,
	'outputlanguage' => $formText_OutputLanguage_Output,
	'defaultOutputlanguage' => $formText_DefaultOutputLanguage_Output,
	'hideOutputlanguage' => $formText_HideOutputLanguage_Output,
);
$o_query = $o_main->db->get('accountinfo_basisconfig');
$v_accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
if(isset($v_accountinfo_basisconfig['account_type']) && 'webapp' == $v_accountinfo_basisconfig['account_type'])
{
	$b_webapp = TRUE;
	$v_fields = array(
		'name' => $formText_Language_Output,
		'webapp_language' => $formText_WebApplicationLanguage_Output,
		'default_webapp_language' => $formText_Default_Output,
		'published_webapp_language' => $formText_Published_Output,
	);
}

require_once __DIR__ . '/settings_btn.php';

$s_page_reload_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=settings";
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <table class="table">
				<thead>
					<tr>
					<?php
					foreach($v_fields as $s_field => $s_label)
					{
						?><th><?php echo $s_label;?></th><?php
					}
					?>
					<th></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$s_sql = "SELECT * FROM language ORDER BY name";
				$o_query = $o_main->db->query($s_sql);
				if($o_query && $o_query->num_rows()>0)
				foreach($o_query->result_array() as $v_row)
				{
					$s_link_1 = '<a href="#" class="output-edit-language script" data-id="'.$v_row['languageID'].'">';
					$s_link_2 = '</a>';
					?>
					<tr>
						<td><?php echo $s_link_1.$v_row['name'].$s_link_2;?></a></td>
						<?php if($b_webapp) { ?>
						<td><?php echo $s_link_1;?><span class="glyphicon glyphicon-<?php echo (1 == $v_row['webapp_language'] ? 'check' : 'unchecked');?>"></span><?php echo $s_link_2;?></td>
						<td><?php echo $s_link_1;?><span class="glyphicon glyphicon-<?php echo (1 == $v_row['default_webapp_language'] ? 'check' : 'unchecked');?>"></span><?php echo $s_link_2;?></td>
						<td><?php echo $s_link_1;?><span class="glyphicon glyphicon-<?php echo (1 == $v_row['published_webapp_language'] ? 'check' : 'unchecked');?>"></span><?php echo $s_link_2;?></td>
						<?php } else { ?>
						<td><?php echo $s_link_1;?><span class="glyphicon glyphicon-<?php echo (1 == $v_row['inputlanguage'] ? 'check' : 'unchecked');?>"></span><?php echo $s_link_2;?></td>
						<td><?php echo $s_link_1;?><span class="glyphicon glyphicon-<?php echo (1 == $v_row['defaultInputlanguage'] ? 'check' : 'unchecked');?>"></span><?php echo $s_link_2;?></td>
						<td><?php echo $s_link_1;?><span class="glyphicon glyphicon-<?php echo (1 == $v_row['outputlanguage'] ? 'check' : 'unchecked');?>"></span><?php echo $s_link_2;?></td>
						<td><?php echo $s_link_1;?><span class="glyphicon glyphicon-<?php echo (1 == $v_row['defaultOutputlanguage'] ? 'check' : 'unchecked');?>"></span><?php echo $s_link_2;?></td>
						<td><?php echo $s_link_1;?><span class="glyphicon glyphicon-<?php echo (1 == $v_row['hideOutputlanguage'] ? 'check' : 'unchecked');?>"></span><?php echo $s_link_2;?></td>
						<?php } ?>
						<td><a href="#" class="output-delete-language script" data-id="<?php echo $v_row['languageID'];?>" data-delete-msg="<?php echo $formText_DeleteLanguage_Output.": ".$v_row['name'];?>?"><span class="glyphicon glyphicon-trash"></span></a></td>
					</tr>
					<?php
				}
				?>
				</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<?php
if(isset($_GET['list_filter'])){ $list_filter = $_GET['list_filter']; } else { $list_filter = 'active'; }

?>
<script type="text/javascript">
$(function(){
    $('.output-edit-language').off('click').on('click', function(e){
        e.preventDefault();
        var data = {
            languageID: $(this).data('id'),
        };
        ajaxCall('edit_language', data, function(json) {
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $('.output-delete-language').off('click').on('click', function(e){
        e.preventDefault();
		var $this = $(this);
		bootbox.confirm({
			message: $this.data('delete-msg'),
			buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
			callback: function(result){
				if(result)
				{
					var data = {
						action: 'delete_language',
						languageID: $this.data('id'),
						output_form_submit: 1,
					};
					ajaxCall('edit_language', data, function(json){
						output_reload_page();
					});
				}
			}
		});
    });
});
function output_reload_page()
{
    fw_load_ajax('<?php echo $s_page_reload_url;?>', '', false);
}
</script>
<style>
.hoverEye {
	position: relative;
	color: #0284C9;
	float: right;
	margin-top: 2px;
}
.hoverEye .hoverInfo {
	font-family: 'PT Sans', sans-serif;
	width:450px;
	display: none;
	color: #000;
	position: absolute;
	right: 0%;
	top: 100%;
	padding: 5px 10px;
	background: #fff;
	border: 1px solid #ccc;
	z-index: 1;
}
.hoverEye:hover .hoverInfo {
	display: block;
}
</style>
<?php
if(!$o_main->db->table_exists('sys_weboutputload'))
{
	$o_main->db->simple_query('CREATE TABLE sys_weboutputload (
				library VARCHAR(255) NOT NULL,
				libtype TINYINT(1) NOT NULL,
				sortnr TINYINT(4) NOT NULL
			);');
}

$s_folder = "";
if(strlen(ACCOUNTBASE)>0)
{
	$s_folder = 'b='.urlencode(rtrim(ACCOUNTBASE,'/')).'&';
}

$libConfigJS = $libConfigCss = array();
if(is_file(BASEPATH.'elementsGlobal/defaults.css'))
	$libConfigCss[] = 'elementsGlobal/defaults.css';
if(is_file(BASEPATH.'modules/Layoutmanager/output/elementsOutput/layout-'.$layoutID.'.css'))
	$libConfigCss[] = 'modules/Layoutmanager/output/elementsOutput/layout-'.$layoutID.'.css';

$o_query = $o_main->db->query('SELECT name FROM moduledata WHERE type IN(1,2)');
foreach($o_query->result() as $o_row)
{
	if(is_file(BASEPATH.'modules/'.$o_row->name.'/'.$variables->outputTheme.'/output.css'))
		$libConfigCss[] = 'modules/'.$o_row->name.'/'.$variables->outputTheme.'/output.css';
}

if(!function_exists('get_layout_css')) include(__DIR__.'/fn_get_layout_css.php');
$libConfigCss = get_layout_css($o_main, $libConfigCss, $layoutID);

if(is_file(BASEPATH.'modules/'.$variables->outputFolder.'/'.$variables->outputTheme.'/output.css'))
	$libConfigCss[] = 'modules/'.$variables->outputFolder.'/'.$variables->outputTheme.'/output.css';

$o_query = $o_main->db->query('SELECT library, libtype FROM sys_weboutputload ORDER BY sortnr');
foreach($o_query->result() as $o_row)
{
	if(is_file(BASEPATH.$o_row->library))
	{
		if($o_row->libtype==2) $libConfigCss[] = $o_row->library;
		else $libConfigJS[] = $o_row->library;
	}
}
if(is_file(BASEPATH.'modules/'.$variables->outputFolder.'/'.$variables->outputTheme.'/output_javascript.php'))
{
	$libConfigJS[] = 'modules/'.$variables->outputFolder.'/'.$variables->outputTheme.'/output_javascript.php';
}

if(is_file(BASEPATH.'elementsGlobal/defaults_ckeditor.css'))
	$libConfigCss[] = 'elementsGlobal/defaults_ckeditor.css';


// Duplicate remove
$libConfigJSFiles = $libConfigCssFiles = array();
$libConfigCss = array_reverse($libConfigCss);
$libConfigJS = array_reverse($libConfigJS);
foreach($libConfigCss as $item)
{
	if(!in_array($item,$libConfigCssFiles)) $libConfigCssFiles[] = $item;
}
foreach($libConfigJS as $item)
{
	if(!in_array($item,$libConfigJSFiles)) $libConfigJSFiles[] = $item;
}
$libConfigCssFiles = array_reverse($libConfigCssFiles);
$libConfigJSFiles = array_reverse($libConfigJSFiles);
if(sizeof($libConfigCssFiles)>0)
{
	?><link href="<?php echo parseLink('min/?'.$s_folder.'f='.urlencode(implode(',',$libConfigCssFiles)));?>&604800" rel="stylesheet" type="text/css"><?php
}
if(sizeof($libConfigJSFiles)>0)
{
	?><script type="text/javascript" src="<?php echo parseLink('min/?'.$s_folder.'f='.urlencode(implode(',',$libConfigJSFiles)));?>&604800"></script><?php
}

/*
** External inlclude
*/
?>
<script type="text/javascript">
jQuery(window).load(function(){
	var page_ids = '';
	var accUrl = '<?php echo parseLink(''); ?>';
	$('a.ck_accLinks').each(function(e){
		if($(this).attr('href') == '' && $(this).attr('data-pageid'))
		{
			var param = $(this).attr('data-pageid').split('#');
			param[1] = parseInt(param[1]);
			if(param[1] > 0)
			{
				if(page_ids != '') page_ids += ',';
				page_ids += param[1];
			}
		}
	});
	
	if(page_ids != '')
	{
		$.ajax({
			url:'<?php echo parseLink('elementsGlobal/ajax.getpagesurl.php'); ?>',
			type: "POST",
			cache: false,
			data: {ids: page_ids, languageID: '<?php echo $variables->languageID;?>'},
			success:function(html){
				var json_obj = $.parseJSON(html);
				$('a.ck_accLinks').each(function(e){
					if($(this).attr('href') == '' && $(this).attr('data-pageid'))
					{
						var splitedArr = $(this).attr('data-pageid').split('#');
						if(json_obj[splitedArr[1]][2] == '')
						{
							$(this).attr('href', accUrl+'?pageID='+json_obj[splitedArr[1]][0]+'&openLevel='+json_obj[splitedArr[1]][1]);
						} else {
							$(this).attr('href', accUrl+json_obj[splitedArr[1]][2]);
						}
						$(this).removeAttr('data-pageid');
					}
				});
			},
			error: function(xhr, ajaxOptions, thrownError){
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
});
</script>
<script language="javascript">
function debug_print(file,line,text,err_type, msg_type)
{
	<?php
	if(!isset($_GET['debug']))
	{
		?>return;<?php
	} else {
		?>
		text = ' <strong>'+ text + '</strong> (' + file +'[' + line +'] )';
		switch(err_type)
		{
			case 'notice':
				text='<div class="ui-state-highlight ui-corner-all" style="margin-top: 3px; padding: 0 .7em;"><p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>'+ text +'</p></div>';
				break;
			case 'warning':
				text='<div class="ui-state-highlight ui-corner-all" style="margin-top: 3px; padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>'+ text +'</p></div>';
				break;
			case 'error':
				text='<div class="ui-state-error ui-corner-all" style="margin-top: 3px; padding: 0 .7em;"><p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>'+ text +'</p></div>';
				break;
			default:
				msg_type='js_print_out';
				text='<div class="ui-state-highlight ui-corner-all" style="margin-top: 3px; padding: 0 .7em;"><p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>'+ text +'</p></div>';
				break;
		}
		$('#err_all').append('{' + msg_type + '}' + text);
		
		switch (msg_type)
		{
			case 'err':
				$('#err').append(text);
				break;
			case 'sql':
				$('#sql').append(text);
				break;
			case 'print_out':
				$('#print_out').append(text);
				break;
			case 'js_print_out':
				$('#js_print_out').append(text);
				break;
			case 'php_inc':
				$('#php_inc').append(text);
				break;
			default:
				$('#js_print_out').append(text);
				break;
		}
		<?php
	}
	?>
}
</script>
<?php



function debug_print($file,$line,$text,$err_type='notice',$msg_type='print_out')
{
	if (!isset($_GET['debug']))
	{
		return;
	}
	$text = date('H:i:s').' <strong>'.$text.'</strong> ('.$file.'['.$line.'] )';
	switch($err_type)
	{
		case 'notice':
			$text='<div class="ui-state-highlight ui-corner-all" style="margin-top: 3px; padding: 0 .7em;"> 
			<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
			'.$text.'
			</p>
			</div>';
			break;
		case 'warning':
			$text='<div class="ui-state-highlight ui-corner-all" style="margin-top: 3px; padding: 0 .7em;"> 
			<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
			'.$text.'
			</p>
			</div>';
			break;
		case 'error':
			$text='<div class="ui-state-error ui-corner-all" style="margin-top: 3px; padding: 0 .7em;"> 
			<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
			'.$text.'
			</p>
			</div>';
			break;
		default:
			$text='<div class="ui-state-highlight ui-corner-all" style="margin-top: 3px; padding: 0 .7em;"> 
			<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
			'.$text.'
			</p>
			</div>';
			break;
	}
	print('<script language="javascript">
	$(\'#err_all\').append('.json_encode('{'.$msg_type.'}'.$text).');
	</script>');
	switch ($msg_type)
	{
		case 'err':
			print('<script language="javascript">
			$(\'#err\').append('.json_encode($text).');
			</script>');
			break;
		case 'sql':
			print('<script language="javascript">
			$(\'#sql\').append('.json_encode($text).');
			</script>');
			break;
		case 'print_out':
			print('<script language="javascript">
			$(\'#print_out\').append('.json_encode($text).');
			</script>');
			break;
		case 'js_print_out':
			print('<script language="javascript">
			$(\'#js_print_out\').append('.json_encode($text).');
			</script>');
			break;
		case 'php_inc':
			print('<script language="javascript">
			$(\'#php_inc\').append('.json_encode($text).');
			</script>');
			break;
		default:
			print('<script language="javascript">
			$(\'#print_out\').append('.json_encode($text).');
			</script>');
			/*
			print('<script language="javascript">
			if ($(\''.$msg_type.'\').length ) 
			{
			
			$(\''.$msg_type.'\').append('.json_encode($text).'); 
			}
			else
			{
			$(\'#error_tabs\').tabs( "add" , \''.$msg_type.'\' , \''.$msg_type.'\');
			$(\''.$msg_type.'\').append('.json_encode($text).'); 
			}     
			</script>');
			*/
			break;
	}
}
?>
<?php
define('ACCOUNT_PATH', realpath(__DIR__.'/../../'));
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
require_once(__DIR__."/../account_fw/includes/APIconnector.php");

$b_getynet_redirect = TRUE;
if(isset($_COOKIE['username'], $_COOKIE['sessionID']))
{
	#Check username before loading page
	$s_response = APIconnectorUser('usersessionget', $_COOKIE['username'], $_COOKIE['sessionID']);
	$v_response = json_decode($s_response, TRUE);
	
	if(!array_key_exists('error', $v_response))
	{
		$o_query = $o_main->db->query("SELECT * FROM session_framework WHERE session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."' ORDER BY cache_timestamp DESC LIMIT 1");
		if($o_query && $o_query->num_rows()>0)
		{
			$b_getynet_redirect = FALSE;
			$v_fw_session = $o_query->row_array();
			?>
			<div style="margin-top:20%; text-align:center;"><h3>Logging into the account. This can take a few seconds.</h3></div>
			<div style="text-align:center; margin-top:15px;"><img src="../account_fw/layout/ajax.svg"></div>
			<script type="text/javascript">
			window.setTimeout(function redir(){window.location = "<?php echo '../index.php?'.urldecode($v_fw_session['urlpath']);?>";},100);
			</script>
			<?php
		}
	}
}

if($b_getynet_redirect)
{
	header('Location: https://www.getynet.com/index.php?lp='.str_replace("/","",$_GET['lp']));
}

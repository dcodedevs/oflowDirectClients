<?php
/**
 * Totaloverview4
 * Created by: Rinalds
 * Create date: 21.03.2018
 *
 * Please note that this works outside of framework scope, but it uses the same
 * session. If some additional framework feature implementations must be done,
 * please do it in includes/init.php script
 *
 * PLEASE KEEP THIS SPA STYLE. DO NOT SEND PRECOMPILED HTML VIA AJAX CALLS.
 * REACT + REDUX SOLUTION
 */

// Make "API" available to other origin
header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: POST,GET,OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

// If call made with application/json instead of x-www-form-urlencoded
$json_post_data = json_decode(file_get_contents('php://input'), true);
if ($json_post_data) {
    $_POST = $json_post_data;
}

// Init session & database connection
require_once (__DIR__.'/includes/init.php');
include_once(__DIR__."/includes/readOutputLanguage.php");
//include_once(__DIR__."/includes/readAccessElements.php");

$v_include = array(
	"ajax",
	"list"
);

$v_include_default = 'list';
if(isset($_GET['inc_obj']) && in_array($_GET['inc_obj'], $v_include)) $s_inc_obj = $_GET['inc_obj']; else $s_inc_obj = $v_include_default;

if($s_inc_obj != "ajax") { ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php echo $formText_ContactpersonOverview_Ouptut;?></title>
	<script type="text/javascript" src="<?php echo ACCOUNT_BASE_URL;?>/lib/jquery/jquery.js"></script>
	<script type="text/javascript" src="<?php echo ACCOUNT_BASE_URL;?>/lib/jquery/jquery.bpopup-0.8.0.min.js"></script>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="<?php echo ACCOUNT_BASE_URL;?>/modules/Customer2/output_overview/output.css" />
  </head>
  <body>
	<div id="output-content-container">
		<?php
		if(is_file(__DIR__."/includes/".$s_inc_obj.".php")) include(__DIR__."/includes/".$s_inc_obj.".php");
		?>
	</div>
	<div id="popupeditbox" class="popupeditbox">
		<span class="button b-close fw_popup_x_color"><span>X</span></span>
		<div id="popupeditboxcontent"></div>
	</div>
	<div class="loader"></div>
	<style type="text/css">
	.loader {
		position:fixed;
		top:35%;
		left:50%;
		z-index:99999999;
		border: 10px solid #f3f3f3; /* Light grey */
		border-top: 10px solid #3498db; /* Blue */
		border-radius: 50%;
		width: 80px;
		height: 80px;
		animation: spin 2s linear infinite;
		display:none;
	}
	
	@keyframes spin {
		0% { transform: rotate(0deg); }
		100% { transform: rotate(360deg); }
	}
	</style>
	<?php include_once __DIR__ . '/output_javascript.php'; ?>
  </body>
</html>
<?php } else {
	$s_inc_act = "";
	if(is_string($_GET['inc_act'])) $s_inc_act = $_GET['inc_act'];
	if(is_file(__DIR__."/includes/".$s_inc_obj.".".$s_inc_act.".php")) include(__DIR__."/includes/".$s_inc_obj.".".$s_inc_act.".php");
}

if(!isset($_POST['output_form_submit'])) print ob_get_clean();

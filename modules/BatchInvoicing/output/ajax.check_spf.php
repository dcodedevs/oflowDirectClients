<?php
/*
 * Version	1.04
 * Date		2018.07.12
*/
$v_return = array();

//https://github.com/Mika56/PHP-SPF-Check
use Mika56\SPFCheck\SPFCheck;
use Mika56\SPFCheck\DNSRecordGetter;

require(__DIR__.'/SPFCheck/Exception/DNSLookupLimitReachedException.php');
require(__DIR__.'/SPFCheck/Exception/DNSLookupException.php');
require(__DIR__.'/SPFCheck/DNSRecordGetterInterface.php');
require(__DIR__.'/SPFCheck/DNSRecordGetter.php');
require(__DIR__.'/SPFCheck/IpUtils.php');
require(__DIR__.'/SPFCheck/SPFCheck.php');

$v_all = array(SPFCheck::RESULT_PASS=>'RESULT_PASS', SPFCheck::RESULT_FAIL=>'RESULT_FAIL', SPFCheck::RESULT_SOFTFAIL=>'RESULT_SOFTFAIL', SPFCheck::RESULT_NEUTRAL=>'RESULT_NEUTRAL', SPFCheck::RESULT_NONE=>'RESULT_NONE', SPFCheck::RESULT_PERMERROR=>'RESULT_PERMERROR', SPFCheck::RESULT_TEMPERROR=>'RESULT_TEMPERROR');
$v_pass = array(SPFCheck::RESULT_PASS/*, SPFCheck::RESULT_SOFTFAIL*/, SPFCheck::RESULT_NEUTRAL, SPFCheck::RESULT_NONE);

$s_mailserver_ip = gethostbyname($_POST['host']);
$v_sender = explode('[::]', $_POST['sender']);
$v_email_sender = explode("@", $v_sender[0]);
$s_email_sender_domain = $v_email_sender[1];
$s_email_sender_ip = gethostbyname($s_email_sender_domain);
$checker = new SPFCheck(new DNSRecordGetter()); // Uses php's dns_get_record method for lookup.
$s_result = $checker->isIPAllowed($s_mailserver_ip, $s_email_sender_domain);

if(in_array($s_result, $v_pass))
{
	$v_return['status'] = 'OK';
} else {
	$v_return['status'] = 'FAIL';
	ob_start();
	?><strong><?php echo $formText_Warning_spf;?></strong> <?php echo $formText_SpfRecordIsNotCorrectlyConfiguredForTheEmailAddress_spf;?>"<strong><?php echo $v_sender[0];?></strong>". <?php echo $formText_ThisCanCauseTheEmailWillBeMarkedAsSpamOrRejectedByTheReceiversEmailSever_spf;?>. <?php echo $formText_PleaseContactTheSystemAdministrator_output;?>.<?php
	$v_return['message'] = ob_get_clean();
}

$fw_return_data = $v_return;

<?php
if(!class_exists('PHPMailer')) include_once('class.phpmailer.php');
function send_email($host, $port, $username, $password, $from, $to, $subject, $body)
{
	$mail = new PHPMailer();
	$mail->CharSet	= 'UTF-8';
	$mail->Host		= $host;
	$mail->IsSMTP(true);
	if($port != '')
	{
		$mail->Port		= $port;
	}
	if($username != '' and $password != '')
	{
		$mail->SMTPAuth	= true;
		$mail->Username	= $username;
		$mail->Password	= $password;
	}
	$mail->SetFrom($from);
	$mail->AddAddress($to);
	$mail->Subject  = $subject;
	$mail->Body		= $body;
	$mail->WordWrap = 150;
	
	$mail->Send();
}
?>
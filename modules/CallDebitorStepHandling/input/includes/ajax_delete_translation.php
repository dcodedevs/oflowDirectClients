<?php
$data = array('data'=>json_encode(array('action'=>'del_variables','data'=>array(array($_POST['id'],$_POST['delete_this'])))));
$url = 'https://languages.getynet.com/api.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
print 'OK';

<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "TestRequest";

$Url = 'https://scs.hennott.de/index.php';
$Data = array('Access'=>'doccen.update.repo','Account'=>'test','ApiKey'=>'test');
$Connect = curl_init();

curl_setopt($Connect, CURLOPT_URL, $Url);
curl_setopt($Connect, CURLOPT_POST, true);
curl_setopt($Connect, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($Connect, CURLOPT_POSTFIELDS, $Data);

$Return = curl_exec($Connect);

echo '<br />Result:<br />';
print_r($Return);

curl_close($Connect);

?>
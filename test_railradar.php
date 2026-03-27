<?php
$from = "NDLS";
$to = "BCT";
$url = "https://api.railradar.in/api/v1/trains/between-stations?from=$from&to=$to";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
$response = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

echo "Response:\n";
echo $response;
echo "\nError:\n";
echo $curl_error;
?>

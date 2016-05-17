<?php

class Util
{

	static function curl_post_json($ip,$port, $serviceName, $target, $method, $data_string)
	{
		$url =  'http://'.$ip.':'.$port.'/'.$serviceName.'/webapi/'.$target.'/'.$method;
		$header = array(
			'Content-Type: application/json',
			'Content-Length: '.strlen($data_string),
			);
	$curl_opt_a = array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS =>$data_string,
		CURLOPT_HTTPHEADER =>$header,
	);
	$ch = curl_init();
	curl_setopt_array($ch,$curl_opt_a);
	$out = curl_exec($ch);
	curl_close($ch);
	 
	return $out; 
	}
}
?>

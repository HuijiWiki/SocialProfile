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
	static function curl_get_json($ip,$port, $serviceName, $target, $method, $data_string){
		$url =  'http://'.$ip.':'.$port.'/'.$serviceName.'/webapi/'.$target.'/'.$method.'?'.$data_string;
		$header = array(
			'Content-Type: application/json',
			'Content-Length: '.strlen($data_string),
			);
		$curl_opt_a = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => 30,
			// CURLOPT_POST => 1,
			// CURLOPT_POSTFIELDS =>$data_string,
			CURLOPT_HTTPHEADER =>$header,
		);
		$ch = curl_init();
		curl_setopt_array($ch,$curl_opt_a);
		$out = curl_exec($ch);
		curl_close($ch);
		return $out; 
	}
	static function curl_get_youdao($keyfrom, $key, $query){
		$url =  'http://fanyi.youdao.com/openapi.do?keyfrom='.$keyfrom.'&key='.$key.'&type=data&doctype=json&version=1.1&q='.$query;
		$header = array(
			'Content-Type: application/json',
			);
		$curl_opt_a = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => 30,
			// CURLOPT_POST => 1,
			// CURLOPT_POSTFIELDS =>$data_string,
			CURLOPT_HTTPHEADER =>$header,
		);
		$ch = curl_init();
		curl_setopt_array($ch,$curl_opt_a);
		$out = curl_exec($ch);
		curl_close($ch);
		return $out; 		
	}
}
// $xml = file_get_contents('pc2.xml');
// $res = array();
// $i = 1;
// $newxml = preg_replace_callback('/<title>(.*?)<\/title>/', function($matches) use (&$res , &$i) {

// 	preg_match('/\s(IX|IV|V?I{0,3})$/', $matches[1], $series);
// 	preg_match('/\s(\d){1,3}$/', $matches[1], $series2);
// 	$matches[1] = preg_replace('/\s(IX|IV|V?I{0,3})$/', '', $matches[1]);
// 	$matches[1] = preg_replace('/\s(\d){1,3}$/', '', $matches[1]);
// 	if (!isset($series[1])){
// 		$series[1] = '';
// 	} 
// 	if (!isset($series2[1])){
// 		$series2[1] = '';
// 	}
// 	if ($i%4 == 0){
// 		$out = json_decode(Util::curl_get_youdao('huijidata', '587017573', str_replace('&amp;', 'and', urlencode($matches[1]))));
// 	} else if ($i % 4 == 3){
// 		$out = json_decode(Util::curl_get_youdao('huijiwiki', '587591019', str_replace('&amp;', 'and', urlencode($matches[1]))));
// 	} else if ($i % 4 == 2){
// 		$out = json_decode(Util::curl_get_youdao('huijitrans', '1032940621', str_replace('&amp;', 'and', urlencode($matches[1]))));
// 	} else {
// 		$out = json_decode(Util::curl_get_youdao('huijistatic', '1929611625', str_replace('&amp;', 'and', urlencode($matches[1]))));
// 	}
// 	if ($series2[1] != ''){
// 		$matches[1].= ' '.$series2[1];
// 	} 
// 	if ($series[1] != ''){
// 		$matches[1].= ' '.$series[1];
// 	}
// 	if (isset($out->web)){
// 		foreach($out->web as $item){	
// 			$res[$matches[1]]['alias'] = implode(",", $item->value);
// 			$res[$matches[1]]['translation'] = $item->value[0];
// 			break;	
// 		}
// 	}
// 	if (isset($out->basic) && isset($out->basic->explains)){
// 		$res[$matches[1]]['explaination'] = implode(",", $out->basic->explains);
// 	}
// 	sleep(1);
// 	if (isset($res[$matches[1]]['translation'])){
// 		$res[$matches[1]]['translation'] .= $series[1];
// 		$res[$matches[1]]['translation'] .= $series2[1];

// 		echo $i++.") translated [".$matches[1]."] to 【".$res[$matches[1]]['translation']."】\n";
// 		return $res[$matches[1]]['translation'];		
// 	} else {
// 		$res[$matches[1]]['translation'] = $matches[1];
// 		echo $i++.") unable to translate [".$matches[1]."]\n";
// 		return $matches[1];
// 	}
// }, $xml);
// file_put_contents('pc_new.xml', $newxml);
// file_put_contents('trans33.txt', json_encode($res));
?>

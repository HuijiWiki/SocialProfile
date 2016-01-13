<?php

class QueryInterface
{

	static function curl_post_json($type,$op,$data_string)
	{
		$url =  'http://121.42.179.100:8080/queryService/webapi/'.$type.'/'.$op;
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



	

	static function pageSearch($content,$size, $offset)
	{
		$data =json_encode(array('content'=>$content,'size'=>$size,'offset'=>$offset));
		return self::curl_post_json('page','search',$data);	

	}


	static function wikiSiteSearch($content,$size, $offset)
	{
		$data =json_encode(array('content'=>$content,'size'=>$size,'offset'=>$offset));
		return self::curl_post_json('wikisite','search',$data);	

	}
}

//var_dump(QueryInterface::wikisiteSearch("魔戒",1,0));

var_dump(QueryInterface::pageSearch("Hawkeye",1,0));
//TestQuery::upsert('zhang','zhang','1','niubi','niiuuuuuu','today')
//TestQuery::searchWithLogInfo('1232.343.555.5','343',-1,"yezhu",30,0);
?>

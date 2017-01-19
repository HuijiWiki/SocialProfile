<?php
require_once('Util.php');
class FeedProvider
{

	static function get($method,$data_string)
	{
		return Util::curl_get_json('huijidata.com','8080', 'events-statistics-rest', 'records', $method, $data_string);
	}
	 
	static function getFeed( $type, $sites = [],$users = [], $ns = [], $score = null, $from = null, $to = null, $size = 30 , $page = 0)
	{
		$str = $type.';';
		foreach($sites as $site){
			$str.="sites=";
			$str.=$site;
			$str.=";";
		}
		foreach($users as $user){
			$str.="users=";
			$str.=$user;
			$str.=";";
		}		
		foreach($ns as $n){
			$str.="ns=";
			$str.=$n;
			$str.=";";
		}
		if (!empty($score)){
			$str.="score=".$score.";";	
		}
		if (!empty($from)){
			$str.="fromDateTime=".$from.";";	
		}
		if (!empty($to)){
			$str.="toDateTime=".$to.";";	
		}
		$data = "size=".$size."&page=".$page;
		return json_decode(self::get($str, $data));
	}
}

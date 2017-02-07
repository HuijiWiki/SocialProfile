<?php
require_once('Util.php');
class StatProvider
{

	static function get($method,$data_string)
	{
		return Util::curl_get_json('huijidata.com','8080', 'events-statistics-rest', 'stats', $method, $data_string);
	}
	static function getStatsPerSite($type, $site, $day, $fromDate, $toDate){
		$str="sites/$site/types/$type;";
		if (!empty($day)){
			$str.="day=$day;";
		}
		if (!empty($fromDate)){
			$str.="fromDate=$fromDate;";
		}
		if (!empty($toDate)){
			$str.="toDate=$toDate;";
		}
		$res = json_decode(self::get($str, ''));
		return $res->message;
	}
	static function getStatsPerUser($type, $userId, $fromDate = null, $toDate = null, $site = null, $category = null){
		$str="users/$userId/types/$type;";
		if (!empty($fromDate)){
			$str.="fromDate=$fromDate;";
		}
		if (!empty($toDate)){
			$str.="toDate=$toDate;";
		}
		if (!empty($site)){
			$str.="sitePrefix=$site;";
		}
		if (!empty($category)){
			$str.="category=$category;";
		}
		$res = json_decode(self::get($str, ''));
		return $res->message;
	}
	static function getStatPerPage($type, $pageId, $fromDate = null, $toDate = null, $site){
		$str="pages/$site-$pageId/types/$type;";
		if (!empty($fromDate)){
			$str.="fromDate=$fromDate;";
		}
		if (!empty($toDate)){
			$str.="toDate=$toDate;";
		}
		$res = json_decode(self::get($str, ''));
		return $res->message;		
	}
	static function getTopUser($type, $fromDate = null, $toDate = null, $site = null, $top = 5){
		$str="users/$type;";
		if (!empty($fromDate)){
			$str.="fromDate=$fromDate;";
		}
		if (!empty($toDate)){
			$str.="toDate=$toDate;";
		}
		if (!empty($site)){
			$str.="sitePrefix=$site;";
		}
		if (!empty($top)){
			$str.="top=$top;";
		}		
		$res = json_decode(self::get($str, ''));
		return $res->message;
	}
	static function getTopPage($type, $fromDate = null, $toDate = null, $site = null, $top = 5){
		$str="pages/$type;";
		if (!empty($fromDate)){
			$str.="fromDate=$fromDate;";
		}
		if (!empty($toDate)){
			$str.="toDate=$toDate;";
		}
		if (!empty($site)){
			$str.="sitePrefix=$site;";
		}
		if (!empty($top)){
			$str.="top=$top;";
		}		
		$res = json_decode(self::get($str, ''));
		return $res->message;
	}	 
}
// var_dump(StatProvider::getStatPerPage('view','934', null, null, 'dnfcn'));

<?php

if(!defined('MEDIAWIKI')){
	die("This is not a valid entry point.\n");
}

$wgHooks['NewRevisionFromEditComplete'][] = 'insertEditRecord';

function insertEditRecord($article, $rev, $baseID, $user ){
	global $wgHuijiPrefix, $wgSitename, $wgIsProduction;
	/*
	$url = 'http://huijidata.com:50007/insertEditRecord/';
	$post_data = array(
		'userName' => $user->getName(),
		'userId' => $user->getId(),
		'wikiSite' => $wgHuijiPrefix,
		'siteName' => $wgSitename,
		'articleId' => $article->getId(),
		'titleName' => $article->getTitle()->getText()
	);
	$post_data_string = '';
	foreach($post_data as $key => $value){
		$post_data_string .= $key.'='.$value.'&';
	}
	$post_data_string = substr($post_data_string,0,-1);
	//echo $post_data_string;

	$curl_opt = array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_TIMEOUT => 1,
		//CURLOPT_HEADER => false,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $post_data_string
	);
	$ch = curl_init();
	curl_setopt_array($ch,$curl_opt);
	curl_exec($ch);
	curl_close($ch);
        */

        $log_data = array(
		'user_name' => $user->getName(),
                'user_id' => $user->getId(),
                'site_prefix' => $wgHuijiPrefix,
                'site_name' => $wgSitename,
                'page_title' => $article->getTitle()->getText(),
                'page_id' => $article->getId(),
                'page_ns' => $article->getTitle()->getNamespace(),
		'timestamp' => isset($_SERVER[ 'REQUEST_TIME' ]) ? $_SERVER[ 'REQUEST_TIME' ] : "",
                'client_ip'=> isset($_SERVER[ 'HTTP_X_FORWARDED_FOR' ]) ? $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] : "",
                'client_userAgent' => isset($_SERVER[ 'HTTP_USER_AGENT' ]) ? $_SERVER[ 'HTTP_USER_AGENT' ] : "",
	); 
	if($wgIsProduction == false) return;       
	include("curl.php");
        $out =MySPCURL::postDataInJson('http://huijidata.com:8080/statisticQuery/webapi/edit/insertOnePageEditRecord',json_encode($log_data));


}

/*
 function curl_post_json($data_string, $username, $pwd)
{
                $url =  'http://test.huiji.wiki:8080';
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
                CURLOPT_USERPWD => $username.':'.$pwd,
        );
        $ch = curl_init();
        curl_setopt_array($ch,$curl_opt_a);
        $out = curl_exec($ch);
        curl_close($ch);

        return $out;
}
*/

?>

<?php



if(!defined('MEDIAWIKI')){
	die("This is not a valid entry point.\n");
}

$wgHooks['NewRevisionFromEditComplete'][] = 'insertEditRecord';


function insertEditRecord($article, $rev, $baseID, $user ){
	global $wgHuijiPrefix, $wgSitename, $wgIsProduction;
	$url = 'http://huijidata.com:50007/insertEditRecord/';
	$post_data = array(
		'userName' => $user->getName(),
		'userId' => $user->getId(),
		'wikiSite' => $wgHuijiPrefix,
		'siteName' => $wgSitename,
		'articleId' => $article->getId(),
		'titleName' => $article->getTitle()->getText()
	);
        $log_data = array(
		'user.name' => $user->getName(),
                'user.id' => $user->getId(),
                'site.prefix' => $wgHuijiPrefix,
                'site.name' => $wgSitename,
                'page.title' => $article->getTitle()->getText(),
                'page.id' => $article->getId(),
                'page.ns' => $article->getTitle()->getNamespace(),
		'timestamp' => isset($_SERVER[ 'REQUEST_TIME' ]) ? $_SERVER[ 'REQUEST_TIME' ] : "",
                'user.ip'=> isset($_SERVER[ 'HTTP_X_FORWARDED_FOR' ]) ? $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] : "",
                'server.userAgent' => isset($_SERVER[ 'HTTP_USER_AGENT' ]) ? $_SERVER[ 'HTTP_USER_AGENT' ] : "",
	);        

        require_once("Logger.php");
        $logger = new EditRecordLogger();
	$logger->record("dfdfddfd444");	
	wfErrorLog($logger,"/var/log/user-edit-record/edit.log");
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

}

function  getClientIP() { 
	if (getenv('HTTP_CLIENT_IP')) { 
		$ip = getenv('HTTP_CLIENT_IP'); 
	} 
	elseif (getenv('HTTP_X_FORWARDED_FOR')) { 
		$ip = getenv('HTTP_X_FORWARDED_FOR'); 
	}	 
	elseif (getenv('HTTP_X_FORWARDED')) { 
		$ip = getenv('HTTP_X_FORWARDED'); 
	} 
	elseif (getenv('HTTP_FORWARDED_FOR')) { 
		$ip = getenv('HTTP_FORWARDED_FOR'); 
	} 
	elseif (getenv('HTTP_FORWARDED')) { 
		$ip = getenv('HTTP_FORWARDED'); 
	} 
	else { 
		$ip = $_SERVER['REMOTE_ADDR']; 
	} 
	return $ip; 
} 

function getServerIP(){
	return gethostbyname($_SERVER["SERVER_NAME"]);
}


?>

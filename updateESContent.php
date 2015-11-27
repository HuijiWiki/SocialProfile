<?php

if(!defined('MEDIAWIKI')){
	die("This is not a valid entry point.\n");
}


$wgHooks['NewRevisionFromEditComplete'][] = 'updatePageContent';
$wgHooks['ArticleDeleteComplete'][] = 'deletePage';

function updatePageContent($article, $rev, $baseID, $user ){
	global $wgHuijiPrefix, $wgSitename;
	
	$title = ($article->getId() == 1) ? $wgSitename : $article->getTitle()->getText();
	$post_data = array(
		'timestamp' => $rev->getTimestamp(),
		'content' => ContentHandler::getContentText($rev->getContent(Revision::RAW)),
		'sitePrefix' => $wgHuijiPrefix,
		'siteName' => $wgSitename,
		'id' => $article->getId(),
		'title' => $title
	);

	$post_data_string = json_encode($post_data);
	curl_post_json('upsert',$post_data_string);
}

function deletePage($article, $user, $reason, $id){
	global $wgHuijiPrefix, $wgSitename;
	
	$post_data = array(
		'sitePrefix' => $wgHuijiPrefix,
		'id' => $id
	);
	$post_data_string = json_encode($post_data);
	curl_post_json('delete',$post_data_string);
}


function curl_post_json($type,$data_string)
        {
                $url =  'http://localhost:8080/queryTest/webapi/page/'.$type;
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



?>

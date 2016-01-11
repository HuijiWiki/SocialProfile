<?php

if(!defined('MEDIAWIKI')){
	die("This is not a valid entry point.\n");
}

$wgHooks['PageContentSaveComplete'][] = 'updatePage';
//$wgHooks['ArticleDeleteComplete'][] = 'deletePage';



function updatePage($article, $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $revision, $status, $baseRevId){
	global $wgHuijiPrefix, $wgSitename;
	$old_rev = $rev->getPrevious();
	$old_redirectId = -1;
	$new_redirectId = -1;
	$category = array();
	//new & old content
	if($old_rev != null && ($old_content = $old_rev->getContent(Revision::RAW)) != null) $old_redirect = $old_content->getRedirectTarget();
	if(($new_content = $rev->getContent(Revision::RAW)) != null) $new_redirect = $new_content->getRedirectTarget();

	//new & old redirect 
         
	if($old_redirect != null){
		$old_redirectId = $old_redirect->getArticleId();
	}else{
		$old_redirectId = -1;
	}

	if($new_redirect != null){
		$new_redirectId = $new_redirect->getArticleId();
	}else{
		$new_redirecId = -1;
	}

	//category
	$options = $new_content->getContentHandler()->makeParserOptions( 'canonical' );
       	$output = $new_content->getParserOutput( $article->getTitle(), $rev->getId(), $options );
       	$category = array_map( 'strval', array_keys( $output->getCategories() ) );

//	wfErrorLog($old_redirectId."   ".$new_redirectId,"/var/log/mediawiki/SocialProfile.log");
//	wfErrorLog(implode(',',$category),"/var/log/mediawiki/SocialProfile.log");

	$title = ($article->getText() == "首页") ? $wgSitename : $article->getTitle()->getText();
	$preTitle = $old_rev != null ? $old_rev->getTitle()->getText():null;
	$redirectPageTitle = $new_redirect != null ? $new_redirect->getText():null;
	$post_data = array(
		'timestamp' => $rev->getTimestamp(),
		'content' => ContentHandler::getContentText($rev->getContent(Revision::RAW)),
		'sitePrefix' => $wgHuijiPrefix,
		'siteName' => $wgSitename,
		'id' => $article->getId(),
		'title' => $title,
		'preTitle' => $preTitle,
		'preRedirectPageId' => $old_redirectId,
		'redirectPageId' => $new_redirectId,
		'category' => $category,
		'redirectPageTitle' => $redirectPageTitle,
		
	);
	$post_data_string = json_encode($post_data);
	wfErrorLog($post_data_string,"/var/log/mediawiki/SocialProfile.log");
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
                $url =  'http://121.42.179.100:8080/queryService/webapi/page/'.$type;
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

<?php

if(!defined('MEDIAWIKI')){
	die("This is not a valid entry point.\n");
}

$wgHooks['PageContentSaveComplete'][] = 'savePage';
$wgHooks['ArticleDeleteComplete'][] = 'deletePage';
$wgHooks['ArticleRevisionUndeleted'][] = 'unDeletePage';
$wgHooks['TitleMoveComplete'][] = 'movePage';




function movePage($oldTitle, $newTitle, $user, $oldId, $newId, $reason,$rev){
	global $wgHuijiPrefix, $wgSitename, $wgIsProduction;
	if($wgIsProduction == false) return;	
	$new_ns = $newTitle->getNamespace();
	$old_ns = $oldTitle->getNamespace();
	
	if($old_ns === 0 && $new_ns !== 0){
		$post_data = array(
			'sitePrefix' => $wgHuijiPrefix,
			'id' => $oldTitle->getArticleID()
		);
		$post_data_string = json_encode($post_data);
		curl_post_json('delete',$post_data_string);	
	}else if($old_ns !== 0 && $new_ns === 0){
		upsertPage($newTitle, $rev);
	}else if($old_ns === 0 && $new_ns === 0){
		$post_data = array(
			'sitePrefix' => $wgHuijiPrefix,
			'siteName' => $wgSitename,
			'oldTitle' => $oldTitle->getText(),
			'newTitle' => $newTitle->getText(),
			'oldId' => $oldId,
			'newId' => $newId,
		);
		$post_data_string = json_encode($post_data);
//		wfErrorLog($post_data_string,"/var/log/mediawiki/SocialProfile.log");
		curl_post_json('move',$post_data_string);
	}else{
		return;
	}
}




function unDeletePage($title, $revision, $oldPageId){
	global $wgHuijiPrefix, $wgSitename,$wgIsProduction;
	if($wgIsProduction == false) return;	
	//title
	if($title == null || $title->getNamespace() !== 0) return;
	$titleT = ($title->getText() == "首页") ? $wgSitename : $title->getText();
	// new_content ,   new_redirect 
	if(($new_content = $revision->getContent(Revision::RAW)) != null) $new_redirect = $new_content->getRedirectTarget();
	//redirectPageTitle
	$redirectPageTitle = $new_redirect != null ? $new_redirect->getText():null;
	//new_redirectId
	if($new_redirect != null){
		$new_redirectId = $new_redirect->getArticleID();
	}else{
		$new_redirectId = -1;
	}

	//category
	$options = $new_content->getContentHandler()->makeParserOptions( 'canonical' );
       	$output = $new_content->getParserOutput( $title, $revision->getId(), $options,true);
       	$category = array_map( 'strval', array_keys( $output->getCategories() ) );


	$post_data = array(
		'timestamp' => $revision->getTimestamp(),
		'content' => $output->getText(),
		'sitePrefix' => $wgHuijiPrefix,
		'siteName' => $wgSitename,
		'id' => $title->getArticleID(),
		'title' => $titleT,
		'preTitle' => null,
		'preRedirectPageId' => -1,
		'redirectPageId' => $new_redirectId,
		'category' => $category,
		'redirectPageTitle' => $redirectPageTitle,
		
	);
	$post_data_string = json_encode($post_data);
//	wfErrorLog($post_data_string,"/var/log/mediawiki/SocialProfile.log");
	curl_post_json('upsert',$post_data_string);

}


function savePage($article, $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $revision, $status, $baseRevId){
	if($article == null || $revision == null || $article->getTitle() == null) return;
	upsertPage($article->getTitle(), $revision);
}
function upsertPage($title, $rev){
	global $wgHuijiPrefix, $wgSitename,$wgIsProduction;
	if($wgIsProduction == false) return;
	if($rev == null || $title == null || $title->getNamespace() !== 0) return;
	$old_rev = $rev->getPrevious();
	$old_redirectId = -1;
	$new_redirectId = -1;
	$category = array();
	//new & old content
	if($old_rev != null && ($old_content = $old_rev->getContent(Revision::RAW)) != null) $old_redirect = $old_content->getRedirectTarget();
	if(($new_content = $rev->getContent(Revision::RAW)) != null) $new_redirect = $new_content->getRedirectTarget();

	//new & old redirect 
         
	if($old_redirect != null){
		$old_redirectId = $old_redirect->getArticleID();
	}else{
		$old_redirectId = -1;
	}

	if($new_redirect != null){
		$new_redirectId = $new_redirect->getArticleID();
	}else{
		$new_redirectId = -1;
	}

	//category
	$options = $new_content->getContentHandler()->makeParserOptions( 'canonical' );
       	$output = $new_content->getParserOutput( $title, $rev->getId(), $options,true);
       	$category = array_map( 'strval', array_keys( $output->getCategories() ) );

	$titleName = ($title->getText() == "首页") ? $wgSitename : $title->getText();
	$preTitle = $old_rev != null ? $old_rev->getTitle()->getText():null;
	$redirectPageTitle = $new_redirect != null ? $new_redirect->getText():null;
	$post_data = array(
		'timestamp' => $rev->getTimestamp(),
		'content' => $output->getText(),
		'sitePrefix' => $wgHuijiPrefix,
		'siteName' => $wgSitename,
		'id' => $title->getArticleID(),
		'title' => $titleName,
		'preTitle' => $preTitle,
		'preRedirectPageId' => $old_redirectId,
		'redirectPageId' => $new_redirectId,
		'category' => $category,
		'redirectPageTitle' => $redirectPageTitle,
		
	);
	$post_data_string = json_encode($post_data);
//	wfErrorLog($post_data_string,"/var/log/mediawiki/SocialProfile.log");
	curl_post_json('upsert',$post_data_string);
}

function deletePage($article, $user, $reason, $id){
	global $wgHuijiPrefix, $wgSitename, $wgIsProduction;
	if($wgIsProduction == false) return;
	if($article->getTitle()->getNamespace() !== 0) return;
	$post_data = array(
		'sitePrefix' => $wgHuijiPrefix,
		'id' => $id
	);
	$post_data_string = json_encode($post_data);
//	wfErrorLog($post_data_string,"/var/log/mediawiki/SocialProfile.log");
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

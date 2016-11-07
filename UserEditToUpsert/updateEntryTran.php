<?php

if(!defined('MEDIAWIKI')){
	die("This is not a valid entry point.\n");
}


$wgHooks['PageContentSaveComplete'][] = 'saveEntryTran';
$wgHooks['ArticleDeleteComplete'][] = 'deleteEntryTran';
$wgHooks['ArticleRevisionUndeleted'][] = 'unDeleteEntryTran';
$wgHooks['TitleMoveComplete'][] = 'moveEntryTran';

function saveEntryTran($article, $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $revision, $status, $baseRevId){
    if($article == null || $revision == null || $article->getTitle() == null) return;
    
	$logger = MediaWiki\Logger\LoggerFactory::getInstance( 'updateEntryTran' );
    $logger->debug( "parsing begins at ".time(), [$article, $content] );
	$links= [];
	try{
		$parserOutput = $content->getParserOutput($article->getTitle());
		$links = $parserOutput->getLanguageLinks();
		$logger->debug( "parsing ends at ".time(), [$article, $content] );
	} catch(Exception $e){
		$logger->error($e->getMessage());
		exit();
	}
	if(count($links) == 0){
		upsert("", $article->getTitle()->getText(),"");
	}
 	
	$redirectToPage = $content->getRedirectTarget();
        //redirectPageTitle
        $toTitle = $redirectToPage != null ? $redirectToPage->getText():$article->getTitle()->getText();

	$preRev = $revision->getPrevious();	
	if($preRev == null){
		insert($toTitle, $article->getTitle()->getText(),$links);
		return;
	}else{
		$preLinks = [];
		try{
			$content = $preRev->getContent(Revision::RAW);
			$parserOutput = $content->getParserOutput($article->getTitle());
			$preLinks = $parserOutput->getLanguageLinks();
		} catch(Exception $e){
			$logger->error($e->getMessage());
			exit();
		}

		if(count($links) != count($preLinks)){
			insert($toTitle,$article->getTitle()->getText(),$links);
			return;
		}

		$preSet = [];
		$set = [];
		
		foreach ( $preLinks as $link) {
  	        	list( $key, $title ) = explode( ':', $link, 2 );
               		$preSet[$key] = $title;
		}

		foreach ( $links as $link) {
  	        	list( $key, $title ) = explode( ':', $link, 2 );
			if($preSet[$key] == null || $preSet[$key] != $title){
				insert($toTitle,$article->getTitle()->getText(),$links);
				return;
			}
		}

        }   	
}


function moveEntryTran($oldTitle, $newTitle, $user, $oldId, $newId, $reason,$rev){
	if($oldTitle == null || $oldTitle->getNamespace() !== 0 || $newTitle == null || $newTitle->getNamespace() !== 0) return;
	upsert($newTitle->getText(), $oldTitle->getText(),$oldId);
}




function unDeleteEntryTran($title, $revision, $oldPageId){
	if($title == null || $title->getNamespace() !== 0) return;	
	$logger = MediaWiki\Logger\LoggerFactory::getInstance( 'updateEntryTran' );
	$links= [];
	try{
		$content = $revision->getContent(Revision::RAW);
		$redirectTitleObject = $content->getRedirectTarget(); 
		$toTitle = $redirectTitleObject != null ? $redirectTitleObject->getText():$title->getText();
		$logger->debug( "parsing begins at ".time(), [$article, $content] );
		$parserOutput = $content->getParserOutput($title);
		$logger->debug( "parsing ends at ".time(), [$article, $content] );
		$links = $parserOutput->getLanguageLinks();
	} catch(Exception $e){
		$logger->error($e->getMessage());
		exit();
	}
	if(count($links) >0 ){
		insert($toTitle,$title->getText(),$links);
	}
}



function deleteEntryTran($article, $user, $reason, $id){
	if($article == null || $article->getTitle() == null || $article->getTitle()->getNamespace() !== 0) return;
	upsert('', $article->getTitle()->getText(),$id);
}


function upsert($newEntry, $oldEntry, $pageId){
	global $wgHuijiPrefix, $wgSitename, $wgIsProduction;
//	if($wgIsProduction == true || $wgHuijiPrefix != 'hearthstone') return;
	if($wgIsProduction == false || $wgHuijiPrefix == 'legion') return;
	$post_data = array(
		'sitePrefix' => $wgHuijiPrefix,
		'siteName' => $wgSitename,
		'oldEntry' => $oldEntry,
		'newEntry' => $newEntry,
		'pageId' => (String)$pageId,
	);

	$post_data_string = json_encode($post_data);
//	wfErrorLog($post_data_string,"/var/log/mediawiki/updateEntryTran.log");
	curl_post_json_entrytran('upsert',$post_data_string);
}


function insert($toTitle,$entry, $trans){
	global $wgHuijiPrefix, $wgSitename, $wgIsProduction;
//	if($wgIsProduction == true || $wgHuijiPrefix != 'hearthstone') return;
	if($wgIsProduction == false || $wgHuijiPrefix == 'legion') return;
	$post_data = array(
		'toTitle' => $toTitle,
		'sitePrefix' => $wgHuijiPrefix,
		'siteName' => $wgSitename,
		'entry' => $entry,
		'trans' => $trans,
	);

	$post_data_string = json_encode($post_data);
//	wfErrorLog($post_data_string,"/var/log/mediawiki/updateEntryTran.log");
	curl_post_json_entrytran('insert',$post_data_string);
}

function curl_post_json_entrytran($type,$data_string)
{
	require_once("curl.php");
        $out =MySPCURL::postDataInJson('http://huijidata.com:8080/entryTranslation/webapi/entryTran',$type, $data_string);
//	wfErrorLog($out,"/var/log/mediawiki/updateEntryTran.log");
        return $out;
}



?>

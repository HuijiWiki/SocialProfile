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
        upsert($article->getTitle(), $article->getTitle(), $article->getArticleID());
}


function moveEntryTran($oldTitle, $newTitle, $user, $oldId, $newId, $reason,$rev=null){
	if($oldTitle == null || $oldTitle->getNamespace() !== 0 || $newTitle == null || $newTitle->getNamespace() !== 0) return;
	upsert($newTitle->getText(), $oldTitle->getText(),$newId);
}




function unDeleteEntryTran($title, $revision, $oldPageId){
	if($title == null || $title->getNamespace() !== 0) return;	
	upsert($title->getText(), $title->getText(),$oldPageId);
}



function deleteEntryTran($article, $user, $reason, $id){
	if($article == null || $article->getTitle()->getNamespace() !== 0) return;
	upsert('', $article->getTitle()->getText(),$id);
}


function upsert($newEntry, $oldEntry, $pageId){
	global $wgHuijiPrefix, $wgSitename, $wgIsProduction;
	if($wgIsProduction == false || $wgHuijiPrefix != "hearthstone") return;
	$post_data = array(
		'sitePrefix' => $wgHuijiPrefix,
		'siteName' => $wgSitename,
		'oldEntry' => $oldTitle->getText(),
		'newEntry' => $newTitle->getText(),
		'pageId' => $oldId,
	);
	$post_data_string = json_encode($post_data);
	curl_post_json_entrytran('upsert',$post_data_string);
}


function curl_post_json_entrytran($type,$data_string)
{
	require_once("curl.php");
        $out =MySPCURL::postDataInJson('http://huijidata.com:8080/entryTranslation/webapi/entryTran',$type, $data_string);
        return $out;
}



?>

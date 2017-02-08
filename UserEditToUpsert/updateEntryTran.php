<?php

if(!defined('MEDIAWIKI')){
	die("This is not a valid entry point.\n");
}


$wgHooks['PageContentSaveComplete'][] = 'saveEntryTran';
$wgHooks['ArticleDeleteComplete'][] = 'deleteEntryTran';
$wgHooks['ArticleRevisionUndeleted'][] = 'unDeleteEntryTran';
$wgHooks['TitleMoveComplete'][] = 'moveEntryTran';

function saveEntryTran($article, $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $revision, $status, $baseRevId){
	$params = ['entrytran_save', $article->getId(), $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $revision, $status, $baseRevId];
	$jobs[] = new AsyncEventJob( $article->getTitle(), $params);
	if ($article->getTitle()->isNewPage()){
		$jobs[] = new AsyncEventJob( $article->getTitle(), ['baidu_push_new']);
	} else {
		$jobs[] = new AsyncEventJob( $article->getTitle(), ['baidu_push_update']);
	}
	JobQueueGroup::singleton()->push( $jobs ); // mediawiki >= 1.21
}

function moveEntryTran($oldTitle, $newTitle, $user, $oldId, $newId, $reason,$rev){
	if($oldTitle == null || $oldTitle->getNamespace() !== 0 || $newTitle == null || $newTitle->getNamespace() !== 0) return;
	upsert($newTitle->getText(), $oldTitle->getText(),$oldId);
	$jobs[] = new AsyncEventJob( $oldTitle, ['baidu_push_delete']);
	$jobs[] = new AsyncEventJob( $newTitle, ['baidu_push_new']);
	JobQueueGroup::singleton()->push( $jobs ); // mediawiki >= 1.21
}

function unDeleteEntryTran($title, $revision, $oldPageId){
	$params = ['entrytran_undelete', $title, $revision, $oldPageId];
	$jobs[] = new AsyncEventJob( $title, $params);
	$jobs[] = new AsyncEventJob( $title, ['baidu_push_new']);
	JobQueueGroup::singleton()->push( $jobs ); // mediawiki >= 1.21	
}



function deleteEntryTran($article, $user, $reason, $id){
	if($article == null || $article->getTitle() == null || $article->getTitle()->getNamespace() !== 0) return;
	upsert('', $article->getTitle()->getText(),$id);
	$jobs[] = new AsyncEventJob( $article->getTitle(), ['baidu_push_delete']);
	JobQueueGroup::singleton()->push( $jobs ); // mediawiki >= 1.21		
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

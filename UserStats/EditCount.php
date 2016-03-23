<?php
/**
 * Protect against register_globals vulnerabilities.
 * This line must be present before any global variable is referenced.
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}

/**
 * For the UserLevels (points) functionality to work, you will need to
 * define $wgUserLevels and require_once() this file in your wiki's
 * LocalSettings.php file.
 */
$wgHooks['NewRevisionFromEditComplete'][] = 'incEditCount';
$wgHooks['ArticleDelete'][] = 'removeDeletedEdits';
$wgHooks['ArticleUndelete'][] = 'restoreDeletedEdits';


/**
 * Updates user's points after they've made an edit in a namespace that is
 * listed in the $wgNamespacesForEditPoints array.
 */
function incEditCount( $article, $revision, $baseRevId ) {
	global $wgUser, $wgNamespacesForEditPoints,$wgMemc,$wgHuijiPrefix;
	// only keep tally for allowable namespaces
	if (
		!is_array( $wgNamespacesForEditPoints ) ||
		in_array( $article->getTitle()->getNamespace(), $wgNamespacesForEditPoints )
	) {
		$stats = new UserStatsTrack( $wgUser->getID(), $wgUser->getName() );
		$stats->incStatField( 'edit' );
	}
    $usg = new UserSystemGifts( $wgUser->getName() );
    if (HuijiFunctions::addLock( 'USG-17-'.$wgUser->getId(), 1 ) ){
    $dbr = wfGetDB( DB_SLAVE );
    $num = SiteStats::edits();
    $sg = SystemGifts::checkEditsCounts($num);  
    if($sg){
	$usg->sendSystemGift( 17 );
    }
        HuijiFunctions::releaseLock('USG-17-'.$wgUser->getId());
    }
	//festival gift
	$today = date("Y-m-d H:i:s");
	$giftList = SystemGifts::getInfoFromFestivalGift();
	$dayCount = 0;
	foreach ($giftList as $value) {
        if ( $today >= $value['startTime'] && $today <= $value['endTime'] ) {
            if (HuijiFunctions::addLock( 'USG-'.$value['giftId'].'-'.$wgUser->getId() , 1 )){				
				$resCount = RecordStatistics2::getAllPageEditCountFromUserId( $wgUser->getId(), $value['startTime'], $value['endTime'] );
				if ($resCount->status == 'success' && $resCount->result == $value['editNum'] ) {
					$usg->sendSystemGift( $value['giftId'] );
				}					
                HuijiFunctions::releaseLock( 'USG-'.$value['giftId'].'-'.$wgUser->getId() );
            }
        }
	}

	//Consecutive edit days 连续编辑天数
	$ueb = new UserEditBox();
    $editBox = $editData = array();
    $userEditInfo = $ueb->getUserEditInfo($wgUser->getId());
    if (HuijiFunctions::addLock( 'USG-maxlen-'.$wgUser->getID() )){
	    $maxlen = 0; //init variables.
	    if ($userEditInfo != false) {
	        foreach ($userEditInfo as $value) {
	        	if (is_object($value) && !empty($value->_id) && $value->value > 0) {
		        	$editBox[$value->_id] = $value->value;
		        	$editData[] = $value->_id;
	        	}
	            
	        }
	        $today = date("Y-m-d");
	        $editBox[$today] = UserEditBox::getTodayEdit($wgUser->getId());
	        if (!empty($editBox[$today])) {
	        	$editData[] = $today;
	        }
	        sort($editData);
	        $totalEdit = count($editData);
	        if ($totalEdit > 0){
		        $resArr[] = strtotime($editData[0]);
		        $maxlen = 1;	        	
	        }

	        for($k=1;$k<count($editData);$k++){
	        	if(in_array(strtotime($editData[$k])-86400, $resArr)){
	        		$resArr[] = strtotime($editData[$k]);
	        		if(count($resArr) > $maxlen){
	        			$maxlen = count($resArr);
	        		}
	        	}else{
	        		$resArr = array();
	        		$resArr[] = strtotime($editData[$k]);
	        	}
	        }
		
	        if ($maxlen == 2) {
				$usg->sendSystemGift( 33 );
	        }elseif ($maxlen == 3) {
				$usg->sendSystemGift( 34 );
	        }elseif ($maxlen == 7) {
				$usg->sendSystemGift( 35 ); 
	        }elseif ($maxlen == 13) {
				$usg->sendSystemGift( 36 ); 
	        }elseif ($maxlen == 23) {
				$usg->sendSystemGift( 37 ); 
	        }elseif ($maxlen == 61) {
				$usg->sendSystemGift( 38 ); 
	        }elseif ($maxlen == 109) {
				$usg->sendSystemGift( 39 ); 
	        }elseif ($maxlen == 199) {
				$usg->sendSystemGift( 40 ); 
	        }elseif ($maxlen == 367) {
				$usg->sendSystemGift( 41 ); 
	        }elseif ($maxlen == 727) {
				$usg->sendSystemGift( 42 ); 
	        }elseif ($maxlen == 1213) {
				$usg->sendSystemGift( 43 ); 
	        }elseif ($maxlen == 1579) {
				$usg->sendSystemGift( 44 ); 
	        }elseif ($maxlen == 1949) {
				$usg->sendSystemGift( 45 ); 
	        }elseif ($maxlen == 2333) {
				$usg->sendSystemGift( 46 ); 
	        }
	    }
	    HuijiFunctions::releaseLock( 'USG-maxlen-'.$wgUser->getID() );
	}
// echo $article->getFile()->getDescriptionText();die();
	// revision update  $baseRevId
	if ( NS_FILE == $article->getTitle()->getNamespace() 
		&& VideoTitle::isVideoTitle( $article->getTitle()  )
	){
		$thumSha1 = $article->getFile()->getSha1();
	if ( !empty($baseRevId) ) {
		$videoRevision = UploadVideos::getVideoRevisionByPageRevision( $thumSha1 );
		if ($videoRevision != ''){
			$pageId = $article->getTitle()->getArticleID();
			$updatePageVideo = UploadVideos::updateVideoPage( $pageId, $videoRevision );
			//$addRevisionBinder = UploadVideos::addRevisionBinder( $thumSha1, $videoRevision );				
		}
	}
		// if (  empty($baseRevId) ) {
		// 	//insert revision binder
		// 	// $pageRevision = $revision->getID();
		// 	$pageId = $article->getTitle()->getArticleID();
		// 	$video = VideoTitle::newFromId( $pageId );
		// 	$videoRevisionId = $video->getVideoRevisionId();
		// 	$addRevisionBinder = UploadVideos::addRevisionBinder( $thumSha1, $videoRevisionId );
			
		// }else{
		// 	// echo '2';die();
		// 	//insert binder  update page
		// 	// selec video_revision_id by baserevid from revisionbinder
		// 	// update page_video set revisionid= video_revision_id
		// 	// insert revisionbinder set pagerevid= $revsion->getid(), videorevisionid = video_revision_id
		// 	// $pageRevision = $revision->getID();
		// 	$videoRevision = UploadVideos::getVideoRevisionByPageRevision( $thumSha1 );
		// 	$pageId = $article->getTitle()->getArticleID();
		// 	$updatePageVideo = UploadVideos::updateVideoPage( $pageId, $videoRevision );
		// 	$addRevisionBinder = UploadVideos::addRevisionBinder( $thumSha1, $videoRevision );
		// }
	}
	
	$key = wfForeignMemcKey( 'huiji', '', 'revision', 'high_edit_site_followed', $wgUser->getName(), $wgHuijiPrefix );
	$wgMemc->incr( $key );
	$key = wfForeignMemcKey( 'huiji', '', 'revision', 'last_edit_user', $article->getTitle()->getArticleId(), $wgHuijiPrefix );
	$wgMemc->delete($key);
	return true;
}

/**
 * Updates user's points after a page in a namespace that is listed in the
 * $wgNamespacesForEditPoints array that they've edited has been deleted.
 */
function removeDeletedEdits( &$article, &$user, &$reason ) {
	global $wgNamespacesForEditPoints,$wgMemc,$wgHuijiPrefix;

	// only keep tally for allowable namespaces
	if (
		!is_array( $wgNamespacesForEditPoints ) ||
		in_array( $article->getTitle()->getNamespace(), $wgNamespacesForEditPoints )
	) {
		$dbr = wfGetDB( DB_MASTER );
		$res = $dbr->select(
			'revision',
			array( 'rev_user_text', 'rev_user', 'COUNT(*) AS the_count' ),
			array( 'rev_page' => $article->getID(), 'rev_user <> 0' ),
			__METHOD__,
			array( 'GROUP BY' => 'rev_user_text' )
		);
		foreach ( $res as $row ) {
			$stats = new UserStatsTrack( $row->rev_user, $row->rev_user_text );
			$stats->decStatField( 'edit', $row->the_count );
			$key = wfForeignMemcKey( 'huiji', '', 'revision', 'high_edit_site_followed', $row->rev_user_text, $wgHuijiPrefix );
			$wgMemc->decr( $key,$row->the_count );
		}
	}
	echo $article->getTitle()->getArticleID();
// echo 's';die();
	//delete video
	if ( NS_FILE == $article->getTitle()->getNamespace() && VideoTitle::isVideoTitle( $article->getTitle() ) ) {
		// echo 'w';die();
		$file_name = $article->getTitle()->getText();
		$pageId = $article->getTitle()->getArticleID();
		$del = UploadVideos::delVideoInfo( $pageId );
	}
	

	return true;
}

/**
 * Updates user's points after a page in a namespace that is listed in the
 * $wgNamespacesForEditPoints array that they've edited has been restored after
 * it was originally deleted.
 */
function restoreDeletedEdits( &$title, $new, $commnent, $oldPageId ) {
	global $wgNamespacesForEditPoints,$wgMemc,$wgHuijiPrefix;

	// only keep tally for allowable namespaces
	if (
		!is_array( $wgNamespacesForEditPoints ) ||
		in_array( $title->getNamespace(), $wgNamespacesForEditPoints )
	) {
		$dbr = wfGetDB( DB_MASTER );
		$res = $dbr->select(
			'revision',
			array( 'rev_user_text', 'rev_user', 'COUNT(*) AS the_count' ),
			array( 'rev_page' => $title->getArticleID(), 'rev_user <> 0' ),
			__METHOD__,
			array( 'GROUP BY' => 'rev_user_text' )
		);
		foreach ( $res as $row ) {
			$stats = new UserStatsTrack( $row->rev_user, $row->rev_user_text );
			$stats->incStatField( 'edit', $row->the_count );
			$key = wfForeignMemcKey( 'huiji', '', 'revision', 'high_edit_site_followed', $row->rev_user_text, $wgHuijiPrefix );
			$wgMemc->incr( $key, $row->the_count );
		}
	}
	// $oldTitle = Title::newFromID($oldPageId);
	// echo $oldPageId;die();
	// echo VideoTitle::isVideoTitle( $oldTitle );die();
// echo $title->getArticleID().'-'.$oldPageId;die();
	//restore video info  storeVideoInfo
	if ( NS_FILE == $title->getNamespace() && VideoTitle::isVideoTitleIdByArchive( $oldPageId ) ) {
		$article = new WikiFilePage($title);
		$thumSha1 = $article->getFile()->getSha1();
		$videoRevision = UploadVideos::getVideoRevisionByPageRevision( $thumSha1 );
		$pageId = $title->getArticleID();
		// echo $oldPageId;die();
		$restore = UploadVideos::restoreVideoInfo( $pageId, $oldPageId, $videoRevision );
		// $file_name = $title->getText();
		// $file_type = strrchr($file_name, ".");
		// $file_title = rtrim($file_name,$file_type);
		// $restor = UploadVideos::storeVideoInfo( $file_title );
	}

	return true;
}

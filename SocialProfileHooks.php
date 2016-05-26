<?php
/**
 * Hooked functions used by SocialProfile.
 *
 * All class methods are public and static.
 *
 * @file
 */
class SocialProfileHooks {
	/** 
	 * Enable follow user/site on every page
	 *
	 */
	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) { 
		// Add required CSS & JS via ResourceLoader
		$out->addModules( array('ext.socialprofile.usersitefollows.js','ext.socialprofile.useruserfollows.js', 'ext.socialprofile.useruserfollows.css' ));
		if( $out->getTitle()->isMainPage() ){
			$out->addModules( 'ext.socialprofile.qqLogin.js' );
		}
	}
	/**
	 * Register the canonical names for our custom namespaces and their talkspaces.
	 *
	 * @param $list Array: array of namespace numbers with corresponding
	 *                     canonical names
	 * @return Boolean: true
	 */
	public static function onCanonicalNamespaces( &$list ) {
		$list[NS_USER_WIKI] = 'UserWiki';
		$list[NS_USER_WIKI_TALK] = 'UserWiki_talk';
		$list[NS_USER_PROFILE] = 'User_profile';
		$list[NS_USER_PROFILE_TALK] = 'User_profile_talk';

		return true;
	}

	/**
	 * Creates SocialProfile's new database tables when the user runs
	 * /maintenance/update.php, the MediaWiki core updater script.
	 *
	 * @param $updater DatabaseUpdater
	 * @return Boolean
	 */
	public static function onLoadExtensionSchemaUpdates( $updater ) {
		$dir = dirname( __FILE__ );
		$dbExt = '';

		if ( $updater->getDB()->getType() == 'postgres' ) {
			$dbExt = '.postgres';
		}

		$updater->addExtensionUpdate( array( 'addTable', 'user_board', "$dir/UserBoard/user_board$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'user_profile', "$dir/UserProfile/user_profile$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'user_stats', "$dir/UserStats/user_stats$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'user_relationship', "$dir/UserRelationship/user_relationship$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'user_relationship_request', "$dir/UserRelationship/user_relationship$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'user_system_gift', "$dir/SystemGifts/systemgifts$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'system_gift', "$dir/SystemGifts/systemgifts$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'user_gift', "$dir/UserGifts/usergifts$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'gift', "$dir/UserGifts/usergifts$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'user_system_messages', "$dir/UserSystemMessages/user_system_messages$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'user_points_weekly', "$dir/UserStats/user_points_weekly$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'user_points_monthly', "$dir/UserStats/user_points_monthly$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'user_points_archive', "$dir/UserStats/user_points_archive$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'template_fork', "$dir/TemplateFork/template_fork$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'template_fork_count', "$dir/TemplateFork/template_fork_count$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addField', 'template_fork', 'target_id', "$dir/TemplateFork/modify_tb_template$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'revision_binder', "$dir/Videos/revision_binder$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'video_archive', "$dir/Videos/video_archive$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'video_page', "$dir/Videos/video_page$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'video_revision', "$dir/Videos/video_revision$dbExt.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'common_css', "$dir/CommonStyle/common_css$dbExt.sql", true ) );
		return true;
	}

	/**
	 * For integration with the Renameuser extension.
	 *
	 * @param int $uid User ID
	 * @param String $oldName old user name
	 * @param String $newName new user name
	 * @return Boolean
	 */
	public static function onRenameUserComplete( $uid, $oldName, $newName ) {
		$dbw = wfGetDB( DB_MASTER );

		$tables = array(
			'user_system_gift' => array( 'sg_user_name', 'sg_user_id' ),
			'user_board' => array( 'ub_user_name_from', 'ub_user_id_from' ),
			'user_gift' => array( 'ug_user_name_to', 'ug_user_id_to' ),
			'gift' => array( 'gift_creator_user_name', 'gift_creator_user_id' ),
			'user_relationship' => array( 'r_user_name_relation', 'r_user_id_relation' ),
			'user_relationship' => array( 'r_user_name', 'r_user_id' ),
			'user_relationship_request' => array( 'ur_user_name_from', 'ur_user_id_from' ),
			'user_stats' => array( 'stats_user_name', 'stats_user_id' ),
			'user_system_messages' => array( 'um_user_name', 'um_user_id' ),
		);

		foreach ( $tables as $table => $data ) {
			$dbw->update(
				$table,
				array( $data[0] => $newName ),
				array( $data[1] => $uid ),
				__METHOD__
			);
		}

		return true;
	}

	/**
	 * Load javascript for new users
	 * 
	 */

	public static function onAddNewAccount( User $user, $byEmail ) { 
		//todo add tours.
		global $wgMemc, $wgHuijiPrefix;
		$huijiUser =  HuijiUser::newFromUser($user);
		$HuijiSite = WikiSite::newFromPrefix($wgHuijiPrefix);        
		$huijiUser->follow($HuijiSite);
		//follow user
		$resUserArr = $HuijiSite->getFollowers(true);
		if( count($resUserArr) > 0 ){
			$num = (count($resUserArr)>=5) ? 5 : (count($resUserArr));
			for ($i=0; $i < $num; $i++) { 
			    $user = User::newFromName( $resUserArr[$i]['user'] );
			    $huijiUser->follow($user);
			}
		}
		
		//follow site
		$type = $HuijiSite->getType();
		$allPrefix = Huiji::getInstance()->getSitePrefixes();
		$res = $resArr = array();
		if ( count($allPrefix) > 0 ) {
			foreach ($allPrefix as $value) {
			    $site = WikiSite::newFromPrefix($value);
			    if( $site->getType() == $type ){
			        $res[$value] = $site->getScore();
			    }
			}
			$siteNum = (count($res)>=5) ? 5 : (count($res));
			arsort($res);
			$resArr = array_slice($res,0,$siteNum);
			foreach ($resArr as $key => $value) {
				$siteObj = WikiSite::newFromPrefix($key);
				$huijiUser->follow($siteObj);
			}
		}
		
		$value = '{"version":1,"tours":{"newuser":{"step":"intro"}}}';
		setcookie("huiji-mw-tour", $value, time()+3600*24*90, "/", ".huiji.wiki" );  /* expire in 90 days */
		$key = wfForeignMemcKey( 'huiji', '', 'user', 'get_all_user' );
 		$data = $wgMemc->get( $key );
		if ($data != ''){
 			$newUser['user_id'] = $user->getId();
 			$newUser['user_name'] = $user->getName();
 			$data[] = $newUser;
 			$wgMemc->set( $key, $data );
		}
	}
	/**
	* Expose config vars to javascript
	*
	*/
	public static function onResourceLoaderGetConfigVars( array &$vars ) {
	    global $wgHuijiPrefix, $wgHuijiId, $wgHuijiSuffix, $wgCentralServer;
	    $vars['wgHuijiPrefix'] = $wgHuijiPrefix;
	    $vars['wgHuijiId'] = $wgHuijiId;
	    $vars['wgHuijiSuffix'] = $wgHuijiSuffix;
	    $vars['wgCentralServer'] = $wgCentralServer;
	    return true;
	}
	/**
	 * modift vide mime type
	 */
	public static function onMimeMagicGuessFromContent( $mimeMagic, &$head, &$tail, $file, &$mime ) {
		// wfDebugLog('SocialProfile', 'onMimeMagicGuessFromContent'.$file);
		$mime = 'application/pdf';
	}

	public static function onBitmapHandlerTransform( $handler, $image, &$scalerParams, &$mto ) { 
		
	}
	public static function onThumbnailBeforeProduceHTML( $handler, &$attribs, &$linkAttribs ){
		$file = $handler->getFile();
		$sha1 = $handler->getFile()->getSha1();
		$vv = VideoRevision::newFromSha1( $sha1 );
		if (!is_null($vv) && $vv->exists()){
			$attribs['data-video'] = $vv->getPlayerUrl();
			$attribs['class'] = 'video-player video-player-asyn';
			$attribs['data-video-link'] = $vv->getVideoLink();
			$attribs['data-video-from'] = $vv->getVideoSource();
			$attribs['data-video-title'] = $vv->getVideoTitle();
			$attribs['data-video-duration'] = $vv->getDuration();			
		}
		if ( !is_null($vv) && $vv->getVideoSource() == '163' ) {
			$attribs['height'] = '0px';			
			$attribs['width'] = '0px';			
		}
		// // print_r($file);die();
		// $title = $file->title;
		// // echo $file_name;die();
		// //判断 是不是 video
		// $isVideoTitle = VideoTitle::isVideoTitle( $title );
		// // print_r($video_info);die();
		// if ( $isVideoTitle ){
		// 	$vt = VideoRevision::newFromId( $title->getArticleId() );
		// 	$attribs['data-video'] = $vt->getPlayerUrl();
		// 	$attribs['class'] = 'video-player video-player-asyn';
		// 	$attribs['data-video-link'] = $vt->getVideoLink();
		// 	$attribs['data-video-from'] = $vt->getVideoSource();
		// 	$attribs['data-video-title'] = $vt->getText();
		// 	$attribs['data-video-duration'] = $vt->getDuration();
		// }
	}
	public static function onUploadComplete(&$uploadBase){
		// $video_info = UploadVideos::checkFile( $uploadBase->getLocalFile()->getTitle() );
		// if ( isset($video_info) && count($video_info) > 0 ){
		// 	$uploadBase->getLocalFile()->setProp(array('major_mime'=>'video', 'minor_mime'=>'youku', 'media_type'=>'playable'));
		// 	$uploadBase->getLocalFile()->updateRow();
		// 	$uploadBase->getLocalFile()->publish($uploadBase->getTempPath(), [], []);
		// }
	}

	public static function onImageOpenShowImageInlineBefore($imagePage, &$out){
		if (VideoTitle::isVideoTitle($imagePage->getTitle())){
			$vt = VideoTitle::newFromId($imagePage->getTitle()->getArticleId());
			$source = $vt->getVideoSource();
			$str = '本文件代表了一部来自'.$source.'的视频&nbsp;';
			$out->addJsConfigVars('wgVideoLink', $vt->getVideoLink());
			$out->addJsConfigVars('wgVideoSource', $source);
			$out->setSubtitle( $str );
			$out->addModules('ext.socialprofile.videopage.js');
		}
	}

	public static function onImagePageAfterImageLinks($imagePage, &$html){
		// if ( VideoTitle::isVideoTitle($imagePage->getTitle() ) ){
		// 	$html = '<p>this is a test</p>';

		// }
	}

	/**
	 * onUserGroupsChanged
	 * when user's sysop right have been remove, delete cache
	 */
	public static function onUserGroupsChanged(){
		global $wgMemc, $wgHuijiPrefix;
        $key = wfForeignMemcKey('huiji','', 'user_group', 'sitemanager', $wgHuijiPrefix,'sysop' );
        $wgMemc->delete( $key );
	}

	public static function onSkinGetPageLink($title, &$html){
		if ($title->isSpecial('CommonStyle') ){
            $cssCon_1 = CommonStyle::getCurrentCssStyle(1);
            if ($cssCon_1 == false) {
                $isNew = 0;
            }else{
                $isNew = 1;
            }
            if ( $cssCon_1['cssContent'] == null ) {
                $lessCon = array();
                $show = 'none';
            }else{
                $lessCon = (array)json_decode( $cssCon_1['cssContent'] );
                $show = '';
            }
            $mainBase = !isset( $lessCon['@main-base'] ) ? "#333" : $lessCon['@main-base'];
            $bg = !isset( $lessCon['@bg'] ) ? "#fff" : $lessCon['@bg'];
            $bgInner = !isset( $lessCon['@bg-inner'] ) ? "#fff" : $lessCon['@bg-inner'];
            $a = !isset( $lessCon['@a'] ) ? "#428bca" : $lessCon['@a'];
            $subBg = !isset( $lessCon['@sub-bg'] ) ? "#f6f8f8" : $lessCon['@sub-bg'];
            $subA = !isset( $lessCon['@sub-a'] ) ? "#333" : $lessCon['@sub-a'];
            $modal = !isset( $lessCon['@modal'] ) ? "#222" : $lessCon['@modal'];
			$html = '
			<li class="dropdown open">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">自定义主题&nbsp;<span class="caret"></span></a>
            <ul class="dropdown-menu picker-color">
                <li>
                <div class="color-box jcolor" data-variable="@main-base" value="'.$mainBase.'" style="color:'.$mainBase.'"></div>
                <div class="color-name">字体主色调</div>
                </li>
                <li>
                <div class="color-box jcolor" data-variable="@bg" value="'.$bg.'" style="color:'.$bg.'"></div>
                <div class="color-name">外背景</div>
                </li>
                <li>
                <div class="color-box jcolor" data-variable="@bg-inner" value="'.$bgInner.'" style="color:'.$bgInner.'"></div>
                <div class="color-name">内背景</div>
                </li>
                <li>
                <div class="color-box jcolor" data-variable="@a" value="'.$a.'" style="color:'.$a.'"></div>
                <div class="color-name">链接颜色</div>
                </li>
                <li>
                <div class="color-box jcolor" data-variable="@sub-bg" value="'.$subBg.'" style="color:'.$subBg.'"></div>
                <div class="color-name">次级导航背景</div>
                </li>
                <li>
                <div class="color-box jcolor" data-variable="@sub-a" value="'.$subA.'" style="color:'.$subA.'"></div>
                <div class="color-name">次级导航字体色</div>
                </li>
                <li>
                <div class="color-box jcolor" data-variable="@modal" value="'.$modal.'" style="color:'.$modal.'"></div>
                <div class="color-name">模板色调</div>
                </li>
                <li>
                <input type="hidden" class="is-new" value="'.$isNew.'">
                <button class="btn btn-primary commonstyle-submit">保存</button>
                <button class="btn btn-primary commonstyle-reset" style="display:'.$show.'">重置</button>
                </li>
            </ul>
            </li>
            <li>
                <a href="/wiki/Mediawiki:Common.css">修改站点css&nbsp;</a>
            </li>
            <li>
                <a href="/wiki/Mediawiki:Common.js">修改站点js&nbsp;</a>
            </li>
			';
			return false;
		}else if($title->isCssOrJsPage()){
           $html='
            <li>
               <a href="/wiki/special:自定义主题">自定义主题&nbsp;</a>
            </li>
            <li>
               <a href="/wiki/Mediawiki:Common.js">修改站点js&nbsp;</a>
            </li>
           ';
		}else{
			return true;
		}	

	}

}

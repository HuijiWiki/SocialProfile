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
		$usf = new UserSiteFollow();
		$usf->addUserSiteFollow($user, $wgHuijiPrefix);
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
		wfDebugLog('SocialProfile', 'onMimeMagicGuessFromContent'.$file);
		$mime = 'application/pdf';
	}

	public static function onBitmapHandlerTransform( $handler, $image, &$scalerParams, &$mto ) { 
		
	}
	public static function onThumbnailBeforeProduceHTML( $handler, &$attribs, &$linkAttribs ){
		$file = $handler->getFile();
		// print_r($file);die();
		$title = $file->title;
		// echo $file_name;die();
		//判断 是不是 video
		$isVideoTitle = VideoTitle::isVideoTitle( $title );
		// print_r($video_info);die();
		if ( $isVideoTitle ){
			$vt = VideoTitle::newFromId( $title->getArticleId() );
			$attribs['data-video'] = $vt->getPlayerUrl();
			$attribs['class'] = 'video-player';
			$attribs['data-video-link'] = $vt->getVideoLink();
			$attribs['data-video-from'] = $vt->getVideoSource();
			$attribs['data-video-title'] = $vt->getText();
			$attribs['data-video-duration'] = $vt->getDuration();
		}
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

}

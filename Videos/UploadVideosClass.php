<?php
/**
 * class UploadVideos
 * authur slx
 */
class UploadVideos{
	/**
	 * insert video info
	 * @return [bool] [success-> ture]
	 */
	static function addVideoInfo( $pageRevision, $page_id, $video_from, $video_id, $video_title, $video_player_url, $video_tags, $user_name, $video_duration ) {
		$dbw = wfGetDB( DB_MASTER );
		$res = $dbw->insert(
					'video_revision',
					array(
						'rev_page_id' => $page_id,
						'rev_video_from' => $video_from,
						'rev_video_id' => $video_id,
						'rev_video_title' => $video_title,
						'rev_video_player_url' => $video_player_url,
						'rev_video_tags' => $video_tags,
						'rev_video_duration' => $video_duration,
						'rev_upload_user' => $user_name,
						'rev_upload_date' => date( 'Y-m-d H:i:s' ),
					),
					__METHOD__
				);
		if( $res ){
			$rev_id = $dbw->insertId();
			// self::addRevisionBinder( $pageRevision, $rev_id );
			$dbw -> upsert(
				'video_page',
				array(
					'page_id' => $page_id,
					'revision_id' => $rev_id
				),
				array(
					'page_id' => $page_id
				),
				array(
					'revision_id' => $rev_id
				),
				__METHOD__
			);
			return $dbw->insertId();
		}
	}
	/**
	 * delete video info
	 */
	
	static function delVideoInfo( $page_id ){
		$dbw = wfGetDB( DB_MASTER );
		$result = false;
		$res = $dbw->select(
			'video_revision',
			array(
				'rev_id',
				'rev_page_id',
				'rev_video_id',
				'rev_video_title',
				'rev_video_from',
				'rev_video_player_url',
				'rev_video_tags',
				'rev_video_duration',
				'rev_upload_user',
				'rev_upload_date',
			),
			array(
				'rev_page_id' => $page_id
			),
			__METHOD__
		);
		if ( $res ) {
			$vide_info = array();
			$i = 0;
			foreach ($res as $key => $value) {
				$video_info[$i] = array(
					'rev_id' => $value->rev_id,
					'rev_page_id' => $value->rev_page_id,
					'rev_video_id' => $value->rev_video_id,
					'rev_video_title' => $value->rev_video_title,
					'rev_video_from' => $value->rev_video_from,
					'rev_video_player_url' => $value->rev_video_player_url,
					'rev_video_tags' => $value->rev_video_tags,
					'rev_video_duration' => $value->rev_video_duration,
					'rev_upload_user' => $value->rev_upload_user,
					'rev_upload_date' => $value->rev_upload_date,
				);
				$i++;
			}
			foreach ($video_info as $key => $value) {
				$dbw->insert(
					'video_archive',
					array(
						'ar_rev_id' => $value['rev_id'],
						'ar_page_id' => $value['rev_page_id'],
						'ar_video_id' => $value['rev_video_id'],
						'ar_video_title' => $value['rev_video_title'],
						'ar_video_from' => $value['rev_video_from'],
						'ar_video_player_url' => $value['rev_video_player_url'],
						'ar_video_tags' => $value['rev_video_tags'],
						'ar_video_duration' => $value['rev_video_duration'],
						'ar_upload_user' => $value['rev_upload_user'],
						'ar_upload_date' => $value['rev_upload_date'],
						'ar_date' => date( 'Y-m-d H:i:s' ),
					),
					__METHOD__
				);
			}
			
			if ( $dbw->insertId() ) {
				$dbw->delete(
					'video_page',
					array(
						'page_id' => $page_id
					),
					__METHOD__
				);
				$dbw->delete(
					'video_revision',
					array(
						'rev_page_id' => $page_id
					),
					__METHOD__
				);
				$result = true;
			}
		}
		return $result;
	}
	/**
	 * restore video info
	 */
	static function restoreVideoInfo( $pageId, $oldPageId, $videoRevision ){
		$dbw = wfGetDB( DB_MASTER );
		$res = $dbw->select(
			'video_archive',
			array(
				'ar_rev_id',
				'ar_page_id',
				'ar_video_id',
				'ar_video_title',
				'ar_video_from',
				'ar_video_player_url',
				'ar_video_tags',
				'ar_video_duration',
				'ar_upload_user',
				'ar_upload_date'
			),
			array(
				'ar_page_id' => $oldPageId
			),
			__METHOD__
		);
		if ( $res ) {
			$result = array();
			$j = 0;
			foreach ($res as $key => $value) {
				$result[$j] = array(
						'ar_rev_id' => $value->ar_rev_id,
						'ar_page_id' => $value->ar_page_id,
						'ar_video_id' => $value->ar_video_id,
						'ar_video_title' => $value->ar_video_title,
						'ar_video_from' => $value->ar_video_from,
						'ar_video_player_url' => $value->ar_video_player_url,
						'ar_video_tags' => $value->ar_video_tags,
						'ar_video_duration' => $value->ar_video_duration,
						'ar_upload_user' => $value->ar_upload_user,
						'ar_upload_date' => $value->ar_upload_date
					);
				$j++;
			}
			foreach ($result as $key => $value) {
				$dbw->insert(
					'video_revision',
					array(
						'rev_id' => $value['ar_rev_id'],
						'rev_page_id' => $value['ar_page_id'],
						'rev_video_id' => $value['ar_video_id'],
						'rev_video_title' => $value['ar_video_title'],
						'rev_video_from' => $value['ar_video_from'],
						'rev_video_player_url' => $value['ar_video_player_url'],
						'rev_video_tags' => $value['ar_video_tags'],
						'rev_video_duration' => $value['ar_video_duration'],
						'rev_upload_user' => $value['ar_upload_user'],
						'rev_upload_date' => $value['ar_upload_date']
					),
					__METHOD__
				);
			}
			
			$dbw->insert(
				'video_page',
				array(
					'page_id' => $pageId,
					'revision_id' => $videoRevision
				),
				__METHOD__
			);
			if ( $dbw->insertId() ) {
				$dbw->delete(
					'video_archive',
					array(
						'ar_page_id' => $oldPageId
					),
					__METHOD__
				);
			}
			
		}
	}
	/**
	 * add revision binder
	 */
	static function addRevisionBinder( $thumSha1, $videoRevision ){
		$dbw = wfGetDB( DB_MASTER );
		$dbw->insert(
				'revision_binder',
				array(
					'thum_sha1' => $thumSha1,
					'video_revision' => $videoRevision
				),
				__METHOD__
			);
		if ( $dbw->insertId() ) {
			return true;
		}
	}
	/**
	 * get video_revision by page_revision
	 */
	static function getVideoRevisionByPageRevision( $thumSha1 ){
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
				'revision_binder',
				array(
					'video_revision'
				),
				array(
					'thum_sha1' => $thumSha1
				),
				__METHOD__
			);
		$videoRevsion = '';
		if ( $res ) {
			foreach ($res as $key => $value) {
				$videoRevsion = $value->video_revision;
				return $videoRevsion;
			}
		}
		return $videoRevsion;
	}
	/**
	 * update page_video set revisionid= video_revision_id
	 */
	static function updateVideoPage( $pageId, $revisionId ){
		$dbw = wfGetDB( DB_MASTER );
		$dbw->update(
			'video_page',
			array(
				'revision_id' => $revisionId
			),
			array(
				'page_id' => $pageId
			),
			__METHOD__
		);
	}
	/**
	 * get all video info
	 */
	static function getAllVideoInfo(){
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'video_page',
			array(
				'page_id',
				'revision_id'
			),
			array(),
			__METHOD__
		);
		if ( $res ) {
			$result = array();
			// $i = 0;
			foreach ($res as $key => $value) {
				$result[$value->page_id] = self::getDetailVideoInfoById( $value->revision_id );
				// $result[$i] = array(
				// 				'page_id' => $value->page_id,
				// 				'revision_id' => $value->revision_id 
				// 			);
				// $i++;
			}
			return $result;
		}
	}
	/**
	 * get video detail info by revisionId
	 */
	static function getDetailVideoInfoById( $revisionId ){
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'video_revision',
			array(
				'rev_page_id',
				'rev_video_id',
				'rev_video_title',
				'rev_video_from',
				'rev_video_player_url',
				'rev_video_tags',
				'rev_video_duration',
				'rev_upload_user',
				'rev_upload_date'
			),
			array(
				'rev_id' => $revisionId
			),
			__METHOD__
		);
		if ( $res ) {
			$result = array();
			foreach ($res as $key => $value) {
				$result = array(
						'rev_page_id' => $value->rev_page_id,
						'rev_video_id' => $value->rev_video_id,
						'rev_video_title' => $value->rev_video_title,
						'rev_video_from' => $value->rev_video_from,
						'rev_video_player_url' => $value->rev_video_player_url,
						'rev_video_tags' => $value->rev_video_tags,
						'rev_video_duration' => $value->rev_video_duration,
						'rev_upload_user' => $value->rev_upload_user,
						'rev_upload_date' => $value->rev_upload_date
					);
			}
			return $result;
		}
	}
}
/**
 * A mock up title class that tries to manipulate external videos
 * @auther
 */
Class VideoTitle extends Title{
	protected /* timestamp */$mDuration;
	protected /* string */$mTags;
	protected /* string, such as 'youku' */$mVideoSource;
	protected /* string */$mExternalId;
	protected /* url */$mPlayerUrl;
	protected /* userid */$mAddedByUser;
	protected /* date */$mAddedOnDate;
	protected /* int */$mVideoRevisionId;
	protected /* string */$mVideoLink;
	const /* string */YOUKULINK = 'http://v.youku.com/v_show/id_';
	function __construct(){
		parent::__construct();
	}
	/**
	 * determine whether the given title is a video title.
	 * @param Title $title: a title object in mediawiki.
	 */
	static function isVideoTitle(Title $title){
		if ($title->isExternal()){
			if ($title->getInterwiki() == 'www'){
				$DB = 'huiji_home';
			} else {
				$DB = 'huiji_sites-'.str_replace('.', '_', $title->getInterwiki());
			}
			wfErrorLog($title->getDbKey().$title->getInterwiki(), '/var/log/mediawiki/SocialProfile.log');
			$dbr = wfGetDB( DB_SLAVE, '', $DB );
			$res = $dbr->select(
						'page',
						'page_id',
						array(
							'page_title' => $title->getDbKey(),
							'page_namespace' => NS_FILE,
						),
						__METHOD__
					);
			if ( $res ){
				foreach ($res as $key => $value) {
					$pageId = $value->page_id;
				}			
			} else {
				return false;
			}
			if (!isset($pageId)){
				return false;
			}
			$res = $dbr->select(
				'video_page',
				array(
					'page_id'
				),
				array(
					'page_id' => $pageId
				),
				__METHOD__
			);
			$videoPageId = false;
			foreach ($res as $key => $value) {
				$videoPageId = $value->page_id;
			}
			if ( $videoPageId ) {
				return true;
			}else{
				return false;
			}
		} else {
			$pageId = $title->getArticleID();
			return self::isVideoTitleId($pageId);			
		}
	}
	static function isVideoTitleId($pageId){
		global $wgMemc;
		$key = wfMemcKey( 'isvideo', 'by pageId', $pageId );
		$data = $wgMemc->get( $key );
		if ( $data ) {
			if ( $data == 0 ) {
				return false;
			}else{
				return true;
			}
		}else{
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select(
				'video_page',
				array(
					'page_id'
				),
				array(
					'page_id' => $pageId
				),
				__METHOD__
			);
			$videoPageId = false;
			foreach ($res as $key => $value) {
				$videoPageId = $value->page_id;
			}
			if ( $videoPageId ) {
				$wgMemc->set( $key, $videoPageId );
				return true;
			}else{
				$wgMemc->set( $key, 0 );
				return false;
			}
		}		
	}
	static function isVideoTitleIdByArchive( $pageId ){
		global $wgMemc;
		$key = wfMemcKey( 'isvideo', 'by pageId', $pageId );
		$data = $wgMemc->get( $key );
		if ( $data ) {
			if ( $data == 0 ) {
				return false;
			}else{
				return true;
			}
		}else{
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select(
				'video_archive',
				array(
					'ar_page_id'
				),
				array(
					'ar_page_id' => $pageId
				),
				__METHOD__
			);
			
			foreach ($res as $key => $value) {
				$videoPageId = $value->ar_page_id;
			}
			if ( $videoPageId ) {
				$wgMemc->set( $key, $videoPageId );
				return true;
			}else{
				$wgMemc->set( $key, 0 );
				return false;
			}
		}
	}
    public static function newFromRow( $row ) {
       $t = self::makeTitle( $row->page_namespace, $row->page_title );
       $t->loadFromRow( $row );
       return $t;
   }
	public static function newFromText( $text, $defaultNamespace = NS_MAIN ) {
		if ( is_object( $text ) ) {
		   throw new InvalidArgumentException( '$text must be a string.' );
		}
		// DWIM: Integers can be passed in here when page titles are used as array keys.
		if ( $text !== null && !is_string( $text ) && !is_int( $text ) ) {
		   wfDebugLog( 'T76305', wfGetAllCallers( 5 ) );
		   return null;
		}
		if ( $text === null ) {
		   return null;
		}
		try {
		   return self::newFromTextThrow( strval( $text ), $defaultNamespace );
		} catch ( MalformedTitleException $ex ) {
		   return null;
		}
	}
	public static function newFromTextThrow( $text, $defaultNamespace = NS_MAIN ) {
		if ( is_object( $text ) ) {
		   throw new MWException( '$text must be a string, given an object' );
		}
	   	// Convert things like &eacute; &#257; or &#x3017; into normalized (bug 14952) text
	   	$filteredText = Sanitizer::decodeCharReferencesAndNormalize( $text );
		$t = new VideoTitle();
		$t->mDbkeyform = strtr( $filteredText, ' ', '_' );
		$t->mDefaultNamespace = intval( $defaultNamespace );
		$t->secureAndSplit();
		if ($t->isExternal()){
			$t->loadFromExternalDB($t->getInterwiki(), $t->mDbkeyform);
		}
	 	return $t;
	}
	private function secureAndSplit() {
	  # Initialisation
	  $this->mInterwiki = '';
	  $this->mFragment = '';
	  $this->mNamespace = $this->mDefaultNamespace; # Usually NS_MAIN
	  $dbkey = $this->mDbkeyform;
	  // @note: splitTitleString() is a temporary hack to allow MediaWikiTitleCodec to share
	  //        the parsing code with Title, while avoiding massive refactoring.
	  // @todo: get rid of secureAndSplit, refactor parsing code.
	  $titleParser = self::getMediaWikiTitleCodec();
	  // MalformedTitleException can be thrown here
	  $parts = $titleParser->splitTitleString( $dbkey, $this->getDefaultNamespace() );
	  # Fill fields
	  $this->setFragment( '#' . $parts['fragment'] );
	  $this->mInterwiki = $parts['interwiki'];
	  $this->mLocalInterwiki = $parts['local_interwiki'];
	  $this->mNamespace = $parts['namespace'];
	  $this->mUserCaseDBKey = $parts['user_case_dbkey'];
	  $this->mDbkeyform = $parts['dbkey'];
	  $this->mUrlform = wfUrlencode( $this->mDbkeyform );
	  $this->mTextform = strtr( $this->mDbkeyform, '_', ' ' );
	  # We already know that some pages won't be in the database!
	  if ( $this->isExternal() || $this->mNamespace == NS_SPECIAL ) {
	      $this->mArticleID = 0;
	  }
	  return true;
	}
	private static function getMediaWikiTitleCodec() {
	   global $wgContLang, $wgLocalInterwikis;
	   static $titleCodec = null;
	   static $titleCodecFingerprint = null;
	   // $wgContLang and $wgLocalInterwikis may change (especially while testing),
	   // make sure we are using the right one. To detect changes over the course
	   // of a request, we remember a fingerprint of the config used to create the
	   // codec singleton, and re-create it if the fingerprint doesn't match.
	   $fingerprint = spl_object_hash( $wgContLang ) . '|' . join( '+', $wgLocalInterwikis );
	   if ( $fingerprint !== $titleCodecFingerprint ) {
	       $titleCodec = null;
	   }
	   if ( !$titleCodec ) {
	       $titleCodec = new MediaWikiTitleCodec(
	           $wgContLang,
	           GenderCache::singleton(),
	           $wgLocalInterwikis
	       );
	       $titleCodecFingerprint = $fingerprint;
	   }
	   return $titleCodec;
	}
	private static function getTitleFormatter() {
	   // NOTE: we know that getMediaWikiTitleCodec() returns a MediaWikiTitleCodec,
	   //      which implements TitleFormatter.
	   return self::getMediaWikiTitleCodec();
	}
	/**
	 *
	 */
	public function loadFromExternalDB( $prefix, $text ) {
		$video_info = self::getVideoInfoByPrefixAndText( $prefix, $text );
		$this->mDuration = !is_null( $video_info['rev_video_duration'] ) ? $video_info['rev_video_duration'] : '';
		$this->mTags = !is_null( $video_info['rev_video_tags'] ) ? $video_info['rev_video_tags'] : '';
		$this->mVideoSource = !is_null( $video_info['rev_video_from'] ) ? $video_info['rev_video_from'] : '';
		$this->mExternalId = !is_null( $video_info['rev_video_id'] ) ? $video_info['rev_video_id'] : '';
		$this->mPlayerUrl = !is_null( $video_info['rev_video_player_url'] ) ? $video_info['rev_video_player_url'] : '';
		$this->mAddedByUser = !is_null( $video_info['rev_upload_user'] ) ? $video_info['rev_upload_user'] : '';
		$this->mAddedOnDate = !is_null( $video_info['rev_upload_date'] ) ? $video_info['rev_upload_date'] : '';
		$this->mVideoRevisionId = !is_null( $video_info['rev_id'] ) ? $video_info['rev_id'] : '';
	}
	/**
	 *
	 */
	public function loadFromRow( $row ) {
		/**
		 * Also load video title protected fields
		 */
		$pageId = $row->page_id;
		$video_info = self::getVideoInfoByPageId( $pageId );
		$this->mDuration = !is_null( $video_info['rev_video_duration'] ) ? $video_info['rev_video_duration'] : '';
		$this->mTags = !is_null( $video_info['rev_video_tags'] ) ? $video_info['rev_video_tags'] : '';
		$this->mVideoSource = !is_null( $video_info['rev_video_from'] ) ? $video_info['rev_video_from'] : '';
		$this->mExternalId = !is_null( $video_info['rev_video_id'] ) ? $video_info['rev_video_id'] : '';
		$this->mPlayerUrl = !is_null( $video_info['rev_video_player_url'] ) ? $video_info['rev_video_player_url'] : '';
		$this->mAddedByUser = !is_null( $video_info['rev_upload_user'] ) ? $video_info['rev_upload_user'] : '';
		$this->mAddedOnDate = !is_null( $video_info['rev_upload_date'] ) ? $video_info['rev_upload_date'] : '';
		$this->mVideoRevisionId = !is_null( $video_info['rev_id'] ) ? $video_info['rev_id'] : '';
		parent::loadFromRow( $row );
	}
	/**
	 * new from id
	 */
	 public static function newFromID( $id, $flags = 0 ) {
        $db = ( $flags & self::GAID_FOR_UPDATE ) ? wfGetDB( DB_MASTER ) : wfGetDB( DB_SLAVE );
        $row = $db->selectRow(
           'page',
           self::getSelectFields(),
           [ 'page_id' => $id ],
           __METHOD__
       );
       if ( $row !== false ) {
           $title = self::newFromRow( $row );
       } else {
           $title = null;
       }
       return $title;
    }
   /**
    * make videtitle
    */
    public static function &makeTitle( $ns, $title, $fragment = '', $interwiki = '' ) {
       $t = new VideoTitle();
       $t->mInterwiki = $interwiki;
       $t->mFragment = $fragment;
       $t->mNamespace = $ns = intval( $ns );
       $t->mDbkeyform = strtr( $title, ' ', '_' );
       $t->mArticleID = ( $ns >= 0 ) ? -1 : 0;
       $t->mUrlform = wfUrlencode( $t->mDbkeyform );
       $t->mTextform = strtr( $title, '_', ' ' );
       $t->mContentModel = false; # initialized lazily in getContentModel()
       return $t;
   }
	/**
	 * Getters
	 */
	public function getDuration($formatted = true){
		if ($formatted == true) {
			return gmstrftime( '%H:%M:%S',$this->mDuration );
		}
		return $this->mDuration;
	}
	public function getTags(){
		return $this->mTags;
		
	}
	public function getVideoSource(){
		return $this->mVideoSource;
		
	}
	public function getExternalId(){
		return $this->mExternalId;
		
	}
	public function getPlayerUrl(){
		return $this->mPlayerUrl;
		
	}
	public function getAddedByUser(){
		return $this->mAddedByUser;
		
	}
	public function getAddedOnDate(){
		return $this->mAddedOnDate;
		
	}
	public function getVideoRevisionId(){
		return $this->mVideoRevisionId;
	}
	public function getVideoLink(){
		if ( $this->getVideoSource() == 'youku') {
			return self::YOUKULINK.$this->getExternalId();
		}
	}
	/**
	 * Generate HTML ready thumbnails.
	 */
	public function getThumbnail($w = 200, $h = 100, $repoArray = null, $asyn = true){
		global $wgLocalFileRepo;
		if ($repoArray == null){
			$repo = new LocalRepo($wgLocalFileRepo);
			$file = LocalFile::newFromTitle($this, $repo);
		} else {
			$repo = new ForeignDBRepo($repoArray);
			$file = ForeignDBFile::newFromTitle($this, $repo);
		}
		$class= $asyn?"video-player video-player-asyn":"video-player";
        $output ='
        <a href="#" class="video video-thumbnail image"><img class="'.$class.'" src="'.htmlspecialchars( $file->createThumb($w, $h) ).'" alt="'.$this->getText().'" data-video-title="'.$this->getText().'" data-video="'.$this->getPlayerUrl().'" data-video-from="'.$this->getVideoSource().'" data-video-link="'.$this->getVideoLink().'" data-video-duration="'.$this->getDuration().'" /></a>';
		return $output;
	}
	static function getVideoInfoByPrefixAndText( $prefix, $text ){
		if ($prefix == 'www'){
			$DB = 'huiji_home';
		} else {
			$DB = 'huiji_sites-'.str_replace('.', '_', $prefix);
		}
		wfErrorLog($DB, '/var/log/mediawiki/SocialProfile.log');
		$dbr = wfGetDB( DB_SLAVE, '', $DB );
		$res = $dbr->select(
					'page',
					'page_id',
					array(
						'page_title' => $text,
						'page_namespace' => NS_FILE,
					),
					__METHOD__
				);
		if ( $res ){
			foreach ($res as $key => $value) {
				$pageId = $value->page_id;
			}			
		} else {
			return;
		}
		$res = $dbr->select(
					'video_page',
					array(
						'revision_id'
					),
					array(
						'page_id' => $pageId,
					),
					__METHOD__
				);
		if ( $res ) {
			foreach ($res as $key => $value) {
				$revisionId = $value->revision_id;
			}
			$req = $dbr->select(
				'video_revision',
				array(
					'rev_id',
					'rev_page_id',
					'rev_video_id',
					'rev_video_title',
					'rev_video_from',
					'rev_video_player_url',
					'rev_video_tags',
					'rev_video_duration',
					'rev_upload_user',
					'rev_upload_date'
				),
				array(
					'rev_id' => $revisionId,
				),
				__METHOD__
			);
			if ( $req ) {
				$videoInfo = array();
				foreach ($req as $key => $value) {
					$videoInfo = array(
									'rev_id' => $revisionId,
									'rev_page_id' => $value->rev_page_id,
									'rev_video_id' => $value->rev_video_id,
									'rev_video_title' => $value->rev_video_title,
									'rev_video_from' => $value->rev_video_from,
									'rev_video_player_url' => $value->rev_video_player_url,
									'rev_video_tags' => $value->rev_video_tags,
									'rev_video_duration' => $value->rev_video_duration,
									'rev_upload_user' => $value->rev_upload_user,
									'rev_upload_date' => $value->rev_upload_date
								);
				}
			}
			return $videoInfo;
			
		}
	}
	static function getVideoInfoByPageId( $pageId ){
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
					'video_page',
					array(
						'revision_id'
					),
					array(
						'page_id' => $pageId
					),
					__METHOD__
				);
		if ( $res ) {
			foreach ($res as $key => $value) {
				$revisionId = $value->revision_id;
			}
			$req = $dbr->select(
				'video_revision',
				array(
					'rev_id',
					'rev_page_id',
					'rev_video_id',
					'rev_video_title',
					'rev_video_from',
					'rev_video_player_url',
					'rev_video_tags',
					'rev_video_duration',
					'rev_upload_user',
					'rev_upload_date'
				),
				array(
					'rev_id' => $revisionId,
				),
				__METHOD__
			);
			if ( $req ) {
				$videoInfo = array();
				foreach ($req as $key => $value) {
					$videoInfo = array(
									'rev_id' => $revisionId,
									'rev_page_id' => $value->rev_page_id,
									'rev_video_id' => $value->rev_video_id,
									'rev_video_title' => $value->rev_video_title,
									'rev_video_from' => $value->rev_video_from,
									'rev_video_player_url' => $value->rev_video_player_url,
									'rev_video_tags' => $value->rev_video_tags,
									'rev_video_duration' => $value->rev_video_duration,
									'rev_upload_user' => $value->rev_upload_user,
									'rev_upload_date' => $value->rev_upload_date
								);
				}
			}
			return $videoInfo;
			
		}
	}
}
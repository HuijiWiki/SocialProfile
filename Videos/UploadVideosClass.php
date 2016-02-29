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
		$pageId = $title->getArticleID();
		return self::isVideoTitleId($pageId);
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
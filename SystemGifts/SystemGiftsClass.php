<?php
/**
 * SystemGifts class
 */
class SystemGifts {

	/**
	 * All member variables should be considered private
	 * Please use the accessor functions
	 */
	private $categories = array(
		'edit' => 1,
		'vote' => 2,
		'comment' => 3,
		'comment_plus' => 4,
		'opinions_created' => 5,
		'opinions_pub' => 6,
		'referral_complete' => 7,
		'friend' => 8,
		'foe' => 9,
		'challenges_won' => 10,
		'gift_rec' => 11,
		'points_winner_weekly' => 12,
		'points_winner_monthly' => 13,
		'quiz_points' => 14,
		'points_finalist_weekly' => 15,
		'points_finalist_monthly' => 16,
		'points_firstthree_weekly' => 17,
		'points_firstthree_monthly' => 18,
		'42' => 19,
		'不可重复' => 20,
		'特别礼物' => 21,
		'连续编辑' => 22,
		'节日' => 23,
	);

	private $repeatableGifts = array( 7, 12, 13, 15, 16, 17, 18, 19, 23 );

	/**
	 * Accessor for the private $categories variable; used by
	 * SpecialSystemGiftManager.php at least.
	 */
	public function getCategories() {
		return $this->categories;
	}

	public function getRepeatableGifts() {
		return $this->repeatableGifts;
	}


	/**
	 * Adds awards for all registered users, updates statistics and purges
	 * caches.
	 * Special:PopulateAwards calls this function
	 */
	public function update_system_gifts( $giftId='' ) {
		global $wgOut, $wgMemc;

		$dbw = wfGetDB( DB_MASTER );
		$stats = new UserStatsTrack( 1, '' );
		$this->categories = array_flip( $this->categories );

		$res = $dbw->select(
			'system_gift',
			array( 'gift_id', 'gift_category', 'gift_threshold', 'gift_name', 'designation' ),
			array( 
				'gift_id' => $giftId
			),
			__METHOD__,
			array( 'ORDER BY' => 'gift_category, gift_threshold ASC' )
		);
		$x = 0;
		foreach ( $res as $row ) {
			$dbw->update(
				'user_title',
				array('title_content' => $row->designation,
					),
				array('gift_id' => $row->gift_id,
					'title_from' => 'system_gift'),
				__METHOD__
			);
			if ( $row->gift_category && !in_array( $row->gift_category, $this->repeatableGifts ) && !empty($stats->stats_fields[$this->categories[$row->gift_category]]) ) {
				$res2 = $dbw->select(
					'user_stats',
					array( 'stats_user_id', 'stats_user_name' ),
					array(
						$stats->stats_fields[$this->categories[$row->gift_category]] .
							" >= {$row->gift_threshold}",
						'stats_user_id <> 0'
					),
					__METHOD__
				);

				foreach ( $res2 as $row2 ) {
					if ( $this->doesUserHaveGift( $row2->stats_user_id, $row->gift_id ) == false ) {
						$dbw->insert(
							'user_system_gift',
							array(
								'sg_gift_id' => $row->gift_id,
								'sg_user_id' => $row2->stats_user_id,
								'sg_user_name' => $row2->stats_user_name,
								'sg_status' => 0,
								'sg_date' => date( 'Y-m-d H:i:s', time() - ( 60 * 60 * 24 * 3 ) ),
							),
							__METHOD__
						);

						//add into user designation table
						$gift = new UserGifts( $row2->stats_user_name );
						$gift->addUserGiftTitleInfo( $row->gift_id, $row2->stats_user_id, $row->gift_name, 'system_gift' );
						
						$sg_key = wfForeignMemcKey( 'huiji', '', 'user', 'profile', 'system_gifts', "{$row2->stats_user_id}" );
						$wgMemc->delete( $sg_key );

						// Update counters (bug #27981)
						UserSystemGifts::incGiftGivenCount( $row->gift_id );

						$wgOut->addHTML( wfMessage(
							'ga-user-got-awards',
							$row2->stats_user_name,
							$row->gift_name
						)->escaped() . '<br />' );
						$x++;
					}
				}
			}
		}

		$wgOut->addHTML( wfMessage( 'ga-awards-given-out' )->numParams( $x )->parse() );
	}

	/**
	 * Checks if the given user has then given award (system gift) via their ID
	 * numbers.
	 *
	 * @param $user_id Integer: user ID number
	 * @param $gift_id Integer: award (system gift) ID number
	 * @return Boolean|Integer: false if the user doesn't have the specified
	 *                          gift, else the gift's ID number
	 */
	public function doesUserHaveGift( $user_id, $gift_id ) {
		$dbr = wfGetDB( DB_SLAVE );
		$s = $dbr->selectRow(
			'user_system_gift',
			array( 'sg_gift_id' ),
			array( 'sg_gift_id' => $gift_id, 'sg_user_id' => $user_id ),
			__METHOD__
		);
		if ( $s === false ) {
			return false;
		} else {
			return $s->sg_gift_id;
		}
	}

	/**
	 * Adds a new system gift to the database.
	 *
	 * @param $name Mixed: gift name
	 * @param $description Mixed: gift description
	 * @param $category Integer: see the $categories class member variable
	 * @param $threshold Integer: threshold number (i.e. 50 or 100 or whatever)
	 * @return Integer: the inserted gift's ID number
	 */
	public function addGift( $name, $description, $category, $threshold, $gift_prefix, $designation ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->insert(
			'system_gift',
			array(
				'gift_name' => $name,
				'gift_description' => $description,
				'gift_category' => $category,
				'gift_threshold' => $threshold,
				'gift_createdate' => date( 'Y-m-d H:i:s' ),
				'designation' => $designation,
				'gift_prefix' => $gift_prefix
			),
			__METHOD__
		);
		return $dbw->insertId();
	}

	/**
	 * Updates the data for a system gift.
	 *
	 * @param $id Integer: system gift unique ID number
	 * @param $name Mixed: gift name
	 * @param $description Mixed: gift description
	 * @param $category
	 * @param $threshold
	 */
	public function updateGift( $id, $name, $description, $category, $threshold, $gift_prefix, $designation ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->update(
			'system_gift',
			/* SET */array(
				'gift_name' => $name,
				'gift_description' => $description,
				'gift_category' => $category,
				'gift_threshold' => $threshold,
				'designation' => $designation,
				'gift_prefix' => $gift_prefix
			),
			/* WHERE */array( 'gift_id' => $id ),
			__METHOD__
		);
	}

	public function doesGiftExistForThreshold( $category, $threshold ) {
		$dbr = wfGetDB( DB_SLAVE );

		$awardCategory = 0;
		if ( isset( $this->categories[$category] ) ) {
			$awardCategory = $this->categories[$category];
		}
		if ( in_array( $awardCategory, $this->repeatableGifts ) ){
			$s = $dbr->select(
				'system_gift',
				array( 'gift_id' ),
				array(
					'gift_category' => $awardCategory
				),
				__METHOD__
			);
		}else{
			$s = $dbr->select(
				'system_gift',
				array( 'gift_id' ),
				array(
					'gift_category' => $awardCategory,
					'gift_threshold' => $threshold
				),
				__METHOD__
			);
		}		
		if ( $s === false ) {
			return false;
		} else {
			$res = $result = array();
			foreach ($s as $value) {
				$res['gift_id'] = $value->gift_id;
				$result[] = $res;
			}
			return $result;
		}
	}

	/**
	 * Fetches the system gift with the ID $id from the database
	 * @param $id Integer: ID number of the system gift to be fetched
	 * @return Array: array of gift information, including, but not limited to,
	 *                the gift ID, its name, description, category, threshold
	 */
	static function getGift( $id ) {
		$dbr = wfGetDB( DB_SLAVE );
		$gift = array();
		$res = $dbr->select(
			'system_gift',
			array(
				'gift_id', 'gift_name', 'gift_description', 'gift_category',
				'gift_threshold', 'gift_given_count','designation','gift_prefix'
			),
			array( 'gift_id' => $id ),
			__METHOD__,
			array( 'LIMIT' => 1 )
		);
		$row = $dbr->fetchObject( $res );
		if ( $row ) {
			$gift['gift_id'] = $row->gift_id;
			$gift['gift_name'] = $row->gift_name;
			$gift['gift_description'] = $row->gift_description;
			$gift['gift_category'] = $row->gift_category;
			$gift['gift_threshold'] = $row->gift_threshold;
			$gift['gift_given_count'] = $row->gift_given_count;
			$gift['designation'] = $row->designation;
			$gift['gift_prefix'] = $row->gift_prefix;
		}
		return $gift;
	}

	/**
	 * Gets the associated image for a system gift.
	 *
	 * @param $id Integer: system gift ID number
	 * @param $size String: image size (s, m, ml or l)
	 * @return String: gift image filename (following the format
	 *                 sg_ID_SIZE.ext; for example, sg_1_l.jpg)
	 */
	// static function getGiftImage( $id, $size ) {
	// 	global $wgUploadDirectory;
	// 	$files = glob( $wgUploadDirectory . '/awards/sg_' . $id . '_' . $size . '*' );

	// 	if ( !empty( $files[0] ) ) {
	// 		$img = basename( $files[0] );
	// 	} else {
	// 		$img = 'default_' . $size . '.gif';
	// 	}

	// 	return $img . '?r=' . rand();
	// }
	static function getGiftImage( $id, $size ) {
		global $wgUploadDirectory, $wgUseOss;
		if($wgUseOss){
			$logger = MediaWiki\Logger\LoggerFactory::getInstance( 'filesystem' );
            // $accessKeyId = Confidential::$aliyunKey;
            // $accessKeySecret = Confidential::$aliyunSecret;
            // $endpoint = $wgOssEndpoint;
            try {
                $ossClient = OssFileBackend::getOssClient();			
	            $bucket = Gifts::GIFT_BUCKET;
	            $avatar_filename = 'sg_'.$id .  '_' . $size  ;
	            $jpgDoesExist = $ossClient->doesObjectExist($bucket, $avatar_filename . ".jpg");
	            if ($jpgDoesExist){
	            	$avatar_filename .= ".jpg";
	            	return $avatar_filename;
	            }
	            $pngDoesExist = $ossClient->doesObjectExist($bucket, $avatar_filename . ".png");
	            if ($pngDoesExist){
	            	$avatar_filename .= ".png";
	            	return $avatar_filename;
	            }
				$gifDoesExist = $ossClient->doesObjectExist($bucket, $avatar_filename . ".gif");  
				if ($gifDoesExist){
	            	$avatar_filename .= ".gif";
	            	return $avatar_filename;
				} 
            } catch (OSS\Core\OssException $e) {
                $logger->error($e->getMessage());
            }
			$avatar_filename = 'sg_default_' . $size . '.gif';
			return $avatar_filename;
		}
		$files = glob( $wgUploadDirectory . '/awards/' . $id .  '_' . $size . "*" );

		if ( !empty( $files[0] ) ) {
			$img = basename( $files[0] );
		} else {
			$img = 'default_' . $size . '.gif';
		}
		return $img;
	}
	static function getGiftImageUrl($id, $size) {
		global $wgUseOss, $wgUploadDirectory;
		if ($wgUseOss){
			return "http://aw.huijiwiki.com/".self::getGiftImage($id, $size);
		} else {
			return $wgUploadDirectory . '/awards/'.self::getGiftImage($id, $size);
		}
		
	}
	static function getGiftImageTag($id, $size, $attr = null) {
		$realAttr = array();
		$defaultAttr = array("class"=>"huiji-award", "src" => self::getGiftImageUrl($id, $size), "alt"=>"gift");
		if ($attr !== null){
			$realAttr = array_merge($defaultAttr, $realAttr); 
		} else {
			$realAttr = $defaultAttr;
		}
		$tag = Xml::element("img", $realAttr, null );
		return $tag;
	}

	/**
	 * Get the list of all existing system gifts (awards).
	 *
	 * @param $limit Integer: LIMIT for the SQL query, 0 by default
	 * @param $page Integer: used to determine OFFSET for the SQL query;
	 *                       0 by default
	 * @return Array: array containing gift info, including (but not limited
	 *                to) gift ID, creation timestamp, name, description, etc.
	 */
	static function getGiftList( $limit = 0, $page = 0 ) {
		$dbr = wfGetDB( DB_SLAVE );

		$limitvalue = 0;
		if ( $limit > 0 && $page ) {
			$limitvalue = $page * $limit - ( $limit );
		}

		$res = $dbr->select(
			'system_gift',
			array(
				'gift_id', 'gift_createdate', 'gift_name', 'gift_description',
				'gift_category', 'gift_threshold', 'gift_given_count'
			),
			array(),
			__METHOD__,
			array(
				'ORDER BY' => 'gift_createdate DESC',
				'LIMIT' => $limit,
				'OFFSET' => $limitvalue
			)
		);

		$gifts = array();
		foreach ( $res as $row ) {
			$gifts[] = array(
				'id' => $row->gift_id,
				'timestamp' => ( $row->gift_createdate ),
				'gift_name' => $row->gift_name,
				'gift_description' => $row->gift_description,
				'gift_category' => $row->gift_category,
				'gift_threshold' => $row->gift_threshold,
				'gift_given_count' => $row->gift_given_count
			);
		}

		return $gifts;
	}

	/**
	 * Gets the amount of available system gifts from the database.
	 *
	 * @return Integer: the amount of all system gifts on the database
	 */
	static function getGiftCount() {
		$dbr = wfGetDB( DB_SLAVE );
		$gift_count = 0;
		$s = $dbr->selectRow(
			'system_gift',
			array( 'COUNT(*) AS count' ),
			array(),
			__METHOD__
		);
		if ( $s !== false ) {
			$gift_count = $s->count;
		}
		return $gift_count;
	}

	/**
	 *check if the total edits 42424242 
	 */
	static function checkEditsCounts( $num ){
		$num = ''.$num;
		$arr = str_split($num);
		$arrlen = count($arr);
		if( $arr[$arrlen-1] == 4 ){
			return false;
		}
		$x = 0;
		foreach($arr as $val){
		    if ($val == 4){
		        if($x == 1){
		            return false;
		        }else{
		            $x++;
		        }
		    }elseif($val == 2){
		        if($x == 0){
		            return false;
		        }else{
		            $x--;
		        }        
		    }elseif($val ==0){
		        if($x == 1){
		            return false;
		        }
		    }else{
		        return false;
		    }
		}
		return true;
	}

	/**
	 * add festival gift to database
	 * @param $festival $editNum $startTime $endTime
	 */
	static function addFestivalGift( $giftId, $editNum, $startTime, $endTime ){

		global $wgMemc;
		if ( $giftId != null && $editNum != null && $startTime != null && $endTime != null ) {
			if( $startTime > $endTime ){
				return false;
			}
			$gInfo = self::getInfoFromFestivalGift();
			foreach ($gInfo as $value) {
				$giftIdArr[] = $value['giftId'];
			}
			// return $giftIdArr;die;
			if(in_array($giftId, $giftIdArr)){
				return false;
			}
			$dbw = wfGetDB( DB_MASTER );
			$res = $dbw->insert(
				'festival_gift',
				array(
					'startTime' => $startTime,
					'endTime' => $endTime,
					'giftId' => $giftId,
					'editNum' => $editNum,
					'addTime' => date( 'Y-m-d H:i:s', time() ),
				),
				__METHOD__
			);
			if($res === true){
				$wgMemc->delete( wfForeignMemcKey('huiji','', 'FestivalGiftInfo', 'all', 'festivalgiftlist' ) );
				return $dbw->insertId();
			}else{
				return false;
			}
		}

	}

	/**
	 * get festival gift from festival_gift
	 */
	
	static function getInfoFromFestivalGift(){

		$data = self::getInfoFromFestivalGiftCache();
		if ( $data == null ) {
			$data = self::getInfoFromFestivalGiftDB();
		}
		return $data;

	}
	static function getInfoFromFestivalGiftCache(){

		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'FestivalGiftInfo', 'all', 'festivalgiftlist' );
		$data = $wgMemc->get( $key );
		return $data;

	}
	static function getInfoFromFestivalGiftDB(){

		global $wgMemc;
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'festival_gift',
			array(
				'startTime',
				'endTime',
				'giftId',
				'editNum',
				'addTime'
			),
			array(),
			__METHOD__,
			array( 'ORDER BY' => 'addTime DESC' )
		);
		if( $res != false ){
			$reslut = $fData = array();
			foreach ($res as $value) {
				$fData['startTime'] = $value->startTime;
				$fData['endTime'] = $value->endTime;
				$fData['giftId'] = $value->giftId;
				$fData['editNum'] = $value->editNum;
				$fData['addTime'] = $value->addTime;
				$reslut[] = $fData;
			}
			$key = wfForeignMemcKey('huiji','', 'FestivalGiftInfo', 'all', 'festivalgiftlist' );
			$wgMemc->set( $key, $reslut );
			return $reslut;
		}

	}

}

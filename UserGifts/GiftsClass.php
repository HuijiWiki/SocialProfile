<?php
/**
 * Gifts class
 * Functions for managing individual social gifts
 * (add to/fetch/remove from database etc.)
 */
class Gifts {
	const GIFT_BUCKET = "huiji-award";
	/**
	 * Constructor
	 */
	public function __construct() {}

	/**
	 * Adds a gift to the database
	 * @param $gift_name Mixed: name of the gift, as supplied by the user
	 * @param $gift_description Mixed: a short description about the gift, as supplied by the user
	 * @param $gift_access Integer: 0 by default
	 */
	static function addGift( $gift_name, $gift_description, $gift_group = 1, $repeat, $gift_prefix, $gift_type, $designation ) {
		global $wgUser;

		$dbw = wfGetDB( DB_MASTER );

		$dbw->insert(
			'gift',
			array(
				'gift_name' => $gift_name,
				'gift_description' => $gift_description,
				'gift_createdate' => date( 'Y-m-d H:i:s' ),
				'gift_creator_user_id' => $wgUser->getID(),
				'gift_creator_user_name' => $wgUser->getName(),
				'gift_group' => $gift_group,
				'isrepeat' => $repeat,
				'gift_prefix' => $gift_prefix,
				'gift_type' => $gift_type,
				'designation' => $designation
			), __METHOD__
		);
		return $dbw->insertId();
	}

	/**
	 * Updates a gift's info in the database
	 * @param $id Integer: internal ID number of the gift that we want to update
	 * @param $gift_name Mixed: name of the gift, as supplied by the user
	 * @param $gift_description Mixed: a short description about the gift, as supplied by the user
	 * @param $gift_access Integer: 0 by default
	 */
	public static function updateGift( $id, $gift_name, $gift_description, $gift_group = 1, $repeat, $gift_prefix, $gift_type, $designation ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->update( 'gift',
			/* SET */array(
				'gift_name' => $gift_name,
				'gift_description' => $gift_description,
				'gift_group' => $gift_group,
				'isrepeat' => $repeat,
				'gift_prefix' => $gift_prefix,
				'gift_type' => $gift_type,
				'designation' => $designation
			),
			/* WHERE */array( 'gift_id' => $id ),
			__METHOD__
		);
	}

	/**
	 * Gets information, such as name and description, about a given gift from the database
	 * @param $id Integer: internal ID number of the gift
	 * @return Gift information, including ID number, name, description, creator's user name and ID and gift access
	 */
	static function getGift( $id ) {
		if ( !is_numeric( $id ) ) {
			return '';
		}
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'gift',
			array(
				'gift_id', 'gift_name', 'gift_description',
				'gift_creator_user_id', 'gift_creator_user_name', 'gift_group', 'isrepeat','gift_prefix','gift_type','designation'
			),
			array( "gift_id = {$id}" ),
			__METHOD__,
			array( 'LIMIT' => 1, 'OFFSET' => 0 )
		);
		$row = $dbr->fetchObject( $res );
		$gift = array();
		if ( $row ) {
			$gift['gift_id'] = $row->gift_id;
			$gift['gift_name'] = $row->gift_name;
			$gift['gift_description'] = $row->gift_description;
			$gift['creator_user_id'] = $row->gift_creator_user_id;
			$gift['creator_user_name'] = $row->gift_creator_user_name;
			$gift['group'] = $row->gift_group;
			$gift['repeat'] = $row->isrepeat;
			$gift['gift_prefix'] = $row->gift_prefix;
			$gift['gift_type'] = $row->gift_type;
			$gift['designation'] = $row->designation;
		}
		return $gift;
	}

	static function getGiftImage( $id, $size ) {
		global $wgUploadDirectory, $wgUseOss;
		if($wgUseOss){

			$logger = MediaWiki\Logger\LoggerFactory::getInstance( 'filesystem' );
            // $accessKeyId = Confidential::$aliyunKey;
            // $accessKeySecret = Confidential::$aliyunSecret;
            // $endpoint = $wgOssEndpoint;
            try {
                $ossClient = OssFileBackend::getOssClient();
				
	            $bucket = self::GIFT_BUCKET;
	            $avatar_filename = $id .  '_' . $size  ;
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
			} catch (Oss\Core\OssException $e) {
                $logger->error($e->getMessage());
            }
			$avatar_filename = 'default_' . $size . '.gif';
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
	
	static function isAllowedToSendGift($user_id, $gift_id){
		global $wgHuijiPrefix;
		$user = HuijiUser::newFromId($user_id);
		//邀请码 invitation code
		if ( $gift_id == 7 ) {
			$level = $user->getLevel();
			if ( $level->getLevelNumber() >= 5 ) {
				return true;
			}else{
				return false;
			}
		}
		$dbr = wfGetDB( DB_SLAVE );
		$params = array();
		$result = $gift = array();
		$res = $dbr->selectRow(
			'gift',
			array(
				'gift_group', 'gift_prefix',
			),
			array( "gift_id = {$gift_id}" ),
			__METHOD__
		);	
		if ($res != false){
			$gift['group'] = $res->gift_group;
			$gift['prefix'] = $res->gift_prefix;
			if ( $gift['prefix'] != $wgHuijiPrefix && $gift['prefix'] != 'www'){
				return false;
			}
			if ( $gift['group'] == 1){
				return $user->isAllowed('sendStaffGifts');
			} elseif ($gift['group'] == 2){
				return $user->isAllowed('sendBureaucratGifts');
			} elseif ($gift['group'] == 3){
				return $user->isAllowed('sendSysopGifts');
			} elseif ($gift['group'] == 4){
				return $user->isAllowed('sendGifts');
			}
		} else {
			return false;
		}
	}

	static function getGiftList( $group, $limit = 0, $page = 0, $gift_prefix ) {
		global $wgUser;
		$dbr = wfGetDB( DB_SLAVE );
		$params = array();
		$order = 'gift_createdate DESC';
		if ( $limit > 0 ) {
			$limitvalue = 0;
			if ( $page ) {
				$limitvalue = $page * $limit - ( $limit );
			}
			$params['LIMIT'] = $limit;
			$params['OFFSET'] = $limitvalue;
		}

		// if ( $group == 1) {
		// 	$condition = array("gift_group >= {$group} OR gift_creator_user_id = {$wgUser->getID()}");
		// }elseif ( $group == 2 ) {
		// 	$condition = array("gift_group >= {$group} OR gift_creator_user_id = {$wgUser->getID()}");
		// }
		$params['ORDER BY'] = $order;
		$res = $dbr->select(
			'gift',
			array(
				'gift_id', 'gift_createdate', 'gift_name', 'gift_description',
				'gift_given_count', 'isrepeat', 'gift_prefix','gift_type','designation'
			),
			array( "gift_group >= {$group} AND ( gift_prefix = '$gift_prefix' OR gift_prefix = 'www' ) " ),
			__METHOD__,
			$params
		);

		$gifts = array();
		foreach ( $res as $row ) {
			$gifts[] = array(
				'id' => $row->gift_id,
				'timestamp' => ( $row->gift_createdate ),
				'gift_name' => $row->gift_name,
				'gift_description' => $row->gift_description,
				'gift_given_count' => $row->gift_given_count,
				'repeat' => $row->isrepeat,
				'gift_prefix' => $row->gift_prefix,
				'gift_type' => $row->gift_type,
				'designation' => $row->designation
			);
		}
		return $gifts;
	}

	static function getManagedGiftList( $limit = 0, $page = 0 ) {
		global $wgUser;
		$dbr = wfGetDB( DB_SLAVE );

		$where = ''; // Prevent E_NOTICE
		$params['ORDER BY'] = 'gift_createdate';
		if ( $limit ) {
			$params['LIMIT'] = $limit;
		}

		// If the user isn't allowed to perform administrative tasks to gifts
		// and isn't allowed to delete pages, only show them the gifts they've
		// created
		if ( !$wgUser->isAllowed( 'giftadmin' ) && !$wgUser->isAllowed( 'delete' ) ) {
			$where = array( 'gift_creator_user_id' => $wgUser->getID() );
		}

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'gift',
			array(
				'gift_id', 'gift_createdate', 'gift_name', 'gift_description',
				'gift_given_count', 'gift_group', 'gift_creator_user_id',
				'gift_creator_user_name', 'isrepeat', 'gift_prefix','gift_type','designation'
			),
			$where,
			__METHOD__,
			$params
		);

		$gifts = array();
		foreach ( $res as $row ) {
			$gifts[] = array(
				'id' => $row->gift_id,
				'timestamp' => ( $row->gift_createdate ),
				'gift_name' => $row->gift_name,
				'gift_description' => $row->gift_description,
				'gift_given_count' => $row->gift_given_count,
				'repeat' => $row->isrepeat,
				'gift_prefix' => $row->gift_prefix,
				'gift_type' => $row->gift_type,
				'designation' => $row->designation,
			);
		}
		return $gifts;
	}

	static function getCustomCreatedGiftCount( $user_id ) {
		$dbr = wfGetDB( DB_SLAVE );
		$gift_count = 0;
		$s = $dbr->selectRow(
			'gift',
			array( 'COUNT(gift_id) AS count' ),
			array( 'gift_creator_user_id' => $user_id ),
			__METHOD__
		);
		if ( $s !== false ) {
			$gift_count = $s->count;
		}
		return $gift_count;
	}

	static function getGiftCount( $gift_prefix ) {
		$dbr = wfGetDB( DB_SLAVE );
		// $gift_count = 0;
		$s = $dbr->selectRow(
			'gift',
			array( 'COUNT(gift_id) AS count' ),
			array( "gift_prefix = '$gift_prefix' OR gift_prefix = 'www'" ),
			__METHOD__
		);
		if ( $s !== false ) {
			$gift_count = $s->count;
		}
		return $gift_count;
	}
}

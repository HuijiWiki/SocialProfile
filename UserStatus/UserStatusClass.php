<?php
/**
 * This Class manages the User and Site follows.
 */
class UserStatus{
	private $user;
	private $username;
	private $userid;

	/** 
	 * Construct a UserStatus with giver user
	 * @param $aUser User Object
	 */
	public function __construct($aUser){
		$this->user = $aUser;
		$this->username = $aUser->getName();
		$this->userid = $aUser->getId();
	}

	public function getGender(){
		$data = $this->getAllCache( );
		if ($data != ''){
			$all = json_decode($data, true);
		} else {
			$data = $this->getAllDB( );
			$all = json_decode($data, true);
		}
		return $all['gender'];
	}  
	public function getProvince(){
		$data = $this->getAllCache( );
		if ($data != ''){
			$all = json_decode($data, true);
		} else {
			$data = $this->getAllDB( );
			$all = json_decode($data, true);
		}
		return $all['province'];
	}  
	public function getCity(){
		$data = $this->getAllCache();
		if ($data != ''){
			$all = json_decode($data, true);
		} else {
			$data = $this->getAllDB( );
			$all = json_decode($data, true);
		}
		return $all['city'];
	}  
	public function getBirthday(){
		$data = $this->getAllCache();
		if ($data != ''){
			$all = json_decode($data, true);
		} else {
			$data = $this->getAllDB();
			$all = json_decode($data, true);
		}
		return $all['birthday'];
	}  
	public function getStatus(){
		$data = $this->getAllCache();
		if ($data != ''){
			$all = json_decode($data, true);
		} else {
			$data = $this->getAllDB( );
			$all = json_decode($data, true);
		}
		return $all['status'];
	}  
	public function getAll(){
		$data = $this->getAllCache( );
		if ( $data != '' ) {
			return $data;
		} else {
			return $this->getAllDB( );
		}
	}
    /**
     * GET ALL INFO FROM DATABASE
     * @param user User object
     * 
     * @return data String a json string contains requested info.
     */
	public function getAllDB(){
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_profile', 'get_all', $this->user->getName() );

		$gender = $this->user->getOption('gender');
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->selectRow(
			'user_profile',
			array(
				'up_location_state',
				'up_location_city',
				'up_birthday',
				'up_about'
			),
			array(
				'up_user_id' => $this->user->getId(),
			),
			__METHOD__
		);
		$data = '{"name":"'
			.$this->user->getName()
			.'","gender":"'
			.$gender
			.'","province":"'
			.$res->up_location_state
			.'","city":"'
			.$res->up_location_city
			.'","birthday":"'
			.$res->up_birthday
			.'","status":"'
			.$res->up_about
			.'"}';

		$wgMemc->set( $key, $data );
		return $data;
	}
    /**
     * GET ALL INFO FROM CACHE
     * @param user User object
     * 
     * @return data String a json string contains requested info.
     */
	public function getAllCache(){
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_profile', 'get_all', $this->user->getName() );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			wfDebug( "Got user bio and status $data ( user = {$this->user} ) from cache\n" );
			return $data;
		}		
	}
    /**
     * INSERT OR UPDATE ALL THE INFO 
     * @param user User object
     * @param others String.
     * @return true if success.
     */
	public function setAll($gender, $province, $city, $birthday, $status){
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_profile', 'get_all', $this->user->getName() );
		if ($gender == 'male'){
			$this->user->setOption('gender', 'male');
		} elseif ($gender == 'female'){
			$this->user->setOption('gender', 'female');
		} else {
			$this->user->setOption('gender', null);
		}
		$this->user->saveSettings();
		$dbw = wfGetDB( DB_MASTER );
		$dbw->upsert(
			'user_profile',
			array(
				'up_user_id' => $this->user->getId(),
				'up_location_state' => $province,
				'up_location_city' => $city,
				'up_birthday' => $birthday,
				'up_about' => $status,
				'up_date' => date( 'Y-m-d H:i:s' ),
			),
			array(
				'up_user_id' => $this->user->getId(),
			),
			array(
				'up_location_state' => $province,
				'up_location_city' => $city,
				'up_birthday' => $birthday,
				'up_about' => $status,
				'up_date' => date( 'Y-m-d H:i:s' ),
			),			
			__METHOD__
		);
		// $profileId = $dbw->insertId();
		$wgMemc->delete( $key );
		return true;
		// return $profileId;

	}
	
	/**
     * GET USER INFO
     * @param user UserName
     * 
     * @return data String a json string contains requested info.
     */
	// public function getUserAllInfo( $username ){
	// 	$data = $this->getUserAllInfoCache( $username );
	// 	if ( $data != '' ) {
	// 		return $data;
	// 	} else {
	// 		return $this->getUserAllInfoDB( $username );
	// 	}
	// }
	/**
     * GET USER INFO FROM CACHE
     * @param user UserName
     * 
     * @return data String a json string contains requested info.
     */
	// public function getUserAllInfoCache( $username ){
	// 	global $wgMemc;
	// 	$key = wfForeignMemcKey('huiji','', 'user_all_info', 'get_all', $username );
	// 	$data = $wgMemc->get( $key );
	// 	if ( $data != '' ) {
	// 		wfDebug( "Got user bio and status $data ( user = {$this->user} ) from cache\n" );
	// 		return $data;
	// 	}
	// }
	/**
     * GET USER INFO FROM DATABASE
     * @param user UserName
     * 
     * @return data String a json string contains requested info.
     */
	public function getUserAllInfoDB( ){
		global $wgUser;
		$dbr = wfGetDB( DB_SLAVE );
		$result = array();
		$result['username'] = $this->username;
		$user_id = $this->userid;
		$avatar = new wAvatar( $user_id, 'ml' );
		$result['url'] = $avatar->getAvatarURL();
		$gender = $this->getGender();
		$status = $this->getStatus();
		$result['gender'] = $gender;
		$result['status'] = $status;

		
		//关注数
		$usercount = UserUserFollow::getFollowingCount( $this->user );
		$result['usercounts'] = $usercount;
		//被关注数
		$usercounted = UserUserFollow::getFollowerCount( $this->user);
		$result['usercounted'] = $usercounted;

		//编辑数
		$stats = new UserStats( $this->userid, $this->username );
		$stats_data = $stats->getUserStats();
		$result['editcount'] = $stats_data['edits'];
		//等级
		$user_level = new UserLevel( $stats_data['points'] );
		$result['level'] = $user_level->getLevelName();

		//是否关注
		$current_user = $wgUser->getName();
		// return $current_user;
		$follower = UserSiteFollow::getFollowedByUser($current_user);
		if(in_array($this->username, $follower)){
			$result['is_follow'] = 'Y';
		}else{
			$result['is_follow'] = 'N';
		}

		//共同关注
		$cfollow = array();
		$t_user = $this->username;
		$ufollower = UserSiteFollow::getFollowedByUser( $t_user );

		foreach ($follower as $valuea) {
			if(in_array($valuea, $ufollower)){
				$cfollow[] = $valuea; 
			}
		}
		$result['commonfollow'] = $cfollow;
		// //我关注的谁也关注他
		// $followehe = $dbr->select(
		// 	'user_user_follow',
		// 	array( 'f_user_name' ),
		// 	array(
		// 		'f_target_user_name' => $this->username
		// 	),
		// 	__METHOD__
		// );
		// $followhim = array();
		// foreach ($followehe as $val) {
		// 	$foname = $val->f_user_name;
		// 	if(in_array($foname, $follower)){
		// 		$followhim[] = $foname;
		// 	}
		// }
		$result['minefollowerhim'] = self::getFollowingFollowsUser( $t_user,$current_user );
		// $result['minefollowerhim'] = $followhim;
		// $data = $result;
		// $wgMemc->set( $key, $result );
		return $result;
	}

	//我关注的谁也关注他
	public static function getFollowingFollowsUser( $username,$current_user ){
		$data = self::getFollowingFollowsUserCache( $username,$current_user );
		if ( $data != '' ) {
			return $data;
		} else {
			return self::getFollowingFollowsUserDB( $username,$current_user );
		}
	}
	public static function getFollowingFollowsUserCache( $username,$current_user ){
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_user_follow', 'my_following_follows_him', $username );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			wfDebug( "Got top followed $data ( User = {$user} ) from cache\n" );
			return $data;
		}	
	}
	public static function getFollowingFollowsUserDB( $username,$current_user ){
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_user_follow', 'my_following_follows_him', $username );
		// return $current_user;
		$dbr = wfGetDB( DB_SLAVE );
		$follower = UserSiteFollow::getFollowedByUser($current_user);
		$followehe = $dbr->select(
			'user_user_follow',
			array( 'f_user_name' ),
			array(
				'f_target_user_name' => $username
			),
			__METHOD__
		);
		$result = array();
		foreach ($followehe as $val) {
			$foname = $val->f_user_name;
			if(in_array($foname, $follower)){
				$result[] = $foname;
			}
		}
		$wgMemc->set( $key, $result );
		return $result;
	}
 
	// public function getSimpleUserInfoDB( ){
	// 	global $wgMemc;
	// 	$username = $this->user->getName();
	// 	$key = wfForeignMemcKey('huiji','', 'simple_user_info', 'get_all', $username );
	// 	$result['username'] = $username;
	// 	$user_id = $this->user->getId();
	// 	$avatar = new wAvatar( $user_id, 'm' );
	// 	$result['url'] = $avatar->getAvatarURL();
	// 	$dbr = wfGetDB( DB_SLAVE );
	// 	$level = $dbr->select(
	// 		'user_stats',
	// 		array(
	// 			'stats_total_points'
	// 		),
	// 		array(
	// 			'stats_user_name' => $username
	// 		),__METHOD__
	// 	);
	// 	foreach ($level as $value) {
	// 		$result['level'] = $value->stats_total_points;
	// 	}
	// 	$gender = $this->getGender();
	// 	$result['gender'] = $gender;
	// 	$current_user = $wgUser->getName();
	// 	// return $current_user;
	// 	$follower = self::getFollowedByUser($current_user);
	// 	// return 'qqq';
	// 	if(in_array($username, $follower)){
	// 		$result['is_follow'] = 'Y';
	// 	}else{
	// 		$result['is_follow'] = 'N';
	// 	}
	// 	$wgMemc->set( $key, $result );
	// 	return $result;
	// }
}
